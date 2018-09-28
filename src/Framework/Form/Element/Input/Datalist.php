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

use Pop\Dom\Child;

class Datalist extends Text
{
    /**
     * Datalist object.
     * @var Child
     */
    protected $datalist = null;
    /**
     * Constructor
     *
     * Instantiate the datalist text input form element
     *
     * @param  string $name
     * @param  array  $values
     * @param  string $value
     * @param  string $indent
     */
    public function __construct($name, array $values, $value = null, $indent = null)
    {
        parent::__construct($name, $value);
        if (null !== $indent) {
            $this->setIndent($indent);
        }
        $this->setAttribute('list', $name . '_datalist');
        if (null !== $values) {
            $this->datalist = new Child('datalist');
            if (null !== $indent) {
                $this->datalist->setIndent($indent);
            }
            $this->datalist->setAttribute('id', $name . '_datalist');
            foreach ($values as $key => $val) {
                $this->datalist->addChild((new Child('option', $val))->setAttribute('value', $key));
            }
        }
    }
    /**
     * Render the datalist element
     *
     * @param  int     $depth
     * @param  string  $indent
     * @param  boolean $inner
     * @return mixed
     */
    public function render($depth = 0, $indent = null, $inner = false)
    {
        return parent::render($depth, $indent, $inner) . $this->datalist->render($depth, $indent, $inner);
    }
    /**
     * Print the datalist element
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }
}