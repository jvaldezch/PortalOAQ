<?php

class Application_Model_MenusMapper {

    protected $_db;

    public function __construct() {
        $this->_db = Zend_Registry::get("oaqintranet");
    }

    /**
     * 
     * @param int $roleId
     * @return boolean
     */
    public function getTopMenu($roleId) {
        try {
            $sql = $this->_db->select()
                    ->from("menus", array("url", "menu", "modulo", "controlador", "accion", "params"))
                    ->where("rolid = ?", $roleId)
                    ->where("tipo = 1")
                    ->order(array("orden", "menu"));  // 1 is top menu, 2 is left, 3 main menu
            $stmt = $this->_db->fetchAll($sql, array());
            if ($stmt) {
                return $stmt;
            }
            return NULL;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param int $roleId
     * @param int $module
     * @return boolean
     */
    public function getMainMenu($roleId, $module) {
        try {
            $sql = $this->_db->select()
                    ->from("menus", array("url", "menu", "modulo", "controlador", "accion", "params"))
                    ->where("rolid = ?", $roleId)
                    ->where("moduloid = ?", $module)
                    ->where("tipo = 3")
                    ->order(array("orden", "menu"));
            $stmt = $this->_db->fetchAll($sql, array());
            if ($stmt) {
                return $stmt;
            }
            return NULL;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param int $roleId
     * @param int $modId
     */
    public function getLeftMenu($roleId, $modId) {
        try {
            $sql = $this->_db->select()
                    ->from("menus", array("url", "menu", "modulo", "controlador", "accion", "params"))
                    ->where("rolid = ?", $roleId)
                    ->where("moduloid = ?", $modId)
                    ->where("tipo = 2")
                    ->order(array("orden", "menu")); // 1 is top menu, 2 is left, 3 main menu
            $stmt = $this->_db->fetchAll($sql, array());
            if ($stmt) {
                return $stmt;
            }
            return NULL;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function getMainMenus($roleId) {
        try {
            $sql = $this->_db->select()
                    ->from("menus", array("url", "menu", "modulo", "controlador", "accion", "params"))
                    ->where("rolid = ?", $roleId)
                    ->where("tipo = 1")
                    ->group("menu")
                    ->order("orden ASC"); // 1 is top menu, 2 is left, 3 main menu
            $stmt = $this->_db->fetchAll($sql, array());
            if ($stmt) {
                return $stmt;
            }
            return NULL;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerIdRol($rol) {
        try {
            $sql = $this->_db->select()
                    ->from(array("r" => "usuarios_roles"), array("id"))
                    ->where("r.nombre = ?", $rol);
            $stmt = $this->_db->fetchRow($sql);
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

    public function obtenerMenuUsuario($rol) {
        try {
            $sql = $this->_db->select()
                    ->from(array("r" => "usuarios_roles"), array())
                    ->joinLeft(array("l" => "menu_rol"), "r.id = l.idRol", array("idAccion"))
                    ->joinLeft(array("a" => "menu_accion"), "a.id = l.idAccion", array("accion", "nombre", "orden", "id"))
                    ->joinLeft(array("c" => "menu_controlador"), "c.id = a.idControlador", array("controlador"))
                    ->joinLeft(array("m" => "menu_modulo"), "m.id = c.idModulo", array("modulo", "nombre AS nombreModulo"))
                    ->where("r.nombre = ?", $rol)
                    ->order(array("m.orden", "a.orden"));
            $stmt = $this->_db->fetchAll($sql, array());
            if ($stmt) {
                $data = array();
                foreach ($stmt as $item) {
                    if (!isset($data[$item["modulo"]])) {
                        $data[$item["modulo"]] = array(
                            "nombre" => $item["nombreModulo"]
                        );
                    }
                    if (isset($data[$item["modulo"]])) {
                        $data[$item["modulo"]]["acciones"][$item["id"]] = array(
                            "id" => $item["id"],
                            "accion" => $item["modulo"] . "/" . $item["controlador"] . "/" . $item["accion"],
                            "nombre" => $item["nombre"],
                            "orden" => $item["orden"],
                        );
                    }
                }
                return $data;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerMenu($id) {
        try {
            $sql = $this->_db->select()
                    ->from(array("r" => "usuarios_roles"), array())
                    ->joinLeft(array("l" => "menu_rol"), "r.id = l.idRol", array("idAccion"))
                    ->joinLeft(array("a" => "menu_accion"), "a.id = l.idAccion", array("accion", "nombre", "orden", "id"))
                    ->joinLeft(array("c" => "menu_controlador"), "c.id = a.idControlador", array("controlador"))
                    ->joinLeft(array("m" => "menu_modulo"), "m.id = c.idModulo", array("modulo", "nombre AS nombreModulo"))
                    ->where("a.id = ?", $id)
                    ->order(array("m.orden", "a.orden"));
            $stmt = $this->_db->fetchRow($sql, array());
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

    public function obtenerControladores() {
        try {
            $sql = $this->_db->select()
                    ->from(array("c" => "menu_controlador"), array("*"))
                    ->joinLeft(array("m" => "menu_modulo"), "m.id = c.idModulo", array("modulo", "nombre AS nombreModulo"))
                    ->order(array("m.nombre"));
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

    public function obtenerModulos() {
        try {
            $sql = $this->_db->select()
                    ->from(array("m" => "menu_modulo"), array("*"))
                    ->order(array("m.nombre"));
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

    public function actualizarAccion($idAccion, $nombre, $orden) {
        try {
            $update = $this->_db->update("menu_accion", array(
                "nombre" => $nombre,
                "orden" => $orden,
                    ), array(
                "id = ?" => $idAccion
                    )
            );
            if ($update) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function comprobarAccion($idControlador, $accion) {
        try {
            $sql = $this->_db->select()
                    ->from(array("a" => "menu_accion"), array("accion"))
                    ->where("idControlador = ?", $idControlador)
                    ->where("accion = ?", $accion);
            $stmt = $this->_db->fetchRow($sql, array());
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function agregarAccion($idControlador, $accion, $nombre) {
        try {
            $data = array(
                "idControlador" => $idControlador,
                "accion" => $accion,
                "nombre" => $nombre,
                "orden" => 99,
            );
            $stmt = $this->_db->insert("menu_accion", $data);
            if ($stmt) {
                return $this->_db->lastInsertId();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function agregarAccionRol($idAccion, $idRol) {
        try {
            $data = array(
                "idAccion" => $idAccion,
                "idRol" => $idRol
            );
            $stmt = $this->_db->insert("menu_rol", $data);
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function removerAccionRol($idAccion, $idRol) {
        try {
            $where = array(
                "idAccion = ?" => $idAccion,
                "idRol = ?" => $idRol
            );
            $stmt = $this->_db->delete($where);
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param int $idAccion
     * @param int $idRol
     * @return boolean
     * @throws Exception
     */
    public function verificar($idAccion, $idRol) {
        try {
            $sql = $this->_db->select()
                    ->from(array("a" => "menu_rol"), array("id"))
                    ->where("a.idAccion = {$idAccion} AND a.idRol = {$idRol}");
            $stmt = $this->_db->fetchRow($sql, array());
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
