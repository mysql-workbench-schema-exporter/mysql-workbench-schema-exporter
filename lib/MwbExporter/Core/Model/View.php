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

abstract class View extends Base
{
    protected $config = null;
    protected $columns = null;
    
    public function __construct($data, $parent)
    {
        parent::__construct($data, $parent);

        $tmp = $this->data->xpath("value[@key='columns']");
        $this->columns = \MwbExporter\Core\Registry::get('formatter')->createColumns($tmp[0], $this);
        
        // iterate on column configuration
        foreach($this->data->value as $key => $node){
            $attributes         = $node->attributes();         // read attributes

            $key                = (string) $attributes['key']; // assign key
            $this->config[$key] = (string) $node[0];           // assign value
        }
        
        \MwbExporter\Core\Registry::set($this->id, $this);
    }
    
    public function getRawTableName()
    {
        return $this->config['name'];
    }
    
    public function getModelName()
    {
        return ucfirst(preg_replace('@\_(\w)@e', 'ucfirst("$1")', $this->getRawTableName()));
    }
    
    public function getModelNameInPlural()
    {
        return Helper_Pluralizer::pluralize($this->getModelName());
    }

}