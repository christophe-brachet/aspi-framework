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
namespace Aspi\Framework\Routing\RouteGenerator;

class FilteredLocaleGenerator implements RouteGeneratorInterface
{
    private $routeGenerator;
    private $locales;
    public function __construct(RouteGeneratorInterface $internalRouteGenerator, array $allowedLocales)
    {
        if (empty($allowedLocales)) {
            throw new \InvalidArgumentException('The allowedLocales must contain at least one locale.');
        }
        $this->routeGenerator = $internalRouteGenerator;
        $this->locales = array_flip(array_values($allowedLocales));
    }
    /**
     * @inheritdoc
     */
    public function generateRoutes($name, array $localesWithPaths, Route $baseRoute)
    {
        return $this->routeGenerator->generateRoutes(
            $name,
            array_intersect_key($localesWithPaths, $this->locales),
            $baseRoute
        );
    }
    /**
     * @inheritdoc
     */
    public function generateCollection($localesWithPrefix, RouteCollection $baseCollection)
    {
        if (is_array($localesWithPrefix)) {
            $localesWithPrefix = array_intersect_key($localesWithPrefix, $this->locales);
        }
        return $this->routeGenerator->generateCollection($localesWithPrefix, $baseCollection);
    }
}