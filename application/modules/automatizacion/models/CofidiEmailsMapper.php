<?php

class Automatizacion_Model_CofidiEmailsMapper {

    protected $_dbTable;

    public function setDbTable($dbTable) {
        if (is_string($dbTable)) {
            $dbTable = new $dbTable();
        }
        if (!$dbTable instanceof Zend_Db_Table_Abstract) {
            throw new Exception('Invalid table data gateway provided');
        }
        $this->_dbTable = $dbTable;
        return $this;
    }

    public function getDbTable() {
        if (null === $this->_dbTable) {
            $this->setDbTable('Automatizacion_Model_DbTable_CofidiEmails');
        }
        return $this->_dbTable;
    }

    public function findAll($idCofidi) {
        try {
            $select = $this->getDbTable()->select()
                    ->where("estatus = 1")
                    ->where("idCofidi = ?", $idCofidi);
            $result = $this->getDbTable()->fetchAll($select);
            if (0 == count($result)) {
                return;
            }
            return $result->toArray();
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("Zend DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        } catch (Exception $ex) {
            throw new Exception("Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

}
