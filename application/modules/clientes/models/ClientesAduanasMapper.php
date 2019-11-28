<?php

class Clientes_Model_ClientesAduanasMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Clientes_Model_DbTable_ClientesAduanas();
    }

    public function getServiciosCliente($id, $tipo) {
        try {
            $sql = $this->_db_table->select()
                    ->where('servicio LIKE ?', $tipo)
                    ->where('id_cli = ?', $id)
                    ->where('estatus = 1');
            $stmt = $this->_db_table->fetchAll($sql, array());
            if ($stmt) {
                $arr = array();
                foreach ($stmt as $serv) {
                    $arr[] = array(
                        'url' => $serv['url'],
                        'nombre' => $serv['nombre'],
                    );
                }
                return $arr;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

}
