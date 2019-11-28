<?php

class Webservice_Model_WsTokens {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Webservice_Model_DbTable_WsTokens();
    }
    
    public function search($token) {
        try {
            $stmt = $this->_db_table->fetchRow(
                    $this->_db_table->select()
                            ->where("token = ?", $token)
            );
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

    public function find(Webservice_Model_Table_WsTokens $t) {
        try {
            $stmt = $this->_db_table->fetchRow(
                    $this->_db_table->select()
                            ->where("rfc = ?", $t->getRfc())
            );
            if (0 == count($stmt)) {
                return;
            }
            $t->setId($stmt->id);
            $t->setRfc($stmt->rfc);
            $t->setToken($stmt->token);
            $t->setActivo($stmt->activo);
            $t->setCreado($stmt->creado);
            $t->setModificado($stmt->modificado);
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

    public function save(Webservice_Model_Table_WsTokens $t) {
        try {
            $array = array(
                "id" => $t->getId(),
                "rfc" => $t->getRfc(),
                "token" => $t->getToken(),
                "activo" => $t->getActivo(),
                "creado" => $t->getCreado(),
                "modificado" => $t->getModificado(),
            );
            if (null === ($id = $t->getId())) {
                unset($array["id"]);
                $id = $this->_db_table->insert($array);
                $t->setId($id);
            } else {
                $this->_db_table->update($array, array("id = ?" => $id));
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

}
