<?php

/*
 * The MIT License
 *
 * Copyright (c) 2010 Johannes Mueller <circus2(at)web.de>
 * Copyright (c) 2012-2013 Toha <tohenk@yahoo.com>
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

namespace MwbExporter\Formatter\Doctrine2\Annotation\Model;

use MwbExporter\Formatter\Doctrine2\Model\Column as BaseColumn;
use Doctrine\Common\Inflector\Inflector;
use MwbExporter\Writer\WriterInterface;

class Column extends BaseColumn
{
    public function asAnnotation()
    {
        $attributes = array(
            'name' => ($columnName = $this->getTable()->quoteIdentifier($this->getColumnName())) !== $this->getColumnName() ? $columnName : $this->getColumnName(),
            'type' => $this->getDocument()->getFormatter()->getDatatypeConverter()->getMappedType($this),
        );
        if (($length = $this->parameters->get('length')) && ($length != -1)) {
            $attributes['length'] = (int) $length;
        }
        if (($precision = $this->parameters->get('precision')) && ($precision != -1) && ($scale = $this->parameters->get('scale')) && ($scale != -1)) {
            $attributes['precision'] = (int) $precision;
            $attributes['scale'] = (int) $scale;
        }
        if (!$this->isNotNull()) {
            $attributes['nullable'] = true;
        }
        if($this->isUnsigned()) {
            $attributes['options'] = array('unsigned' => true);
        }

        return $attributes;
    }

    public function write(WriterInterface $writer)
    {
        $comment = $this->getComment();
        $writer
            ->write('/**')
            ->writeIf($comment, $comment)
            ->writeIf($this->isPrimary,
                    ' * '.$this->getTable()->getAnnotation('Id'))
            ->write(' * '.$this->getTable()->getAnnotation('Column', $this->asAnnotation()))
            ->writeIf($this->isAutoIncrement(),
                    ' * '.$this->getTable()->getAnnotation('GeneratedValue', array('strategy' => 'AUTO')))
            ->write(' */')
            ->write('protected $'.lcfirst($this->columnNameBeautifier($this->getColumnName())).';')
            ->write('')
        ;

        return $this;
    }

    public function writeArrayCollection(WriterInterface $writer)
    {
        foreach ($this->foreigns as $foreign) {
            if ($foreign->getForeign()->getTable()->isManyToMany()) {
                // do not create entities for many2many tables
                continue;
            }

            if ($foreign->isManyToOne() && $foreign->parseComment('unidirectional') !== 'true') { // is ManyToOne
                $related = $this->getRelatedName($foreign);
                $writer->write('$this->%s = new %s();', lcfirst(Inflector::pluralize($foreign->getOwningTable()->getModelName())).$related, $this->getTable()->getCollectionClass(false));
            }
        }

        return $this;
    }

    public function writeRelations(WriterInterface $writer)
    {
        $formatter = $this->getDocument()->getFormatter();
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
            $targetEntityFQCN = $foreign->getOwningTable()->getModelNameAsFQCN($foreign->getReferencedTable()->getEntityNamespace());
            $mappedBy = $foreign->getReferencedTable()->getModelName();

            $annotationOptions = array(
                'targetEntity' => $targetEntityFQCN,
                'mappedBy' => lcfirst($mappedBy),
                'cascade' => $formatter->getCascadeOption($foreign->parseComment('cascade')),
                'fetch' => $formatter->getFetchOption($foreign->parseComment('fetch')),
                'orphanRemoval' => $formatter->getBooleanOption($foreign->parseComment('orphanRemoval')),
            );

            $joinColumnAnnotationOptions = array(
                'name' => $foreign->getForeign()->getColumnName(),
                'referencedColumnName' => $foreign->getLocal()->getColumnName(),
                'onDelete' => $formatter->getDeleteRule($foreign->getLocal()->getParameters()->get('deleteRule')),
                'nullable' => !$foreign->getForeign()->isNotNull() ? null : false,
            );

            //check for OneToOne or OneToMany relationship
            if ($foreign->isManyToOne()) { // is OneToMany
                $related = $this->getRelatedName($foreign);
                $writer
                    ->write('/**')
                    ->write(' * '.$this->getTable()->getAnnotation('OneToMany', $annotationOptions))
                    ->write(' * '.$this->getTable()->getAnnotation('JoinColumn', $joinColumnAnnotationOptions))
                    ->write(' */')
                    ->write('protected $'.lcfirst(Inflector::pluralize($targetEntity)).$related.';')
                    ->write('')
                ;
            } else { // is OneToOne
                $writer
                    ->write('/**')
                    ->write(' * '.$this->getTable()->getAnnotation('OneToOne', $annotationOptions))
                    ->write(' * '.$this->getTable()->getAnnotation('JoinColumn', $joinColumnAnnotationOptions))
                    ->write(' */')
                    ->write('protected $'.lcfirst($targetEntity).';')
                    ->write('')
                ;
            }
        }
        // many to references
        if (null !== $this->local) {
            $targetEntity = $this->local->getReferencedTable()->getModelName();
            $targetEntityFQCN = $this->local->getReferencedTable()->getModelNameAsFQCN($this->local->getOwningTable()->getEntityNamespace());
            $inversedBy = $this->local->getOwningTable()->getModelName();

            $annotationOptions = array(
                'targetEntity' => $targetEntityFQCN,
                'mappedBy' => null,
                'inversedBy' => $inversedBy,
                // 'cascade' => $formatter->getCascadeOption($this->local->parseComment('cascade')),
                // 'fetch' => $formatter->getFetchOption($this->local->parseComment('fetch')),
                // 'orphanRemoval' => $formatter->getBooleanOption($this->local->parseComment('orphanRemoval')),
            );
            $joinColumnAnnotationOptions = array(
                'name' => $this->local->getForeign()->getColumnName(),
                'referencedColumnName' => $this->local->getLocal()->getColumnName(),
                'onDelete' => $formatter->getDeleteRule($this->local->getParameters()->get('deleteRule')),
                'nullable' => !$this->local->getForeign()->isNotNull() ? null : false,
            );

            //check for OneToOne or ManyToOne relationship
            if ($this->local->isManyToOne()) { // is ManyToOne
                $related = $this->getManyToManyRelatedName($this->local->getReferencedTable()->getRawTableName(), $this->local->getForeign()->getColumnName());
                $refRelated = $this->local->getLocal()->getRelatedName($this->local);
                if ($this->local->parseComment('unidirectional') === 'true') {
                    $annotationOptions['inversedBy'] = null;
                } else {
                    $annotationOptions['inversedBy'] = lcfirst(Inflector::pluralize($annotationOptions['inversedBy'])) . $refRelated;
                }
                $writer
                    ->write('/**')
                    ->write(' * '.$this->getTable()->getAnnotation('ManyToOne', $annotationOptions))
                    ->write(' * '.$this->getTable()->getAnnotation('JoinColumn', $joinColumnAnnotationOptions))
                    ->write(' */')
                    ->write('protected $'.lcfirst($targetEntity).$related.';')
                    ->write('')
                ;
            } else { // is OneToOne
                if ($this->local->parseComment('unidirectional') === 'true') {
                    $annotationOptions['inversedBy'] = null;
                } else {
                    $annotationOptions['inversedBy'] = lcfirst($annotationOptions['inversedBy']);
                }
                $annotationOptions['cascade'] = $formatter->getCascadeOption($this->local->parseComment('cascade'));

                $writer
                    ->write('/**')
                    ->write(' * '.$this->getTable()->getAnnotation('OneToOne', $annotationOptions))
                    ->write(' * '.$this->getTable()->getAnnotation('JoinColumn', $joinColumnAnnotationOptions))
                    ->write(' */')
                    ->write('protected $'.lcfirst($targetEntity).';')
                    ->write('')
                ;
            }
        }

        return $this;
    }

    public function writeGetterAndSetter(WriterInterface $writer)
    {
        $table = $this->getTable();
        $converter = $this->getDocument()->getFormatter()->getDatatypeConverter();
        $nativeType = $converter->getNativeType($converter->getMappedType($this));
        $writer
            // setter
            ->write('/**')
            ->write(' * Set the value of '.lcfirst($this->columnNameBeautifier($this->getColumnName())).'.')
            ->write(' *')
            ->write(' * @param '.$nativeType.' $'.lcfirst($this->columnNameBeautifier($this->getColumnName())))
            ->write(' * @return '.$table->getNamespace())
            ->write(' */')
            ->write('public function set'.$this->columnNameBeautifier($this->getColumnName()).'($'.lcfirst($this->columnNameBeautifier($this->getColumnName())).')')
            ->write('{')
            ->indent()
                ->write('$this->'.lcfirst($this->columnNameBeautifier($this->getColumnName())).' = $'.lcfirst($this->columnNameBeautifier($this->getColumnName())).';')
                ->write('')
                ->write('return $this;')
            ->outdent()
            ->write('}')
            ->write('')
            // getter
            ->write('/**')
            ->write(' * Get the value of '.lcfirst($this->columnNameBeautifier($this->getColumnName())).'.')
            ->write(' *')
            ->write(' * @return '.$nativeType)
            ->write(' */')
            ->write('public function get'.$this->columnNameBeautifier($this->getColumnName()).'()')
            ->write('{')
            ->indent()
                ->write('return $this->'.lcfirst($this->columnNameBeautifier($this->getColumnName())).';')
            ->outdent()
            ->write('}')
            ->write('')
        ;

        return $this;
    }

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
                    ->write(' * Add '.trim(Inflector::pluralize($foreign->getOwningTable()->getModelName()).' '.$related_text).' to collection.')
                    ->write(' *')
                    ->write(' * @param '.$table->getCollectionInterface().'|'. $foreign->getOwningTable()->getNamespace().' $'.lcfirst(Inflector::pluralize($foreign->getOwningTable()->getModelName())))
                    ->write(' * @return '.$table->getNamespace())
                    ->write(' */')
                    ->write('public function add'.$this->columnNameBeautifier(Inflector::pluralize($foreign->getOwningTable()->getModelName())).$related.'('.$table->getCollectionInterface().' $'.lcfirst(Inflector::pluralize($foreign->getOwningTable()->getModelName())).')')
                    ->write('{')
                        ->indent()
                        ->write('foreach ($'.lcfirst(Inflector::pluralize($foreign->getOwningTable()->getModelName())).' as $'.lcfirst($foreign->getOwningTable()->getModelName()).') {')
                            ->indent()
                            ->write('$this->add'.$this->columnNameBeautifier($foreign->getOwningTable()->getModelName()).$related.'($'.lcfirst($foreign->getOwningTable()->getModelName()).');')
                            ->outdent()
                        ->write('}')
                        ->write('return $this;')
                        ->outdent()
                    ->write('}')
                    ->write('')
                    ->write('/**')
                    ->write(' * Remove '.trim(Inflector::pluralize($foreign->getOwningTable()->getModelName()).' '.$related_text).' form collection.')
                    ->write(' *')
                    ->write(' * @param '.$table->getCollectionInterface().'|'. $foreign->getOwningTable()->getNamespace().' $'.lcfirst(Inflector::pluralize($foreign->getOwningTable()->getModelName())))
                    ->write(' * @return '.$table->getNamespace())
                    ->write(' */')
                    ->write('public function remove'.$this->columnNameBeautifier(Inflector::pluralize($foreign->getOwningTable()->getModelName())).$related.'('.$table->getCollectionInterface().' $'.lcfirst(Inflector::pluralize($foreign->getOwningTable()->getModelName())).')')
                    ->write('{')
                        ->indent()
                        ->write('foreach ($'.lcfirst(Inflector::pluralize($foreign->getOwningTable()->getModelName())).' as $'.lcfirst($foreign->getOwningTable()->getModelName()).') {')
                            ->indent()
                            ->write('$%s->set%s(null);', lcfirst($foreign->getOwningTable()->getModelName()), $table->getModelName())
                            ->write('$this->remove'.$this->columnNameBeautifier($foreign->getOwningTable()->getModelName()).$related.'($'.lcfirst($foreign->getOwningTable()->getModelName()).');')
                            ->outdent()
                        ->write('}')
                        ->write('return $this;')
                        ->outdent()
                    ->write('}')
                    ->write('')
                    ->write('/**')
                    ->write(' * Add '.trim($foreign->getOwningTable()->getModelName().' '.$related_text). ' entity to collection (one to many).')
                    ->write(' *')
                    ->write(' * @param '.$foreign->getOwningTable()->getNamespace().' $'.lcfirst($foreign->getOwningTable()->getModelName()))
                    ->write(' * @return '.$table->getNamespace())
                    ->write(' */')
                    ->write('public function add'.$this->columnNameBeautifier($foreign->getOwningTable()->getModelName()).$related.'('.$foreign->getOwningTable()->getModelName().' $'.lcfirst($foreign->getOwningTable()->getModelName()).')')
                    ->write('{')
                    ->indent()
                        ->write('if ($this->'.lcfirst(Inflector::pluralize($foreign->getOwningTable()->getModelName())).$related.'->contains($'.lcfirst($foreign->getOwningTable()->getModelName()).')) {')
                        ->indent()
                            ->write('return ;')
                        ->outdent()
                        ->write('}')
                        ->write('$%s->set%s($this);', lcfirst($foreign->getOwningTable()->getModelName()), $table->getModelName())
                        ->write('$this->'.lcfirst(Inflector::pluralize($foreign->getOwningTable()->getModelName())).$related.'[] = $'.lcfirst($foreign->getOwningTable()->getModelName()).';')
                        ->write('')
                        ->write('return $this;')
                    ->outdent()
                    ->write('}')
                    ->write('')
                    ->write('/**')
                    ->write(' * Remove '.trim($foreign->getOwningTable()->getModelName().' '.$related_text). ' form collection (one to many).')
                    ->write(' *')
                    ->write(' * @param '.$foreign->getOwningTable()->getNamespace().' $'.lcfirst($foreign->getOwningTable()->getModelName()))
                    ->write(' */')
                    ->write('public function remove'.$this->columnNameBeautifier($foreign->getOwningTable()->getModelName()).$related.'('.$foreign->getOwningTable()->getModelName().' $'.lcfirst($foreign->getOwningTable()->getModelName()).')')
                    ->write('{')
                    ->indent()
                        ->write('if (!$this->'.lcfirst(Inflector::pluralize($foreign->getOwningTable()->getModelName())).$related.'->contains($'.lcfirst($foreign->getOwningTable()->getModelName()).')) {')
                        ->indent()
                        ->write('return ;')
                        ->outdent()
                        ->write('}')
                        ->write('$this->'.lcfirst(Inflector::pluralize($foreign->getOwningTable()->getModelName())).$related.'->removeElement($'.lcfirst($foreign->getOwningTable()->getModelName()).');')
                    ->outdent()
                    ->write('}')
                    ->write('')
                    // getter
                    ->write('/**')
                    ->write(' * Get '.trim($foreign->getOwningTable()->getModelName().' '.$related_text).' entity collection (one to many).')
                    ->write(' *')
                    ->write(' * @return '.$table->getCollectionInterface())
                    ->write(' */')
                    ->write('public function get'.$this->columnNameBeautifier(Inflector::pluralize($foreign->getOwningTable()->getModelName())).$related.'()')
                    ->write('{')
                    ->indent()
                        ->write('return $this->'.lcfirst(Inflector::pluralize($foreign->getOwningTable()->getModelName())).$related.';')
                    ->outdent()
                    ->write('}')
                ;
            } else { // OneToOne
                $writer
                    // setter
                    ->write('/**')
                    ->write(' * Set '.$foreign->getOwningTable()->getModelName().' entity (one to one).')
                    ->write(' *')
                    ->write(' * @param '.$foreign->getOwningTable()->getNamespace().' $'.lcfirst($foreign->getOwningTable()->getModelName()))
                    ->write(' * @return '.$table->getNamespace())
                    ->write(' */')
                    ->write('public function set'.$this->columnNameBeautifier($foreign->getOwningTable()->getModelName()).'('.$foreign->getOwningTable()->getModelName().' $'.lcfirst($foreign->getOwningTable()->getModelName()).')')
                    ->write('{')
                    ->indent()
                        ->write('$this->'.lcfirst($foreign->getOwningTable()->getModelName()).' = $'.lcfirst($foreign->getOwningTable()->getModelName()).';')
                        ->write('')
                        ->write('return $this;')
                    ->outdent()
                    ->write('}')
                    ->write('')
                    // getter
                    ->write('/**')
                    ->write(' * Get '.$foreign->getOwningTable()->getModelName().' entity (one to one).')
                    ->write(' *')
                    ->write(' * @return '.$foreign->getOwningTable()->getNamespace())
                    ->write(' */')
                    ->write('public function get'.$this->columnNameBeautifier($foreign->getOwningTable()->getModelName()).'()')
                    ->write('{')
                    ->indent()
                        ->write('return $this->'.lcfirst($foreign->getOwningTable()->getModelName()).';')
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
                    ->write(' * Set '.trim($this->local->getReferencedTable()->getModelName().' '.$related_text).' entity (many to one).')
                    ->write(' *')
                    ->write(' * @param '.$this->local->getReferencedTable()->getNamespace().' $'.lcfirst($this->local->getReferencedTable()->getModelName()))
                    ->write(' * @return '.$table->getNamespace())
                    ->write(' */')
                    ->write('public function set'.$this->columnNameBeautifier($this->local->getReferencedTable()->getModelName()).$related.'('.$this->local->getReferencedTable()->getModelName().' $'.lcfirst($this->local->getReferencedTable()->getModelName()).' = null)')
                    ->write('{')
                    ->indent()
                        ->write('$this->'.lcfirst($this->local->getReferencedTable()->getModelName()).$related.' = $'.lcfirst($this->local->getReferencedTable()->getModelName()).';')
                        ->write('')
                        ->write('return $this;')
                    ->outdent()
                    ->write('}')
                    ->write('')
                    // getter
                    ->write('/**')
                    ->write(' * Get '.trim($this->local->getReferencedTable()->getModelName().' '.$related_text).' entity (many to one).')
                    ->write(' *')
                    ->write(' * @return '.$this->local->getReferencedTable()->getNamespace())
                    ->write(' */')
                    ->write('public function get'.$this->columnNameBeautifier($this->local->getReferencedTable()->getModelName()).$related.'()')
                    ->write('{')
                    ->indent()
                        ->write('return $this->'.lcfirst($this->local->getReferencedTable()->getModelName()).$related.';')
                    ->outdent()
                    ->write('}')
                    ->write('')
                ;
            } else { // OneToOne
                $writer
                    // setter
                    ->write('/**')
                    ->write(' * Set '.$this->local->getReferencedTable()->getModelName().' entity (one to one).')
                    ->write(' *')
                    ->write(' * @param '.$this->local->getReferencedTable()->getNamespace().' $'.lcfirst($this->local->getReferencedTable()->getModelName()))
                    ->write(' * @return '.$table->getNamespace())
                    ->write(' */')
                    ->write('public function set'.$this->columnNameBeautifier($this->local->getReferencedTable()->getModelName()).'('.$this->local->getReferencedTable()->getModelName().' $'.lcfirst($this->local->getReferencedTable()->getModelName()).' = null)')
                    ->write('{')
                    ->indent()
                        ->writeIf(!$unidirectional, '$'.lcfirst($this->local->getReferencedTable()->getModelName()).'->set'.$this->columnNameBeautifier($this->local->getOwningTable()->getModelName()).'($this);')
                        ->write('$this->'.lcfirst($this->local->getReferencedTable()->getModelName()).' = $'.lcfirst($this->local->getReferencedTable()->getModelName()).';')
                        ->write('')
                        ->write('return $this;')
                    ->outdent()
                    ->write('}')
                    ->write('')
                    // getter
                    ->write('/**')
                    ->write(' * Get '.$this->local->getReferencedTable()->getModelName().' entity (one to one).')
                    ->write(' *')
                    ->write(' * @return '.$this->local->getReferencedTable()->getNamespace())
                    ->write(' */')
                    ->write('public function get'.$this->columnNameBeautifier($this->local->getReferencedTable()->getModelName()).'()')
                    ->write('{')
                    ->indent()
                        ->write('return $this->'.lcfirst($this->local->getReferencedTable()->getModelName()).';')
                    ->outdent()
                    ->write('}')
                    ->write('')
                ;
            }
        }

        return $this;
    }
}
