<?php

class Usuarios_Model_UsuariosMapper {

    protected $_db_table;
    protected $_passKey = "asdlkjkj34729384172312%!";

    public function __construct() {
        $this->_db_table = new Usuarios_Model_DbTable_Usuarios();
    }

    public function getUsers() {
        try {
            $sql = $this->_db_table->select()
                    ->from("usuarios", array("id", "nombre", "email", "usuario", "aduana", "patente", "empresa", "rol", "departamento", "acceso", new Zend_Db_Expr("AES_DECRYPT(password,'{$this->_passKey}') AS password")))
                    ->order("usuario ASC");
            $stmt = $this->_db_table->fetchAll($sql, array());
            if ($stmt) {
                $data = array();
                foreach ($stmt as $user):
                    $data[] = array(
                        "id" => $user["id"],
                        "nombre" => $user["nombre"],
                        "email" => $user["email"],
                        "usuario" => $user["usuario"],
                        "aduana" => $user["aduana"],
                        "patente" => $user["patente"],
                        "empresa" => $user["empresa"],
                        "rol" => $user["rol"],
                        "departamento" => $user["departamento"],
                        "password" => $user["password"],
                        "acceso" => $user["acceso"],
                    );
                endforeach;
                return $data;
            }
            return NULL;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerUsuarios() {
        try {
            $sql = $this->_db_table->select()
                    ->from("usuarios", array("id", "nombre", "email", "usuario", "aduana", "patente", "departamento"))
                    ->order("nombre ASC");
            $stmt = $this->_db_table->fetchAll($sql, array());
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function verificarUsuario($username) {
        $sql = $this->_db_table->select()
                ->from("usuarios", array("id"))
                ->where("usuario = ?", $username);
        $stmt = $this->_db_table->fetchRow($sql, array());
        if ($stmt) {
            return $stmt->id;
        }
        return;
    }
    
    public function verifyUser($username) {
        $sql = $this->_db_table->select()
                ->from("usuarios", array("usuario"))
                ->where("usuario LIKE ?", $username);
        $stmt = $this->_db_table->fetchRow($sql, array());
        if (!$stmt) {
            return NULL;
        }
        return true;
    }

    public function addNewUser($nombre, $email, $usuario, $patente, $aduana, $rfc, $password, $rol, $empresa, $departamento, $sisped) {
        try {
            $roles = new Usuarios_Model_RolesMapper();
            $data = array(
                "nombre" => $nombre,
                "email" => $email,
                "usuario" => strtolower($usuario),
                "patente" => $patente,
                "aduana" => $aduana,
                "rfc" => $rfc,
                "password" => new Zend_Db_Expr("AES_ENCRYPT('{$password}','{$this->_passKey}')"),
                "estatus" => 1,
                "rol" => $roles->getRolName($rol),
                "empresa" => $empresa,
                "departamento" => $departamento,
                "sispedimentos" => $sisped,
                "creado" => date("Y-m-d H:i:s"),
            );
            $inserted = $this->_db_table->insert($data);
            if ($inserted) {
                return $inserted;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerDatos($id) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("usuario", "id", "nombre", "empresa", "email", "rfc", "patente", "aduana", "departamento", "rol"))
                    ->where("id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }    
    
    public function getUserById($id) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("usuario", "id", "nombre", "empresa", "email", "rfc as empresa", "patente", "aduana", "departamento", "rol AS nombreRol", "sispedimentos", "estatus"))
                    ->where("id = ?", $id);
            return $this->getUserInfo($sql);
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }    
    
    public function obtenerUsuario($id = null, $usuario = null) {
        try {
            $roles = new Usuarios_Model_RolesMapper();
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("usuario", "id", "nombre", "empresa", "email", "rfc as empresa", "patente", "aduana", "departamento", "rol AS nombreRol", "sispedimentos", "estatus", "rfc"));
            if (isset($id)) {
                $sql->where("id = ?", $id);
            }
            if (isset($usuario)) {
                $sql->where("usuario = ?", $usuario);
            }
            $stmt = $this->_db_table->fetchRow($sql);
            if (count($stmt)) {
                $arr = array();
                $arr["id"] = $stmt["id"];
                $arr["nombre"] = $stmt["nombre"];
                $arr["usuario"] = $stmt["usuario"];
                $arr["empresa"] = $stmt["empresa"];
                $arr["email"] = $stmt["email"];
                $arr["patenteUsuario"] = $stmt["patente"];
                $arr["aduanaUsuario"] = $stmt["aduana"];
                $arr["departamento"] = $stmt["departamento"];
                $arr["nombreRol"] = $stmt["nombreRol"];
                $arr["rfc"] = $stmt["rfc"];
                $arr["rol"] = $roles->getRolId($stmt["nombreRol"]);
                $arr["sispedimentos"] = $stmt["sispedimentos"];
                $arr["estatus"] = $stmt["estatus"];
                return $arr;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function getEmailById($id) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("nombre", "email"))
                    ->where("id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerDatosUsuario($id, $usuario) {
        try {
            $sql = $this->_db_table->select()
                    ->where("id = ?", $id)
                    ->where("usuario = ?", $usuario);
            $stmt = $this->_db_table->fetchRow($sql, array());
            if (count($stmt) > 0) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function getUserByIdAndUsername($id, $username) {
        try {
            $sql = $this->_db_table->select()
                    ->where("id = ?", $id)
                    ->where("usuario = ?", $username);
            return $this->getUserInfo($sql);
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    protected function getUserInfo($sql) {
        try {
            $stmt = $this->_db_table->fetchRow($sql, array());
            $roles = new Usuarios_Model_RolesMapper();
            if ($stmt) {
                $data = array();
                $data["bootstrap"]["id"] = $stmt["id"];
                $data["bootstrap"]["nombre"] = $stmt["nombre"];
                $data["bootstrap"]["usuario"] = $stmt["usuario"];
                $data["bootstrap"]["empresa"] = $stmt["empresa"];
                $data["bootstrap"]["email"] = $stmt["email"];
                $data["bootstrap"]["patenteUsuario"] = $stmt["patente"];
                $data["bootstrap"]["aduanaUsuario"] = $stmt["aduana"];
                $data["bootstrap"]["departamento"] = $stmt["departamento"];
                $data["bootstrap"]["nombreRol"] = $stmt["nombreRol"];
                $data["bootstrap"]["rol"] = $roles->getRolId($stmt["nombreRol"]);
                $data["bootstrap"]["sispedimentos"] = $stmt["sispedimentos"];
                $data["bootstrap"]["estatus"] = $stmt["estatus"];
                return $data;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function getUserCompanyRelatedInfo($id) {
        try {
            $sql = $this->_db_table->select()
                    ->from("usuarios", array("aduana", "patente"))
                    ->where("id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql, array());
            if ($stmt) {
                $data = array(
                    "aduana" => $stmt["aduana"],
                    "patente" => $stmt["patente"],
                );
                return $data;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function updatePassword($id, $password) {
        try {
            $data = array(
                "password" => new Zend_Db_Expr("AES_ENCRYPT('{$password}','{$this->_passKey}')"),
                "actualizado" => date("Y-m-d H:i:s"),
            );
            $where = array(
                "id = ?" => $id
            );
            $update = $this->_db_table->update($data, $where);
            if ($update) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function obtenerPassword($id) {
        try {
            $sql = $this->_db_table->select()
                    ->from("usuarios", array(new Zend_Db_Expr("AES_DECRYPT(`password`,'{$this->_passKey}') AS psswd")))
                    ->where("id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql, array());
            if ($stmt) {
                return $stmt->psswd;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    

    public function updateUser($id, $nombre, $email, $usuario, $patente, $aduana, $rfc, $empresa, $departamento, $sisped, $password = null) {
        try {
            $data = array(
                "nombre" => $nombre,
                "email" => $email,
                "patente" => $patente,
                "aduana" => $aduana,
                "rfc" => $rfc,
                "empresa" => $empresa,
                "departamento" => $departamento,
                "sispedimentos" => $sisped,
                "actualizado" => date("Y-m-d H:i:s"),
            );
            if ($password) {
                $data["password"] = new Zend_Db_Expr("AES_ENCRYPT('{$password}','{$this->_passKey}')");
            }
            $where = array(
                "usuario = ?" => $usuario,
                "id = ?" => $id,
            );
            $updated = $this->_db_table->update($data, $where);
            if ($updated) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function updateUserBasic($id, $nombre, $email, $usuario, $patente, $aduana, $rfc, $empresa, $departamento, $rol, $sisped, $estatus) {
        try {
            $roles = new Usuarios_Model_RolesMapper();
            $data = array(
                "nombre" => $nombre,
                "email" => $email,
                "patente" => $patente,
                "aduana" => $aduana,
                "rfc" => $rfc,
                "empresa" => $empresa,
                "departamento" => $departamento,
                "rol" => $roles->getRolName($rol),
                "sispedimentos" => $sisped,
                "estatus" => $estatus,
                "actualizado" => date("Y-m-d H:i:s"),
            );
            $where = array(
                "usuario = ?" => $usuario,
                "id = ?" => $id,
            );
            $updated = $this->_db_table->update($data, $where);
            if ($updated) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function getLastAccess() {
        try {
            $sql = $this->_db_table->select()
                    ->from("usuarios", array("usuario", "aduana", "patente", "rol", "departamento", "acceso"))
                    ->where("acceso IS NOT NULL")
                    ->order("acceso DESC");
            if (($stmt = $this->_db_table->fetchAll($sql))) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param array $ids
     * @return type
     * @throws Exception
     */
    public function obtenerUsuariosEmails($ids = null) {
        try {
            $sql = $this->_db_table->select()
                    ->from("usuarios", array("id", "nombre", "email", "rfc"))
                    ->where("estatus = 1")
                    ->order("nombre ASC");
            if (($stmt = $this->_db_table->fetchAll($sql))) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function obtenerDirectorio($idUsuario) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("u" => "usuarios"), array("id"))
                    ->joinLeft(array("d" => "usuarios_directorio"), "d.idUsuario = u.id", array("*"))
                    ->where("u.estatus = 1")
                    ->where("u.id = ?", $idUsuario);
            if (($stmt = $this->_db_table->fetchRow($sql))) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function directorio() {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("u" => "usuarios"), array("id", "nombre", "email"))
                    ->joinLeft(array("d" => "usuarios_directorio"), "d.idUsuario = u.id", array("*"))
                    ->where("u.estatus = 1")
                    ->order("u.nombre ASC");
            if (($stmt = $this->_db_table->fetchAll($sql))) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function buscarUsuario($nombre) {
        try {
            $sql = $this->_db_table->select()
                    ->from(array("u" => "usuarios"), array("id"))
                    ->where("u.nombre LIKE ?", "%" . $nombre . "%")
                    ->where("u.estatus = 1");
            if (($stmt = $this->_db_table->fetchAll($sql))) {
                $data = array();
                foreach ($stmt->toArray() as $item) {
                    $data[] = $item["id"];
                }
                return $data;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function deleleUser($id) {
        try {
            $where = array(
                "id = ?" => $id
            );
            $removed = $this->_db_table->delete($where);
            if ($removed) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
