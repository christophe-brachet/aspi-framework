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
namespace Aspi\Framework\Protocole\Http;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Stream;
use Aspi\Framework\Application\Kernel;
use Pimple\Container;

class WebServer extends \Swoole\Http\Server
{
    const CHUNK_SIZE = 1048576;//1M
    private $container = null;
    public function __construct(Container $container)
    {
        $this->container = $container;
        $port = $this->container['HttpConfig']->get('port'); 
        $host = $this->container['HttpConfig']->get('host'); 
        parent::__construct($host,((int)$port),SWOOLE_PROCESS, SWOOLE_SOCK_TCP);
        $this->set(
        [
                'open_http2_protocol' => 1,
                'enable_static_handler' => true,
        ]);
        $this->on('request', [$this, 'onRequest']);
    }
    public function onRequest(\swoole_http_request $swooleRequest, \swoole_http_response $swooleResponse)
    {
      
              //https://github.com/mcfog/lit-swan/blob/master/src/SwanServer.php
            //https://github.com/swoole/swoole-src/issues/559
            //$this->initSession($swooleRequest,$swooleResponse);
            $psrReq = $this->makePsrRequest($swooleRequest);
            $app = new Kernel($this->container);
            $psrRequest = $app->run($psrReq);
            $this->emitResponse($swooleResponse, $psrRequest);
    }  
    private function initSession(\swoole_http_request $swooleRequest, \swoole_http_response $swooleResponse)
    {
        if (session_status() != PHP_SESSION_ACTIVE) {
            session_start();
        }
    
        if (isset($swooleRequest->cookie[session_name()])) {
            // Client has session cookie set, but Swoole might have session_id() from some
            // other request, so we need to regenerate it
            session_id($swooleRequest->cookie[session_name()]);
        } else {
            $params = session_get_cookie_params();
    
            if (session_id()) {
                session_id(\bin2hex(\random_bytes(32)));
            }
            $_SESSION = array();
    
            $swooleResponse->rawcookie(
                session_name(),
                session_id(),
                $params['lifetime'] ? time() + $params['lifetime'] : null,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
    } 
    private function emitResponse(\swoole_http_response $res, ResponseInterface $psrRequest)
    {
        $res->status($psrRequest->getStatusCode());
        foreach ($psrRequest->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                $res->header($name, $value);
            }
        }
        $body = $psrRequest->getBody();
        $body->rewind();
        if ($body->getSize() > static::CHUNK_SIZE) {
            while (!$body->eof()) {
                $res->write($body->read(static::CHUNK_SIZE));
            }
            $res->end();
        } else {
            $res->end($body->getContents());
        }
    }
     /**
      * 
     * @param \swoole_http_request $req
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    private function makePsrRequest(\swoole_http_request $req): \Psr\Http\Message\ServerRequestInterface
    {
        
        //http://paul-m-jones.com/archives/6416
        $server = [];
        foreach ($req->server as $key => $value) {
            $server[strtoupper($key)] = $value;
        }
        $server = ServerRequestFactory::normalizeServer($server);
        $files = isset($req->files)
            ? ServerRequestFactory::normalizeFiles($req->files)
            : [];
        $cookies = isset($req->cookie) ? $req->cookie : [];
        $query = isset($req->get) ? $req->get : [];
        $body = isset($req->post) ? $req->post : [];
        $stream = new Stream('php://memory', 'wb+');
        $stream->write($req->rawContent());
        $stream->rewind();
        foreach ($req->header as $key => $value) {
            $headers[strtoupper($key)] = $value;
        }
        $request = new ServerRequest(
            $server,
            $files,
            ServerRequestFactory::marshalUriFromServer($server, $headers),
            ServerRequestFactory::get('REQUEST_METHOD', $server, 'GET'),
            $stream,
            $headers
        );
        return $request
            ->withCookieParams($cookies)
            ->withQueryParams($query)
            ->withParsedBody($body);
    }
}