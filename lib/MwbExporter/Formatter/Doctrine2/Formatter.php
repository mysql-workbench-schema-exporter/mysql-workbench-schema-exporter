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

namespace MwbExporter\Formatter\Doctrine2;

use MwbExporter\Formatter\Formatter as BaseFormatter;
use MwbExporter\Validator\ChoiceValidator;
use MwbExporter\Validator\Validator;

abstract class Formatter extends BaseFormatter
{
    const CFG_BUNDLE_NAMESPACE               = 'bundleNamespace';
    const CFG_ENTITY_NAMESPACE               = 'entityNamespace';
    const CFG_REPOSITORY_NAMESPACE           = 'repositoryNamespace';
    const CFG_AUTOMATIC_REPOSITORY           = 'useAutomaticRepository';
    const CFG_SKIP_COLUMN_WITH_RELATION      = 'skipColumnWithRelation';
    const CFG_RELATED_VAR_NAME_FORMAT        = 'relatedVarNameFormat';
    const CFG_NULLABLE_ATTRIBUTE             = 'nullableAttribute';
    const CFG_GENERATED_VALUE_STRATEGY       = 'generatedValueStrategy';
    const CFG_DEFAULT_CASCADE                = 'defaultCascade';

    const NULLABLE_AUTO                      = 'auto';
    const NULLABLE_ALWAYS                    = 'always';

    const GENERATED_VALUE_AUTO               = 'auto';
    const GENERATED_VALUE_IDENTITY           = 'identity';
    const GENERATED_VALUE_SEQUENCE           = 'sequence';
    const GENERATED_VALUE_TABLE              = 'table';
    const GENERATED_VALUE_NONE               = 'none';

    const CASCADE_OPTION_PERSIST             = 'persist';
    const CASCADE_OPTION_REMOVE              = 'remove';
    const CASCADE_OPTION_MERGE               = 'merge';
    const CASCADE_OPTION_DETACH              = 'detach';
    const CASCADE_OPTION_ALL                 = 'all';
    const CASCADE_OPTION_REFRESH             = 'refresh';

    protected function init()
    {
        parent::init();
        $this->addConfigurations(array(
            static::CFG_BUNDLE_NAMESPACE              => '',
            static::CFG_ENTITY_NAMESPACE              => '',
            static::CFG_REPOSITORY_NAMESPACE          => '',
            static::CFG_AUTOMATIC_REPOSITORY          => true,
            static::CFG_SKIP_COLUMN_WITH_RELATION     => false,
            static::CFG_RELATED_VAR_NAME_FORMAT       => '%name%%related%',
            static::CFG_NULLABLE_ATTRIBUTE            => static::NULLABLE_AUTO,
            static::CFG_GENERATED_VALUE_STRATEGY      => static::GENERATED_VALUE_AUTO,
            static::CFG_DEFAULT_CASCADE               => false,
        ));
        $this->addValidators(array(
            static::CFG_NULLABLE_ATTRIBUTE            => new ChoiceValidator(array(static::NULLABLE_AUTO, static::NULLABLE_ALWAYS)),
            static::CFG_GENERATED_VALUE_STRATEGY      => new ChoiceValidator(array(
                static::GENERATED_VALUE_AUTO,
                static::GENERATED_VALUE_IDENTITY,
                static::GENERATED_VALUE_SEQUENCE,
                static::GENERATED_VALUE_TABLE,
                static::GENERATED_VALUE_NONE,
            )),
            static::CFG_DEFAULT_CASCADE               => new ChoiceValidator(array(
                static::CASCADE_OPTION_PERSIST,
                static::CASCADE_OPTION_REMOVE,
                static::CASCADE_OPTION_DETACH,
                static::CASCADE_OPTION_MERGE,
                static::CASCADE_OPTION_ALL,
                static::CASCADE_OPTION_REFRESH,
                false
            )),
        ));
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Formatter\Formatter::createDatatypeConverter()
     */
    protected function createDatatypeConverter()
    {
        return new DatatypeConverter();
    }

    /**
     * Get owning side of relation.
     *
     * @param array $relation
     * @param \MwbExporter\Model\ForeignKey $mappedRelation
     * @return boolean
     */
    public function isOwningSide($relation, &$mappedRelation)
    {
        $mappedRelation = $relation['reference']->getOwningTable()->getRelationToTable($relation['refTable']->getRawTableName());

        // user can hint which side is the owning side (set d:owningSide on the foreign key)
        if ($relation['reference']->parseComment('owningSide') === 'true') {
            return true;
        }
        if ($mappedRelation->parseComment('owningSide') === 'true') {
            return false;
        }

        // if no owning side is defined, use one side randomly as owning side (the one where the column id is lower)
        return $relation['reference']->getLocal()->getId() < $mappedRelation->getLocal()->getId();
    }

    /**
     * get the cascade option as array. Only returns values allowed by Doctrine.
     *
     * @param $cascadeValue string cascade options separated by comma
     * @return array array with the values or null, if no cascade values are available
     */
    public function getCascadeOption($cascadeValue)
    {
        $defaultCascade = $this->getRegistry()->config->get(static::CFG_DEFAULT_CASCADE);
        if (empty($cascadeValue) && !empty($defaultCascade)) {
            return [$defaultCascade];
        }

        /** @var Validator $validator */
        $validator = $this->getRegistry()->validator->get(static::CFG_DEFAULT_CASCADE);

        $cascadeValue = array_map('strtolower', array_map('trim', explode(',', $cascadeValue)));
        $cascadeValue = array_intersect($cascadeValue, $validator->getChoices());
        $cascadeValue = array_filter($cascadeValue);
        if(empty($cascadeValue)) {
            return null;
        }

        return $cascadeValue;
    }

    /**
     * Parse order option.
     *
     * @param string $sortValue
     * @return array
     */
    public function getOrderOption($sortValue)
    {
        $orders = array();
        if ($sortValue = trim($sortValue)) {
            $lines = array_map('trim', explode("\n", $sortValue));
            foreach ($lines as $line) {
                if (count($values = array_map('trim', explode(',', $line)))) {
                    $column = $values[0];
                    $order = (count($values) > 1) ? strtoupper($values[1]) : null;
                    if (!in_array($order, array('ASC', 'DESC'))) {
                        $order = 'ASC';
                    }
                    $orders[$column] = $order;
                }
            }
        }

        return $orders;
    }

    /**
     * get the fetch option for a relation
     *
     * @param $fetchValue string fetch option as given in comment for foreign key
     * @return string valid fetch value or null
     */
    public function getFetchOption($fetchValue)
    {
        if ($fetchValue) {
            $fetchValue = strtoupper($fetchValue);
            if (in_array($fetchValue, array('EAGER', 'LAZY', 'EXTRA_LAZY'))) {
                return $fetchValue;
            }
        }
    }

    /**
     * get the a boolean option for a relation
     *
     * @param $booleanValue string boolean option (true or false)
     * @return boolean or null, if booleanValue was invalid
     */
    public function getBooleanOption($booleanValue)
    {
        if ($booleanValue) {
            switch (strtolower($booleanValue)) {
                case 'true':
                    return true;

                case 'false':
                    return false;
            }
        }
    }

    /**
     * get the onDelete rule. this will set the database level ON DELETE and can be set
     * to CASCADE or SET NULL. Do not confuse this with the Doctrine-level cascade rules.
     */
    public function getDeleteRule($deleteRule)
    {
        if ($deleteRule == 'NO ACTION' || $deleteRule == 'RESTRICT' || empty($deleteRule)) {
            // NO ACTION acts the same as RESTRICT,
            // RESTRICT is the default
            // http://dev.mysql.com/doc/refman/5.5/en/innodb-foreign-key-constraints.html
            $deleteRule = null;
        }

        return $deleteRule;
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Formatter\Formatter::getCommentTagPrefixes()
     */
    protected function getCommentTagPrefixes()
    {
        return array_merge(parent::getCommentTagPrefixes(), array('d', 'doctrine'));
    }
}