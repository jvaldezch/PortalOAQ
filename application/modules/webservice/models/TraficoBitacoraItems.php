<?php

class Webservice_Model_TraficoBitacoraItems {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Webservice_Model_DbTable_TraficoBitacoraItems();
    }

    public function verificar($idGuia, $idFactura, $idItem) {
        try {
            $sql = $this->_db_table->select()
                    ->where("idGuia = ?", $idGuia)
                    ->where("idFactura = ?", $idFactura)
                    ->where("idItem = ?", $idItem);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->id;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function obtenerItems($idGuia, $idFactura) {
        try {
            $sql = $this->_db_table->select()
                    ->where("idGuia = ?", $idGuia)
                    ->where("idFactura = ?", $idFactura);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function agregar($idGuia, $idFactura, $idItem, $descripcion, $usuario) {
        try {
            $stmt = $this->_db_table->insert(array(
                "idGuia" => $idGuia,
                "idFactura" => $idFactura,
                "idItem" => $idItem,
                "descripcion" => $descripcion,
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

    public function actualizar($id, $arr) {
        try {
            $stmt = $this->_db_table->update($arr, array("id = ?" => $id));
            /*$stmt = $this->_db_table->update(array(
                "descripcion" => $descripcion,
                "marca" => $marca,
                "modelo" => $modelo,
                "numeroSerie" => $numeroSerie,
                "numeroParte" => $numeroParte,
                "actualizado" => date("Y-m-d H:i:s"),
                "actualizadoPor" => $usuario,
                    ), array("id = ?" => $id)
            );*/
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
