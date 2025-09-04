<?php

/*
 * The MIT License
 *
 * Copyright (c) 2010 Johannes Mueller <circus2(at)web.de>
 * Copyright (c) 2012-2025 Toha <tohenk@yahoo.com>
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
use Traversable;

class Columns extends Base implements \ArrayAccess, \IteratorAggregate, \Countable
{
    /**
     * @var array
     */
    protected $childs = [];

    protected function init()
    {
        foreach ($this->node as $key => $node) {
            $this->childs[] = $this->getFormatter()->createColumn($this, $node);
        }
    }

    public function getManyToManyCount($tablename)
    {
        $count = 0;
        $fks = [];
        foreach ($this->childs as $column) {
            foreach ($column->getLocalForeignKeys() as $foreign) {
                if ($foreign->getOwningTable()->getRawTableName() === $tablename && !in_array($foreign->getId(), $fks)) {
                    $count++;
                    $fks[] = $foreign->getId();
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
        foreach ($this->childs as $column) {
            if ($column->hasOneToManyRelation()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get column names.
     *
     * @return array
     */
    public function getColumnNames()
    {
        $columns = [];
        foreach ($this->childs as $column) {
            $columns[] = $column->getColumnName();
        }

        return $columns;
    }

    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->childs);
    }

    public function offsetGet($offset): Column
    {
        return $this->childs[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        if (null === $offset) {
            $this->childs[] = $value;
        } else {
            $this->childs[$offset] = $value;
        }
    }

    public function offsetUnset($offset): void
    {
        unset($this->childs[$offset]);
    }

    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->childs);
    }

    public function count(): int
    {
        return count($this->childs);
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Model\Base::write()
     */
    public function write(WriterInterface $writer)
    {
        foreach ($this->childs as $column) {
            $column->write($writer);
        }

        return $this;
    }
}
