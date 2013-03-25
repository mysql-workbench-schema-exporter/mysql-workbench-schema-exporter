<?php

/*
 * The MIT License
 *
 * Copyright (c) 2010 Johannes Mueller <circus2(at)web.de>
 * Copyright (c) 2012 Toha <tohenk@yahoo.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace MwbExporter\Formatter\Doctrine2\AnnotationZF2InputFilter\Model;

use MwbExporter\Model\Table as BaseTable;
use MwbExporter\Helper\Pluralizer;
use MwbExporter\Helper\AnnotationObject;
use MwbExporter\Writer\WriterInterface;
use MwbExporter\Formatter\Doctrine2\AnnotationZF2InputFilter\Formatter;

class Table
    extends BaseTable
{

    protected $ormPrefix = null;
    protected $collectionClass = 'Doctrine\Common\Collections\ArrayCollection';
    protected $collectionInterface = 'Doctrine\Common\Collections\Collection';

    /**
     * Get the entity namespace.
     *
     * @return string
     */
    public function getEntityNamespace()
    {
        $namespace = '';
        if ($bundleNamespace = $this->getDocument()->getConfig()->get(Formatter::CFG_BUNDLE_NAMESPACE)) {
            $namespace = $bundleNamespace . '\\';
        }
        if ($entityNamespace = $this->getDocument()->getConfig()->get(Formatter::CFG_ENTITY_NAMESPACE)) {
            $namespace .= $entityNamespace;
        } else {
            $namespace .= 'Entity';
        }

        return $namespace;
    }

    /**
     * Get namespace of a class.
     *
     * @param string $class The class name
     * @return string
     */
    public function getNamespace($class = null, $absolute = true)
    {
        return sprintf('%s%s\%s', $absolute
                ? '\\'
                : '', $this->getEntityNamespace(), null === $class
                ? $this->getModelName()
                : $class);
    }

    /**
     * Get the array collection class name.
     *
     * @param bool $useFQCN return full qualified class name
     * @return string
     */
    public function getCollectionClass($useFQCN = true)
    {
        $class = $this->collectionClass;
        if (!$useFQCN && count($array = explode('\\', $class))) {
            $class = array_pop($array);
        }

        return $class;
    }

    /**
     * Get collection interface class.
     *
     * @param bool $absolute Use absolute class name
     * @return string
     */
    public function getCollectionInterface($absolute = true)
    {
        return ($absolute
                ? '\\'
                : '') . $this->collectionInterface;
    }

    /**
     * Write document as generated code.
     *
     * @param \MwbExporter\Writer\WriterInterface $writer
     * @return \MwbExporter\Formatter\Doctrine2\AnnotationZF2InputFilter\Model\Table
     */
    public function write(WriterInterface $writer)
    {
        if (!$this->isExternal() && !$this->isManyToMany()) {
            $writer->open($this->getTableFileName());
            $this->writeTable($writer);
            $writer->close();
        }

        return $this;
    }

    /**
     * Get annotation prefix.
     *
     * @param string $annotation Annotation type
     * @return string
     */
    public function addPrefix($annotation = null)
    {
        if (null === $this->ormPrefix) {
            $this->ormPrefix = '@' . $this->getDocument()->getConfig()->get(Formatter::CFG_ANNOTATION_PREFIX);
        }

        return $this->ormPrefix . ($annotation
                ? $annotation
                : '');
    }

    /**
     * Quote identifier if necessary. Quoting is enabled if configuration `CFG_QUOTE_IDENTIFIER` is set
     * to true.
     *
     * @param string $value  The identifier to quote
     * @return string
     */
    public function quoteIdentifier($value)
    {
        return $this->getDocument()->getConfig()->get(Formatter::CFG_QUOTE_IDENTIFIER)
            ? '`' . $value . '`'
            : $value;
    }

    /**
     * Get annotation object.
     *
     * @param string $annotation  The annotation name
     * @param mixed  $content     The annotation content
     * @param array  $options     The annotation options
     * @return \MwbExporter\Helper\AnnotationObject
     */
    public function getAnnotation($annotation, $content = null, $options = array())
    {
        return new AnnotationObject($this->addPrefix($annotation), $content, $options);
    }

    /**
     * Get indexes annotation.
     *
     * @return array|null
     */
    protected function getIndexesAnnotation()
    {
        $indices = array();
        foreach ($this->indexes as $index) {
            if ($index->isIndex()) {
                $indices[] = $this->getAnnotation('Index', $index->asAnnotation());
            }
        }

        return count($indices)
            ? $indices
            : null;
    }

    /**
     * Get unique constraints annotation.
     *
     * @return array|null
     */
    protected function getUniqueConstraintsAnnotation()
    {
        $uniques = array();
        foreach ($this->indexes as $index) {
            if ($index->isUnique()) {
                $uniques[] = $this->getAnnotation('UniqueConstraint', $index->asAnnotation());
            }
        }

        return count($uniques)
            ? $uniques
            : null;
    }

    /**
     * Get join annotation.
     *
     * @param string $joinType    Join type
     * @param string $entity      Entity name
     * @param string $mappedBy    Column mapping
     * @param string $inversedBy  Reverse column mapping
     * @return \MwbExporter\Helper\AnnotationObject
     */
    public function getJoinAnnotation($joinType, $entity, $mappedBy = null, $inversedBy = null)
    {
        return $this->getAnnotation($joinType, array('targetEntity' => $entity, 'mappedBy' => $mappedBy, 'inversedBy' => $inversedBy));
    }

    /**
     * Get column join annotation.
     *
     * @param string $local       Local column name
     * @param string $foreign     Reference column name
     * @param string $deleteRule  On delete rule
     * @return \MwbExporter\Helper\AnnotationObject
     */
    public function getJoinColumnAnnotation($local, $foreign, $deleteRule = null)
    {
        if ($deleteRule == 'NO ACTION' || $deleteRule == 'RESTRICT') {
            // NO ACTION acts the same as RESTRICT,
            // RESTRICT is the default
            // http://dev.mysql.com/doc/refman/5.5/en/innodb-foreign-key-constraints.html
            $deleteRule = null;
        }
        return $this->getAnnotation('JoinColumn', array('name' => $local, 'referencedColumnName' => $foreign, 'onDelete' => $deleteRule));
    }

    public function writeTable(WriterInterface $writer)
    {
        $namespace = $this->getEntityNamespace();
        if ($repositoryNamespace = $this->getDocument()->getConfig()->get(Formatter::CFG_REPOSITORY_NAMESPACE)) {
            $repositoryNamespace .= '\\';
        }
        $skipGetterAndSetter = $this->getDocument()->getConfig()->get(Formatter::CFG_SKIP_GETTER_SETTER);
        $serializableEntity = $this->getDocument()->getConfig()->get(Formatter::CFG_GENERATE_ENTITY_SERIALIZATION);

        $comment = $this->getComment();
        $writer
            ->write('<?php')
            ->write('')
            ->write('namespace %s;', $namespace)
            ->write('')
            ->writeCallback(function(WriterInterface $writer, Table $_this = null) {
                    $_this->writeUsedClasses($writer);
                })
            ->write('/**')
            ->write(' * ' . $this->getNamespace(null, false))
            ->write(' *')
            ->writeIf($comment, $comment)
            ->write(' * ' . $this->getAnnotation('Entity', array('repositoryClass' => $this->getDocument()->getConfig()->get(Formatter::CFG_AUTOMATIC_REPOSITORY)
                        ? $repositoryNamespace . $this->getModelName() . 'Repository'
                        : null)))
            ->write(' * ' . $this->getAnnotation('Table', array('name' => $this->quoteIdentifier($this->getRawTableName()), 'indexes' => $this->getIndexesAnnotation(), 'uniqueConstraints' => $this->getUniqueConstraintsAnnotation())))
            ->write(' */')
            ->write('class ' . $this->getModelName())
            ->indent()
            ->write('implements InputFilterAwareInterface')
            ->outdent()
            ->write('{')
            ->indent()
            ->writeCallback(function(WriterInterface $writer, Table $_this = null) use ($skipGetterAndSetter, $serializableEntity) {
                    $_this->getColumns()->write($writer);
                    $_this->writeManyToMany($writer);

                    $writer
                    ->write('/**')
                    ->write(' * Instance of InputFilterInterface.')
                    ->write(' *')
                    ->write(' * @var InputFilter')
                    ->write(' */')
                    ->write('private $_inputFilter;')
                    ->write('');

                    $_this->writeConstructor($writer);

                    if (!$skipGetterAndSetter) {
                        $_this->getColumns()->writeGetterAndSetter($writer);
                        $_this->writeManyToManyGetterAndSetter($writer);
                    }

                    $_this->writeInputFilter($writer);
                    $_this->writePopulate($writer);
                    $_this->writeGetArrayCopy($writer);

                    if ($serializableEntity) {
                        $_this->writeSerialization($writer);
                    }

                    $writer->write('// Custom methods //////////////////////////////////////////////////////////');
                })
            ->outdent()
            ->write('}')
        ;

        return $this;
    }

    public function writeUsedClasses(WriterInterface $writer)
    {
        $count = 0;
        if ('@ORM\\' === $this->addPrefix()) {
            $writer->write('use Doctrine\ORM\Mapping as ORM;');
            $count++;
        }
        if (count($this->getManyToManyRelations()) || $this->getColumns()->hasOneToManyRelation()) {
            $writer->write('use %s;', $this->getCollectionClass());
            $count++;
        }
        $writer->write('use Zend\InputFilter\InputFilter,')
            ->indent()
            ->write('Zend\InputFilter\Factory as InputFactory,')
            ->write('Zend\InputFilter\InputFilterAwareInterface,')
            ->write('Zend\InputFilter\InputFilterInterface;')
            ->outdent();
        if ($count) {
            $writer->write('');
        }

        return $this;
    }

    public function writeConstructor(WriterInterface $writer)
    {
        $writer
            ->write('public function __construct()')
            ->write('{')
            ->indent()
            ->writeCallback(function(WriterInterface $writer, Table $_this = null) {
                    $_this->getColumns()->writeArrayCollections($writer);
                    foreach ($_this->getManyToManyRelations() as $relation) {
                        $writer->write('$this->%s = new %s();', lcfirst(Pluralizer::pluralize($relation['refTable']->getModelName())), $_this->getCollectionClass(false));
                    }
                })
            ->outdent()
            ->write('}')
            ->write('')
        ;

        return $this;
    }

    public function writeSerialization(WriterInterface $writer)
    {
        $columns = $this->getColumns()->getColumns();
        $writer
            ->write('public function __sleep()')
            ->write('{')
            ->indent()
            ->write('return array(%s);', implode(', ', array_map(function($column) {
                            return sprintf('\'%s\'', $column->getColumnName());
                        }, $columns)))
            ->outdent()
            ->write('}')
        ;
    }

    public function writeManyToMany(WriterInterface $writer)
    {
        // @TODO D2A ManyToMany relation joinColumns and inverseColumns
        // referencing wrong column names

        foreach ($this->manyToManyRelations as $relation) {
            $mappedRelation = $relation['reference']->getOwningTable()->getRelationToTable($relation['refTable']->getRawTableName());

            // user can hint which side is the owning side (set d:owningSide on the foreign key)
            if ($relation['reference']->parseComment('owningSide') === 'true') {
                $isOwningSide = true;
            } else if ($mappedRelation->parseComment('owningSide') === 'true') {
                $isOwningSide = false;
            } else {
                // if no owning side is defined, use one side randomly as owning side (the one where the column id is lower)
                $isOwningSide = $relation['reference']->getLocal()->getId() < $mappedRelation->getLocal()->getId();
            }

            $annotationOptions = array(
                'targetEntity' => $relation['refTable']->getModelName(),
                'mappedBy' => null,
                'inversedBy' => lcfirst(Pluralizer::pluralize($this->getModelName())),
                'cascade' => $this->getCascadeOption($relation['reference']->parseComment('cascade')),
                'fetch' => $this->getFetchOption($relation['reference']->parseComment('fetch')),
            );

            // if this is the owning side, also output the JoinTable Annotation
            // otherwise use "mappedBy" feature
            if ($isOwningSide) {
                if ($mappedRelation->parseComment('unidirectional') === 'true') {
                    unset($annotationOptions['inversedBy']);
                }

                $writer
                    ->write('/**')
                    ->write(' * ' . $this->getAnnotation('ManyToMany', $annotationOptions))
                    ->write(' * ' . $this->getAnnotation('JoinTable', array(
                            'name' => $relation['reference']->getOwningTable()->getRawTableName(),
                            'joinColumns' => array(
                                $this->getJoinColumnAnnotation(
                                    $relation['reference']->getForeign()->getColumnName(), $relation['reference']->getLocal()->getColumnName(), $relation['reference']->getParameters()->get('deleteRule')
                                )
                            ),
                            'inverseJoinColumns' => array(
                                $this->getJoinColumnAnnotation(
                                    $mappedRelation->getForeign()->getColumnName(), $mappedRelation->getLocal()->getColumnName(), $mappedRelation->getParameters()->get('deleteRule')
                                )
                            )
                            ), array('multiline' => true, 'wrapper' => ' * %s')))
                    ->write(' */')
                ;
            } else {
                if ($relation['reference']->parseComment('unidirectional') === 'true') {
                    continue;
                }

                $annotationOptions['mappedBy'] = $annotationOptions['inversedBy'];
                $annotationOptions['inversedBy'] = null;
                $writer
                    ->write('/**')
                    ->write(' * ' . $this->getAnnotation('ManyToMany', $annotationOptions))
                    ->write(' */')
                ;
            }
            $writer
                ->write('protected $' . lcfirst(Pluralizer::pluralize($relation['refTable']->getModelName())) . ';')
                ->write('')
            ;
        }

        return $this;
    }

    public function writeManyToManyGetterAndSetter(WriterInterface $writer)
    {
        foreach ($this->manyToManyRelations as $relation) {
            $writer
                ->write('/**')
                ->write(' * Add ' . $relation['refTable']->getModelName() . ' entity to collection.')
                ->write(' *')
                ->write(' * @param ' . $relation['refTable']->getNamespace() . ' $' . lcfirst($relation['refTable']->getModelName()))
                ->write(' * @return ' . $this->getNamespace($this->getModelName()))
                ->write(' */')
                ->write('public function add' . $relation['refTable']->getModelName() . '(' . $relation['refTable']->getModelName() . ' $' . lcfirst($relation['refTable']->getModelName()) . ')')
                ->write('{')
                ->indent()
                ->write('$this->' . lcfirst(Pluralizer::pluralize($relation['refTable']->getModelName())) . '[] = $' . lcfirst($relation['refTable']->getModelName()) . ';')
                ->write('')
                ->write('return $this;')
                ->outdent()
                ->write('}')
                ->write('')
                ->write('/**')
                ->write(' * Get ' . $relation['refTable']->getModelName() . ' entity collection.')
                ->write(' *')
                ->write(' * @return ' . $this->getCollectionInterface())
                ->write(' */')
                ->write('public function get' . Pluralizer::pluralize($relation['refTable']->getModelName()) . '()')
                ->write('{')
                ->indent()
                ->write('return $this->' . lcfirst(Pluralizer::pluralize($relation['refTable']->getModelName())) . ';')
                ->outdent()
                ->write('}')
                ->write('')
            ;
        }

        return $this;
    }

    public function writeInputFilter(WriterInterface $writer)
    {
        $columns = $this->getColumns()->getColumns();

        $writer
            ->write('/**')
            ->write(' * Not used, Only defined to be compatible with InputFilterAwareInterface.')
            ->write(' * ')
            ->write(' * @param \Zend\InputFilter\InputFilterInterface $inputFilter')
            ->write(' * @throws \Exception')
            ->write(' */')
            ->write('public function setInputFilter(InputFilterInterface $inputFilter)')
            ->write('{')
            ->indent()
            ->write('// End.')
            ->write('throw new \Exception("Not used.");')
            ->outdent()
            ->write('}')
            ->write('')
            ->write('/**')
            ->write(' * Return a for this entity configured input filter instance.')
            ->write(' *')
            ->write(' * @return InputFilterInterface')
            ->write(' */')
            ->write('public function getInputFilter()')
            ->write('{')
            ->indent()
            ->write('if ($this->_inputFilter instanceof InputFilterInterface) {')
            ->indent()
            ->write('// End.')
            ->write('return $this->_inputFilter;')
            ->outdent()
            ->write('}')
            ->write('$factory = new InputFactory();')
            ->write('$filters = array(')
            ->indent();

        foreach ($columns as $i => $column) {
            $writer
                ->write('array(')
                ->indent()
                ->write('\'name\' => \'%s\',', $column->getColumnName())
                ->write('\'required\' => %s,', $column->getIsrequired()
                        ? 'true'
                        : 'false')
                ->write('\'filters\' => array(),')
                ->write('\'validators\' => array(),')
                ->outdent()
                ->write('),');
        }

        $writer
            ->outdent()
            ->write(');')
            ->write('$this->_inputFilter = $factory->createInputFilter($filters);')
            ->write('// End.')
            ->write('return $this->_inputFilter;')
            ->outdent()
            ->write('}')
            ->write('');

        // End.
        return $this;
    }

    /**
     * Add a populate method to the entity writer.
     * 
     * @param \MwbExporter\Writer\WriterInterface $writer
     * @return \MwbExporter\Formatter\Doctrine2\AnnotationZF2InputFilter\Model\Table
     */
    public function writePopulate(WriterInterface $writer)
    {
        $writer
            ->write('/**')
            ->write(' * Populate entity with the given data.')
            ->write(' * The set* method will be used to set the data.')
            ->write(' *')
            ->write(' * @param array $data')
            ->write(' * @return boolean')
            ->write(' */')
            ->write('public function populate(array $data = array())')
            ->write('{')
            ->indent()
            ->write('foreach ($data as $field => $value) {')
            ->indent()
            ->write('$setter = sprintf(\'set%s\', ucfirst(')
            ->indent()
            ->write('str_replace(\' \', \'\', ucwords(str_replace(\'_\', \' \', $field)))')
            ->outdent()
            ->write('));')
            ->write('if (method_exists($this, $setter)) {')
            ->indent()
            ->write('$this->{$setter}($value);')
            ->outdent()
            ->write('}')
            ->outdent()
            ->write('}')
            ->write('// End.')
            ->write('return true;')
            ->outdent()
            ->write('}')
            ->write('');
        // End.
        return $this;
    }

    /**
     * Add a getArrayCopy method to the entity writer.
     * 
     * @param \MwbExporter\Writer\WriterInterface $writer
     * @return \MwbExporter\Formatter\Doctrine2\AnnotationZF2InputFilter\Model\Table
     */
    public function writeGetArrayCopy(WriterInterface $writer)
    {
        $columns = $this->getColumns()->getColumns();
        $relations = $this->getRelations();



        $writer
            ->write('/**')
            ->write(' * Return all entity fields with values.')
            ->write(' * Fields started with _ will be excluded.')
            ->write(' * ')
            ->write(' * @param array $fields This fields will be copied')
            ->write(' * @return array')
            ->write(' */')
            ->write('public function getArrayCopy(array $fields = array())')
            ->write('{')
            ->indent()
            ->write('$dataFields = array(%s);', implode(', ', array_map(function($column) {
                            return sprintf('\'%s\'', $column->getColumnName());
                        }, $columns)))
            ->write('$relationFields = array(%s);', implode(', ', array_map(function($relation) {
                            return sprintf('\'%s\'', lcfirst($relation->getReferencedTable()->getModelName()));
                        }, $relations)))
            ->write('$copiedFields = array();')
            ->outdent()
            ->indent()
            ->write('foreach ($dataFields as $field) {')
            ->indent()
            ->write('if (!in_array($field, $fields) && !empty($fields)) {')
            ->indent()
            ->write('continue;')
            ->outdent()
            ->write('}')
            ->write('$copiedFields[$field] = $value;')
            ->outdent()
            ->write('}')
            ->write('// foreach ($relationFields as $field => $relation) {')
            ->indent()
            ->write('// $copiedFields[$field] = $relation->getArrayCopy();')
            ->outdent()
            ->write('// }')
            ->write('')
            ->write('// End.')
            ->write('return $copiedFields;')
            ->outdent()
            ->write('}')
            ->write('');

        // End.
        return $this;
    }

    /**
     * get the cascade option as array. Only returns values allowed by Doctrine.
     *
     * @param $cascadeValue string cascade options separated by comma
     * @return array array with the values or null, if no cascade values are available
     */
    private function getCascadeOption($cascadeValue)
    {
        if (!$cascadeValue) {
            return null;
        }

        $cascadeValue = array_map('strtolower', array_map('trim', explode(',', $cascadeValue)));

        // only allow certain values
        $allowed = array('persist', 'remove', 'merge', 'detach', 'all');

        $cascadeValue = array_intersect($cascadeValue, $allowed);

        if ($cascadeValue) {
            return $cascadeValue;
        } else {
            return null;
        }
    }

    /**
     * get the fetch option for a relation
     *
     * @param $fetchValue string fetch option as given in comment for foreign key
     * @return string valid fetch value or null
     */
    private function getFetchOption($fetchValue)
    {
        if (!$fetchValue) {
            return null;
        }

        $fetchValue = strtoupper($fetchValue);

        if (!in_array($fetchValue, array('EAGER', 'LAZY', 'EXTRA_LAZY'))) {
            // invalid fetch value
            return null;
        } else {
            return $fetchValue;
        }
    }

}
