<?php

class Archivo_Model_DbTable_ImagenesAgencia extends Zend_Db_Table_Abstract {

    protected $_name = "imagenes_agencia";

    public function __construct() {
        $this->_db = Zend_Registry::get("digitex");
        parent::__construct();
    }

}
