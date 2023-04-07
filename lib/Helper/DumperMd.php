<?php

/*
 * The MIT License
 *
 * Copyright (c) 2023 Toha <tohenk@yahoo.com>
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

class DumperMd extends Dumper
{
    public const LINE_WRAPPER_LINE = '  * %s';
    public const LINE_WRAPPER_SUBLINE = '    %s';
    public const LINE_WIDTH = 100;

    /**
     * Add title.
     *
     * @param string $title
     * @return \MwbExporter\Helper\DumperMd
     */
    public function addTitle($title)
    {
        $this->lines[] = '## '.$title;

        return $this;
    }

    /**
     * Add line.
     *
     * @param string $line
     * @return \MwbExporter\Helper\DumperMd
     */
    public function addLine($line)
    {
        array_push($this->lines, ...LineWrap::wrap($line, static::LINE_WRAPPER_LINE, static::LINE_WIDTH));
        $this->addBlank();

        return $this;
    }

    /**
     * Add sub line.
     *
     * @param string $line
     * @param bool $raw
     * @return \MwbExporter\Helper\DumperMd
     */
    public function addSubLine($line, $raw = false)
    {
        array_push($this->lines, ...LineWrap::wrap($line, static::LINE_WRAPPER_SUBLINE, static::LINE_WIDTH));

        return $this;
    }

    /**
     * Add blank.
     *
     * @return \MwbExporter\Helper\DumperMd
     */
    public function addBlank()
    {
        $this->lines[] = '';

        return $this;
    }

    /**
     * Highlight text.
     *
     * @param string $text
     * @return string
     */
    public function highlight($text)
    {
        return sprintf('`%s`', $text);
    }

    /**
     * Highlight text array.
     *
     * @param array $array
     * @return array
     */
    public function highlightValues($array)
    {
        return array_map([$this, 'highlight'], $array);
    }
}
