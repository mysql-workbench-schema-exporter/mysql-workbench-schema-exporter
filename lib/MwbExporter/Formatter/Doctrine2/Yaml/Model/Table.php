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

namespace MwbExporter\Formatter\Doctrine2\Yaml\Model;

class Table extends \MwbExporter\Core\Model\Table
{
    public function __construct($data, $parent)
    {
        parent::__construct($data, $parent);
    }

    /**
     * return the table definition
     * Yaml format
     *
     * @return string the table definition
     */
    public function display()
    {
        $config = \MwbExporter\Core\Registry::get('config');

        $return = array();
        /**
         * formatting the entity's namespace
         */
        $return[] = sprintf('%s\\%s\\%s:',
            (isset($config['bundleNamespace']) && $config['bundleNamespace']) ? $config['bundleNamespace'] : '',
            (isset($config['entityNamespace']) && $config['entityNamespace']) ? $config['entityNamespace'] : 'Entity',
            $this->getModelName()
        );
        
        /**
         * formatting repository's Namespace
         */
        $repositoryNamespace = '';
        if(isset($config['repositoryNamespace']) && $config['repositoryNamespace']){
            $repositoryNamespace = $config['repositoryNamespace'] . '\\';
        }

        $return[] = $this->indentation() . 'type: entity';

        /**
         * Adding the repository class if necessary
         */
        if(isset($config['useAutomaticRepository']) && $config['useAutomaticRepository']){
            $return[] = $this->indentation() . 'repositoryClass: '.$repositoryNamespace . $this->getModelName() . 'Repository';
        }

        // check if schema name has to be included
        if(isset($config['extendTableNameWithSchemaName']) && $config['extendTableNameWithSchemaName']){
            // $schemaname = table->tables->schema->getName()
            $schemaName = $this->getParent()->getParent()->getName();
            $return[] = $this->indentation(1) . 'table: ' . $schemaName . '.' . $this->getRawTableName();
        } else {
            // add table name if necessary
            if($this->getModelName() !== ucfirst($this->getRawTableName())){
                $return[] = $this->indentation(1) . 'table: ' . $this->getRawTableName();
            }
        }

        $return[] = $this->columns->display();

        // add relations
        if(count($this->relations) > 0){
            $return[] = $this->indentation() . 'relations:';

            foreach($this->relations as $relation){
                $return[] = $relation->display();
            }
        }

        // add indices
        if(count($this->indexes) > 0){
            $return[] = $this->indentation() . 'indexes:';

            foreach($this->indexes as $index){
                $return[] = $index->display();
            }
        }

        $return[] = $this->indentation(1) . 'options:';
        $return[] = $this->indentation(2) . 'charset: ' . $this->config['defaultCharacterSetName'];
        $return[] = $this->indentation(2) . 'type: ' . $this->config['tableEngine'];

        // add empty line behind table
        $return[] = '';

        return implode("\n", $return);
    }
}
