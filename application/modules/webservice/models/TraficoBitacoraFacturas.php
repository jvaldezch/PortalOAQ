<?php

class Webservice_Model_TraficoBitacoraFacturas {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Webservice_Model_DbTable_TraficoBitacoraFacturas();
    }

    public function verificar($idGuia, $idFactura) {
        try {
            $sql = $this->_db_table->select()
                    ->where("idGuia = ?", (int) $idGuia)
                    ->where("idFactura = ?", (int) $idFactura);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->id;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerFacturas($idGuia) {
        try {
            $sql = $this->_db_table->select()
                    ->where("idGuia = ?", (int) $idGuia)
                    ->order("numFactura ASC");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function proveedorFactura($id) {
        try {
            $sql = $this->_db_table->select()
                    ->where("id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->nomProveedor;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function numFactura($id) {
        try {
            $sql = $this->_db_table->select()
                    ->where("id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->numFactura;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function agregar($idGuia, $idFactura, $numFactura, $nomProveedor, $usuario) {
        try {
            $stmt = $this->_db_table->insert(array(
                "idGuia" => $idGuia,
                "idFactura" => $idFactura,
                "numFactura" => $numFactura,
                "nomProveedor" => $nomProveedor,
                "creado" => date("Y-m-d H:i:s"),
                "creadoPor" => $usuario,
            ));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function actualizarNombre($id, $numFactura, $nomProveedor, $usuario) {
        try {
            $stmt = $this->_db_table->update(array(
                "numFactura" => $numFactura,
                "nomProveedor" => $nomProveedor,
                "actualizado" => date("Y-m-d H:i:s"),
                "actualizadoPor" => $usuario,), 
                    array("id = ?" => $id)
            );
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
