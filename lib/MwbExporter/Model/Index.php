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

class Index extends Base
{
    /**
     * @var array
     */
    protected $columns = array();

    protected function init()
    {
        $this->getDocument()->addLog(sprintf('Processing index "%s.%s"', $this->getTable()->getRawTableName(), $this->getName()));
        // check for primary columns, to notify column
        $nodes = $this->node->xpath("value[@key='columns']/value/link[@key='referencedColumn']");
        foreach ($nodes as $node) {
            // for primary indexes ignore external index
            // definition and set column to primary instead
            if (!($column = $this->getReference()->get((string) $node))) {
                continue;
            }
            if ($this->isPrimary()) {
                $column->markAsPrimary();
            }
            if ($this->isUnique()) {
                $column->markAsUnique();
            }
            $this->columns[] = $column;
        }
        if (!$this->isPrimary() && ($table = $this->getReference()->get((string) $this->node->link))) {
            $table->injectIndex($this);
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
     * Is a unique index
     *
     * @return boolean
     */
    public function isUnique()
    {
        return $this->parameters->get('indexType') === 'UNIQUE' ? true : false;
    }

    /**
     * Is a normal index
     *
     * @return boolean
     */
    public function isIndex()
    {
        return $this->parameters->get('indexType') === 'INDEX' ? true : false;
    }

    /**
     * Is a primary index
     *
     * @return boolean
     */
    public function isPrimary()
    {
        return $this->parameters->get('indexType') === 'PRIMARY' ? true : false;
    }

    /**
     * Get index columns.
     *
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Get index column names.
     *
     * @return array
     */
    public function getColumnNames()
    {
        $columns = array();
        foreach ($this->columns as $refColumn) {
            $columns[] = $refColumn->getColumnName();
        }

        return $columns;
    }
}