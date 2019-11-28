<?php

class Trafico_Model_TraficoOtrosMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_TraficoOtros();
    }

    public function verificar($idTrafico) {
        try {
            $sql = $this->_db_table->select()
                    ->where('idTrafico = ?', $idTrafico);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt["id"];
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

    public function obtener($idTrafico) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array('idServicio', 'idObservacion', 'idTransportista', 'comentarios'))
                    ->where('idTrafico = ?', $idTrafico);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

    public function obtenerWs($idTrafico) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("o" => "trafico_otros"), array('o.idServicio', 'o.idObservacion', 'o.idTransportista', 'o.comentarios'))
                    ->joinLeft(array("p" => "traficos"), "o.idTrafico = p.id", array("p.referencia"))
                    ->joinLeft(array("t" => "trafico_transportistas"), "o.idTransportista = t.id AND t.idAduana = p.idAduana", array("t.nombre as nombreTransportista"))
                    ->joinLeft(array("s" => "trafico_servicios"), "o.idServicio = s.id AND s.idAduana = p.idAduana", array("s.servicio as nombreServicio"))
                    ->joinLeft(array("ob" => "trafico_observaciones"), "o.idObservacion = ob.id", array("ob.observacion"))
                    ->where('o.idTrafico = ?', $idTrafico);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

    public function agregar($idTrafico, $idServicio, $idObservacion, $idTransportista, $comentarios, $idUsuario) {
        try {
            $data = array(
                'idTrafico' => $idTrafico,
                'idServicio' => $idServicio,
                'idObservacion' => $idObservacion,
                'idTransportista' => $idTransportista,
                'idUsuario' => $idUsuario,
                'comentarios' => $comentarios,
                'creado' => date('Y-m-d H:i:s'),
            );
            $inserted = $this->_db_table->insert($data);
            if ($inserted) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

    public function actualizar($id, $idServicio, $idObservacion, $idTransportista, $comentarios, $idUsuario) {
        try {
            $data = array(
                'idServicio' => $idServicio,
                'idObservacion' => $idObservacion,
                'idTransportista' => $idTransportista,
                'comentarios' => $comentarios,
                'actualizado' => date('Y-m-d H:i:s'),
                'idUsuarioModif' => $idUsuario,
            );
            $where = array(
                'id = ?' => $id,
            );
            $stmt = $this->_db_table->update($data, $where);
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

}
