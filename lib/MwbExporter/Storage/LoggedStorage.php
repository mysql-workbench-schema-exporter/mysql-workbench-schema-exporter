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

use MwbExporter\Buffer\Buffer;

class LoggedStorage extends Storage
{
    /**
     * @var \MwbExporter\Buffer\Buffer
     */
    protected $logs = null;

    /**
     * @var \MwbExporter\Storage\StorageInterface
     */
    protected $storage = null;

    public function __construct(StorageInterface $storage)
    {
        if ($storage instanceof LoggedStorage) {
            throw new \InvalidArgumentException('Inner storage must not a LoggedStorage instance.');
        }
        $this->storage = $storage;
        $this->logs = new Buffer();
        $this->logs->setEol("\n\n");
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Storage\Storage::setOutdir()
     */
    public function setOutdir($dir)
    {
        parent::setOutdir($dir);
        $this->storage->setOutdir($dir);

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Storage\Storage::setBackup()
     */
    public function setBackup($value)
    {
        parent::setBackup($value);
        $this->storage->setBackup($value);

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Storage\Storage::initialize()
     */
    public function initialize()
    {
        $this->storage->initialize();
        $this->result = $this->outDir;

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Storage\Storage::save()
     */
    public function save($filename, $content)
    {
        $this->logs[] = $content;
        $this->storage->save($filename, $content);

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Storage\Storage::finalize()
     */
    public function finalize()
    {
        $this->storage->finalize();

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Storage\Storage::getResult()
     */
    public function getResult()
    {
        return $this->storage->getResult();
    }

    /**
     * Get logs.
     *
     * @return \MwbExporter\Buffer\Buffer
     */
    public function getLogs()
    {
        return $this->logs;
    }
}