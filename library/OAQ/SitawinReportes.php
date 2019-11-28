<?php

/**
 * Description of EmailNotifications
 *
 * @author Jaime E. Valdez <jvaldezch@gmail.com>
 */
class OAQ_SitawinReportes {

    protected $_db;
    protected $_dbName;
    protected $_username;
    protected $_password;
    protected $_host;
    protected $_port = 1433;
    protected $_adapter = 'Pdo_Mssql';

    function set_username($_username) {
        $this->_username = $_username;
    }

    function set_password($_password) {
        $this->_password = $_password;
    }

    function set_host($_host) {
        $this->_host = $_host;
    }

    function set_port($_port) {
        $this->_port = $_port;
    }

    function set_adapter($_adapter) {
        $this->_adapter = $_adapter;
    }

    function set_dbName($_dbName) {
        $this->_dbName = $_dbName;
    }

    function __construct() {
        
    }

    public function connect() {
        try {
            $this->_db = Zend_Db::factory(
                    $this->_adapter,
                    array(
                        'host' => $this->_host,
                        'username' => $this->_username,
                        'password' => $this->_password,
                        'dbname' => $this->_dbName,
                        'port' => $this->_port,
                    )
            );            
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("Zend DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        } catch (Exception $ex) {
            throw new Exception("Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    /**
     * 
     * @param int $patente
     * @param int $aduana
     * @param string $rfc
     * @param string $fechaIni
     * @param string $fechaFin
     * @param int $year
     * @param int $month
     * @return boolean
     * @throws Exception
     */
    public function encabezados($patente, $aduana, $rfc, $fechaIni = null, $fechaFin = null, $year = null, $month = null) {
        try {
            $select = $this->_db->select()
                    ->from(array('p' => 'sm3ped'), array(
                        "p.ADUANAD + p.SECCDES + '-' + CAST(p.PATENTE AS VARCHAR(4)) + '-' + CAST(p.NUM_PED AS VARCHAR(7)) AS operacion",
                        'p.PATENTE AS patente',
                        'p.NUM_PED AS pedimento',
                        '(p.ADUANAD + p.SECCDES) AS aduana',
                        'p.NUM_REF AS trafico',
                        'CONVERT(varchar, p.FEC_PAG, 121) AS fechaPago',
                        new Zend_Db_Expr("CASE WHEN p.IMP_EXP = 1 THEN 'TOCE.IMP' ELSE 'TOCE.EXP' END AS tipoOperacion"),
                        'P.NUM_REF AS referencia',
                        'P.MEDTRAS AS transporteEntrada',
                        'P.MEDTRAA AS transporteArribo',
                        'P.MEDTRAE AS transporteSalida',
                        'CONVERT(VARCHAR(10), p.FEC_ENT, 121) AS fechaEntrada',
                        'CONVERT(VARCHAR(10), P.FEC_PAG, 121) AS fechaPago ',
                        'P.FIRMA AS firmaValidacion',
                        'P.TIP_CAM AS tipoCambio',
                        'P.CVEPEDIM AS cvePed',
                        'P.REGIMEN AS regimen',
                        'P.ADUANAE AS aduanaEntrada',
                        'P.VALMEDLLS AS valorDolares',
                        'P.VALADUANA AS valorAduana',
                        'P.VALMN_PAG AS valorComercial',
                        'P.FLETES AS fletes',
                        'P.SEGUROS AS seguros',
                        'P.EMBALAJ AS embalajes',
                        'P.OTROINC AS otrosIncrementales',
                        'P.DTA_TOT AS dta',
                        'P.IVA1_TOT AS iva',
                        'P.IGIE_TOT AS igi',
                        'P.PRE_TOT AS prev',
                        'P.CNT_TOT AS cnt',
                        '((CASE P.DTA_FP WHEN 0 THEN P.DTA_TOT ELSE 0 end) +  (CASE P.DTA_FPADI WHEN 0 THEN  P.DTA_TLADI ELSE 0 end) + (CASE P.CC1_FP WHEN 0 THEN P.CC1_TOT ELSE 0 end) +  (CASE P.CC2_FP WHEN 0 THEN P.CC2_TOT ELSE 0 end) +  (CASE P.IVA1_FP WHEN 0 THEN P.IVA1_TOT ELSE 0 end) +  (CASE P.IVA2_FP WHEN 0 THEN P.IVA2_TOT ELSE 0 end) +  (CASE P.ISAN_FP WHEN 0 THEN P.ISAN_TOT ELSE 0 end) +  (CASE P.IEPS_FP WHEN 0 THEN P.IEPS_TOT ELSE 0 end) +  (CASE P.REC_FP WHEN 0 THEN P.REC_TOT ELSE 0 end) +  (CASE P.OTR_FP WHEN 0 THEN P.OTR_TOT ELSE 0 end) +  (CASE GAR_FP WHEN 0 THEN P.GAR_TOT ELSE 0 end) +  (CASE P.MUL_FP WHEN 0 THEN P.MUL_TOT ELSE 0 end) +  (CASE P.MUL2_FP WHEN 0 THEN P.MUL2_TOT ELSE 0 end) +  (CASE P.DTI_FP WHEN 0 THEN P.DTI_TOT ELSE 0 end) +  (CASE P.IGIR_FP WHEN 0 THEN P.IGIR_TOT ELSE 0 end) +  (CASE P.PRE_FP WHEN 0 THEN P.PRE_TOT ELSE 0 end) +  (CASE P.BSS_FP WHEN 0 THEN P.BSS_TOT ELSE 0 end) +  (CASE P.EUR_FP WHEN 0 THEN P.EUR_TOT ELSE 0 end) +  (CASE P.ECI_FP WHEN 0 THEN P.ECI_TOT ELSE 0 end) +  (CASE P.ITV_FP WHEN 0 THEN P.ITV_TOT ELSE 0 end) +  (CASE P.IGIR_FP2 WHEN 0 THEN P.IGIR_TOT2 ELSE 0 end) +  (CASE P.REC2_FP WHEN 0 THEN P.REC2_TOT ELSE 0 end)) AS totalEfectivo',
                        'P.PESBRU AS pesoBruto',
                        'P.BULTOS AS bultos',
                    ))
                    ->joinLeft(array('b' => 'saiban'), 'p.NUM_PED = b.DOCTO', array('b.FIRMA AS firmaBanco'))
                    ->where("p.FEC_PAG BETWEEN '" . date('Y-m-d', strtotime($fechaIni)) . "' AND '" . date('Y-m-d', strtotime($fechaFin)) . "'")
                    ->where('p.PATENTE = ?', $patente)
                    ->where('p.ADUANAD = ?', (int) substr($aduana, 0, 2))
                    ->where('p.RFCCTE = ?', $rfc)
                    ->where("b.FIRMA <> '' AND b.FIRMA IS NOT NULL");
            if (!isset($fechaIni) && !isset($fechaFin)) {
                $select->where('YEAR(p.FEC_PAG) = ?', $year)
                        ->where('MONTH(p.FEC_PAG) = ?', $month);
            }
            $result = $this->_db->fetchAll($select);
            if ($result) {
                return $result;
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("Zend DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        } catch (Exception $ex) {
            throw new Exception("Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    /**
     * 
     * @param int $patente
     * @param int $aduana
     * @param string $rfc
     * @param string $fechaIni
     * @param string $fechaFin
     * @param int $year
     * @param int $month
     * @return boolean
     * @throws Exception
     */
    public function prasad($patente, $aduana, $rfc, $fechaIni = null, $fechaFin = null, $year = null, $month = null) {
        try {
            $query = "SELECT\n"
                    . "p.ADUANAD + p.SECCDES + '-' + CAST(p.PATENTE AS VARCHAR(4)) + '-' + CAST(p.NUM_PED AS VARCHAR(7)) AS operacion\n"
                    . "FROM sm3ped p\n"
                    . "LEFT JOIN sm3fact f ON p.NUM_REF = f.NUM_REF\n"
                    . "LEFT JOIN cm3fra s ON p.NUM_REF = f.NUM_REF\n"
                    . "LEFT JOIN saiban b ON p.NUM_PED = b.DOCTO\n"
                    . "WHERE p.PATENTE = {$patente}\n"
                    . "AND p.ADUANAD = " . (int) substr($aduana, 0, 2) . "\n"
                    . "AND b.FIRMA <> '' AND b.FIRMA IS NOT NULL\n"
                    . "AND p.RFCCTE = '{$rfc}'\n"
                    . "AND p.FEC_PAG BETWEEN '" . date('Y-m-d', strtotime($fechaIni)) . "' AND '" . date('Y-m-d', strtotime($fechaFin)) . "';\n";
            echo $query;
            $result = $this->_db->fetchAll($query);
            if ($result) {
                return $result;
            }
        } catch (PDOException $ex) {
            throw new Exception("PDO Exception \n" . __METHOD__ . ":\n" . $ex->getMessage());
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("Zend DB Exception \n" . __METHOD__ . ":\n" . $ex->getMessage());
        } catch (Exception $ex) {
            throw new Exception("Exception \n" . __METHOD__ . ":\n" . $ex->getMessage());
        }
    }

}
