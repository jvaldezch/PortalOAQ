<?php

class Zend_View_Helper_Currency extends Zend_View_Helper_Abstract {

    public function currency($value) {
        return '<span style="float:left;">$</span> ' . number_format($value, 2, '.', ',');
    }

}
