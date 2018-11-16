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


namespace Aspi\Framework\Manager\Doctrine;
use \Doctrine\ORM\Tools\Setup;
use \Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\DBAL\Types\Type;
use Pimple\Container;

class Schema
{
     private $cnxRoot = null;
     private $dbParams = array(
         'driver'   => 'pdo_mysql',
         'host'     => '127.0.0.1',
         'user'     => 'root'
     );
     private $config = null;
     private $createdTables = array();
     public function __construct(Container $container)
     {
   
        if($container['isCMS'])
        {
          $locations =  array(__DIR__.'/../../../../../../../src/CMS/Framework/Entity');
        }
        else
        {
            $locations = array(__DIR__.'/../Entity');
        }
        $this->config = Setup::createAnnotationMetadataConfiguration($locations,true);
     }
     public function connectRootUser(string $rootPassword)
     {
        $this->dbParams['password'] = $rootPassword;
        $em = EntityManager::create($this->dbParams, $this->config);
        $this->cnxRoot = $em->getConnection();
        Type::addType('file', 'Aspi\Framework\Doctrine\Type\FileType');
        $this->cnxRoot->getDatabasePlatform()->registerDoctrineTypeMapping('file', 'file');
        $this->cnxRoot->connect();
     }
     public function getCreatedTables()
     {
       return $this->createdTables;
     }
     public function createDatabase(string $dbName,string $userCMS, string $passwordCMS)
     {
         if(($this->cnxRoot != null) && ($this->cnxRoot->isConnected()===true))
         {
            $this->cnxRoot->getSchemaManager()->createDatabase($dbName);
            $stmt = $this->cnxRoot->prepare('GRANT  SELECT, INSERT, DELETE,UPDATE,ALTER ON '.$dbName.'.* TO :user@localhost IDENTIFIED BY :pass');
            $stmt->bindValue(':user',$userCMS);
            $stmt->bindValue(':pass',$passwordCMS);
            $stmt->execute();
            //flush priviledges (mysql command)
            $this->cnxRoot->query('FLUSH PRIVILEGES;');
    

            //close database
            $this->cnxRoot->close();
            $this->dbParams['dbname'] =$dbName;
            $em = EntityManager::create($this->dbParams, $this->config);
            $cnxCMS = $em->getConnection();
            $cnxCMS->connect();
            if($cnxCMS->isConnected()===true)
            {
                //Create schema / all the table in database
                $meta = $em->getMetadataFactory()->getAllMetadata();
                $tool = new SchemaTool($em);
                $tool->createSchema($meta);
                $em->getConnection()->query('alter table '.$dbName.'.files Modify data longblob');
            }
            $this->createdTables = $cnxCMS->getSchemaManager()->listTableNames();
            $cnxCMS->close();
            
         }

     }
    
}