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

class ZipFileExporter
{
    protected $zip              = null;
    protected $fileName         = null;
    protected $filePath         = null;

    protected $savePath         = null;
    protected $availableFormats = array('yml' => 'yml', 'php' => 'php', 'yaml' => 'yml');
    protected $saveFormat       = 'php';

    protected $config           = null;

    public function __construct($savePath)
    {
        $this->config = \MwbExporter\Core\Registry::get('config');

        $this->savePath = realpath($savePath);

        $this->fileName = date('Y-m-d_h-i-s') . '_' . sprintf('%03d', mt_rand(1,999)) . '.zip';
        $this->filePath = $this->savePath . DIRECTORY_SEPARATOR . $this->fileName;

        $this->zip = new \ZipArchive();
        $res = $this->zip->open($this->filePath, \ZipArchive::CREATE);

        if($res !== true){
            throw new \Exception('error while creating zip in file ' . __FILE__ . ' on line ' . __LINE__);
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

    public function addTable(\MwbExporter\Core\Model\Table $table)
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

        $this->zip->addFromString($fileName, $table->display());
    }

    public function getFileName()
    {
        return $this->fileName;
    }

    public function getFilePath()
    {
        return $this->filePath;
    }

    public function save()
    {
        $this->zip->close();
    }
}