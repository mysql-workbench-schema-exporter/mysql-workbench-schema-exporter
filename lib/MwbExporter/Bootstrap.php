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
        if (null === static::$formatters) {
            static::$formatters = array();
            $dirs = array();
            // if we'are using Composer, include these formatters
            if ($composer = $this->getComposer()) {
                $vendorDir = realpath(__DIR__.'/../../../..');
                if (is_readable($installed = $vendorDir.'/composer/installed.json')) {
                    $packages = json_decode(file_get_contents($installed), true);
                    foreach ($packages as $package) {
                        if (isset($package['name']) && is_dir($dir = $vendorDir.DIRECTORY_SEPARATOR.$package['name'])) {
                            /**
                             * Check for extended package extra attribute to customize
                             * formatter inclusion.
                             *
                             * An example to include formatter using class:
                             * {
                             *     "extra" : {
                             *         "mysql-workbench-schema-exporter" : {
                             *             "formatters" : {
                             *                 "my-simple" : "\\My\\Simple\\Formatter",
                             *                 "my-simple2" : "\\My\\Simple2\\Formatter"
                             *             }
                             *         }
                             *     }
                             * }
                             *
                             * An example include formatter using namespace:
                             * {
                             *     "extra" : {
                             *         "mysql-workbench-schema-exporter" : {
                             *             "namespaces" : {
                             *                 "lib/My/Exporter" : "\\Acme\\My\\Exporter",
                             *             }
                             *         }
                             *     }
                             * }
                             */
                            if (isset($package['extra']) && isset($package['extra']['mysql-workbench-schema-exporter'])) {
                                if (is_array($options = $package['extra']['mysql-workbench-schema-exporter'])) {
                                    if (isset($options['formatters']) && is_array($options['formatters'])) {
                                        foreach ($options['formatters'] as $name => $class) {
                                            $this->registerFormatter($name, $class);
                                        }
                                    }
                                    if (isset($options['namespaces']) && is_array($options['namespaces'])) {
                                        foreach ($options['namespaces'] as $lib => $namespace) {
                                            $dirs[$dir.DIRECTORY_SEPARATOR.$lib] = $namespace;
                                        }
                                    }
                                    continue;
                                }
                            }
                            $dirs[] = $dir;
                        }
                    }
                }
            } else {
                $dirs[] = realpath(__DIR__.'/../..');
            }
            $this->scanFormatters($dirs);
        }

        return static::$formatters;
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
            list($module, $exporter) = explode('-', $name, 2);
            $class = sprintf('\\MwbExporter\\Formatter\\%s\\%s\\Formatter', ucfirst(strtolower($module)), ucfirst(strtolower($exporter)));
            if (!class_exists($class)) {
                throw new \InvalidArgumentException(sprintf('Unknown formatter "%s".', $name));
            }
        } else {
            $class = $formatters[$name];
        }

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
                    case FormatterInterface::EOL_WIN:
                        $writer->getBuffer()->setEol("\r\n");
                        break;

                    case FormatterInterface::EOL_UNIX:
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

    /**
     * Register schema exporter class.
     *
     * @param string $name
     * @param string $class
     * @return \MwbExporter\Bootstrap
     */
    public function registerFormatter($name, $class)
    {
        $name = strtolower(is_array($name) ? implode('-', $name) : $name);
        if (array_key_exists($name, static::$formatters)) {
            throw new \RuntimeException(sprintf('Formatter %s already registered.', $class));
        }
        static::$formatters[$name] = $class;

        return $this;
    }

    /**
     * Scan directories for available formatters.
     *
     * Try to guess if schema formatter (or exporter) is present in the specified directory
     * which is named according to convention: MwbExporter\Formatter\*\*\Formatter.php.
     *
     * @param array $dirs
     * @return \MwbExporter\Bootstrap
     */
    protected function scanFormatters($dirs)
    {
        $dirs = is_array($dirs) ? $dirs : array($dirs);
        foreach ($dirs as $key => $dir) {
            $namespace = null;
            if (is_string($key)) {
                $namespace = $dir;
                $dir = $key;
            }
            if (is_dir($dir)) {
                $parts = array('*', '*', 'Formatter.php');
                if (null == $namespace) {
                    $parts = array_merge(array('*', 'MwbExporter', 'Formatter'), $parts);
                }
                $pattern = implode(DIRECTORY_SEPARATOR, array_merge(array($dir), $parts));
                foreach (glob($pattern) as $filename) {
                    $parts = explode(DIRECTORY_SEPARATOR, dirname(realpath($filename)));
                    $exporter = array_pop($parts);
                    $module = array_pop($parts);
                    $class = sprintf('%s\\%s\\%s\\Formatter', $namespace ?: '\\MwbExporter\\Formatter', $module, $exporter);
                    $this->registerFormatter(array($module, $exporter), $class);
                }
            }
        }

        return $this;
    }

    /**
     * Get Composer autoloader instance.
     *
     * @return \Composer\Autoload\ClassLoader
     */
    protected function getComposer()
    {
        if ($autoloaders = spl_autoload_functions()) {
            foreach ($autoloaders as $autoload) {
                if (is_array($autoload)) {
                    $class = $autoload[0];
                    if ('Composer\Autoload\ClassLoader' == get_class($class)) {
                        return $class;
                    }
                }
            }
        }
    }
}
