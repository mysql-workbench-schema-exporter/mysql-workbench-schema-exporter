<?php

/*
 * The MIT License
 *
 * Copyright (c) 2010 Johannes Mueller <circus2(at)web.de>
 * Copyright (c) 2012-2023 Toha <tohenk@yahoo.com>
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

namespace MwbExporter\Formatter;

use MwbExporter\Model\Column;

interface DatatypeConverterInterface
{
    public const DATATYPE_TINYINT = 'com.mysql.rdbms.mysql.datatype.tinyint';
    public const DATATYPE_SMALLINT = 'com.mysql.rdbms.mysql.datatype.smallint';
    public const DATATYPE_MEDIUMINT = 'com.mysql.rdbms.mysql.datatype.mediumint';
    public const DATATYPE_INT = 'com.mysql.rdbms.mysql.datatype.int';
    public const DATATYPE_BIGINT = 'com.mysql.rdbms.mysql.datatype.bigint';
    public const DATATYPE_FLOAT = 'com.mysql.rdbms.mysql.datatype.float';
    public const DATATYPE_DOUBLE = 'com.mysql.rdbms.mysql.datatype.double';
    public const DATATYPE_DECIMAL = 'com.mysql.rdbms.mysql.datatype.decimal';
    public const DATATYPE_CHAR = 'com.mysql.rdbms.mysql.datatype.char';
    public const DATATYPE_NCHAR = 'com.mysql.rdbms.mysql.datatype.nchar';
    public const DATATYPE_VARCHAR = 'com.mysql.rdbms.mysql.datatype.varchar';
    public const DATATYPE_NVARCHAR = 'com.mysql.rdbms.mysql.datatype.nvarchar';
    public const DATATYPE_JSON = 'com.mysql.rdbms.mysql.datatype.json';
    public const DATATYPE_BINARY = 'com.mysql.rdbms.mysql.datatype.binary';
    public const DATATYPE_VARBINARY = 'com.mysql.rdbms.mysql.datatype.varbinary';
    public const DATATYPE_TINYTEXT = 'com.mysql.rdbms.mysql.datatype.tinytext';
    public const DATATYPE_TEXT = 'com.mysql.rdbms.mysql.datatype.text';
    public const DATATYPE_MEDIUMTEXT = 'com.mysql.rdbms.mysql.datatype.mediumtext';
    public const DATATYPE_LONGTEXT = 'com.mysql.rdbms.mysql.datatype.longtext';
    public const DATATYPE_TINYBLOB = 'com.mysql.rdbms.mysql.datatype.tinyblob';
    public const DATATYPE_BLOB = 'com.mysql.rdbms.mysql.datatype.blob';
    public const DATATYPE_MEDIUMBLOB = 'com.mysql.rdbms.mysql.datatype.mediumblob';
    public const DATATYPE_LONGBLOB = 'com.mysql.rdbms.mysql.datatype.longblob';
    public const DATATYPE_DATETIME = 'com.mysql.rdbms.mysql.datatype.datetime';
    public const DATATYPE_DATETIME_F = 'com.mysql.rdbms.mysql.datatype.datetime_f';
    public const DATATYPE_DATE = 'com.mysql.rdbms.mysql.datatype.date';
    public const DATATYPE_DATE_F = 'com.mysql.rdbms.mysql.datatype.date_f';
    public const DATATYPE_TIME = 'com.mysql.rdbms.mysql.datatype.time';
    public const DATATYPE_TIME_F = 'com.mysql.rdbms.mysql.datatype.time_f';
    public const DATATYPE_YEAR = 'com.mysql.rdbms.mysql.datatype.year';
    public const DATATYPE_TIMESTAMP = 'com.mysql.rdbms.mysql.datatype.timestamp';
    public const DATATYPE_TIMESTAMP_F = 'com.mysql.rdbms.mysql.datatype.timestamp_f';
    public const DATATYPE_GEOMETRY = 'com.mysql.rdbms.mysql.datatype.geometry';
    public const DATATYPE_LINESTRING = 'com.mysql.rdbms.mysql.datatype.linestring';
    public const DATATYPE_POLYGON = 'com.mysql.rdbms.mysql.datatype.polygon';
    public const DATATYPE_MULTIPOINT = 'com.mysql.rdbms.mysql.datatype.multipoint';
    public const DATATYPE_MULTILINESTRING = 'com.mysql.rdbms.mysql.datatype.multilinestring';
    public const DATATYPE_MULTIPOLYGON = 'com.mysql.rdbms.mysql.datatype.multipolygon';
    public const DATATYPE_GEOMETRYCOLLECTION = 'com.mysql.rdbms.mysql.datatype.geometrycollection';
    public const DATATYPE_BIT = 'com.mysql.rdbms.mysql.datatype.bit';
    public const DATATYPE_ENUM = 'com.mysql.rdbms.mysql.datatype.enum';
    public const DATATYPE_SET = 'com.mysql.rdbms.mysql.datatype.set';
    public const USERDATATYPE_BOOLEAN = 'com.mysql.rdbms.mysql.userdatatype.boolean';
    public const USERDATATYPE_BOOL = 'com.mysql.rdbms.mysql.userdatatype.bool';
    public const USERDATATYPE_FIXED = 'com.mysql.rdbms.mysql.userdatatype.fixed';
    public const USERDATATYPE_FLOAT4 = 'com.mysql.rdbms.mysql.userdatatype.float4';
    public const USERDATATYPE_FLOAT8 = 'com.mysql.rdbms.mysql.userdatatype.float8';
    public const USERDATATYPE_INT1 = 'com.mysql.rdbms.mysql.userdatatype.int1';
    public const USERDATATYPE_INT2 = 'com.mysql.rdbms.mysql.userdatatype.int2';
    public const USERDATATYPE_INT3 = 'com.mysql.rdbms.mysql.userdatatype.int3';
    public const USERDATATYPE_INT4 = 'com.mysql.rdbms.mysql.userdatatype.int4';
    public const USERDATATYPE_INT8 = 'com.mysql.rdbms.mysql.userdatatype.int8';
    public const USERDATATYPE_INTEGER = 'com.mysql.rdbms.mysql.userdatatype.integer';
    public const USERDATATYPE_LONGVARBINARY = 'com.mysql.rdbms.mysql.userdatatype.longvarbinary';
    public const USERDATATYPE_LONGVARCHAR = 'com.mysql.rdbms.mysql.userdatatype.longvarchar';
    public const USERDATATYPE_LONG = 'com.mysql.rdbms.mysql.userdatatype.long';
    public const USERDATATYPE_MIDDLEINT = 'com.mysql.rdbms.mysql.userdatatype.middleint';
    public const USERDATATYPE_NUMERIC = 'com.mysql.rdbms.mysql.userdatatype.numeric';
    public const USERDATATYPE_DEC = 'com.mysql.rdbms.mysql.userdatatype.dec';
    public const USERDATATYPE_CHARACTER = 'com.mysql.rdbms.mysql.userdatatype.character';

    public function setup();
    public function registerUserDatatypes($dataTypes = []);
    public function getAllDataTypes();
    public function getRegisteredDataTypes();
    public function getDataType($key);
    public function getNativeType($type);
    public function getMappedType(Column $column);
    public function getType(Column $column);
    public function transformDataType($key, $dataType);
    public function setFormatter(FormatterInterface $formatter);
}
