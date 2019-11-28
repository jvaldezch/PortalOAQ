<?php

class Zend_View_Helper_EDoc extends Zend_View_Helper_Abstract
{
    public function eDoc($id)
    {
        $arch = new Archivo_Model_RepositorioMapper();
        if(($result = $arch->verificarEDoc($id))) {
            unset($arch);
            return $result;
        } else {
            unset($arch);
            return '&nbsp;';
        }
    }
}
