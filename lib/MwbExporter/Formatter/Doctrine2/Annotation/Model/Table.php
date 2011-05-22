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

class Table extends \MwbExporter\Core\Model\Table
{
    protected $manyToManyRelations = array();

    public function __construct($data, $parent)
    {
        parent::__construct($data, $parent);
    }

    public function display()
    {
        $config = \MwbExporter\Core\Registry::get('config');

        // add relations
        if(count($this->relations) > 0){
            foreach($this->relations as $relation){
                $relation->display();
            }
        }

        // add indices
        if(count($this->indexes) > 0){
            foreach($this->indexes as $index){
                $index->display();
            }
        }

        $return = array();

        $return[] = '<?php';
        $return[] = '';
        $return[] = 'namespace Models;';
        $return[] = '';
        $return[] = '/**';

        $entity = ' * @Entity';
        if(isset($config['useAutomaticRepository']) && $config['useAutomaticRepository']) {
            $entity .= '(repositoryClass="Entity\\' . $this->getModelName() . 'Repository")';
        }
        $return[] = $entity;

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
        $return[] = 'class ' . $this->getModelName();
        $return[] = '{';

        $return[] = $this->columns->display();
        $return[] = $this->displayManyToMany();
        $return[] = $this->displayConstructor();
        $return[] = '';
        $return[] = $this->columns->displayGetterAndSetter();
        $return[] = $this->displayManyToManyGetterAndSetter();

        $return[] = '}';
        $return[] = '';
        return implode("\n", $return);
    }

    public function displayConstructor()
    {
        $return = array();
        $return[] = $this->indentation() . 'public function __construct()';
        $return[] = $this->indentation() . '{';
        $return[] = $this->columns->displayArrayCollections();
        foreach($this->manyToManyRelations as $relation){
            $return[] = $this->indentation(2) . '$this->' . lcfirst(\MwbExporter\Helper\Pluralizer::pluralize($relation['refTable']->getModelName())) . ' = new ArrayCollection();';
        }
        $return[] = $this->indentation() . '}';
        $return[] = '';

        return implode("\n", $return);
    }

    public function setManyToManyRelation(Array $rel)
    {
        $key = $rel['refTable']->getModelName();
        $this->manyToManyRelations[$key] = $rel;
    }

    protected function displayManyToMany()
    {
        // @TODO D2A ManyToMany relation
        $return = array();

        foreach($this->manyToManyRelations as $relation){
            $return[] = $this->indentation() . '/**';
            $return[] = $this->indentation() . ' * @ManyToMany(targetEntity="' . $relation['refTable']->getModelName() . '")';
            $return[] = $this->indentation() . ' * @JoinTable(name="' . $relation['reference']->getOwningTable()->getRawTableName() . '",';
            $return[] = $this->indentation() . ' *      joinColumns={@JoinColumn(name="' . $relation['reference']->foreign->getColumnName() . '", referencedColumnName="' . $relation['reference']->local->getColumnName() . '")},';
            $return[] = $this->indentation() . ' *      inverseJoinColumns={@JoinColumn(name="' . $relation['reference']->getOwningTable()->getRelationToTable($relation['refTable']->getRawTableName())->foreign->getColumnName() . '", referencedColumnName="id")}';
            $return[] = $this->indentation() . ' *      )';
            $return[] = $this->indentation() . ' */';
            $return[] = $this->indentation() . 'private $' . lcfirst(\MwbExporter\Helper\Pluralizer::pluralize($relation['refTable']->getModelName())) . ';';
        }
        $return[] = '';
        return implode("\n", $return);
    }

    protected function displayManyToManyGetterAndSetter()
    {
        $return = array();

        foreach($this->manyToManyRelations as $relation){
            $return[] = $this->indentation() . 'public function add' . $relation['refTable']->getModelName() . '(' . $relation['refTable']->getModelName() . ' $' . lcfirst($relation['refTable']->getModelName()) . ')';
            $return[] = $this->indentation() . '{';
            $return[] = $this->indentation(2) . '$' . lcfirst($relation['refTable']->getModelName()) . '->add' . $this->getModelName() . '($this);';
            $return[] = $this->indentation(2) . '$this->' . lcfirst(\MwbExporter\Helper\Pluralizer::pluralize($relation['refTable']->getModelName())) . '[] = $' . lcfirst($relation['refTable']->getModelName()) . ';';
            $return[] = $this->indentation(2) . 'return $this;';
            $return[] = $this->indentation() . '}';
            $return[] = '';
            $return[] = $this->indentation() . 'public function get' . \MwbExporter\Helper\Pluralizer::pluralize($relation['refTable']->getModelName()) . '()';
            $return[] = $this->indentation() . '{';
            $return[] = $this->indentation(2) . 'return $this->' . lcfirst(\MwbExporter\Helper\Pluralizer::pluralize($relation['refTable']->getModelName())) . ';';
            $return[] = $this->indentation() . '}';
        }

        return implode("\n", $return);
    }
}
