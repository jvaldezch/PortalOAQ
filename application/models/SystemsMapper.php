<?php

class Application_Model_SystemsMapper {

    protected $_db;

    public function __construct() {
        $this->_db = Zend_Registry::get("oaqintranet");
    }

    /**
     * 
     * @param int $roleId
     * @return boolean
     */
    public function getMySystem($id, $username, $sys) {
        try {
            $sql = $this->_db->select()
                    ->from("usuarios", array($sys))
                    ->where("id = ?", $id)
                    ->where("usuario = ?", $username);
            $stmt = $this->_db->fetchRow($sql);
            if ($stmt) {
                return $stmt[$sys];
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
