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

class Document extends Base
{
    protected $data = null;
    protected $attributes = null;
    
    protected $id = null;
    
    protected $physicalModel = null;
    
    public function __construct($mwbFile, \MwbExporter\Core\IFormatter $formatter)
    {
        // load mwb simple_xml object
        $this->data = \MwbExporter\Core\Helper\Mwb::readXML($mwbFile);

        // save formatter in registry
        \MwbExporter\Core\Registry::set('formatter', $formatter);

        // save document in registry
        \MwbExporter\Core\Registry::set('document', $this);
        $this->parse();

        // save this object in registry by workebench id
        \MwbExporter\Core\Registry::set($this->id, $this);
    }
    
    protected function parse()
    {
        $this->attributes = $this->data->value->attributes();
        $this->data       = $this->data->value;
        
        $this->id = (string) $this->attributes['id'];

        $tmp = $this->data->xpath("value[@key='physicalModels']/value");
        $this->physicalModel = new \MwbExporter\Core\Model\PhysicalModel($tmp[0], $this);
    }
    
    public function display()
    {
        return $this->physicalModel->display();
    }
    
    public function zipExport($path = null, $format = 'php')
    {
        if($path === null){
            throw new Exception('missing path for zip export');
        }
        
        $path = realpath($path);
        
        $zip = new \MwbExporter\Core\Helper\ZipFileExporter($path);
        $zip->setSaveFormat($format);
        
        $zip = $this->physicalModel->zipExport($zip);
        
        $zip->save();
        
        return 'document zipped as ' . $zip->getFileName();
    }
}