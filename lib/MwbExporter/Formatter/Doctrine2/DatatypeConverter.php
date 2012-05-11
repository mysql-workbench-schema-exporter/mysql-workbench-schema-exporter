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

namespace MwbExporter\Formatter\Doctrine2;

use MwbExporter\DatatypeConverter as Base;
use MwbExporter\Model\Column;

class DatatypeConverter extends Base
{
    public function setup()
    {
        /**
        Doctrine 2.2 Data Type:
        const TARRAY = 'array';
        const BIGINT = 'bigint';
        const BOOLEAN = 'boolean';
        const DATETIME = 'datetime';
        const DATETIMETZ = 'datetimetz';
        const DATE = 'date';
        const TIME = 'time';
        const DECIMAL = 'decimal';
        const INTEGER = 'integer';
        const OBJECT = 'object';
        const SMALLINT = 'smallint';
        const STRING = 'string';
        const TEXT = 'text';
        const BLOB = 'blob';
        const FLOAT = 'float';
        */
        $this->register(array(
            // simple datatypes
            'com.mysql.rdbms.mysql.datatype.tinyint'            => 'integer',
            'com.mysql.rdbms.mysql.datatype.smallint'           => 'smallint',
            'com.mysql.rdbms.mysql.datatype.mediumint'          => 'integer',
            'com.mysql.rdbms.mysql.datatype.int'                => 'integer',
            'com.mysql.rdbms.mysql.datatype.bigint'             => 'bigint',
            'com.mysql.rdbms.mysql.datatype.float'              => 'float',
            'com.mysql.rdbms.mysql.datatype.double'             => 'float',
            'com.mysql.rdbms.mysql.datatype.decimal'            => 'decimal',
            'com.mysql.rdbms.mysql.datatype.char'               => 'string',
            'com.mysql.rdbms.mysql.datatype.varchar'            => 'string',
            'com.mysql.rdbms.mysql.datatype.binary'             => 'blob',
            'com.mysql.rdbms.mysql.datatype.varbinary'          => 'blob',
            'com.mysql.rdbms.mysql.datatype.tinytext'           => 'text',
            'com.mysql.rdbms.mysql.datatype.text'               => 'text',
            'com.mysql.rdbms.mysql.datatype.mediumtext'         => 'text',
            'com.mysql.rdbms.mysql.datatype.longtext'           => 'text',
            'com.mysql.rdbms.mysql.datatype.tinyblob'           => 'blob',
            'com.mysql.rdbms.mysql.datatype.blob'               => 'blob',
            'com.mysql.rdbms.mysql.datatype.mediumblob'         => 'blob',
            'com.mysql.rdbms.mysql.datatype.longblob'           => 'blob',
            'com.mysql.rdbms.mysql.datatype.datetime'           => 'datetime',
            'com.mysql.rdbms.mysql.datatype.date'               => 'date',
            'com.mysql.rdbms.mysql.datatype.time'               => 'time',
            'com.mysql.rdbms.mysql.datatype.year'               => 'smallint',
            'com.mysql.rdbms.mysql.datatype.timestamp'          => 'datetime',
            'com.mysql.rdbms.mysql.datatype.geometry'           => 'object',
            'com.mysql.rdbms.mysql.datatype.linestring'         => 'object',
            'com.mysql.rdbms.mysql.datatype.polygon'            => 'object',
            'com.mysql.rdbms.mysql.datatype.multipoint'         => 'object',
            'com.mysql.rdbms.mysql.datatype.multilinestring'    => 'object',
            'com.mysql.rdbms.mysql.datatype.multipolygon'       => 'object',
            'com.mysql.rdbms.mysql.datatype.geometrycollection' => 'object',
            'com.mysql.rdbms.mysql.datatype.bit'                => 'bigint',
            'com.mysql.rdbms.mysql.datatype.enum'               => 'string',
            'com.mysql.rdbms.mysql.datatype.set'                => 'string',
            // userdatatypes
            'com.mysql.rdbms.mysql.userdatatype.boolean'        => 'boolean',
            'com.mysql.rdbms.mysql.userdatatype.bool'           => 'boolean',
            'com.mysql.rdbms.mysql.userdatatype.fixed'          => 'decimal',
            'com.mysql.rdbms.mysql.userdatatype.float4'         => 'float',
            'com.mysql.rdbms.mysql.userdatatype.float8'         => 'float',
            'com.mysql.rdbms.mysql.userdatatype.int1'           => 'integer',
            'com.mysql.rdbms.mysql.userdatatype.int2'           => 'integer',
            'com.mysql.rdbms.mysql.userdatatype.int3'           => 'integer',
            'com.mysql.rdbms.mysql.userdatatype.int4'           => 'integer',
            'com.mysql.rdbms.mysql.userdatatype.int8'           => 'integer',
            'com.mysql.rdbms.mysql.userdatatype.integer'        => 'integer',
            'com.mysql.rdbms.mysql.userdatatype.longvarbinary'  => 'blob',
            'com.mysql.rdbms.mysql.userdatatype.longvarchar'    => 'blob',
            'com.mysql.rdbms.mysql.userdatatype.long'           => 'bigint',
            'com.mysql.rdbms.mysql.userdatatype.middleint'      => 'integer',
            'com.mysql.rdbms.mysql.userdatatype.numeric'        => 'decimal',
            'com.mysql.rdbms.mysql.userdatatype.dec'            => 'decimal',
            'com.mysql.rdbms.mysql.userdatatype.character'      => 'string'
        ));
    }

    public function getNativeType($type)
    {
        switch ($type) {
            case 'array':
            case 'boolean':
            case 'datetime':
            case 'integer':
            case 'string':
            case 'float':
            case 'object':
                break;

            case 'smallint':
            case 'bigint':
                $type = 'integer';
                break;

            case 'datetimez':
            case 'date':
            case 'time':
                $type = 'datetime';
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