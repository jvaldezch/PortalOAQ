<?php

/**
 * Description of EmailNotifications
 *
 * @author Jaime E. Valdez <jvaldezch@gmail.com>
 */
class OAQ_SitawinCargoQuin {

    protected $_db;

    function __construct($host = null, $username = null, $pwd = null, $dbname = null, $port = null, $adapter = null) {
        try {
            if (strtoupper(substr(PHP_OS, 0, 3)) === "WIN") {
                $this->_db = Zend_Db::factory("SqlSrv", array(
                        "host" => $host,
                        "username" => $username,
                        "password" => $pwd,
                        "port" => $port,
                        "dbname" => $dbname,
                ));
            } else {
                $this->_db = new Zend_Db_Adapter_Pdo_Mssql(array(
                    "host" => $host,
                    "username" => $username,
                    "password" => $pwd,
                    "dbname" => $dbname,
                    "port" => $port,
                    "pdoType" => "dblib"
                ));
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    protected function _guias($referencia) {
        try {
            $select = $this->_db->select()
                    ->distinct()
                    ->from(array("s" => "SM3GUIA"), array("IDGUIA AS tipoGuia", "NUMGUIA as guia"))
                    ->where("s.NUM_REF = ?", $referencia);
            $stmt = $this->_db->fetchAll($select);
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

    public function _grupoCargoQuinFactura($referencia, $ie) {
        try {
            if ((int) $ie == 1) {
                $prov = new Zend_Db_Expr("(SELECT TOP 1 pr.NOMPRO FROM CMPRO pr WHERE pr.CVE_PRO = f.CVEPROV) as proveedor");
            } elseif ((int) $ie == 2) {
                $prov = new Zend_Db_Expr("(SELECT TOP 1 ds.NOMPRO FROM CMDEST ds WHERE ds.CVE_PRO = f.CVEPROV) as proveedor");
            }
            $select = $this->_db->select()
                    ->from(array("f" => "SM3FACT"), array("f.NUMFAC as numFactura", $prov, "f.VALDLS as valorDolares", "f.INCOTER AS incoterm", "f.MONFAC as divisa", "f.FACEQ as factorEquivalencia", "FVINCULA as vinculacion"))
                    ->where("f.NUM_REF = ?", $referencia);
            $stmt = $this->_db->fetchAll($select, array());
            if ($stmt) {
                $data = array();
                foreach ($stmt as $item) {
                    $item["cantidadFactura"] = $this->_grupoCargoQuinTotalCantidad($referencia);
                    $item["partes"] = $this->_grupoCargoQuinPartes($referencia, $item["numFactura"]);
                    $data[] = $item;
                }
                return $data;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function _grupoCargoQuinPartes($referencia, $numFactura) {
        try {
            $select = $this->_db->select()
                    ->from(array("fr" => "CM3FRA"), array("fr.PARTE as numParte", "fr.CANTFAC as cantidadFactura", "fr.VALCOM as valorComercial"))
                    ->where("fr.NUM_REF = ?", $referencia)
                    ->where("fr.FACTFRA = ?", $numFactura);
            $stmt = $this->_db->fetchAll($select, array());
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function _grupoCargoQuinTotalCantidad($referencia) {
        try {
            $select = $this->_db->select()
                    ->from(array("fr" => "CM3FRA"), array("sum(fr.CANTFAC) as totalFactura"))
                    ->where("fr.NUM_REF = ?", $referencia);
            $stmt = $this->_db->fetchRow($select, array());
            if ($stmt) {
                return $stmt["totalFactura"];
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function _totalItems($referencia) {
        try {
            $select = $this->_db->select()
                    ->from(array("f" => "CM3FRA"), array("SUM(f.CANTFAC) as cantidad"))
                    ->where("f.NUM_REF = ?", $referencia);
            $stmt = $this->_db->fetchRow($select, array());
            if ($stmt) {
                return (int) $stmt["cantidad"];
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function _totalPartes($referencia) {
        try {
            $select = $this->_db->select()
                    ->from(array("f" => "CM3FRA"), array("COUNT(f.CANTFAC) as cantidad"))
                    ->where("f.NUM_REF = ?", $referencia);
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                return (int) $stmt["cantidad"];
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param type $rfc
     * @param type $year
     * @param type $mes
     * @param type $fechaIni
     * @param type $fechaFin
     * @return boolean
     * @throws Exception
     */
    public function grupoCargoQuin($rfc, $year = null, $mes = null, $fechaIni = null, $fechaFin = null) {
        try {
            $month = new Zend_Db_Expr("MONTH(P.FEC_PAG) AS mes");
            $pedimento = new Zend_Db_Expr("RIGHT(YEAR(p.FEC_PAG),2) + '-' + p.ADUANAD + '-' + CAST(p.PATENTE AS VARCHAR(4)) + '-' + CAST(p.NUM_PED AS VARCHAR(7)) AS operacion");
            $aduana = new Zend_Db_Expr("CAST(p.ADUANAD AS VARCHAR(2)) + CAST(p.SECCDES AS VARCHAR(1)) AS aduana");
            $placas = new Zend_Db_Expr("(SELECT TOP 1 T.IDETRAN FROM SM3TRANS T WHERE T.NUM_REF = P.NUM_REF) AS placas");
            $totalRegistros = new Zend_Db_Expr("(SELECT COUNT(*) FROM CM3FRA WHERE NUM_REF = p.NUM_REF) as totalRegistros");
            $sumatoria = new Zend_Db_Expr("(SELECT SUM(CANTFAC) FROM CM3FRA WHERE NUM_REF = p.NUM_REF) as sumatoria");
            $select = $this->_db->select()
                    ->from(array("p" => "SM3PED"), array("p.NUM_PED as pedimento", "p.NUM_REF as referencia", "p.TIP_CAM as tipoCambio", $month, $pedimento, new Zend_Db_Expr("CONVERT(VARCHAR(10), p.FEC_PAG, 103) AS fechaImportacion"), $aduana, $placas, new Zend_Db_Expr("CONVERT(VARCHAR(10), p.FEC_ENT, 103) AS fechaEntrada"), "p.BULTOS AS bultos", new Zend_Db_Expr("CONVERT(VARCHAR(10), p.FEC_PAG, 103) AS fechaPago"), "p.IMP_EXP as ie", "p.VALMEDLLS as valorAduana", "p.VALMN_PAG as valorAduanaMxn", "p.CNT_TOT as cnt", "p.DTA_TOT as dta", "p.PRE_TOT as prev", "p.FLETES AS fletes", $totalRegistros, $sumatoria, "p.PESBRU as peso"))
                    ->joinLeft(array("b" => "SAIBAN"), "p.NUM_PED = b.DOCTO", array())
                    ->where("p.RFCCTE = ?", $rfc)
                    ->where("(p.FIRMA IS NOT NULL AND p.FIRMA <> '' AND b.FIRMA IS NOT NULL)")
                    ->order("p.FEC_PAG ASC");
            if (isset($mes) & isset($year)) {
                $select->where("YEAR(p.FEC_PAG) = ?", $year)
                        ->where("MONTH(P.FEC_PAG) = ?", $mes);
            } elseif (isset($fechaIni) && isset($fechaFin)) {
                $select->where("p.FEC_PAG >= '{$fechaIni}'")
                        ->where("p.FEC_PAG <= '{$fechaFin}'");
            } else {
                throw new Exception("Not parameters set!");
            }
            $stmt = $this->_db->fetchAll($select, array());
            if ($stmt) {
                $data = array();
                foreach ($stmt as $item) {
                    $item["guias"] = $this->_guias($item["referencia"]);
                    $item["facturas"] = $this->_grupoCargoQuinFactura($item["referencia"], $item["ie"]);
                    $item["totalItems"] = $this->_totalItems($item["referencia"]);
                    $item["totalPartes"] = $this->_totalPartes($item["referencia"]);
                    $data[] = $item;
                }
                return $data;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param type $rfc
     * @param type $year
     * @param type $mes
     * @param type $fechaIni
     * @param type $fechaFin
     * @return boolean
     * @throws Exception
     */
    public function grupoCargoQuinFracciones($rfc, $year = null, $mes = null, $fechaIni = null, $fechaFin = null) {
        try {
            $select = $this->_db->select()
                    ->from(array("p" => "SM3PED"), array("p.NUM_REF as referencia", "p.NUM_PED as pedimento"))
                    ->joinleft(array("f" => "SM3FACT"), "f.NUM_REF = p.NUM_REF", array("f.NUMFAC as numFactura", "f.ORDENFAC as ordenFactura"))
                    ->joinLeft(array("fr" => "CM3FRA"), "fr.NUM_REF = p.NUM_REF AND f.NUMFAC = fr.FACTFRA", array("fr.PARTE as numParte", "fr.CANTFAC as cantidadFactura", "fr.VALCOM as valorComercial", "fr.DESC1 as descripcion", "fr.PAIORI as paisOrigen", "fr.UMC as umc", new Zend_Db_Expr("(fr.VALCOM / fr.CANTFAC) as precioUnitario"), "fr.VALCOM as valorComercial", "fr.CANTFAC as cantidadFactura"))
                    ->joinLeft(array("s" => "SM3FRA"), "p.NUM_REF = s.NUM_REF AND fr.ORDENAGRU = s.ORDEN", array("s.TASAADV as igi", "s.IMPOADV as importeIgi", "s.TASAIVA as iva", "s.IMPOIVA as importeIva"))
                    ->joinLeft(array("b" => "SAIBAN"), "p.NUM_PED = b.DOCTO", array())
                    ->where("p.RFCCTE = ?", $rfc)
                    ->where("(p.FIRMA IS NOT NULL AND p.FIRMA <> '' AND b.FIRMA IS NOT NULL)")
                    ->order("p.FEC_PAG ASC");
            if (isset($mes) & isset($year)) {
                $select->where("YEAR(p.FEC_PAG) = ?", $year)
                        ->where("MONTH(P.FEC_PAG) = ?", $mes);
            } elseif (isset($fechaIni) && isset($fechaFin)) {
                $select->where("p.FEC_PAG >= '{$fechaIni}'")
                        ->where("p.FEC_PAG <= '{$fechaFin}'");
            } else {
                throw new Exception("Not parameters set!");
            }
            $stmt = $this->_db->fetchAll($select, array());
            if ($stmt) {
                $data = array();
                foreach ($stmt as $item) {
                    $data[] = $item;
                }
                return $data;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param type $rfc
     * @param type $year
     * @param type $mes
     * @param type $fechaIni
     * @param type $fechaFin
     * @return boolean
     * @throws Exception
     */
    public function grupoCargoQuinPartes($rfc, $year = null, $mes = null, $fechaIni = null, $fechaFin = null) {
        try {
            $select = $this->_db->select()
                    ->from(array("p" => "SM3PED"), array("p.NUM_REF as referencia", "p.NUM_PED as pedimento"))
                    ->joinleft(array("f" => "SM3FACT"), "f.NUM_REF = p.NUM_REF", array())
                    ->joinLeft(array("fr" => "CM3FRA"), "fr.NUM_REF = p.NUM_REF AND f.NUMFaC = fr.FACTFRA", array("fr.PARTE as numParte", "fr.CANTTAR as cantidadTarifa", "fr.CANTFAC as cantidadFactura", "fr.UMC as umc", "fr.CAN_OMA as cantidadOma", "fr.UMC_OMA as oma"))
                    ->joinLeft(array("b" => "SAIBAN"), "p.NUM_PED = b.DOCTO", array())
                    ->where("p.RFCCTE = ?", $rfc)
                    ->where("(p.FIRMA IS NOT NULL AND p.FIRMA <> '' AND b.FIRMA IS NOT NULL)")
                    ->order("p.FEC_PAG ASC");
            if (isset($mes) & isset($year)) {
                $select->where("YEAR(p.FEC_PAG) = ?", $year)
                        ->where("MONTH(P.FEC_PAG) = ?", $mes);
            } elseif (isset($fechaIni) && isset($fechaFin)) {
                $select->where("p.FEC_PAG >= '{$fechaIni}'")
                        ->where("p.FEC_PAG <= '{$fechaFin}'");
            } else {
                throw new Exception("Not parameters set!");
            }
            $stmt = $this->_db->fetchAll($select, array());
            if ($stmt) {
                $data = array();
                foreach ($stmt as $item) {
                    $data[] = $item;
                }
                return $data;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    /**
     * 
     * @param type $rfc
     * @param type $year
     * @param type $mes
     * @param type $fechaIni
     * @param type $fechaFin
     * @return boolean
     * @throws Exception
     */
    public function cnhIndustrial($rfc, $year = null, $mes = null, $fechaIni = null, $fechaFin = null) {
        try {
            $aduana = new Zend_Db_Expr("CAST(p.ADUANAD AS VARCHAR(2)) + CAST(p.SECCDES AS VARCHAR(1)) AS aduana");
            $efectivo = new Zend_Db_Expr("((CASE DTA_FP WHEN 0 THEN DTA_TOT ELSE 0 end) +  (CASE DTA_FPADI WHEN 0 THEN  DTA_TLADI ELSE 0 end) + (CASE CC1_FP WHEN 0 THEN CC1_TOT ELSE 0 end) +  (CASE CC2_FP WHEN 0 THEN CC2_TOT ELSE 0 end) +  (CASE IVA1_FP WHEN 0 THEN IVA1_TOT ELSE 0 end) +  (CASE IVA2_FP WHEN 0 THEN IVA2_TOT ELSE 0 end) +  (CASE ISAN_FP WHEN 0 THEN ISAN_TOT ELSE 0 end) +  (CASE IEPS_FP WHEN 0 THEN IEPS_TOT ELSE 0 end) +  (CASE REC_FP WHEN 0 THEN REC_TOT ELSE 0 end) +  (CASE OTR_FP WHEN 0 THEN OTR_TOT ELSE 0 end) +  (CASE GAR_FP WHEN 0 THEN GAR_TOT ELSE 0 end) +  (CASE MUL_FP WHEN 0 THEN MUL_TOT ELSE 0 end) +  (CASE MUL2_FP WHEN 0 THEN MUL2_TOT ELSE 0 end) +  (CASE DTI_FP WHEN 0 THEN DTI_TOT ELSE 0 end) +  (CASE IGIR_FP WHEN 0 THEN IGIR_TOT ELSE 0 end) +  (CASE PRE_FP WHEN 0 THEN PRE_TOT ELSE 0 end) +  (CASE BSS_FP WHEN 0 THEN BSS_TOT ELSE 0 end) +  (CASE EUR_FP WHEN 0 THEN EUR_TOT ELSE 0 end) +  (CASE ECI_FP WHEN 0 THEN ECI_TOT ELSE 0 end) +  (CASE ITV_FP WHEN 0 THEN ITV_TOT ELSE 0 end) +  (CASE IGIR_FP2 WHEN 0 THEN IGIR_TOT2 ELSE 0 end) +  (CASE REC2_FP WHEN 0 THEN REC2_TOT ELSE 0 end)) AS totalEfectivo");
            $select = $this->_db->select()
                    ->from(array("p" => "SM3PED"), array("p.NUM_PED as pedimento", "p.NUM_REF as referencia", "p.PATENTE as patente", "p.TIP_CAM as tipoCambio", "p.CVEPEDIM as cvePedimento", "p.RFCCTE as rfcCliente", "p.DOMER as destino", "p.REGIMEN as regimen", "p.MEDTRAS AS transporteEntrada", "p.MEDTRAA AS transporteArribo", "p.MEDTRAE AS transporteSalida", "(p.VALMEDLLS * p.FACAJU) AS valorDolares", "p.VALADUANA AS valorAduana", "p.VALMN_PAG AS valorComercial", new Zend_Db_Expr("CONVERT(VARCHAR(10), p.FEC_PAG, 103) AS fechaImportacion"), $aduana, new Zend_Db_Expr("CONVERT(VARCHAR(10), p.FEC_ENT, 103) AS fechaEntrada"), "p.BULTOS AS bultos", new Zend_Db_Expr("CONVERT(VARCHAR(10), p.FEC_PAG, 103) AS fechaPago"), "p.IMP_EXP as ie", "p.VALMEDLLS as valorAduana", "p.VALMN_PAG as valorAduanaMxn", "p.CNT_TOT as cnt", "p.IVA1_TOT AS iva", "p.ISAN_TOT as isan", "IEPS_TOT as ieps", "p.IGIE_TOT as igi", "p.DTA_TOT as dta", "p.PRE_TOT as prev", "p.FLETES AS fletes", "p.PESBRU as peso", "SEGUROS AS seguros", "EMBALAJ AS embalajes", "OTROINC AS otrosIncrementables", "p.MUL_TOT as multas", "p.MUL_FP as formaMultas", "p.IVA1_FP as formaIva", "p.DTA_FP as formaDta", "p.ISAN_FP as formaIsan", "p.IEPS_FP as formaIeps", "p.OTR_TOT as otros", "p.OTR_FP as formaOtros", "GAR_TOT as garantias", "GAR_FP as formaGarantias", "p.REC_TOT as recargos", "p.PRE_FP as formaPrev", new Zend_Db_Expr("p.REC_FP as formaRecargos"), $efectivo, new Zend_Db_Expr("(SELECT TOP 1 r.PEDORI FROM SM3PREV r WHERE r.NUM_REF = p.NUM_REF) as pedimentoOriginal"), "p.CNT_FP as formaCnt"))
                    ->joinLeft(array("b" => "SAIBAN"), "p.NUM_PED = b.DOCTO", array())
                    ->where("p.RFCCTE = ?", $rfc)
                    ->order("p.FEC_PAG ASC");
            if (isset($mes) & isset($year)) {
                $select->where("YEAR(p.FEC_PAG) = ?", $year)
                        ->where("MONTH(P.FEC_PAG) = ?", $mes);
            } elseif (isset($fechaIni) && isset($fechaFin)) {
                $select->where("p.FEC_PAG >= '{$fechaIni}'")
                        ->where("p.FEC_PAG <= '{$fechaFin}'");
            } else {
                throw new Exception("Not parameters set!");
            }
            $stmt = $this->_db->fetchAll($select, array());
            if ($stmt) {
                $data = array();
                foreach ($stmt as $item) {
                    $item["guias"] = $this->_guias($item["referencia"]);
                    $item["facturas"] = $this->_grupoCargoQuinFactura($item["referencia"], $item["ie"]);
                    $data[] = $item;
                }
                return $data;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }
    
    /**
     * 
     * @param type $rfc
     * @param type $year
     * @param type $mes
     * @param type $fechaIni
     * @param type $fechaFin
     * @return boolean
     * @throws Exception
     */
    public function layoutTecnico($rfc, $year = null, $mes = null, $fechaIni = null, $fechaFin = null) {
        try {
            $select = $this->_db->select()
                    ->from(array("p" => "SM3PED"), array("p.NUM_PED as pedimento", "p.CNT_TOT AS cnt", "p.IVA1_TOT AS iva"))
                    ->joinLeft(array("b" => "SAIBAN"), "p.NUM_PED = b.DOCTO", array())
                    ->joinLeft(array("f" => "SM3FACT"), "f.NUM_REF = p.NUM_REF", array("f.NUMFAC as numFactura", "f.PAISFAC as paisFactura", new Zend_Db_Expr("REPLACE(CONVERT(VARCHAR(10), f.FECFAC, 111), '/', '-') as fechaFactura"), "f.FACEQ AS factorMoneda","f.INCOTER AS incoterm", "f.ACUSECOVE as cove", new Zend_Db_Expr("(SELECT TOP 1 r.NOMPRO FROM CMPRO r WHERE r.CVE_PRO = f.CVEPROV ) AS nomProveedor")))
                    ->joinLeft(array("c" => "CM3FRA"), "c.NUM_REF = f.NUM_REF AND f.NUMFAC = c.FACTFRA", array("c.PARTE AS numParte", "c.UMC as unidad", "c.CANTFAC AS cantidad", new Zend_Db_Expr("(c.VALCOM / c.CANTFAC) as precioUnitario"), "c.VALAGRE as valorAgregado", new Zend_Db_Expr("c.PAIORI as destino"), new Zend_Db_Expr("c.PAICOM as comprador")))
                    ->where("p.RFCCTE = ?", $rfc)
                    ->order("p.FEC_PAG ASC");
            if (isset($mes) & isset($year)) {
                $select->where("YEAR(p.FEC_PAG) = ?", $year)
                        ->where("MONTH(P.FEC_PAG) = ?", $mes);
            } elseif (isset($fechaIni) && isset($fechaFin)) {
                $select->where("p.FEC_PAG >= '{$fechaIni}'")
                        ->where("p.FEC_PAG <= '{$fechaFin}'");
            } else {
                throw new Exception("Not parameters set!");
            }
            $stmt = $this->_db->fetchAll($select, array());
            if ($stmt) {                
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

}
