<?php

class Zend_View_Helper_SingleField extends Zend_View_Helper_Abstract
{
    public function singleField(&$array, $key)
    {
        if(isset($array[$key])) 
            return '$ '.number_format($array[$key], 2, '.', ',');
        else
            return '<span style="color: #ddd">n/d</span>';
    }
    
    public function breakdownField(&$array, $key)
    {
        return '<td><span style="color: #ddd">n/d</span></td>
            <td><span style="color: #ddd">n/d</span></td>
            <td><span style="color: #ddd">n/d</span></td>';
    }
}
