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

namespace MwbExporter\Formatter\Zend\DbTable\Model;

class Column extends \MwbExporter\Core\Model\Column
{
    /**
     *
     * @param SimpleXMLElement $data
     * @param type $parent 
     */
    public function __construct($data, $parent)
    {
        parent::__construct($data, $parent);
    }

    
    
    /**
     *
     * @return string 
     */
    public function display()
    {
        $return = array();

        // do not include columns that reflect references
        if(is_null($this->local)){
            // generate private $<column> class vars
            $return[] = '    /** ';

            $tmp = '     * ';
            if($this->isPrimary){
                $tmp .= '@Id ';
            }

            // set name of column
            $tmp  .= '@Column(type=' . \MwbExporter\Core\Registry::get('formatter')->useDatatypeConverter((isset($this->link['simpleType']) ? $this->link['simpleType'] : $this->link['userType']), $this);

            if(!isset($this->config['isNotNull']) || $this->config['isNotNull'] != 1){
                $tmp .= ',nullable=true';
            }
            $tmp .= ')'; // column definition ending bracket
            $return[] = $tmp;

            if(isset($this->config['autoIncrement']) && $this->config['autoIncrement'] == 1){
                $return[] = '     * @GeneratedValue(strategy="AUTO")';
            }

            $return[] = '     */';
            $return[] = '    private $' . $this->config['name'] . ';';
            $return[] = '';
        }

        // one to many references
        if(is_array($this->foreigns)){
            foreach($this->foreigns as $foreign){
                $return[] = '    /**';
                $return[] = '     * @OneToMany(targetEntity="' . $foreign->getOwningTable()->getModelName() . '", mappedBy="' . $foreign->getReferencedTable()->getModelName() . '")';
                $return[] = '     */';
                $return[] = '    private $' . lcfirst(\MwbExporter\Helper\Pluralizer::pluralize($foreign->getOwningTable()->getModelName())) . ';';
                $return[] = '';
            }
        }

        // many to references
        if(!is_null($this->local)){
            $return[] = '    /**';
            $return[] = '     * @ManyToOne(targetEntity="' . $this->local->getReferencedTable()->getModelName() . '", inversedBy="' . $this->local->getOwningTable()->getModelName() . '")';
            $return[] = '     */';
            $return[] = '    private $' . lcfirst($this->local->getReferencedTable()->getModelName()) . ';';
            $return[] = '';
        }
        
        /*
        // set default value
        if(isset($this->config['defaultValue']) && $this->config['defaultValue'] != '' && $this->config['defaultValue'] != 'NULL'){
            $return[] = '      default: ' . $this->config['defaultValue'];
        }

        // iterate on column flags
        foreach($this->data->xpath("value[@key='flags']/value") as $flag){
            $return[] = '      ' . strtolower($flag) . ': true';
        }
        */
        
        // return yaml representation of column
        return implode("\n", $return);
    }

    
    
    /**
     *
     * @return string|boolean 
     */
    public function displayArrayCollection()
    {
        if(is_array($this->foreigns)){
            foreach($this->foreigns as $foreign){
                return '        $this->' . lcfirst(\MwbExporter\Helper\Pluralizer::pluralize($foreign->getOwningTable()->getModelName())) . ' = new ArrayCollection();';
            }
        }
        
        return false;
    }
    
    
    
    /**
     *
     * @return string 
     */
    public function displayGetterAndSetter()
    {
        $return = array();

        // do not include getter and setter for columns that reflect references
        if(is_null($this->local)){
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
        }

        // one to many references
        if(is_array($this->foreigns)){
            foreach($this->foreigns as $foreign){
                $return[] = '    public function add' . $this->columnNameBeautifier($foreign->getOwningTable()->getModelName()) . '(' . $foreign->getOwningTable()->getModelName() . ' $' . lcfirst($foreign->getOwningTable()->getModelName()) . ')';
                $return[] = '    {';
                $return[] = '         $this->' . lcfirst(\MwbExporter\Helper\Pluralizer::pluralize($foreign->getOwningTable()->getModelName())) . '[] = $' . lcfirst($foreign->getOwningTable()->getModelName()) . ';';
                $return[] = '         return $this; // fluent interface';
                $return[] = '    }';
                $return[] = '';

                $return[] = '    public function get' . $this->columnNameBeautifier(\MwbExporter\Helper\Pluralizer::pluralize($foreign->getOwningTable()->getModelName())) . '()';
                $return[] = '    {';
                $return[] = '         return $this->' . lcfirst(\MwbExporter\Helper\Pluralizer::pluralize($foreign->getOwningTable()->getModelName())) . ';';
                $return[] = '    }';
                $return[] = '';
            }
        }

        // many to one references
        if(!is_null($this->local)){
            $return[] = '    public function set' . $this->columnNameBeautifier($this->local->getReferencedTable()->getModelName()) . '(' . $this->local->getReferencedTable()->getModelName() . ' $' . lcfirst($this->local->getReferencedTable()->getModelName()) . ')';
            $return[] = '    {';
            $return[] = '         $' . lcfirst($this->local->getReferencedTable()->getModelName()) . '->add' . $this->columnNameBeautifier($this->local->getOwningTable()->getModelName()) . '($this);';
            $return[] = '         $this->' . lcfirst($this->local->getReferencedTable()->getModelName()) . ' = $' . lcfirst($this->local->getReferencedTable()->getModelName()) . ';';
            $return[] = '         return $this; // fluent interface';
            $return[] = '    }';
            $return[] = '';

            $return[] = '    public function get' . $this->columnNameBeautifier($this->local->getReferencedTable()->getModelName()) . '()';
            $return[] = '    {';
            $return[] = '         return $this->' . lcfirst($this->local->getReferencedTable()->getModelName()) . ';';
            $return[] = '    }';
            $return[] = '';
        }

        return implode("\n", $return);
    }

    
    
    /**
     *
     * @param string $columnName
     * @return string 
     */
    protected function columnNameBeautifier($columnName)
    {
        return ucfirst(preg_replace('@\_(\w)@e', 'ucfirst("$1")', $columnName));
    }
}