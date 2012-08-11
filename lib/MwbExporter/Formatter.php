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

namespace MwbExporter;

use MwbExporter\Model\Base;
use MwbExporter\Model\Column;

abstract class Formatter implements FormatterInterface
{
    /**
     * @var \MwbExporter\Registry
     */
    private $registry = null;

    /**
     * @var \MwbExporter\DatatypeConverterInterface
     */
    private $nodetypeConverter = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->registry = new Registry();
        $this->addConfigurations(array(
            static::CFG_INDENTATION            => 2,
            static::CFG_USE_TABS               => false,
            static::CFG_FILENAME               => '%entity%.%extension%',
            static::CFG_SKIP_PLURAL            => false,
            static::CFG_BACKUP_FILE            => true,
            static::CFG_USE_LOGGED_STORAGE     => false,
            static::CFG_ENHANCE_M2M_DETECTION  => true,
        ));
        $this->init();
    }

    /**
     * Initialization.
     */
    protected function init()
    {
    }

    /**
     * Add configurations data.
     *
     * @param string $configurations Configurations data
     * @return \MwbExporter\Formatter
     */
    protected function addConfigurations($configurations = array())
    {
        foreach ($configurations as $key => $value)
        {
            $this->registry->config->set($key, $value);
        }

        return $this;
    }

    /**
     * Get all configurations.
     *
     * @return array
     */
    public function getConfigurations()
    {
        return $this->registry->config->getAll();
    }

    /**
     * Setup formatter.
     *
     * @param array $configurations
     * @throws \RuntimeException
     * @return \MwbExporter\Formatter
     */
    public function setup($configurations = array())
    {
        foreach ($configurations as $key => $value)
        {
            if (!$this->registry->config->has($key))
            {
                throw new \RuntimeException(sprintf('Unknown setup key "%s".', $key));
            }
            $this->registry->config->set($key, $value);
        }

        return $this;
    }

    /**
     * Set data type converter.
     *
     * @param \MwbExporter\DatatypeConverterInterface $nodetypeConverter
     * @return \MwbExporter\Formatter
     */
    protected function setDatatypeConverter(DatatypeConverterInterface $nodetypeConverter)
    {
        $this->nodetypeConverter = $nodetypeConverter;
        $this->nodetypeConverter->setup();

        return $this;
    }

    /**
     * Get registry object.
     *
     * @return \MwbExporter\Registry
     */
    public function getRegistry()
    {
        return $this->registry;
    }

    /**
     * Get data type converter.
     *
     * @return \MwbExporter\DatatypeConverterInterface
     */
    public function getDatatypeConverter()
    {
        if (null === $this->nodetypeConverter) {
            throw new \RuntimeException('DatatypeConverter has not been set.');
        }

        return $this->nodetypeConverter;
    }

    /**
     * (non-PHPdoc)
     * @see MwbExporter.FormatterInterface::createCatalog()
     */
    public function createCatalog(Base $parent, $node)
    {
        return new Model\Catalog($parent, $node);
    }

    /**
     * (non-PHPdoc)
     * @see MwbExporter.FormatterInterface::createSchemas()
     */
    public function createSchemas(Base $parent, $node)
    {
        return new Model\Schemas($parent, $node);
    }

    /**
     * (non-PHPdoc)
     * @see MwbExporter.FormatterInterface::createSchema()
     */
    public function createSchema(Base $parent, $node)
    {
        return new Model\Schema($parent, $node);
    }

    /**
     * (non-PHPdoc)
     * @see MwbExporter.FormatterInterface::createTables()
     */
    public function createTables(Base $parent, $node)
    {
        return new Model\Tables($parent, $node);
    }

    /**
     * (non-PHPdoc)
     * @see MwbExporter.FormatterInterface::createTable()
     */
    public function createTable(Base $parent, $node)
    {
        return new Model\Table($parent, $node);
    }

    /**
     * (non-PHPdoc)
     * @see MwbExporter.FormatterInterface::createForeignKeys()
     */
    public function createForeignKeys(Base $parent, $node)
    {
        return new Model\ForeignKeys($parent, $node);
    }

    /**
     * (non-PHPdoc)
     * @see MwbExporter.FormatterInterface::createForeignKey()
     */
    public function createForeignKey(Base $parent, $node)
    {
        return new Model\ForeignKey($parent, $node);
    }

    /**
     * (non-PHPdoc)
     * @see MwbExporter.FormatterInterface::createIndices()
     */
    public function createIndices(Base $parent, $node)
    {
        return new Model\Indices($parent, $node);
    }

    /**
     * (non-PHPdoc)
     * @see MwbExporter.FormatterInterface::createIndex()
     */
    public function createIndex(Base $parent, $node)
    {
        return new Model\Index($parent, $node);
    }

    /**
     * (non-PHPdoc)
     * @see MwbExporter.FormatterInterface::createColumns()
     */
    public function createColumns(Base $parent, $node)
    {
        return new Model\Columns($parent, $node);
    }

    /**
     * (non-PHPdoc)
     * @see MwbExporter.FormatterInterface::createColumn()
     */
    public function createColumn(Base $parent, $node)
    {
        return new Model\Column($parent, $node);
    }

    /**
     * (non-PHPdoc)
     * @see MwbExporter.FormatterInterface::createViews()
     */
    public function createViews(Base $parent, $node)
    {
        return new Model\Views($parent, $node);
    }

    /**
     * (non-PHPdoc)
     * @see MwbExporter.FormatterInterface::createView()
     */
    public function createView(Base $parent, $node)
    {
        return new Model\View($parent, $node);
    }

    /**
     * (non-PHPdoc)
     * @see MwbExporter.FormatterInterface::getPreferredWriter()
     */
    public function getPreferredWriter()
    {
        return 'default';
    }
}