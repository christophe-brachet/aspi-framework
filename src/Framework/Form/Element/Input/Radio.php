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

class Radio extends Element\Input
{
    /**
     * Constructor
     *
     * Instantiate the text input form element
     *
     * @param  string $name
     * @param  string $value
     * @param  string $indent
     */
    public function __construct($name, $value = null, $indent = null)
    {
        parent::__construct($name, 'radio', $value, $indent);
    }
    /**
     * Set the value of the form input element object
     *
     * @param  mixed $value
     * @return Radio
     */
    public function setValue($value)
    {
        if ($value == $this->getAttribute('value')) {
            $this->check();
        } else {
            $this->uncheck();
        }
        return $this;
    }
    /**
     * Reset the value of the form element
     *
     * @return Radio
     */
    public function resetValue()
    {
        $this->uncheck();
        return $this;
    }
    /**
     * Set the checkbox to checked
     *
     * @return Radio
     */
    public function check()
    {
        $this->setAttribute('checked', 'checked');
        return $this;
    }
    /**
     * Set the checkbox to checked
     *
     * @return Radio
     */
    public function uncheck()
    {
        $this->removeAttribute('checked');
        return $this;
    }
    /**
     * Determine if the radio value is checked
     *
     * @return boolean
     */
    public function isChecked()
    {
        return ($this->getAttribute('checked') == 'checked');
    }
}