<?php
/*
 *  The MIT License
 *
 *  Copyright (c) 2010 Johannes Mueller <circus2(at)web.de>
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is
 *  furnished to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in
 *  all copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

namespace MwbExporter\Core\Model;

abstract class Base
{
    protected $id;
    protected $attributes;
    protected $data;
    protected $parent;

    public function __construct($data, Base $parent)
    {
        $this->preLoad();

        $this->attributes = $data->attributes();
        $this->data = $data;
        $this->id = (string) $this->attributes['id'];
        $this->parent = $parent;

        $this->postLoad();

        $this->init();
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
     * @return SimpleXmlElement
     */
    public function getAttributes()
    {
        return $this->attributes;
    }
    
    /**
     * Returns an attribute by $key or null
     * 
     * @param String $key
     * @return String
     */
    public function getAttribute($key=null)
    {
        return isset($this->config[$key]) ? $this->config[$key] : null;
    }

    /**
     * Returns current MySQL Workbench object
     * 
     * @return SimpleXmlElement
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Returns the comment of the current MySQL Workbench object
     * 
     * @return string
     */
    public function getComment()
    {
        return isset($this->config['comment']) ? trim($this->config['comment']) : '';
    }

    /**
     * Filters given comment for embedded code by a given keyword
     * 
     * @param string $needle_raw
     * @param string $comment
     * @return string
     */
    protected function parseComment($needle_raw, $comment=null)
    {
        if($comment === null){
            $comment = $this->getComment();
        }

        $needle_quoted = preg_quote($needle_raw);
        $pattern = '@\{(d|doctrine):' . $needle_quoted . '\}(.+)\{\/(d|doctrine):' . $needle_quoted . '\}@si';

        preg_match($pattern, $comment, $matches);
        return isset($matches[2]) ? $matches[2] : false;
    }

    /**
     * Returns XML of the current MySQL Workbench object
     * 
     * @return string
     */
    public function debug()
    {
        return $this->data->asXML();
    }

    /**
     * Returns spaces for Yaml by a given indentation level
     * 
     * @param int $level
     * @return string
     */
    protected function indentation($level = 1)
    {
        $config = \MwbExporter\Core\Registry::get('config');
        if(isset($config['indentation']) && $config['indentation']){
            $indentation = $config['indentation'];
        } else {
            $indentation = 2;
        }
        return str_repeat(' ', $indentation * $level);
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
     * Returns object by MySQL Workbench object ID
     * 
     * @param string $id
     * @return object
     */
    protected function getElementById($id)
    {
        return \MwbExporter\Core\Registry::get($id);
    }

    public function preLoad()
    {}

    public function postLoad()
    {}

    public function init()
    {}
}
