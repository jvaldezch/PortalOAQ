<?php

class Trafico_Model_RfcConsultaMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_RfcConsulta();
    }

    public function obtener($idCliente) {
        $sql = $this->_db_table->select()
                ->where('idCliente = ?', $idCliente);
        $stmt = $this->_db_table->fetchAll($sql);
        if ($stmt) {
            $data = array();
            foreach ($stmt->toArray() as $item) {
                if ($item["tipo"] == 'cove') {
                    $data["cove"][$item["id"]] = $item["rfc"];
                } elseif ($item["tipo"] == 'edoc') {
                    $data["edoc"][$item["id"]] = $item["rfc"];
                }
            }
            return $data;
        }
        return false;
    }

    public function rfcCove($idCliente) {

    }

    public function rfcEdocument($idCliente) {
        $sql = $this->_db_table->select()
                ->from($this->_db_table, array("rfc"))
                ->where("idCliente = ?", $idCliente)
                ->where("tipo = 'edoc'");
        $stmt = $this->_db_table->fetchRow($sql);
        if ($stmt) {
            $data = $stmt->toArray();
            return $data["rfc"];
        }
        return null;
    }

    public function verificarRfcEdocument($idCliente, $rfc) {
        $sql = $this->_db_table->select()
            ->from($this->_db_table, array("rfc"))
            ->where("idCliente = ?", $idCliente)
            ->where("rfc = ?", $rfc)
            ->where("tipo = 'edoc'");
        $stmt = $this->_db_table->fetchRow($sql);
        if ($stmt) {
            return true;
        }
        return null;
    }

    public function verificarRfcCove($idCliente, $rfc) {
        $sql = $this->_db_table->select()
            ->from($this->_db_table, array("rfc"))
            ->where("idCliente = ?", $idCliente)
            ->where("rfc = ?", $rfc)
            ->where("tipo = 'cove'");
        $stmt = $this->_db_table->fetchRow($sql);
        if ($stmt) {
            return true;
        }
        return null;
    }

    public function agregar($arr) {
        $stmt = $this->_db_table->insert($arr);
        if ($stmt) {
            return true;
        }
        return null;
    }

    public function borrar($id) {
        $stmt = $this->_db_table->delete(array("id = ?" => $id));
        if ($stmt) {
            return true;
        }
        return null;
    }

}
