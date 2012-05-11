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
use \MwbExporter\Formatter\Doctrine2\Annotation\Formatter;

// lets stop the time
$start = microtime(true);

// formatter setup
$setup = array(
    Formatter::CFG_USE_LOGGED_STORAGE           => true,
    Formatter::CFG_INDENTATION               => 4,
    Formatter::CFG_FILENAME                  => '%entity%.%extension%',
    Formatter::CFG_ANNOTATION_PREFIX         => 'ORM\\',
    Formatter::CFG_BUNDLE_NAMESPACE          => 'MyBundle',
    Formatter::CFG_ENTITY_NAMESPACE          => 'Entity',
    Formatter::CFG_REPOSITORY_NAMESPACE      => '',
    Formatter::CFG_AUTOMATIC_REPOSITORY      => true,
    Formatter::CFG_SKIP_GETTER_SETTER        => false,
    Formatter::CFG_ENHANCED_M2M_DETECTION    => false,
);
$filename = __DIR__.'/data/test.mwb';
$outDir = __DIR__.'/result';

$bootstrap = new Bootstrap();
$formatter = $bootstrap->getFormatter('doctrine2-annotation');
$formatter->setup($setup);
$document  = $bootstrap->export($formatter, $filename, $outDir, 'zip');

// show the time needed to parse the mwb file
$end = microtime(true);

output($document, $end - $start);
