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

class YAML extends Base
{
    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Object\Base::asCode()
     */
    public function asCode($value, $level = 0)
    {
        if ($value instanceof YAML) {
            $value = (string) $value;
        } elseif (null === $value) {
            $value = '~';
        } elseif (is_bool($value)) {
            $value = $value ? 'true' : 'false';
        } elseif (is_string($value)) {
            // nothing
        } elseif (is_array($value)) {
            $tmp = array();
            $spacer = str_repeat(' ', max(array($level * $this->getOption('indent', 1), 0)));
            if (!$this->isKeysNumeric($value)) {
                $inline = $this->getOption('inline', false);
                $inline_size = $this->getOption('inline_size', 0);
                foreach ($value as $k => $v) {
                    // skip null value
                    if (null === $v && $this->getOption('skip_null_value', false)) {
                        continue;
                    }
                    $x = $inline_size > 0 && $level >= 0 ? str_repeat(' ', $inline_size - strlen($k) - strlen($spacer)) : '';
                    if ($inline && $this->canBeInlined($v)) {
                        $v = explode("\n", $this->asCode($v, -1));
                        $tmp[] = $spacer.sprintf('%s: %s{ %s }', $k, $x, implode(", ",  $v));
                    } else {
                        if ($this->isInline($v)) {
                            if ($this->isArrayValueArray($v)) {
                                $tmp[] = $spacer.sprintf('%s:', $k);
                                $tmp[] = $this->asCode($v, $level + 1);
                            } else {
                                $tmp[] = $spacer.sprintf('%s: %s%s', $k, $x, $this->asCode($v, $level + 1));
                            }
                        } else {
                            $tmp[] = $spacer.sprintf('%s:', $k);
                            $tmp[] = $this->asCode($v, $level + 1);
                        }
                    }
                }
                $value = implode("\n", $tmp);
            } else {
                foreach ($value as $k => $v) {
                    if (is_array($v) && !$this->isKeysNumeric($v)) {
                        $v = explode("\n", $this->asCode($v, -1));
                        $tmp[] = sprintf('{ %s }', implode(", ",  $v));
                    } else {
                        $tmp[] = $this->asCode($v, $level + 1);
                    }
                }
                if ($this->isArrayValueArray($value)) {
                    $value = implode("\n", array_map(function($x) use ($spacer) {
                        return $spacer.sprintf('- %s', $x);
                    }, $tmp));
                } else {
                    $value = sprintf('[ %s ]', implode(', ', $tmp));
                }
            }
        }

        return $value;
    }

    /**
     * Check if value should be displayed as inline.
     *
     * @param mixed $value
     * @return boolean
     */
    protected function isInline($value)
    {
        return is_array($value) && !$this->isKeysNumeric($value) ? false : true;
    }

    /**
     * Check if value can be writen as inline array using curly brace.
     *
     * @param array $value
     * @return boolean
     */
    protected function canBeInlined($value)
    {
        if (!is_array($value) || $this->isKeysNumeric($value)) {
            return false;
        }
        foreach ($value as $v) {
            if (is_array($v)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if all array values is an array.
     *
     * @param array $array
     * @return boolean
     */
    protected function isArrayValueArray($array)
    {
        if (!is_array($array)) {
            return false;
        }
        foreach ($array as $k => $v) {
            if (!is_array($v)) {
                return false;
            }
        }

        return true;
    }
}