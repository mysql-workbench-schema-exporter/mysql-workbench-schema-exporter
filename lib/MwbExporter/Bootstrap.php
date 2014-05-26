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
    protected static $formatters = null;

    /**
     * Get available formatters.
     *
     * @return array
     */
    public function getFormatters()
    {
        if (null === self::$formatters) {
            self::$formatters = array();
            $pattern = implode(DIRECTORY_SEPARATOR, array(__DIR__, 'Formatter', '*', '*', 'Formatter.php'));
            foreach (glob($pattern) as $filename) {
                $dirs = explode(DIRECTORY_SEPARATOR, dirname(realpath($filename)));
                $subVendor = array_pop($dirs);
                $vendor = array_pop($dirs);
                $formatter = strtolower(implode('-', array($vendor, $subVendor)));
                $formatterClass = sprintf('\\MwbExporter\\Formatter\\%s\\%s\\Formatter', $vendor, $subVendor);
                self::$formatters[$formatter] = $formatterClass;
            }
        }

        return self::$formatters;
    }

    /**
     * Get formatter.
     *
     * @param string $name  The formatter name
     * @return \MwbExporter\Formatter\FormatterInterface
     */
    public function getFormatter($name)
    {
        $formatters = $this->getFormatters();
        if (!array_key_exists($name, $formatters)) {
            throw new \InvalidArgumentException(sprintf('Unknown formatter "%s".', $name));
        }
        $class = $formatters[$name];

        return new $class($name);
    }

    /**
     * Get writer.
     * 
     * @param string $name  The writer name
     * @return \MwbExporter\Writer\WriterInterface
     */
    public function getWriter($name)
    {
        $class = sprintf('\\MwbExporter\\Writer\\%sWriter', ucfirst($name));
        if (class_exists($class)) {
            $writter = new $class();

            return $writter;
        }

        throw new \InvalidArgumentException(sprintf('Writer %s not found.', $class));
    }

    /**
     * Get storage.
     * 
     * @param string $name  The storage name
     * @return \MwbExporter\Storage\StorageInterface
     */
    public function getStorage($name)
    {
        $class = sprintf('\\MwbExporter\\Storage\\%sStorage', ucfirst($name));
        if (class_exists($class)) {
            $storage = new $class();

            return $storage;
        }

        throw new \InvalidArgumentException(sprintf('Storage %s not found.', $class));
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
        if (!is_readable($filename)) {
            throw new \InvalidArgumentException(sprintf('Document not found "%s".', $filename));
        }
        if ($formatter && $storage = $this->getStorage($storage)) {
            if ($formatter->getRegistry()->config->get(FormatterInterface::CFG_USE_LOGGED_STORAGE)) {
                $storage = new LoggedStorage($storage); 
            }
            $storage->setName(basename($filename, '.mwb'));
            $storage->setOutdir(realpath($outDir) ? realpath($outDir) : $outDir);
            $storage->setBackup($formatter->getRegistry()->config->get(FormatterInterface::CFG_BACKUP_FILE));
            $writer = $this->getWriter($formatter->getPreferredWriter());
            $writer->setStorage($storage);
            if ($eol = strtolower(trim($formatter->getRegistry()->config->get(FormatterInterface::CFG_EOL)))) {
                switch ($eol) {
                    case 'win':
                        $writer->getBuffer()->setEol("\r\n");
                        break;

                    case 'unix':
                        $writer->getBuffer()->setEol("\n");
                        break;
                }
            }
            $document = new Document($formatter);
            if (strlen($logFile = $formatter->getRegistry()->config->get(FormatterInterface::CFG_LOG_FILE))) {
                $logger = new LoggerFile(array('filename' => $logFile));
            } elseif ($formatter->getRegistry()->config->get(FormatterInterface::CFG_LOG_TO_CONSOLE)) {
                $logger = new LoggerConsole();
            } else {
                $logger = new Logger();
            }
            $document->setLogger($logger);
            $document->load($filename);
            $document->write($writer);
            if ($e = $document->getError()) {
                throw $e;
            }

            return $document;
        }
    }
}