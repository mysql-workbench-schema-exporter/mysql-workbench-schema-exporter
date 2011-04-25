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

    public function getId()
    {
        return $this->id;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getComment()
    {
        return isset($this->config['comment']) ? trim($this->config['comment']) : '';
    }

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

    public function debug()
    {
        return $this->data->asXML();
    }

    public function getParent()
    {
        return $this->parent;
    }

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