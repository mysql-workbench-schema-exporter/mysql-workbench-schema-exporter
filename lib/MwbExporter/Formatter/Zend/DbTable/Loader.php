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

namespace MwbExporter\Formatter\Zend\DbTable;

use MwbExporter\Core\IFormatter;
use MwbExporter\Core\Registry;
use MwbExporter\Core\Model\Base;
use MwbExporter\Core\Model\Column;

class Loader implements IFormatter
{
    protected $datatypeConverter = null;
    protected $_defaultZendConfig = array(
            /* */
            'tablePrefix'               => 'Application_Model_DbTable_',

            /* If you want to use your personnal Zend_Db_Table_Abstract,
             * you need to specifie here his name
             */
            'parentTable'               => 'Zend_Db_Table_Abstract',

            /* If true, the script will generate the $_dependentTables.
             * Before active this option please read:
             * http://framework.zend.com/manual/en/zend.db.table.relationships.html#zend.db.table.relationships.cascading */
            'generateDRI'               => false,

            /* */
            'generateGetterSetter'      => false,

            /* */
            'indentation'               => 4,

            /* */
            'filename'                  => 'DbTable/%schema%/%entity%.%extension%',

            /* */
            'generateSchema'            => true,

            /* */
            'generateName'              => true,

            /* */
            'generateReferences'        => true,

            /* */
            'generatePrimary'           => true,
        );

    /**
     *
     * @param array $setup
     */
    public function __construct(array $setup=array()){
        $setup = array_merge($this->_defaultZendConfig, $setup);

        Registry::set('config', $setup);
    }

    /**
     *
     * @param array $parameter
     * @param \MwbExporter\Core\Model\Base $parent
     * @return Model\Catalog
     */
    public function createCatalog($parameter, Base $parent){
        return new Model\Catalog($parameter, $parent);
    }

    /**
     *
     * @param array $parameter
     * @param \MwbExporter\Core\Model\Base $parent
     * @return Model\Column
     */
    public function createColumn($parameter, Base $parent){
        return new Model\Column($parameter, $parent);
    }

    /**
     *
     * @param array $parameter
     * @param \MwbExporter\Core\Model\Base $parent
     * @return Model\Columns
     */
    public function createColumns($parameter, Base $parent){
        return new Model\Columns($parameter, $parent);
    }

    /**
     *
     * @param array $parameter
     * @param \MwbExporter\Core\Model\Base $parent
     * @return Model\ForeignKey
     */
    public function createForeignKey($parameter, Base $parent){
        return new Model\ForeignKey($parameter, $parent);
    }

    /**
     *
     * @param array $parameter
     * @param \MwbExporter\Core\Model\Base $parent
     * @return Model\ForeignKeys
     */
    public function createForeignKeys($parameter, Base $parent){
        return new Model\ForeignKeys($parameter, $parent);
    }

    /**
     *
     * @param array $parameter
     * @param \MwbExporter\Core\Model\Base $parent
     * @return Model\Index
     */
    public function createIndex($parameter, Base $parent){
        return new Model\Index($parameter, $parent);
    }

    /**
     *
     * @param array $parameter
     * @param \MwbExporter\Core\Model\Base $parent
     * @return Model\Indices
     */
    public function createIndices($parameter, Base $parent){
        return new Model\Indices($parameter, $parent);
    }

    /**
     *
     * @param array $parameter
     * @param \MwbExporter\Core\Model\Base $parent
     * @return Model\Schema
     */
    public function createSchema($parameter, Base $parent){
        return new Model\Schema($parameter, $parent);
    }

    /**
     *
     * @param array $parameter
     * @param \MwbExporter\Core\Model\Base $parent
     * @return Model\Schemas
     */
    public function createSchemas($parameter, Base $parent){
        return new Model\Schemas($parameter, $parent);
    }

    /**
     *
     * @param array $parameter
     * @param \MwbExporter\Core\Model\Base $parent
     * @return Model\Table
     */
    public function createTable($parameter, Base $parent){
        return new Model\Table($parameter, $parent);
    }

    /**
     *
     * @param array $parameter
     * @param \MwbExporter\Core\Model\Base $parent
     * @return Model\Tables
     */
    public function createTables($parameter, Base $parent){
        return new Model\Tables($parameter, $parent);
    }

    /**
     *
     * @param array $parameter
     * @param \MwbExporter\Core\Model\Base $parent
     * @return Model\View
     */
    public function createView($parameter, Base $parent){
        return new Model\View($parameter, $parent);
    }

    /**
     *
     * @param array $parameter
     * @param \MwbExporter\Core\Model\Base $parent
     * @return Model\Views
     */
    public function createViews($parameter, Base $parent){
        return new Model\Views($parameter, $parent);
    }

    public function getDatatypeConverter(){
        if (null === $this->datatypeConverter) {
            $this->datatypeConverter = new DatatypeConverter();
            $this->datatypeConverter->setUp();
        }

        return $this->datatypeConverter;
    }

    public function useDatatypeConverter(Column $column){
        return $this->getDatatypeConverter()->getType($column);
    }
}