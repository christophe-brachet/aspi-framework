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
namespace Aspi\Framework\View;
use Pimple\Container;
use Zend\Diactoros\Response\HtmlResponse;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Aspi\Framework\DebugBar\DataCollector\MvcCollector;
use Aspi\Framework\View\Loader\DoctrineTwigLoader;

class Twig
{
    
    /**
    * Twig environment
    *
    * @var \Twig_Environment
    */
    protected $environment;

    private $container;

       /**
    * Create new Twig view
    *
    * @param IPimple\Container $container
    * 
    */
    public function __construct(Container $container)
    {
     
        $loader = new  DoctrineTwigLoader($container);
        $this->environment = new \Twig_Environment($loader,array());
        $this->environment->addExtension(new \Aspi\Framework\View\TwigExtension($container));
        $this->environment->addExtension(new TranslationExtension($container['Translator']));
        $this->environment->addGlobal('language',$container['Language']->get());
        $container['DebugBar']->addCollector(new MvcCollector($container,$this->environment));
        $this->container = $container;
       

    }

    public function render($status,$template, $data = [])
    {
  
        $this->container['StatusPage']=$status;
        $this->container['Template']=$template;
        $result =  $this->environment->render($template, $data);
        return new HtmlResponse($result,$status);
    }

}