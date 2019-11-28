<?php

class Operaciones_Model_CatalogoPartesImagenes {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Operaciones_Model_DbTable_CatalogoPartesImagenes();
    }
    
    public function obtener($id) {
        try {
            $sql = $this->_db_table->select()
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
    
    public function obtenerImagenes($idProducto) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array(new Zend_Db_Expr("REPLACE(SUBSTR(`carpeta`, -20, 20), '\\\\', '/') as carpeta"), "imagen", "miniatura", "nombre"))
                    ->where("idProducto = ?", (int) $idProducto);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function verificar($idProducto, $imagen) {
        try {
            $sql = $this->_db_table->select()
                    ->where("idProducto = ?", $idProducto)
                    ->where("imagen = ?", $imagen);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function agregar($idProducto, $filename, $nombre, $usuario) {
        try {
            $arr = array(
                "idProducto" => $idProducto,
                "carpeta" => pathinfo($filename, PATHINFO_DIRNAME),
                "imagen" => basename($filename),
                "nombre" => $nombre,
                "creado" => date("Y-m-d H:i:s"),
                "creadoPor" => $usuario,
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
    
    public function actualizar($id, $arr) {
        try {
            $stmt = $this->_db_table->update($arr, array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
