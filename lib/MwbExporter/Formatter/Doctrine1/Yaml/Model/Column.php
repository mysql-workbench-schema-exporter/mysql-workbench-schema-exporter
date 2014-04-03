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

namespace MwbExporter\Formatter\Doctrine1\Yaml\Model;

use MwbExporter\Model\Column as BaseColumn;
use MwbExporter\Writer\WriterInterface;

class Column extends BaseColumn
{
    public function write(WriterInterface $writer)
    {
        $writer
            ->write('%s:', $this->getColumnName())
            ->indent()
                ->write('type: %s', $this->getFormatter()->getDatatypeConverter()->getType($this))
                ->writeIf($this->isPrimary(), 'primary: true')
                ->writeIf($this->isNotNull(), 'notnull: true')
                ->writeIf($this->isAutoIncrement(), 'autoincrement: true')
                // enum and set values
                ->writeCallback(function(WriterInterface $writer, Column $_this = null) {
                    if ($values = $_this->getParameters()->get('datatypeExplicitParams')) {
                        $writer->write('values: %s', strtr($values, array('(' => '[', ')' => ']')));
                    }
                })
                ->writeIf(($default = $this->getDefaultValue()), 'default: '.$default)
                ->writeCallback(function(WriterInterface $writer, Column $_this = null) {
                    foreach ($_this->getNode()->xpath("value[@key='flags']/value") as $flag) {
                        $writer->write(strtolower($flag).': true');
                    }
                })
            ->outdent()
        ;

        return $this;
    }
}