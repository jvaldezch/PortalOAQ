<?php

class Zend_View_Helper_Number6 extends Zend_View_Helper_Abstract {

    public function number6($value) {
        return number_format($value, 6, '.', ',');
    }

}
