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

class Loader implements \MwbExporter\Core\IFormatter
{
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
        );
    
    /**
     *
     * @param array $setup 
     */
    public function __construct(array $setup=array()){
        $setup = array_merge($this->_defaultZendConfig, $setup);
        
        \MwbExporter\Core\Registry::set('config', $setup);
    }
 
    /**
     *
     * @param array $parameter
     * @param \MwbExporter\Core\Model\Base $parent
     * @return Model\Catalog 
     */
    public function createCatalog($parameter, \MwbExporter\Core\Model\Base $parent){
        return new Model\Catalog($parameter, $parent);
    }

    /**
     *
     * @param array $parameter
     * @param \MwbExporter\Core\Model\Base $parent
     * @return Model\Column 
     */
    public function createColumn($parameter, \MwbExporter\Core\Model\Base $parent){
        return new Model\Column($parameter, $parent);
    }

    /**
     *
     * @param array $parameter
     * @param \MwbExporter\Core\Model\Base $parent
     * @return Model\Columns 
     */
    public function createColumns($parameter, \MwbExporter\Core\Model\Base $parent){
        return new Model\Columns($parameter, $parent);
    }

    /**
     *
     * @param array $parameter
     * @param \MwbExporter\Core\Model\Base $parent
     * @return Model\ForeignKey 
     */
    public function createForeignKey($parameter, \MwbExporter\Core\Model\Base $parent){
        return new Model\ForeignKey($parameter, $parent);
    }

    /**
     *
     * @param array $parameter
     * @param \MwbExporter\Core\Model\Base $parent
     * @return Model\ForeignKeys 
     */
    public function createForeignKeys($parameter, \MwbExporter\Core\Model\Base $parent){
        return new Model\ForeignKeys($parameter, $parent);
    }

    /**
     *
     * @param array $parameter
     * @param \MwbExporter\Core\Model\Base $parent
     * @return Model\Index 
     */
    public function createIndex($parameter, \MwbExporter\Core\Model\Base $parent){
        return new Model\Index($parameter, $parent);
    }

    /**
     *
     * @param array $parameter
     * @param \MwbExporter\Core\Model\Base $parent
     * @return Model\Indices 
     */
    public function createIndices($parameter, \MwbExporter\Core\Model\Base $parent){
        return new Model\Indices($parameter, $parent);
    }

    /**
     *
     * @param array $parameter
     * @param \MwbExporter\Core\Model\Base $parent
     * @return Model\Schema 
     */
    public function createSchema($parameter, \MwbExporter\Core\Model\Base $parent){
        return new Model\Schema($parameter, $parent);
    }

    /**
     *
     * @param array $parameter
     * @param \MwbExporter\Core\Model\Base $parent
     * @return Model\Schemas 
     */
    public function createSchemas($parameter, \MwbExporter\Core\Model\Base $parent){
        return new Model\Schemas($parameter, $parent);
    }

    /**
     *
     * @param array $parameter
     * @param \MwbExporter\Core\Model\Base $parent
     * @return Model\Table 
     */
    public function createTable($parameter, \MwbExporter\Core\Model\Base $parent){
        return new Model\Table($parameter, $parent);
    }

    /**
     *
     * @param array $parameter
     * @param \MwbExporter\Core\Model\Base $parent
     * @return Model\Tables 
     */
    public function createTables($parameter, \MwbExporter\Core\Model\Base $parent){
        return new Model\Tables($parameter, $parent);
    }

    /**
     *
     * @param array $parameter
     * @param \MwbExporter\Core\Model\Base $parent
     * @return Model\View 
     */
    public function createView($parameter, \MwbExporter\Core\Model\Base $parent){
        return new Model\View($parameter, $parent);
    }

    /**
     *
     * @param array $parameter
     * @param \MwbExporter\Core\Model\Base $parent
     * @return Model\Views 
     */
    public function createViews($parameter, \MwbExporter\Core\Model\Base $parent){
        return new Model\Views($parameter, $parent);
    }
    
    /**
     *
     * @param type $type
     * @param \MwbExporter\Core\Model\Column $column
     * @return type 
     */
    public function useDatatypeConverter($type, \MwbExporter\Core\Model\Column $column){
        return DatatypeConverter::getType($type, $column);
    }
}