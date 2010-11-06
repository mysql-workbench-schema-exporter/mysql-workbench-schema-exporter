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

namespace MwbExporter\Formatter\Doctrine1\Yaml\Model;

class Column extends \MwbExporter\Core\Model\Column
{
    public function __construct($data, $parent)
    {
        parent::__construct($data, $parent);
    }

    public function display()
    {
        $return = array();

        // set name of column
        $return[] = '    ' . $this->config['name'] . ':';

        // set datatype of column
        $return[] = '      type: ' . \MwbExporter\Core\Registry::get('formatter')->useDatatypeConverter((isset($this->link['simpleType']) ? $this->link['simpleType'] : $this->link['userType']), $this);

        if($this->isPrimary){
            $return[] = '      primary: true';
        }

        // check for not null column
        if(isset($this->config['isNotNull']) && $this->config['isNotNull'] == 1){
            $return[] = '      notnull: true';
        }
        
        // check for auto increment column
        if(isset($this->config['autoIncrement']) && $this->config['autoIncrement'] == 1){
            $return[] = '      autoincrement: true';
        }

        // set default value
        if(isset($this->config['defaultValue']) && $this->config['defaultValue'] != '' && $this->config['defaultValue'] != 'NULL'){
            $return[] = '      default: ' . $this->config['defaultValue'];
        }

        // iterate on column flags
        foreach($this->data->xpath("value[@key='flags']/value") as $flag){
            $return[] = '      ' . strtolower($flag) . ': true';
        }

        // return yaml representation of column
        return implode("\n", $return);
    }
}