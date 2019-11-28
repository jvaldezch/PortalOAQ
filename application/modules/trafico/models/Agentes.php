<?php

class Trafico_Model_Agentes {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_Agentes();
    }

    public function todos() {
        try {
            $sql = $this->_db_table->select()
                    ->where("activo = 1")
                    ->order("patente ASC");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

    public function obtener($id) {
        try {
            $sql = $this->_db_table->select()
                    ->where("id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

    public function verificar($rfc) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("id"))
                    ->where("rfc = ?", $rfc);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

    public function agregar($rfc, $patente, $nombre) {
        try {
            $arr = array(
                "rfc" => $rfc,
                "patente" => $patente,
                "nombre" => $nombre,
                "creado" => date("Y-m-d H:i:s"),
                "activo" => 1,
            );
            $stmt = $this->_db_table->insert($arr);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

    public function actualizar($id, $rfc, $patente, $nombre) {
        try {
            $arr = array(
                "rfc" => $rfc,
                "patente" => $patente,
                "nombre" => $nombre,
                "activo" => 1,
            );
            $stmt = $this->_db_table->update($arr, array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

}
