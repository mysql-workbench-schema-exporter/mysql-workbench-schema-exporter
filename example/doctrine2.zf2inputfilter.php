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

// show errors
error_reporting(E_ALL);

include 'util.php';

// enable autoloading of classes
autoload();

use \MwbExporter\Formatter\Doctrine2\ZF2InputFilterAnnotation\Formatter;

// formatter setup
$setup = array(
    Formatter::CFG_USE_LOGGED_STORAGE        => true,
    Formatter::CFG_INDENTATION               => 4,
    Formatter::CFG_BUNDLE_NAMESPACE          => 'MyBundle',
    Formatter::CFG_ENTITY_NAMESPACE          => 'Entity',
    Formatter::CFG_AUTOMATIC_REPOSITORY      => true,
    Formatter::CFG_SKIP_GETTER_SETTER        => false,
);

// lets do it
export('doctrine2-zf2inputfilterannotation', $setup);
