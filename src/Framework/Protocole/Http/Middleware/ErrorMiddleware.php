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
use Aspi\Framework\Protocole\Http\Middleware\MiddleWareInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Pimple\Container;


class  ErrorMiddleware implements MiddleWareInterface
{
    private $container = null;
    public function __construct(Container $container)
    {
        
        $this->container = $container;
    }
    /**
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, callable $next):ResponseInterface{
        try {
            return $next($request);
        } catch (\Exception $e) {
            $this->container['HttpConfig']->refresh();
            $mode =  $this->container['HttpConfig']->get('mode');
            if($mode == null)
            {
                $mode = 'development';
            }
            if($mode == 'production')
            {
                $content = '<h1>500 Server Error Oops, Something Went Wrong!</h1>Please contact the website administrator ';
                $email =  $this->container['HttpConfig']->get('administrator_email');
                if($email != null)
                {
                    $content .= ': <a href="mailto:'.$email.'">Administrator</a> ('.$email.')';
                }
                $content .= '.';
                return new HtmlResponse($content, 500);
            }
            else
            {
                $output = $this->container['error']->handleException($e);
                return new HtmlResponse($output, 500);
            }
        }
    }

}