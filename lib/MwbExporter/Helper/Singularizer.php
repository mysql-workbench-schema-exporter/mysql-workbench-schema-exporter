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

class Singularizer extends \MwbExporter\Core\Helper\WordTransform
{
    public static function singularize($word)
    {
        if($tmpWord = \MwbExporter\Helper\SpecialWordList::getSingularOf($word)){
            return ucfirst($tmpWord);
        }
        
        $word = self::stripWordEnd($word, 's');

        // we can't just strip the s without looking at the remaining English plural endings
        // see http://en.wikipedia.org/wiki/English_plural

        // if the table name ends with "e" ("coache", "hashe", "addresse", "buzze", "heroe", ...)
        if (    self::wordEndsWith($word, 'che')
             or self::wordEndsWith($word, 'she')
             or self::wordEndsWith($word, 'sse')
             or self::wordEndsWith($word, 'zze')
             or self::wordEndsWith($word, 'oe') ) {

            // strip an "e", too
            $word = self::stripWordEnd($word, 'e');

        // if table name ends with "ie"
        } elseif ( self::wordEndsWith($word, 'ie') ) {
            // replace "ie" by a "y" ("countrie" -> "country", "hobbie" -> "hobby", ...)
            $word = self::stripWordEnd($word, 'ie') . 'y';

        } elseif ( self::wordEndsWith($word, 've') ) {
            // replace "ve" by an "f" ("calve" -> "calf", "leave" -> "leaf", ...)
            $word = self::stripWordEnd($word, 've') . 'f';

            // does *not* work for certain words ("knive" -> "knif", "stave" -> "staf", ...)
        } else {
            // do nothing ("game", "referee", "monkey", ...)

            // note: table names like "Caches" can't be handled correctly because of the "che" rule above,
            // that word however basically stems from French and might be considered a special case anyway
            // also collective names like "Personnel", "Cast" (caution: SQL keyword!) can't be singularized
        }
        
        return $word;
    }
    
    public static function wordIsSingular($word)
    {
        return !( \MwbExporter\Helper\Pluralizer::wordIsPlural($word) );
    }
}