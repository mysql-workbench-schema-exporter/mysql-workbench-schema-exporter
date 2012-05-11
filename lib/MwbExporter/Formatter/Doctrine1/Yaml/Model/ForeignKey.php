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

namespace MwbExporter\Formatter\Doctrine1\Yaml\Model;

use MwbExporter\Model\ForeignKey as BaseForeignKey;
use MwbExporter\Helper\Pluralizer;
use MwbExporter\Writer\WriterInterface;

class ForeignKey extends BaseForeignKey
{
    public function getForeignAlias()
    {
        return trim($this->parseComment('foreignAlias'));
    }

    public function write(WriterInterface $writer)
    {
        if ($this->referencedTable == null) {
            $writer
                ->write('# There is another foreign key declaration.')
            ;
        } else {
            $writer
                ->write('%s:', 0 === strpos($this->parameters->get('name'), 'd:') ? substr($this->parameters->get('name'), 2) : $this->referencedTable->getModelName())
                ->indent()
                    ->write('class: '.$this->referencedTable->getModelName())
                    ->write('local: '.$this->local->getColumnName())
                    ->write('foreign: '.$this->foreign->getColumnName())
                    ->write('foreignAlias: '.(($alias = $this->getForeignAlias()) ? $alias : ($this->isManyToOne() ? Pluralizer::pluralize($this->owningTable->getModelName()) : $this->owningTable->getModelName())))
                    ->write('onDelete: '.strtolower($this->parameters->get('deleteRule')))
                    ->write('onUpdate: '.strtolower($this->parameters->get('updateRule')))
                ->outdent()
            ;
        }
    }
}