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

use MwbExporter\Formatter\DatatypeConverter as BaseDatatypeConverter;

class DatatypeConverter extends BaseDatatypeConverter
{
    public function setup()
    {
        $this->register(array(
            static::DATATYPE_TINYINT            => 'int',
            static::DATATYPE_SMALLINT           => 'int',
            static::DATATYPE_MEDIUMINT          => 'int',
            static::DATATYPE_INT                => 'int',
            static::DATATYPE_BIGINT             => 'int',
            static::DATATYPE_FLOAT              => 'float',
            static::DATATYPE_DOUBLE             => 'float',
            static::DATATYPE_DECIMAL            => 'float',
            static::DATATYPE_CHAR               => 'string',
            static::DATATYPE_VARCHAR            => 'string',
            static::DATATYPE_BINARY             => 'string',
            static::DATATYPE_VARBINARY          => 'string',
            static::DATATYPE_TINYTEXT           => 'string',
            static::DATATYPE_TEXT               => 'string',
            static::DATATYPE_MEDIUMTEXT         => 'string',
            static::DATATYPE_LONGTEXT           => 'string',
            static::DATATYPE_TINYBLOB           => 'string',
            static::DATATYPE_BLOB               => 'string',
            static::DATATYPE_MEDIUMBLOB         => 'string',
            static::DATATYPE_LONGBLOB           => 'string',
            static::DATATYPE_DATETIME           => 'string',
            static::DATATYPE_DATE               => 'string',
            static::DATATYPE_TIME               => 'string',
            static::DATATYPE_YEAR               => 'int',
            static::DATATYPE_TIMESTAMP          => 'string',
            static::DATATYPE_GEOMETRY           => 'undefined',
            static::DATATYPE_LINESTRING         => 'string',
            static::DATATYPE_POLYGON            => 'undefined',
            static::DATATYPE_MULTIPOINT         => 'undefined',
            static::DATATYPE_MULTILINESTRING    => 'undefined',
            static::DATATYPE_MULTIPOLYGON       => 'undefined',
            static::DATATYPE_GEOMETRYCOLLECTION => 'undefined',
            static::DATATYPE_BIT                => 'int',
            static::DATATYPE_ENUM               => 'string',
            static::DATATYPE_SET                => 'string',
            static::USERDATATYPE_BOOLEAN        => 'bool',
            static::USERDATATYPE_BOOL           => 'bool',
            static::USERDATATYPE_FIXED          => 'int',
            static::USERDATATYPE_FLOAT4         => 'float',
            static::USERDATATYPE_FLOAT8         => 'float',
            static::USERDATATYPE_INT1           => 'int',
            static::USERDATATYPE_INT2           => 'int',
            static::USERDATATYPE_INT3           => 'int',
            static::USERDATATYPE_INT4           => 'int',
            static::USERDATATYPE_INT8           => 'int',
            static::USERDATATYPE_INTEGER        => 'int',
            static::USERDATATYPE_LONGVARBINARY  => 'string',
            static::USERDATATYPE_LONGVARCHAR    => 'string',
            static::USERDATATYPE_LONG           => 'int',
            static::USERDATATYPE_MIDDLEINT      => 'int',
            static::USERDATATYPE_NUMERIC        => 'int',
            static::USERDATATYPE_DEC            => 'int',
            static::USERDATATYPE_CHARACTER      => 'string',
        ));
    }
}