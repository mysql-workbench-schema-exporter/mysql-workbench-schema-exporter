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

namespace MwbExporter\Formatter\Doctrine2\Yaml\Model;

use MwbExporter\Formatter\Doctrine2\Model\Table as BaseTable;
use MwbExporter\Formatter\Doctrine2\Yaml\Formatter;
use MwbExporter\Model\ForeignKey;
use MwbExporter\Writer\WriterInterface;
use MwbExporter\Object\YAML;
use MwbExporter\Helper\Comment;
use Doctrine\Common\Inflector\Inflector;

class Table extends BaseTable
{
    public function writeTable(WriterInterface $writer)
    {
        switch (true) {
            case $this->isExternal(): 
                return self::WRITE_EXTERNAL;

            case $this->getConfig()->get(Formatter::CFG_SKIP_M2M_TABLES) && $this->isManyToMany():
                return self::WRITE_M2M;

            default:
                $this->getDocument()->addLog(sprintf('Writing table "%s"', $this->getModelName()));

                $writer
                    ->open($this->getTableFileName())
                    ->writeCallback(function(WriterInterface $writer, Table $_this = null) {
                        if ($_this->getConfig()->get(Formatter::CFG_ADD_COMMENT)) {
                            $writer
                                ->write($_this->getFormatter()->getComment(Comment::FORMAT_YAML))
                                ->write('')
                            ;
                        }
                    })
                    ->write($this->asYAML())
                    ->close()
                ;

                return self::WRITE_OK;
        }
    }

    public function asYAML()
    {
        $namespace = $this->getNamespace(null, false);
        $values = array(
            'type' => 'entity',
            'table' => $this->getRawTableName(), 
        );
        if ($this->getConfig()->get(Formatter::CFG_AUTOMATIC_REPOSITORY)) {
            if ($repositoryNamespace = $this->getConfig()->get(Formatter::CFG_REPOSITORY_NAMESPACE)) {
                $repositoryNamespace .= '\\';
            }
            $values['repositoryClass'] = $repositoryNamespace.$this->getModelName().'Repository';
        }
        // indices
        $this->getIndicesAsYAML($values);
        // columns => ids & fields
        $this->getColumnsAsYAML($values);
        // table relations
        $this->getRelationsAsYAML($values);
        // table m2m relations
        $this->getM2MRelationsAsYAML($values);
        // lifecycle callback
        if (count($lifecycleCallbacks = $this->getLifecycleCallbacks())) {
            $values['lifecycleCallbacks'] = $lifecycleCallbacks;
        }

        return new YAML(array($namespace => $values), array('indent' => $this->getConfig()->get(Formatter::CFG_INDENTATION), 'skip_null_value' => true));
    }

    protected function getIndicesAsYAML(&$values)
    {
        foreach ($this->getTableIndices() as $index) {
            if (!isset($values['indexes'])) {
                $values['indexes'] = array();
            }
            $values['indexes'][$index->getName()] = $index->asYAML();
        }

        return $this;
    }

    protected function getColumnsAsYAML(&$values)
    {
        foreach ($this->getColumns() as $column) {
            if ($column->isPrimary()) {
                if (!isset($values['id'])) {
                    $values['id'] = array();
                }
                $values['id'][$column->getColumnName()] = $column->asYAML();
            } else {
                if (!isset($values['fields'])) {
                    $values['fields'] = array();
                }
                $values['fields'][$column->getColumnName()] = $column->asYAML();
            }
        }

        return $this;
    }

    protected function getRelationsAsYAML(&$values)
    {
        // 1 <=> ? references
        foreach ($this->getAllLocalForeignKeys() as $local) {
            if ($this->isLocalForeignKeyIgnored($local)) {
                continue;
            }
            $targetEntity     = $local->getOwningTable()->getModelName();
            $targetEntityFQCN = $local->getOwningTable()->getModelNameAsFQCN($local->getReferencedTable()->getEntityNamespace());
            $mappedBy         = $local->getReferencedTable()->getModelName();
            $related          = $local->getForeignM2MRelatedName();

            $this->getDocument()->addLog(sprintf('  Writing 1 <=> ? relation "%s"', $targetEntity));

            if ($local->isManyToOne()) {
                $this->getDocument()->addLog('  Relation considered as "1 <=> N"');

                $type = 'oneToMany';
                $relationName = lcfirst($this->getRelatedVarName($targetEntity, $related, true));
                if (!isset($values[$type])) {
                    $values[$type] = array();
                }
                $values[$type][$relationName] = array_merge(array(
                    'targetEntity'  => $targetEntity,
                    'mappedBy'      => lcfirst($this->getRelatedVarName($mappedBy, $related)),
                    'cascade'       => $this->getFormatter()->getCascadeOption($local->parseComment('cascade')),
                    'fetch'         => $this->getFormatter()->getFetchOption($local->parseComment('fetch')),
                    'orphanRemoval' => $this->getFormatter()->getBooleanOption($local->parseComment('orphanRemoval')),
                ), $this->getJoins($local));
            } else {
                $this->getDocument()->addLog('  Relation considered as "1 <=> 1"');

                $type = 'oneToOne';
                $relationName = lcfirst($targetEntity);
                if (!isset($values[$type])) {
                    $values[$type] = array();
                }
                $values[$type][$relationName] = array_merge(array(
                    'targetEntity' => $targetEntity,
                    'inversedBy'   => lcfirst($this->getRelatedVarName($mappedBy, $related)),
                ), $this->getJoins($local));
            }
        }

        // N <=> ? references
        foreach ($this->getAllForeignKeys() as $foreign) {
            if ($this->isForeignKeyIgnored($foreign)) {
                continue;
            }
            $targetEntity     = $foreign->getReferencedTable()->getModelName();
            $targetEntityFQCN = $foreign->getReferencedTable()->getModelNameAsFQCN($foreign->getOwningTable()->getEntityNamespace());
            $inversedBy       = $foreign->getOwningTable()->getModelName();
            $related          = $this->getRelatedName($foreign);

            $this->getDocument()->addLog(sprintf('  Writing N <=> ? relation "%s"', $targetEntity));

            if ($foreign->isManyToOne()) {
                $this->getDocument()->addLog('  Relation considered as "N <=> 1"');

                $type = 'manyToOne';
                $relationName = lcfirst($this->getRelatedVarName($targetEntity, $related));
                if (!isset($values[$type])) {
                    $values[$type] = array();
                }
                $values[$type][$relationName] = array_merge(array(
                    'targetEntity'  => $targetEntityFQCN,
                    'inversedBy'    => lcfirst($this->getRelatedVarName($inversedBy, $related, true)),
                ), $this->getJoins($foreign, false));
            } else {
                $this->getDocument()->addLog('  Relation considered as "1 <=> 1"');

                $type = 'oneToOne';
                $relationName = lcfirst($targetEntity);
                if (!isset($values[$type])) {
                    $values[$type] = array();
                }
                $values[$type][$relationName] = array_merge(array(
                    'targetEntity'  => $targetEntityFQCN,
                    'inversedBy'    => $foreign->parseComment('unidirectional') === 'true' ? null : lcfirst($this->getRelatedVarName($inversedBy, $related)),
                ), $this->getJoins($foreign, false));
            }
        }

        return $this;
    }

    protected function getM2MRelationsAsYAML(&$values)
    {
        // many to many relations
        foreach ($this->getTableM2MRelations() as $relation) {
            $isOwningSide = $this->getFormatter()->isOwningSide($relation, $mappedRelation);
            $mappings = array(
                'targetEntity' => $relation['refTable']->getModelNameAsFQCN($this->getEntityNamespace()),
                'mappedBy'     => null,
                'inversedBy'   => lcfirst(Inflector::pluralize($this->getModelName())),
                'cascade'      => $this->getFormatter()->getCascadeOption($relation['reference']->parseComment('cascade')),
                'fetch'        => $this->getFormatter()->getFetchOption($relation['reference']->parseComment('fetch')),
            );
            $relationName = Inflector::pluralize($relation['refTable']->getRawTableName());
            // if this is the owning side, also output the JoinTable Annotation
            // otherwise use "mappedBy" feature
            if ($isOwningSide) {
                if ($mappedRelation->parseComment('unidirectional') === 'true') {
                    unset($mappings['inversedBy']);
                }

                $type = 'manyToMany';
                if (!isset($values[$type])) {
                    $values[$type] = array();
                }
                $values[$type][$relationName] = array_merge($mappings, array(
                    'joinTable' => array(
                        'name'               => $relation['reference']->getOwningTable()->getRawTableName(),
                        'joinColumns'        => array(
                            'joinColumn'     => array(
                                'name'                 => $relation['reference']->getForeign()->getColumnName(),
                                'referencedColumnName' => $relation['reference']->getLocal()->getColumnName(),
                                'onDelete'             => $this->getFormatter()->getDeleteRule($relation['reference']->getParameters()->get('deleteRule')),
                            ),
                        ),
                        'inverseJoinColumns' => array(
                            'joinColumn'     => array(
                                'name'                 => $mappedRelation->getForeign()->getColumnName(),
                                'referencedColumnName' => $mappedRelation->getLocal()->getColumnName(),
                                'onDelete'             => $this->getFormatter()->getDeleteRule($mappedRelation->getParameters()->get('deleteRule')),
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

                $type = 'manyToMany';
                if (!isset($values[$type])) {
                    $values[$type] = array();
                }
                $values[$type][$relationName] = $mappings;
            }
        }

        return $this;
    }

    protected function convertJoinColumns($joins = array())
    {
        $result = array();
        foreach ($joins as $join) {
            if (!isset($join['name'])) {
                continue;
            }
            $key = $join['name'];
            unset($join['name']);
            $result[$key] = $join;
        }

        return $result;
    }

    /**
     * Get foreign key join descriptor.
     *
     * @param \MwbExporter\Model\ForeignKey $fkey  Foreign key
     * @param string $owningSide  Is join for owning side or vice versa
     * @return array
     */
    protected function getJoins(ForeignKey $fkey, $owningSide = true)
    {
        $joins = array();
        $lcols = $owningSide ? $fkey->getLocals() : $fkey->getForeigns();
        $fcols = $owningSide ? $fkey->getForeigns() : $fkey->getLocals();
        $onDelete = $this->getFormatter()->getDeleteRule($fkey->getParameters()->get('deleteRule'));
        for ($i = 0; $i < count($lcols); $i++) {
            $joins[] = array(
                'name'                  => $lcols[$i]->getColumnName(),
                'referencedColumnName'  => $fcols[$i]->getColumnName(),
                'nullable'              => $lcols[$i]->isNotNull() ? null : false,
                'onDelete'              => $onDelete,
            );
        }
    
        return count($joins) > 1 ? array('joinColumns' => $this->convertJoinColumns($joins)) : array('joinColumn' => $joins[0]);
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Model\Base::getVars()
     */
    protected function getVars()
    {
        return array_merge(parent::getVars(), array('%entity%' => str_replace('\\', '.', $this->getModelNameAsFQCN())));
    }
}
