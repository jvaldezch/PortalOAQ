<?php

class Trafico_Model_TraficoSolComentarioMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_TraficoSolComentario();
    }

    protected function _throwException($message, $method, Exception $ex) {
        return $message . " at " . $method . " >> " . $ex->getMessage() . " line: " . $ex->getLine() . " info: " . $ex->getCode() . " trace: " . $ex->getTrace();
    }

    public function obtenerTodos($idSolicitud) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array('c' => 'trafico_solcomentario'), array('*'))
                    ->joinLeft(array('u' => 'usuarios'), 'u.usuario = c.creadoPor', array('nombre'))
                    ->where('c.idSolicitud = ?', $idSolicitud)
                    ->order('creado DESC');
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception($this->_throwException("Db Exception found on", __METHOD__, $ex));
        }
    }
    
    public function agregarComentario($data) {
        try {            
            $stmt = $this->_db_table->insert($data);
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception($this->_throwException("Db Exception found on", __METHOD__, $ex));
        }
    }

}
