<?php

class Trafico_Model_ContactosMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_Contactos();
    }

    /**
     * 
     * @param int $idAduana
     * @return boolean
     * @throws Exception
     */
    public function obtener($idAduana) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("c" => "trafico_contactos"), array("*"))
                    ->joinLeft(array("t" => "trafico_tipocontacto"), "t.id = c.tipoContacto", array("tipo"))
                    ->where("c.idAduana = ?", $idAduana)
                    ->where("c.habilitado = 1")
                    ->order("nombre ASC");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param int $idAduana
     * @return boolean
     * @throws Exception
     */
    public function contactosTrafico($idAduana) {
        try {
            $sql = $this->_db_table->select()
                    ->from(array("c" => "trafico_contactos"), array("nombre", "email"))
                    ->where("c.idAduana = ?", $idAduana)
                    ->where("c.tipoContacto IN (?)", array(1))
                    ->where("c.habilitado = 1");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param int $idAduana
     * @return boolean
     * @throws Exception
     */
    public function contactosAdministrativos($idAduana) {
        try {
            $sql = $this->_db_table->select()
                    ->from(array("c" => "trafico_contactos"), array("nombre", "email"))
                    ->where("c.idAduana = ?", $idAduana)
                    ->where("c.tipoContacto IN (?)", array(1, 3, 6))
                    ->where("c.habilitado = 1");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param int $idAduana
     * @return boolean
     * @throws Exception
     */
    public function avisoSistemas() {
        try {
            $sql = $this->_db_table->select()
                    ->from(array("c" => "trafico_contactos"), array("nombre", "email"))
                    ->where("c.tipoContacto = 7 AND c.habilitado = 1");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param int $idAduana
     * @return boolean
     * @throws Exception
     */
    public function avisoDeposito($idAduana) {
        try {
            $sql = $this->_db_table->select()
                    ->from(array("c" => "trafico_contactos"), array("nombre", "email"))
                    ->where("c.idAduana = ?", $idAduana)
                    ->where("c.deposito = 1 AND c.habilitado = 1");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param int $idAduana
     * @return boolean
     * @throws Exception
     */
    public function avisoCreacion($idAduana) {
        try {
            $sql = $this->_db_table->select()
                    ->from(array("c" => "trafico_contactos"), array("nombre", "email"))
                    ->where("c.idAduana = ?", $idAduana)
                    ->where("c.creacion = 1 AND c.habilitado = 1");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @return boolean
     * @throws Exception
     */
    public function avisoSolicitud() {
        try {
            $sql = $this->_db_table->select()
                    ->from(array("c" => "trafico_contactos"), array("nombre", "email"))
                    ->where("c.idAduana = 0")
                    ->where("c.solicitud = 1 AND c.habilitado = 1");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @return boolean
     * @throws Exception
     */
    public function para() {
        try {
            $sql = $this->_db_table->select()
                    ->from(array("c" => "trafico_contactos"), array("nombre", "email"))
                    ->where("c.tipoContacto = 0 AND c.idAduana = 0");
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    /**
     * 
     * @return boolean
     * @throws Exception
     */
    public function avisoGeneralDeposito() {
        try {
            $sql = $this->_db_table->select()
                    ->from(array("c" => "trafico_contactos"), array("nombre", "email"))
                    ->where("c.idAduana = 0")
                    ->where("c.deposito = 1 AND c.habilitado = 1");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @return boolean
     * @throws Exception
     */
    public function avisoGeneralComentarios() {
        try {
            $sql = $this->_db_table->select()
                    ->from(array("c" => "trafico_contactos"), array("nombre", "email"))
                    ->where("c.idAduana = 0")
                    ->where("c.comentario = 1 AND c.habilitado = 1");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param int $idAduana
     * @return boolean
     * @throws Exception
     */
    public function avisoComentario($idAduana) {
        try {
            $sql = $this->_db_table->select()
                    ->from(array("c" => "trafico_contactos"), array("nombre", "email"))
                    ->where("c.idAduana = ?", $idAduana)
                    ->where("c.comentario = 1 AND c.habilitado = 1");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param int $idAduana
     * @return boolean
     * @throws Exception
     */
    public function avisoComentarioSolicitud($idAduana) {
        try {
            $sql = $this->_db_table->select()
                    ->from(array("c" => "trafico_contactos"), array("nombre", "email"))
                    ->where("c.idAduana = ?", $idAduana)
                    ->where("c.comentarioSolicitud = 1 AND c.habilitado = 1");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param int $id
     * @param string $alert
     * @param string $action
     * @param string $username
     * @return boolean
     * @throws Exception
     */
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

    /**
     * 
     * @param Trafico_Model_Table_Contactos $tbl
     * @return type
     * @throws Exception
     */
    public function find(Trafico_Model_Table_Contactos $tbl) {
        try {
            $slc = $this->_db_table->select()
                    ->where("idAduana = ?", $tbl->getIdAduana())
                    ->where("email = ?", $tbl->getEmail());
            $stmt = $this->_db_table->fetchAll($slc);
            if ($stmt) {
                $tbl->setId($stmt->id);
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param Trafico_Model_Table_Contactos $tbl
     * @throws Exception
     */
    public function save(Trafico_Model_Table_Contactos $tbl) {
        try {
            $arr = array(
                "idAduana" => $tbl->getIdAduana(),
                "nombre" => $tbl->getNombre(),
                "email" => $tbl->getEmail(),
                "tipoContacto" => $tbl->getTipoContacto(),
                "creacion" => $tbl->getCreacion(),
                "cancelacion" => $tbl->getCancelacion(),
                "comentario" => $tbl->getComentario(),
                "deposito" => $tbl->getDeposito(),
                "habilitado" => $tbl->getHabilitado(),
                "creado" => $tbl->getCreado(),
                "creadoPor" => $tbl->getCreadoPor(),
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

    /**
     * 
     * @param int $idContacto
     * @return boolean
     * @throws Exception
     */
    public function delete($idContacto) {
        try {
            $stmt = $this->_db_table->delete(array("id = ?" => $idContacto));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
