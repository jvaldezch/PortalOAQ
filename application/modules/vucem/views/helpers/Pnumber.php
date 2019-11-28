<?php

class Zend_View_Helper_Pnumber extends Zend_View_Helper_Abstract {

    public function pnumber($value) {
        return number_format(ceil($value), 0, '', '');
    }

}
