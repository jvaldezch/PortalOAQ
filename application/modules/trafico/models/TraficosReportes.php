<?php

class Trafico_Model_TraficosReportes
{

    protected $_db_table;

    public function __construct()
    {
        $this->_db_table = new Trafico_Model_DbTable_Traficos();
    }

    public function obtenerPorAduanaGrafica($year, $month)
    {
        try {
            $sql = $this->_db_table->select()
                ->setIntegrityCheck(false)
                ->from(array("t" => "traficos"), array(
                    "a.nombre AS name",
                    "a.abbrv",
                    "a.patente",
                    "a.aduana",
                    new Zend_Db_Expr("count(t.id) AS y")
                ))
                ->joinLeft(array("a" => "trafico_aduanas"), "a.id = t.idAduana", array())
                ->where("YEAR(t.fechaPago) = ?", $year)
                ->where("MONTH(t.fechaPago) = ?", $month)
                ->where("t.fechaPago IS NOT NULL")
                ->where("t.estatus NOT IN (4)")
                ->group("t.idAduana");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                $arrl = [];
                $arr = [];
                foreach ($stmt->toArray() as $value) {
                    if ($value["abbrv"]) {
                        array_push($arrl, $value["abbrv"]);
                        $arr[] = array(
                            "name" => $value["abbrv"],
                            "y" => (int) $value["y"],
                        );
                    } else {
                        array_push($arrl, $value["aduana"] . '-' . $value['patente']);
                        $arr[] = array(
                            "name" => $value["aduana"] . '-' . $value['patente'],
                            "y" => (int) $value["y"],
                        );
                    }
                }
                return array(
                    "labels" => $arrl,
                    "data" => $arr
                );
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerRojosPorAduanaGrafica($year)
    {
        try {
            $sql = $this->_db_table->select()
                ->setIntegrityCheck(false)
                ->from(array("t" => "traficos"), array(
                    "a.nombre AS name",
                    "a.abbrv",
                    "a.patente",
                    "a.aduana",
                    new Zend_Db_Expr("SUM(CASE WHEN t.semaforo = 2 THEN 1 ELSE 0 END) AS y")
                ))
                ->joinLeft(array("a" => "trafico_aduanas"), "a.id = t.idAduana", array())
                ->where("YEAR(t.fechaPago) = ?", $year)
                ->where("t.fechaPago IS NOT NULL")
                ->where("t.estatus NOT IN (4)")
                ->group("t.idAduana");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                $arrl = [];
                $arr = [];
                foreach ($stmt->toArray() as $value) {
                    if ($value["abbrv"] && $value["y"] > 0) {
                        array_push($arrl, $value["abbrv"]);
                        $arr[] = array(
                            "name" => $value["abbrv"],
                            "y" => (int) $value["y"],
                        );
                    } else if ($value["y"] > 0) {
                        array_push($arrl, $value["aduana"] . '-' . $value['patente']);
                        $arr[] = array(
                            "name" => $value["aduana"] . '-' . $value['patente'],
                            "y" => (int) $value["y"],
                        );
                    }
                }
                return array(
                    "labels" => $arrl,
                    "data" => $arr
                );
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerPorDiasGraficaAereas($year, $month, $tipoAduana)
    {
        try {
            $fields = array(
                new Zend_Db_Expr("SUM(CASE WHEN datediff(t.fechaLiberacion, t.fechaRevalidacion) <= 2 THEN 1 ELSE 0 END) AS 2dias"),
                new Zend_Db_Expr("SUM(CASE WHEN datediff(t.fechaLiberacion, t.fechaRevalidacion) > 2 AND datediff(t.fechaLiberacion, t.fechaRevalidacion) <= 5 THEN 1 ELSE 0 END) AS 5dias"),
                new Zend_Db_Expr("SUM(CASE WHEN datediff(t.fechaLiberacion, t.fechaRevalidacion) > 5 AND datediff(t.fechaLiberacion, t.fechaRevalidacion) <= 7 THEN 1 ELSE 0 END) AS 7dias"),
                new Zend_Db_Expr("SUM(CASE WHEN datediff(t.fechaLiberacion, t.fechaRevalidacion) > 7 THEN 1 ELSE 0 END) AS mayor"),
            );
            $sql = $this->_db_table->select()
                ->setIntegrityCheck(false)
                ->from(array("t" => "traficos"), $fields)
                ->joinLeft(array("a" => "trafico_aduanas"), "a.id = t.idAduana", array())
                ->where("YEAR(t.fechaLiberacion) = ?", $year)
                ->where("MONTH(t.fechaLiberacion) = ?", $month)
                ->where("a.tipoAduana = ?", $tipoAduana)
                ->where("(t.fechaLiberacion IS NOT NULL AND t.fechaRevalidacion IS NOT NULL AND t.fechaEnvioDocumentos IS NOT NULL)")
                ->where("t.estatus = 3");
            if ($tipoAduana) {
                $sql->where("t.cvePedimento NOT IN ('A3', 'E1', 'F4', 'F5', 'G1', 'V1', 'F5')");
            }
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerPorDiasGraficaMaritimas($year, $month)
    {
        try {
            $fields = array(
                new Zend_Db_Expr("SUM(CASE WHEN datediff(t.fechaLiberacion, t.fechaRevalidacion) <= 2 THEN 1 ELSE 0 END) AS 2dias"),
                new Zend_Db_Expr("SUM(CASE WHEN datediff(t.fechaLiberacion, t.fechaRevalidacion) > 2 AND datediff(t.fechaLiberacion, t.fechaRevalidacion) <= 5 THEN 1 ELSE 0 END) AS 5dias"),
                new Zend_Db_Expr("SUM(CASE WHEN datediff(t.fechaLiberacion, t.fechaRevalidacion) > 5 AND datediff(t.fechaLiberacion, t.fechaRevalidacion) <= 7 THEN 1 ELSE 0 END) AS 7dias"),
                new Zend_Db_Expr("SUM(CASE WHEN datediff(t.fechaLiberacion, t.fechaRevalidacion) > 7 THEN 1 ELSE 0 END) AS mayor"),
            );
            $sql = $this->_db_table->select()
                ->setIntegrityCheck(false)
                ->from(array("t" => "traficos"), $fields)
                ->joinLeft(array("a" => "trafico_aduanas"), "a.id = t.idAduana", array())
                ->where("YEAR(t.fechaLiberacion) = ?", $year)
                ->where("MONTH(t.fechaLiberacion) = ?", $month)
                ->where("a.tipoAduana = 3")
                ->where("(t.fechaLiberacion IS NOT NULL AND t.fechaRevalidacion IS NOT NULL AND t.fechaEnvioDocumentos IS NOT NULL)")
                ->where("t.estatus = 3");
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerPorDiasGraficaTerrestres($year, $month)
    {
        try {
            $fields = array(
                new Zend_Db_Expr("SUM(CASE WHEN datediff(t.fechaLiberacion, t.fechaRevalidacion) <= 2 THEN 1 ELSE 0 END) AS 2dias"),
                new Zend_Db_Expr("SUM(CASE WHEN datediff(t.fechaLiberacion, t.fechaRevalidacion) > 2 AND datediff(t.fechaLiberacion, t.fechaRevalidacion) <= 5 THEN 1 ELSE 0 END) AS 5dias"),
                new Zend_Db_Expr("SUM(CASE WHEN datediff(t.fechaLiberacion, t.fechaRevalidacion) > 5 AND datediff(t.fechaLiberacion, t.fechaRevalidacion) <= 7 THEN 1 ELSE 0 END) AS 7dias"),
                new Zend_Db_Expr("SUM(CASE WHEN datediff(t.fechaLiberacion, t.fechaRevalidacion) > 7 THEN 1 ELSE 0 END) AS mayor"),
            );
            $sql = $this->_db_table->select()
                ->setIntegrityCheck(false)
                ->from(array("t" => "traficos"), $fields)
                ->joinLeft(array("a" => "trafico_aduanas"), "a.id = t.idAduana", array())
                ->where("YEAR(t.fechaLiberacion) = ?", $year)
                ->where("MONTH(t.fechaLiberacion) = ?", $month)
                ->where("a.tipoAduana = 4")
                ->where("(t.fechaLiberacion IS NOT NULL AND t.fechaRevalidacion IS NOT NULL AND t.fechaEnvioDocumentos IS NOT NULL)")
                ->where("t.estatus = 3");
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerTipoOperacionesGrafica($year, $month, $idCliente = null, $idAduana = null)
    {
        try {
            $fields = array(
                new Zend_Db_Expr("SUM(CASE WHEN a.tipoAduana = 1 AND t.cvePedimento IN ('A3', 'E1', 'F4', 'F5', 'G1', 'V1', 'F5') THEN 1 ELSE 0 END) AS especiales"),
                new Zend_Db_Expr("SUM(CASE WHEN a.tipoAduana = 1 AND t.cvePedimento NOT IN ('A3', 'E1', 'F4', 'F5', 'G1', 'V1', 'F5') THEN 1 ELSE 0 END) AS aereas"),
                new Zend_Db_Expr("SUM(CASE WHEN a.tipoAduana = 3 THEN 1 ELSE 0 END) AS maritimas"),
                new Zend_Db_Expr("SUM(CASE WHEN a.tipoAduana = 4 THEN 1 ELSE 0 END) AS terrestres"),
            );
            $sql = $this->_db_table->select()
                ->setIntegrityCheck(false)
                ->from(array("t" => "traficos"), $fields)
                ->joinLeft(array("a" => "trafico_aduanas"), "a.id = t.idAduana", array())
                ->where("YEAR(t.fechaLiberacion) = ?", $year)
                ->where("MONTH(t.fechaLiberacion) = ?", $month)
                ->where("t.fechaLiberacion IS NOT NULL")
                ->where("t.estatus = 3");
            if ($idCliente) {
                $sql->where('t.idCliente = ?', $idCliente);
            }
            if ($idAduana) {
                $sql->where('t.idAduana = ?', $idAduana);
            }
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerLiberadosVsCompleto($year, $month)
    {
        try {
            $fields = array(
                new Zend_Db_Expr("SUM(CASE WHEN fechaLiberacion IS NOT NULL THEN 1 ELSE 0 END) AS liberados"),
                new Zend_Db_Expr("SUM(CASE WHEN fechaRevalidacion IS NULL THEN 1 ELSE 0 END) AS sinRevalidacion"),
                new Zend_Db_Expr("SUM(CASE WHEN fechaPago IS NULL THEN 1 ELSE 0 END) AS sinPago"),
                new Zend_Db_Expr("SUM(CASE WHEN fechaEnvioDocumentos IS NULL THEN 1 ELSE 0 END) AS sinEnvioDocumentos"),
                new Zend_Db_Expr("SUM(CASE WHEN (fechaEnvioDocumentos IS NOT NULL AND fechaRevalidacion IS NOT NULL) THEN 1 ELSE 0 END) AS completo"),
            );
            $sql = $this->_db_table->select()
                ->from($this->_db_table, $fields)
                ->where("estatus = 3")
                ->where("fechaLiberacion IS NOT NULL")
                ->where("YEAR(fechaLiberacion) = ?", $year)
                ->where("MONTH(fechaLiberacion) = ?", $month);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerLiberadosGrafica($year, $idCliente = null, $idAduana = null)
    {
        try {
            $fields = array(
                new Zend_Db_Expr("SUM(CASE WHEN MONTH(fechaLiberacion) = 1 THEN 1 ELSE 0 END) AS Ene"),
                new Zend_Db_Expr("SUM(CASE WHEN MONTH(fechaLiberacion) = 2 THEN 1 ELSE 0 END) AS Feb"),
                new Zend_Db_Expr("SUM(CASE WHEN MONTH(fechaLiberacion) = 3 THEN 1 ELSE 0 END) AS Mar"),
                new Zend_Db_Expr("SUM(CASE WHEN MONTH(fechaLiberacion) = 4 THEN 1 ELSE 0 END) AS Abr"),
                new Zend_Db_Expr("SUM(CASE WHEN MONTH(fechaLiberacion) = 5 THEN 1 ELSE 0 END) AS May"),
                new Zend_Db_Expr("SUM(CASE WHEN MONTH(fechaLiberacion) = 6 THEN 1 ELSE 0 END) AS Jun"),
                new Zend_Db_Expr("SUM(CASE WHEN MONTH(fechaLiberacion) = 7 THEN 1 ELSE 0 END) AS Jul"),
                new Zend_Db_Expr("SUM(CASE WHEN MONTH(fechaLiberacion) = 8 THEN 1 ELSE 0 END) AS Ago"),
                new Zend_Db_Expr("SUM(CASE WHEN MONTH(fechaLiberacion) = 9 THEN 1 ELSE 0 END) AS Sep"),
                new Zend_Db_Expr("SUM(CASE WHEN MONTH(fechaLiberacion) = 10 THEN 1 ELSE 0 END) AS 'Oct'"),
                new Zend_Db_Expr("SUM(CASE WHEN MONTH(fechaLiberacion) = 11 THEN 1 ELSE 0 END) AS Nov"),
                new Zend_Db_Expr("SUM(CASE WHEN MONTH(fechaLiberacion) = 12 THEN 1 ELSE 0 END) AS Dic"),
            );
            $sql = $this->_db_table->select()
                ->from($this->_db_table, $fields)
                ->where("estatus = 3")
                ->where("fechaLiberacion IS NOT NULL")
                ->where("YEAR(fechaLiberacion) = ?", $year);
            if ($idCliente) {
                $sql->where('idCliente = ?', $idCliente);
            }
            if ($idAduana) {
                $sql->where('idAduana = ?', $idAduana);
            }
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerPagadosGrafica($year, $idCliente = null, $idAduana = null)
    {
        try {
            $fields = array(
                new Zend_Db_Expr("SUM(CASE WHEN MONTH(fechaPago) = 1 THEN 1 ELSE 0 END) AS Ene"),
                new Zend_Db_Expr("SUM(CASE WHEN MONTH(fechaPago) = 2 THEN 1 ELSE 0 END) AS Feb"),
                new Zend_Db_Expr("SUM(CASE WHEN MONTH(fechaPago) = 3 THEN 1 ELSE 0 END) AS Mar"),
                new Zend_Db_Expr("SUM(CASE WHEN MONTH(fechaPago) = 4 THEN 1 ELSE 0 END) AS Abr"),
                new Zend_Db_Expr("SUM(CASE WHEN MONTH(fechaPago) = 5 THEN 1 ELSE 0 END) AS May"),
                new Zend_Db_Expr("SUM(CASE WHEN MONTH(fechaPago) = 6 THEN 1 ELSE 0 END) AS Jun"),
                new Zend_Db_Expr("SUM(CASE WHEN MONTH(fechaPago) = 7 THEN 1 ELSE 0 END) AS Jul"),
                new Zend_Db_Expr("SUM(CASE WHEN MONTH(fechaPago) = 8 THEN 1 ELSE 0 END) AS Ago"),
                new Zend_Db_Expr("SUM(CASE WHEN MONTH(fechaPago) = 9 THEN 1 ELSE 0 END) AS Sep"),
                new Zend_Db_Expr("SUM(CASE WHEN MONTH(fechaPago) = 10 THEN 1 ELSE 0 END) AS 'Oct'"),
                new Zend_Db_Expr("SUM(CASE WHEN MONTH(fechaPago) = 11 THEN 1 ELSE 0 END) AS Nov"),
                new Zend_Db_Expr("SUM(CASE WHEN MONTH(fechaPago) = 12 THEN 1 ELSE 0 END) AS Dic"),
            );
            $sql = $this->_db_table->select()
                ->from($this->_db_table, $fields)
                ->where("estatus = 3")
                ->where("YEAR(fechaPago) = ?", $year);
            if ($idCliente) {
                $sql->where('idCliente = ?', $idCliente);
            }
            if ($idAduana) {
                $sql->where('idAduana = ?', $idAduana);
            }
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerLiberadosPorFecha($yesterday, $idCliente = null, $idAduana = null, $today = null)
    {
        try {
            $sql = $this->_db_table->select()
                ->setIntegrityCheck(false)
                ->from(array("t" => "traficos"), array(
                    "t.idCliente", 
                    "count(*) AS total", 
                    new Zend_Db_Expr("SUM(CASE WHEN t.semaforo = 2 THEN 1 ELSE 0 END) AS rojos"),
                    new Zend_Db_Expr("SUM(CASE WHEN t.ie = 'TOCE.IMP' THEN 1 ELSE 0 END) AS impos"),
                    new Zend_Db_Expr("SUM(CASE WHEN t.ie = 'TOCE.EXP' THEN 1 ELSE 0 END) AS expos"),
                    "c.nombre AS razonSocial",
                    "a.nombre AS nombreAduana",
                ))
                ->where("t.estatus = 3")
                ->where("t.fechaLiberacion IS NOT NULL")                
                ->joinLeft(array("c" => "trafico_clientes"), "c.id = t.idCliente", array())
                ->joinLeft(array("a" => "trafico_aduanas"), "a.id = t.idAduana", array())
                ->group(array("t.idAduana", "t.idCliente", "t.ie"))
                ->order(array("t.idAduana ASC", "c.nombre ASC", "t.ie DESC"));
            if (!isset($today)) {
                $sql->where("t.fechaLiberacion BETWEEN '{$yesterday} 00:00:00' AND '{$yesterday} 23:59:59' ");
            } else {
                $sql->where("t.fechaLiberacion BETWEEN '{$yesterday} 00:00:00' AND '{$today} 23:59:59' ");
            }
            if ($idCliente) {
                $sql->where('t.idCliente = ?', $idCliente);
            }
            if ($idAduana) {
                $sql->where('t.idAduana = ?', $idAduana);
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

    public function obtenerNoLiberadosPorFecha($yesterday, $idCliente = null, $idAduana = null, $today = null)
    {
        try {
            $sql = $this->_db_table->select()
                ->setIntegrityCheck(false)
                ->from(array("t" => "traficos"), array(
                    "t.idCliente", 
                    "count(*) AS total", 
                    new Zend_Db_Expr("SUM(CASE WHEN t.semaforo = 2 THEN 1 ELSE 0 END) AS rojos"),
                    new Zend_Db_Expr("SUM(CASE WHEN t.ie = 'TOCE.IMP' THEN 1 ELSE 0 END) AS impos"),
                    new Zend_Db_Expr("SUM(CASE WHEN t.ie = 'TOCE.EXP' THEN 1 ELSE 0 END) AS expos"),
                    "c.nombre AS razonSocial",
                    "a.nombre AS nombreAduana",
                ))
                ->where("t.estatus NOT IN (4)")                
                ->where("t.fechaLiberacion IS NULL")                
                ->joinLeft(array("c" => "trafico_clientes"), "c.id = t.idCliente", array())
                ->joinLeft(array("a" => "trafico_aduanas"), "a.id = t.idAduana", array())
                ->where("t.fechaEta BETWEEN '{$yesterday} 00:00:00' AND '{$today} 23:59:59' ")
                ->where("t.idAduana IS NOT NULL")
                ->group(array("t.idAduana", "t.idCliente", "t.ie"))
                ->order(array("t.idAduana ASC", "c.nombre ASC", "t.ie DESC"));
            if ($idCliente) {
                $sql->where('t.idCliente = ?', $idCliente);
            }
            if ($idAduana) {
                $sql->where('t.idAduana = ?', $idAduana);
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
}
