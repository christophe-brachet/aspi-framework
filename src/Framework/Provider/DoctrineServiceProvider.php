<?php
/*
*MIT License
*
*Copyright (c) 2018 Christophe Brachet
*
*Permission is hereby granted, free of charge, to any person obtaining a copy
*of this software and associated documentation files (the "Software"), to deal
*in the Software without restriction, including without limitation the rights
*to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
*copies of the Software, and to permit persons to whom the Software is
*furnished to do so, subject to the following conditions:
*
*The above copyright notice and this permission notice shall be included in all
*copies or substantial portions of the Software.
*
*THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
*IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
*FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
*AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
*LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
*OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
*SOFTWARE.
*
*
*/
declare(strict_types=1);

namespace Aspi\Framework\Provider;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Tools\Setup;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Doctrine\DBAL\Types\Type;
/**
 * A ServiceProvider for registering services related to
 * Doctrine in a DI container(pimple).
 *
 * If the project had custom repositories (e.g. UserRepository)
 * they could be registered here.
 */

class DoctrineServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $container)
    {
        $container['SchemaManager'] = function (Container $container): \Aspi\Framework\Manager\Doctrine\Schema {
          return new  \Aspi\Framework\Manager\Doctrine\Schema();
        };
        $container['TemplateManager'] = function (Container $container): \Aspi\Framework\Manager\Doctrine\Template {
         
          return new  \Aspi\Framework\Manager\Doctrine\Template($container);
        };
        $container['SiteManager'] = function (Container $container): \Aspi\Framework\Manager\Doctrine\Site {
         
          return new  \Aspi\Framework\Manager\Doctrine\Site($container);
        };
        $container['FileManager'] = function (Container $container): \Aspi\Framework\Manager\Doctrine\File {
         
          return new  \Aspi\Framework\Manager\Doctrine\File($container);
        };
        $container['em'] = function (Container $container): EntityManager {
            $container['DatabaseConfig']->refresh();
            
            $connection = array(
                'driver' => 'pdo_mysql',
                'host' => 'localhost',
                'port' => 3306,
                'dbname' => $container['DatabaseConfig']->get('dbname'),
                'user' => $container['DatabaseConfig']->get('user'),
                'password' => $container['DatabaseConfig']->get('password')
            );
            // ensure standard doctrine annotations are registered
            \Doctrine\Common\Annotations\AnnotationRegistry::registerFile(
                  __DIR__.'/../../../vendor/doctrine/orm/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php'
            );
            // Second configure ORM
            // globally used cache driver, in production use APC or memcached
            $cache = new \Doctrine\Common\Cache\ArrayCache();
            // standard annotation reader
            $annotationReader = new \Doctrine\Common\Annotations\AnnotationReader();
            $cachedAnnotationReader = new \Doctrine\Common\Annotations\CachedReader(
              $annotationReader, // use reader
              $cache // and a cache driver
            );
            // create a driver chain for metadata reading
            $driverChain = new \Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain();
            // load superclass metadata mapping only, into driver chain
            // also registers Gedmo annotations.NOTE: you can personalize it
            \Gedmo\DoctrineExtensions::registerMappingIntoDriverChainORM(
              $driverChain, // our metadata driver chain, to hook into
              $cachedAnnotationReader // our cached annotation reader
            );
            // now we want to register our application entities,
            // for that we need another metadata driver used for Entity namespace
            $annotationDriver = new \Doctrine\ORM\Mapping\Driver\AnnotationDriver(
              $cachedAnnotationReader, // our cached annotation reader
              array(__DIR__.'/../Entity') // paths to look in
            );
            // NOTE: driver for application Entity can be different, Yaml, Xml or whatever
            // register annotation driver for our application Entity fully qualified namespace
            $driverChain->addDriver($annotationDriver, 'Aspi\Framework\Entity');
            
            // general ORM configuration
            $config = new \Doctrine\ORM\Configuration();
            $config->setProxyDir(sys_get_temp_dir());
            $config->setProxyNamespace('Proxy');
            $config->setAutoGenerateProxyClasses(false); // this can be based on production config.
            // register metadata driver
            $config->setMetadataDriverImpl($driverChain);
            // use our allready initialized cache driver
            $config->setMetadataCacheImpl($cache);
            $config->setQueryCacheImpl($cache);

            // Third, create event manager and hook prefered extension listeners
            $evm = new \Doctrine\Common\EventManager();
            // gedmo extension listeners

            // sluggable
            $sluggableListener = new \Gedmo\Sluggable\SluggableListener();
            // you should set the used annotation reader to listener, to avoid creating new one for mapping drivers
            $evm->addEventSubscriber($sluggableListener);

              // translatable
              $translatableListener = new \Gedmo\Translatable\TranslatableListener();
              // current translation locale should be set from session or hook later into the listener
              // most important, before entity manager is flushed
              $translatableListener->setTranslatableLocale('fr');   
              $evm->addEventSubscriber($translatableListener);

            // tree
            $treeListener = new \Gedmo\Tree\TreeListener();
            $treeListener->setAnnotationReader($cachedAnnotationReader);
            $evm->addEventSubscriber($treeListener);

            // loggable, not used in example
            $loggableListener = new \Gedmo\Loggable\LoggableListener;
            $loggableListener->setAnnotationReader($cachedAnnotationReader);
            $loggableListener->setUsername('admin');
            $evm->addEventSubscriber($loggableListener);

            // timestampable
            $timestampableListener = new \Gedmo\Timestampable\TimestampableListener();
            $timestampableListener->setAnnotationReader($cachedAnnotationReader);
            $evm->addEventSubscriber($timestampableListener);

            // blameable
            $blameableListener = new \Gedmo\Blameable\BlameableListener();
            $blameableListener->setAnnotationReader($cachedAnnotationReader);
            $blameableListener->setUserValue('MyUsername'); // determine from your environment
            $evm->addEventSubscriber($blameableListener);

          

            // sortable, not used in example
            $sortableListener = new \Gedmo\Sortable\SortableListener;
            $sortableListener->setAnnotationReader($cachedAnnotationReader);
            $evm->addEventSubscriber($sortableListener);

            // mysql set names UTF-8 if required
            $evm->addEventSubscriber(new \Doctrine\DBAL\Event\Listeners\MysqlSessionInit());

            $em = \Doctrine\ORM\EntityManager::create($connection, $config, $evm);
            if(!Type::hasType('file'))
            {
              Type::addType('file', 'Aspi\Framework\Doctrine\Type\FileType');
              $em->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping('file', 'file');
            }

            return $em;
            
      };
    }
}