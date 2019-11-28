<?php

class Comercializacion_Model_ClientesMapper {

    protected $_db_table;
    protected $_custPassKey = "oaqlkjkj3asdjaksdjqweuiuyyASDQWEksald";

    public function __construct() {
        $this->_db_table = new Comercializacion_Model_DbTable_Clientes();
    }

    public function getAllCustomers() {
        try {
            $select = $this->_db_table->select()
                    ->order("nombre ASC");
            $result = $this->_db_table->fetchAll($select, array());

            if ($result) {
                $data = array();
                foreach ($result as $cliente):
                    $data[] = array(
                        "id" => $cliente["id"],
                        "rfc" => $cliente["rfc"],
                        "nombre" => $cliente["nombre"],
                        "email" => $cliente["email"],
                        "sica_id" => $cliente["sica_id"],
                        "access" => $cliente["access"],
                    );
                endforeach;
                return $data;
            }
            return NULL;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function customersByName($name) {
        try {
            $select = $this->_db_table->select()
                    ->where("nombre LIKE ?", "%" . $name . "%")
                    ->order("nombre ASC");
            $result = $this->_db_table->fetchAll($select, array());

            if ($result) {
                $data = array();
                foreach ($result as $cliente):
                    $data[] = array(
                        "id" => $cliente["id"],
                        "rfc" => $cliente["rfc"],
                        "nombre" => $cliente["nombre"],
                        "sica_id" => $cliente["sica_id"],
                        "access" => $cliente["access"],
                    );
                endforeach;
                return $data;
            }
            return NULL;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function customersByRfc($rfc) {
        try {
            $select = $this->_db_table->select()
                    ->from("clientes", array("id", "rfc", "nombre", "sica_id", "sita_id", "slam_id", "access", "AES_DECRYPT(password,'{$this->_custPassKey}') AS password"))
                    ->where("rfc LIKE ?", "%" . $rfc . "%")
                    ->order("nombre ASC");
            $stmt = $this->_db_table->fetchAll($select, array());
            if ($stmt) {
                $arr = array();
                foreach ($stmt as $item) {
                    $arr[] = array(
                        "id" => $item["id"],
                        "rfc" => $item["rfc"],
                        "nombre" => $item["nombre"],
                        "sica_id" => $item["sica_id"],
                        "sita_id" => $item["sita_id"],
                        "slam_id" => $item["slam_id"],
                        "access" => $item["access"],
                        "password" => $item["password"],
                    );
                }
                return $arr;
            }
            return NULL;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function customersId($id) {
        try {
            $select = $this->_db_table->select()
                    ->from("clientes", array("id", "rfc", "nombre", "sica_id", "sita_id", "slam_id", "access", "AES_DECRYPT(password,'{$this->_custPassKey}') AS password"))
                    ->where("id = ?", $id);
            $result = $this->_db_table->fetchRow($select, array());
            if ($result) {
                return $result->toArray();
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function getCustomerRfc($name) {
        try {
            $select = $this->_db_table->select()
                    ->where("nombre LIKE ?", $name);
            $result = $this->_db_table->fetchRow($select, array());
            if ($result) {
                $data = array(
                    "id" => $result["id"],
                    "rfc" => $result["rfc"],
                    "nombre" => $result["nombre"],
                    "sica_id" => $result["sica_id"],
                    "access" => $result["access"],
                );
                return $data;
            }
            return NULL;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function jsonCustomersByName($name) {
        try {
            $select = $this->_db_table->select()
                    ->where("nombre LIKE ?", "%" . $name . "%")
                    ->order("nombre ASC")
                    ->limit(10);
            $result = $this->_db_table->fetchAll($select, array());
            if ($result) {
                $data = array();
                foreach ($result as $cliente):
                    $data[] = $cliente["nombre"];
                endforeach;
                return $data;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function jsonCustomerRfcByName($name) {
        try {
            $select = $this->_db_table->select()
                    ->where("nombre LIKE ?", "%" . $name . "%")
                    ->order("nombre ASC")
                    ->limit(10);
            $result = $this->_db_table->fetchAll($select, array());
            if ($result) {
                $data = array();
                foreach ($result as $cliente):
                    $data[] = $cliente["rfc"];
                endforeach;
                if (count($data) == 1) {
                    return $data;
                } else {
                    return;
                }
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function verificar($id) {
        try {
            $sql = $this->_db_table->select()
                    ->from("clientes", array("id"))
                    ->where("id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function actualizarAcceso($id, $acceso, $password, $usuario) {
        try {
            $arr = array(
                "access" => $acceso,
                "actualizado" => date("Y-m-d H:i:s"),
                "password" => new Zend_Db_Expr("AES_ENCRYPT('{$password}','{$this->_custPassKey}')"),
                "actualizadoPor" => $usuario,
            );
            $stmt = $this->_db_table->update($arr, array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function updateCustomerDataByRfc($id, $access, $password, $username) {
        try {
            $updateDate = date("Y-m-d H:i:s");
            $query = "UPDATE clientes SET access = {$access}, password = AES_ENCRYPT('{$password}','{$this->_custPassKey}'), actualizado = '$updateDate', actualizadoPor = '{$username}' WHERE id = {$id};";
            $db = $this->_db_table->getAdapter();
            $updated = $db->query($query);
            if ($updated) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function getCustomerSicaId($id_cli) {
        try {
            $select = $this->_db_table->select()
                    ->from("clientes", array("sica_id"))
                    ->where("id = ?", $id_cli);
            $result = $this->_db_table->fetchRow($select, array());
            if ($result) {
                return $result["sica_id"];
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function getCustomerSlamId($id_cli) {
        try {
            $select = $this->_db_table->select()
                    ->from("clientes", array("slam_id"))
                    ->where("id = ?", $id_cli);
            $result = $this->_db_table->fetchRow($select, array());
            if ($result) {
                return $result["slam_id"];
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function getCustomerSitaId($id_cli) {
        try {
            $select = $this->_db_table->select()
                    ->from("clientes", array("sita_id"))
                    ->where("id = ?", $id_cli);
            $result = $this->_db_table->fetchRow($select, array());
            if ($result) {
                return $result["sita_id"];
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerDatosCliente($id = null, $rfc = null) {
        try {
            $sql = $this->_db_table->select()
                    ->from("clientes", array("id", "rfc", "nombre", "sica_id", "sita_id", "slam_id", "access", "AES_DECRYPT(password,'{$this->_custPassKey}') AS password"));
            if (isset($id)) {
                $sql->where("id = ?", $id);
            } elseif (isset($rfc)) {
                $sql->where("rfc = ?", $rfc);
            } else {
                return;
            }
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function getCustomerSicaIdByRfc($rfc, $nombre = null) {
        try {
            $select = $this->_db_table->select()
                    ->from("clientes", array("sica_id"))
                    ->where("rfc LIKE ?", $rfc)
                    ->order("sica_id DESC");
            if (isset($nombre)) {
                $select->where("nombre LIKE ?", $nombre);
            }
            $result = $this->_db_table->fetchRow($select, array());
            if ($result) {
                return $result["sica_id"];
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function verifyCustomer($rfc) {
        try {
            $select = $this->_db_table->select()
                    ->from("clientes", array("sica_id", "id"))
                    ->where("rfc = ?", $rfc);
            $result = $this->_db_table->fetchRow($select, array());
            if ($result) {
                return true;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function addNewCustomer($rfc, $nombre, $username) {
        try {
            $data = array(
                "rfc" => $rfc,
                "nombre" => $nombre,
                "creado" => date("Y-m-d H:i:s"),
                "creadoPor" => $username,
            );
            $added = $this->_db_table->insert($data);
            if ($added) {
                return $added;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function updateAccess($rfc) {
        try {
            $data = array(
                "acceso" => date("Y-m-d H:i:s"),
            );
            $where = array(
                "rfc = ?" => $rfc,
            );
            $this->_db_table->update($data, $where);
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
