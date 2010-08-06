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

class MwbExporter_Formatter_Doctrine2_Annotation_Model_ForeignKey extends MwbExporter_Core_Model_ForeignKey
{
    public function __construct($data)
    {
        parent::__construct($data);
        
        $referencedColumn = $this->data->xpath("value[@key='referencedColumns']");
        $local = MwbExporter_Core_Registry::get((string) $referencedColumn[0]->link);

        $ownerColumn = $this->data->xpath("value[@key='columns']");
        $foreign = MwbExporter_Core_Registry::get((string) $ownerColumn[0]->link);
        
        // for doctrine2 annotations switch the local and the foreign
        // reference for a proper output
        $local->markAsForeignReference($this);
        $foreign->markAsLocalReference($this);
    }

    public function display()
    {
    }
}