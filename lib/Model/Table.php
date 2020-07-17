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

use MwbExporter\Formatter\FormatterInterface;
use MwbExporter\Writer\WriterInterface;
use Doctrine\Common\Inflector\Inflector;

class Table extends Base
{
    const WRITE_OK = 1;
    const WRITE_EXTERNAL = 2;
    const WRITE_M2M = 3;

    /**
     * @var \MwbExporter\Model\Columns
     */
    protected $columns = null;

    /**
     * @var \MwbExporter\Model\Indices
     */
    protected $indices = null;

    /**
     * @var \MwbExporter\Model\ForeignKeys
     */
    protected $foreignKeys = null;

    /**
     * @var array
     */
    protected $_indexes = array();

    /**
     * @var array
     */
    protected $_relations = array();

    /**
     * @var array
     */
    protected $_m2mRelations = array();

    /**
     * @var bool
     */
    protected $isM2M = null;

    protected function init()
    {
        $this->getDocument()->addLog(sprintf('Processing table "%s"', $this->getRawTableName()));
        $this->initColumns();
    }

    protected function hasParameters()
    {
        return true;
    }

    /**
     * Initialize table columns.
     *
     * @return \MwbExporter\Model\Table
     */
    public function initColumns()
    {
        $elems = $this->node->xpath("value[@key='columns']");
        $this->columns = $this->getFormatter()->createColumns($this, $elems[0]);

        return $this;
    }

    /**
     * Initialize table indices.
     *
     * @return \MwbExporter\Model\Table
     */
    public function initIndices()
    {
        $elems = $this->node->xpath("value[@key='indices']");
        $this->indices = $this->getFormatter()->createIndices($this, $elems[0]);

        return $this;
    }

    /**
     * Initialize table foreign keys.
     *
     * @return \MwbExporter\Model\Table
     */
    public function initForeignKeys()
    {
        $elems = $this->node->xpath("value[@key='foreignKeys']");
        $this->foreignKeys = $this->getFormatter()->createForeignKeys($this, $elems[0]);

        return $this;
    }

    /**
     * Initialize many to many relations.
     *
     * @return \MwbExporter\Model\Table
     */
    public function initManyToManyRelations()
    {
        if ($this->isManyToMany()) {
            $fk1 = $this->foreignKeys[0];
            $fk2 = $this->foreignKeys[1];
            $this->injectManyToMany($fk1, $fk2);
            $this->injectManyToMany($fk2, $fk1);
        }

        return $this;
    }

    /**
     * Inject a many to many relation into referenced table.
     *
     * @param \MwbExporter\Model\ForeignKey $fk1
     * @param \MwbExporter\Model\ForeignKey $fk2
     * @return \MwbExporter\Model\Table
     */
    protected function injectManyToMany(ForeignKey $fk1, ForeignKey $fk2)
    {
        $fk1->getReferencedTable()->setManyToManyRelation(array('reference' => $fk1, 'refTable' => $fk2->getReferencedTable()));

        return $this;
    }

    /**
     * Get the owner schema.
     *
     * @return \MwbExporter\Model\Schema
     */
    public function getSchema()
    {
        return $this->getParent()->getParent();
    }

    /**
     * Get columns model.
     *
     * @return \MwbExporter\Model\Columns;
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Get indices model.
     *
     * @return \MwbExporter\Model\Indices
     */
    public function getIndices()
    {
        return $this->indices;
    }

    /**
     * Get foreign keys model.
     *
     * @return \MwbExporter\Model\ForeignKeys
     */
    public function getForeignKeys()
    {
        return $this->foreignKeys;
    }

    /**
     * Get table category.
     *
     * @return string
     */
    public function getCategory()
    {
        if ($category = trim($this->parseComment('category'))) {
            return $category;
        }
    }

    /**
     * Check if table is an external entity.
     *
     * @return boolean
     */
    public function isExternal()
    {
        $external = trim($this->parseComment('external'));
        if ($external === 'true') {
            return true;
        }

        return false;
    }

    /**
     * Check if this table is a many to many table.
     *
     * @param bool $deep True to check many to many relation for referenced tables
     * @return bool
     */
    public function isManyToMany($deep = true)
    {
        if (null === $this->isM2M) {
            switch (true) {
                // user hinted that this is a m2m table or not
                case in_array($m2m = $this->parseComment('m2m'), array('true', 'false')):
                    $this->isM2M = 'true' === $m2m ? true : false;
                    $this->getDocument()->addLog(sprintf('  * %s: M2M from comment "%s"', $this->getRawTableName(), var_export($this->isM2M, true)));
                    break;

                // contains 2 foreign keys
                case (2 !== count($fkeys = $this->getForeignKeys())):
                    $this->isM2M = false;
                    $this->getDocument()->addLog(sprintf('  * %s: M2M set to false, foreign keys not equal to 2', $this->getRawTableName()));
                    break;

                // different foreign tables
                case ($fkeys[0]->getReferencedTable()->getId() === $fkeys[1]->getReferencedTable()->getId()):
                    $this->isM2M = false;
                    $this->getDocument()->addLog(sprintf('  * %s: M2M set to false, foreign table is same', $this->getRawTableName()));
                    break;

                // foreign tables is not many to many
                case $deep && $fkeys[0]->getReferencedTable()->isManyToMany(false):
                case $deep && $fkeys[1]->getReferencedTable()->isManyToMany(false):
                    $this->isM2M = false;
                    $this->getDocument()->addLog(sprintf('  * %s: M2M set to false, foreign table is M2M', $this->getRawTableName()));
                    break;

                // has more columns than id + 2 x key columnns, is not many to many
                case (count($this->getColumns()) >= 3):
                    $this->isM2M = false;
                    $this->getDocument()->addLog(sprintf('  * %s: M2M set to false, columns are 3 or more', $this->getRawTableName()));
                    break;

                default:
                    $this->isM2M = true;
                    $this->getDocument()->addLog(sprintf('  * %s: is M2M', $this->getRawTableName()));
                    break;
            }
        }

        return $this->isM2M;
    }

    /**
     * Check if this table is a translation table.
     *
     * @return bool
     */
    public function isTranslationTable()
    {
        $return = preg_match('@^(.*)\_translation$@', $this->getRawTableName(), $matches);
        if ($return) {
            $return = $matches[1];
        }

        return $return;
    }

    /**
     * Add a many to many relation.
     *
     * @param array $rel  The relation
     * @return \MwbExporter\Model\Table
     */
    public function setManyToManyRelation($rel)
    {
        $key = $rel['refTable']->getModelName();
        $this->_m2mRelations[$key] = $rel;
        $this->getDocument()->addLog(sprintf('Applying N <=> N relation "%s" for "%s <=> %s"', $rel['refTable']->getParameters()->get('name'), $this->getModelName(), $key));

        return $this;
    }

    /**
     * Get raw table name.
     *
     * @return string
     */
    public function getRawTableName()
    {
        return $this->getName();
    }

    /**
     * Get the table model name.
     *
     * @return string
     */
    public function getModelName()
    {
        $tableName = $this->getRawTableName();

        // check if table name is plural --> convert to singular
        if (
            !$this->getConfig()->get(FormatterInterface::CFG_SKIP_PLURAL) &&
            ($tableName != ($singular = Inflector::singularize($tableName)))
        ) {
            $tableName = $singular;
        }

        return $this->beautify($tableName);
    }

    /**
     * Get the table model name in plural form.
     *
     * @return string
     */
    public function getPluralModelName()
    {
        return Inflector::pluralize($this->getModelName());
    }

    /**
     * Inject index.
     *
     * @param \MwbExporter\Model\Index $index
     */
    public function injectIndex(Index $index)
    {
        foreach ($this->_indexes as $_index) {
            if ($_index->getId() === $index->getId()) {
                return;
            }
        }
        $this->_indexes[] = $index;
    }

    /**
     * Inject relation.
     *
     * @param \MwbExporter\Model\ForeignKey $foreignKey
     */
    public function injectRelation(ForeignKey $foreignKey)
    {
        foreach ($this->_relations as $_relation) {
            if ($_relation->getId() === $foreignKey->getId()) {
                return;
            }
        }
        $this->_relations[] = $foreignKey;
    }

    /**
     * Get indexes.
     *
     * @return array
     */
    public function getTableIndices()
    {
        return $this->_indexes;
    }

    /**
     * Get relations.
     *
     * @return array
     */
    public function getTableRelations()
    {
        return $this->_relations;
    }

    /**
     * Get many to many relations.
     *
     * @return array
     */
    public function getTableM2MRelations()
    {
        return $this->_m2mRelations;
    }

    /**
     * Return relation betweens the current table and the $rawTableName table
     *
     * @param string $rawTableName
     * @return \MwbExporter\Model\ForeignKey|null
     */
    public function getRelationToTable($rawTableName)
    {
        foreach ($this->_relations as $relation) {
            if ($relation->getReferencedTable()->getRawTableName() === $rawTableName) {
                return $relation;
            }
        }
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Model\Base::getVars()
     */
    protected function getVars()
    {
        return array(
            '%table%'     => $this->getRawTableName(),
            '%entity%'    => $this->getModelName(),
            '%category%'  => $this->getCategory(),
        );
    }

    /**
     * Get table file name.
     *
     * @param string $format  The filename format
     * @param array $vars  The overriden variables
     * @return string
     */
    public function getTableFileName($format = null, $vars = array())
    {
        if (0 === strlen($filename = $this->getDocument()->translateFilename($format, $this, $vars)))
        {
            $filename = implode('.', array($this->getSchema()->getName(), $this->getRawTableName(), $this->getFormatter()->getFileExtension()));
        }

        return $filename;
    }

    /**
     * Get all foreign keys references.
     *
     * @return array \MwbExporter\Model\ForeignKey
     */
    public function getAllForeignKeys()
    {
        $columns = array();
        foreach ($this->getColumns() as $column) {
            foreach ($column->getForeignKeys() as $foreignKey) {
                if (array_key_exists($foreignKey->getId(), $columns)) {
                    continue;
                }
                $columns[$foreignKey->getId()] = $foreignKey;
            }
        }

        return $columns;
    }

    /**
     * Get all local foreign keys references.
     *
     * @return array \MwbExporter\Model\ForeignKey
     */
    public function getAllLocalForeignKeys()
    {
        $columns = array();
        foreach ($this->getColumns() as $column) {
            foreach ($column->getLocalForeignKeys() as $foreignKey) {
                if (array_key_exists($foreignKey->getId(), $columns)) {
                    continue;
                }
                $columns[$foreignKey->getId()] = $foreignKey;
            }
        }

        return $columns;
    }

    /**
     * Check if foreign key should be ignored.
     *
     * @param \MwbExporter\Model\ForeignKey
     * @return boolean
     */
    public function isForeignKeyIgnored($foreignKey)
    {
        // do not create entities for many2many tables
        if ($this->getConfig()->get(FormatterInterface::CFG_SKIP_M2M_TABLES) && $foreignKey->getReferencedTable()->isManyToMany()) {
            return true;
        }

        return false;
    }

    /**
     * Check if local foreign key should be ignored.
     *
     * @param \MwbExporter\Model\ForeignKey
     * @return boolean
     */
    public function isLocalForeignKeyIgnored($foreignKey)
    {
        // do not create entities for many2many tables
        if ($this->getConfig()->get(FormatterInterface::CFG_SKIP_M2M_TABLES) && $foreignKey->getOwningTable()->isManyToMany()) {
            return true;
        }
        // do not output mapping in foreign table when the unidirectional option is set
        if ($foreignKey->parseComment('unidirectional') === 'true') {
            return true;
        }

        return false;
    }

    /**
     * Get the foreign key reference count.
     *
     * @param \MwbExporter\Model\ForeignKey $fkey   The foreign key
     * @param int        $max    The maximum count
     * @return int
     */
    protected function getForeignKeyReferenceCount($fkey, $max = null)
    {
        $count = 0;
        $tablename = $fkey->getReferencedTable()->getRawTableName();
        $columns = array();
        foreach ($this->getColumns() as $column) {
            foreach ($column->getForeignKeys() as $foreignKey) {
                // process only unique foreign key
                if (in_array($foreignKey->getId(), $columns)) {
                    continue;
                }
                $columns[] = $foreignKey->getId();
                if ($this->checkForeignKeyOwnerTableName($foreignKey, $tablename)) {
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
     * @param \MwbExporter\Model\ForeignKey $fkey   The foreign key
     * @param int        $max    The maximum count
     * @return int
     */
    protected function getRelationReferenceCount($fkey, $max = null)
    {
        $count = 0;
        $tablename = $fkey->getReferencedTable()->getRawTableName();
        foreach ($this->getTableM2MRelations() as $relation) {
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
     * @param \MwbExporter\Model\ForeignKey $reference   The foreign key to check
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
     * Check if foreign key owner tablename matched.
     *
     * @param \MwbExporter\Model\ForeignKey $foreign    The foreign key
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
     * @param \MwbExporter\Model\Table   $table      The reference table
     * @param string  $tablename  The table name
     * @return bool
     */
    protected function checkReferenceTableName($table, $tablename)
    {
        return ($table && $table->getRawTableName() === $tablename) ? true : false;
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
        return $code ? sprintf('RelatedBy%s', $this->beautify($column)) : sprintf('related by `%s`', $column);
    }

    /**
     * Get the related name for one-to-many relation.
     *
     * @param \MwbExporter\Model\ForeignKey $reference   The foreign key
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
        // foreign tables count
        $count = $this->getColumns()->getManyToManyCount($tablename);
        // m2m foreign tables count
        foreach ($this->getTableM2MRelations() as $relation) {
            if ($relation['refTable']->getRawTableName() === $tablename) {
                $count++;
            }
        }

        return $count > 1 ? $this->formatRelatedName($column, $code) : '';
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Model\Base::write()
     */
    public function write(WriterInterface $writer)
    {
        try {
            if (strlen($category = $this->getConfig()->get(FormatterInterface::CFG_EXPORT_TABLE_CATEGORY)) && 
                $this->getCategory() != $category) {
                $status = 'skipped, not in category';
            } else {
                switch ($this->writeTable($writer)) {
                    case self::WRITE_OK:
                        $status = 'OK';
                        break;

                    case self::WRITE_EXTERNAL:
                        $status = 'skipped, marked as external';
                        break;

                    case self::WRITE_M2M:
                        $status = 'skipped, M2M table';
                        break;

                    default:
                        $status = 'unsupported';
                        break;
                }
            }
            $this->getDocument()->addLog(sprintf('* %s: %s', $this->getRawTableName(), $status));
        } catch (\Exception $e) {
            $this->getDocument()->addLog(sprintf('* %s: ERROR', $this->getRawTableName()));
            throw $e;
        }

        return $this;
    }

    /**
     * Write table entity as code.
     *
     * @param \MwbExporter\Writer\WriterInterface $writer
     * @return string
     */
    public function writeTable(WriterInterface $writer)
    {
        if (!$this->isExternal()) {
            $this->getColumns()->write($writer);
            $this->getIndices()->write($writer);
            $this->getForeignKeys()->write($writer);

            return self::WRITE_OK;
        }

        return self::WRITE_EXTERNAL;
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Model\Base::getSortValue()
     */
    public function getSortValue()
    {
        return $this->getCategory().$this->getModelName();
    }
}