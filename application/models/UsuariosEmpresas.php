<?php

class Application_Model_UsuariosEmpresas {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Application_Model_DbTable_UsuariosEmpresas();
    }

    public function empresasDeUsuario($idUsuario = null) {
        try {
            $sql = $this->_db_table->select()
                    ->where("idUsuario = ?", $idUsuario);
            $stmt = $this->_db_table->fetchAll($sql);
            if (count($stmt)) {
                $data = array();
                foreach ($stmt->toArray() as $item) {
                    $data[] = $item["idEmpresa"];
                }
                return $data;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function empresas() {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("e" => "empresas"), array("*"))
                    ->order("razonSocial ASC");
            $stmt = $this->_db_table->fetchAll($sql);
            if (count($stmt)) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function selectEmpresasDeUsuario($idUsuario = null) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("r" => "usuarios_empresas"), array("idEmpresa"))
                    ->joinLeft(array("e" => "empresas"), "e.id = r.idEmpresa", array("razonSocial"))
                    ->where("idUsuario = ?", $idUsuario);
            $stmt = $this->_db_table->fetchAll($sql);
            if (count($stmt)) {
                $data = array("" => "---");
                foreach ($stmt->toArray() as $item) {
                    $data[$item["idEmpresa"]] = $item["razonSocial"];
                }
                return $data;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
