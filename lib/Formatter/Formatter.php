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

use MwbExporter\Configuration\Configurations;
use MwbExporter\Configuration\Backup as BackupConfiguration;
use MwbExporter\Configuration\Category as CategoryConfiguration;
use MwbExporter\Configuration\Comment as CommentConfiguration;
use MwbExporter\Configuration\ConsoleLogging as ConsoleLoggingConfiguration;
use MwbExporter\Configuration\EOL as EOLConfiguration;
use MwbExporter\Configuration\FileLogging as FileLoggingConfiguration;
use MwbExporter\Configuration\Filename as FilenameConfiguration;
use MwbExporter\Configuration\Header as HeaderConfiguration;
use MwbExporter\Configuration\IdentifierStrategy as IdentifierStrategyConfiguration;
use MwbExporter\Configuration\Indentation as IndentationConfiguration;
use MwbExporter\Configuration\Language as LanguageConfiguration;
use MwbExporter\Configuration\LoggedStorage as LoggedStorageConfiguration;
use MwbExporter\Configuration\M2MEnhanced as M2MEnhancedConfiguration;
use MwbExporter\Configuration\M2MSkip as M2MSkipConfiguration;
use MwbExporter\Configuration\NamingStrategy as NamingStrategyConfiguration;
use MwbExporter\Configuration\PluralSkip as PluralSkipConfiguration;
use MwbExporter\Configuration\Tab as TabConfiguration;
use MwbExporter\Configuration\TableAndViewSort as TableAndViewSortConfiguration;
use MwbExporter\Configuration\UserDatatype as UserDatatypeConfiguration;
use MwbExporter\Helper\Comment;
use MwbExporter\Model\Base;
use MwbExporter\Model\Catalog;
use MwbExporter\Model\Schemas;
use MwbExporter\Model\Schema;
use MwbExporter\Model\Tables;
use MwbExporter\Model\Table;
use MwbExporter\Model\ForeignKeys;
use MwbExporter\Model\ForeignKey;
use MwbExporter\Model\Indices;
use MwbExporter\Model\Index;
use MwbExporter\Model\Columns;
use MwbExporter\Model\Column;
use MwbExporter\Model\Views;
use MwbExporter\Model\View;
use MwbExporter\Registry\Registry;

abstract class Formatter implements FormatterInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var \MwbExporter\Configuration\Configurations
     */
    private $configurations = null;

    /**
     * @var \MwbExporter\Registry\Registry
     */
    private $registry = null;

    /**
     * @var \MwbExporter\Formatter\DatatypeConverterInterface
     */
    private $datatypeConverter = null;

    /**
     * Constructor.
     *
     * @param string $name  Formatter name
     */
    public function __construct($name = null)
    {
        $this->name = $name;
        $this->configurations = new Configurations();
        $this->registry = new Registry();
        $this->configurations
            ->add(new LanguageConfiguration())
            ->add(new TabConfiguration())
            ->add(new IndentationConfiguration())
            ->add(new EOLConfiguration())
            ->add(new FilenameConfiguration())
            ->add(new BackupConfiguration())
            ->add(new HeaderConfiguration())
            ->add(new CommentConfiguration())
            ->add(new NamingStrategyConfiguration())
            ->add(new IdentifierStrategyConfiguration())
            ->add(new UserDatatypeConfiguration())
            ->add(new M2MEnhancedConfiguration())
            ->add(new TableAndViewSortConfiguration())
            ->add(new M2MSkipConfiguration())
            ->add(new PluralSkipConfiguration())
            ->add(new CategoryConfiguration())
            ->add(new ConsoleLoggingConfiguration())
            ->add(new FileLoggingConfiguration())
            ->add(new LoggedStorageConfiguration())
        ;
        $this->setDatatypeConverter($this->createDatatypeConverter());
        $this->init();
    }

    /**
     * Initialization.
     */
    protected function init()
    {
    }

    /**
     * Get configurations.
     *
     * @return \MwbExporter\Configuration\Configurations
     */
    public function getConfigurations()
    {
        return $this->configurations;
    }

    /**
     * Get configuration.
     *
     * @param string $key
     * @return \MwbExporter\Configuration\Configuration
     */
    public function getConfig($key)
    {
        return $this->configurations->get($key);
    }

    /**
     * Setup formatter.
     *
     * @param array $configurations
     * @throws \RuntimeException
     * @return \MwbExporter\Formatter\Formatter
     */
    public function setup($configurations = [])
    {
        $this->configurations->merge($configurations);

        return $this;
    }

    /**
     * Create datatype converter instance.
     *
     * @return \MwbExporter\Formatter\DatatypeConverterInterface
     */
    protected function createDatatypeConverter()
    {
    }

    /**
     * Set data type converter.
     *
     * @param \MwbExporter\Formatter\DatatypeConverterInterface $datatypeConverter
     * @return \MwbExporter\Formatter\Formatter
     */
    protected function setDatatypeConverter(DatatypeConverterInterface $datatypeConverter)
    {
        if (null == $datatypeConverter) {
            throw new \RuntimeException('DatatypeConverter can\'t be null.');
        }
        $this->datatypeConverter = $datatypeConverter;
        $this->datatypeConverter->setup();

        return $this;
    }

    /**
     * Get formatter name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get formatter version.
     *
     * @return string
     */
    public function getVersion()
    {
    }

    /**
     * Get registry object.
     *
     * @return \MwbExporter\Registry\Registry
     */
    public function getRegistry()
    {
        return $this->registry;
    }

    /**
     * Get data type converter.
     *
     * @return \MwbExporter\Formatter\DatatypeConverterInterface
     */
    public function getDatatypeConverter()
    {
        if (null === $this->datatypeConverter) {
            throw new \RuntimeException('DatatypeConverter has not been set.');
        }

        return $this->datatypeConverter;
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Formatter\FormatterInterface::createCatalog()
     */
    public function createCatalog(Base $parent, $node)
    {
        return new Catalog($parent, $node);
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Formatter\FormatterInterface::createSchemas()
     */
    public function createSchemas(Base $parent, $node)
    {
        return new Schemas($parent, $node);
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Formatter\FormatterInterface::createSchema()
     */
    public function createSchema(Base $parent, $node)
    {
        return new Schema($parent, $node);
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Formatter\FormatterInterface::createTables()
     */
    public function createTables(Base $parent, $node)
    {
        return new Tables($parent, $node);
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Formatter\FormatterInterface::createTable()
     */
    public function createTable(Base $parent, $node)
    {
        return new Table($parent, $node);
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Formatter\FormatterInterface::createForeignKeys()
     */
    public function createForeignKeys(Base $parent, $node)
    {
        return new ForeignKeys($parent, $node);
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Formatter\FormatterInterface::createForeignKey()
     */
    public function createForeignKey(Base $parent, $node)
    {
        return new ForeignKey($parent, $node);
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Formatter\FormatterInterface::createIndices()
     */
    public function createIndices(Base $parent, $node)
    {
        return new Indices($parent, $node);
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Formatter\FormatterInterface::createIndex()
     */
    public function createIndex(Base $parent, $node)
    {
        return new Index($parent, $node);
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Formatter\FormatterInterface::createColumns()
     */
    public function createColumns(Base $parent, $node)
    {
        return new Columns($parent, $node);
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Formatter\FormatterInterface::createColumn()
     */
    public function createColumn(Base $parent, $node)
    {
        return new Column($parent, $node);
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Formatter\FormatterInterface::createViews()
     */
    public function createViews(Base $parent, $node)
    {
        return new Views($parent, $node);
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Formatter\FormatterInterface::createView()
     */
    public function createView(Base $parent, $node)
    {
        return new View($parent, $node);
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Formatter\FormatterInterface::getPreferredWriter()
     */
    public function getPreferredWriter()
    {
        return 'default';
    }

    /**
     * Get comment tag prefixes.
     *
     * @return array
     */
    protected function getCommentTagPrefixes()
    {
        return ['MwbExporter'];
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Formatter\FormatterInterface::getCommentTagPrefix()
     */
    public function getCommentTagPrefix()
    {
        return implode('|', $this->getCommentTagPrefixes());
    }

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
    public function getCommentFormat()
    {
        return <<<EOF
Auto generated by MySQL Workbench Schema Exporter.
Version %VERSION% (%FORMATTER%) on %TIME%.
Goto https://github.com/mysql-workbench-schema-exporter/mysql-workbench-schema-exporter for more information.
EOF;
    }

    /**
     * Get comment variable substitution.
     *
     * @return array
     */
    public function getCommentVars()
    {
        return [
            '%VERSION%' => static::VERSION,
            '%FORMATTER%' => $this->getName().(($version = $this->getVersion()) ? ' '.$version : ''),
            '%TIME%' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get comment.
     *
     * @param string $format  Comment wrapper format
     * @return string
     */
    public function getComment($format)
    {
        return $this->getFormattedComment(strtr($this->getCommentFormat(), $this->getCommentVars()), $format);
    }

    /**
     * Get formatted comment.
     *
     * @param string $comment  Comment content
     * @param string $format  Comment wrapper format
     * @param int $width  Maximum line width
     * @return string
     */
    public function getFormattedComment($comment, $format, $width = 80)
    {
        return implode("\n", Comment::wrap($comment, $format, $width));
    }
}
