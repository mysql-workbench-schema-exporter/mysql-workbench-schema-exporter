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

use MwbExporter\Model\Column as BaseColumn;
use MwbExporter\Helper\Pluralizer;
use MwbExporter\Writer\WriterInterface;

class Column
    extends BaseColumn
{

    /**
     * COMMENTME
     * 
     * @return boolean
     */
    public function asAnnotation()
    {
        $attributes = array(
            'name' => ($columnName = $this->getTable()->quoteIdentifier($this->getColumnName())) !== $this->getColumnName()
                ? $columnName
                : null,
            'type' => $this->getDocument()->getFormatter()->getDatatypeConverter()->getMappedType($this),
        );
        if (($length = $this->parameters->get('length')) && ($length != -1)) {
            $attributes['length'] = (int) $length;
        }
        if (($precision = $this->parameters->get('precision')) && ($precision != -1) && ($scale = $this->parameters->get('scale')) && ($scale != -1)) {
            $attributes['precision'] = (int) $precision;
            $attributes['scale'] = (int) $scale;
        }
        if ($this->parameters->get('isNotNull') != 1) {
            $attributes['nullable'] = true;
        }

        return $attributes;
    }

    /**
     * COMMENTME
     * 
     * @param \MwbExporter\Writer\WriterInterface $writer
     * @return \MwbExporter\Formatter\Doctrine2\AnnotationZF2InputFilter\Model\Column
     */
    public function write(WriterInterface $writer)
    {
        $comment = $this->getComment();
        $writer
            ->write('/**')
            ->writeIf($comment, $comment)
            ->writeIf($this->isPrimary, ' * ' . $this->getTable()->getAnnotation('Id'))
            ->write(' * ' . $this->getTable()->getAnnotation('Column', $this->asAnnotation()))
            ->writeIf($this->parameters->get('autoIncrement') == 1, ' * ' . $this->getTable()->getAnnotation('GeneratedValue', array('strategy' => 'AUTO')))
            ->write(' */')
            ->write('protected $' . $this->getColumnName() . ';')
            ->write('')
        ;

        return $this;
    }

    /**
     * COMMENTME
     * 
     * @param \MwbExporter\Writer\WriterInterface $writer
     * @return \MwbExporter\Formatter\Doctrine2\AnnotationZF2InputFilter\Model\Column
     */
    public function writeArrayCollection(WriterInterface $writer)
    {
        foreach ($this->foreigns as $foreign) {
            if ($foreign->getForeign()->getTable()->isManyToMany()) {
                // do not create entities for many2many tables
                continue;
            }

            if ($foreign->isManyToOne() && $foreign->parseComment('unidirectional') !== 'true') { // is ManyToOne
                $related = $this->getRelatedName($foreign);
                $writer->write('$this->%s = new %s();', lcfirst(Pluralizer::pluralize($foreign->getOwningTable()->getModelName())) . $related, $this->getTable()->getCollectionClass(false));
            }
        }

        return $this;
    }

    /**
     * COMMENTME
     * 
     * @param \MwbExporter\Writer\WriterInterface $writer
     * @return \MwbExporter\Formatter\Doctrine2\AnnotationZF2InputFilter\Model\Column
     */
    public function writeRelations(WriterInterface $writer)
    {
        // one to many references
        foreach ($this->foreigns as $foreign) {
            if ($foreign->getForeign()->getTable()->isManyToMany()) {
                // do not create entities for many2many tables
                continue;
            }
            if ($foreign->parseComment('unidirectional') === 'true') {
                // do not output mapping in foreign table when the unidirectional option is set
                continue;
            }

            $targetEntity = $foreign->getOwningTable()->getModelName();
            $mappedBy = $foreign->getReferencedTable()->getModelName();

            $annotationOptions = array(
                'targetEntity' => $targetEntity,
                'mappedBy' => lcfirst($mappedBy),
                'cascade' => $this->getCascadeOption($foreign->parseComment('cascade')),
                'fetch' => $this->getFetchOption($foreign->parseComment('fetch')),
                'orphanRemoval' => $this->getBooleanOption($foreign->parseComment('orphanRemoval')),
            );

            $joinColumnAnnotationOptions = array(
                'name' => $foreign->getForeign()->getColumnName(),
                'referencedColumnName' => $foreign->getLocal()->getColumnName(),
                'onDelete' => $this->getDeleteRule($foreign->getLocal()->getParameters()->get('deleteRule')),
                'nullable' => !$foreign->getForeign()->getParameters()->get('isNotNull')
                    ? null
                    : false,
            );

            //check for OneToOne or OneToMany relationship
            if ($foreign->isManyToOne()) { // is OneToMany
                $related = $this->getRelatedName($foreign);
                $writer
                    ->write('/**')
                    ->write(' * ' . $this->getTable()->getAnnotation('OneToMany', $annotationOptions))
                    ->write(' * ' . $this->getTable()->getAnnotation('JoinColumn', $joinColumnAnnotationOptions))
                    ->write(' */')
                    ->write('protected $' . lcfirst(Pluralizer::pluralize($targetEntity)) . $related . ';')
                    ->write('')
                ;
            } else { // is OneToOne
                $writer
                    ->write('/**')
                    ->write(' * ' . $this->getTable()->getAnnotation('OneToOne', $annotationOptions))
                    ->write(' * ' . $this->getTable()->getAnnotation('JoinColumn', $joinColumnAnnotationOptions))
                    ->write(' */')
                    ->write('protected $' . lcfirst($targetEntity) . ';')
                    ->write('')
                ;
            }
        }
        // many to references
        if (null !== $this->local) {
            $targetEntity = $this->local->getReferencedTable()->getModelName();
            $inversedBy = $this->local->getOwningTable()->getModelName();

            $annotationOptions = array(
                'targetEntity' => $targetEntity,
                'mappedBy' => null,
                'inversedBy' => $inversedBy,
                'cascade' => $this->getCascadeOption($this->local->parseComment('cascade')),
                'fetch' => $this->getFetchOption($this->local->parseComment('fetch')),
                'orphanRemoval' => $this->getBooleanOption($this->local->parseComment('orphanRemoval')),
            );
            $joinColumnAnnotationOptions = array(
                'name' => $this->local->getForeign()->getColumnName(),
                'referencedColumnName' => $this->local->getLocal()->getColumnName(),
                'onDelete' => $this->getDeleteRule($this->local->getParameters()->get('deleteRule')),
                'nullable' => !$this->local->getForeign()->getParameters()->get('isNotNull')
                    ? null
                    : false,
            );

            //check for OneToOne or ManyToOne relationship
            if ($this->local->isManyToOne()) { // is ManyToOne
                $related = $this->getManyToManyRelatedName($this->local->getReferencedTable()->getRawTableName(), $this->local->getForeign()->getColumnName());
                $refRelated = $this->local->getLocal()->getRelatedName($this->local);
                if ($this->local->parseComment('unidirectional') === 'true') {
                    $annotationOptions['inversedBy'] = null;
                } else {
                    $annotationOptions['inversedBy'] = lcfirst(Pluralizer::pluralize($annotationOptions['inversedBy'])) . $refRelated;
                }
                $writer
                    ->write('/**')
                    ->write(' * ' . $this->getTable()->getAnnotation('ManyToOne', $annotationOptions))
                    ->write(' * ' . $this->getTable()->getAnnotation('JoinColumn', $joinColumnAnnotationOptions))
                    ->write(' */')
                    ->write('protected $' . lcfirst($targetEntity) . $related . ';')
                    ->write('')
                ;
            } else { // is OneToOne
                if ($this->local->parseComment('unidirectional') === 'true') {
                    $annotationOptions['inversedBy'] = null;
                } else {
                    $annotationOptions['inversedBy'] = lcfirst($annotationOptions['inversedBy']);
                }
                $annotationOptions['cascade'] = $this->getCascadeOption($this->local->parseComment('cascade'));

                $writer
                    ->write('/**')
                    ->write(' * ' . $this->getTable()->getAnnotation('OneToOne', $annotationOptions))
                    ->write(' * ' . $this->getTable()->getAnnotation('JoinColumn', $joinColumnAnnotationOptions))
                    ->write(' */')
                    ->write('protected $' . lcfirst($targetEntity) . ';')
                    ->write('')
                ;
            }
        }

        return $this;
    }

    /**
     * COMMENTME
     * 
     * @param \MwbExporter\Writer\WriterInterface $writer
     * @return \MwbExporter\Formatter\Doctrine2\AnnotationZF2InputFilter\Model\Column
     */
    public function writeGetterAndSetter(WriterInterface $writer)
    {
        $table = $this->getTable();
        $converter = $this->getDocument()->getFormatter()->getDatatypeConverter();
        $nativeType = $converter->getNativeType($converter->getMappedType($this));
        $writer
            // setter
            ->write('/**')
            ->write(' * Set the value of ' . $this->getColumnName() . '.')
            ->write(' *')
            ->write(' * @param ' . $nativeType . ' $' . $this->getColumnName())
            ->write(' * @return ' . $table->getNamespace())
            ->write(' */')
            ->write('public function set' . $this->columnNameBeautifier($this->getColumnName()) . '($' . $this->getColumnName() . ')')
            ->write('{')
            ->indent()
            ->write('$this->' . $this->getColumnName() . ' = $' . $this->getColumnName() . ';')
            ->write('')
            ->write('return $this;')
            ->outdent()
            ->write('}')
            ->write('')
            // getter
            ->write('/**')
            ->write(' * Get the value of ' . $this->getColumnName() . '.')
            ->write(' *')
            ->write(' * @return ' . $nativeType)
            ->write(' */')
            ->write('public function get' . $this->columnNameBeautifier($this->getColumnName()) . '()')
            ->write('{')
            ->indent()
            ->write('return $this->' . $this->getColumnName() . ';')
            ->outdent()
            ->write('}')
            ->write('')
        ;

        return $this;
    }

    /**
     * COMMENTME
     * 
     * @param \MwbExporter\Writer\WriterInterface $writer
     * @return \MwbExporter\Formatter\Doctrine2\AnnotationZF2InputFilter\Model\Column
     */
    public function writeRelationsGetterAndSetter(WriterInterface $writer)
    {
        $table = $this->getTable();
        // one to many references
        foreach ($this->foreigns as $foreign) {
            if ($foreign->getForeign()->getTable()->isManyToMany()) {
                // do not create entities for many2many tables
                continue;
            }
            if ($foreign->parseComment('unidirectional') === 'true') {
                // do not output mapping in foreign table when the unidirectional option is set
                continue;
            }

            if ($foreign->isManyToOne()) { // is ManyToOne
                $related = $this->getRelatedName($foreign);
                $related_text = $this->getRelatedName($foreign, false);
                $writer
                    // setter
                    ->write('/**')
                    ->write(' * Add ' . trim($foreign->getOwningTable()->getModelName() . ' ' . $related_text) . ' entity to collection (one to many).')
                    ->write(' *')
                    ->write(' * @param ' . $foreign->getOwningTable()->getNamespace() . ' $' . lcfirst($foreign->getOwningTable()->getModelName()))
                    ->write(' * @return ' . $table->getNamespace())
                    ->write(' */')
                    ->write('public function add' . $this->columnNameBeautifier($foreign->getOwningTable()->getModelName()) . $related . '(' . $foreign->getOwningTable()->getModelName() . ' $' . lcfirst($foreign->getOwningTable()->getModelName()) . ')')
                    ->write('{')
                    ->indent()
                    ->write('$this->' . lcfirst(Pluralizer::pluralize($foreign->getOwningTable()->getModelName())) . $related . '[] = $' . lcfirst($foreign->getOwningTable()->getModelName()) . ';')
                    ->write('')
                    ->write('return $this;')
                    ->outdent()
                    ->write('}')
                    ->write('')
                    // getter
                    ->write('/**')
                    ->write(' * Get ' . trim($foreign->getOwningTable()->getModelName() . ' ' . $related_text) . ' entity collection (one to many).')
                    ->write(' *')
                    ->write(' * @return ' . $table->getCollectionInterface())
                    ->write(' */')
                    ->write('public function get' . $this->columnNameBeautifier(Pluralizer::pluralize($foreign->getOwningTable()->getModelName())) . $related . '()')
                    ->write('{')
                    ->indent()
                    ->write('return $this->' . lcfirst(Pluralizer::pluralize($foreign->getOwningTable()->getModelName())) . $related . ';')
                    ->outdent()
                    ->write('}')
                ;
            } else { // OneToOne
                $writer
                    // setter
                    ->write('/**')
                    ->write(' * Set ' . $foreign->getOwningTable()->getModelName() . ' entity (one to one).')
                    ->write(' *')
                    ->write(' * @param ' . $foreign->getOwningTable()->getNamespace() . ' $' . lcfirst($foreign->getOwningTable()->getModelName()))
                    ->write(' * @return ' . $table->getNamespace())
                    ->write(' */')
                    ->write('public function set' . $this->columnNameBeautifier($foreign->getOwningTable()->getModelName()) . '(' . $foreign->getOwningTable()->getModelName() . ' $' . lcfirst($foreign->getOwningTable()->getModelName()) . ')')
                    ->write('{')
                    ->indent()
                    ->write('$this->' . lcfirst($foreign->getOwningTable()->getModelName()) . ' = $' . lcfirst($foreign->getOwningTable()->getModelName()) . ';')
                    ->write('')
                    ->write('return $this;')
                    ->outdent()
                    ->write('}')
                    ->write('')
                    // getter
                    ->write('/**')
                    ->write(' * Get ' . $foreign->getOwningTable()->getModelName() . ' entity (one to one).')
                    ->write(' *')
                    ->write(' * @return ' . $foreign->getOwningTable()->getNamespace())
                    ->write(' */')
                    ->write('public function get' . $this->columnNameBeautifier($foreign->getOwningTable()->getModelName()) . '()')
                    ->write('{')
                    ->indent()
                    ->write('return $this->' . lcfirst($foreign->getOwningTable()->getModelName()) . ';')
                    ->outdent()
                    ->write('}')
                ;
            }
            $writer
                ->write('')
            ;
        }
        // many to one references
        if (null !== $this->local) {
            $unidirectional = ($this->local->parseComment('unidirectional') === 'true');

            if ($this->local->isManyToOne()) { // is ManyToOne
                $related = $this->getManyToManyRelatedName($this->local->getReferencedTable()->getRawTableName(), $this->local->getForeign()->getColumnName());
                $related_text = $this->getManyToManyRelatedName($this->local->getReferencedTable()->getRawTableName(), $this->local->getForeign()->getColumnName(), false);
                $writer
                    // setter
                    ->write('/**')
                    ->write(' * Set ' . trim($this->local->getReferencedTable()->getModelName() . ' ' . $related_text) . ' entity (many to one).')
                    ->write(' *')
                    ->write(' * @param ' . $this->local->getReferencedTable()->getNamespace() . ' $' . lcfirst($this->local->getReferencedTable()->getModelName()))
                    ->write(' * @return ' . $table->getNamespace())
                    ->write(' */')
                    ->write('public function set' . $this->columnNameBeautifier($this->local->getReferencedTable()->getModelName()) . $related . '(' . $this->local->getReferencedTable()->getModelName() . ' $' . lcfirst($this->local->getReferencedTable()->getModelName()) . ' = null)')
                    ->write('{')
                    ->indent()
                    ->write('$this->' . lcfirst($this->local->getReferencedTable()->getModelName()) . $related . ' = $' . lcfirst($this->local->getReferencedTable()->getModelName()) . ';')
                    ->write('')
                    ->write('return $this;')
                    ->outdent()
                    ->write('}')
                    ->write('')
                    // getter
                    ->write('/**')
                    ->write(' * Get ' . trim($this->local->getReferencedTable()->getModelName() . ' ' . $related_text) . ' entity (many to one).')
                    ->write(' *')
                    ->write(' * @return ' . $this->local->getReferencedTable()->getNamespace())
                    ->write(' */')
                    ->write('public function get' . $this->columnNameBeautifier($this->local->getReferencedTable()->getModelName()) . $related . '()')
                    ->write('{')
                    ->indent()
                    ->write('return $this->' . lcfirst($this->local->getReferencedTable()->getModelName()) . $related . ';')
                    ->outdent()
                    ->write('}')
                    ->write('')
                ;
            } else { // OneToOne
                $writer
                    // setter
                    ->write('/**')
                    ->write(' * Set ' . $this->local->getReferencedTable()->getModelName() . ' entity (one to one).')
                    ->write(' *')
                    ->write(' * @param ' . $this->local->getReferencedTable()->getNamespace() . ' $' . lcfirst($this->local->getReferencedTable()->getModelName()))
                    ->write(' * @return ' . $table->getNamespace())
                    ->write(' */')
                    ->write('public function set' . $this->columnNameBeautifier($this->local->getReferencedTable()->getModelName()) . '(' . $this->local->getReferencedTable()->getModelName() . ' $' . lcfirst($this->local->getReferencedTable()->getModelName()) . ' = null)')
                    ->write('{')
                    ->indent()
                    ->writeIf(!$unidirectional, '$' . lcfirst($this->local->getReferencedTable()->getModelName()) . '->set' . $this->columnNameBeautifier($this->local->getOwningTable()->getModelName()) . '($this);')
                    ->write('$this->' . lcfirst($this->local->getReferencedTable()->getModelName()) . ' = $' . lcfirst($this->local->getReferencedTable()->getModelName()) . ';')
                    ->write('')
                    ->write('return $this;')
                    ->outdent()
                    ->write('}')
                    ->write('')
                    // getter
                    ->write('/**')
                    ->write(' * Get ' . $this->local->getReferencedTable()->getModelName() . ' entity (one to one).')
                    ->write(' *')
                    ->write(' * @return ' . $this->local->getReferencedTable()->getNamespace())
                    ->write(' */')
                    ->write('public function get' . $this->columnNameBeautifier($this->local->getReferencedTable()->getModelName()) . '()')
                    ->write('{')
                    ->indent()
                    ->write('return $this->' . lcfirst($this->local->getReferencedTable()->getModelName()) . ';')
                    ->outdent()
                    ->write('}')
                    ->write('')
                ;
            }
        }

        return $this;
    }

    /**
     * COMMENTME
     * 
     * @return boolean
     */
    public function getIsrequired()
    {
        $isNotNull = $this->parameters->get('isNotNull');

        // End.
        return (1 != $isNotNull)
            ? false
            : true;
    }

    /**
     * get the cascade option as array. Only returns values allowed by Doctrine.
     *
     * @param $cascadeValue string cascade options separated by comma
     * @return array array with the values or null, if no cascade values are available
     */
    private function getCascadeOption($cascadeValue)
    {
        if (!$cascadeValue) {
            return null;
        }

        $cascadeValue = array_map('strtolower', array_map('trim', explode(',', $cascadeValue)));

        // only allow certain values
        $allowed = array('persist', 'remove', 'merge', 'detach', 'all');

        $cascadeValue = array_intersect($cascadeValue, $allowed);

        if ($cascadeValue) {
            return $cascadeValue;
        } else {
            return null;
        }
    }

    /**
     * get the fetch option for a relation
     *
     * @param $fetchValue string fetch option as given in comment for foreign key
     * @return string valid fetch value or null
     */
    private function getFetchOption($fetchValue)
    {
        if (!$fetchValue) {
            return null;
        }

        $fetchValue = strtoupper($fetchValue);

        if ($fetchValue != 'EAGER' && $fetchValue != 'LAZY') {
            // invalid fetch value
            return null;
        } else {
            return $fetchValue;
        }
    }

    /**
     * get the a boolean option for a relation
     *
     * @param $booleanValue string boolean option (true or false)
     * @return boolean or null, if booleanValue was invalid
     */
    private function getBooleanOption($booleanValue)
    {
        if (!$booleanValue) {
            return null;
        }

        $booleanValue = strtolower($booleanValue);

        if ($booleanValue == 'true') {
            return true;
        } else if ($booleanValue == 'false') {
            return false;
        } else {
            return null;
        }
    }

    /**
     * get the onDelete rule. this will set the database level ON DELETE and can be set
     * to CASCADE or SET NULL. Do not confuse this with the Doctrine-level cascade rules.
     * 
     * @param string $deleteRule
     * @return string or null if the given $deleteRule != 'NO ACTION' | 'RESTRICT'
     */
    private function getDeleteRule($deleteRule)
    {
        if ($deleteRule == 'NO ACTION' || $deleteRule == 'RESTRICT') {
            // NO ACTION acts the same as RESTRICT,
            // RESTRICT is the default
            // http://dev.mysql.com/doc/refman/5.5/en/innodb-foreign-key-constraints.html
            $deleteRule = null;
        }
        return $deleteRule;
    }

}
