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

class MwbExporter_Formatter_Doctrine2_Annotation_Model_Table extends MwbExporter_Core_Model_Table
{
    public function __construct($data)
    {
        parent::__construct($data);
    }

    public function display()
    {
        $return = array();

        $return[] = '<?php';
        $return[] = '';
        $return[] = 'namespace Models;';
        $return[] = '';
        $return[] = '/**';
        $return[] = ' * @Entity';
        $tmp = ' * @Table(name="' . $this->getRawTableName() . '"';

        if(count($this->indexes) > 0){
            $tmp .= ',indexes={';

            foreach($this->indexes as $index){
                $tmp .= '@index(' . $index->display() . '),';
            }
            $tmp = substr($tmp, 0, -1);
            $tmp .= '}';
        }

        $return[] = $tmp . ')';
        $return[] = ' */';
        $return[] = 'class MwbExporter_' . $this->getModelName();
        $return[] = '{';

        $return[] = $this->columns->display();

        $return[] = '}';
        $return[] = '?>';
        return implode("\n", $return);
    }
}