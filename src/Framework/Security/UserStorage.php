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
namespace Aspi\Framework\Security;
use Interop\Container\ContainerInterface;
use Nette\Security as NS;
class UserStorage implements NS\IUserStorage
{
        private $container = null;
        private $config = null;
        public function __construct(ContainerInterface $container)
        {
        
            $this->container = $container;
        }
        /**
        * Sets the authenticated status of this user.
        * @param  bool
        * @return static
        */
        function setAuthenticated($state)
        {
            if($state == false)
            {
                $this->container->get('session')->remove('autenticated');
                if($this->container->get('session')->has('identity'))
                {
                    $this->container->get('session')->remove('identity');
                }

            }
            else
            {
                $this->container->get('session')->set('autenticated',true);
            }
        }
 
        /**
        * Is this user authenticated?
        * @return bool
         */
        function isAuthenticated()
        {
            
           
            return  $this->container->get('session')->has('autenticated');
          
       
        }
        /**
        * Sets the user identity.
        * @return static
        */
        function setIdentity(NS\IIdentity $identity = null)
       {
           if(isset($identity))
           {
                $data = serialize($identity);
                $this->container->get('session')->set('identity',$data);
           
            
          
           }
  
         
       }
        /**
        * Returns current user identity, if any.
        * @return IIdentity|null
        */
        function getIdentity()
        {
        
            if( $this->container->get('session')->has('identity'))
            {
           
                $obj = unserialize( $this->container->get('session')->get('identity'));
                return $obj;
            }
            else
            {
                return null;
            }
   
        }
        /**
        * Enables log out from the persistent storage after inactivity.
        * @param  string|int|\DateTimeInterface number of seconds or timestamp
        * @param  int  flag IUserStorage::CLEAR_IDENTITY
        * @return static
         */
        function setExpiration($time, $flags = 0)
        {
        }
        /**
        * Why was user logged out?
        * @return int|null
        */
        function getLogoutReason()
        {
            return null;
        }
}