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

namespace MwbExporter\Helper;

class Comment
{
    const FORMAT_PHP        = '/**| * %s| */';
    const FORMAT_JS         = '/**| * %s| */';
    const FORMAT_XML        = '<!--|  %s|-->';
    const FORMAT_YAML       = '# %s';

    /**
     * Wrap comment.
     *
     * @param string $comment  Comment content
     * @param array $format  Comment wrapper format
     * @param int $width  The line width
     * @return array
     */
    public static function wrap($comment, $format, $width = 80)
    {
        $result = array();
        $width = $width - self::getWidth($format);
        // collect lines
        $lines = array();
        foreach (explode("\n", $comment) as $line) {
            foreach (explode("\n", wordwrap($line, $width, "\n")) as $sline) {
                $lines[] = $sline;
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
     * Get comment line.
     *
     * @param int $line  Current line number, start from 1
     * @param int $count  Total line number
     * @param array $format  Comment format
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
        $lines = array();
        self::getLine(2, 3, $format, '-', $lines);

        return strlen($lines[0]) - 1;
    }
}