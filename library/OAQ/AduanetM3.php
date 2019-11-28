<?php

/**
 * Description of EmailNotifications
 *
 * @author Jaime E. Valdez <jvaldezch@gmail.com>
 */
class OAQ_AduanetM3 {

    protected $_db;
    protected $_logger;

    function __construct($init = null, $host = null, $username = null, $pwd = null, $dbname = null, $port = null) {
        $this->_logger = Zend_Registry::get("logDb");
        $this->_db = Zend_Db::factory('Pdo_Mysql', array(
                    'host' => $host,
                    'username' => $username,
                    'password' => $pwd,
                    'dbname' => $dbname,
                    'port' => $port,
        ));
    }

    public function wsPedimentoPagados($rfc, $fechaIni, $fechaFin) {
        try {
            $select = $this->_db->select()
                    ->from(array('p' => 'AT001'), array(
                        'p.C001PATEN AS patente',
                        'p.C001NUMPED AS pedimento',
                        'p.C001ADUSEC AS aduana',
                        'p.C001REFPED AS referencia',
                        'p.D001FECPAG AS fechaPago',
                        new Zend_Db_Expr("CASE WHEN p.C001TIPOPE = 1 THEN 'TOCE.IMP' ELSE 'TOCE.EXP' END AS tipoOperacion")
                    ))
                    ->where('p.C001RFCCLI = ?', $rfc)
                    ->where('p.D001FECPAG >= ?', $fechaIni)
                    ->where('p.D001FECPAG <= ?', $fechaFin)
                    ->where("(p.C001FIRELE <> '' AND p.C001RFCSOC = 'OAQ030623UL8')");
            $result = $this->_db->fetchAll($select);
            if ($result) {
                return $result;
            }
            return false;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function wsDetallePedimento($pedimento) {
        try {
            $select = $this->_db->select()
                    ->from(array('p' => 'AT001'), array(
                        'p.C001PATEN AS patente',
                        'p.C001NUMPED AS pedimento',
                        'SUBSTRING(p.C001ADUSEC,1,2) AS aduana',
                        'SUBSTRING(p.C001ADUSEC,-1) AS seccAduana',
                        'p.C001REFPED AS referencia',
                        new Zend_Db_Expr("CASE WHEN p.C001TIPOPE = 1 THEN 'TOCE.IMP' ELSE 'TOCE.EXP' END AS tipoOperacion"),
                        'p.C001REFPED AS trafico',
                        'p.C001MEDTRE AS transporteEntrada',
                        'p.C001MEDTRA AS transporteArribo',
                        'p.C001MEDTRS AS transporteSalida',
                        "p.D001FECEP AS fechaEntrada",
                        "p.D001FECPAG AS fechaPago",
                        'p.C001FIRELE AS firmaValidacion',
                        'p.C001FIRBAN AS firmaBanco',
                        'p.F001TIPCAM AS tipoCambio',
                        'p.C001CVEDOC AS cvePed',
                        'p.C001TIPREG AS regimen',
                        'p.C001ADUSE AS aduanaEntrada',
                        'p.F001VALDOL AS valorDolares',
                        'p.N001VALADU AS valorAduana',
                        'p.N001VALCOM AS valorComercial',
                        'CAST(p.F001FLETES AS DECIMAL(10,4))* CAST(p.F001TIPCAM AS DECIMAL(10,4)) AS fletes',
                        'CAST(p.F001SEGURO AS DECIMAL(10,4))* CAST(p.F001TIPCAM AS DECIMAL(10,4)) AS seguros',
                        'CAST(p.F001EMBALA AS DECIMAL(10,4))* CAST(p.F001TIPCAM AS DECIMAL(10,4)) AS embalajes',
                        'CAST(p.F001OTRINC AS DECIMAL(10,4))* CAST(p.F001TIPCAM AS DECIMAL(10,4)) AS otrosIncrementales',
                        new Zend_Db_Expr("CASE WHEN p.I001TTDTA1 = 0 THEN 0 ELSE (SELECT M.N008IMPCON FROM AT008 AS M WHERE M.C008CVECON = 'DTA' AND M.C008REFPED = p.C001REFPED AND M.C008PATEN = p.C001PATEN AND M.C008ADUSEC = p.C001ADUSEC LIMIT 1) END AS dta"),
                        new Zend_Db_Expr("CASE WHEN (SELECT M.N008IMPCON FROM AT008 AS M WHERE M.C008CVECON = 'IVA' AND M.C008REFPED = p.C001REFPED AND M.C008PATEN = p.C001PATEN AND M.C008ADUSEC = p.C001ADUSEC LIMIT 1) IS NULL THEN 0 ELSE (SELECT M.N008IMPCON FROM AT008 AS M WHERE M.C008CVECON = 'IVA' AND M.C008REFPED = p.C001REFPED AND M.C008PATEN = p.C001PATEN AND M.C008ADUSEC = p.C001ADUSEC LIMIT 1) END AS iva"),
                        new Zend_Db_Expr("CASE WHEN (SELECT M.N008IMPCON FROM AT008 AS M WHERE M.C008CVECON = 'IGI/IGE' AND M.C008REFPED = p.C001REFPED AND M.C008PATEN = p.C001PATEN AND M.C008ADUSEC = p.C001ADUSEC LIMIT 1) IS NULL THEN 0 ELSE (SELECT M.N008IMPCON FROM AT008 AS M WHERE M.C008CVECON = 'IGI/IGE' AND M.C008REFPED = p.C001REFPED AND M.C008PATEN = p.C001PATEN AND M.C008ADUSEC = p.C001ADUSEC LIMIT 1) END AS igi"),
                        new Zend_Db_Expr("CASE WHEN (SELECT M.N008IMPCON FROM AT008 AS M WHERE M.C008CVECON = 'PREV' AND M.C008REFPED = p.C001REFPED AND M.C008PATEN = p.C001PATEN AND M.C008ADUSEC = p.C001ADUSEC LIMIT 1) IS NULL THEN 0 ELSE (SELECT M.N008IMPCON FROM AT008 AS M WHERE M.C008CVECON = 'PREV' AND M.C008REFPED = p.C001REFPED AND M.C008PATEN = p.C001PATEN AND M.C008ADUSEC = p.C001ADUSEC LIMIT 1) END AS prev"),
                        new Zend_Db_Expr("CASE WHEN (SELECT M.N008IMPCON FROM AT008 AS M WHERE M.C008CVECON = 'CNT' AND M.C008REFPED = p.C001REFPED AND M.C008PATEN = p.C001PATEN AND M.C008ADUSEC = p.C001ADUSEC LIMIT 1) IS NULL THEN 0 ELSE (SELECT M.N008IMPCON FROM AT008 AS M WHERE M.C008CVECON = 'CNT' AND M.C008REFPED = p.C001REFPED AND M.C008PATEN = p.C001PATEN AND M.C008ADUSEC = p.C001ADUSEC LIMIT 1) END AS cnt"),
                        new Zend_Db_Expr("(SELECT SUM(M.N008IMPCON) FROM AT008 AS M WHERE M.C008REFPED = p.C001REFPED AND M.C008PATEN = p.C001PATEN AND M.C008ADUSEC = p.C001ADUSEC) AS totalEfectivo"),
                        'p.F001PESO AS pesoBruto',
                        'p.N001NUMBUL AS bultos',
                    ))
                    ->where('p.C001NUMPED = ?', $pedimento);
            $result = $this->_db->fetchRow($select);
            if ($result) {
                $result["facturas"] = $this->wsFacturasPedimento($result["referencia"]);
                return $result;
            }
            return false;
        } catch (Exception $ex) {
            throw new Exception("Exception found " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    protected function wsFacturasPedimento($referencia) {
        try {
            $slam = new OAQ_Slam('192.168.200.5', 'sa', 'adminOAQ123', 'Aduana', 'SqlSrv', 1433);
            $select = $this->_db->select()
                    ->from(array('f' => 'AT005'), array(
                        'f.C005NUMFAC AS numFactura',
                        'f.C005EDOC AS cove',
                        'f.C005IND AS ordenFactura',
                        'f.D005FECFAC AS fechaFactura',
                        'f.C005CVEINC AS incoterm',
                        'f.F005VALDOL AS valorFacturaUsd',
                        'f.F005VALMEX AS valorFacturaMonExt',
                        'f.C005NOMPRO AS nomProveedor',
                        'f.C005IDEPRO AS taxId',
                        'f.C005PAISFA AS paisFactura',
                        'f.C005MONFAC AS divisa',
                        'f.F005FACMEX AS factorMonExt'
                    ))
                    ->where('f.C005REFPED = ?', $referencia);
            $result = $this->_db->fetchAll($select);
            if ($result) {
                $data = array();
                foreach ($result as $item) {
                    $item["partes"] = $slam->wsPartesPedimento($referencia, $item["numFactura"]);
                    $data[] = $item;
                }
                return $data;
            }
            return false;
        } catch (Exception $ex) {
            throw new Exception("Exception found " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function obtenerPedimentos($rfc, $ini, $fin) {
        try {
            $select = "SELECT p.C001PATEN, p.C001ADUSEC, p.C001REFPED, p.C001NUMPED, p.C001TIPOPE, p.C001CVEDOC, p.D001FECPAG, p.C001FIRELE, p.C001FIRBAN, b.C036FIMBAN
                    FROM at001 AS p
                    LEFT JOIN at036 AS b ON b.C036PATEN = p.C001PATEN AND b.C036NUMPED = p.C001NUMPED AND b.C036REFPED = p.C001REFPED
                    WHERE p.C001RFCCLI = '{$rfc}' AND p.D001FECPAG > '{$ini}' AND p.C001FIRELE <> '' AND p.C001RFCSOC = 'OAQ030623UL8';";
            $result = $this->_db->fetchAll($select);
            if ($result) {
                $data = array();
                foreach ($result as $item) {
                    $data[] = array(
                        'patente' => $item["C001PATEN"],
                        'aduana' => $item["C001ADUSEC"],
                        'referencia' => $item["C001REFPED"],
                        'fecha_pago' => $item["D001FECPAG"],
                        'pedimento' => $item["C001NUMPED"],
                        'cve_doc' => $item["C001CVEDOC"],
                        'ie' => $item["C001TIPOPE"],
                        'firma_validacion' => $item["C001FIRELE"],
                        'firma_banco' => $item["C001FIRBAN"],
                    );
                }
                return $data;
            }
            return null;
        } catch (Exception $e) {
            $this->_logger->logEntry(__METHOD__, "ZEND DB EXCEPTION: " . $e->getMessage(), $_SERVER['REMOTE_ADDR'], null);
        }
    }

    public function buscarReferencia($ref) {
        try {
            $Query = "SELECT 
                        p.`C001PATEN` AS Patente,
                        p.`C001ADUSEC` AS Aduana,
                        p.`C001REFPED` AS Referencia,
                        p.`C001NUMPED` AS Pedimento,
                        YEAR(p.`D001FECPAG`) AS 'Year',
                        p.`D001FECPAG` AS Fecha
                    FROM at001 AS p
                    WHERE p.C001REFPED LIKE '{$ref}';";
            $result = $this->_db->fetchAll($Query);
            if ($result) {
                $data = array();
                foreach ($result as $item) {
                    $data[] = array(
                        'year' => $item["Year"],
                        'fecha' => $item["Fecha"],
                        'referencia' => $item["Referencia"],
                        'pedimento' => $item["Pedimento"],
                        'patente' => $item["Patente"],
                        'aduana' => $item["Aduana"],
                    );
                }
                return $data;
            }
            return null;
        } catch (Exception $e) {
            $this->_logger->logEntry(__METHOD__, "ZEND DB EXCEPTION: " . $e->getMessage(), $_SERVER['REMOTE_ADDR'], null);
        }
    }

    public function ejecutarQueryAnexo24($Query) {
        try {
            $result = $this->_db->fetchAll($Query);
            if ($result) {
                return $result;
            }
            return null;
        } catch (Exception $e) {
            $this->_logger->logEntry(__METHOD__, "ZEND DB EXCEPTION: " . $e->getMessage(), $_SERVER['REMOTE_ADDR'], null);
        }
    }

    public function informacionFraccion($referencia, $fraccion) {
        try {
            $Query = "SELECT at019.`C019CVECAS` FROM `at016`
                LEFT JOIN `at019` ON at019.C019REFPED = at016.C016REFPED AND at019.C019FRAC = at016.C016FRAC
                WHERE at016.C016REFPED = '{$referencia}' AND at016.C016FRAC = '{$fraccion}' AND at019.C019CVECAS IN ('TL','PS','EN') GROUP BY at019.`C019CVECAS`;";
            $result = $this->_db->fetchAll($Query);
            if ($result) {
                return $result;
            }
            return null;
        } catch (Exception $e) {
            $this->_logger->logEntry(__METHOD__, "ZEND DB EXCEPTION: " . $e->getMessage(), $_SERVER['REMOTE_ADDR'], null);
        }
    }

    public function informacionFraccionDetalle($referencia, $fraccion) {
        try {
            $Query = "SELECT    
                at019.`C019FRAC`
                ,at019.`C019CVECAS`
                ,at016.`C016DESMER`
                ,at016.`F016PREPAG`
                ,at016.`F016VALDOL`
                ,at016.`N016VALADU`
                ,at016.`F016PREUNI`
                ,at016.`N016VALCOM`
                ,at016.`F016CANUMC`
                ,at016.`C016UNIUMC`
                ,at016.`F016CANUMT`
                ,at016.`C016UNIUMT`
                ,at016.`N016VALAGR`
                ,at016.`C016VINCU`
                ,at016.`C016METVAL`
                ,at016.`C016CODPRO`
                ,at016.`C016MARCA`
                ,at016.`C016MODMER`
                ,at016.`C016ENTDES`
                ,at016.`C016PAISCV`
                ,at016.`C016ENTCOM`
                ,at016.`C016ENTVEN`
                ,at016.`F016TASDTA`
                ,at016.`C016TIPDTA`
                ,at016.`F016TASADV`
                ,at016.`F016TASADN`
                ,at016.`C016TIPADV`
                ,at016.`F016TASADE`
                ,at016.`C016TIPADE`
                ,at016.`F016TASAD3`
                ,at016.`C016TIPAD3`
                ,at016.`F016TASIVA`
                ,at016.`C016TIPIVA`
                ,at016.`F016TASIEP`
                ,at016.`C016TIPIEP`
                ,at016.`F016TASISA`
                ,at016.`C016TIPISA`
                ,at016.`F016MONCC`
                ,at016.`F016TASCC`
                ,at016.`C016TIPCC`
                ,at016.`C016TLC`
                ,at016.`C016PROSEC`
                ,at016.`C016ISAN`
                ,at016.`I016BANISA`
                ,at016.`F016PREDET`
                ,at016.`C016APL303`
                ,at016.`N016VALMER`
                ,at016.`N016MONIGI`
                ,at016.`C016PAISDES`
                ,at016.`F016ARAEUA`
                ,at016.`C016MONEUA`
                ,at016.`N016MONEXE`
                ,at016.`C016FRAEUA`
                ,at016.`F016TASEUA`
                ,at016.`F016CANEUA`
                ,at016.`C016UMTEUA`
                ,at016.`C016FORPAG`
                ,at016.`N016IMPTOT`
                ,at016.`C016CONOBS`
                ,at016.`F016TASITV`
                ,at016.`C016TIPITV`
                ,at016.`D016STAMP`
                ,at016.`C016ORIGEN`
                ,at016.`F016TASAD4`
                ,at016.`C016TIPAD4`
                FROM `at016`
                LEFT JOIN `at019` ON at019.C019REFPED = at016.C016REFPED AND at019.C019FRAC = at016.C016FRAC
                WHERE at016.C016REFPED = '{$referencia}' AND at016.C016FRAC = '{$fraccion}' AND at019.C019CVECAS IN ('TL','PS','EN');";
            $result = $this->_db->fetchAll($Query);
            if ($result) {
                return $result;
            }
            return null;
        } catch (Exception $e) {
            $this->_logger->logEntry(__METHOD__, "ZEND DB EXCEPTION: " . $e->getMessage(), $_SERVER['REMOTE_ADDR'], null);
        }
    }

    public function pedimentoDatosBasicos($pedimento) {
        try {
            $select = $this->_db->select()
                    ->from(array('s' => 'AT001'), array('C001PATEN as patente', 'C001ADUSEC as aduana', 'C001REFPED as referencia', 'C001NUMPED as pedimento', new Zend_Db_Expr("CASE C001TIPOPE WHEN 1 THEN 'IMP' ELSE 'EXP' END AS tipoOperacion"), new Zend_Db_Expr("CASE I001CONSOL WHEN 'F' THEN 'N' ELSE 'S' END AS consolidado"), 'C001CVEDOC AS cvePedimento', 'C001FIRELE as firmaValidacion', 'C001FIRBAN as firmaBanco'))
                    ->where("s.C001FIRBAN <> ''")
                    ->where("s.C001FIRELE <> ''")
                    ->where('s.C001NUMPED = ?', $pedimento);
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on <strong>" . __METHOD__ . "</strong> >> " . $e->getMessage() . " line: " . $e->getLine() . " info: " . $e->getCode() . " trace: " . $e->getTrace());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on <strong>" . __METHOD__ . "</strong> >> " . $e->getMessage() . " line: " . $e->getLine() . " info: " . $e->getCode() . " trace: " . $e->getTrace());
        }
    }
    
    public function basicoReferencia($referencia) {
        try {
            $select = $this->_db->select()
                    ->from(array('s' => 'AT001'), array('C001PATEN as patente', 'C001ADUSEC as aduana', 'C001REFPED as referencia', 'C001NUMPED as pedimento', new Zend_Db_Expr("CASE C001TIPOPE WHEN 1 THEN 'IMP' ELSE 'EXP' END AS tipoOperacion"), new Zend_Db_Expr("CASE I001CONSOL WHEN 'F' THEN 'N' ELSE 'S' END AS consolidado"), 'C001CVEDOC AS cvePedimento', 'C001FIRELE as firmaValidacion', 'C001FIRBAN as firmaBanco', 'C001RFCCLI AS rfcCliente'))
                    ->where("s.C001FIRBAN <> ''")
                    ->where("s.C001FIRELE <> ''")
                    ->where('s.C001REFPED = ?', $referencia);
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on <strong>" . __METHOD__ . "</strong> >> " . $e->getMessage() . " line: " . $e->getLine() . " info: " . $e->getCode() . " trace: " . $e->getTrace());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on <strong>" . __METHOD__ . "</strong> >> " . $e->getMessage() . " line: " . $e->getLine() . " info: " . $e->getCode() . " trace: " . $e->getTrace());
        }
    }

    public function pedimentoCompleto($pedimento) {
        try {
            $select = $this->_db->select()
                    ->from(array('s' => 'AT001'), array('C001REFPED AS referencia', 'C001NUMPED AS pedimento', 'C001CVEDOC AS cvePedimento', new Zend_Db_Expr("CASE I001CONSOL WHEN 'F' THEN 'N' ELSE 'S' END AS consolidado"), 'C001FIRELE AS firmaValidacion', 'C001RFCCLI AS rfcCliente', 'C001CVECLI as cveCliente', 'C001TIPREG as regimen', new Zend_Db_Expr("DATE_FORMAT(D001FECPAG,'%d/%m/%Y') AS fechaPago"), new Zend_Db_Expr("DATE_FORMAT(D001FECEXT,'%d/%m/%Y') AS fechaExtraccion"), new Zend_Db_Expr("DATE_FORMAT(D001FECEP,'%d/%m/%Y') AS fechaEntrada"), 'C001ADUSEC as aduana', 'C001PATEN as patente', new Zend_Db_Expr("CASE C001TIPOPE WHEN 1 THEN 'IMP' ELSE 'EXP' END AS tipoOperacion"), 'F001TIPCAM as tipoCambio', 'C001NOMUSU as usuario', 'C001MEDTRE AS transporteEntrada', 'C001MEDTRA AS transporteArribo', 'C001MEDTRS AS transporteSalida', 'C001DESORI AS destinoOrigen', 'F001PESO AS peroBruto', 'C001ADUSE AS aduanaEntrada', '(N001VALCOM / F001TIPCAM) * F001FACAJU AS valorDolares', 'N001VALADU AS valorAduana', 'N001VALCOM AS valorComercial', '(F001FLETES * F001TIPCAM) AS fletes', '(F001VALSEG * F001TIPCAM) AS valorSeguros', '(F001SEGURO * F001TIPCAM) AS seguros', '(F001EMBALA * F001TIPCAM) AS embalajes', '(F001OTRINC * F001TIPCAM) as otrosIncrementables', 'N001NUMBUL as bultos', 'C001MARNUM as marcas', 'F001TASDT1 AS dta', 'I001TTDTA1 AS dtaFp', 'C001FIRFEA as firmaDigital', 'C001RFCSOC as sociedad'))
                    ->where('s.C001NUMPED = ?', $pedimento);
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                $stmt["cnt"] = $this->_contraprestacion($stmt["patente"], $stmt["pedimento"]);
                $stmt["cliente"] = $this->_datosCliente($stmt["cveCliente"]);
                $stmt["pago"] = $this->_banco($stmt["patente"], $stmt["pedimento"]);
                $stmt["liquidacion"] = $this->_liquidacion($stmt["patente"], $stmt["pedimento"], $stmt["regimen"], $stmt["dta"], $stmt["dtaFp"]);
                $stmt["observaciones"] = $this->_observacion($stmt["patente"], $stmt["pedimento"]);
//                $stmt["aduanaNombre"] = $this->_aduana(substr($stmt["aduana"], 0, 2), substr($stmt["aduana"], -1));
                $stmt["candados"] = $this->_candados($stmt["patente"], $stmt["pedimento"]);
                $stmt["extracciones"] = $this->_extracciones($stmt["patente"], $stmt["pedimento"]);
                $stmt["contenedores"] = $this->_contenedores($stmt["patente"], $stmt["pedimento"]);
                $stmt["identificadores"] = $this->_identificadoresPedimento($stmt["patente"], $stmt["pedimento"]);
                $stmt["proveedores"] = $this->_proveedores($stmt["patente"], $stmt["pedimento"]);
                $stmt["fracciones"] = $this->_fracciones($stmt["patente"], $stmt["pedimento"]);
                $stmt["transporte"] = $this->_transportes($stmt["patente"], $stmt["pedimento"]);
                $stmt["agente"] = $this->_datosAgente($stmt["patente"]);
                $stmt["sociedad"] = $this->_datosSociedad($stmt["sociedad"]);
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on <strong>" . __METHOD__ . "</strong> >> " . $e->getMessage() . " line: " . $e->getLine() . " info: " . $e->getCode() . " trace: " . $e->getTrace());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on <strong>" . __METHOD__ . "</strong> >> " . $e->getMessage() . " line: " . $e->getLine() . " info: " . $e->getCode() . " trace: " . $e->getTrace());
        }
    }

    protected function _datosCliente($cveCli) {
        try {
            $select = $this->_db->select()
                    ->from(array('s' => 'CAX503'), array('C503NOMCLI AS razonSocial', 'C503RFCCLI AS rfc', 'C503DOMCLI AS calle', 'C503NUMINT AS numInterior', 'C503NUMEXT AS numExterior', 'C503CODCLI as codigoPostal', 'C503PAISCL AS pais', 'C503ENTCLI as entidad', 'C503CIUCLI as municipio', 'C503COLCLI AS colonia'))
                    ->where('s.C503CVECLI = ?', $cveCli);
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                $data = array(
                    'rfc' => $stmt["rfc"],
                    'razonSocial' => $stmt["razonSocial"],
                    'domicilio' => array(
                        'calle' => $stmt["calle"] . ((isset($stmt["colonia"]) && $stmt["colonia"]) ? $stmt["colonia"] : null),
                        'numExterior' => $stmt["numExterior"],
                        'numInterior' => $stmt["numInterior"],
                        'municipio' => $stmt["municipio"],
                        'entidad' => $stmt["entidad"],
                        'pais' => $stmt["pais"],
                        'codigoPostal' => $stmt["codigoPostal"],
                    ),
                );
                return $data;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on <strong>" . __METHOD__ . "</strong> >> " . $e->getMessage() . " line: " . $e->getLine() . " info: " . $e->getCode() . " trace: " . $e->getTrace());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on <strong>" . __METHOD__ . "</strong> >> " . $e->getMessage() . " line: " . $e->getLine() . " info: " . $e->getCode() . " trace: " . $e->getTrace());
        }
    }

    protected function _contraprestacion($patente, $pedimento) {
        try {
            $select = $this->_db->select()
                    ->from(array('s' => 'AT008'), array('C008CVECON', 'C008CVEPAG', 'N008IMPCON'))
                    ->where('C008PATEN = ?', $patente)
                    ->where('C008NUMPED = ?', $pedimento)
                    ->where("C008CVECON = 'CNT'");
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                return $stmt["N008IMPCON"];
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on <strong>" . __METHOD__ . "</strong> >> " . $e->getMessage() . " line: " . $e->getLine() . " info: " . $e->getCode() . " trace: " . $e->getTrace());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on <strong>" . __METHOD__ . "</strong> >> " . $e->getMessage() . " line: " . $e->getLine() . " info: " . $e->getCode() . " trace: " . $e->getTrace());
        }
    }

    protected function _liquidacion($patente, $pedimento, $regimen, $ttDta, $dtaFp) {
        try {
            $select = $this->_db->select()
                    ->from(array('s' => 'AT008'), array('C008CVECON', 'C008CVEPAG', 'N008IMPCON'))
                    ->where('C008PATEN = ?', $patente)
                    ->where('C008NUMPED = ?', $pedimento)
                    ->order('C008AUTO');
            $stmt = $this->_db->fetchAll($select);
            if ($stmt) {
                $data = array();
                $prev = 0;
                $iva = 0;
                $cnt = 0;
                $dta = 0;
                $igi = 0;
                foreach ($stmt as $item) {
                    if ($item["C008CVECON"] == 'IVA') {
                        $data['impuestos'][] = array(
                            'impuesto' => 'IVA',
                            'cantidad' => $item["N008IMPCON"],
                            'fp' => $item["C008CVEPAG"],
                        );
                        $iva += $item["N008IMPCON"];
                    }
                    if ($item["C008CVECON"] == 'DTA') {
                        $data['impuestos'][] = array(
                            'impuesto' => 'DTA',
                            'cantidad' => $item["N008IMPCON"],
                            'fp' => $item["C008CVEPAG"],
                        );
                        $data['tasas'][] = array(
                            'impuesto' => 'DTA',
                            'cve' => $ttDta,
                            'tasa' => $dtaFp,
                        );
                        $dta += $item["N008IMPCON"];
                    }
                    if ($item["C008CVECON"] == 'PREV') {
                        $data['impuestos'][] = array(
                            'impuesto' => 'PREV',
                            'cantidad' => $item["N008IMPCON"],
                            'fp' => $item["C008CVEPAG"],
                        );
                        $data['tasas'][] = array(
                            'impuesto' => 'PRV',
                            'cve' => '2',
                            'tasa' => '210',
                        );
                        $prev += $item["N008IMPCON"];
                    }
                    if ($item["C008CVECON"] == 'CNT') {
                        $data['impuestos'][] = array(
                            'impuesto' => 'CNT',
                            'cantidad' => $item["N008IMPCON"],
                            'fp' => $item["C008CVEPAG"],
                        );
                        $data['tasas'][] = array(
                            'impuesto' => 'CNT',
                            'cve' => '2',
                            'tasa' => '20',
                        );
                        $cnt += $item["N008IMPCON"];
                    }
                    if ($item["C008CVECON"] == 'IGI/IGE') {
                        $data['impuestos'][] = array(
                            'impuesto' => 'IGI',
                            'cantidad' => $item["N008IMPCON"],
                            'fp' => $item["C008CVEPAG"],
                        );
                        $igi += $item["N008IMPCON"];
                    }
                }
                if ($regimen == 'ITE') {
                    $data["efectivo"] = $dta + $iva + $prev + $cnt + $igi;
                    $data["otros"] = $iva;
                    $data["total"] = $data["efectivo"] + $data["otros"];
                } elseif ($regimen == 'IMD') {
                    $data["efectivo"] = $dta + $iva + $prev + $cnt + $igi;
                    $data["otros"] = 0;
                    $data["total"] = $data["efectivo"] + $data["otros"];
                }
                return $data;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on <strong>" . __METHOD__ . "</strong> >> " . $e->getMessage() . " line: " . $e->getLine() . " info: " . $e->getCode() . " trace: " . $e->getTrace());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on <strong>" . __METHOD__ . "</strong> >> " . $e->getMessage() . " line: " . $e->getLine() . " info: " . $e->getCode() . " trace: " . $e->getTrace());
        }
    }

    protected function _proveedores($patente, $pedimento) {
        try {
            $select = $this->_db->select()
                    ->distinct()
                    ->from(array('s' => 'AT005'), array('C005CVEPRO as cveProveedor', 'C005IDEPRO as taxId', 'C005NOMPRO as nomProveedor', 'C005DOMPRO as calle', 'C005NUMINT as numInterior', 'C005NUMEXT as numExterior', 'C005CODPRO as codigoPostal', 'C005CIUPRO as ciudad', 'C005PAISPR as  pais', 'C005ENTPRO as entidad'))
                    ->where('s.C005PATEN = ?', $patente)
                    ->where('s.C005NUMPED = ?', $pedimento);
            $stmt = $this->_db->fetchAll($select);
            if ($stmt) {
                $data = array();
                foreach ($stmt as $item) {
                    $data[] = array(
                        'taxId' => $item["taxId"],
                        'nomProveedor' => $item["nomProveedor"],
                        'domicilio' => array(
                            'calle' => $item["calle"],
                            'numExterior' => $item["numExterior"],
                            'numInterior' => $item["numInterior"],
                            'municipio' => $item["ciudad"] . ', ' . $item['entidad'],
                            'pais' => $item["pais"],
                            'codigoPostal' => $item["codigoPostal"],
                        ),
                        'facturas' => $this->_facturas($patente, $pedimento, $item['cveProveedor']),
                    );
                }
                return $data;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on <strong>" . __METHOD__ . "</strong> >> " . $e->getMessage() . " line: " . $e->getLine() . " info: " . $e->getCode() . " trace: " . $e->getTrace());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on <strong>" . __METHOD__ . "</strong> >> " . $e->getMessage() . " line: " . $e->getLine() . " info: " . $e->getCode() . " trace: " . $e->getTrace());
        }
    }

    protected function _facturas($patente, $pedimento, $cveProveedor) {
        try {
            $select = $this->_db->select()
                    ->from(array('s' => 'AT005'), array('C005NUMFAC as numFactura', 'C005EDOC as cove', new Zend_Db_Expr("DATE_FORMAT(D005FECFAC,'%d/%m/%Y') AS fechaFactura"), 'C005CVEINC as incoterm', 'C005MONFAC as divisa', 'F005VALDOL as valorDolares', 'C005VINCU as vinculacion', 'F005VALMEX as valorMonExt', 'F005FACMEX as factorEquivalencia'))
                    ->where('s.C005PATEN = ?', $patente)
                    ->where('s.C005NUMPED = ?', $pedimento)
                    ->where('s.C005CVEPRO = ?', $cveProveedor)
                    ->order('s.C005NUMFAC ASC');
            $stmt = $this->_db->fetchAll($select);
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on <strong>" . __METHOD__ . "</strong> >> " . $e->getMessage() . " line: " . $e->getLine() . " info: " . $e->getCode() . " trace: " . $e->getTrace());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on <strong>" . __METHOD__ . "</strong> >> " . $e->getMessage() . " line: " . $e->getLine() . " info: " . $e->getCode() . " trace: " . $e->getTrace());
        }
    }

    protected function _candados($patente, $pedimento) {
        try {
            $select = $this->_db->select()
                    ->distinct()
                    ->from(array('s' => 'AT075'), array('C075CAND1 as candado1', 'C075CAND2 as candado2', 'C075CAND3 as candado3', 'C075CAND4 as candado4', 'C075CAND5 as candado5'))
                    ->where('s.C075PATEN = ?', $patente)
                    ->where('s.C075NUMPED = ?', $pedimento);
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on <strong>" . __METHOD__ . "</strong> >> " . $e->getMessage() . " line: " . $e->getLine() . " info: " . $e->getCode() . " trace: " . $e->getTrace());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on <strong>" . __METHOD__ . "</strong> >> " . $e->getMessage() . " line: " . $e->getLine() . " info: " . $e->getCode() . " trace: " . $e->getTrace());
        }
    }

    protected function _extracciones($patente, $pedimento) {
        try {
            $select = $this->_db->select()
                    ->distinct()
                    ->from(array('s' => 'AT011'), array('C011PATORI as patente', 'C011ADUORI as aduana', 'C011PEDORI as pedimento', 'C011FECVAL as year', 'C011DOCORI as regimen', new Zend_Db_Expr("DATE_FORMAT(D011FECORI,'%d/%m/%Y') AS fecha")))
                    ->where('s.C011PATEN = ?', $patente)
                    ->where('s.C011NUMPED = ?', $pedimento);
            $stmt = $this->_db->fetchAll($select);
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on <strong>" . __METHOD__ . "</strong> >> " . $e->getMessage() . " line: " . $e->getLine() . " info: " . $e->getCode() . " trace: " . $e->getTrace());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on <strong>" . __METHOD__ . "</strong> >> " . $e->getMessage() . " line: " . $e->getLine() . " info: " . $e->getCode() . " trace: " . $e->getTrace());
        }
    }

    protected function _contenedores($patente, $pedimento) {
        try {
            $select = $this->_db->select()
                    ->from(array('s' => 'AT004'), array('C004NUMCON as numContenedor', 'C004TIPCON as tipoContenedor'))
                    ->where('s.C004PATEN = ?', $patente)
                    ->where('s.C004NUMPED = ?', $pedimento);
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on <strong>" . __METHOD__ . "</strong> >> " . $e->getMessage() . " line: " . $e->getLine() . " info: " . $e->getCode() . " trace: " . $e->getTrace());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on <strong>" . __METHOD__ . "</strong> >> " . $e->getMessage() . " line: " . $e->getLine() . " info: " . $e->getCode() . " trace: " . $e->getTrace());
        }
    }

    protected function _fracciones($patente, $pedimento) {
        try {
            $select = $this->_db->select()
                    ->from(array('s' => 'AT016'), array('C016FRAC AS fraccion', 'I016SUBFRA as subdivision', 'C016SECFRA AS secuencia', 'C016DESMER as descripcion', 'C016VINCU as vinculacion', 'C016METVAL as valoracion', 'F016CANUMC as cantidad', 'C016UNIUMC as umc', 'F016CANUMT as tarifa', 'C016UNIUMT as utm', 'C016PAISOD as paisOrigen', 'C016PAISCV as paisComprador', 'N016VALCOM as valorComercial', 'N016VALADU as valorAduana', 'F016VALDOL as valorDolares', 'F016PREUNI as precioUnitario', 'N016VALAGR as valorAgregado', 'F016TASADV as igiTasa', 'F016TASADV as igiTasa', 'F016TASIVA as ivaTasa', 'C016TIPIVA as ivaTt'))
                    ->joinLeft(array('p' => 'AT021'), "p.C021CVEGRA = 'IGI/IGE' AND p.C021PATEN = s.C016PATEN AND p.C021NUMPED = s.C016NUMPED AND p.C021SECFRA = s.C016SECFRA", array('F021IMPGRA as igi', 'C021CVEPAG as igiFp'))
                    ->joinLeft(array('a' => 'AT021'), "a.C021CVEGRA = 'IVA' AND a.C021PATEN = s.C016PATEN AND a.C021NUMPED = s.C016NUMPED AND a.C021SECFRA = s.C016SECFRA", array('F021IMPGRA as iva', 'C021CVEPAG as ivaFp'))
                    ->where('s.C016PATEN = ?', $patente)
                    ->where('s.C016NUMPED = ?', $pedimento)
                    ->order('s.C016SECFRA ASC');
            $stmt = $this->_db->fetchAll($select);
            if ($stmt) {
                $data = array();
                foreach ($stmt as $item) {
                    $item["observaciones"] = $this->_observacionesPartida($patente, $pedimento, $item["secuencia"]);
                    $item["identificadores"] = $this->_casos($patente, $pedimento, $item["secuencia"]);
                    $data[] = $item;
                }
                return $data;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on <strong>" . __METHOD__ . "</strong> >> " . $e->getMessage() . " line: " . $e->getLine() . " info: " . $e->getCode() . " trace: " . $e->getTrace());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on <strong>" . __METHOD__ . "</strong> >> " . $e->getMessage() . " line: " . $e->getLine() . " info: " . $e->getCode() . " trace: " . $e->getTrace());
        }
    }

    protected function _observacion($patente, $pedimento) {
        try {
            $select = $this->_db->select()
                    ->from(array('s' => 'AT010'), array('M010OBSERV as observacion'))
                    ->where('s.C010PATEN = ?', $patente)
                    ->where('s.C010NUMPED = ?', $pedimento);
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                return $stmt["observacion"];
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on <strong>" . __METHOD__ . "</strong> >> " . $e->getMessage() . " line: " . $e->getLine() . " info: " . $e->getCode() . " trace: " . $e->getTrace());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on <strong>" . __METHOD__ . "</strong> >> " . $e->getMessage() . " line: " . $e->getLine() . " info: " . $e->getCode() . " trace: " . $e->getTrace());
        }
    }

    protected function _casos($patente, $pedimento, $orden) {
        try {
            $select = $this->_db->select()
                    ->distinct()
                    ->from(array('s' => 'AT019'), array('C019CVECAS as tipoCaso', 'C019IDECAS as caso1', 'C019IDE2CAS as caso2', 'C019IDE3CAS as caso3'))
                    ->where('s.C019PATEN = ?', $patente)
                    ->where('s.C019NUMPED = ?', $pedimento)
                    ->where('s.C019SECFRA = ?', $orden);
            $stmt = $this->_db->fetchAll($select);
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on <strong>" . __METHOD__ . "</strong> >> " . $e->getMessage() . " line: " . $e->getLine() . " info: " . $e->getCode() . " trace: " . $e->getTrace());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on <strong>" . __METHOD__ . "</strong> >> " . $e->getMessage() . " line: " . $e->getLine() . " info: " . $e->getCode() . " trace: " . $e->getTrace());
        }
    }

    protected function _observacionesPartida($patente, $pedimento, $orden) {
        try {
            $select = $this->_db->select()
                    ->from(array('s' => 'AT023'), array('C023OBSERV as observacion'))
                    ->where('s.C023PATEN = ?', $patente)
                    ->where('s.C023NUMPED = ?', $pedimento)
                    ->where('s.C023SECFRA = ?', $orden);
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on <strong>" . __METHOD__ . "</strong> >> " . $e->getMessage() . " line: " . $e->getLine() . " info: " . $e->getCode() . " trace: " . $e->getTrace());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on <strong>" . __METHOD__ . "</strong> >> " . $e->getMessage() . " line: " . $e->getLine() . " info: " . $e->getCode() . " trace: " . $e->getTrace());
        }
    }

    protected function _banco($patente, $pedimento) {
        try {
            $select = $this->_db->select()
                    ->from(array('s' => 'AT036'), array('C036FIMBAN AS firmaBanco', 'C036NUMOPE AS operacion', 'N036TOTEFE AS efectivo', 'N036TOTCON as contribuciones', 'C036PATEN AS patente', 'C036ID AS cuenta', new Zend_Db_Expr("DATE_FORMAT(D036FECPAG,'%d/%m/%Y') AS fechaPago")))
                    ->where('s.C036PATEN = ?', $patente)
                    ->where('s.C036NUMPED = ?', $pedimento);
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on <strong>" . __METHOD__ . "</strong> >> " . $e->getMessage() . " line: " . $e->getLine() . " info: " . $e->getCode() . " trace: " . $e->getTrace());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on <strong>" . __METHOD__ . "</strong> >> " . $e->getMessage() . " line: " . $e->getLine() . " info: " . $e->getCode() . " trace: " . $e->getTrace());
        }
    }

    protected function _transportes($patente, $pedimento) {
        try {
            $select = $this->_db->select()
                    ->from(array('s' => 'AT002'), array('C002NOMTRA AS transportista', 'C002PAISMT as pais', 'C002PLACAS as placas'))
                    ->where('s.C002PATEN = ?', $patente)
                    ->where('s.C002NUMPED = ?', $pedimento);
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on <strong>" . __METHOD__ . "</strong> >> " . $e->getMessage() . " line: " . $e->getLine() . " info: " . $e->getCode() . " trace: " . $e->getTrace());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on <strong>" . __METHOD__ . "</strong> >> " . $e->getMessage() . " line: " . $e->getLine() . " info: " . $e->getCode() . " trace: " . $e->getTrace());
        }
    }

    protected function _identificadoresPedimento($patente, $pedimento) {
        try {
            $select = $this->_db->select()
                    ->from(array('s' => 'AT006'), array('C006CVECAS as tipoCaso', 'C006IDECAS as caso1', 'C006IDE2CAS as caso2', 'C006IDE3CAS as caso3'))
                    ->where('s.C006PATEN = ?', $patente)
                    ->where('s.C006NUMPED = ?', $pedimento);
            $stmt = $this->_db->fetchAll($select);
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on <strong>" . __METHOD__ . "</strong> >> " . $e->getMessage() . " line: " . $e->getLine() . " info: " . $e->getCode() . " trace: " . $e->getTrace());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on <strong>" . __METHOD__ . "</strong> >> " . $e->getMessage() . " line: " . $e->getLine() . " info: " . $e->getCode() . " trace: " . $e->getTrace());
        }
    }

    protected function _datosAgente($patente) {
        try {
            $select = $this->_db->select()
                    ->from(array('s' => 'CAX515'), array("CONCAT(C515NOM, ' ', C515APPAT, ' ', C515APMAT) as nombre", 'C515RFC as rfc', 'C515CURP as curp', 'C515SERIECERT as serie'))
                    ->where('s.C515PATEN = ?', $patente)
                    ->where("s.C515TIPOP = 'agente'");
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on <strong>" . __METHOD__ . "</strong> >> " . $e->getMessage() . " line: " . $e->getLine() . " info: " . $e->getCode() . " trace: " . $e->getTrace());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on <strong>" . __METHOD__ . "</strong> >> " . $e->getMessage() . " line: " . $e->getLine() . " info: " . $e->getCode() . " trace: " . $e->getTrace());
        }
    }

    protected function _datosSociedad($rfc) {
        try {
            $select = $this->_db->select()
                    ->from(array('s' => 'CAX515'), array('C515NOM as nombre', 'C515RFC as rfc', 'C515CURP as curp'))
                    ->where('s.C515RFC = ?', $rfc)
                    ->where("s.C515TIPOP = 'razon_soc'");
            $stmt = $this->_db->fetchRow($select);
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on <strong>" . __METHOD__ . "</strong> >> " . $e->getMessage() . " line: " . $e->getLine() . " info: " . $e->getCode() . " trace: " . $e->getTrace());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on <strong>" . __METHOD__ . "</strong> >> " . $e->getMessage() . " line: " . $e->getLine() . " info: " . $e->getCode() . " trace: " . $e->getTrace());
        }
    }

}
