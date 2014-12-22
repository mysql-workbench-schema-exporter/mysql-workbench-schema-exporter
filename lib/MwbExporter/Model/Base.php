<?php

/*
 * The MIT License
 *
 * Copyright (c) 2010 Johannes Mueller <circus2(at)web.de>
 * Copyright (c) 2012-2014 Toha <tohenk@yahoo.com>
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

namespace MwbExporter\Model;

use MwbExporter\Registry\Registry;
use MwbExporter\Registry\RegistryHolder;
use MwbExporter\Writer\WriterInterface;
use MwbExporter\Helper\Comment;

abstract class Base
{
    /**
     * @var \MwbExporter\Model\Base
     */
    protected $parent;

    /**
     * @var \SimpleXMLElement
     */
    protected $node;

    /**
     * @var \SimpleXMLElement
     */
    protected $attributes;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var \MwbExporter\Registry\RegistryHolder
     */
    protected $parameters = null;

    /**
     * @var \MwbExporter\Model\Document
     */
    protected $document = null;

    /**
     * Constructor.
     *
     * @param \MwbExporter\Model\Base $parent
     * @param \SimpleXMLElement $node
     */
    public function __construct(Base $parent = null, $node = null)
    {
        $this->parameters = new RegistryHolder();
        $this->parent = $parent;
        $this->configure($node);
    }

    protected function configure($node)
    {
        $this->node = $node;
        if ($this->node) {
            $this->attributes = $node->attributes();
            $this->id = (string) $this->attributes['id'];
            $this->populateParameters();
            $this->init();
            if ($this->id && ($document = $this->getDocument())) {
                $document->getReference()->set($this->id, $this);
            }
        }
    }

    protected function init()
    {
    }

    /**
     * Populate parameters from XML node.
     */
    protected function populateParameters()
    {
        $this->parameters->clear();
        if ($this->hasParameters() && $this->node) {
            foreach ($this->node->value as $key => $node) {
                $attributes = $node->attributes();
                switch ((string) $attributes['type']) {
                    case 'list':
                        $value = array();
                        foreach ($node->children() as $c) {
                            $value[] = (string) $c;
                        }
                        break;

                    case 'int':
                        if (strlen($value = (string) $node[0])) {
                            $value = (int) $value;
                        } else {
                            $value = null;
                        }
                        break;

                    default:
                        $value = (string) $node[0];
                        break;
                }
                $this->parameters->set((string) $attributes['key'], $value);
            }
        }
    }

    /**
     * Is parameters available.
     *
     * @return boolean
     */
    protected function hasParameters()
    {
        return false;
    }

    /**
     * Return the internal ID of the MySQL Workbench object
     * 
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the attributes of the current MySQL Workbench object
     * 
     * @return \SimpleXmlElement
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Returns current MySQL Workbench object
     * 
     * @return \SimpleXmlElement
     */
    public function getNode()
    {
        return $this->node;
    }

    /**
     * Returns the parent object
     * 
     * @return object
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Get name from parameter.
     *
     * @return string
     */
    public function getName()
    {
        return $this->parameters->get('name');
    }

    /**
     * Get parameters holder.
     * 
     * @return \MwbExporter\Registry\RegistryHolder
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Get the document owner.
     *
     * @return \MwbExporter\Model\Document
     */
    public function getDocument()
    {
        if (null === $this->document) {
            $parent = $this->parent;
            while (true) {
                if (!$parent) {
                    break;
                }
                if ($parent->parent) {
                    $parent = $parent->parent;
                } else {
                    break;
                }
            }
            $this->document = $parent;
        }

        return $this->document;
    }

    /**
     * Get document formatter.
     *
     * @return \MwbExporter\Formatter\FormatterInterface
     */
    public function getFormatter()
    {
        return $this->getDocument()->getFormatter();
    }

    /**
     * Get document config.
     *
     * @return \MwbExporter\Registry\RegistryHolder
     */
    public function getConfig()
    {
        return $this->getDocument()->getConfig();
    }

    /**
     * Get document factory.
     *
     * @return \MwbExporter\Registry\RegistryHolder
     */
    public function getFactory()
    {
        return $this->getDocument()->getFactory();
    }

    /**
     * Get document reference.
     *
     * @return \MwbExporter\Registry\RegistryHolder
     */
    public function getReference()
    {
        return $this->getDocument()->getReference();
    }

    /**
     * Filters given comment for embedded code by a given keyword
     * 
     * @param string $needle_raw
     * @param string $comment
     * @return string
     */
    public function parseComment($needle_raw, $comment = null)
    {
        if ($comment === null) {
            $comment = $this->parameters->get('comment');
        }
        $needle_quoted = preg_quote($needle_raw);
        $pattern = sprintf('@\{(%1$s):%2$s\}(.+)\{\/(%1$s):%2$s\}@si', $this->getFormatter()->getCommentTagPrefix(), $needle_quoted);
        if (preg_match($pattern, $comment, $matches) && isset($matches[2])) {
            return $matches[2];
        }
    }

    /**
     * get the comment of this object (without the hints for the exporter)
     *
     * @param boolean $asPhpComment add * infront of the lines and indent according to current indentation level
     * @return string
     */
    protected function getComment($asPhpComment = true)
    {
        $comment = $this->parameters->get('comment');
        // strip hints for mysql-exporter in comments (starting with {d:keyword}
        // or {doctrine:keyword} and ending with {/d:keyword}
        if ($comment = trim(preg_replace(sprintf('/\{(%s):([^\}]+)\}(.+?)\{\/\1:\2\}/si', $this->getFormatter()->getCommentTagPrefix()), '', $comment))) {
            if ($asPhpComment) {
                $comment = implode("\n", Comment::wrap($comment."\n", ' * %s'));
            }

            return $comment;
        }
    }

    /**
     * Returns XML of the current MySQL Workbench object
     * 
     * @return string
     */
    public function debug()
    {
        return $this->node->asXML();
    }

    /**
     * Write document as generated code.
     *
     * @param \MwbExporter\Writer\WriterInterface $writer
     * @return \MwbExporter\Model\Base
     */
    public function write(WriterInterface $writer)
    {
        return $this;
    }

    /**
     * Translate text with object contextual data.
     *
     * @param string $text  The text to translate
     * @param array $vars   The overriden variables
     * @return string
     */
    public function translateVars($text, $vars = array())
    {
        return strtr($text, array_merge($this->getParentVars(), $this->getVars(), $vars));
    }

    /**
     * Get parent variables.
     *
     * @return array
     */
    protected function getParentVars()
    {
        $vars = array();
        $p = $this->getParent();
        while ($p) {
            $vars = array_merge($p->getVars(), $vars);
            $p = $p->getParent();
        }

        return $vars;
    }

    /**
     * Get translate variables. This function is called by translateVars() to
     * translate text and should be overriden in the subclass to match the
     * class requirement.
     *
     * @return array.
     */
    protected function getVars()
    {
        return array();
    }

    /**
     * Get parameter value.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getParameter($key, $default = null)
    {
        return $this->parameters->get($key, $default);
    }

    /**
     * Beautify an underscored_text and change into CamelCaseText.
     *
     * @param string $underscored_text
     * @return string
     */
    public function beautify($underscored_text)
    {
        return ucfirst(preg_replace_callback('@\_(\w)@', function($matches) {
            return ucfirst($matches[1]);
        }, $underscored_text));
    }

    /**
     * Get the value for comparing the model for sorting.
     * Default value taken from the model id.
     *
     * @see \MwbExporter\Model\Base::getId()
     * @return string
     */
    public function getSortValue()
    {
        return $this->getId();
    }
}
