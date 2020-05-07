<?php

class Pedimento_Model_PedimentoDetalle
{

    protected $_db_table;

    public function __construct()
    {
        $this->_db_table = new Pedimento_Model_DbTable_PedimentoDetalle();
    }

    public function obtener($idPedimento)
    {
        $sql = $this->_db_table->select()
            ->where("idTrafico = ?", $idPedimento);
        $stmt = $this->_db_table->fetchRow($sql);
        if ($stmt) {
            return $stmt->toArray();
        }
        return null;
    }
}
