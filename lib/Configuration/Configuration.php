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

use MwbExporter\Helper\DocBlock;

abstract class Configuration
{
    /**
     * @var \MwbExporter\Configuration\Configurations
     */
    protected $parent;

    /**
     * @var array
     */
    protected $metadata = [];

    /**
     * @var array
     */
    protected $depends = [];

    /**
     * @var array
     */
    protected $choices = [];

    /**
     * @var string
     */
    protected $category;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @var mixed
     */
    protected $defaultValue;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->initialize();
        $this->buildMetadata();
        $this->configure();
    }

    /**
     * Do initialization.
     *
     * @return void
     */
    protected function initialize()
    {
    }

    /**
     * Do configuration.
     *
     * @return void
     */
    protected function configure()
    {
        $this->value = $this->defaultValue;
    }

    /**
     * Build metadata from doc block.
     *
     * @return void
     */
    protected function buildMetadata()
    {
        $reflection = new \ReflectionClass(get_class($this));
        if ($docBlock = DocBlock::create($reflection->getDocComment())) {
            if (count($tags = $docBlock->getNamedTags('config'))) {
                $keys = explode('|', $tags[0]['data']);
                if (count($keys)) {
                    $this->metadata['key'] = array_shift($keys);
                }
                if (count($keys)) {
                    $this->metadata['aliases'] = $keys;
                }
            }
            if (count($tags = $docBlock->getNamedTags('label'))) {
                $this->metadata['label'] = $tags[0]['data'];
            }
            if ($description = $docBlock->getRawDescription()) {
                if (count($lines = $docBlock->splitOnBlank(explode("\n", $description)))) {
                    $firstPara = array_shift($lines);
                    $extraPara = [];
                    foreach ($lines as $para) {
                        $extraPara[] = implode("\n", $para);
                    }
                    $this->metadata['help'] = implode("\n", $firstPara);
                    if (count($extraPara)) {
                        $this->metadata['usage'] = implode("\n\n", $extraPara);
                    }
                }
            }
        }
    }

    /**
     * Get configuration parent.
     *
     * @return \MwbExporter\Configuration\Configurations
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set configuration parent.
     *
     * @param \MwbExporter\Configuration\Configurations $parent
     * @return \MwbExporter\Configuration\Configuration
     */
    public function setParent(Configurations $parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get configuration key.
     *
     * The key used to store the key in configuration file.
     *
     * @return string
     */
    public function getKey()
    {
        return isset($this->metadata['key']) ? $this->metadata['key'] : null;
    }

    /**
     * Get configuration aliases.
     *
     * @return array
     */
    public function getAliases()
    {
        return isset($this->metadata['aliases']) ? $this->metadata['aliases'] : [];
    }

    /**
     * Get configuration label.
     *
     * The label used to show the question while prompting for configuration value.
     *
     * @return string
     */
    public function getLabel()
    {
        return isset($this->metadata['label']) ? $this->metadata['label'] : null;
    }

    /**
     * Get configuration help.
     *
     * The help used to show the description of configuration.
     *
     * @return string
     */
    public function getHelp()
    {
        return isset($this->metadata['help']) ? $this->metadata['help'] : null;
    }

    /**
     * Get configuration usage.
     *
     * An optional configuration usage example.
     *
     * @return string
     */
    public function getUsage()
    {
        return isset($this->metadata['usage']) ? $this->metadata['usage'] : null;
    }

    /**
     * Get configuration category.
     *
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Get configuration choices.
     *
     * @return array
     */
    public function getChoices()
    {
        return $this->choices;
    }

    /**
     * Is configuration enabled.
     *
     * @return bool
     */
    public function isEnabled()
    {
        if (count($this->depends)) {
            foreach ($this->depends as $depend => $value) {
                $config = $this->parent->get($depend);
                if (null === $depend) {
                    throw new \RuntimeException(sprintf('Configuration %s depends on unknown configuration %s!', get_class($this), $depend));
                }
                if ($config->getValue() !== $value) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Set and validate value.
     *
     * @param mixed $value
     * @throws \RuntimeException
     */
    public function setValue($value)
    {
        if (count($this->choices) && !in_array($value, $this->choices)) {
            throw new \RuntimeException(sprintf('%s: invalid value %s, valid values are %s!', $this->getKey(), $value, implode(', ', $this->choices)));
        }
        $this->value = $value;
    }

    /**
     * Get configuration value.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set configuration default value.
     *
     * @param mixed $defaultValue
     * @return \MwbExporter\Configuration\Configuration
     */
    public function setDefaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue;

        return $this;
    }

    /**
     * Get configuration default value.
     *
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }
}
