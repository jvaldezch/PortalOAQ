<?php

class Archivo_Model_CuentasGastosMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Automatizacion_Model_DbTable_CuentasGastos();
    }

    public function getAll($fechaIni, $fechaFin, $rfc = null) {
        try {
            $sql = $this->_db_table->select()
                    ->where("tipo_archivo = 2")
                    ->where("fecha >= ?", $fechaIni)
                    ->where("DATE_FORMAT(fecha, '%Y-%m-%d') >= ?", $fechaIni)
                    ->where("DATE_FORMAT(fecha, '%Y-%m-%d') <= ?", $fechaFin)
                    ->order("folio ASC");
            if(isset($rfc)) {
                $sql->where("receptor_rfc = ?", $rfc);
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
    
    public function searchSql($fechaIni, $fechaFin, $rfc = null) {
        try {
            $sql = $this->_db_table->select()
                    ->where("tipo_archivo = 2")
                    ->where("fecha >= ?", $fechaIni)
                    ->where("DATE_FORMAT(fecha, '%Y-%m-%d') >= ?", $fechaIni)
                    ->where("DATE_FORMAT(fecha, '%Y-%m-%d') <= ?", $fechaFin)
                    ->order("folio ASC");
            if(isset($rfc)) {
                $sql->where("receptor_rfc = ?", $rfc);
            }
            return $sql;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function getByRfc($rfc, $fechaIni, $fechaFin) {
        try {
            $select = $this->_db_table->select()
                    ->where("receptor_rfc = ?", $rfc)
                    ->where("tipo_archivo = 2")
                    ->where("fecha >= ?", $fechaIni)
                    ->where("DATE_FORMAT(fecha, '%Y-%m-%d') <= ?", $fechaFin)
                    ->order("folio ASC");
            $result = $this->_db_table->fetchAll($select);
            if ($result) {
                return $result->toArray();
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function getXmlPaths($fechaIni = null, $fechaFin = null, $rfc = null, $ids = null) {
        try {
            $select = $this->_db_table->select();
            if (!isset($ids)) {
                $select->where("tipo_archivo = 2")
                        ->where("fecha >= ?", $fechaIni)
                        ->where("DATE_FORMAT(fecha, '%Y-%m-%d') <= ?", $fechaFin);
                if ($rfc) {
                    $select->where("receptor_rfc = ?", $rfc);
                }
            } else {
                $select->where("tipo_archivo = 2")
                        ->where("id IN (?)", $ids);
            }
            $result = $this->_db_table->fetchAll($select);
            if ($result) {
                $data = array();
                foreach ($result as $item) {
                    $data[] = $item["ubicacion"];
                }
                return $data;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function getFilePath($id) {
        try {
            $select = $this->_db_table->select();
            $select->from("cuentas_gastos", array("nom_archivo", "ubicacion_pdf", "ubicacion_xml"))
                    ->where("id = ?", $id);
            $result = $this->_db_table->fetchRow($select);
            if ($result) {
                return $result->toArray();
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
