<?php

/**
 * Description of UserEmails
 *
 * @author Jaime E. Valdez <jvaldezch@gmail.com>
 */
class Application_Model_TipoCambio {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Application_Model_DbTable_TipoCambio();
    }

    public function verificar($fecha) {
        try {
            $sql = $this->_db_table->select()
                    ->where('value = ?', $fecha);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $e) {
            throw new Exception("<b>DB Exception at " . __METHOD__ . "</b>" . $e->getMessage());
        }
    }

    public function agregar($arr) {
        try {
            $stmt = $this->_db_table->insert($arr);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $e) {
            throw new Exception("<b>DB Exception at " . __METHOD__ . "</b>" . $e->getMessage());
        }
    }

    public function obtener($fecha) {
        try {
            $sql = $this->_db_table->select()
                    ->where('value = ?', $fecha);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->today;
            }
            return;
        } catch (Zend_Db_Exception $e) {
            throw new Exception("<b>DB Exception at " . __METHOD__ . "</b>" . $e->getMessage());
        }
    }

}
