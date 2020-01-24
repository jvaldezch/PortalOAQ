<?php

class Bodega_Model_Entradas {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Bodega_Model_DbTable_Entradas();
    }

    public function buscarEntrada($idBodega, $referencia) {
        $sql = $this->_db_table->select()
                ->from($this->_db_table, array("*"))
                ->where("idBodega = ?", $idBodega)
                ->where("referencia = ?", $referencia)
                ->where("estatus <> 4");
        $stmt = $this->_db_table->fetchRow($sql);
        if ($stmt) {
            return $stmt;
        }
        return null;
    }

    public function obtenerPorId($id) {
        $sql = $this->_db_table->select()
            ->from($this->_db_table, array("*"))
            ->where("id = ?", $id);
        $stmt = $this->_db_table->fetchRow($sql);
        if ($stmt) {
            $arr = $stmt->toArray();
            return $arr;
        }
        return null;
    }

    public function actualizarEntrada($id, $arr) {
        $stmt = $this->_db_table->update($arr, array("id = ?" => $id));
        if ($stmt) {
            return true;
        }
        return null;
    }

}
