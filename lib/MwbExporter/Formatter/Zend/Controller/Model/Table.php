<?php

/*
 * The MIT License
 *
 * Copyright (c) 2012 Allan Sun <sunajia@gmail.com>
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

namespace MwbExporter\Formatter\Zend\Controller\Model;

use MwbExporter\Model\Table as BaseTable;
use MwbExporter\Formatter\Zend\Controller\Formatter;
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
            ->write('<?php')
            ->write('')
            ->write('class '.$this->getTablePrefix().$this->getModelName().'Controller extends '.$this->getParentTable())
            ->write('{')
            ->indent()
                ->write('')
            ->outdent()
            ->write('}')
            ->write('')
        ;

        return $this;
    }
}