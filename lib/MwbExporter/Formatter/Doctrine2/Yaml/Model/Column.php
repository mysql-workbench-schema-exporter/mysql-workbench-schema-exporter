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

namespace MwbExporter\Formatter\Doctrine2\Yaml\Model;

use MwbExporter\Formatter\Doctrine2\Model\Column as BaseColumn;
use Doctrine\Common\Inflector\Inflector;

class Column extends BaseColumn
{
    public function asYAML()
    {
        $values = array();
        $values['type'] = $this->getFormatter()->getDatatypeConverter()->getMappedType($this);
        if (($length = $this->getParameters()->get('length')) && ($length != -1)) {
            $values['length'] = (int) $length;
        }
        if (($precision = $this->getParameters()->get('precision')) && ($precision != -1) && ($scale = $this->getParameters()->get('scale')) && ($scale != -1)) {
            $values['precision'] = (int) $precision;
            $values['scale'] = (int) $scale;
        }
        if ($this->isUnique) {
            $values['unique'] = true;
        }
        if (!$this->isNotNull()) {
            $values['nullable'] = true;
        }
        if($this->isUnsigned()) {
            $values['unsigned'] = true;
        }
        if ($this->isAutoIncrement()) {
            $values['generator'] = array('strategy' => 'AUTO');
        }

        return $values;
    }

    public function relationsAsYAML()
    {
        $values = array();
        $formatter = $this->getFormatter();
        // one to many references
        foreach ($this->foreigns as $foreign) {
            // do not create entities for many2many tables
            if ($foreign->getForeign()->getTable()->isManyToMany()) {
                continue;
            }
            // do not output mapping in foreign table when the unidirectional option is set
            if ($foreign->parseComment('unidirectional') === 'true') {
                continue;
            }
            $targetEntity     = $foreign->getReferencedTable()->getModelName();
            $targetEntityFQCN = $foreign->getReferencedTable()->getModelNameAsFQCN($foreign->getOwningTable()->getEntityNamespace());
            $mappedBy         = $foreign->getOwningTable()->getModelName();
            $relationName     = $foreign->getReferencedTable()->getRawTableName();
            // check for OneToOne or OneToMany relationship
            if ($foreign->isManyToOne()) {
                // OneToMany
                if (!isset($values[static::RELATION_ONE_TO_MANY])) {
                    $values[static::RELATION_ONE_TO_MANY] = array();
                }
                $values[static::RELATION_ONE_TO_MANY][Inflector::pluralize($relationName)] = array(
                    'targetEntity'  => $targetEntityFQCN,
                    'mappedBy'      => lcfirst($mappedBy),
                    'cascade'       => $formatter->getCascadeOption($foreign->parseComment('cascade')),
                    'fetch'         => $formatter->getFetchOption($foreign->parseComment('fetch')),
                    'orphanRemoval' => $formatter->getBooleanOption($foreign->parseComment('orphanRemoval')),
                    'joinColumn'    => array(
                        'name'                 => $foreign->getLocal()->getColumnName(),
                        'referencedColumnName' => $foreign->getForeign()->getColumnName(),
                        'onDelete'             => $formatter->getDeleteRule($foreign->getForeign()->getParameters()->get('deleteRule')),
                        'nullable'             => !$foreign->getForeign()->isNotNull() ? null : false,
                    ),
                );
            } else {
                // OneToOne
                if (!isset($values[static::RELATION_ONE_TO_ONE])) {
                    $values[static::RELATION_ONE_TO_ONE] = array();
                }
                $values[static::RELATION_ONE_TO_ONE][$relationName] = array(
                    'targetEntity'  => $targetEntityFQCN,
                    'joinColumn'    => array(
                        'name'                 => $foreign->getLocal()->getColumnName(),
                        'referencedColumnName' => $foreign->getForeign()->getColumnName(),
                        'onDelete'             => $formatter->getDeleteRule($foreign->getForeign()->getParameters()->get('deleteRule')),
                        'nullable'             => !$foreign->getForeign()->isNotNull() ? null : false,
                    ),
                );
            }
        }
        // many to one references
        foreach ($this->getLocalForeignKeys() as $local) {
            $targetEntity     = $local->getReferencedTable()->getModelName();
            $targetEntityFQCN = $local->getReferencedTable()->getModelNameAsFQCN($local->getOwningTable()->getEntityNamespace());
            $inversedBy       = $local->getOwningTable()->getModelName();
            $relationName     = $local->getReferencedTable()->getRawTableName();
            // check for OneToOne or ManyToOne relationship
            if ($local->isManyToOne()) {
                // ManyToOne
                if (!isset($values[static::RELATION_MANY_TO_ONE])) {
                    $values[static::RELATION_MANY_TO_ONE] = array();
                }
                $values[static::RELATION_MANY_TO_ONE][$relationName] = array(
                    'targetEntity' => $targetEntity,
                    'inversedBy'   => $local->parseComment('unidirectional') === 'true' ? null : lcfirst(Inflector::pluralize($inversedBy)),
                    'joinColumn'   => array(
                        'name'                 => $local->getForeign()->getColumnName(),
                        'referencedColumnName' => $local->getLocal()->getColumnName(),
                        'onDelete'             => $formatter->getDeleteRule($local->getParameters()->get('deleteRule')),
                        'nullable'             => !$local->getForeign()->isNotNull() ? null : false,
                    ),
                );
            } else {
                // OneToOne
                if (!isset($values[static::RELATION_ONE_TO_ONE])) {
                    $values[static::RELATION_ONE_TO_ONE] = array();
                }
                $values[static::RELATION_ONE_TO_ONE][$relationName] = array(
                    'targetEntity' => $targetEntity,
                    'joinColumn'   => array(
                        'name'                 => $local->getForeign()->getColumnName(),
                        'referencedColumnName' => $local->getLocal()->getColumnName(),
                        'onDelete'             => $formatter->getDeleteRule($local->getParameters()->get('deleteRule')),
                        'nullable'             => !$local->getForeign()->isNotNull() ? null : false,
                    ),
                );
            }
        }

        return $values;
    }
}
