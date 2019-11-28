<?php

class Zend_View_Helper_MenuIcono extends Zend_View_Helper_Abstract {

    public function menuIcono($link, $titulo) {
        $mppr = new Principal_Model_MenuIcono();        
        return $mppr->icono($link, $titulo);
    }

}
