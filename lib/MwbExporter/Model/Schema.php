<?php

/*
 * The MIT License
 *
 * Copyright (c) 2010 Johannes Mueller <circus2(at)web.de>
 * Copyright (c) 2012-2014 Toha <tohenk@yahoo.com>
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

namespace MwbExporter\Model;

use MwbExporter\Writer\WriterInterface;

class Schema extends Base
{
    /**
     * @var \MwbExporter\Model\Tables
     */
    protected $tables = null;

    /**
     * @var \MwbExporter\Model\Views
     */
    protected $views  = null;

    /**
     * @var string
     */
    protected $name   = null;

    protected function init()
    {
        $elems = $this->node->xpath("value[@key='name']");
        $this->name = (string) $elems[0];
        $this->getDocument()->addLog(sprintf('Processing schema "%s"', $this->name));
        $elems = $this->node->xpath("value[@key='tables']");
        $this->tables = $this->getFormatter()->createTables($this, $elems[0]);
        $elems = $this->node->xpath("value[@key='views']");
        $this->views  = $this->getFormatter()->createViews($this, $elems[0]);
    }

    /**
     * Get tables model.
     *
     * @return \MwbExporter\Model\Tables
     */
    public function getTables()
    {
        return $this->tables;
    }

    /**
     * Get views model.
     *
     * @return \MwbExporter\Model\Views
     */
    public function getViews()
    {
        return $this->views;
    }

    /**
     * Get schema name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Model\Base::write()
     */
    public function write(WriterInterface $writer)
    {
        $this->writeSchema($writer);

        return $this;
    }

    /**
     * Write schema entity as code.
     *
     * @param \MwbExporter\Writer\WriterInterface $writer
     * @return \MwbExporter\Model\Schema
     */
    public function writeSchema(WriterInterface $writer)
    {
        $this->getDocument()->addLog(sprintf('Processing schema %s:', $this->name));
        $this->getDocument()->addLog('Processing tables:');
        $this->getTables()->write($writer);
        $this->getDocument()->addLog('Processing views:');
        $this->getViews()->write($writer);

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Model\Base::getVars()
     */
    protected function getVars()
    {
        return array('%schema%' => $this->getName());
    }
}