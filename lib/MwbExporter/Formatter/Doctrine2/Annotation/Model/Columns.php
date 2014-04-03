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

use MwbExporter\Model\Columns as BaseColumns;
use MwbExporter\Writer\WriterInterface;

class Columns extends BaseColumns
{
    /**
     * Collected unique foreign keys for all columns.
     *
     * @var array
     */
    protected $collectedForeignKeys = array();

    /**
     * Collected unique local foreign keys for all columns.
     *
     * @var array
     */
    protected $collectedLocalForeignKeys = array();

    public function write(WriterInterface $writer)
    {
        // display column
        foreach ($this->columns as $column) {
            // do not output fields of relations.
            if (!$column->isPrimary() && (count($column->getLocalForeignKeys()) || $column->hasOneToManyRelation())) {
                continue;
            }
            $column->write($writer);
        }
        $this->emptyCollectedForeignKeys();
        // display column relations
        foreach ($this->columns as $column) {
            $column->writeRelations($writer);
        }

        return $this;
    }

    public function writeArrayCollections(WriterInterface $writer)
    {
        $this->emptyCollectedForeignKeys();
        foreach ($this->columns as $column) {
            $column->writeArrayCollection($writer);
        }

        return $this;
    }

    public function writeGetterAndSetter(WriterInterface $writer)
    {
        // column getter and setter
        foreach ($this->columns as $column) {
            // do not output fields of relations.
            if (!$column->isPrimary() && (count($column->getLocalForeignKeys()) || $column->hasOneToManyRelation())) {
                continue;
            }
            $column->writeGetterAndSetter($writer);
        }
        $this->emptyCollectedForeignKeys();
        // column getter and setter for relations
        foreach ($this->columns as $column) {
            $column->writeRelationsGetterAndSetter($writer);
        }

        return $this;
    }

    protected function emptyCollectedForeignKeys()
    {
        $this->collectedForeignKeys = array();
        $this->collectedLocalForeignKeys = array();
    }

    /**
     * Collect column foreign key.
     *
     * @param \MwbExporter\Model\ForeignKey $foreignKey
     * @return \MwbExporter\Formatter\Doctrine2\Annotation\Model\Columns
     */
    public function collectForeignKey($foreignKey)
    {
        if (!array_key_exists($foreignKey->getId(), $this->collectedForeignKeys)) {
            $this->collectedForeignKeys[$foreignKey->getId()] = $foreignKey;
        }

        return $this;
    }

    /**
     * Check if foreign key already collected.
     *
     * @param \MwbExporter\Model\ForeignKey $foreignKey
     * @return boolean
     */
    public function isCollectedForeignKeyExist($foreignKey)
    {
        return array_key_exists($foreignKey->getId(), $this->collectedForeignKeys);
    }

    /**
     * Collect local column foreign key.
     *
     * @param \MwbExporter\Model\ForeignKey $foreignKey
     * @return \MwbExporter\Formatter\Doctrine2\Annotation\Model\Columns
     */
    public function collectLocalForeignKey($foreignKey)
    {
        if (!array_key_exists($foreignKey->getId(), $this->collectedLocalForeignKeys)) {
            $this->collectedLocalForeignKeys[$foreignKey->getId()] = $foreignKey;
        }

        return $this;
    }

    /**
     * Check if local foreign key already collected.
     *
     * @param \MwbExporter\Model\ForeignKey $foreignKey
     * @return boolean
     */
    public function isCollectedLocalForeignKeyExist($foreignKey)
    {
        return array_key_exists($foreignKey->getId(), $this->collectedLocalForeignKeys);
    }
}