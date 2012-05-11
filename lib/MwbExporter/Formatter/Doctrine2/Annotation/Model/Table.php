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
     *
     * @param array $relation
     * @return bool
     */
    public function isEnhancedManyToManyRelationDetection($relation)
    {
        if (false === $enhancedManyToManyDetection = $this->getDocument()->getConfig()->get(Formatter::CFG_ENHANCED_M2M_DETECTION)) {
            return false;
        }
        // ignore relation tables with more than two columns
        // if enhancedManyToMany config is set true
        // (it is most likely not an intended m2m relation)
        if ($relation['reference']->getOwningTable()->isManyToMany()) {
            return true;
        }

        return false;
    }

    /**
     * Write document as generated code.
     *
     * @param \MwbExporter\Writer\WriterInterface $writer
     */
    public function write(WriterInterface $writer)
    {
        if (!$this->isExternal()) {
            $writer->open($this->getTableFileName());
            $this->writeTable($writer);
            $writer->close();
        }
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
            ->writeCallback(function($writer) {
                $this->writeUsedClasses($writer);
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
                ->writeCallback(function($writer) use ($skipGetterAndSetter) {
                    $this->columns->write($writer);
                    $this->writeManyToMany($writer);
                    $this->writeConstructor($writer);
                    if (!$skipGetterAndSetter) {
                        $this->columns->writeGetterAndSetter($writer);
                        $this->writeManyToManyGetterAndSetter($writer);
                    }
                })
            ->outdent()
            ->write('}')
        ;
    }

    public function writeUsedClasses(WriterInterface $writer)
    {
        $count = 0;
        if ($this->ormPrefix == '@ORM\\') {
            $writer->write('use Doctrine\ORM\Mapping as ORM;');
            $count++;
        }
        $useArray = false;
        foreach ($this->manyToManyRelations as $relation) {
            if ($this->isEnhancedManyToManyRelationDetection($relation)) {
                continue;
            }
            $useArray = true;
            break;
        }
        if ($useArray || $this->getColumns()->hasOneToManyRelation()) {
            $writer->write('use %s;', $this->getCollectionClass());
            $count++;
        }
        if ($count) {
            $writer->write('');
        }
    }

    public function writeConstructor(WriterInterface $writer)
    {
        $writer
            ->write('public function __construct()')
            ->write('{')
            ->indent()
                ->writeCallback(function($writer) {
                    $this->columns->writeArrayCollections($writer);
                    foreach ($this->manyToManyRelations as $relation) {
                        if ($this->isEnhancedManyToManyRelationDetection($relation)) {
                            continue;
                        }
                        $writer->write('$this->%s = new %s();', lcfirst(Pluralizer::pluralize($relation['refTable']->getModelName())), $this->getCollectionClass(false));
                    }
                })
            ->outdent()
            ->write('}')
            ->write('')
        ;
    }

    public function writeManyToMany(WriterInterface $writer)
    {
        // @TODO D2A ManyToMany relation joinColumns and inverseColumns
        // referencing wrong column names
        foreach ($this->manyToManyRelations as $relation) {
            if ($this->isEnhancedManyToManyRelationDetection($relation)) {
                continue;
            }
            // if relation is not mapped yet define relation
            // otherwise use "mappedBy" feature
            if ($relation['reference']->local->getColumnName() != $relation['reference']->getOwningTable()->getRelationToTable($relation['refTable']->getRawTableName())->local->getColumnName()) {
                $writer
                    ->write('/**')
                    ->write(' * '.$this->addPrefix('ManyToMany(targetEntity="'.$relation['refTable']->getModelName().'")'))
                    ->write(' * '.$this->addPrefix('JoinTable(name="'.$relation['reference']->getOwningTable()->getRawTableName().'",'))
                    ->write(' *     joinColumns={'       .$this->addPrefix('JoinColumn(name="'.$relation['reference']->foreign->getColumnName().'", referencedColumnName="'.$relation['reference']->local->getColumnName().'")},'))
                    ->write(' *     inverseJoinColumns={'.$this->addPrefix('JoinColumn(name="'.$relation['reference']->getOwningTable()->getRelationToTable($relation['refTable']->getRawTableName())->foreign->getColumnName().'", referencedColumnName="'.$relation['reference']->getOwningTable()->getRelationToTable($relation['refTable']->getRawTableName())->local->getColumnName().'")}'))
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
    }

    public function writeManyToManyGetterAndSetter(WriterInterface $writer)
    {
        foreach ($this->manyToManyRelations as $relation) {
            if ($this->isEnhancedManyToManyRelationDetection($relation)) {
                continue;
            }
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
    }
}
