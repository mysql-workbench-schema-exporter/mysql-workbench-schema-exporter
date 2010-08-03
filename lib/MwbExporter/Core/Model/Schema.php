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

abstract class MwbExporter_Core_Model_Schema
{
    protected $data = null;
    protected $attributes = null;
    
    protected $id = null;
    
    protected $tables = null;
    protected $views = null;
    
    public function __construct($data)
    {
        $this->attributes = $data->attributes();
        $this->data       = $data;
        
        $this->id = (string) $this->attributes['id'];

        $tmp = $this->data->xpath("value[@key='tables']");
        $this->tables = MwbExporter_Core_Registry::get('formatter')->createTables($tmp[0]);

        $tmp = $this->data->xpath("value[@key='views']");
        $this->views  = MwbExporter_Core_Registry::get('formatter')->createViews($tmp[0]);
        
        MwbExporter_Core_Registry::set($this->id, $this);
    }

}