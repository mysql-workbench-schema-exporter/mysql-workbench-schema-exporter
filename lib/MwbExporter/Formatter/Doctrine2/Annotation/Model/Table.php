<?php

/*
 * The MIT License
 *
 * Copyright (c) 2010 Johannes Mueller <circus2(at)web.de>
 * Copyright (c) 2012-2013 Toha <tohenk@yahoo.com>
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

namespace MwbExporter\Formatter\Doctrine2\Annotation\Model;

use MwbExporter\Formatter\Doctrine2\Model\Table as BaseTable;
use MwbExporter\Helper\Pluralizer;
use MwbExporter\Object\Annotation;
use MwbExporter\Writer\WriterInterface;
use MwbExporter\Formatter\Doctrine2\Annotation\Formatter;

class Table extends BaseTable
{
    protected $ormPrefix = null;
    protected $collectionClass = 'Doctrine\Common\Collections\ArrayCollection';
    protected $collectionInterface = 'Doctrine\Common\Collections\Collection';

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
        return ($absolute ? '\\' : '').$this->collectionInterface;
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
            $this->ormPrefix = '@'.$this->getDocument()->getConfig()->get(Formatter::CFG_ANNOTATION_PREFIX);
        }

        return $this->ormPrefix.($annotation ? $annotation : '');
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
        return $this->getDocument()->getConfig()->get(Formatter::CFG_QUOTE_IDENTIFIER) ? '`'.$value.'`' : $value;
    }

    /**
     * Get annotation object.
     *
     * @param string $annotation  The annotation name
     * @param mixed  $content     The annotation content
     * @param array  $options     The annotation options
     * @return \MwbExporter\Object\Annotation
     */
    public function getAnnotation($annotation, $content = null, $options = array())
    {
        return new Annotation($this->addPrefix($annotation), $content, $options);
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
            if($index->isIndex()){
                $indices[] = $this->getAnnotation('Index', $index->asAnnotation());
            }
        }

        return count($indices) ? $indices : null;
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
            if($index->isUnique()){
                $uniques[] = $this->getAnnotation('UniqueConstraint', $index->asAnnotation());
            }
        }

        return count($uniques) ? $uniques : null;
    }

    /**
     * Get join annotation.
     *
     * @param string $joinType    Join type
     * @param string $entity      Entity name
     * @param string $mappedBy    Column mapping
     * @param string $inversedBy  Reverse column mapping
     * @return \MwbExporter\Object\Annotation
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
     * @return \MwbExporter\Object\Annotation
     */
    public function getJoinColumnAnnotation($local, $foreign, $deleteRule = null)
    {
        return $this->getAnnotation('JoinColumn', array('name' => $local, 'referencedColumnName' => $foreign, 'onDelete' => $this->getDocument()->getFormatter()->getDeleteRule($deleteRule)));
    }

    public function writeTable(WriterInterface $writer)
    {
        if (!$this->isExternal()) {
            $namespace = $this->getEntityNamespace();
            if ($repositoryNamespace = $this->getDocument()->getConfig()->get(Formatter::CFG_REPOSITORY_NAMESPACE)) {
                $repositoryNamespace .= '\\';
            }
            $skipGetterAndSetter = $this->getDocument()->getConfig()->get(Formatter::CFG_SKIP_GETTER_SETTER);
            $serializableEntity  = $this->getDocument()->getConfig()->get(Formatter::CFG_GENERATE_ENTITY_SERIALIZATION);
            $lifecycleCallbacks  = $this->getLifecycleCallbacks();

            $comment = $this->getComment();
            $writer
                ->open($this->getTableFileName())
                ->write('<?php')
                ->write('')
                ->write('namespace %s;', $namespace)
                ->write('')
                ->writeCallback(function(WriterInterface $writer, Table $_this = null) {
                    $_this->writeUsedClasses($writer);
                })
                ->write('/**')
                ->write(' * '.$this->getNamespace(null, false))
                ->write(' *')
                ->writeIf($comment, $comment)
                ->write(' * '.$this->getAnnotation('Entity', array('repositoryClass' => $this->getDocument()->getConfig()->get(Formatter::CFG_AUTOMATIC_REPOSITORY) ? $repositoryNamespace.$this->getModelName().'Repository' : null)))
                ->write(' * '.$this->getAnnotation('Table', array('name' => $this->quoteIdentifier($this->getRawTableName()), 'indexes' => $this->getIndexesAnnotation(), 'uniqueConstraints' => $this->getUniqueConstraintsAnnotation())))
                ->writeIf($lifecycleCallbacks, ' * @HasLifecycleCallbacks')
                ->write(' */')
                ->write('class '.$this->getModelName())
                ->write('{')
                ->indent()
                    ->writeCallback(function(WriterInterface $writer, Table $_this = null) use ($skipGetterAndSetter, $serializableEntity, $lifecycleCallbacks) {
                        $_this->getColumns()->write($writer);
                        $_this->writeManyToMany($writer);
                        $_this->writeConstructor($writer);
                        if (!$skipGetterAndSetter) {
                            $_this->getColumns()->writeGetterAndSetter($writer);
                            $_this->writeManyToManyGetterAndSetter($writer);
                        }
                        foreach ($lifecycleCallbacks as $callback => $handlers) {
                            foreach ($handlers as $handler) {
                                $writer
                                    ->write('/**')
                                    ->write(' * @%s', ucfirst($callback))
                                    ->write(' */')
                                    ->write('public function %s()', $handler)
                                    ->write('{')
                                    ->write('}')
                                    ->write('')
                                ;
                            }
                        }
                        if ($serializableEntity) {
                            $_this->writeSerialization($writer);
                        }
                    })
                ->outdent()
                ->write('}')
                ->close()
            ;

            return self::WRITE_OK;
        }

        return self::WRITE_EXTERNAL;
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

        $formatter = $this->getDocument()->getFormatter();
        foreach ($this->manyToManyRelations as $relation) {
            $isOwningSide = $formatter->isOwningSide($relation, $mappedRelation);

            $annotationOptions = array(
                'targetEntity' => $relation['refTable']->getModelNameAsFQCN($this->getEntityNamespace()),
                'mappedBy' => null,
                'inversedBy' => lcfirst(Pluralizer::pluralize($this->getModelName())),
                'cascade' => $formatter->getCascadeOption($relation['reference']->parseComment('cascade')),
                'fetch' => $formatter->getFetchOption($relation['reference']->parseComment('fetch')),
            );

            // if this is the owning side, also output the JoinTable Annotation
            // otherwise use "mappedBy" feature
            if ($isOwningSide) {
                if ($mappedRelation->parseComment('unidirectional') === 'true') {
                    unset($annotationOptions['inversedBy']);
                }

                $writer
                    ->write('/**')
                    ->write(' * '.$this->getAnnotation('ManyToMany', $annotationOptions))
                    ->write(' * '.$this->getAnnotation('JoinTable',
                        array(
                            'name'               => $relation['reference']->getOwningTable()->getRawTableName(),
                            'joinColumns'        => array(
                                $this->getJoinColumnAnnotation(
                                    $relation['reference']->getForeign()->getColumnName(),
                                    $relation['reference']->getLocal()->getColumnName(),
                                    $relation['reference']->getParameters()->get('deleteRule')
                                )
                            ),
                            'inverseJoinColumns' => array(
                                $this->getJoinColumnAnnotation(
                                    $mappedRelation->getForeign()->getColumnName(),
                                    $mappedRelation->getLocal()->getColumnName(),
                                    $mappedRelation->getParameters()->get('deleteRule')
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
                    ->write(' * '.$this->getAnnotation('ManyToMany', $annotationOptions))
                    ->write(' */')
                ;
            }
            $writer
                ->write('protected $'.lcfirst(Pluralizer::pluralize($relation['refTable']->getModelName())).';')
                ->write('')
            ;
        }

        return $this;
    }

    public function writeManyToManyGetterAndSetter(WriterInterface $writer)
    {
        $formatter = $this->getDocument()->getFormatter();
        foreach ($this->manyToManyRelations as $relation) {
            $isOwningSide = $formatter->isOwningSide($relation, $mappedRelation);
            $writer
                ->write('/**')
                ->write(' * Add '.$relation['refTable']->getModelName().' entity to collection.')
                ->write(' *')
                ->write(' * @param '. $relation['refTable']->getNamespace().' $'.lcfirst($relation['refTable']->getModelName()))
                ->write(' * @return '.$this->getNamespace($this->getModelName()))
                ->write(' */')
                ->write('public function add'.$relation['refTable']->getModelName().'('.$relation['refTable']->getModelName().' $'.lcfirst($relation['refTable']->getModelName()).')')
                ->write('{')
                ->indent()
                    ->writeCallback(function(WriterInterface $writer, Table $_this = null) use ($isOwningSide, $relation, $mappedRelation) {
                        if ($isOwningSide) {
                            $writer->write('$%s->add%s($this);', lcfirst($relation['refTable']->getModelName()), $_this->getModelName());
                        }
                    })
                    ->write('$this->'.lcfirst(Pluralizer::pluralize($relation['refTable']->getModelName())).'[] = $'.lcfirst($relation['refTable']->getModelName()).';')
                    ->write('')
                    ->write('return $this;')
                ->outdent()
                ->write('}')
                ->write('')
                ->write('/**')
                ->write(' * Get '.$relation['refTable']->getModelName().' entity collection.')
                ->write(' *')
                ->write(' * @return '.$this->getCollectionInterface())
                ->write(' */')
                ->write('public function get'.Pluralizer::pluralize($relation['refTable']->getModelName()).'()')
                ->write('{')
                ->indent()
                    ->write('return $this->'.lcfirst(Pluralizer::pluralize($relation['refTable']->getModelName())).';')
                ->outdent()
                ->write('}')
                ->write('')
            ;
        }

        return $this;
    }
}
