<?php

/*
 * The MIT License
 *
 * Copyright (c) 2010 Johannes Mueller <circus2(at)web.de>
 * Copyright (c) 2012 Toha <tohenk@yahoo.com>
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

namespace MwbExporter\Formatter\Doctrine2\AnnotationZF2InputFilter\Model;

use MwbExporter\Formatter\Doctrine2\Annotation\Model\Table as BaseTable;
use MwbExporter\Writer\WriterInterface;
use MwbExporter\Formatter\Doctrine2\AnnotationZF2InputFilter\Formatter;

class Table
    extends BaseTable
{

    public function writeTable(WriterInterface $writer)
    {
        if (!$this->isExternal()) {
            $namespace = $this->getEntityNamespace();
            if ($repositoryNamespace = $this->getDocument()->getConfig()->get(Formatter::CFG_REPOSITORY_NAMESPACE)) {
                $repositoryNamespace .= '\\';
            }
            $skipGetterAndSetter = $this->getDocument()->getConfig()->get(Formatter::CFG_SKIP_GETTER_SETTER);
            $serializableEntity = $this->getDocument()->getConfig()->get(Formatter::CFG_GENERATE_ENTITY_SERIALIZATION);
            $lifecycleCallbacks = $this->getLifecycleCallbacks();

            $generatePopulate = $this->getDocument()->getConfig()->get(Formatter::CFG_GENERATE_ENTITY_POPULATE);
            $generateGetArrayCopy = $this->getDocument()->getConfig()->get(Formatter::CFG_GENERATE_ENTITY_GETARRAYCOPY);

            $comment = $this->getComment();
            $writer
                ->open($this->getTableFileName())
                ->write('<?php')
                ->write('')
                ->write('namespace %s;', $namespace)
                ->write('')
                ->writeCallback(function(WriterInterface $writer, Table $_this = null) {
                        $_this->writeUsedClasses($writer);
                    })
                ->write('/**')
                ->write(' * ' . $this->getNamespace(null, false))
                ->write(' *')
                ->writeIf($comment, $comment)
                ->write(' * ' . $this->getAnnotation('Entity', array('repositoryClass' => $this->getDocument()->getConfig()->get(Formatter::CFG_AUTOMATIC_REPOSITORY)
                            ? $repositoryNamespace . $this->getModelName() . 'Repository'
                            : null)))
                ->write(' * ' . $this->getAnnotation('Table', array('name' => $this->quoteIdentifier($this->getRawTableName()), 'indexes' => $this->getIndexesAnnotation(), 'uniqueConstraints' => $this->getUniqueConstraintsAnnotation())))
                ->writeIf($lifecycleCallbacks, ' * @HasLifecycleCallbacks')
                ->write(' */')
                ->write('class ' . $this->getModelName())
                ->indent()
                ->write('implements InputFilterAwareInterface')
                ->outdent()
                ->write('{')
                ->indent()
                ->writeCallback(function(WriterInterface $writer, Table $_this = null) use ($skipGetterAndSetter, $serializableEntity, $lifecycleCallbacks, $generatePopulate, $generateGetArrayCopy) {
                        $_this->getColumns()->write($writer);
                        $_this->writeManyToMany($writer);

                        $writer
                        ->write('/**')
                        ->write(' * Instance of InputFilterInterface.')
                        ->write(' *')
                        ->write(' * @var InputFilter')
                        ->write(' */')
                        ->write('private $inputFilter;')
                        ->write('');

                        $_this->writeConstructor($writer);
                        if (!$skipGetterAndSetter) {
                            $_this->getColumns()->writeGetterAndSetter($writer);
                            $_this->writeManyToManyGetterAndSetter($writer);
                        }

                        foreach ($lifecycleCallbacks as $callback => $handlers) {
                            foreach ($handlers as $handler) {
                                $writer
                                ->write('/**')
                                ->write(' * @%s', ucfirst($callback))
                                ->write(' */')
                                ->write('public function %s()', $handler)
                                ->write('{')
                                ->write('}')
                                ->write('')
                                ;
                            }
                        }

                        $_this->writeInputFilter($writer);

                        if ($generatePopulate) {
                            $_this->writePopulate($writer);
                        }

                        if ($generateGetArrayCopy) {
                            $_this->writeGetArrayCopy($writer);
                        }

                        if ($serializableEntity) {
                            $_this->writeSerialization($writer);
                        }
                    })
                ->outdent()
                ->write('}')
                ->close()
            ;

            return self::WRITE_OK;
        }

        return self::WRITE_EXTERNAL;
    }

    /**
     * Write entity used classes.
     * 
     * @param \MwbExporter\Writer\WriterInterface $writer
     * @return \MwbExporter\Formatter\Doctrine2\AnnotationZF2InputFilter\Model\Table
     */
    public function writeUsedClasses(WriterInterface $writer)
    {
        parent::writeUsedClasses($writer);

        $writer->write('use Zend\InputFilter\InputFilter,')
            ->indent()
            ->write('Zend\InputFilter\Factory as InputFactory,')
            ->write('Zend\InputFilter\InputFilterAwareInterface,')
            ->write('Zend\InputFilter\InputFilterInterface;')
            ->outdent();


//        $count = 0;
//        if ('@ORM\\' === $this->addPrefix()) {
//            $writer->write('use Doctrine\ORM\Mapping as ORM;');
//            $count++;
//        }
//        if (count($this->getManyToManyRelations()) || $this->getColumns()->hasOneToManyRelation()) {
//            $writer->write('use %s;', $this->getCollectionClass());
//            $count++;
//        }
//
//        if ($count) {
//            $writer->write('');
//        }

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
        $columns = $this->getColumns()->getColumns();

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
            ->write('// End.')
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
            ->write('// End.')
            ->write('return $this->inputFilter;')
            ->outdent()
            ->write('}')
            ->write('$factory = new InputFactory();')
            ->write('$filters = array(')
            ->indent();

        foreach ($columns as $i => $column) {
            $writer
                ->write('array(')
                ->indent()
                ->write('\'name\' => \'%s\',', $column->getColumnName())
                ->write('\'required\' => %s,', $column->getIsrequired()
                        ? 'true'
                        : 'false')
                ->write('\'filters\' => array(),')
                ->write('\'validators\' => array(),')
                ->outdent()
                ->write('),');
        }

        $writer
            ->outdent()
            ->write(');')
            ->write('$this->inputFilter = $factory->createInputFilter($filters);')
            ->write('// End.')
            ->write('return $this->inputFilter;')
            ->outdent()
            ->write('}')
            ->write('');

        // End.
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
            ->write('// End.')
            ->write('return true;')
            ->outdent()
            ->write('}')
            ->write('');
        // End.
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
        $columns = $this->getColumns()->getColumns();
        $relations = $this->getRelations();

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
                            return sprintf('\'%s\'', $column->getColumnName());
                        }, $columns)))
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
            ->write('// End.')
            ->write('return $copiedFields;')
            ->outdent()
            ->write('}')
            ->write('');

        // End.
        return $this;
    }

}
