<?php

class Zend_View_Helper_Valor extends Zend_View_Helper_Abstract {

    public function valor($value) {
        return ($value !== null) ? $value : "";
    }

}
