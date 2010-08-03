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

/**
 * simple word list to handle exceptions
 * of singularization and pluralization rule
 */

class MwbExporter_Helper_SpecialWordList
{

    protected static $specialWordList = array(
        // ***************************************************
        // **        add special words here
        // ***************************************************
         array('s' => 'cache', 'p' => 'caches')
        ,array('s' => 'status', 'p' => 'statuses') // real plural of status is status
        ,array('s' => 'profile', 'p' => 'profiles')
        ,array('s' => 'address', 'p' => 'addresses')
    );




    /**
     * do not change class MwbExporter_from here
     */
    public static function getPluralOf($singular)
    {
        foreach(self::$specialWordList as $word){
            if($word['s'] === strtolower($singular)){
                return ucfirst($word['p']);
            }
        }
        return false;
    }

    public static function getSingularOf($plural)
    {
        foreach(self::$specialWordList as $word){
            if($word['p'] === strtolower($plural)){
                return ucfirst($word['s']);
            }
        }
        return false;
    }
}

