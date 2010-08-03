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

class MwbExporter_Formatter_Doctrine1_Yaml_Loader implements MwbExporter_Core_IFormatter
{
    
    public function createCatalog($parameter){
        return new MwbExporter_Formatter_Doctrine1_Yaml_Model_Catalog($parameter);
    }

    public function createColumn($parameter){
        return new MwbExporter_Formatter_Doctrine1_Yaml_Model_Column($parameter);
    }

    public function createColumns($parameter){
        return new MwbExporter_Formatter_Doctrine1_Yaml_Model_Columns($parameter);
    }

    public function createForeignKey($parameter){
        return new MwbExporter_Formatter_Doctrine1_Yaml_Model_ForeignKey($parameter);
    }

    public function createForeignKeys($parameter){
        return new MwbExporter_Formatter_Doctrine1_Yaml_Model_ForeignKeys($parameter);
    }

    public function createIndex($parameter){
        return new MwbExporter_Formatter_Doctrine1_Yaml_Model_Index($parameter);
    }

    public function createIndices($parameter){
        return new MwbExporter_Formatter_Doctrine1_Yaml_Model_Indices($parameter);
    }

    public function createSchema($parameter){
        return new MwbExporter_Formatter_Doctrine1_Yaml_Model_Schema($parameter);
    }

    public function createSchemas($parameter){
        return new MwbExporter_Formatter_Doctrine1_Yaml_Model_Schemas($parameter);
    }

    public function createTable($parameter){
        return new MwbExporter_Formatter_Doctrine1_Yaml_Model_Table($parameter);
    }

    public function createTables($parameter){
        return new MwbExporter_Formatter_Doctrine1_Yaml_Model_Tables($parameter);
    }

    public function createView($parameter){
        return new MwbExporter_Formatter_Doctrine1_Yaml_Model_View($parameter);
    }

    public function createViews($parameter){
        return new MwbExporter_Formatter_Doctrine1_Yaml_Model_Views($parameter);
    }
    
    public function useDatatypeConverter($type, MwbExporter_Core_Model_Column $column){
        return MwbExporter_Formatter_Doctrine1_Yaml_DatatypeConverter::getType($type, $column);
    }
}