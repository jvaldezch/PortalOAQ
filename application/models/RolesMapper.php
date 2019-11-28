<?php

class Application_Model_RolesMapper {

    protected $_db;

    public function __construct() {
        $this->_db = Zend_Registry::get("oaqintranet");
    }

    /**
     * 
     * @param String $roles Must a JSON encode string.
     * @return boolean
     */
    public function checkForRole($rol) {
        try {
            $sql = $this->_db->select()
                    ->from("usuarios_roles", array("id"))
                    ->where("nombre LIKE ?", $rol);
            $stmt = $this->_db->fetchRow($sql, array());
            if ($stmt) {
                return $stmt["id"];
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param String $rol
     * @return array
     */
    public function checkModules($rol) {
        try {
            $sql = $this->_db->select()
                    ->from("usuarios_roles", array("modulos"))
                    ->where("nombre LIKE ?", $rol)
                    ->order("nombre ASC");
            $stmt = $this->_db->fetchRow($sql, array());
            if ($stmt) {
                return $stmt["modulos"];
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @return array
     * @throws Exception
     */
    public function todos() {
        try {
            $sql = $this->_db->select()
                    ->from("usuarios_roles", array("id", "nombre", "desc"))
                    ->order("desc ASC");
            $stmt = $this->_db->fetchAll($sql, array());
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
