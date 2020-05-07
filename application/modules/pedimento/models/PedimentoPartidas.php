<?php

class Pedimento_Model_PedimentoPartidas
{

    protected $_db_table;

    public function __construct()
    {
        $this->_db_table = new Pedimento_Model_DbTable_PedimentoPartidas();
    }

    public function borrarTodo($idPedimento)
    {
        $stmt = $this->_db_table->delete(array("idPedimento" => $idPedimento));
        if ($stmt) {
            return true;
        }
        return null;
    }

    public function obtener($idPedimento)
    {
        $sql = $this->_db_table->select()
            ->where("idPedimento = ?", $idPedimento);
        $stmt = $this->_db_table->fetchAll($sql);
        if ($stmt) {
            return $stmt->toArray();
        }
        return null;
    }

    public function total($idPedimento)
    {
        $sql = $this->_db_table->select()
            ->from($this->_db_table, array("count(*) AS total"))
            ->where("idPedimento = ?", $idPedimento);
        $stmt = $this->_db_table->fetchRow($sql);
        if ($stmt) {
            return $stmt['total'];
        }
        return null;
    }

    public function agregar($arr)
    {
        $stmt = $this->_db_table->insert($arr);
        if ($stmt) {
            return $stmt;
        }
        return null;
    }
}
