<?php

class Archivo_Model_FtpMapper {

    protected $_dbTable;

    public function __construct() {
        $this->_dbTable = new Archivo_Model_DbTable_Ftp();
    }

    public function fetchAll() {
        try {
            $result = $this->_dbTable->fetchAll(
                    $this->_dbTable->select()
            );
            if (0 == count($result)) {
                return;
            }
            return $result->toArray();
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function save(Archivo_Model_Table_Ftp $tbl) {
        try {
            $arr = array(
                "id" => $tbl->getId(),
                "type" => $tbl->getType(),
                "rfc" => $tbl->getRfc(),
                "url" => $tbl->getUrl(),
                "user" => $tbl->getUser(),
                "password" => $tbl->getPassword(),
                "port" => $tbl->getPort(),
                "remoteFolder" => $tbl->getRemoteFolder(),
                "active" => $tbl->getActive(),
            );
            if (null === ($id = $tbl->getId())) {
                unset($arr['id']);
                $id = $this->_dbTable->insert($arr);
                $tbl->setId($id);
            } else {
                $this->_dbTable->update($arr, array('id = ?' => $id));
            }
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    public function find(Archivo_Model_Table_Ftp $tbl) {
        try {
            $result = $this->_dbTable->fetchRow(
                    $this->_dbTable->select()
                    ->where("rfc = ?", $tbl->getRfc())
                    ->where("type = ?", $tbl->getType())
            );
            if (0 == count($result)) {
                return;
            }
            return $result->toArray();
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    public function buscar($rfc, $tipo) {
        try {
            $result = $this->_dbTable->fetchRow(
                    $this->_dbTable->select()
                    ->where("rfc = ?", $rfc)
                    ->where("type = ?", $tipo)
            );
            if (0 == count($result)) {
                return;
            }
            return $result->toArray();
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

}
