<?php

class Dashboard_Model_ComentariosClientes {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Dashboard_Model_DbTable_Comentarios();
    }
    
}
