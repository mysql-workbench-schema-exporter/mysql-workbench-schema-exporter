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
    protected $ormPrefix = '@';

    public function __construct($data, $parent)
    {
        parent::__construct($data, $parent);
    }

    /**
     * Return the table definition
     * Annotation format
     *
     * @return string
     */
    public function display()
    {
        $config = \MwbExporter\Core\Registry::get('config');

        $this->ormPrefix = '@' . ((isset($config['useAnnotationPrefix']) && $config['useAnnotationPrefix']) ? $config['useAnnotationPrefix'] : '');

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
        $namespace = '';
        $repositoryNamespace = '';
        if(isset($config['bundleNamespace']) && $config['bundleNamespace']){
            $namespace = $config['bundleNamespace'] . '\\';
        }

        if(isset($config['entityNamespace']) && $config['entityNamespace']){
            $namespace .= $config['entityNamespace'];
        } else {
            $namespace .= 'Entity';
        }

        if(isset($config['repositoryNamespace']) && $config['repositoryNamespace']){
            $repositoryNamespace = $config['repositoryNamespace'] . '\\';
        }

        $return[] = sprintf('namespace %s;', $namespace);
        $return[] = '';

        // Dirty fix, only in one case we need a use statement
        if($this->ormPrefix == '@ORM\\'){
            $return[] = 'use Doctrine\ORM\Mapping as ORM;';
            $return[] = '';
        }

        $return[] = '/**';

        $entity = ' * ' . $this->ormPrefix . 'Entity';
        if(isset($config['useAutomaticRepository']) && $config['useAutomaticRepository']){
            $entity .= '(repositoryClass="' . $repositoryNamespace . $this->getModelName() . 'Repository")';
        }
        $return[] = $entity;

        $tmp = ' * ' . $this->ormPrefix . 'Table(name="' . $this->getRawTableName() . '"';

        if(count($this->indexes) > 0){
            $tmp .= ',indexes={';

            foreach($this->indexes as $index){
                $tmp .= $this->ormPrefix . 'index(' . $index->display() . '),';
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

            if($this->isEnhancedManyToManyRelationDetection($relation)){
                continue;
            }

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
        // @TODO D2A ManyToMany relation joinColumns and inverseColumns
        // referencing wrong column names
        $return = array();

        foreach($this->manyToManyRelations as $relation){

            if($this->isEnhancedManyToManyRelationDetection($relation)){
                continue;
            }

            // if relation is not mapped yet define relation
            // otherwise use "mappedBy" feature
            if($relation['reference']->local->getColumnName() != $relation['reference']->getOwningTable()->getRelationToTable($relation['refTable']->getRawTableName())->local->getColumnName()){
                $return[] = $this->indentation() . '/**';
                $return[] = $this->indentation() . ' * ' . $this->ormPrefix . 'ManyToMany(targetEntity="' . $relation['refTable']->getModelName() . '")';
                $return[] = $this->indentation() . ' * ' . $this->ormPrefix . 'JoinTable(name="' . $relation['reference']->getOwningTable()->getRawTableName() . '",';
                $return[] = $this->indentation() . ' *      joinColumns={'        . $this->ormPrefix . 'JoinColumn(name="' . $relation['reference']->foreign->getColumnName() . '", referencedColumnName="' . $relation['reference']->local->getColumnName() . '")},';
                $return[] = $this->indentation() . ' *      inverseJoinColumns={' . $this->ormPrefix . 'JoinColumn(name="' . $relation['reference']->getOwningTable()->getRelationToTable($relation['refTable']->getRawTableName())->foreign->getColumnName() . '", referencedColumnName="' . $relation['reference']->getOwningTable()->getRelationToTable($relation['refTable']->getRawTableName())->local->getColumnName() . '")}';
                $return[] = $this->indentation() . ' *      )';
                $return[] = $this->indentation() . ' */';
            } else {
                $return[] = $this->indentation() . '/**';
                $return[] = $this->indentation() . ' * ' . $this->ormPrefix . 'ManyToMany(targetEntity="' . $relation['refTable']->getModelName() . '", mappedBy="' . lcfirst(\MwbExporter\Helper\Pluralizer::pluralize($this->getModelName())) . '")';
                $return[] = $this->indentation() . ' */';
            }
            $return[] = $this->indentation() . 'private $' . lcfirst(\MwbExporter\Helper\Pluralizer::pluralize($relation['refTable']->getModelName())) . ';';
        }
        $return[] = '';
        return implode("\n", $return);
    }

    protected function displayManyToManyGetterAndSetter()
    {
        $return = array();

        foreach($this->manyToManyRelations as $relation){

            if($this->isEnhancedManyToManyRelationDetection($relation)){
                continue;
            }

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

    /**
     *
     * @param array $relation
     * @return bool
     */
    protected function isEnhancedManyToManyRelationDetection($relation)
    {
        $config = \MwbExporter\Core\Registry::get('config');

        $enhancedManyToManyDetection = false;

        if(isset($config['enhancedManyToManyDetection']) && $config['enhancedManyToManyDetection']){
            $enhancedManyToManyDetection = (bool) $config['enhancedManyToManyDetection'];
        }

        if($enhancedManyToManyDetection == false){
            return false;
        }

        // ignore relation tables with more than two columns
        // if enhancedManyToMany config is set true
        // (it is most likely not an intended m2m relation)
        if($relation['reference']->getOwningTable()->countColumns() > 2){
            return true;
        }

        return false;
    }
}
