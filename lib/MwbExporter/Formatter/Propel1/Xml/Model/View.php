<?php
/*
 * The MIT License
 *
 * Copyright (c) 2010 Johannes Mueller <circus2(at)web.de>
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

namespace MwbExporter\Formatter\Propel1\Xml\Model;

use MwbExporter\FormatterInterface;

use MwbExporter\Model\View as BaseView;
use MwbExporter\Writer\WriterInterface;
use MwbExporter\Formatter\Propel1\Xml\Formatter;

class View extends BaseView
{
    /**
     * Write document as generated code.
     *
     * @param \MwbExporter\Writer\WriterInterface $writer
     * @return \MwbExporter\Formatter\Propel1\Xml\Model\Table
     */
    public function write(WriterInterface $writer)
    {
        if (!$this->isExternal()) {
            $writer->indent();
            $this->writeView($writer);
            $writer->outdent();
        }

        return $this;
    }

    public function writeView(WriterInterface $writer)
    {
        $namespace = $this->getDocument()->getConfig()->get(Formatter::CFG_NAMESPACE);

        $writer->write('<table name="%s" phpName="%s" namespace="%s">', $this->getRawTableName(), $this->getModelName(), $namespace);
        $writer->indent();
        $this->getColumns()->write($writer);
        $writer->outdent();
        $writer->write('</table>');

        return $this;
    }
}
