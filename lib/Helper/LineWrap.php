<?php

/*
 * The MIT License
 *
 * Copyright (c) 2023-2024 Toha <tohenk@yahoo.com>
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

class LineWrap
{
    /**
     * Wrap lines.
     *
     * @param string $content Content to wrap
     * @param array $format Wrapping format
     * @param int $width The maximum line width
     * @return array
     */
    public static function wrap($content, $format, $width)
    {
        $result = [];
        // collect lines
        if (null === $width) {
            $lines = explode("\n", $content);
        } else {
            $width = $width - self::getWidth($format);
            $lines = [];
            foreach (explode("\n", $content) as $line) {
                foreach (explode("\n", wordwrap($line, $width, "\n")) as $sline) {
                    $lines[] = $sline;
                }
            }
        }
        // write lines
        $no = 0;
        $count = count($lines);
        foreach ($lines as $line) {
            $no++;
            self::getLine($no, $count, $format, $line, $result);
        }

        return $result;
    }

    /**
     * Get wrapped line.
     *
     * @param int $line  Current line number, start from 1
     * @param int $count  Total line number
     * @param string $format  Comment format
     * @param string $content  Line content
     * @param array $result Result array
     */
    protected static function getLine($line, $count, $format, $content, &$result)
    {
        // make sure format is an array
        $format = explode('|', $format);
        // is first line
        if (count($format) >= 3 && 1 === $line) {
            $result[] = $format[0];
        }
        // format line
        $result[] = rtrim(sprintf(count($format) >= 3 ? $format[1] : $format[0], $content));
        // is last line
        if (count($format) >= 3 && $count === $line) {
            $result[] = $format[2];
        }
    }

    /**
     * Get the width of line format.
     *
     * @param array $format
     * @return int
     */
    protected static function getWidth($format)
    {
        $lines = [];
        self::getLine(2, 3, $format, '-', $lines);

        return strlen($lines[0]) - 1;
    }
}
