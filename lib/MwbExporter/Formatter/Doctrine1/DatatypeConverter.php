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

namespace MwbExporter\Formatter\Doctrine1;

use MwbExporter\Formatter\DatatypeConverter as BaseDatatypeConverter;

class DatatypeConverter extends BaseDatatypeConverter
{
    public function setup()
    {
        $this->register(array(
            static::DATATYPE_TINYINT            => 'integer(1)',
            static::DATATYPE_SMALLINT           => 'integer(2)',
            static::DATATYPE_MEDIUMINT          => 'integer(3)',
            static::DATATYPE_INT                => 'integer(4)',
            static::DATATYPE_BIGINT             => 'integer(8)',
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
            static::DATATYPE_DATETIME           => 'timestamp',
            static::DATATYPE_DATE               => 'date',
            static::DATATYPE_TIME               => 'time',
            static::DATATYPE_YEAR               => 'integer(2)',
            static::DATATYPE_TIMESTAMP          => 'timestamp',
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
            static::USERDATATYPE_INT1           => 'integer(1)',
            static::USERDATATYPE_INT2           => 'integer(2)',
            static::USERDATATYPE_INT3           => 'integer(3)',
            static::USERDATATYPE_INT4           => 'integer(4)',
            static::USERDATATYPE_INT8           => 'integer(8)',
            static::USERDATATYPE_INTEGER        => 'integer(4)',
            static::USERDATATYPE_LONGVARBINARY  => 'blob(16777215)',
            static::USERDATATYPE_LONGVARCHAR    => 'clob(16777215)',
            static::USERDATATYPE_LONG           => 'clob(16777215)',
            static::USERDATATYPE_MIDDLEINT      => 'integer(3)',
            static::USERDATATYPE_NUMERIC        => 'decimal',
            static::USERDATATYPE_DEC            => 'decimal',
            static::USERDATATYPE_CHARACTER      => 'char',
        ));
    }
}