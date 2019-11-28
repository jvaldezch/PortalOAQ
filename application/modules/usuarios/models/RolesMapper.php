<?php

class Usuarios_Model_RolesMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Usuarios_Model_DbTable_Roles();
    }

    public function getRoles() {
        try {
            $sql = $this->_db_table->select()
                    ->where("activo = 1")
                    ->order("desc ASC");
            $stmt = $this->_db_table->fetchAll($sql, array());
            $modules = new Usuarios_Model_ModulosMapper();
            if ($stmt) {
                $data = array();
                foreach ($stmt as $rol):
                    $modulos = "";
                    $mods = explode(",", $rol["modulos"]);
                    foreach ($mods as $m) {
                        $modulos .= $modules->getModuleName($m) . ", ";
                    }
                    $data[] = array(
                        "id" => $rol["id"],
                        "nombre" => $rol["nombre"],
                        "modulos" => $modulos,
                        "desc" => $rol["desc"]
                    );
                endforeach;
                return $data;
            }
            return NULL;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function getRolName($id) {
        try {
            $sql = $this->_db_table->select()
                    ->from("usuarios_roles", array("nombre"))
                    ->where("id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql, array());
            if ($stmt) {
                return $stmt["nombre"];
            }
            return NULL;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function getRolId($name) {
        try {
            $sql = $this->_db_table->select()
                    ->from("usuarios_roles", array("id"))
                    ->where("nombre LIKE ?", $name);
            $stmt = $this->_db_table->fetchRow($sql, array());
            if ($stmt) {
                return $stmt["id"];
            }
            return -1;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
