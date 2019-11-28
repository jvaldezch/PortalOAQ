<?php

class Application_Model_LogMapper {

    protected $_db;

    public function __construct() {
        $this->_db = Zend_Registry::get("oaqintranet");
    }

    public function logEntry($source, $message, $ip, $username) {
        try {
            $arr = array(
                "origen" => $source,
                "mensaje" => $message,
                "ip" => $ip,
                "creado" => date("Y-m-d H:i:s"),
                "usuario" => $username,
            );
            $stmt = $this->_db->insert("log", $arr);
            if (!$stmt) {
                return null;
            }
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    public function estatus($idExpediente) {
        try {
            $stmt = $this->_db->fetchAll('SELECT * FROM log_ftp WHERE idExpediente = ? ORDER BY creado DESC', $idExpediente);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    public function ftpLog($idExpediente, $source, $message, $ip, $username) {
        try {
            $arr = array(
                "idExpediente" => $idExpediente,
                "origen" => $source,
                "mensaje" => $message,
                "ip" => $ip,
                "creado" => date("Y-m-d H:i:s"),
                "usuario" => $username,
            );
            $stmt = $this->_db->insert("log_ftp", $arr);
            if (!$stmt) {
                return null;
            }
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function agregar($arr) {
        try {
            $stmt = $this->_db->insert("log_ftp", $arr);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

}
