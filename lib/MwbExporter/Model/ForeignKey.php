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

namespace MwbExporter\Model;

class ForeignKey extends Base
{
    /**
     * @var \MwbExporter\Model\Table
     */
    protected $referencedTable = null;

    /**
     * @var \MwbExporter\Model\Tabel
     */
    protected $owningTable = null;

    /**
     * @var array \MwbExporter\Model\Column
     */
    protected $locals = array();

    /**
     * @var array \MwbExporter\Model\Column
     */
    protected $foreigns = array();

    protected function init()
    {
        $this->getDocument()->addLog(sprintf('Processing foreign key "%s.%s"', $this->getTable()->getRawTableName(), $this->getName()));
        // follow references to tables
        foreach ($this->node->link as $key => $node) {
            $attributes         = $node->attributes();
            $key                = (string) $attributes['key'];
            if ($key === 'referencedTable') {
                $this->referencedTable = $this->getReference()->get((string) $node);
            }
            if ($key === 'owner') {
                $this->owningTable = $this->getReference()->get((string) $node);
                $this->owningTable->injectRelation($this);
            }
        }

        $this->locals = array();
        foreach ($this->node->xpath("value[@key='columns']") as $column) {
            foreach ($column->children() as $link) {
                $this->locals[] = $this->getReference()->get((string) $link);
            }
        }

        $this->foreigns = array();
        foreach ($this->node->xpath("value[@key='referencedColumns']") as $column) {
            foreach ($column->children() as $link) {
                $this->foreigns[] = $this->getReference()->get((string) $link);
            }
        }

        // for doctrine2 annotations switch the local and the foreign
        // reference for a proper output
        foreach ($this->locals as $column) {
            $this->getDocument()->addLog(sprintf('Mark column %s.%s as foreign reference for %s',
                $column->getTable()->getRawTableName(), $column->getColumnName(), $this->getReferencedTable()->getRawTableName()));
            $column->markAsForeignReference($this);
        }
        foreach ($this->foreigns as $column) {
            $this->getDocument()->addLog(sprintf('Mark column %s.%s as local reference for %s',
                $column->getTable()->getRawTableName(), $column->getColumnName(), $this->getReferencedTable()->getRawTableName()));
            $column->markAsLocalReference($this);
        }
    }

    protected function hasParameters()
    {
        return true;
    }

    /**
     * Get the table owner.
     *
     * @return \MwbExporter\Model\Table
     */
    public function getTable()
    {
        return $this->getParent()->getParent();
    }

    /**
     * Get the referenced table.
     *
     * @return \MwbExporter\Model\Table
     */
    public function getReferencedTable()
    {
        return $this->referencedTable;
    }

    /**
     * Get owner table.
     *
     * @return \MwbExporter\Model\Table
     */
    public function getOwningTable()
    {
        return $this->owningTable;
    }

    /**
     * Get local column.
     * 
     * @return \MwbExporter\Model\Column
     * @deprecated
     */
    public function getLocal()
    {
        if (count($this->locals)) {
            return $this->locals[0];
        }
    }

    /**
     * Get local column references.
     *
     * @return array
     */
    public function getLocals()
    {
        return $this->locals;
    }

    /**
     * Get foreign column.
     *
     * @return \MwbExporter\Model\Column
     * @deprecated
     */
    public function getForeign()
    {
        if (count($this->foreigns)) {
            return $this->foreigns[0];
        }
    }

    /**
     * Get foreign column references.
     *
     * @return array
     */
    public function getForeigns()
    {
        return $this->foreigns;
    }

    /**
     * Get local columns name.
     *
     * @return array
     */
    public function getLocalColumns()
    {
        return array_map(function($column) {
            return $column->getColumnName();
        }, $this->getLocals());
    }

    /**
     * Get foreigns columns name.
     *
     * @return array
     */
    public function getForeignColumns()
    {
        return array_map(function($column) {
            return $column->getColumnName();
        }, $this->getForeigns());
    }

    /**
     * Get local related name.
     *
     * @param boolean $code
     * @return string
     */
    public function getLocalM2MRelatedName($code = true)
    {
        return $this->getReferencedTable()->getManyToManyRelatedName($this->getReferencedTable()->getRawTableName(), implode('_', $this->getLocalColumns()), $code);
    }

    /**
     * Get foreign related name.
     *
     * @param boolean $code
     * @return string
     */
    public function getForeignM2MRelatedName($code = true)
    {
        return $this->getReferencedTable()->getManyToManyRelatedName($this->getOwningTable()->getRawTableName(), implode('_', $this->getLocalColumns()), $code);
    }

    /**
     * Check relation if it is a many to one relation.
     *
     * @return boolean
     */
    public function isManyToOne()
    {
        return (bool) $this->parameters->get('many');
    }

    /**
     * Check if foreign key is a composite relation.
     *
     * @return boolean
     */
    public function isComposite()
    {
        return count($this->locals) > 1 ? true : false;
    }

    /**
     * Check if foreign key is uni directional.
     *
     * @return boolean
     */
    public function isUnidirectional()
    {
        return $this->parseComment('unidirectional') === 'true' ? true : false;
    }
}