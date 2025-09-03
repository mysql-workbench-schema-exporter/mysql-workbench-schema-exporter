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
 * Naming strategy detemines how objects, variables, and methods name will be generated.
 *
 * @author Toha <tohenk@yahoo.com>
 * @config namingStrategy
 * @label Naming strategy
 */
class NamingStrategy extends Configuration
{
    public const AS_IS = 'as-is';
    public const CAMEL_CASE = 'camel-case';
    public const PASCAL_CASE = 'pascal-case';

    protected function initialize()
    {
        $this->category = 'strategies';
        $this->defaultValue = static::AS_IS;
        $this->choices = [
            static::AS_IS,
            static::CAMEL_CASE,
            static::PASCAL_CASE,
        ];
    }

    /**
     * Beautify an underscored_text and change into CamelCaseText.
     *
     * @param string $text
     * @return string
     */
    public function beautify($text)
    {
        /** @var \MwbExporter\Configuration\IdentifierStrategy $identifierStrategy */
        $identifierStrategy = $this->getParent()->get(IdentifierStrategy::class);

        return ucfirst(preg_replace_callback('@\_(\w)@', function($matches) {
            return ucfirst($matches[1]);
        }, $identifierStrategy->getIdentifier($text)));
    }

    /**
     * Get name using naming strategy.
     *
     * @param string $name
     * @param string $strategy
     * @return string
     */
    public function getNaming($name, $strategy = null)
    {
        $strategy = $strategy ?: $this->getValue();
        switch ($strategy) {
            case static::AS_IS:
                break;
            case static::CAMEL_CASE:
                $name = lcfirst($this->beautify($name));
                break;
            case static::PASCAL_CASE:
                $name = $this->beautify($name);
                break;
        }

        return $name;
    }
}
