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

namespace MwbExporter\Writer;

use MwbExporter\Model\Document;
use MwbExporter\Storage\StorageInterface;

interface WriterInterface
{
    /**
     * Set the storage used for writer.
     *
     * @param \MwbExporter\Storage\StorageInterface $storage  The storage
     * @return \MwbExporter\Writer\WriterInterface
     */
    public function setStorage(StorageInterface $storage);

    /**
     * Get writer storage.
     *
     * @return \MwbExporter\Storage\StorageInterface
     */
    public function getStorage();

    /**
     * Set document model.
     *
     * @param \MwbExporter\Model\Document $document  The document
     * @return \MwbExporter\Writer\WriterInterface
     */
    public function setDocument(Document $document);

    /**
     * Get writer buffer.
     *
     * @return \MwbExporter\Buffer\Buffer.
     */
    public function getBuffer();

    /**
     * Mark the beginning of the writer.
     * 
     * @return \MwbExporter\Writer\WriterInterface
     */
    public function begin();

    /**
     * Open writer for a new file.
     *
     * @param string $filename
     * @return \MwbExporter\Writer\WriterInterface
     */
    public function open($filename);

    /**
     * Close opened file.
     *
     * @return \MwbExporter\Writer\WriterInterface
     */
    public function close();

    /**
     * Mark the end of the writer.
     * 
     * @return \MwbExporter\Writer\WriterInterface
     */
    public function end();

    /**
     * Write content to buffer.
     *
     * @return \MwbExporter\Writer\WriterInterface
     */
    public function write();

    /**
     * Write content to buffer with condition.
     *
     * @return \MwbExporter\Writer\WriterInterface
     */
    public function writeIf();

    /**
     * Write content to buffer via callback.
     *
     * @param \Closure $callback  The callback
     * @return \MwbExporter\Writer\WriterInterface
     */
    public function writeCallback($callback);

    /**
     * Increase indentation.
     *
     * @return \MwbExporter\Writer\WriterInterface
     */
    public function indent();

    /**
     * Decrease indentation.
     *
     * @return \MwbExporter\Writer\WriterInterface
     */
    public function outdent();
}