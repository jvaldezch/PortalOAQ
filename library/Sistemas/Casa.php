<?php 

class Sistemas_Casa {

    protected $_db;
    
    function __construct($patente = null, $aduana = null) {
        try {
            if (APPLICATION_ENV == "production") {
                if (!isset($patente) && !isset($aduana)) {
                    $init = array(
                        'db' => '192.168.200.5:C:\CASAWIN\CSAAIWIN\Datos\casa.gdb',
                        'user' => 'Admin',
                        'pass' => 'admin',
                    );
                    $this->_db = ibase_connect($init["db"], $init["user"], $init["pass"]);
                }
            } else {
                $init = array(
                    'db' => 'localhost:C:\Tmp\CASA\CASA.GDB',
                    'user' => 'Admin',
                    'pass' => 'admin',
                );
                $this->_db = ibase_connect($init["db"], $init["user"], $init["pass"]);
            }
        } catch (Exception $ex) {
            throw new Exception("Firebird Error: " . $ex->getMessage());
        }
    }
    
    public function informacionBasica($patente, $aduana, $pedimento) {
        try {
            if (!Zend_Validate::is($patente, 'Int')) {
                return 'Invalid input';
            }
            if (!Zend_Validate::is($aduana, 'Int')) {
                return 'Invalid input';
            }
            if (!Zend_Validate::is($pedimento, 'Int')) {
                return 'Invalid input';
            }
            $sql = "SELECT "
                    . "P.NUM_REFE, "
                    . "P.CVE_PEDI, "
                    . "P.FIR_PAGO, "
                    . "P.FIR_ELEC, "
                    . "P.FEC_PAGO, "
                    . "P.NUM_REFE, "
                    . "P.REG_ADUA, "
                    . "P.CVE_PEDI, "
                    . "P.IMP_EXPO, "
                    . "P.CVE_IMPO, "
                    . "P.NUM_REFE, "
                    . "C.RFC_IMP, "
                    . "C.NOM_IMP "
                    . "FROM SAAIO_PEDIME P "
                    . "LEFT JOIN CTRAC_CLIENT C ON P.CVE_IMPO = C.CVE_IMP "
                    . "WHERE P.ADU_DESP = {$aduana} AND P.PAT_AGEN = {$patente} AND P.NUM_PEDI = {$pedimento} ROWS 1;";
            $stmt = ibase_query($this->_db, $sql);
            if (!$stmt) {
                return "Firebird error:" . ibase_errmsg();
            }
            $row = ibase_fetch_object($stmt);
            if ($row) {
                ibase_free_result($stmt);
                return array(
                    'referencia' => $row->NUM_REFE,
                    'cveDoc' => $row->CVE_PEDI,
                    'firmaBanco' => $row->FIR_PAGO,
                    'firmaValidacion' => $row->FIR_ELEC,
                    'fechaPago' => $row->FEC_PAGO,
                    'regimen' => $row->REG_ADUA,
                    'clavePedimento' => $row->CVE_PEDI,
                    'tipoOperacion' => $row->IMP_EXPO,
                    'importador' => $row->CVE_IMPO,
                    'rfcCliente' => $row->RFC_IMP,
                    'nomCliente' => mb_convert_encoding($row->NOM_IMP, 'UTF-8', 'ISO-8859-1'),
                );
            }
            return;
        } catch (Exception $ex) {
            throw new Exception("DB Exception: " . $ex->getMessage());
        }
    }
    
    public function facturas($patente, $aduana, $pedimento) {
        try {
            if (!Zend_Validate::is($patente, 'Int')) {
                return 'Invalid input';
            }
            if (!Zend_Validate::is($aduana, 'Int')) {
                return 'Invalid input';
            }
            if (!Zend_Validate::is($pedimento, 'Int')) {
                return 'Invalid input';
            }
            $query = "SELECT 
                P.NUM_PEDI,
                P.ADU_DESP,
                P.PAT_AGEN,
                P.CVE_PEDI,
                P.REG_ADUA,
                P.FEC_PAGO,
                P.FIR_ELEC,
                P.FIR_PAGO,
                P.NUM_OPER,
                P.CVE_IMPO,
                C.RFC_IMP,
                C.NOM_IMP,
                D.NOM_PRO,
                F.NUM_FACT,
                F.FEC_FACT,
                F.ICO_FACT,
                F.CVE_PROV,
                E.E_DOCUMENT AS COVE
                FROM SAAIO_PEDIME P
                LEFT JOIN SAAIO_FACTUR F ON F.NUM_REFE = P.NUM_REFE
                LEFT JOIN CTRAC_CLIENT C ON P.CVE_IMPO = C.CVE_IMP
                LEFT JOIN CTRAC_DESTIN D ON D.CVE_PRO = F.CVE_PROV
                LEFT JOIN SAAIO_COVE E ON E.NUM_REFE = F.NUM_REFE AND F.CONS_FACT = E.CONS_FACT
                WHERE P.NUM_PEDI = {$pedimento} AND P.PAT_AGEN = {$patente} AND P.ADU_DESP = {$aduana};";
            $result = ibase_query($this->_db, $query);
            if (!$result) {
                return "Firebird error:" . ibase_errmsg();
            }
            if ($result) {
                $data = array();
                while ($row = ibase_fetch_object($result)) {
                    $data[] = array(
                        'patente' => $row->PAT_AGEN,
                        'aduana' => $row->ADU_DESP,
                        'pedimento' => $row->NUM_PEDI,
                        'cveDoc' => $row->CVE_PEDI,
                        'regimen' => $row->REG_ADUA,
                        'fechaPago' => $row->FEC_PAGO,
                        'firmaValidacion' => $row->FIR_ELEC,
                        'firmaBanco' => $row->FIR_PAGO,
                        'numOperacion' => $row->NUM_OPER,
                        'cveImpo' => $row->CVE_IMPO,
                        'rfcCliente' => $row->RFC_IMP,
                        'nomCliente' => mb_convert_encoding($row->NOM_IMP, 'UTF-8', 'ISO-8859-1'),
                        'cveProv' => mb_convert_encoding($row->CVE_PROV, 'UTF-8', 'ISO-8859-1'),
                        'nomProveedor' => mb_convert_encoding($row->NOM_PRO, 'UTF-8', 'ISO-8859-1'),
                        'numFactura' => $row->NUM_FACT,
                        'cove' => $row->COVE,
                        'fechaFactura' => $row->FEC_FACT,
                        'incoterm' => $row->ICO_FACT,
                    );
                }
                ibase_free_result($result);
                return $data;
            } else {
                return false;
            }
        } catch (Exception $ex) {
            throw new Exception("DB Exception: " . $ex->getMessage());
        }
    }
    
    public function factura($patente, $aduana, $pedimento, $numFactura) {
        try {
            if (!Zend_Validate::is($patente, 'Int')) {
                return 'Invalid input';
            }
            if (!Zend_Validate::is($aduana, 'Int')) {
                return 'Invalid input';
            }
            if (!Zend_Validate::is($pedimento, 'Int')) {
                return 'Invalid input';
            }
            $sql = "SELECT 
                P.NUM_PEDI,
                P.IMP_EXPO,
                P.NUM_REFE,
                P.ADU_DESP,
                P.PAT_AGEN,
                P.CVE_PEDI,
                P.REG_ADUA,
                P.FEC_PAGO,
                P.FIR_ELEC,
                P.FIR_PAGO,
                P.NUM_OPER,
                P.CVE_IMPO,
                C.RFC_IMP,
                C.NOM_IMP,
                D.NOM_PRO,
                F.NUM_FACT,
                F.FEC_FACT,
                F.ICO_FACT,
                F.CONS_FACT,
                F.CVE_PROV,
                F.VAL_DLLS,
                F.VAL_EXTR,
                F.EQU_DLLS,
                F.MON_FACT,
                F.SUB_FACT,
                E.E_DOCUMENT AS COVE
                FROM SAAIO_PEDIME P
                LEFT JOIN SAAIO_FACTUR F ON F.NUM_REFE = P.NUM_REFE
                LEFT JOIN CTRAC_CLIENT C ON P.CVE_IMPO = C.CVE_IMP
                LEFT JOIN CTRAC_DESTIN D ON D.CVE_PRO = F.CVE_PROV
                LEFT JOIN SAAIO_COVE E ON E.NUM_REFE = F.NUM_REFE AND F.CONS_FACT = E.CONS_FACT
                WHERE P.NUM_PEDI = {$pedimento} AND P.PAT_AGEN = {$patente} AND P.ADU_DESP = {$aduana} AND F.NUM_FACT = '{$numFactura}';";
            $stmt = ibase_query($this->_db, $sql);
            if (!$stmt) {
                return "Firebird error:" . ibase_errmsg();
            }
            if ($stmt) {
                $array = array();
                while ($row = ibase_fetch_object($stmt)) {
                    $array = array(
                        'Patente' => $row->PAT_AGEN,
                        'Aduana' => $row->ADU_DESP,
                        'Pedimento' => $row->NUM_PEDI,
                        'Referencia' => $row->NUM_REFE,
                        'TipoOperacion' => ($row->IMP_EXPO == 1) ? 'TOCE.IMP' : 'TOCE.EXP',
                        'Subdivision' => $row->SUB_FACT,
                        'CveImp' => $row->CVE_IMPO,
                        'CvePro' => mb_convert_encoding($row->CVE_PROV, 'UTF-8', 'ISO-8859-1'),
                        'NumFactura' => $row->NUM_FACT,
                        'FechaFactura' => $row->FEC_FACT,
                        'OrdenFact' => $row->CONS_FACT,
                        'ValDls' => $row->EQU_DLLS * $row->VAL_EXTR,
                        'ValExt' => $row->VAL_EXTR,
                        'Divisa' => $row->MON_FACT,
                    );
                    if ($row->IMP_EXPO == 1) {
                        $rowc = $this->datosCliente($row->CVE_IMPO);
                        $rowp = $this->datosProveedor($row->CVE_PROV);
                        $array = array_merge($array, $rowc, $rowp);
                    }
                    if ($row->IMP_EXPO == 2) {
                        $rowc = $this->datosCliente($row->CVE_IMPO);
                        $rowp = $this->datosDestinatario($row->CVE_PROV);
                    }
                    $array["Observaciones"] = "";
                    $array["NumParte"] = "";
                    $array["CertificadoOrigen"] = "0";
                    $array["NumExportador"] = "";
                    $array["Manual"] = 0;
                }
                ibase_free_result($stmt);
                return $array;
            }
            return;
        } catch (Exception $ex) {
            throw new Exception("DB Exception: " . $ex->getMessage());
        }
    }

    public function productos($numFactura) {
        try {
            
        } catch (Exception $ex) {
            throw new Exception("DB Exception: " . $ex->getMessage());
        }
    }
    
    public function datosDestinatario($cveDestinario, $prefix = 'Pro') {
        try {
            $sql = "SELECT * "
                    . "FROM CTRAC_DESTIN "
                    . "WHERE CVE_PRO = '{$cveDestinario}';";
            $stmt = ibase_query($this->_db, $sql);
            if (!$stmt) {
                return "Firebird error:" . ibase_errmsg();
            }
            if ($stmt) {
                $array = array();
                while ($row = ibase_fetch_object($stmt)) {
                    $array = array(
                        $prefix . 'TaxID' => $row->TAX_PRO,
                        $prefix . 'Nombre' => mb_convert_encoding($row->NOM_PRO, 'UTF-8', 'ISO-8859-1'),
                        $prefix . 'Calle' => mb_convert_encoding($row->DIR_PRO, 'UTF-8', 'ISO-8859-1'),
                        $prefix . 'NumExt' => $row->NOE_PRO,
                        $prefix . 'NumInt' => $row->NOI_PRO,
                        $prefix . 'Colonia' => mb_convert_encoding($row->POB_PRO, 'UTF-8', 'ISO-8859-1'),
                        $prefix . 'Mun' => mb_convert_encoding($row->LOC_PRO, 'UTF-8', 'ISO-8859-1'),
                        $prefix . 'CP' => $row->ZIP_PRO,
                        $prefix . 'Edo' => $row->EFE_PRO,
                        $prefix . 'Pais' => $row->PAI_PRO,
                    );
                }
                return $array;
            }
        } catch (Exception $ex) {
            throw new Exception("DB Exception: " . $ex->getMessage());
        }
    }
    
    public function datosProveedor($cveProveedor, $prefix = 'Pro') {
        try {
            $sql = "SELECT * "
                    . "FROM CTRAC_PROVED "
                    . "WHERE CVE_PRO = '{$cveProveedor}';";
            $stmt = ibase_query($this->_db, $sql);
            if (!$stmt) {
                return "Firebird error:" . ibase_errmsg();
            }
            if ($stmt) {
                $array = array();
                while ($row = ibase_fetch_object($stmt)) {
                    $array = array(
                        $prefix . 'TaxID' => $row->TAX_PRO,
                        $prefix . 'Nombre' => mb_convert_encoding($row->NOM_PRO, 'UTF-8', 'ISO-8859-1'),
                        $prefix . 'Calle' => mb_convert_encoding($row->DIR_PRO, 'UTF-8', 'ISO-8859-1'),
                        $prefix . 'NumExt' => $row->NOE_PRO,
                        $prefix . 'NumInt' => $row->NOI_PRO,
                        $prefix . 'Colonia' => mb_convert_encoding($row->POB_PRO, 'UTF-8', 'ISO-8859-1'),
                        $prefix . 'Mun' => mb_convert_encoding($row->LOC_PRO, 'UTF-8', 'ISO-8859-1'),
                        $prefix . 'CP' => $row->ZIP_PRO,
                        $prefix . 'Edo' => $row->EFE_PRO,
                        $prefix . 'Pais' => $row->PAI_PRO,
                    );
                }
                return $array;
            }
        } catch (Exception $ex) {
            throw new Exception("DB Exception: " . $ex->getMessage());
        }
    }
    
    public function datosCliente($cveImportador, $prefix = 'Cte') {
        try {
            $sql = "SELECT * "
                    . "FROM CTRAC_CLIENT "
                    . "WHERE CVE_IMP = '{$cveImportador}';";
            $stmt = ibase_query($this->_db, $sql);
            if (!$stmt) {
                return "Firebird error:" . ibase_errmsg();
            }
            if ($stmt) {
                $array = array();
                while ($row = ibase_fetch_object($stmt)) {
                    $array = array(
                        $prefix . 'Rfc' => $row->RFC_IMP,
                        $prefix . 'Nombre' => mb_convert_encoding($row->NOM_IMP, 'UTF-8', 'ISO-8859-1'),
                        $prefix . 'Calle' => mb_convert_encoding($row->DIR_IMP, 'UTF-8', 'ISO-8859-1'),
                        $prefix . 'NumExt' => $row->NOE_IMP,
                        $prefix . 'NumInt' => $row->NOI_IMP,
                        $prefix . 'Colonia' => mb_convert_encoding($row->POB_IMP, 'UTF-8', 'ISO-8859-1'),
                        $prefix . 'Mun' => mb_convert_encoding($row->LOC_IMP, 'UTF-8', 'ISO-8859-1'),
                        $prefix . 'CP' => $row->CP_IMP,
                        $prefix . 'Edo' => $row->EFE_IMP,
                        $prefix . 'Pais' => $row->PAI_IMP,
                    );
                }
                return $array;
            }
        } catch (Exception $ex) {
            throw new Exception("DB Exception: " . $ex->getMessage());
        }
    }
    
    public function reportesAnexo4($patente, $aduana, $rfcCliente, $fechaIni, $fechaFin) {
        try {
            $sql = <<<SQL
SELECT
RIGHT(EXTRACT(YEAR FROM p.FEC_PAGO), 2) || '-' || cast(p.ADU_DESP as varchar(3)) || '-'  || cast(p.PAT_AGEN as varchar(4)) || '-'  || cast(p.NUM_PEDI as varchar(7)) AS operacion,
CASE p.IMP_EXPO WHEN 1 THEN 'IMP' ELSE 'EXP' END AS tipoOperacion,
p.PAT_AGEN as patente,
p.ADU_DESP AS aduana,
p.NUM_PEDI AS pedimento,
p.NUM_REFE AS trafico,
p.MTR_ENTR AS transporteEntrada,
p.MTR_ARRI AS transporteArribo,
p.MTR_SALI AS transporteSalida,
LPAD(EXTRACT( YEAR FROM p.FEC_ENTR), 4, '0') || '-' || LPAD(EXTRACT(MONTH FROM p.FEC_ENTR), 2, '0') || '-' || LPAD(EXTRACT(DAY FROM p.FEC_ENTR), 2, '0') AS fechaEntrada,
LPAD(EXTRACT( YEAR FROM p.FEC_PAGO), 4, '0') || '-' || LPAD(EXTRACT(MONTH FROM p.FEC_PAGO), 2, '0') || '-' || LPAD(EXTRACT(DAY FROM p.FEC_PAGO), 2, '0') AS fechaPago,
p.FIR_ELEC AS firmaValidacion,
p.FIR_PAGO AS firmaBanco,
p.TIP_CAMB AS tipoCambio,
p.CVE_PEDI AS cvePed,
p.REG_ADUA AS regimen,
p.ADU_ENTR AS aduanaEntrada,
p.VAL_DLLS AS valorDolares,
p.VAL_COME AS valorAduana,
COALESCE((select i.imp_incr from saaio_increm i where i.NUM_REFE = p.NUM_REFE and i.CVE_INCR = '1'),0) AS fletes,
COALESCE((select i.imp_incr from saaio_increm i where i.NUM_REFE = p.NUM_REFE and i.CVE_INCR = '2'),0) AS seguros,
COALESCE((select i.imp_incr from saaio_increm i where i.NUM_REFE = p.NUM_REFE and i.CVE_INCR = '3'),0) AS embalajes,
COALESCE((select i.imp_incr from saaio_increm i where i.NUM_REFE = p.NUM_REFE and i.CVE_INCR NOT IN ('1', '2',  '3')),0) AS otrosIncrementales,
(select coalesce(sum(coalesce(tot_impu,0)),0) from saaio_contped where num_refe = p.num_refe and cve_impu = '1') AS dta,
CASE
    WHEN extract(year from p.FEC_PAGO) > 2013 then ((select coalesce(sum(coalesce(tot_impu,0)),0) from saaio_contped where num_refe = p.num_refe and cve_impu = '15') - 54)
    else (select coalesce(sum(coalesce(tot_impu,0)),0) from saaio_contped where num_refe = p.num_refe and cve_impu = '15')
END AS prev,
CASE
    WHEN extract(year from P.FEC_PAGO) > 2013 then 54
    ELSE 0
END AS cnt,
(select coalesce(sum(coalesce(tot_impu,0)),0) from saaio_contfra where num_refe = p.num_refe and cve_impu = '6') as igi,
(select coalesce(sum(coalesce(tot_impu,0)),0) from saaio_contfra where num_refe = p.num_refe and cve_impu = '3') as iva,
p.TOT_INCR AS otrosIncrementales,
P.PES_BRUT AS pesoBruto,
P.CAN_BULT AS bultos,
p.TOT_EFEC AS totalEfectivo,
f.NUM_FACT AS numFactura,
co.E_DOCUMENT AS cove,
LPAD(EXTRACT( YEAR FROM f.FEC_FACT), 4, '0') || '-' || LPAD(EXTRACT(MONTH FROM f.FEC_FACT), 2, '0') || '-' || LPAD(EXTRACT(DAY FROM f.FEC_FACT), 2, '0') AS fechaFactura,
f.ICO_FACT AS incoterm,
f.VAL_DLLS AS valorFacturaUsd,
f.VAL_EXTR AS valorFacturaMonExt,
case p.IMP_EXPO
	when '1' then (select first 1 pro.tax_pro from saaio_factur fac join ctrac_proved pro on pro.cve_pro = fac.cve_prov where fac.num_refe = p.num_refe)
	when '2' then (select first 1 pro.tax_pro from saaio_factur fac join ctrac_destin pro on pro.cve_pro = fac.cve_prov where fac.num_refe = p.num_refe)
end as taxId,
case p.IMP_EXPO
	when '1' then (select first 1 pro.nom_pro from saaio_factur fac join ctrac_proved pro on pro.cve_pro = fac.cve_prov where fac.num_refe = p.num_refe)
	when '2' then (select first 1 pro.nom_pro from saaio_factur fac join ctrac_destin pro on pro.cve_pro = fac.cve_prov where fac.num_refe = p.num_refe)
end as nomProveedor,
f.MON_FACT AS divisa,
case p.IMP_EXPO
	when '1' then (select first 1 pro.PAI_PRO from saaio_factur fac join ctrac_proved pro on pro.cve_pro = fac.cve_prov where fac.num_refe = p.num_refe)
	when '2' then (select first 1 pro.PAI_PRO from saaio_factur fac join ctrac_destin pro on pro.cve_pro = fac.cve_prov where fac.num_refe = p.num_refe)
end as paisFactura,
f.EQU_DLLS AS factorMonExt,
a.NUM_PART AS numParte,
a.DES_MERC AS descripcion,
a.FRACCION AS fraccion,
a.CONS_PART AS ordenFraccion,
(a.mon_fact/a.can_fact) AS precioUnitario,
a.UNI_FACT AS umc,
a.CAN_FACT AS cantUmc,
a.UNI_TARI AS umt,
a.CAN_TARI AS cantUmt,
a.ADVAL AS tasaAdvalorem,
a.PAI_ORIG AS paisOrigen,
case
	when (select first 1 fra.CAS_TLCS from SAAIO_FRACCI fra 
		where fra.NUM_REFE = a.NUM_REFE AND fra.FRACCION = a.FRACCION AND fra.PAI_ORIG = a.PAI_ORIG AND fra.PAI_VEND = a.PAI_VEND) = 'TL' then 'S'
	else null
end AS tlc,
case
	when (select first 1 fra.CAS_TLCS from SAAIO_FRACCI fra 
		where fra.NUM_REFE = a.NUM_REFE AND fra.FRACCION = a.FRACCION AND fra.PAI_ORIG = a.PAI_ORIG AND fra.PAI_VEND = a.PAI_VEND) = 'PS' then 'S'
	else null
end AS prosec,
a.PAI_VEND AS paisVendedor,
(select first 1 des.PAT_AAOR from saaio_descar des where des.num_refe = p.num_refe) AS patenteOrig,
(select first 1 des.CVE_ADOR from saaio_descar des where des.num_refe = p.num_refe) AS aduanaOrig,
(select first 1 des.NUM_PEOR from saaio_descar des where des.num_refe = p.num_refe) AS pedimentoOrig
FROM SAAIO_PEDIME p
LEFT JOIN CTRAC_CLIENT c ON c.CVE_IMP = p.CVE_IMPO
LEFT JOIN SAAIO_FACTUR f ON f.NUM_REFE = p.NUM_REFE 
LEFT JOIN SAAIO_FACPAR a on a.NUM_REFE = p.NUM_REFE and a.CONS_FACT = f.CONS_FACT
LEFT JOIN SAAIO_COVE co on co.NUM_REFE = p.NUM_REFE and co.CONS_FACT = f.CONS_FACT
WHERE p.FEC_PAGO > '{$fechaIni}' AND p.FEC_PAGO < '{$fechaFin}'
AND (p.FIR_ELEC is not null AND p.FIR_PAGO is not null)
AND c.RFC_IMP = '{$rfcCliente}'
AND p.PAT_AGEN = '{$patente}'
AND p.ADU_DESP = '{$aduana}'
ORDER BY p.NUM_PEDI ASC;
SQL;
            $stmt = ibase_query($this->_db, $sql);
            if (!$stmt) {
                return "Firebird error:" . ibase_errmsg();
            }
            if ($stmt) {
                $array = array();
                while ($row = ibase_fetch_assoc($stmt)) {                    
                    $array[] = $row;
                }
                ibase_free_result($stmt);
                return $array;
            }
        } catch (Exception $ex) {
            throw new Exception("DB Exception: " . $ex->getMessage());
        }
    }

    public function reportesEncabezado($patente, $aduana, $rfcCliente, $fechaIni, $fechaFin) {
        try {
            $sql = <<<SQL
SELECT
RIGHT(EXTRACT(YEAR FROM p.FEC_PAGO), 2) || '-' || cast(p.ADU_DESP as varchar(3)) || '-'  || cast(p.PAT_AGEN as varchar(4)) || '-'  || cast(p.NUM_PEDI as varchar(7)) AS operacion,
CASE p.IMP_EXPO WHEN 1 THEN 'IMP' ELSE 'EXP' END AS tipoOperacion,
p.PAT_AGEN as patente,
p.ADU_DESP AS aduana,
p.NUM_PEDI AS pedimento,
p.NUM_REFE AS trafico,
p.MTR_ENTR AS transporteEntrada,
p.MTR_ARRI AS transporteArribo,
p.MTR_SALI AS transporteSalida,
LPAD(EXTRACT( YEAR FROM p.FEC_ENTR), 4, '0') || '-' || LPAD(EXTRACT(MONTH FROM p.FEC_ENTR), 2, '0') || '-' || LPAD(EXTRACT(DAY FROM p.FEC_ENTR), 2, '0') AS fechaEntrada,
LPAD(EXTRACT( YEAR FROM p.FEC_PAGO), 4, '0') || '-' || LPAD(EXTRACT(MONTH FROM p.FEC_PAGO), 2, '0') || '-' || LPAD(EXTRACT(DAY FROM p.FEC_PAGO), 2, '0') AS fechaPago,
p.FIR_ELEC AS firmaValidacion,
p.FIR_PAGO AS firmaBanco,
p.TIP_CAMB AS tipoCambio,
p.CVE_PEDI AS cvePed,
p.REG_ADUA AS regimen,
p.ADU_ENTR AS aduanaEntrada,
p.VAL_DLLS AS valorDolares,
p.VAL_COME AS valorAduana,
COALESCE((select i.imp_incr from saaio_increm i where i.NUM_REFE = p.NUM_REFE and i.CVE_INCR = '1'),0) AS fletes,
COALESCE((select i.imp_incr from saaio_increm i where i.NUM_REFE = p.NUM_REFE and i.CVE_INCR = '2'),0) AS seguros,
COALESCE((select i.imp_incr from saaio_increm i where i.NUM_REFE = p.NUM_REFE and i.CVE_INCR = '3'),0) AS embalajes,
COALESCE((select i.imp_incr from saaio_increm i where i.NUM_REFE = p.NUM_REFE and i.CVE_INCR NOT IN ('1', '2',  '3')),0) AS otrosIncrementales,
(select coalesce(sum(coalesce(tot_impu,0)),0) from saaio_contped where num_refe = p.num_refe and cve_impu = '1') AS dta,
CASE
    WHEN extract(year from p.FEC_PAGO) > 2013 then ((select coalesce(sum(coalesce(tot_impu,0)),0) from saaio_contped where num_refe = p.num_refe and cve_impu = '15') - 54)
    else (select coalesce(sum(coalesce(tot_impu,0)),0) from saaio_contped where num_refe = p.num_refe and cve_impu = '15')
END AS prev,
CASE
    WHEN extract(year from P.FEC_PAGO) > 2013 then 54
    ELSE 0
END AS cnt,
(select coalesce(sum(coalesce(tot_impu,0)),0) from saaio_contfra where num_refe = p.num_refe and cve_impu = '6') as igi,
(select coalesce(sum(coalesce(tot_impu,0)),0) from saaio_contfra where num_refe = p.num_refe and cve_impu = '3') as iva,
p.TOT_INCR AS otrosIncrementales,
P.PES_BRUT AS pesoBruto,
P.CAN_BULT AS bultos,
p.TOT_EFEC AS totalEfectivo
FROM SAAIO_PEDIME p
LEFT JOIN CTRAC_CLIENT c ON c.CVE_IMP = p.CVE_IMPO
WHERE p.FEC_PAGO > '{$fechaIni}' AND p.FEC_PAGO < '{$fechaFin}'
AND (p.FIR_ELEC is not null AND p.FIR_PAGO is not null)
AND c.RFC_IMP = '{$rfcCliente}'
AND p.PAT_AGEN = '{$patente}'
AND p.ADU_DESP = '{$aduana}'
ORDER BY p.NUM_PEDI ASC;
SQL;
            $stmt = ibase_query($this->_db, $sql);
            if (!$stmt) {
                return "Firebird error:" . ibase_errmsg();
            }
            if ($stmt) {
                $array = array();
                while ($row = ibase_fetch_assoc($stmt)) {                    
                    $array[] = $row;
                }
                ibase_free_result($stmt);
                return $array;
            }
        } catch (Exception $ex) {
            throw new Exception("DB Exception: " . $ex->getMessage());
        }
    }
    
}