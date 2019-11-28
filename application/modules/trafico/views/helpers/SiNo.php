<?php

class Zend_View_Helper_SiNo extends Zend_View_Helper_Abstract {

    public function siNo($value) {
        return ($value !== null) ? 'SI' : 'NO';
    }

}
