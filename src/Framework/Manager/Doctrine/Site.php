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
namespace  Aspi\Framework\Manager\Doctrine;
use \Pimple\Container;

class Site
{
    private $container = null;
    public function __construct(Container $container)
    {
        
        $this->container = $container;
    }
    public function add(string $domainName,string $routingString,$theme =null,$hook=null)
    {
       
        if(!$this->container['isCMS'])
        {
            $site = new \Aspi\Framework\Entity\Site();
        }
        else
        {
            $site = new \Aspi\CMS\Framework\Entity\Site();
            if($theme !=null)
            {
                if(get_class($theme) == 'Aspi\CMS\Framework\Entity\Theme')
                {
                    $site->setTheme($theme);
                }
                else
                {
                 throw Exception('$theme parameter must be an instance of Aspi\CMS\Entity\Theme');
                }
            }
            if($hook !=null)
            {
                $site->setHook($hook);
            }
        }
        $site->setDomainName($domainName);
        $site-> setStatus(\Aspi\Framework\Entity\Site::STATUS_PUBLISHED);
        $site->setRouting($routingString);
        $this->container['em']->persist($site);
        $this->container['em']->flush(); 
    }
}