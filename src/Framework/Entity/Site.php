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
namespace Aspi\Framework\Entity;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;

/**
 * @Entity(repositoryClass="Aspi\Framework\Repository\SiteRepository")
 * @Table(name="sites")
 */
class Site
{
	
	// Site status constants.
	const STATUS_DRAFT       = 1; // Draft.
	const STATUS_PUBLISHED   = 2; // Published.
    /**
     * @var integer
     *
     * @Id
     * @Column(name="id", type="integer")
     * @GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
    * @var string
    *
    * @Column(name="domain_name", type="string", length=255,unique=true, nullable=false)
    */
    protected $domainName;

    
    /** 
    * @Column(name="status", type="integer")  
    */
    protected $status;

    /** 
    * @Column(name="routing", type="text")  
    */
    protected $routing;

    // Returns ID of this site.
    public function getId(): integer
    {
        return $this->id;
    }

    // Returns domainname of this site.
    public function getDomainName(): string
    {
        return $this->domainName;
    }
  
    // Sets domainname of this site.
    public function setDomainName($domainName) 
    {
        $this->domainName = $domainName;
    }
    
    // Returns status of this site.
    public function getStatus(): integer
    {
        return $this->status;
    }
  
    // Sets status of this site.
    public function setStatus($status) 
    {
        $this->status = $status;
    }

    // Returns routing of this site.
    public function getRouting(): string
    {
        return $this->routing;
    }
    
      // Sets routing of this site.
      public function setRouting($routing) 
      {
        $this->routing = $routing;
      }

    
      
    
}