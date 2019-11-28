<?php

class Zend_View_Helper_Currency extends Zend_View_Helper_Abstract {

    /**
     * 
     * @param string $value
     * @param int $width
     * @return string
     */
    public function currency($value, $width = null) {
        if(isset($width)) {
            return '<div style="width: ' . $width . 'px" class="traffic-currency"><div style="float: left; width: 10%">$</div><div style="text-align: right">' . number_format($value, 2, '.', ',') . '</div></div>';
        } else {
            return '<div style="width: 100%" class="traffic-currency"><div style="float: left; width: 10%">$</div><div style="text-align: right">' . number_format($value, 2, '.', ',') . '</div></div>';
            
        }
    }

}
