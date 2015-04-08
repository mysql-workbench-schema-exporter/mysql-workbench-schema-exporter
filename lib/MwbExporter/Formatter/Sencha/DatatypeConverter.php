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

namespace MwbExporter\Formatter\Sencha;

use MwbExporter\Formatter\DatatypeConverter as BaseDatatypeConverter;

class DatatypeConverter extends BaseDatatypeConverter
{
    public function setup()
    {
        /*
         * http://docs.sencha.com/extjs/3.4.0/#!/api/Ext.data.Field
         * http://docs.sencha.com/extjs/4.2.0/#!/api/Ext.data.Types
         */
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
            static::DATATYPE_DATETIME           => 'date',
            static::DATATYPE_DATETIME_F         => 'date',
            static::DATATYPE_DATE               => 'date',
            static::DATATYPE_DATE_F             => 'date',
            static::DATATYPE_TIME               => 'date',
            static::DATATYPE_TIME_F             => 'date',
            static::DATATYPE_TIMESTAMP          => 'date',
            static::DATATYPE_TIMESTAMP_F        => 'date',
            static::DATATYPE_YEAR               => 'int',
            static::DATATYPE_GEOMETRY           => 'auto',
            static::DATATYPE_LINESTRING         => 'string',
            static::DATATYPE_POLYGON            => 'auto',
            static::DATATYPE_MULTIPOINT         => 'auto',
            static::DATATYPE_MULTILINESTRING    => 'auto',
            static::DATATYPE_MULTIPOLYGON       => 'auto',
            static::DATATYPE_GEOMETRYCOLLECTION => 'auto',
            static::DATATYPE_BIT                => 'int',
            static::DATATYPE_ENUM               => 'string',
            static::DATATYPE_SET                => 'string',
            static::USERDATATYPE_BOOLEAN        => 'boolean',
            static::USERDATATYPE_BOOL           => 'boolean',
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