<?php

/*
 * The MIT License
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

/**
 * Table.php
 * Created on May 5, 2013 9:46:52 PM
 *
 * @author    Boy van Moorsel <development@wittestier.nl>
 * @license   license.wittestier.nl
 * @copyright 2013 WitteStier <copyright@wittestier.nl>
 */

namespace MwbExporter\Formatter\Sencha\ExtJS4Store\Model;

use MwbExporter\Formatter\Sencha\Model\Table as BaseTable;
use MwbExporter\Formatter\Sencha\ExtJS4Store\Formatter;
use MwbExporter\Writer\WriterInterface;

class Table
    extends BaseTable
{

    /**
     * Write store.
     * 
     * @param \MwbExporter\Writer\WriterInterface $writer
     * @return type
     */
    public function writeTable(WriterInterface $writer)
    {
        $writer->open($this->getTableFileName());
        $this->writeBody($writer);
        $writer->close();
        return self::WRITE_OK;
    }

    /**
     * Write store body code.
     * 
     * @param \MwbExporter\Writer\WriterInterface $writer
     * @return \MwbExporter\Formatter\Sencha\ExtJS4Store\Model\Table
     */
    public function writeBody(WriterInterface $writer)
    {
        $writer
            ->write("Ext.define('%s', %s);", $this->getClassPrefix() . '.' . $this->getModelName(), $this->asStore())
        ;

        return $this;
    }

    /**
     * 
     * @return type
     */
    public function asStore()
    {
        $config = $this->getDocument()->getConfig();
        $generateData = $config->get(Formatter::CFG_GENERATE_DATA);
        $generatePaging = $config->get(Formatter::CFG_GENERATE_PAGING);
        $generateBuffer = $config->get(Formatter::CFG_GENERATE_BUFFER);
        $generateSorter = $config->get(Formatter::CFG_GENERATE_SORT);
        $generateGrouper = $config->get(Formatter::CFG_GENERATE_GROUP);
        $generateFilter = $config->get(Formatter::CFG_GENERATE_Filter);
        $generateProxy = $config->get(Formatter::CFG_GENERATE_PROXY);

        $result = array(
            'extend' => $this->getParentClass(),
            'uses' => array(sprintf('%s.%s', $this->getModelPrefix(), $this->getModelName())),
            'model' => sprintf('%s.%s', $this->getModelPrefix(), $this->getModelName()),
        );

        if ($generateData) {
            $result = array_merge($result, $this->getDataOptions());
        }

        if ($generatePaging) {
            $result = array_merge($result, $this->getPagingOptions());
        }

        if ($generateBuffer) {
            $result = array_merge($result, $this->getBufferOptions());
        }

        if ($generateSorter) {
            $result = array_merge($result, $this->getSortOptions());
        }

        if ($generateGrouper) {
            $result = array_merge($result, $this->getGroupOptions());
        }

        if ($generateFilter) {
            $result = array_merge($result, $this->getFilterOptions());
        }

        if ($generateProxy && count($data = $this->getAjaxProxy())) {
            $result['proxy'] = $data;
        }

        // End.
        return $this->getJSObject($result);
    }

    /**
     * Get model classname prefix.
     * 
     * @return type
     */
    protected function getModelPrefix()
    {
        // End.
        return $this->translateVars($this->getDocument()->getConfig()->get(Formatter::CFG_MODEL_PREFIX));
    }

    /**
     * Get the proxy url prefix.
     * 
     * @return string
     */
    protected function getUrlPrefix()
    {
        // End.
        return rtrim($this->getDocument()->getConfig()->get(Formatter::CFG_PROXY_URL_PREFIX), '/');
    }

    /**
     * Get the default date options.
     * 
     * @return array
     */
    protected function getDataOptions()
    {
        // End.
        return array(
            'date' => array(),
            'autoDestroy' => false,
            'autoLoad' => false,
            'autoSync' => false,
            'clearRemovedOnLoad' => true,
            'batchUpdateMode' => 'operation',
        );
    }

    /**
     * Get the default paging options.
     * 
     * @return array
     */
    protected function getPagingOptions()
    {
        // End.
        return array(
            'clearOnPageLoad' => true,
            'pageSize' => 25,
        );
    }

    /**
     * Get the default buffer options.
     * 
     * @return array
     */
    protected function getBufferOptions()
    {
        // End.
        return array(
            'buffered' => false,
            'purgePageCount' => 5,
            'leadingBufferZone' => 200,
            'trailingBufferZone' => 25,
        );
    }

    /**
     * Get the default sort options.
     * 
     * @return array
     */
    protected function getSortOptions()
    {
        // End.
        return array(
            'sorters' => array(),
            'remoteSort' => false,
            'sortOnFilter' => true,
            'sortOnLoad' => true,
            'defaultSortDirection' => 'ASC',
        );
    }

    /**
     * Get default group options.
     * 
     * @return array
     */
    protected function getGroupOptions()
    {
        // End.
        return array(
            'remoteGroup' => false,
            'groupDir' => 'ASC',
            'groupField' => '',
        );
    }

    /**
     * Get default filter options.
     * 
     * @return array
     */
    protected function getFilterOptions()
    {
        // End.
        return array(
            'filters' => array(),
            'filterOnLoad' => true,
            'remoteFilter' => false,
            'statefulFilters' => false,
        );
    }

    /**
     * Get defaul ajax proxy options.
     *
     * @link http://docs.sencha.com/extjs/4.2.0/#!/api/Ext.data.proxy.Ajax
     * @return array
     */
    protected function getAjaxProxy()
    {
        // End.
        return array(
            'type' => 'ajax',
            'url' => sprintf('%s/%s', $this->getUrlPrefix(), strtolower($this->getModelName())),
            'api' => $this->getApiOptions(),
            'reader' => $this->getJsonReaderOptions(),
            'writer' => $this->getJsonWriterOptions(),
        );
    }

    /**
     * Get proxy API options.
     *
     * @link http://docs.sencha.com/extjs/4.2.0/#!/api/Ext.data.proxy.Ajax-cfg-api
     * @return array
     */
    private function getApiOptions()
    {
        $urlPrefix = $this->getUrlPrefix();
        $modelName = strtolower($this->getModelName());

        // End.
        return array(
            'read' => sprintf('%s/%s', $urlPrefix, $modelName),
            'update' => sprintf('%s/%s/update', $urlPrefix, $modelName),
            'create' => sprintf('%s/%s/create', $urlPrefix, $modelName),
            'destroy' => sprintf('%s/%s/destroy', $urlPrefix, $modelName),
        );
    }

    /**
     * Get default json reader options.
     *
     * @link http://docs.sencha.com/extjs/4.2.0/#!/api/Ext.data.reader.Json
     * @return array
     */
    private function getJsonReaderOptions()
    {
        // End.
        return array(
            'type' => 'json',
            'root' => strtolower($this->getModelName()),
            'messageProperty' => 'message',
        );
    }

    /**
     * Get default json writer options.
     *
     * @link http://docs.sencha.com/extjs/4.2.0/#!/api/Ext.data.writer.Json
     * @return array
     */
    private function getJsonWriterOptions()
    {
        // End.
        return array(
            'type' => 'json',
            'root' => strtolower($this->getModelName()),
            'encode' => true,
            'expandData' => true,
        );
    }

}