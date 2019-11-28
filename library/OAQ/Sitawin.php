<?php
/**
 * Description of EmailNotifications
 *
 * @author Jaime E. Valdez <jvaldezch@gmail.com>
 */
class OAQ_Sitawin {

    protected $_db;
    protected $_adapter;
    protected $_logger;

    function __construct($init = null, $host = null, $username = null, $pwd = null, $dbname = null, $port = null, $adapter = null) {
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
            if (isset($init) && $init === true) {
                $this->_logger = Zend_Registry::get("logDb");
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerPedimentosPorRfc($rfc, $page, $perPage = 20) {
        try {
            $select = $this->_db->select()
                    ->from(array("p" => "sm3ped"), array("PATENTE", "ADUANAD", "SECCDES", "NUM_REF", "NUM_PED", "FEC_PAG", "IMP_EXP", "FIRMA", "CVEPEDIM"))
                    ->join(array("b" => "saiban"), "p.NUM_PED = b.DOCTO", array("FIRMA AS ACUSE"))
                    ->where("p.RFCCTE LIKE ?", $rfc)
                    ->where("p.FEC_PAG >= '2013-01-01 00:00:00'")
                    ->order("p.FEC_PAG DESC");
            $adapter = new Zend_Paginator_Adapter_DbSelect($select);
            $adapter->setRowCount(
                    $this->_db->select()->from("sm3ped", array(
                        Zend_Paginator_Adapter_DbSelect::ROW_COUNT_COLUMN => "NUM_REF"
                    ))
            );
            $paginator = new Zend_Paginator($adapter);
            $paginator->setItemCountPerPage($perPage);
            $paginator->setCurrentPageNumber($page);
            return $paginator;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerPedimentos($rfc, $ini, $fin) {
        try {
            if ($this->_adapter == "SqlSrv") {
                $fechaPago = date("Y-d-m", strtotime($ini));
                $sqlFecha = "CONVERT(VARCHAR(10), FEC_PAG, 101) AS FEC_PAG";
            } else {
                $fechaPago = "{$ini} 00:00:00";
                $sqlFecha = "FEC_PAG";
            }
            $select = $this->_db->select()
                    ->from(array("p" => "sm3ped"), array("PATENTE", "ADUANAD", "SECCDES", "NUM_REF", "NUM_PED", $sqlFecha, "IMP_EXP", "FIRMA", "CVEPEDIM"))
                    ->join(array("b" => "saiban"), "p.NUM_PED = b.DOCTO", array("FIRMA AS ACUSE"))
                    ->where("p.RFCCTE LIKE ?", $rfc)
                    ->where("p.FEC_PAG >= '{$fechaPago}'")
                    ->order("p.FEC_PAG DESC");
            $stmt = $this->_db->fetchAll($select);
            if ($stmt) {
                $data = array();
                foreach ($stmt as $item) {
                    $data[] = array(
                        "patente" => $item["PATENTE"],
                        "aduana" => $item["ADUANAD"] . $item["SECCDES"],
                        "referencia" => $item["NUM_REF"],
                        "fecha_pago" => $item["FEC_PAG"],
                        "pedimento" => $item["NUM_PED"],
                        "cve_doc" => $item["CVEPEDIM"],
                        "ie" => $item["IMP_EXP"],
                        "firma_validacion" => $item["FIRMA"],
                        "firma_banco" => $item["ACUSE"],
                    );
                }
                return $data;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function wsPedimentoPagados($rfc, $fechaIni, $fechaFin) {
        try {
            $select = $this->_db->select()
                    ->from(array("p" => "sm3ped"), array(
                        "p.PATENTE AS patente",
                        "p.NUM_PED AS pedimento",
                        "p.ADUANAD AS aduana",
                        "p.SECCDES AS seccAduana",
                        "p.RFCCTE AS rfcCliente",
                        "p.CVEPEDIM AS cveDoc",
                        "p.NUM_REF AS referencia",
                        "CONVERT(varchar, p.FEC_PAG, 121) AS fechaPago",
                        new Zend_Db_Expr("CASE WHEN p.IMP_EXP = 1 THEN 'TOCE.IMP' ELSE 'TOCE.EXP' END AS tipoOperacion")
                    ))
                    ->joinLeft(array("b" => "saiban"), "p.NUM_PED = b.DOCTO", array("FIRMA as firmaBanco", "NOOPE as numeroOperacion"))
                    ->joinLeft(array("s" => "cmrfc"), "p.RFCSOCAG = s.CLAVE", array("RFC as rfcSociedad"))
                    ->where("p.RFCCTE = ?", $rfc)
                    ->where("p.FEC_PAG >= ?", $fechaIni)
                    ->where("p.FEC_PAG <= ?", $fechaFin)
                    ->where("(p.FIRMA IS NOT NULL AND p.FIRMA <> '' AND b.FIRMA IS NOT NULL)");
            $stmt = $this->_db->fetchAll($select);
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function wsPedimentoPagadosSociedad($rfc, $fechaIni, $fechaFin) {
        try {
            $select = $this->_db->select()
                    ->from(array("p" => "sm3ped"), array(
                        "p.PATENTE AS patente",
                        "p.NUM_PED AS pedimento",
                        "p.ADUANAD AS aduana",
                        "p.SECCDES AS seccAduana",
                        "p.RFCCTE AS rfcCliente",
                        "p.CVEPEDIM AS cveDoc",
                        "p.IMP_EXP AS tipoMovimiento",
                        "p.NUM_REF AS referencia",
                        "CONVERT(varchar, p.FEC_PAG, 121) AS fechaPago",
                        new Zend_Db_Expr("CASE WHEN p.IMP_EXP = 1 THEN 'TOCE.IMP' ELSE 'TOCE.EXP' END AS tipoOperacion")
                    ))
                    ->joinLeft(array("b" => "saiban"), "p.NUM_PED = b.DOCTO", array("FIRMA as firmaBanco", "NOOPE as numeroOperacion"))
                    ->joinLeft(array("s" => "cmrfc"), "p.RFCSOCAG = s.CLAVE", array("RFC as rfcSociedad"))
                    ->where("s.RFC = ?", $rfc)
                    ->where("p.FEC_PAG >= ?", $fechaIni)
                    ->where("p.FEC_PAG <= ?", $fechaFin)
                    ->where("(p.FIRMA IS NOT NULL AND p.FIRMA <> '' AND b.FIRMA IS NOT NULL)")
                    ->order("p.FEC_PAG ASC");
            $stmt = $this->_db->fetchAll($select);
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param int $pedimento
     * @param int $aduana
     * @return boolean
     * @throws Exception
     */
    public function wsDetallePedimento($pedimento, $aduana = null) {
        try {
            $select = $this->_db->select()
                    ->from(array("p" => "sm3ped"), array(
                        "p.PATENTE AS patente",
                        "p.RFCCTE AS rfcCliente",
                        "p.NUM_PED AS pedimento",
                        "p.ADUANAD AS aduana",
                        "p.SECCDES AS seccAduana",
                        "p.NUM_REF AS referencia",
                        new Zend_Db_Expr("CONVERT(varchar, p.FEC_PAG, 121) AS fechaPago"),
                        new Zend_Db_Expr("CASE WHEN p.IMP_EXP = 1 THEN 'TOCE.IMP' ELSE 'TOCE.EXP' END AS tipoOperacion"),
                        "P.NUM_REF AS referencia",
                        "P.MEDTRAS AS transporteEntrada",
                        "P.MEDTRAA AS transporteArribo",
                        "P.MEDTRAE AS transporteSalida",
                        new Zend_Db_Expr("CONVERT(VARCHAR(10), p.FEC_ENT, 121) AS fechaEntrada"),
                        new Zend_Db_Expr("CONVERT(VARCHAR(10), P.FEC_PAG, 121) AS fechaPago "),
                        "P.FIRMA AS firmaValidacion",
                        "P.TIP_CAM AS tipoCambio",
                        "P.CVEPEDIM AS cvePed",
                        "P.REGIMEN AS regimen",
                        "P.ADUANAE AS aduanaEntrada",
                        "P.VALMEDLLS AS valorDolares",
                        "P.VALADUANA AS valorAduana",
                        "P.VALMN_PAG AS valorComercial",
                        "P.FLETES AS fletes",
                        "P.SEGUROS AS seguros",
                        "P.EMBALAJ AS embalajes",
                        "P.OTROINC AS otrosIncrementales",
                        "P.DTA_TOT AS dta",
                        "P.IVA1_TOT AS iva",
                        "P.IGIE_TOT AS igi",
                        "P.PRE_TOT AS prev",
                        "P.CNT_TOT AS cnt",
                        new Zend_Db_Expr("((CASE P.DTA_FP WHEN 0 THEN P.DTA_TOT ELSE 0 end) +  (CASE P.DTA_FPADI WHEN 0 THEN  P.DTA_TLADI ELSE 0 end) + (CASE P.CC1_FP WHEN 0 THEN P.CC1_TOT ELSE 0 end) +  (CASE P.CC2_FP WHEN 0 THEN P.CC2_TOT ELSE 0 end) +  (CASE P.IVA1_FP WHEN 0 THEN P.IVA1_TOT ELSE 0 end) +  (CASE P.IVA2_FP WHEN 0 THEN P.IVA2_TOT ELSE 0 end) +  (CASE P.ISAN_FP WHEN 0 THEN P.ISAN_TOT ELSE 0 end) +  (CASE P.IEPS_FP WHEN 0 THEN P.IEPS_TOT ELSE 0 end) +  (CASE P.REC_FP WHEN 0 THEN P.REC_TOT ELSE 0 end) +  (CASE P.OTR_FP WHEN 0 THEN P.OTR_TOT ELSE 0 end) +  (CASE GAR_FP WHEN 0 THEN P.GAR_TOT ELSE 0 end) +  (CASE P.MUL_FP WHEN 0 THEN P.MUL_TOT ELSE 0 end) +  (CASE P.MUL2_FP WHEN 0 THEN P.MUL2_TOT ELSE 0 end) +  (CASE P.DTI_FP WHEN 0 THEN P.DTI_TOT ELSE 0 end) +  (CASE P.IGIR_FP WHEN 0 THEN P.IGIR_TOT ELSE 0 end) +  (CASE P.PRE_FP WHEN 0 THEN P.PRE_TOT ELSE 0 end) +  (CASE P.BSS_FP WHEN 0 THEN P.BSS_TOT ELSE 0 end) +  (CASE P.EUR_FP WHEN 0 THEN P.EUR_TOT ELSE 0 end) +  (CASE P.ECI_FP WHEN 0 THEN P.ECI_TOT ELSE 0 end) +  (CASE P.ITV_FP WHEN 0 THEN P.ITV_TOT ELSE 0 end) +  (CASE P.IGIR_FP2 WHEN 0 THEN P.IGIR_TOT2 ELSE 0 end) +  (CASE P.REC2_FP WHEN 0 THEN P.REC2_TOT ELSE 0 end)) AS totalEfectivo"),
                        "P.PESBRU AS pesoBruto",
                        "P.BULTOS AS bultos",
                    ))
                    ->joinLeft(array("b" => "saiban"), "p.NUM_PED = b.DOCTO", array("b.FIRMA AS firmaBanco", "NOOPE as numeroOperacion", "CAJA as caja"))
                    ->where("p.NUM_PED = ?", $pedimento);
            if (isset($aduana)) {
                $select->where("p.ADUANAD = ?", substr($aduana, 0, 2));
            }
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                $stmt["facturas"] = $this->wsFacturasPedimento($stmt["referencia"]);
                $stmt["archivo"] = $this->ultimoArchivoValidacion($pedimento);
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param string $referencia
     * @return boolean
     * @throws Exception
     */
    public function wsFacturasPedimento($referencia) {
        try {
            $select = $this->_db->select()
                    ->from(array("f" => "sm3fact"), array(
                        "f.NUMFAC AS numFactura",
                        "f.ACUSECOVE AS cove",
                        "f.ORDENFAC AS ordenFactura",
                        new Zend_Db_Expr("CONVERT(VARCHAR, F.FECFAC, 121) AS fechaFactura"),
                        "f.INCOTER AS incoterm",
                        "f.VALDLS AS valorFacturaUsd",
                        "f.VALEXT AS valorFacturaMonExt",
                        new Zend_Db_Expr("(SELECT TOP 1 PR.NOMPRO FROM CMPRO PR WHERE PR.CVE_PRO = F.CVEPROV ) AS nomProveedor"),
                        new Zend_Db_Expr("(SELECT TOP 1 PR.NUM_TAX FROM CMPRO PR WHERE PR.CVE_PRO = F.CVEPROV ) AS taxId"),
                        "f.PAISFAC AS paisFactura",
                        "f.MONFAC AS divisa",
                        "f.FACEQ AS factorMonExt"
                    ))
                    ->where("f.NUM_REF = ?", $referencia);
            $stmt = $this->_db->fetchAll($select);
            if ($stmt) {
                $data = array();
                foreach ($stmt as $item) {
                    $item["partes"] = $this->wsPartesPedimento($referencia, $item["numFactura"]);
                    $data[] = $item;
                }
                return $data;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param string $referencia
     * @return boolean
     * @throws Exception
     */
    public function wsDesgloseFactura($referencia, $numFactura) {
        try {
            $select = $this->_db->select()
                    ->from(array("f" => "sm3fact"), array(
                        "f.NUMFAC AS numFactura",
                        "f.ACUSECOVE AS cove",
                        "f.ORDENFAC AS ordenFactura",
                        new Zend_Db_Expr("CONVERT(VARCHAR, F.FECFAC, 121) AS fechaFactura"),
                        "f.INCOTER AS incoterm",
                        "f.VALDLS AS valorFacturaUsd",
                        "f.VALEXT AS valorFacturaMonExt",
                        new Zend_Db_Expr("(SELECT TOP 1 PR.NOMPRO FROM CMPRO PR WHERE PR.CVE_PRO = F.CVEPROV ) AS nomProveedor"),
                        new Zend_Db_Expr("(SELECT TOP 1 PR.NUM_TAX FROM CMPRO PR WHERE PR.CVE_PRO = F.CVEPROV ) AS taxId"),
                        "f.PAISFAC AS paisFactura",
                        "f.MONFAC AS divisa",
                        "f.FACEQ AS factorMonExt"
                    ))
                    ->where("f.NUM_REF = ?", $referencia)
                    ->where("f.NUMFAC = ?", $numFactura);
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                $stmt["partes"] = $this->wsPartesPedimento($referencia, $numFactura);
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param string $referencia
     * @param string $numFactura
     * @param string $tipoOperacion
     * @return boolean
     * @throws Exception
     */
    public function traficoDesgloseFactura($referencia, $numFactura, $tipoOperacion) {
        try {
            $select = $this->_db->select()
                    ->from(array("f" => "sm3fact"), array(
                        "f.NUMFAC AS numFactura",
                        "f.ACUSECOVE AS cove",
                        "f.ORDENFAC AS ordenFactura",
                        new Zend_Db_Expr("CONVERT(VARCHAR, F.FECFAC, 121) AS fechaFactura"),
                        "f.INCOTER AS incoterm",
                        "f.VALDLS AS valorFacturaUsd",
                        "f.VALEXT AS valorFacturaMonExt",
                        "f.PAISFAC AS paisFactura",
                        "f.MONFAC AS divisa",
                        "f.FACEQ AS factorMonExt"
                    ))
                    ->where("f.NUM_REF = ?", $referencia)
                    ->where("f.NUMFAC = ?", $numFactura);
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                if ($tipoOperacion === "TOCE.IMP") {
                    $stmt["proveedor"] = $this->_proveedor($referencia, $numFactura);
                } else {
                    $stmt["destinatario"] = $this->_destinatario($referencia, $numFactura);
                }
                $stmt["productos"] = $this->traficoPartesPedimento($referencia, $numFactura);
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param string $referencia
     * @param int $pedimento
     * @param string $numFactura
     * @param string $tipoOperacion
     * @return boolean
     * @throws Exception
     */
    public function traficoDesgloseConsolidado($referencia, $pedimento, $numFactura, $tipoOperacion) {
        try {
            $select = $this->_db->select()
                    ->from(array("f" => "sm3confa"), array(
                        "f.NUM_FAC AS numFactura",
                        "f.FACTURACOVE AS cove",
                        "f.ORDEN AS ordenFactura",
                        new Zend_Db_Expr("CONVERT(VARCHAR, F.FEC_FAC, 121) AS fechaFactura"),
                        "f.INCOTER AS incoterm",
                        "f.VALDLS AS valorFacturaUsd",
                        "f.VALEXT AS valorFacturaMonExt",
                        "f.PAISFAC AS paisFactura",
                        "f.MONFAC AS divisa",
                        "f.FACEQ AS factorMonExt"
                    ))
                    ->where("f.NUM_PED = ?", $pedimento)
                    ->where("f.NUM_FAC = ?", $numFactura);
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                if ($tipoOperacion === "TOCE.IMP") {
                    $stmt["proveedor"] = $this->_proveedor($referencia, $numFactura);
                } else {
                    $stmt["destinatario"] = $this->_destinatario($referencia, $numFactura);
                }
                $stmt["productos"] = $this->traficoPartesConsolidado($pedimento, $numFactura);
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param string $referencia
     * @param type $cveCliente
     * @param type $tipoOperacion
     * @return boolean|array
     * @throws Exception
     */
    protected function _proveedor($referencia, $numFactura) {
        try {
            $select = $this->_db->select()
                    ->distinct()
                    ->from(array("i" => "sm3fact"), array("CVEPROV as cveProveedor"))
                    ->where("i.NUM_REF = ?", $referencia)
                    ->where("i.NUMFAC = ?", $numFactura);
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                $pro = $this->_obtenerDatosProveedor($stmt["cveProveedor"]);
                $data = array(
                    "cveProveedor" => $stmt["cveProveedor"],
                    "taxId" => $pro["taxId"],
                    "nomProveedor" => $pro["nomProveedor"],
                    "domicilio" => array(
                        "calle" => $pro["calle"],
                        "numExterior" => $pro["numExterior"],
                        "numInterior" => $pro["numInterior"],
                        "municipio" => $pro["municipio"],
                        "pais" => $pro["pais"],
                        "codigoPostal" => $pro["codigoPostal"],
                    ),
                );
                return $data;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param string $referencia
     * @param type $cveCliente
     * @param type $tipoOperacion
     * @return boolean|array
     * @throws Exception
     */
    protected function _destinatario($referencia, $numFactura) {
        try {
            $select = $this->_db->select()
                    ->distinct()
                    ->from(array("i" => "sm3fact"), array("CVEPROV as cveProveedor"))
                    ->where("i.NUM_REF = ?", $referencia)
                    ->where("i.NUMFAC = ?", $numFactura);
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                $pro = $this->_obtenerDatosDestinatario($stmt["cveProveedor"]);
                $data = array(
                    "cveProveedor" => $stmt["cveProveedor"],
                    "taxId" => $pro["taxId"],
                    "nomProveedor" => $pro["nomProveedor"],
                    "domicilio" => array(
                        "calle" => $pro["calle"],
                        "numExterior" => $pro["numExterior"],
                        "numInterior" => $pro["numInterior"],
                        "municipio" => $pro["municipio"],
                        "pais" => $pro["pais"],
                        "codigoPostal" => $pro["codigoPostal"],
                    ),
                );
                return $data;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param string $referencia
     * @param string $factura
     * @return boolean
     * @throws Exception
     */
    public function wsPartesPedimento($referencia, $factura) {
        try {
            $select = $this->_db->select()
                    ->from(array("f" => "cm3fra"), array(
                        "f.PARTE AS numParte",
                        "f.DESC1 AS descripcion",
                        "f.CODIGO AS fraccion",
                        "f.ORDEN AS ordenFraccion",
                        "f.VALCOM AS valorMonExt",
                        "f.UMC AS umc",
                        "f.CANTFAC AS cantUmc",
                        "f.UMT AS umt",
                        "f.CANTTAR AS cantUmt",
                        "f.PAIORI AS paisOrigen",
                        "f.PAICOM AS paisVendedor",
                        "f.TASAADV AS tasaAdvalorem",
                        "f.CERTLC AS tlc",
                        "f.PROSEC AS prosec",
                    ))
                    ->where("f.NUM_REF = ?", $referencia)
                    ->where("f.FACTFRA = ?", $factura);
            $stmt = $this->_db->fetchAll($select);
            if ($stmt) {
                $data = array();
                $prev = $this->wsPrevio($referencia);
                foreach ($stmt as $item) {
                    $item["prosec"] = (trim($item["prosec"]) != "") ? $item["prosec"] : "N";
                    $item["precioUnitario"] = $item["valorMonExt"] / $item["cantUmc"];
                    if ($prev !== false) {
                        $item["patenteOriginal"] = isset($prev["patente"]) ? $prev["patente"] : null;
                        $item["pedimentoOriginal"] = isset($prev["pedimento"]) ? $prev["pedimento"] : null;
                        $item["aduanaOriginal"] = isset($prev["aduana"]) ? $prev["aduana"] . $prev["seccion"] : null;
                        $item["regimenOriginal"] = isset($prev["regimen"]) ? $prev["regimen"] : null;
                        $item["cantidadOriginal"] = isset($prev["cantidad"]) ? $prev["cantidad"] : null;
                        $item["unidadOriginal"] = isset($prev["unidad"]) ? $prev["unidad"] : null;
                        $item["fechaOriginal"] = isset($prev["fecha"]) ? $prev["fecha"] : null;
                    }
                    $data[] = $item;
                }
                return $data;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param string $referencia
     * @param string $factura
     * @return boolean
     * @throws Exception
     */
    public function traficoPartesPedimento($referencia, $factura) {
        try {
            $select = $this->_db->select()
                    ->from(array("f" => "cm3fra"), array(
                        "f.PARTE AS numParte",
                        "f.DESC1 AS descripcion",
                        "f.CODIGO AS fraccion",
                        "f.ORDEN AS ordenFraccion",
                        "f.VALCOM AS valorMonExt",
                        "f.UMC AS umc",
                        "f.CANTFAC AS cantUmc",
                        "f.UMT AS umt",
                        "f.CANTTAR AS cantUmt",
                        "f.PAIORI AS paisOrigen",
                        "f.PAICOM AS paisVendedor",
                        "f.TASAADV AS tasaAdvalorem",
                        "f.CERTLC AS tlc",
                        "f.PROSEC AS prosec",
                    ))
                    ->where("f.NUM_REF = ?", $referencia)
                    ->where("f.FACTFRA = ?", $factura);
            $stmt = $this->_db->fetchAll($select);
            if ($stmt) {
                $data = array();
                $prev = $this->wsPrevio($referencia);
                foreach ($stmt as $item) {
                    $item["descripcion"] = trim($item["descripcion"]);
                    $item["prosec"] = (trim($item["prosec"]) != "") ? $item["prosec"] : "N";
                    $item["precioUnitario"] = $item["valorMonExt"] / $item["cantUmc"];
                    if ($prev !== false) {
                        $item["patenteOriginal"] = isset($prev["patente"]) ? $prev["patente"] : null;
                        $item["pedimentoOriginal"] = isset($prev["pedimento"]) ? $prev["pedimento"] : null;
                        $item["aduanaOriginal"] = isset($prev["aduana"]) ? $prev["aduana"] . $prev["seccion"] : null;
                        $item["regimenOriginal"] = isset($prev["regimen"]) ? $prev["regimen"] : null;
                        $item["cantidadOriginal"] = isset($prev["cantidad"]) ? $prev["cantidad"] : null;
                        $item["unidadOriginal"] = isset($prev["unidad"]) ? $prev["unidad"] : null;
                        $item["fechaOriginal"] = isset($prev["fecha"]) ? $prev["fecha"] : null;
                    }
                    $data[] = $item;
                }
                return $data;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " >> " . $e->getMessage());
        }
    }

    /**
     * 
     * @param int $pedimento
     * @param string $factura
     * @return boolean
     * @throws Exception
     */
    public function traficoPartesConsolidado($pedimento, $factura) {
        try {
            $select = $this->_db->select()
                    ->from(array("f" => "sm3confr"), array(
                        "f.NUMPARTE AS numParte",
                        "f.DESC1 AS descripcion",
                        "f.CODIGO AS fraccion",
                        "f.NUM_ORD AS ordenFraccion",
                        "f.V_FACDLS AS valorMonExt",
                        "f.UNI_FAC AS umc",
                        "f.CAN_FAC AS cantUmc",
                        "f.UNI_TAR AS umt",
                        "f.CAN_TAR AS cantUmt",
                        "f.PAIORI AS paisOrigen",
                        "f.PAICOM AS paisVendedor",
                        "f.ADV AS tasaAdvalorem",
                    ))
                    ->where("f.NUM_PED = ?", $pedimento)
                    ->where("f.NUM_FAC = ?", $factura);
            $stmt = $this->_db->fetchAll($select);
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " >> " . $e->getMessage());
        }
    }

    public function wsPrevio($referencia) {
        try {
            $select = $this->_db->select()
                    ->from(array("p" => "sm3prev"), array(
                        "p.PATORI as patente",
                        "p.ADUORI as aduana",
                        "p.SECORI as seccion",
                        "p.PEDORI as pedimento",
                        "p.REGORI as regimen",
                        "p.CANTORI as cantidad",
                        "p.UNIORI as unidad",
                        new Zend_Db_Expr("CONVERT(VARCHAR(10), p.FECORI, 121) as fecha"),
                    ))
                    ->where("p.NUM_REF = ?", $referencia);
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " >> " . $e->getMessage());
        }
    }

    public function obtenerFacturasPedimento($numPedimento, $consolidado = null) {
        try {
            $select = $this->_db->select();
            $select->from("sm3ped", array("IMP_EXP"))
                    ->where("NUM_PED = ?", $numPedimento);
            $ie = $this->_db->fetchRow($select);

            $conn = $this->_db->getConfig();

            if ($conn["port"] == 1433) {
                $concat = new Zend_Db_Expr("p.ADUANAD + p.SECCDES AS Aduana");
                $fecfac = new Zend_Db_Expr("CONVERT(varchar, f.FECFAC, 111) AS FechaFactura");
                $fecfacCon = new Zend_Db_Expr("CONVERT(varchar, f.FEC_FAC, 111) AS FechaFactura");
                $limitSql = " TOP 1";
                $limitMy = "";
            } else {
                $concat = new Zend_Db_Expr("CONCAT(p.ADUANAD,p.SECCDES) AS Aduana");
                $fecfac = "f.FECFAC AS FechaFactura";
                $fecfacCon = "f.FEC_FAC AS FechaFactura";
                $limitSql = "";
                $limitMy = " LIMIT 1";
            }
            if ($ie && !$consolidado) {
                if ($ie["IMP_EXP"] == 1) {
                    $facturas = $this->_db->select()
                            ->distinct()
                            ->from(array("p" => "sm3ped"), array("p.PATENTE AS Patente",
                                "p.NUM_PED AS Pedimento",
                                $concat,
                                "p.NUM_REF AS Referencia",
                                new Zend_Db_Expr("CASE WHEN p.IMP_EXP = 1 THEN 'TOCE.IMP' ELSE 'TOCE.EXP' END AS TipoOperacion"),
                                "p.CVE_IMP AS CveImp"
                            ))
                            ->joinLeft(array("f" => "sm3fact"), "f.NUM_REF = p.NUM_REF", array("f.NUMFAC AS NumFactura",
                                $fecfac,
                                "f.CVEPROV AS CvePro"
                            ))
                            ->joinLeft(array("c" => "cmcli"), "c.CVE_IMP = p.CVE_IMP", array("c.RFC AS CteRfc",
                                "c.NOMCLI AS CteNombre",
                                "c.DIRCALLE AS CteCalle",
                                "c.DIRNUMEXT AS CteNumExt",
                                "c.DIRNUMINT AS CteNumInt",
                                "c.COLONIA AS CteColonia",
                                "c.DIRMUNIC AS CteMun",
                                "c.DIRENTFED AS CteEdo",
                                "c.DIRPAIS AS CtePais",
                            ))
                            ->joinLeft(array("pr" => "cmpro"), "pr.CVE_PRO = f.CVEPROV", array("pr.NOMPRO AS ProNombre",
                                "pr.DIRCALLE AS ProCalle",
                                "pr.DIRNUMEXT AS ProNumExt",
                                "pr.DIRNUMINT AS ProNumInt",
                                "pr.DIRCOLONI AS ProColonia",
                                "pr.DIRMUNI AS ProMun",
                                "pr.DIRESTADO AS ProEdo",
                                "pr.DIRPAIS AS ProPais",
                                "pr.NUM_TAX AS ProTaxID",
                            ))
                            ->where("p.NUM_PED = ?", $numPedimento);
                    $stmt = $this->_db->fetchAll($facturas);
                    if ($stmt) {
                        return $stmt;
                    }
                    return null;
                } elseif ($ie["IMP_EXP"] == 2) {
                    $facturas = $this->_db->select()
                            ->distinct()
                            ->from(array("p" => "sm3ped"), array("p.PATENTE AS Patente",
                                "p.NUM_PED AS Pedimento",
                                $concat,
                                "p.NUM_REF AS Referencia",
                                new Zend_Db_Expr("CASE WHEN p.IMP_EXP = 1 THEN 'TOCE.IMP' ELSE 'TOCE.EXP' END AS TipoOperacion"),
                                "p.CVE_IMP AS CveImp"
                            ))
                            ->joinLeft(array("f" => "sm3fact"), "f.NUM_REF = p.NUM_REF", array("f.NUMFAC AS NumFactura",
                                $fecfac,
                                "f.CVEPROV AS CvePro"
                            ))
                            ->joinLeft(array("c" => "cmcli"), "c.CVE_IMP = p.CVE_IMP", array("c.RFC AS CteRfc",
                                "c.NOMCLI AS CteNombre",
                                "c.DIRCALLE AS CteCalle",
                                "c.DIRNUMEXT AS CteNumExt",
                                "c.DIRNUMINT AS CteNumInt",
                                "c.COLONIA AS CteColonia",
                                "c.DIRMUNIC AS CteMun",
                                "c.DIRENTFED AS CteEdo",
                                "c.DIRPAIS AS CtePais",
                            ))
                            ->joinLeft(array("d" => "cmdest"), "d.CVE_PRO = f.CVEPROV", array("d.NOMPRO AS ProNombre",
                                "d.DIRCALLE AS ProCalle",
                                "d.DIRNUMEXT AS ProNumExt",
                                "d.DIRNUMINT AS ProNumInt",
                                "d.DIRCOLONI AS ProColonia",
                                "d.DIRMUNI AS ProMun",
                                "d.DIRESTADO AS ProEdo",
                                "d.DIRPAIS AS ProPais",
                                "d.NUM_TAX AS ProTaxID",
                            ))
                            ->where("p.NUM_PED = ?", $numPedimento);
                    $stmt = $this->_db->fetchAll($facturas);
                    if ($stmt) {
                        return $stmt;
                    }
                    return null;
                }
            } else {
                if ($ie["IMP_EXP"] == 1) {
                    $facturas = $this->_db->select()
                            ->from(array("p" => "sm3ped"), array("p.PATENTE AS Patente",
                                "p.NUM_PED AS Pedimento",
                                $concat,
                                "p.NUM_REF AS Referencia",
                                new Zend_Db_Expr("CASE WHEN p.IMP_EXP = 1 THEN 'TOCE.IMP' ELSE 'TOCE.EXP' END AS TipoOperacion"),
                                "p.CVE_IMP AS CveImp"
                            ))
                            ->joinLeft(array("f" => "sm3confa"), "f.NUM_PED = p.NUM_PED", array("f.NUM_FAC AS NumFactura",
                                $fecfacCon,
                                "f.CVEPROV AS CvePro"
                            ))
                            ->joinLeft(array("c" => "cmcli"), "c.CVE_IMP = p.CVE_IMP", array("c.RFC AS CteRfc",
                                "c.NOMCLI AS CteNombre",
                                "c.DIRCALLE AS CteCalle",
                                "c.DIRNUMEXT AS CteNumExt",
                                "c.DIRNUMINT AS CteNumInt",
                                "c.COLONIA AS CteColonia",
                                "c.DIRMUNIC AS CteMun",
                                "c.DIRENTFED AS CteEdo",
                                "c.DIRPAIS AS CtePais",
                            ))
                            ->joinLeft(array("pr" => "cmpro"), "pr.CVE_PRO = f.CVEPROV", array("pr.NOMPRO AS ProNombre",
                                "pr.DIRCALLE AS ProCalle",
                                "pr.DIRNUMEXT AS ProNumExt",
                                "pr.DIRNUMINT AS ProNumInt",
                                "pr.DIRCOLONI AS ProColonia",
                                "pr.DIRMUNI AS ProMun",
                                "pr.DIRESTADO AS ProEdo",
                                "pr.DIRPAIS AS ProPais",
                                "pr.NUM_TAX AS ProTaxID",
                            ))
                            ->where("p.NUM_PED = ?", $numPedimento);
                    $stmt = $this->_db->fetchAll($facturas);
                    if ($stmt) {
                        return $stmt;
                    }
                    return null;
                } elseif ($ie["IMP_EXP"] == 2) {
                    $facturas = $this->_db->select()
                            ->from(array("p" => "sm3ped"), array("p.PATENTE AS Patente",
                                "p.NUM_PED AS Pedimento",
                                $concat,
                                "p.NUM_REF AS Referencia",
                                new Zend_Db_Expr("CASE WHEN p.IMP_EXP = 1 THEN 'TOCE.IMP' ELSE 'TOCE.EXP' END AS TipoOperacion"),
                                "p.CVE_IMP AS CveImp"
                            ))
                            ->joinLeft(array("f" => "sm3confa"), "f.NUM_PED = p.NUM_PED", array("f.NUM_FAC AS NumFactura",
                                new Zend_Db_Expr("CONVERT (VARCHAR, f.FEC_FAC, 111) AS FechaFactura"),
                                "f.CVEPROV AS CvePro"
                            ))
                            ->joinLeft(array("c" => "cmcli"), "c.CVE_IMP = p.CVE_IMP", array("c.RFC AS CteRfc",
                                "c.NOMCLI AS CteNombre",
                                "c.DIRCALLE AS CteCalle",
                                "c.DIRNUMEXT AS CteNumExt",
                                "c.DIRNUMINT AS CteNumInt",
                                "c.COLONIA AS CteColonia",
                                "c.DIRMUNIC AS CteMun",
                                "c.DIRENTFED AS CteEdo",
                                "c.DIRPAIS AS CtePais",
                            ))
                            ->joinLeft(array("pr" => "cmdest"), "pr.CVE_PRO = f.CVEPROV", array("pr.NOMPRO AS ProNombre",
                                "pr.DIRCALLE AS ProCalle",
                                "pr.DIRNUMEXT AS ProNumExt",
                                "pr.DIRNUMINT AS ProNumInt",
                                "pr.DIRCOLONI AS ProColonia",
                                "pr.DIRMUNI AS ProMun",
                                "pr.DIRESTADO AS ProEdo",
                                "pr.DIRPAIS AS ProPais",
                                "pr.NUM_TAX AS ProTaxID",
                            ))
                            ->where("p.NUM_PED = ?", $numPedimento);
                    $stmt = $this->_db->fetchAll($facturas);
                    if ($stmt) {
                        return $stmt;
                    }
                    return;
                }
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerFactura($numPedimento, $factura, $tipo) {
        try {
            $conn = $this->_db->getConfig();
            if ($conn["port"] == 1433) {
                $concat = new Zend_Db_Expr("p.ADUANAD + p.SECCDES AS Aduana");
                $fecfac = new Zend_Db_Expr("CONVERT(varchar, f.FECFAC, 111) AS FechaFactura");
            } else {
                $concat = new Zend_Db_Expr("CONCAT(p.ADUANAD,p.SECCDES) AS Aduana");
                $fecfac = "f.FECFAC AS FechaFactura";
            }
            if ($tipo == "TOCE.EXP") {
                $select = $this->_db->select()
                        ->from(array("p" => "sm3ped"), array("p.PATENTE AS Patente",
                            "p.NUM_PED AS Pedimento",
                            $concat,
                            "p.NUM_REF AS Referencia",
                            new Zend_Db_Expr("CASE WHEN p.IMP_EXP = 1 THEN 'TOCE.IMP' ELSE 'TOCE.EXP' END AS TipoOperacion"),
                            "p.CVE_IMP AS CveImp"
                        ))
                        ->joinLeft(array("f" => "sm3fact"), "f.NUM_REF = p.NUM_REF", array("f.NUMFAC AS NumFactura",
                            $fecfac,
                            "f.CVEPROV AS CvePro"
                        ))
                        ->joinLeft(array("fr" => "sm3fra"), "fr.NUM_REF = f.NUM_REF", array("fr.CODIGO AS CveFraccion",
                            "fr.UMC AS Unidad",
                            "f.CVEPROV AS CvePro"
                        ))
                        ->joinLeft(array("fra" => "cm3fra"), "fra.CODIGO = fr.CODIGO AND f.NUMFAC = fra.FACTFRA AND fra.NUM_REF = f.NUM_REF", array("fra.UMC_OMA AS UnidadOma",
                            "fra.CANTFAC AS Cantidad",
                            new Zend_Db_Expr("(fra.VALCOM/fra.CANTFAC) AS PrecioUnitario"),
                            "fra.VALCOM AS Total",
                            "fra.MONVAL AS Moneda",
                        ))
                        ->joinLeft(array("c" => "cmcli"), "c.CVE_IMP = p.CVE_IMP", array("c.RFC AS CteRfc",
                            "c.NOMCLI AS CteNombre",
                            "c.DIRCALLE AS CteCalle",
                            "c.DIRNUMEXT AS CteNumExt",
                            "c.DIRNUMINT AS CteNumInt",
                            "c.COLONIA AS CteColonia",
                            "c.DIRMUNIC AS CteMun",
                            "c.DIRENTFED AS CteEdo",
                            "c.DIRPAIS AS CtePais",
                        ))
                        ->joinLeft(array("d" => "cmdest"), "d.CVE_PRO = f.CVEPROV", array("d.NOMPRO AS ProNombre",
                            "d.DIRCALLE AS ProCalle",
                            "d.DIRNUMEXT AS ProNumExt",
                            "d.DIRNUMINT AS ProNumInt",
                            "d.DIRCOLONI AS ProColonia",
                            "d.DIRMUNI AS ProMun",
                            "d.DIRESTADO AS ProEdo",
                            "d.DIRPAIS AS ProPais",
                            "d.NUM_TAX AS ProTaxID",
                        ))
                        ->where("p.NUM_PED = ?", $numPedimento)
                        ->where("f.NUMFAC = ?", $factura);
            } else if ($tipo == "TOCE.IMP") {
                $select = $this->_db->select()
                        ->from(array("p" => "sm3ped"), array("p.PATENTE AS Patente",
                            "p.NUM_PED AS Pedimento",
                            $concat,
                            "p.NUM_REF AS Referencia",
                            new Zend_Db_Expr("CASE WHEN p.IMP_EXP = 1 THEN 'TOCE.IMP' ELSE 'TOCE.EXP' END AS TipoOperacion"),
                            "p.CVE_IMP AS CveImp"
                        ))
                        ->joinLeft(array("f" => "sm3fact"), "f.NUM_REF = p.NUM_REF", array("f.NUMFAC AS NumFactura",
                            $fecfac,
                            "f.CVEPROV AS CvePro"
                        ))
                        ->joinLeft(array("fr" => "sm3fra"), "fr.NUM_REF = f.NUM_REF", array("fr.CODIGO AS CveFraccion",
                            "fr.UMC AS Unidad",
                            "f.CVEPROV AS CvePro"
                        ))
                        ->joinLeft(array("fra" => "cm3fra"), "fra.CODIGO = fr.CODIGO AND f.NUMFAC = fra.FACTFRA AND fra.NUM_REF = f.NUM_REF", array("fra.UMC_OMA AS UnidadOma",
                            "fra.CANTFAC AS Cantidad",
                            "(fra.VALCOM/fra.CANTFAC) AS PrecioUnitario",
                            "fra.VALCOM AS Total",
                            "fra.MONVAL AS Moneda",
                        ))
                        ->joinLeft(array("c" => "cmcli"), "c.CVE_IMP = p.CVE_IMP", array("c.RFC AS CteRfc",
                            "c.NOMCLI AS CteNombre",
                            "c.DIRCALLE AS CteCalle",
                            "c.DIRNUMEXT AS CteNumExt",
                            "c.DIRNUMINT AS CteNumInt",
                            "c.COLONIA AS CteColonia",
                            "c.DIRMUNIC AS CteMun",
                            "c.DIRENTFED AS CteEdo",
                            "c.DIRPAIS AS CtePais",
                        ))
                        ->joinLeft(array("pr" => "cmpro"), "pr.CVE_PRO = f.CVEPROV", array("pr.NOMPRO AS ProNombre",
                            "pr.DIRCALLE AS ProCalle",
                            "pr.DIRNUMEXT AS ProNumExt",
                            "pr.DIRNUMINT AS ProNumInt",
                            "pr.DIRCOLONI AS ProColonia",
                            "pr.DIRMUNI AS ProMun",
                            "pr.DIRESTADO AS ProEdo",
                            "pr.DIRPAIS AS ProPais",
                            "pr.NUM_TAX AS ProTaxID",
                        ))
                        ->where("p.NUM_PED = ?", $numPedimento)
                        ->where("f.NUMFAC = ?", $factura);
            }
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                return $stmt;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function infoPedimentoBasica($pedimento) {
        try {
            $efectivo = new Zend_Db_Expr("((CASE DTA_FP WHEN 0 THEN DTA_TOT ELSE 0 end) +
                (CASE DTA_FPADI WHEN 0 THEN DTA_TLADI ELSE 0 end) +
                (CASE CC1_FP WHEN 0 THEN CC1_TOT ELSE 0 end) + 
                (CASE CC2_FP WHEN 0 THEN CC2_TOT ELSE 0 end) + 
                (CASE IVA1_FP WHEN 0 THEN IVA1_TOT ELSE 0 end) + 
                (CASE IVA2_FP WHEN 0 THEN IVA2_TOT ELSE 0 end) + 
                (CASE ISAN_FP WHEN 0 THEN ISAN_TOT ELSE 0 end) + 
                (CASE IEPS_FP WHEN 0 THEN IEPS_TOT ELSE 0 end) + 
                (CASE REC_FP WHEN 0 THEN REC_TOT ELSE 0 end) + 
                (CASE OTR_FP WHEN 0 THEN OTR_TOT ELSE 0 end) + 
                (CASE GAR_FP WHEN 0 THEN GAR_TOT ELSE 0 end) + 
                (CASE MUL_FP WHEN 0 THEN MUL_TOT ELSE 0 end) + 
                (CASE MUL2_FP WHEN 0 THEN MUL2_TOT ELSE 0 end) + 
                (CASE DTI_FP WHEN 0 THEN DTI_TOT ELSE 0 end) + 
                (CASE IGIR_FP WHEN 0 THEN IGIR_TOT ELSE 0 end) + 
                (CASE PRE_FP WHEN 0 THEN PRE_TOT ELSE 0 end) + 
                (CASE BSS_FP WHEN 0 THEN BSS_TOT ELSE 0 end) + 
                (CASE EUR_FP WHEN 0 THEN EUR_TOT ELSE 0 end) + 
                (CASE ECI_FP WHEN 0 THEN ECI_TOT ELSE 0 end) + 
                (CASE ITV_FP WHEN 0 THEN ITV_TOT ELSE 0 end) + 
                (CASE IGIR_FP2 WHEN 0 THEN IGIR_TOT2 ELSE 0 end) + 
                (CASE REC2_FP WHEN 0 THEN REC2_TOT ELSE 0 end) +
                (CASE CNT_FP WHEN 0 THEN CNT_TOT ELSE 0 end)) AS EFECTIVO");
            $select = $this->_db->select()
                    ->from("sm3ped", array("NUM_REF", "IMP_EXP", "TIP_CAM", "RFCCTE", "CVEPEDIM", "REGIMEN", "VALADUANA", "SUB", "CONSOLR", "RECTIF", "FEC_ENT", "FECALT", "FECMOD", "CONSOLR", "FIRMA", $efectivo))
                    ->where("NUM_PED = ?", $pedimento);
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                return $stmt;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function infoPedimentoBasicaReferencia($referencia) {
        try {
            $select = $this->_db->select();
            $select->from("sm3ped", array("NUM_REF", "IMP_EXP", "TIP_CAM", "RFCCTE", "CVEPEDIM", "REGIMEN", "VALADUANA", "SUB", "CONSOLR", "RECTIF", "FEC_ENT", "FECALT", "FECMOD", "CONSOLR"))
                    ->where("NUM_REF = ?", trim($referencia));
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                return $stmt;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function datosPedimento($referencia) {
        try {
            $select = $this->_db->select()
                    ->from(array("p" => "sm3ped"), array("NUM_REF as referencia", "NUM_PED as pedimento", "IMP_EXP as ie", "TIP_CAM as tipoCambio", "RFCCTE as rfcCliente", "CVEPEDIM as cvePed", "REGIMEN as regimen", "VALADUANA as valorAduana", "SUB as subdivision", "CONSOLR as consolidado", "RECTIF as rectificacion", "FEC_ENT as fechaEntrada", "FECALT as fechaAlta", "FECMOD as fechaModificacion", "FIRMA as firmaValidacion"))
                    ->joinLeft(array("b" => "saiban"), "p.NUM_PED = b.DOCTO", array("FIRMA AS firmaBanco"))
                    ->where("p.NUM_REF = ?", trim($referencia));
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                return $stmt;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function rfcReferencia($referencia) {
        try {
            $select = $this->_db->select();

            $select->from(array("p" => "sm3ped"), array("RFCCTE"))
                    ->joinLeft(array("c" => "cmcli"), "c.CVE_IMP = p.CVE_IMP", array("c.NOMCLI AS NOMCLI",))
                    ->where("p.NUM_REF = ?", $referencia);
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                return $stmt;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function seleccionarFacturas($pedimento, $facturas, $tipo) {
        try {
            $misc = new OAQ_Misc();
            $array = explode("|", $facturas);
            $in = "";
            foreach (range(0, count($array) - 1) as $k) {
                $in .= "'" . $array[$k] . "'";
                if ($k < count($array) - 1) {
                    $in .= ",";
                }
            }
            $ped = $this->infoPedimentoBasica($pedimento);
            $conn = $this->_db->getConfig();
            if ($conn["port"] == 1433) {
                $concat = new Zend_Db_Expr("p.ADUANAD + p.SECCDES AS Aduana");
                $fecfac = new Zend_Db_Expr("CONVERT(varchar, f.FECFAC, 111) AS FechaFactura");
                $fecfacCon = new Zend_Db_Expr("CONVERT(varchar, f.FEC_FAC, 111) AS FechaFactura");
            } else {
                $concat = new Zend_Db_Expr("CONCAT(p.ADUANAD,p.SECCDES) AS Aduana");
                $fecfac = "f.FECFAC AS FechaFactura";
                $fecfacCon = "f.FEC_FAC AS FechaFactura";
            }
            if ($ped["CONSOLR"] == "N") {
                if ($tipo == "TOCE.IMP") {
                    $select = $this->_db->select()
                            ->distinct()
                            ->from(array("p" => "sm3ped"), array("p.PATENTE AS Patente",
                                "p.NUM_PED AS Pedimento",
                                $concat,
                                "p.NUM_REF AS Referencia",
                                "p.CVE_IMP AS CveImp",
                                "p.CONSOLR AS Consolidado"
                            ))
                            ->joinLeft(array("f" => "sm3fact"), "f.NUM_REF = p.NUM_REF", array("f.NUMFAC AS NumFactura",
                                $fecfac,
                                "f.CVEPROV AS CvePro",
                                "f.SUB AS Subdivision",
                                "f.ORDENFAC AS OrdenFact",
                                "f.VALDLS AS ValDls",
                                "f.VALEXT AS ValExt",
                            ))
                            ->joinLeft(array("c" => "cmcli"), "c.CVE_IMP = p.CVE_IMP", array("c.RFC AS CteRfc",
                                "c.NOMCLI AS CteNombre",
                                "c.DIRCALLE AS CteCalle",
                                "c.DIRNUMEXT AS CteNumExt",
                                "c.DIRNUMINT AS CteNumInt",
                                "c.COLONIA AS CteColonia",
                                "c.DIRMUNIC AS CteMun",
                                "c.DIRENTFED AS CteEdo",
                                "c.DIRPAIS AS CtePais",
                                "c.CP AS CteCP",
                            ))
                            ->joinLeft(array("pr" => "cmpro"), "pr.CVE_PRO = f.CVEPROV", array("pr.NOMPRO AS ProNombre",
                                "pr.DIRCALLE AS ProCalle",
                                "pr.DIRNUMEXT AS ProNumExt",
                                "pr.DIRNUMINT AS ProNumInt",
                                "pr.DIRCOLONI AS ProColonia",
                                "pr.DIRMUNI AS ProMun",
                                "pr.DIRESTADO AS ProEdo",
                                "pr.DIRPAIS AS ProPais",
                                "pr.NUM_TAX AS ProTaxID",
                                "pr.CP AS ProCP",
                            ))
                            ->where("p.NUM_PED = ?", $pedimento)
                            ->where("f.NUMFAC IN ({$in})");
                } elseif ($tipo == "TOCE.EXP") {
                    $sql = "SELECT 
                        p.PATENTE AS Patente, 
                        p.NUM_PED AS Pedimento, 
                        p.ADUANAD + p.SECCDES AS Aduana, 
                        p.NUM_REF AS Referencia, 
                        p.CVE_IMP AS CveImp, 
                        p.CONSOLR AS Consolidado, 
                        f.NUMFAC AS NumFactura, 
                        CONVERT(varchar, f.FECFAC, 111) AS FechaFactura, 
                        f.CVEPROV AS CvePro, 
                        f.SUB AS Subdivision, 
                        f.ORDENFAC AS OrdenFact, 
                        f.VALDLS AS ValDls, 
                        f.VALEXT AS ValExt,
                        c.RFC AS CteRfc, 
                        c.NOMCLI AS CteNombre, 
                        c.DIRCALLE AS CteCalle, 
                        c.DIRNUMEXT AS CteNumExt, 
                        c.DIRNUMINT AS CteNumInt, 
                        c.COLONIA AS CteColonia, 
                        c.DIRMUNIC AS CteMun, 
                        c.DIRENTFED AS CteEdo, 
                        c.DIRPAIS AS CtePais, 
                        c.CP AS CteCP,
                        d.NOMPRO AS ProNombre, 
                        d.DIRCALLE AS ProCalle, 
                        d.DIRNUMEXT AS ProNumExt, 
                        d.DIRNUMINT AS ProNumInt, 
                        d.DIRCOLONI AS ProColonia, 
                        d.DIRMUNI AS ProMun, 
                        d.DIRESTADO AS ProEdo, 
                        d.DIRPAIS AS ProPais, 
                        d.NUM_TAX AS ProTaxID, 
                        d.CP AS ProCP
                        FROM sm3ped AS p 
                        LEFT JOIN sm3fact AS f ON f.NUM_REF = p.NUM_REF 
                        CROSS APPLY (
                                SELECT TOP 1 * FROM CMCLI c WHERE c.CVE_IMP = p.CVE_IMP
                        ) c
                        CROSS APPLY (
                                SELECT TOP 1 * FROM CMDEST d WHERE d.CVE_PRO = f.CVEPROV 
                        ) d
                        WHERE (p.NUM_PED = '{$pedimento}') AND (f.NUMFAC IN ({$in}));";
                    $stmt = $this->_db->fetchAll($sql);
                }
            } elseif ($ped["CONSOLR"] == "S") {
                if ($tipo == "TOCE.IMP") {
                    $select = $this->_db->select()
                            ->from(array("p" => "sm3ped"), array("p.PATENTE AS Patente",
                                "p.NUM_PED AS Pedimento",
                                $concat,
                                "p.NUM_REF AS Referencia",
                                "p.CVE_IMP AS CveImp",
                                "p.CONSOLR AS Consolidado"
                            ))
                            ->joinLeft(array("f" => "sm3confa"), "f.NUM_PED = p.NUM_PED", array("f.NUM_FAC AS NumFactura",
                                $fecfacCon,
                                "f.CVEPROV AS CvePro",
                                "f.ORDEN AS OrdenFact",
                                "f.VALDLS AS ValDls",
                                "f.VALEXT AS ValExt",
                            ))
                            ->joinLeft(array("c" => "cmcli"), "c.CVE_IMP = p.CVE_IMP", array("c.RFC AS CteRfc",
                                "c.NOMCLI AS CteNombre",
                                "c.DIRCALLE AS CteCalle",
                                "c.DIRNUMEXT AS CteNumExt",
                                "c.DIRNUMINT AS CteNumInt",
                                "c.COLONIA AS CteColonia",
                                "c.DIRMUNIC AS CteMun",
                                "c.DIRENTFED AS CteEdo",
                                "c.DIRPAIS AS CtePais",
                            ))
                            ->joinLeft(array("pr" => "cmpro"), "pr.CVE_PRO = f.CVEPROV", array("pr.NOMPRO AS ProNombre",
                                "pr.DIRCALLE AS ProCalle",
                                "pr.DIRNUMEXT AS ProNumExt",
                                "pr.DIRNUMINT AS ProNumInt",
                                "pr.DIRCOLONI AS ProColonia",
                                "pr.DIRMUNI AS ProMun",
                                "pr.DIRESTADO AS ProEdo",
                                "pr.DIRPAIS AS ProPais",
                                "pr.NUM_TAX AS ProTaxID",
                            ))
                            ->where("p.NUM_PED = ?", $pedimento)
                            ->where("f.NUM_FAC IN ({$in})");
                } elseif ($tipo == "TOCE.EXP") {
                    $select = $this->_db->select()
                            ->from(array("p" => "sm3ped"), array("p.PATENTE AS Patente",
                                "p.NUM_PED AS Pedimento",
                                $concat,
                                "p.NUM_REF AS Referencia",
                                "p.CVE_IMP AS CveImp",
                                "p.CONSOLR AS Consolidado",
                            ))
                            ->joinLeft(array("f" => "sm3confa"), "f.NUM_PED = p.NUM_PED", array("f.NUM_FAC AS NumFactura",
                                $fecfacCon,
                                "f.CVEPROV AS CvePro",
                                "f.ORDEN AS OrdenFact",
                                "f.VALDLS AS ValDls",
                                "f.VALEXT AS ValExt",
                            ))
                            ->joinLeft(array("c" => "cmcli"), "c.CVE_IMP = p.CVE_IMP", array("c.RFC AS CteRfc",
                                "c.NOMCLI AS CteNombre",
                                "c.DIRCALLE AS CteCalle",
                                "c.DIRNUMEXT AS CteNumExt",
                                "c.DIRNUMINT AS CteNumInt",
                                "c.COLONIA AS CteColonia",
                                "c.DIRMUNIC AS CteMun",
                                "c.DIRENTFED AS CteEdo",
                                "c.DIRPAIS AS CtePais",
                            ))
                            ->joinLeft(array("d" => "cmdest"), "d.CVE_PRO = f.CVEPROV", array("d.NOMPRO AS ProNombre",
                                "d.DIRCALLE AS ProCalle",
                                "d.DIRNUMEXT AS ProNumExt",
                                "d.DIRNUMINT AS ProNumInt",
                                "d.DIRCOLONI AS ProColonia",
                                "d.DIRMUNI AS ProMun",
                                "d.DIRESTADO AS ProEdo",
                                "d.DIRPAIS AS ProPais",
                                "d.NUM_TAX AS ProTaxID",
                            ))
                            ->where("p.NUM_PED = ?", $pedimento)
                            ->where("f.NUM_FAC IN ({$in})");
                }
            }
            if (!isset($stmt)) {
                $stmt = $this->_db->fetchAll($select);
            }
            if (!isset($stmt) || empty($stmt)) {            
                $select = $this->_db->select()
                        ->from(array("p" => "sm3ped"), array("p.PATENTE AS Patente",
                            "p.NUM_PED AS Pedimento",
                            $concat,
                            "p.NUM_REF AS Referencia",
                            "p.CVE_IMP AS CveImp",
                            "p.CONSOLR AS Consolidado",
                        ))
                        ->joinLeft(array("f" => "sm3fact"), "f.NUM_REF = p.NUM_REF", array("f.NUMFAC AS NumFactura",
                            new Zend_Db_Expr("CONVERT (VARCHAR, f.FECFAC, 111) AS FechaFactura"),
                            "f.CVEPROV AS CvePro",
                            "f.ORDENFAC AS OrdenFact",
                            "f.VALDLS AS ValDls",
                            "f.VALEXT AS ValExt",
                        ))
                        ->joinLeft(array("c" => "cmcli"), "c.CVE_IMP = p.CVE_IMP", array("c.RFC AS CteRfc",
                            "c.NOMCLI AS CteNombre",
                            "c.DIRCALLE AS CteCalle",
                            "c.DIRNUMEXT AS CteNumExt",
                            "c.DIRNUMINT AS CteNumInt",
                            "c.COLONIA AS CteColonia",
                            "c.DIRMUNIC AS CteMun",
                            "c.DIRENTFED AS CteEdo",
                            "c.DIRPAIS AS CtePais",
                        ))
                        ->joinLeft(array("d" => "cmdest"), "d.CVE_PRO = f.CVEPROV", array("d.NOMPRO AS ProNombre",
                            "d.DIRCALLE AS ProCalle",
                            "d.DIRNUMEXT AS ProNumExt",
                            "d.DIRNUMINT AS ProNumInt",
                            "d.DIRCOLONI AS ProColonia",
                            "d.DIRMUNI AS ProMun",
                            "d.DIRESTADO AS ProEdo",
                            "d.DIRPAIS AS ProPais",
                            "d.NUM_TAX AS ProTaxID",
                        ))
                        ->where("p.NUM_PED = ?", $pedimento)
                        ->where("f.NUMFAC IN ({$in})");
                $stmt = $this->_db->fetchAll($select);
            }
            if ($stmt) {
                $facturas = array();
                foreach ($stmt as $item) {
                    if ($ped["CONSOLR"] == "S") {
                        $orden = $item["OrdenFactCon"];
                    } else {
                        $orden = $item["OrdenFact"];
                    }
                    $item["CteRfc"] = preg_replace("!\s+!", " ", trim($item["CteRfc"]));
                    $item["CteNombre"] = preg_replace("!\s+!", " ", trim($item["CteNombre"]));
                    $item["ProTaxID"] = preg_replace("!\s+!", " ", trim($item["ProTaxID"]));
                    $item["ProNombre"] = preg_replace("!\s+!", " ", trim($item["ProNombre"]));
                    $item["ProPais"] = preg_replace("!\s+!", " ", trim($item["ProPais"]));
                    $item["CtePais"] = preg_replace("!\s+!", " ", trim($item["CtePais"]));

                    $item["IdFact"] = $misc->getUuid($item["Patente"] . $item["Pedimento"] . $item["Aduana"] . $item["NumFactura"] . $item["OrdenFact"] . time());
                    $item["TipoOperacion"] = $tipo;
                    $item["Observaciones"] = "";
                    $item["NumParte"] = "";
                    $item["CertificadoOrigen"] = "0";
                    $item["NumExportador"] = "";
                    $productos = $this->obtenerProductos($pedimento, $item["NumFactura"], $orden);
                    $item["Productos"] = $productos;
                    $item["Manual"] = 0;
                    $facturas[] = $item;
                }
                return $facturas;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerProductos($pedimento, $factura, $ordenFact) {
        try {
            $misc = new OAQ_Misc();
            $ped = $this->infoPedimentoBasica($pedimento);
            if ($ped["CONSOLR"] == "N") {
                $select = $this->_db->select()
                        ->distinct()
                        ->from(array("f" => "CM3FRA"), array(
                            "f.NUM_REF",
                            "f.CODIGO",
                            "f.SUBFRA",
                            new Zend_Db_Expr("f.DESC1 Collate SQL_Latin1_General_CP1253_CI_AI AS DESC1"),
                            "f.ORDEN",
                            new Zend_Db_Expr("CASE WHEN f.MONVAL = 'USD' THEN f.VALCOM / f.CAN_OMA WHEN f.MONVAL <> 'USD' THEN f.VALCOM / f.CAN_OMA ELSE 0 END AS PREUNI"),
                            "f.VALCOM",
                            "f.MONVAL",
                            "f.VALMN",
                            new Zend_Db_Expr("CASE WHEN f.MONVAL = 'USD' THEN (f.VALCOM / f.CAN_OMA) * f.CAN_OMA WHEN f.MONVAL <> 'USD' THEN ((f.VALCOM / f.CAN_OMA) * f.CAN_OMA) * f.VALCEQ ELSE 0 END AS VALDLS"),
                            "f.CANTFAC",
                            "f.CANTTAR",
                            "f.UMC",
                            "f.UMT",
                            "f.PAIORI",
                            "f.PAICOM",
                            "f.FACTAJU",
                            "f.CERTLC",
                            "f.PARTE",
                            "f.CAN_OMA",
                            "f.UMC_OMA",
                            new Zend_Db_Expr("f.DESC_COVE Collate SQL_Latin1_General_CP1253_CI_AI AS DESC_COVE"),
                            "(SELECT TOP 1 fa.FACEQ FROM SM3FACT AS fa WHERE fa.NUMFAC = '{$factura}' AND NUM_REF = '{$ped["NUM_REF"]}') AS VALCEQ"
                        ))
                        ->where("f.NUM_REF = ?", $ped["NUM_REF"])
                        ->where("f.FACTFRA = ?", $factura);
                $stmt = $this->_db->fetchAll($select);
            } elseif ($ped["CONSOLR"] == "S") {
                $select = $this->_db->select()
                        ->from(array("p" => "SM3PED"), array("p.NUM_REF"))
                        ->joinLeft(array("f" => "SM3CONFA"), "f.NUM_PED = p.NUM_PED", array())
                        ->joinLeft(array("fr" => "SM3CONFR"), "p.NUM_PED = p.NUM_PED", array("fr.CODIGO",
                            new Zend_Db_Expr("fr.DESC1 Collate SQL_Latin1_General_CP1253_CI_AI AS DESC1"),
                            "fr.ORDEN",
                            new Zend_Db_Expr("CASE WHEN fr.MONEDA = 'USD' THEN fr.V_FACDLS / fr.CAN_OMA WHEN fr.MONEDA <> 'USD' THEN fr.V_FACDLS / fr.CAN_OMA ELSE 0 END AS PREUNI"),
                            "fr.V_FACDLS AS VALCOM",
                            "fr.V_FACDLS AS VALDLS",
                            "fr.MONEDA AS MONVAL",
                            "fr.EQ AS VALCEQ",
                            "fr.CAN_FAC AS CANTFAC",
                            "fr.CAN_TAR AS CANTTAR",
                            "fr.UNI_FAC AS UMC",
                            "fr.UNI_TAR AS UMT",
                            "fr.PAIORI",
                            "fr.PAICOM",
                            "fr.NUMPARTE AS PARTE",
                            "fr.CAN_OMA",
                            "fr.UMC_OMA",
                            new Zend_Db_Expr("fr.DESC_COVE Collate SQL_Latin1_General_CP1253_CI_AI AS DESC_COVE"),
                            ))
                        ->where("p.NUM_PED = {$pedimento} AND fr.NUM_PED = {$pedimento}")
                        ->where("f.NUM_FAC = ?", $factura)
                        ->where("f.ORDEN = fr.ORDEN AND f.NUM_OPE = fr.NUM_OPE");
                $stmt = $this->_db->fetchAll($select);
                if (!isset($stmt) || empty($stmt)) {
                    $select = $this->_db->select()
                            ->distinct()
                            ->from(array("f" => "CM3FRA"), array("f.NUM_REF",
                                "f.CODIGO",
                                "f.SUBFRA",
                                new Zend_Db_Expr("f.DESC1 Collate SQL_Latin1_General_CP1253_CI_AI AS DESC1"),
                                "f.ORDEN",
                                new Zend_Db_Expr("CASE WHEN f.MONVAL = 'USD' THEN f.VALCOM / f.CAN_OMA WHEN f.MONVAL <> 'USD' THEN f.VALCOM / f.CAN_OMA ELSE 0 END AS PREUNI"),
                                "f.VALCOM",
                                "f.MONVAL",
                                "f.VALMN",
                                new Zend_Db_Expr("CASE WHEN f.MONVAL = 'USD' THEN (f.VALCOM / f.CAN_OMA) * f.CAN_OMA WHEN f.MONVAL <> 'USD' THEN ((f.VALCOM / f.CAN_OMA) * f.CAN_OMA) * f.VALCEQ ELSE 0 END AS VALDLS"),
                                "f.CANTFAC",
                                "f.CANTTAR",
                                "f.UMC",
                                "f.UMT",
                                "f.PAIORI",
                                "f.PAICOM",
                                "f.FACTAJU",
                                "f.CERTLC",
                                "f.PARTE",
                                "f.CAN_OMA",
                                "f.UMC_OMA",
                                new Zend_Db_Expr("f.DESC_COVE Collate SQL_Latin1_General_CP1253_CI_AI AS DESC_COVE"),
                                new Zend_Db_Expr("(SELECT TOP 1 fa.FACEQ FROM SM3FACT AS fa WHERE fa.NUMFAC = '{$factura}' AND NUM_REF = '{$ped["NUM_REF"]}') AS VALCEQ")
                            ))
                            ->where("f.NUM_REF = ?", $ped["NUM_REF"])
                            ->where("f.FACTFRA = ?", $factura);
                    $stmt = $this->_db->fetchAll($select);
                }
            }
            if ($stmt) {
                $prods = array();
                foreach ($stmt as $k => $item) {
                    $tbl = new Vucem_Model_VucemUnidadesMapper();
                    $uuid = $misc->getUuid($pedimento . $factura . $item["PARTE"] . $item["CODIGO"] . md5(time()) . $k);
                    if ($item["MONVAL"] == "USD") {
                        $valUsd = $item["VALDLS"];
                    } elseif ($item["MONVAL"] == "MXP") {
                        $valUsd = $item["VALDLS"];
                    } else {
                        $valUsd = $item["VALCOM"] * $item["VALCEQ"];
                    }
                    if(isset($item["ORDEN"]) && isset($ordenFact)) {
                        $obs = $this->getObservations($ped["NUM_REF"], $item["ORDEN"], $ordenFact, $factura);
                    }
                    $prods[] = array(
                        "ID_PROD" => $uuid,
                        "SUB" => isset($item["SUB"]) ? $item["SUB"] : "0",
                        "ORDEN" => $item["ORDEN"],
                        "CODIGO" => $item["CODIGO"],
                        "SUBFRA" => isset($item["SUBFRA"]) ? $item["SUBFRA"] : null,
                        "DESC1" => $item["DESC1"],
                        "PREUNI" => ($item["PREUNI"] == null) ? $item["VALCOM"] / $item["CANTFAC"] : $item["PREUNI"],
                        "VALCOM" => $item["VALCOM"],
                        "MONVAL" => $item["MONVAL"],
                        "VALCEQ" => $item["VALCEQ"],
                        "VALMN" => $item["VALMN"],
                        "VALDLS" => isset($item["VALDLS"]) ? $item["VALDLS"] : $item["VALCOM"],
                        "CANTFAC" => $item["CANTFAC"],
                        "UMC" => $item["UMC"],
                        "CANTTAR" => $item["CANTTAR"],
                        "UMT" => $item["UMT"],
                        "PAIORI" => $item["PAIORI"],
                        "PAICOM" => $item["PAICOM"],
                        "FACTAJU" => $item["FACTAJU"],
                        "CERTLC" => $item["CERTLC"],
                        "PARTE" => $item["PARTE"],
                        "CAN_OMA" => $item["CAN_OMA"],
                        "UMC_OMA" => ($item["UMC_OMA"] == null) ? $tbl->getOma($item["UMC"]) : $item["UMC_OMA"],
                        "DESC_COVE" => ($item["DESC_COVE"] == null) ? $item["DESC1"] : $item["DESC1"],
                        "OBS" => (isset($obs["OBS"]) && $obs["OBS"] != NULL && $obs["OBS"] != "") ? $obs["OBS"] : null,
                        "MARCA" => ($obs["MARCA"] != NULL && $obs["MARCA"] != "") ? $obs["MARCA"] : null,
                        "MODELO" => ($obs["MODELO"] != NULL && $obs["MODELO"] != "") ? $obs["MODELO"] : null,
                        "SUBMODELO" => ($obs["SUBMODELO"] != NULL && $obs["SUBMODELO"] != "") ? $obs["SUBMODELO"] : null,
                        "NUMSERIE" => ($obs["NUM_SERIE"] != NULL && $obs["NUM_SERIE"] != "") ? $obs["NUM_SERIE"] : null,
                    );
                    unset($uuid);
                }
                return $prods;
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    protected function getObservations($referencia, $orden, $ordenFact, $numFact = null) {
        try {
            $select = $this->_db->select()
                    ->from("cm3obsfr", array("OBS", "MARCA", "MODELO", "SUBMODELO", "NUM_SERIE"))
                    ->where("NUM_REF = ?", $referencia)
                    ->where("ORDEN = ?", $orden)
                    ->where("ORDENFAC = ?", $ordenFact)
                    ->limit(1);
            if (isset($numFact)) {
                $select->where("FACTFRA = ?", $numFact);
            }
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                return $stmt;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    protected function observaciones($obs, $pattern) {
        $split = explode("\r\n", $obs);
        foreach ($split as $line) {
            if (preg_match($pattern, $line)) {
                $found = explode(":", $line);
                return trim($found[1]);
            }
        }
        return null;
    }

    public function verificarPedimento($pedimento) {
        try {
            $select = $this->_db->select()
                    ->from("sm3ped", array("USUMOD", "CONSOLR"))
                    ->where("NUM_PED = ?", $pedimento);
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                return array(
                    "usuario" => $stmt["USUMOD"],
                    "consolidado" => (trim($stmt["CONSOLR"]) == "S") ? true : null,
                );
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function cveImportador($rfc) {
        try {
            $select = $this->_db->select()
                    ->from("cmcli", array("CVE_IMP"))
                    ->where("RFC = ?", $rfc);
            $stmt = $this->_db->fetchRow($select);

            if ($stmt) {
                return $stmt["CVE_IMP"];
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function buscarReferencia($ref) {
        try {
            $fields = array(
                "p.FECMOD AS FECHA",
                "p.NUM_REF AS REFERENCIA",
                "p.NUM_PED AS PEDIMENTO",
                "p.PATENTE AS PATENTE",
                "p.ADUANAD AS ADUANAD",
                "p.SECCDES AS SECCDES",
            );
            $select = $this->_db->select()
                    ->from(array("p" => "sm3ped"), $fields)
                    ->where("p.NUM_REF LIKE ?", $ref);

            $stmt = $this->_db->fetchAll($select);
            if ($stmt) {
                $data = array();
                foreach ($stmt as $item) {
                    $data[] = array(
                        "year" => date("Y", strtotime($item["FECHA"])),
                        "fecha" => date("Y", strtotime($item["FECHA"])) . "/" . date("m", strtotime($item["FECHA"])) . "/" . date("d", strtotime($item["FECHA"])),
                        "referencia" => $item["REFERENCIA"],
                        "pedimento" => $item["PEDIMENTO"],
                        "patente" => $item["PATENTE"],
                        "aduana" => $item["ADUANAD"] . $item["SECCDES"],
                    );
                }
                return $data;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function buscarGuia($guia) {
        try {
            $select = $this->_db->select()
                    ->from(array("g" => "sm3guia"), array("NUM_REF as referencia"))
                    ->joinLeft(array("t" => "sm3ped"), "g.NUM_REF = t.NUM_REF", array("t.NUM_PED as pedimento", "t.RFCCTE AS rfcCliente"))
                    ->where("REPLACE(g.NUMGUIA,' ','') LIKE ?", "%" . $guia . "%");
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function buscarClienteReferencia($referencia) {
        try {
            $select = $this->_db->select()
                    ->from("sm3ped")
                    ->where("NUM_REF LIKE ?", $referencia . "%")
                    ->limit(1);
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                return array(
                    "rfc" => $stmt["RFCCTE"],
                    "patente" => $stmt["PATENTE"],
                    "aduana" => $stmt["ADUANAD"] . $stmt["SECCDES"],
                );
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function buscarFactura($referencia, $numFactura, $patente = null) {
        try {
            $select = $this->_db->select()
                    ->from(array("f" => "SM3FACT"))
                    ->where("f.NUM_REF = ?", $referencia)
                    ->where("f.NUMFAC = ?", $numFactura);
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    

    public function actualizarCoveEnFactura($referencia, $numFactura, $cove) {
        try {
            $stmt = $this->_db->query("UPDATE SM3FACT SET ACUSECOVE = '{$cove}' WHERE NUM_REF = '{$referencia}' AND NUMFAC = '{$numFactura}';");
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function buscarFacturaConsolidado($pedimento, $numFactura, $numOperacion = null) {
        try {
            $select = $this->_db->select()
                    ->from(array("f" => "SM3CONFA"))
                    ->where("f.NUM_PED = ?", $pedimento)
                    ->where("f.NUM_FAC = ?", $numFactura);
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function actualizarCoveConsolidadoEnFactura($pedimento, $numFactura, $cove, $numOperacion = null) {
        try {
            $stmt = $this->_db->query("UPDATE SM3CONFA SET FACTURACOVE = '{$cove}' WHERE NUM_PED = '{$pedimento}' AND NUM_FAC = '{$numFactura}';");
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function folioEdoc($referencia) {
        try {
            $select = $this->_db->select()
                    ->from("SM3CASOS")
                    ->where("NUM_REF = ?", $referencia)
                    ->where("SUB = 0")
                    ->where("ORDEN = 0")
                    ->order("FOLIO DESC")
                    ->limit(1);
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                return $stmt["FOLIO"];
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function verificarEdoc($referencia, $edoc) {
        try {
            $select = $this->_db->select()
                    ->from("SM3CASOS")
                    ->where("NUM_REF = ?", $referencia)
                    ->where("TIPCAS = 'ED'")
                    ->where("IDCASO = ?", $edoc);
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Exception $e) {
            throw new Exception("<b>DB Exception on " . __METHOD__ . "</b> " . $e->getMessage());
        } catch (Exception $e) {
            throw new Exception("<b>Exception on " . __METHOD__ . "</b> " . $e->getMessage());
        }
    }

    public function verificarCoveEnFactura($referencia, $numfac) {
        try {
            $select = $this->_db->select()
                    ->from("SM3FACT", array("ACUSECOVE"))
                    ->where("NUM_REF = ?", $referencia)
                    ->where("NUMFAC = ?", $numfac);
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                return $stmt["ACUSECOVE"];
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function verificarPagoPedimento($referencia, $pedimento) {
        try {
            $select = $this->_db->select()
                    ->from(array("p" => "SM3PED"), array("FIRMA"))
                    ->joinLeft(array("b" => "SAIBAN"), "p.NUM_PED = b.DOCTO", array("FIRMA AS FIRMABANCO"))
                    ->where("p.NUM_REF = ?", $referencia)
                    ->where("p.NUM_PED = ?", $pedimento);
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                return $stmt["FIRMABANCO"];
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function actualizarEdocEnPedimento($referencia, $folio, $edoc) {
        try {
            $data = array(
                "NUM_REF" => $referencia,
                "SUB" => 0,
                "ORDEN" => 0,
                "TIPCAS" => "ED",
                "IDCASO" => $edoc,
                "IDCASO2" => "",
                "IDCASO3" => "",
                "FOLIO" => $folio,
            );
            $added = $this->_db->insert("SM3CASOS", $data);
            if ($added) {
                return true;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function buscarReferenciaAdv($ref, $pedimento, $patente) {
        try {
            $fields = array(
                "p.FECMOD AS FECHA",
                "p.NUM_REF AS REFERENCIA",
                "p.NUM_PED AS PEDIMENTO",
                "p.PATENTE AS PATENTE",
                "p.ADUANAD AS ADUANAD",
                "p.SECCDES AS SECCDES",
            );
            $select = $this->_db->select()
                    ->from(array("p" => "sm3ped"), $fields)
                    ->where("p.NUM_REF = ?", $ref)
                    ->where("p.NUM_PED = ?", $pedimento)
                    ->where("p.PATENTE = ?", $patente);

            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                $data = array(
                    "year" => date("Y", strtotime($stmt["FECHA"])),
                    "fecha" => date("Y", strtotime($stmt["FECHA"])) . "/" . date("m", strtotime($stmt["FECHA"])) . "/" . date("d", strtotime($stmt["FECHA"])),
                    "referencia" => $stmt["REFERENCIA"],
                    "pedimento" => $stmt["PEDIMENTO"],
                    "patente" => $stmt["PATENTE"],
                    "aduana" => $stmt["ADUANAD"] . $stmt["SECCDES"],
                );
                return $data;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function ultimasModificaciones() {
        try {
            $conn = $this->_db->getConfig();
            if ($conn["port"] == 1433) {
                $columns = array("NUM_REF",
                    "PATENTE",
                    "NUM_PED",
                    new Zend_Db_Expr("CONVERT(VARCHAR(23),FEC_ENT,126) AS FEC_ENT"),
                    new Zend_Db_Expr("CONVERT(VARCHAR(23),FECALT,126) AS FECALT"),
                    "USUMOD",
                    new Zend_Db_Expr("CONVERT(VARCHAR(23),FECMOD,126) AS FECMOD"),
                    new Zend_Db_Expr("CONVERT(VARCHAR(23),FEC_PAG,126) AS FEC_PAG"),
                    new Zend_Db_Expr("CONVERT(VARCHAR(23),FECEXT,126) AS FECEXT"),
                    "FIRMA",
                    "FIRMADIG",
                    "CONVERT(VARCHAR(23),FEC_APER,126) AS FEC_APER", "RESPONS", "CONVERT(VARCHAR(23),FEC_ENORI,126) AS FEC_ENORI"
                    );
            } else {
                $columns = array(
                    "NUM_REF",
                    "PATENTE",
                    "NUM_PED",
                    "FEC_ENT",
                    "FECALT",
                    "USUMOD",
                    "FECMOD",
                    "FEC_PAG",
                    "FECEXT",
                    "FIRMA",
                    "FIRMADIG",
                    "FEC_APER",
                    "RESPONS",
                    "FEC_ENORI"
                    );
            }
            $select = $this->_db->select()
                    ->from(array("p" => "sm3ped"), $columns)
                    ->where("FIRMA = '' AND FECMOD >= '2013-01-01' AND NUM_PED <> 0")
                    ->order("FECMOD DESC");

            $stmt = $this->_db->fetchAll($select);
            if ($stmt) {
                foreach ($stmt as $item) {
                    $data[] = array(
                        "referencia" => $item["NUM_REF"],
                        "patente" => $item["PATENTE"],
                        "pedimento" => $item["NUM_PED"],
                        "fecha_ent" => $item["FEC_ENT"],
                        "fecha_alt" => $item["FECALT"],
                        "modifico" => $item["USUMOD"],
                        "fecha_mod" => $item["FECMOD"],
                        "fecha_pago" => $item["FEC_PAG"],
                        "fecha_ext" => $item["FECEXT"],
                    );
                }
                return $data;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function ejecutarQueryAnexo24SitaSql($Query) {
        try {
            $stmt = $this->_db->fetchAll($Query);
            if ($stmt) {
                return $stmt;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function pedimentosPorCorresponsal($year) {
        try {
            $Query = "SELECT 
                    p.RFCCTE,
                    c.NOMCLI,
                    SUM(CASE WHEN MONTH(p.FEC_PAG) = 1 THEN 1 ELSE 0 END) AS Ene,
                    SUM(CASE WHEN MONTH(p.FEC_PAG) = 2 THEN 1 ELSE 0 END) AS Feb,
                    SUM(CASE WHEN MONTH(p.FEC_PAG) = 3 THEN 1 ELSE 0 END) AS Mar,
                    SUM(CASE WHEN MONTH(p.FEC_PAG) = 4 THEN 1 ELSE 0 END) AS Abr,
                    SUM(CASE WHEN MONTH(p.FEC_PAG) = 5 THEN 1 ELSE 0 END) AS May,
                    SUM(CASE WHEN MONTH(p.FEC_PAG) = 6 THEN 1 ELSE 0 END) AS Jun,
                    SUM(CASE WHEN MONTH(p.FEC_PAG) = 7 THEN 1 ELSE 0 END) AS Jul,
                    SUM(CASE WHEN MONTH(p.FEC_PAG) = 8 THEN 1 ELSE 0 END) AS Ago,
                    SUM(CASE WHEN MONTH(p.FEC_PAG) = 9 THEN 1 ELSE 0 END) AS Sep,
                    SUM(CASE WHEN MONTH(p.FEC_PAG) = 10 THEN 1 ELSE 0 END) AS 'Oct',
                    SUM(CASE WHEN MONTH(p.FEC_PAG) = 11 THEN 1 ELSE 0 END) AS Nov,
                    SUM(CASE WHEN MONTH(p.FEC_PAG) = 12 THEN 1 ELSE 0 END) AS Dic,
                    COUNT(*) AS Total    
                FROM cmcli AS c
                LEFT JOIN sm3ped AS p ON c.CVE_IMP = p.CVE_IMP
                WHERE (p.FEC_PAG BETWEEN '{$year}-01-01' AND '{$year}-12-31') 
                    AND p.FIRMA <> '' AND p.FIRMADIG <> '' AND p.ADUANAD = '64'
                GROUP BY c.NOMCLI,p.RFCCTE
                ORDER BY c.NOMCLI ASC;";
            $stmt = $this->_db->fetchAll($Query);
            if ($stmt) {
                return $stmt;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function getOperationsByMonth($rfc, $fecha) {
        try {
            $conn = $this->_db->getConfig();
            if ($conn["port"] == 1433) {
                $columns = array(new Zend_Db_Expr("CONVERT(VARCHAR(23),p.FEC_PAG,111) AS FEC_PAG"), "p.ADUANAD", "p.SECCDES", "p.PATENTE", "p.NUM_PED", "p.IMP_EXP", "p.FIRMA", "B.FIRMA AS FIRMAB");
            } else {
                $columns = array("p.FEC_PAG", "p.ADUANAD", "p.SECCDES", "p.PATENTE", "p.NUM_PED", "p.IMP_EXP", "p.FIRMA", "b.FIRMA AS FIRMAB");
            }
            $select = $this->_db->select();
            $select->from(array("p" => "sm3ped"), $columns)
                    ->joinLeft(array("b" => "saiban"), "p.NUM_PED = b.DOCTO", array("b.FIRMA AS FIRMAB"))
                    ->where("p.RFCCTE = ?", $rfc)
                    ->where("YEAR(p.FEC_PAG) = ?", (int) date("Y", strtotime($fecha)))
                    ->where("MONTH(p.FEC_PAG) = ?", (int) date("m", strtotime($fecha)))
                    ->where("(p.FIRMA IS NOT NULL AND p.FIRMA <> '' AND b.FIRMA IS NOT NULL)");
            $stmt = $this->_db->fetchAll($select);
            if ($stmt) {
                foreach ($stmt as $item) {
                    $data[] = array(
                        "Operacion" => date("y", strtotime($item["FEC_PAG"])) . "-" . $item["ADUANAD"] . $item["SECCDES"] . "-" . $item["PATENTE"] . "-" . $item["NUM_PED"],
                        "FechaPago" => $item["FEC_PAG"],
                        "Aduana" => $item["ADUANAD"] . $item["SECCDES"],
                        "Patente" => $item["PATENTE"],
                        "Pedimento" => $item["NUM_PED"],
                        "TipoOpe" => ($item["IMP_EXP"] == "1") ? "I" : "E",
                        "FirmaValidacion" => $item["FIRMA"],
                        "FirmaBanco" => $item["FIRMAB"],
                    );
                }
                return $data;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function validateCustomer($rfc) {
        try {
            $select = $this->_db->select()
                    ->from("cmcli", array("nomcli"))
                    ->where("RFC = ?", $rfc);
            $reseult = $this->_db->fetchRow($select);
            if ($reseult) {
                return true;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function getOperations($rfc, $fechaIni, $fechaFin, $tipo, $aduana) {
        try {
            $conn = $this->_db->getConfig();
            if ($conn["port"] == 1433) {
                //FECHA 111 = 2014/02/06
                //FECHA 103 = 31/01/2014                
                $columns = array(
                    new Zend_Db_Expr("CONVERT(VARCHAR(23), p.FEC_PAG, 103) AS FEC_PAG"),
                    new Zend_Db_Expr("CONVERT(VARCHAR, f.FECFAC, 103) AS FechaFactura"),
                    new Zend_Db_Expr("CASE WHEN (cas.TIPCAS IS NOT NULL) THEN cas.TIPCAS ELSE 'TG' END AS TipoTasa"),
                );
            } else {
                $columns = array(
                    new Zend_Db_Expr("DATE_FORMAT(p.FEC_PAG, '%d/%m/%Y') AS FEC_PAG"),
                    new Zend_Db_Expr("DATE_FORMAT(f.FECFAC, '%d/%m/%Y') AS FechaFactura"),
                    new Zend_Db_Expr("CASE WHEN (cas.TIPCAS IS NOT NULL) THEN cas.TIPCAS ELSE 'TG' END AS TipoTasa"),
                );
            }
            if ($aduana == 640) {
                $parte = "fra.PARTE AS NumeroDeParte";
            } elseif ($aduana == 646) {
                $parte = "ofr.OBS AS NumeroDeParte";
            }
            $normal = array($columns[0], $columns[1], $columns[2], $parte, "p.ADUANAD", "p.SECCDES", "p.PATENTE", "p.NUM_PED", "p.NUM_REF", "p.IMP_EXP", "p.TIP_CAM", "p.IVA2_TOT", "p.CVEPEDIM", "p.REGIMEN", "p.FLETES", "p.SEGUROS", "p.EMBALAJ", "p.OTROINC", "p.DTA_TOT", "(fra.VALCOM * p.TIP_CAM) AS ValorComercial", "p.VALADUANA AS ValorAduana", "o.OBS AS Observaciones", "p.CONSOLR AS Consolidado", "p.PRE_TOT AS Prevalidacion", "pro.NOMPRO AS NomProveedor", "f.NUMFAC AS Factura", "fra.VALCEQ AS FMoneda", "fra.CODIGO AS FraccionImportacion", "fra.TASAADV AS Tasa", "un.ABREVIA AS Unidad", new Zend_Db_Expr("(fra.VALCOM / fra.CANTFAC) AS Precio"), "fra.CANTFAC AS Cantidad", "fra.PAIORI AS Origen", "fra.PAICOM AS Vendedor", "fra.FPAGADV1 AS FormaPago", "f.INCOTER AS Incoterm", "f.ACUSECOVE AS Cove", "b.FIRMA AS FIRMAB");

            $select = $this->_db->select();
            $select->from(array("p" => "sm3ped"), $normal)
                    ->joinLeft(array("c" => "cm3fra"), "p.NUM_REF = c.NUM_REF", array("fra.VALCOM"))
                    ->joinLeft(array("fra" => "sm3fra"), "p.NUM_REF = fra.NUM_REF", array("fra.VALCOM"))
                    ->joinLeft(array("o" => "sm3obs"), "p.NUM_REF = o.NUM_REF", array("o.OBS"))
                    ->joinLeft(array("pro" => "cmpro"), "fra.CVE_PRO = pro.CVE_PRO", array("pro.NOMPRO"))
                    ->joinLeft(array("f" => "sm3fact"), "p.NUM_REF = f.NUM_REF", array("f.NUMFAC", "f.ACUSECOVE"))
                    ->joinLeft(array("cas" => "sm3casos"), "cas.ORDEN = fra.ORDEN AND cas.NUM_REF = fra.NUM_REF AND cas.TIPCAS <> 'EN'", array("cas.TIPCAS"))
                    ->joinLeft(array("ofr" => "sm3obsfr"), "p.NUM_REF = ofr.NUM_REF AND fra.ORDEN = ofr.ORDEN", array("ofr.OBS"))
                    ->joinLeft(array("b" => "saiban"), "p.NUM_PED = b.DOCTO", array("b.FIRMA AS FIRMAB"))
                    ->joinLeft(array("un" => "cmum"), "fra.UMC = un.CLAVE", array("un.ABREVIA"))
                    ->where("p.RFCCTE = ?", $rfc)
                    ->where("p.FEC_PAG BETWEEN '" . date("Y/m/d", strtotime($fechaIni)) . "' AND '" . date("Y/m/d", strtotime($fechaFin)) . "'")
                    ->where("(p.FIRMA IS NOT NULL AND p.FIRMA <> '' AND b.FIRMA IS NOT NULL)")
                    ->where("p.IMP_EXP = ?", ($tipo == "I") ? 1 : 2)
                    ->order("p.FEC_PAG ASC");

            $stmt = $this->_db->fetchAll($select);
            if ($stmt) {
                foreach ($stmt as $item) {
                    $data[] = array(
                        "Referencia" => $item["NUM_REF"],
                        "Operacion" => substr($item["FEC_PAG"], 8, 2) . "-" . $item["ADUANAD"] . $item["SECCDES"] . "-" . $item["PATENTE"] . "-" . $item["NUM_PED"],
                        "FechaPago" => $item["FEC_PAG"],
                        "Aduana" => $item["ADUANAD"] . $item["SECCDES"],
                        "Patente" => $item["PATENTE"],
                        "Pedimento" => $item["NUM_PED"],
                        "TipoCambio" => $item["TIP_CAM"],
                        "IVA" => $item["IVA2_TOT"],
                        "Clave" => $item["CVEPEDIM"],
                        "Regimen" => $item["REGIMEN"],
                        "Fletes" => $item["FLETES"],
                        "Seguros" => $item["SEGUROS"],
                        "Embalajes" => $item["EMBALAJ"],
                        "Otros" => $item["OTROINC"],
                        "DTA" => $item["DTA_TOT"],
                        "ValorComercial" => $item["ValorComercial"],
                        "ValorAduana" => $item["ValorAduana"],
                        "Observaciones" => $item["Observaciones"],
                        "Virtual" => "",
                        "NotaInterna" => "",
                        "Prevalidacion" => $item["Prevalidacion"],
                        "NomProveedor" => $item["NomProveedor"],
                        "Factura" => $item["Factura"],
                        "FechaFactura" => $item["FechaFactura"],
                        "FMoneda" => $item["FMoneda"],
                        "NumeroDeParte" => $item["NumeroDeParte"],
                        "FraccionImportacion" => $item["FraccionImportacion"],
                        "Tasa" => $item["Tasa"],
                        "TipoTasa" => $item["TipoTasa"],
                        "Unidad" => $item["Unidad"],
                        "Precio" => $item["Precio"],
                        "Cantidad" => $item["Cantidad"],
                        "Origen" => $item["Origen"],
                        "Vendedor" => $item["Vendedor"],
                        "FormaPago" => $item["FormaPago"],
                        "Incoterm" => $item["Incoterm"],
                        "PagaTLC" => "",
                        "PagaTLCUEM" => "",
                        "PagaTLCAELC" => "",
                        "JustTLC" => "",
                        "JustTLCUEM" => "",
                        "JustTLCAELC" => "",
                        "EB" => "",
                        "MontoEB" => "",
                        "EnConsignacion" => "",
                        "NotaInterna2" => "",
                        "Revision" => "",
                        "Cove" => $item["Cove"],
                        "TipoOpe" => ($item["IMP_EXP"] == "1") ? "I" : "E",
                    );
                }
                return $data;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    protected function getPrev($referencia) {
        try {
            $select = $this->_db->select()
                    ->from(array("p" => "sm3prev"), array("p.PATORI", "p.PEDORI", "p.ADUORI"))
                    ->where("p.NUM_REF = ?", $referencia);
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                return $stmt;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    protected function getObs($referencia, $orden) {
        try {
            $select = $this->_db->select()
                    ->from(array("o" => "sm3obsfr"), array("o.OBS"))
                    ->where("o.NUM_REF = ?", $referencia)
                    ->where("o.ORDEN = ?", $orden);
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                return $stmt;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function reportePrasad($rfc, $year, $month = null) {
        try {
            $select = $this->_db->select()
                    ->from(array("p" => "sm3ped"), array("p.NUM_PED", "p.CVEPEDIM", "p.ADUANAD", "p.SECCDES", "p.NUM_REF"))
                    ->joinLeft(array("f" => "sm3fact"), "f.NUM_REF = p.NUM_REF", array("f.NUMFAC", "f.ACUSECOVE", "f.VALDLS"))
                    ->joinLeft(array("fr" => "cm3fra"), "fr.NUM_REF = f.NUM_REF AND f.NUMFAC = fr.FACTFRA", array("fr.PARTE", "fr.PAIORI", "fr.ORDEN", "fr.CODIGO", "fr.UMC", "fr.CANTFAC"))
                    ->joinLeft(array("b" => "saiban"), "p.NUM_PED = b.DOCTO")
                    ->where("p.RFCCTE = ?", $rfc)
                    ->where("YEAR(p.FEC_PAG) = ?", $year)
                    ->where("p.NUM_PED <> 0 AND p.FIRMA <> '' AND p.FIRMA IS NOT NULL")
                    ->where("b.FIRMA IS NOT NULL");
            if (isset($month)) {
                $select->where("MONTH(p.FEC_PAG) = ?", $month);
            }
            $stmt = $this->_db->fetchAll($select);
            if ($stmt) {
                $data = array();
                foreach ($stmt as $item) {
                    $prev = $this->getPrev($item["NUM_REF"]);
                    if (isset($item["ORDEN"])) {
                        $obs = $this->getObs($item["NUM_REF"], $item["ORDEN"]);
                    }
                    if (!isset($item["PARTE"]) || $item["PARTE"] == null || $item["PARTE"] === "") {
                        $item["PARTE"] = trim(preg_replace("/\s\s+/", " ", $obs["OBS"]));
                    }
                    $data[] = array(
                        "Referencia" => substr($year, 2) . "-" . $item["ADUANAD"] . $item["SECCDES"] . "-" . $item["NUM_PED"],
                        "CvePedimento" => $item["CVEPEDIM"],
                        "ReferenciaAA" => "",
                        "ClaveProyecto" => "",
                        "Factura" => $item["ACUSECOVE"],
                        "OrdenCompra" => "",
                        "ClaveCliente" => "",
                        "NumFactura" => $item["NUMFAC"],
                        "NumeroParte" => $item["PARTE"],
                        "PiasOrigen" => $item["PAIORI"],
                        "Secuencial" => $item["ORDEN"],
                        "Fraccion" => $item["CODIGO"],
                        "UMC" => $item["UMC"],
                        "CantUMC" => $item["CANTFAC"],
                        "PrecioUnitario" => $item["VALDLS"] / $item["CANTFAC"],
                        "PatenteOrig" => isset($prev["PATORI"]) ? $prev["PATORI"] : null,
                        "PedimentoOrig" => isset($prev["PEDORI"]) ? $prev["PEDORI"] : null,
                        "AduanaOrig" => isset($prev["ADUORI"]) ? $prev["ADUORI"] : null,
                        "Observaciones" => $obs["OBS"],
                    );
                }
                return $data;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function anexo24Extendido($rfc, $year, $month) {
        try {
            $sql = "SELECT
                  RIGHT(YEAR(p.FEC_PAG),2) + '-' + p.ADUANAD + '-' + CAST(p.PATENTE AS VARCHAR(4)) + '-' + CAST(p.NUM_PED AS VARCHAR(7)) AS Referencia
                  ,CASE P.IMP_EXP
                    WHEN 1 THEN 'IMP'
                    ELSE 'EXP'
                  END AS Operacion
                  ,P.PATENTE AS Patente
                  ,P.NUM_REF AS Trafico
                  ,p.NUM_PED AS Pedimento
                  ,P.ADUANAD AS Aduana
                  ,P.RFCCTE AS RFCCliente
                  ,C.NOMCLI AS NomCliente
                  ,P.SECCDES AS SeccionDescargo
                  ,P.MEDTRAS AS TransporteEntrada
                  ,P.MEDTRAA AS TransporteArribo
                  ,P.MEDTRAE AS TransporteSalida
                  ,REPLACE(CONVERT(VARCHAR(10), P.FEC_ENT, 111), '/', '-') AS FechaEntrada
                  ,REPLACE(CONVERT(VARCHAR(10), P.FEC_PAG, 111), '/', '-') AS FechaPago
                  ,P.FIRMA AS FirmaValidacion
                  ,B.FIRMA AS FirmaBanco
                  ,P.TIP_CAM AS TipoCambio
                  ,P.CVEPEDIM AS CvePed
                  ,P.REGIMEN AS Regimen
                  ,P.CONSOLR AS Consolidado
                  ,P.ADUANAE AS AduanaEntrada
                  ,P.SECCENT AS SeccionEntrada
                  ,P.RECTIF AS Rectificacion
                  ,P.VALMEDLLS AS ValorDolares
                  ,P.VALADUANA AS ValorAduana
                  ,P.FLETES AS Fletes
                  ,P.SEGUROS AS Seguros
                  ,P.EMBALAJ AS Embalajes
                  ,P.OTROINC AS OtrosIncrementales
                  ,P.DTA_TOT AS DTA
                  ,P.IVA1_TOT AS IVA
                  ,P.IGIE_TOT AS IGI
                  ,P.PRE_TOT AS PREV
                  ,P.CNT_TOT AS CNT
                  ,((CASE P.DTA_FP WHEN 0 THEN P.DTA_TOT ELSE 0 end) +  (CASE P.DTA_FPADI WHEN 0 THEN  P.DTA_TLADI ELSE 0 end) + (CASE P.CC1_FP WHEN 0 THEN P.CC1_TOT ELSE 0 end) +  (CASE P.CC2_FP WHEN 0 THEN P.CC2_TOT ELSE 0 end) +  (CASE P.IVA1_FP WHEN 0 THEN P.IVA1_TOT ELSE 0 end) +  (CASE P.IVA2_FP WHEN 0 THEN P.IVA2_TOT ELSE 0 end) +  (CASE P.ISAN_FP WHEN 0 THEN P.ISAN_TOT ELSE 0 end) +  (CASE P.IEPS_FP WHEN 0 THEN P.IEPS_TOT ELSE 0 end) +  (CASE P.REC_FP WHEN 0 THEN P.REC_TOT ELSE 0 end) +  (CASE P.OTR_FP WHEN 0 THEN P.OTR_TOT ELSE 0 end) +  (CASE GAR_FP WHEN 0 THEN P.GAR_TOT ELSE 0 end) +  (CASE P.MUL_FP WHEN 0 THEN P.MUL_TOT ELSE 0 end) +  (CASE P.MUL2_FP WHEN 0 THEN P.MUL2_TOT ELSE 0 end) +  (CASE P.DTI_FP WHEN 0 THEN P.DTI_TOT ELSE 0 end) +  (CASE P.IGIR_FP WHEN 0 THEN P.IGIR_TOT ELSE 0 end) +  (CASE P.PRE_FP WHEN 0 THEN P.PRE_TOT ELSE 0 end) +  (CASE P.BSS_FP WHEN 0 THEN P.BSS_TOT ELSE 0 end) +  (CASE P.EUR_FP WHEN 0 THEN P.EUR_TOT ELSE 0 end) +  (CASE P.ECI_FP WHEN 0 THEN P.ECI_TOT ELSE 0 end) +  (CASE P.ITV_FP WHEN 0 THEN P.ITV_TOT ELSE 0 end) +  (CASE P.IGIR_FP2 WHEN 0 THEN P.IGIR_TOT2 ELSE 0 end) + (CASE P.REC2_FP WHEN 0 THEN P.REC2_TOT ELSE 0 end) + (CASE P.CNT_FP WHEN 0 THEN P.CNT_TOT ELSE 0 end)) AS TotalEfectivo
                  ,P.PESBRU AS PesoBruto
                  ,P.BULTOS AS Bultos
                  ,F.NUMFAC AS NumFactura
                  ,F.ORDENFAC AS OrdenFactura
                  ,REPLACE(CONVERT(VARCHAR(10), F.FECFAC, 111), '/', '-') AS FechaFactura
                  ,F.INCOTER AS Incoterm
                  ,F.VALDLS AS ValorFacturaUsd
                  ,F.VALEXT AS ValorFacturaMonExt
                  ,F.CVEPROV AS CveProveedor
                  ,CASE
                    WHEN P.IMP_EXP = 1 THEN (SELECT TOP 1 PR.NOMPRO FROM CMPRO PR WHERE PR.CVE_PRO = F.CVEPROV)
                    ELSE (SELECT TOP 1 PR.NOMPRO FROM CMDEST PR WHERE PR.CVE_PRO = F.CVEPROV )
                  END AS NomProveedor
                  ,CASE
                    WHEN P.IMP_EXP = 1 THEN (SELECT TOP 1 PR.NUM_TAX FROM CMPRO PR WHERE PR.CVE_PRO = F.CVEPROV )
                    ELSE (SELECT TOP 1 PR.NUM_TAX FROM CMDEST PR WHERE PR.CVE_PRO = F.CVEPROV )
                   END AS TaxId
                  ,F.PAISFAC AS PaisFactura
                  ,F.MONFAC AS Divisa
                  ,F.FACEQ AS FactorMonExt
                  ,FR.PARTE AS NumParte
                  ,FR.DESC1 AS Descripcion
                  ,FR.CODIGO AS Fraccion
                  ,FR.ORDENAGRU AS OrdenFraccion
                  ,FR.VALCOM AS ValorMonExt
                  ,FR.VALMN AS ValorAduanaMXN
                  ,FR.UMC AS UMC
                  ,(SELECT TOP 1 CM.ABREVIA FROM CMUM CM WHERE CM.CLAVE = FR.UMC) AS abrevUMC
                  ,FR.UMT AS UMT
                  ,(SELECT TOP 1 ABREVIA FROM CMUM AS UN WHERE UN.CLAVE = FR.UMT) AS abrevUMT
                  ,FR.CANTFAC AS CantUMC
                  ,FR.CANTTAR AS CantUMT
                  ,FR.PAIORI AS PaisOrigen
                  ,FR.TASAADV AS TasaAdvalorem
                  ,FR.FPAGADV1 as formaPagoAdvalorem
                  ,FR.PAICOM AS PaisVendedor
                  ,FR.CERTLC AS TLC
                  ,FR.PROSEC AS PROSEC
                  ,FR.IMPOIEPS AS IEPS
                  ,FR.IMPOISAN AS ISAN
                  ,(SELECT TOP 1 G.NUMGUIA FROM SM3GUIA AS G WHERE G.NUM_REF = P.NUM_REF) AS Guias
                  ,CASE
                      WHEN CAST((SELECT TOP 1 PREV.PATORI FROM SM3PREV AS PREV WHERE PREV.NUM_REF = P.NUM_REF) AS Int) = 0 THEN ''
                      ELSE (SELECT TOP 1 PREV.PATORI FROM SM3PREV AS PREV WHERE PREV.NUM_REF = P.NUM_REF)
                  END AS PatenteOrig
                  ,CASE
                      WHEN CAST((SELECT TOP 1 PREV.PEDORI FROM SM3PREV AS PREV WHERE PREV.NUM_REF = P.NUM_REF) AS Int) = 0 THEN ''
                      ELSE (SELECT TOP 1 PREV.PEDORI FROM SM3PREV AS PREV WHERE PREV.NUM_REF = P.NUM_REF)
                  END AS PedimentoOrig
                  ,CASE
                      WHEN CAST((SELECT TOP 1 PREV.ADUORI FROM SM3PREV AS PREV WHERE PREV.NUM_REF = P.NUM_REF) AS Int) = 0 THEN ''
                      ELSE (SELECT TOP 1 PREV.ADUORI FROM SM3PREV AS PREV WHERE PREV.NUM_REF = P.NUM_REF)
                  END AS AduanaOrig
                  ,FR.CAN_OMA AS CantOMA
                  ,FR.UMC_OMA AS OMA
                  ,CASE F.ACUSECOVE
                    WHEN '' THEN 'N'
                    ELSE F.ACUSECOVE
                  END AS Cove
                FROM SM3PED AS P
                LEFT JOIN CMCLI C ON P.CVE_IMP = C.CVE_IMP
                LEFT JOIN SAIBAN B ON P.NUM_PED = B.DOCTO
                LEFT JOIN SM3FACT AS F ON F.NUM_REF = P.NUM_REF
                LEFT JOIN CM3FRA AS FR ON FR.NUM_REF = F.NUM_REF AND F.NUMFAC = FR.FACTFRA
                WHERE P.RFCCTE = '{$rfc}' AND YEAR(P.FEC_PAG) = {$year} AND MONTH(P.FEC_PAG) = {$month}
                AND P.NUM_PED <> 0 AND P.FIRMA <> '' AND P.FIRMA IS NOT NULL AND B.FIRMA IS NOT NULL;";
            $stmt = $this->_db->fetchAll($sql);
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function encabezado($rfc, $year, $month) {
        try {
            $fileds = [
                new Zend_Db_Expr("RIGHT(YEAR(p.FEC_PAG),2) + '-' + p.ADUANAD + '-' + CAST(p.PATENTE AS VARCHAR(4)) + '-' + CAST(p.NUM_PED AS VARCHAR(7)) AS Referencia"),
                new Zend_Db_Expr("CASE P.IMP_EXP WHEN 1 THEN 'IMP' ELSE 'EXP' END AS Operacion"),
                "p.PATENTE AS Patente",
                "p.ADUANAD AS Aduana",
                "p.NUM_PED AS Pedimento",
                "p.NUM_REF AS Trafico",
                "p.MEDTRAS AS TransporteEntrada",
                "p.MEDTRAA AS TransporteArribo",
                "p.MEDTRAE AS TransporteSalida",
                new Zend_Db_Expr("REPLACE(CONVERT(VARCHAR(10), p.FEC_ENT, 111), '/', '-') AS FechaEntrada"),
                new Zend_Db_Expr("REPLACE(CONVERT(VARCHAR(10), p.FEC_PAG, 111), '/', '-') AS FechaPago"),
                "p.FIRMA AS FirmaValidacion",
                "b.FIRMA AS FirmaBanco",
                "p.TIP_CAM AS TipoCambio",
                "p.CVEPEDIM AS CvePed",
                "p.REGIMEN AS Regimen",
                "p.ADUANAE AS AduanaEntrada",
                "p.VALMEDLLS AS ValorDolares",
                "p.VALADUANA AS ValorAduana",
                "p.FLETES AS Fletes",
                "p.SEGUROS AS Seguros",
                "p.EMBALAJ AS Embalajes",
                "p.OTROINC AS OtrosIncrementales",
                "p.DTA_TOT AS DTA",
                "p.IVA1_TOT AS IVA",
                "p.IGIE_TOT AS IGI",
                "p.PRE_TOT AS PREV",
                "p.CNT_TOT AS CNT",
                new Zend_Db_Expr("((CASE p.DTA_FP WHEN 0 THEN p.DTA_TOT ELSE 0 end) +  (CASE p.DTA_FPADI WHEN 0 THEN  p.DTA_TLADI ELSE 0 end) + (CASE p.CC1_FP WHEN 0 THEN p.CC1_TOT ELSE 0 end) +  (CASE p.CC2_FP WHEN 0 THEN p.CC2_TOT ELSE 0 end) +  (CASE p.IVA1_FP WHEN 0 THEN p.IVA1_TOT ELSE 0 end) +  (CASE p.IVA2_FP WHEN 0 THEN p.IVA2_TOT ELSE 0 end) +  (CASE p.ISAN_FP WHEN 0 THEN p.ISAN_TOT ELSE 0 end) +  (CASE p.IEPS_FP WHEN 0 THEN p.IEPS_TOT ELSE 0 end) +  (CASE p.REC_FP WHEN 0 THEN p.REC_TOT ELSE 0 end) +  (CASE p.OTR_FP WHEN 0 THEN p.OTR_TOT ELSE 0 end) +  (CASE GAR_FP WHEN 0 THEN p.GAR_TOT ELSE 0 end) +  (CASE p.MUL_FP WHEN 0 THEN p.MUL_TOT ELSE 0 end) +  (CASE p.MUL2_FP WHEN 0 THEN p.MUL2_TOT ELSE 0 end) +  (CASE p.DTI_FP WHEN 0 THEN p.DTI_TOT ELSE 0 end) +  (CASE p.IGIR_FP WHEN 0 THEN p.IGIR_TOT ELSE 0 end) +  (CASE p.PRE_FP WHEN 0 THEN p.PRE_TOT ELSE 0 end) +  (CASE p.BSS_FP WHEN 0 THEN p.BSS_TOT ELSE 0 end) +  (CASE p.EUR_FP WHEN 0 THEN p.EUR_TOT ELSE 0 end) +  (CASE p.ECI_FP WHEN 0 THEN p.ECI_TOT ELSE 0 end) +  (CASE p.ITV_FP WHEN 0 THEN p.ITV_TOT ELSE 0 end) +  (CASE p.IGIR_FP2 WHEN 0 THEN p.IGIR_TOT2 ELSE 0 end) + (CASE p.REC2_FP WHEN 0 THEN p.REC2_TOT ELSE 0 end) + (CASE p.CNT_FP WHEN 0 THEN p.CNT_TOT ELSE 0 end)) AS TotalEfectivo"),
                "p.PESBRU AS PesoBruto",
                "p.BULTOS AS Bultos",
            ];
            $sql = $this->_db->select()
                    ->from(["p" => "sm3ped"], $fileds)
                    ->joinLeft(["b" => "saiban"], "p.NUM_PED = b.DOCTO", [""])
                    ->where("p.NUM_PED <> 0 AND p.FIRMA <> '' AND p.FIRMA IS NOT NULL AND b.FIRMA IS NOT NULL")
                    ->where("p.RFCCTE = ?", $rfc)
                    ->where("YEAR(p.FEC_PAG) = ?", $year)
                    ->where("MONTH(p.FEC_PAG) = ?", $month);
            $stmt = $this->_db->fetchAll($sql);
            if (count($stmt)) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function anexo24ExtendidoHtml($rfc, $year, $month) {
        try {
            $fileds = [
                new Zend_Db_Expr("RIGHT(YEAR(p.FEC_PAG),2) + '-' + p.ADUANAD + '-' + CAST(p.PATENTE AS VARCHAR(4)) + '-' + CAST(p.NUM_PED AS VARCHAR(7)) AS referencia"),
                new Zend_Db_Expr("CASE P.IMP_EXP WHEN 1 THEN 'IMP' ELSE 'EXP' END AS operacion"),
                "p.PATENTE AS patente",
                "p.ADUANAD AS aduana",
                "p.NUM_PED AS pedimento",
                "p.NUM_REF AS trafico",
                "p.MEDTRAS AS TransporteEntrada",
                "p.MEDTRAA AS TransporteArribo",
                "p.MEDTRAE AS TransporteSalida",
                new Zend_Db_Expr("REPLACE(CONVERT(VARCHAR(10), p.FEC_ENT, 111), '/', '-') AS fechaEntrada"),
                new Zend_Db_Expr("REPLACE(CONVERT(VARCHAR(10), p.FEC_PAG, 111), '/', '-') AS fechaPago"),
                "p.FIRMA AS firmaValidacion",
                "b.FIRMA AS firmaBanco",
                "p.TIP_CAM AS tipoCambio",
                "p.CVEPEDIM AS cvePedimento",
                "p.REGIMEN AS regimen",
                "p.ADUANAE AS aduanaEntrada",
                "p.VALMEDLLS AS valorDolares",
                "p.VALADUANA AS valorAduana",
                "p.FLETES AS fletes",
                "p.SEGUROS AS seguros",
                "p.EMBALAJ AS embalajes",
                "p.OTROINC AS otrosIncrementales",
                "p.DTA_TOT AS dta",
                "p.IVA1_TOT AS iva",
                "p.IGIE_TOT AS igi",
                "p.PRE_TOT AS prev",
                "p.CNT_TOT AS cnt",
                new Zend_Db_Expr("((CASE p.DTA_FP WHEN 0 THEN p.DTA_TOT ELSE 0 end) +  (CASE p.DTA_FPADI WHEN 0 THEN  p.DTA_TLADI ELSE 0 end) + (CASE p.CC1_FP WHEN 0 THEN p.CC1_TOT ELSE 0 end) +  (CASE p.CC2_FP WHEN 0 THEN p.CC2_TOT ELSE 0 end) +  (CASE p.IVA1_FP WHEN 0 THEN p.IVA1_TOT ELSE 0 end) +  (CASE p.IVA2_FP WHEN 0 THEN p.IVA2_TOT ELSE 0 end) +  (CASE p.ISAN_FP WHEN 0 THEN p.ISAN_TOT ELSE 0 end) +  (CASE p.IEPS_FP WHEN 0 THEN p.IEPS_TOT ELSE 0 end) +  (CASE p.REC_FP WHEN 0 THEN p.REC_TOT ELSE 0 end) +  (CASE p.OTR_FP WHEN 0 THEN p.OTR_TOT ELSE 0 end) +  (CASE GAR_FP WHEN 0 THEN p.GAR_TOT ELSE 0 end) +  (CASE p.MUL_FP WHEN 0 THEN p.MUL_TOT ELSE 0 end) +  (CASE p.MUL2_FP WHEN 0 THEN p.MUL2_TOT ELSE 0 end) +  (CASE p.DTI_FP WHEN 0 THEN p.DTI_TOT ELSE 0 end) +  (CASE p.IGIR_FP WHEN 0 THEN p.IGIR_TOT ELSE 0 end) +  (CASE p.PRE_FP WHEN 0 THEN p.PRE_TOT ELSE 0 end) +  (CASE p.BSS_FP WHEN 0 THEN p.BSS_TOT ELSE 0 end) +  (CASE p.EUR_FP WHEN 0 THEN p.EUR_TOT ELSE 0 end) +  (CASE p.ECI_FP WHEN 0 THEN p.ECI_TOT ELSE 0 end) +  (CASE p.ITV_FP WHEN 0 THEN p.ITV_TOT ELSE 0 end) +  (CASE p.IGIR_FP2 WHEN 0 THEN p.IGIR_TOT2 ELSE 0 end) + (CASE p.REC2_FP WHEN 0 THEN p.REC2_TOT ELSE 0 end) + (CASE p.CNT_FP WHEN 0 THEN p.CNT_TOT ELSE 0 end)) AS totalEfectivo"),
                "p.PESBRU AS pesoBruto",
                "p.BULTOS AS bultos",
                new Zend_Db_Expr("(SELECT TOP 1 g.NUMGUIA FROM SM3GUIA AS g WHERE g.NUM_REF = P.NUM_REF) AS guias"),
                "f.NUMFAC AS numFactura",
                "f.ACUSECOVE AS cove",
                new Zend_Db_Expr("REPLACE(CONVERT(VARCHAR(10), f.FECFAC, 111), '/', '-') AS fechaFactura"),
                "f.INCOTER AS incoterm",
                "f.VALDLS AS valorFacturaUsd",
                "f.VALEXT AS valorFacturaMonExt",
                new Zend_Db_Expr("CASE WHEN p.IMP_EXP = 1 THEN (SELECT TOP 1 PR.NOMPRO FROM CMPRO PR WHERE PR.CVE_PRO = f.CVEPROV) ELSE (SELECT TOP 1 PR.NOMPRO FROM CMDEST PR WHERE PR.CVE_PRO = f.CVEPROV ) END AS nomProveedor"),
                new Zend_Db_Expr("CASE WHEN p.IMP_EXP = 1 THEN (SELECT TOP 1 PR.NUM_TAX FROM CMPRO PR WHERE PR.CVE_PRO = f.CVEPROV ) ELSE (SELECT TOP 1 PR.NUM_TAX FROM CMDEST PR WHERE PR.CVE_PRO = f.CVEPROV ) END AS taxId"),
                "f.PAISFAC AS paisFactura",
                "f.MONFAC AS divisa",
                "f.FACEQ AS factorMonExt",
                "fr.PARTE AS numParte",
                "fr.DESC1 AS descripcion",
                "fr.CODIGO AS fraccion",
                "fr.ORDENAGRU AS ordenFraccion",
                "(fr.VALCOM/fr.CANTFAC) AS precioUnitario",
                "fr.VALCOM AS valorMonExt",
                "fr.VALMN AS valorAduanaMXN",
                "fr.UMC AS umc",
                "fr.CANTFAC AS cantUmc",
                "fr.UMT AS umt",
                "fr.CANTTAR AS cantUmt",
                "fr.PAIORI AS paisOrigen",
                "fr.CERTLC AS tlc",
                "CAST('' AS char) AS tlcan",
                "CAST('' AS char) AS tlcue",
                "fr.PROSEC AS prosec",
                "fr.TASAADV AS tasaAdvalorem",
                "fr.PAICOM AS paisVendedor",
                new Zend_Db_Expr("CASE WHEN CAST((SELECT TOP 1 PREV.PATORI FROM SM3PREV AS PREV WHERE PREV.NUM_REF = p.NUM_REF) AS Int) = 0 THEN '' ELSE (SELECT TOP 1 PREV.PATORI FROM SM3PREV AS PREV WHERE PREV.NUM_REF = p.NUM_REF) END AS patenteOriginal"),
                new Zend_Db_Expr("CASE WHEN CAST((SELECT TOP 1 PREV.ADUORI FROM SM3PREV AS PREV WHERE PREV.NUM_REF = p.NUM_REF) AS Int) = 0 THEN '' ELSE (SELECT TOP 1 PREV.ADUORI FROM SM3PREV AS PREV WHERE PREV.NUM_REF = p.NUM_REF)END AS aduanaOriginal"),
                new Zend_Db_Expr("CASE WHEN CAST((SELECT TOP 1 PREV.PEDORI FROM SM3PREV AS PREV WHERE PREV.NUM_REF = p.NUM_REF) AS Int) = 0 THEN '' ELSE (SELECT TOP 1 PREV.PEDORI FROM SM3PREV AS PREV WHERE PREV.NUM_REF = p.NUM_REF) END AS pedimentoOriginal"),
            ];
            $sql = $this->_db->select()
                    ->from(["p" => "sm3ped"], $fileds)
                    ->joinLeft(["f" => "sm3fact"], "f.NUM_REF = p.NUM_REF", [""])
                    ->joinLeft(["fr" => "cm3fra"], "fr.NUM_REF = f.NUM_REF AND f.NUMFAC = fr.FACTFRA", [""])
                    ->joinLeft(["b" => "saiban"], "p.NUM_PED = b.DOCTO", [""])
                    ->where("p.NUM_PED <> 0 AND p.FIRMA <> '' AND p.FIRMA IS NOT NULL AND b.FIRMA IS NOT NULL")
                    ->where("p.RFCCTE = ?", $rfc)
                    ->where("YEAR(p.FEC_PAG) = ?", $year)
                    ->where("MONTH(p.FEC_PAG) = ?", $month);
            $stmt = $this->_db->fetchAll($sql);
            if (count($stmt)) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerFacturasYCoves($pedimento) {
        try {
            $select = $this->_db->select()
                    ->from(array("p" => "sm3ped"), array("p.NUM_REF AS referencia"))
                    ->joinLeft(array("f" => "sm3fact"), "f.NUM_REF = p.NUM_REF", array("F.NUMFAC AS numFactura", "F.ACUSECOVE AS cove"))
                    ->where("p.NUM_PED = ?", $pedimento);
            $stmt = $this->_db->fetchAll($select);
            if ($stmt) {
                $data = array();
                foreach ($stmt as $item) {
                    $data[] = array(
                        "referencia" => $item["referencia"],
                        "factura" => $item["numFactura"],
                        "cove" => $item["cove"],
                    );
                }
                return $data;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerDetalleFacturas($pedimento, $consolidado = null) {
        try {
            if (!$consolidado) {
                $select = $this->_db->select()
                        ->from(array("p" => "sm3ped"), array(
                            "p.NUM_REF as referencia",
                            "p.TIP_CAM as tip_cam")
                        )
                        ->joinLeft(array("f" => "sm3fact"), "f.NUM_REF = p.NUM_REF", array(
                            "f.NUMFAC as factura",
                            "f.FACEQ",
                            "f.INCOTER as incoterm",
                            "f.PAISFAC as paisdfa")
                        )
                        ->joinLeft(array("fr" => "sm3fra"), "fr.NUM_REF = f.NUM_REF", array(
                            "fr.ORDEN as orden",
                            "fr.DESC1 as descdfa",
                            "fr.DESC_COVE as descori",
                            "fr.CODIGO as fracdfa",
                            "fr.CANTTAR as cantumt",
                            "fr.CANTFAC as cantdfa",
                            "fr.UMC as umcdfa",
                            "fr.UMT as umtdfa",
                            "fr.VALORAC as mvaldfa",
                            "fr.CERTLC as tlcdfa",
                            "fr.VALCOM as impomex",
                            "fr.VALDLS as impodls",
                            "fr.VALCEQ as valceq",
                            "fr.PARTE as nparte",
                            "fr.VALAGRE as agredfa",
                            "fr.IMPOADV as igiedfa")
                        )
                        ->joinLeft(array("fra" => "cm3fra"), "fra.NUM_REF = f.NUM_REF AND f.NUMFAC = fra.FACTFRA", array("fra.AGRUPA as agrupa"))
                        ->where("p.NUM_PED = ?", $pedimento)
                        ->order("f.ORDENFAC ASC")
                        ->group(new Zend_Db_Expr("p.NUM_REF, p.TIP_CAM, f.ORDENFAC, f.NUMFAC, f.FACEQ, f.INCOTER, f.PAISFAC, fr.ORDEN, fr.DESC1, fr.DESC_COVE, fr.CODIGO, fr.CANTTAR, fr.CANTFAC,fr.UMC,fr.UMT,fr.VALORAC,fr.CERTLC, fr.VALCOM, fr.VALDLS, fr.VALCEQ, fr.PARTE,fr.VALAGRE, fr.IMPOADV, fra.AGRUPA"));
                $stmt = $this->_db->fetchAll($select);
                if ($stmt) {
                    $data = array();
                    foreach ($stmt as $item) {
                        $tl = $this->getCaso("CM3CASOS", $item["referencia"], $item["factura"], "TL");
                        if ($tl == "USA") {
                            $tlcue = "N";
                        } elseif ($tl == "CAN") {
                            $tlcue = "N";
                        } else {
                            $tlcue = "S";
                        }
                        $data[] = array(
                            "factura" => $item["factura"],
                            "orden" => $item["orden"],
                            "paisdfa" => $item["paisdfa"],
                            "cantdfa" => $item["cantdfa"],
                            "descdfa" => $item["descdfa"],
                            "descori" => $item["descori"],
                            "umcdfa" => $item["umcdfa"],
                            "umtdfa" => $item["umtdfa"],
                            "fracdfa" => $item["fracdfa"],
                            "cantumt" => $item["cantumt"],
                            "tlcdfa" => $item["tlcdfa"],
                            "tlcuedfa" => $tlcue,
                            "agrupa" => $item["agrupa"],
                            "mvaldfa" => $item["mvaldfa"],
                            "tip_cam" => $item["tip_cam"],
                            "punidls" => $item["cantdfa"] / $item["impodls"],
                            "punimex" => $item["cantdfa"] / $item["impomex"],
                            "impomex" => $item["impomex"],
                            "impodls" => $item["impodls"],
                            "valceq" => $item["valceq"],
                            "agredfa" => $item["agredfa"],
                            "igiedfa" => $item["igiedfa"],
                            "incoterm" => $item["incoterm"],
                            "nparte" => (isset($item["nparte"]) && trim($item["nparte"]) != "") ? $item["nparte"] : $this->getObservaciones("SM3OBSFR", $item["referencia"], $item["orden"]),
                        );
                    }
                    return $data;
                }
            } elseif ($consolidado) {
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    protected function getCaso($tabla, $referencia, $factura, $caso) {
        try {
            $select = $this->_db->select()
                    ->from($tabla, array("IDCASO"))
                    ->where("NUM_REF = ?", $referencia)
                    ->where("FACTFRA = ?", $factura)
                    ->where("TIPCAS = ?", $caso);
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                return $stmt["IDCASO"];
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    protected function getObservaciones($tabla, $referencia, $orden) {
        try {
            $select = $this->_db->select()
                    ->from($tabla, array("OBS"))
                    ->where("NUM_REF = ?", $referencia)
                    ->where("ORDEN = ?", $orden);
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                return $stmt["OBS"];
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function lastReference($year, $pattern) {
        try {
            $select = $this->_db->select()
                    ->from(array("p" => "sm3ped"), array("NUM_REF", "NUM_PED", "FEC_ENT"))
                    ->where("YEAR(FEC_ENT) = ?", $year)
                    ->where("NUM_REF LIKE ?", $pattern . "%")
                    ->order("NUM_PED DESC");
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                return $stmt;
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function basicInvoices($referencia) {
        try {
            $select = $this->_db->select()
                    ->from(array("p" => "sm3ped"), array(""))
                    ->joinLeft(array("f" => "sm3fact"), "f.NUM_REF = p.NUM_REF", array("f.NUMFAC AS numFactura", "f.VALEXT AS valorMonExt", "f.MONFAC AS moneda", new Zend_Db_Expr("CASE WHEN P.IMP_EXP = 1 THEN (SELECT TOP 1 C.NOMPRO FROM CMPRO C WHERE C.CVE_PRO = F.CVEPROV) ELSE (SELECT TOP 1 C.NOMPRO FROM CMDEST C WHERE C.CVE_PRO = F.CVEPROV) END AS proveedor"), "ACUSECOVE as cove"))
                    ->where("p.NUM_REF = ?", $referencia);
            $stmt = $this->_db->fetchAll($select);
            if ($stmt) {
                return $stmt;
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function paidInformation($referencia) {
        try {
            $select = $this->_db->select()
                    ->from(array("p" => "sm3ped"), array(new Zend_Db_Expr("CONVERT(VARCHAR(30), p.FEC_ENT, 126) AS fechaEntrada"), new Zend_Db_Expr("CONVERT(VARCHAR(30), P.FEC_PAG, 126) AS fechaPago"), "p.FIRMA AS firmaValidacion", "p.CONSOLR AS consolidado", "p.RECTIF AS rectificacion"))
                    ->joinLeft(array("b" => "saiban"), "p.NUM_PED = b.DOCTO", array("b.FIRMA AS firmaBanco"))
                    ->where("p.NUM_REF = ?", $referencia);
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                return $stmt;
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function trackingNumbers($referencia) {
        try {
            $select = $this->_db->select()
                    ->from(array("g" => "sm3guia"), array("g.IDGUIA as tipo", "g.NUMGUIA as guia"))
                    ->where("g.NUM_REF = ?", $referencia);
            $stmt = $this->_db->fetchAll($select);
            if ($stmt) {
                return $stmt;
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function candados($referencia) {
        try {
            $sql = $this->_db->select()
                    ->from(array("c" => "cmcand"), array("c.NUMERO AS numero", "c.COLOR AS color"))
                    ->where("c.NUM_REF = ?", $referencia);
            $stmt = $this->_db->fetchAll($sql);
            if ($stmt) {
                return $stmt;
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function searchTrackingNumber($tracking) {
        try {
            $select = $this->_db->select()
                    ->from(array("g" => "sm3guia"), array("NUM_REF as referencia", "NUMGUIA as guia"))
                    ->joinLeft(array("p" => "SM3PED"), "p.NUM_REF = g.NUM_REF", array("NUM_PED as pedimento", "RFCCTE as rfcCliente"))
                    ->where("REPLACE(g.NUMGUIA, ' ', '') = ?", $tracking);
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                return $stmt;
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function searchTrackingNumberConsolidado($tracking) {
        try {
            $select = $this->_db->select()
                    ->from(array("f" => "sm3confa"), array("NUM_PED as pedimento", "GUIAHOUSE as h", "GUIAMAST as m"))
                    ->joinLeft(array("p" => "sm3ped"), "p.NUM_PED = f.NUM_PED", array("NUM_REF as referencia", "RFCCTE as rfcCliente"))
                    ->where("(REPLACE(f.GUIAHOUSE, ' ', '') = '{$tracking}' OR REPLACE(f.GUIAMAST, ' ', '') = '{$tracking}')");
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                return array(
                    "rfcCliente" => $stmt["rfcCliente"],
                    "pedimento" => $stmt["pedimento"],
                    "referencia" => $stmt["referencia"],
                    "guia" => isset($stmt["m"]) ? $stmt["m"] : $stmt["h"],
                );
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function guiasConsolidado($pedimento) {
        try {
            $select = $this->_db->select()
                    ->from(array("g" => "sm3confa"), array("GUIAMAST as master", "GUIAHOUSE as house"))
                    ->where("g.NUM_PED = ?", $pedimento);
            $stmt = $this->_db->fetchAll($select);
            if ($stmt) {
                return $stmt;
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param string $rfcCliente
     * @param int $year
     * @param int $mes
     * @return boolean|array
     * @throws Exception
     */
    public function reporteIva($rfcCliente, $year, $mes) {
        try {
            $ped = array(
                new Zend_Db_Expr("RIGHT(YEAR(P.FEC_PAG),2) + '-' + P.ADUANAD + '-' + CAST(P.PATENTE AS VARCHAR(4)) + '-' + CAST(P.NUM_PED AS VARCHAR(7)) AS operacion"),
                new Zend_Db_Expr("CASE P.IMP_EXP WHEN 1 THEN 'IMP' ELSE 'EXP' END AS impexp"),
                "P.NUM_REF AS trafico",
                "P.CVEPEDIM AS cvePedimento"
            );
            $fracc = array("R.ORDEN AS ordenFraccion", "R.CODIGO AS fraccion", "R.DESC1 AS descripcion", "R.VALMN AS valor", "R.IMPOIVA AS iva");
            $select = $this->_db->select()
                    ->from(array("P" => "SM3PED"), $ped)
                    ->joinLeft(array("R" => "SM3FRA"), "R.NUM_REF = P.NUM_REF", $fracc)
                    ->joinLeft(array("B" => "SAIBAN"), "P.NUM_PED = B.DOCTO")
                    ->where("P.RFCCTE = ?", $rfcCliente)
                    ->where("YEAR(P.FEC_PAG) = ?", $year)
                    ->where("MONTH(P.FEC_PAG) = ?", $mes)
                    ->where("P.REGIMEN = 'IMD'")
                    ->where("P.NUM_PED <> 0 AND P.FIRMA <> '' AND P.FIRMA IS NOT NULL AND B.FIRMA IS NOT NULL");
            $stmt = $this->_db->fetchAll($select);
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param string $rfcCliente
     * @param int $year
     * @param int $mes
     * @return boolean|array
     * @throws Exception
     */
    public function reporteIvaProveedores($rfcCliente, $year, $mes) {
        try {
            $sql = "SELECT
                RIGHT(YEAR(P.FEC_PAG),2) + '-' + P.ADUANAD + '-' + CAST(P.PATENTE AS VARCHAR(4)) + '-' + CAST(P.NUM_PED AS VARCHAR(7)) AS operacion
                ,CASE P.IMP_EXP
                WHEN 1 THEN 'IMP'
                ELSE 'EXP'
                END AS impexp
                ,P.NUM_REF AS trafico
                ,P.CVEPEDIM AS cvePedimento
                ,(SELECT TOP 1 D.NUM_TAX FROM CMPRO D WHERE D.CVE_PRO = R.CVE_PRO) AS taxID 
                ,(SELECT TOP 1 D.NOMPRO FROM CMPRO D WHERE D.CVE_PRO = R.CVE_PRO) AS nomProveedor
                ,R.ORDEN AS ordenFraccion
                ,R.CODIGO AS fraccion
                ,R.DESC1 AS descripcion
                ,R.VALMN AS valor
                ,R.IMPOIVA AS iva
                FROM SM3PED P
                INNER JOIN SM3FRA R ON R.NUM_REF = P.NUM_REF
                INNER JOIN SAIBAN B ON P.NUM_PED = B.DOCTO
                WHERE
                        P.RFCCTE = '{$rfcCliente}'
                        AND YEAR(P.FEC_PAG) = {$year}
                        AND MONTH(P.FEC_PAG) = {$mes}
                        AND (P.NUM_PED <> 0 AND P.FIRMA <> '' AND P.FIRMA IS NOT NULL AND B.FIRMA IS NOT NULL)
                        AND P.REGIMEN = 'IMD'
                ORDER BY P.PATENTE, P.ADUANAD, p.NUM_PED,P.IMP_EXP, P.NUM_REF, R.ORDEN ASC;";
            $stmt = $this->_db->fetchAll($sql);
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " >> " . $e->getMessage());
        }
    }

    public function revisarCliente($rfc) {
        try {
            $select = $this->_db->select()
                    ->from(array("c" => "CMCLI"), array("CVE_IMP"))
                    ->where("c.RFC = ?", $rfc);
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                return $stmt["CVE_IMP"];
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function ultimoPedimento($curr, $next, $prefijo) {
        try {
            $select = $this->_db->select()
                    ->from(array("p" => "sm3ped"), array("NUM_REF", "NUM_PED"))
                    ->where("NUM_PED >= ?", $curr)
                    ->where("NUM_PED < ?", $next)
                    ->where("NUM_REF LIKE ?", $prefijo . "%")
                    ->order("NUM_PED DESC");
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                return $stmt;
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function tipoCambio($fecha) {
        try {
            $select = $this->_db->select()
                    ->from(array("t" => "CMTIP"), array("TIP_CAM"))
                    ->where("t.DIA = ?", date("Y-m-d", strtotime($fecha)));
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                return $stmt["TIP_CAM"];
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function ultimoArchivoValidacion($pedimento) {
        try {
            $select = $this->_db->select()
                    ->from(array("m" => "SM3USUGE"), array("ARCHIVO", "JULIANO"))
                    ->joinLeft(array("p" => "SM3PED"), "p.NUM_PED = m.PEDIMENTO", array("NUM_REF"))
                    ->where("m.PEDIMENTO = ?", $pedimento)
                    ->order("m.GENERADO DESC");
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                return array(
                    "archivo" => $stmt["ARCHIVO"] . "." . $stmt["JULIANO"],
                    "referencia" => $stmt["NUM_REF"]
                );
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function ultimoArchivoPago($patente, $referencia) {
        try {
            $sql = "SELECT RIGHT(CONVERT(varchar(8000),CONCEPTO),50) AS CONCEPTO "
                    . "FROM BITAPED "
                    . "WHERE LLAVE LIKE '%{$referencia}%' AND CONCEPTO LIKE '%E{$patente}%' "
                    . "AND YEAR(FECHA) = " . date("Y") . " AND MONTH(FECHA) = " . date("m") . " AND DAY(FECHA) = " . date("d") . " "
                    . "ORDER BY HORA DESC;";
            $stmt = $this->_db->query($sql);
            $stmt = $stmt->fetchAll();
            foreach ($stmt as $item) {
                if (preg_match("/E3589/i", $item["CONCEPTO"])) {
                    $exp = explode("\\", $item["CONCEPTO"]);
                    $filename = $exp[(count($exp) - 1)];
                    if (preg_match("/E[0-9]{7}.[0-9]{3}/i", $filename)) {
                        return $filename;
                    }
                    return false;
                }
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function ultimoArchivoEliminar($patente, $referencia) {
        try {
            $sql = "SELECT RIGHT(CONVERT(varchar(8000),CONCEPTO),70) AS CONCEPTO "
                    . "FROM BITAPED "
                    . "WHERE LLAVE LIKE '%{$referencia}%' AND CONCEPTO LIKE '%M{$patente}%' AND CONCEPTO LIKE '%PARA ELIMINAR FIRMA%' "
                    . "AND YEAR(FECHA) = " . date("Y") . " AND MONTH(FECHA) = " . date("m") . " AND DAY(FECHA) = " . date("d") . " "
                    . "ORDER BY HORA DESC;";
            $stmt = $this->_db->query($sql);
            $stmt = $stmt->fetchAll();
            foreach ($stmt as $item) {
                if (preg_match("/M3589/i", $item["CONCEPTO"]) && preg_match("/PARA ELIMINAR FIRMA/i", $item["CONCEPTO"])) {
                    $exp = explode("\\", $item["CONCEPTO"]);
                    $tmp = $exp[(count($exp) - 1)];
                    $ex = explode(" ", $tmp);
                    if (preg_match("/M[0-9]{7}.[0-9]{3}/i", $ex[0])) {
                        return $ex[0];
                    }
                    return false;
                }
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function operacionesPorUsuario($year) {
        try {
            $sql = "SELECT "
                    . "P.USUALT AS Usuario,"
                    . "SUM(CASE WHEN MONTH(P.FEC_PAG) = 1 THEN 1 ELSE 0 END) AS Ene, "
                    . "SUM(CASE WHEN MONTH(P.FEC_PAG) = 2 THEN 1 ELSE 0 END) AS Feb, "
                    . "SUM(CASE WHEN MONTH(P.FEC_PAG) = 3 THEN 1 ELSE 0 END) AS Mar, "
                    . "SUM(CASE WHEN MONTH(P.FEC_PAG) = 4 THEN 1 ELSE 0 END) AS Abr, "
                    . "SUM(CASE WHEN MONTH(P.FEC_PAG) = 5 THEN 1 ELSE 0 END) AS May, "
                    . "SUM(CASE WHEN MONTH(P.FEC_PAG) = 6 THEN 1 ELSE 0 END) AS Jun, "
                    . "SUM(CASE WHEN MONTH(P.FEC_PAG) = 7 THEN 1 ELSE 0 END) AS Jul, "
                    . "SUM(CASE WHEN MONTH(P.FEC_PAG) = 8 THEN 1 ELSE 0 END) AS Ago, "
                    . "SUM(CASE WHEN MONTH(P.FEC_PAG) = 9 THEN 1 ELSE 0 END) AS Sep, "
                    . "SUM(CASE WHEN MONTH(P.FEC_PAG) = 10 THEN 1 ELSE 0 END) AS 'Oct', "
                    . "SUM(CASE WHEN MONTH(P.FEC_PAG) = 11 THEN 1 ELSE 0 END) AS Nov, "
                    . "SUM(CASE WHEN MONTH(P.FEC_PAG) = 12 THEN 1 ELSE 0 END) AS Dic, "
                    . "COUNT(*) AS TotalPagados "
                    . "FROM SM3PED P "
                    . "LEFT JOIN  SAIBAN B ON P.NUM_PED = B.DOCTO "
                    . "WHERE YEAR(P.FEC_PAG) = {$year} "
                    . "AND (P.NUM_PED <> 0 AND P.FIRMA <> '' AND P.FIRMA IS NOT NULL AND B.FIRMA IS NOT NULL) "
                    . "GROUP BY P.USUALT;";
            $stmt = $this->_db->query($sql);
            $stmt = $stmt->fetchAll();
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function operacionesPorDiaClientes($year, $mes, $dia) {
        try {
            $sql = "SELECT "
                    . "P.RFCCTE AS RFC, "
                    . "(SELECT TOP 1 C.NOMCLI FROM CMCLI C WHERE C.RFC = P.RFCCTE) AS NomCliente, "
                    . "COUNT(*) AS TotalPagados "
                    . "FROM SM3PED P "
                    . "LEFT JOIN  SAIBAN B ON P.NUM_PED = B.DOCTO "
                    . "WHERE YEAR(P.FEC_PAG) = {$year} "
                    . "AND MONTH(P.FEC_PAG) = {$mes} "
                    . "AND DAY(P.FEC_PAG) = {$dia} "
                    . "AND (P.NUM_PED <> 0 AND P.FIRMA <> '' AND P.FIRMA IS NOT NULL AND B.FIRMA IS NOT NULL) "
                    . "GROUP BY P.RFCCTE "
                    . "ORDER BY TotalPagados DESC;";
            $stmt = $this->_db->query($sql);
            $stmt = $stmt->fetchAll();
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function operacionesPorMes($year, $mes) {
        try {
            $sql = "SELECT
                SUM(CASE WHEN DAY(P.FEC_PAG) = 1 THEN 1 ELSE 0 END) AS '1', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 2 THEN 1 ELSE 0 END) AS '2', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 3 THEN 1 ELSE 0 END) AS '3', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 4 THEN 1 ELSE 0 END) AS '4', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 5 THEN 1 ELSE 0 END) AS '5', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 6 THEN 1 ELSE 0 END) AS '6', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 7 THEN 1 ELSE 0 END) AS '7', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 8 THEN 1 ELSE 0 END) AS '8', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 9 THEN 1 ELSE 0 END) AS '9', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 10 THEN 1 ELSE 0 END) AS '10', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 11 THEN 1 ELSE 0 END) AS '11', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 12 THEN 1 ELSE 0 END) AS '12', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 13 THEN 1 ELSE 0 END) AS '13', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 14 THEN 1 ELSE 0 END) AS '14', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 15 THEN 1 ELSE 0 END) AS '15', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 16 THEN 1 ELSE 0 END) AS '16', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 17 THEN 1 ELSE 0 END) AS '17', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 18 THEN 1 ELSE 0 END) AS '18', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 19 THEN 1 ELSE 0 END) AS '19', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 20 THEN 1 ELSE 0 END) AS '20', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 21 THEN 1 ELSE 0 END) AS '21', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 22 THEN 1 ELSE 0 END) AS '22', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 23 THEN 1 ELSE 0 END) AS '23', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 24 THEN 1 ELSE 0 END) AS '24', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 25 THEN 1 ELSE 0 END) AS '25', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 26 THEN 1 ELSE 0 END) AS '26', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 27 THEN 1 ELSE 0 END) AS '27', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 28 THEN 1 ELSE 0 END) AS '28', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 29 THEN 1 ELSE 0 END) AS '29', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 30 THEN 1 ELSE 0 END) AS '30', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 31 THEN 1 ELSE 0 END) AS '31', 
                COUNT(*) AS TotalPagados 
                FROM SM3PED P 
                LEFT JOIN  SAIBAN B ON P.NUM_PED = B.DOCTO 
                WHERE YEAR(P.FEC_PAG) = {$year} AND MONTH(P.FEC_PAG) = {$mes}
                AND (P.NUM_PED <> 0 AND P.FIRMA <> '' AND P.FIRMA IS NOT NULL AND B.FIRMA IS NOT NULL);";
            $stmt = $this->_db->query($sql);
            $stmt = $stmt->fetchAll();
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function operacionesPorMesImpExp($year, $mes) {
        try {
            $sql = "SELECT
                P.IMP_EXP AS ImpExp,
                SUM(CASE WHEN DAY(P.FEC_PAG) = 1 THEN 1 ELSE 0 END) AS '1', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 2 THEN 1 ELSE 0 END) AS '2', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 3 THEN 1 ELSE 0 END) AS '3', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 4 THEN 1 ELSE 0 END) AS '4', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 5 THEN 1 ELSE 0 END) AS '5', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 6 THEN 1 ELSE 0 END) AS '6', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 7 THEN 1 ELSE 0 END) AS '7', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 8 THEN 1 ELSE 0 END) AS '8', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 9 THEN 1 ELSE 0 END) AS '9', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 10 THEN 1 ELSE 0 END) AS '10', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 11 THEN 1 ELSE 0 END) AS '11', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 12 THEN 1 ELSE 0 END) AS '12', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 13 THEN 1 ELSE 0 END) AS '13', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 14 THEN 1 ELSE 0 END) AS '14', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 15 THEN 1 ELSE 0 END) AS '15', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 16 THEN 1 ELSE 0 END) AS '16', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 17 THEN 1 ELSE 0 END) AS '17', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 18 THEN 1 ELSE 0 END) AS '18', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 19 THEN 1 ELSE 0 END) AS '19', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 20 THEN 1 ELSE 0 END) AS '20', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 21 THEN 1 ELSE 0 END) AS '21', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 22 THEN 1 ELSE 0 END) AS '22', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 23 THEN 1 ELSE 0 END) AS '23', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 24 THEN 1 ELSE 0 END) AS '24', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 25 THEN 1 ELSE 0 END) AS '25', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 26 THEN 1 ELSE 0 END) AS '26', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 27 THEN 1 ELSE 0 END) AS '27', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 28 THEN 1 ELSE 0 END) AS '28', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 29 THEN 1 ELSE 0 END) AS '29', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 30 THEN 1 ELSE 0 END) AS '30', 
                SUM(CASE WHEN DAY(P.FEC_PAG) = 31 THEN 1 ELSE 0 END) AS '31', 
                COUNT(*) AS TotalPagados 
                FROM SM3PED P 
                LEFT JOIN  SAIBAN B ON P.NUM_PED = B.DOCTO 
                WHERE YEAR(P.FEC_PAG) = {$year} AND MONTH(P.FEC_PAG) = {$mes}
                AND (P.NUM_PED <> 0 AND P.FIRMA <> '' AND P.FIRMA IS NOT NULL AND B.FIRMA IS NOT NULL)
                GROUP BY P.IMP_EXP;";
            $stmt = $this->_db->query($sql);
            $stmt = $stmt->fetchAll();
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function operacionesPorDiaImpExp($year, $mes, $dia) {
        try {
            $sql = "SELECT "
                    . "P.IMP_EXP AS ImpExp, "
                    . "COUNT(*) AS TotalPagados "
                    . "FROM SM3PED P "
                    . "LEFT JOIN  SAIBAN B ON P.NUM_PED = B.DOCTO "
                    . "WHERE YEAR(P.FEC_PAG) = {$year} "
                    . "AND MONTH(P.FEC_PAG) = {$mes} "
                    . "AND DAY(P.FEC_PAG) = {$dia} "
                    . "AND (P.NUM_PED <> 0 AND P.FIRMA <> '' AND P.FIRMA IS NOT NULL AND B.FIRMA IS NOT NULL) "
                    . "GROUP BY P.IMP_EXP "
                    . "ORDER BY TotalPagados DESC;";
            $stmt = $this->_db->query($sql);
            $stmt = $stmt->fetchAll();
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function operacionesPorDiaUsuarios($year, $mes, $dia) {
        try {
            $sql = "SELECT "
                    . "P.USUALT AS Usuario, "
                    . "COUNT(*) AS TotalPagados "
                    . "FROM SM3PED P "
                    . "LEFT JOIN  SAIBAN B ON P.NUM_PED = B.DOCTO "
                    . "WHERE YEAR(P.FEC_PAG) = {$year} "
                    . "AND MONTH(P.FEC_PAG) = {$mes} "
                    . "AND DAY(P.FEC_PAG) = {$dia} "
                    . "AND (P.NUM_PED <> 0 AND P.FIRMA <> '' AND P.FIRMA IS NOT NULL AND B.FIRMA IS NOT NULL) "
                    . "GROUP BY P.USUALT;;";
            $stmt = $this->_db->query($sql);
            $stmt = $stmt->fetchAll();
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function operacionesTotales($year) {
        try {
            $sql = "SELECT "
                    . "SUM(CASE WHEN MONTH(P.FEC_PAG) = 1 THEN 1 ELSE 0 END) AS Ene, "
                    . "SUM(CASE WHEN MONTH(P.FEC_PAG) = 2 THEN 1 ELSE 0 END) AS Feb, "
                    . "SUM(CASE WHEN MONTH(P.FEC_PAG) = 3 THEN 1 ELSE 0 END) AS Mar, "
                    . "SUM(CASE WHEN MONTH(P.FEC_PAG) = 4 THEN 1 ELSE 0 END) AS Abr, "
                    . "SUM(CASE WHEN MONTH(P.FEC_PAG) = 5 THEN 1 ELSE 0 END) AS May, "
                    . "SUM(CASE WHEN MONTH(P.FEC_PAG) = 6 THEN 1 ELSE 0 END) AS Jun, "
                    . "SUM(CASE WHEN MONTH(P.FEC_PAG) = 7 THEN 1 ELSE 0 END) AS Jul, "
                    . "SUM(CASE WHEN MONTH(P.FEC_PAG) = 8 THEN 1 ELSE 0 END) AS Ago, "
                    . "SUM(CASE WHEN MONTH(P.FEC_PAG) = 9 THEN 1 ELSE 0 END) AS Sep, "
                    . "SUM(CASE WHEN MONTH(P.FEC_PAG) = 10 THEN 1 ELSE 0 END) AS 'Oct', "
                    . "SUM(CASE WHEN MONTH(P.FEC_PAG) = 11 THEN 1 ELSE 0 END) AS Nov, "
                    . "SUM(CASE WHEN MONTH(P.FEC_PAG) = 12 THEN 1 ELSE 0 END) AS Dic, "
                    . "COUNT(*) AS TotalPagados "
                    . "FROM SM3PED P "
                    . "LEFT JOIN  SAIBAN B ON P.NUM_PED = B.DOCTO "
                    . "WHERE YEAR(P.FEC_PAG) = {$year} "
                    . "AND (P.NUM_PED <> 0 AND P.FIRMA <> '' AND P.FIRMA IS NOT NULL AND B.FIRMA IS NOT NULL);";
            $stmt = $this->_db->query($sql);
            $stmt = $stmt->fetchAll();
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function operacionesSumarizadas($year) {
        try {
            $sql = "SELECT "
                    . "P.USUALT AS Usuario, "
                    . "COUNT(*) AS TotalPagados "
                    . "FROM SM3PED P "
                    . "LEFT JOIN  SAIBAN B ON P.NUM_PED = B.DOCTO "
                    . "WHERE YEAR(P.FEC_PAG) = {$year} "
                    . "AND (P.NUM_PED <> 0 AND P.FIRMA <> '' AND P.FIRMA IS NOT NULL AND B.FIRMA IS NOT NULL) "
                    . "GROUP BY P.USUALT;";
            $stmt = $this->_db->query($sql);
            $stmt = $stmt->fetchAll();
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function operacionesCliente($year, $rfc) {
        try {
            $sql = "SELECT "
                    . "P.IMP_EXP AS TipoOperacion, "
                    . "SUM(CASE WHEN MONTH(P.FEC_PAG) = 1 THEN 1 ELSE 0 END) AS Ene, "
                    . "SUM(CASE WHEN MONTH(P.FEC_PAG) = 2 THEN 1 ELSE 0 END) AS Feb, "
                    . "SUM(CASE WHEN MONTH(P.FEC_PAG) = 3 THEN 1 ELSE 0 END) AS Mar, "
                    . "SUM(CASE WHEN MONTH(P.FEC_PAG) = 4 THEN 1 ELSE 0 END) AS Abr, "
                    . "SUM(CASE WHEN MONTH(P.FEC_PAG) = 5 THEN 1 ELSE 0 END) AS May, "
                    . "SUM(CASE WHEN MONTH(P.FEC_PAG) = 6 THEN 1 ELSE 0 END) AS Jun, "
                    . "SUM(CASE WHEN MONTH(P.FEC_PAG) = 7 THEN 1 ELSE 0 END) AS Jul, "
                    . "SUM(CASE WHEN MONTH(P.FEC_PAG) = 8 THEN 1 ELSE 0 END) AS Ago, "
                    . "SUM(CASE WHEN MONTH(P.FEC_PAG) = 9 THEN 1 ELSE 0 END) AS Sep, "
                    . "SUM(CASE WHEN MONTH(P.FEC_PAG) = 10 THEN 1 ELSE 0 END) AS 'Oct', "
                    . "SUM(CASE WHEN MONTH(P.FEC_PAG) = 11 THEN 1 ELSE 0 END) AS Nov, "
                    . "SUM(CASE WHEN MONTH(P.FEC_PAG) = 12 THEN 1 ELSE 0 END) AS Dic, "
                    . "COUNT(*) AS TotalPagados "
                    . "FROM SM3PED P "
                    . "LEFT JOIN  SAIBAN B ON P.NUM_PED = B.DOCTO "
                    . "WHERE YEAR(P.FEC_PAG) = {$year} "
                    . "AND P.RFCCTE = '{$rfc}' "
                    . "AND (P.NUM_PED <> 0 AND P.FIRMA <> '' AND P.FIRMA IS NOT NULL AND B.FIRMA IS NOT NULL) "
                    . "GROUP BY P.IMP_EXP;";
            $stmt = $this->_db->query($sql);
            $stmt = $stmt->fetchAll();
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function operacionesClienteCve($year, $rfc) {
        try {
            $sql = "SELECT "
                    . "P.CVEPEDIM AS CvePedimento, "
                    . "SUM(CASE WHEN MONTH(P.FEC_PAG) = 1 THEN 1 ELSE 0 END) AS Ene, "
                    . "SUM(CASE WHEN MONTH(P.FEC_PAG) = 2 THEN 1 ELSE 0 END) AS Feb, "
                    . "SUM(CASE WHEN MONTH(P.FEC_PAG) = 3 THEN 1 ELSE 0 END) AS Mar, "
                    . "SUM(CASE WHEN MONTH(P.FEC_PAG) = 4 THEN 1 ELSE 0 END) AS Abr, "
                    . "SUM(CASE WHEN MONTH(P.FEC_PAG) = 5 THEN 1 ELSE 0 END) AS May, "
                    . "SUM(CASE WHEN MONTH(P.FEC_PAG) = 6 THEN 1 ELSE 0 END) AS Jun, "
                    . "SUM(CASE WHEN MONTH(P.FEC_PAG) = 7 THEN 1 ELSE 0 END) AS Jul, "
                    . "SUM(CASE WHEN MONTH(P.FEC_PAG) = 8 THEN 1 ELSE 0 END) AS Ago, "
                    . "SUM(CASE WHEN MONTH(P.FEC_PAG) = 9 THEN 1 ELSE 0 END) AS Sep, "
                    . "SUM(CASE WHEN MONTH(P.FEC_PAG) = 10 THEN 1 ELSE 0 END) AS 'Oct', "
                    . "SUM(CASE WHEN MONTH(P.FEC_PAG) = 11 THEN 1 ELSE 0 END) AS Nov, "
                    . "SUM(CASE WHEN MONTH(P.FEC_PAG) = 12 THEN 1 ELSE 0 END) AS Dic, "
                    . "COUNT(*) AS TotalPagados "
                    . "FROM SM3PED P "
                    . "LEFT JOIN  SAIBAN B ON P.NUM_PED = B.DOCTO "
                    . "WHERE YEAR(P.FEC_PAG) = {$year} "
                    . "AND P.RFCCTE = '{$rfc}' "
                    . "AND (P.NUM_PED <> 0 AND P.FIRMA <> '' AND P.FIRMA IS NOT NULL AND B.FIRMA IS NOT NULL) "
                    . "GROUP BY P.CVEPEDIM;";
            $stmt = $this->_db->query($sql);
            $stmt = $stmt->fetchAll();
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function pedimentoDatosBasicos($pedimento) {
        try {
            $select = $this->_db->select()
                    ->from(array("s" => "sm3ped"), array("NUM_REF AS referencia", "NUM_PED AS pedimento", new Zend_Db_Expr("CASE IMP_EXP WHEN 1 THEN 'IMP' ELSE 'EXP' END AS tipoOperacion"), "CVEPEDIM AS cvePedimento", "CONSOLR AS consolidado", "RECTIF AS rectificacion", "FIRMA AS firmaValidacion"))
                    ->joinLeft(array("b" => "saiban"), "s.NUM_PED = b.DOCTO", array("FIRMA as firmaBanco"))
                    ->where("s.NUM_PED = ?", $pedimento);
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function pedimentoCompleto($pedimento) {
        try {
            $efectivo = new Zend_Db_Expr("((CASE DTA_FP WHEN 0 THEN DTA_TOT ELSE 0 end) +  (CASE DTA_FPADI WHEN 0 THEN  DTA_TLADI ELSE 0 end) + (CASE CC1_FP WHEN 0 THEN CC1_TOT ELSE 0 end) +  (CASE CC2_FP WHEN 0 THEN CC2_TOT ELSE 0 end) +  (CASE IVA1_FP WHEN 0 THEN IVA1_TOT ELSE 0 end) +  (CASE IVA2_FP WHEN 0 THEN IVA2_TOT ELSE 0 end) +  (CASE ISAN_FP WHEN 0 THEN ISAN_TOT ELSE 0 end) +  (CASE IEPS_FP WHEN 0 THEN IEPS_TOT ELSE 0 end) +  (CASE REC_FP WHEN 0 THEN REC_TOT ELSE 0 end) +  (CASE OTR_FP WHEN 0 THEN OTR_TOT ELSE 0 end) +  (CASE GAR_FP WHEN 0 THEN GAR_TOT ELSE 0 end) +  (CASE MUL_FP WHEN 0 THEN MUL_TOT ELSE 0 end) +  (CASE MUL2_FP WHEN 0 THEN MUL2_TOT ELSE 0 end) +  (CASE DTI_FP WHEN 0 THEN DTI_TOT ELSE 0 end) +  (CASE IGIR_FP WHEN 0 THEN IGIR_TOT ELSE 0 end) +  (CASE PRE_FP WHEN 0 THEN PRE_TOT ELSE 0 end) +  (CASE BSS_FP WHEN 0 THEN BSS_TOT ELSE 0 end) +  (CASE EUR_FP WHEN 0 THEN EUR_TOT ELSE 0 end) +  (CASE ECI_FP WHEN 0 THEN ECI_TOT ELSE 0 end) +  (CASE ITV_FP WHEN 0 THEN ITV_TOT ELSE 0 end) +  (CASE IGIR_FP2 WHEN 0 THEN IGIR_TOT2 ELSE 0 end) +  (CASE REC2_FP WHEN 0 THEN REC2_TOT ELSE 0 end)) AS totalEfectivo");
            $sql = $this->_db->select()
                    ->from(array("s" => "sm3ped"), array("NUM_REF AS referencia", "NUM_PED AS pedimento", "CVEPEDIM AS cvePedimento", "CONSOLR AS consolidado", "RECTIF AS rectificacion", "FIRMA AS firmaValidacion", "RFCCTE AS rfcCliente", "CVE_IMP as cveCliente", "REGIMEN as regimen", new Zend_Db_Expr("CONVERT(VARCHAR(10), FEC_PAG, 103) AS fechaPago"), new Zend_Db_Expr("CONVERT(VARCHAR(10), FECEXT, 103) AS fechaExtraccion"), new Zend_Db_Expr("CONVERT(VARCHAR(10), FEC_ENT, 103) AS fechaEntrada"), new Zend_Db_Expr("CAST(ADUANAD AS VARCHAR(2)) + CAST(SECCDES AS VARCHAR(1)) AS aduana"), "PATENTE AS patente", new Zend_Db_Expr("CASE IMP_EXP WHEN 1 THEN 'IMP' ELSE 'EXP' END AS tipoOperacion"), "TIP_CAM AS tipoCambio", "USUMOD AS usuario", "MEDTRAS AS transporteEntrada", "MEDTRAA AS transporteArribo", "MEDTRAE AS transporteSalida", "DOMER AS destinoOrigen", "PESBRU AS pesoBruto", new Zend_Db_Expr("CAST(ADUANAE AS VARCHAR(2)) + CAST(SECCENT AS VARCHAR(1)) AS aduanaEntrada"), "(VALMEDLLS * FACAJU) AS valorDolares", "VALADUANA AS valorAduana", "VALMN_PAG AS valorComercial", "FLETES AS fletes", "SEGUROS AS seguros", "EMBALAJ AS embalajes", "OTROINC AS otrosIncrementables", "BULTOS AS bultos", "MARYNUM AS marcas", "DTA_TOT AS dta", "MANDAT AS cveTasa", "MANDAT AS agente", "DTA_FP AS dtaFp", "IVA1_TOT AS iva", "IVA1_FP as ivaFp", $efectivo, "PRE_TOT AS prev", "CNT_TOT AS cnt", "CNT_FP AS cntFp", "FIRMADIG as firmaDigital", "RFCSOCAG as sociedad"))
                    ->joinLeft(array("b" => "saiban"), "s.NUM_PED = b.DOCTO", array("FIRMA as firmaBanco"))
                    ->where("s.NUM_PED = ?", $pedimento);
            $stmt = $this->_db->fetchRow($sql);
            if ($stmt) {
                $stmt["pago"] = $this->_banco($stmt["pedimento"]);
                $stmt["liquidacion"] = $this->_liquidacion($stmt["referencia"], $stmt["regimen"]);
                $stmt["observaciones"] = $this->_observacion($stmt["referencia"]);
                $stmt["aduanaNombre"] = $this->_aduana(substr($stmt["aduana"], 0, 2), substr($stmt["aduana"], -1));
                $stmt["extracciones"] = $this->_extracciones($stmt["referencia"]);
                $stmt["guias"] = $this->_guias($stmt["referencia"]);
                $stmt["contenedores"] = $this->_contenedores($stmt["referencia"]);
                $stmt["transporte"] = $this->_transportes($stmt["referencia"]);
                $stmt["cliente"] = $this->_datosCliente($stmt["cveCliente"]);
                $stmt["identificadores"] = $this->_casos($stmt["referencia"], 0);
                $stmt["proveedores"] = $this->_proveedores($stmt["referencia"], $stmt["cveCliente"], $stmt["tipoOperacion"]);
                $stmt["fracciones"] = $this->_fracciones($stmt["referencia"]);
                $stmt["agente"] = $this->_datosAgente($stmt["agente"]);
                $stmt["sociedad"] = $this->_datosSociedad($stmt["sociedad"]);
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function pedimentoSimplicado($pedimento) {
        try {
            $efectivo = new Zend_Db_Expr("((CASE DTA_FP WHEN 0 THEN DTA_TOT ELSE 0 end) +  (CASE DTA_FPADI WHEN 0 THEN  DTA_TLADI ELSE 0 end) + (CASE CC1_FP WHEN 0 THEN CC1_TOT ELSE 0 end) +  (CASE CC2_FP WHEN 0 THEN CC2_TOT ELSE 0 end) +  (CASE IVA1_FP WHEN 0 THEN IVA1_TOT ELSE 0 end) +  (CASE IVA2_FP WHEN 0 THEN IVA2_TOT ELSE 0 end) +  (CASE ISAN_FP WHEN 0 THEN ISAN_TOT ELSE 0 end) +  (CASE IEPS_FP WHEN 0 THEN IEPS_TOT ELSE 0 end) +  (CASE REC_FP WHEN 0 THEN REC_TOT ELSE 0 end) +  (CASE OTR_FP WHEN 0 THEN OTR_TOT ELSE 0 end) +  (CASE GAR_FP WHEN 0 THEN GAR_TOT ELSE 0 end) +  (CASE MUL_FP WHEN 0 THEN MUL_TOT ELSE 0 end) +  (CASE MUL2_FP WHEN 0 THEN MUL2_TOT ELSE 0 end) +  (CASE DTI_FP WHEN 0 THEN DTI_TOT ELSE 0 end) +  (CASE IGIR_FP WHEN 0 THEN IGIR_TOT ELSE 0 end) +  (CASE PRE_FP WHEN 0 THEN PRE_TOT ELSE 0 end) +  (CASE BSS_FP WHEN 0 THEN BSS_TOT ELSE 0 end) +  (CASE EUR_FP WHEN 0 THEN EUR_TOT ELSE 0 end) +  (CASE ECI_FP WHEN 0 THEN ECI_TOT ELSE 0 end) +  (CASE ITV_FP WHEN 0 THEN ITV_TOT ELSE 0 end) +  (CASE IGIR_FP2 WHEN 0 THEN IGIR_TOT2 ELSE 0 end) +  (CASE REC2_FP WHEN 0 THEN REC2_TOT ELSE 0 end)) AS totalEfectivo");
            $select = $this->_db->select()
                    ->from(array("s" => "sm3ped"), array("NUM_REF AS referencia", "NUM_PED AS pedimento", "CVEPEDIM AS cvePedimento", "CONSOLR AS consolidado", "RECTIF AS rectificacion", "FIRMA AS firmaValidacion", "RFCCTE AS rfcCliente", "CVE_IMP as cveCliente", "REGIMEN as regimen", new Zend_Db_Expr("CONVERT(VARCHAR(10), FEC_PAG, 103) AS fechaPago"), new Zend_Db_Expr("CONVERT(VARCHAR(10), FECEXT, 103) AS fechaExtraccion"), new Zend_Db_Expr("CONVERT(VARCHAR(10), FEC_ENT, 103) AS fechaEntrada"), new Zend_Db_Expr("CAST(ADUANAD AS VARCHAR(2)) + CAST(SECCDES AS VARCHAR(1)) AS aduana"), "PATENTE AS patente", new Zend_Db_Expr("CASE IMP_EXP WHEN 1 THEN 'IMP' ELSE 'EXP' END AS tipoOperacion"), "TIP_CAM AS tipoCambio", "USUMOD AS usuario", "DOMER AS destinoOrigen", "PESBRU AS pesoBruto", new Zend_Db_Expr("CAST(ADUANAE AS VARCHAR(2)) + CAST(SECCENT AS VARCHAR(1)) AS aduanaEntrada"), "(VALMEDLLS * FACAJU) AS valorDolares", "VALADUANA AS valorAduana", "VALMN_PAG AS valorComercial", "BULTOS AS bultos", "MARYNUM AS marcas", "DTA_TOT AS dta", "MANDAT AS cveTasa", "MANDAT AS agente", "DTA_FP AS dtaFp", "IVA1_TOT AS iva", "IVA1_FP as ivaFp", $efectivo, "PRE_TOT AS prev", "CNT_TOT AS cnt", "CNT_FP AS cntFp", "FIRMADIG as firmaDigital", "RFCSOCAG as sociedad"))
                    ->joinLeft(array("b" => "saiban"), "s.NUM_PED = b.DOCTO", array("FIRMA as firmaBanco"))
                    ->where("s.NUM_PED = ?", $pedimento);
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                $stmt["pago"] = $this->_banco($stmt["pedimento"]);
                $stmt["liquidacion"] = $this->_liquidacion($stmt["referencia"], $stmt["regimen"]);
                $stmt["observaciones"] = $this->_observacion($stmt["referencia"]);
                $stmt["aduanaNombre"] = $this->_aduana(substr($stmt["aduana"], 0, 2), substr($stmt["aduana"], -1));
                $stmt["cliente"] = $this->_datosCliente($stmt["cveCliente"]);
                $stmt["agente"] = $this->_datosAgente($stmt["agente"]);
                $stmt["sociedad"] = $this->_datosSociedad($stmt["sociedad"]);
                $stmt["fracciones"] = $this->_fraccionesSimplicado($stmt["referencia"]);
                $stmt["coves"] = $this->_facturasSimplicado($stmt["referencia"]);
                $stmt["edocuments"] = $this->_casos($stmt["referencia"], 0, "ED");
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    protected function _datosCliente($cveCli) {
        try {
            $select = $this->_db->select()
                    ->from(array("s" => "cmcli"), array("NOMCLI AS razonSocial", "DIRCALLE AS calle", "DIRNUMEXT AS numExterior", "DIRNUMINT AS numInterior", "DIRMUNIC as municipio", "DIRENTFED AS entidad", "DIRPAIS AS pais", "RFC AS rfc", "CP AS codigoPostal"))
                    ->where("s.CVE_IMP = ?", $cveCli);
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                $data = array(
                    "rfc" => $stmt["rfc"],
                    "razonSocial" => $stmt["razonSocial"],
                    "domicilio" => array(
                        "calle" => $stmt["calle"],
                        "numExterior" => $stmt["numExterior"],
                        "numInterior" => $stmt["numInterior"],
                        "municipio" => $stmt["municipio"],
                        "entidad" => $stmt["entidad"],
                        "pais" => $this->_pais($stmt["pais"]),
                        "codigoPostal" => $stmt["codigoPostal"],
                    ),
                );
                return $data;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    protected function _observacion($referencia) {
        try {
            $select = $this->_db->select()
                    ->from(array("s" => "sm3obs"), array("OBS as observacion"))
                    ->where("s.NUM_REF = ?", $referencia);
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                return $stmt["observacion"];
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    protected function _extracciones($referencia) {
        try {
            $select = $this->_db->select()
                    ->distinct()
                    ->from(array("s" => "sm3prev"), array("PATORI AS patente", new Zend_Db_Expr("CAST(ADUORI AS VARCHAR(2)) + CAST(SECORI AS VARCHAR(1)) AS aduana"), "PEDORI AS pedimento", "YEARORI as year", new Zend_Db_Expr("CONVERT(VARCHAR(10), FECORI, 103) AS fecha"), "REGORI as regimen"))
                    ->where("s.NUM_REF = ?", $referencia);
            $stmt = $this->_db->fetchAll($select);
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
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
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    protected function _casos($referencia, $orden, $tipoCaso = null) {
        try {
            $select = $this->_db->select()
                    ->distinct()
                    ->from(array("s" => "sm3casos"), array("TIPCAS AS tipoCaso", "FOLIO AS folio", "IDCASO AS caso1", "IDCASO2 AS caso2", "IDCASO3 as caso3"))
                    ->where("s.NUM_REF = ?", $referencia)
                    ->where("s.orden = ?", $orden)
                    ->order("folio ASC");
            if (isset($tipoCaso)) {
                $select->where("TIPCAS = ?", $tipoCaso);
            }
            $stmt = $this->_db->fetchAll($select);
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    protected function _aduana($aduana, $seccion) {
        try {
            $select = $this->_db->select()
                    ->distinct()
                    ->from(array("s" => "cmadu"), array("NOM_ADU as nombre"))
                    ->where("s.ADUANA = ?", $aduana)
                    ->where("s.SECCION = ?", $seccion);
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                return $stmt["nombre"];
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    protected function _datosProveedor($cveProveedor, $cveCliente = null) {
        try {
            $select = $this->_db->select()
                    ->from(array("s" => "cmpro"), array("NUM_TAX as taxId", "NOMPRO as nomProveedor", "DIRCALLE AS calle", "DIRNUMEXT AS numExterior", "DIRNUMINT AS numInterior", "DIRMUNI as municipio", "DIRPAIS AS pais", "CP AS codigoPostal"))
                    ->where("s.CVE_PRO = ?", $cveProveedor)
                    ->limit(1);
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                return $stmt;
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    protected function _obtenerDatosProveedor($cveProveedor) {
        try {
            $select = $this->_db->select()
                    ->from(array("s" => "cmpro"), array("NUM_TAX as taxId", "NOMPRO as nomProveedor", "DIRCALLE AS calle", "DIRNUMEXT AS numExterior", "DIRNUMINT AS numInterior", "DIRMUNI as municipio", "DIRPAIS AS pais", "CP AS codigoPostal"))
                    ->where("s.CVE_PRO = ?", $cveProveedor)
                    ->limit(1);
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                return $stmt;
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    protected function _obtenerDatosDestinatario($cveProveedor) {
        try {
            $select = $this->_db->select()
                    ->from(array("s" => "cmdest"), array("NUM_TAX as taxId", "NOMPRO as nomProveedor", "DIRCALLE AS calle", "DIRNUMEXT AS numExterior", "DIRNUMINT AS numInterior", "DIRMUNI as municipio", "DIRPAIS AS pais", "CP AS codigoPostal"))
                    ->where("s.CVE_PRO = ?", $cveProveedor)
                    ->limit(1);
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                return $stmt;
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    protected function _proveedores($referencia, $cveCliente, $tipoOperacion) {
        try {
            $select = $this->_db->select()
                    ->distinct()
                    ->from(array("s" => "sm3fact"), array("CVEPROV as cveProveedor"))
                    ->where("s.NUM_REF = ?", $referencia);
            $stmt = $this->_db->fetchAll($select);
            if ($stmt) {
                $data = array();
                foreach ($stmt as $item) {
                    $pro = $this->_datosProveedor($item["cveProveedor"]);
                    $data[] = array(
                        "cveProveedor" => $item["cveProveedor"],
                        "taxId" => $pro["taxId"],
                        "nomProveedor" => $pro["nomProveedor"],
                        "domicilio" => array(
                            "calle" => $pro["calle"],
                            "numExterior" => $pro["numExterior"],
                            "numInterior" => $pro["numInterior"],
                            "municipio" => $pro["municipio"],
                            "pais" => $pro["pais"],
                            "codigoPostal" => $pro["codigoPostal"],
                        ),
                        "facturas" => $this->_facturas($referencia, $item["cveProveedor"]),
                    );
                }
                return $data;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    protected function _liquidacion($referencia, $regimen) {
        try {
            $select = $this->_db->select()
                    ->from(array("s" => "sm3ped"), array("DTA", "DTA_FP", "DTA_TOT", "IGIE_TOT", "IVA1_TOT", "IVA1_FP", "PRE_TOT", "PRE_FP", "CNT_TOT", "CNT_FP", "MUL_TOT", "MUL_FP"))
                    ->where("s.NUM_REF = ?", $referencia);
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                $data = array();
                if ($stmt["DTA"] > 0) {
                    $data["impuestos"][] = array(
                        "impuesto" => "DTA",
                        "cantidad" => $stmt["DTA_TOT"],
                        "fp" => $stmt["DTA_FP"],
                    );
                    if($stmt["DTA"] == "8") {
                        $data["tasas"][] = array(
                            "impuesto" => "DTA",
                            "cve" => "7",
                            "tasa" => $stmt["DTA"],
                        );
                    } elseif($stmt["DTA"] == "281") {
                        $data["tasas"][] = array(
                            "impuesto" => "DTA",
                            "cve" => "4",
                            "tasa" => $stmt["DTA"],
                        );                        
                    }
                }
                if ($stmt["MUL_TOT"] > 0) {
                    $data["impuestos"][] = array(
                        "impuesto" => "MULT",
                        "cantidad" => $stmt["MUL_TOT"],
                        "fp" => $stmt["MUL_FP"],
                    );
                    $data["tasas"][] = array(
                        "impuesto" => "MULT",
                        "cve" => "4",
                        "tasa" => $stmt["MUL_TOT"],
                    );
                }
                if ($stmt["IVA1_TOT"] > 0) {
                    $data["impuestos"][] = array(
                        "impuesto" => "IVA",
                        "cantidad" => $stmt["IVA1_TOT"],
                        "fp" => $stmt["IVA1_FP"],
                    );
                }
                if ($stmt["IGIE_TOT"] > 0) {
                    $data["impuestos"][] = array(
                        "impuesto" => "IGI",
                        "cantidad" => $stmt["IGIE_TOT"],
                        "fp" => 0,
                    );
                }
                if ($stmt["PRE_TOT"] > 0) {
                    $data["impuestos"][] = array(
                        "impuesto" => "PRV",
                        "cantidad" => $stmt["PRE_TOT"],
                        "fp" => $stmt["PRE_FP"],
                    );
                    $data["tasas"][] = array(
                        "impuesto" => "PRV",
                        "cve" => "2",
                        "tasa" => "210",
                    );
                }
                if ($stmt["CNT_TOT"] > 0) {
                    $data["impuestos"][] = array(
                        "impuesto" => "CNT",
                        "cantidad" => $stmt["CNT_TOT"],
                        "fp" => $stmt["CNT_FP"],
                    );
                    $data["tasas"][] = array(
                        "impuesto" => "CNT",
                        "cve" => "2",
                        "tasa" => "20",
                    );
                }
                if ($regimen == "ITE") {
                    $data["efectivo"] = $stmt["DTA_TOT"] + $stmt["IGIE_TOT"] + $stmt["PRE_TOT"] + $stmt["CNT_TOT"] + $stmt["MUL_TOT"];
                    $data["otros"] = $stmt["IVA1_TOT"];
                    $data["total"] = $data["efectivo"] + $data["otros"];
                } elseif ($regimen == "IMD") {
                    $data["efectivo"] = $stmt["DTA_TOT"] + $stmt["IGIE_TOT"] + $stmt["PRE_TOT"] + $stmt["CNT_TOT"] + $stmt["IVA1_TOT"] + $stmt["MUL_TOT"];
                    $data["otros"] = 0;
                    $data["total"] = $data["efectivo"] + $data["otros"];
                } else {
                    $data["efectivo"] = $stmt["DTA_TOT"] + $stmt["IGIE_TOT"] + $stmt["PRE_TOT"] + $stmt["CNT_TOT"] + $stmt["IVA1_TOT"] + $stmt["MUL_TOT"];
                    $data["otros"] = 0;
                    $data["total"] = $data["efectivo"] + $data["otros"];                    
                }
                return $data;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    protected function _facturas($referencia, $cveProveedor) {
        try {
            $select = $this->_db->select()
                    ->from(array("s" => "sm3fact"), array("NUMFAC AS numFactura", new Zend_Db_Expr("CONVERT(VARCHAR(10), FECFAC, 103) AS fechaFactura"), "INCOTER as incoterm", "MONFAC as divisa", "VALEXT as valorMonExt", "VALDLS as valorDolares", "FACEQ as factorEquivalencia", "FVINCULA as vinculacion", "ACUSECOVE as cove"))
                    ->where("s.NUM_REF = ?", $referencia)
                    ->where("s.CVEPROV = ?", $cveProveedor)
                    ->order("s.NUMFAC ASC");
            $stmt = $this->_db->fetchAll($select);
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    protected function _pais($cve) {
        try {
            $select = $this->_db->select()
                    ->from(array("s" => "cmmon"), array("PAIS as pais"))
                    ->where("s.CVE_PAIS = ?", $cve);
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                return $stmt["pais"];
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    protected function _fracciones($referencia) {
        try {
            $select = $this->_db->select()
                    ->from(array("s" => "sm3fra"), array("CODIGO as fraccion", "SUB as subdivision", "VINCUL as vinculacion", "VALORAC as valoracion", "ORDEN AS secuencia", "DESC1 as descripcion", "VALCOM AS valorComercial", "CANTFAC AS cantidad", "UMC AS umc", "CANTTAR as tarifa", "UMT as umt", "PAIORI as paisOrigen", "PAICOM as paisComprador", "TASAIVA as ivaTasa", "FPAGIVA1 as ivaFp", "IMPOIVA as iva", "TASAADV as igiTasa", "FPAGADV1 as igiFp", "IMPOADV as igi"))
                    ->where("s.NUM_REF = ?", $referencia)
                    ->order("ORDEN ASC");
            $stmt = $this->_db->fetchAll($select);
            if ($stmt) {
                $data = array();
                foreach ($stmt as $item) {
                    $item["observaciones"] = $this->_observacionesPartida($referencia, $item["secuencia"]);
                    $item["identificadores"] = $this->_casos($referencia, $item["secuencia"]);
                    $data[] = $item;
                }
                return $data;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    protected function _fraccionesSimplicado($referencia) {
        try {
            $select = $this->_db->select()
                    ->from(array("s" => "sm3fra"), array("CODIGO as fraccion"))
                    ->where("s.NUM_REF = ?", $referencia)
                    ->order("ORDEN ASC");
            $stmt = $this->_db->fetchAll($select);
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    protected function _observacionesPartida($referencia, $orden) {
        try {
            $select = $this->_db->select()
                    ->from(array("s" => "sm3obsfr"), array("OBS as observacion", "MARCA as marca", "MODELO as modelo", "SUBMODELO as submodelo", "NUM_SERIE as serie"))
                    ->where("s.NUM_REF = ?", $referencia)
                    ->where("s.ORDEN = ?", $orden);
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    protected function _datosAgente($num) {
        try {
            $select = $this->_db->select()
                    ->from(array("s" => "cmrep"), array("AUT_NOM as nombre", "AUT_RFC as rfc", "AUT_CURP as curp", "NUMSERIE as serie"))
                    ->where("s.NUM_NOM = ?", $num);
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    protected function _datosSociedad($cve) {
        try {
            $select = $this->_db->select()
                    ->from(array("s" => "cmrfc"), array("NOMBRE AS nombre", "RFC as rfc"))
                    ->where("s.CLAVE = ?", $cve);
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    protected function _banco($pedimento) {
        try {
            $select = $this->_db->select()
                    ->from(array("s" => "saiban"), array("FIRMA AS firmaBanco", "NOOPE AS operacion", "EFECTIVO AS efectivo", "CONTRIBUCI as contribuciones", "PAT_PAGO AS patente", "CUENTA AS cuenta"))
                    ->joinLeft(array("b" => "sm3banco"), "s.BANCO = b.CLAVE", array("BANCO AS nombreBanco"))
                    ->where("s.DOCTO = ?", $pedimento);
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    protected function _facturasSimplicado($referencia) {
        try {
            $select = $this->_db->select()
                    ->from(array("s" => "sm3fact"), array("ACUSECOVE as cove"))
                    ->where("s.NUM_REF = ?", $referencia)
                    ->where("s.ACUSECOVE IS NOT NULL")
                    ->where("s.ACUSECOVE <> ' '")
                    ->order("s.ACUSECOVE ASC");
            $stmt = $this->_db->fetchAll($select);
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    protected function _contenedores($referencia) {
        try {
            $select = $this->_db->select()
                    ->from(array("s" => "SM3CONT"), array("IDCONT as numContenedor", "TIPCONT as tipoContenedor"))
                    ->where("s.NUM_REF = ?", $referencia);
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function transportes($referencia) {
        try {
            $sql = $this->_db->select()
                    ->from(array("s" => "SM3TRANS"), array("IDETRAN as placas", "LIN_AEREA AS transportista", "PAISORI AS pais", "DIRTRANS as domicilio"))
                    ->where("s.NUM_REF = ?", $referencia);
            $stmt = $this->_db->fetchAll($sql);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    protected function _transportes($referencia) {
        try {
            $select = $this->_db->select()
                    ->from(array("s" => "SM3TRANS"), array("IDETRAN as placas", "LIN_AEREA AS transportista", "PAISORI AS pais", "DIRTRANS as domicilio"))
                    ->where("s.NUM_REF = ?", $referencia);
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
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
        }
    }

}
