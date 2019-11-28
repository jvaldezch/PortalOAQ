<?php

/**
 * Clase para la conectividad con la base de datos de SLAM y el web service que provee de datos al dashboard
 *
 * @author Jaime E. Valdez <jvaldezch@gmail.com>
 */
class OAQ_Slam {

    protected $_config;
    protected $_db;

    function __construct($host, $username, $password, $dbname, $adapter, $port) {
        if ($port == 1433) {
            if (preg_match('/WINNT/i', PHP_OS) && $adapter == 'SqlSrv') {
                $adapter = 'SqlSrv';
            } elseif (preg_match('/Linux/i', PHP_OS) && $adapter == 'SqlSrv') {
                $adapter = 'Pdo_Mssql';
            }
        }
        $this->_db = Zend_Db::Factory($adapter, array(
                'host' => $host,
                'username' => $username,
                'password' => $password,
                'dbname' => $dbname,
                'port' => $port,
        ));
    }
    
    public function wsPartesPedimento($referencia, $factura) {
        try {
            $select = $this->_db->select()
                    ->distinct()
                    ->from(array('t' => 'tblClasifica'), array(
                        't.NumFraccion AS fraccion',
                        't.NUMPARTE AS numParte',
                        't.SECUENCIA_PED AS ordenFraccion',
                        't.DescFra AS descripcion',
                        't.PreUnitario AS precioUnitario',
                        't.PaisOrigen AS paisOrigen',
                        't.PV AS paisVendedor',
                        't.TLC AS tlc',
                        new Zend_Db_Expr("(SELECT U.Clave FROM tblUnidad U WHERE U.Abreviacion = t.UniMedFac) AS umc"),
                        new Zend_Db_Expr("(SELECT U.Clave FROM tblUnidad U WHERE U.Abreviacion = t.UniMedTar) AS umt"),
                        't.PROSEC AS prosec',
                        't.Cantidad AS cantUmc',
                        't.Cantidad AS cantUmt',
                        't.Total AS valorMonExt',
                        't.ADV AS tasaAdvalorem',
                    ))
//                    ->where('t.REFERENCIA = ?', $referencia)
                    ->where('t.REFERENCIA_PED = ?', $referencia)
                    ->where('t.Factura = ?', $factura);
            $result = $this->_db->fetchAll($select);
            if($result) {
                return $result;
            }
            return false;
        } catch (Zend_Db_Exception $e) {
            echo "<b>Zend Db Exception on " . __METHOD__ . "</b>" . $e->getMessage();
        }
    }

    /**
     * 
     * @param int $patente
     * @param int $aduana
     * @param string $referencia
     * @return boolean
     * @throws Exception
     */
    public function slamConsultarFacturasImpo($patente, $aduana, $referencia) {
        try {
            $facturas = $this->_db->select()
                    ->distinct()
                    ->from(array('t' => 'Trafico'), array(
                        't.traPedimento AS Operacion',
                        't.traReferencia AS Referencia',
                        't.traCli AS CveImp'
                    ))
                    ->joinLeft(array('f' => 'tblFactgen'), 'F.facgref = T.traReferencia', array(
                        'f.facgnofac AS NumFactura',
                        "CONVERT(varchar, f.facgfefac, 111) AS FechaFactura",
                        'f.facgprov AS CvePro'
                    ))
                    ->joinLeft(array('c' => 'Clientes'), 'C.CLIENTE_ID = T.traCli', array(
                        'c.RFC AS CteRfc',
                        'c.Nom AS CteNombre',
                        'c.Dir AS CteCalle',
                        'c.NUMEXT AS CteNumExt',
                        'c.NUMINT AS CteNumInt',
                        'c.Dir2 AS CteColonia',
                        'c.Cd AS CteMun',
                        'c.Edo AS CteEdo',
                        'c.CP AS CteCP',
                        'c.Pais AS CtePais',
                    ))
                    ->joinLeft(array('p' => 'ProCli'), 't.traProCli = p.PROVEEDOR_ID', array(
                        'p.proIRS AS ProTaxID',
                        'p.proNom AS ProNombre',
                        'p.proDir AS ProCalle',
                        'p.NUMEXT AS ProNumExt',
                        'p.NUMINT AS ProNumInt',
                        'p.proDir2 AS ProColonia',
                        'p.proCd AS ProMun',
                        'p.proEdo AS ProEdo',
                        'p.proCp AS ProCP',
                        'p.proPais AS ProPais',
                    ))
                    ->where('t.traReferencia = ?', $referencia);
            $result = $this->_db->fetchAll($facturas);
            if ($result) {
                $data = array();
                foreach ($result as $item) {
                    $data[] = array(
                        'Patente' => substr($item["Operacion"], 0, 4),
                        'Aduana' => $aduana,
                        'Pedimento' => substr($item["Operacion"], 5, 7),
                        'Referencia' => $item["Referencia"],
                        'TipoOperacion' => "TOCE.IMP",
                        'NumFactura' => $item["NumFactura"],
                        'FechaFactura' => $item["FechaFactura"],
                        'CteRfc' => $item["CteRfc"],
                        'CteNombre' => $item["CteNombre"],
                        'ProTaxID' => $item["ProTaxID"],
                        'ProNombre' => $item["ProNombre"],
                    );
                }
                return $data;
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception found at " . __METHOD__ . ": " . $ex->getMessage());
        } catch (Exception $ex) {
            throw new Exception("Exception found at " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    /**
     * 
     * @param int $patente
     * @param int $aduana
     * @param string $referencia
     * @param string $facturas
     * @return boolean
     * @throws Exception
     */
    public function slamObtenerFacturasImpo($patente, $aduana, $referencia, $facturas) {
        try {
            $misc = new OAQ_Misc();
            $array = explode('|', $facturas);
            $in = '';
            foreach (range(0, count($array) - 1) as $k) {
                $in .= "'" . $array[$k] . "'";
                if ($k < count($array) - 1) {
                    $in .= ',';
                }
            }
            $select = $this->_db->select()
                    ->distinct()
                    ->from(array('t' => 'Trafico'), array(
                        't.traPedimento AS Operacion',
                        't.traReferencia AS Referencia',
                        't.traCli AS CveImp'
                    ))
                    ->joinLeft(array('f' => 'tblFactgen'), 'F.facgref = T.traReferencia', array(
                        'f.facgnofac AS NumFactura',
                        "CONVERT(varchar, f.facgfefac, 111) AS FechaFactura",
                        'f.facgprov AS CvePro',
                        'f.facgmoneda AS Moneda',
                        'f.FACTORMONEXT AS FactorAjuste',
                        'f.VIN AS Vinculacion',
                        'f.facgsubdivision AS Subdivision',
                    ))
                    ->joinLeft(array('c' => 'Clientes'), 'C.CLIENTE_ID = T.traCli', array(
                        'c.RFC AS CteRfc',
                        'c.Nom AS CteNombre',
                        'c.Dir AS CteCalle',
                        'c.NUMEXT AS CteNumExt',
                        'c.NUMINT AS CteNumInt',
                        'c.Dir2 AS CteColonia',
                        'c.Cd AS CteMun',
                        'c.Edo AS CteEdo',
                        'c.CP AS CteCP',
                        'c.Pais AS CtePais',
                    ))
                    ->joinLeft(array('p' => 'ProCli'), 't.traProCli = p.PROVEEDOR_ID', array(
                        'p.proIRS AS ProTaxID',
                        'p.proNom AS ProNombre',
                        'p.proDir AS ProCalle',
                        'p.NUMEXT AS ProNumExt',
                        'p.NUMINT AS ProNumInt',
                        'p.proDir2 AS ProColonia',
                        'p.proCd AS ProMun',
                        'p.proEdo AS ProEdo',
                        'p.proCp AS ProCP',
                        'p.proPais AS ProPais',
                    ))
                    ->where("t.traReferencia = ?", $referencia)
                    ->where("f.facgnofac IN ({$in})");
            $result = $this->_db->fetchAll($select);
            if ($result) {
                $data = array();
                foreach ($result as $item) {
                    $data[] = array(
                        "IdFact" => $misc->getUuid($referencia . $item["Operacion"] . $item["NumFactura"] . $item["OrdenFact"] . microtime()),
                        'Patente' => substr($item["Operacion"], 0, 4),
                        'Aduana' => $aduana,
                        'Pedimento' => substr($item["Operacion"], 5, 7),
                        'Referencia' => $item["Referencia"],
                        'TipoOperacion' => "TOCE.IMP",
                        'CveCli' => $item["CveImp"],
                        'NumFactura' => $item["NumFactura"],
                        'FechaFactura' => $item["FechaFactura"],
                        'CteRfc' => $item["CteRfc"],
                        'CteNombre' => $item["CteNombre"],
                        'CteCalle' => $item["CteCalle"],
                        'CteNumExt' => $item["CteNumExt"],
                        'CteNumInt' => $item["CteNumInt"],
                        'CteColonia' => $item["CteColonia"],
                        'CteColonia' => $item["CteColonia"],
                        'CteMun' => $item["CteMun"],
                        'CteEdo' => $item["CteEdo"],
                        'CteCP' => $item["CteCP"],
                        'CtePais' => $item["CtePais"],
                        'CvePro' => $item["CvePro"],
                        'ProTaxID' => $item["ProTaxID"],
                        'ProNombre' => $item["ProNombre"],
                        'ProCalle' => $item["ProCalle"],
                        'ProNumExt' => $item["ProNumExt"],
                        'ProNumInt' => $item["ProNumInt"],
                        'ProMun' => $item["ProMun"],
                        'ProEdo' => $item["ProEdo"],
                        'ProCP' => $item["ProCP"],
                        'ProPais' => $item["ProPais"],
                        'Subdivision' => $item["Subdivision"],
                        "Observaciones" => '',
                        "NumParte" => '',
                        "CertificadoOrigen" => '0',
                        "NumExportador" => '',
                        'Productos' => $this->slamObtenerProductosImpo($item["Referencia"], $item["NumFactura"], $item["Moneda"], $item["FactorAjuste"]),
                    );
                }
                return $data;
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception found at " . __METHOD__ . ": " . $ex->getMessage());
        } catch (Exception $ex) {
            throw new Exception("Exception found at " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function slamObtenerProductosImpo($referencia, $numFactura, $moneda, $factorAjuste) {
        try {
            $misc = new OAQ_Misc();
            $units = new Vucem_Model_VucemUnidadesMapper();
            $select = $this->_db->select()
                    ->distinct()
                    ->from(array('c' => 'tblClasifica'), array(
                        'c.REFERENCIA as Referencia',
                        'c.NumFraccion AS Fraccion',
                        'c.NUMPARTE AS NumParte',
                        'c.Renglon AS Renglon',
                        new Zend_Db_Expr('(SELECT u.Clave FROM tblUnidad u WHERE u.Abreviacion = c.UniMedFac) AS UMC'),
                        new Zend_Db_Expr('(SELECT u.Clave FROM tblUnidad u WHERE u.Abreviacion = c.UniMedTar) AS UMT'),
                        'c.Cantidad AS CantUMC',
                        'c.CantTar AS CantUMT',
                        'c.Total AS Total',
                        'c.DescFra AS Descripcion',
                        'c.SECUENCIA_CON AS OrdenCaptura',
                        'c.PaisOrigen AS PaisOrigen',
                        'c.PV AS PaisVendedor',
                        'c.PreUnitario AS PreUnitario',
                        'c.ADV AS TasaAdvalorem',
                        'c.TLC AS TLC',
                        'c.PROSEC AS PROSEC',
                        'c.Total AS ValorMonExt',
                    ))
                    ->where('c.REFERENCIA = ?', $referencia)
                    ->where('c.Factura = ?', $numFactura);
            $result = $this->_db->fetchAll($select);
            if ($result) {
                $data = array();
                foreach ($result as $item) {
                    $valDls = 0;
                    if ($moneda !== 'USD') {
                        $valDls = $item["ValorMonExt"] * $item["FactorAjuste"];
                    } else {
                        $valDls = $item["ValorMonExt"];
                    }
                    $data[] = array(
                        'ID_PROD' => $misc->getUuid($referencia . $numFactura . $item["NumParte"] . $item["Fraccion"] . microtime() . $item["Renglon"]),
                        'SUB' => isset($item["SUB"]) ? $item["SUB"] : '0',
                        'CODIGO' => $item["Fraccion"],
                        'PARTE' => $item["NumParte"],
                        'SUBFRA' => '',
                        'DESC1' => $item["Descripcion"],
                        'ORDEN' => $item["Renglon"],
                        'DESC_COVE' => $item["Descripcion"],
                        'FACTAJU' => $factorAjuste,
                        'PREUNI' => $item["PreUnitario"],
                        'VALMN' => 0,
                        'VALCOM' => $item["ValorMonExt"],
                        'VALDLS' => $valDls,
                        'VALCEQ' => $factorAjuste,
                        'MONVAL' => $moneda,
                        'UMC' => $item["UMC"],
                        'UMT' => $item["UTM"],
                        'CANTFAC' => $item["CantUMC"],
                        'CANTTAR' => $item["CantUMT"],
                        'PAIORI' => $item["PaisOrigen"],
                        'PAICOM' => $item["PaisVendedor"],
                        'CERTLC' => $item["TLC"],
                        'CAN_OMA' => $item["CantUMC"],
                        'UMC_OMA' => $units->getOma($item["UMC"]),
                    );
                }
                return $data;
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception found at " . __METHOD__ . ": " . $ex->getMessage());
        } catch (Exception $ex) {
            throw new Exception("Exception found at " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function slamFactura($factura, $cove) {
        $sql = "DECLARE	@fact	nvarchar (100)
            DECLARE	@cove	nvarchar (100)
            SET @fact	=	'{$factura}'
            SET @cove	=	'{$cove}'
            SELECT
               F.facgmoneda AS Divisa
               ,F.FACTORMONEXT AS FactorME
               ,F.facgnofac AS FacturaNo
               ,CONVERT(VARCHAR(10), F.facgfefac, 103) AS FechaFactura
               ,F.FACGINCOTERM AS Incoterm
               ,Ca.NumFraccion AS FraccionA
               ,F.facgValorMerc AS ValorME
               ,Ca.NUMPARTE AS NumParte
               ,Ca.DescFra AS Descripcion
               ,Ca.PaisOrigen AS Origen    
               ,(SELECT TOP 1 U.Clave FROM tblUnidad U WHERE U.Abreviacion = Ca.UniMedFac) AS UMC
               ,Ca.UniMedFac AS AbrevUMC    
               ,Ca.Cantidad AS CantidadUMC
               ,(SELECT TOP 1 U.Clave FROM tblUnidad U WHERE U.Abreviacion = Ca.UniMedTar) AS UMT
               ,Ca.UniMedTar AS AbrevUMT
               ,Ca.CantTar AS CantUMT
               ,Ca.PV AS Vendedor
               ,Ca.TLC AS TLC
               ,Ca.IVA AS IVA_
               ,Ca.IEPS AS IEPS
               ,Ca.ISAN AS ISAN
               ,Ca.PesoKgs AS PesoBru
               ,@cove AS Cove
            FROM tblFactgen AS F 
            LEFT JOIN tblClasifica AS Ca ON Ca.Factura = F.facgnofac
            WHERE F.facgnofac = @fact AND F.FACTURA_ID = (SELECT TOP 1 FACTURA_ID FROM COVE_FAC WHERE FACTURA = @fact AND COVE = @cove);";
        $result = $this->_db->fetchAll($sql);
        if ($result) {
            return $result;
        }
        return null;
    }

}
