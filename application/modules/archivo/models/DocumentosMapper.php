<?php

class Archivo_Model_DocumentosMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Archivo_Model_DbTable_Documentos();
    }

    public function getAll() {
        try {
            $sql = $this->_db_table->select()
                    ->from('documentos', array('id', new Zend_Db_Expr("CASE WHEN LENGTH(nombre) > 70 THEN CONCAT(LEFT(nombre,70),' ...') ELSE nombre  END AS nombre"), 'activo'))
                    ->where('visible = 1')
                    ->where('activo = 1')
                    ->order('Nombre ASC');
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function obtenerTodos() {
        try {
            $sql = $this->_db_table->select()
                    ->from('documentos', array('id', new Zend_Db_Expr("CASE WHEN LENGTH(nombre) > 70 THEN CONCAT(LEFT(nombre,70),' ...') ELSE nombre  END AS nombre"), 'activo'))
                    ->order('Nombre ASC');
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function getAllEdocs() {
        try {
            $sql = $this->_db_table->select()
                    ->from('documentos', array('id', new Zend_Db_Expr("CASE WHEN LENGTH(nombre) > 70 THEN CONCAT(LEFT(nombre,70),' ...') ELSE nombre  END AS nombre"), 'activo'))
                    ->where('activo = ?', 1)
                    ->order('id ASC')
                    ->where('id > 99');
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function obtener() {
        try {
            $sql = $this->_db_table->select()
                    ->from('documentos', array('*'))
                    ->where('visible = 1')
                    ->where('activo = 1')
                    ->order('Nombre ASC');
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    public function getAllNormal() {
        try {
            $sql = $this->_db_table->select()
                    ->from('documentos', array('id', new Zend_Db_Expr("CASE WHEN LENGTH(nombre) > 70 THEN CONCAT(LEFT(nombre,70),' ...') ELSE nombre  END AS nombre"), 'activo'))
                    ->where('visible = 1')
                    ->where('activo = 1')
                    ->where('id < 168')
                    ->order('Nombre ASC');
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function getAllEdocument() {
        try {
            $sql = $this->_db_table->select()
                    ->from('documentos', array('id', 'nombre', 'activo'))
                    ->where('visible = 1')
                    ->where('activo = 1')
                    ->where('id >= 168')
                    ->order('id ASC');
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function tipoDocumento($id) {
        try {
            $sql = $this->_db_table->select()
                    ->from("documentos", array('nombre'))
                    ->where('id = ?', $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt["nombre"];
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function tipoDocumentoEdoc($id) {
        try {
            $sql = $this->_db_table->select()
                    ->from("documentos", array('nombre', 'id'))
                    ->where('id = ?', $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt["id"] . ' - ' . $stmt["nombre"];
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

}
