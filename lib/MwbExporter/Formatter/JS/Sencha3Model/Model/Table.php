<?php
/*
 *  The MIT License
 *
 *  Copyright (c) 2012 Allan Sun <sunajia@gmail.com>
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

namespace MwbExporter\Formatter\JS\Sencha3Model\Model;

class Table extends \MwbExporter\Core\Model\Table
{
    /**
     * @var string
     */
    protected $parentClass = '';


    /**
     * @var string
     */
    protected $classPrefix = '';



    /**
     *
     * @param SimpleXMLElement $data
     * @param type $parent
     */
    public function __construct($data, $parent)
    {
        parent::__construct($data, $parent);
        $config = \MwbExporter\Core\Registry::get('config');
        $this->classPrefix = str_replace(
            array('%schema%', '%table%', '%entity%'),
            array($this->getSchemaName(), $this->getRawTableName(), $this->getModelName()),
            $config['classPrefix']
        );
        $this->parentClass = str_replace(
            array('%schema%', '%table%', '%entity%'),
            array($this->getSchemaName(), $this->getRawTableName(), $this->getModelName()),
            $config['parentClass']
        );
    }



    /**
     *
     * @return string
     */
    public function display()
    {
        $config = \MwbExporter\Core\Registry::get('config');

        $return = array();

        //Model
        $return[] = $this->classPrefix . '.'. $this->getModelName() . ' = Ext.extend(' . $this->parentClass . ',{';
        $return[] = $this->indentation(1) .'id:\''. $this->getModelName() .'\',';
        $return[] = $this->indentation(1) .'url:\'/'. \MwbExporter\Helper\ZendURLFormatter::fromCamelCaseToDashConnection($this->getModelName()) .'\',';
        $return[] = $this->indentation(1) .'title:\''. str_replace('-', ' ',\MwbExporter\Helper\ZendURLFormatter::fromCamelCaseToDashConnection($this->getModelName()) ).'\',';
        $return[] = $this->getColumns()->displayFields(1);
        $return[] = '});';
        $return[] = '';

        //UI
        $return[] = $this->classPrefix . '.'. $this->getModelName() . ' = Ext.extend(' . $this->classPrefix . '.'. $this->getModelName() . ',{';
        $return[] = $this->getColumns()->displayColumns(1).",";
        $return[] = $this->getColumns()->displayFormItems(1);
        $return[] = '});';
        $return[] = '';

        return implode("\n", $return);
    }


    /**
     *
     * @param int $in Default number of indentation
     */
    protected function displayFields()
    {
      $return = array();

      $return[] = 'fields:[';

//      echo $this->getColumns()->display(); die();

      foreach($this->getColumns() AS $column){
        $return[] = $this->indentation(1) . "{name:'".$column->getColumnName()."},";
      }
      $return[count($return)] = substr($return[count($return)], 0, strlen($return[count($return)])-1); // Remove the last comma from the loop above

      $return[] = ']';
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
        $return[] = '';

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

            $return[] = $this->indentation(2) .');';
        } else {
            $return[] = $this->indentation(1) .'protected $_referenceMap    = array();';
        }

        $return[] = '';
        return implode("\n", $return);
    }
}