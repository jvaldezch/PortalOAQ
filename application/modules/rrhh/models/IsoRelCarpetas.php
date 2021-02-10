<?php

class Rrhh_Model_IsoRelCarpetas {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Rrhh_Model_DbTable_IsoRelCarpetas();
    }

    /**
     * 
     * @param string $directory
     * @return type
     * @throws Exception
     */
    public function obtener($directory = null) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("r" => "iso_relcarpetas"), array("id"))
                    ->joinLeft(array("cc" => "iso_carpetas"), "cc.id = r.idCarpeta", array(""))
                    ->joinLeft(array("p" => new Zend_Db_Expr("(SELECT id, carpeta FROM iso_carpetas)")), "p.id = r.idParent", array("carpeta AS previo"))
                    ->joinLeft(array("c" => new Zend_Db_Expr("(SELECT id, carpeta, nombreCarpeta, creada, modificada FROM iso_carpetas)")), "c.id = r.idChild", array("carpeta AS siguiente", "nombreCarpeta", "creada", "modificada", "id AS idCarpeta"))
                    ->where("c.carpeta IS NOT NULL")
                    ->order("c.nombreCarpeta ASC");
            if (isset($directory)) {
                $sql->where("cc.carpeta = ?", $directory);
            } else {
                $sql->where("r.idCarpeta = 0");
            }
            $stmt = $this->_db_table->fetchAll($sql);
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
     * @param string $directory
     * @return type
     * @throws Exception
     */
    public function obtenerParent($directory = null) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("r" => "iso_relcarpetas"), array(""))
                    ->joinLeft(array("cc" => "iso_carpetas"), "cc.id = r.idCarpeta", array(""))
                    ->joinLeft(array("p" => new Zend_Db_Expr("(SELECT id, carpeta FROM iso_carpetas)")), "p.id = r.idParent", array("carpeta AS previo"))
                    ->group("previo");
            if (isset($directory)) {
                $sql->where("cc.carpeta = ?", $directory);
            } else {
                $sql->where("r.idCarpeta = 0");
            }
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerParentArray($directory = null) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("r" => "iso_relcarpetas"), array(""))
                    ->joinLeft(array("cc" => "iso_carpetas"), "cc.id = r.idCarpeta", array("nombreCarpeta"))
                    ->joinLeft(array("p" => new Zend_Db_Expr("(SELECT id, carpeta FROM iso_carpetas)")), "p.id = r.idParent", array("carpeta AS previo"))
                    ->group(array("nombreCarpeta", "previo"));
            if (isset($directory)) {
                $sql->where("cc.carpeta = ?", $directory);
            } else {
                $sql->where("r.idCarpeta = 0");
            }
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
     * @param int $idCarpeta
     * @param int $idParent
     * @param int $idChild
     * @return boolean
     * @throws Exception
     */
    public function verificar($idCarpeta, $idParent = null, $idChild = null) {
        try {
            $sql = $this->_db_table->select()
                    ->where("idCarpeta = ?", $idCarpeta);
            if (isset($idParent)) {
                $sql->where("idParent = ?", $idParent);
            }
            if (isset($idChild)) {
                $sql->where("idChild = ?", $idChild);
            }
            $stmt = $this->_db_table->fetchRow($sql);
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
     * @param int $idCarpeta
     * @param itn $idParent
     * @param int $idChild
     * @return boolean
     * @throws Exception
     */
    public function verificarNoChild($idCarpeta, $idParent = null) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("id"))
                    ->where("idCarpeta = ?", $idCarpeta)
                    ->where("idParent = ?", $idParent)
                    ->where("idChild IS NULL");
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->id;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param int $idCarpeta
     * @return type
     * @throws Exception
     */
    public function isChildOf($idCarpeta) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("idCarpeta"))
                    ->where("idChild = ?", $idCarpeta);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->idCarpeta;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function actualizarIdChild($id, $idChild) {
        try {
            $stmt = $this->_db_table->update(array("idChild" => $idChild),array("id = ?" => $id));
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
     * @param int $idCarpeta
     * @param int $idParent
     * @param int $idChild
     * @return boolean
     * @throws Exception
     */
    public function agregar($idCarpeta, $idParent = null, $idChild = null) {
        try {
            $arr = array(
                "idCarpeta" => $idCarpeta,
                "idParent" => $idParent,
                "idChild" => $idChild,
            );
            $stmt = $this->_db_table->insert($arr);
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
     * @param int $idCarpeta
     * @return boolean
     * @throws Exception
     */
    public function contarElementos($idCarpeta) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array(new Zend_Db_Expr("CASE WHEN count(*) = 0 THEN NULL ELSE count(*) END AS cantidad")))
                    ->where("idCarpeta = ?", $idCarpeta)
                    ->where("idChild IS NOT NULL");
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->cantidad;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    /**
     * 
     * @param int $id
     * @return boolean
     * @throws Exception
     */
    public function eliminarDirectorio($id) {
        try {
            $stmt = $this->_db_table->delete(array("idCarpeta = ?" => $id));
            $stmt2 = $this->_db_table->delete(array("idChild = ?" => $id, "idParent IS NULL"));
            $stmt3 = $this->_db_table->update(array("idChild" => null), array("idChild = ?" => $id));
            if ($stmt || $stmt2 || $stmt3) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
