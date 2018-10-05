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
namespace Aspi\Framework\Application;

use Pimple\Container;
use Symfony\Component\Console\Application;
use \Aspi\Framework\Command\WebServerCommand;
use \Aspi\Framework\Command\InitAppCommand;
use \Aspi\Framework\Provider\ConfigurationServiceProvider;
use \Aspi\Framework\Provider\DoctrineServiceProvider;
use \Aspi\Framework\Provider\ServerServiceProvider;
use \Aspi\Framework\Provider\SerializerServiceProvider;
use \Aspi\Framework\Provider\FileServiceProvider;
use \Aspi\Framework\Provider\SassServiceProvider;
use \Aspi\CMS\Provider\CMSProvider;

class Console extends \Symfony\Component\Console\Application
{
    public function __construct(bool $isCMS)
    {
        parent::__construct();
        $container = new Container(array('isCMS'=>$isCMS));
        $container->register(new FileServiceProvider());
        $container->register(new SerializerServiceProvider());
        $container->register(new DoctrineServiceProvider()); 
        $container->register(new ConfigurationServiceProvider());
        $container->register(new SassServiceProvider()); 
        if($isCMS)
        {
            $container->register(new CMSProvider());
        }
        $container->register(new ServerServiceProvider());
        $this->add(new WebServerCommand($container));
        $this->add(new InitAppCommand($container));
    }
}