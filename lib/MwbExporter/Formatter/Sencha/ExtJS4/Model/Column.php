<?php

/*
 * The MIT License
 *
 * Copyright (c) 2012 Allan Sun <sunajia@gmail.com>
 * Copyright (c) 2012-2013 Toha <tohenk@yahoo.com>
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

namespace MwbExporter\Formatter\Sencha\ExtJS4\Model;

use MwbExporter\Model\Column as BaseColumn;
use MwbExporter\Writer\WriterInterface;

class Column extends BaseColumn
{
    /**
     * Write model field.
     * 
     * @param \MwbExporter\Writer\WriterInterface $writer
     * @param boolean $hasMore
     * @return \MwbExporter\Formatter\Sencha\ExtJS4\Model\Column
     */
    public function write(WriterInterface $writer, $hasMore = false)
    {
        $table = $this->getTable();
        $type = $this->getDocument()->getFormatter()->getDatatypeConverter()->getType($this);
        $defaultValue = $this->getDefaultValue();
        $content = array(
            'name' => $this->getColumnName(),
            'type' => ($type)
                ? $type
                : 'string'
        );

        if ($defaultValue) {
            $content['defaultValue'] = trim($defaultValue, "'");
        }

        $field = (string) $table->getJSObject($content);

        if ($hasMore) {
            $field .= ',';
        }

        $writer->write($field);

        // End.
        return $this;
    }

    /**
     * Write model validation(s).
     * 
     * @param \MwbExporter\Writer\WriterInterface $writer
     * @param boolean $hasMore
     * @return \MwbExporter\Formatter\Sencha\ExtJS4\Model\Column
     */
    public function writeValidation(WriterInterface $writer, $hasMore = false)
    {
        $table = $this->getTable();
        $isRequired = $this->getIsrequired();
        $isPrimary = $this->isPrimary();
        $maxLength = $this->getMaxLength();

        if ($isRequired && !$isPrimary) {
            $content = array(
                'type' => 'presence',
                'field' => $this->getColumnName()
            );

            $writer->write(
                $table->getJSObject($content) . (($maxLength || $hasMore)
                    ? ','
                    : '')
            );
        }

        if ($maxLength) {
            $content = array(
                'type' => 'length',
                'field' => $this->getColumnName(),
                'max' => $maxLength
            );

            $writer->write(
                $table->getJSObject($content) . (($hasMore)
                    ? ','
                    : '')
            );
        }


        // End.
        return $this;
    }

    /**
     * Get the column foreign key.
     * 
     * @return \MwbExporter\Model\ForeignKey
     */
    public function getForeignKeys()
    {
        // End.
        return $this->foreigns;
    }

    /**
     * Get column default value or false if there is no default value or the
     * default value is NULL.
     * 
     * @return boolean
     */
    private function getDefaultValue()
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
     * Return whatever this column require a values.
     * 
     * @return boolean
     */
    private function getIsrequired()
    {
        $isNotNull = $this->parameters->get('isNotNull');

        // End.
        return (1 != $isNotNull)
            ? false
            : true;
    }

    /**
     * Get column max length pr false if there is no length.
     * 
     * @return boolean
     */
    private function getMaxLength()
    {
        $length = $this->parameters->get('length');
        return ($length > 0)
            ? $length
            : false;
    }
}