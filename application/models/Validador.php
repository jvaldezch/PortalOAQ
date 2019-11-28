<?php

/**
 * Description of UserEmails
 *
 * @author Jaime E. Valdez <jvaldezch@gmail.com>
 */
class Application_Model_Validador {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Application_Model_DbTable_Validador();
    }

    public function obtener($patente, $aduana) {
        try {
            $select = $this->_db_table->select()
                    ->from($this->_db_table, array('*'))
                    ->where('patente = ?', $patente)
                    ->where('aduana = ?', $aduana)
                    ->where('habilitado = 1');
            $result = $this->_db_table->fetchRow($select);
            if ($result) {
                return $result;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
