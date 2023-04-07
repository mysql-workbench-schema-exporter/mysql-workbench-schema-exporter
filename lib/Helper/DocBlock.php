<?php

/*
 * The MIT License
 *
 * Copyright (c) 2023 Toha <tohenk@yahoo.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do
 * so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace MwbExporter\Helper;

class DocBlock
{
    public const PARSE_DESC = 0;
    public const PARSE_TAGS = 1;

    /**
     * @var string
     */
    protected $content;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $rawDescription;

    /**
     * @var array
     */
    protected $tags = [];

    /**
     * Constructor.
     *
     * @param string $content  Doc block content
     */
    public function __construct($content)
    {
        $this->content = $this->clean($content);
        $this->parse();
    }

    /**
     * Clean doc comment.
     *
     * @param string $str  The doc comment
     * @return string
     */
    protected function clean($str)
    {
        $lines = [];
        foreach (explode("\n", $str) as $line) {
            // clean leading space and eol
            $line = rtrim(ltrim($line), "\r");
            // ignore opening and ending
            if (in_array($line, ['/**', '*/'])) {
                continue;
            }
            // only process line begin with *
            if ('*' === substr($line, 0, 1)) {
                $line = substr($line, 1);
                // remove one space
                if (' ' === substr($line, 0, 1)) {
                    $line = substr($line, 1);
                }
                $lines[] = $line;
            }
        }

        return implode("\n", $lines);
    }

    protected function parse()
    {
        $this->description = null;
        $this->tags = [];
        if ($this->content) {
            $lines = explode("\n", $this->content);
            $parsing = static::PARSE_DESC;
            while (true) {
                if ($this->isEof($lines)) {
                    break;
                }
                switch ($parsing) {
                    case static::PARSE_DESC:
                        $this->parseDescription($lines);
                        $parsing = static::PARSE_TAGS;
                        break;
                    case static::PARSE_TAGS:
                        $this->parseTags($lines);
                        break;
                }
            }
        }

        return $this;
    }

    /**
     * Parse comment description.
     *
     * @param array $lines  Comment lines
     */
    protected function parseDescription(&$lines = [])
    {
        $result = [];
        $raw = [];
        $str = null;
        while (true) {
            if ($this->isEof($lines) || $this->isTag($lines[0])) {
                break;
            }
            // skip blanks
            while (true) {
                if ($this->isEof($lines) || !$this->isBlank($lines[0])) {
                    break;
                }
                array_shift($lines);
                $raw[] = '';
            }
            // collect lines
            while (true) {
                if ($this->isEof($lines) || $this->isBlank($lines[0]) || $this->isTag($lines[0])) {
                    break;
                }
                $line = array_shift($lines);
                $raw[] = $line;
                $line = rtrim($line);
                if (null === $str) {
                    $str = $line;
                } else {
                    $str .= " ".$line;
                }
            }
            // add lines
            if ($this->isEof($lines) || (!$this->isEof($lines) && ($this->isBlank($lines[0]) || $this->isTag($lines[0])))) {
                if (null !== $str) {
                    $result[] = $str;
                    $str = null;
                }
            }
        }
        $this->description = implode("\n", $result);
        $this->rawDescription = rtrim(implode("\n", $raw));

        return $this;
    }

    /**
     * Parse comment tags.
     *
     * @param array $lines  Comment lines
     */
    protected function parseTags(&$lines = [])
    {
        while (true) {
            if ($this->isEof($lines)) {
                break;
            }
            // skip non tag
            while (true) {
                if ($this->isEof($lines) || $this->isTag($lines[0])) {
                    break;
                }
                array_shift($lines);
            }
            // tag data
            $tag = array_shift($lines);
            $tag = trim($tag);
            // next line must be a tag or eof
            while (true) {
                if ($this->isEof($lines) || $this->isTag($lines[0])) {
                    break;
                }
                $line = array_shift($lines);
                $line = trim($line);
                $tag .= " ".$line;
            }
            // process tag data: @tag type $param description
            if (strlen($tag)) {
                $tags = explode(" ", str_replace("\t", " ", $tag), 2);
                $tagName = array_shift($tags);
                $tagData = implode(" ", $tags);
                $this->tags[] = [
                    'name' => $tagName,
                    'data' => $tagData,
                ];
            }
        }

        return $this;
    }

    /**
     * Check if no more line present.
     *
     * @param array $lines  The lines
     * @return boolean
     */
    protected function isEof($lines)
    {
        return 0 === count($lines) ? true : false;
    }

    /**
     * Is line contain tag symbol @.
     *
     * @param string $line  The comment line
     * @return boolean
     */
    protected function isTag($line)
    {
        return '@' === substr($line, 0, 1) ? true : false;
    }

    /**
     * Is line a blank.
     *
     * @param string $line  The comment line
     * @return boolean
     */
    protected function isBlank($line)
    {
        return 0 === strlen($line) ? true : false;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Get brief description.
     *
     * @return string
     */
    public function getBriefDescription()
    {
        if (strlen($this->description)) {
            if (count($lines = explode("\n", $this->description))) {
                return $lines[0];
            }
        }
    }

    /**
     * Get extra description.
     *
     * @return string
     */
    public function getExtraDescription()
    {
        if (strlen($this->description)) {
            if (count($lines = explode("\n", $this->description))) {
                array_shift($lines);

                return implode("\n", $lines);
            }
        }
    }

    /**
     * Get the original description.
     *
     * @return string
     */
    public function getRawDescription()
    {
        return $this->rawDescription;
    }

    /**
     * Get all tags.
     *
     * @return array
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Get all named tags.
     *
     * @param string $name  The tag name
     * @return array
     */
    public function getNamedTags($name)
    {
        $tags = [];
        if ('@' !== substr($name, 0, 1)) {
            $name = '@'.$name;
        }
        foreach ($this->tags as $tag) {
            if ($name === $tag['name']) {
                $tags[] = $tag;
            }
        }

        return $tags;
    }

    /**
     * Get param tag data by variable name (excluding $).
     *
     * @param string $key  The variable name
     * @return array
     */
    public function getParamTag($key)
    {
        if (count($params = $this->getNamedTags('param'))) {
            foreach ($params as $param) {
                if (null !== ($data = $this->splitParam($param['data'])) && $key === $data['name']) {
                    return $data;
                }
            }
        }
    }

    /**
     * Split a param tag.
     *
     * @param string $param  Tag data
     * @return array
     */
    public function splitParam($param)
    {
        // Handle the following format:
        // @param string $str String input
        // @param $str String input
        // @param $str
        $pName = null;
        $pType = $this->splitBy($param);
        if ('$' === substr($pType, 0, 1)) {
            $pName = $pType;
            $pType = null;
        }
        if (null === $pName) {
            $pName = $this->splitBy($param);
        }
        // check mandatory variable name
        if ('$' === substr($pName, 0, 1)) {
            return [
                'name' => substr($pName, 1),
                'type' => $pType,
                'desc' => $param,
            ];
        }
    }

    /**
     * Split text by delimeter.
     *
     * @param string $str  The input text
     * @param string $delimeter  The delimeter
     * @return string
     */
    protected function splitBy(&$str, $delimeter = ' ')
    {
        $result = null;
        if (false !== ($pos = strpos($str, $delimeter))) {
            $result = substr($str, 0, $pos);
            $str = trim(substr($str, $pos));
        } else {
            $result = $str;
            $str = null;
        }

        return $result;
    }

    /**
     * Split on blank lines.
     *
     * @param array $lines
     * @return array
     */
    public function splitOnBlank($lines)
    {
        $result = [];
        $group = [];
        for ($i = 0, $n = count($lines) - 1; $i <= $n; $i++) {
            if ($lines[$i] === '' || $i === $n) {
                if ($i === $n) {
                    $group[] = $lines[$i];
                }
                if (count($group)) {
                    $result[] = $group;
                }
                $group = [];
            } else {
                $group[] = $lines[$i];
            }
        }

        return $result;
    }

    /**
     * Create doc block.
     *
     * @param string $comment  The doc comment
     * @return \MwbExporter\Helper\DocBlock
     */
    public static function create($comment)
    {
        if (strlen($comment)) {
            return new self($comment);
        }
    }
}
