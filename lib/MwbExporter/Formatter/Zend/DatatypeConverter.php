<?php

namespace MwbExporter\Formatter\Zend;

use MwbExporter\Core\DatatypeConverter as Base;

class DatatypeConverter extends Base
{
    public function setUp()
    {
        $this->register(array(
            // simple datatypes
            'com.mysql.rdbms.mysql.datatype.tinyint'            => 'tinyint',
            'com.mysql.rdbms.mysql.datatype.smallint'           => 'smallint',
            'com.mysql.rdbms.mysql.datatype.mediumint'          => 'mediumint',
            'com.mysql.rdbms.mysql.datatype.int'                => 'integer',
            'com.mysql.rdbms.mysql.datatype.bigint'             => 'bigint',
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
            'com.mysql.rdbms.mysql.datatype.datetime'           => 'datetime',
            'com.mysql.rdbms.mysql.datatype.date'               => 'date',
            'com.mysql.rdbms.mysql.datatype.time'               => 'time',
            'com.mysql.rdbms.mysql.datatype.year'               => 'smallint',
            'com.mysql.rdbms.mysql.datatype.timestamp'          => 'datetime',
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
            'com.mysql.rdbms.mysql.userdatatype.int1'           => 'tinyint',
            'com.mysql.rdbms.mysql.userdatatype.int2'           => 'smallint',
            'com.mysql.rdbms.mysql.userdatatype.int3'           => 'mediumint',
            'com.mysql.rdbms.mysql.userdatatype.int4'           => 'integer',
            'com.mysql.rdbms.mysql.userdatatype.int8'           => 'bigint',
            'com.mysql.rdbms.mysql.userdatatype.integer'        => 'integer',
            'com.mysql.rdbms.mysql.userdatatype.longvarbinary'  => 'blob(16777215)',
            'com.mysql.rdbms.mysql.userdatatype.longvarchar'    => 'clob(16777215)',
            'com.mysql.rdbms.mysql.userdatatype.long'           => 'clob(16777215)',
            'com.mysql.rdbms.mysql.userdatatype.middleint'      => 'mediumint',
            'com.mysql.rdbms.mysql.userdatatype.numeric'        => 'decimal',
            'com.mysql.rdbms.mysql.userdatatype.dec'            => 'decimal',
            'com.mysql.rdbms.mysql.userdatatype.character'      => 'char'
        ));
    }
}