<?php

class Zend_View_Helper_TipoArchivo extends Zend_View_Helper_Abstract {

    public function tipoArchivo($id) {
        $mapper = new Rrhh_Model_DocumentosEmpleados();
        return $mapper->descripcionArchivo($id);
    }

}
