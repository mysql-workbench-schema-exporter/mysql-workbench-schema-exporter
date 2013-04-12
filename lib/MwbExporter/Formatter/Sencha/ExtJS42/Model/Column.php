<?php

/*
 * The MIT License
 *
 * Copyright (c) 2012 Allan Sun <sunajia@gmail.com>
 * Copyright (c) 2012 Toha <tohenk@yahoo.com>
 * Copyright (c) 2013 WitteStier <development@wittestier.nl>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace MwbExporter\Formatter\Sencha\ExtJS42\Model;

use MwbExporter\Model\Column as BaseColumn;

class Column
    extends BaseColumn
{

    /**
     * COMMENTME
     * 
     * @return type
     */
    public function getAsField()
    {
        $field = array(
            'name' => $this->getColumnName(),
            'type' => $this->getDocument()->getFormatter()->getDatatypeConverter()->getType($this)
        );

        $default = $this->getDefault();
        if ($default) {
            $field['defaultValue'] = trim($default, "'");
        }

        return $this->getTable()->getJSObject($field);
    }

    /**
     * COMMENTME
     * 
     * @return type
     */
    public function getAsValidation()
    {
        $validations = "";
        $isRequired = $this->getIsrequired();
        $maxLength = $this->getMaxLength();
        $table = $this->getTable();

        if ($isRequired && !$this->isPrimary()) {

            $validation = array(
                'type' => 'presence',
                'field' => $this->getColumnName()
            );

            $validations .= $table->getJSObject($validation);
        }

        if ($maxLength) {
            $validation = array(
                'type' => 'length',
                'field' => $this->getColumnName(),
                'max' => $maxLength
            );

            $validations .= ($isRequired && !$this->isPrimary())
                ? ",
" . $table->getJSObject($validation) // Very bad way to define a new line, i know.
                : $table->getJSObject($validation);
        }

        // End.
        return $validations;
    }

    /**
     * Get the column default value or false if there is no default value or the
     * default value is NULL.
     * 
     * @return boolean
     */
    public function getDefault()
    {
        $params = $this->parameters;

        if (!$params->get('defaultValue') || $params->get('defaultValueIsNull')) {
            // End.
            return false;
        }

        // End.
        return $params->get('defaultValue');
    }

    /**
     * Return whatever this column allows empty values.
     * 
     * @return boolean
     */
    public function getIsrequired()
    {
        $isNotNull = $this->parameters->get('isNotNull');

        // End.
        return (1 != $isNotNull)
            ? false
            : true;
    }

    /**
     * Get the column max length pr false if there is no length.
     * 
     * @return boolean
     */
    public function getMaxLength()
    {
        $length = $this->parameters->get('length');
        return ($length > 0)
            ? $length
            : false;
    }

}