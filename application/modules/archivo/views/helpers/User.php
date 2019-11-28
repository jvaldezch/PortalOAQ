<?php

class Zend_View_Helper_User extends Zend_View_Helper_Abstract {

    public function user($patente, $aduana, $referencia) {
        $model = new Archivo_Model_RepositorioMapper();
        return $model->ulitmaMoficacion($patente, $referencia);
    }

}
