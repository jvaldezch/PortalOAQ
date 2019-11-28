<?php

class Webservice_Model_TraficoBitacoraFotos {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Webservice_Model_DbTable_TraficoBitacoraFotos();
    }

    public function verificar($idGuia, $idFactura, $idItem, $archivoNombre) {
        try {
            $sql = $this->_db_table->select()
                    ->where("idGuia = ?", $idGuia)
                    ->where("idFactura = ?", $idFactura)
                    ->where("idItem = ?", $idItem)
                    ->where("archivoNombre = ?", $archivoNombre);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function obtenerFotos($idGuia, $idFactura, $idItem) {
        try {
            $sql = $this->_db_table->select()
                    ->where("idGuia = ?", $idGuia)
                    ->where("idFactura = ?", $idFactura)
                    ->where("idItem = ?", $idItem);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function obtenerFoto($id) {
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

    public function agregar($idGuia, $idFactura, $idItem, $archivoNombre, $ubicacion, $usuario) {
        try {
            $stmt = $this->_db_table->insert(array(
                "idGuia" => $idGuia,
                "idFactura" => $idFactura,
                "idItem" => $idItem,
                "archivoNombre" => $archivoNombre,
                "ubicacion" => $ubicacion,
                "creado" => date("Y-m-d H:i:s"),
                "creadoPor" => $usuario,
            ));
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function actualizar($id, $archivoNombre) {
        try {
            $stmt = $this->_db_table->update(array(
                "thumb" => $archivoNombre,
            ), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
