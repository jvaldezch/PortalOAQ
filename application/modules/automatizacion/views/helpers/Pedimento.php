<?php

class Zend_View_Helper_Pedimento extends Zend_View_Helper_Abstract {

    public function pedimento($value) {
        if(strlen($value) == 12) {
            return substr($value, 5, 7);
        }
        return '';
    }

}
