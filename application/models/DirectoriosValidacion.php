<?php

/**
 * Description of UserEmails
 *
 * @author Jaime E. Valdez <jvaldezch@gmail.com>
 */
class Application_Model_DirectoriosValidacion {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Application_Model_DbTable_ValidadorDirectorio();
    }

    public function get($patente, $aduana) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("*"))
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function obtener($patente, $aduana) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("directorio"))
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana)
                    ->where("activo = 1");
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt["directorio"] . DIRECTORY_SEPARATOR . date("Y");
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerDirectorios() {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("d" => "validador_directorio"), array("directorio", "patente", "aduana"))
                    ->joinLeft(array("a" => "trafico_aduanas"), "a.patente = d.patente AND a.aduana = d.aduana", array("nombre", "id"))
                    ->where("d.activo = 1");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
