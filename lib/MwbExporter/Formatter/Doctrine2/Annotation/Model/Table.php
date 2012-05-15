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

namespace MwbExporter\Formatter\Doctrine2\Annotation\Model;

use MwbExporter\FormatterInterface;

use MwbExporter\Model\Table as BaseTable;
use MwbExporter\Helper\Pluralizer;
use MwbExporter\Writer\WriterInterface;
use MwbExporter\Formatter\Doctrine2\Annotation\Formatter;

class Table extends BaseTable
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
            $namespace = $bundleNamespace.'\\';
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
        return sprintf('%s%s\%s', $absolute ? '\\' : '', $this->getEntityNamespace(), null === $class ? $this->getModelName() : $class);
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
        return ($absolute ? '\\' : '').$this->collectionInterface;
    }

    /**
     * Write document as generated code.
     *
     * @param \MwbExporter\Writer\WriterInterface $writer
     * @return \MwbExporter\Formatter\Doctrine2\Annotation\Model\Table
     */
    public function write(WriterInterface $writer)
    {
        if (!$this->isExternal()) {
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
            $this->ormPrefix = '@'.$this->getDocument()->getConfig()->get(Formatter::CFG_ANNOTATION_PREFIX);
        }

        return $this->ormPrefix.($annotation ? $annotation : '');
    }

    public function writeTable(WriterInterface $writer)
    {
        $namespace = $this->getEntityNamespace();
        if ($repositoryNamespace = $this->getDocument()->getConfig()->get(Formatter::CFG_REPOSITORY_NAMESPACE)) {
            $repositoryNamespace .= '\\';
        }
        $skipGetterAndSetter = $this->getDocument()->getConfig()->get(Formatter::CFG_SKIP_GETTER_SETTER);
        $serializableEntity  = $this->getDocument()->getConfig()->get(Formatter::CFG_GENERATE_ENTITY_SERIALIZATION);
        $automaticRepository = $this->getDocument()->getConfig()->get(Formatter::CFG_AUTOMATIC_REPOSITORY) ? sprintf('(repositoryClass="%sRepository")', $repositoryNamespace.$this->getModelName()) : '';
        $indices = array();
        foreach ($this->indexes as $index) {
            $indices[] = $this->addPrefix(sprintf('Index(%s)', (string) $index));
        }
        $writer
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
            ->write(' * '.$this->addPrefix('Entity'.$automaticRepository))
            ->write(' * '.$this->addPrefix('Table(name="'.$this->getRawTableName().'"'.(count($indices) ? ', indexes={'.implode(', ', $indices).'}' : '').')'))
            ->write(' */')
            ->write('class '.$this->getModelName())
            ->write('{')
            ->indent()
                ->writeCallback(function(WriterInterface $writer, Table $_this = null) use ($skipGetterAndSetter, $serializableEntity) {
                    $_this->getColumns()->write($writer);
                    $_this->writeManyToMany($writer);
                    $_this->writeConstructor($writer);
                    if (!$skipGetterAndSetter) {
                        $_this->getColumns()->writeGetterAndSetter($writer);
                        $_this->writeManyToManyGetterAndSetter($writer);
                    }
                    if ($serializableEntity) {
                        $_this->writeSerialization($writer);
                    }
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
            // if relation is not mapped yet define relation
            // otherwise use "mappedBy" feature
            if ($relation['reference']->getLocal()->getColumnName() != $relation['reference']->getOwningTable()->getRelationToTable($relation['refTable']->getRawTableName())->getLocal()->getColumnName()) {
                $writer
                    ->write('/**')
                    ->write(' * '.$this->addPrefix('ManyToMany(targetEntity="'.$relation['refTable']->getModelName().'")'))
                    ->write(' * '.$this->addPrefix('JoinTable(name="'.$relation['reference']->getOwningTable()->getRawTableName().'",'))
                    ->write(' *     joinColumns={'       .$this->addPrefix('JoinColumn(name="'.$relation['reference']->getForeign()->getColumnName().'", referencedColumnName="'.$relation['reference']->getLocal()->getColumnName().'")},'))
                    ->write(' *     inverseJoinColumns={'.$this->addPrefix('JoinColumn(name="'.$relation['reference']->getOwningTable()->getRelationToTable($relation['refTable']->getRawTableName())->getForeign()->getColumnName().'", referencedColumnName="'.$relation['reference']->getOwningTable()->getRelationToTable($relation['refTable']->getRawTableName())->getLocal()->getColumnName().'")}'))
                    ->write(' * )')
                    ->write(' */')
                ;
            } else {
                $writer
                    ->write('/**')
                    ->write(' * '.$this->addPrefix('ManyToMany(targetEntity="'.$relation['refTable']->getModelName().'", mappedBy="'.lcfirst(Pluralizer::pluralize($this->getModelName())).'")'))
                    ->write(' */')
                ;
            }
            $writer
                ->write('private $'.lcfirst(Pluralizer::pluralize($relation['refTable']->getModelName())).';')
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
                ->write(' * Add '.$relation['refTable']->getModelName().' entity to collection.')
                ->write(' *')
                ->write(' * @param '. $relation['refTable']->getNamespace().' $'.lcfirst($relation['refTable']->getModelName()))
                ->write(' * @return '.$this->getNamespace($this->getModelName()))
                ->write(' */')
                ->write('public function add'.$relation['refTable']->getModelName().'('.$relation['refTable']->getModelName().' $'.lcfirst($relation['refTable']->getModelName()).')')
                ->write('{')
                ->indent()
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