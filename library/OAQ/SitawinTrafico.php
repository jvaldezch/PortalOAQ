<?php

/**
 * Description of EmailNotifications
 *
 * @author Jaime E. Valdez <jvaldezch@gmail.com>
 */
class OAQ_SitawinTrafico {

    protected $_db;

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
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function pedimentoPagados($fechaIni, $fechaFin) {
        try {
            $sql = $this->_db->select()
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
                    ->where("p.FEC_PAG >= ?", $fechaIni)
                    ->where("p.FEC_PAG <= ?", $fechaFin)
                    ->where("(p.FIRMA IS NOT NULL AND p.FIRMA <> '' AND b.FIRMA IS NOT NULL)");
            $stmt = $this->_db->fetchAll($sql);
            if ($stmt) {
                return $stmt;
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
     * @param string $referencia
     * @param type $cveCliente
     * @param type $tipoOperacion
     * @return boolean|array
     * @throws Exception
     */
    protected function _proveedor($referencia, $numFactura) {
        try {
            $sql = $this->_db->select()
                    ->distinct()
                    ->from(array("i" => "sm3fact"), array("CVEPROV as cveProveedor"))
                    ->where("i.NUM_REF = ?", $referencia)
                    ->where("i.NUMFAC = ?", $numFactura);
            $stmt = $this->_db->fetchRow($sql);
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
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

    /**
     * 
     * @param string $cveProveedor
     * @return type
     * @throws Exception
     */
    protected function _obtenerDatosProveedor($cveProveedor) {
        try {
            $sql = $this->_db->select()
                    ->from(array("s" => "cmpro"), array(
                        "NUM_TAX as taxId",
                        "NOMPRO as nomProveedor",
                        "DIRCALLE AS calle",
                        "DIRNUMEXT AS numExterior",
                        "DIRNUMINT AS numInterior",
                        "DIRMUNI as municipio",
                        "DIRPAIS AS pais",
                        "CP AS codigoPostal")
                    )
                    ->where("s.CVE_PRO = ?", $cveProveedor)
                    ->limit(1);
            $stmt = $this->_db->fetchRow($sql);
            if ($stmt) {
                return $stmt;
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
     * @param string $referencia
     * @param type $cveCliente
     * @param type $tipoOperacion
     * @return boolean|array
     * @throws Exception
     */
    protected function _destinatario($referencia, $numFactura) {
        try {
            $sql = $this->_db->select()
                    ->distinct()
                    ->from(array("i" => "sm3fact"), array("CVEPROV as cveProveedor"))
                    ->where("i.NUM_REF = ?", $referencia)
                    ->where("i.NUMFAC = ?", $numFactura);
            $stmt = $this->_db->fetchRow($sql);
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
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

    /**
     * 
     * @param string $cveProveedor
     * @return type
     * @throws Exception
     */
    protected function _obtenerDatosDestinatario($cveProveedor) {
        try {
            $sql = $this->_db->select()
                    ->from(array("s" => "cmdest"), array("NUM_TAX as taxId", "NOMPRO as nomProveedor", "DIRCALLE AS calle", "DIRNUMEXT AS numExterior", "DIRNUMINT AS numInterior", "DIRMUNI as municipio", "DIRPAIS AS pais", "CP AS codigoPostal"))
                    ->where("s.CVE_PRO = ?", $cveProveedor)
                    ->limit(1);
            $stmt = $this->_db->fetchRow($sql);
            if ($stmt) {
                return $stmt;
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

    /**
     * 
     * @param string $referencia
     * @param string $factura
     * @return boolean
     * @throws Exception
     */
    protected function _productos($referencia, $factura) {
        try {
            $sql = $this->_db->select()
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
            $stmt = $this->_db->fetchAll($sql);
            if ($stmt) {
                $data = array();
                $prev = $this->_previo($referencia);
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
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param int $pedimento
     * @param string $factura
     * @return boolean
     * @throws Exception
     */
    protected function _productosConsolidado($pedimento, $factura) {
        try {
            $sql = $this->_db->select()
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
            $stmt = $this->_db->fetchAll($sql);
            if ($stmt) {
                return $stmt;
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
     * @param string $referencia
     * @return type
     * @throws Exception
     */
    protected function _previo($referencia) {
        try {
            $sql = $this->_db->select()
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
            $stmt = $this->_db->fetchRow($sql);
            if ($stmt) {
                return $stmt;
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
     * @param string $referencia
     * @return array|null
     * @throws Exception
     */
    public function obtenerFacturas($referencia) {
        try {
            $sql = $this->_db->select()
                    ->from(array("f" => "SM3FACT"), array("f.NUMFAC AS numFactura", "f.ACUSECOVE AS cove", "f.CVEPROV AS cveProveedor", new Zend_Db_Expr("CONVERT(varchar, f.FECFAC, 111) AS fechaFactura")))
                    ->where("f.NUM_REF = ?", $referencia);
            $stmt = $this->_db->fetchAll($sql);
            if (count($stmt) > 0) {
                return $stmt;
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
     * @param int $pedimento
     * @return array|null
     * @throws Exception
     */
    public function obtenerFacturasRemesas($pedimento) {
        try {
            $sql = $this->_db->select()
                    ->from(array("f" => "SM3CONFA"), array("f.NUM_FAC AS numFactura", "f.CVEPROV AS cveProveedor", new Zend_Db_Expr("CONVERT(varchar, f.FEC_FAC, 111) AS fechaFactura")))
                    ->joinLeft(array("c" => "SM3CONOP"), "c.NUM_PED = f.NUM_PED AND c.NUM_OPE = f.NUM_OPE", array("c.NOCOVE as cove"))
                    ->where("f.NUM_PED = ?", $pedimento);
            $stmt = $this->_db->fetchAll($sql);
            if ($stmt) {
                return $stmt;
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
     * @param string $referencia
     * @param string $numFactura
     * @return array|null
     * @throws Exception
     */
    public function infoBasicaFactura($referencia, $numFactura) {
        try {
            $sql = $this->_db->select()
                    ->from(array("p" => "sm3ped"), array(""))
                    ->joinLeft(array("f" => "sm3fact"), "f.NUM_REF = p.NUM_REF", array("f.NUMFAC AS numFactura", "f.VALEXT AS valorMonExt", "f.MONFAC AS moneda", new Zend_Db_Expr("CASE WHEN P.IMP_EXP = 1 THEN (SELECT TOP 1 C.NOMPRO FROM CMPRO C WHERE C.CVE_PRO = F.CVEPROV) ELSE (SELECT TOP 1 C.NOMPRO FROM CMDEST C WHERE C.CVE_PRO = F.CVEPROV) END AS proveedor"), "ACUSECOVE as cove"))
                    ->where("p.NUM_REF = ?", $referencia)
                    ->where("f.NUMFAC = ?", $numFactura);
            $stmt = $this->_db->fetchRow($sql);
            if (count($stmt)) {
                return $stmt;
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
     * @param string $referencia
     * @param string $numFactura
     * @param string $tipoOperacion
     * @return boolean
     * @throws Exception
     */
    public function factura($referencia, $numFactura, $tipoOperacion) {
        try {
            $sql = $this->_db->select()
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
            $stmt = $this->_db->fetchRow($sql);
            if ($stmt) {
                if ($tipoOperacion === "TOCE.IMP") {
                    $stmt["proveedor"] = $this->_proveedor($referencia, $numFactura);
                } else {
                    $stmt["destinatario"] = $this->_destinatario($referencia, $numFactura);
                }
                $stmt["productos"] = $this->_productos($referencia, $numFactura);
                return $stmt;
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
     * @param string $referencia
     * @param int $pedimento
     * @param string $numFactura
     * @param string $tipoOperacion
     * @return boolean
     * @throws Exception
     */
    public function facturaConsolidado($referencia, $pedimento, $numFactura, $tipoOperacion) {
        try {
            $sql = $this->_db->select()
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
            $stmt = $this->_db->fetchRow($sql);
            if ($stmt) {
                if ($tipoOperacion == "TOCE.IMP") {
                    $arr = $this->_proveedor($referencia, $numFactura);
                    if (isset($arr["taxId"])) {
                        $stmt["proveedor"] = $arr;
                    } else {
                        $arr = $this->_destinatario($referencia, $numFactura);
                        if (isset($arr["taxId"])) {
                            $stmt["proveedor"] = $arr;
                        }
                    }
                } else {
                    $arr = $this->_destinatario($referencia, $numFactura);
                    if (isset($arr["taxId"])) {
                        $stmt["destinatario"] = $this->_destinatario($referencia, $numFactura);
                    }
                }
                $stmt["productos"] = $this->_productosConsolidado($pedimento, $numFactura);
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function encabezado($rfc, $fechaIni, $fechaFin) {
        try {
            $init = date("Y/m/d", strtotime($fechaIni));
            $end = date("Y/m/d", strtotime($fechaFin));
            $fileds = [
                new Zend_Db_Expr("RIGHT(YEAR(p.FEC_PAG),2) + '-' + p.ADUANAD + '-' + CAST(p.PATENTE AS VARCHAR(4)) + '-' + CAST(p.NUM_PED AS VARCHAR(7)) AS operacion"),
                new Zend_Db_Expr("CASE P.IMP_EXP WHEN 1 THEN 'IMP' ELSE 'EXP' END AS tipoOperacion"),
                "p.PATENTE AS patente",
                "p.ADUANAD AS aduana",
                "p.NUM_PED AS pedimento",
                "p.NUM_REF AS trafico",
                "p.MEDTRAS AS transporteEntrada",
                "p.MEDTRAA AS transporteArribo",
                "p.MEDTRAE AS transporteSalida",
                new Zend_Db_Expr("REPLACE(CONVERT(VARCHAR(10), p.FEC_ENT, 111), '/', '-') AS fechaEntrada"),
                new Zend_Db_Expr("REPLACE(CONVERT(VARCHAR(10), p.FEC_PAG, 111), '/', '-') AS fechaPago"),
                "p.FIRMA AS firmaValidacion",
                "b.FIRMA AS firmaBanco",
                "p.TIP_CAM AS tipoCambio",
                "p.CVEPEDIM AS cvePed",
                "p.REGIMEN AS regimen",
                "p.ADUANAE AS AduanaEntrada",
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
            ];
            $sql = $this->_db->select()
                    ->from(["p" => "sm3ped"], $fileds)
                    ->joinLeft(["b" => "saiban"], "p.NUM_PED = b.DOCTO", [""])
                    ->where("p.NUM_PED <> 0 AND p.FIRMA <> '' AND p.FIRMA IS NOT NULL AND b.FIRMA IS NOT NULL")
                    ->where("p.RFCCTE = ?", $rfc)
                    ->where("p.FEC_PAG BETWEEN '{$init}' AND '{$end}'");
            $stmt = $this->_db->fetchAll($sql);
            if (count($stmt)) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function anexo($rfc, $fechaIni, $fechaFin) {
        try {
            $init = date("Y/m/d", strtotime($fechaIni));
            $end = date("Y/m/d", strtotime($fechaFin));
            $fileds = [
                new Zend_Db_Expr("RIGHT(YEAR(p.FEC_PAG),2) + '-' + p.ADUANAD + '-' + CAST(p.PATENTE AS VARCHAR(4)) + '-' + CAST(p.NUM_PED AS VARCHAR(7)) AS operacion"),
                new Zend_Db_Expr("CASE P.IMP_EXP WHEN 1 THEN 'IMP' ELSE 'EXP' END AS tipoOperacion"),
                "p.PATENTE AS patente",
                "p.ADUANAD AS aduana",
                "p.NUM_PED AS pedimento",
                "p.NUM_REF AS trafico",
                "p.MEDTRAS AS transporteEntrada",
                "p.MEDTRAA AS transporteArribo",
                "p.MEDTRAE AS transporteSalida",
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
                new Zend_Db_Expr("CASE WHEN CAST((SELECT TOP 1 PREV.PATORI FROM SM3PREV AS PREV WHERE PREV.NUM_REF = p.NUM_REF) AS Int) = 0 THEN '' ELSE (SELECT TOP 1 PREV.PATORI FROM SM3PREV AS PREV WHERE PREV.NUM_REF = p.NUM_REF) END AS patenteOrig"),
                new Zend_Db_Expr("CASE WHEN CAST((SELECT TOP 1 PREV.ADUORI FROM SM3PREV AS PREV WHERE PREV.NUM_REF = p.NUM_REF) AS Int) = 0 THEN '' ELSE (SELECT TOP 1 PREV.ADUORI FROM SM3PREV AS PREV WHERE PREV.NUM_REF = p.NUM_REF)END AS aduanaOrig"),
                new Zend_Db_Expr("CASE WHEN CAST((SELECT TOP 1 PREV.PEDORI FROM SM3PREV AS PREV WHERE PREV.NUM_REF = p.NUM_REF) AS Int) = 0 THEN '' ELSE (SELECT TOP 1 PREV.PEDORI FROM SM3PREV AS PREV WHERE PREV.NUM_REF = p.NUM_REF) END AS pedimentoOrig"),
            ];
            $sql = $this->_db->select()
                    ->from(["p" => "sm3ped"], $fileds)
                    ->joinLeft(["f" => "sm3fact"], "f.NUM_REF = p.NUM_REF", [""])
                    ->joinLeft(["fr" => "cm3fra"], "fr.NUM_REF = f.NUM_REF AND f.NUMFAC = fr.FACTFRA", [""])
                    ->joinLeft(["b" => "saiban"], "p.NUM_PED = b.DOCTO", [""])
                    ->where("p.NUM_PED <> 0 AND p.FIRMA <> '' AND p.FIRMA IS NOT NULL AND b.FIRMA IS NOT NULL")
                    ->where("p.RFCCTE = ?", $rfc)
                    ->where("p.FEC_PAG BETWEEN '{$init}' AND '{$end}'");
            $stmt = $this->_db->fetchAll($sql);
            if (count($stmt)) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function infoPedimentoBasicaReferencia($referencia) {
        try {
            $sql = $this->_db->select()
                    ->from("sm3ped", array(
                        "NUM_PED AS pedimento",
                        "NUM_REF AS referencia",
                        "IMP_EXP AS tipoOperacion",
                        "TIP_CAM AS tipoCambio",
                        "RFCCTE AS rfcCliente",
                        "CVEPEDIM AS cvePedimento",
                        "REGIMEN AS regimen",
                        "VALADUANA AS valorAduana",
                        "SUB AS subdivision",
                        "CONSOLR AS consolidado",
                        "RECTIF AS rectificacion",
                        "FEC_ENT AS fechaEntrada",
                        "FECALT AS fechaAlta",
                        "FECMOD AS fechaModificaciom",
                        "PESBRU AS pesoBruto",
                        "BULTOS AS bultos"
                    ))
                    ->where("NUM_REF = ?", trim($referencia));
            $stmt = $this->_db->fetchRow($sql);
            if ($stmt) {
                $stmt["guias"] = $this->_guias($referencia);
                $stmt["facturas"] = $this->_facturasSimplicado($referencia);
                $stmt["candados"] = $this->_candados($referencia);
                $stmt["transportes"] = $this->_transportes($referencia);
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    protected function _candados($referencia) {
        try {
            $sql = $this->_db->select()
                    ->from(array("c" => "cmcand"), array("c.NUMERO AS numero", "c.COLOR AS color"))
                    ->where("c.NUM_REF = ?", $referencia);
            $stmt = $this->_db->fetchAll($sql);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    protected function _transportes($referencia) {
        try {
            $sql = $this->_db->select()
                    ->from(array("s" => "SM3TRANS"), array("IDETRAN as placas", "LIN_AEREA AS transportista", "PAISORI AS pais", "DIRTRANS as domicilio"))
                    ->where("s.NUM_REF = ?", $referencia);
            $stmt = $this->_db->fetchRow($sql);
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

    public function verificarEdoc($referencia, $edoc) {
        try {
            $sql = $this->_db->select()
                    ->from("SM3CASOS")
                    ->where("NUM_REF = ?", $referencia)
                    ->where("TIPCAS = 'ED'")
                    ->where("IDCASO = ?", $edoc);
            $stmt = $this->_db->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $e) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $e->getMessage());
        } catch (Exception $e) {
            throw new Exception("Exception on " . __METHOD__ . " : " . $e->getMessage());
        }
    }

    public function folioEdoc($referencia) {
        try {
            $sql = $this->_db->select()
                    ->from("SM3CASOS")
                    ->where("NUM_REF = ?", $referencia)
                    ->where("(SUB = 0 AND ORDEN = 0)")
                    ->order("FOLIO DESC")
                    ->limit(1);
            $stmt = $this->_db->fetchRow($sql);
            if ($stmt) {
                return (int) $stmt["FOLIO"] + 1;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function actualizarEdocEnPedimento($referencia, $folio, $edoc) {
        try {
            $arr = array(
                "NUM_REF" => $referencia,
                "SUB" => 0,
                "ORDEN" => 0,
                "TIPCAS" => "ED",
                "IDCASO" => $edoc,
                "IDCASO2" => "",
                "IDCASO3" => "",
                "FOLIO" => $folio,
            );
            $stmt = $this->_db->insert("SM3CASOS", $arr);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function verificarPagoPedimento($referencia, $pedimento) {
        try {
            $sql = $this->_db->select()
                    ->from(array("p" => "SM3PED"), array("FIRMA"))
                    ->joinLeft(array("b" => "SAIBAN"), "p.NUM_PED = b.DOCTO", array("FIRMA AS FIRMABANCO"))
                    ->where("p.NUM_REF = ?", $referencia)
                    ->where("p.NUM_PED = ?", $pedimento);
            $stmt = $this->_db->fetchRow($sql);
            if ($stmt) {
                return $stmt["FIRMABANCO"];
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function verificarCoveEnFactura($referencia, $numfac) {
        try {
            $sql = $this->_db->select()
                    ->from("SM3FACT", array("ACUSECOVE"))
                    ->where("NUM_REF = ?", $referencia)
                    ->where("NUMFAC = ?", $numfac);
            $stmt = $this->_db->fetchRow($sql);
            if ($stmt) {
                return $stmt["ACUSECOVE"];
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
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
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function buscarPedimento($pedimento) {
        try {
            $sql = $this->_db->select()
                    ->from(array("p" => "SM3PED"), array("NUM_REF", "NUM_PED", "PATENTE", new Zend_Db_Expr("ADUANAD + SECCDES AS ADUANA"), "USUMOD", "CONSOLR", "IMP_EXP", "CVEPEDIM", "REGIMEN", "RECTIF", "TIP_CAM", "FIRMA"))
                    ->joinLeft(array("b" => "SAIBAN"), "p.NUM_PED = b.DOCTO", array("FIRMA AS firmaBanco", "NOOPE"))
                    ->where("p.NUM_PED = ?", $pedimento);
            $stmt = $this->_db->fetchRow($sql);
            if ($stmt) {
                return array(
                    "patente" => $stmt["PATENTE"],
                    "aduana" => $stmt["ADUANA"],
                    "pedimento" => $stmt["NUM_PED"],
                    "referencia" => $stmt["NUM_REF"],
                    "usuario" => $stmt["USUMOD"],
                    "consolidado" => (trim($stmt["CONSOLR"]) == "S") ? true : null,
                    "tipoOperacion" => $stmt["IMP_EXP"],
                    "cvePedimento" => $stmt["CVEPEDIM"],
                    "regimen" => $stmt["REGIMEN"],
                    "rectificacion" => $stmt["RECTIF"],
                    "tipoCambio" => $stmt["TIP_CAM"],
                    "firmaValidacion" => $stmt["FIRMA"],
                    "firmaBanco" => $stmt["firmaBanco"],
                    "operacion" => $stmt["NOOPE"],
                );
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function buscarGuia($guia) {
        try {
            $sql = $this->_db->select()
                    ->from(array("g" => "sm3guia"), array("NUM_REF as referencia"))
                    ->joinLeft(array("t" => "sm3ped"), "g.NUM_REF = t.NUM_REF", array("t.NUM_PED as pedimento", "t.RFCCTE AS rfcCliente", "t.PATENTE AS patente", new Zend_Db_Expr("t.ADUANAD + t.SECCDES AS aduana")))
                    ->where("REPLACE(g.NUMGUIA,' ','') LIKE ?", "%" . $guia . "%");
            $stmt = $this->_db->fetchRow($sql);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    protected function _guias($referencia) {
        try {
            $sql = $this->_db->select()
                    ->distinct()
                    ->from(array("s" => "SM3GUIA"), array("IDGUIA AS tipoGuia", "NUMGUIA as guia"))
                    ->where("s.NUM_REF = ?", $referencia);
            $stmt = $this->_db->fetchAll($sql);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    protected function _facturasSimplicado($referencia) {
        try {
            $sql = $this->_db->select()
                    ->from(array("f" => "sm3fact"), array(
                        "f.NUMFAC AS numFactura",
                        "f.VALDLS AS valorFacturaUsd",
                        "f.VALEXT AS valorMonExt",
                        "f.MONFAC AS moneda",
                        new Zend_Db_Expr("CASE WHEN P.IMP_EXP = 1 THEN (SELECT TOP 1 C.NOMPRO FROM CMPRO C WHERE C.CVE_PRO = F.CVEPROV) ELSE (SELECT TOP 1 C.NOMPRO FROM CMDEST C WHERE C.CVE_PRO = F.CVEPROV) END AS proveedor"),
                        "f.ACUSECOVE as cove"
                    ))
                    ->joinLeft(array("p" => "sm3ped"), "p.NUM_REF = f.NUM_REF", array(""))
                    ->where("f.NUM_REF = ?", $referencia);
            $stmt = $this->_db->fetchAll($sql);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function informacionDePago($referencia) {
        try {
            $sql = $this->_db->select()
                    ->from(array("p" => "sm3ped"), array(new Zend_Db_Expr("CONVERT(VARCHAR(30), p.FEC_ENT, 126) AS fechaEntrada"), new Zend_Db_Expr("CONVERT(VARCHAR(30), P.FEC_PAG, 126) AS fechaPago"), "p.FIRMA AS firmaValidacion", "p.CONSOLR AS consolidado", "p.RECTIF AS rectificacion"))
                    ->joinLeft(array("b" => "saiban"), "p.NUM_PED = b.DOCTO", array("b.FIRMA AS firmaBanco"))
                    ->where("p.NUM_REF = ?", $referencia);
            $stmt = $this->_db->fetchRow($sql);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function proveedoresFacturas($referencia) {
        try {
            $cfact = $this->_cantidadFactura($referencia);
            $cpart = $this->_cantidadPartes($referencia);
            return array(
                "cantidadFacturas" => $cfact,
                "cantidadPartes" => $cpart,
                "facturas" => ($cfact > 1) ? "VARIAS" : $this->_obtenerFactura($referencia),
                "proveedores" => ($cfact > 1) ? "VARIOS" : $this->_obtenerProveedor($referencia, $this->_obtenerFactura($referencia)),
            );
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    protected function _obtenerProveedor($referencia, $numFactura) {
        try {
            $sql = $this->_db->select()
                    ->distinct()
                    ->from(array("f" => "sm3ped"), array("IMP_EXP"))
                    ->where("f.NUM_REF = ?", $referencia);
            $stmt = $this->_db->fetchRow($sql);
            if ($stmt) {
                if ((int) $stmt["IMP_EXP"] == 1) {
                    $prov = $this->_proveedor($referencia, $numFactura);
                    return $prov["nomProveedor"];
                } else {
                    $prov = $this->_destinatario($referencia, $numFactura);
                    return $prov["nomProveedor"];
                }
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    protected function _obtenerFactura($referencia) {
        try {
            $sql = $this->_db->select()
                    ->distinct()
                    ->from(array("f" => "sm3fact"), array("NUMFAC"))
                    ->where("f.NUM_REF = ?", $referencia);
            $stmt = $this->_db->fetchRow($sql);
            if ($stmt) {
                return $stmt["NUMFAC"];
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    protected function _cantidadFactura($referencia) {
        try {
            $sql = $this->_db->select()
                    ->distinct()
                    ->from(array("f" => "sm3fact"), array("count(*) as total"))
                    ->where("f.NUM_REF = ?", $referencia);
            $stmt = $this->_db->fetchRow($sql);
            if ($stmt) {
                return $stmt["total"];
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    protected function _cantidadPartes($referencia) {
        try {
            $sql = $this->_db->select()
                    ->distinct()
                    ->from(array("f" => "cm3fra"), array("count(*) as total"))
                    ->where("f.NUM_REF = ?", $referencia);
            $stmt = $this->_db->fetchRow($sql);
            if ($stmt) {
                return $stmt["total"];
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
            $result = $this->_db->fetchRow($select);
            if ($result) {
                $result["facturas"] = $this->wsFacturasPedimento($result["referencia"]);
                $result["archivo"] = $this->ultimoArchivoValidacion($pedimento);
                return $result;
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
            $result = $this->_db->fetchAll($select);
            if ($result) {
                $data = array();
                foreach ($result as $item) {
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
            $result = $this->_db->fetchAll($select);
            if ($result) {
                $data = array();
                $prev = $this->wsPrevio($referencia);
                foreach ($result as $item) {
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
            $result = $this->_db->fetchRow($select);
            if ($result) {
                return $result;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " >> " . $e->getMessage());
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
            $result = $this->_db->fetchAll($select);
            if ($result) {
                return $result;
            }
            return false;
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
            $result = $this->_db->fetchRow($select);
            if ($result) {
                return array(
                    "archivo" => $result["ARCHIVO"] . "." . $result["JULIANO"],
                    "referencia" => $result["NUM_REF"]
                );
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function buscarClienteClave($rfc) {
        try {
            
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 1 fecha Entrada
     * 2 fech Pago 
     * 3 fecha pago original
     * @param type $referencia
     * @param type $fecha
     */
    public function insertFechas($referencia, $tipoFecha, $fecha) {
        $arr = array(
            "NUM_REF" => $referencia,
            "SUB" => 0,
            "TIPFEC" => $tipoFecha,
            "FECHA" => $fecha . " 00:00:00.000",
        );
        return "INSERT INTO SM3FECHA ([NUM_REF], [SUB], [TIPFEC], [FECHA]) VALUES ('{$referencia}', '0', '{$tipoFecha}', '{$fecha} 00:00:00.000');";
    }

    public function insertPedimento($patente, $pedimento, $referencia, $cvePedimento, $regimen, $cveCliente, $rfcCliente, $tipoCambio, $tipoFecha, $fecha, $regla, $usuario) {
        $arr = array(
            "NUM_REF" => $referencia,
            "SUB" => '0',
            "IMP_EXP" => '1',
            "FEC_ENT" => $fecha . '00:00:00.000',
            "TIP_CAM" => $tipoCambio,
            "MEDTRAS" => '4',
            "MEDTRAA" => '4',
            "MEDTRAE" => '4',
            "CVE_IMP" => $cveCliente,
            "RFCCTE" => $rfcCliente,
            "CURPCTE" => NULL,
            "CVEPEDIM" => $cvePedimento,
            "REGIMEN" => $regimen,
            "CONSOLR" => 'N',
            "CALCEXT" => '1',
            "FECENTAL" => NULL,
            "TIPCEXT" => '0',
            "VALFACEN" => NULL,
            "RECTIF" => 'N',
            "BASSEGS" => '0',
            "SEGUROS" => '0',
            "SEGMON" => NULL,
            "SEGEQ" => '0',
            "SEGEMIL" => 'N',
            "FLETES" => '0',
            "FLEMON" => NULL,
            "FLEEQ" => '0',
            "FLEEMIL" => 'N',
            "EMBALAJ" => '0',
            "EMBMON" => NULL,
            "EMBEQ" => '0',
            "EMBEMIL" => 'N',
            "OTROINC" => '0',
            "OTRMON" => NULL,
            "OTREQ" => '0',
            "OTREMIL" => 'N',
            "ADUANAD" => '64',
            "SECCDES" => '0',
            "ADUANAE" => '47',
            "SECCENT" => '0',
            "DOMER" => '9',
            "PESBRU" => '0',
            "BULTOS" => '0',
            "NUMVEH" => '1',
            "EMBPARC" => 'N',
            "MARYNUM" => 'S/M,S/N',
            "DEDUCIB" => '0',
            "IDMON" => NULL,
            "IDEQ" => '0',
            "IDEMIL" => 'N',
            "MANDAT" => '1',
            "CANCOL" => NULL,
            "CANNUM" => NULL,
            "DTA" => '260',
            "MILLCANT" => 'C',
            "DTA_FP" => '0',
            "DTA_TOT" => '0',
            "DTA_TLADI" => '0',
            "DTA_FPADI" => '0',
            "DTAMIN" => 'N',
            "FACINPC" => '0',
            "DTA_ENBASE" => 'S',
            "VALADUANA" => '0',
            "FACAJU" => '0',
            "IGIE_TOT" => '0',
            "IGIO_TOT" => '0',
            "CC1_TOT" => '0',
            "CC1_FP" => '0',
            "CC2_TOT" => '0',
            "CC2_FP" => '0',
            "IVA1_TOT" => '0',
            "IVA1_FP" => '0',
            "IVA2_TOT" => '0',
            "IVA2_FP" => '0',
            "ISAN_TOT" => '0',
            "ISAN_FP" => '0',
            "IEPS_TOT" => '0',
            "IEPS_FP" => '0',
            "REC_TOT" => '0',
            "REC_FP" => '0',
            "FACREC" => '0',
            "OTR_TOT" => '0',
            "OTR_FP" => '0',
            "GAR_TOT" => '0',
            "GAR_FP" => '0',
            "MUL_TOT" => '0',
            "MUL_FP" => '0',
            "NUM_PED" => '0',
            "PATENTE" => '3589',
            "FUECON" => 'N',
            "USUALT" => 'EVERARDO',
            "FECALT" => '2017-07-28 12:30:48.000',
            "USUMOD" => 'EVERARDO',
            "FECMOD" => '2017-07-28 12:30:48.000',
            "FEC_PAG" => NULL,
            "DUMMY1" => NULL,
            "REC_TASA" => NULL,
            "INCREME" => NULL,
            "IMPRESO" => NULL,
            "FIRMA" => '',
            "PAGELEC" => NULL,
            "PEDTRAN" => NULL,
            "ALMACEN" => NULL,
            "REEXPED" => NULL,
            "BASCALC" => NULL,
            "ALM_ESP" => NULL,
            "PEDANT" => NULL,
            "CTAGTOS" => NULL,
            "FECEXT" => '1899-12-30 00:00:00.000',
            "DTASEP" => NULL,
            "TIPCENT" => NULL,
            "VALMEDLLS" => '0',
            "VALMN_PAG" => '0',
            "NIVELRE" => NULL,
            "DTI_TOT" => '0',
            "DTI_FP" => '0',
            "NUMVEHDTA" => NULL,
            "FEC_PAGR" => NULL,
            "GENERADO" => NULL,
            "IGIR_TOT" => '0',
            "IGIR_FP" => '0',
            "FEC_RET" => NULL,
            "TC_RET" => NULL,
            "APROB" => NULL,
            "DTACOMP" => '0',
            "IGICOMP" => '0',
            "PRE_TOT" => '0',
            "PRE_FP" => '0',
            "BSS_TOT" => '0',
            "BSS_FP" => '0',
            "INCIMPUS" => '0',
            "FIRMADIG" => '',
            "COMPEURO" => NULL,
            "EUR_TOT" => '0',
            "EUR_FP" => '0',
            "ECI_TOT" => '0',
            "ECI_FP" => '0',
            "ITV_TOT" => '0',
            "ITV_FP" => '0',
            "GENERADOCC" => NULL,
            "FECHAGENCC" => NULL,
            "USUGENCC" => NULL,
            "FIRMACC" => NULL,
            "FOLIOCC" => NULL,
            "DTAXREG" => 'S',
            "PORBAAN" => 'N',
            "VSEGUROS" => '0',
            "VSEGMON" => NULL,
            "VSEGEQ" => '0',
            "VSEGEMIL" => 'N',
            "IGIR_TOT2" => NULL,
            "IGIR_FP2" => NULL,
            "FEC_APER" => NULL,
            "TRANSITO" => 'N',
            "PEDVEH" => 'N',
            "CVE_EMP" => '',
            "FECORIRE" => NULL,
            "CLI_DIV" => NULL,
            "CVE_PREVA" => NULL,
            "SISTEMA" => ' ',
            "MUL2_TOT" => '0',
            "MUL2_FP" => '0',
            "REC2_TOT" => '0',
            "REC2_FP" => '0',
            "ECI2_TOT" => '0',
            "ECI2_FP" => '0',
            "MT1_TOT" => '0',
            "MT1_FP" => '0',
            "MT2_TOT" => '0',
            "MT2_FP" => '0',
            "NUM_DIR" => '0',
            "RESPONS" => 'IHERNANDEZ',
            "VALIDAR" => 'S',
            "ECITOT" => NULL,
            "RFCSOCAG" => 'CQ',
            "FCALAI" => '0',
            "DTAPART" => '100',
            "DTA2_TOT" => '0',
            "DTA2_FP" => '0',
            "FEC_VALIDAC" => NULL,
            "HORA_VALIDAC" => NULL,
            "REGLA" => 'Regla 2.5.1',
            "CTRPARTE" => NULL,
            "FEC_ENORI" => '1899-12-30 00:00:00.000',
            "RECRETOR" => '0',
            "RECRETFP" => '0',
            "REQAUTPR" => NULL,
            "AUTORIZO" => NULL,
            "ID_FERROVIARIO" => NULL,
            "CNT_TOT" => '0',
            "CNT_FP" => '0'
        );
    }

    public function mostrarFacturasImportacion($referencia, $pedimento, $consolidado = null) {
        try {
            $sql = $this->_db->select()
                    ->distinct()
                    ->from(array("p" => "SM3PED"), array("p.PATENTE AS Patente",
                "p.NUM_PED AS Pedimento",
                new Zend_Db_Expr("p.ADUANAD + p.SECCDES AS Aduana"),
                "p.NUM_REF AS Referencia",
                new Zend_Db_Expr("CASE WHEN p.IMP_EXP = 1 THEN 'TOCE.IMP' ELSE 'TOCE.EXP' END AS TipoOperacion"),
                "p.CVE_IMP AS CveImp"
            ));
            if (!isset($consolidado)) {
                $sql->joinLeft(array("f" => "SM3FACT"), "f.NUM_REF = p.NUM_REF", array(
                    "f.NUMFAC AS NumFactura",
                    new Zend_Db_Expr("CONVERT(varchar, f.FECFAC, 111) AS FechaFactura"),
                    "f.CVEPROV AS CvePro",
                    "f.ACUSECOVE AS Cove",
                    "f.ORDENFAC AS OrdenFact",
                ));
            } else {
                $sql->joinLeft(array("f" => "SM3CONFA"), "f.NUM_PED = p.NUM_PED", array(
                    "f.NUM_FAC AS NumFactura",
                    new Zend_Db_Expr("CONVERT(varchar, f.FEC_FAC, 111) AS FechaFactura"),
                    "f.CVEPROV AS CvePro",
                    "f.FACTURACOVE AS Cove",
                    "f.ORDEN_FAC AS OrdenFact",
                ));
            }
            $sql->joinLeft(array("c" => "cmcli"), "c.CVE_IMP = p.CVE_IMP", array(
                        "c.RFC AS CteRfc",
                        "c.NOMCLI AS CteNombre",
                    ))
                    ->joinLeft(array("pr" => "cmpro"), "pr.CVE_PRO = f.CVEPROV", array(
                        "pr.NUM_TAX AS ProTaxID",
                        "pr.NOMPRO AS ProNombre",
            ));
            if (!isset($consolidado)) {
                $sql->where("p.NUM_REF = ?", $referencia)
                        ->where("p.NUM_PED = ?", $pedimento)
                        ->group(array("p.PATENTE", "p.ADUANAD", "p.SECCDES", "p.NUM_PED", "p.NUM_REF", "p.IMP_EXP", "p.CVE_IMP", "f.ACUSECOVE", "f.NUMFAC", "f.FECFAC", "f.ORDENFAC", "f.CVEPROV", "c.RFC", "c.NOMCLI", "pr.NUM_TAX", "pr.NOMPRO"))
                        ->order("f.ORDENFAC ASC");
            } else {
                $sql->where("p.NUM_REF = ?", $referencia)
                        ->where("p.NUM_PED = ?", $pedimento)
                        ->group(array("p.PATENTE", "p.ADUANAD", "p.SECCDES", "p.NUM_PED", "p.NUM_REF", "p.IMP_EXP", "p.CVE_IMP", "f.FACTURACOVE", "f.NUM_FAC", "f.ORDEN_FAC", "f.FEC_FAC", "f.CVEPROV", "c.RFC", "c.NOMCLI", "pr.NUM_TAX", "pr.NOMPRO"))
                        ->order("f.ORDEN_FAC ASC");
            }
            $stmt = $this->_db->fetchAll($sql);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function seleccionarFacturaImportacion($referencia, $pedimento, $numFactura, $tipoCambio, $consolidado = null) {
        try {
            $sql = $this->_db->select()
                    ->from(array("p" => "sm3ped"), array(
                "p.PATENTE AS Patente",
                "p.NUM_PED AS Pedimento",
                new Zend_Db_Expr("p.ADUANAD + p.SECCDES AS Aduana"),
                "p.NUM_REF AS Referencia",
                new Zend_Db_Expr("CASE WHEN p.IMP_EXP = 1 THEN 'TOCE.IMP' ELSE 'TOCE.EXP' END AS TipoOperacion"),
                "p.CVE_IMP AS CveImp"
            ));
            if (!isset($consolidado)) {
                $sql->joinLeft(array("f" => "sm3fact"), "f.NUM_REF = p.NUM_REF", array(
                    "f.NUMFAC AS NumFactura",
                    new Zend_Db_Expr("CONVERT(varchar, f.FECFAC, 111) AS FechaFactura"),
                    "f.CVEPROV AS CvePro",
                    "f.SUB AS Subdivision",
                    "f.ORDENFAC AS OrdenFact",
                    "f.VALDLS AS ValDls",
                    "f.VALEXT AS ValExt",
                    "f.MONFAC AS Divisa",
                ));
            } else {
                $sql->joinLeft(array("f" => "sm3confa"), "f.NUM_PED = p.NUM_PED", array(
                    "f.NUM_FAC AS NumFactura",
                    new Zend_Db_Expr("CONVERT(varchar, f.FEC_FAC, 111) AS FechaFactura"),
                    "f.CVEPROV AS CvePro",
                    "f.ORDEN_FAC AS OrdenFact",
                    "f.VALDLS AS ValDls",
                    "f.MONFAC AS Divisa",
                    "f.VALEXT AS ValExt",
                ));
            }
            $sql->joinLeft(array("c" => "cmcli"), "c.CVE_IMP = p.CVE_IMP", array(
                        "c.RFC AS CteRfc",
                        "c.NOMCLI AS CteNombre",
                        "c.DIRCALLE AS CteCalle",
                        "c.DIRNUMEXT AS CteNumExt",
                        "c.DIRNUMINT AS CteNumInt",
                        "c.DIRCOLONI AS CteColonia",
                        "c.DIRMUNIC AS CteMun",
                        "c.DIRENTFED AS CteEdo",
                        "c.DIRPAIS AS CtePais",
                        "c.CP AS CteCP",
                    ))
                    ->joinLeft(array("pr" => "cmpro"), "pr.CVE_PRO = f.CVEPROV", array(
                        "pr.NUM_TAX AS ProTaxID",
                        "pr.NOMPRO AS ProNombre",
                        "pr.DIRCALLE AS ProCalle",
                        "pr.DIRNUMEXT AS ProNumExt",
                        "pr.DIRNUMINT AS ProNumInt",
                        "pr.DIRCOLONI AS ProColonia",
                        "pr.DIRMUNI AS ProMun",
                        "pr.DIRESTADO AS ProEdo",
                        "pr.DIRPAIS AS ProPais",
                        "pr.CP AS ProCP",
            ));
            if (!isset($consolidado)) {
                $sql->where("p.NUM_REF = '{$referencia}' AND p.NUM_PED = '{$pedimento}' AND f.NUMFAC = '{$numFactura}'");
            } else {
                $sql->where("p.NUM_REF = '{$referencia}' AND p.NUM_PED = '{$pedimento}' AND f.NUM_FAC = '{$numFactura}'");
            }
            $stmt = $this->_db->fetchRow($sql);
            if ($stmt) {
                $stmt["CteRfc"] = preg_replace("!\s+!", " ", trim($stmt["CteRfc"]));
                $stmt["CteNombre"] = preg_replace("!\s+!", " ", trim($stmt["CteNombre"]));
                $stmt["ProTaxID"] = preg_replace("!\s+!", " ", trim($stmt["ProTaxID"]));
                $stmt["ProNombre"] = preg_replace("!\s+!", " ", trim($stmt["ProNombre"]));
                $stmt["ProPais"] = preg_replace("!\s+!", " ", trim($stmt["ProPais"]));
                $stmt["CtePais"] = preg_replace("!\s+!", " ", trim($stmt["CtePais"]));
                $stmt["Observaciones"] = "";
                $stmt["NumParte"] = "";
                $stmt["CertificadoOrigen"] = "0";
                $stmt["NumExportador"] = "";
                $stmt["Manual"] = 0;
                if ($stmt["Divisa"] == "MXP") {
                    $stmt["FactorEquivalencia"] = $stmt["ValDls"] / $stmt["ValExt"];
                } else if ($stmt["Divisa"] == "USD") {
                    $stmt["FactorEquivalencia"] = round((float) ($stmt["ValDls"] / $stmt["ValExt"]), 6);
                } else {
                    $stmt["FactorEquivalencia"] = round((float) ($stmt["ValDls"] / $stmt["ValExt"]), 6);
                }
                if (!isset($consolidado)) {
                    $productos = $this->_obtenerProductos($pedimento, $referencia, $stmt["NumFactura"], $tipoCambio, $stmt["OrdenFact"]);
                    $stmt["Productos"] = $productos;
                } else {
                    $productos = $this->_obtenerProductosConsolidado($pedimento, $referencia, $stmt["NumFactura"], $tipoCambio, $stmt["OrdenFact"]);
                    $stmt["Productos"] = $productos;
                }
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function mostrarFacturasExportacion($referencia, $pedimento, $consolidado = null) {
        try {
            $sql = $this->_db->select()
                    ->distinct()
                    ->from(array("p" => "sm3ped"), array(
                "p.PATENTE AS Patente",
                "p.NUM_PED AS Pedimento",
                new Zend_Db_Expr("p.ADUANAD + p.SECCDES AS Aduana"),
                "p.NUM_REF AS Referencia",
                new Zend_Db_Expr("CASE WHEN p.IMP_EXP = 1 THEN 'TOCE.IMP' ELSE 'TOCE.EXP' END AS TipoOperacion"),
                "p.CVE_IMP AS CveImp"
            ));
            if (!isset($consolidado)) {
                $sql->joinLeft(array("f" => "sm3fact"), "f.NUM_REF = p.NUM_REF", array(
                    "f.NUMFAC AS NumFactura",
                    new Zend_Db_Expr("CONVERT(varchar, f.FECFAC, 111) AS FechaFactura"),
                    "f.CVEPROV AS CvePro",
                    "f.ACUSECOVE AS Cove",
                    "f.ORDENFAC AS OrdenFact",
                ));
            } else {
                $sql->joinLeft(array("f" => "SM3CONFA"), "f.NUM_PED = p.NUM_PED", array(
                    "f.NUM_FAC AS NumFactura",
                    new Zend_Db_Expr("CONVERT(varchar, f.FEC_FAC, 111) AS FechaFactura"),
                    "f.CVEPROV AS CvePro",
                    "f.FACTURACOVE AS Cove",
                    "f.ORDEN_FAC AS OrdenFact",
                ));
            }
            $sql->joinLeft(array("c" => "cmcli"), "c.CVE_IMP = p.CVE_IMP", array(
                        "c.RFC AS CteRfc",
                        "c.NOMCLI AS CteNombre",
                    ))
                    ->joinLeft(array("d" => "cmdest"), "d.CVE_PRO = f.CVEPROV", array(
                        "d.NUM_TAX AS ProTaxID",
                        "d.NOMPRO AS ProNombre",
            ));
            if (!isset($consolidado)) {
                $sql->group(array("p.PATENTE", "p.ADUANAD", "p.SECCDES", "p.NUM_PED", "p.NUM_REF", "p.IMP_EXP", "p.CVE_IMP", "f.ACUSECOVE", "f.NUMFAC", "f.ORDENFAC", "f.FECFAC", "f.CVEPROV", "c.RFC", "c.NOMCLI", "d.NUM_TAX", "d.NOMPRO"))
                        ->order("f.ORDENFAC ASC");
            } else {
                $sql->group(array("p.PATENTE", "p.ADUANAD", "p.SECCDES", "p.NUM_PED", "p.NUM_REF", "p.IMP_EXP", "p.CVE_IMP", "f.FACTURACOVE", "f.NUM_FAC", "f.ORDEN_FAC", "f.FEC_FAC", "f.CVEPROV", "c.RFC", "c.NOMCLI", "d.NUM_TAX", "d.NOMPRO"))
                        ->order("f.ORDEN_FAC ASC");
            }
            $sql->where("p.NUM_REF = ?", $referencia)
                    ->where("p.NUM_PED = ?", $pedimento);
            $stmt = $this->_db->fetchAll($sql);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function seleccionarFacturaExportacion($referencia, $pedimento, $numFactura, $tipoCambio, $consolidado = null) {
        try {
            $sql = $this->_db->select()
                    ->from(array("p" => "sm3ped"), array(
                "p.PATENTE AS Patente",
                "p.NUM_PED AS Pedimento",
                new Zend_Db_Expr("p.ADUANAD + p.SECCDES AS Aduana"),
                "p.NUM_REF AS Referencia",
                new Zend_Db_Expr("CASE WHEN p.IMP_EXP = 1 THEN 'TOCE.IMP' ELSE 'TOCE.EXP' END AS TipoOperacion"),
                "p.CVE_IMP AS CveImp"
            ));
            if (!isset($consolidado)) {
                $sql->joinLeft(array("f" => "sm3fact"), "f.NUM_REF = p.NUM_REF", array(
                    "f.NUMFAC AS NumFactura",
                    new Zend_Db_Expr("CONVERT(varchar, f.FECFAC, 111) AS FechaFactura"),
                    "f.CVEPROV AS CvePro",
                    "f.SUB AS Subdivision",
                    "f.ORDENFAC AS OrdenFact",
                    "f.MONFAC AS Divisa",
                    "f.VALDLS AS ValDls",
                    "f.VALEXT AS ValExt",
                ));
            } else {
                $sql->joinLeft(array("f" => "SM3CONFA"), "f.NUM_PED = p.NUM_PED", array(
                    "f.NUM_FAC AS NumFactura",
                    new Zend_Db_Expr("CONVERT(varchar, f.FEC_FAC, 111) AS FechaFactura"),
                    "f.CVEPROV AS CvePro",
                    "f.ORDEN_FAC AS OrdenFact",
                    "f.VALDLS AS ValDls",
                    "f.MONFAC AS Divisa",
                    "f.VALEXT AS ValExt",
                ));
            }
            $sql->joinLeft(array("c" => "cmcli"), "c.CVE_IMP = p.CVE_IMP", array(
                        "c.RFC AS CteRfc",
                        "c.NOMCLI AS CteNombre",
                        "c.DIRCALLE AS CteCalle",
                        "c.DIRNUMEXT AS CteNumExt",
                        "c.DIRNUMINT AS CteNumInt",
                        "c.DIRCOLONI AS CteColonia",
                        "c.DIRMUNIC AS CteMun",
                        "c.DIRENTFED AS CteEdo",
                        "c.DIRPAIS AS CtePais",
                        "c.CP AS CteCP",
                    ))
                    ->joinLeft(array("d" => "cmdest"), "d.CVE_PRO = f.CVEPROV", array(
                        "d.NOMPRO AS ProNombre",
                        "d.DIRCALLE AS ProCalle",
                        "d.DIRNUMEXT AS ProNumExt",
                        "d.DIRNUMINT AS ProNumInt",
                        "d.DIRCOLONI AS ProColonia",
                        "d.DIRMUNI AS ProMun",
                        "d.DIRESTADO AS ProEdo",
                        "d.DIRPAIS AS ProPais",
                        "d.NUM_TAX AS ProTaxID",
            ));
            if (!isset($consolidado)) {
                $sql->where("p.NUM_REF = '{$referencia}' AND p.NUM_PED = '{$pedimento}' AND f.NUMFAC = '{$numFactura}'");
            } else {
                $sql->where("p.NUM_REF = '{$referencia}' AND p.NUM_PED = '{$pedimento}' AND f.NUM_FAC = '{$numFactura}'");
            }
            $stmt = $this->_db->fetchRow($sql);
            if ($stmt) {
                $stmt["CteRfc"] = preg_replace("!\s+!", " ", trim($stmt["CteRfc"]));
                $stmt["CteNombre"] = preg_replace("!\s+!", " ", trim($stmt["CteNombre"]));
                $stmt["ProTaxID"] = preg_replace("!\s+!", " ", trim($stmt["ProTaxID"]));
                $stmt["ProNombre"] = preg_replace("!\s+!", " ", trim($stmt["ProNombre"]));
                $stmt["ProPais"] = preg_replace("!\s+!", " ", trim($stmt["ProPais"]));
                $stmt["CtePais"] = preg_replace("!\s+!", " ", trim($stmt["CtePais"]));
                $stmt["Observaciones"] = "";
                $stmt["NumParte"] = "";
                $stmt["CertificadoOrigen"] = "0";
                $stmt["NumExportador"] = "";
                $stmt["Manual"] = 0;
                if ($stmt["Divisa"] == "MXP") {
                    $stmt["FactorEquivalencia"] = $stmt["ValDls"] / $stmt["ValExt"];
                } else if ($stmt["Divisa"] == "USD") {
                    $stmt["FactorEquivalencia"] = round((float) ($stmt["ValDls"] / $stmt["ValExt"]), 6);
                } else {
                    $stmt["FactorEquivalencia"] = round((float) ($stmt["ValDls"] / $stmt["ValExt"]), 6);
                }
                if (!isset($consolidado)) {
                    $productos = $this->_obtenerProductos($pedimento, $referencia, $stmt["NumFactura"], $tipoCambio, $stmt["OrdenFact"]);
                    $stmt["Productos"] = $productos;
                } else {
                    $productos = $this->_obtenerProductosConsolidado($pedimento, $referencia, $stmt["NumFactura"], $tipoCambio, $stmt["OrdenFact"]);
                    $stmt["Productos"] = $productos;
                }
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    protected function _obtenerProductos($pedimento, $referencia, $numFactura, $tipoCambio, $ordenFactura) {
        try {
            $sql = $this->_db->select()
                    ->from(array("f" => "CM3FRA"), array(
                        "f.NUM_REF AS REFERENCIA",
                        "f.CODIGO",
                        "f.SUBFRA",
                        new Zend_Db_Expr("f.DESC1 Collate SQL_Latin1_General_CP1253_CI_AI AS DESC1"),
                        "f.ORDEN",
                        "f.MONVAL",
                        "f.VALCOM",
                        "f.VALCEQ",
                        "f.VALMN",
                        "f.VALDLS",
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
                    ))
                    ->where("f.NUM_REF = '{$referencia}' AND f.FACTFRA = '{$numFactura}'");
            $stmt = $this->_db->fetchAll($sql);
            if ($stmt) {
                $arr = array();
                foreach ($stmt as $item) {
                    $mppr = new Vucem_Model_VucemUnidadesMapper();
                    if ($item["MONVAL"] == "USD") {
                        $item["VALDLS"] = $item["VALCOM"];
                        $item["VALCEQ"] = 1.00000;
                        $item["VALMN"] = floor((float) $item["VALCOM"] * $tipoCambio);
                    } else if ($item["MONVAL"] == "MXP") {
                        $item["VALMN"] = floor($item["VALCOM"]);
                        $item["VALDLS"] = round((float) $item["VALCOM"] / (float) $tipoCambio, 4);
                        $item["VALCEQ"] = round((float) $item["VALDLS"] / (float) $item["VALCOM"], 6);
                    } else {
                        $item["VALDLS"] = round((float) $item["VALCOM"] * $item["VALCEQ"], 4);
                        $item["VALCEQ"] = round($item["VALCEQ"], 6);
                        $item["VALMN"] = floor($item["VALCOM"]);
                    }
                    $precioUnitario = (float) $item["VALCOM"] / (float) $item["CANTFAC"];
                    if (isset($item["ORDEN"]) && isset($ordenFactura)) {
                        $obs = $this->getObservations($referencia, $item["ORDEN"], $ordenFactura, $numFactura);
                    }
                    $item["SUB"] = isset($item["SUB"]) ? $item["SUB"] : "0";
                    $item["SUBFRA"] = isset($item["SUBFRA"]) ? $item["SUBFRA"] : null;
                    $item["PREUNI"] = $precioUnitario;
                    $item["UMC_OMA"] = ($item["UMC_OMA"] == null) ? $mppr->getOma($item["UMC"]) : $item["UMC_OMA"];
                    $item["DESC_COVE"] = ($item["DESC_COVE"] == null) ? $item["DESC1"] : $item["DESC1"];
                    $item["OBS"] = (isset($obs["OBS"]) && $obs["OBS"] != NULL && $obs["OBS"] != "") ? $obs["OBS"] : null;
                    $item["MARCA"] = ($obs["MARCA"] != NULL && $obs["MARCA"] != "") ? $obs["MARCA"] : null;
                    $item["MODELO"] = ($obs["MODELO"] != NULL && $obs["MODELO"] != "") ? $obs["MODELO"] : null;
                    $item["SUBMODELO"] = ($obs["SUBMODELO"] != NULL && $obs["SUBMODELO"] != "") ? $obs["SUBMODELO"] : null;
                    $item["NUMSERIE"] = ($obs["NUM_SERIE"] != NULL && $obs["NUM_SERIE"] != "") ? $obs["NUM_SERIE"] : null;
                    $arr[] = $item;
                }
                return $arr;
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    protected function _obtenerProductosConsolidado($pedimento, $referencia, $numFactura, $tipoCambio, $ordenFactura) {
        try {
            $sql = $this->_db->select()
                    ->from(array("p" => "SM3PED"), array())
                    ->joinLeft(array("f" => "SM3CONFA"), "f.NUM_PED = p.NUM_PED", array())
                    ->joinLeft(array("fr" => "SM3CONFR"), "p.NUM_PED = p.NUM_PED", array(
                        "fr.CODIGO",
                        new Zend_Db_Expr("fr.DESC1 Collate SQL_Latin1_General_CP1253_CI_AI AS DESC1"),
                        "fr.ORDEN_FRAC AS ORDEN",
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
                    ->where("p.NUM_PED = {$pedimento} AND fr.NUM_PED = {$pedimento} AND f.NUM_FAC = '{$numFactura}'")
                    ->where("f.ORDEN_FAC = fr.ORDEN_FAC AND f.REMESA = fr.REMESA");
            $stmt = $this->_db->fetchAll($sql);
            if ($stmt) {
                $arr = array();
                foreach ($stmt as $item) {
                    $mppr = new Vucem_Model_VucemUnidadesMapper();
                    if ($item["MONVAL"] == "USD") {
                        $item["VALCOM"] = $item["VALDLS"];
                        $item["VALCEQ"] = round(1.00000, 6);
                        $item["FACTAJU"] = round(1.00000, 6);
                    } else {
                        $item["VALDLS"] = round((float) $item["VALCOM"] * $item["VALCEQ"], 4);
                        $item["VALCEQ"] = round($item["VALCEQ"], 6);
                        $item["VALMN"] = ceil($item["VALCOM"]);
                    }
                    $precioUnitario = round((float) $item["VALCOM"] / (float) $item["CANTFAC"], 6);
                    if (isset($item["ORDEN"]) && isset($ordenFactura)) {
                        $obs = $this->getObservations($referencia, $item["ORDEN"], $ordenFactura, $numFactura);
                    }
                    $item["PREUNI"] = $precioUnitario;
                    $item["UMC_OMA"] = ($item["UMC_OMA"] == null) ? $mppr->getOma($item["UMC"]) : $item["UMC_OMA"];
                    $item["DESC_COVE"] = ($item["DESC_COVE"] == null) ? $item["DESC1"] : $item["DESC1"];
                    if (isset($obs) && !empty($obs)) {
                        $item["OBS"] = (isset($obs["OBS"]) && $obs["OBS"] != NULL && $obs["OBS"] != "") ? $obs["OBS"] : null;
                        $item["MARCA"] = ($obs["MARCA"] != NULL && $obs["MARCA"] != "") ? $obs["MARCA"] : null;
                        $item["MODELO"] = ($obs["MODELO"] != NULL && $obs["MODELO"] != "") ? $obs["MODELO"] : null;
                        $item["SUBMODELO"] = ($obs["SUBMODELO"] != NULL && $obs["SUBMODELO"] != "") ? $obs["SUBMODELO"] : null;
                        $item["NUMSERIE"] = ($obs["NUM_SERIE"] != NULL && $obs["NUM_SERIE"] != "") ? $obs["NUM_SERIE"] : null;
                    }
                    $arr[] = $item;
                }
                return $arr;
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    protected function getObservations($referencia, $orden, $ordenFactura, $numFactura = null) {
        try {
            $sql = $this->_db->select()
                    ->from("cm3obsfr", array("OBS", "MARCA", "MODELO", "SUBMODELO", "NUM_SERIE"))
                    ->where("NUM_REF = ?", $referencia)
                    ->where("ORDEN = ?", $orden)
                    ->where("ORDENFAC = ?", $ordenFactura)
                    ->limit(1);
            if (isset($numFactura)) {
                $sql->where("FACTFRA = ?", $numFactura);
            }
            $stmt = $this->_db->fetchRow($sql);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function reporteIvaProveedores($rfcCliente, $year, $mes) {
        try {
            $sql = $this->_db->select()
                    ->from(array("P" => "SM3PED"), array(
                        new Zend_Db_Expr("RIGHT(YEAR(P.FEC_PAG),2) + '-' + P.ADUANAD + '-' + CAST(P.PATENTE AS VARCHAR(4)) + '-' + CAST(P.NUM_PED AS VARCHAR(7)) AS operacion"),
                        new Zend_Db_Expr("CASE P.IMP_EXP WHEN 1 THEN 'IMP' ELSE 'EXP' END AS impexp"),
                        "P.NUM_REF AS trafico",
                        "P.CVEPEDIM AS cvePedimento",
                        new Zend_Db_Expr("(SELECT TOP 1 D.NUM_TAX FROM CMPRO D WHERE D.CVE_PRO = R.CVE_PRO) AS taxID"),
                        new Zend_Db_Expr("(SELECT TOP 1 D.NOMPRO FROM CMPRO D WHERE D.CVE_PRO = R.CVE_PRO) AS nomProveedor"),
                        "R.ORDEN AS ordenFraccion",
                        "R.CODIGO AS fraccion",
                        "R.DESC1 AS descripcion",
                        "R.VALMN AS valor",
                        "R.IMPOIVA AS iva",
                    ))
                    ->joinInner(array("R" => "SM3FRA"), "R.NUM_REF = P.NUM_REF", array())
                    ->joinInner(array("B" => "SAIBAN"), "B.DOCTO = P.NUM_PED", array())
                    ->where("P.RFCCTE >= ?", $rfcCliente)
                    ->where("YEAR(P.FEC_PAG) = ?", $year)
                    ->where("MONTH(P.FEC_PAG) = ?", $mes)
                    ->where("(P.NUM_PED <> 0 AND P.FIRMA <> '' AND P.FIRMA IS NOT NULL AND B.FIRMA IS NOT NULL) AND P.REGIMEN = 'IMD'")
                    ->order(array("P.PATENTE", "P.ADUANAD", "p.NUM_PED", "P.IMP_EXP", "P.NUM_REF", "R.ORDEN"));
            $stmt = $this->_db->fetchAll($sql);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " >> " . $e->getMessage());
        }
    }

}
