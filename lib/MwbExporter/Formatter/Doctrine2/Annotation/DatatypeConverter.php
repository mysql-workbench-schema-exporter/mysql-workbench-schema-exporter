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

namespace MwbExporter\Formatter\Doctrine2\Annotation;

use MwbExporter\Core\Model\Column;
use MwbExporter\Formatter\Doctrine2\DatatypeConverter as Base;

class DatatypeConverter extends Base
{
    public function getType(Column $column, $baseOnly = false)
    {
        $type = $this->getMappedType($column);
        $return = '"' . $type . '"';
        if (($scale = $column->getConfigValue('scale')) && ($scale != -1) && ($precision = $column->getConfigValue('precision')) && ($precision != -1)) {
            $return .= ', scale=' . $scale . ', precision=' . $precision;
        }
        if (($length = $column->getConfigValue('length')) && ($length != -1) && $type == 'string') {
            $return .= ', length=' . $length;
        }

        return $return;
    }
}
