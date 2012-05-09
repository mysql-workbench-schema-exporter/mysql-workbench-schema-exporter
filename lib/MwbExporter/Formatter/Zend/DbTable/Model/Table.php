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

namespace MwbExporter\Formatter\Zend\DbTable\Model;

use MwbExporter\Core\Registry;
use MwbExporter\Core\Model\Table as Base;

class Table extends Base
{
    /**
     * @var array
     */
    protected $manyToManyRelations = array();

    /**
     * @var string
     */
    protected $tablePrefix = '';

    /**
     *
     * @param SimpleXMLElement $data
     * @param type $parent
     */
    public function __construct($data, $parent)
    {
        parent::__construct($data, $parent);
        $config = Registry::get('config');
        $this->tablePrefix = str_replace(
            array('%schema%', '%table%', '%entity%'),
            array($this->getSchemaName(), $this->getRawTableName(), $this->getModelName()),
            $config['tablePrefix']
        );
        $this->parentTable = str_replace(
            array('%schema%', '%table%', '%entity%'),
            array($this->getSchemaName(), $this->getRawTableName(), $this->getModelName()),
            $config['parentTable']
        );
    }

    /**
     *
     * @return string
     */
    public function display()
    {
        $config = Registry::get('config');

        $return = array();

        $return[] = '<?php';
        $return[] = '';
        $return[] = '/**';
        $return[] = ' * ';
        $return[] = ' */';

        /* FIXME: [Zend] Table name is one time in singular form, one time in plural form.
         *        All table occurence need to be at the original form.
         *
         *        $this->getModelName() return singular form with correct camel case
         *        $this->getRawTableName() return original form with no camel case
         */
        $return[] = 'class ' . $this->getTableClassName() . ' extends ' . $this->parentTable;
        $return[] = '{';
        
        if (true === $config['generateSchema']) {
            $return[] = $this->indentation(1) .'/* @var string $_schema */';
            $return[] = $this->indentation(1) .'protected $_schema          = \''. $this->getSchemaName() .'\';';
            $return[] = '';
        }

        if (true === $config['generateName']) {
            $return[] = $this->indentation(1) .'/* @var string $_name */';
            $return[] = $this->indentation(1) .'protected $_name            = \''. $this->getRawTableName() .'\';';
            $return[] = '';
        }

        if (true === $config['generatePrimary']){
            $return[] = $this->displayPrimary();
            $return[] = '';
        }

        if (true === $config['generateDRI']) {
            $return[] = $this->displayDependencies();
            $return[] = '';
        }
        
        if (true === $config['generateReferences']) {
            $return[] = $this->displayReferences();
            $return[] = '';
        }   

        $return[] = '}';
        return implode("\n", $return);
    }

    /**
     *
     * @param array $rel
     */
    public function setManyToManyRelation(array $rel)
    {
        $key = $rel['refTable']->getModelName();
        $this->manyToManyRelations[$key] = $rel;
    }

    /**
     *
     * @return string
     */
    protected function displayDependencies()
    {
        //TODO: [Zend] Find a way to print dependance without change the core.
        $return = array();

//        $dependentTables = $this->getRelationToTable('users');
//        var_dump($this->getRawTableName());
//        var_dump(count($dependentTables));
//        var_dump($dependentTables);

        $return[] = $this->indentation(1) .'/* Note: this feature isn\'t implement yet */';

        $return[] = $this->indentation(1) .'/* @var array $_dependentTables */';
        $return[] = $this->indentation(1) .'protected $_dependentTables = array();';

        return implode("\n", $return);
    }

    /**
     *
     * @return string
     */
    protected function displayReferences()
    {
        $return = array();

        $return[] = $this->indentation(1) .'/* @var array $_referenceMap */';

        if (count($this->getForeignKeys()) > 0) {
            $return[] = $this->indentation(1) .'protected $_referenceMap    = array(';

            foreach($this->getForeignKeys() as $foreignKey){
                $return[] = $foreignKey->display();
            }

            $return[] = $this->indentation(1) .');';
        } else {
            $return[] = $this->indentation(1) .'protected $_referenceMap    = array();';
        }
        return implode("\n", $return);
    }

    /**
     *
     * @return string
     */
    protected function displayPrimary()
    {
        $return = array();
        $return[] = $this->indentation(1) .'/* @var array $_primary */';
        $return[] = $this->indentation(1) .'protected $_primary    = array('.$this->getColumns()->displayPrimary().');';

        return implode("\n", $return);
    }

    public function getTableClassName()
    {
        return $this->tablePrefix . $this->getSchemaName() .'_'. $this->getModelName();
    }
}