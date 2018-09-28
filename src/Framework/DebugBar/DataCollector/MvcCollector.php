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
namespace Aspi\Framework\DebugBar\DataCollector;
use Pimple\Container;

class MvcCollector extends \DebugBar\DataCollector\DataCollector implements \DebugBar\DataCollector\Renderable, \DebugBar\DataCollector\AssetProvider
{
    private $container;
    private $environment;
    public function __construct(Container $container,\Twig_Environment $environment)
    {
     
        $this->container = $container;
        $this->environment = $environment;

    }
    /**
     * @return array
     */
    public function collect()
    {
       
        $data = array();
        $data['Status'] = $this->container['StatusPage'];
        $data['Controller']=$this->container['Controller'];
        $data['Action'] = $this->container['Action'];
        $data['Template'] = $this->container['Template'];
        $data['Language'] = $this->container['Language']->get();
        $data['Charset'] = $this->environment->getCharset();
        return $data;
    }
    /**
     * @return string
     */
    public function getName()
    {
        return 'mvc';
    }
    /**
     * @return array
     */
    public function getAssets() {
   
    }
    /**
     * @return array
     */
    public function getWidgets()
    {
        $widget = "PhpDebugBar.Widgets.VariableListWidget";
        return array(
            "MVC" => array(
                "icon" => "tags",
                "widget" => $widget,
                "map" => "mvc",
                "default" => "{}"
            )
        );
    }
}