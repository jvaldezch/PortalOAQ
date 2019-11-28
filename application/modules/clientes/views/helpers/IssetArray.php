<?php

class Zend_View_Helper_IssetArray extends Zend_View_Helper_Abstract
{
    public function issetArray(&$array, $key)
    {
        if(isset($array[$key])) {
            return $array[$key];
        } else {
            return '&nbsp;';
        }
    }
}
