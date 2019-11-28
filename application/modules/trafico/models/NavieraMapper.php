<?php

class Trafico_Model_NavieraMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_Naviera();
    }

    public function get($id) {
        try {
            $sql = $this->_db_table->select()
                    ->where("id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " : " . $e->getMessage());
        }
    }

    public function obtener($idAduana) {
        try {
            $sql = $this->_db_table->select()
                    ->where("idAduana = ?", $idAduana)
                    ->where("activo = 1")
                    ->order("nombre ASC");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " : " . $e->getMessage());
        }
    }

    public function obtenerPorAduana($patente, $aduana) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("n" => "trafico_naviera"), array("id", "nombre"))
                    ->joinLeft(array("a" => "trafico_aduanas"), "a.id = n.idAduana", array(""))
                    ->where("a.patente = ?", $patente)
                    ->where("a.aduana = ?", $aduana)
                    ->where("n.activo = 1")
                    ->order("n.nombre ASC");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " : " . $e->getMessage());
        }
    }

    public function buscar($idAduana, $nombre) {
        try {
            $sql = $this->_db_table->select()
                    ->where("idAduana = ?", $idAduana)
                    ->where("nombre = ?", $nombre)
                    ->where("activo = 1");
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " : " . $e->getMessage());
        }
    }

    public function agregar($data) {
        try {
            $stmt = $this->_db_table->insert($data);
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " : " . $e->getMessage());
        }
    }

    public function desactivar($idAlmacen, $idAduana) {
        try {
            $stmt = $this->_db_table->update(array("activo" => 0), array("id = ?" => $idAlmacen, "idAduana = ?" => $idAduana));
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . " : " . $e->getMessage());
        }
    }

}
