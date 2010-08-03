<?php
/*
 *  The MIT License
 *
 *  Copyright (c) 2010 Johannes Mueller <circus2(at)web.de>
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is
 *  furnished to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in
 *  all copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

class MwbExporter_Formatter_Doctrine2_Annotation_DatatypeConverter extends MwbExporter_Core_DatatypeConverter
{
    public static function setUp()
    {
        self::$datatypes['com.mysql.rdbms.mysql.datatype.tinyint']   = 'tinyint';
        self::$datatypes['com.mysql.rdbms.mysql.datatype.smallint']  = 'smallint';
        self::$datatypes['com.mysql.rdbms.mysql.datatype.mediumint'] = 'mediumint';
        self::$datatypes['com.mysql.rdbms.mysql.datatype.int']       = 'integer';
        self::$datatypes['com.mysql.rdbms.mysql.datatype.bigint']    = 'bigint';
        self::$datatypes['com.mysql.rdbms.mysql.datatype.year']      = 'smallint';

        self::$datatypes['com.mysql.rdbms.mysql.userdatatype.int1']      = 'tinyint';
        self::$datatypes['com.mysql.rdbms.mysql.userdatatype.int2']      = 'smallint';
        self::$datatypes['com.mysql.rdbms.mysql.userdatatype.int3']      = 'mediumint';
        self::$datatypes['com.mysql.rdbms.mysql.userdatatype.int4']      = 'integer';
        self::$datatypes['com.mysql.rdbms.mysql.userdatatype.int8']      = 'bigint';
        self::$datatypes['com.mysql.rdbms.mysql.userdatatype.integer']   = 'integer';
        self::$datatypes['com.mysql.rdbms.mysql.userdatatype.middleint'] = 'mediumint';
    }

    public static function getType($key, MwbExporter_Core_Model_Column $column)
    {
        self::setUp();

        $return = '"' . (isset(self::$datatypes[$key]) ? self::$datatypes[$key] : 'unknown') . '"';
        $config = $column->getConfig();
        if (   isset($config['scale'])
            && $config['scale'] != -1
            && isset($config['precision'])
            && $config['precision'] != -1 ){

            $return = $return . ',scale=' . $config['scale'] . ',precision=' . $config['precision'];
        }
        
        if( isset($config['length']) && $config['length'] != -1 && self::$datatypes[$key] == 'string'){
            $return = $return . ',length=' . $config['length'];
        }

        // handle enums
        if($key === 'com.mysql.rdbms.mysql.datatype.enum'){
            $return .= "\n";
            $return .= "      values: " . str_replace(array('(',')'), array('[',']'), $config['datatypeExplicitParams']);
        }

        // handle sets
        // @TODO sets are not supported by doctrine
        if($key === 'com.mysql.rdbms.mysql.datatype.set'){
            $return .= "\n";
            $return .= "      values: " . str_replace(array('(',')'), array('[',']'), $config['datatypeExplicitParams']);
        }
        
        return $return;
    }
}