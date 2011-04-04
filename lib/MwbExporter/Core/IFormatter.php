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

namespace MwbExporter\Core;

interface IFormatter {

    public function useDatatypeConverter($type, \MwbExporter\Core\Model\Column $column);

    public function visitDocument(Workbench\Document $node);
    public function visitCatalog(Model\Catalog $node);
    public function visitColumn(Model\Column $node);
    public function visitColumns(Model\Columns $node);
    public function visitForeignKey(Model\ForeignKey $node);
    public function visitForeignKeys(Model\ForeignKeys $node);
    public function visitIndex(Model\Index $node);
    public function visitIndices(Model\Indices $node);
    public function visitSchema(Model\Schema $node);
    public function visitSchemas(Model\Schemas $node);
    public function visitTable(Model\Table $node);
    public function visitTables(Model\Tables $node);
    public function visitView(Model\View $node);
    public function visitViews(Model\Views $node);
}