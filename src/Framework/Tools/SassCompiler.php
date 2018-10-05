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
namespace Aspi\Framework\Tools;
use Leafo\ScssPhp\Compiler;
use Pimple\Container;

class SassCompiler
{

    private $container = null;
    public function __construct(Container $container)
    {
      
        $this->container = $container;
    }
    /**
     * Compiles all .scss files in a given folder into .css files in a given folder
     *
     * @param string $scss_folder source folder where you have your .scss files
     * @param string $css_folder destination folder where you want your .css files
     * @param string $format_style CSS output format, see http://leafo.net/scssphp/docs/#output_formatting for more.
     */
    public function run($scss_folder, $format_style = "scss_formatter")
    {

        // scssc will be loaded automatically via Composer
        $scss_compiler = new Compiler();
        $bootstrap_folder = __DIR__.'/../../../../../../node_modules/bootstrap/scss';
        $flag_folder = __DIR__.'/../../../../../../node_modules/flag-icon-css/sass'; 
        // set the path where your _mixins are
        $scss_compiler->setImportPaths(array($flag_folder,$bootstrap_folder,$scss_folder));
        // set css formatting (normal, nested or minimized), @see http://leafo.net/scssphp/docs/#output_formatting
         $scss_compiler->setFormatter('Leafo\ScssPhp\Formatter\Compressed');
        // get all .scss files from scss folder
        $filelist = glob($scss_folder . "/*.scss");
        // step through all .scss files in that folder
        foreach ($filelist as $file_path) {
            // get path elements from that file
            $file_path_elements = pathinfo($file_path);
            // get file's name without extension
            $file_name = $file_path_elements['filename'];
            // get .scss's content, put it into $string_sass
            $string_sass = file_get_contents($scss_folder .'/'. $file_name . ".scss");
            // compile this SASS code to CSS
            $string_css = $scss_compiler->compile($string_sass);
            $this->container['FileManager']->add('text/css', '/css/'. $file_name . ".css", $string_css);
        }
    }
}