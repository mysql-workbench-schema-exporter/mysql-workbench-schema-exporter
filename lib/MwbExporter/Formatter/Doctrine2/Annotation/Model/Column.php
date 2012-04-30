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

use MwbExporter\Core\Registry;
use MwbExporter\Core\Model\Column as Base;
use MwbExporter\Helper\Pluralizer;

class Column extends Base
{
    protected $ormPrefix = '@';

    public function __construct($data, $parent)
    {
        parent::__construct($data, $parent);
    }

    /**
     * @inheritDoc
     */
    protected function getRelationReferenceCount($fkey, $max = null)
    {
        $count = 0;
        $table = $this->getParent()->getParent();
        if (is_array($relations = $table->getManyToManyRelations())) {
            $tablename = $fkey->getOwningTable()->getRawTableName();
            foreach($relations as $relation) {
                // $relation key => reference (ForeignKey), refTable (Table)
                if ($this->checkReferenceTableName($relation['refTable'], $tablename) && !$relation['reference']->getOwningTable()->isManyToMany()) {
                    $count++;
                }
                if ($max && $count == $max) {
                    break;
                }
            }
        }

        return $count;
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
        $config = Registry::get('config');
        $this->ormPrefix = '@' . ((isset($config['useAnnotationPrefix']) && $config['useAnnotationPrefix']) ? $config['useAnnotationPrefix'] : '');

        $return[] = $this->indentation() . '/**';
        if($this->isPrimary){
            $return[] = $this->indentation() . ' * ' . $this->ormPrefix . 'Id';
        }
        $return[] = $this->indentation() . ' * ' . $this->ormPrefix . 'Column(type=' . Registry::get('formatter')->useDatatypeConverter($this) . (!isset($this->config['isNotNull']) || $this->config['isNotNull'] != 1 ? ', nullable=true' : '') . ')';
        if(isset($this->config['autoIncrement']) && $this->config['autoIncrement'] == 1){
            $return[] = $this->indentation() . ' * ' . $this->ormPrefix . 'GeneratedValue(strategy="AUTO")';
        }
        $return[] = $this->indentation() . ' */';
        $return[] = $this->indentation() . 'private $' . $this->config['name'] . ';';
        $return[] = '';

        return implode("\n", $return);
    }

    public function displayArrayCollection()
    {
        $return = array();
        if(is_array($this->foreigns)){
            foreach($this->foreigns as $foreign){
                if($foreign->isManyToOne()){ // is ManyToOne
                    $related = $this->getRelatedName($foreign);
                    $return[] = $this->indentation(2) . '$this->' . lcfirst(Pluralizer::pluralize($foreign->getOwningTable()->getModelName())) . $related . ' = new ArrayCollection();';
                } else { // is OneToOne
                }
            }
        }

        return implode("\n", $return);
    }

    public function displayRelations()
    {
        $return = array();

        // one to many references
        if(is_array($this->foreigns)){
            foreach($this->foreigns as $foreign){
                //check for OneToOne or OneToMany relationship
                if($foreign->isManyToOne()){ // is OneToMany
                    $related = $this->getRelatedName($foreign);
                    $return[] = $this->indentation() . '/**';
                    $return[] = $this->indentation() . ' * ' . $this->ormPrefix . 'OneToMany(targetEntity="' . $foreign->getOwningTable()->getModelName() . '", mappedBy="' . lcfirst($foreign->getReferencedTable()->getModelName()) . '")';
                    $return[] = $this->indentation() . ' * ' . $this->ormPrefix . 'JoinColumn(name="' . $foreign->foreign->getColumnName() . '", referencedColumnName="' . $foreign->local->getColumnName() . '")';
                    $return[] = $this->indentation() . ' */';
                    $return[] = $this->indentation() . 'private $' . lcfirst(Pluralizer::pluralize($foreign->getOwningTable()->getModelName())) . $related . ';';
                    $return[] = '';
                } else { // is OneToOne
                    $return[] = $this->indentation() . '/**';
                    $return[] = $this->indentation() . ' * ' . $this->ormPrefix . 'OneToOne(targetEntity="' . $foreign->getOwningTable()->getModelName() . '", mappedBy="' . lcfirst($foreign->getReferencedTable()->getModelName()) . '")';
                    $return[] = $this->indentation() . ' * ' . $this->ormPrefix . 'JoinColumn(name="' . $foreign->foreign->getColumnName() . '", referencedColumnName="' . $foreign->local->getColumnName() . '")';
                    $return[] = $this->indentation() . ' */';
                    $return[] = $this->indentation() . 'private $' . lcfirst($foreign->getOwningTable()->getModelName()) . ';';
                    $return[] = '';
                }
            }
        }

        // many to references
        if(null !== $this->local){
            //check for OneToOne or ManyToOne relationship
            if($this->local->isManyToOne()){ // is ManyToOne
                $related = $this->getManyToManyRelatedName($this->local->getReferencedTable()->getRawTableName(), $this->local->foreign->getColumnName());
                $return[] = $this->indentation() . '/**';
                $return[] = $this->indentation() . ' * ' . $this->ormPrefix . 'ManyToOne(targetEntity="' . $this->local->getReferencedTable()->getModelName() . '", inversedBy="' . lcfirst(Pluralizer::pluralize($this->local->getOwningTable()->getModelName())) . '")';
                $return[] = $this->indentation() . ' * ' . $this->ormPrefix . 'JoinColumn(name="' . $this->local->foreign->getColumnName() . '", referencedColumnName="' . $this->local->local->getColumnName() . '")';
                $return[] = $this->indentation() . ' */';
                $return[] = $this->indentation() . 'private $' . lcfirst($this->local->getReferencedTable()->getModelName()) . $related . ';';
                $return[] = '';
            } else { // is OneToOne
                $return[] = $this->indentation() . '/**';
                $return[] = $this->indentation() . ' * ' . $this->ormPrefix . 'OneToOne(targetEntity="' . $this->local->getReferencedTable()->getModelName() . '", inversedBy="' . lcfirst($this->local->getOwningTable()->getModelName()) . '")';
                $return[] = $this->indentation() . ' * ' . $this->ormPrefix . 'JoinColumn(name="' . $this->local->foreign->getColumnName() . '", referencedColumnName="' . $this->local->local->getColumnName() . '")';
                $return[] = $this->indentation() . ' */';
                $return[] = $this->indentation() . 'private $' . lcfirst($this->local->getReferencedTable()->getModelName()) . ';';
                $return[] = '';
            }
        }

        return implode("\n", $return);
    }

    /**
     * Getters and setters for the entity attributes
     *
     * @return string
     */
    public function displayGetterAndSetter()
    {
        $return = array();
        $table = $this->getParent()->getParent();
        $converter = Registry::get('formatter')->getDatatypeConverter();
        $nativeType = $converter->getNativeType($converter->getMappedType($this));

        // setter
        $return[] = $this->indentation() . '/**';
        $return[] = $this->indentation() . ' * Set the value of ' . $this->getColumnName() . '.';
        $return[] = $this->indentation() . ' *';
        $return[] = $this->indentation() . ' * @param ' . $nativeType . ' $' . $this->config['name'];
        $return[] = $this->indentation() . ' * @return ' . $table->getNamespace();
        $return[] = $this->indentation() . ' */';
        $return[] = $this->indentation() . 'public function set' . $this->columnNameBeautifier($this->config['name']) . '($' . $this->config['name'] . ')';
        $return[] = $this->indentation() . '{';
        $return[] = $this->indentation(2) . '$this->' . $this->config['name'] . ' = $' . $this->config['name'] . ';';
        $return[] = '';
        $return[] = $this->indentation(2) . 'return $this;';
        $return[] = $this->indentation() . '}';
        $return[] = '';
        // getter
        $return[] = $this->indentation() . '/**';
        $return[] = $this->indentation() . ' * Get the value of ' . $this->getColumnName() . '.';
        $return[] = $this->indentation() . ' *';
        $return[] = $this->indentation() . ' * @return ' . $nativeType;
        $return[] = $this->indentation() . ' */';
        $return[] = $this->indentation() . 'public function get' . $this->columnNameBeautifier($this->config['name']) . '()';
        $return[] = $this->indentation() . '{';
        $return[] = $this->indentation(2) . 'return $this->' . $this->config['name'] . ';';
        $return[] = $this->indentation() . '}';
        $return[] = '';

        return implode("\n", $return);
    }

    /**
     * Getters and setters for relations.
     *
     * @return string
     */
    public function displayRelationsGetterAndSetter()
    {
        $return = array();
        $table = $this->getParent()->getParent();

        // one to many references
        if(is_array($this->foreigns)){
            foreach($this->foreigns as $foreign){
                if($foreign->isManyToOne()){ // is ManyToOne
                    $related = $this->getRelatedName($foreign);
                    $related_text = $this->getRelatedName($foreign, false);
                    // setter
                    $return[] = $this->indentation() . '/**';
                    $return[] = $this->indentation() . ' * Add ' . trim($foreign->getOwningTable()->getModelName() . ' ' . $related_text). ' entity to collection (one to many).';
                    $return[] = $this->indentation() . ' *';
                    $return[] = $this->indentation() . ' * @param ' . $foreign->getOwningTable()->getNamespace() . ' $' . lcfirst($foreign->getOwningTable()->getModelName());
                    $return[] = $this->indentation() . ' * @return ' . $table->getNamespace();
                    $return[] = $this->indentation() . ' */';
                    $return[] = $this->indentation() . 'public function add' . $this->columnNameBeautifier($foreign->getOwningTable()->getModelName()) . $related . '(' . $foreign->getOwningTable()->getModelName() . ' $' . lcfirst($foreign->getOwningTable()->getModelName()) . ')';
                    $return[] = $this->indentation() . '{';
                    $return[] = $this->indentation(2) . '$this->' . lcfirst(Pluralizer::pluralize($foreign->getOwningTable()->getModelName())) . $related . '[] = $' . lcfirst($foreign->getOwningTable()->getModelName()) . ';';
                    $return[] = '';
                    $return[] = $this->indentation(2) . 'return $this;';
                    $return[] = $this->indentation() . '}';
                    $return[] = '';
                    // getter
                    $return[] = $this->indentation() . '/**';
                    $return[] = $this->indentation() . ' * Get ' . trim($foreign->getOwningTable()->getModelName() . ' ' . $related_text) . ' entity collection (one to many).';
                    $return[] = $this->indentation() . ' *';
                    $return[] = $this->indentation() . ' * @return ' . $table->getArrayClass();
                    $return[] = $this->indentation() . ' */';
                    $return[] = $this->indentation() . 'public function get' . $this->columnNameBeautifier(Pluralizer::pluralize($foreign->getOwningTable()->getModelName())) . $related . '()';
                    $return[] = $this->indentation() . '{';
                    $return[] = $this->indentation(2) . 'return $this->' . lcfirst(Pluralizer::pluralize($foreign->getOwningTable()->getModelName())) . $related . ';';
                    $return[] = $this->indentation() . '}';
                } else { // OneToOne
                    // setter
                    $return[] = $this->indentation() . '/**';
                    $return[] = $this->indentation() . ' * Set ' . $foreign->getOwningTable()->getModelName() . ' entity (one to one).';
                    $return[] = $this->indentation() . ' *';
                    $return[] = $this->indentation() . ' * @param ' . $foreign->getOwningTable()->getNamespace() . ' $' . lcfirst($foreign->getOwningTable()->getModelName());
                    $return[] = $this->indentation() . ' * @return ' . $table->getNamespace();
                    $return[] = $this->indentation() . ' */';
                    $return[] = $this->indentation() . 'public function set' . $this->columnNameBeautifier($foreign->getOwningTable()->getModelName()) . '(' . $foreign->getOwningTable()->getModelName() . ' $' . lcfirst($foreign->getOwningTable()->getModelName()) . ')';
                    $return[] = $this->indentation() . '{';
                    $return[] = $this->indentation(2) . '$this->' . lcfirst($foreign->getOwningTable()->getModelName()) . ' = $' . lcfirst($foreign->getOwningTable()->getModelName()) . ';';
                    $return[] = '';
                    $return[] = $this->indentation(2) . 'return $this;';
                    $return[] = $this->indentation() . '}';
                    $return[] = '';
                    // getter
                    $return[] = $this->indentation() . '/**';
                    $return[] = $this->indentation() . ' * Get ' . $foreign->getOwningTable()->getModelName() . ' entity (one to one).';
                    $return[] = $this->indentation() . ' *';
                    $return[] = $this->indentation() . ' * @return ' . $foreign->getOwningTable()->getNamespace();
                    $return[] = $this->indentation() . ' */';
                    $return[] = $this->indentation() . 'public function get' . $this->columnNameBeautifier($foreign->getOwningTable()->getModelName()) . '()';
                    $return[] = $this->indentation() . '{';
                    $return[] = $this->indentation(2) . 'return $this->' . lcfirst($foreign->getOwningTable()->getModelName()) . ';';
                    $return[] = $this->indentation() . '}';
                }
                $return[] = '';
            }
        }

        // many to one references
        if(null !== $this->local){
            if($this->local->isManyToOne()){ // is ManyToOne
                $related = $this->getManyToManyRelatedName($this->local->getReferencedTable()->getRawTableName(), $this->local->foreign->getColumnName());
                $related_text = $this->getManyToManyRelatedName($this->local->getReferencedTable()->getRawTableName(), $this->local->foreign->getColumnName(), false);
                // setter
                $return[] = $this->indentation() . '/**';
                $return[] = $this->indentation() . ' * Set ' . trim($this->local->getReferencedTable()->getModelName() . ' ' . $related_text) . ' entity (many to one).';
                $return[] = $this->indentation() . ' *';
                $return[] = $this->indentation() . ' * @param ' . $this->local->getReferencedTable()->getNamespace() . ' $' . lcfirst($this->local->getReferencedTable()->getModelName());
                $return[] = $this->indentation() . ' * @return ' . $table->getNamespace();
                $return[] = $this->indentation() . ' */';
                $return[] = $this->indentation() . 'public function set' . $this->columnNameBeautifier($this->local->getReferencedTable()->getModelName()) . $related . '(' . $this->local->getReferencedTable()->getModelName() . ' $' . lcfirst($this->local->getReferencedTable()->getModelName()) . ')';
                $return[] = $this->indentation() . '{';
                $return[] = $this->indentation(2) . '$' . lcfirst($this->local->getReferencedTable()->getModelName()) . '->add' . $this->columnNameBeautifier($this->local->getOwningTable()->getModelName()) . $related . '($this);';
                $return[] = $this->indentation(2) . '$this->' . lcfirst($this->local->getReferencedTable()->getModelName()) . $related . ' = $' . lcfirst($this->local->getReferencedTable()->getModelName()) . ';';
                $return[] = '';
                $return[] = $this->indentation(2) . 'return $this;';
                $return[] = $this->indentation() . '}';
                $return[] = '';
                // getter
                $return[] = $this->indentation() . '/**';
                $return[] = $this->indentation() . ' * Get ' . trim($this->local->getReferencedTable()->getModelName() . ' ' . $related_text) . ' entity (many to one).';
                $return[] = $this->indentation() . ' *';
                $return[] = $this->indentation() . ' * @return ' . $this->local->getReferencedTable()->getNamespace();
                $return[] = $this->indentation() . ' */';
                $return[] = $this->indentation() . 'public function get' . $this->columnNameBeautifier($this->local->getReferencedTable()->getModelName()) . $related . '()';
                $return[] = $this->indentation() . '{';
                $return[] = $this->indentation(2) . 'return $this->' . lcfirst($this->local->getReferencedTable()->getModelName()) . $related . ';';
                $return[] = $this->indentation() . '}';
                $return[] = '';
            } else { // OneToOne
                // setter
                $return[] = $this->indentation() . '/**';
                $return[] = $this->indentation() . ' * Set ' . $this->local->getReferencedTable()->getModelName() . ' entity (one to one).';
                $return[] = $this->indentation() . ' *';
                $return[] = $this->indentation() . ' * @param ' . $this->local->getReferencedTable()->getNamespace() . ' $' . lcfirst($this->local->getReferencedTable()->getModelName());
                $return[] = $this->indentation() . ' * @return ' . $table->getNamespace();
                $return[] = $this->indentation() . ' */';
                $return[] = $this->indentation() . 'public function set' . $this->columnNameBeautifier($this->local->getReferencedTable()->getModelName()) . '(' . $this->local->getReferencedTable()->getModelName() . ' $' . lcfirst($this->local->getReferencedTable()->getModelName()) . ')';
                $return[] = $this->indentation() . '{';
                $return[] = $this->indentation(2) . '$' . lcfirst($this->local->getReferencedTable()->getModelName()) . '->set' . $this->columnNameBeautifier($this->local->getOwningTable()->getModelName()) . '($this);';
                $return[] = $this->indentation(2) . '$this->' . lcfirst($this->local->getReferencedTable()->getModelName()) . ' = $' . lcfirst($this->local->getReferencedTable()->getModelName()) . ';';
                $return[] = '';
                $return[] = $this->indentation(2) . 'return $this;';
                $return[] = $this->indentation() . '}';
                $return[] = '';
                // getter
                $return[] = $this->indentation() . '/**';
                $return[] = $this->indentation() . ' * Get ' . $this->local->getReferencedTable()->getModelName() . ' entity (one to one).';
                $return[] = $this->indentation() . ' *';
                $return[] = $this->indentation() . ' * @return ' . $this->local->getReferencedTable()->getNamespace();
                $return[] = $this->indentation() . ' */';
                $return[] = $this->indentation() . 'public function get' . $this->columnNameBeautifier($this->local->getReferencedTable()->getModelName()) . '()';
                $return[] = $this->indentation() . '{';
                $return[] = $this->indentation(2) . 'return $this->' . lcfirst($this->local->getReferencedTable()->getModelName()) . ';';
                $return[] = $this->indentation() . '}';
                $return[] = '';
            }
        }

        return implode("\n", $return);
    }
}
