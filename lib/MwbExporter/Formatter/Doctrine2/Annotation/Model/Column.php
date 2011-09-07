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

namespace MwbExporter\Formatter\Doctrine2\Annotation\Model;

class Column extends \MwbExporter\Core\Model\Column
{
    protected $ormPrefix = '@';

    public function __construct($data, $parent)
    {
        parent::__construct($data, $parent);
    }

    /**
     * Return the column definition
     * Annotation format
     *
     * @return string
     */
    public function display()
    {
        $return = array();
        
        $config = \MwbExporter\Core\Registry::get('config');
        
        /**
         * if needed, use a prefix (like @ORM\ or @orm:
         * for symfony2
         * @ by default
         */
        $this->ormPrefix = '@' . ((isset($config['useAnnotationPrefix']) && $config['useAnnotationPrefix']) ? $config['useAnnotationPrefix'] : '');

        // do not include columns that reflect references
        if(is_null($this->local))
        {
            // generate private $<column> class vars
            $return[] = $this->indentation() . '/** ';

            /**
             * checking if the current column is a primary key
             */
            $tmp = $this->indentation() . ' * ';
            if($this->isPrimary){
                $tmp .= $this->ormPrefix . 'Id ';
            }
            $return[] = $tmp;

            // set name of column
            $tmp = $this->indentation() . ' * ';
            $tmp  .= $this->ormPrefix . 'Column(type=' . \MwbExporter\Core\Registry::get('formatter')->useDatatypeConverter((isset($this->link['simpleType']) ? $this->link['simpleType'] : $this->link['userType']), $this);

            if(!isset($this->config['isNotNull']) || $this->config['isNotNull'] != 1){
                $tmp .= ',nullable=true';
            }
            $tmp .= ')'; // column definition ending bracket
            $return[] = $tmp;

            /**
             * adding the auto increment statement
             */
            if(isset($this->config['autoIncrement']) && $this->config['autoIncrement'] == 1){
                $return[] = $this->indentation() . ' * ' . $this->ormPrefix . 'GeneratedValue(strategy="AUTO")';
            }

            $return[] = $this->indentation() . ' */';
            $return[] = $this->indentation() . 'private $' . $this->config['name'] . ';';
            $return[] = '';
        }

        // one to many references
        if(is_array($this->foreigns)){
            foreach($this->foreigns as $foreign){
                //check for OneToOne or OneToMany relationship
                if(intval($foreign->getAttribute('many')) == 1){ // is OneToMany
                    $return[] = $this->indentation() . '/**';
                    $return[] = $this->indentation() . ' * ' . $this->ormPrefix . 'OneToMany(targetEntity="' . $foreign->getOwningTable()->getModelName() . '", mappedBy="' . lcfirst($foreign->getReferencedTable()->getModelName()) . '")';
                    $return[] = $this->indentation() . ' */';
                    //$return[] = $this->indentation() . 'private $' . lcfirst(\MwbExporter\Helper\Pluralizer::pluralize(preg_replace('~\_id$~', '', $this->config['name']))) . ';';
                    $return[] = $this->indentation() . 'private $' . lcfirst(\MwbExporter\Helper\Pluralizer::pluralize($foreign->getOwningTable()->getModelName())) . ';';
                    $return[] = '';
                } else { // is OneToOne
                    $return[] = $this->indentation() . '/**';
                    $return[] = $this->indentation() . ' * ' . $this->ormPrefix . 'OneToOne(targetEntity="' . $foreign->getOwningTable()->getModelName() . '", mappedBy="' . lcfirst($foreign->getReferencedTable()->getModelName()) . '")';
                    $return[] = $this->indentation() . ' */';
                    //$return[] = $this->indentation() . 'private $' . lcfirst(\MwbExporter\Helper\Pluralizer::pluralize(preg_replace('~\_id$~', '', $this->config['name']))) . ';';
                    $return[] = $this->indentation() . 'private $' . lcfirst($foreign->getOwningTable()->getModelName()) . ';';
                    $return[] = '';
                }
            }
        }

        // many to references
        if(!is_null($this->local)){
            //check for OneToOne or ManyToOne relationship
            if(intval($this->local->getAttribute('many')) == 1){ // is ManyToOne
                $return[] = $this->indentation() . '/**';
                $return[] = $this->indentation() . ' * ' . $this->ormPrefix . 'ManyToOne(targetEntity="' . $this->local->getReferencedTable()->getModelName() . '", inversedBy="' . lcfirst(\MwbExporter\Helper\Pluralizer::pluralize($this->local->getOwningTable()->getModelName())) . '")';
                $return[] = $this->indentation() . ' */';
                //$return[] = $this->indentation() . 'private $' . preg_replace('~\_id$~', '', $this->config['name']) . ';';
                $return[] = $this->indentation() . 'private $' . lcfirst($this->local->getReferencedTable()->getModelName()) . ';';
                $return[] = '';
            } else { // is OneToOne
                $return[] = $this->indentation() . '/**';
                $return[] = $this->indentation() . ' * ' . $this->ormPrefix . 'OneToOne(targetEntity="' . $this->local->getReferencedTable()->getModelName() . '", inversedBy="' . lcfirst($this->local->getOwningTable()->getModelName()) . '")';
                $return[] = $this->indentation() . ' */';
                //$return[] = $this->indentation() . 'private $' . preg_replace('~\_id$~', '', $this->config['name']) . ';';
                $return[] = $this->indentation() . 'private $' . lcfirst($this->local->getReferencedTable()->getModelName()) . ';';
                $return[] = '';
            }
        }

        /*
        // set default value
        if(isset($this->config['defaultValue']) && $this->config['defaultValue'] != '' && $this->config['defaultValue'] != 'NULL'){
            $return[] = $this->indentation() . '  default: ' . $this->config['defaultValue'];
        }

        // iterate on column flags
        foreach($this->data->xpath("value[@key='flags']/value") as $flag){
            $return[] = $this->indentation() . '  ' . strtolower($flag) . ': true';
        }
        */

        // return yaml representation of column
        return implode("\n", $return);
    }

    public function displayArrayCollection()
    {
        if(is_array($this->foreigns)){
            foreach($this->foreigns as $foreign){
                return $this->indentation(2) . '$this->' . lcfirst(\MwbExporter\Helper\Pluralizer::pluralize($foreign->getOwningTable()->getModelName())) . ' = new ArrayCollection();';
            }
        }

        return false;
    }

    /**
     * Getters and setters for the entity attributes
     *
     * @return string
     */
    public function displayGetterAndSetter()
    {
        $return = array();

        // do not include getter and setter for columns that reflect references
        if(is_null($this->local)){
            $return[] = $this->indentation() . 'public function set' . $this->columnNameBeautifier($this->config['name']) . '($' . $this->config['name'] . ')';
            $return[] = $this->indentation() . '{';
            $return[] = $this->indentation(2) . '$this->' . $this->config['name'] . ' = $' . $this->config['name'] . ';';
            $return[] = $this->indentation(2) . 'return $this; // fluent interface';
            $return[] = $this->indentation() . '}';
            $return[] = '';

            $return[] = $this->indentation() . 'public function get' . $this->columnNameBeautifier($this->config['name']) . '()';
            $return[] = $this->indentation() . '{';
            $return[] = $this->indentation(2) . 'return $this->' . $this->config['name'] . ';';
            $return[] = $this->indentation() . '}';
            $return[] = '';
        }

        // one to many references
        if(is_array($this->foreigns)){
            foreach($this->foreigns as $foreign){
                $return[] = $this->indentation() . 'public function add' . $this->columnNameBeautifier($foreign->getOwningTable()->getModelName()) . '(' . $foreign->getOwningTable()->getModelName() . ' $' . lcfirst($foreign->getOwningTable()->getModelName()) . ')';
                $return[] = $this->indentation() . '{';
                $return[] = $this->indentation(2) . '$this->' . lcfirst(\MwbExporter\Helper\Pluralizer::pluralize($foreign->getOwningTable()->getModelName())) . '[] = $' . lcfirst($foreign->getOwningTable()->getModelName()) . ';';
                $return[] = $this->indentation(2) . 'return $this; // fluent interface';
                $return[] = $this->indentation() . '}';
                $return[] = '';

                $return[] = $this->indentation() . 'public function get' . $this->columnNameBeautifier(\MwbExporter\Helper\Pluralizer::pluralize($foreign->getOwningTable()->getModelName())) . '()';
                $return[] = $this->indentation() . '{';
                $return[] = $this->indentation(2) . 'return $this->' . lcfirst(\MwbExporter\Helper\Pluralizer::pluralize($foreign->getOwningTable()->getModelName())) . ';';
                $return[] = $this->indentation() . '}';
                $return[] = '';
            }
        }

        // many to one references
        if(!is_null($this->local)){
            $return[] = $this->indentation() . 'public function set' . $this->columnNameBeautifier($this->local->getReferencedTable()->getModelName()) . '(' . $this->local->getReferencedTable()->getModelName() . ' $' . lcfirst($this->local->getReferencedTable()->getModelName()) . ')';
            $return[] = $this->indentation() . '{';
            $return[] = $this->indentation(2) . '$' . lcfirst($this->local->getReferencedTable()->getModelName()) . '->add' . $this->columnNameBeautifier($this->local->getOwningTable()->getModelName()) . '($this);';
            $return[] = $this->indentation(2) . '$this->' . lcfirst($this->local->getReferencedTable()->getModelName()) . ' = $' . lcfirst($this->local->getReferencedTable()->getModelName()) . ';';
            $return[] = $this->indentation(2) . 'return $this; // fluent interface';
            $return[] = $this->indentation() . '}';
            $return[] = '';

            $return[] = $this->indentation() . 'public function get' . $this->columnNameBeautifier($this->local->getReferencedTable()->getModelName()) . '()';
            $return[] = $this->indentation() . '{';
            $return[] = $this->indentation(2) . 'return $this->' . lcfirst($this->local->getReferencedTable()->getModelName()) . ';';
            $return[] = $this->indentation() . '}';
            $return[] = '';
        }

        return implode("\n", $return);
    }

    protected function columnNameBeautifier($columnName)
    {
        return ucfirst(preg_replace('@\_(\w)@e', 'ucfirst("$1")', $columnName));
    }
}
