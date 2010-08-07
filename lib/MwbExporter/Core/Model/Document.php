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

class MwbExporter_Core_Model_Document extends MwbExporter_Core_Model_Base
{
    protected $data = null;
    protected $attributes = null;
    
    protected $id = null;
    
    protected $physicalModel = null;
    
    public function __construct($mwbFile, MwbExporter_Core_IFormatter $formatter)
    {
        // load mxb xml file
        $this->data = simplexml_load_string(MwbExporter_Core_Helper_Mwb::read($mwbFile));

        // save formatter in registry
        MwbExporter_Core_Registry::set('formatter', $formatter);

        // save document in registry
        MwbExporter_Core_Registry::set('document', $this);
        $this->parse();

        // save this object in registry by workebench id
        MwbExporter_Core_Registry::set($this->id, $this);
    }
    
    protected function parse()
    {
        $this->attributes = $this->data->value->attributes();
        $this->data       = $this->data->value;
        
        $this->id = (string) $this->attributes['id'];

        $tmp = $this->data->xpath("value[@key='physicalModels']/value");
        $this->physicalModel = new MwbExporter_Core_Model_PhysicalModel($tmp[0]);
    }
    
    public function getData()
    {
        return $this->data;
    }
    
    public function display()
    {
        return $this->physicalModel->display();
    }
}