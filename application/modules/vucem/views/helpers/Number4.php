<?php

class Zend_View_Helper_Number4 extends Zend_View_Helper_Abstract {

    public function number4($value) {
        return number_format($value, 4, '.', ',');
    }

}
