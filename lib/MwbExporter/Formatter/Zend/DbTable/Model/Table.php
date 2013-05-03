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

namespace MwbExporter\Formatter\Zend\DbTable\Model;

use MwbExporter\Model\Table as BaseTable;
use MwbExporter\Formatter\Zend\DbTable\Formatter;
use MwbExporter\Writer\WriterInterface;

class Table extends BaseTable
{
    public function getTablePrefix()
    {
        return $this->translateVars($this->getDocument()->getConfig()->get(Formatter::CFG_TABLE_PREFIX));
    }

    public function getParentTable()
    {
        return $this->translateVars($this->getDocument()->getConfig()->get(Formatter::CFG_PARENT_TABLE));
    }

    public function writeTable(WriterInterface $writer)
    {
        if (!$this->isExternal()) {
            /* FIXME: [Zend] Table name is one time in singular form, one time in plural form.
             *       All table occurence need to be at the original form.
             *
             *       $this->getModelName() return singular form with correct camel case
             *       $this->getRawTableName() return original form with no camel case
             */
            $writer
                ->open($this->getTableFileName())
                ->write('<?php')
                ->write('')
                ->write('class '.$this->getTablePrefix().$this->getSchema()->getName().'_'. $this->getModelName().' extends '.$this->getParentTable())
                ->write('{')
                ->indent()
                    ->write('/**')
                    ->write(' * @var string')
                    ->write(' */')
                    ->write('protected $_schema = \''. $this->getSchema()->getName() .'\';')
                    ->write('')
                    ->write('/**')
                    ->write(' * @var string')
                    ->write(' */')
                    ->write('protected $_name = \''. $this->getRawTableName() .'\';')
                    ->write('')
                    ->writeCallback(function(WriterInterface $writer, Table $_this = null) {
                        if ($_this->getDocument()->getConfig()->get(Formatter::CFG_GENERATE_DRI)) {
                            $_this->writeDependencies($writer);
                        }
                    })
                    ->writeCallback(function(WriterInterface $writer, Table $_this = null) {
                        $_this->writeReferences($writer);
                    })
                ->outdent()
                ->write('}')
                ->write('')
                ->close()
            ;

            return self::WRITE_OK;
        }

        return self::WRITE_EXTERNAL;
    }

    public function writeDependencies(WriterInterface $writer)
    {
        //TODO: [Zend] Find a way to print dependance without change the core.
        $writer
            ->write('/**')
            ->write(' * TODO: this feature isn\'t implement yet')
            ->write(' *')
            ->write(' * @var array')
            ->write(' */')
            ->write('protected $_dependentTables = array();')
            ->write('')
        ;

        return $this;
    }

    public function writeReferences(WriterInterface $writer)
    {
        $writer
            ->write('/**')
            ->write(' * @var array')
            ->write(' */')
            ->writeCallback(function(WriterInterface $writer, Table $_this = null) {
                if (count($_this->getForeignKeys())) {
                    $writer->write('protected $_referenceMap = array(');
                    $writer->indent();
                    foreach ($_this->getForeignKeys() as $foreignKey) {
                        $foreignKey->write($writer);
                    }
                    $writer->outdent();
                    $writer->write(');');
                } else {
                    $writer->write('protected $_referenceMap = array();');
                }
            })
        ;

        return $this;
    }
}
