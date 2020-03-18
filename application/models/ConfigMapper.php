<?php

class Application_Model_ConfigMapper {

    protected $_db;

    public function __construct() {
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
        $this->_db = Zend_Registry::get("oaqintranet");
    }

    public function getParam($param) {
        try {
            $sql = $this->_db->select()
                    ->from("config", array("value"))
                    ->where("param = ?", $param);
            $stmt = $this->_db->fetchRow($sql, array());
            if ($stmt) {
                return $stmt["value"];
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function getParamc($param) {
        try {
            $decryption_iv = $this->_config->app->encode;
            $decryption_key = $this->_config->app->encode_key;            
            $sql = $this->_db->select()
                    ->from("config", array("value"))
                    ->where("param = ?", $param);
            $stmt = $this->_db->fetchRow($sql, array());
            if ($stmt) {
                return openssl_decrypt($stmt['value'], "AES-128-CTR", $decryption_key, 0, $decryption_iv); 
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
