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

namespace MwbExporter\Registry;

class RegistryHolder implements \ArrayAccess, \IteratorAggregate, \Countable
{
    protected $nodes = array();

    /**
     * Store value in the registry data.
     *
     * @param string $key    The registry key
     * @param mixed  $value  The value to store
     * @return \MwbExporter\Registry\RegistryHolder
     */
    public function set($key, $value)
    {
        $this->nodes[$key] = $value;

        return $this;
    }

    /**
     * Get stored value from registry data.
     *
     * @param string $key      The registry key
     * @param mixed  $default  The default value if not set
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return isset($this->nodes[$key]) ? $this->nodes[$key] : $default;
    }

    /**
     * Check if data is stored in the registry data.
     *
     * @param string $key      The registry key
     * @return bool
     */
    public function has($key)
    {
        return isset($this->nodes[$key]) ? true : false;
    }

    /**
     * Check all stored registry data.
     *
     * @return array
     */
    public function getAll()
    {
        return $this->nodes;
    }

    /**
     * Check if data is stored in the registry data.
     *
     * @return \MwbExporter\Registry\RegistryHolder
     */
    public function clear()
    {
        $this->nodes = array();

        return $this;
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->nodes);
    }

    public function offsetGet($offset)
    {
        return $this->nodes[$offset];
    }

    public function offsetSet($offset, $value)
    {
        if (null === $offset) {
            $this->nodes[] = $value;
        } else {
            $this->nodes[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->nodes[$offset]);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->nodes);
    }

    public function count()
    {
        return count($this->nodes);
    }
}