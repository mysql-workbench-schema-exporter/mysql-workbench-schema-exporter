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

use MwbExporter\Model\Tables as BaseTables;
use MwbExporter\Formatter\Doctrine2\Annotation\Model\Table as Table;
use MwbExporter\Formatter\Doctrine2\Annotation\Model\ExtendedClass as ExtendedClass;
use MwbExporter\Formatter\Doctrine2\Annotation\Formatter;
use MwbExporter\Writer\WriterInterface;

class Tables extends BaseTables {
	
	/**
	 * (non-PHPdoc) 
	 * @see \MwbExporter\Model\Tables::init()
	 */
	public function init()
	{		
		parent::init();

		// Process tables if use base class
		if ($this->getDocument()->getConfig()->get(Formatter::CFG_USE_BASE_CLASS)) {
			foreach ($this->tables as $table) {				
			    // Create the extended class and define it as child of base class
				$table->parameters->set('base_extend', new BaseExtend($this, $table->parameters->get('name')));
			}
		}
	}

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Model\Base::write()
     */
    public function write(WriterInterface $writer)
    {
        parent::write($writer);
        
        // Write the extended classes if use base class
		if ($this->getDocument()->getConfig()->get(Formatter::CFG_USE_BASE_CLASS)) {
    		foreach ($this->tables as $table) {
    		    $table->getBaseExtend()->write($writer);
    		}
		}

        return $this;
    }
}
