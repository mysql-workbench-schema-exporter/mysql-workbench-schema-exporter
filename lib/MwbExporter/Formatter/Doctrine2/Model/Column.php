<?php

/*
 * The MIT License
 *
 * Copyright (c) 2012-2014 Toha <tohenk@yahoo.com>
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

namespace MwbExporter\Formatter\Doctrine2\Model;

use MwbExporter\Model\Column as BaseColumn;
use MwbExporter\Formatter\Doctrine2\Formatter;

class Column extends BaseColumn
{
    const RELATION_ONE_TO_ONE = '1:1';
    const RELATION_ONE_TO_MANY = '1:M';
    const RELATION_MANY_TO_ONE = 'M:1';
    const RELATION_MANY_TO_MANY = 'M:M';

    /**
     * Current Doctrine default value for nullable column attribute.
     */
    const NULLABLE_DEFAULT = false;

    /**
     * Is nullable attribute always required.
     *
     * @return boolean
     */
    protected function isAlwaysNullable()
    {
        return $this->getConfig()->get(Formatter::CFG_NULLABLE_ATTRIBUTE) === Formatter::NULLABLE_ALWAYS;
    }

    /**
     * Is nullable attribute required.
     *
     * @return boolean
     */
    public function isNullableRequired($defaultValue = self::NULLABLE_DEFAULT)
    {
        $isNullable = !$this->isNotNull();

        return $isNullable === $defaultValue ? ($this->isAlwaysNullable() ? true : false) : true;
    }

    /**
     * Get nullable attribute value.
     *
     * @return boolean
     */
    public function getNullableValue($defaultValue = self::NULLABLE_DEFAULT)
    {
        $isNullable = !$this->isNotNull();

        return $isNullable === $defaultValue ? ($this->isAlwaysNullable() ? $isNullable : null) : $isNullable;
    }

    /**
     * Check if column is ignored.
     *
     * @return boolean
     */
    public function isIgnored()
    {
        // don't ignore primary key
        if ($this->isPrimary) {
            return false;
        }
        // don't ignore when configuration is not set
        if (!$this->getConfig()->get(Formatter::CFG_SKIP_COLUMN_WITH_RELATION)) {
            return false;
        }
        // don't ignore when column has no relation
        if (0 === (count($this->getLocalForeignKeys()) + count($this->getForeignKeys()))) {
            return false;
        }

        return true;
    }
}
