<?php

/*
 * The MIT License
 *
 * Copyright (c) 2023-2025 Toha <tohenk@yahoo.com>
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
 * Determines how identifier like table name will be treated for generated
 * entity/model name. Supported identifier strategies are `fix-underscore`
 * which will fix for double underscore to single underscore, and `none` which
 * will do nothing.
 *
 * @author Toha <tohenk@yahoo.com>
 * @config identifierStrategy
 * @label Identifier strategy
 */
class IdentifierStrategy extends Configuration
{
    public const NONE = 'none';
    public const FIX_UNDERSCORE = 'fix-underscore';

    protected function initialize()
    {
        $this->category = 'strategies';
        $this->defaultValue = static::NONE;
        $this->choices = [
            static::NONE,
            static::FIX_UNDERSCORE,
        ];
    }

    public function getIdentifier($identifier)
    {
        if ($this->getValue() === true || $this->getValue() === static::FIX_UNDERSCORE) {
            $identifier = strtr($identifier, ['__' => '_']);
        }

        return $identifier;
    }
}
