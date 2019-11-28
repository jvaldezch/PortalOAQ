<?php

class Archivo_Model_RepositorioPermalinks {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Archivo_Model_DbTable_RepositorioPermalinks();
    }

    public function buscarPermalink($permalink) {
        try {
            $sql = $this->_db_table->select()
                    ->where("uri = ?", $permalink);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }
    
    public function verificar($idRepositorio) {
        try {
            $sql = $this->_db_table->select()
                    ->where("idRepositorio = ?", $idRepositorio);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->uri;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }
    
    public function verificarIdTrafico($idTrafico) {
        try {
            $sql = $this->_db_table->select()
                    ->where("idTrafico = ?", $idTrafico);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->uri;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function agregar($idRepositorio, $uri, $usuario) {
        try {
            $arr = array(
                "idRepositorio" => $idRepositorio,
                "uri" => $uri,
                "creado" => date("Y-m-d H:i:s"),
                "creadoPor" => $usuario,
            );
            $stmt = $this->_db_table->insert($arr);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function agregarIdTrafico($idTrafico, $uri, $usuario) {
        try {
            $arr = array(
                "idTrafico" => $idTrafico,
                "uri" => $uri,
                "creado" => date("Y-m-d H:i:s"),
                "creadoPor" => $usuario,
            );
            $stmt = $this->_db_table->insert($arr);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

}
