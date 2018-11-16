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
class TwigExtension extends \Twig_Extension
{
    private $container = null;
    public function __construct(Container $container)
    {
        
        $this->container = $container;
    }
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('path', array($this, 'path')),
            new \Twig_SimpleFunction('dbg_renderHead', array($this, 'renderHead'),['is_safe' => ['html']]),
            new \Twig_SimpleFunction('dbg_render', array($this, 'render'),['is_safe' => ['html']]),
            new \Twig_SimpleFunction('aspi_title', array($this, 'aspiTitle'))
        ];
    }
    public function path($name, $data = [], $queryParams = [])
    {
        return  $this->container['router']->generate($name, $data, $queryParams);
    }
    public function renderHead()
    {
        return  $this->container['DebugBar']->getJavascriptRenderer()->renderHead();
    }
    public function aspiTitle()
    {
        if(!$this->container['isCMS'])
        {
            return  'Aspi Framework';
        }
        else
        {
            if($this->container['HttpConfig']->get('aspi_title')!=NULL)
            {
                return $this->container['HttpConfig']->get('aspi_title');
            }
            else
            {
                return 'Aspi CMS';
            }
        }
    }
    public function render()
    {
        return  $this->container['DebugBar']->getJavascriptRenderer()->render();
    }


    public function getName()
    {
        return 'aspi-framework';
    }


}