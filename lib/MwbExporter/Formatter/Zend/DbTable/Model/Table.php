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
        $return = array();

        $return[] = '<?php';
        $return[] = '';
        $return[] = '/**';
        $return[] = ' * ';
        $return[] = ' */';
        $return[] = 'class ' . $this->tablePrefix . $this->getModelName() . ' extends ' . $this->parentTable;
        $return[] = '{';
        $return[] = '    /* @var string $_shema */';
        $return[] = '    protected $_shema           = \''. $this->getSchemaName() .'\';';
        $return[] = '';
        $return[] = '    /* @var string $_name */';
        $return[] = '    protected $_name            = \''. $this->getRawTableName() .'\';';
        $return[] = '';
        
        $return[] = $this->displayDependances();
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
    protected function displayDependances()
    {
        //TODO: Find a way to print dependance without change the core.
        $return = array();
        
//        $dependentTables = $this->getRelationToTable('users');
//        var_dump($this->getRawTableName());
//        var_dump(count($dependentTables));
//        var_dump($dependentTables);
        
        
        $return[] = '    /* @var array $_dependentTables */';
        $return[] = '    protected $_dependentTables = array();';
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
        
        $return[] = '    /* @var array $_referenceMap */';
        
        if (count($this->getForeignKeys()) > 0) {
            $return[] = '    protected $_referenceMap    = array(';
       
            foreach($this->getForeignKeys() as $foreignKey){
                $return[] = $foreignKey->display();
                $return[] = '            ),';
            }

            $return[] = '        );';
        } else {
            $return[] = '    protected $_referenceMap    = array();';
        }
        
        $return[] = '';
        return implode("\n", $return);
    }
}