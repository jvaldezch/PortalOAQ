<?php

class Zend_View_Helper_Number extends Zend_View_Helper_Abstract
{
    public function number($value)
    {
        return number_format($value, 3, '.', ',');        
    }
    
}
