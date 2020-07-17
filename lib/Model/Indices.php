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

class Indices extends Base implements \ArrayAccess, \IteratorAggregate, \Countable
{
    /**
     * @var array
     */
    protected $childs = array();

    protected function init()
    {
        foreach ($this->node as $key => $node) {
            $this->childs[] = $this->getFormatter()->createIndex($this, $node);
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
     * (non-PHPdoc)
     * @see \MwbExporter\Model\Base::write()
     */
    public function write(WriterInterface $writer)
    {
        foreach ($this->childs as $indice) {
            $indice->write($writer);
        }

        return $this;
    }
}