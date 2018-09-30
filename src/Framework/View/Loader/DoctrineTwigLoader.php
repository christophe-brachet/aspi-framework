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
namespace  Aspi\Framework\View\Loader;

use \Pimple\Container;

class DoctrineTwigLoader implements \Twig_LoaderInterface
{
 
	 /**
     * @var \Pimple\Container
     */
    private $container;
	 
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function getSourceContext($name)
    {
        if (false === $source = $this->getValue($name)) {
            $source = sprintf('Template "%s" does not exist in database.', $name);
        }

        return new \Twig_Source($source, $name);
    }

    public function exists($name)
    {
        return $name === $this->getValue('name', $name);
    }

    public function getCacheKey($name)
    {
        return $name;
    }

    public function isFresh($name, $time)
    {
        if (false === $lastModified = $this->getValue('last_modified', $name)) {
            return false;
        }

        return $lastModified <= $time;
    }

    protected function getValue($name)
    {
        
        $dbh = $this->container['em']->getConnection();
     
            $sth = $dbh->prepare('SELECT source FROM templates INNER JOIN themes ON templates.theme_id = themes.id  WHERE templates.name = :name');
            $sth->execute(array(':name' => (string) $name));
            return $sth->fetchColumn();
          
        
    }
}