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

class ZendURLFormatter
{
    /**
     * Format a CamelCase word into Camel-Case format.
     *
     * @param string $word
     */
    public static function fromCamelCaseToDashConnection($string)
    {
        $return = preg_replace_callback('/([A-Z])/', function($matches){
            return '-' . ucwords(strtolower($matches[1]));
        }, $string);

        if (substr($return, 0,1) === '-') {
            $return = substr($return, 1, strlen($return));
        }

        return $return;
    }

    /**
     * Format a underscore connected word into Camel-Case format.
     *
     * @param string $word
     */
    public static function fromUnderscoreConnectionToDashConnection($string)
    {
        $return = str_replace(' ','-',ucwords(str_replace('_',' ',$string)));
        if (substr($return, 0,1) === '-') {
            $return = substr($return, 1, strlen($return));
        }

        return $return;
    }
}