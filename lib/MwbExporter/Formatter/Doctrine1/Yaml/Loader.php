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

namespace MwbExporter\Formatter\Doctrine1\Yaml;

use MwbExporter\Core\IFormatter;
use MwbExporter\Core\Registry;
use MwbExporter\Core\Model\Base;
use MwbExporter\Core\Model\Column;

class Loader implements IFormatter
{
    protected $datatypeConverter = null;

    public function __construct(array $setup = array()){
        Registry::set('config', $setup);
    }

    public function createCatalog($parameter, Base $parent){
        return new Model\Catalog($parameter, $parent);
    }

    public function createColumn($parameter, Base $parent){
        return new Model\Column($parameter, $parent);
    }

    public function createColumns($parameter, Base $parent){
        return new Model\Columns($parameter, $parent);
    }

    public function createForeignKey($parameter, Base $parent){
        return new Model\ForeignKey($parameter, $parent);
    }

    public function createForeignKeys($parameter, Base $parent){
        return new Model\ForeignKeys($parameter, $parent);
    }

    public function createIndex($parameter, Base $parent){
        return new Model\Index($parameter, $parent);
    }

    public function createIndices($parameter, Base $parent){
        return new Model\Indices($parameter, $parent);
    }

    public function createSchema($parameter, Base $parent){
        return new Model\Schema($parameter, $parent);
    }

    public function createSchemas($parameter, Base $parent){
        return new Model\Schemas($parameter, $parent);
    }

    public function createTable($parameter, Base $parent){
        return new Model\Table($parameter, $parent);
    }

    public function createTables($parameter, Base $parent){
        return new Model\Tables($parameter, $parent);
    }

    public function createView($parameter, Base $parent){
        return new Model\View($parameter, $parent);
    }

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