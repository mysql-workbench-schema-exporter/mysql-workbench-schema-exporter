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

namespace MwbExporter\Formatter\Sencha\ExtJS3\Model;

use MwbExporter\Model\Columns as BaseColumns;
use MwbExporter\Writer\WriterInterface;

class Columns extends BaseColumns
{
    public function writeFields(WriterInterface $writer)
    {
        $writer
            ->write('fields: [')
            ->indent()
                ->writeCallback(function(WriterInterface $writer, Columns $_this = null) {
                    $columns = $_this->getColumns();
                    for ($i = 0; $i < count($columns); $i++) {
                        $writer->write($columns[$i]->getAsField().($i < count($columns) - 1 ? ',' : ''));
                    }
                })
            ->outdent()
            ->write(']')
        ;

        return $this;
    }

    public function writeColumns(WriterInterface $writer)
    {
        $writer
            ->write('columns: [')
            ->indent()
                ->writeCallback(function(WriterInterface $writer, Columns $_this = null) {
                    $columns = $_this->getColumns();
                    for ($i = 0; $i < count($columns); $i++) {
                        $writer->write($columns[$i]->getAsColumn().($i < count($columns) - 1 ? ',' : ''));
                    }
                })
            ->outdent()
            ->write('],')
        ;

        return $this;
    }

    public function writeFormItems(WriterInterface $writer)
    {
        $writer
            ->write('formItems: [')
            ->indent()
                ->write('title: \'Basic Details\',')
                ->write('layout: \'form\',')
                ->write('items: [')
                ->indent()
                    ->writeCallback(function(WriterInterface $writer, Columns $_this = null) {
                        $columns = $_this->getColumns();
                        for ($i = 0; $i < count($columns); $i++) {
                            $writer->write($columns[$i]->getAsFormItem().($i < count($columns) - 1 ? ',' : ''));
                        }
                    })
                ->outdent()
                ->write(']')
            ->outdent()
            ->write(']')
        ;

        return $this;
    }
}