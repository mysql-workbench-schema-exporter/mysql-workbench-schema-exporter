<?php
/*
 *  The MIT License
 *
 *  Copyright (c) 2010 Johannes Mueller <circus2(at)web.de>
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is
 *  furnished to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in
 *  all copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

namespace MwbExporter\Formatter\Doctrine2\Annotation\Model;

use MwbExporter\Core\Registry;
use MwbExporter\Core\Model\View as Base;

class View extends Base
{
    protected $ormPrefix = '@';
    protected $namespace = null;

    public function __construct($data, $parent)
    {
        parent::__construct($data, $parent);
    }

    public function display()
    {
        $return = array();
        
        $config              = Registry::get('config');
        $this->ormPrefix     = '@' . ((isset($config['useAnnotationPrefix']) && $config['useAnnotationPrefix']) ? $config['useAnnotationPrefix'] : '');
        $namespace           = $this->getEntityNamespace();
        $repositoryNamespace = isset($config['repositoryNamespace']) && $config['repositoryNamespace'] ? $config['repositoryNamespace'] . '\\' : '';
        
        
        $return[] = '<?php';
        $return[] = '';
        $return[] = sprintf('namespace %s;', $namespace);
        $return[] = '';
        $return[] = '/**';
        $return[] = ' * ' . $this->getNamespace();
        $return[] = ' *';
        $return[] = ' * ' . $this->ormPrefix . 'Entity' . (isset($config['useAutomaticRepository']) && $config['useAutomaticRepository'] ? sprintf('(repositoryClass="%sRepository")', $repositoryNamespace . $this->getModelName()) : '');
        $return[] = ' */';
        $return[] = 'class ' . $this->getModelName();
        $return[] = '{';
        $return[] = $this->columns->display();
        $return[] = $this->displayConstructor();
        $return[] = '}';
        $return[] = '';
        
        return implode("\n", $return);
    }
    
    public function displayConstructor()
    {
        $return = array();
        $return[] = $this->indentation() . 'public function __construct()';
        $return[] = $this->indentation() . '{';
        $return[] = $this->indentation() . '}';
        $return[] = '';

        return implode("\n", $return);
    }
    
    /**
     * Get the entity namespace.
     *
     * @return string
     */
    public function getEntityNamespace()
    {
        if (null === $this->namespace) {
            $config = Registry::get('config');
            if (isset($config['bundleNamespace']) && $config['bundleNamespace']) {
                $this->namespace = $config['bundleNamespace'] . '\\';
            }
            if (isset($config['entityNamespace']) && $config['entityNamespace']) {
                $this->namespace .= $config['entityNamespace'];
            } else {
                $this->namespace .= 'Entity';
            }
        }

        return $this->namespace;
    }
    
    /**
     * Get namespace of a class.
     *
     * @param string $class The class name
     * @return string
     */
    public function getNamespace($class = null)
    {
        return sprintf('%s\%s', $this->getEntityNamespace(), null === $class ? $this->getModelName() : $class);
    }
}