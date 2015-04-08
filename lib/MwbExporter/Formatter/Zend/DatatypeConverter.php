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

namespace MwbExporter\Formatter\Zend;

use MwbExporter\Formatter\DatatypeConverter as BaseDatatypeConverter;

class DatatypeConverter extends BaseDatatypeConverter
{
    public function setup()
    {
        $this->register(array(
            static::DATATYPE_TINYINT            => 'tinyint',
            static::DATATYPE_SMALLINT           => 'smallint',
            static::DATATYPE_MEDIUMINT          => 'mediumint',
            static::DATATYPE_INT                => 'integer',
            static::DATATYPE_BIGINT             => 'bigint',
            static::DATATYPE_FLOAT              => 'float',
            static::DATATYPE_DOUBLE             => 'double',
            static::DATATYPE_DECIMAL            => 'decimal',
            static::DATATYPE_CHAR               => 'char',
            static::DATATYPE_VARCHAR            => 'string',
            static::DATATYPE_BINARY             => 'binary',
            static::DATATYPE_VARBINARY          => 'varbinary',
            static::DATATYPE_TINYTEXT           => 'clob(255)',
            static::DATATYPE_TEXT               => 'clob(65535)',
            static::DATATYPE_MEDIUMTEXT         => 'clob(16777215)',
            static::DATATYPE_LONGTEXT           => 'clob',
            static::DATATYPE_TINYBLOB           => 'blob(255)',
            static::DATATYPE_BLOB               => 'blob(65535)',
            static::DATATYPE_MEDIUMBLOB         => 'blob(16777215)',
            static::DATATYPE_LONGBLOB           => 'blob',
            static::DATATYPE_DATETIME           => 'datetime',
            static::DATATYPE_DATETIME_F         => 'datetime',
            static::DATATYPE_DATE               => 'date',
            static::DATATYPE_DATE_F             => 'date',
            static::DATATYPE_TIME               => 'time',
            static::DATATYPE_TIME_F             => 'time',
            static::DATATYPE_TIMESTAMP          => 'datetime',
            static::DATATYPE_TIMESTAMP_F        => 'datetime',
            static::DATATYPE_YEAR               => 'smallint',
            static::DATATYPE_GEOMETRY           => 'geometry',
            static::DATATYPE_LINESTRING         => 'linestring',
            static::DATATYPE_POLYGON            => 'polygon',
            static::DATATYPE_MULTIPOINT         => 'multipoint',
            static::DATATYPE_MULTILINESTRING    => 'multilinestring',
            static::DATATYPE_MULTIPOLYGON       => 'multipolygon',
            static::DATATYPE_GEOMETRYCOLLECTION => 'geometrycollection',
            static::DATATYPE_BIT                => 'bit',
            static::DATATYPE_ENUM               => 'enum',
            static::DATATYPE_SET                => 'set',
            static::USERDATATYPE_BOOLEAN        => 'boolean',
            static::USERDATATYPE_BOOL           => 'boolean',
            static::USERDATATYPE_FIXED          => 'decimal',
            static::USERDATATYPE_FLOAT4         => 'float',
            static::USERDATATYPE_FLOAT8         => 'double',
            static::USERDATATYPE_INT1           => 'tinyint',
            static::USERDATATYPE_INT2           => 'smallint',
            static::USERDATATYPE_INT3           => 'mediumint',
            static::USERDATATYPE_INT4           => 'integer',
            static::USERDATATYPE_INT8           => 'bigint',
            static::USERDATATYPE_INTEGER        => 'integer',
            static::USERDATATYPE_LONGVARBINARY  => 'blob(16777215)',
            static::USERDATATYPE_LONGVARCHAR    => 'clob(16777215)',
            static::USERDATATYPE_LONG           => 'clob(16777215)',
            static::USERDATATYPE_MIDDLEINT      => 'mediumint',
            static::USERDATATYPE_NUMERIC        => 'decimal',
            static::USERDATATYPE_DEC            => 'decimal',
            static::USERDATATYPE_CHARACTER      => 'char',
        ));
    }
}