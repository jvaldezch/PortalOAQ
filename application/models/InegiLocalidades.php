<?php

/**
 * Description of UserEmails
 *
 * @author Jaime E. Valdez <jvaldezch@gmail.com>
 */
class Application_Model_InegiLocalidades {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Application_Model_DbTable_InegiLocalidades();
    }

    public function obtenerTodos($idMunicipio) {
        try {
            $select = $this->_db_table->select()
                    ->where("municipio_id = ?", $idMunicipio)
                    ->where("nombre NOT LIKE 'NINGUNO%'")
                    ->order("nombre ASC");
            if (($result = $this->_db_table->fetchAll($select))) {
                return $result->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function datos($id) {
        try {
            $select = $this->_db_table->select()
                    ->where("id = ?", $id);
            if (($result = $this->_db_table->fetchRow($select))) {
                return $result->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
