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

namespace MwbExporter\Formatter;

use MwbExporter\Model\Column;

interface DatatypeConverterInterface
{
    const DATATYPE_TINYINT             = 'com.mysql.rdbms.mysql.datatype.tinyint';
    const DATATYPE_SMALLINT            = 'com.mysql.rdbms.mysql.datatype.smallint';
    const DATATYPE_MEDIUMINT           = 'com.mysql.rdbms.mysql.datatype.mediumint';
    const DATATYPE_INT                 = 'com.mysql.rdbms.mysql.datatype.int';
    const DATATYPE_BIGINT              = 'com.mysql.rdbms.mysql.datatype.bigint';
    const DATATYPE_FLOAT               = 'com.mysql.rdbms.mysql.datatype.float';
    const DATATYPE_DOUBLE              = 'com.mysql.rdbms.mysql.datatype.double';
    const DATATYPE_DECIMAL             = 'com.mysql.rdbms.mysql.datatype.decimal';
    const DATATYPE_CHAR                = 'com.mysql.rdbms.mysql.datatype.char';
    const DATATYPE_VARCHAR             = 'com.mysql.rdbms.mysql.datatype.varchar';
    const DATATYPE_BINARY              = 'com.mysql.rdbms.mysql.datatype.binary';
    const DATATYPE_VARBINARY           = 'com.mysql.rdbms.mysql.datatype.varbinary';
    const DATATYPE_TINYTEXT            = 'com.mysql.rdbms.mysql.datatype.tinytext';
    const DATATYPE_TEXT                = 'com.mysql.rdbms.mysql.datatype.text';
    const DATATYPE_MEDIUMTEXT          = 'com.mysql.rdbms.mysql.datatype.mediumtext';
    const DATATYPE_LONGTEXT            = 'com.mysql.rdbms.mysql.datatype.longtext';
    const DATATYPE_TINYBLOB            = 'com.mysql.rdbms.mysql.datatype.tinyblob';
    const DATATYPE_BLOB                = 'com.mysql.rdbms.mysql.datatype.blob';
    const DATATYPE_MEDIUMBLOB          = 'com.mysql.rdbms.mysql.datatype.mediumblob';
    const DATATYPE_LONGBLOB            = 'com.mysql.rdbms.mysql.datatype.longblob';
    const DATATYPE_DATETIME            = 'com.mysql.rdbms.mysql.datatype.datetime';
    const DATATYPE_DATETIME_F          = 'com.mysql.rdbms.mysql.datatype.datetime_f';
    const DATATYPE_DATE                = 'com.mysql.rdbms.mysql.datatype.date';
    const DATATYPE_DATE_F              = 'com.mysql.rdbms.mysql.datatype.date_f';
    const DATATYPE_TIME                = 'com.mysql.rdbms.mysql.datatype.time';
    const DATATYPE_TIME_F              = 'com.mysql.rdbms.mysql.datatype.time_f';
    const DATATYPE_YEAR                = 'com.mysql.rdbms.mysql.datatype.year';
    const DATATYPE_TIMESTAMP           = 'com.mysql.rdbms.mysql.datatype.timestamp';
    const DATATYPE_TIMESTAMP_F         = 'com.mysql.rdbms.mysql.datatype.timestamp_f';
    const DATATYPE_GEOMETRY            = 'com.mysql.rdbms.mysql.datatype.geometry';
    const DATATYPE_LINESTRING          = 'com.mysql.rdbms.mysql.datatype.linestring';
    const DATATYPE_POLYGON             = 'com.mysql.rdbms.mysql.datatype.polygon';
    const DATATYPE_MULTIPOINT          = 'com.mysql.rdbms.mysql.datatype.multipoint';
    const DATATYPE_MULTILINESTRING     = 'com.mysql.rdbms.mysql.datatype.multilinestring';
    const DATATYPE_MULTIPOLYGON        = 'com.mysql.rdbms.mysql.datatype.multipolygon';
    const DATATYPE_GEOMETRYCOLLECTION  = 'com.mysql.rdbms.mysql.datatype.geometrycollection';
    const DATATYPE_BIT                 = 'com.mysql.rdbms.mysql.datatype.bit';
    const DATATYPE_ENUM                = 'com.mysql.rdbms.mysql.datatype.enum';
    const DATATYPE_SET                 = 'com.mysql.rdbms.mysql.datatype.set';
    const USERDATATYPE_BOOLEAN         = 'com.mysql.rdbms.mysql.userdatatype.boolean';
    const USERDATATYPE_BOOL            = 'com.mysql.rdbms.mysql.userdatatype.bool';
    const USERDATATYPE_FIXED           = 'com.mysql.rdbms.mysql.userdatatype.fixed';
    const USERDATATYPE_FLOAT4          = 'com.mysql.rdbms.mysql.userdatatype.float4';
    const USERDATATYPE_FLOAT8          = 'com.mysql.rdbms.mysql.userdatatype.float8';
    const USERDATATYPE_INT1            = 'com.mysql.rdbms.mysql.userdatatype.int1';
    const USERDATATYPE_INT2            = 'com.mysql.rdbms.mysql.userdatatype.int2';
    const USERDATATYPE_INT3            = 'com.mysql.rdbms.mysql.userdatatype.int3';
    const USERDATATYPE_INT4            = 'com.mysql.rdbms.mysql.userdatatype.int4';
    const USERDATATYPE_INT8            = 'com.mysql.rdbms.mysql.userdatatype.int8';
    const USERDATATYPE_INTEGER         = 'com.mysql.rdbms.mysql.userdatatype.integer';
    const USERDATATYPE_LONGVARBINARY   = 'com.mysql.rdbms.mysql.userdatatype.longvarbinary';
    const USERDATATYPE_LONGVARCHAR     = 'com.mysql.rdbms.mysql.userdatatype.longvarchar';
    const USERDATATYPE_LONG            = 'com.mysql.rdbms.mysql.userdatatype.long';
    const USERDATATYPE_MIDDLEINT       = 'com.mysql.rdbms.mysql.userdatatype.middleint';
    const USERDATATYPE_NUMERIC         = 'com.mysql.rdbms.mysql.userdatatype.numeric';
    const USERDATATYPE_DEC             = 'com.mysql.rdbms.mysql.userdatatype.dec';
    const USERDATATYPE_CHARACTER       = 'com.mysql.rdbms.mysql.userdatatype.character';

    public function setup();
    public function getDataType($key);
    public function getNativeType($type);
    public function getMappedType(Column $column);
    public function getType(Column $column);
}
