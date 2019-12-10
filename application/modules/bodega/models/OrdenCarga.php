<?php

class Bodega_Model_OrdenCarga
{

    protected $_db_table;

    public function __construct()
    {
        $this->_db_table = new Bodega_Model_DbTable_OrdenCarga();
    }

    public function agregar($idTrafico, $idUsuario)
    {
        $arr = array(
            "idTrafico" => $idTrafico,
            "idUsuario" => $idUsuario,
            "creado" => date("Y-m-d H:i:s")
        );
        $stmt = $this->_db_table->insert($arr);
        if ($stmt) {
            return $stmt;
        }
        return null;
    }

    public function verificar($idTrafico)
    {
        $sql = $this->_db_table->select()
            ->from($this->_db_table)
            ->where("idTrafico = ?", $idTrafico);
        $stmt = $this->_db_table->fetchRow($sql);
        if ($stmt) {
            return $stmt->id;
        }
        return null;
    }

}
