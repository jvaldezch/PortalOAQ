<?php

class Archivo_Model_DocumentosFiscalMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Archivo_Model_DbTable_DocumentosFiscal();
    }

    public function rgex($filename) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("id"))
                    ->where("rgex LIKE ?", substr($filename, 0, 3));
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return (int) $stmt->id;
            }
            return 999;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        } catch (Zend_Exception $ex) {
            throw new Exception("Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }
    
    public function obtenerTodos() {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("*"));
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        } catch (Zend_Exception $ex) {
            throw new Exception("Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }
    
    public function descripcionArchivo($id) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("*"))
                    ->where("id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->nombre;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        } catch (Zend_Exception $ex) {
            throw new Exception("Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

}
