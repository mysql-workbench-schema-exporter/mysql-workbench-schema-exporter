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

                    $_this->getColumns()->writeFields($writer);

                    $_this->getColumns()->writeValidations($writer);

                    $_this->writeAjaxProxy($writer);
                })
            ->outdent()
            ->write('});')
        ;

        return $this;
    }

    /**
     * Write used associations.
     * http://docs.sencha.com/ext-js/4-2/#!/api/Ext.Class-cfg-uses
     * 
     * @param \MwbExporter\Writer\WriterInterface $writer
     * @return \MwbExporter\Formatter\Sencha\ExtJS42\Model\Table
     */
    public function writeUses(WriterInterface $writer)
    {
        // TODO find all assiciated class files.
        
        // End.
        return $this;
    }

    /**
     * http://docs.sencha.com/ext-js/4-2/#!/api/Ext.data.association.BelongsTo
     * 
     * @param \MwbExporter\Writer\WriterInterface $writer
     * @return \MwbExporter\Formatter\Sencha\ExtJS42\Model\Table
     */
    public function writeBelongsTo(WriterInterface $writer)
    {
        // TODO find all class file where this model belongs to.
        
        // End.
        return $this;
    }

    /**
     * http://docs.sencha.com/ext-js/4-2/#!/api/Ext.data.association.HasOne
     * 
     * @param \MwbExporter\Writer\WriterInterface $writer
     * @return \MwbExporter\Formatter\Sencha\ExtJS42\Model\Table
     */
    public function writeHasOne(WriterInterface $writer)
    {
        // TODO Find all one to * class files.
        
        // End.
        return $this;
    }

    /**
     * http://docs.sencha.com/ext-js/4-2/#!/api/Ext.data.association.HasMany
     * 
     * @param \MwbExporter\Writer\WriterInterface $writer
     * @return \MwbExporter\Formatter\Sencha\ExtJS42\Model\Table
     */
    public function writeHasMany(WriterInterface $writer)
    {
        // TODO find all many to * class files.
        
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
    protected function getApi()
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
    protected function getJsonReader()
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
    protected function getJsonWriter()
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

}