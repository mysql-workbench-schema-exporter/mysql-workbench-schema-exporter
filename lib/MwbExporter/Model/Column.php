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

namespace MwbExporter\Model;

use MwbExporter\Registry\RegistryHolder;

class Column extends Base
{
    /**
     * @var \MwbExporter\Registry\RegistryHolder
     */
    protected $links = null;

    /**
     * @var bool
     */
    protected $isPrimary = false;

    /**
     * @var bool
     */
    protected $isUnique  = false;

    /**
     * @var \MwbExporter\Model\ForeignKey
     */
    protected $local    = null;

    /**
     * @var array \MwbExporter\Model\ForeignKey
     */
    protected $foreigns = array();

    protected function init()
    {
        $this->links = new RegistryHolder();
        // iterate on column configuration
        foreach ($this->node->xpath("value") as $key => $node) {
            $attributes         = $node->attributes();
            $this->parameters->set((string) $attributes['key'], (string) $node[0]);
        }
        // iterate on links to other wb objects
        foreach ($this->node->xpath("link") as $key => $node) {
            $attributes         = $node->attributes();
            $key                = (string) $attributes['key'];
            $this->links->set((string) $attributes['key'], (string) $node[0]);
        }
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
     * Get raw column name.
     *
     * @return string
     */
    public function getColumnName()
    {
        return $this->parameters->get('name');
    }

    /**
     * Get column type, either by its simpleType or userType.
     *
     * @return string
     */
    public function getColumnType()
    {
        if (!($type = $this->links->get('simpleType'))) {
            $type = $this->links->get('userType');
        }

        return $type;
    }

    /**
     * Set the column as primary key
     */
    public function markAsPrimary()
    {
        $this->isPrimary = true;
    }

    /**
     * return true if the column is a primary key
     *
     * @return boolean
     */
    public function isPrimary()
    {
        return $this->isPrimary;
    }

    /**
     * Set the column as unique
     */
    public function markAsUnique()
    {
        $this->isUnique = true;
    }

    /**
     * Set the local foreign key.
     *
     * @param \MwbExporter\Model\ForeignKey
     */
    public function markAsLocalReference(ForeignKey $local)
    {
        $this->local = $local;
    }

    /**
     * Add foreign key reference.
     *
     * @param \MwbExporter\Model\ForeignKey $foreign
     */
    public function markAsForeignReference(ForeignKey $foreign)
    {
        $this->foreigns[($foreign->getId())] = $foreign;
    }

    /**
     * Get local foreign key.
     *
     * @return \MwbExporter\Model\ForeignKey
     */
    public function getLocalForeignKey()
    {
        return $this->local;
    }

    /**
     *
     * @param string $columnName
     * @return string
     */
    protected function columnNameBeautifier($columnName)
    {
        return ucfirst(preg_replace('@\_(\w)@e', 'ucfirst("$1")', $columnName));
    }

    /**
     * Check if foreign key owner tablename matched.
     *
     * @param ForeignKey $foreign    The foreign key
     * @param string     $tablename  The table name
     * @return bool
     */
    protected function checkForeignKeyOwnerTableName($foreign, $tablename)
    {
        return $this->checkReferenceTableName($foreign ? $foreign->getOwningTable() : null, $tablename);
    }

    /**
     * Check if reference tablename matched.
     *
     * @param Table   $table      The reference table
     * @param string  $tablename  The table name
     * @return bool
     */
    protected function checkReferenceTableName($table, $tablename)
    {
        return ($table && $table->getRawTableName() === $tablename) ? true : false;
    }

    /**
     * Get the foreign key reference count.
     *
     * @param ForeignKey $fkey   The foreign key
     * @param int        $max    The maximum count
     * @return int
     */
    protected function getForeignKeyReferenceCount($fkey, $max = null)
    {
        $count = 0;
        $tablename = $fkey->getOwningTable()->getRawTableName();
        foreach ($this->foreigns as $foreign) {
            if ($this->checkForeignKeyOwnerTableName($foreign, $tablename)) {
                $count++;
            }
            if ($max && $count == $max) {
                break;
            }
        }

        return $count;
    }

    /**
     * Get the relation reference count.
     *
     * @param ForeignKey $fkey   The foreign key
     * @param int        $max    The maximum count
     * @return int
     */
    protected function getRelationReferenceCount($fkey, $max = null)
    {
        $count = 0;
        $tablename = $fkey->getOwningTable()->getRawTableName();
        foreach ($this->getTable()->getManyToManyRelations() as $relation) {
            // $relation key => reference (ForeignKey), refTable (Table)
            if ($this->checkReferenceTableName($relation['refTable'], $tablename)) {
                $count++;
            }
            if ($max && $count == $max) {
                break;
            }
        }

        return $count;
    }

    /**
     * Check if foreign table reference is referenced by more than one column.
     *
     * @param ForeignKey $reference   The foreign key to check
     * @return bool
     */
    public function isMultiReferences($reference)
    {
        $count = 0;
        if ($reference) {
            // check foreign keys
            $count += $this->getForeignKeyReferenceCount($reference);
            // check relations
            $count += $this->getRelationReferenceCount($reference);
        }

        return $count > 1 ? true : false;
    }

    /**
     * Format column name as relation to foreign table.
     *
     * @param string $column  The column name
     * @param bool   $code    If true, use result as PHP code or false, use as comment
     * @return string
     */
    public function formatRelatedName($column, $code = true)
    {
        return $code ? sprintf('RelatedBy%s', $this->columnNameBeautifier($column)) : sprintf('related by `%s`', $column);
    }

    /**
     * Get the related name for one-to-many relation.
     *
     * @param ForeignKey $reference   The foreign key
     * @param bool       $code        If true, use result as PHP code or false, use as comment
     * @return string
     */
    public function getRelatedName($reference, $code = true)
    {
        return $this->isMultiReferences($reference) ? $this->formatRelatedName($reference->getForeign()->getColumnName(), $code) : '';
    }

    /**
     * Get the related name for many-to-many relation.
     *
     * @param string $tablename   The foreign tablename
     * @param string $column      The foreign column name
     * @param bool   $code        If true, use result as PHP code or false, use as comment
     * @return string
     */
    public function getManyToManyRelatedName($tablename, $column, $code = true)
    {
        return $this->getParent()->getManyToManyCount($tablename) > 1 ? $this->formatRelatedName($column, $code) : '';
    }

    /**
     * Is column has one to many relation.
     *
     * @return bool
     */
    public function hasOneToManyRelation()
    {
        foreach ($this->foreigns as $foreign) {
            if ($foreign->isManyToOne()) {
                return true;
            }
        }

        return false;
    }
}
