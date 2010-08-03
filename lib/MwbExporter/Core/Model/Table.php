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

abstract class MwbExporter_Core_Model_Table
{
    protected $data = null;
    protected $attributes = null;
    protected $config = null;
    
    protected $id = null;
    
    protected $columns = null;     // workbench object
    protected $indices = null;     // workbench object
    protected $foreignKeys = null; // workbench object
    protected $indexes = array();   // collection of indexes
    protected $relations = array(); // collection of relations

    
    public function __construct($data)
    {
        $this->attributes = $data->attributes();
        $this->data = $data;
        
        $this->id = (string) $this->attributes['id'];

        $tmp = $this->data->xpath("value[@key='columns']");
        $this->columns = MwbExporter_Core_Registry::get('formatter')->createColumns($tmp[0]);
        
        /**
         * iterate on column configuration
         */
        foreach($this->data->value as $key => $node){
            $attributes         = $node->attributes();         // read attributes

            $key                = (string) $attributes['key']; // assign key
            $this->config[$key] = (string) $node[0];           // assign value
        }

        MwbExporter_Core_Registry::set($this->id, $this);
    }
    
    public function checkForIndices()
    {
        foreach($this->data->xpath("value[@key='indices']") as $key => $node){
            $this->indices = MwbExporter_Core_Registry::get('formatter')->createIndices($node);
        }
    }
    
    public function checkForForeignKeys()
    {
        foreach($this->data->xpath("value[@key='foreignKeys']") as $key => $node){
            $this->foreignKeys = MwbExporter_Core_Registry::get('formatter')->createForeignKeys($node);
        }
    }
    
    public function isTranslationTable()
    {
        $return = preg_match('@^(.*)\_translation$@', $this->getRawTableName(), $matches);
        if($return){
            $return = $matches[1];
        }
        return $return;
    }
    
    public function getRawTableName()
    {
        return $this->config['name'];
    }
    
    public function getModelName()
    {
        // camleCase under scores for model names
        return ucfirst(preg_replace('@\_(\w)@e', 'ucfirst("$1")', $this->getRawTableName()));
    }
    
    public function getModelNameInPlural()
    {
        return Helper_Pluralizer::pluralize($this->getModelName());
    }

    public function injectIndex(MwbExporter_Core_Model_Index $index)
    {
        $this->indexes[] = $index;
    }

    public function injectRelation(MwbExporter_Core_Model_ForeignKey $foreignKey)
    {
        $this->relations[] = $foreignKey;
    }
}