<?php

/*
 * The MIT License
 *
 * Copyright (c) 2014 Toha <tohenk@yahoo.com>
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

namespace MwbExporter\Validator;

class ChoiceValidator extends Validator
{
    /**
     * @var array
     */
    protected $choices = array();

    /**
     * Constructor.
     *
     * @param array $choices  Default choices
     */
    public function __construct($choices = array())
    {
        $this->choices = $choices;
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Validator\Validator::isValid()
     */
    public function isValid(&$value)
    {
        // skip empty value
        if (is_string($value) && 0 === strlen($value)) {
            return false;
        }
        // found exact match
        if (in_array($value, $this->choices)) {
            return true;
        }
        // compare by ignoring case
        foreach ($this->choices as $choice) {
            if (!is_string($choice)) {
                continue;
            }
            if (strtoupper($value) == strtoupper($choice)) {
                $value = $choice;
                return true;
            }
        }
        // compare by match prefix
        foreach ($this->choices as $choice) {
            if (!is_string($choice)) {
                continue;
            }
            if (0 === strpos(strtoupper($choice), strtoupper($value))) {
                $value = $choice;
                return true;
            }
        }

        return false;
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Validator\Validator::getChoices()
     */
    public function getChoices()
    {
        return $this->choices;
    }
}