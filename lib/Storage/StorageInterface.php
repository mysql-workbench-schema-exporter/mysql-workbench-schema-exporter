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

namespace MwbExporter\Storage;

interface StorageInterface
{
    /**
     * Set storage name.
     *
     * @param string $name  Storage name
     * @return \MwbExporter\Storage\StorageInterface
     */
    public function setName($name);

    /**
     * Set the output directory.
     *
     * @param string $dir  Directory name
     * @return \MwbExporter\Storage\StorageInterface
     */
    public function setOutdir($dir);

    /**
     * Enable/disable backup.
     *
     * @return \MwbExporter\Storage\StorageInterface
     */
    public function setBackup($value);

    /**
     * Create directory.
     * 
     * @param string $path
     * @return \MwbExporter\Storage\StorageInterface
     */
    public function mkdir($path);

    /**
     * Check file if already exist and do backup if necessary.
     *
     * @param string $filename  The file name
     * @return string
     */
    public function getFile($filename);

    /**
     * Check file if already exist.
     *
     * @param string $filename  The file name
     * @return boolean
     */
    public function hasFile($filename);

    /**
     * Initialize storage for writing.
     *
     * @return \MwbExporter\Storage\StorageInterface
     */
    public function initialize();

    /**
     * Finalize storage after writing.
     *
     * @return \MwbExporter\Storage\StorageInterface
     */
    public function finalize();

    /**
     * Save the buffer content.
     *
     * @param string $filename  The filename
     * @param string $content   The content
     * @return \MwbExporter\Storage\StorageInterface
     */
    public function save($filename, $content);

    /**
     * Get result file name.
     *
     * @return string
     */
    public function getResult();
}