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

namespace MwbExporter\Core\Model;

abstract class Index extends Base
{
    protected $referencedColumn = array();

    public function __construct($data, $parent)
    {
        parent::__construct($data, $parent);

        //iterate on column configuration
        foreach($this->data->value as $key => $node){
            $attributes         = $node->attributes();         // read attributes
            $key                = (string) $attributes['key']; // assign key
            $this->config[$key] = (string) $node[0];           // assign value
        }

        // iterate on links to other wb objects
        foreach($this->data->link as $key => $node){
            $attributes         = $node->attributes();
            $key                = (string) $attributes['key'];
        }

        $isPrimary = false;
        // check for primary columns, to notify column
        $nodes = $this->data->xpath("value[@key='columns']/value/link[@key='referencedColumn']");
        if(is_array($nodes)){
            foreach($nodes as $node){
                // for primary indexes ignore external index
                // definition and set column to primary instead
                if($this->config['name'] == 'PRIMARY'){
                    $isPrimary = true;
                    \MwbExporter\Core\Registry::get((string) $node)->markAsPrimary();
                } else {
                    if($this->config['indexType'] == 'UNIQUE'){
                        \MwbExporter\Core\Registry::get((string) $node)->markAsUnique();
                    }
                    $this->referencedColumn[] = \MwbExporter\Core\Registry::get((string) $node);
                }
            }
        }
        if($isPrimary) {
            return;
        }
        \MwbExporter\Core\Registry::get((string)$this->data->link)->injectIndex($this);
        \MwbExporter\Core\Registry::set($this->id, $this);
    }
}