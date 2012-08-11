<?php

/*
 * The MIT License
 *
 * Copyright (c) 2010 Johannes Mueller <circus2(at)web.de>
 * Copyright (c) 2012 Toha <tohenk@yahoo.com>
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

use MwbExporter\Registry;
use MwbExporter\RegistryHolder;
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
     * @var \MwbExporter\RegistryHolder
     */
    protected $parameters = null;

    public function __construct(Base $parent = null, $node)
    {
        $this->parameters = new RegistryHolder();
        $this->parent = $parent;
        $this->node = $node;
        $this->attributes = $node->attributes();
        $this->id = (string) $this->attributes['id'];
        $this->init();
        if ($this->id && ($document = $this->getDocument())) {
            $document->getReference()->set($this->id, $this);
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
     * @return \MwbExporter\RegistryHolder
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
    protected function parseComment($needle_raw, $comment = null)
    {
        if ($comment === null) {
            $comment = $this->parameters->get('comment');
        }
        $needle_quoted = preg_quote($needle_raw);
        $pattern = '@\{(d|doctrine):'.$needle_quoted.'\}(.+)\{\/(d|doctrine):'.$needle_quoted.'\}@si';
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
        $comment = trim(preg_replace('/\{(d|doctrine):([^\}]+)\}(.+?)\{\/\1:\2\}/si', '', $comment));

        if (!$comment) {
            return '';
        }

        if ($asPhpComment) {
            // start the comment with a "*"" and add a " * " after each newline
            $comment = str_replace("\n", "\n * ", $comment);

            // comments are wrapped at 80 chars and will end with a newline
            $comment = ' * ' . wordwrap($comment, 77, "\n * ") . "\n *";
        }

        return $comment;
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
}
