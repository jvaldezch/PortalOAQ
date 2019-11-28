<?php

class Trafico_Model_TraficoPedimento {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_TraficoPedimento();
    }

    public function verificar($idTrafico, $pedimento) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("id"))
                    ->where("idTrafico = ?", $idTrafico)
                    ->where("pedimento = ?", $pedimento);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->id;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function agregar($idTrafico, $patente, $aduana, $pedimento, $referencia, $arr) {
        try {
            $stmt = $this->_db_table->insert(array(
                "idTrafico" => $idTrafico,
                "patente" => $patente,
                "aduana" => $aduana,
                "pedimento" => $pedimento,
                "referencia" => $referencia,
                "transporteSalida" => $arr["transporteSalida"],
                "transporteArribo" => $arr["transporteArribo"],
                "transporteEntrada" => $arr["transporteEntrada"],
                "creado" => date("Y-m-d H:i:s")
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
            $stmt = $this->_db_table->update(array(
                "transporteSalida" => $arr["transporteSalida"],
                "transporteArribo" => $arr["transporteArribo"],
                "transporteEntrada" => $arr["transporteEntrada"],
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
