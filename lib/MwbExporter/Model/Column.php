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

namespace MwbExporter\Model;

use MwbExporter\Registry\RegistryHolder;

class Column extends Base
{
    /**
     * @var \MwbExporter\Registry\RegistryHolder
     */
    protected $links = null;

    /**
     * @var bool
     */
    protected $isPrimary = false;

    /**
     * @var bool
     */
    protected $isUnique = false;

    /**
     * @var array \MwbExporter\Model\ForeignKey
     */
    protected $locals = array();

    /**
     * @var array \MwbExporter\Model\ForeignKey
     */
    protected $foreigns = array();

    /**
     * Constructor.
     *
     * @param \MwbExporter\Model\Base $parent
     * @param \SimpleXMLElement $node
     */
    public function __construct(Base $parent = null, $node = null)
    {
        $this->links = new RegistryHolder();
        parent::__construct($parent, $node);
    }

    protected function init()
    {
        $this->getDocument()->addLog(sprintf('Processing column "%s.%s"', $this->getTable()->getRawTableName(), $this->getColumnName()));
        // iterate on links to other wb objects
        foreach ($this->node->xpath("link") as $key => $node) {
            $attributes         = $node->attributes();
            $key                = (string) $attributes['key'];
            $this->links->set((string) $attributes['key'], (string) $node[0]);
        }
    }

    protected function hasParameters()
    {
        return true;
    }

    /**
     * Get the table owner.
     *
     * @return \MwbExporter\Model\Table
     */
    public function getTable()
    {
        return $this->getParent()->getParent();
    }

    /**
     * Get raw column name.
     *
     * @return string
     */
    public function getColumnName()
    {
        return $this->getName();
    }

    /**
     * Get beautified column name aka CamelCased column name.
     *
     * @return string
     */
    public function getBeautifiedColumnName()
    {
        return $this->beautify($this->getColumnName());
    }

    /**
     * Get column type, either by its simpleType or userType.
     *
     * @return string
     */
    public function getColumnType()
    {
        if (!($type = $this->links->get('simpleType'))) {
            $type = $this->links->get('userType');
        }

        return $type;
    }

    /**
     * Set the column as primary key
     */
    public function markAsPrimary()
    {
        $this->isPrimary = true;
    }

    /**
     * Set the column as unique
     */
    public function markAsUnique()
    {
        $this->isUnique = true;
    }

    /**
     * Set the local foreign key.
     *
     * @param \MwbExporter\Model\ForeignKey $foreign
     */
    public function markAsLocalReference(ForeignKey $foreign)
    {
        $this->locals[$foreign->getId()] = $foreign;
    }

    /**
     * Add foreign key reference.
     *
     * @param \MwbExporter\Model\ForeignKey $foreign
     */
    public function markAsForeignReference(ForeignKey $foreign)
    {
        $this->foreigns[$foreign->getId()] = $foreign;
    }

    /**
     * Get local foreign keys reference.
     *
     * @return array \MwbExporter\Model\ForeignKey
     */
    public function getLocalForeignKeys()
    {
        return $this->locals;
    }

    /**
     * Get foreign keys reference.
     *
     * @return array \MwbExporter\Model\ForeignKey
     */
    public function getForeignKeys()
    {
        return $this->foreigns;
    }

    /**
     * Is column has one to many relation.
     *
     * @return bool
     */
    public function hasOneToManyRelation()
    {
        foreach ($this->foreigns as $foreign) {
            if ($foreign->isManyToOne()) {
                return true;
            }
        }

        return false;
    }

    /**
     * return true if the column is a primary key
     *
     * @return boolean
     */
    public function isPrimary()
    {
        return $this->isPrimary;
    }

    /**
     * Is column not null (aka. required).
     *
     * @return boolean
     */
    public function isNotNull()
    {
        return 1 == $this->parameters->get('isNotNull') ? true : false;
    }

    /**
     * Is column auto increment.
     *
     * @return boolean
     */
    public function isAutoIncrement()
    {
        return 1 == $this->parameters->get('autoIncrement') ? true : false;
    }

    /**
     * Is the field an unsigned value
     * 
     * @return boolean
     */
    public function isUnsigned()
    {
        $flags = $this->parameters->get('flags');
        if (is_array($flags)) {
            return array_key_exists('UNSIGNED', array_flip($flags));
        }
        return false;
    }

    /**
     * Get column default value.
     *
     * @return string
     */
    public function getDefaultValue()
    {
        if (1 != $this->parameters->get('defaultValueIsNull')) {
            if (($defaultValue = trim($this->parameters->get('defaultValue'), '\'"')) && ('NULL' != $defaultValue)) {
                return $defaultValue;
            }
        }
    }

    /**
     * Get column length.
     *
     * @return int
     */
    public function getLength()
    {
        return $this->parameters->get('length');
    }
}
