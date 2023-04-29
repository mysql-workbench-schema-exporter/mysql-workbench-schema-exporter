<?php

/*
 * The MIT License
 *
 * Copyright (c) 2010 Johannes Mueller <circus2(at)web.de>
 * Copyright (c) 2012-2023 Toha <tohenk@yahoo.com>
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

namespace MwbExporter\Formatter;

use MwbExporter\Model\Base;

interface FormatterInterface
{
    public const VERSION = '4.0.1';

    /**
     * Get formatter name.
     *
     * @return string
     */
    public function getName();

    /**
     * Get formatter version.
     *
     * @return string
     */
    public function getVersion();

    /**
     * Get the registry object.
     *
     * @return \MwbExporter\Registry\Registry
     */
    public function getRegistry();

    /**
     * Get the data type converter.
     *
     * @return \MwbExporter\Formatter\DatatypeConverterInterface
     */
    public function getDatatypeConverter();

    /**
     * Get configurations.
     *
     * @return \MwbExporter\Configuration\Configurations
     */
    public function getConfigurations();

    /**
     * Get configuration.
     *
     * @param string $key
     * @return \MwbExporter\Configuration\Configuration
     */
    public function getConfig($key);

    /**
     * Setup formatter.
     *
     * @param array $configurations
     */
    public function setup($configurations = []);

    /**
     * Create catalog model.
     *
     * @param \MwbExporter\Model\Base $parent  The object parent
     * @param \SimpleXMLElement $node  The model data
     * @return \MwbExporter\Model\Catalog
     */
    public function createCatalog(Base $parent, $node);

    /**
     * Create schemas model.
     *
     * @param \MwbExporter\Model\Base $parent  The object parent
     * @param \SimpleXMLElement $node  The model data
     * @return \MwbExporter\Model\Schemas
     */
    public function createSchemas(Base $parent, $node);

    /**
     * Create schema model.
     *
     * @param \MwbExporter\Model\Base $parent  The object parent
     * @param \SimpleXMLElement $node  The model data
     * @return \MwbExporter\Model\Schema
     */
    public function createSchema(Base $parent, $node);

    /**
     * Create tables model.
     *
     * @param \MwbExporter\Model\Base $parent  The object parent
     * @param \SimpleXMLElement $node  The model data
     * @return \MwbExporter\Model\Tables
     */
    public function createTables(Base $parent, $node);

    /**
     * Create catalog model.
     *
     * @param \MwbExporter\Model\Base $parent  The object parent
     * @param \SimpleXMLElement $node  The model data
     * @return \MwbExporter\Model\Table
     */
    public function createTable(Base $parent, $node);

    /**
     * Create foreign keys model.
     *
     * @param \MwbExporter\Model\Base $parent  The object parent
     * @param \SimpleXMLElement $node  The model data
     * @return \MwbExporter\Model\ForeignKeys
     */
    public function createForeignKeys(Base $parent, $node);

    /**
     * Create foreign key model.
     *
     * @param \MwbExporter\Model\Base $parent  The object parent
     * @param \SimpleXMLElement $node  The model data
     * @return \MwbExporter\Model\ForeignKey
     */
    public function createForeignKey(Base $parent, $node);

    /**
     * Create indices model.
     *
     * @param \MwbExporter\Model\Base $parent  The object parent
     * @param \SimpleXMLElement $node  The model data
     * @return \MwbExporter\Model\Indices
     */
    public function createIndices(Base $parent, $node);

    /**
     * Create index model.
     *
     * @param \MwbExporter\Model\Base $parent  The object parent
     * @param \SimpleXMLElement $node  The model data
     * @return \MwbExporter\Model\Index
     */
    public function createIndex(Base $parent, $node);

    /**
     * Create columns model.
     *
     * @param \MwbExporter\Model\Base $parent  The object parent
     * @param \SimpleXMLElement $node  The model data
     * @return \MwbExporter\Model\Columns
     */
    public function createColumns(Base $parent, $node);

    /**
     * Create column model.
     *
     * @param \MwbExporter\Model\Base $parent  The object parent
     * @param \SimpleXMLElement $node  The model data
     * @return \MwbExporter\Model\Column
     */
    public function createColumn(Base $parent, $node);

    /**
     * Create views model.
     *
     * @param \MwbExporter\Model\Base $parent  The object parent
     * @param \SimpleXMLElement $node  The model data
     * @return \MwbExporter\Model\Views
     */
    public function createViews(Base $parent, $node);

    /**
     * Create view model.
     *
     * @param \MwbExporter\Model\Base $parent  The object parent
     * @param \SimpleXMLElement $node  The model data
     * @return \MwbExporter\Model\View
     */
    public function createView(Base $parent, $node);

    /**
     * Get formatter title.
     *
     * @return string
     */
    public function getTitle();

    /**
     * Get file extension for generated code.
     *
     * @return string
     */
    public function getFileExtension();

    /**
     * Get preferred write to be used.
     *
     * @return string
     */
    public function getPreferredWriter();

    /**
     * Get all prefixes recognized as tag for comment.
     *
     * Each workbench element has a comment field. Several formatters utilize
     * this comment field to enable customization of the formatter according
     * to the user requirement.
     *
     * @return string
     */
    public function getCommentTagPrefix();

    /**
     * Get generated comment format.
     *
     * Variable placeholders supported:
     *   %VERSION%      Exporter version
     *   %FORMATTER%    Formatter name
     *   %TIME%         The date and time of code generation
     *
     * @return string
     */
    public function getCommentFormat();

    /**
     * Get comment variable substitution.
     *
     * @return array
     */
    public function getCommentVars();

    /**
     * Get comment.
     *
     * @param string $format  Comment wrapper format
     * @return string
     */
    public function getComment($format);

    /**
     * Get formatted comment.
     *
     * @param string $comment  Comment content
     * @param string $format  Comment wrapper format
     * @param int $width  Maximum line width
     * @return string
     */
    public function getFormattedComment($comment, $format, $width = 80);

    /**
     * Dump all configurations.
     *
     * @param string $format
     * @return string
     */
    public function dump($format = 'plain');
}
