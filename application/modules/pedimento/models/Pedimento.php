<?php

class Pedimento_Model_Pedimento {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Pedimento_Model_DbTable_Pedimento();
    }

    public function buscar($idTrafico) {
        $sql = $this->_db_table->select()
            ->where("idTrafico = ?", $idTrafico);
        $stmt = $this->_db_table->fetchRow($sql);
        if ($stmt) {
            return $stmt->toArray();
        }
        return null;
    }

    public function obtener($id) {
        $sql = $this->_db_table->select()
            ->where("id = ?", $id);
        $stmt = $this->_db_table->fetchRow($sql);
        if ($stmt) {
            return $stmt->toArray();
        }
        return null;
    }

    public function actualizar($id, $arr) {
        $stmt = $this->_db_table->update($arr, array("id = ?" => $id));
        if ($stmt) {
            return true;
        }
        return null;
    }

    public function agregar($idTrafico, $usuario) {
        $arr = array(
            "idTrafico" => $idTrafico,
            "creadoPor" => $usuario
        );
        $stmt = $this->_db_table->insert($arr);
        if ($stmt) {
            return $stmt;
        }
        return null;
    }

}
