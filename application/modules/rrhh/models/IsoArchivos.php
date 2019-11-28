<?php

class Rrhh_Model_IsoArchivos {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Rrhh_Model_DbTable_IsoArchivos();
    }

    /**
     * 
     * @param string $directory
     * @return type
     * @throws Exception
     */
    public function obtenerTodos($directory = null) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("a" => "iso_archivos"), array("*"))
                    ->joinLeft(array("c" => "iso_carpetas"), "c.id = a.idCarpeta", array("carpeta"))
                    ->order("nombreArchivo ASC");
            if (isset($directory)) {
                $sql->where("c.carpeta = ?", $directory);
            } else {
                $sql->where("c.carpeta IS NULL");
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
     * @param type $id
     * @return type
     * @throws Exception
     */
    public function obtener($id) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("a" => "iso_archivos"), array("*"))
                    ->joinLeft(array("c" => "iso_carpetas"), "c.id = a.idCarpeta", array("carpeta"))
                    ->where("a.id = ?", $id);
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
     * @param string $archivo
     * @return boolean
     * @throws Exception
     */
    public function verificar($idCarpeta, $archivo) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("id"))
                    ->where("idCarpeta = ?", $idCarpeta)
                    ->where("archivo = ?", $archivo);
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
     * @param string $archivo
     * @param string $nombreArchivo
     * @return boolean
     * @throws Exception
     */
    public function agregar($idCarpeta, $archivo, $nombreArchivo) {
        try {
            $arr = array(
                "idCarpeta" => $idCarpeta,
                "archivo" => $archivo,
                "nombreArchivo" => $nombreArchivo,
                "creado" => date("Y-m-d H:i:s"),
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
     * @param int $id
     * @param string $nombreArchivo
     * @return boolean
     * @throws Exception
     */
    public function actualizarNombre($id, $nombreArchivo) {
        try {
            $arr = array(
                "nombreArchivo" => $nombreArchivo,
                "modificado" => date("Y-m-d H:i:s"),
            );
            $stmt = $this->_db_table->update($arr, array("id = ?" => $id));
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
     * @param array $arr
     * @return boolean
     * @throws Exception
     */
    public function eliminarArchivos($arr) {
        try {
            $stmt = $this->_db_table->delete(array("id IN (?)", $arr));
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
     * @param array $id
     * @return boolean
     * @throws Exception
     */
    public function eliminarArchivo($id) {
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
                    ->where("idCarpeta = ?", $idCarpeta);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->cantidad;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
