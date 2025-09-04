<?php

/*
 * The MIT License
 *
 * Copyright (c) 2010 Johannes Mueller <circus2(at)web.de>
 * Copyright (c) 2012-2025 Toha <tohenk@yahoo.com>
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

use MwbExporter\Configuration\Filename as FilenameConfiguration;
use MwbExporter\Configuration\UserDatatype as UserDatatypeConfiguration;
use MwbExporter\Formatter\FormatterInterface;
use MwbExporter\Logger\LoggerInterface;
use MwbExporter\Writer\WriterInterface;

class Document extends Base
{
    /**
     * @var string
     */
    protected $filename = null;

    /**
     * @var \SimpleXMLElement
     */
    protected $xml = null;

    /**
     * @var \MwbExporter\Model\PhysicalModel
     */
    protected $physicalModel = null;

    /**
     * @var \MwbExporter\Formatter\FormatterInterface
     */
    protected $formatter = null;

    /**
     * @var \MwbExporter\Writer\WriterInterface
     */
    protected $writer = null;

    /**
     * @var \MwbExporter\Logger\LoggerInterface
     */
    protected $logger = null;

    /**
     * @var \Exception
     */
    protected $error = null;

    /**
     * Constructor.
     *
     * @param FormatterInterface $formatter
     */
    public function __construct(FormatterInterface $formatter)
    {
        $this->formatter = $formatter;
        parent::__construct();
    }

    /**
     * Get the formatter object.
     *
     * @return \MwbExporter\Formatter\FormatterInterface
     */
    public function getFormatter()
    {
        return $this->formatter;
    }

    /**
     * Get document writer.
     *
     * @return \MwbExporter\Writer\WriterInterface
     */
    public function getWriter()
    {
        return $this->writer;
    }

    /**
     * Get logger object.
     *
     * @return \MwbExporter\Logger\LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * Set logger object.
     *
     * @param \MwbExporter\Logger\LoggerInterface $logger
     * @return \MwbExporter\Model\Document
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * Log a message.
     *
     * @param string $message  Log message
     * @param string $level    Log level
     * @return \MwbExporter\Model\Document
     */
    public function addLog($message, $level = LoggerInterface::INFO)
    {
        if ($this->logger) {
            $this->logger->log($message, $level);
        }

        return $this;
    }

    /**
     * Get configuration.
     *
     * @param string $key
     * @return \MwbExporter\Configuration\Configuration
     */
    public function getConfig($key)
    {
        return $this->formatter->getConfig($key);
    }

    /**
     * Get factory object.
     *
     * @return \MwbExporter\Registry\RegistryHolder
     */
    public function getFactory()
    {
        return $this->formatter->getRegistry()->factory;
    }

    /**
     * Get reference object.
     *
     * @return \MwbExporter\Registry\RegistryHolder
     */
    public function getReference()
    {
        return $this->formatter->getRegistry()->reference;
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Model\Base::getDocument()
     */
    public function getDocument()
    {
        return $this;
    }

    /**
     * Get physical model.
     *
     * @return \MwbExporter\Model\PhysicalModel
     */
    public function getPhysicalModel()
    {
        return $this->physicalModel;
    }

    /**
     * Load a workbench file.
     *
     * @param string $filename
     */
    public function load($filename)
    {
        $this->filename = $filename;
        $this->readXML($this->filename);
        $this->configure($this->xml->value);
        $this->loadUserDataTypes();
        $this->checkDataTypes();
        $this->parse();
    }

    protected function readXML($filename)
    {
        $this->addLog(sprintf('Read document "%s"', basename($filename)));
        $this->xml = simplexml_load_file("zip://".str_replace("\\", "/", realpath($filename))."#document.mwb.xml");
        if (false === $this->xml) {
            throw new \RuntimeException(sprintf('Can\'t load "%s", may be it not MySQL Workbench document.', $filename));
        }
    }

    protected function loadUserDataTypes()
    {
        $dataTypeConverter = $this->formatter->getDataTypeConverter();
        $dataTypes = [];
        $userTypes = $this->node->xpath("//value[@key='userDatatypes']")[0];
        /** @var \MwbExporter\Configuration\UserDatatype $userDatatype */
        $userDatatype = $this->getConfig(UserDatatypeConfiguration::class);
        foreach ($userTypes as $userType) {
            $userDatatypeName = (string) $userType->xpath("value")[2];
            $cleanedUserDatatypeName = $userDatatype->clean($userDatatypeName);
            if ($userDatatypeName !== $cleanedUserDatatypeName) {
                $dataTypes[(string) $userType['id']] = $cleanedUserDatatypeName;
            } else {
                $dataTypes[(string) $userType['id']] = $dataTypeConverter->getDataType((string) $userType->xpath("link[@key='actualType']")[0]);
            }
        }
        $dataTypeConverter->registerUserDatatypes($dataTypes);
    }

    protected function checkDataTypes()
    {
        $dataTypeConverter = $this->formatter->getDataTypeConverter();
        $registeredDataTypes = array_keys($dataTypeConverter->getRegisteredDataTypes());
        if (count($dataTypes = array_diff($dataTypeConverter->getAllDataTypes(), $registeredDataTypes))) {
            $this->addLog(sprintf('The following data types is not handled: %s', implode(', ', $dataTypes)), LoggerInterface::WARNING);
        }
    }

    protected function parse()
    {
        $elems = $this->node->xpath("value[@key='physicalModels']/value");
        $this->physicalModel = new PhysicalModel($this, $elems[0]);
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Model\Base::write()
     */
    public function write(WriterInterface $writer)
    {
        $this->writer = $writer;
        $this->error = null;
        $writer->setDocument($this);
        $writer->begin();
        try {
            if ($format = $this->getFormatter()->getCommentFormatter()) {
                $writer->commentFormat($format);
            }
            $this->addLog(sprintf('Start writing document "%s"', basename($this->filename)));
            $this->physicalModel->write($writer);
            $this->addLog('Done writing document');
        } catch (\Exception $e) {
            $this->error = $e;
            $this->addLog($e->getMessage(), LoggerInterface::ERROR);
        }
        $writer->end();

        return $this;
    }

    /**
     * Get the latest thrown error while document being written.
     * Return null if document written successfully.
     *
     * @return \Exception
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Translate and replace variable tags with contextual data from object using supplied format.
     *
     * If format omitted, it considered equal to configuration `%entity%.%extension%`.
     * By default, the translated filename will be checked against the variables provided by the object
     * to ensure no variables tag ('%var%') left.
     *
     * @param string $format  Filename format
     * @param \MwbExporter\Model\Base $object  The object to translate
     * @param array $vars  The overriden variables
     * @param bool $check  True to check the translated filename
     * @throws \Exception
     * @return string
     */
    public function translateFilename($format, Base $object, $vars = [], $check = true)
    {
        if ($object && ($filename = $object->translateVars(null !== $format ? $format : $this->getConfig(FilenameConfiguration::class)->getValue(), $vars))) {
            if ($check && false !== strpos($filename, '%')) {
                throw new \Exception(sprintf('Some filename variables were not converted, may be a mistype (%s)!', substr($filename, strpos($filename, '%'), strrpos($filename, '%') + 1)));
            }

            return $filename;
        }
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Model\Base::getVars()
     */
    protected function getVars()
    {
        return ['%extension%' => $this->getFormatter()->getFileExtension()];
    }
}
