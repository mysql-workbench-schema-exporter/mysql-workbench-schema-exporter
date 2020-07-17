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

namespace MwbExporter\Logger;

class Logger implements LoggerInterface
{
    /**
     * @var string
     */
    protected $format = '%TIME% [%TYPE%] %MESSAGE%';

    /**
     * @var string
     */
    protected $eol = "\n";

    /**
     * @var array
     */
    protected $options = array();

    /**
     * Constructor.
     *
     * @param array $options  Logger options
     */
    public function __construct($options = array())
    {
        $this->options = $options;
        if (isset($this->options['format'])) {
            $this->format = $this->options['format'];
            unset($this->options['format']);
        }
        if (isset($this->options['eol'])) {
            $this->eol = $this->options['eol'];
            unset($this->options['eol']);
        }
        $this->init();
    }

    /**
     * Initialization. Override in subclass for logger initialization.
     */
    protected function init()
    {
    }

    public function log($message, $level = LoggerInterface::INFO)
    {
        $log = strtr($this->format, array('%TIME%' => date('Y-m-d H:i:s'), '%TYPE%' => $level, '%MESSAGE%' => $message));
        $this->sendLog($log.$this->eol);
    }

    /**
     * Send message log.
     *
     * @param string $message  The log message
     */
    protected function sendLog($message)
    {
    }
}