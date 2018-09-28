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
namespace Aspi\Framework\Form\Element;

class Select extends AbstractSelect
{
    /**
     * Constructor
     *
     * Instantiate the select form element object
     *
     * @param  string       $name
     * @param  string|array $values
     * @param  string       $selected
     * @param  string       $xmlFile
     * @param  string       $indent
     */
    public function __construct($name, $values, $selected = null, $xmlFile = null, $indent = null)
    {
        parent::__construct('select');
        $this->setName($name);
        $this->setAttributes([
            'name' => $name,
            'id'   => $name
        ]);
        if (null !== $selected) {
            $this->setValue($selected);
        }
        if (null !== $indent) {
            $this->setIndent($indent);
        }
        $values = self::parseValues($values, $xmlFile);
        // Create the child option elements.
        foreach ($values as $k => $v) {
            if (is_array($v)) {
                $optGroup = new Select\Optgroup();
                if (null !== $indent) {
                    $optGroup->setIndent($indent);
                }
                $optGroup->setAttribute('label', $k);
                foreach ($v as $ky => $vl) {
                    $option = new Select\Option($ky, $vl);
                    if (null !== $indent) {
                        $option->setIndent($indent);
                    }
                    // Determine if the current option element is selected.
                    if ((null !== $this->selected) && ($ky == $this->selected)) {
                        $option->select();
                    }
                    $optGroup->addChild($option);
                }
                $this->addChild($optGroup);
            } else {
                $option = new Select\Option($k, $v);
                if (null !== $indent) {
                    $option->setIndent($indent);
                }
                // Determine if the current option element is selected.
                if ((null !== $this->selected) && ($k == $this->selected)) {
                    $option->select();
                }
                $this->addChild($option);
            }
        }
    }
    /**
     * Set the selected value of the select form element
     *
     * @param  mixed $value
     * @return Select
     */
    public function setValue($value)
    {
        $this->selected = $value;
        if ($this->hasChildren()) {
            foreach ($this->childNodes as $child) {
                if ($child instanceof Select\Option) {
                    if ($child->getValue() == $this->selected) {
                        $child->select();
                    } else {
                        $child->deselect();
                    }
                } else if ($child instanceof Select\Optgroup) {
                    $options = $child->getOptions();
                    foreach ($options as $option) {
                        if ($option->getValue() == $this->selected) {
                            $option->select();
                        } else {
                            $option->deselect();
                        }
                    }
                }
            }
        }
        return $this;
    }
    /**
     * Reset the value of the form element
     *
     * @return Select
     */
    public function resetValue()
    {
        $this->selected = null;
        if ($this->hasChildren()) {
            foreach ($this->childNodes as $child) {
                if ($child instanceof Select\Option) {
                    $child->deselect();
                } else if ($child instanceof Select\Optgroup) {
                    $options = $child->getOptions();
                    foreach ($options as $option) {
                        $option->deselect();
                    }
                }
            }
        }
        return $this;
    }
}