<?php

namespace MwbExporter\Formatter\Doctrine1;

class DatatypeConverter extends \MwbExporter\Core\DatatypeConverter
{
    public static function setUp()
    {
        self::$datatypes = array(
            // simple datatypes
            'com.mysql.rdbms.mysql.datatype.tinyint'            => 'integer(1)',
            'com.mysql.rdbms.mysql.datatype.smallint'           => 'integer(2)',
            'com.mysql.rdbms.mysql.datatype.mediumint'          => 'integer(3)',
            'com.mysql.rdbms.mysql.datatype.int'                => 'integer(4)',
            'com.mysql.rdbms.mysql.datatype.bigint'             => 'integer(8)',
            'com.mysql.rdbms.mysql.datatype.float'              => 'float',
            'com.mysql.rdbms.mysql.datatype.double'             => 'double',
            'com.mysql.rdbms.mysql.datatype.decimal'            => 'decimal',
            'com.mysql.rdbms.mysql.datatype.char'               => 'char',
            'com.mysql.rdbms.mysql.datatype.varchar'            => 'string',
            'com.mysql.rdbms.mysql.datatype.binary'             => 'binary',
            'com.mysql.rdbms.mysql.datatype.varbinary'          => 'varbinary',
            'com.mysql.rdbms.mysql.datatype.tinytext'           => 'clob(255)',
            'com.mysql.rdbms.mysql.datatype.text'               => 'clob(65535)',
            'com.mysql.rdbms.mysql.datatype.mediumtext'         => 'clob(16777215)',
            'com.mysql.rdbms.mysql.datatype.longtext'           => 'clob',
            'com.mysql.rdbms.mysql.datatype.tinyblob'           => 'blob(255)',
            'com.mysql.rdbms.mysql.datatype.blob'               => 'blob(65535)',
            'com.mysql.rdbms.mysql.datatype.mediumblob'         => 'blob(16777215)',
            'com.mysql.rdbms.mysql.datatype.longblob'           => 'blob',
            'com.mysql.rdbms.mysql.datatype.datetime'           => 'timestamp',
            'com.mysql.rdbms.mysql.datatype.date'               => 'date',
            'com.mysql.rdbms.mysql.datatype.time'               => 'time',
            'com.mysql.rdbms.mysql.datatype.year'               => 'integer(2)',
            'com.mysql.rdbms.mysql.datatype.timestamp'          => 'timestamp',
            'com.mysql.rdbms.mysql.datatype.geometry'           => 'geometry',
            'com.mysql.rdbms.mysql.datatype.linestring'         => 'linestring',
            'com.mysql.rdbms.mysql.datatype.polygon'            => 'polygon',
            'com.mysql.rdbms.mysql.datatype.multipoint'         => 'multipoint',
            'com.mysql.rdbms.mysql.datatype.multilinestring'    => 'multilinestring',
            'com.mysql.rdbms.mysql.datatype.multipolygon'       => 'multipolygon',
            'com.mysql.rdbms.mysql.datatype.geometrycollection' => 'geometrycollection',
            'com.mysql.rdbms.mysql.datatype.bit'                => 'bit',
            'com.mysql.rdbms.mysql.datatype.enum'               => 'enum',
            'com.mysql.rdbms.mysql.datatype.set'                => 'set',

            // userdatatypes
            'com.mysql.rdbms.mysql.userdatatype.boolean'        => 'boolean',
            'com.mysql.rdbms.mysql.userdatatype.bool'           => 'boolean',
            'com.mysql.rdbms.mysql.userdatatype.fixed'          => 'decimal',
            'com.mysql.rdbms.mysql.userdatatype.float4'         => 'float',
            'com.mysql.rdbms.mysql.userdatatype.float8'         => 'double',
            'com.mysql.rdbms.mysql.userdatatype.int1'           => 'integer(1)',
            'com.mysql.rdbms.mysql.userdatatype.int2'           => 'integer(2)',
            'com.mysql.rdbms.mysql.userdatatype.int3'           => 'integer(3)',
            'com.mysql.rdbms.mysql.userdatatype.int4'           => 'integer(4)',
            'com.mysql.rdbms.mysql.userdatatype.int8'           => 'integer(8)',
            'com.mysql.rdbms.mysql.userdatatype.integer'        => 'integer(4)',
            'com.mysql.rdbms.mysql.userdatatype.longvarbinary'  => 'blob(16777215)',
            'com.mysql.rdbms.mysql.userdatatype.longvarchar'    => 'clob(16777215)',
            'com.mysql.rdbms.mysql.userdatatype.long'           => 'clob(16777215)',
            'com.mysql.rdbms.mysql.userdatatype.middleint'      => 'integer(3)',
            'com.mysql.rdbms.mysql.userdatatype.numeric'        => 'decimal',
            'com.mysql.rdbms.mysql.userdatatype.dec'            => 'decimal',
            'com.mysql.rdbms.mysql.userdatatype.character'      => 'char'
        );
    }
}