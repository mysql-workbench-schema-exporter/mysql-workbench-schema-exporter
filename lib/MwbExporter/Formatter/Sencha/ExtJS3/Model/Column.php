<?php

/*
 * The MIT License
 *
 * Copyright (c) 2012 Allan Sun <sunajia@gmail.com>
 * Copyright (c) 2012-2014 Toha <tohenk@yahoo.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace MwbExporter\Formatter\Sencha\ExtJS3\Model;

use MwbExporter\Model\Column as BaseColumn;
use MwbExporter\Helper\ZendURLFormatter;
use MwbExporter\Formatter\DatatypeConverter;

class Column extends BaseColumn
{
    public function asField()
    {
        return array('name' => $this->getColumnName(), 'type' => $this->getFormatter()->getDatatypeConverter()->getType($this));
    }

    public function asColumn()
    {
        return array('header' => ucwords(str_replace('_', ' ', $this->getColumnName())), 'dataIndex' => $this->getColumnName());
    }

    public function asFormItem()
    {
        $result = array();
        // @see http://docs.sencha.com/ext-js/3-4/#!/api/Ext.form.ComboBox-cfg-hiddenName
        if (count($this->getLocalForeignKeys())) {
            $result['hiddenName'] = $this->getColumnName();
        } else {
            $result['name'] = $this->getColumnName();
        }
        $anchor = null;
        switch (true) {
            case $this->isPrimary():
                $type = 'hidden';
                break;

            case $this->getColumnType() === DatatypeConverter::DATATYPE_DATETIME:
            case $this->getColumnType() === DatatypeConverter::DATATYPE_TIMESTAMP:
                $type = 'xdatetime';
                break;

            case $this->getColumnType() === DatatypeConverter::DATATYPE_TINYTEXT:
            case $this->getColumnType() === DatatypeConverter::DATATYPE_MEDIUMTEXT:
            case $this->getColumnType() === DatatypeConverter::DATATYPE_LONGTEXT:
            case $this->getColumnType() === DatatypeConverter::DATATYPE_TEXT:
                $type = 'htmleditor';
                $anchor = '100%';
                break;

            case count($this->getLocalForeignKeys()):
                $type = 'combo';
                break;

            default:
                $type = 'textfield'; 
        }
        $result['xtype'] = $type;
        $result['fieldLabel'] = ucwords(str_replace('_', ' ', $this->getColumnName()));
        $result['allowBlank'] = $this->isNotNull() ? false : true;
        if ($anchor) {
            $result['anchor'] = $anchor;
        }
        foreach ($this->getLocalForeignKeys() as $local) {
            $result['valueField'] = $local->getForeign()->getColumnName();
            $result['displayField'] = $local->getReferencedTable()->getRawTableName();
            $result['mode'] = 'local';
            $result['forceSelection'] = true;
            $result['triggerAction'] = 'all';
            $result['listeners'] = array('afterrender' => $this->getTable()->getJSObject('function() {this.store.load();}', true, true));
            $result['store'] = $this->getTable()->getJSObject(sprintf('new Ext.data.JsonStore(%s);',
                $this->getTable()->getJSObject(array(
                    'id'     => str_replace(' ', '', ucwords(str_replace('_',' ',$local->getReferencedTable()->getRawTableName()))).'Store',
                    'url'    => ZendURLFormatter::fromUnderscoreConnectionToDashConnection($local->getReferencedTable()->getRawTableName()),
                    'root'   => 'data',
                    'fields' => array('id', 'name'),
                ), true)
            ), false, true);
        }

        return $result;
    }
}