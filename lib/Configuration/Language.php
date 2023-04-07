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

use Doctrine\Inflector\InflectorFactory;
use Doctrine\Inflector\Language as InflectorLanguage;

/**
 * Language detemines which language used to transform singular and plural words
 * used in certains other schema like Doctrine.
 *
 * @author Toha <tohenk@yahoo.com>
 * @config language
 * @label Select language
 */
class Language extends Configuration
{
    /**
     * @var \Doctrine\Inflector\Inflector
     */
    protected $inflector = null;

    protected function initialize()
    {
        $this->category = 'general';
        $this->defaultValue = InflectorLanguage::ENGLISH;
        $this->choices = [
            InflectorLanguage::ENGLISH,
            InflectorLanguage::FRENCH,
            InflectorLanguage::NORWEGIAN_BOKMAL,
            InflectorLanguage::PORTUGUESE,
            InflectorLanguage::SPANISH,
            InflectorLanguage::TURKISH,
        ];
    }

    /**
     * Get language inflector.
     *
     * @return \Doctrine\Inflector\Inflector
     */
    public function getInflector()
    {
        if (null === $this->inflector) {
            $this->inflector = InflectorFactory::createForLanguage($this->getValue())->build();
        }

        return $this->inflector;
    }

    /**
     * Singularize plural word.
     *
     * @param string $word
     * @return string
     */
    public function singularize($word)
    {
        if ($inflector = $this->getInflector()) {
            $word = $inflector->singularize($word);
        }

        return $word;
    }

    /**
     * Pluralize singular word.
     *
     * @param string $word
     * @return string
     */
    public function pluralize($word)
    {
        if ($inflector = $this->getInflector()) {
            $word = $inflector->pluralize($word);
        }

        return $word;
    }
}
