<?php
/*
 *  The MIT License
 *
 *  Copyright (c) 2010 Johannes Mueller <circus2(at)web.de>
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is
 *  furnished to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in
 *  all copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

namespace MwbExporter\Core\Helper;

use MwbExporter\Core\Registry;
use MwbExporter\Core\Model\Table;

class FileExporter
{
    protected $savePath         = null;
    protected $availableFormats = array('yml' => 'yml', 'php' => 'php', 'yaml' => 'yml');
    protected $saveFormat       = 'php';

    protected $config           = null;

    public function __construct($savePath)
    {
        $this->config = Registry::get('config');
        $this->mkdir($savePath);
        $this->savePath = realpath($savePath);
    }

    protected function mkdir($path)
    {
        if (!is_readable($path)) {
            @mkdir($path, null, true);
        }
    }

    public function setSaveFormat($format = 'php')
    {
        if(array_key_exists($format, $this->availableFormats)){
            $this->saveFormat = $this->availableFormats[$format];
            return true;
        }
        return false;
    }

    protected function getTableFileName(Table $table)
    {
        $schemaName = $table->getSchemaName();
        $tableName  = $table->getRawTableName();
        if (isset($this->config['filename']) && $this->config['filename'])
        {
            $searched = array('%schema%', '%table%', '%entity%', '%extension%');
            $replaced = array($schemaName, $tableName, $table->getModelName(), $this->saveFormat);
            $fileName = str_replace(
                $searched,
                $replaced,
                $this->config['filename']
            );

            if (false !== strpos($fileName, '%'))
            {
                throw new \Exception(sprintf('All filename variable where not converted. Perhaps a misstyped name (%s) ?', substr($fileName, strpos($fileName, '%'), strrpos($fileName, '%'))));
            }
        }
        else
        {
            $fileName   = $schemaName . '.' . $tableName . '.' . $this->saveFormat;
        }

        return $fileName;
    }

    public function addTable(Table $table)
    {
        $filename = $this->savePath.DIRECTORY_SEPARATOR.$this->getTableFileName($table);
        $this->mkdir(dirname($filename));
        file_put_contents($filename, $table->display());
    }

    public function getFileName()
    {
        return $this->savePath;
    }

    public function save()
    {
    }
}