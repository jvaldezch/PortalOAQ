<?php

class Clientes_Model_FtpLinks {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Clientes_Model_DbTable_FtpLinks();
    }

    public function verificar($archivoNombre) {
        try {
            $sql = $this->_db_table->select()
                    ->from(array("f" => "ftp_links"), array("*"))
                    ->where("archivoNombre = ?", $archivoNombre);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function agregar($arr) {
        try {
            $stmt = $this->_db_table->insert($arr);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    public function borrar($id) {
        try {
            $stmt = $this->_db_table->delete(array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function archivosCaducos() {
        try {
            $sql = $this->_db_table->select()
                    ->from(array("f" => "ftp_links"), array("id", "ubicacion"))
                    ->where("TIMESTAMPDIFF(HOUR, creado, NOW()) > 24");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

}
