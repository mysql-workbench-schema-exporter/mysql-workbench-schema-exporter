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
            ->write('protected $'.$this->getColumnName().';')
            ->write('')
        ;

        return $this;
    }

    public function writeArrayCollection(WriterInterface $writer)
    {
        foreach ($this->foreigns as $foreign) {
            if ($this->isForeignKeyIgnored($foreign) || !$foreign->isManyToOne()) {
                continue;
            }
            $this->getDocument()->addLog(sprintf('  Writing 1 <=> N constructor "%s".', $foreign->getOwningTable()->getModelName()));

            $related = $this->getRelatedName($foreign);
            $writer->write('$this->%s = new %s();', lcfirst(Inflector::pluralize($foreign->getReferencedTable()->getModelName())).$related, $this->getTable()->getCollectionClass(false));
        }

        return $this;
    }

    public function writeRelations(WriterInterface $writer)
    {
        $formatter = $this->getDocument()->getFormatter();
        // one to many references
        foreach ($this->foreigns as $foreign) {
            if ($this->isForeignKeyIgnored($foreign)) {
                continue;
            }

            $targetEntity = $foreign->getReferencedTable()->getModelName();
            $targetEntityFQCN = $foreign->getReferencedTable()->getModelNameAsFQCN($foreign->getOwningTable()->getEntityNamespace());
            $mappedBy = $foreign->getOwningTable()->getModelName();
            $related = $this->getManyToManyRelatedName($foreign->getOwningTable()->getRawTableName(), $foreign->getLocal()->getColumnName());

            $this->getDocument()->addLog(sprintf('  Writing 1 <=> ? relation "%s".', $targetEntity));

            $annotationOptions = array(
                'targetEntity' => $targetEntityFQCN,
                'mappedBy' => lcfirst($mappedBy).$related,
                'cascade' => $formatter->getCascadeOption($foreign->parseComment('cascade')),
                'fetch' => $formatter->getFetchOption($foreign->parseComment('fetch')),
                'orphanRemoval' => $formatter->getBooleanOption($foreign->parseComment('orphanRemoval')),
            );

            $joinColumnAnnotationOptions = array(
                'name' => $foreign->getLocal()->getColumnName(),
                'referencedColumnName' => $foreign->getForeign()->getColumnName(),
                'nullable' => !$foreign->getLocal()->isNotNull() ? null : false,
            );


            if (null !== ($deleteRule = $formatter->getDeleteRule($foreign->getLocal()->getParameters()->get('deleteRule')))) {
                $joinColumnAnnotationOptions['onDelete'] = $deleteRule;
            }

            //check for OneToOne or OneToMany relationship
            if ($foreign->isManyToOne()) { // is OneToMany
                $this->getDocument()->addLog('  Relation considered as "1 <=> N".');

                $related = $this->getRelatedName($foreign);
                if (count($foreign->getLocals()) == 1) {
                    $writer
                        ->write('/**')
                        ->write(' * '.$this->getTable()->getAnnotation('OneToMany', $annotationOptions))
                        ->write(' * '.$this->getTable()->getAnnotation('JoinColumn', $joinColumnAnnotationOptions))
                        ->write(' */')
                        ->write('protected $'.lcfirst(Inflector::pluralize($targetEntity)).$related.';')
                        ->write('')
                    ;
                } else {
                    // composite foreign keys
                    $joins = array();
                    $lcols = $foreign->getLocals();
                    $fcols = $foreign->getForeigns();
                    for ($i = 0; $i < count($lcols); $i++) {
                        $joins[] = $this->getTable()->getAnnotation('JoinColumn', array(
                            'name' => $lcols[$i]->getColumnName(),
                            'referencedColumnName' => $fcols[$i]->getColumnName(),
                            'nullable' => $lcols[$i]->isNotNull() ? null : false,
                        ));
                    }
                    $writer
                        ->write('/**')
                        ->write(' * '.$this->getTable()->getAnnotation('OneToMany', $annotationOptions))
                        ->write(' * '.$this->getTable()->getAnnotation('JoinColumns', array($joins), array('multiline' => true, 'wrapper' => ' * %s')))
                        ->write(' */')
                        ->write('protected $'.lcfirst(Inflector::pluralize($targetEntity)).$related.';')
                        ->write('')
                    ;
                }
            } else { // is OneToOne
                $this->getDocument()->addLog('  Relation considered as "1 <=> 1".');

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
        foreach ($this->locals as $local) {
            if (!$this->isLocalForeignKeyIgnored($local)) {
                $targetEntity = $local->getOwningTable()->getModelName();
                $targetEntityFQCN = $local->getOwningTable()->getModelNameAsFQCN($local->getReferencedTable()->getEntityNamespace());
                $inversedBy = $local->getReferencedTable()->getModelName();
    
                $this->getDocument()->addLog(sprintf('  Writing N <=> ? relation "%s".', $targetEntity));
    
                $annotationOptions = array(
                    'targetEntity' => $targetEntityFQCN,
                    'mappedBy' => null,
                    'inversedBy' => $inversedBy,
                    // 'cascade' => $formatter->getCascadeOption($local->parseComment('cascade')),
                    // 'fetch' => $formatter->getFetchOption($local->parseComment('fetch')),
                    // 'orphanRemoval' => $formatter->getBooleanOption($local->parseComment('orphanRemoval')),
                );
                $joinColumnAnnotationOptions = array(
                    'name' => $local->getForeign()->getColumnName(),
                    'referencedColumnName' => $local->getLocal()->getColumnName(),
                    'onDelete' => $formatter->getDeleteRule($local->getParameters()->get('deleteRule')),
                    'nullable' => !$local->getForeign()->isNotNull() ? null : false,
                );
    
                //check for OneToOne or ManyToOne relationship
                if ($local->isManyToOne()) { // is ManyToOne
                    $this->getDocument()->addLog('  Relation considered as "N <=> 1".');
    
                    $related = $this->getManyToManyRelatedName($local->getOwningTable()->getRawTableName(), $local->getLocal()->getColumnName());
                    $refRelated = $local->getLocal()->getRelatedName($local);
                    if ($local->parseComment('unidirectional') === 'true') {
                        $annotationOptions['inversedBy'] = null;
                    } else {
                        $annotationOptions['inversedBy'] = lcfirst(Inflector::pluralize($annotationOptions['inversedBy'])) . $refRelated;
                    }
                    if (count($local->getLocals()) == 1) {
                        $writer
                            ->write('/**')
                            ->write(' * '.$this->getTable()->getAnnotation('ManyToOne', $annotationOptions))
                            ->write(' * '.$this->getTable()->getAnnotation('JoinColumn', $joinColumnAnnotationOptions))
                            ->write(' */')
                            ->write('protected $'.lcfirst($targetEntity).$related.';')
                            ->write('')
                        ;
                    } else {
                        // composite foreign keys
                        $joins = array();
                        $lcols = $local->getLocals();
                        $fcols = $local->getForeigns();
                        for ($i = 0; $i < count($lcols); $i++) {
                            $joins[] = $this->getTable()->getAnnotation('JoinColumn', array(
                                'name' => $fcols[$i]->getColumnName(),
                                'referencedColumnName' => $lcols[$i]->getColumnName(),
                                'nullable' => $fcols[$i]->isNotNull() ? null : false,
                            ));
                        }
                        $writer
                            ->write('/**')
                            ->write(' * '.$this->getTable()->getAnnotation('ManyToOne', $annotationOptions))
                            ->write(' * '.$this->getTable()->getAnnotation('JoinColumns', array($joins), array('multiline' => true, 'wrapper' => ' * %s')))
                            ->write(' */')
                            ->write('protected $'.lcfirst($targetEntity).$related.';')
                            ->write('')
                        ;
                    }
                } else { // is OneToOne
                    $this->getDocument()->addLog('  Relation considered as "1 <=> 1".');
    
                    if ($local->parseComment('unidirectional') === 'true') {
                        $annotationOptions['inversedBy'] = null;
                    } else {
                        $annotationOptions['inversedBy'] = lcfirst($annotationOptions['inversedBy']);
                    }
                    $annotationOptions['cascade'] = $formatter->getCascadeOption($local->parseComment('cascade'));
    
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
        }

        return $this;
    }

    public function writeGetterAndSetter(WriterInterface $writer)
    {
        $this->getDocument()->addLog(sprintf('  Writing setter/getter for column "%s" .', $this->getColumnName()));

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
        foreach ($this->foreigns as $foreign) {
            if ($this->isForeignKeyIgnored($foreign)) {
                continue;
            }

            $this->getDocument()->addLog(sprintf('  Writing setter/getter for 1 <=> ? "%s".', $foreign->getParameters()->get('name')));

            if ($foreign->isManyToOne()) { // is ManyToOne
                $this->getDocument()->addLog(sprintf('  Applying setter/getter for "%s".', '1 <=> N'));

                $related = $this->getRelatedName($foreign);
                $related_text = $this->getRelatedName($foreign, false);
                $writer
                    // setter
                    ->write('/**')
                    ->write(' * Add '.trim($foreign->getReferencedTable()->getModelName().' '.$related_text). ' entity to collection (one to many).')
                    ->write(' *')
                    ->write(' * @param '.$foreign->getReferencedTable()->getNamespace().' $'.lcfirst($foreign->getReferencedTable()->getModelName()))
                    ->write(' * @return '.$table->getNamespace())
                    ->write(' */')
                    ->write('public function add'.$this->columnNameBeautifier($foreign->getReferencedTable()->getModelName()).$related.'('.$foreign->getReferencedTable()->getModelName().' $'.lcfirst($foreign->getReferencedTable()->getModelName()).')')
                    ->write('{')
                    ->indent()
                        ->write('$this->'.lcfirst(Inflector::pluralize($foreign->getReferencedTable()->getModelName())).$related.'[] = $'.lcfirst($foreign->getReferencedTable()->getModelName()).';')
                        ->write('')
                        ->write('return $this;')
                    ->outdent()
                    ->write('}')
                    ->write('')
                    // getter
                    ->write('/**')
                    ->write(' * Get '.trim($foreign->getReferencedTable()->getModelName().' '.$related_text).' entity collection (one to many).')
                    ->write(' *')
                    ->write(' * @return '.$table->getCollectionInterface())
                    ->write(' */')
                    ->write('public function get'.$this->columnNameBeautifier(Inflector::pluralize($foreign->getReferencedTable()->getModelName())).$related.'()')
                    ->write('{')
                    ->indent()
                        ->write('return $this->'.lcfirst(Inflector::pluralize($foreign->getReferencedTable()->getModelName())).$related.';')
                    ->outdent()
                    ->write('}')
                ;
            } else { // OneToOne
                $this->getDocument()->addLog(sprintf('  Applying setter/getter for "%s".', '1 <=> 1'));

                $writer
                    // setter
                    ->write('/**')
                    ->write(' * Set '.$foreign->getReferencedTable()->getModelName().' entity (one to one).')
                    ->write(' *')
                    ->write(' * @param '.$foreign->getReferencedTable()->getNamespace().' $'.lcfirst($foreign->getReferencedTable()->getModelName()))
                    ->write(' * @return '.$table->getNamespace())
                    ->write(' */')
                    ->write('public function set'.$this->columnNameBeautifier($foreign->getReferencedTable()->getModelName()).'('.$foreign->getReferencedTable()->getModelName().' $'.lcfirst($foreign->getReferencedTable()->getModelName()).')')
                    ->write('{')
                    ->indent()
                        ->write('$this->'.lcfirst($foreign->getReferencedTable()->getModelName()).' = $'.lcfirst($foreign->getReferencedTable()->getModelName()).';')
                        ->write('')
                        ->write('return $this;')
                    ->outdent()
                    ->write('}')
                    ->write('')
                    // getter
                    ->write('/**')
                    ->write(' * Get '.$foreign->getReferencedTable()->getModelName().' entity (one to one).')
                    ->write(' *')
                    ->write(' * @return '.$foreign->getReferencedTable()->getNamespace())
                    ->write(' */')
                    ->write('public function get'.$this->columnNameBeautifier($foreign->getReferencedTable()->getModelName()).'()')
                    ->write('{')
                    ->indent()
                        ->write('return $this->'.lcfirst($foreign->getReferencedTable()->getModelName()).';')
                    ->outdent()
                    ->write('}')
                ;
            }
            $writer
                ->write('')
            ;
        }
        // many to one references
        foreach ($this->locals as $local) {
            if (!$this->isLocalForeignKeyIgnored($local)) {
                $this->getDocument()->addLog(sprintf('  Writing setter/getter for N <=> ? "%s".', $local->getParameters()->get('name')));

                $unidirectional = $local->parseComment('unidirectional') === 'true';
                if ($local->isManyToOne()) { // is ManyToOne
                    $this->getDocument()->addLog(sprintf('  Applying setter/getter for "%s".', 'N <=> 1'));

                    $related = $this->getManyToManyRelatedName($local->getOwningTable()->getRawTableName(), $local->getLocal()->getColumnName());
                    $related_text = $this->getManyToManyRelatedName($local->getOwningTable()->getRawTableName(), $local->getForeign()->getColumnName(), false);
                    $writer
                        // setter
                        ->write('/**')
                        ->write(' * Set '.trim($local->getOwningTable()->getModelName().' '.$related_text).' entity (many to one).')
                        ->write(' *')
                        ->write(' * @param '.$local->getOwningTable()->getNamespace().' $'.lcfirst($local->getOwningTable()->getModelName()))
                        ->write(' * @return '.$table->getNamespace())
                        ->write(' */')
                        ->write('public function set'.$this->columnNameBeautifier($local->getOwningTable()->getModelName()).$related.'('.$local->getOwningTable()->getModelName().' $'.lcfirst($local->getOwningTable()->getModelName()).' = null)')
                        ->write('{')
                        ->indent()
                            ->write('$this->'.lcfirst($local->getOwningTable()->getModelName()).$related.' = $'.lcfirst($local->getOwningTable()->getModelName()).';')
                            ->write('')
                            ->write('return $this;')
                        ->outdent()
                        ->write('}')
                        ->write('')
                        // getter
                        ->write('/**')
                        ->write(' * Get '.trim($local->getOwningTable()->getModelName().' '.$related_text).' entity (many to one).')
                        ->write(' *')
                        ->write(' * @return '.$local->getOwningTable()->getNamespace())
                        ->write(' */')
                        ->write('public function get'.$this->columnNameBeautifier($local->getOwningTable()->getModelName()).$related.'()')
                        ->write('{')
                        ->indent()
                            ->write('return $this->'.lcfirst($local->getOwningTable()->getModelName()).$related.';')
                        ->outdent()
                        ->write('}')
                        ->write('')
                    ;
                } else { // OneToOne
                    $this->getDocument()->addLog(sprintf('  Applying setter/getter for "%s".', '1 <=> 1'));

                    $writer
                        // setter
                        ->write('/**')
                        ->write(' * Set '.$local->getReferencedTable()->getModelName().' entity (one to one).')
                        ->write(' *')
                        ->write(' * @param '.$local->getReferencedTable()->getNamespace().' $'.lcfirst($local->getReferencedTable()->getModelName()))
                        ->write(' * @return '.$table->getNamespace())
                        ->write(' */')
                        ->write('public function set'.$this->columnNameBeautifier($local->getReferencedTable()->getModelName()).'('.$local->getReferencedTable()->getModelName().' $'.lcfirst($local->getReferencedTable()->getModelName()).' = null)')
                        ->write('{')
                        ->indent()
                            ->writeIf(!$unidirectional, '$'.lcfirst($local->getReferencedTable()->getModelName()).'->set'.$this->columnNameBeautifier($local->getOwningTable()->getModelName()).'($this);')
                            ->write('$this->'.lcfirst($local->getReferencedTable()->getModelName()).' = $'.lcfirst($local->getReferencedTable()->getModelName()).';')
                            ->write('')
                            ->write('return $this;')
                        ->outdent()
                        ->write('}')
                        ->write('')
                        // getter
                        ->write('/**')
                        ->write(' * Get '.$local->getReferencedTable()->getModelName().' entity (one to one).')
                        ->write(' *')
                        ->write(' * @return '.$local->getReferencedTable()->getNamespace())
                        ->write(' */')
                        ->write('public function get'.$this->columnNameBeautifier($local->getReferencedTable()->getModelName()).'()')
                        ->write('{')
                        ->indent()
                            ->write('return $this->'.lcfirst($local->getReferencedTable()->getModelName()).';')
                        ->outdent()
                        ->write('}')
                        ->write('')
                    ;
                }
            }
        }

        return $this;
    }

    public function asAnnotation()
    {
        $attributes = array(
            'name' => ($columnName = $this->getTable()->quoteIdentifier($this->getColumnName())) !== $this->getColumnName() ? $columnName : null,
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

    /**
     * Check if foreign key should be ignored.
     *
     * @param \MwbExporter\Model\ForeignKey $foreignKey
     * @return boolean
     */
    protected function isForeignKeyIgnored($foreignKey)
    {
        // foreign key has not been processed
        if ($this->getParent()->isCollectedForeignKeyExist($foreignKey)) {
            return true;
        }
        // do not create entities for many2many tables
        if ($foreignKey->getForeign()->getTable()->isManyToMany()) {
            return true;
        }
        // do not output mapping in foreign table when the unidirectional option is set
        if ($foreignKey->parseComment('unidirectional') === 'true') {
            return true;
        }
        $this->getParent()->collectForeignKey($foreignKey);

        return false;
    }

    /**
     * Check if local foreign key should be ignored.
     *
     * @param \MwbExporter\Model\ForeignKey $foreignKey
     * @return boolean
     */
    protected function isLocalForeignKeyIgnored($foreignKey)
    {
        // foreign key has not been processed
        if ($this->getParent()->isCollectedLocalForeignKeyExist($foreignKey)) {
            return true;
        }
        // do not create entities for many2many tables
        if ($foreignKey->getLocal()->getTable()->isManyToMany()) {
            return true;
        }
        if ($foreignKey->getOwningTable()->getId() == $this->getTable()->getId()) {
            return true;
        }
        $this->getParent()->collectLocalForeignKey($foreignKey);

        return false;
    }
}
