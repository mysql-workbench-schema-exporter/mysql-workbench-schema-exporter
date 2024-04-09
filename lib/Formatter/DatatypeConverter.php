<?php

/*
 * The MIT License
 *
 * Copyright (c) 2010 Johannes Mueller <circus2(at)web.de>
 * Copyright (c) 2012-2023 Toha <tohenk@yahoo.com>
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

namespace MwbExporter\Formatter;

use MwbExporter\Model\Column;

abstract class DatatypeConverter implements DatatypeConverterInterface
{
    /**
     * @var \MwbExporter\Formatter\FormatterInterface
     */
    protected $formatter;

    /**
     * @var array
     */
    protected $dataTypes = [];

    /**
     * @var array
     */
    protected $userDatatypes = [];

    /**
     * Register data types mapping.
     *
     * @param array $dataTypes
     * @return \MwbExporter\Formatter\DatatypeConverter
     */
    protected function register($dataTypes = [])
    {
        if (count($dataTypes)) {
            $this->dataTypes = array_merge($this->dataTypes, $dataTypes);
        }

        return $this;
    }

    /**
     * Register user data types mapping.
     *
     * @param array $dataTypes
     * @return \MwbExporter\Formatter\DatatypeConverter
     */
    public function registerUserDatatypes($dataTypes = [])
    {
        if (count($dataTypes)) {
            $this->userDatatypes = array_merge($this->userDatatypes, $dataTypes);
        }

        return $this;
    }

    /**
     * Get all data types.
     *
     * @return array
     */
    public function getAllDataTypes()
    {
        return [
            DatatypeConverterInterface::DATATYPE_TINYINT,
            DatatypeConverterInterface::DATATYPE_SMALLINT,
            DatatypeConverterInterface::DATATYPE_MEDIUMINT,
            DatatypeConverterInterface::DATATYPE_INT,
            DatatypeConverterInterface::DATATYPE_BIGINT,
            DatatypeConverterInterface::DATATYPE_FLOAT,
            DatatypeConverterInterface::DATATYPE_DOUBLE,
            DatatypeConverterInterface::DATATYPE_DECIMAL,
            DatatypeConverterInterface::DATATYPE_CHAR,
            DatatypeConverterInterface::DATATYPE_NCHAR,
            DatatypeConverterInterface::DATATYPE_VARCHAR,
            DatatypeConverterInterface::DATATYPE_NVARCHAR,
            DatatypeConverterInterface::DATATYPE_JSON,
            DatatypeConverterInterface::DATATYPE_BINARY,
            DatatypeConverterInterface::DATATYPE_VARBINARY,
            DatatypeConverterInterface::DATATYPE_TINYTEXT,
            DatatypeConverterInterface::DATATYPE_TEXT,
            DatatypeConverterInterface::DATATYPE_MEDIUMTEXT,
            DatatypeConverterInterface::DATATYPE_LONGTEXT,
            DatatypeConverterInterface::DATATYPE_TINYBLOB,
            DatatypeConverterInterface::DATATYPE_BLOB,
            DatatypeConverterInterface::DATATYPE_MEDIUMBLOB,
            DatatypeConverterInterface::DATATYPE_LONGBLOB,
            DatatypeConverterInterface::DATATYPE_DATETIME,
            DatatypeConverterInterface::DATATYPE_DATETIME_F,
            DatatypeConverterInterface::DATATYPE_DATE,
            DatatypeConverterInterface::DATATYPE_DATE_F,
            DatatypeConverterInterface::DATATYPE_TIME,
            DatatypeConverterInterface::DATATYPE_TIME_F,
            DatatypeConverterInterface::DATATYPE_YEAR,
            DatatypeConverterInterface::DATATYPE_TIMESTAMP,
            DatatypeConverterInterface::DATATYPE_TIMESTAMP_F,
            DatatypeConverterInterface::DATATYPE_GEOMETRY,
            DatatypeConverterInterface::DATATYPE_LINESTRING,
            DatatypeConverterInterface::DATATYPE_POLYGON,
            DatatypeConverterInterface::DATATYPE_MULTIPOINT,
            DatatypeConverterInterface::DATATYPE_MULTILINESTRING,
            DatatypeConverterInterface::DATATYPE_MULTIPOLYGON,
            DatatypeConverterInterface::DATATYPE_GEOMETRYCOLLECTION,
            DatatypeConverterInterface::DATATYPE_BIT,
            DatatypeConverterInterface::DATATYPE_ENUM,
            DatatypeConverterInterface::DATATYPE_SET,
            DatatypeConverterInterface::USERDATATYPE_BOOLEAN,
            DatatypeConverterInterface::USERDATATYPE_BOOL,
            DatatypeConverterInterface::USERDATATYPE_FIXED,
            DatatypeConverterInterface::USERDATATYPE_FLOAT4,
            DatatypeConverterInterface::USERDATATYPE_FLOAT8,
            DatatypeConverterInterface::USERDATATYPE_INT1,
            DatatypeConverterInterface::USERDATATYPE_INT2,
            DatatypeConverterInterface::USERDATATYPE_INT3,
            DatatypeConverterInterface::USERDATATYPE_INT4,
            DatatypeConverterInterface::USERDATATYPE_INT8,
            DatatypeConverterInterface::USERDATATYPE_INTEGER,
            DatatypeConverterInterface::USERDATATYPE_LONGVARBINARY,
            DatatypeConverterInterface::USERDATATYPE_LONGVARCHAR,
            DatatypeConverterInterface::USERDATATYPE_LONG,
            DatatypeConverterInterface::USERDATATYPE_MIDDLEINT,
            DatatypeConverterInterface::USERDATATYPE_NUMERIC,
            DatatypeConverterInterface::USERDATATYPE_DEC,
            DatatypeConverterInterface::USERDATATYPE_CHARACTER,
        ];
    }

    /**
     * Get registered data types.
     *
     * @return array
     */
    public function getRegisteredDataTypes()
    {
        return array_merge($this->dataTypes, $this->userDatatypes);
    }

    /**
     * Get data type mapping for associated key.
     *
     * @throws \RuntimeException
     * @return string
     */
    public function getDataType($key)
    {
        $result = null;
        // check for existing datatype, and raise an exception
        // if it doesn't exist. Usefull when new data type defined
        // in the new version of MySQL Workbench
        if (isset($this->dataTypes[$key])) {
            $result = $this->dataTypes[$key];
        } else if (isset($this->userDatatypes[$key])) {
            $result = $this->userDatatypes[$key];
        }
        if (null === $result) {
            throw new \RuntimeException(sprintf('Unknown data type "%s".', $key));
        }

        return $this->transformDataType($key, $result);
    }

    /**
     * Get column type as native type supported by formatter.
     *
     * @param string $type
     * @return string
     */
    public function getNativeType($type)
    {
        return $type;
    }

    /**
     * Get column type mapping.
     *
     * @param \MwbExporter\Model\Column $column
     * @return string
     */
    public function getMappedType(Column $column)
    {
        return $this->getDataType($column->getColumnType());
    }

    /**
     * Get column type with its attribute.
     *
     * @param \MwbExporter\Model\Column $column
     * @return string
     */
    public function getType(Column $column)
    {
        return $this->getMappedType($column);
    }

    /**
     * Perform data type transformation if necessary.
     *
     * @param string $key
     * @param string $dataType
     * @return string
     */
    public function transformDataType($key, $dataType)
    {
        return $dataType;
    }

    /**
     * Set formatter.
     *
     * @param \MwbExporter\Formatter\FormatterInterface $formatter
     */
    public function setFormatter(FormatterInterface $formatter)
    {
        $this->formatter = $formatter;
    }
}
