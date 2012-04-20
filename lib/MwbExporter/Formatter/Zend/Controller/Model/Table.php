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

namespace MwbExporter\Formatter\Zend\Controller\Model;

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
        parent::__construct($data, $parent);
        $config = \MwbExporter\Core\Registry::get('config');
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
        $config = \MwbExporter\Core\Registry::get('config');

        $return = array();

        $return[] = '<?php';
        $return[] = '';
        $return[] = '/**';
        $return[] = ' * ';
        $return[] = ' */';

        $return[] = 'class ' . $this->tablePrefix . $this->getModelName() . 'Controller extends ' . $this->parentTable;
        $return[] = '{';
        $return[] = '';
        $return[] = '}';
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
}