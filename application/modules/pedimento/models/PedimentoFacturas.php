<?php

class Pedimento_Model_PedimentoFacturas
{

    protected $_db_table;

    public function __construct()
    {
        $this->_db_table = new Pedimento_Model_DbTable_PedimentoFacturas();
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

    public function obtenerProveedores($idPedimento)
    {
        $sql = $this->_db_table->select()
            ->distinct()
            ->from($this->_db_table, array("idFiscal", "razonSocial", "pais"))
            ->where("idPedimento = ?", $idPedimento);
        $stmt = $this->_db_table->fetchAll($sql);
        if ($stmt) {
            return $stmt->toArray();
        }
        return null;
    }

    public function facturasProveedor($idPedimento, $idFiscal, $razonSocial, $pais)
    {
        $sql = $this->_db_table->select()
            ->distinct()
            ->from($this->_db_table, array("*"))
            ->where("idPedimento = ?", $idPedimento)
            ->where("idFiscal = ?", $idFiscal)
            ->where("razonSocial = ?", $razonSocial)
            ->where("pais = ?", $pais);
        $stmt = $this->_db_table->fetchAll($sql);
        if ($stmt) {
            return $stmt->toArray();
        }
        return null;
    }

    public function datosProveedor($idPedimento, $idFiscal, $razonSocial, $pais)
    {
        $sql = $this->_db_table->select()
            ->distinct()
            ->from($this->_db_table, array("*"))
            ->where("idPedimento = ?", $idPedimento)
            ->where("idFiscal = ?", $idFiscal)
            ->where("razonSocial = ?", $razonSocial)
            ->where("pais = ?", $pais)
            ->limit(1);
        $stmt = $this->_db_table->fetchRow($sql);
        if ($stmt) {
            return $stmt->toArray();
        }
        return null;
    }

}
