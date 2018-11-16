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
    private $mode = null;
    private $messageError='';
    public function getMessageError($messageError)
    {
        if($this->mode != 'production')
        {
            return new HtmlResponse($messageError,500);
        }
        else
        {
            return new HtmlResponse($this->messageError,500);
        }
    }
    public function __construct(Container $container)
    {
       
        $this->container = $container;
        $this->mode =  $this->container['HttpConfig']->get('mode');
        if($this->mode == null)
        {
            $this->mode = 'development';
        }
        $this->messageError = '<h1>500 Server Error Oops, Something Went Wrong!</h1>Please contact the website administrator ';
        $email =  $this->container['HttpConfig']->get('administrator_email');
        if($email != null)
        {
            $this->messageError .= ': <a href="mailto:'.$email.'">Administrator</a> ('.$email.')';
        }
        $this->messageError .= '.';
        
    }
    /**
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, callable $next):ResponseInterface
    {
            $host  = $request->getUri()->getHost();
            $loader = new YamlDoctrineLoader($this->container);
            $routes = $loader->load($host);
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
                    return $this->getMessageError('Inexistant controller');
                }
                $controller =  $parameters['_controller'];
                $controllerChunks = explode("::",$controller);
                if(count($controllerChunks)!=2)
                {
                    return $this->getMessageError('Controller poorly configured');
                }
      
                $this->container['Controller'] = $controllerChunks[0];
                $route =  $parameters['_route'];
                $loginPages = array('loginpage.fr','loginpage.en');
                if(!$this->container['isCMS']||in_array($route,$loginPages))
                {
                    $controller =  $this->container['Controller'].'Controller';
                }
                else
                {
                
                    $execCode =str_replace('<?php','',$this->container['hook']);
                    unset($this->container['hook']);
                    $execCode =str_replace('?>','',$execCode);
                    eval($execCode);
                    $controller =  '\\Aspi\\CMS\\Framework'.$this->container['Controller'].'Controller';
                }
                $this->container['Action']  =  $controllerChunks[1];
                try {
                    $reflector = new \ReflectionClass($controller);

                    if(!$reflector->hasMethod( "__construct" ))
                    {
                        return $this->getMessageError('Controller: '.  $controller .' must have a constructeur.');
                    }
                    $methodInstance = $reflector->getMethod('__construct');
                    if(!$methodInstance->isPublic())
                    {
                        return $this->getMessageError('Controller: '.  $controller .' must have a public constructeur.');
                    }
                    $param = $reflector->getMethod('__construct')->getParameters();
                    if(count($param)!= 1)
                    {
                        return $this->getMessageError('Constructor must contains a parameter.');
                    }
                    $type = '';
                    if($param[0]->getClass()!=null)
                    {
                        $type = $param[0]->getClass()->getName();
                    }
                    if($type != 'Pimple\Container')
                    {
                        return $this->getMessageError('Constructor must take as parameter a object Pimple\Container.');
                    }
                    if(!$reflector->hasMethod($this->container['Action']))
                    {
                        return $this->getMessageError('Inexistant action : '.$this->container['Action']);
                    }
                    $methodInstance = $reflector->getMethod($this->container['Action']);
                    if(!$methodInstance->isPublic())
                    {
                        return $this->getMessageError('Action '.$this->container['Action'].' must be public.');
                    }
                    $param = $reflector->getMethod($this->container['Action'])->getParameters();
                    if(count($param)!= 1)
                    {
                        return $this->getMessageError('Action '.$this->container['Action'].' must contains a parameter.');
                    }
                    $type = '';
                    if($param[0]->getClass()!=null)
                    {
                        $type = $param[0]->getClass()->getName();
                    }
                    if($type != 'Psr\Http\Message\ServerRequestInterface')
                    {
                        return $this->getMessageError('Action '.$this->container['Action'].' must contains a parameter.(Psr\Http\Message\ServerRequestInterface)');
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
                        return $this->getMessageError('Action ' .$this->container['Action'].' must return something ...');
                    }
                    if(gettype($response)=='object') 
                    {
                        if(get_class($response) != 'Zend\Diactoros\Response\HtmlResponse')
                        {
                            return $this->getMessageError('Action ' .$this->container['Action'].' must return Zend\Diactoros\Response\HtmlResponse type');
                        }
                    }
                    else
                    {
                        return $this->getMessageError('Action ' .$this->container['Action'].' must return Zend\Diactoros\Response\HtmlResponse type');
                
                    }
                    return $response;
                    

                    
                  

                } catch (LogicException $ex) {
                    $output = $this->container['error']->handleException($ex);
                    return $this->getMessageError($output);
                   
                } catch (\ReflectionException $ex) {
                    return $this->getMessageError('Inexistant controller : '. $controller);
                }
               
            } catch (ResourceNotFoundException $exception) {
                // Call the next middleware
                return $next($request);
            } catch (\Exception $exception) {
                return new HtmlResponse('An error occurred : '.$exception->getMessage(), 505);
            }
         
         
    }
}