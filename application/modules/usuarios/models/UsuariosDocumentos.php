<?php

class Usuarios_Model_UsuariosDocumentos {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Usuarios_Model_DbTable_UsuariosDocumentos();
    }

    public function agregar($arr) {
        try {
            $stmt = $this->_db_table->insert($arr);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function actualizar($idUsuario, $arr) {
        try {
            $stmt = $this->_db_table->update($arr, array("idUsuario = ?" => $idUsuario));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function verificar($idUsuario) {
        try {
            $sql = $this->_db_table->select()
                    ->where("idUsuario = ?", $idUsuario);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function obtener($idUsuario) {
        try {
            $sql = $this->_db_table->select()
                    ->where("idUsuario = ?", $idUsuario);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
