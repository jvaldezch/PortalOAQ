<?php

/**
 * Description of UserEmails
 *
 * @author Jaime E. Valdez <jvaldezch@gmail.com>
 */
class Application_Model_Aduanas {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Application_Model_DbTable_Aduanas();
    }

    /**
     * 
     * @param int $patente
     * @param int $aduana
     * @return type
     * @throws Exception
     */
    public function verificar($patente, $aduana) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("*"))
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana);
            $stmt = $this->_db_table->fetchRow($sql);
            if (count($stmt)) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    /**
     * 
     * @param int $patente
     * @param int $aduana
     * @param string $nombre
     * @return boolean
     * @throws Exception
     */
    public function agregar($patente, $aduana, $nombre) {
        try {
            
            $stmt = $this->_db_table->insert(array("patente" => $patente, "aduana" => $aduana, "ubicacion" => $nombre));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
