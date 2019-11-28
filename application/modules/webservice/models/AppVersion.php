<?php

class Webservice_Model_AppVersion {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Webservice_Model_DbTable_AppVersion();
    }

    public function ultimaVersion($app = null) {
        try {
            $sql = $this->_db_table->select()
                    ->order("creado DESC");
            if (isset($app)) {
                $sql->where("appName = ?", $app);
            }
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function todasVersiones($app = null) {
        try {
            $sql = $this->_db_table->select()
                    ->order("creado ASC");
            if (isset($app)) {
                $sql->where("appName = ?", $app);
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function agregar($arr) {
        try {
            $stmt = $this->_db_table->insert($arr);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function verificar($sistemaOperativo, $versionName, $versionCode, $appName, $filename) {
        try {
            $sql = $this->_db_table->select()
                    ->where("sistemaOperativo = ?", $sistemaOperativo)
                    ->where("versionName = ?", $versionName)
                    ->where("versionCode = ?", $versionCode)
                    ->where("appName = ?", $appName)
                    ->where("filename = ?", $filename);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
