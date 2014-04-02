<?php

/*
 * The MIT License
 *
 * Copyright (c) 2010 Johannes Mueller <circus2(at)web.de>
 * Copyright (c) 2012-2013 Toha <tohenk@yahoo.com>
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

        return $parent;
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
        $pattern = sprintf('@\{(%1$s):%2$s\}(.+)\{\/(%1$s):%2$s\}@si', $this->getDocument()->getFormatter()->getCommentParserIdentifierPrefix(), $needle_quoted);
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
        if ($comment = trim(preg_replace(sprintf('/\{(%s):([^\}]+)\}(.+?)\{\/\1:\2\}/si', $this->getDocument()->getFormatter()->getCommentParserIdentifierPrefix()), '', $comment))) {
            if ($asPhpComment) {
                // start the comment with a "*"" and add a " * " after each newline
                $comment = str_replace("\n", "\n * ", $comment);
    
                // comments are wrapped at 80 chars and will end with a newline
                $comment = ' * ' . wordwrap($comment, 77, "\n * ") . "\n *";
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
     * @return string
     */
    public function translateVars($text)
    {
        return strtr($text, $this->getVars());
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
}
