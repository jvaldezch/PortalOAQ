<?php

class Zend_View_Helper_BreakdownField extends Zend_View_Helper_Abstract
{
    public function breakdownField(&$array, $key)
    {
        if(isset($array[$key])) {
            return '<td style="text-align: right">$ '.number_format($array['subtotal_'.$key], 2, '.', ',').'</td>
                    <td style="text-align: right">$ '.number_format($array['iva_'.$key], 2, '.', ',').'</td>
                    <td style="text-align: right">$ '.number_format($array[$key], 2, '.', ',').'</td>';
        } else {
            return '<td style="text-align: center"><span style="color: #ddd">n/d</span></td>        
            <td style="text-align: center"><span style="color: #ddd">n/d</span></td>
            <td style="text-align: center"><span style="color: #ddd">n/d</span></td>';
        }
    }
}
