<?php

/*
 * The MIT License
 *
 * Copyright (c) 2010 Johannes Mueller <circus2(at)web.de>
 * Copyright (c) 2012-2025 Toha <tohenk@yahoo.com>
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

// show errors
error_reporting(E_ALL);

include 'util.php';

// enable autoloading of classes
autoload();

use MwbExporter\Configuration\Indentation as IndentationConfiguration;
use MwbExporter\Configuration\LoggedStorage as LoggedStorageConfiguration;
use MwbExporter\Formatter\Propel1\Configuration\ModelNamespace as ModelNamespaceConfiguration;
use MwbExporter\Formatter\Propel1\Xml\Configuration\Vendor as VendorConfiguration;
use MwbExporter\Formatter\Doctrine2\Configuration\AutomaticRepository as AutomaticRepositoryConfiguration;
use MwbExporter\Formatter\Doctrine2\Configuration\BundleNamespace as BundleNamespaceConfiguration;
use MwbExporter\Formatter\Doctrine2\Configuration\EntityNamespace as EntityNamespaceConfiguration;
use MwbExporter\Formatter\Doctrine2\Annotation\Configuration\GetterSetterSkip as GetterSetterSkipConfiguration;

/**********************************************
 *                                            *
 * start generation for Propel1 Xml Formatter *
 *                                            *
 **********************************************/

$setup = [
    LoggedStorageConfiguration::class => true,
    IndentationConfiguration::class => 4,
    ModelNamespaceConfiguration::class => 'Acme\Namespace',
    VendorConfiguration::class => true,
];

// lets do it
export('propel1-xml', $setup);

/*******************************************************
 *                                                     *
 * start generation for Doctrine2 Annotation Formatter *
 *                                                     *
 *******************************************************/

$setup = [
    LoggedStorageConfiguration::class => true,
    IndentationConfiguration::class => 4,
    BundleNamespaceConfiguration::class => 'MyBundle',
    EntityNamespaceConfiguration::class => 'Entity',
    AutomaticRepositoryConfiguration::class => true,
    GetterSetterSkipConfiguration::class => false,
];

// lets do it again
export('doctrine2-annotation', $setup);
