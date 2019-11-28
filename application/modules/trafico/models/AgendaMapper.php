<?php

class Trafico_Model_AgendaMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_Agenda();
    }

    public function obtenerContactosTrafico($idTrafico, $tipo) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array('t' => 'traficos'), array())
                    ->joinLeft(array('a' => 'trafico_agenda'), "a.idCliente = t.idCliente", array('nombre','email'))
                    ->where('t.id = ?', $idTrafico)
                    ->where('a.tipo = ?', $tipo)
                    ->where('a.notificacion = 1');
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " >> " . $e->getMessage());
        }
    }
    
    public function obtenerContactosComentario($idComentario, $tipo) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array('c' => 'trafico_comentarios'), array())
                    ->joinLeft(array('t' => 'traficos'), "t.id = c.idTrafico", array())
                    ->joinLeft(array('a' => 'trafico_agenda'), "a.idCliente = t.idCliente", array('nombre','email'))
                    ->where('c.id = ?', $idComentario)
                    ->where('a.tipo = ?', $tipo)
                    ->where('a.notificacion = 1');
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " >> " . $e->getMessage());
        }
    }

}
