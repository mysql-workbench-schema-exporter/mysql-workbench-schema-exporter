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

namespace MwbExporter\Formatter\Doctrine2\Yaml\Model;

class Index extends \MwbExporter\Core\Model\Index
{
    public function __construct($data, $parent)
    {
        parent::__construct($data, $parent);
    }

    /**
     * Return the index definition
     * Yaml format
     *
     * @return string
     */
    public function display()
    {
        $return = array();
        $return[] = $this->indentation(2) . $this->config['name'] . ':';
        $tmp = $this->indentation(3) . 'columns: [';
        foreach($this->referencedColumn as $refColumn){
            $tmp .= $refColumn->getColumnName() . ',';
        }
        $return[] = substr($tmp, 0, -1) . ']';

        // disable type: index for foreign key indexes
        if(strtolower($this->config['indexType']) !== 'index') {
            $return[] = $this->indentation(3) . 'type: ' . strtolower($this->config['indexType']);
        }

        return implode("\n", $return);
    }
}
