<?php

/*
 * The MIT License
 *
 * Copyright (c) 2023 Toha <tohenk@yahoo.com>
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

namespace MwbExporter\Configuration;

/**
 * Include file as header in the generated files. It will be wrapped as a
 * comment by choosen formatter. This configuration useful as for example
 * to include notice to generated files such as license file.
 *
 * @author Toha <tohenk@yahoo.com>
 * @config headerFile
 * @label Include file as header
 */
class Header extends Configuration
{
    protected $header = null;

    protected function initialize()
    {
        $this->category = 'codeGeneration';
        $this->defaultValue = '';
    }

    /**
     * Get header content.
     *
     * @return string
     */
    public function getHeader()
    {
        if (null === $this->header && is_readable($this->getValue())) {
            $this->header = file_get_contents($this->getValue());
        }

        return $this->header;
    }
}
