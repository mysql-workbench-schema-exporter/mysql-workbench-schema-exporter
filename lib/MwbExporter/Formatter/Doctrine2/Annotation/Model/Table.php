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
use MwbExporter\Core\Model\Table as Base;
use MwbExporter\Helper\Pluralizer;

class Table extends Base
{
    protected $manyToManyRelations = array();
    protected $ormPrefix = '@';
    protected $namespace = null;
    protected $arrayClass = 'Doctrine\Common\Collections\ArrayCollection';

    public function __construct($data, $parent)
    {
        parent::__construct($data, $parent);
    }

    /**
     * Get many to many relations.
     *
     * @return array
     */
    public function getManyToManyRelations()
    {
        return $this->manyToManyRelations;
    }

    /**
     * Get the entity namespace.
     *
     * @return string
     */
    public function getEntityNamespace()
    {
        if (null === $this->namespace) {
            $config = Registry::get('config');
            if (isset($config['bundleNamespace']) && $config['bundleNamespace']) {
                $this->namespace = $config['bundleNamespace'] . '\\';
            }
            if (isset($config['entityNamespace']) && $config['entityNamespace']) {
                $this->namespace .= $config['entityNamespace'];
            } else {
                $this->namespace .= 'Entity';
            }
        }

        return $this->namespace;
    }

    /**
     * Get namespace of a class.
     *
     * @param string $class The class name
     * @return string
     */
    public function getNamespace($class = null)
    {
        return sprintf('%s\%s', $this->getEntityNamespace(), null === $class ? $this->getModelName() : $class);
    }

    /**
     * Get the array collection class name.
     *
     * @param bool $useFQCN return full qualified class name
     * @return string
     */
    public function getArrayClass($useFQCN = true)
    {
        $class = $this->arrayClass;
        if (!$useFQCN && count($array = explode('\\', $class))) {
            $class = array_pop($array);
        }

        return $class;
    }

    /**
     * Return the table definition
     * Annotation format
     *
     * @return string
     */
    public function display()
    {
        $return = array();
        $config = Registry::get('config');
        $this->ormPrefix = '@' . ((isset($config['useAnnotationPrefix']) && $config['useAnnotationPrefix']) ? $config['useAnnotationPrefix'] : '');
        $namespace = $this->getEntityNamespace();
        $repositoryNamespace = isset($config['repositoryNamespace']) && $config['repositoryNamespace'] ? $config['repositoryNamespace'] . '\\' : '';
        $skipGetterAndSetter = isset($config['skipGetterAndSetter']) && $config['skipGetterAndSetter'] ? true : false;
        // indices
        $indices = array();
        $uniqueIndices = array();
        foreach($this->indexes as $index){
            if($index->isIndex()){
                $indices[] = $this->ormPrefix . 'Index(' . $index->display() . ')';
            }
            if ($index->isUnique()){
                $uniqueIndices[] = $this->ormPrefix . 'UniqueConstraint(' . $index->display() . ')';
            }
        }

        $return[] = '<?php';
        $return[] = '';
        $return[] = sprintf('namespace %s;', $namespace);
        $return[] = '';
        if ($retval = $this->displayUsedClasses()) {
            $return[] = $retval;
            $return[] = '';
        }

        $return[] = '/**';
        $return[] = ' * ' . $this->getNamespace();
        $return[] = ' *';
        $return[] = ' * ' . $this->ormPrefix . 'Entity' . (isset($config['useAutomaticRepository']) && $config['useAutomaticRepository'] ? sprintf('(repositoryClass="%sRepository")', $repositoryNamespace . $this->getModelName()) : '');
        $return[] = ' * ' . $this->ormPrefix . 'Table(name="' . $this->getRawTableName() . '"' . (count($indices) ? ', indexes={' . implode(', ', $indices) . '}' : '') . (count($uniqueIndices) ? ', uniqueConstraints={' . implode(', ', $uniqueIndices) . '}' : '') . ')';
        $return[] = ' */';
        $return[] = 'class ' . $this->getModelName();
        $return[] = '{';
        $return[] = $this->columns->display();
        $return[] = $this->displayManyToMany();
        $return[] = $this->displayConstructor();
        if (!$skipGetterAndSetter) {
            $return[] = $this->columns->displayGetterAndSetter();
            $return[] = $this->displayManyToManyGetterAndSetter();
        }
        $return[] = '}';
        $return[] = '';

        return implode("\n", $return);
    }

    public function displayUsedClasses()
    {
        $return = array();
        if ($this->ormPrefix == '@ORM\\') {
            $return[] = 'use Doctrine\ORM\Mapping as ORM;';
        }
        $useArray = false;
        foreach ($this->manyToManyRelations as $relation) {
            if($this->isEnhancedManyToManyRelationDetection($relation)){
                continue;
            }
            $useArray = true;
            break;
        }
        if ($useArray || $this->getColumns()->hasOneToManyRelation()) {
            $return[] = sprintf('use %s;', $this->getArrayClass());
        }

        return implode("\n", $return);
    }

    public function displayConstructor()
    {
        $return = array();
        $return[] = $this->indentation() . 'public function __construct()';
        $return[] = $this->indentation() . '{';
        if ($retval = $this->columns->displayArrayCollections()) {
            $return[] = $retval;
        }
        foreach($this->manyToManyRelations as $relation){

            if($this->isEnhancedManyToManyRelationDetection($relation)){
                continue;
            }

            $return[] = $this->indentation(2) . sprintf('$this->%s = new %s();', lcfirst(Pluralizer::pluralize($relation['refTable']->getModelName())), $this->getArrayClass(false));
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
                $return[] = $this->indentation() . ' * )';
                $return[] = $this->indentation() . ' */';
            } else {
                $return[] = $this->indentation() . '/**';
                $return[] = $this->indentation() . ' * ' . $this->ormPrefix . 'ManyToMany(targetEntity="' . $relation['refTable']->getModelName() . '", mappedBy="' . lcfirst(Pluralizer::pluralize($this->getModelName())) . '")';
                $return[] = $this->indentation() . ' */';
            }
            $return[] = $this->indentation() . 'private $' . lcfirst(Pluralizer::pluralize($relation['refTable']->getModelName())) . ';';
            $return[] = '';
        }

        return implode("\n", $return);
    }

    protected function displayManyToManyGetterAndSetter()
    {
        $return = array();

        foreach($this->manyToManyRelations as $relation){

            if($this->isEnhancedManyToManyRelationDetection($relation)){
                continue;
            }

            $return[] = $this->indentation() . '/**';
            $return[] = $this->indentation() . ' * Add ' . $relation['refTable']->getModelName() . ' entity to collection.';
            $return[] = $this->indentation() . ' *';
            $return[] = $this->indentation() . ' * @param '. $relation['refTable']->getNamespace() . ' $' . lcfirst($relation['refTable']->getModelName());
            $return[] = $this->indentation() . ' * @return ' . $this->getNamespace($this->getModelName());
            $return[] = $this->indentation() . ' */';
            $return[] = $this->indentation() . 'public function add' . $relation['refTable']->getModelName() . '(' . $relation['refTable']->getModelName() . ' $' . lcfirst($relation['refTable']->getModelName()) . ')';
            $return[] = $this->indentation() . '{';
            $return[] = $this->indentation(2) . '$this->' . lcfirst(Pluralizer::pluralize($relation['refTable']->getModelName())) . '[] = $' . lcfirst($relation['refTable']->getModelName()) . ';';
            $return[] = '';
            $return[] = $this->indentation(2) . 'return $this;';
            $return[] = $this->indentation() . '}';
            $return[] = '';
            $return[] = $this->indentation() . '/**';
            $return[] = $this->indentation() . ' * Get ' . $relation['refTable']->getModelName() . ' entity collection.';
            $return[] = $this->indentation() . ' *';
            $return[] = $this->indentation() . ' * @return ' . $this->getArrayClass();
            $return[] = $this->indentation() . ' */';
            $return[] = $this->indentation() . 'public function get' . Pluralizer::pluralize($relation['refTable']->getModelName()) . '()';
            $return[] = $this->indentation() . '{';
            $return[] = $this->indentation(2) . 'return $this->' . lcfirst(Pluralizer::pluralize($relation['refTable']->getModelName())) . ';';
            $return[] = $this->indentation() . '}';
            $return[] = '';
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
        $config = Registry::get('config');

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
        if($relation['reference']->getOwningTable()->isManyToMany()){
            return true;
        }

        return false;
    }
}
