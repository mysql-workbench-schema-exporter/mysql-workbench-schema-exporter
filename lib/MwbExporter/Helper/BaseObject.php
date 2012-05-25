<?php

/*
 * The MIT License
 *
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

namespace MwbExporter\Helper;

class BaseObject
{
    /**
     * @var mixed
     */
    protected $content = null;

    /**
     * @var bool
     */
    protected $raw = null;

    /**
     * Constructor.
     * 
     * @param mixed $content
     * @param bool $raw
     */
    public function __construct($content = null, $raw = false)
    {
        $this->content = $content;
        $this->raw = $raw;
    }

    /**
     * Check if array keys is all numeric.
     *
     * @param array $array  The input array
     * @return bool
     */
    public function isKeysNumeric($array)
    {
        foreach ($array as $key => $value) {
            if (!is_int($key)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Decorate generated code.
     *
     * @param string $code  The generated code
     * @return string
     */
    protected function decorateCode($code)
    {
        return $code; 
    }

    /**
     * Convert value as code equivalent.
     *
     * @param mixed $value  The value
     * @return string
     */
    public function asCode($value)
    {
        return $value;
    }

    public function __toString()
    {
        return $content = $this->raw ? $this->content : $this->decorateCode($this->asCode($this->content));
    }
}