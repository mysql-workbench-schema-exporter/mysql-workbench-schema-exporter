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

namespace MwbExporter\Model;

use MwbExporter\FormatterInterface;
use MwbExporter\Writer\WriterInterface;

class Document extends Base
{
    /**
     * @var \SimpleXMLElement
     */
    protected $xml = null;

    /**
     * @var \MwbExporter\Model\PhysicalModel
     */
    protected $physicalModel = null;

    /**
     * @var \MwbExporter\FormatterInterface
     */
    protected $formatter = null;

    /**
     * @var \MwbExporter\Writer\WriterInterface
     */
    protected $writer = null;

    /**
     * Constructor.
     *
     * @param FormatterInterface $formatter
     * @param string $filename
     */
    public function __construct(FormatterInterface $formatter, $filename)
    {
        $this->formatter = $formatter;
        $this->readXML($filename);
        parent::__construct(null, $this->xml->value);
        $this->parse();
    }

    /**
     * Get the formatter object.
     *
     * @return \MwbExporter\FormatterInterface
     */
    public function getFormatter()
    {
        return $this->formatter;
    }

    /**
     * Get document writer.
     *
     * @return \MwbExporter\Writer\WriterInterface
     */
    public function getWriter()
    {
        return $this->writer;
    }

    /**
     * Get configuration object.
     *
     * @return \MwbExporter\RegistryHolder
     */
    public function getConfig()
    {
        return $this->formatter->getRegistry()->config;
    }

    /**
     * Get factory object.
     *
     * @return \MwbExporter\RegistryHolder
     */
    public function getFactory()
    {
        return $this->formatter->getRegistry()->factory;
    }

    /**
     * Get reference object.
     *
     * @return \MwbExporter\RegistryHolder
     */
    public function getReference()
    {
        return $this->formatter->getRegistry()->reference;
    }

    /**
     * (non-PHPdoc)
     * @see MwbExporter\Model.Base::getDocument()
     */
    public function getDocument()
    {
        return $this;
    }

    /**
     * Get physical model.
     *
     * @return \MwbExporter\Model\PhysicalModel
     */
    public function getPhysicalModel()
    {
        return $this->physicalModel;
    }

    protected function readXML($filename)
    {
        $this->xml = simplexml_load_file("zip://".str_replace("\\", "/", realpath($filename))."#document.mwb.xml");
        if (false === $this->xml) {
            throw new \RuntimeException(sprintf('Can\'t load "%s", may be it not MySQL Workbench document.', $filename));
        } 
    }

    protected function parse()
    {
        $elems = $this->node->xpath("value[@key='physicalModels']/value");
        $this->physicalModel = new PhysicalModel($this, $elems[0]);
    }

    /**
     * (non-PHPdoc)
     * @see MwbExporter\Model.Base::write()
     */
    public function write(WriterInterface $writer)
    {
        $this->writer = $writer;
        $writer->setDocument($this);
        $writer->begin();
        try {
            $this->physicalModel->write($writer);
        } catch (\Exception $e) {
        }
        $writer->end();

        return $this;
    }
}