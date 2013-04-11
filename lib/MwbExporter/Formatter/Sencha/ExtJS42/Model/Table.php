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
use MwbExporter\Helper\ZendURLFormatter;
use MwbExporter\Helper\JSObject;

class Table
    extends BaseTable
{

    public function getClassPrefix()
    {
        return $this->translateVars($this->getDocument()->getConfig()->get(Formatter::CFG_CLASS_PREFIX));
    }

    public function getParentClass()
    {
        return $this->translateVars($this->getDocument()->getConfig()->get(Formatter::CFG_PARENT_CLASS));
    }

    public function write(WriterInterface $writer)
    {
        if (!$this->isExternal()) {
            $writer->open($this->getTableFileName());
            $this->writeTable($writer);
            $writer->close();
        }

        return $this;
    }

    public function writeTable(WriterInterface $writer)
    {

        $writer
            ->write("Ext.define('%s', {", $this->getClassPrefix() . '.' . $this->getModelName())
            ->indent()
            ->write("extend: '%s',", $this->getParentClass())
            // TODO Add uses, See doctrine fromatter for a nice sollution
            // TODO Add relations
            ->writeCallback(function(WriterInterface $writer, Table $_this = null) {
                    $_this->getColumns()->writeFields($writer);
                })
            ->writeCallback(function(WriterInterface $writer, Table $_this = null) {
                    $_this->getColumns()->writeValidations($writer);
                })
            ->write("listners: {},")
            ->write("proxy: {}")
            ->outdent()
            ->write('});')
        ;

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
        return new JSObject($content, array('multiline' => $multiline, 'raw' => $raw, 'indent' => $this->getDocument()->getConfig()->get(Formatter::CFG_INDENTATION)));
    }

}