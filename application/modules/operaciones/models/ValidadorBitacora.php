<?php

class Operaciones_Model_ValidadorBitacora {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Operaciones_Model_DbTable_ValidadorBitacora();
    }

    public function obtenerUltimo($id) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array('mensaje', 'creado'))
                    ->where('idArchivo = ?', $id)
                    ->order('creado DESC');
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerTodos($id) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("mensaje", "creado"))
                    ->where('idArchivo = ?', $id)
                    ->order('creado DESC');
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function agregar($id, $mensaje) {
        try {
            $stmt = $this->_db_table->insert(array('idArchivo' => $id, 'mensaje' => $mensaje, 'creado' => date('Y-m-d H:i:s')));
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
