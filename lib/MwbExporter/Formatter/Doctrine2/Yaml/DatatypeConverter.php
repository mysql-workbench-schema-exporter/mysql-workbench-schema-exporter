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

namespace MwbExporter\Formatter\Doctrine2\Yaml;

class DatatypeConverter extends \MwbExporter\Formatter\Doctrine2\DatatypeConverter
{

    public static function getType($key, \MwbExporter\Core\Model\Column $column)
    {
        $return = isset(self::$datatypes[$key]) ? self::$datatypes[$key] : 'unknown';

        $config = $column->getConfig();
        if (   isset($config['scale'])
            && $config['scale'] != -1
            && isset($config['precision'])
            && $config['precision'] != -1 ){

            $return = $return . '(' . $config['scale'] . ',' . $config['precision'] . ')';
        }

        if( isset($config['length']) && $config['length'] != -1 ){
            $return = $return . '(' . $config['length'] . ')';
        }

        // handle enums
        if($key === 'com.mysql.rdbms.mysql.datatype.enum'){
            $return .= "\n";
            $return .= "      values: " . str_replace(array('(',')'), array('[',']'), $config['datatypeExplicitParams']);
        }

        // handle sets
        // @TODO D2Y sets are not supported by Doctrine
        if($key === 'com.mysql.rdbms.mysql.datatype.set'){
            $return .= "\n";
            $return .= "      values: " . str_replace(array('(',')'), array('[',']'), $config['datatypeExplicitParams']);
        }

        return $return;
    }
}