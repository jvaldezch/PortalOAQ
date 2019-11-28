<?php
/**
 * Description of Anexo24
 *
 * @author Jaime E. Valdez <jvaldezch@gmail.com>
 */
class OAQ_Anexo24
{
    protected $_db;
    protected $_valid;
    protected $_ad;
    public $fields;
    
    function __construct($aduana=null)
    {
        $this->_ad = $aduana;
        switch ($aduana) {
            case 640:
                $this->connectToLocalMysql('sitaw3010640');
                $this->valid = true;
                break;
            case 646:
                $this->connectToLocalMysql('sitaw3010640');
                $this->valid = true;
                break;
            default:
                $this->valid = null;
                break;
        }
    }
    
    public function getValid()
    {
        return $this->valid;
    }
    
    protected function connectToLocalMysql($database)
    {
        try {            
            if($this->_ad == 640) {
                $this->_db = Zend_Db::factory('Pdo_Mssql', array(
                    'host'             => '192.168.200.5',
                    'username'         => 'sa',
                    'password'         => 'adminOAQ123',
                    'port'             => 1433,
                    'dbname'           => $database,
                ));
                $this->_db->getConnection();
                $this->_db->setFetchMode(Zend_Db::FETCH_OBJ);
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            echo '<b>Adapter Exception found while connecting @OAQ_Anexo24 @connectToLocalMysql: </b> ' . $e->getMessage(); die();
        } catch (Zend_Exception $e) {
            echo '<b>Zend Exception found while connecting @OAQ_Anexo24 @connectToLocalMysql: </b> ' . $e->getMessage(); die();
        }
    }
    
    public function validateCustomer($rfc,$aduana)
    {
        try {            
            if($aduana == 640) {
                $sitawin = new OAQ_Sitawin(true,'192.168.200.5','sa','adminOAQ123','SITAW3010640',1433,'Pdo_Mssql');
            } elseif($aduana == 646) {            
                $sitawin = new OAQ_Sitawin(true, '192.168.0.253', 'sa', 'sqlcointer', 'SITAW3589640', 1433, 'Pdo_Mssql');
            }
            return $sitawin->validateCustomer($rfc);           
            
        } catch (Exception $e) {
            echo '<b>Exception found while validating user @OAQ_Anexo24 @validateCustomer: </b> ' . $e->getMessage(); die();
        }
    }
    
    public function getDataByPeriod($rfc,$fechaIni,$fechaFin,$tipo,$aduana)
    {
        try {
            if($aduana == 640) {
                $sitawin = new OAQ_Sitawin(true,'192.168.200.5','sa','adminOAQ123','SITAW3010640',1433,'Pdo_Mssql');
            } elseif($aduana == 646) {            
                $sitawin = new OAQ_Sitawin(true, '192.168.0.253', 'sa', 'sqlcointer', 'SITAW3589640', 1433, 'Pdo_Mssql');
            }
            $pedimentos = $sitawin->getOperations($rfc, $fechaIni, $fechaFin, $tipo,$aduana);
            return $pedimentos;
            
        } catch (Exception $e) {
            echo '<b>Exception found while getting CVE_IMP @OAQ_Anexo24 @getDataByPeriod: </b> ' . $e->getMessage(); die();
        }
    }
    
    public function getListByPeriod($rfc,$fecha,$aduana)
    {
        try {
            if($aduana == 640) {
                $sitawin = new OAQ_Sitawin(true,'192.168.200.5','sa','adminOAQ123','SITAW3010640',1433,'Pdo_Mssql');
            } elseif($aduana == 646) {            
                $sitawin = new OAQ_Sitawin(true, '192.168.0.253', 'sa', 'sqlcointer', 'SITAW3589640', 1433, 'Pdo_Mssql');
            }
            
            $pedimentos = $sitawin->getOperationsByMonth($rfc, $fecha);
            return $pedimentos;
            
            
        } catch (Exception $e) {
            echo '<b>Exception found while getting CVE_IMP @OAQ_Anexo24 @getDataByPeriod: </b> ' . $e->getMessage(); die();
        }
    }
    
    protected function getCveImpo($rfc)
    {
        try {
            $select = "SELECT TOP 1 CVE_IMP FROM cmcli WHERE RFC LIKE '{$rfc}';";
            $result = $this->_db->fetchRow($select);
            if($result) {
                return $result->CVE_IMP;
            }
            return false;
        } catch (Exception $e) {
            echo '<b>Exception found while getting CVE_IMP @OAQ_Anexo24 @getCveImpo: </b> ' . $e->getMessage(); die();
        }
    }
    
    protected function getFields($tipo)
    {
        if($tipo == 'I') {
            return array(
                'Referencia',
                'Importacion',
                'Aduana',
                'Fecha',
                'TipoCambio',
                'IVA',
                'Clave',
                'Regimen',
                'Fletes',
                'Seguros',
                'Embalajes',
                'Otros',
                'DTA',
                'ValorComercial',
                'ValorAduana',
                'Observaciones',
                'Consolidado',
                'Virtual',
                'NotaInterna',
                'Prevalidacion',
                'NomProveedor',
                'Factura',
                'FechaFactura',
                'FMoneda',
                'NumeroDeParte',
                'FraccionImportacion',
                'Tasa',
                'TipoTasa',
                'Unidad',
                'Precio',
                'Cantidad',
                'Origen',
                'Vendedor',
                'NotaInterna',
                'FormaPago',
                'Incoterm',
                'PagaTLC',
                'PagaTLCUEM',
                'PagaTLCAELC',
                'JustTLC',
                'JustTLCUEM',
                'JustTLCAELC',
                'EB',
                'MontoEB',
                'EnConsignacion',
                'NotaInterna2',
                'Revision',
                'Cove',
            );
        } elseif($tipo == 'E') {
            return array(
                'Referencia',
                'Exportacion',
                'Aduana',
                'Fecha',
                'TipoCambio',
                'IVA',
                'Clave',
                'Regimen',
                'Fletes',
                'Seguros',
                'Embalajes',
                'Otros',
                'DTA',
                'ValorComercial',
                'ValorAduana',
                'Observaciones',
                'Consolidado',
                'Virtual',
                'NotaInterna',
                'Prevalidacion',
                'NomProveedor',
                'Factura',
                'FechaFactura',
                'FMoneda',
                'NumeroDeParte',
                'FraccionImportacion',
                'Tasa',
                'TipoTasa',
                'Unidad',
                'Precio',
                'Cantidad',
                'Origen',
                'Vendedor',
                'NotaInterna',
                'FormaPago',
                'Incoterm',
                'PagaTLC',
                'PagaTLCUEM',
                'PagaTLCAELC',
                'JustTLC',
                'JustTLCUEM',
                'JustTLCAELC',
                'EB',
                'MontoEB',
                'EnConsignacion',
                'NotaInterna2',
                'Revision',
                'Cove',
            );
        }
    }
    
    protected function getQuery($rfc,$fechaIni,$fechaFin,$tipo)
    {
        if($this->_ad == 640) {
            $impo = "(right(convert(varchar, p.FEC_PAG, 105), 2) + '-' + CAST(p.ADUANAD AS varchar(2)) + '-' + CAST(p.PATENTE as VARCHAR(4)) + '-' + CAST(p.NUM_PED as VARCHAR(7))) AS Importacion";
            $aduana = "p.ADUANAD + p.SECCDES AS Aduana";
            $fecha = "convert(varchar, p.FEC_PAG, 103) AS Fecha";
            $fechaFac = "convert(varchar, f.FECFAC, 103) AS FechaFactura";
            $fi = date('d/m/Y',  strtotime($fechaIni));
            $ff = date('d/m/Y',  strtotime($fechaFin));
        } else {
            $impo = "CONCAT(DATE_FORMAT(p.FEC_PAG,'%y'),'-',p.ADUANAD,'-',p.PATENTE,'-',p.NUM_PED) AS Importacion";
            $aduana = "CONCAT(p.ADUANAD,p.SECCDES) AS Aduana";
            $fecha = "DATE_FORMAT(p.FEC_PAG,'%d/%m/%Y') AS Fecha";
            $fechaFac = "DATE_FORMAT(f.FECFAC,'%d/%m/%Y') AS FechaFactura";
            $fi = $fechaIni;
            $ff = $fechaFin;
        }
        $t = ($tipo == 'I') ? 1 : 2;
        if($tipo == 'I') {
            $select = "SELECT 
                p.NUM_REF AS Referencia,
                {$impo},
                {$aduana},
                {$fecha},
                p.TIP_CAM AS TipoCambio,
                p.IVA2_TOT AS IVA,
                p.CVEPEDIM AS Clave,
                p.REGIMEN AS Regimen,
                p.FLETES AS Fletes,
                p.SEGUROS AS Seguros,
                p.EMBALAJ AS Embalajes,
                p.OTROINC AS Otros,
                p.DTA_TOT AS DTA,  
                (fra.VALCOM * p.TIP_CAM) AS ValorComercial,
                p.VALADUANA AS ValorAduana,
                o.OBS AS Observaciones,
                p.CONSOLR AS Consolidado,
                '' AS Virtual,
                '' AS NotaInterna,
                p.PRE_TOT AS Prevalidacion,
                pro.NOMPRO AS NomProveedor,
                f.NUMFAC AS Factura,
                {$fechaFac},
                fra.VALCEQ AS FMoneda,
                fra.PARTE AS NumeroDeParte,
                fra.CODIGO AS FraccionImportacion,
                fra.TASAADV AS Tasa,
                /*'' AS TipoTasa,*/
                CASE
                  WHEN (cas.TIPCAS IS NOT NULL) THEN cas.TIPCAS
                  ELSE 'TG'
                END AS TipoTasa,
                un.ABREVIA AS Unidad,
                (fra.VALCOM / fra.CANTFAC) AS Precio,
                fra.CANTFAC AS Cantidad,    
                fra.PAIORI AS Origen,
                fra.PAICOM AS Vendedor,
                '' AS NotaInterna,
                fra.FPAGADV1 AS FormaPago,
                f.INCOTER AS Incoterm,
                '' AS PagaTLC,
                '' AS PagaTLCUEM,
                '' AS PagaTLCAELC,
                '' AS JustTLC,
                '' AS JustTLCUEM,
                '' AS JustTLCAELC,
                '' AS EB,
                '' AS MontoEB,
                '' AS EnConsignacion,
                '' AS NotaInterna2,
                '' AS Revision,
                CASE f.ACUSECOVE
                  WHEN '' THEN 'N'
                  ELSE f.ACUSECOVE
                END AS Cove
              FROM sm3ped AS p
              LEFT JOIN cm3fra AS c ON p.NUM_REF = c.NUM_REF
              LEFT JOIN sm3fra AS fra ON p.NUM_REF = fra.NUM_REF
              LEFT JOIN sm3fact AS f ON p.NUM_REF = f.NUM_REF
              LEFT JOIN sm3obs AS o ON p.NUM_REF = o.NUM_REF
              LEFT JOIN cmpro AS pro ON fra.CVE_PRO = pro.CVE_PRO
              LEFT JOIN cmum AS un ON fra.UMC = un.CLAVE
              LEFT JOIN sm3casos AS cas ON cas.ORDEN = fra.ORDEN AND cas.NUM_REF = fra.NUM_REF AND cas.TIPCAS <> 'EN'
              WHERE p.CVE_IMP LIKE '{$this->getCveImpo($rfc)}'
                AND p.FEC_PAG >= '{$fi}' AND p.FEC_PAG <= '{$ff}' AND (FIRMA IS NOT NULL AND FIRMA <> '')
                AND p.IMP_EXP = {$t};";
            return $select;
        } elseif ($tipo == 'E') {
            $select = "SELECT 
                p.NUM_REF AS Referencia,
                {$impo},
                {$aduana},
                {$fecha},
                p.TIP_CAM AS TipoCambio,
                p.IVA2_TOT AS IVA,
                p.CVEPEDIM AS Clave,
                p.REGIMEN AS Regimen,
                p.FLETES AS Fletes,
                p.SEGUROS AS Seguros,
                p.EMBALAJ AS Embalajes,
                p.OTROINC AS Otros,
                p.DTA_TOT AS DTA,  
                (fra.VALCOM * p.TIP_CAM) AS ValorComercial,
                p.VALADUANA AS ValorAduana,
                o.OBS AS Observaciones,
                p.CONSOLR AS Consolidado,
                '' AS Virtual,
                '' AS NotaInterna,
                p.PRE_TOT AS Prevalidacion,
                pro.NOMPRO AS NomProveedor,
                f.NUMFAC AS Factura,
                {$fechaFac},
                fra.VALCEQ AS FMoneda,
                fra.PARTE AS NumeroDeParte,
                fra.CODIGO AS FraccionImportacion,
                fra.TASAADV AS Tasa,
                /*'' AS TipoTasa,*/
                CASE
                  WHEN (cas.TIPCAS IS NOT NULL) THEN cas.TIPCAS
                  ELSE 'TG'
                END AS TipoTasa,
                un.ABREVIA AS Unidad,
                (fra.VALCOM / fra.CANTFAC) AS Precio,
                fra.CANTFAC AS Cantidad,    
                fra.PAIORI AS Origen,
                fra.PAICOM AS Vendedor,
                '' AS NotaInterna,
                fra.FPAGADV1 AS FormaPago,
                f.INCOTER AS Incoterm,
                '' AS PagaTLC,
                '' AS PagaTLCUEM,
                '' AS PagaTLCAELC,
                '' AS JustTLC,
                '' AS JustTLCUEM,
                '' AS JustTLCAELC,
                '' AS EB,
                '' AS MontoEB,
                '' AS EnConsignacion,
                '' AS NotaInterna2,
                '' AS Revision,
                CASE f.ACUSECOVE
                  WHEN '' THEN 'N'
                  ELSE f.ACUSECOVE
                END AS Cove
              FROM sm3ped AS p
              LEFT JOIN cm3fra AS c ON p.NUM_REF = c.NUM_REF
              LEFT JOIN sm3fra AS fra ON p.NUM_REF = fra.NUM_REF
              LEFT JOIN sm3fact AS f ON p.NUM_REF = f.NUM_REF
              LEFT JOIN sm3obs AS o ON p.NUM_REF = o.NUM_REF
              LEFT JOIN cmpro AS pro ON fra.CVE_PRO = pro.CVE_PRO
              LEFT JOIN cmum AS un ON fra.UMC = un.CLAVE
              LEFT JOIN sm3casos AS cas ON cas.ORDEN = fra.ORDEN AND cas.NUM_REF = fra.NUM_REF AND cas.TIPCAS <> 'EN'
              WHERE p.CVE_IMP LIKE '{$this->getCveImpo($rfc)}'
                AND p.FEC_PAG >= '{$fi}' AND p.FEC_PAG <= '{$ff}'
                AND p.IMP_EXP = {$t};";
            
            return $select;
        }
    }
        
    public function reporteAnexo24($sistema, $patente, $aduana, $sql)
    {
        // $report["sistema"] == 'sitawin' && $report["patente"] == 3589 && $report["aduana"] == 640        
        if($sistema == 'sitawin' && $patente == 3589 && $aduana == 640) {
            $sitawin = new OAQ_Sitawin(true, '192.168.200.5', 'sa', 'adminOAQ123', 'SITAW3010640', 1433,'Pdo_Mssql');
            $result = $sitawin->ejecutarQueryAnexo24SitaSql($sql);
            //$misc->saveCache('rptanexo24'.$this->_config->username, $result);
        }
        if($sistema == 'sitawin' && $patente == 3589 && $aduana == 646) {
            $sitawin = new OAQ_Sitawin(true, '192.168.0.253', 'sa', 'sqlcointer', 'SITAW3589640', 1433, 'Pdo_Mssql');
            $result = $sitawin->ejecutarQueryAnexo24SitaSql($sql);
            //$misc->saveCache('rptanexo24'.$this->_config->username, $result);
        }
        if($sistema == 'aduanet' && $patente == 3589 && $aduana == 240) {
            $aduanet = new OAQ_AduanetM3(true, '12.185.80.109', 'repqro', 'RepQro3589', 'saaiweb', 3306);
            $result = array();

            $pedimentos = $aduanet->ejecutarQueryAnexo24($sql);                                
            $slam = new OAQ_Slam('12.185.80.109','master','master','Aduana','SqlSrv',1433);

            foreach($pedimentos as $ped) {
                if($ped["Operacion"] == 'IMPO') {
                    $facturas = $slam->slamFactura($ped["FacturaNo"], $ped["Cove"]);
                    if($facturas && !empty($facturas)) {
                        foreach($facturas as $fact) {
                            $tmp = $ped;
                            $tmp["Divisa"] = $fact["Divisa"];
                            $tmp["FactorME"] = $fact["FactorME"];
                            $tmp["FechaFactura"] = $fact["FechaFactura"];
                            $tmp["Incoterm"] = $fact["Incoterm"];
                            $tmp["FraccionA"] = $fact["FraccionA"];
                            $tmp["ValorME"] = $fact["ValorME"];
                            $tmp["NumParte"] = $fact["NumParte"];
                            $tmp["Descripcion"] = strtoupper($fact["Descripcion"]);
                            $tmp["UMC"] = $fact["UMC"];
                            $tmp["AbrevUMC"] = $fact["AbrevUMC"];
                            $tmp["CantidadUMC"] = $fact["CantidadUMC"];
                            $tmp["UMT"] = $fact["UMT"];
                            $tmp["AbrevUMT"] = $fact["AbrevUMT"];
                            $tmp["CantUMT"] = $fact["CantUMT"];
                            $tmp["Origen"] = $fact["Origen"];
                            $tmp["Vendedor"] = $fact["Vendedor"];
                            $tmp["TLC"] = $fact["TLC"];
                            $tmp["IVA_"] = $fact["IVA_"];
                            $tmp["IEPS"] = $fact["IEPS"];
                            $tmp["ISAN"] = $fact["ISAN"];
                            $tmp["PesoBru"] = $fact["PesoBru"];                                                
                            /*$iden = $aduanet->informacionFraccion($ped["Trafico"], $fact["FraccionA"]);
                            Zend_Debug::dump($iden);
                            unset($iden);*/
                        }
                        $result[] = $tmp;
                        unset($tmp);
                        unset($fact);
                    } else {
                        $result[] = $ped;
                    }
                } else {
                    $result[] = $ped;
                }
            }
        }
        if(isset($sitawin)) unset($sitawin);
        if(isset($aduanet)) unset($aduanet);
        if(isset($slam)) unset($slam);
        
        if(isset($result)) {
            return $result;
        }
        return null;
    }
    
}
