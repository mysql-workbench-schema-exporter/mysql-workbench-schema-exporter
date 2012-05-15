<?php

/*
 * The MIT License
 *
 * Copyright (c) 2010 Johannes Mueller <circus2(at)web.de>
 * Copyright (c) 2012 Toha <tohenk@yahoo.com>
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

namespace MwbExporter;

use MwbExporter\Model\Column;

abstract class DatatypeConverter implements DatatypeConverterInterface
{
    protected $dataTypes = array();

    /**
     * Register data types mapping.
     */
    protected function register($dataTypes = array())
    {
        if (!count($this->dataTypes)) {
            $this->dataTypes = $dataTypes;
        }
    }

    /**
     * Get data type mapping for associated key.
     *
     * @return string|null
     */
    public function getDataType($key)
    {
        return isset($this->dataTypes[$key]) ? $this->dataTypes[$key] : null;
    }

    /**
     * Get column type as native type supported by formatter.
     *
     * @param string $type
     * @return string
     */
    public function getNativeType($type)
    {
        return $type;
    }

    /**
     * Get column type mapping.
     *
     * @param MwbExporter\Model\Column $column
     * @return string
     */
    public function getMappedType(Column $column)
    {
        return $this->getDataType($column->getColumnType());
    }

    /**
     * Get column type with its attribute.
     *
     * @param MwbExporter\Model\Column $column
     * @return string
     */
    public function getType(Column $column)
    {
        return $this->getMappedType($column);
    }
}