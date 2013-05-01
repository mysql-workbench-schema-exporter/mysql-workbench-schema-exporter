<?php

/*
 * The MIT License
 *
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
            if (!$this->isKeysNumeric($value)) {
                $spacer = str_repeat(' ', $level * $this->getOption('indent', 2));
                foreach ($value as $k => $v) {
                    // skip null value
                    if (null === $v) {
                        continue;
                    }
                    if ($this->isInline($v)) {
                        $tmp[] = $spacer.sprintf('%s: %s', $k, $this->asCode($v, $level + 1));
                    } else {
                        $tmp[] = $spacer.sprintf('%s:', $k);
                        $tmp[] = $this->asCode($v, $level + 1);
                    }
                }
                $value = implode("\n", $tmp);
            } else {
                foreach ($value as $k => $v) {
                    $tmp[] = $this->asCode($v, $level + 1);
                }
                $value = sprintf('[%s]', implode(', ', $tmp));
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
}