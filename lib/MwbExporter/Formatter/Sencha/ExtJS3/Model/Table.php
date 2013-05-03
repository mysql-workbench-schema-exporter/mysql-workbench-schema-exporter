<?php

/*
 * The MIT License
 *
 * Copyright (c) 2012 Allan Sun <sunajia@gmail.com>
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

namespace MwbExporter\Formatter\Sencha\ExtJS3\Model;

use MwbExporter\Model\Table as BaseTable;
use MwbExporter\Writer\WriterInterface;
use MwbExporter\Formatter\Sencha\ExtJS3\Formatter;
use MwbExporter\Helper\ZendURLFormatter;
use MwbExporter\Object\JS;

class Table extends BaseTable
{
    public function getClassPrefix()
    {
        return $this->translateVars($this->getDocument()->getConfig()->get(Formatter::CFG_CLASS_PREFIX));
    }

    public function getParentClass()
    {
        return $this->translateVars($this->getDocument()->getConfig()->get(Formatter::CFG_PARENT_CLASS));
    }

    public function writeTable(WriterInterface $writer)
    {
        if (!$this->isExternal()) {
            $writer
                ->open($this->getTableFileName())
                // model
                ->write($this->getClassPrefix().'.'. $this->getModelName().' = Ext.extend('.$this->getParentClass().', {')
                ->indent()
                    ->write("id: '%s',", $this->getModelName())
                    ->write("url: '%s',", ZendURLFormatter::fromCamelCaseToDashConnection($this->getModelName()))
                    ->write("title: '%s',", str_replace('-', ' ', ZendURLFormatter::fromCamelCaseToDashConnection($this->getModelName())))
                    ->writeCallback(function(WriterInterface $writer, Table $_this = null) {
                        $_this->getColumns()->writeFields($writer);
                    })
                ->outdent()
                ->write('});')
                ->write('')
                // UI
                ->write($this->getClassPrefix().'.'. $this->getModelName().' = Ext.extend('.$this->getClassPrefix().'.'. $this->getModelName().', {')
                ->indent()
                    ->writeCallback(function(WriterInterface $writer, Table $_this = null) {
                        $_this->getColumns()
                            ->writeColumns($writer)
                            ->writeFormItems($writer)
                        ;
                    })
                ->outdent()
                ->write('});')
                ->write('')
                ->close()
            ;

            return self::WRITE_OK;
        }

        return self::WRITE_EXTERNAL;
    }

    /**
     * Get JSObject.
     *
     * @param mixed $content    Object content
     * @param bool  $multiline  Multiline result
     * @param bool  $raw        Is raw object
     * @return \MwbExporter\Object\JS
     */
    public function getJSObject($content, $multiline = false, $raw = false)
    {
        return new JS($content, array('multiline' => $multiline, 'raw' => $raw, 'indent' => $this->getDocument()->getConfig()->get(Formatter::CFG_INDENTATION))); 
    }
}