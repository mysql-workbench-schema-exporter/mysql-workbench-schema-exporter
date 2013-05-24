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

use MwbExporter\Formatter\Doctrine2\Annotation\Model\Table as BaseTable;
use MwbExporter\Registry\RegistryHolder;
use MwbExporter\Writer\WriterInterface;
use MwbExporter\Formatter\Doctrine2\Annotation\Formatter;

class BaseExtend extends BaseTable {

    public function __construct($parent, $name) {
    	$this->parameters = new RegistryHolder();
    
    	$this->parent = $parent;
        $this->parameters->set('name', $name);
    }


    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Model\Base::write()
     */
    public function write(WriterInterface $writer) {    
    	// don't replace extended class if exist
    	if(!is_file($writer->getStorage()->getFile($this->getTableFileName()))) {
    		 
	        $namespace = $this->getEntityNamespace();  
	        
	        if ($repositoryNamespace = $this->getDocument()->getConfig()->get(Formatter::CFG_REPOSITORY_NAMESPACE)) {
	        	$repositoryNamespace .= '\\';
	        }
        
	        $writer
	            ->open($this->getTableFileName())
	            ->write('<?php')
	            ->write('')
	            ->write('namespace %s;', $namespace)
	            ->write('')
	            ->write('/**')
	            ->write(' * '.$this->getNamespace(null, false))
	            ->write(' *')
	            ->write(' * '.$this->getAnnotation('Entity', array('repositoryClass' => $this->getDocument()->getConfig()->get(Formatter::CFG_AUTOMATIC_REPOSITORY) ? $repositoryNamespace.$this->getModelName().'Repository' : null)))
	            ->write(' */')
	            ->write('class '.$this->getModelName().' extends Base'.$this->getModelName())
	            ->write('{')
	            ->write('')
	            ->write('    ')
	            ->write('')
	            ->write('}')
	        ->close();
        }
    }

}
