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

abstract class Tables extends Base
{
    protected $tables = array();
    
    public function __construct($data, $parent)
    {
        parent::__construct($data, $parent);
        
        // collect tables
        foreach($this->data->xpath("value") as $key => $node){
            $tmp = \MwbExporter\Core\Registry::get('formatter')->createTable($node, $this);
            
            if($tmp->isTranslationTable()){
                // skip translation tables
                continue;
            }
            
            $this->tables[] = $tmp;
        }
        
        usort($this->tables, function($a, $b) {
           return strcmp($a->getModelName(), $b->getModelName()); 
        });
        
        /*
         * before you can check for foreign keys
         * you have to store at first all tables in the
         * object registry
         */
        foreach($this->tables as $table){
            $table->checkForIndices();
            $table->checkForForeignKeys();
        }
        
        \MwbExporter\Core\Registry::set($this->id, $this);
    }
    
    public function display()
    {
        $return = array();

        foreach($this->tables as $table){
            if (!$table->isExternal()) {
                $return[] = $table->display();
            }
        }

        return implode("\n", $return);
    }
    
    public function zipExport(\MwbExporter\Core\Helper\ZipFileExporter $zip)
    {
        foreach($this->tables as $table){
            $zip->addTable($table);
        }
        
        return $zip;
    }
}