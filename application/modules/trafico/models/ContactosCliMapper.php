<?php

class Trafico_Model_ContactosCliMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_ContactosCli();
    }

    public function obtenerTodos($idCliente) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array('c' => 'trafico_contactoscli'), array('*'))
                    ->joinLeft(array('t' => 'trafico_tipocontacto'), "t.id = c.tipoContacto", array('tipo'))
                    ->where('c.idCliente = ?', $idCliente)
                    ->order('c.nombre ASC');
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function obtenerPorArregloId($arr) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array('c' => 'trafico_contactoscli'), array('nombre', 'email'))
                    ->where('c.id IN (?)', $arr);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function verificar($idCliente, $email, $tipoContacto) {
        try {
            $sql = $this->_db_table->select()
                    ->where('idCliente = ?', $idCliente)
                    ->where('email = ?', $email)
                    ->where('tipoContacto = ?', $tipoContacto);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function agregar($data) {
        try {
            $stmt = $this->_db_table->insert($data);
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function save(Trafico_Model_Table_ContactosCli $tbl) {
        try {
            $arr = array(
                "id" => $tbl->getId(),
                "idCliente" => $tbl->getIdCliente(),
                "nombre" => $tbl->getNombre(),
                "email" => $tbl->getEmail(),
                "tipoContacto" => $tbl->getTipoContacto(),
                "aviso" => $tbl->getAviso(),
                "pedimento" => $tbl->getPedimento(),
                "cruce" => $tbl->getCruce(),
                "creado" => $tbl->getCreado(),
                "creadoPor" => $tbl->getCreadoPor(),
                "modificado" => $tbl->getModificado(),
                "modificadoPor" => $tbl->getModificadoPor(),
            );
            if (null === ($id = $tbl->getId())) {
                $id = $this->_db_table->insert($arr);
                $tbl->setId($id);
            } else {
                $this->_db_table->update($arr, array("id = ?" => $id));
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function findEmail(Trafico_Model_Table_ContactosCli $tbl) {
        try {
            $sql = $this->_db_table->select()
                    ->where("email = ?", $tbl->getEmail())
                    ->where("idCliente = ?", $tbl->getIdCliente());
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                $tbl->setId($stmt->id);
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function cambiarAlerta($id, $alert, $action, $username) {
        try {
            $data = array(
                $alert => $action,
                "modificado" => date("Y-m-d H:i:s"),
                "modificadoPor" => $username,
            );
            $stmt = $this->_db_table->update($data, array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function delete($id) {
        try {
            $stmt = $this->_db_table->delete(array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function avisoPago($idCliente) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("nombre", "email"))
                    ->where("idCliente = ?", $idCliente)
                    ->where("aviso = 1");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function notificacion($idCliente) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("nombre", "email"))
                    ->where("idCliente = ?", $idCliente)
                    ->where("notificacion = 1");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
