<?php

/*
 * The MIT License
 *
 * Copyright (c) 2010 Johannes Mueller <circus2(at)web.de>
 * Copyright (c) 2012-2014 Toha <tohenk@yahoo.com>
 * Copyright (c) 2013 WitteStier <development@wittestier.nl>
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

namespace MwbExporter\Formatter\Doctrine2\ZF2InputFilterAnnotation\Model;

use MwbExporter\Formatter\Doctrine2\Annotation\Model\Table as BaseTable;
use MwbExporter\Formatter\Doctrine2\ZF2InputFilterAnnotation\Formatter;
use MwbExporter\Writer\WriterInterface;

class Table extends BaseTable
{
    protected function getClassImplementations()
    {
        return 'InputFilterAwareInterface';
    }

    protected function getUsedClasses()
    {
        $uses = array(
            'Zend\InputFilter\InputFilter',
            'Zend\InputFilter\Factory as InputFactory',
            'Zend\InputFilter\InputFilterAwareInterface',
            'Zend\InputFilter\InputFilterInterface',
        );

        return array_merge(parent::getUsedClasses(), $uses);
    }

    public function writePreClassHandler(WriterInterface $writer)
    {
        $writer
            ->write('/**')
            ->write(' * Instance of InputFilterInterface.')
            ->write(' *')
            ->write(' * @var InputFilter')
            ->write(' */')
            ->write('private $inputFilter;')
            ->write('')
        ;

        return $this;
    }

    public function writePostClassHandler(WriterInterface $writer)
    {
        $this->writeInputFilter($writer);
        if ($this->getConfig()->get(Formatter::CFG_GENERATE_ENTITY_POPULATE)) {
            $this->writePopulate($writer);
        }
        if ($this->getConfig()->get(Formatter::CFG_GENERATE_ENTITY_GETARRAYCOPY)) {
            $this->writeGetArrayCopy($writer);
        }

        return $this;
    }

    /**
     * Write \Zend\InputFilter\InputFilterInterface methods.
     * http://framework.zend.com/manual/2.1/en/modules/zend.input-filter.intro.html
     * 
     * @param \MwbExporter\Writer\WriterInterface $writer
     * @return \MwbExporter\Formatter\Doctrine2\AnnotationZF2InputFilter\Model\Table
     */
    public function writeInputFilter(WriterInterface $writer)
    {
        $writer
            ->write('/**')
            ->write(' * Not used, Only defined to be compatible with InputFilterAwareInterface.')
            ->write(' * ')
            ->write(' * @param \Zend\InputFilter\InputFilterInterface $inputFilter')
            ->write(' * @throws \Exception')
            ->write(' */')
            ->write('public function setInputFilter(InputFilterInterface $inputFilter)')
            ->write('{')
            ->indent()
                ->write('throw new \Exception("Not used.");')
            ->outdent()
            ->write('}')
            ->write('')
            ->write('/**')
            ->write(' * Return a for this entity configured input filter instance.')
            ->write(' *')
            ->write(' * @return InputFilterInterface')
            ->write(' */')
            ->write('public function getInputFilter()')
            ->write('{')
            ->indent()
                ->write('if ($this->inputFilter instanceof InputFilterInterface) {')
                ->indent()
                    ->write('return $this->inputFilter;')
                ->outdent()
                ->write('}')
                ->write('$factory = new InputFactory();')
                ->write('$filters = array(')
                ->indent()
                    ->writeCallback(function(WriterInterface $writer, Table $_this = null) {
                        $_this->writeInputFilterColumns($writer);
                    })
                ->outdent()
                ->write(');')
                ->write('$this->inputFilter = $factory->createInputFilter($filters);')
                ->write('')
                ->write('return $this->inputFilter;')
            ->outdent()
            ->write('}')
            ->write('')
        ;

        return $this;
    }

    public function writeInputFilterColumns(WriterInterface $writer)
    {
        foreach ($this->getColumns() as $column) {
            $writer
                ->write('array(')
                ->indent()
                    ->write('\'name\' => \'%s\',', $column->getColumnName())
                    ->write('\'required\' => %s,', $column->isNotNull() ? 'true' : 'false')
                    ->write('\'filters\' => array(),')
                    ->write('\'validators\' => array(),')
                ->outdent()
                ->write('),')
            ;
        }

        return $this;
    }

    /**
     * Write entity populate method.
     * 
     * @see \Zend\Stdlib\Hydrator\ArraySerializable::extract()
     * @param \MwbExporter\Writer\WriterInterface $writer
     * @return \MwbExporter\Formatter\Doctrine2\AnnotationZF2InputFilter\Model\Table
     */
    public function writePopulate(WriterInterface $writer)
    {
        $writer
            ->write('/**')
            ->write(' * Populate entity with the given data.')
            ->write(' * The set* method will be used to set the data.')
            ->write(' *')
            ->write(' * @param array $data')
            ->write(' * @return boolean')
            ->write(' */')
            ->write('public function populate(array $data = array())')
            ->write('{')
            ->indent()
                ->write('foreach ($data as $field => $value) {')
                ->indent()
                    ->write('$setter = sprintf(\'set%s\', ucfirst(')
                    ->indent()
                        ->write('str_replace(\' \', \'\', ucwords(str_replace(\'_\', \' \', $field)))')
                    ->outdent()
                    ->write('));')
                    ->write('if (method_exists($this, $setter)) {')
                    ->indent()
                        ->write('$this->{$setter}($value);')
                    ->outdent()
                    ->write('}')
                ->outdent()
                ->write('}')
                ->write('')
                ->write('return true;')
            ->outdent()
            ->write('}')
            ->write('')
        ;

        return $this;
    }

    /**
     * Write getArrayCopy method.
     * 
     * @see \Zend\Stdlib\Hydrator\ArraySerializable::hydrate()
     * @param \MwbExporter\Writer\WriterInterface $writer
     * @return \MwbExporter\Formatter\Doctrine2\AnnotationZF2InputFilter\Model\Table
     */
    public function writeGetArrayCopy(WriterInterface $writer)
    {
        $columns   = $this->getColumns();
        $relations = $this->getTableRelations();

        $writer
            ->write('/**')
            ->write(' * Return a array with all fields and data.')
            ->write(' * Default the relations will be ignored.')
            ->write(' * ')
            ->write(' * @param array $fields')
            ->write(' * @return array')
            ->write(' */')
            ->write('public function getArrayCopy(array $fields = array())')
            ->write('{')
            ->indent()
                ->write('$dataFields = array(%s);', implode(', ', array_map(function($column) {
                    return sprintf('\'%s\'', $column);
                }, $columns->getColumnNames())))
                ->write('$relationFields = array(%s);', implode(', ', array_map(function($relation) {
                    return sprintf('\'%s\'', lcfirst($relation->getReferencedTable()->getModelName()));
                }, $relations)))
                ->write('$copiedFields = array();')
                ->write('foreach ($relationFields as $relationField) {')
                ->indent()
                    ->write('$map = null;')
                    ->write('if (array_key_exists($relationField, $fields)) {')
                    ->indent()
                        ->write('$map = $fields[$relationField];')
                        ->write('$fields[] = $relationField;')
                        ->write('unset($fields[$relationField]);')
                    ->outdent()
                    ->write('}')
                    ->write('if (!in_array($relationField, $fields)) {')
                    ->indent()
                        ->write('continue;')
                    ->outdent()
                    ->write('}')
                    ->write('$getter = sprintf(\'get%s\', ucfirst(str_replace(\' \', \'\', ucwords(str_replace(\'_\', \' \', $relationField)))));')
                    ->write('$relationEntity = $this->{$getter}();')
                    ->write('$copiedFields[$relationField] = (!is_null($map))')
                    ->indent()
                        ->write('? $relationEntity->getArrayCopy($map)')
                        ->write(': $relationEntity->getArrayCopy();')
                    ->outdent()
                    ->write('$fields = array_diff($fields, array($relationField));')
                ->outdent()
                ->write('}')
                ->write('foreach ($dataFields as $dataField) {')
                ->indent()
                    ->write('if (!in_array($dataField, $fields) && !empty($fields)) {')
                    ->indent()
                        ->write('continue;')
                    ->outdent()
                    ->write('}')
                    ->write('$getter = sprintf(\'get%s\', ucfirst(str_replace(\' \', \'\', ucwords(str_replace(\'_\', \' \', $dataField)))));')
                    ->write('$copiedFields[$dataField] = $this->{$getter}();')
                ->outdent()
                ->write('}')
                ->write('')
                ->write('return $copiedFields;')
            ->outdent()
            ->write('}')
            ->write('')
        ;

        return $this;
    }
}
