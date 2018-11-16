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
namespace Aspi\Framework\Protocole\Http\Middleware;

use Nette\Utils\Strings;
use Nette\Utils\FileSystem;
use Nette\Utils\Image;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Pimple\Container;
use Zend\Diactoros\Stream;

class AssetsMiddleware
{

    /**
    * @var Container
    */
    private $container = null;
    
    public function __construct(Container $container)
    {
        $this->container = $container;
    }
    /**
     * Example middleware invokable class
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param  callable                                 $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, callable $next):ResponseInterface{
    
        $isCms =false;
        if($this->container['HttpConfig']->get('cms')!=null)
        {
            $isCms = (bool)$this->container['HttpConfig']->get('cms');
        }

        $path = $request->getUri()->getPath();
        if(!$this->container['isCMS'])
        {
  
            $fileObject = $this->container['em']->getRepository('\Aspi\Framework\Entity\File')->getByPath($path);
        }
        else
        {
            $fileObject = $this->container['em']->getRepository('\Aspi\CMS\Framework\Entity\File')->getByPath($path);
        }
        if($fileObject)
        {
            $body = new Stream('php://temp', 'wb+');
            $body->write($fileObject['data']);
            $body->rewind();
            $header = array();
            $header['Content-Type'] = $fileObject['mime_type'];
            return new \Zend\Diactoros\Response($body,200,$header);
        }
        


        if(Strings::startsWith($path,'/vendor/'))
        {
            $fileName=__DIR__.'/../../../../..'.$path;
            return $this->loadFile($fileName);
        }
    
        return $next($request);
      
     
    }
    private function loadFile(string $fileName)
    {
       
        if(file_exists($fileName))
        {
         
         
           
            $type = \MimeType\MimeType::getType($fileName);
            $images = array(
                'image/gif',
                'image/jpeg',
                'image/png',
                'image/tiff'
            );
            if (in_array($type,$images)) {
                $content = Image::fromFile($fileName);
            }
            else
            {
                $content = FileSystem::read($fileName);
            }
            $body = new Stream('php://temp', 'wb+');
            $body->write($content);
            $body->rewind();
            $header = array();
            $header['Content-Type'] = $type;
            return new \Zend\Diactoros\Response($body,200,$header);
           
        }
        else
        {
            return $this->container['twig']->render(404,'404.twig.html',array());
        }
    }
}