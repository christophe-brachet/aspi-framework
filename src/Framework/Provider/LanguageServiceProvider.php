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
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\JsonFileLoader;

class LanguageServiceProvider implements \Pimple\ServiceProviderInterface
{
    public function register(\Pimple\Container $container)
    {
        $container['Language'] = function ($container):\Aspi\Framework\i18n\Language {
            return new  \Aspi\Framework\i18n\Language();
        };
        $container['Translator'] = function ($container) {
            $translator = new Translator( $container['Language']->get());
            $translator->addLoader('json', new JsonFileLoader());
            $translator->setFallbackLocales(['fr']);
            if(!$container['isCMS'])
            {
                $translator->addResource('json',  __DIR__.'/../../../application/Config/Translation/en.json', 'en');
                $translator->addResource('json',  __DIR__.'/../../../application/Config/Translation/fr.json', 'fr');
            }
            else
            {
                $translator->addResource('json',  __DIR__.'/../../../../../../src/CMS/Themes/'.$container['theme'].'/translation/en.json', 'en');
                $translator->addResource('json',  __DIR__.'/../../../../../../src/CMS/Themes/'.$container['theme'].'/translation/fr.json', 'fr');
            }
            return $translator;
        };
    
    
    }
    
}
    