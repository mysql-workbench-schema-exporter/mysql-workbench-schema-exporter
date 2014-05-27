<?php
/*
 * The MIT License
 *
 * Copyright (c) 2010 Johannes Mueller <circus2(at)web.de>
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

namespace MwbExporter\Formatter\Propel1\Yaml\Model;

use MwbExporter\Model\Column as BaseColumn;
use MwbExporter\Writer\WriterInterface;
use MwbExporter\Formatter\Propel1\Yaml\Formatter;

class Column extends BaseColumn
{
    /**
     * List of column names considered as simple column.
     *
     * @var array
     */
    protected $simpleColumns = array('created_at', 'updated_at');

    public function asYAML()
    {
        if ($this->getConfig()->get(Formatter::CFG_GENERATE_SIMPLE_COLUMN) && in_array($this->getColumnName(), $this->simpleColumns)) {
            $attributes = null;
        } else {
            $attributes = array();
            $type = strtolower($this->getFormatter()->getDatatypeConverter()->getType($this));
            $attributes['type'] = $type;
            switch ($type) {
                case 'decimal':
                    $attributes['size'] = $this->parameters->get('precision');
                    $attributes['scale'] = $this->parameters->get('scale');
                    break;
    
                case 'enum':
                    break;
            }
            if ($this->parameters->get('length') > 0) {
                $attributes['size'] = $this->parameters->get('length');
            }
            if ($this->isNotNull()) {
                $attributes['required'] = true;
            }
            if (1 == $this->isPrimary()) {
                $attributes['primaryKey'] = true;
            }
            if ($this->isAutoIncrement()) {
                $attributes['autoIncrement'] = true;
            }
            if (($defaultValue = $this->getDefaultValue()) && !in_array($defaultValue, array('CURRENT_TIMESTAMP'))) {
                $attributes['defaultExpr'] = $defaultValue;
            }
            // simple foreign key
            foreach ($this->foreigns as $foreign) {
                if (count($foreign->getLocals()) > 1) {
                    continue;
                }
                $attributes['foreignTable'] = $foreign->getReferencedTable()->getRawTableName();
                $attributes['foreignReference'] = $foreign->getForeign()->getColumnName();
                if (($action = strtolower($foreign->parameters->get('updateRule'))) !== 'no action') {
                    $attributes['onUpdate'] = $action;
                }
                if (($action = strtolower($foreign->parameters->get('deleteRule'))) !== 'no action') {
                    $attributes['onDelete'] = $action;
                }
                // validate foreign referenced table name for name conflict with
                // table columns
                if ($this->getConfig()->get(Formatter::CFG_VALIDATE_FK_PHP_NAME)) {
                    $columns = array_map('strtolower', $this->getTable()->getColumns()->getColumnNames());
                    if (in_array(strtolower($foreign->getReferencedTable()->getRawTableName()), $columns)) {
                        $attributes['fkPhpName'] = $foreign->getReferencedTable()->getModelName().'FK';
                    }
                }
            }
            // column description
            if ($comment = $this->getComment(false)) {
                $attributes['description'] = $comment;
            }
        }

        return array($this->getColumnName() => $attributes);
    }
}
