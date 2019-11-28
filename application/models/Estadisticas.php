<?php

class Application_Model_Estadisticas {

    protected $_db;

    public function __construct() {
        $this->_db = Zend_Registry::get("oaqintranet");
    }

    public function covesPorHora($fechaIni, $fechaFin) {
        try {
            $sql = $this->_db->select()
                    ->from("vucem_solicitudes", array(
                        new Zend_Db_Expr("SUM(CASE HOUR (enviado) WHEN 7 THEN 1 ELSE 0 END) AS '0'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (enviado) WHEN 8 THEN 1 ELSE 0 END) AS '1'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (enviado) WHEN 9 THEN 1 ELSE 0 END) AS '2'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (enviado) WHEN 10 THEN 1 ELSE 0 END) AS '3'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (enviado) WHEN 11 THEN 1 ELSE 0 END) AS '4'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (enviado) WHEN 12 THEN 1 ELSE 0 END) AS '5'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (enviado) WHEN 13 THEN 1 ELSE 0 END) AS '6'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (enviado) WHEN 14 THEN 1 ELSE 0 END) AS '7'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (enviado) WHEN 15 THEN 1 ELSE 0 END) AS '8'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (enviado) WHEN 16 THEN 1 ELSE 0 END) AS '9'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (enviado) WHEN 17 THEN 1 ELSE 0 END) AS '10'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (enviado) WHEN 18 THEN 1 ELSE 0 END) AS '11'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (enviado) WHEN 19 THEN 1 ELSE 0 END) AS '12'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (enviado) WHEN 20 THEN 1 ELSE 0 END) AS '13'"),
                    ))
                    ->where("estatus = 2")
                    ->where("enviado >= ?", $fechaIni)
                    ->where("enviado <= ?", $fechaFin);
            $stmt = $this->_db->fetchRow($sql, array());
            if ($stmt) {
                return $this->_transform($stmt);
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function terminal() {
        try {
            $sql = $this->_db->select()
                    ->from(array("l" => "emails_log"), array("count(id) AS total"))
                    ->where("creado >= ?", date("Y-m-d"));
            $stmt = $this->_db->fetchRow($sql, array());
            if ($stmt) {
                return $stmt["total"];
            }
            return 0;
        } catch (Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function facturacion() {
        try {
            $sql = $this->_db->select()
                    ->from(array("f" => "admon_facturacion"), array("count(id) AS total"))
                    ->where("fechaFacturacion >= ?", date("Y-m-d"));
            $stmt = $this->_db->fetchRow($sql, array());
            if ($stmt) {
                return $stmt["total"];
            }
            return 0;
        } catch (Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function ftp() {
        try {
            $sql = $this->_db->select()
                    ->from(array("l" => "log_ftp"), array("count(id) AS total"))
                    ->where("creado >= ?", date("Y-m-d"));
            $stmt = $this->_db->fetchRow($sql, array());
            if ($stmt) {
                return $stmt["total"];
            }
            return 0;
        } catch (Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function coves() {
        try {
            $sql = $this->_db->select()
                    ->from("vucem_solicitudes", array(new Zend_Db_Expr("COUNT(id) AS total")))
                    ->where("estatus = 2")
                    ->where("enviado >= ?", date("Y-m-d"));
            $stmt = $this->_db->fetchRow($sql, array());
            if ($stmt) {
                return $stmt["total"];
            }
            return 0;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function expedientes() {
        try {
            $sql = $this->_db->select()
                    ->from("repositorio_index", array(new Zend_Db_Expr("COUNT(id) AS total")))
                    ->where("creado >= ?", date("Y-m-d"));
            $stmt = $this->_db->fetchRow($sql, array());
            if ($stmt) {
                return $stmt["total"];
            }
            return 0;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function expedientesPorHora($fechaIni, $fechaFin) {
        try {
            $sql = $this->_db->select()
                    ->from("repositorio_index", array(
                        new Zend_Db_Expr("SUM(CASE HOUR (creado) WHEN 7 THEN 1 ELSE 0 END) AS '0'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (creado) WHEN 8 THEN 1 ELSE 0 END) AS '1'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (creado) WHEN 9 THEN 1 ELSE 0 END) AS '2'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (creado) WHEN 10 THEN 1 ELSE 0 END) AS '3'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (creado) WHEN 11 THEN 1 ELSE 0 END) AS '4'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (creado) WHEN 12 THEN 1 ELSE 0 END) AS '5'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (creado) WHEN 13 THEN 1 ELSE 0 END) AS '6'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (creado) WHEN 14 THEN 1 ELSE 0 END) AS '7'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (creado) WHEN 15 THEN 1 ELSE 0 END) AS '8'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (creado) WHEN 16 THEN 1 ELSE 0 END) AS '9'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (creado) WHEN 17 THEN 1 ELSE 0 END) AS '10'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (creado) WHEN 18 THEN 1 ELSE 0 END) AS '11'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (creado) WHEN 19 THEN 1 ELSE 0 END) AS '12'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (creado) WHEN 20 THEN 1 ELSE 0 END) AS '13'"),
                    ))
                    ->where("creado >= ?", $fechaIni)
                    ->where("creado <= ?", $fechaFin);
            $stmt = $this->_db->fetchRow($sql, array());
            if ($stmt) {
                return $this->_transform($stmt);
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function edocsPorHora($fechaIni, $fechaFin) {
        try {
            $sql = $this->_db->select()
                    ->from("vucem_edoc", array(
                        new Zend_Db_Expr("SUM(CASE HOUR (enviado) WHEN 7 THEN 1 ELSE 0 END) AS '0'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (enviado) WHEN 8 THEN 1 ELSE 0 END) AS '1'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (enviado) WHEN 9 THEN 1 ELSE 0 END) AS '2'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (enviado) WHEN 10 THEN 1 ELSE 0 END) AS '3'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (enviado) WHEN 11 THEN 1 ELSE 0 END) AS '4'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (enviado) WHEN 12 THEN 1 ELSE 0 END) AS '5'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (enviado) WHEN 13 THEN 1 ELSE 0 END) AS '6'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (enviado) WHEN 14 THEN 1 ELSE 0 END) AS '7'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (enviado) WHEN 15 THEN 1 ELSE 0 END) AS '8'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (enviado) WHEN 16 THEN 1 ELSE 0 END) AS '9'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (enviado) WHEN 17 THEN 1 ELSE 0 END) AS '10'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (enviado) WHEN 18 THEN 1 ELSE 0 END) AS '11'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (enviado) WHEN 19 THEN 1 ELSE 0 END) AS '12'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (enviado) WHEN 20 THEN 1 ELSE 0 END) AS '13'"),
                    ))
                    ->where("estatus = 2")
                    ->where("enviado >= ?", $fechaIni)
                    ->where("enviado <= ?", $fechaFin);
            $stmt = $this->_db->fetchRow($sql, array());
            if ($stmt) {
                return $this->_transform($stmt);
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function edocs() {
        try {
            $sql = $this->_db->select()
                    ->from("vucem_edoc", array(new Zend_Db_Expr("COUNT(id) AS total")))
                    ->where("enviado >= ?", date("Y-m-d"));
            $stmt = $this->_db->fetchRow($sql, array());
            if ($stmt) {
                return $stmt["total"];
            }
            return 0;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function pagadosPorHora($fechaIni, $fechaFin) {
        try {
            $sql = $this->_db->select()
                    ->from("traficos", array(
                        new Zend_Db_Expr("SUM(CASE HOUR (actualizado) WHEN 7 THEN 1 ELSE 0 END) AS '0'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (actualizado) WHEN 8 THEN 1 ELSE 0 END) AS '1'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (actualizado) WHEN 9 THEN 1 ELSE 0 END) AS '2'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (actualizado) WHEN 10 THEN 1 ELSE 0 END) AS '3'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (actualizado) WHEN 11 THEN 1 ELSE 0 END) AS '4'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (actualizado) WHEN 12 THEN 1 ELSE 0 END) AS '5'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (actualizado) WHEN 13 THEN 1 ELSE 0 END) AS '6'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (actualizado) WHEN 14 THEN 1 ELSE 0 END) AS '7'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (actualizado) WHEN 15 THEN 1 ELSE 0 END) AS '8'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (actualizado) WHEN 16 THEN 1 ELSE 0 END) AS '9'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (actualizado) WHEN 17 THEN 1 ELSE 0 END) AS '10'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (actualizado) WHEN 18 THEN 1 ELSE 0 END) AS '11'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (actualizado) WHEN 19 THEN 1 ELSE 0 END) AS '12'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (actualizado) WHEN 20 THEN 1 ELSE 0 END) AS '13'"),
                    ))
                    ->where("fechaPago IS NOT NULL")
                    ->where("estatus NOT IN (4)")
                    ->where("actualizado >= ?", $fechaIni)
                    ->where("actualizado <= ?", $fechaFin);
            $stmt = $this->_db->fetchRow($sql, array());
            if ($stmt) {
                return $this->_transform($stmt);
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function pagados() {
        try {
            $sql = $this->_db->select()
                    ->from("traficos", array(new Zend_Db_Expr("COUNT(id) AS total")))
                    ->where("fechaPago IS NOT NULL")
                    ->where("estatus NOT IN (4)")
                    ->where("fechaPago >= ?", date("Y-m-d"));
            $stmt = $this->_db->fetchRow($sql, array());
            if ($stmt) {
                return $stmt["total"];
            }
            return 0;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function liberadosPorHoraPorAduana($fechaIni, $fechaFin, $idAduana) {
        try {
            $sql = $this->_db->select()
                    ->from("traficos", array(
                        new Zend_Db_Expr("SUM(CASE HOUR (actualizado) WHEN 7 THEN 1 ELSE 0 END) AS '0'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (actualizado) WHEN 8 THEN 1 ELSE 0 END) AS '1'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (actualizado) WHEN 9 THEN 1 ELSE 0 END) AS '2'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (actualizado) WHEN 10 THEN 1 ELSE 0 END) AS '3'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (actualizado) WHEN 11 THEN 1 ELSE 0 END) AS '4'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (actualizado) WHEN 12 THEN 1 ELSE 0 END) AS '5'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (actualizado) WHEN 13 THEN 1 ELSE 0 END) AS '6'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (actualizado) WHEN 14 THEN 1 ELSE 0 END) AS '7'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (actualizado) WHEN 15 THEN 1 ELSE 0 END) AS '8'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (actualizado) WHEN 16 THEN 1 ELSE 0 END) AS '9'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (actualizado) WHEN 17 THEN 1 ELSE 0 END) AS '10'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (actualizado) WHEN 18 THEN 1 ELSE 0 END) AS '11'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (actualizado) WHEN 19 THEN 1 ELSE 0 END) AS '12'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (actualizado) WHEN 20 THEN 1 ELSE 0 END) AS '13'"),
                    ))
                    ->where("estatus = 3")
                    ->where("idAduana = ?", $idAduana)
                    ->where("actualizado >= ?", $fechaIni)
                    ->where("actualizado <= ?", $fechaFin);
            $stmt = $this->_db->fetchRow($sql, array());
            if ($stmt) {
                return $this->_transform($stmt);
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function liberadosPorHora($fechaIni, $fechaFin) {
        try {
            $sql = $this->_db->select()
                    ->from("traficos", array(
                        new Zend_Db_Expr("SUM(CASE HOUR (actualizado) WHEN 7 THEN 1 ELSE 0 END) AS '0'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (actualizado) WHEN 8 THEN 1 ELSE 0 END) AS '1'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (actualizado) WHEN 9 THEN 1 ELSE 0 END) AS '2'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (actualizado) WHEN 10 THEN 1 ELSE 0 END) AS '3'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (actualizado) WHEN 11 THEN 1 ELSE 0 END) AS '4'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (actualizado) WHEN 12 THEN 1 ELSE 0 END) AS '5'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (actualizado) WHEN 13 THEN 1 ELSE 0 END) AS '6'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (actualizado) WHEN 14 THEN 1 ELSE 0 END) AS '7'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (actualizado) WHEN 15 THEN 1 ELSE 0 END) AS '8'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (actualizado) WHEN 16 THEN 1 ELSE 0 END) AS '9'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (actualizado) WHEN 17 THEN 1 ELSE 0 END) AS '10'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (actualizado) WHEN 18 THEN 1 ELSE 0 END) AS '11'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (actualizado) WHEN 19 THEN 1 ELSE 0 END) AS '12'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (actualizado) WHEN 20 THEN 1 ELSE 0 END) AS '13'"),
                    ))
                    ->where("estatus = 3")
                    ->where("actualizado >= ?", $fechaIni)
                    ->where("actualizado <= ?", $fechaFin);
            $stmt = $this->_db->fetchRow($sql, array());
            if ($stmt) {
                return $this->_transform($stmt);
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function liberados() {
        try {
            $sql = $this->_db->select()
                    ->from("traficos", array(new Zend_Db_Expr("COUNT(id) AS total")))
                    ->where("estatus = 3")
                    ->where("fechaLiberacion >= ?", date("Y-m-d"));
            $stmt = $this->_db->fetchRow($sql, array());
            if ($stmt) {
                return $stmt["total"];
            }
            return 0;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function firmas() {
        try {
            $sql = $this->_db->select()
                    ->from("archivos_validacion_firmas", array(new Zend_Db_Expr("COUNT(id) AS total")))
                    ->where("creado >= ?", date("Y-m-d"));
            $stmt = $this->_db->fetchRow($sql, array());
            if ($stmt) {
                return $stmt["total"];
            }
            return 0;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function firmasPorHora($fechaIni, $fechaFin) {
        try {
            $sql = $this->_db->select()
                    ->from("archivos_validacion_firmas", array(
                        new Zend_Db_Expr("SUM(CASE HOUR (creado) WHEN 7 THEN 1 ELSE 0 END) AS '0'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (creado) WHEN 8 THEN 1 ELSE 0 END) AS '1'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (creado) WHEN 9 THEN 1 ELSE 0 END) AS '2'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (creado) WHEN 10 THEN 1 ELSE 0 END) AS '3'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (creado) WHEN 11 THEN 1 ELSE 0 END) AS '4'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (creado) WHEN 12 THEN 1 ELSE 0 END) AS '5'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (creado) WHEN 13 THEN 1 ELSE 0 END) AS '6'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (creado) WHEN 14 THEN 1 ELSE 0 END) AS '7'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (creado) WHEN 15 THEN 1 ELSE 0 END) AS '8'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (creado) WHEN 16 THEN 1 ELSE 0 END) AS '9'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (creado) WHEN 17 THEN 1 ELSE 0 END) AS '10'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (creado) WHEN 18 THEN 1 ELSE 0 END) AS '11'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (creado) WHEN 19 THEN 1 ELSE 0 END) AS '12'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (creado) WHEN 19 THEN 1 ELSE 0 END) AS '13'"),
                    ))
                    ->where("creado >= ?", $fechaIni)
                    ->where("creado <= ?", $fechaFin);
            $stmt = $this->_db->fetchRow($sql, array());
            if ($stmt) {
                return $this->_transform($stmt);
            }
            return 0;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function pagos() {
        try {
            $sql = $this->_db->select()
                    ->from("archivos_validacion_pagos", array(new Zend_Db_Expr("COUNT(id) AS total")))
                    ->where("creado >= ?", date("Y-m-d"));
            $stmt = $this->_db->fetchRow($sql, array());
            if ($stmt) {
                return $stmt["total"];
            }
            return 0;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function pagosPorHora($fechaIni, $fechaFin) {
        try {
            $sql = $this->_db->select()
                    ->from("archivos_validacion_pagos", array(
                        new Zend_Db_Expr("SUM(CASE HOUR (creado) WHEN 7 THEN 1 ELSE 0 END) AS '0'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (creado) WHEN 8 THEN 1 ELSE 0 END) AS '1'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (creado) WHEN 9 THEN 1 ELSE 0 END) AS '2'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (creado) WHEN 10 THEN 1 ELSE 0 END) AS '3'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (creado) WHEN 11 THEN 1 ELSE 0 END) AS '4'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (creado) WHEN 12 THEN 1 ELSE 0 END) AS '5'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (creado) WHEN 13 THEN 1 ELSE 0 END) AS '6'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (creado) WHEN 14 THEN 1 ELSE 0 END) AS '7'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (creado) WHEN 15 THEN 1 ELSE 0 END) AS '8'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (creado) WHEN 16 THEN 1 ELSE 0 END) AS '9'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (creado) WHEN 17 THEN 1 ELSE 0 END) AS '10'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (creado) WHEN 18 THEN 1 ELSE 0 END) AS '11'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (creado) WHEN 19 THEN 1 ELSE 0 END) AS '12'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (creado) WHEN 20 THEN 1 ELSE 0 END) AS '13'"),
                    ))
                    ->where("creado >= ?", $fechaIni)
                    ->where("creado <= ?", $fechaFin);
            $stmt = $this->_db->fetchRow($sql, array());
            if ($stmt) {
                return $this->_transform($stmt);
            }
            return 0;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function transmitidos() {
        try {
            $sql = $this->_db->select()
                    ->from("archivos_validacion", array(new Zend_Db_Expr("COUNT(id) AS total")))
                    ->where("tipo = 'm3'")
                    ->where("creado >= ?", date("Y-m-d"));
            $stmt = $this->_db->fetchRow($sql, array());
            if ($stmt) {
                return $stmt["total"];
            }
            return 0;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function transmitidosPorHora($fechaIni, $fechaFin) {
        try {
            $sql = $this->_db->select()
                    ->from("archivos_validacion", array(
                        new Zend_Db_Expr("SUM(CASE HOUR (creado) WHEN 7 THEN 1 ELSE 0 END) AS '0'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (creado) WHEN 8 THEN 1 ELSE 0 END) AS '1'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (creado) WHEN 9 THEN 1 ELSE 0 END) AS '2'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (creado) WHEN 10 THEN 1 ELSE 0 END) AS '3'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (creado) WHEN 11 THEN 1 ELSE 0 END) AS '4'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (creado) WHEN 12 THEN 1 ELSE 0 END) AS '5'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (creado) WHEN 13 THEN 1 ELSE 0 END) AS '6'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (creado) WHEN 14 THEN 1 ELSE 0 END) AS '7'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (creado) WHEN 15 THEN 1 ELSE 0 END) AS '8'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (creado) WHEN 16 THEN 1 ELSE 0 END) AS '9'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (creado) WHEN 17 THEN 1 ELSE 0 END) AS '10'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (creado) WHEN 18 THEN 1 ELSE 0 END) AS '11'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (creado) WHEN 19 THEN 1 ELSE 0 END) AS '12'"),
                        new Zend_Db_Expr("SUM(CASE HOUR (creado) WHEN 20 THEN 1 ELSE 0 END) AS '13'"),
                    ))
                    ->where("tipo = 'm3'")
                    ->where("creado >= ?", $fechaIni)
                    ->where("creado <= ?", $fechaFin);
            $stmt = $this->_db->fetchRow($sql, array());
            if ($stmt) {
                return $this->_transform($stmt);
            }
            return 0;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    protected function _transform($stmt) {
        $arr = array();
        foreach ($stmt as $k => $v) {
            $arr[$k] = (int) $v;
        }
        return $arr;
    }

}
