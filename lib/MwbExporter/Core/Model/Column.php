<?php
/*
 *  The MIT License
 *
 *  Copyright (c) 2010 Johannes Mueller <circus2(at)web.de>
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is
 *  furnished to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in
 *  all copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

namespace MwbExporter\Core\Model;

use MwbExporter\Core\Registry;

abstract class Column extends Base
{
    protected $config = array();
    protected $link   = array();
    protected $isPrimary = false;
    protected $isUnique  = false;

    protected $local    = null;
    protected $foreigns = null;

    public function __construct($data, $parent)
    {
        parent::__construct($data, $parent);

        // iterate on column configuration
        foreach($this->data->xpath("value") as $key => $node){
            $attributes         = $node->attributes();         // read attributes

            $key                = (string) $attributes['key']; // assign key
            $this->config[$key] = (string) $node[0];           // assign value
        }

        // iterate on links to other wb objects
        foreach($this->data->xpath("link") as $key => $node){
            $attributes         = $node->attributes();

            $key                = (string) $attributes['key'];
            $this->link[$key]   = (string) $node[0];
        }

        Registry::set($this->id, $this);
    }

    /**
     * Return the current column's configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Return the current SimpleXMLElement
     *
     * @return SimpleXMLElement
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Return the name of column link to other wb objects.
     *
     * @param string $key  The link key
     * @return string
     */
    public function getLink($key)
    {
        return isset($this->link[$key]) ? $this->link[$key] : null;
    }

    /**
     * Return column configuration value.
     *
     * @param string $name  The config name
     * @return string
     */
    public function getConfigValue($name)
    {
        return isset($this->config[$name]) ? $this->config[$name] : null;
    }

    /**
     * Get column type, either by its simpleType or userType.
     *
     * @return string
     */
    public function getType()
    {
        if (!($type = $this->getLink('simpleType'))) {
            $type = $this->getLink('userType');
        }

        return $type;
    }

    /**
     * Set the column as primary key
     *
     * @return null
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
     *
     * @return null
     */
    public function markAsUnique()
    {
        $this->isUnique = true;
    }

    public function markAsLocalReference(ForeignKey $local)
    {
        $this->local = $local;
    }

    public function markAsForeignReference(ForeignKey $foreign)
    {
        if($this->foreigns == null) {
            $this->foreigns = array();
        }
        $this->foreigns[($foreign->getId())] = $foreign;
        // $this->foreigns[] = $foreign; // unfortunately this code doubles the references
    }

    public function getColumnName()
    {
        return $this->config['name'];
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
     * Get local foreign key.
     *
     * @return ForeignKey
     */
    public function getLocalForeignKey()
    {
        return $this->local;
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
        if (is_array($this->foreigns)) {
            $tablename = $fkey->getOwningTable()->getRawTableName();
            foreach($this->foreigns as $foreign) {
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
        $table = $this->getParent()->getParent();
        if (is_array($relations = $table->getRelations())) {
            $tablename = $fkey->getOwningTable()->getRawTableName();
            foreach($relations as $foreign) {
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
        return $this->isMultiReferences($reference) ? $this->formatRelatedName($reference->foreign->getColumnName(), $code) : '';
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
        if (is_array($this->foreigns)) {
            foreach ($this->foreigns as $foreign) {
                if ($foreign->isManyToOne()) {
                    return true;
                }
            }
        }

        return false;
    }
}