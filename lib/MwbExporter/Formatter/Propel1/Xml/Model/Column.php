<?php
/*
 * The MIT License
 *
 * Copyright (c) 2010 Johannes Mueller <circus2(at)web.de>
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

namespace MwbExporter\Formatter\Propel1\Xml\Model;

use MwbExporter\Model\Column as BaseColumn;
use MwbExporter\Writer\WriterInterface;

class Column extends BaseColumn
{
    public function write(WriterInterface $writer)
    {
        $type = strtoupper($this->getDocument()->getFormatter()->getDatatypeConverter()->getType($this));

        if($type == 'DECIMAL'){
            $type = $type.'" size="'.$this->parameters->get('precision').'" scale="'.$this->parameters->get('scale');
        }
        if($type == 'ENUM' or $type == 'SET'){
            $type = $type.'" sqlType="'.$type.$this->parameters->get('datatypeExplicitParams').'" valueSet="'.substr($this->parameters->get('datatypeExplicitParams'), 1, -1);
        }

        $writer
            ->write('<column name="%s" type="%s"%s%s%s%s%s%s />',
                $this->getColumnName(),                                                                                // name
                $type,                                                                                                 // type
                ($this->isPrimary                        == 1  ? ' primaryKey="true"'                           : ''), // primaryKey
                ($this->parameters->get('length')         > 0  ? ' size="'.$this->parameters->get('length').'"' : ''), // size
                ($this->parameters->get('isNotNull')     == 1  ? ' required="true"'                             : ''), // required
                ($this->parameters->get('autoIncrement') == 1  ? ' autoIncrement="true"'                        : ''), // autoIncrement
                (($this->parameters->get('defaultValue')  != '' && !in_array($this->parameters->get('defaultValue'), array('CURRENT_TIMESTAMP'))) ? ' defaultValue="'.$this->parameters->get('defaultValue').'"' : ''), // defaultValue
                ($this->parameters->get('defaultValue')  != '' ? ' defaultExpr="'.$this->parameters->get('defaultValue').'"' : '') // defaultExpr
            )
        ;
        return $this;
    }

    public function writeRelations(WriterInterface $writer)
    {
        $parentTable = $this->getParent()->getParent();
        foreach ($this->foreigns as $foreign) {
            //if($foreign->getParent()->getParent()->getRawTableName() == $parentTable->getRawTableName()){
                $writer
                    ->write('<foreign-key name="%s" foreignTable="%s" phpName="%s" refPhpName="%s" onDelete="%s" onUpdate="%s">',
                        $foreign->parameters->get('name'),
                        $foreign->getOwningTable()->getRawTableName(),
                        $foreign->getOwningTable()->getModelName(),
                        $foreign->getReferencedTable()->getModelName(),
                        (strtolower($foreign->parameters->get('deleteRule')) == 'no action' ? 'none' : strtolower($foreign->parameters->get('deleteRule'))),
                        (strtolower($foreign->parameters->get('updateRule')) == 'no action' ? 'none' : strtolower($foreign->parameters->get('updateRule')))
                    )
                ;
                $writer->indent();
                $writer
                    ->write('<reference local="%s" foreign="%s" />',
                        $foreign->getLocal()->getColumnName(),
                        $foreign->getForeign()->getColumnName()
                    )
                ;
                $writer->outdent();
                $writer->write('</foreign-key>');
            //}
        }
        return $this;
    }
}
