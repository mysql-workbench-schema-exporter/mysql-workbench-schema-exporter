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

class ZipStorage extends Storage
{
    /**
     * @var \ZipArchive
     */
    protected $zip = null;

    public function initialize()
    {
        if (null === ($filename = $this->name)) {
            $filename = date('Y-m-d_h-i-s').'_'.sprintf('%03d', mt_rand(1, 999));
        }
        $filename = $this->getFile($filename.'.zip');
        $this->zip = new \ZipArchive();
        if (false === $this->zip->open($filename, \ZipArchive::CREATE)) {
            throw new \Exception(sprintf('Can\'t create archive %s.', $filename));
        }
        $this->result = $filename;

        return $this;
    }

    public function hasFile($filename)
    {
        if ($this->zip) {
            return false !== $this->zip->locateName($filename) ? true : false;
        }
    }

    public function save($filename, $content)
    {
        $this->zip->addFromString($filename, $content);

        return $this;
    }

    public function finalize()
    {
        $this->zip->close();

        return $this;
    }
}