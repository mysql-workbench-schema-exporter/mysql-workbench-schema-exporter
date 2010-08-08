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

namespace MwbExporter\Helper;

class Pluralizer extends \MwbExporter\Core\Helper\WordTransform
{
    public static function pluralize($word)
    {
        if($tmpWord = \MwbExporter\Helper\SpecialWordList::getPluralOf($word)){
            return ucfirst($tmpWord);
        }

        if (    self::wordEndsWith($word, 'ch')
             or self::wordEndsWith($word, 'sh')
             or self::wordEndsWith($word, 'ss')
             or self::wordEndsWith($word, 'zz')
             or self::wordEndsWith($word, 'o') ) {

            //append "es"
            $word = $word . 'es';

        // if table name ends with "y"
        } elseif ( self::wordEndsWith($word,  'y') ) {
            // replace "y" with "ies" ("country" -> "countries", "hobby" -> "hobbies", ...)
            $word = self::stripWordEnd($word, 'y') . 'ies';

        } elseif ( self::wordEndsWith($word, 'ff') ) {
            // to prevent single f's to manipulate ff's
            $word = $word . 's';

        } elseif ( self::wordEndsWith($word, 'f') ) {
            // replace "f" by an "ves" ("leaf" -> "leaves", "half" -> "halves", ...)
            $word = self::stripWordEnd($word, 'f') . 'ves';

        } else {
            // append "s" ("games", "referees", "monkeys", ...)
            $word = $word . 's';
        }

        return $word;
    }

    public static function wordIsPlural($word)
    {
        // check if plural is in special word list
        if( \MwbExporter\Helper\SpecialWordList::getSingularOf($word)){
            return true;
        }
        return strlen($word) > 1 && self::wordEndsWith($word, 's') && !self::wordEndsWith($word, 'ss') && !self::wordEndsWith($word, 'us');
    }

}