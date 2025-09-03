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

namespace MwbExporter\Writer;

use MwbExporter\Buffer\Buffer;
use MwbExporter\Configuration\Indentation as IndentationConfiguration;
use MwbExporter\Helper\LineWrap;
use MwbExporter\Model\Document;
use MwbExporter\Storage\StorageInterface;

abstract class Writer implements WriterInterface
{
    /**
     * @var \MwbExporter\Storage\StorageInterface
     */
    protected $storage = null;

    /**
     * @var \MwbExporter\Model\Document
     */
    protected $document = null;

    /**
     * @var \MwbExporter\Buffer\Buffer
     */
    protected $buffer = null;

    /**
     * @var int
     */
    protected $level = 0;

    /**
     * @var string
     */
    protected $filename = null;

    /**
     * @var bool
     */
    protected $opened = false;

    /**
     * @var string
     */
    protected $cfmt = null;

    /**
     * @var \MwbExporter\Buffer\Buffer
     */
    protected $cbuff = null;

    /**
     * @var bool
     */
    protected $comment = false;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->buffer = new Buffer();
        $this->cbuff = new Buffer();
        $this->init();
    }

    /**
     * Initialization, override in the subclass to configure the writer.
     */
    protected function init()
    {
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Writer\WriterInterface::setStorage()
     */
    public function setStorage(StorageInterface $storage)
    {
        $this->storage = $storage;

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Writer\WriterInterface::getStorage()
     */
    public function getStorage()
    {
        return $this->storage;
    }

    protected function checkStorage()
    {
        if (null === $this->storage) {
            throw new \RuntimeException('Writer storage not assigned.');
        }
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Writer\WriterInterface::setDocument()
     */
    public function setDocument(Document $document)
    {
        $this->document = $document;

        return $this;
    }

    /**
     * Get writer buffer.
     *
     * @return \MwbExporter\Buffer\Buffer.
     */
    public function getBuffer()
    {
        return $this->buffer;
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Writer\WriterInterface::begin()
     */
    public function begin()
    {
        $this->checkStorage();
        $this->storage->initialize();

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Writer\WriterInterface::open()
     */
    public function open($filename)
    {
        if ($this->opened) {
            $this->close();
        }
        $this->level = 0;
        $this->buffer->clear();
        $this->filename = $filename;
        $this->opened = true;

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Writer\WriterInterface::close()
     */
    public function close()
    {
        if ($this->opened) {
            $this->opened = false;
            $this->flush();
        }

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Writer\WriterInterface::end()
     */
    public function end()
    {
        $this->close();
        $this->checkStorage();
        $this->storage->finalize();

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Writer\WriterInterface::write()
     */
    public function write()
    {
        if (!$this->opened) {
            throw new \RuntimeException('Writer is not opened.');
        }
        $args = func_get_args();
        if (count($args) > 1) {
            $line = call_user_func_array('sprintf', $args);
        } else {
            $line = $args[0];
        }
        $lines = explode("\n", $line);
        foreach ($lines as $line) {
            if (!$this->comment) {
                if ($line) {
                    $line = $this->getIndentation().$line;
                }
                $this->buffer[] = $line;
            } else {
                $this->cbuff[] = $line;
            }
        }

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Writer\WriterInterface::writeIf()
     */
    public function writeIf()
    {
        $args = func_get_args();
        if (count($args) > 1) {
            $condition = array_shift($args);
            if ((bool) $condition) {
                call_user_func_array([$this, 'write'], $args);
            }
        }

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Writer\WriterInterface::writeCallback()
     */
    public function writeCallback($callback)
    {
        if (is_callable($callback)) {
            $debugs = debug_backtrace(version_compare(PHP_VERSION, '5.3.6', '>=') ? DEBUG_BACKTRACE_PROVIDE_OBJECT : true);
            // this is current function debug backtrace
            $current = array_shift($debugs);
            // this is the current function caller debug backtrace
            $caller = array_shift($debugs);
            call_user_func($callback, $this, isset($caller['object']) ? $caller['object'] : $this);
        }

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Writer\WriterInterface::writeComment()
     */
    public function writeComment($comment)
    {
        $this
            ->commentStart()
            ->write($comment)
            ->commentEnd();

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Writer\WriterInterface::commentFormat()
     */
    public function commentFormat($format)
    {
        $this->cfmt = $format;

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Writer\WriterInterface::commentStart()
     */
    public function commentStart()
    {
        if ($this->comment) {
            throw new \RuntimeException('A comment already started.');
        }
        $this->cbuff->clear();
        $this->comment = true;

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Writer\WriterInterface::commentEnd()
     */
    public function commentEnd()
    {
        if (!$this->comment) {
            throw new \RuntimeException('No comment has been started.');
        }
        $lines = LineWrap::wrap(trim((string) $this->cbuff), $this->cfmt, null);
        $this->comment = false;
        foreach ($lines as $line) {
            $this->write($line);
        }

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Writer\WriterInterface::indent()
     */
    public function indent()
    {
        $this->level++;

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Writer\WriterInterface::outdent()
     */
    public function outdent()
    {
        $this->level--;
        if ($this->level < 0) {
            throw new \RuntimeException(sprintf('Can\'t outdent more.'));
        }

        return $this;
    }

    /**
     * Get line indentation.
     *
     * @return string
     */
    protected function getIndentation()
    {
        if ($this->document) {
            /** @var \MwbExporter\Configuration\Indentation $indentation */
            $indentation = $this->document->getConfig(IndentationConfiguration::class);

            return $indentation->getIndentation($this->level);
        }
    }

    /**
     * Save the buffer content.
     *
     * @return \MwbExporter\Writer\Writer
     */
    abstract protected function flush();
}
