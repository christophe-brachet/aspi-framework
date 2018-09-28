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

namespace Aspi\Framework\Configuration;
use Interop\Container\ContainerInterface;
use Noodlehaus\Config;
use \Pimple\Container;
class Database
{
    private $configDir = __DIR__.'/../../../application/Config/Database/';
    public function __construct(Container $container)
    {
        
        $this->container = $container;
    }
    public function refresh()
    {
        

        $configFile =  $this->configDir.'connexion.json';
        $this->conf = Config::load($configFile);
    }
    public function get(string $key)
    {
        if($this->conf != null)
        {   
            return $this->conf->get($key); 
        }
        else
        {
            return null;
        }
       
    }
    public function writeFile(string $dbname,string $userFramework,string $passwordFramework)
    {
      
        $this->container['DatabaseConfigModel']->setUser($userFramework);
        $this->container['DatabaseConfigModel']->setPassword($passwordFramework);
        $this->container['DatabaseConfigModel']->setDbname($dbname);
        $jsonContent = $this->container['Serializer']->serialize($this->container['DatabaseConfigModel'], 'json');
        if(!$this->container['FileSystem']->exists($this->configDir))
        {
            try {
                $this->container['FileSystem']->mkdir($this->configDir);
            } catch (IOExceptionInterface $exception) {
                echo "An error occurred while creating your directory at ".$exception->getPath();
            }
        }
        $configFile =  $this->configDir.'connexion.json';
        $this->container['FileSystem']->touch($configFile);
        $this->container['FileSystem']->dumpFile($configFile,  $jsonContent);
    }

}