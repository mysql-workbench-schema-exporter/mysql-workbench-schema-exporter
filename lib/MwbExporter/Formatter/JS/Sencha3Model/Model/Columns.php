<?php
/*
 *  The MIT License
 *
 *  Copyright (c) 2012 Allan Sun <sunajia@gmail.com>
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

namespace MwbExporter\Formatter\JS\Sencha3Model\Model;

class Columns extends \MwbExporter\Core\Model\Columns
{
    /**
     *
     * @param SimpleXMLElement $data
     * @param type $parent
     */
    public function __construct($data, $parent)
    {
        parent::__construct($data, $parent);
    }

    /**
     *
     * @param int $in Default indenation
     */
    public function displayFields($in=0){
      $return = array();
      $return[] = $this->indentation($in+0).'fields:[';
      foreach($this->columns as $column){
        $return[] = $this->indentation($in+1).$column->displayField();
      }
      $return[count($return)-1] = substr($return[count($return)-1], 0, strlen($return[count($return)])-1); // Remove the last comma from the loop above
      $return[] = $this->indentation($in+0).']';
      return implode("\n",$return);
    }

    /**
     *
     * @param int $in Default indenation
     */
    public function displayColumns($in=0){
      $return = array();
      $return[] = $this->indentation($in+0).'columns:[';
      foreach($this->columns as $column){
        $return[] = $this->indentation($in+1).$column->displayColumn();
      }
      $return[count($return)-1] = substr($return[count($return)-1], 0, strlen($return[count($return)])-1); // Remove the last comma from the loop above
      $return[] = $this->indentation($in+0).']';
      return implode("\n",$return);
    }

    /**
     *
     * @param int $in Default indenation
     */
    public function displayFormItems($in=0){
      $return = array();
      $return[] = $this->indentation($in+0).'formItems:[';
      foreach($this->columns as $column){
        $return[] = $column->displayFormItem($in+1);
      }
      $return[count($return)-1] = substr($return[count($return)-1], 0, strlen($return[count($return)])-1); // Remove the last comma from the loop above
      $return[] = $this->indentation($in+0).']';
      return implode("\n",$return);
    }

    /**
     *
     * @return string
     */
    public function display()
    {
        $return = array();

        foreach($this->columns as $column){
            $return[] = $column->display();
        }

        return implode("\n", $return);
    }



    /**
     *
     * @return string
     */
    public function displayArrayCollections()
    {
        $return = array();

        foreach($this->columns as $column){
            if (true == $arrayCollection = $column->displayArrayCollection()){
                $return[] = $arrayCollection;
            }
        }

        return implode("\n", $return);
    }



    /**
     *
     * @return string
     */
    public function displayGetterAndSetter()
    {
        $return = array();

        foreach($this->columns as $column){
            $return[] = $column->displayGetterAndSetter();
        }

        return implode("\n", $return);
    }
}