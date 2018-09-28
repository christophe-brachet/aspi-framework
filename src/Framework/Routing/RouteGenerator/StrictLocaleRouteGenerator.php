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

use Aspi\Framework\Routing\Exception\MissingLocaleException;
use Aspi\Framework\Routing\Exception\UnknownLocaleException;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
/**
 * A class to enforce a supported set of locales.
 */
class StrictLocaleRouteGenerator implements RouteGeneratorInterface
{
    private $routeGenerator;
    private $locales;
    private $allowFallback = false;
    public function __construct(RouteGeneratorInterface $internalRouteGenerator, array $supportedLocales)
    {
        if (empty($supportedLocales)) {
            throw new \InvalidArgumentException('The supportedLocales must contain at least one locale.');
        }
        $this->routeGenerator = $internalRouteGenerator;
        $this->locales = $supportedLocales;
    }
    public function allowFallback($enabled = true)
    {
        $this->allowFallback = $enabled;
    }
    /**
     * Generate localized versions of the given route.
     *
     * @param $name
     * @param array $localesWithPaths
     * @param Route $baseRoute
     * @return RouteCollection
     */
    public function generateRoutes($name, array $localesWithPaths, Route $baseRoute)
    {
        $this->assertLocalesAreSupported(array_keys($localesWithPaths));
        return $this->routeGenerator->generateRoutes($name, $localesWithPaths, $baseRoute);
    }
    /**
     * Generate a localized version of the given route collection.
     *
     * @param array|string $localesWithPrefix
     * @param RouteCollection $baseCollection
     * @return RouteCollection
     */
    public function generateCollection($localesWithPrefix, RouteCollection $baseCollection)
    {
        if (is_array($localesWithPrefix)) {
            $this->assertLocalesAreSupported(array_keys($localesWithPrefix));
        }
        return $this->routeGenerator->generateCollection($localesWithPrefix, $baseCollection);
    }
    private function assertLocalesAreSupported(array $locales)
    {
        if (!$this->allowFallback) {
            $missingLocales = array_diff($this->locales, $locales);
            if (!empty($missingLocales)) {
                throw MissingLocaleException::shouldSupportLocale($missingLocales);
            }
        }
        $unknownLocales = array_diff($locales, $this->locales);
        if (!empty($unknownLocales)) {
            throw UnknownLocaleException::unexpectedLocale($unknownLocales, $this->locales);
        }
    }
}