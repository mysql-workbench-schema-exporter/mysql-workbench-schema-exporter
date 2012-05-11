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

namespace MwbExporter\Formatter\Doctrine2\Yaml\Model;

use MwbExporter\Model\Table as Base;
use MwbExporter\Writer\WriterInterface;
use MwbExporter\Formatter\Doctrine2\Yaml\Formatter;

class Table extends Base
{
    /**
     * Get the entity namespace.
     *
     * @return string
     */
    public function getEntityNamespace()
    {
        $namespace = '';
        if ($bundleNamespace = $this->getDocument()->getConfig()->get(Formatter::CFG_BUNDLE_NAMESPACE)) {
            $namespace = $bundleNamespace.'\\';
        }
        if ($entityNamespace = $this->getDocument()->getConfig()->get(Formatter::CFG_ENTITY_NAMESPACE)) {
            $namespace .= $entityNamespace;
        } else {
            $namespace .= 'Entity';
        }

        return $namespace;
    }

    /**
     * Get namespace of a class.
     *
     * @param string $class The class name
     * @return string
     */
    public function getNamespace($class = null, $absolute = true)
    {
        return sprintf('%s%s\%s', $absolute ? '\\' : '', $this->getEntityNamespace(), null === $class ? $this->getModelName() : $class);
    }

    public function write(WriterInterface $writer)
    {
        if (!$this->isExternal()) {
            $writer->open($this->getTableFileName());
            $this->writeTable($writer);
            $writer->close();
        }
    }

    public function writeTable(WriterInterface $writer)
    {
        $writer
            ->write('%s:', $this->getNamespace())
            ->indent()
                ->write('type: Entity')
                ->writeIf($this->getDocument()->getConfig()->get(Formatter::CFG_AUTOMATIC_REPOSITORY), 'repositoryClass: %s', (($namespace = $this->getDocument()->getConfig()->get(Formatter::CFG_REPOSITORY_NAMESPACE)) ? $namespace.'\\' : '').$this->getModelName().'Repository')
                ->write('table: %s', ($this->getDocument()->getConfig()->get(Formatter::CFG_EXTEND_TABLENAME_WITH_SCHEMA) ? $this->getSchema()->getName().'.' : '').$this->getRawTableName())
                ->writeCallback(function($writer) {
                    $this->columns->write($writer);
                })
                ->writeCallback(function($writer) {
                    if (count($this->relations)) {
                        $writer->write('relations:');
                        $writer->indent();
                        foreach ($this->relations as $relation) {
                            $relation->write($writer);
                        }
                        $writer->outdent();
                    }
                })
                ->writeCallback(function($writer) {
                    if (count($this->indexes)) {
                        $writer->write('indexes:');
                        $writer->indent();
                        foreach ($this->indexes as $index) {
                            $index->write($writer);
                        }
                        $writer->outdent();
                    }
                })
                ->write('options:')
                ->indent()
                    ->writeIf($charset = $this->parameters->get('defaultCharacterSetName'), 'charset: '.$charset)
                    ->writeIf($engine = $this->parameters->get('tableEngine'), 'type: '.$engine)
                ->outdent()
            ->outdent()
        ;
    }
}
