<?php

/*
 * The MIT License
 *
 * Copyright (c) 2010 Johannes Mueller <circus2(at)web.de>
 * Copyright (c) 2012-2013 Toha <tohenk@yahoo.com>
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

namespace MwbExporter\Model;

use MwbExporter\Writer\WriterInterface;

class Columns extends Base implements \ArrayAccess, \IteratorAggregate, \Countable
{
    /**
     * @var array
     */
    protected $columns = array();

    protected function init()
    {
        foreach ($this->node as $key => $node) {
            $this->columns[] = $this->getDocument()->getFormatter()->createColumn($this, $node);
        }
    }

    /**
     * Get columns column.
     *
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    public function getManyToManyCount($tablename)
    {
        $count = 0;
        foreach ($this->columns as $column) {
            foreach ($column->getLocalForeignKeys() as $foreign) {
                if ($foreign->getReferencedTable()->getRawTableName() === $tablename) {
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Check if this table has an one to many relation.
     *
     * @return bool
     */
    public function hasOneToManyRelation()
    {
        foreach ($this->columns as $column) {
            if ($column->hasOneToManyRelation()) {
                return true;
            }
        }

        return false;
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->columns);
    }

    public function offsetGet($offset)
    {
        return $this->columns[$offset];
    }

    public function offsetSet($offset, $value)
    {
        if (null === $offset) {
            $this->columns[] = $value;
        } else {
            $this->columns[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->columns[$offset]);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->columns);
    }

    public function count()
    {
        return count($this->columns);
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Model\Base::write()
     */
    public function write(WriterInterface $writer)
    {
        foreach ($this->columns as $column) {
            $column->write($writer);
        }

        return $this;
    }
}