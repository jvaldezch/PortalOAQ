<?php

class Trafico_Model_TraficosTmp
{

    protected $_db_table;

    public function __construct()
    {
        $this->_db_table = new Trafico_Model_DbTable_TraficosTmp();
    }

    public function verificar($idAduana, $pedimento)
    {
        $sql = $this->_db_table->select()
            ->from($this->_db_table, array("id"))
            ->where("idAduana = ?", $idAduana)
            ->where("pedimento = ?", $pedimento);
        $stmt = $this->_db_table->fetchRow($sql);
        if ($stmt) {
            return $stmt["id"];
        }
        return null;
    }

    public function obtener($usuario)
    {
        $sql = $this->_db_table->select()
            ->setIntegrityCheck(false)
            ->from(array("t" => "traficos_tmp"), array("*"))
            ->joinLeft(array("c" => "trafico_clientes"), "t.idCliente = c.id", array("nombre"))
            ->where("t.usuario = ?", $usuario);
        $stmt = $this->_db_table->fetchAll($sql);
        if ($stmt) {
            return $stmt->toArray();
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

    public function actualizar($id, $arr)
    {
        $stmt = $this->_db_table->update($arr, array("id = ?" => $id));
        if ($stmt) {
            return true;
        }
        return null;
    }

    public function borrar($id)
    {
        $stmt = $this->_db_table->delete(array("id = ?" => $id));
        if ($stmt) {
            return true;
        }
        return null;
    }

    public function seleccionar($id)
    {
        $sql = $this->_db_table->select()
            ->setIntegrityCheck(false)
            ->from(array("t" => "traficos_tmp"), array("*"))
            ->joinLeft(array("c" => "trafico_clientes"), "t.idCliente = c.id", array("nombre"))
            ->where("t.id = ?", $id);
        $stmt = $this->_db_table->fetchRow($sql);
        if ($stmt) {
            return $stmt->toArray();
        }
        return null;
    }

}
