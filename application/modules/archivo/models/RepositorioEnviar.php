<?php

class Archivo_Model_RepositorioEnviar {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Archivo_Model_DbTable_RepositorioEnviar();
    }
    
    public function buscar($patente, $referencia) {
        try {
            $select = $this->_db_table->select()
                    ->from(array("a" => "repositorio_enviar"), array("id"))
                    ->where("a.patente = ?", $patente)
                    ->where("a.referencia = ?", $referencia);
            $stmt = $this->_db_table->fetchRow($select);
            if ($stmt) {
                return false;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        } catch (Zend_Exception $ex) {
            throw new Exception("Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }
    
    public function agregar($patente, $referencia, $rfcCliente) {
        try {
            $arr = array(                
                "patente" => $patente,                
                "referencia" => $referencia,
                "rfcCliente" => isset($rfcCliente) ? $rfcCliente : null,
                "creado" => date("Y-m-d H:i:s"),
            );
            $stmt = $this->_db_table->insert($arr);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        } catch (Zend_Exception $ex) {
            throw new Exception("Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }
    
    public function noEnviado($patente, $limit) {
        try {
            $select = $this->_db_table->select()
                    ->from(array("a" => "repositorio_enviar"), array("*"))
                    ->where("a.enviado IS NULL")
                    ->where("a.patente = ?", $patente)
                    ->limit($limit);
            $stmt = $this->_db_table->fetchAll($select);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        } catch (Zend_Exception $ex) {
            throw new Exception("Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }
    
    public function enviado($id) {
        try {            
            $stmt = $this->_db_table->update(array("enviado" => 1, "actualizado" => date("Y-m-d H:i:s")), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        } catch (Zend_Exception $ex) {
            throw new Exception("Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }
    
    public function vacio($id) {
        try {            
            $stmt = $this->_db_table->update(array("enviado" => 0), array("id = ?", $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        } catch (Zend_Exception $ex) {
            throw new Exception("Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }
    
    public function archivos($id, $archivos) {
        try {            
            $stmt = $this->_db_table->update(array("archivos" => $archivos), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        } catch (Zend_Exception $ex) {
            throw new Exception("Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

}
