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
*/
namespace Aspi\Framework\Doctrine\Routing;
use Aspi\Framework\Routing\RouteGenerator\I18nRouteGenerator;
use Aspi\Framework\Routing\RouteGenerator\RouteGeneratorInterface;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml as YamlParser;
use Pimple\Container;

/**
 * YamlFileLoader loads Yaml routing files.
 */
class YamlDoctrineLoader
{
    private static $availableKeys = array(
        'locales', 'resource', 'type', 'prefix', 'pattern', 'path', 'host', 'schemes', 'methods', 'defaults', 'requirements', 'options', 'condition',
    );
    /**
     * @var YamlParser|null
     */
    private $yamlParser;
    /**
     * @var RouteGeneratorInterface
     */
    private $routeGenerator;

    private $container = null;
    public function __construct(Container $container)
    {

        $this->routeGenerator = new I18nRouteGenerator();
        $this->container = $container;
    }
    /**
     * Loads a Yaml file.
     *
     * @param string      $file A Yaml file path
     * @param string|null $type The resource type
     *
     * @return RouteCollection A RouteCollection instance
     *
     * @throws \InvalidArgumentException When a route can't be parsed because YAML is invalid
     */
    public function load($domainName, $type = null)
    {
        $path = 'routing.yml';
      
        if(!$this->container['isCMS'])
        {
          
            $site = $this->container['em']->getRepository('\Aspi\Framework\Entity\Site')->getByDomain($domainName);
        }
        else
        {
          
            $site = $this->container['em']->getRepository('\Aspi\CMS\Framework\Entity\Site')->getByDomain($domainName);
            $this->container['theme'] = $site['name'];
            $this->container['hook'] = $site['hook'];
     
        }
        if(!$site)
        {
            throw new \InvalidArgumentException(sprintf('File not found for domain "%s".', $domainName));
        }
        if (null === $this->yamlParser) {
            $this->yamlParser = new YamlParser();
        }
        try {
$parsedString =<<<EOT
homepage:
    locales:  { en: "/welcome", fr: "/bienvenue", de: "/willkommen" }
    defaults: { _controller: '\Controller\Home::Index'}

page:
    locales:  { en: "/page/", fr: "/bienvenue", de: "/willkommen" }
    defaults: { _controller: '\Controller\Home::Index'}

loginpage:
    locales:  { en: "/login", fr: "/se-connecter"}    
    defaults: { _controller: '\Controller\Home::Login' }

admin:
    path:     /admin
    defaults: { _controller: 'App\Controller\DefaultController::default' }
EOT;

            $parsedString .=$site['routing'];
            $parsedConfig = $this->yamlParser->parse($parsedString);
        } catch (ParseException $e) {
            throw new \InvalidArgumentException(sprintf('The file "%s" does not contain valid YAML.', $path), 0, $e);
        }
        $collection = new RouteCollection();
        // empty file
        if (null === $parsedConfig) {
            return $collection;
        }
        // not an array
        if (!is_array($parsedConfig)) {
            throw new \InvalidArgumentException(sprintf('The file "%s" must contain a YAML array.', $path));
        }
        foreach ($parsedConfig as $name => $config) {
            if (isset($config['pattern'])) {
                if (isset($config['path'])) {
                    throw new \InvalidArgumentException(sprintf('The file "%s" cannot define both a "path" and a "pattern" attribute. Use only "path".', $path));
                }
                $config['path'] = $config['pattern'];
                unset($config['pattern']);
            }
            $this->validate($config, $name, $path);
            if (isset($config['resource'])) {
                $this->parseImport($collection, $config, $path, $file);
            } else {
                $this->parseRoute($collection, $name, $config, $path);
            }
        }
        return $collection;
    }
    /**
     * @inheritdoc
     */
    public function supports($resource, $type = null)
    {
        return 'be_simple_i18n' === $type && is_string($resource) && in_array(pathinfo($resource, PATHINFO_EXTENSION), array('yml', 'yaml'), true);
    }
    /**
     * Parses a route and adds it to the RouteCollection.
     *
     * @param RouteCollection $collection A RouteCollection instance
     * @param string $name Route name
     * @param array $config Route definition
     * @param string $path Full path of the YAML file being processed
     */
    protected function parseRoute(RouteCollection $collection, $name, array $config, $path)
    {
        $defaults = isset($config['defaults']) ? $config['defaults'] : array();
        $requirements = isset($config['requirements']) ? $config['requirements'] : array();
        $options = isset($config['options']) ? $config['options'] : array();
        $host = isset($config['host']) ? $config['host'] : '';
        $schemes = isset($config['schemes']) ? $config['schemes'] : array();
        $methods = isset($config['methods']) ? $config['methods'] : array();
        $condition = isset($config['condition']) ? $config['condition'] : null;
        if (isset($config['locales'])) {
            $collection->addCollection(
                $this->routeGenerator->generateRoutes(
                    $name,
                    $config['locales'],
                    new Route('', $defaults, $requirements, $options, $host, $schemes, $methods, $condition)
                )
            );
        } else {
            $route = new Route($config['path'], $defaults, $requirements, $options, $host, $schemes, $methods, $condition);
            $collection->add($name, $route);
        }
    }
    /**
     * Parses an import and adds the routes in the resource to the RouteCollection.
     *
     * @param RouteCollection $collection A RouteCollection instance
     * @param array $config Route definition
     * @param string $path Full path of the YAML file being processed
     * @param string $file Loaded file name
     */
    protected function parseImport(RouteCollection $collection, array $config, $path, $file)
    {
        $type = isset($config['type']) ? $config['type'] : null;
        $prefix = isset($config['prefix']) ? $config['prefix'] : '';
        $defaults = isset($config['defaults']) ? $config['defaults'] : array();
        $requirements = isset($config['requirements']) ? $config['requirements'] : array();
        $options = isset($config['options']) ? $config['options'] : array();
        $host = isset($config['host']) ? $config['host'] : null;
        $condition = isset($config['condition']) ? $config['condition'] : null;
        $schemes = isset($config['schemes']) ? $config['schemes'] : null;
        $methods = isset($config['methods']) ? $config['methods'] : null;
        $this->setCurrentDir(dirname($path));
        $subCollection = $this->import($config['resource'], $type, false, $file);
        /* @var $subCollection \Symfony\Component\Routing\RouteCollection */
        $subCollection = $this->routeGenerator->generateCollection($prefix, $subCollection);
        if (null !== $host) {
            $subCollection->setHost($host);
        }
        if (null !== $condition) {
            $subCollection->setCondition($condition);
        }
        if (null !== $schemes) {
            $subCollection->setSchemes($schemes);
        }
        if (null !== $methods) {
            $subCollection->setMethods($methods);
        }
        $subCollection->addDefaults($defaults);
        $subCollection->addRequirements($requirements);
        $subCollection->addOptions($options);
        $collection->addCollection($subCollection);
    }
    /**
     * @inheritDoc
     */
    protected function validate($config, $name, $path)
    {
        if (!is_array($config)) {
            throw new \InvalidArgumentException(sprintf('The definition of "%s" in "%s" must be a YAML array.', $name, $path));
        }
        if ($extraKeys = array_diff(array_keys($config), self::$availableKeys)) {
            throw new \InvalidArgumentException(sprintf(
                'The routing file "%s" contains unsupported keys for "%s": "%s". Expected one of: "%s".',
                $path,
                $name,
                implode('", "', $extraKeys),
                implode('", "', self::$availableKeys)
            ));
        }
        if (isset($config['resource']) && isset($config['path'])) {
            throw new \InvalidArgumentException(sprintf(
                'The routing file "%s" must not specify both the "resource" key and the "path" key for "%s". Choose between an import and a route definition.',
                $path,
                $name
            ));
        }
        if (!isset($config['resource']) && isset($config['type'])) {
            throw new \InvalidArgumentException(sprintf(
                'The "type" key for the route definition "%s" in "%s" is unsupported. It is only available for imports in combination with the "resource" key.',
                $name,
                $path
            ));
        }
        if (!isset($config['resource']) && !isset($config['path']) && !isset($config['locales'])) {
            throw new \InvalidArgumentException(sprintf(
                'You must define a "path" for the route "%s" in file "%s".',
                $name,
                $path
            ));
        }
    }
}