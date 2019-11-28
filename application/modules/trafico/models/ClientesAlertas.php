<?php

class Trafico_Model_ClientesAlertas {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_ClientesAlertas();
    }

    public function ultimaActividad($idCliente = null) {
        try {
            $sql = $this->_db_table->select()
                    ->order("creado DESC")
                    ->limit(50);
            if (isset($idCliente)) {
                $sql->where("idCliente = ?", $idCliente);
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function agregar($idCliente, $mensaje, $usuario) {
        try {
            $arr = array(
                "idCliente" => $idCliente,
                "mensaje" => $mensaje,
                "usuario" => $usuario,
                "creado" => date("Y-m-d H:i:s")
            );
            $stmt = $this->_db_table->insert($arr);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
