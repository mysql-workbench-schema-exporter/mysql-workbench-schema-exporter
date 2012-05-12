<?php
/*
 * The MIT License
 *
 * Copyright (c) 2010 Johannes Mueller <circus2(at)web.de>
 * Copyright (c) 2012 Toha <tohenk@yahoo.com>
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

use MwbExporter\Model\Column as BaseColumn;
use MwbExporter\Helper\Pluralizer;
use MwbExporter\Writer\WriterInterface;

class Column extends BaseColumn
{
    /**
     * @inheritDoc
     */
    protected function getRelationReferenceCount($fkey, $max = null)
    {
        $count = 0;
        $table = $this->getParent()->getParent();
        if (is_array($relations = $table->getManyToManyRelations())) {
            $tablename = $fkey->getOwningTable()->getRawTableName();
            foreach ($relations as $relation) {
                // $relation key => reference (ForeignKey), refTable (Table)
                if ($this->checkReferenceTableName($relation['refTable'], $tablename) && !$relation['reference']->getOwningTable()->isManyToMany()) {
                    $count++;
                }
                if ($max && $count == $max) {
                    break;
                }
            }
        }

        return $count;
    }

    public function write(WriterInterface $writer)
    {
        $writer
            ->write('/**')
            ->writeIf($this->isPrimary,
                    ' * '.$this->getTable()->addPrefix('Id'))
            ->write(' * '.$this->getTable()->addPrefix('Column(type='.$this->getDocument()->getFormatter()->getDatatypeConverter()->getType($this).($this->parameters->get('isNotNull') != 1 ? ', nullable=true' : '').')'))
            ->writeIf($this->parameters->get('autoIncrement') == 1,
                    ' * '.$this->getTable()->addPrefix('GeneratedValue(strategy="AUTO")'))
            ->write(' */')
            ->write('private $'.$this->getColumnName().';')
            ->write('')
        ;

        return $this;
    }

    public function writeArrayCollection(WriterInterface $writer)
    {
        if (is_array($this->foreigns)) {
            foreach ($this->foreigns as $foreign) {
                if ($foreign->isManyToOne()) { // is ManyToOne
                    $related = $this->getRelatedName($foreign);
                    $writer->write('$this->%s = new %s();', lcfirst(Pluralizer::pluralize($foreign->getOwningTable()->getModelName())).$related, $this->getTable()->getCollectionClass(false));
                } else { // is OneToOne
                }
            }
        }

        return $this;
    }

    public function writeRelations(WriterInterface $writer)
    {
        // one to many references
        if (is_array($this->foreigns)) {
            foreach ($this->foreigns as $foreign) {
                //check for OneToOne or OneToMany relationship
                if ($foreign->isManyToOne()) { // is OneToMany
                    $related = $this->getRelatedName($foreign);
                    $writer
                        ->write('/**')
                        ->write(' * '.$this->getTable()->addPrefix('OneToMany(targetEntity="'.$foreign->getOwningTable()->getModelName().'", mappedBy="'.lcfirst($foreign->getReferencedTable()->getModelName()).'")'))
                        ->write(' * '.$this->getTable()->addPrefix('JoinColumn(name="'.$foreign->getForeign()->getColumnName().'", referencedColumnName="'.$foreign->getLocal()->getColumnName().'")'))
                        ->write(' */')
                        ->write('private $'.lcfirst(Pluralizer::pluralize($foreign->getOwningTable()->getModelName())).$related.';')
                        ->write('')
                    ;
                } else { // is OneToOne
                    $writer
                        ->write('/**')
                        ->write(' * '.$this->getTable()->addPrefix('OneToOne(targetEntity="'.$foreign->getOwningTable()->getModelName().'", mappedBy="'.lcfirst($foreign->getReferencedTable()->getModelName()).'")'))
                        ->write(' * '.$this->getTable()->addPrefix('JoinColumn(name="'.$foreign->getForeign()->getColumnName().'", referencedColumnName="'.$foreign->getLocal()->getColumnName().'")'))
                        ->write(' */')
                        ->write('private $'.lcfirst($foreign->getOwningTable()->getModelName()).';')
                        ->write('')
                    ;
                }
            }
        }
        // many to references
        if (null !== $this->local) {
            //check for OneToOne or ManyToOne relationship
            if ($this->local->isManyToOne()) { // is ManyToOne
                $related = $this->getManyToManyRelatedName($this->local->getReferencedTable()->getRawTableName(), $this->local->getForeign()->getColumnName());
                $writer
                    ->write('/**')
                    ->write(' * '.$this->getTable()->addPrefix('ManyToOne(targetEntity="'.$this->local->getReferencedTable()->getModelName().'", inversedBy="'.lcfirst(Pluralizer::pluralize($this->local->getOwningTable()->getModelName())).'")'))
                    ->write(' * '.$this->getTable()->addPrefix('JoinColumn(name="'.$this->local->getForeign()->getColumnName().'", referencedColumnName="'.$this->local->getLocal()->getColumnName().'")'))
                    ->write(' */')
                    ->write('private $'.lcfirst($this->local->getReferencedTable()->getModelName()).$related.';')
                    ->write('')
                ;
            } else { // is OneToOne
                $writer
                    ->write('/**')
                    ->write(' * '.$this->getTable()->addPrefix('OneToOne(targetEntity="'.$this->local->getReferencedTable()->getModelName().'", inversedBy="'.lcfirst($this->local->getOwningTable()->getModelName()).'")'))
                    ->write(' * '.$this->getTable()->addPrefix('JoinColumn(name="'.$this->local->getForeign()->getColumnName().'", referencedColumnName="'.$this->local->getLocal()->getColumnName().'")'))
                    ->write(' */')
                    ->write('private $'.lcfirst($this->local->getReferencedTable()->getModelName()).';')
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
            ->write(' * Set the value of '.$this->getColumnName().'.')
            ->write(' *')
            ->write(' * @param '.$nativeType.' $'.$this->getColumnName())
            ->write(' * @return '.$table->getNamespace())
            ->write(' */')
            ->write('public function set'.$this->columnNameBeautifier($this->getColumnName()).'($'.$this->getColumnName().')')
            ->write('{')
            ->indent()
                ->write('$this->'.$this->getColumnName().' = $'.$this->getColumnName().';')
                ->write('')
                ->write('return $this;')
            ->outdent()
            ->write('}')
            ->write('')
            // getter
            ->write('/**')
            ->write(' * Get the value of '.$this->getColumnName().'.')
            ->write(' *')
            ->write(' * @return '.$nativeType)
            ->write(' */')
            ->write('public function get'.$this->columnNameBeautifier($this->getColumnName()).'()')
            ->write('{')
            ->indent()
                ->write('return $this->'.$this->getColumnName().';')
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
        if (is_array($this->foreigns)) {
            foreach ($this->foreigns as $foreign) {
                if ($foreign->isManyToOne()) { // is ManyToOne
                    $related = $this->getRelatedName($foreign);
                    $related_text = $this->getRelatedName($foreign, false);
                    $writer
                        // setter
                        ->write('/**')
                        ->write(' * Add '.trim($foreign->getOwningTable()->getModelName().' '.$related_text). ' entity to collection (one to many).')
                        ->write(' *')
                        ->write(' * @param '.$foreign->getOwningTable()->getNamespace().' $'.lcfirst($foreign->getOwningTable()->getModelName()))
                        ->write(' * @return '.$table->getNamespace())
                        ->write(' */')
                        ->write('public function add'.$this->columnNameBeautifier($foreign->getOwningTable()->getModelName()).$related.'('.$foreign->getOwningTable()->getModelName().' $'.lcfirst($foreign->getOwningTable()->getModelName()).')')
                        ->write('{')
                        ->indent()
                            ->write('$this->'.lcfirst(Pluralizer::pluralize($foreign->getOwningTable()->getModelName())).$related.'[] = $'.lcfirst($foreign->getOwningTable()->getModelName()).';')
                            ->write('')
                            ->write('return $this;')
                        ->outdent()
                        ->write('}')
                        ->write('')
                        // getter
                        ->write('/**')
                        ->write(' * Get '.trim($foreign->getOwningTable()->getModelName().' '.$related_text).' entity collection (one to many).')
                        ->write(' *')
                        ->write(' * @return '.$table->getCollectionInterface())
                        ->write(' */')
                        ->write('public function get'.$this->columnNameBeautifier(Pluralizer::pluralize($foreign->getOwningTable()->getModelName())).$related.'()')
                        ->write('{')
                        ->indent()
                            ->write('return $this->'.lcfirst(Pluralizer::pluralize($foreign->getOwningTable()->getModelName())).$related.';')
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
        }
        // many to one references
        if (null !== $this->local) {
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
                        ->write('$'.lcfirst($this->local->getReferencedTable()->getModelName()).'->set'.$this->columnNameBeautifier($this->local->getOwningTable()->getModelName()).'($this);')
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
