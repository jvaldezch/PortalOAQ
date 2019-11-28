<?php

class Dashboard_Model_Traficos {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Trafico_Model_DbTable_Traficos();
    }

    public function obtenerTraficos($rfcCliente, $page, $size, $fecha = null) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("t" => "traficos"), array(
                        "id",
                        "patente",
                        "aduana",
                        "pedimento",
                        "referencia",
                        "cvePedimento",
                        "regimen",
                        "ie",
                        "estatus",
                        "DATE_FORMAT(fechaEta,'%Y-%m-%d') AS fechaEta",
                        "DATE_FORMAT(fechaPago,'%Y-%m-%d') AS fechaPago",
                        "DATE_FORMAT(fechaLiberacion,'%Y-%m-%d') AS fechaLiberacion",
                        "DATE_FORMAT(fechaRevalidacion,'%Y-%m-%d') AS fechaRevalidacion",
                        "DATE_FORMAT(fechaEntrada,'%Y-%m-%d') AS fechaEntrada",
                        "DATE_FORMAT(fechaPrevio,'%Y-%m-%d') AS fechaPrevio",
                        "DATE_FORMAT(fechaFacturacion,'%Y-%m-%d') AS fechaFacturacion",
                        "DATE_FORMAT(fechaDespacho,'%Y-%m-%d') AS fechaDespacho",
                        "DATE_FORMAT(fechaEtaAlmacen,'%Y-%m-%d') AS fechaEtaAlmacen",
                        "DATE_FORMAT(fechaEnvioProforma,'%Y-%m-%d') AS fechaEnvioProforma",
                        "DATE_FORMAT(fechaEnvioDocumentos,'%Y-%m-%d') AS fechaEnvioDocumentos",
                        "DATE_FORMAT(fechaNotificacion,'%Y-%m-%d') AS fechaNotificacion",
                        "DATE_FORMAT(fechaDeposito,'%Y-%m-%d') AS fechaDeposito",
                    ))
                    ->joinLeft(array("a" => "trafico_aduanas"), "a.id = t.idAduana", array("nombre AS nombreAduana"))
                    ->where("t.estatus <> 4")
                    ->where("t.rfcCliente = ?", $rfcCliente)
                    ->where("t.fechaEta > ?", date('Y-m-d', strtotime('-15 days', strtotime($fecha))))
                    ->order("t.idAduana ASC");
            if (isset($size)) {
                $sql->limit($size, ((int) $size * ((int) $page - 1 )));
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $ex) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function total($rfcCliente, $fecha = null) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array(new Zend_Db_Expr("COUNT(*) AS total")))
                    ->where("rfcCliente = ?", $rfcCliente)
                    ->where("fechaEta > ?", date('Y-m-d', strtotime('-15 days', strtotime($fecha))))
                    ->where("estatus <> 4");
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->total;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $ex) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function porAduana($rfcCliente, $patente, $aduana, $fecha = null) {
        try {
            $sql = $this->_db_table->select()
                    ->from(array("t" => "traficos"), array("COUNT(*) AS total"))
                    ->where("t.aduana = ?", $aduana)
                    ->where("t.rfcCliente = ?", $rfcCliente)
                    ->where("YEAR(t.fechaEta) = ?", (int) date('Y', strtotime($fecha)))
                    ->where("MONTH(t.fechaEta) = ?", (int) date('m', strtotime($fecha)))
                    ->where("t.estatus IN (1, 2, 3)");
            if (isset($patente)) {
                $sql->where("t.patente = ?", $patente);
            }
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->total;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $ex) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function totalPorLiberar($rfcCliente, $fecha) {
        try {
            $sql = $this->_db_table->select()
                    ->from(array("t" => "traficos"), array("count(*) AS total"))
                    ->where("t.rfcCliente = ?", $rfcCliente)
                    ->where("YEAR(t.fechaEta) = ?", (int) date('Y', strtotime($fecha)))
                    ->where("MONTH(t.fechaEta) = ?", (int) date('m', strtotime($fecha)))
                    ->where("t.estatus = 1 AND t.estatus NOT IN (2, 3, 4)");
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->total;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $ex) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    public function totalMes($rfcCliente, $fecha) {
        try {
            $sql = $this->_db_table->select()
                    ->from(array("t" => "traficos"), array("count(*) AS total"))
                    ->where("t.rfcCliente = ?", $rfcCliente)
                    ->where("YEAR(t.fechaEta) = ?", (int) date('Y', strtotime($fecha)))
                    ->where("MONTH(t.fechaEta) = ?", (int) date('m', strtotime($fecha)))
                    ->where("t.estatus IN (1, 2, 3)");
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->total;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $ex) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    public function totalMesAnterior($rfcCliente, $fecha) {
        try {
            $sql = $this->_db_table->select()
                    ->from(array("t" => "traficos"), array("count(*) AS total"))
                    ->where("t.rfcCliente = ?", $rfcCliente)
                    ->where("YEAR(t.fechaLiberacion) = ?", (int) date('Y', strtotime('-1 month', strtotime($fecha))))
                    ->where("MONTH(t.fechaLiberacion) = ?", (int) date('m', strtotime('-1 month', strtotime($fecha))))
                    ->where("t.estatus IN (1, 2, 3)");
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->total;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $ex) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

}
