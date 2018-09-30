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
use Aspi\Framework\Protocole\Http\Middleware\MiddlewareInterface; 

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Pimple\Container;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\SapiEmitter;

use Aspi\Framework\Protocole\Http\Middleware\RoutingMiddleware;

class Kernel implements  MiddlewareInterface
{
     /**
     * @var Middleware[]
     */
    private $middlewares;
    
    /**
     * @var Container
     */
    private $container;

    
    public function __construct(Container $container)
    {
    
        $this->container =new Container();
        $this->middlewares = array();
       //$this->registerServiceProvider(new \Aspi\Framework\Provider\FormServiceProvider($this->container)); 
        $this->registerServiceProvider(new \Aspi\Framework\Provider\LanguageServiceProvider());
        $this->registerServiceProvider(new \Aspi\Framework\Provider\ViewServiceProvider());
        $this->registerServiceProvider(new \Aspi\Framework\Provider\SessionServiceProvider());
        $this->registerServiceProvider(new \Aspi\Framework\Provider\UserServiceProvider()); 
        $this->add('\Aspi\Framework\Protocole\Http\Middleware\ErrorMiddleware');
        foreach ($container->keys() as $serviceName){
            if(!isset($this->container[$serviceName]))
            {
                $this->container[$serviceName] = $container[$serviceName]; 
            }
       
        }
    
     
        
    
    }
    public function registerServiceProvider(\Pimple\ServiceProviderInterface $serviceProvider)
    {
        
        $this->container->register($serviceProvider);
    }
    public function add(string $middleware)
    {
        $this->middlewares[] = $middleware;  
    }
    public function __invoke(ServerRequestInterface $psrRequest, callable $next) : ResponseInterface
    {
        
        $i=0;
        foreach (array_reverse($this->middlewares) as $middleware) {
            $name = 'middleware'.$i;
            $next = function (ServerRequestInterface $psrRequest) use ($name,$middleware, $next) {
                
                $this->container[$name] = function ($container) use($middleware) {
                    //return new $middleware($container);
                    $oReflectionClass = new \ReflectionClass($middleware); 
                    $instance = $oReflectionClass->newInstance($container);
                    return $instance;
                };
                return call_user_func( $this->container[$name],$psrRequest,$next);
            };
            $i++;
         
        }
        // Invoke the first middleware
        return $next($psrRequest);
    }
    public function run(\Psr\Http\Message\ServerRequestInterface $psrRequest) : \Psr\Http\Message\ResponseInterface
    {
       // Run the application
        $notFoundCallBack = function () {
            if(isset($this->container['twig']))
            {
                return  $this->container['twig']->render(404,'404.twig.html',array());
            }
            else
            {
                return new HtmlResponse('<h1>Not Found twig</h1>', 404);
            }
        
        };
    
        $this->add('\Aspi\Framework\Protocole\Http\Middleware\RoutingMiddleware');
        $this->add('\Aspi\Framework\Protocole\Http\Middleware\AssetsMiddleware');
        $response = $this($psrRequest,$notFoundCallBack);
        return $response;
    }
}