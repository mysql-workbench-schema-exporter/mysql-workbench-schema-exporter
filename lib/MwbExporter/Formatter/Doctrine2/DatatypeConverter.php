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

namespace MwbExporter\Formatter\Doctrine2;

use MwbExporter\Formatter\DatatypeConverter as BaseDatatypeConverter;
use MwbExporter\Model\Column;

class DatatypeConverter extends BaseDatatypeConverter
{
    public function setup()
    {
        $this->register(array(
            static::DATATYPE_TINYINT            => 'smallint',
            static::DATATYPE_SMALLINT           => 'smallint',
            static::DATATYPE_MEDIUMINT          => 'integer',
            static::DATATYPE_INT                => 'integer',
            static::DATATYPE_BIGINT             => 'bigint',
            static::DATATYPE_FLOAT              => 'float',
            static::DATATYPE_DOUBLE             => 'float',
            static::DATATYPE_DECIMAL            => 'decimal',
            static::DATATYPE_CHAR               => 'string',
            static::DATATYPE_VARCHAR            => 'string',
            static::DATATYPE_BINARY             => 'blob',
            static::DATATYPE_VARBINARY          => 'blob',
            static::DATATYPE_TINYTEXT           => 'text',
            static::DATATYPE_TEXT               => 'text',
            static::DATATYPE_MEDIUMTEXT         => 'text',
            static::DATATYPE_LONGTEXT           => 'text',
            static::DATATYPE_TINYBLOB           => 'blob',
            static::DATATYPE_BLOB               => 'blob',
            static::DATATYPE_MEDIUMBLOB         => 'blob',
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
            static::DATATYPE_GEOMETRY           => 'object',
            static::DATATYPE_LINESTRING         => 'object',
            static::DATATYPE_POLYGON            => 'object',
            static::DATATYPE_MULTIPOINT         => 'object',
            static::DATATYPE_MULTILINESTRING    => 'object',
            static::DATATYPE_MULTIPOLYGON       => 'object',
            static::DATATYPE_GEOMETRYCOLLECTION => 'object',
            static::DATATYPE_BIT                => 'bigint',
            static::DATATYPE_ENUM               => 'string',
            static::DATATYPE_SET                => 'string',
            static::USERDATATYPE_BOOLEAN        => 'boolean',
            static::USERDATATYPE_BOOL           => 'boolean',
            static::USERDATATYPE_FIXED          => 'decimal',
            static::USERDATATYPE_FLOAT4         => 'float',
            static::USERDATATYPE_FLOAT8         => 'float',
            static::USERDATATYPE_INT1           => 'integer',
            static::USERDATATYPE_INT2           => 'integer',
            static::USERDATATYPE_INT3           => 'integer',
            static::USERDATATYPE_INT4           => 'integer',
            static::USERDATATYPE_INT8           => 'integer',
            static::USERDATATYPE_INTEGER        => 'integer',
            static::USERDATATYPE_LONGVARBINARY  => 'blob',
            static::USERDATATYPE_LONGVARCHAR    => 'blob',
            static::USERDATATYPE_LONG           => 'bigint',
            static::USERDATATYPE_MIDDLEINT      => 'integer',
            static::USERDATATYPE_NUMERIC        => 'decimal',
            static::USERDATATYPE_DEC            => 'decimal',
            static::USERDATATYPE_CHARACTER      => 'string',
        ));
    }

    public function getNativeType($type)
    {
        switch ($type) {
            case 'array':
            case 'boolean':
            case 'integer':
            case 'string':
            case 'float':
            case 'object':
                break;

            case 'datetime':
            case 'datetimez':
            case 'date':
            case 'time':
                return '\\DateTime';
                break;

            case 'smallint':
            case 'bigint':
                $type = 'integer';
                break;

            case 'decimal':
                $type = 'float';
                break;

            case 'text':
            case 'blob':
                $type = 'string';
                break;

            default:
                break;
        }

        return $type;
    }

    public function getMappedType(Column $column)
    {
        $type = parent::getMappedType($column);
        // map tinyint(1) as boolean
        if ('tinyint' == substr($column->getColumnType(), -7) && 1 == $column->getParameters()->get('precision')) {
            $type = 'boolean';
        }

        return $type;
    }
}
