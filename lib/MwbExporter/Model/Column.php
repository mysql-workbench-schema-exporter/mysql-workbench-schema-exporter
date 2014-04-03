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
    protected $isUnique = false;

    /**
     * @var array \MwbExporter\Model\ForeignKey
     */
    protected $locals = array();

    /**
     * @var array \MwbExporter\Model\ForeignKey
     */
    protected $foreigns = array();

    /**
     * Constructor.
     *
     * @param \MwbExporter\Model\Base $parent
     * @param \SimpleXMLElement $node
     */
    public function __construct(Base $parent = null, $node = null)
    {
        $this->links = new RegistryHolder();
        parent::__construct($parent, $node);
    }

    protected function init()
    {
        $this->getDocument()->addLog(sprintf('Processing column "%s.%s".', $this->getTable()->getRawTableName(), $this->getColumnName()));
        // iterate on links to other wb objects
        foreach ($this->node->xpath("link") as $key => $node) {
            $attributes         = $node->attributes();
            $key                = (string) $attributes['key'];
            $this->links->set((string) $attributes['key'], (string) $node[0]);
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
     * Get raw column name.
     *
     * @return string
     */
    public function getColumnName()
    {
        return $this->getName();
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
     * Set the column as unique
     */
    public function markAsUnique()
    {
        $this->isUnique = true;
    }

    /**
     * Set the local foreign key.
     *
     * @param \MwbExporter\Model\ForeignKey $foreign
     */
    public function markAsLocalReference(ForeignKey $foreign)
    {
        $this->locals[$foreign->getId()] = $foreign;
    }

    /**
     * Add foreign key reference.
     *
     * @param \MwbExporter\Model\ForeignKey $foreign
     */
    public function markAsForeignReference(ForeignKey $foreign)
    {
        $this->foreigns[$foreign->getId()] = $foreign;
    }

    /**
     * Get local foreign keys reference.
     *
     * @return array \MwbExporter\Model\ForeignKey
     */
    public function getLocalForeignKeys()
    {
        return $this->locals;
    }

    /**
     * Get foreign keys reference.
     *
     * @return array \MwbExporter\Model\ForeignKey
     */
    public function getForeignKeys()
    {
        return $this->foreigns;
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
        return $this->checkReferenceTableName($foreign ? $foreign->getReferencedTable() : null, $tablename);
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
        $tablename = $fkey->getReferencedTable()->getRawTableName();
        $columns = array();
        foreach ($this->getTable()->getColumns() as $column) {
            foreach ($column->foreigns as $foreign) {
                // process only unique foreign key
                if (in_array($foreign->getId(), $columns)) {
                    continue;
                }
                $columns[] = $foreign->getId();
                if ($this->checkForeignKeyOwnerTableName($foreign, $tablename)) {
                    $count++;
                }
                if ($max && $count == $max) {
                    break;
                }
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
        $tablename = $fkey->getReferencedTable()->getRawTableName();
        foreach ($this->getTable()->getTableM2MRelations() as $relation) {
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
        return $this->isMultiReferences($reference) ? $this->formatRelatedName($reference->getLocal()->getColumnName(), $code) : '';
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
     * Is column not null (aka. required).
     *
     * @return boolean
     */
    public function isNotNull()
    {
        return 1 == $this->parameters->get('isNotNull') ? true : false;
    }

    /**
     * Is column auto increment.
     *
     * @return boolean
     */
    public function isAutoIncrement()
    {
        return 1 == $this->parameters->get('autoIncrement') ? true : false;
    }

    /**
     * Is the field an unsigned value
     * 
     * @return boolean
     */
    public function isUnsigned()
    {
        $flags = $this->parameters->get('flags');
        if (is_array($flags)) {
            return array_key_exists('UNSIGNED', array_flip($flags));
        }
        return false;
    }

    /**
     * Get column default value.
     *
     * @return string
     */
    public function getDefaultValue()
    {
        if (1 != $this->parameters->get('defaultValueIsNull')) {
            if (($defaultValue = trim($this->parameters->get('defaultValue'), '\'"')) && ('NULL' != $defaultValue)) {
                return $defaultValue;
            }
        }
    }

    /**
     * Get column length.
     *
     * @return int
     */
    public function getLength()
    {
        return $this->parameters->get('length');
    }
}
