<?php

class Trafico_Model_TransporteMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_Transporte();
    }

    public function obtener($idAduana) {
        try {
            $sql = $this->_db_table->select()
                    ->where('idAduana = ?', $idAduana)
                    ->where('activo = 1');
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " >> " . $e->getMessage());
        }
    }

    public function buscar($idAduana, $nombre) {
        try {
            $sql = $this->_db_table->select()
                    ->where("idAduana = ?", $idAduana)
                    ->where("nombre = ?", $nombre)
                    ->where('activo = 1');
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " >> " . $e->getMessage());
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
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " >> " . $e->getMessage());
        }
    }

    public function desactivar($idAlmacen, $idAduana) {
        try {
            $stmt = $this->_db_table->update(array('activo' => 0), array('id = ?' => $idAlmacen, 'idAduana = ?' => $idAduana));
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " >> " . $e->getMessage());
        }
    }

}
