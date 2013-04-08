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

namespace MwbExporter;

use MwbExporter\Formatter\FormatterInterface;
use MwbExporter\Model\Document;
use MwbExporter\Storage\LoggedStorage;
use MwbExporter\Logger\Logger;
use MwbExporter\Logger\LoggerFile;
use MwbExporter\Logger\LoggerConsole;

class Bootstrap
{
    /**
     * @var array
     */
    protected $formatters = array();

    /**
     * @var array
     */
    protected $writers = array();

    /**
     * @var array
     */
    protected $storages = array();

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this
            // formatter
            ->registerFormatter('doctrine2-annotation', '\\MwbExporter\\Formatter\\Doctrine2\Annotation\\Formatter')
            ->registerFormatter('doctrine2-yaml',       '\\MwbExporter\\Formatter\\Doctrine2\Yaml\\Formatter')
            ->registerFormatter('doctrine1-yaml',       '\\MwbExporter\\Formatter\\Doctrine1\Yaml\\Formatter')
            ->registerFormatter('propel1-xml',          '\\MwbExporter\\Formatter\\Propel1\Xml\\Formatter')
            ->registerFormatter('sencha-extjs3',        '\\MwbExporter\\Formatter\\Sencha\ExtJS3\\Formatter')
            ->registerFormatter('zend-rest-controller', '\\MwbExporter\\Formatter\\Zend\Controller\\Formatter')
            ->registerFormatter('zend-dbtable',         '\\MwbExporter\\Formatter\\Zend\DbTable\\Formatter')
            ->registerFormatter('cake2-php',            '\\MwbExporter\\Formatter\\Cake2\Php\\Formatter')
            // writer
            ->registerWriter('default',   '\\MwbExporter\\Writer\\DefaultWriter')
            ->registerWriter('aggregate', '\\MwbExporter\\Writer\\AggregateWriter')
            // storage
            ->registerStorage('file', '\\MwbExporter\\Storage\\FileStorage')
            ->registerStorage('zip',  '\\MwbExporter\\Storage\\ZipStorage')
        ;
    }

    /**
     * Register formatter.
     *
     * @param string $name  Formatter name
     * @param string $class Formatter class name
     * @return \MwbExporter\Bootstrap
     */
    protected function registerFormatter($name, $class)
    {
        $this->formatters[$name] = $class;

        return $this;
    }

    /**
     * Register writer.
     *
     * @param string $name  Writer name
     * @param string $class Writer class name
     * @return \MwbExporter\Bootstrap
     */
    protected function registerWriter($name, $class)
    {
        $this->writers[$name] = $class;

        return $this;
    }

    /**
     * Register storage.
     *
     * @param string $name  Storage name
     * @param string $class Storage class name
     * @return \MwbExporter\Bootstrap
     */
    protected function registerStorage($name, $class)
    {
        $this->storages[$name] = $class;

        return $this;
    }

    /**
     * Get registered formatter.
     *
     * @return array
     */
    public function getFormatters()
    {
        return $this->formatters;
    }

    /**
     * Get registered writer.
     *
     * @return array
     */
    public function getWriters()
    {
        return $this->writers;
    }

    /**
     * Get registered storage.
     *
     * @return array
     */
    public function getStorages()
    {
        return $this->writers;
    }

    /**
     * Get formatter.
     *
     * @param string $name  The formatter name
     * @return \MwbExporter\Formatter\FormatterInterface
     */
    public function getFormatter($name)
    {
        if (array_key_exists($name, $this->formatters)) {
            $formatterClass = $this->formatters[$name];
            $formatter = new $formatterClass();
    
            return $formatter;
        }
    }

    /**
     * Get writer.
     * 
     * @param string $name  The writer name
     * @return \MwbExporter\Writer\WriterInterface
     */
    public function getWriter($name)
    {
        if (array_key_exists($name, $this->writers)) {
            $writterClass = $this->writers[$name];
            $writter = new $writterClass();
    
            return $writter;
        }
    }

    /**
     * Get storage.
     * 
     * @param string $name  The storage name
     * @return \MwbExporter\Storage\StorageInterface
     */
    public function getStorage($name)
    {
        if (array_key_exists($name, $this->storages)) {
            $storageClass = $this->storages[$name];
            $storage = new $storageClass();
    
            return $storage;
        }
    }

    /**
     * Load workbench schema and generate the code.
     *
     * @param \MwbExporter\Formatter\FormatterInterface $formatter
     * @param string $filename
     * @param string $outDir
     * @param string $storage
     * @return \MwbExporter\Model\Document
     */
    public function export(FormatterInterface $formatter, $filename, $outDir, $storage = 'file')
    {
        if ($formatter && $storage = $this->getStorage($storage)) {
            if ($formatter->getRegistry()->config->get(FormatterInterface::CFG_USE_LOGGED_STORAGE)) {
                $storage = new LoggedStorage($storage); 
            }
            $storage->setOutdir(realpath($outDir) ? realpath($outDir) : $outDir);
            $storage->setBackup($formatter->getRegistry()->config->get(FormatterInterface::CFG_BACKUP_FILE));
            $writer = $this->getWriter($formatter->getPreferredWriter());
            $writer->setStorage($storage);
            $document = new Document($formatter, $filename);
            if (strlen($logFile = $formatter->getRegistry()->config->get(FormatterInterface::CFG_LOG_FILE))) {
                $logger = new LoggerFile(array('filename' => $logFile));
            } elseif ($formatter->getRegistry()->config->get(FormatterInterface::CFG_LOG_TO_CONSOLE)) {
                $logger = new LoggerConsole();
            } else {
                $logger = new Logger();
            }
            $document->setLogger($logger);
            $document->write($writer);
            if ($e = $document->getError()) {
                throw $e;
            }

            return $document;
        }
    }
}