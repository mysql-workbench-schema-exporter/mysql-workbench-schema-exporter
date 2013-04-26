<?php

/*
 * The MIT License
 *
 * Copyright (c) 2010 Johannes Mueller <circus2(at)web.de>
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

namespace MwbExporter\Formatter\Doctrine2\AnnotationZF2InputFilter;

use MwbExporter\Formatter as BaseFormatter;
use MwbExporter\Model\Base;

class Formatter
    extends BaseFormatter
{

    const CFG_ANNOTATION_PREFIX = 'useAnnotationPrefix';
    const CFG_BUNDLE_NAMESPACE = 'bundleNamespace';
    const CFG_ENTITY_NAMESPACE = 'entityNamespace';
    const CFG_REPOSITORY_NAMESPACE = 'repositoryNamespace';
    const CFG_AUTOMATIC_REPOSITORY = 'useAutomaticRepository';
    const CFG_SKIP_GETTER_SETTER = 'skipGetterAndSetter';
    const CFG_GENERATE_ENTITY_POPULATE = 'generateEntityPopulate';
    const CFG_GENERATE_ENTITY_GETARRAYCOPY = 'generateEntityGetArrayCopy';
    const CFG_GENERATE_ENTITY_SERIALIZATION = 'generateEntitySerialization';
    const CFG_QUOTE_IDENTIFIER = 'quoteIdentifier';
    const CFG_BACKUP_FILE = 'backupExistingFile';
    const CFG_ENHANCE_M2M_DETECTION = 'enhanceManyToManyDetection';

    /**
     * (non-PHPdoc)
     * 
     * @see \MwbExporter\Formatter::init()
     */
    protected function init()
    {
        $this->setDatatypeConverter(new DatatypeConverter());
        $this->addConfigurations(array(
            static::CFG_INDENTATION => 4,
            static::CFG_FILENAME => 'Entity/%entity%.%extension%',
            static::CFG_ANNOTATION_PREFIX => 'ORM\\',
            static::CFG_BUNDLE_NAMESPACE => '',
            static::CFG_ENTITY_NAMESPACE => 'Entity',
            static::CFG_REPOSITORY_NAMESPACE => 'Repository',
            static::CFG_AUTOMATIC_REPOSITORY => false,
            static::CFG_SKIP_GETTER_SETTER => false,
            static::CFG_GENERATE_ENTITY_POPULATE => true,
            static::CFG_GENERATE_ENTITY_GETARRAYCOPY => true,
            static::CFG_GENERATE_ENTITY_SERIALIZATION => true,
            static::CFG_QUOTE_IDENTIFIER => false,
            static::CFG_BACKUP_FILE => false,
            static::CFG_ENHANCE_M2M_DETECTION => false,
        ));
    }

    /**
     * (non-PHPdoc)
     * 
     * @see \MwbExporter\Formatter::createTable()
     */
    public function createTable(Base $parent, $node)
    {
        return new Model\Table($parent, $node);
    }

    /**
     * (non-PHPdoc)
     * 
     * @see \MwbExporter\FormatterInterface::createColumns()
     */
    public function createColumns(Base $parent, $node)
    {
        return new Model\Columns($parent, $node);
    }

    /**
     * (non-PHPdoc)
     * 
     * @see \MwbExporter\FormatterInterface::createColumn()
     */
    public function createColumn(Base $parent, $node)
    {
        return new Model\Column($parent, $node);
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\FormatterInterface::createIndex()
     */
    public function createIndex(Base $parent, $node)
    {
        return new Model\Index($parent, $node);
    }

    /**
     * (non-PHPdoc)
     * 
     * @see \MwbExporter\FormatterInterface::getTitle()
     */
    public function getTitle()
    {
        return 'Doctrine 2.0 Annotation with ZF2 input filter Classes';
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\FormatterInterface::getFileExtension()
     */
    public function getFileExtension()
    {
        return 'php';
    }

}

