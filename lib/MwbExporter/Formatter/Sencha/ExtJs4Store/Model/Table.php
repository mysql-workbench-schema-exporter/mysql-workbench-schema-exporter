<?php

/*
 * The MIT License
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

/**
 * Table.php
 * Created on May 5, 2013 9:46:52 PM
 *
 * @author    Boy van Moorsel <development@wittestier.nl>
 * @license   license.wittestier.nl
 * @copyright 2013 WitteStier <copyright@wittestier.nl>
 */

namespace MwbExporter\Formatter\Sencha\ExtJS4Store\Model;

use MwbExporter\Formatter\Sencha\Model\Table as BaseTable;
use MwbExporter\Formatter\Sencha\ExtJS4Store\Formatter;
use MwbExporter\Writer\WriterInterface;

class Table
    extends BaseTable
{

    /**
     * Write store.
     * 
     * @param \MwbExporter\Writer\WriterInterface $writer
     * @return type
     */
    public function writeTable(WriterInterface $writer)
    {
        $writer->open($this->getTableFileName());
        $this->writeBody($writer);
        $writer->close();
        return self::WRITE_OK;
    }

    /**
     * Write store body code.
     * 
     * @param \MwbExporter\Writer\WriterInterface $writer
     * @return \MwbExporter\Formatter\Sencha\ExtJS4Store\Model\Table
     */
    public function writeBody(WriterInterface $writer)
    {
        $writer
            ->write("Ext.define('%s', %s);", $this->getClassPrefix() . '.' . $this->getModelName(), $this->asModel())
        ;

        return $this;
    }

    public function asModel()
    {
        $result = array(
            'extend' => $this->getParentClass(),
            'model' => sprintf('%s.%s', $this->getModelPrefix(), $this->getModelName()),
        );
        if (count($data = $this->getUses())) {
            $result['uses'] = $data;
        }
        // TODO add model
        // TODO add config options.
        if ($this->getDocument()->getConfig()->get(Formatter::CFG_GENERATE_PROXY) && count($data = $this->getAjaxProxy())) {
            $result['proxy'] = $data;
        }

        return $this->getJSObject($result);
    }

    protected function getModelPrefix()
    {
        return $this->translateVars($this->getDocument()->getConfig()->get(Formatter::CFG_MODEL_PREFIX));
    }

    /**
     * Get uses.
     *
     * @link http://docs.sencha.com/extjs/4.2.0/#!/api/Ext.Class-cfg-uses
     * @return array
     */
    protected function getUses()
    {
        $result = array();
//        $current = sprintf('%s.%s', $this->getClassPrefix(), $this->getModelName());
//        // Collect belongsTo uses.
//        foreach ($this->relations as $relation) {
//            $refTableName = sprintf('%s.%s', $this->getClassPrefix(), $relation->getReferencedTable()->getModelName());
//            if ($relation->isManyToOne() && !in_array($refTableName, $result) && ($refTableName !== $current)) {
//                $result[] = $refTableName;
//            }
//        }
//
//        // Collect hasOne uses.
//        foreach ($this->relations as $relation) {
//            $refTableName = sprintf('%s.%s', $this->getClassPrefix(), $relation->getReferencedTable()->getModelName());
//            if (!$relation->isManyToOne() && !in_array($refTableName, $result) && ($refTableName !== $current)) {
//                $result[] = $refTableName;
//            }
//        }
//
//        // Collect hasMany uses.
//        foreach ($this->getManyToManyRelations() as $relation) {
//            $referencedTable = $relation['refTable'];
//            $refTableName = sprintf('%s.%s', $this->getClassPrefix(), $referencedTable->getModelName());
//            if (!in_array($refTableName, $result) && ($refTableName !== $current)) {
//                $result[] = $refTableName;
//            }
//        }

        return $result;
    }

    /**
     * Get model ajax proxy object.
     *
     * @link http://docs.sencha.com/extjs/4.2.0/#!/api/Ext.data.proxy.Ajax
     * @return array
     */
    protected function getAjaxProxy()
    {
        return array(
            'type' => 'ajax',
            'url' => sprintf('/data/%s', strtolower($this->getModelName())),
            'api' => $this->getApi(),
            'reader' => $this->getJsonReader(),
            'writer' => $this->getJsonWriter(),
        );
    }

    /**
     * Get the model API object.
     *
     * @link http://docs.sencha.com/extjs/4.2.0/#!/api/Ext.data.proxy.Ajax-cfg-api
     * @return array
     */
    private function getApi()
    {
        $modelName = strtolower($this->getModelName());

        return array(
            'read' => sprintf('/data/%s', $modelName),
            'update' => sprintf('/data/%s/update', $modelName),
            'create' => sprintf('/data/%s/create', $modelName),
            'destroy' => sprintf('/data/%s/destroy', $modelName),
        );
    }

    /**
     * Get the model json reader.
     *
     * @link http://docs.sencha.com/extjs/4.2.0/#!/api/Ext.data.reader.Json
     * @return array
     */
    private function getJsonReader()
    {
        return array(
            'type' => 'json',
            'root' => strtolower($this->getModelName()),
            'messageProperty' => 'message',
        );
    }

    /**
     * Get the model json writer.
     *
     * @link http://docs.sencha.com/extjs/4.2.0/#!/api/Ext.data.writer.Json
     * @return array
     */
    private function getJsonWriter()
    {
        return array(
            'type' => 'json',
            'root' => strtolower($this->getModelName()),
            'encode' => true,
            'expandData' => true,
        );
    }

}