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

class MwbExporter_Formatter_Doctrine2_Annotation_Model_Column extends MwbExporter_Core_Model_Column
{
    public function __construct($data)
    {
        parent::__construct($data);
    }

    public function display()
    {
        $return = array();

        $return[] = '    /** ';

        $tmp = '     * ';
        if($this->isPrimary){
            $tmp .= '@Id ';
        }

        // set name of column
        $tmp  .= '@Column(type=' . MwbExporter_Core_Registry::get('formatter')->useDatatypeConverter((isset($this->link['simpleType']) ? $this->link['simpleType'] : $this->link['userType']), $this);

        if(!isset($this->config['isNotNull']) || $this->config['isNotNull'] != 1){
            $tmp .= ',nullable=true';
        }
        $tmp .= ')'; // column definition ending bracket
        $return[] = $tmp;
        
        if(isset($this->config['autoIncrement']) && $this->config['autoIncrement'] == 1){
            $return[] = '     * @GeneratedValue';
        }
        
        $return[] = '     */';
        $return[] = '    private $' . $this->config['name'] . ';';

        // one to many references
        if(!is_null($this->foreign)){
            $return[] = '    /**';
            $return[] = '     * @OneToMany(targetEntity="' . $this->foreign->getOwningTable()->getModelName() . '", mappedBy="' . $this->foreign->getReferencedTable()->getModelName() . '")';
            $return[] = '     */';
            $return[] = '    private $' . lcfirst(MwbExporter_Helper_Pluralizer::pluralize($this->foreign->getOwningTable()->getModelName())) . ';';
        }
        
        /*
        // set datatype of column
        $return[] = '      type: ' . Wb_DatatypeConverter::getType((isset($this->link['simpleType']) ? $this->link['simpleType'] : $this->link['userType']), $this);

        if($this->isPrimary){
            $return[] = '      primary: true';
        }

        // check for auto increment column
        if(isset($this->config['autoIncrement']) && $this->config['autoIncrement'] == 1){
            $return[] = '      autoincrement: true';
        }

        // set default value
        if(isset($this->config['defaultValue']) && $this->config['defaultValue'] != '' && $this->config['defaultValue'] != 'NULL'){
            $return[] = '      default: ' . $this->config['defaultValue'];
        }

        // check for not null column
        if(isset($this->config['isNotNull']) && $this->config['isNotNull'] == 1){
            $return[] = '      notnull: true';
        }

        // iterate on column flags
        foreach($this->data->xpath("value[@key='flags']/value") as $flag){
            $return[] = '      ' . strtolower($flag) . ': true';
        }
        */
        
        // return yaml representation of column
        return implode("\n", $return);
    }

    public function displayGetterAndSetter()
    {
        $return = array();

        $return[] = '    public function set' . $this->columnNameBeautifier($this->config['name']) . '($' . $this->config['name'] . ')';
        $return[] = '    {';
        $return[] = '         $this->' . $this->config['name'] . ' = $' . $this->config['name'] . ';';
        $return[] = '         return $this; // fluent interface';
        $return[] = '    }';
        $return[] = '';

        $return[] = '    public function get' . $this->columnNameBeautifier($this->config['name']) . '()';
        $return[] = '    {';
        $return[] = '         return $this->' . $this->config['name'] . ';';
        $return[] = '    }';
        $return[] = '';

        return implode("\n", $return);
    }

    protected function columnNameBeautifier($columnName)
    {
        return ucfirst(preg_replace('@\_(\w)@e', 'ucfirst("$1")', $columnName));
    }
}