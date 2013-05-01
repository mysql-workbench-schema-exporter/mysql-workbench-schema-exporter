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

namespace MwbExporter\Formatter\Sencha\ExtJS42;

use MwbExporter\Formatter\Formatter as BaseFormatter;
use MwbExporter\Model\Base;

class Formatter
    extends BaseFormatter
{

    const CFG_CLASS_PREFIX = 'classPrefix';
    const CFG_PARENT_CLASS = 'parentClass';
    const CFG_GENERATE_VALIDATION = 'generateValidation';
    const CFG_GENERATE_PROXY = 'generateProxy';
    const CFG_BACKUP_FILE = 'backupExistingFile';

    /**
     * (non-PHPdoc)
     * 
     * @see \MwbExporter\Formatter::init()
     */
    protected function init()
    {
        $this->setDatatypeConverter(new DatatypeConverter());
        $this->addConfigurations(array(
            static::CFG_INDENTATION => 4,
            static::CFG_FILENAME => 'model/%entity%.%extension%',
            static::CFG_CLASS_PREFIX => 'App.model',
            static::CFG_PARENT_CLASS => 'Ext.data.Model',
            static::CFG_GENERATE_VALIDATION => true,
            static::CFG_GENERATE_PROXY => true,
            static::CFG_BACKUP_FILE => false,
        ));
    }

    /**
     * (non-PHPdoc)
     * 
     * @see \MwbExporter\Formatter::createTable()
     */
    public function createTable(Base $parent, $node)
    {
        return new Model\Table($parent, $node);
    }

    /**
     * (non-PHPdoc)
     * 
     * @see \MwbExporter\FormatterInterface::createColumns()
     */
    public function createColumns(Base $parent, $node)
    {
        return new Model\Columns($parent, $node);
    }

    /**
     * (non-PHPdoc)
     * 
     * @see \MwbExporter\FormatterInterface::createColumn()
     */
    public function createColumn(Base $parent, $node)
    {
        return new Model\Column($parent, $node);
    }

    /**
     * (non-PHPdoc)
     * 
     * @see \MwbExporter\FormatterInterface::createIndex()
     */
    public function createIndex(Base $parent, $node)
    {
        return new Model\Index($parent, $node);
    }

    /**
     * (non-PHPdoc)
     * 
     * @see \MwbExporter\FormatterInterface::getTitle()
     */
    public function getTitle()
    {
        return 'Sencha ExtJS4.2 Model';
    }

    /**
     * (non-PHPdoc)
     * 
     * @see \MwbExporter\FormatterInterface::getFileExtension()
     */
    public function getFileExtension()
    {
        return 'js';
    }

}