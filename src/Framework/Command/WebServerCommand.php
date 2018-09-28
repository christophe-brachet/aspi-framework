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
declare(strict_types=1);
namespace Aspi\Framework\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Pimple\Container;

class WebServerCommand extends Command
{
    private $container = null;
    public function __construct(Container $container)
    {
        parent::__construct();
        $this->container = $container;
    }
    protected function configure()
    {
        $this
        // the name of the command (the part after "bin/console")
        ->setName('aspi:webserver')

        // the short description shown while running "php bin/console list"
        ->setDescription('ASPI - Start Webserver')

        // the full command description shown when running the command with
        // the "--help" option
        ->setHelp('This command starts ASPI Webserver');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $port = $this->container['HttpConfig']->get('port'); 
        $host = $this->container['HttpConfig']->get('host'); 
        if(($host==null) && ($port==null))
        {
            $io->error('WebServer miss-configuration : host and port failed !');
            return;
        }
        if($host==null)
        {
            $io->error('WebServer miss-configuration : host failed !');
            return;
        }
        if($port==null)
        {
            $io->error('WebServer miss-configuration : port failed !');
            return;
        }
        $io->success('ASPI Webserver is listening on '.$host.' : '.$port);
        $this->container['WebServer']->start();
    }
}