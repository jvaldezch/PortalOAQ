<?php

/**
 * Description of UserEmails
 *
 * @author Jaime E. Valdez <jvaldezch@gmail.com>
 */
class Application_Model_MensajesFijos {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Application_Model_DbTable_MensajesFijos();
    }

    public function obtenerMensajes($idAduana = null) {
        try {
            $sql = $this->_db_table->select()                    
                    ->where("activo = 1")
                    ->order(array("orden ASC", "mensaje ASC"));
            if (isset($idAduana)) {
                $sql->where("idAduana = ?", $idAduana);
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if (0 == count($stmt)) {
                return;
            }
            return $stmt->toArray();
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtener($id) {
        try {
            $sql = $this->_db_table->select()
                    ->where("id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if (0 == count($stmt)) {
                return;
            }
            return $stmt->mensaje;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
