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

use MwbExporter\Helper\Dumper;
use ReflectionClass;

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
     * @param bool $override
     * @throws \RuntimeException
     * @return \MwbExporter\Formatter\Formatter
     */
    public function merge($configurations, $override = false)
    {
        foreach ($configurations as $key => $value) {
            $config = $this->get($key);
            if (!$config) {
                throw new \RuntimeException(sprintf('Unknown configuration key %s.', $key));
            }
            if ($override) {
                $config->setDefaultValue($value);
            } else {
                $config->setValue($value);
            }
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

    /**
     * Describe configuration by class.
     *
     * @param string $class
     * @return array
     */
    public function describe($class)
    {
        $result = [];
        $r = new ReflectionClass($class);
        if ($r->getParentClass()) {
            $result = array_merge($result, $this->describe($r->getParentClass()->getName()));
        }
        $groups = [];
        $names = explode('\\', $r->getNamespaceName());
        while (true) {
            if (count($names) && $names[count($names) - 1] === 'Formatter') {
                array_pop($names);
            } else {
                break;
            }
        }
        $names[] = 'Configuration';
        $ns = implode('\\', $names);
        foreach ($this->items as $config) {
            $rr = new ReflectionClass($config);
            if ($ns === $rr->getNamespaceName()) {
                $groups[] = $config;
            }
        }
        $result[$r->getName()] = $groups;

        return $result;
    }

    /**
     * Dump configurations.
     *
     * @param string $class
     * @param string $format
     * @return string
     */
    public function dump($class, $format)
    {
        $dumper = Dumper::get($format);
        $descriptions = $this->describe($class);
        foreach ($descriptions as $sclass => $configurations) {
            $scope = $sclass::getScope();
            $dumper
                ->addTitle(sprintf('%s Configuration', $scope))
                ->addBlank();
            /** @var \MwbExporter\Configuration\Configuration $config */
            foreach ($configurations as $config) {
                if (count($config->getAliases())) {
                    $dumper->addLine(sprintf(
                        '%s (alias: %s)',
                        $dumper->highlight($config->getKey()),
                        implode(', ', $dumper->highlightValues($config->getAliases()))
                    ));
                } else {
                    $dumper->addLine($dumper->highlight($config->getKey()));
                }
                if (!$help = $config->getHelp()) {
                    $help = 'No description available';
                }
                $dumper
                    ->addSubLine($help)
                    ->addBlank();
                if ($usage = $config->getUsage()) {
                    $dumper
                        ->addSubLine($usage, true)
                        ->addBlank();
                }
                if (count($config->getChoices())) {
                    $dumper
                        ->addSubLine(sprintf('Valid values: %s', implode(', ', $dumper->highlightValues($config->getChoices()))))
                        ->addBlank();
                }
                $defaultValue = $config->getDefaultValue();
                if ('' === $defaultValue) {
                    $defaultValue = 'blank';
                } elseif (true === $defaultValue) {
                    $defaultValue = 'true';
                } elseif (false === $defaultValue) {
                    $defaultValue = 'false';
                } elseif (is_array($defaultValue)) {
                    $defaultValue = sprintf('[%s]', implode(', ', $dumper->highlightValues($defaultValue)));
                }
                $dumper
                    ->addSubLine(sprintf('Default value: %s', $dumper->highlight($defaultValue)))
                    ->addBlank();
            }
        }
        $dumper
            ->addBlank();

        return implode("\n", $dumper->getLines());
    }
}
