<?php
/**
 * Original License
 *
 * @category   Pop
 * @package    Pop\Form
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2018 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.1.6
 * 
 * Modification for Aspi Framework by  Christophe Brachet Copyright (c) 2018
 */
namespace Aspi\Framework\Form\Element\Input;

use Aspi\Framework\Form\Element;

class Range extends Element\Input
{
    /**
     * Constructor
     *
     * Instantiate the range input form element
     *
     * @param  string $name
     * @param  int    $min
     * @param  int    $max
     * @param  string $value
     * @param  string $indent
     */
    public function __construct($name, $min, $max, $value = null, $indent = null)
    {
        parent::__construct($name, 'range', $value, $indent);
        $this->setAttributes([
            'min' => $min,
            'max' => $max
        ]);
    }
}