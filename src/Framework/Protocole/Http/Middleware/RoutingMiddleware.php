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
namespace Aspi\Framework\Protocole\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Config\FileLocator;
use Aspi\Framework\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Aspi\Framework\Protocole\Http\Middleware\MiddleWareInterface;
use Aspi\Framework\Routing\Router;
use Pimple\Container;
use Aspi\Framework\Doctrine\Routing\YamlDoctrineLoader;


class  RoutingMiddleware implements MiddleWareInterface
{
    private $container = null;
    public function __construct(Container $container)
    {
       
        $this->container = $container;
    }
    /**
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, callable $next):ResponseInterface
    {

        
            $loader = new YamlDoctrineLoader();
            $routes = $loader->load($this->container['em'],$this->container['isCMS'],'website.org');
            $context = new RequestContext();
            $matcher = new UrlMatcher($routes, $context);
            $generator = new UrlGenerator($routes, $context);
            try {
                $this->container['router'] = function ($container) use($matcher,$generator) {
                    return new Router($matcher,$generator);
                };

                if($request->getUri()->getPath()=='/')
                {
                    return new RedirectResponse($this->container['router']->generate('homepage.fr'));
                }
                
                $parameters =  $this->container['router']->match($request->getUri()->getPath());
      
                if(!isset($parameters['_controller']))
                {
                    return new HtmlResponse('Inexistant controller',505);
                }
                $realController =  $parameters['_controller'];
                $controllerChunks = explode("::",$realController);
                if(count($controllerChunks)!=2)
                {
                    return new HtmlResponse('Controller poorly configured',505);
                }
      
                $realController = $controllerChunks[0];
                $controller = $realController.'Controller';
                $this->container['Controller']=$realController;
                $realAction =  $controllerChunks[1];
                $this->container['Action'] = $realAction;
                try {
                    $reflector = new \ReflectionClass($controller);

                    if(!$reflector->hasMethod( "__construct" ))
                    {
                        return new HtmlResponse('Controller: '.  $controller .' must have a constructeur.',505);
                    }
                    $methodInstance = $reflector->getMethod('__construct');
                    if(!$methodInstance->isPublic())
                    {
                        return new HtmlResponse('Controller: '.  $controller .' must have a public constructeur.',505);
                    }
                    $param = $reflector->getMethod('__construct')->getParameters();
                    if(count($param)!= 1)
                    {
                        return new HtmlResponse('Constructor must contains a parameter.',505);
                    }
                    $type = '';
                    if($param[0]->getClass()!=null)
                    {
                        $type = $param[0]->getClass()->getName();
                    }
                    if($type != 'Pimple\Container')
                    {
                        return new HtmlResponse('Constructor must take as parameter a object Pimple\Container.',505);
                    }
                    if(!$reflector->hasMethod($realAction))
                    {
                        return new HtmlResponse('Inexistant action : '.$realAction,505);
                    }
                    $methodInstance = $reflector->getMethod($realAction);
                    if(!$methodInstance->isPublic())
                    {
                        return new HtmlResponse('Action '.$realAction.' must be public.',505);
                    }
                    $param = $reflector->getMethod($realAction)->getParameters();
                    if(count($param)!= 1)
                    {
                        return new HtmlResponse('Action '.$realAction.' must contains a parameter.',505);
                    }
                    $type = '';
                    if($param[0]->getClass()!=null)
                    {
                        $type = $param[0]->getClass()->getName();
                    }
                    if($type != 'Psr\Http\Message\ServerRequestInterface')
                    {
                        return new HtmlResponse('Action '.$realAction.' must contains a parameter.(Psr\Http\Message\ServerRequestInterface)',505);
                    }
                    unset($parameters['_controller']);
                    $locale = $parameters['_locale'];
                    $this->container['Language']->set($locale);
                    unset($parameters['_locale']);
                    unset($parameters['_route']);
                    foreach ($parameters as $key => $value) {
                        $request->get[$key] = $value;
                    }
                    $request->get['language']= $locale;
                    $instance = $reflector->newInstance($this->container);
                    $response=  $methodInstance->invoke($instance, $request);
                    if($response == null)
                    {
                        return new HtmlResponse('Action ' .$realAction.' must return something ...');
                    }
                    if(gettype($response)=='object') 
                    {
                        if(get_class($response) != 'Zend\Diactoros\Response\HtmlResponse')
                        {
                            return new HtmlResponse('Action ' .$realAction.' must return Zend\Diactoros\Response\HtmlResponse type');
                        }
                    }
                    else
                    {
                        return new HtmlResponse('Action ' .$realAction.' must return Zend\Diactoros\Response\HtmlResponse type');
                    }
                    return $response;
                    

                    
                  

                } catch (LogicException $ex) {
                    $output = $this->container['error']->handleException($ex);
                    return new HtmlResponse($output, 500);
                } catch (\ReflectionException $ex) {
                    return new HtmlResponse('Inexistant controller : '.$realController,505);
                }
               
            } catch (ResourceNotFoundException $exception) {
                // Call the next middleware
                return $next($request);
            } catch (\Exception $exception) {
                return new HtmlResponse('An error occurred : '.$exception->getMessage(), 505);
            }
         
         
    }
}