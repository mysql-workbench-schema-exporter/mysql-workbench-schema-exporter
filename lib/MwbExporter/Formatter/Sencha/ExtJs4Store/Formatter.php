<?php

/*
 * The MIT License
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

/**
 * Formatter.php
 * Created on May 5, 2013 9:46:06 PM
 *
 * @author    Boy van Moorsel <development@wittestier.nl>
 * @license   license.wittestier.nl
 * @copyright 2013 WitteStier <copyright@wittestier.nl>
 */

namespace MwbExporter\Formatter\Sencha\ExtJS4Store;

use MwbExporter\Formatter\Sencha\Formatter as BaseFormatter;
use MwbExporter\Model\Base;

class Formatter
    extends BaseFormatter
{

    const CFG_MODEL_PREFIX = 'modelPrefix';
    const CFG_GENERATE_PROXY = 'generateProxy';

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Formatter::init()
     */
    protected function init()
    {
        parent::init();
        $this->addConfigurations(array(
            static::CFG_FILENAME => 'store/%entity%.%extension%',
            static::CFG_CLASS_PREFIX => 'App.store',
            static::CFG_PARENT_CLASS => 'Ext.data.Store',
            static::CFG_MODEL_PREFIX => 'App.model',
            static::CFG_GENERATE_PROXY => false,
        ));
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Formatter::createTable()
     */
    public function createTable(Base $parent, $node)
    {
        return new Model\Table($parent, $node);
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\FormatterInterface::getTitle()
     */
    public function getTitle()
    {
        return 'Sencha ExtJS4 Store';
    }

}