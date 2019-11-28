<?php

class Administracion_Model_DocumentosPolizasMapper {

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
            $this->setDbTable('Administracion_Model_DbTable_DocumentosPolizas');
        }
        return $this->_dbTable;
    }

    public function fetchAll() {
        try {
            $result = $this->getDbTable()->fetchAll(
                    $this->getDbTable()->select()
            );
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

    public function tipoPoliza($id) {
        try {
            $result = $this->getDbTable()->fetchRow(
                    $this->getDbTable()->select()
                            ->where('id = ?', $id)
            );
            if (0 == count($result)) {
                return;
            }
            return $result["tipoPoliza"];
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("Zend DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        } catch (Exception $ex) {
            throw new Exception("Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

}
