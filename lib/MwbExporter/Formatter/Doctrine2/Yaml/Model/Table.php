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

namespace MwbExporter\Formatter\Doctrine2\Yaml\Model;

use MwbExporter\Model\Table as BaseTable;
use MwbExporter\Writer\WriterInterface;
use MwbExporter\Formatter\Doctrine2\Yaml\Formatter;
use MwbExporter\Object\YAML;
use MwbExporter\Helper\Pluralizer;

class Table extends BaseTable
{
    /**
     * Get the entity namespace.
     *
     * @return string
     */
    public function getEntityNamespace()
    {
        $namespace = '';
        if (($bundleNamespace = $this->parseComment('bundleNamespace')) || ($bundleNamespace = $this->getDocument()->getConfig()->get(Formatter::CFG_BUNDLE_NAMESPACE))) {
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

    public function writeTable(WriterInterface $writer)
    {
        if (!$this->isExternal()) {
            $writer
                ->open($this->getTableFileName())
                ->write($this->asYAML())
                ->close()
            ;

            return self::WRITE_OK;
        }

        return self::WRITE_EXTERNAL;
    }

    public function asYAML()
    {
        $namespace = $this->getNamespace(null, false);
        $values = array(
            'type' => 'entity',
            'table' => $this->getRawTableName(), 
        );
        // indices
        if (count($this->getIndexes())) {
            $values['indexes'] = array();
            foreach ($this->getIndexes() as $index) {
                $values['indexes'][$index->getParameters()->get('name')] = $index->asYAML();
            }
        }
        // id, fields, relations
        $ids = array();
        $fields = array();
        $oneToOne = array();
        $oneToMany = array();
        $manyToOne = array();
        $manyToMany = array();
        foreach ($this->getColumns() as $column) {
            if ($column->isPrimary()) {
                $ids[$column->getParameters()->get('name')] = $column->asYAML();
            } else {
                $fields[$column->getParameters()->get('name')] = $column->asYAML();
            }
            foreach ($column->relationsAsYAML() as $key => $relation) {
                switch ($key) {
                    case Column::RELATION_ONE_TO_ONE:
                        $oneToOne = array_merge($oneToOne, $relation);
                        break;

                    case Column::RELATION_ONE_TO_MANY:
                        $oneToMany = array_merge($oneToMany, $relation);
                        break;

                    case Column::RELATION_MANY_TO_ONE:
                        $manyToOne = array_merge($manyToOne, $relation);
                        break;

                    case Column::RELATION_MANY_TO_MANY:
                        $manyToMany = array_merge($manyToMany, $relation);
                        break;
                }
            }
        }
        // many to many relations
        $formatter = $this->getDocument()->getFormatter();
        foreach ($this->manyToManyRelations as $relation) {
            $isOwningSide = $formatter->isOwningSide($relation, $mappedRelation);
            $mappings = array(
                'targetEntity' => $relation['refTable']->getModelName(),
                'mappedBy'     => null,
                'inversedBy'   => lcfirst(Pluralizer::pluralize($this->getModelName())),
                'cascade'      => $formatter->getCascadeOption($relation['reference']->parseComment('cascade')),
                'fetch'        => $formatter->getFetchOption($relation['reference']->parseComment('fetch')),
            );
            $relationName = $relation['refTable']->getRawTableName();
            // if this is the owning side, also output the JoinTable Annotation
            // otherwise use "mappedBy" feature
            if ($isOwningSide) {
                if ($mappedRelation->parseComment('unidirectional') === 'true') {
                    unset($mappings['inversedBy']);
                }
                $manyToMany[$relationName] = array_merge($mappings, array(
                    'joinTable' => array(
                        'name'               => $relation['reference']->getOwningTable()->getRawTableName(),
                        'joinColumns'        => array(
                            'joinColumn'     => array(
                                'name'                 => $relation['reference']->getForeign()->getColumnName(),
                                'referencedColumnName' => $relation['reference']->getLocal()->getColumnName(),
                                'onDelete'             => $formatter->getDeleteRule($relation['reference']->getParameters()->get('deleteRule')),
                            ),
                        ),
                        'inverseJoinColumns' => array(
                            'joinColumn'     => array(
                                'name'                 => $mappedRelation->getForeign()->getColumnName(),
                                'referencedColumnName' => $mappedRelation->getLocal()->getColumnName(),
                                'onDelete'             => $formatter->getDeleteRule($mappedRelation->getParameters()->get('deleteRule')),
                            ),
                        )
                    ),
                ));
            } else {
                if ($relation['reference']->parseComment('unidirectional') === 'true') {
                    continue;
                }
                $mappings['mappedBy'] = $mappings['inversedBy'];
                $mappings['inversedBy'] = null;
                $manyToMany[$relationName] = $mappings;
            }
        }

        // update values
        if (count($ids)) {
            $values['id'] = $ids;
        }
        if (count($fields)) {
            $values['fields'] = $fields;
        }
        if (count($oneToOne)) {
            $values['oneToOne'] = $oneToOne;
        }
        if (count($oneToMany)) {
            $values['oneToMany'] = $oneToMany;
        }
        if (count($manyToOne)) {
            $values['manyToOne'] = $manyToOne;
        }
        if (count($manyToMany)) {
            $values['manyToMany'] = $manyToMany;
        }
        if ($lifecycleCallbacks = $this->parseComment('lifecycleCallbacks')) {
            $values['lifecycleCallbacks'] = $lifecycleCallbacks;
        }

        return new YAML(array($namespace => $values), array('indent' => $this->getDocument()->getConfig()->get(Formatter::CFG_INDENTATION)));
    }
}
