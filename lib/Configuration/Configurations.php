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

class Configurations
{
    /**
     * Hold all configurations.
     *
     * @var array<\MwbExporter\Configuration\Configuration>
     */
    protected $items = [];

    /**
     * Add a configuration.
     *
     * @param \MwbExporter\Configuration\Configuration $config
     * @return \MwbExporter\Configuration\Configurations
     */
    public function add(Configuration $config)
    {
        if (in_array($config, $this->items)) {
            throw new \InvalidArgumentException(sprintf('Configuration %s already exist!', get_class($config)));
        }
        $config->setParent($this);
        $this->items[] = $config;

        return $this;
    }

    /**
     * Get configuration by class name, key, or aliases.
     *
     * @param string $key
     * @return \MwbExporter\Configuration\Configuration
     */
    public function get($key)
    {
        foreach ($this->items as $config) {
            if (get_class($config) === $key || $config->getKey() === $key || in_array($key, $config->getAliases())) {
                return $config;
            }
        }
    }

    /**
     * Get all configurations.
     *
     * @return array<\MwbExporter\Configuration\Configuration>
     */
    public function getAll()
    {
        return $this->items;
    }

    /**
     * Get all configurations grouped by categories.
     *
     * @return array<\MwbExporter\Configuration\Configuration>
     */
    public function getCategories()
    {
        $items = [];
        foreach ($this->items as $config) {
            if (!isset($items[$config->getCategory()])) {
                $items[$config->getCategory()] = [];
            }
            $items[$config->getCategory()][] = $config;
        }

        return $items;
    }

    /**
     * Validate confiugration keys.
     *
     * @param array $configurations
     * @return array
     */
    public function validate(&$configurations)
    {
        $result = [];
        foreach (array_keys($configurations) as $key) {
            $config = $this->get($key);
            if (!$config) {
                $result[] = $key;
                unset($configurations[$key]);
            }
        }

        return $result;
    }

    /**
     * Merge configurations.
     *
     * @param array $configurations
     * @throws \RuntimeException
     * @return \MwbExporter\Formatter\Formatter
     */
    public function merge($configurations)
    {
        foreach ($configurations as $key => $value) {
            $config = $this->get($key);
            if (!$config) {
                throw new \RuntimeException(sprintf('Unknown configuration key %s.', $key));
            }
            $config->setValue($value);
        }

        return $this;
    }

    /**
     * Export configurations.
     *
     * @return array
     */
    public function export()
    {
        $result = [];
        foreach ($this->getCategories() as $category => $configurations) {
            /** @var \MwbExporter\Configuration\Configuration $config */
            foreach ($configurations as $config) {
                $result[$config->getKey()] = $config->getValue();
            }
        }

        return $result;
    }
}
