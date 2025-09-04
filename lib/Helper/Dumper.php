<?php

/*
 * The MIT License
 *
 * Copyright (c) 2023-2025 Toha <tohenk@yahoo.com>
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

abstract class Dumper
{
    protected $lines = [];

    /**
     * Get dumper instance.
     *
     * @param string $format
     * @return \MwbExporter\Helper\Dumper
     */
    public static function get($format)
    {
        switch ($format) {
            case 'md':
                return new DumperMd();
            default:
                return new DumperPlain();
        }
    }

    /**
     * Add title.
     *
     * @param string $title
     * @return \MwbExporter\Helper\Dumper
     */
    abstract public function addTitle($title);

    /**
     * Add line.
     *
     * @param string $line
     * @return \MwbExporter\Helper\Dumper
     */
    abstract public function addLine($line);

    /**
     * Add sub line.
     *
     * @param string $line
     * @param bool $raw
     * @return \MwbExporter\Helper\Dumper
     */
    abstract public function addSubLine($line, $raw = false);

    /**
     * Add blank.
     *
     * @return \MwbExporter\Helper\Dumper
     */
    abstract public function addBlank();

    /**
     * Highlight text.
     *
     * @param string $text
     * @return string
     */
    abstract public function highlight($text);

    /**
     * Highlight text array.
     *
     * @param array $array
     * @return array
     */
    abstract public function highlightValues($array);

    /**
     * Get lines.
     *
     * @return array
     */
    public function getLines()
    {
        return $this->lines;
    }
}
