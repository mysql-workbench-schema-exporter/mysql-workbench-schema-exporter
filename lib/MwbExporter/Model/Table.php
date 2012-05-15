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

namespace MwbExporter\Model;

use MwbExporter\FormatterInterface;
use MwbExporter\Helper\Pluralizer;
use MwbExporter\Helper\Singularizer;
use MwbExporter\Writer\WriterInterface;

class Table extends Base
{
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

    protected function init()
    {
        $this->initColumns();
        foreach ($this->node->value as $key => $node) {
            $attributes = $node->attributes();
            $this->parameters->set((string) $attributes['key'], (string) $node[0]);
        }
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
     * @return bool
     */
    public function isManyToMany()
    {
        // contains 2 foreign keys
        if (2 !== count($fkeys = $this->getForeignKeys())) {
            return false;
        }
        // different foreign tables
        if ($fkeys[0]->getId() === $fkeys[1]->getId()) {
            return false;
        }
        // foreign tables is not many to many
        // @FIXME memory allocation exhaustion --> recursion
        // if ($fkeys[0]->getReferencedTable()->isManyToMany() || $fkeys[1]->getReferencedTable()->isManyToMany()) {
            // return false;
        // }

        return true;
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
        $tablename = $this->getRawTableName();
        // check if table name is plural --> convert to singular
        if (!$this->getDocument()->getConfig()->get(FormatterInterface::CFG_SKIP_PLURAL) && Pluralizer::wordIsPlural($tablename)) {
            $tablename = Singularizer::singularize($tablename);
        }

        // camleCase under scores for model names
        return ucfirst(preg_replace('@\_(\w)@e', 'ucfirst("$1")', $tablename));
    }

    /**
     * Return the model in the plural form
     *
     * @return string
     */
    public function getModelNameInPlural()
    {
        return Pluralizer::pluralize($this->getModelName());
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
     * Translate text with table contextual data.
     *
     * @param string $text  The text to translate
     * @return string
     */
    public function translateVars($text)
    {
        return strtr($text, array('%schema%' => $this->getSchema()->getName(), '%table%' => $this->getRawTableName(), '%entity%' => $this->getModelName(), '%extension%' => $this->getDocument()->getFormatter()->getFileExtension()));
    }

    /**
     * Get table file name.
     *
     * @throws \Exception
     * @return string
     */
    public function getTableFileName()
    {
        if ($filename = $this->translateVars($this->getDocument()->getConfig()->get(FormatterInterface::CFG_FILENAME)))
        {
            if (false !== strpos($filename, '%')) {
                throw new \Exception(sprintf('All filename variable where not converted. Perhaps a misstyped name (%s) ?', substr($filename, strpos($filename, '%'), strrpos($filename, '%'))));
            }
        } else {
            $filename = $this->getSchema()->getName().'.'.$this->getRawTableName().'.'.$this->getDocument()->getFormatter()->getFileExtension();
        }

        return $filename;
    }

    /**
     * (non-PHPdoc)
     * @see MwbExporter\Model.Base::write()
     */
    public function write(WriterInterface $writer)
    {
        if (!$this->isExternal()) {
            $this->getColumns()->write($writer);
            $this->getIndices()->write($writer);
            $this->getForeignKeys()->write($writer);
        }

        return $this;
    }
}