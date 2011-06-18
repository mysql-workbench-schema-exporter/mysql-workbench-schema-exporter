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

class Table extends \MwbExporter\Core\Model\Table
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
        $config = \MwbExporter\Core\Registry::get('config');
        $this->tablePrefix = $config['tablePrefix'];
        $this->parentTable = $config['parentTable'];
        
        parent::__construct($data, $parent);
    }

    
    
    /**
     *
     * @return string 
     */
    public function display()
    {   
        $config = \MwbExporter\Core\Registry::get('config');
        
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
        $return[] = 'class ' . $this->tablePrefix . $this->getSchemaName() .'_'. $this->getModelName() . ' extends ' . $this->parentTable;
        $return[] = '{';
        $return[] = $this->indentation(1) .'/* @var string $_schema */';
        $return[] = $this->indentation(1) .'protected $_schema          = \''. $this->getSchemaName() .'\';';
        $return[] = '';
        $return[] = $this->indentation(1) .'/* @var string $_name */';
        $return[] = $this->indentation(1) .'protected $_name            = \''. $this->getRawTableName() .'\';';
        $return[] = '';
        
        
        if (true === $config['generateDRI']) {
            $return[] = $this->displayDependencies();
        }
        
        $return[] = $this->displayReferences();
        
        $return[] = '';
        $return[] = '}';
        $return[] = '?>';
        $return[] = '';
        $return[] = '';
        
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