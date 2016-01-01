<?php

/*
 * The MIT License
 *
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

namespace MwbExporter\Formatter\Doctrine2\Model;

use MwbExporter\Model\Table as BaseTable;
use MwbExporter\Formatter\Doctrine2\Formatter;
use Doctrine\Common\Inflector\Inflector;

class Table extends BaseTable
{
    /**
     * Get the entity namespace.
     *
     * @param boolean $base wether its for base entities or not
     * @return string
     */
    public function getEntityNamespace($base = false)
    {
        $namespace = '';
        if (($bundleNamespace = $this->parseComment('bundleNamespace')) || ($bundleNamespace = $this->getConfig()->get(Formatter::CFG_BUNDLE_NAMESPACE))) {
            $namespace = $bundleNamespace.'\\';
        }
        
        $namespace .= $this->getConfig()->get($base ? Formatter::CFG_BASE_ENTITY_NAMESPACE : Formatter::CFG_ENTITY_NAMESPACE);

        return $namespace;
    }

    /**
     * Get the repositories namespace.
     *
     * @return string
     */
    public function getRepositoryNamespace()
    {
        $namespace = '';
        if (($bundleNamespace = $this->parseComment('bundleNamespace')) || ($bundleNamespace = $this->getConfig()->get(Formatter::CFG_BUNDLE_NAMESPACE))) {
            $namespace = $bundleNamespace.'\\';
        }
        
        $namespace .= $this->getConfig()->get(Formatter::CFG_REPOSITORY_NAMESPACE);

        return $namespace;
    }

    /**
     * Get namespace of a class.
     *
     * @param string $class The class name
     * @param boolean $absolute wether generate absolute namespace or not
     * @param boolean $base wether its for base entities or not
     * @return string
     */
    public function getNamespace($class = null, $absolute = true, $base = false)
    {
        return sprintf('%s%s\%s', $absolute ? '\\' : '', $this->getEntityNamespace($base), null === $class ? $this->getModelName() : $class);
    }

    /**
     * Get Model Name in FQCN format. If reference namespace is suplied and the entity namespace
     * is equal then relative model name returned instead.
     *
     * @param string $referenceNamespace The reference namespace
     * @param boolean $base wether its for base entities or not
     * @return string
     */
    public function getModelNameAsFQCN($referenceNamespace = null, $base = false, $className = null)
    {
        $namespace = $this->getEntityNamespace($base);
        
        $fqcn = ($namespace == $referenceNamespace) ? false : true;

        return $fqcn ? $namespace.'\\'.$this->getClassName($base, $className) : $this->getClassName($base, $className);
    }

    /**
     * Get the generated class name.
     *
     * @param bool $base
     * @param string $className The class name to format
     * @return string
     */
    protected function getClassName($base = false, $className = null)
    {
        return ($base && $this->getConfig()->get(Formatter::CFG_ENTITY_NAMESPACE) ==  $this->getConfig()->get(Formatter::CFG_BASE_ENTITY_NAMESPACE) ? 'Base' : '').($className?$className:$this->getModelName());
    }

    /**
     * Get lifecycleCallbacks.
     *
     * @return array
     */
    public function getLifecycleCallbacks()
    {
        $result = array();
        if ($lifecycleCallbacks = trim($this->parseComment('lifecycleCallbacks'))) {
            foreach (explode("\n", $lifecycleCallbacks) as $callback) {
                list($method, $handler) = explode(':', $callback, 2);
                $method = lcfirst(trim($method));
                if (!in_array($method, array('postLoad', 'prePersist', 'postPersist', 'preRemove', 'postRemove', 'preUpdate', 'postUpdate'))) {
                    continue;
                }
                if (!isset($result[$method])) {
                    $result[$method] = array();
                }
                $result[$method][] = trim($handler);
            }
        }

        return $result;
    }

    /**
     * Get identifier name formatting.
     *
     * @param string $name  Identifier name
     * @param string $related  Related name
     * @param string $plural  Return plural form
     * @return string
     */
    public function getRelatedVarName($name, $related = null, $plural = false)
    {
        $name = $related ? strtr($this->getConfig()->get(Formatter::CFG_RELATED_VAR_NAME_FORMAT), array('%name%' => $name, '%related%' => $related)) : $name;

        return $plural ? Inflector::pluralize($name) : $name;
    }
}
