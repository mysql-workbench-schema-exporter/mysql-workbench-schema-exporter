<?php

/*
 * The MIT License
 *
 * Copyright (c) 2010 Johannes Mueller <circus2(at)web.de>
 * Copyright (c) 2012-2014 Toha <tohenk@yahoo.com>
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
use MwbExporter\Object\Annotation;
use MwbExporter\Writer\WriterInterface;
use MwbExporter\Formatter\Doctrine2\Annotation\Formatter;
use MwbExporter\Helper\Comment;
use Doctrine\Common\Inflector\Inflector;

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
     * Get collection interface class name.
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
            $this->ormPrefix = '@'.$this->getConfig()->get(Formatter::CFG_ANNOTATION_PREFIX);
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
        return $this->getConfig()->get(Formatter::CFG_QUOTE_IDENTIFIER) ? '`'.$value.'`' : $value;
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
        foreach ($this->getTableIndices() as $index) {
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
        foreach ($this->getTableIndices() as $index) {
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
        return $this->getAnnotation('JoinColumn', array('name' => $local, 'referencedColumnName' => $foreign, 'onDelete' => $this->getFormatter()->getDeleteRule($deleteRule)));
    }

    public function writeTable(WriterInterface $writer)
    {
        switch (true) {
            case $this->isManyToMany():
                return self::WRITE_M2M;
                break;
            case $this->isExternal():
                return self::WRITE_EXTERNAL;
                break;
            default:
                $this->writeTableOk($writer);
                return self::WRITE_OK;
        }
    }

    protected function writeTableOk(WriterInterface $writer)
    {
        $this->getDocument()->addLog(sprintf('Writing table "%s".', $this->getModelName()));

        $namespace = $this->getEntityNamespace();
        if ($repositoryNamespace = $this->getConfig()->get(Formatter::CFG_REPOSITORY_NAMESPACE)) {
            $repositoryNamespace .= '\\';
        }
        $skipGetterAndSetter = $this->getConfig()->get(Formatter::CFG_SKIP_GETTER_SETTER);
        $serializableEntity  = $this->getConfig()->get(Formatter::CFG_GENERATE_ENTITY_SERIALIZATION);
        $extendableEntity    = $this->getConfig()->get(Formatter::CFG_GENERATE_EXTENDABLE_ENTITY);
        $lifecycleCallbacks  = $this->getLifecycleCallbacks();

        $comment = $this->getComment();
        $writer
            ->open($this->getClassFileName($extendableEntity ? true : false))
            ->write('<?php')
            ->write('')
            ->writeCallback(function(WriterInterface $writer, Table $_this = null) {
                if ($_this->getConfig()->get(Formatter::CFG_ADD_COMMENT)) {
                    $writer
                        ->write($_this->getFormatter()->getComment(Comment::FORMAT_PHP))
                        ->write('')
                    ;
                }
            })
            ->write('namespace %s;', $namespace)
            ->write('')
            ->writeCallback(function(WriterInterface $writer, Table $_this = null) {
                $_this->writeUsedClasses($writer);
            })
            ->write('/**')
            ->write(' * '.$this->getNamespace(null, false))
            ->write(' *')
            ->writeIf($comment, $comment)
            ->write(' * '.$this->getAnnotation('Entity', array('repositoryClass' => $this->getConfig()->get(Formatter::CFG_AUTOMATIC_REPOSITORY) ? $repositoryNamespace.$this->getModelName().'Repository' : null)))
            ->write(' * '.$this->getAnnotation('Table', array('name' => $this->quoteIdentifier($this->getRawTableName()), 'indexes' => $this->getIndexesAnnotation(), 'uniqueConstraints' => $this->getUniqueConstraintsAnnotation())))
            ->writeIf($extendableEntity, ' * '.$this->getAnnotation('InheritanceType', array('SINGLE_TABLE')))
            ->writeIf($extendableEntity, ' * '.$this->getAnnotation('DiscriminatorColumn', $this->getInheritanceDiscriminatorColumn()))
            ->writeIf($extendableEntity, ' * '.$this->getAnnotation('DiscriminatorMap', array($this->getInheritanceDiscriminatorMap())))
            ->writeIf($lifecycleCallbacks, ' * @HasLifecycleCallbacks')
            ->write(' */')
            ->write('class '.$this->getClassName($extendableEntity ? true : false).(($implements = $this->getClassImplementations()) ? ' implements '.$implements : ''))
            ->write('{')
            ->indent()
                ->writeCallback(function(WriterInterface $writer, Table $_this = null) use ($skipGetterAndSetter, $serializableEntity, $lifecycleCallbacks) {
                    $_this->writePreClassHandler($writer);
                    $_this->writeVars($writer);
                    $_this->writeConstructor($writer);
                    if (!$skipGetterAndSetter) {
                        $_this->writeGetterAndSetter($writer);
                    }
                    $_this->writePostClassHandler($writer);
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
        if ($extendableEntity && !$writer->getStorage()->hasFile($this->getClassFileName())) {
            $writer
                ->open($this->getClassFileName())
                ->write('<?php')
                ->write('')
                ->writeCallback(function(WriterInterface $writer, Table $_this = null) {
                    if ($_this->getConfig()->get(Formatter::CFG_ADD_COMMENT)) {
                        $writer
                            ->write($_this->getFormatter()->getComment(Comment::FORMAT_PHP))
                            ->write('')
                        ;
                    }
                })
                ->write('namespace %s;', $namespace)
                ->write('')
                ->write('/**')
                ->write(' * '.$this->getNamespace(null, false))
                ->write(' *')
                ->write(' * '.$this->getAnnotation('Entity', array('repositoryClass' => $this->getConfig()->get(Formatter::CFG_AUTOMATIC_REPOSITORY) ? $repositoryNamespace.$this->getModelName().'Repository' : null)))
                ->write(' */')
                ->write('class %s extends %s', $this->getClassName(), $this->getClassName(true))
                ->write('{')
                ->write('}')
                ->close()
            ;
        }
    }

    /**
     * Get the generated class name.
     *
     * @param bool $base
     * @return string
     */
    protected function getClassFileName($base = false)
    {
        return ($base ? $this->getTableFileName(null, array('%entity%' => 'Base'.$this->getModelName())) : $this->getTableFileName());
    }

    /**
     * Get the generated class name.
     *
     * @param bool $base
     * @return string
     */
    protected function getClassName($base = false)
    {
        return ($base ? 'Base' : '').$this->getModelName();
    }

    /**
     * Get the class name to implements.
     *
     * @return string
     */
    protected function getClassImplementations()
    {
    }

    /**
     * Get the use class for ORM if applicable.
     *
     * @return string
     */
    protected function getOrmUse()
    {
        if ('@ORM\\' === $this->addPrefix()) {
            return 'Doctrine\ORM\Mapping as ORM';
        }
    }

    /**
     * Get used classes.
     *
     * @return array
     */
    protected function getUsedClasses()
    {
        $uses = array();
        if ($orm = $this->getOrmUse()) {
            $uses[] = $orm;
        }
        if (count($this->getTableM2MRelations()) || $this->getColumns()->hasOneToManyRelation()) {
            $uses[] = $this->getCollectionClass();
        }

        return $uses;
    }

    protected function getInheritanceDiscriminatorColumn()
    {
        $result = array();
        if ($column = trim($this->parseComment('discriminator'))) {
            $result['name'] = $column;
            foreach ($this->getColumns() as $col) {
                if ($column == $col->getColumnName()) {
                    $result['type'] = $this->getFormatter()->getDatatypeConverter()->getDataType($col->getColumnType());
                    break;
                }
            }
        } else {
            $result['name'] = 'discr';
            $result['type'] = 'string';
        }

        return $result;
    }

    protected function getInheritanceDiscriminatorMap()
    {
        return array('base' => $this->getClassName(true), 'extended' => $this->getClassName());
    }

    public function writeUsedClasses(WriterInterface $writer)
    {
        $this->writeUses($writer, $this->getUsedClasses());

        return $this;
    }

    public function writeExtendedUsedClasses(WriterInterface $writer)
    {
        $uses = array();
        if ($orm = $this->getOrmUse()) {
            $uses[] = $orm;
        }
        $uses[] = sprintf('%s\%s', $this->getEntityNamespace(), $this->getClassName(true));
        $this->writeUses($writer, $uses);

        return $this;
    }

    protected function writeUses(WriterInterface $writer, $uses = array())
    {
        if (count($uses)) {
            foreach ($uses as $use) {
                $writer->write('use %s;', $use);
            }
            $writer->write('');
        }

        return $this;
    }

    /**
     * Write pre class handler.
     *
     * @param \MwbExporter\Writer\WriterInterface $writer
     * @return \MwbExporter\Formatter\Doctrine2\Annotation\Model\Table
     */
    public function writePreClassHandler(WriterInterface $writer)
    {
        return $this;
    }

    public function writeVars(WriterInterface $writer)
    {
        $this->writeColumnsVar($writer);
        $this->writeRelationsVar($writer);
        $this->writeManyToManyVar($writer);

        return $this;
    }

    protected function writeColumnsVar(WriterInterface $writer)
    {
        foreach ($this->getColumns() as $column) {
            $column->writeVar($writer);
        }
    }

    protected function writeRelationsVar(WriterInterface $writer)
    {
        // one to many references
        foreach ($this->getAllForeignKeys() as $foreign) {
            if ($this->isForeignKeyIgnored($foreign)) {
                continue;
            }

            $targetEntity = $foreign->getReferencedTable()->getModelName();
            $targetEntityFQCN = $foreign->getReferencedTable()->getModelNameAsFQCN($foreign->getOwningTable()->getEntityNamespace());
            $inversedBy = Inflector::pluralize($foreign->getOwningTable()->getModelName());
            $related = $foreign->getForeignM2MRelatedName();

            $this->getDocument()->addLog(sprintf('  Writing 1 <=> ? relation "%s".', $targetEntity));

            $annotationOptions = array(
                'targetEntity' => $targetEntityFQCN,
                'inversedBy' => lcfirst($inversedBy).$related,
                'cascade' => $this->getFormatter()->getCascadeOption($foreign->parseComment('cascade')),
                'fetch' => $this->getFormatter()->getFetchOption($foreign->parseComment('fetch')),
            );

            $joinColumnAnnotationOptions = array(
                'name' => $foreign->getLocal()->getColumnName(),
                'referencedColumnName' => $foreign->getForeign()->getColumnName(),
                'nullable' => !$foreign->getLocal()->isNotNull() ? null : false,
            );


            if (null !== ($deleteRule = $this->getFormatter()->getDeleteRule($foreign->getLocal()->getParameters()->get('deleteRule')))) {
                $joinColumnAnnotationOptions['onDelete'] = $deleteRule;
            }

            // check for OneToOne or OneToMany relationship
            if ($foreign->isManyToOne()) { // is OneToMany
                $this->getDocument()->addLog('  Relation considered as "1 <=> N".');

                $related = $this->getRelatedName($foreign);
                if (!$foreign->isComposite()) {
                    $writer
                        ->write('/**')
                        ->write(' * '.$this->getAnnotation('ManyToOne', $annotationOptions))
                        ->write(' * '.$this->getAnnotation('JoinColumn', $joinColumnAnnotationOptions))
                        ->write(' */')
                        ->write('protected $'.lcfirst($targetEntity).$related.';')
                        ->write('')
                    ;
                } else {
                    // composite foreign keys
                    $joins = array();
                    $lcols = $foreign->getLocals();
                    $fcols = $foreign->getForeigns();
                    for ($i = 0; $i < count($lcols); $i++) {
                        $joins[] = $this->getAnnotation('JoinColumn', array(
                            'name' => $lcols[$i]->getColumnName(),
                            'referencedColumnName' => $fcols[$i]->getColumnName(),
                            'nullable' => $lcols[$i]->isNotNull() ? null : false,
                        ));
                    }
                    $writer
                        ->write('/**')
                        ->write(' * '.$this->getAnnotation('ManyToOne', $annotationOptions))
                        ->write(' * '.$this->getAnnotation('JoinColumns', array($joins), array('multiline' => true, 'wrapper' => ' * %s')))
                        ->writeCallback(function(WriterInterface $writer, Table $_this = null) use ($foreign) {
                            if (count($orders = $_this->getFormatter()->getOrderOption($foreign->parseComment('order')))) {
                                $writer
                                    ->write(' * '.$_this->getAnnotation('OrderBy', array($orders)))
                                ;
                            }
                        })
                        ->write(' */')
                        ->write('protected $'.lcfirst(Inflector::pluralize($targetEntity)).$related.';')
                        ->write('')
                    ;
                }
            } else { // is OneToOne
                $this->getDocument()->addLog('  Relation considered as "1 <=> 1".');

                if ($foreign->parseComment('unidirectional') === 'true') {
                    $annotationOptions['inversedBy'] = null;
                } else {
                    $annotationOptions['inversedBy'] = lcfirst($annotationOptions['inversedBy']);
                }
                $annotationOptions['cascade'] = $this->getFormatter()->getCascadeOption($foreign->parseComment('cascade'));
                $writer
                    ->write('/**')
                    ->write(' * '.$this->getAnnotation('OneToOne', $annotationOptions))
                    ->write(' * '.$this->getAnnotation('JoinColumn', $joinColumnAnnotationOptions))
                    ->write(' */')
                    ->write('protected $'.lcfirst($targetEntity).';')
                    ->write('')
                ;
            }
        }
        // many to references
        foreach ($this->getAllLocalForeignKeys() as $local) {
            if ($this->isLocalForeignKeyIgnored($local)) {
                continue;
            }

            $targetEntity = $local->getOwningTable()->getModelName();
            $targetEntityFQCN = $local->getOwningTable()->getModelNameAsFQCN($local->getReferencedTable()->getEntityNamespace());
            $mappedBy = $local->getReferencedTable()->getModelName();
            $related = $local->getForeignM2MRelatedName();
            $refRelated = $local->getOwningTable()->getRelatedName($local);

            $this->getDocument()->addLog(sprintf('  Writing N <=> ? relation "%s".', $targetEntity));

            $annotationOptions = array(
                'targetEntity' => $targetEntityFQCN,
                'mappedBy' => lcfirst($mappedBy).$related,
                'cascade' => $this->getFormatter()->getCascadeOption($local->parseComment('cascade')),
                'fetch' => $this->getFormatter()->getFetchOption($local->parseComment('fetch')),
                'orphanRemoval' => $this->getFormatter()->getBooleanOption($local->parseComment('orphanRemoval')),
            );
            $joinColumnAnnotationOptions = array(
                'name' => $local->getForeign()->getColumnName(),
                'referencedColumnName' => $local->getLocal()->getColumnName(),
                'onDelete' => $this->getFormatter()->getDeleteRule($local->getParameters()->get('deleteRule')),
                'nullable' => !$local->getForeign()->isNotNull() ? null : false,
            );

            //check for OneToOne or ManyToOne relationship
            if ($local->isManyToOne()) { // is ManyToOne
                $this->getDocument()->addLog('  Relation considered as "N <=> 1".');

                if (!$local->isComposite()) {
                    $writer
                        ->write('/**')
                        ->write(' * '.$this->getAnnotation('OneToMany', $annotationOptions))
                        ->write(' * '.$this->getAnnotation('JoinColumn', $joinColumnAnnotationOptions))
                        ->writeCallback(function(WriterInterface $writer, Table $_this = null) use ($local) {
                            if (count($orders = $_this->getFormatter()->getOrderOption($local->parseComment('order')))) {
                                $writer
                                    ->write(' * '.$_this->getAnnotation('OrderBy', array($orders)))
                                ;
                            }
                        })
                        ->write(' */')
                        ->write('protected $'.lcfirst(Inflector::pluralize($targetEntity)).$related.';')
                        ->write('')
                    ;
                } else {
                    // composite foreign keys
                    $joins = array();
                    $lcols = $local->getLocals();
                    $fcols = $local->getForeigns();
                    for ($i = 0; $i < count($lcols); $i++) {
                        $joins[] = $this->getAnnotation('JoinColumn', array(
                            'name' => $fcols[$i]->getColumnName(),
                            'referencedColumnName' => $lcols[$i]->getColumnName(),
                            'nullable' => $fcols[$i]->isNotNull() ? null : false,
                        ));
                    }
                    $writer
                        ->write('/**')
                        ->write(' * '.$this->getAnnotation('OneToMany', $annotationOptions))
                        ->write(' * '.$this->getAnnotation('JoinColumns', array($joins), array('multiline' => true, 'wrapper' => ' * %s')))
                        ->writeCallback(function(WriterInterface $writer, Table $_this = null) use ($local) {
                            if (count($orders = $_this->getFormatter()->getOrderOption($local->parseComment('order')))) {
                                $writer
                                    ->write(' * '.$_this->getAnnotation('OrderBy', array($orders)))
                                ;
                            }
                        })
                        ->write(' */')
                        ->write('protected $'.lcfirst($targetEntity).$related.';')
                        ->write('')
                    ;
                }
            } else { // is OneToOne
                $this->getDocument()->addLog('  Relation considered as "1 <=> 1".');

                $writer
                    ->write('/**')
                    ->write(' * '.$this->getAnnotation('OneToOne', $annotationOptions))
                    ->write(' * '.$this->getAnnotation('JoinColumn', $joinColumnAnnotationOptions))
                    ->write(' */')
                    ->write('protected $'.lcfirst($targetEntity).';')
                    ->write('')
                ;
            }
        }

        return $this;
    }

    protected function writeManyToManyVar(WriterInterface $writer)
    {
        foreach ($this->getTableM2MRelations() as $relation) {
            $this->getDocument()->addLog(sprintf('  Writing setter/getter for N <=> N "%s".', $relation['refTable']->getModelName()));

            $isOwningSide = $this->getFormatter()->isOwningSide($relation, $mappedRelation);
            $annotationOptions = array(
                'targetEntity' => $relation['refTable']->getModelNameAsFQCN($this->getEntityNamespace()),
                'mappedBy' => null,
                'inversedBy' => lcfirst($this->getPluralModelName()),
                'cascade' => $this->getFormatter()->getCascadeOption($relation['reference']->parseComment('cascade')),
                'fetch' => $this->getFormatter()->getFetchOption($relation['reference']->parseComment('fetch')),
            );

            // if this is the owning side, also output the JoinTable Annotation
            // otherwise use "mappedBy" feature
            if ($isOwningSide) {
                $this->getDocument()->addLog(sprintf('  Applying setter/getter for N <=> N "%s".', "owner"));

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
                                    $relation['reference']->getLocal()->getColumnName(),
                                    $relation['reference']->getForeign()->getColumnName(),
                                    $relation['reference']->getParameters()->get('deleteRule')
                                )
                            ),
                            'inverseJoinColumns' => array(
                                $this->getJoinColumnAnnotation(
                                    $mappedRelation->getLocal()->getColumnName(),
                                    $mappedRelation->getForeign()->getColumnName(),
                                    $mappedRelation->getParameters()->get('deleteRule')
                                )
                            )
                        ), array('multiline' => true, 'wrapper' => ' * %s')))
                    ->writeCallback(function(WriterInterface $writer, Table $_this = null) use ($mappedRelation) {
                        if (count($orders = $_this->getFormatter()->getOrderOption($mappedRelation->parseComment('order')))) {
                            $writer
                                ->write(' * '.$_this->getAnnotation('OrderBy', array($orders)))
                            ;
                        }
                    })
                    ->write(' */')
                ;
            } else {
                $this->getDocument()->addLog(sprintf('  Applying setter/getter for N <=> N "%s".', "inverse"));

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
                ->write('protected $'.lcfirst($relation['refTable']->getPluralModelName()).';')
                ->write('')
            ;
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
                    $_this->writeRelationsConstructor($writer);
                    $_this->writeManyToManyConstructor($writer);
                })
            ->outdent()
            ->write('}')
            ->write('')
        ;

        return $this;
    }

    public function writeRelationsConstructor(WriterInterface $writer)
    {
        foreach ($this->getAllLocalForeignKeys() as $local) {
            if ($this->isLocalForeignKeyIgnored($local)) {
                continue;
            }
            $this->getDocument()->addLog(sprintf('  Writing N <=> 1 constructor "%s".', $local->getOwningTable()->getModelName()));

            $related = $local->getForeignM2MRelatedName();
            $writer->write('$this->%s = new %s();', lcfirst($local->getOwningTable()->getPluralModelName()).$related, $this->getCollectionClass(false));
        }
    }

    public function writeManyToManyConstructor(WriterInterface $writer)
    {
        foreach ($this->getTableM2MRelations() as $relation) {
            $this->getDocument()->addLog(sprintf('  Writing M2M constructor "%s".', $relation['refTable']->getModelName()));
            $writer->write('$this->%s = new %s();', lcfirst($relation['refTable']->getPluralModelName()), $this->getCollectionClass(false));
        }
    }

    public function writeGetterAndSetter(WriterInterface $writer)
    {
        $this->writeColumnsGetterAndSetter($writer);
        $this->writeRelationsGetterAndSetter($writer);
        $this->writeManyToManyGetterAndSetter($writer);

        return $this;
    }

    protected function writeColumnsGetterAndSetter(WriterInterface $writer)
    {
        foreach ($this->getColumns() as $column) {
            $column->writeGetterAndSetter($writer);
        }
    }

    protected function writeRelationsGetterAndSetter(WriterInterface $writer)
    {
        // one to many references
        foreach ($this->getAllForeignKeys() as $foreign) {
            if ($this->isForeignKeyIgnored($foreign)) {
                continue;
            }

            $this->getDocument()->addLog(sprintf('  Writing setter/getter for 1 <=> ? "%s".', $foreign->getParameters()->get('name')));

            if ($foreign->isManyToOne()) { // is ManyToOne
                $this->getDocument()->addLog(sprintf('  Applying setter/getter for "%s".', '1 <=> N'));

                $related = $foreign->getForeignM2MRelatedName();
                $related_text = $foreign->getForeignM2MRelatedName(false);
                $writer
                    // setter
                    ->write('/**')
                    ->write(' * Set '.trim($foreign->getReferencedTable()->getModelName().' '.$related_text).' entity (many to one).')
                    ->write(' *')
                    ->write(' * @param '.$foreign->getReferencedTable()->getNamespace().' $'.lcfirst($foreign->getReferencedTable()->getModelName()))
                    ->write(' * @return '.$this->getNamespace())
                    ->write(' */')
                    ->write('public function set'.$foreign->getReferencedTable()->getModelName().$related.'('.$foreign->getReferencedTable()->getModelName().' $'.lcfirst($foreign->getReferencedTable()->getModelName()).' = null)')
                    ->write('{')
                    ->indent()
                        ->write('$this->'.lcfirst($foreign->getReferencedTable()->getModelName()).$related.' = $'.lcfirst($foreign->getReferencedTable()->getModelName()).';')
                        ->write('')
                        ->write('return $this;')
                    ->outdent()
                    ->write('}')
                    ->write('')
                    // getter
                    ->write('/**')
                    ->write(' * Get '.trim($foreign->getReferencedTable()->getModelName().' '.$related_text).' entity (many to one).')
                    ->write(' *')
                    ->write(' * @return '.$foreign->getReferencedTable()->getNamespace())
                    ->write(' */')
                    ->write('public function get'.$foreign->getReferencedTable()->getModelName().$related.'()')
                    ->write('{')
                    ->indent()
                        ->write('return $this->'.lcfirst($foreign->getReferencedTable()->getModelName()).$related.';')
                    ->outdent()
                    ->write('}')
                    ->write('')
                ;
            } else { // OneToOne
                $this->getDocument()->addLog(sprintf('  Applying setter/getter for "%s".', '1 <=> 1'));

                $writer
                    // setter
                    ->write('/**')
                    ->write(' * Set '.$foreign->getReferencedTable()->getModelName().' entity (one to one).')
                    ->write(' *')
                    ->write(' * @param '.$foreign->getReferencedTable()->getNamespace().' $'.lcfirst($foreign->getReferencedTable()->getModelName()))
                    ->write(' * @return '.$this->getNamespace())
                    ->write(' */')
                    ->write('public function set'.$foreign->getReferencedTable()->getModelName().'('.$foreign->getReferencedTable()->getModelName().' $'.lcfirst($foreign->getReferencedTable()->getModelName()).')')
                    ->write('{')
                    ->indent()
                        ->write('$this->'.lcfirst($foreign->getReferencedTable()->getModelName()).' = $'.lcfirst($foreign->getReferencedTable()->getModelName()).';')
                        ->write('')
                        ->write('return $this;')
                    ->outdent()
                    ->write('}')
                    ->write('')
                    // getter
                    ->write('/**')
                    ->write(' * Get '.$foreign->getReferencedTable()->getModelName().' entity (one to one).')
                    ->write(' *')
                    ->write(' * @return '.$foreign->getReferencedTable()->getNamespace())
                    ->write(' */')
                    ->write('public function get'.$foreign->getReferencedTable()->getModelName().'()')
                    ->write('{')
                    ->indent()
                        ->write('return $this->'.lcfirst($foreign->getReferencedTable()->getModelName()).';')
                    ->outdent()
                    ->write('}')
                    ->write('')
                ;
            }
        }
        // many to one references
        foreach ($this->getAllLocalForeignKeys() as $local) {
            if ($this->isLocalForeignKeyIgnored($local)) {
                continue;
            }

            $this->getDocument()->addLog(sprintf('  Writing setter/getter for N <=> ? "%s".', $local->getParameters()->get('name')));

            $unidirectional = $local->parseComment('unidirectional') === 'true';
            if ($local->isManyToOne()) { // is ManyToOne
                $this->getDocument()->addLog(sprintf('  Applying setter/getter for "%s".', 'N <=> 1'));

                $related = $local->getForeignM2MRelatedName();
                $related_text = $local->getForeignM2MRelatedName(false);
                $writer
                    // setter
                    ->write('/**')
                    ->write(' * Add '.trim($local->getOwningTable()->getModelName().' '.$related_text). ' entity to collection (one to many).')
                    ->write(' *')
                    ->write(' * @param '.$local->getOwningTable()->getNamespace().' $'.lcfirst($local->getOwningTable()->getModelName()))
                    ->write(' * @return '.$this->getNamespace())
                    ->write(' */')
                    ->write('public function add'.$local->getOwningTable()->getModelName().$related.'('.$local->getOwningTable()->getModelName().' $'.lcfirst($local->getOwningTable()->getModelName()).')')
                    ->write('{')
                    ->indent()
                        ->write('$this->'.lcfirst($local->getOwningTable()->getPluralModelName()).$related.'[] = $'.lcfirst($local->getOwningTable()->getModelName()).';')
                        ->write('')
                        ->write('return $this;')
                    ->outdent()
                    ->write('}')
                    ->write('')
                    // getter
                    ->write('/**')
                    ->write(' * Get '.trim($local->getOwningTable()->getModelName().' '.$related_text).' entity collection (one to many).')
                    ->write(' *')
                    ->write(' * @return '.$this->getCollectionInterface())
                    ->write(' */')
                    ->write('public function get'.$local->getOwningTable()->getPluralModelName().$related.'()')
                    ->write('{')
                    ->indent()
                        ->write('return $this->'.lcfirst($local->getOwningTable()->getPluralModelName()).$related.';')
                    ->outdent()
                    ->write('}')
                    ->write('')
                ;
            } else { // OneToOne
                $this->getDocument()->addLog(sprintf('  Applying setter/getter for "%s".', '1 <=> 1'));

                $writer
                    // setter
                    ->write('/**')
                    ->write(' * Set '.$local->getReferencedTable()->getModelName().' entity (one to one).')
                    ->write(' *')
                    ->write(' * @param '.$local->getReferencedTable()->getNamespace().' $'.lcfirst($local->getReferencedTable()->getModelName()))
                    ->write(' * @return '.$this->getNamespace())
                    ->write(' */')
                    ->write('public function set'.$local->getReferencedTable()->getModelName().'('.$local->getReferencedTable()->getModelName().' $'.lcfirst($local->getReferencedTable()->getModelName()).' = null)')
                    ->write('{')
                    ->indent()
                        ->writeIf(!$unidirectional, '$'.lcfirst($local->getReferencedTable()->getModelName()).'->set'.$local->getOwningTable()->getModelName().'($this);')
                        ->write('$this->'.lcfirst($local->getReferencedTable()->getModelName()).' = $'.lcfirst($local->getReferencedTable()->getModelName()).';')
                        ->write('')
                        ->write('return $this;')
                    ->outdent()
                    ->write('}')
                    ->write('')
                    // getter
                    ->write('/**')
                    ->write(' * Get '.$local->getReferencedTable()->getModelName().' entity (one to one).')
                    ->write(' *')
                    ->write(' * @return '.$local->getReferencedTable()->getNamespace())
                    ->write(' */')
                    ->write('public function get'.$local->getReferencedTable()->getModelName().'()')
                    ->write('{')
                    ->indent()
                        ->write('return $this->'.lcfirst($local->getReferencedTable()->getModelName()).';')
                    ->outdent()
                    ->write('}')
                    ->write('')
                ;
            }
        }

        return $this;
    }

    protected function writeManyToManyGetterAndSetter(WriterInterface $writer)
    {
        foreach ($this->getTableM2MRelations() as $relation) {
            $this->getDocument()->addLog(sprintf('  Writing N <=> N relation "%s".', $relation['refTable']->getModelName()));

            $isOwningSide = $this->getFormatter()->isOwningSide($relation, $mappedRelation);
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
                    ->write('$this->'.lcfirst($relation['refTable']->getPluralModelName()).'[] = $'.lcfirst($relation['refTable']->getModelName()).';')
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
                ->write('public function get'.$relation['refTable']->getPluralModelName().'()')
                ->write('{')
                ->indent()
                    ->write('return $this->'.lcfirst($relation['refTable']->getPluralModelName()).';')
                ->outdent()
                ->write('}')
                ->write('')
            ;
        }

        return $this;
    }

    /**
     * Write post class handler.
     *
     * @param \MwbExporter\Writer\WriterInterface $writer
     * @return \MwbExporter\Formatter\Doctrine2\Annotation\Model\Table
     */
    public function writePostClassHandler(WriterInterface $writer)
    {
        return $this;
    }

    public function writeSerialization(WriterInterface $writer)
    {
        $writer
            ->write('public function __sleep()')
            ->write('{')
            ->indent()
                ->write('return array(%s);', implode(', ', array_map(function($column) {
                    return sprintf('\'%s\'', $column);
                }, $this->getColumns()->getColumnNames())))
            ->outdent()
            ->write('}')
        ;

        return $this;
    }
}
