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

use MwbExporter\Model\Columns as BaseColumns;
use MwbExporter\Writer\WriterInterface;

class Columns
    extends BaseColumns
{

    /**
     * COMMENTME
     * 
     * @param \MwbExporter\Writer\WriterInterface $writer
     * @return \MwbExporter\Formatter\Sencha\ExtJS42\Model\Columns
     */
    public function writeHasOneRelations(WriterInterface $writer)
    {
        $hasOneCount = $this->getHasOneCount();

        $writer
            ->write('hasOne: [')
            ->indent()
            ->writeCallback(function(WriterInterface $writer, Columns $_this = null) use($hasOneCount) {
                    foreach ($_this->getColumns() as $column) {
                        if ($column->getLocalForeignKey()) {
                            $hasMore = (bool) --$hasOneCount;
                            $column->writeHasOneRelation($writer, $hasMore);
                        }
                    }
                })
            ->outdent()
            ->write('],')
        ;
        // End.
        return $this;
    }

    /**
     * COMMENTME
     * 
     * @param \MwbExporter\Writer\WriterInterface $writer
     * @param \MwbExporter\Writer\WriterInterface $writer
     * @return \MwbExporter\Formatter\Sencha\ExtJS42\Model\Columns
     */
    public function writeHasManyRelations(WriterInterface $writer)
    {
        $writer
            ->write('hasMany: [')
            ->indent()
            ->writeCallback(function(WriterInterface $writer, Columns $_this = null) {
                    
                })
            ->outdent()
            ->write('],')
        ;

        // End.
        return $this;
    }

    /**
     * TODO Use method write.
     * Write model fields.
     * 
     * @param \MwbExporter\Writer\WriterInterface $writer
     * @return \MwbExporter\Formatter\Sencha\ExtJS42\Model\Columns
     */
    public function writeFields(WriterInterface $writer)
    {
        $writer
            ->write('fields: [')
            ->indent()
            ->writeCallback(function(WriterInterface $writer, Columns $_this = null) {
                    $columns = $_this->getColumns();
                    $columnsCount = count($columns);

                    foreach ($columns as $column) {
                        $hasMore = (bool) --$columnsCount;
                        $column->writeField($writer, $hasMore);
                    }
                })
            ->outdent()
            ->write('],')
        ;

        return $this;
    }

    /**
     * Write model validations.
     * 
     * @param \MwbExporter\Writer\WriterInterface $writer
     */
    public function writeValidations(WriterInterface $writer)
    {
        $writer
            ->write('validations: [')
            ->indent()
            ->writeCallback(function(WriterInterface $writer, Columns $_this = null) {
                    $columns = $_this->getColumns();
                    $columnsCount = count($columns);

                    foreach ($columns as $column) {
                        $hasMore = (bool) --$columnsCount;
                        $column->writeValidation($writer, $hasMore);
                    }
                })
            ->outdent()
            ->write('],')
        ;
    }

    /**
     * Get the number of hasOne relations.
     * All columns with local foreign key. 
     * 
     * @return int
     */
    protected function getHasOneCount()
    {
        $count = 0;
        foreach ($this->columns as $column) {
            if ($column->getLocalForeignKey()) {
                $count++;
            }
        }

        // End.
        return $count;
    }

}