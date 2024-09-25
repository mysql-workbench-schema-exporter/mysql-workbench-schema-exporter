<?php

/*
 * The MIT License
 *
 * Copyright (c) 2023-2024 Toha <tohenk@yahoo.com>
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
 * End of line (EOL) delimiter detemines the end of line in generated files.
 *
 * @author Toha <tohenk@yahoo.com>
 * @config eolDelimiter|eolDelimeter
 * @label End of line delimiter
 */
class EOL extends Configuration
{
    public const WIN = 'win';
    public const UNIX = 'unix';

    /**
     * Configured EOLs.
     *
     * @var array
     */
    protected $eols = [self::WIN => "\r\n", self::UNIX => "\n"];

    protected function initialize()
    {
        $this->category = 'codeGeneration';
        $this->defaultValue = static::WIN;
        $this->choices = [
            static::WIN,
            static::UNIX,
        ];
    }

    public function getEol()
    {
        return $this->eols[$this->getValue()];
    }
}
