<?php

/*
 * The MIT License
 *
 * Copyright (c) 2010 Johannes Mueller <circus2(at)web.de>
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

namespace MwbExporter\Formatter\Sencha;

use MwbExporter\DatatypeConverter as BaseDatatypeConverter;

class DatatypeConverter extends BaseDatatypeConverter
{
    public function setup()
    {
        $this->register(array(
            // simple datatypes
            'com.mysql.rdbms.mysql.datatype.tinyint'            => 'int',
            'com.mysql.rdbms.mysql.datatype.smallint'           => 'int',
            'com.mysql.rdbms.mysql.datatype.mediumint'          => 'int',
            'com.mysql.rdbms.mysql.datatype.int'                => 'int',
            'com.mysql.rdbms.mysql.datatype.bigint'             => 'int',
            'com.mysql.rdbms.mysql.datatype.float'              => 'float',
            'com.mysql.rdbms.mysql.datatype.double'             => 'float',
            'com.mysql.rdbms.mysql.datatype.decimal'            => 'float',
            'com.mysql.rdbms.mysql.datatype.char'               => 'string',
            'com.mysql.rdbms.mysql.datatype.varchar'            => 'string',
            'com.mysql.rdbms.mysql.datatype.binary'             => 'string',
            'com.mysql.rdbms.mysql.datatype.varbinary'          => 'string',
            'com.mysql.rdbms.mysql.datatype.tinytext'           => 'string',
            'com.mysql.rdbms.mysql.datatype.text'               => 'string',
            'com.mysql.rdbms.mysql.datatype.mediumtext'         => 'string',
            'com.mysql.rdbms.mysql.datatype.longtext'           => 'string',
            'com.mysql.rdbms.mysql.datatype.tinyblob'           => 'string',
            'com.mysql.rdbms.mysql.datatype.blob'               => 'string',
            'com.mysql.rdbms.mysql.datatype.mediumblob'         => 'string',
            'com.mysql.rdbms.mysql.datatype.longblob'           => 'string',
            'com.mysql.rdbms.mysql.datatype.datetime'           => 'string',
            'com.mysql.rdbms.mysql.datatype.date'               => 'string',
            'com.mysql.rdbms.mysql.datatype.time'               => 'string',
            'com.mysql.rdbms.mysql.datatype.year'               => 'undefined',
            'com.mysql.rdbms.mysql.datatype.timestamp'          => 'string',
            'com.mysql.rdbms.mysql.datatype.geometry'           => 'undefined',
            'com.mysql.rdbms.mysql.datatype.linestring'         => 'string',
            'com.mysql.rdbms.mysql.datatype.polygon'            => 'undefined',
            'com.mysql.rdbms.mysql.datatype.multipoint'         => 'undefined',
            'com.mysql.rdbms.mysql.datatype.multilinestring'    => 'undefined',
            'com.mysql.rdbms.mysql.datatype.multipolygon'       => 'undefined',
            'com.mysql.rdbms.mysql.datatype.geometrycollection' => 'undefined',
            'com.mysql.rdbms.mysql.datatype.bit'                => 'undefined',
            'com.mysql.rdbms.mysql.datatype.enum'               => 'undefined',
            'com.mysql.rdbms.mysql.datatype.set'                => 'undefined'
        ));
    }
}