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

use MwbExporter\Model\Table as BaseTable;
use MwbExporter\Writer\WriterInterface;
use MwbExporter\Formatter\Propel1\Yaml\Formatter;
use Symfony\Component\Yaml\Yaml;

class Table extends BaseTable
{
    public function asYAML()
    {
        $data = array(
            'tableName' => $this->getRawTableName(),
        );
        if ($package = $this->parseComment('package')) {
            $basePackage = $this->getConfig()->get(Formatter::CFG_PACKAGE);
            $data['package'] = ($basePackage ? $basePackage.'.' : '').$package;
        }
        if ('true' == trim($this->parseComment('allowPkInsert'))) {
            $data['allowPkInsert'] = true;
        }
        $columns = array();
        foreach ($this->getColumns() as $column) {
            if (!count($attributes = $column->asYAML())) {
                continue;
            }
            $columns = array_merge($columns, $attributes);
        }
        if (count($columns)) {
            $data['columns'] = $columns;
        }
        $indexes = array();
        foreach ($this->getIndices() as $index) {
            if (!$index->isIndex()) {
                continue;
            }
            if (!count($attributes = $index->asYAML())) {
                continue;
            }
            $indexes = array_merge($indexes, $attributes);
        }
        if (count($indexes)) {
            $data['indexes'] = $indexes;
        }
        $uniques = array();
        foreach ($this->getIndices() as $index) {
            if (!$index->isUnique()) {
                continue;
            }
            if (!count($attributes = $index->asYAML())) {
                continue;
            }
            $uniques = array_merge($uniques, $attributes);
        }
        if (count($uniques)) {
            $data['uniques'] = $uniques;
        }
        $foreignKeys = array();
        foreach ($this->foreignKeys as $foreign) {
            if (count($locals = $foreign->getLocals()) == 1) {
                continue;
            }
            $attributes = array('foreignTable' => $foreign->getReferencedTable()->getRawTableName());
            if (($action = strtolower($foreign->getParameters()->get('updateRule'))) !== 'no action') {
                $attributes['onUpdate'] = $action;
            }
            if (($action = strtolower($foreign->getParameters()->get('deleteRule'))) !== 'no action') {
                $attributes['onDelete'] = $action;
            }
            $references = array();
            $foreigns = $foreign->getForeigns();
            for ($i = 0; $i < count($locals); $i++) {
                $lcol = $locals[$i];
                $fcol = $foreigns[$i];
                $references[] = array('local' => $lcol->getColumnName(), 'foreign' => $fcol->getColumnName());
            }
            $attributes['references'] = $references;
            $foreignKeys = array_merge($foreignKeys, array($foreign->getParameters()->get('name') => $attributes));
        }
        if (count($foreignKeys)) {
            $data['foreignKeys'] = $foreignKeys;
        }
        foreach (array('propel_behaviors', 'behaviors') as $key) {
            if ($behavior = trim($this->parseComment($key))) {
                try {
                    $behavior = Yaml::parse($behavior);
                    $data[$key] = $behavior;
                } catch (\Exception $e) {
                    $this->getDocument()->addLog(sprintf('  Skip %s: %s.', $key, $e->getMessage()));
                }
            }
        }

        return array($this->getModelName() => $data);
    }
}
