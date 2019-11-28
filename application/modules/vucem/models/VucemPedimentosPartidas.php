<?php

class Vucem_Model_VucemPedimentosPartidas {

    protected $_db_table;

    function __construct() {
        $this->_db_table = new Vucem_Model_DbTable_VucemPedimentosPartidas();
    }

}
