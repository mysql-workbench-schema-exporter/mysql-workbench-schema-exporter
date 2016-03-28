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

namespace MwbExporter\Object;

class Base
{
    /**
     * @var mixed
     */
    protected $content = null;

    /**
     * @var array
     */
    protected $options = array();

    /**
     * Constructor.
     *
     * @param mixed $content
     * @param array $options
     */
    public function __construct($content = null, $options = array())
    {
        $this->content = $content;
        $this->setOptions(array_merge(array('raw' => false, 'indentation' => '    '), (array) $options));
        $this->init();
    }

    /**
     * Initialization.
     */
    protected function init()
    {
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
     * Set options from array.
     *
     * @param array $options  The options array
     * @return \MwbExporter\Object\Base
     */
    public function setOptions($options)
    {
        foreach ($options as $key => $value) {
            $this->setOption($key, $value);
        }

        return $this;
    }

    /**
     * Get option value.
     *
     * @param string $key      Option name
     * @param mixed  $default  Default value
     * @return mixed
     */
    public function getOption($key, $default = null)
    {
        return isset($this->options[$key]) ? $this->options[$key] : $default;
    }

    /**
     * Set option value.
     *
     * @param string $key    Option name
     * @param mixed  $value  Option value
     * @return \MwbExporter\Object\Base
     */
    public function setOption($key, $value)
    {
        $this->options[$key] = $value;

        return $this;
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
     * Wrap text.
     *
     * @param string $lines      The text
     * @param int    $indentationLevel
     * @return string
     */
    protected function wrapLines($lines, $indentationLevel = 0)
    {
        if ($wrapper = $this->getOption('wrapper')) {
            $lines = explode("\n", $lines);
            for ($i = 0; $i < count($lines); $i++) {
                // first line ignored
                if ($i === 0) {
                    continue;
                }
                $line = $lines[$i];
                if ($indentationLevel && $i < count($lines) - 1) {
                    $line = str_repeat($this->getOption('indentation'), $indentationLevel) . $line;
                }
                $lines[$i] = sprintf($wrapper, $line);
            }
            $lines = implode("\n", $lines);
        }

        return $lines;
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
        return $content = $this->getOption('raw') ? $this->content : $this->decorateCode($this->asCode($this->content));
    }
}