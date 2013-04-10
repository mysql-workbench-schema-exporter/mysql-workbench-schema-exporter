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

use MwbExporter\Model\Column as BaseColumn;
use MwbExporter\Helper\Pluralizer;

class Column extends BaseColumn
{
    const RELATION_ONE_TO_ONE = '1:1';
    const RELATION_ONE_TO_MANY = '1:M';
    const RELATION_MANY_TO_ONE = 'M:1';
    const RELATION_MANY_TO_MANY = 'M:M';

    public function asYAML()
    {
        $values = array();
        $values['type'] = $this->getDocument()->getFormatter()->getDatatypeConverter()->getMappedType($this);
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
        if ($this->getParameters()->get('isNotNull') != 1) {
            $values['nullable'] = true;
        }
        if ($this->getParameters()->get('autoIncrement') == 1) {
            $values['generator'] = array('strategy' => 'AUTO');
        }

        return $values;
    }

    public function relationsAsYAML()
    {
        $values = array();
        $formatter = $this->getDocument()->getFormatter();
        // one to many references
        foreach ($this->foreigns as $foreign) {
            if ($foreign->getForeign()->getTable()->isManyToMany()) {
                // do not create entities for many2many tables
                continue;
            }
            if ($foreign->parseComment('unidirectional') === 'true') {
                // do not output mapping in foreign table when the unidirectional option is set
                continue;
            }
            $targetEntity = $foreign->getOwningTable()->getModelName();
            $mappedBy     = $foreign->getReferencedTable()->getModelName();
            $relationName = $foreign->getOwningTable()->getRawTableName();
            // check for OneToOne or OneToMany relationship
            if ($foreign->isManyToOne()) {
                // OneToMany
                if (!isset($values[static::RELATION_ONE_TO_MANY])) {
                    $values[static::RELATION_ONE_TO_MANY] = array();
                }
                $values[static::RELATION_ONE_TO_MANY][$relationName] = array(
                    'targetEntity'  => $targetEntity,
                    'mappedBy'      => lcfirst($mappedBy),
                    'cascade'       => $formatter->getCascadeOption($foreign->parseComment('cascade')),
                    'fetch'         => $formatter->getFetchOption($foreign->parseComment('fetch')),
                    'orphanRemoval' => $formatter->getBooleanOption($foreign->parseComment('orphanRemoval')),
                    'joinColumn'    => array(
                        'name'                 => $foreign->getForeign()->getColumnName(),
                        'referencedColumnName' => $foreign->getLocal()->getColumnName(),
                        'onDelete'             => $formatter->getDeleteRule($foreign->getLocal()->getParameters()->get('deleteRule')),
                        'nullable'             => !$foreign->getForeign()->getParameters()->get('isNotNull') ? null : false,
                    ),
                );
            } else {
                // OneToOne
                if (!isset($values[static::RELATION_ONE_TO_ONE])) {
                    $values[static::RELATION_ONE_TO_ONE] = array();
                }
                $values[static::RELATION_ONE_TO_ONE][$relationName] = array(
                    'targetEntity'  => $targetEntity,
                    'joinColumn'    => array(
                        'name'                 => $foreign->getForeign()->getColumnName(),
                        'referencedColumnName' => $foreign->getLocal()->getColumnName(),
                        'onDelete'             => $formatter->getDeleteRule($foreign->getLocal()->getParameters()->get('deleteRule')),
                        'nullable'             => !$foreign->getForeign()->getParameters()->get('isNotNull') ? null : false,
                    ),
                );
            }
        }
        // many to one references
        if (null !== $this->local) {
            $targetEntity = $this->local->getReferencedTable()->getModelName();
            $inversedBy   = $this->local->getOwningTable()->getModelName();
            $relationName = $this->local->getReferencedTable()->getRawTableName();
            // check for OneToOne or ManyToOne relationship
            if ($this->local->isManyToOne()) {
                // ManyToOne
                if (!isset($values[static::RELATION_MANY_TO_ONE])) {
                    $values[static::RELATION_MANY_TO_ONE] = array();
                }
                $values[static::RELATION_MANY_TO_ONE][$relationName] = array(
                    'targetEntity' => $targetEntity,
                    'inversedBy'   => $this->local->parseComment('unidirectional') === 'true' ? null : lcfirst(Pluralizer::pluralize($inversedBy)),
                    'joinColumn'   => array(
                        'name'                 => $this->local->getForeign()->getColumnName(),
                        'referencedColumnName' => $this->local->getLocal()->getColumnName(),
                        'onDelete'             => $formatter->getDeleteRule($this->local->getParameters()->get('deleteRule')),
                        'nullable'             => !$this->local->getForeign()->getParameters()->get('isNotNull') ? null : false,
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
                        'name'                 => $this->local->getForeign()->getColumnName(),
                        'referencedColumnName' => $this->local->getLocal()->getColumnName(),
                        'onDelete'             => $formatter->getDeleteRule($this->local->getParameters()->get('deleteRule')),
                        'nullable'             => !$this->local->getForeign()->getParameters()->get('isNotNull') ? null : false,
                    ),
                );
            }
        }

        return $values;
    }
}
