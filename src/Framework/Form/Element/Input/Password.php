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

class Password extends Element\Input
{
    /**
     * Flag to allow rendering the value
     * @var boolean
     */
    protected $renderValue = false;
    /**
     * Constructor
     *
     * Instantiate the password input form element
     *
     * @param  string $name
     * @param  string $value
     * @param  string $indent
     * @param  boolean $renderValue
     */
    public function __construct($name, $value = null, $indent = null, $renderValue = false)
    {
        parent::__construct($name, 'password', $value, $indent);
    }
    /**
     * Set the render value flag
     *
     * @param  boolean $renderValue
     * @return Password
     */
    public function setRenderValue($renderValue)
    {
        $this->renderValue = (bool)$renderValue;
        return $this;
    }
    /**
     * Get the render value flag
     *
     * @return boolean
     */
    public function getRenderValue()
    {
        return $this->renderValue;
    }
    /**
     * Render the password element
     *
     * @param  int     $depth
     * @param  string  $indent
     * @param  boolean $inner
     * @return mixed
     */
    public function render($depth = 0, $indent = null, $inner = false)
    {
        if (!$this->renderValue) {
            $this->setAttribute('value', '');
        }
        return parent::render($depth, $indent, $inner);
    }
}