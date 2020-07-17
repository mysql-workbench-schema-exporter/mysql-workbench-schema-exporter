<?php

/*
 * The MIT License
 *
 * Copyright (c) 2010 Johannes Mueller <circus2(at)web.de>
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

namespace MwbExporter\Model;

use MwbExporter\Writer\WriterInterface;
use MwbExporter\Formatter\FormatterInterface;

class Tables extends Base implements \ArrayAccess, \IteratorAggregate, \Countable
{
    /**
     * @var array
     */
    protected $childs = array();

    public function init()
    {
        // collect childs
        foreach ($this->node->xpath("value") as $key => $node) {
            $table = $this->getFormatter()->createTable($this, $node);
            // skip translation childs
            if ($table->isTranslationTable()) {
                continue;
            }
            $this->childs[] = $table;
        }
        // apply sorting
        if ($this->getConfig()->get(FormatterInterface::CFG_SORT_TABLES_AND_VIEWS)) {
            usort($this->childs, function($a, $b) {
                return strcmp($a->getSortValue(), $b->getSortValue());
            });
        }
        /*
         * before you can check for foreign keys
         * you have to store at first all childs in the
         * object registry
         */
        foreach ($this->childs as $table) {
            $table->initIndices();
            $table->initForeignKeys();
        }
        // initialize many to many relation
        if ($this->getConfig()->get(FormatterInterface::CFG_ENHANCE_M2M_DETECTION)) {
            foreach ($this->childs as $table) {
                $table->initManyToManyRelations();
            }
        }
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->childs);
    }

    public function offsetGet($offset)
    {
        return $this->childs[$offset];
    }

    public function offsetSet($offset, $value)
    {
        if (null === $offset) {
            $this->childs[] = $value;
        } else {
            $this->childs[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->childs[$offset]);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->childs);
    }

    public function count()
    {
        return count($this->childs);
    }

    /**
     * Get the owner schema.
     *
     * @return \MwbExporter\Model\Schema
     */
    public function getSchema()
    {
        return $this->getParent();
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Model\Base::write()
     */
    public function write(WriterInterface $writer)
    {
        foreach ($this->childs as $table) {
            $table->write($writer);
        }

        return $this;
    }
}