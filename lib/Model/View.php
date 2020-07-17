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

namespace MwbExporter\Model;

use MwbExporter\Writer\WriterInterface;
use Doctrine\Common\Inflector\Inflector;

class View extends Base
{
    const WRITE_OK = 1;
    const WRITE_EXTERNAL = 2;

    /**
     * @var \MwbExporter\Model\Columns
     */
    protected $columns;

    protected function init()
    {
        $elems = $this->node->xpath("value[@key='columns']");
        $this->columns = $this->getFormatter()->createColumns($this, $elems[0]);
    }

    protected function hasParameters()
    {
        return true;
    }

    /**
     * Get the owner schema.
     *
     * @return \MwbExporter\Model\Schema
     */
    public function getSchema()
    {
        return $this->getParent()->getParent();
    }

    /**
     * Get raw view name.
     *
     * @return string
     */
    public function getRawViewName()
    {
        return $this->getName();
    }

    /**
     * Get the view model name.
     *
     * @return string
     */
    public function getModelName()
    {
        return $this->beautify($this->getRawViewName());
    }

    /**
     * Get the view model name in plural form.
     *
     * @return string
     */
    public function getPluralModelName()
    {
        return Inflector::pluralize($this->getModelName());
    }

    /**
     * Get table category.
     *
     * @return string
     */
    public function getCategory()
    {
        if ($category = trim($this->parseComment('category'))) {
            return $category;
        }
    }

    /**
     * Check if view is an external entity.
     *
     * @return boolean
     */
    public function isExternal()
    {
        $external = trim($this->parseComment('external'));
        if ($external === 'true') {
            return true;
        }

        return false;
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Model\Base::getVars()
     */
    protected function getVars()
    {
        return array(
            '%view%'      => $this->getRawViewName(),
            '%entity%'    => $this->getModelName(),
            '%category%'  => $this->getCategory(),
        );
    }

    /**
     * Get view file name.
     *
     * @param string $format  The filename format
     * @param array $vars  The overriden variables
     * @return string
     */
    public function getViewFileName($format = null, $vars = array())
    {
        if (0 === strlen($filename = $this->getDocument()->translateFilename($format, $this, $vars)))
        {
            $filename = implode('.', array($this->getSchema()->getName(), $this->getRawViewName(), $this->getFormatter()->getFileExtension()));
        }

        return $filename;
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Model\Base::write()
     */
    public function write(WriterInterface $writer)
    {
        try {
            switch ($this->writeView($writer)) {
                case self::WRITE_OK:
                    $status = 'OK';
                    break;

                case self::WRITE_EXTERNAL:
                    $status = 'skipped, marked as external';
                    break;

                default:
                    $status = 'unsupported';
                    break;
            }
            $this->getDocument()->addLog(sprintf('* %s: %s', $this->getRawViewName(), $status));
        } catch (\Exception $e) {
            $this->getDocument()->addLog(sprintf('* %s: ERROR', $this->getRawViewName()));
            throw $e;
        }

        return $this;
    }

    /**
     * Write view entity as code.
     *
     * @param \MwbExporter\Writer\WriterInterface $writer
     */
    public function writeView(WriterInterface $writer)
    {
    }
}