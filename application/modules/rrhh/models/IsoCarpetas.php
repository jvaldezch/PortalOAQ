<?php

class Rrhh_Model_IsoCarpetas {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Rrhh_Model_DbTable_IsoCarpetas();
    }

    /**
     * 
     * @param string $directory
     * @return type
     * @throws Exception
     */
    public function obtenerTodas($directory = null) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("*"));
            if (isset($directory)) {
                $sql->where("carpeta = ?", $directory);
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
    public function obtener($directory) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("*"))
                    ->where("carpeta = ?", $directory);
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
     * Funcion para obtener informacion de un directorio mediante su Id
     * 
     * @param int $id Id del directorio que desea obtener informacion
     * @return type
     * @throws Exception
     */
    public function obtenerDirectorio($id) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("*"))
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

    /**
     * 
     * @param string $directory
     * @return type
     * @throws Exception
     */
    public function buscarId($directory) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("id"))
                    ->where("carpeta = ?", $directory);
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
     * @param string $carpeta
     * @return boolean
     * @throws Exception
     */
    public function verificar($carpeta) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("id"))
                    ->where("carpeta = ?", $carpeta);
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
     * @param string $carpeta
     * @param string $nombreCarpeta
     * @return boolean
     * @throws Exception
     */
    public function agregar($carpeta, $nombreCarpeta) {
        try {
            $arr = array(
                "carpeta" => $carpeta,
                "nombreCarpeta" => $nombreCarpeta,
                "creada" => date("Y-m-d H:i:s"),
            );
            $stmt = $this->_db_table->insert($arr);
            if ($stmt) {
                return $stmt;
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
                "nombreCarpeta" => $nombreArchivo,
                "modificada" => date("Y-m-d H:i:s"),
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
     * @param int $id
     * @return boolean
     * @throws Exception
     */
    public function eliminarDirectorio($id) {
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

}
