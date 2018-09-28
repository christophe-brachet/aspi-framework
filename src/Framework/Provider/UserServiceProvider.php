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

namespace Aspi\Framework\Provider;
use \Pimple\ServiceProviderInterface;
use \Pimple\Container;
/**
 * A ServiceProvider for registering services related to
 * \Core\Application\Security\UserStorage,\Core\Application\Security\Authenticator,\Core\Application\Security\Authorizator,\Nette\Security\User in a DI container(pimple). 
 */
class UserServiceProvider  implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container['UserStorage']  = function (Container $container): \Core\Application\Security\UserStorage{
            return new \Aspi\Framework\Security\UserStorage($container);
        };
        $container['UserAuthenticator']  = function (Container $container): \Core\Application\Security\Authenticator{
            return new \Aspi\Framework\Security\Authenticator( $container);
        };
        $container['UserAuthorizator']  = function (): \Core\Application\Security\Authorizator{
            return new \Aspi\Framework\Security\Authorizator();
        };
        $container['user'] = function (Container $container):  \Nette\Security\User{
            $user = new  \Nette\Security\User($container['UserStorage'],null,$container['UserAuthorizator']);
            $user->setAuthenticator($container['UserAuthenticator']);
            return $user;

        };
    

    }

   
}