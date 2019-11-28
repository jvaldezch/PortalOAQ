<?php

class Vucem_Model_VucemPermisosMapper {

    protected $_db_table;

    function __construct() {
        $this->_db_table = new Vucem_Model_DbTable_VucemPermisos();
    }

    public function obtenerAduanas($idUsuario, $patente) {
        try {
            $select = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array('p' => 'vucem_permisos'), array('*'))
                    ->joinLeft(array('a' => 'aduanas'), "p.patente = a.patente AND p.aduana = a.aduana", array('ubicacion'))
                    ->where('idusuario = ?', $idUsuario)
                    ->where('rfc = ?', $patente);
            if (($result = $this->_db_table->fetchAll($select))) {
                return $result->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function obtenerPermisosAduanas($idUsuario, $patente) {
        try {
            $select = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array('p' => 'vucem_permisos'), array('*'))
                    ->joinInner(array('a' => 'trafico_aduanas'), "p.patente = a.patente AND p.aduana = a.aduana", array('nombre AS ubicacion'))
                    ->where('idusuario = ?', $idUsuario)
                    ->where('rfc = ?', $patente)
                    ->order('a.nombre ASC');
            if (($result = $this->_db_table->fetchAll($select))) {
                return $result->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerTodasAduanas($idUsuario) {
        try {
            $select = $this->_db_table->select()
                    ->where('idusuario = ?', $idUsuario);
            if (($result = $this->_db_table->fetchAll($select))) {
                return $result->toArray();
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerPermisos($idUsuario) {
        try {
            $select = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array('p' => 'vucem_permisos'), array('p.id', 'p.patente', 'p.aduana', 'p.rfc'))
                    ->joinLeft(array('f' => 'vucem_firmante'), "p.rfc = f.rfc AND p.aduana = f.aduana AND p.patente = f.patente AND f.tipo = 'prod'", array('f.razon', 'f.tipo'))
                    ->where('p.idusuario = ?', $idUsuario)
                    ->order('razon ASC');
            if (($result = $this->_db_table->fetchAll($select))) {
                return $result->toArray();
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerPermisosGroup($idUsuario) {
        try {
            $select = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array('p' => 'vucem_permisos'))
                    ->joinLeft(array('f' => 'vucem_firmante'), 'p.rfc = f.rfc AND p.aduana = f.aduana AND p.patente = f.patente', array('f.razon', 'f.rfc'))
                    ->where('p.idusuario = ?', $idUsuario)
                    ->group("f.razon");
            if (($result = $this->_db_table->fetchAll($select))) {
                return $result->toArray();
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function agregarNuevoPermiso($idUsuario, $rfc, $idFirmante, $patente, $aduana) {
        try {
            $data = array(
                'idusuario' => $idUsuario,
                'rfc' => $rfc,
                'idfirmante' => $idFirmante,
                'patente' => $patente,
                'aduana' => $aduana,
            );
            if (($added = $this->_db_table->insert($data))) {
                return true;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function verificarPermiso($idUsuario, $rfc, $idFirmante, $patente, $aduana) {
        try {
            $select = $this->_db_table->select()
                    ->where('idusuario = ?', $idUsuario)
                    ->where('rfc = ?', $rfc)
                    ->where('idfirmante = ?', $idFirmante)
                    ->where('patente = ?', $patente)
                    ->where('aduana = ?', $aduana);
            if (($result = $this->_db_table->fetchRow($select))) {
                return true;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function removerPermiso($idUsuario, $rfc, $idFirmante, $patente, $aduana) {
        try {
            $where = array(
                'idusuario = ?' => $idUsuario,
                'rfc = ?' => $rfc,
                'idfirmante = ?' => $idFirmante,
                'patente = ?' => $patente,
                'aduana = ?' => $aduana,
            );
            if (($result = $this->_db_table->delete($where))) {
                return true;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function borrarPermiso($id) {
        try {
            $where = array(
                'id = ?' => $id,
            );
            if (($result = $this->_db_table->delete($where))) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
