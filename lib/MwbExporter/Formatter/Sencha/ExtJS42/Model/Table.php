<?php

/*
 * The MIT License
 *
 * Copyright (c) 2012 Allan Sun <sunajia@gmail.com>
 * Copyright (c) 2012 Toha <tohenk@yahoo.com>
 * Copyright (c) 2013 WitteStier <development@wittestier.nl>
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

namespace MwbExporter\Formatter\Sencha\ExtJS42\Model;

use MwbExporter\Model\Table as BaseTable;
use MwbExporter\Writer\WriterInterface;
use MwbExporter\Formatter\Sencha\ExtJS42\Formatter;
use MwbExporter\Helper\JSObject;

class Table
    extends BaseTable
{

    /**
     * Get model class prefix from the configuration.
     * 
     * @return string
     */
    public function getClassPrefix()
    {
        return $this->translateVars($this->getDocument()->getConfig()->get(Formatter::CFG_CLASS_PREFIX));
    }

    /**
     * Get model class parent from the configuration.
     * 
     * @return type
     */
    public function getParentClass()
    {
        return $this->translateVars($this->getDocument()->getConfig()->get(Formatter::CFG_PARENT_CLASS));
    }

    /**
     * (non-PHPdoc)
     * 
     * @see \MwbExporter\Model\Table::write()
     * @param \MwbExporter\Writer\WriterInterface $writer
     * @return \MwbExporter\Formatter\Sencha\ExtJS42\Model\Table
     */
    public function write(WriterInterface $writer)
    {
        if (!$this->isExternal()) {
            $writer->open($this->getTableFileName());
            $this->writeTable($writer);
            $writer->close();
        }

        return $this;
    }

    /**
     * Write model body code.
     * 
     * @param \MwbExporter\Writer\WriterInterface $writer
     * @return \MwbExporter\Formatter\Sencha\ExtJS42\Model\Table
     */
    public function writeTable(WriterInterface $writer)
    {
        $writer
            ->write("Ext.define('%s', {", $this->getClassPrefix() . '.' . $this->getModelName())
            ->indent()
            ->write("extend: '%s',", $this->getParentClass())
            ->writeCallback(function(WriterInterface $writer, Table $_this = null) {
                    $_this->writeUses($writer);

                    $_this->writeBelongsTo($writer);

                    $_this->writeHasOne($writer);

                    $_this->writeHasMany($writer);

                    $_this->getColumns()->write($writer);

                    $_this->getColumns()->writeValidations($writer);

                    $_this->writeAjaxProxy($writer);
                })
            ->outdent()
            ->write('});')
        ;

        return $this;
    }

    /**
     * Write uses.
     * http://docs.sencha.com/ext-js/4-2/#!/api/Ext.Class-cfg-uses
     * 
     * @param \MwbExporter\Writer\WriterInterface $writer
     * @return \MwbExporter\Formatter\Sencha\ExtJS42\Model\Table
     */
    public function writeUses(WriterInterface $writer)
    {
        $uses = array();
        $primary = $this->columns[0];
        $current = sprintf('%s.%s', $this->getClassPrefix(), $this->getModelName());

        // Collect belongsTo uses.
        foreach ($primary->getForeignKeys() as $foreignKey) {
            $referencedTable = $foreignKey->getForeign()->getTable();
            $refTableName = sprintf('%s.%s', $this->getClassPrefix(), $referencedTable->getModelName());
            if (!$referencedTable->isManyToMany() && !in_array($refTableName, $uses) && ($refTableName !== $current)) {
                $uses[] = $refTableName;
            }
        }

        // Collect hasOne uses.
        foreach ($this->getRelations() as $relation) {
            $refTableName = sprintf('%s.%s', $this->getClassPrefix(), $relation->getReferencedTable()->getModelName());
            if (!in_array($refTableName, $uses) && ($refTableName !== $current)) {
                $uses[] = $refTableName;
            }
        }

        // Collect hasMany uses.
        foreach ($this->getManyToManyRelations() as $relation) {
            $referencedTable = $relation['refTable'];
            $refTableName = sprintf('%s.%s', $this->getClassPrefix(), $referencedTable->getModelName());
            if (!in_array($refTableName, $uses) && ($refTableName !== $current)) {
                $uses[] = $refTableName;
            }
        }

        $usesCount = count($uses);

        if (0 === $usesCount) {
            // End, No uses found.
            return $this;
        }

        $writer
            ->write('uses: [')
            ->indent()
            ->writeCallback(function(WriterInterface $writer) use($uses, $usesCount) {
                    foreach ($uses as $use) {
                        $use = sprintf("'%s'", $use);
                        if (--$usesCount) {
                            $use .= ',';
                        }

                        $writer->write($use);
                    }
                })
            ->outdent()
            ->write('],')
        ;

        // End.
        return $this;
    }

    /**
     * Write belongsTo relations.
     * http://docs.sencha.com/ext-js/4-2/#!/api/Ext.data.association.BelongsTo
     * 
     * @param \MwbExporter\Writer\WriterInterface $writer
     * @return \MwbExporter\Formatter\Sencha\ExtJS42\Model\Table
     */
    public function writeBelongsTo(WriterInterface $writer)
    {
        $primary = $this->columns[0];
        $belongToCount = $this->getBelongToCount();

        if (0 === $belongToCount) {
            // End, No belongTo relations found.
            return false;
        }

        $writer
            ->write('belongsTo: [')
            ->indent()
            ->writeCallback(function(WriterInterface $writer, Table $_this = null) use($primary, $belongToCount) {
                    foreach ($primary->getForeignKeys() as $foreignKey) {
                        $referencedTable = $foreignKey->getForeign()->getTable();
                        if ($referencedTable->isManyToMany()) {
                            // Do not write ManyToMany relations.
                            continue;
                        }

                        $hasMore = (bool) --$belongToCount;
                        $relation = (string) $_this->getJSObject(array(
                                'model' => sprintf('%s.%s', $_this->getClassPrefix(), $referencedTable->getModelName()),
                                'associationKey' => lcfirst($referencedTable->getModelName()),
                                'getterName' => sprintf('get%s', $referencedTable->getModelName()),
                                'setterName' => sprintf('set%s', $referencedTable->getModelName()),
                        ));

                        if ($hasMore) {
                            $relation .= ',';
                        }

                        $writer->write($relation);
                    }
                })
            ->outdent()
            ->write('],')
        ;

        // End.
        return $this;
    }

    /**
     * Write hasOne relations.
     * http://docs.sencha.com/ext-js/4-2/#!/api/Ext.data.association.HasOne
     * 
     * @param \MwbExporter\Writer\WriterInterface $writer
     * @return \MwbExporter\Formatter\Sencha\ExtJS42\Model\Table
     */
    public function writeHasOne(WriterInterface $writer)
    {
        $hasOneCount = $this->getHasOneCount();

        if (0 === $hasOneCount) {
            // End, No hasOne relations found.
            return false;
        }

        $writer
            ->write('hasOne: [')
            ->indent()
            ->writeCallback(function(WriterInterface $writer, Table $_this = null) use($hasOneCount) {
                    foreach ($_this->getRelations() as $relation) {
                        $hasMore = (bool) --$hasOneCount;
                        $refTable = $relation->getReferencedTable();
                        $relation = (string) $_this->getJSObject(array(
                                'model' => sprintf('%s.%s', $_this->getClassPrefix(), $refTable->getModelName()),
                                'associationKey' => lcfirst($refTable->getModelName()),
                                'getterName' => sprintf('get%s', $refTable->getModelName()),
                                'setterName' => sprintf('set%s', $refTable->getModelName()),
                        ));

                        if ($hasMore) {
                            $relation .= ',';
                        }

                        $writer->write($relation);
                    }
                })
            ->outdent()
            ->write('],')
        ;
        // End.
        return $this;
    }

    /**
     * Write hasMany relations.
     * http://docs.sencha.com/ext-js/4-2/#!/api/Ext.data.association.HasMany
     * 
     * @param \MwbExporter\Writer\WriterInterface $writer
     * @return \MwbExporter\Formatter\Sencha\ExtJS42\Model\Table
     */
    public function writeHasMany(WriterInterface $writer)
    {
        $hasManyCount = $this->getHasManyCount();

        if (0 === $hasManyCount) {
            // End, No belongTo relations found.
            return false;
        }

        $writer
            ->write('hasMany: [')
            ->indent()
            ->writeCallback(function(WriterInterface $writer, Table $_this = null) use($hasManyCount) {
                    foreach ($_this->getManyToManyRelations() as $relation) {
                        $referencedTable = $relation['refTable'];
                        $hasMore = (bool) --$hasManyCount;
                        $relation = (string) $_this->getJSObject(array(
                                'model' => sprintf('%s.%s', $_this->getClassPrefix(), $referencedTable->getModelName()),
                                'associationKey' => lcfirst($referencedTable->getModelName()),
                                'name' => sprintf('get%sStore', $referencedTable->getModelName()),
                        ));

                        if ($hasMore) {
                            $relation .= ',';
                        }

                        $writer->write($relation);
                    }
                })
            ->outdent()
            ->write('],')
        ;

        // End.
        return $this;
    }

    /**
     * Write model ajax proxy object.
     * http://docs.sencha.com/ext-js/4-2/#!/api/Ext.data.proxy.Ajax
     * 
     * @param \MwbExporter\Writer\WriterInterface $writer
     * @return \MwbExporter\Formatter\Sencha\ExtJS42\Model\Table
     */
    public function writeAjaxProxy(WriterInterface $writer)
    {
        $modelName = strtolower($this->getModelName());

        $writer
            ->write('proxy: ' . $this->getJSObject(array(
                    'type' => 'ajax',
                    'url' => sprintf('/data/%s', $modelName),
                    'api' => $this->getApi(),
                    'reader' => $this->getJsonReader(),
                    'writer' => $this->getJsonWriter()
            )))
        ;

        // End.
        return $this;
    }

    /**
     * Get JSObject.
     *
     * @param mixed $content    Object content
     * @param bool  $multiline  Multiline result
     * @param bool  $raw        Is raw object
     * @return \MwbExporter\Helper\JSObject
     */
    public function getJSObject($content, $multiline = true, $raw = false)
    {
        return new JSObject($content, array(
            'multiline' => $multiline,
            'raw' => $raw,
            'indent' => $this->getDocument()->getConfig()->get(Formatter::CFG_INDENTATION)
        ));
    }

    /**
     * Get the model API object.
     * http://docs.sencha.com/ext-js/4-2/#!/api/Ext.data.proxy.Ajax-cfg-api
     * 
     * @return \MwbExporter\Helper\JSObject
     */
    private function getApi()
    {
        $modelName = strtolower($this->getModelName());

        // End.
        return $this->getJSObject(array(
                'read' => sprintf('/data/%s', $modelName),
                'update' => sprintf('/data/%s/update', $modelName),
                'create' => sprintf('/data/%s/add', $modelName),
                'destroy' => sprintf('/data/%s/destroy', $modelName)
        ));
    }

    /**
     * Get the model json reader.
     * http://docs.sencha.com/ext-js/4-2/#!/api/Ext.data.reader.Json
     * 
     * @return \MwbExporter\Helper\JSObject
     */
    private function getJsonReader()
    {
        $modelName = strtolower($this->getModelName());

        // End.
        return $this->getJSObject(array(
                'type' => 'json',
                'root' => $modelName,
                'messageProperty' => 'message'
        ));
    }

    /**
     * Get the model json writer
     * http://docs.sencha.com/ext-js/4-2/#!/api/Ext.data.writer.Json
     * 
     * @return \MwbExporter\Helper\JSObject
     */
    private function getJsonWriter()
    {
        $modelName = strtolower($this->getModelName());

        // End.
        return $this->getJSObject(array(
                'type' => 'json',
                'root' => $modelName,
                'encode' => true,
                'expandData' => true
        ));
    }

    /**
     * Get the number of belong to relations.
     * All PRIMARY foreign keys minus the M2M relations.
     * 
     * @return int
     */
    private function getBelongToCount()
    {
        $count = 0;
        $primary = $this->columns[0];
        foreach ($primary->getForeignKeys() as $foreignKey) {
            if (!$foreignKey->getForeign()->getTable()->isManyToMany()) {
                $count++;
            }
        }

        // End.
        return $count;
    }

    /**
     * Get the number of hasOne relations.
     * 
     * @return int
     */
    private function getHasOneCount()
    {
        // End.
        return count($this->relations);
    }

    /**
     * Get the number of hasMany relations.
     * 
     * @return int
     */
    private function getHasManyCount()
    {
        // End.
        return count($this->manyToManyRelations);
    }

}