<?php

class Automatizacion_Model_ArchivosValidacionDirectorios {

    protected $_dbTable;

    function __construct() {
        $this->_dbTable = new Automatizacion_Model_DbTable_ArchivosValidacionDirectorios();
    }

    /**
     * 
     * @return type
     * @throws Exception
     */
    public function fetchAll() {
        try {
            $stmt = $this->_dbTable->fetchAll(
                    $this->_dbTable->select()
                            ->where("activo = 1")
                            ->order("orden ASC")
            );
            if (0 == count($stmt)) {
                return;
            }
            return $stmt->toArray();
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        } catch (Exception $ex) {
            throw new Exception("Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    /**
     * 
     * @param int $patente
     * @param int $aduana
     * @return type
     * @throws Exception
     */
    public function obtener($patente, $aduana) {
        try {
            $stmt = $this->_dbTable->fetchRow(
                    $this->_dbTable->select()
                            ->where("patente = ?", $patente)
                            ->where("aduana = ?", $aduana)
                            ->where("activo = 1")
                            ->order("orden ASC")
            );
            if (0 == count($stmt)) {
                return;
            }
            return $stmt->toArray();
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        } catch (Exception $ex) {
            throw new Exception("Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

}
