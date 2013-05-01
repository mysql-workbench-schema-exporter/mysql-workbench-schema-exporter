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

namespace MwbExporter\Formatter\Zend\DbTable;

use MwbExporter\Formatter\Formatter as BaseFormatter;
use MwbExporter\Model\Base;

class Formatter extends BaseFormatter
{
    const CFG_TABLE_PREFIX           = 'tablePrefix';
    const CFG_PARENT_TABLE           = 'parentTable';
    const CFG_GENERATE_DRI           = 'generateDRI';
    const CFG_GENERATE_GETTER_SETTER = 'generateGetterSetter';

    protected function init()
    {
        $this->setDatatypeConverter(new DatatypeConverter());
        $this->addConfigurations(array(
            static::CFG_INDENTATION             => 4,
            static::CFG_FILENAME                => 'DbTable/%schema%/%entity%.%extension%',
            static::CFG_TABLE_PREFIX            => 'Application_Model_DbTable_',
            // If you want to use your personnal Zend_Db_Table_Abstract,
            // you need to specifie here his name
            static::CFG_PARENT_TABLE            => 'Zend_Db_Table_Abstract',
            // If true, the script will generate the $_dependentTables.
            // Before active this option please read:
            // http://framework.zend.com/manual/en/zend.db.table.relationships.html#zend.db.table.relationships.cascading
            static::CFG_GENERATE_DRI            => false,
            static::CFG_GENERATE_GETTER_SETTER  => false,
        ));
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Formatter\Formatter::createTable()
     */
    public function createTable(Base $parent, $node)
    {
        return new Model\Table($parent, $node);
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Formatter\Formatter::createForeignKey()
     */
    public function createForeignKey(Base $parent, $node)
    {
        return new Model\ForeignKey($parent, $node);
    }

    public function getTitle()
    {
        return 'Zend DbTable';
    }

    public function getFileExtension()
    {
        return 'php';
    }
}