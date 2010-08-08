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

class Table extends \MwbExporter\Core\Model\Table
{
    public function __construct($data)
    {
        parent::__construct($data);
    }

    public function display()
    {
        $return = array();
        $return[] = $this->getModelName() . ':';

        if($this->getModelName() !== ucfirst($this->getRawTableName())){
            $return[] = '  table_name: ' . $this->getRawTableName();
        }

        $return[] = $this->columns->display();

        // add relations
        if(count($this->relations) > 0){
            $return[] = '  relations:';

            foreach($this->relations as $relation){
                $return[] = $relation->display();
            }
        }

        // add indices
        if(count($this->indexes) > 0){
            $return[] = '  indexes:';

            foreach($this->indexes as $index){
                $return[] = $index->display();
            }
        }

        $return[] = '  options:';
        $return[] = '    charset: ' . (($this->config['defaultCharacterSetName'] == "") ? 'utf8' : $this->config['defaultCharacterSetName']);
        $return[] = '    type: ' . $this->config['tableEngine'];

        // add empty line behind table
        $return[] = '';

        return implode("\n", $return);
    }
}