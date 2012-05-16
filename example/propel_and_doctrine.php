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

// show errors
error_reporting(E_ALL);

include 'util.php';

// enable autoloading of classes
autoload();

use \MwbExporter\Bootstrap;
use \MwbExporter\Formatter\Propel1\Xml\Formatter as PropelFormatter;
use \MwbExporter\Formatter\Doctrine2\Annotation\Formatter as DoctrineFormatter;

// lets stop the time
$start = microtime(true);

/**********************************************
 *                                            *
 * start generation for Propel1 Xml Formatter *
 *                                            *
 **********************************************/
$setup = array(
    PropelFormatter::CFG_USE_LOGGED_STORAGE  => true,
    PropelFormatter::CFG_INDENTATION         => 4,
    PropelFormatter::CFG_ADD_VENDOR          => true,
    PropelFormatter::CFG_NAMESPACE           => 'Acme\Namespace',

);

$filename = __DIR__.'/data/sakila.mwb';
$outDir   = __DIR__.'/result';

$bootstrap = new Bootstrap();

$formatter = $bootstrap->getFormatter('propel1-xml');
$formatter->setup($setup);
$document1 = $bootstrap->export($formatter, $filename, $outDir, 'zip');

// show the time needed to parse the mwb file
$end = microtime(true);
output($document1, $end - $start); // output Propel1 Xml


/*******************************************************
 *                                                     *
 * start generation for Doctrine2 Annotation Formatter *
 *                                                     *
 *******************************************************/

$setup = array(
    DoctrineFormatter::CFG_USE_LOGGED_STORAGE        => true,
    DoctrineFormatter::CFG_INDENTATION               => 4,
    DoctrineFormatter::CFG_FILENAME                  => '%entity%.%extension%',
    DoctrineFormatter::CFG_ANNOTATION_PREFIX         => 'ORM\\',
    DoctrineFormatter::CFG_BUNDLE_NAMESPACE          => 'MyBundle',
    DoctrineFormatter::CFG_ENTITY_NAMESPACE          => 'Entity',
    DoctrineFormatter::CFG_REPOSITORY_NAMESPACE      => '',
    DoctrineFormatter::CFG_AUTOMATIC_REPOSITORY      => true,
    DoctrineFormatter::CFG_SKIP_GETTER_SETTER        => false,
);
$formatter = $bootstrap->getFormatter('doctrine2-annotation');
$formatter->setup($setup);
$document2 = $bootstrap->export($formatter, $filename, $outDir, 'zip');

// show the time needed to parse the mwb file
$end = microtime(true);
output($document2, $end - $start); // output Doctrine2 Annotation
