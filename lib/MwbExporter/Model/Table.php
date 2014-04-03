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

use MwbExporter\Formatter\FormatterInterface;
use Doctrine\Common\Inflector\Inflector;
use MwbExporter\Writer\WriterInterface;

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
    protected $indexes     = array();

    /**
     * @var array
     */
    protected $relations   = array();

    /**
     * @var array
     */
    protected $manyToManyRelations = array();

    /**
     * @var bool
     */
    protected $isM2M = null;

    protected function init()
    {
        foreach ($this->node->value as $key => $node) {
            $attributes = $node->attributes();
            $this->parameters->set((string) $attributes['key'], (string) $node[0]);
        }
        $this->getDocument()->addLog(sprintf('Processing table "%s".', $this->getRawTableName()));
        $this->initColumns();
    }

    /**
     * Initialize table columns.
     *
     * @return \MwbExporter\Model\Table
     */
    public function initColumns()
    {
        $elems = $this->node->xpath("value[@key='columns']");
        $this->columns = $this->getDocument()->getFormatter()->createColumns($this, $elems[0]);

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
        $this->indices = $this->getDocument()->getFormatter()->createIndices($this, $elems[0]);

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
        $this->foreignKeys = $this->getDocument()->getFormatter()->createForeignKeys($this, $elems[0]);

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
     * Get indexes.
     *
     * @return array
     */
    public function getIndexes()
    {
        return $this->indexes;
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
     *
     * @return boolean
     */
    public function isExternal()
    {
        $external = trim($this->parseComment('external', $this->parameters->get('comment')));
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
                    break;

                // contains 2 foreign keys
                case (2 !== count($fkeys = $this->getForeignKeys())):
                    $this->isM2M = false;
                    break;

                // different foreign tables
                case ($fkeys[0]->getReferencedTable()->getId() === $fkeys[1]->getReferencedTable()->getId()):
                    $this->isM2M = false;
                    break;

                // foreign tables is not many to many
                case $deep && $fkeys[0]->getReferencedTable()->isManyToMany(false):
                case $deep && $fkeys[1]->getReferencedTable()->isManyToMany(false):
                    $this->isM2M = false;
                    break;

                // has more columns than id + 2 x key columnns, is not many to many
                case (count($this->getColumns()) >= 3):
                    $this->isM2M = false;
                    break;

                default:
                    $this->isM2M = true;
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
     * Get many to many relations.
     *
     * @return array
     */
    public function getManyToManyRelations()
    {
        return $this->manyToManyRelations;
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
        $this->manyToManyRelations[$key] = $rel;
        $this->getDocument()->addLog(sprintf('Applying N <=> N relation "%s" for "%s <=> %s".', $rel['refTable']->getParameters()->get('name'), $this->getModelName(), $key));

        return $this;
    }

    /**
     * Get raw table name.
     *
     * @return string
     */
    public function getRawTableName()
    {
        return $this->parameters->get('name');
    }

    /**
     * Get the table name in the form of camel cased.
     *
     * @return string
     */
    public function getModelName()
    {
        $tableName = $this->getRawTableName();
        // check if table name is plural --> convert to singular

        if (
            !$this->getDocument()->getConfig()->get(FormatterInterface::CFG_SKIP_PLURAL) &&
            ($tableName != ($singular = Inflector::singularize($tableName)))
        ) {
            $tableName = $singular;
        }

        // camleCase under scores for model names
        return ucfirst(preg_replace('@\_(\w)@e', 'ucfirst("$1")', $tableName));
    }

    /**
     * Return the model in the plural form
     *
     * @return string
     */
    public function getModelNameInPlural()
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
        foreach ($this->indexes as $_index) {
            if ($_index->getId() === $index->getId()) {
                return;
            }
        }
        $this->indexes[] = $index;
    }

    /**
     * Inject relation.
     *
     * @param \MwbExporter\Model\ForeignKey $foreignKey
     */
    public function injectRelation(ForeignKey $foreignKey)
    {
        foreach ($this->relations as $_relation) {
            if ($_relation->getId() === $foreignKey->getId()) {
                return;
            }
        }
        $this->relations[] = $foreignKey;
    }

    /**
     *
     * @return array
     */
    public function getRelations()
    {
        return $this->relations;
    }

    /**
     * Return relation betweens the current table and the $rawTableName table
     *
     * @param string $rawTableName
     * @return MwbExporter\Model\ForeignKey|null
     */
    public function getRelationToTable($rawTableName)
    {
        foreach ($this->relations as $relation) {
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
      return array('%schema%' => $this->getSchema()->getName(), '%table%' => $this->getRawTableName(), '%entity%' => $this->getModelName(), '%extension%' => $this->getDocument()->getFormatter()->getFileExtension());
    }

    /**
     * Get table file name.
     *
     * @throws \Exception
     * @return string
     */
    public function getTableFileName()
    {
        if (0 === strlen($filename = $this->getDocument()->translateFilename($this)))
        {
            $filename = $this->getSchema()->getName().'.'.$this->getRawTableName().'.'.$this->getDocument()->getFormatter()->getFileExtension();
        }

        return $filename;
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Model\Base::write()
     */
    public function write(WriterInterface $writer)
    {
        try {
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
}