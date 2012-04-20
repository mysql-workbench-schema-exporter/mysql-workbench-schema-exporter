<?php

namespace MwbExporter\Formatter\JS;

class DatatypeConverter extends \MwbExporter\Core\DatatypeConverter
{
    public static function setUp()
    {
        self::$datatypes = array(
            // simple datatypes
            'com.mysql.rdbms.mysql.datatype.tinyint'            => 'int',
            'com.mysql.rdbms.mysql.datatype.smallint'           => 'int',
            'com.mysql.rdbms.mysql.datatype.mediumint'          => 'int',
            'com.mysql.rdbms.mysql.datatype.int'                => 'int',
            'com.mysql.rdbms.mysql.datatype.bigint'             => 'int',
            'com.mysql.rdbms.mysql.datatype.float'              => 'float',
            'com.mysql.rdbms.mysql.datatype.double'             => 'float',
            'com.mysql.rdbms.mysql.datatype.decimal'            => 'float',
            'com.mysql.rdbms.mysql.datatype.char'               => 'string',
            'com.mysql.rdbms.mysql.datatype.varchar'            => 'string',
            'com.mysql.rdbms.mysql.datatype.binary'             => 'string',
            'com.mysql.rdbms.mysql.datatype.varbinary'          => 'string',
            'com.mysql.rdbms.mysql.datatype.tinytext'           => 'string',
            'com.mysql.rdbms.mysql.datatype.text'               => 'string',
            'com.mysql.rdbms.mysql.datatype.mediumtext'         => 'string',
            'com.mysql.rdbms.mysql.datatype.longtext'           => 'string',
            'com.mysql.rdbms.mysql.datatype.tinyblob'           => 'string',
            'com.mysql.rdbms.mysql.datatype.blob'               => 'string',
            'com.mysql.rdbms.mysql.datatype.mediumblob'         => 'string',
            'com.mysql.rdbms.mysql.datatype.longblob'           => 'string',
            'com.mysql.rdbms.mysql.datatype.datetime'           => 'string',
            'com.mysql.rdbms.mysql.datatype.date'               => 'string',
            'com.mysql.rdbms.mysql.datatype.time'               => 'string',
            'com.mysql.rdbms.mysql.datatype.year'               => 'undefined',
            'com.mysql.rdbms.mysql.datatype.timestamp'          => 'string',
            'com.mysql.rdbms.mysql.datatype.geometry'           => 'undefined',
            'com.mysql.rdbms.mysql.datatype.linestring'         => 'string',
            'com.mysql.rdbms.mysql.datatype.polygon'            => 'undefined',
            'com.mysql.rdbms.mysql.datatype.multipoint'         => 'undefined',
            'com.mysql.rdbms.mysql.datatype.multilinestring'    => 'undefined',
            'com.mysql.rdbms.mysql.datatype.multipolygon'       => 'undefined',
            'com.mysql.rdbms.mysql.datatype.geometrycollection' => 'undefined',
            'com.mysql.rdbms.mysql.datatype.bit'                => 'undefined',
            'com.mysql.rdbms.mysql.datatype.enum'               => 'undefined',
            'com.mysql.rdbms.mysql.datatype.set'                => 'undefined'
        );

    }
}