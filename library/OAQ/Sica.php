<?php

/**
 * Description of EmailNotifications
 *
 * @author Jaime E. Valdez <jvaldezch@gmail.com>
 */
class OAQ_Sica {

    protected $_host;
    protected $_db;
    protected $_user;
    protected $_password;
    protected $_conn;
    protected $_config;
    protected $_logger;

    function __construct() {
        try {
            $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
            $this->_logger = Zend_Registry::get("logDb");
            $this->_host = "192.168.200.5";
            $this->_db = "SICA";
            $this->_password = "adminOAQ123";
            $this->_user = "sa";
            $this->_conn = mssql_connect($this->_host, $this->_user, $this->_password);
            if ($this->_conn == false) {
                return false;
            }
            if (!mssql_select_db($this->_db, $this->_conn)) {
                return false;
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

    /**
     * 
     * @param String $rfc
     * @return String
     */
    public function getCustomerId($rfc) {
        try {
            $result = mssql_query("SELECT * FROM [SICA].[dbo].[Cliente] WHERE RFC LIKE '{$rfc}';", $this->_conn);
            if ($result) {
                $row = mssql_fetch_assoc($result);
                return $row["ClienteID"];
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
     * @param int $ClienteId
     * @return array
     */
    public function getCustomerName($ClienteId) {
        try {
            $result = mssql_query("SELECT * FROM Cliente WHERE ClienteID = {$ClienteId};", $this->_conn);
            $row = mssql_fetch_assoc($result);
            return array(
                "nombre" => $row["Nombre"],
                "rfc" => $row["RFC"],
            );
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

    /**
     * 
     * @param int $ClienteId
     * @param String $fechaIni
     * @param String $fechaFin
     * @return array
     */
    public function getInvoices($ClienteId, $fechaIni, $fechaFin) {
        try {
            $sql = "USE SICA
                SELECT 
                C.RFC AS RFCCliente,
                C.Nombre AS NomCliente,
                F.Fecha,
                F.Referencia,
                F.Pedimento,
                F.Regimen,
                F.FolioID,
                F.Patente,
                F.AduanaID,
                F.IE,
                F.Anticipo,
                F.Honorarios,
                F.ValorFactura,
                F.ValorAduana,
                F.IVA,
                F.FechaPedimento,
                F.RefFactura,
                F.Bultos,
                F.Total 
                FROM Factura F
                LEFT JOIN Cliente AS C ON C.ClienteID = F.ClienteID
                WHERE (CAST(F.Fecha AS date) >= '{$fechaIni}') and (CAST(F.Fecha AS date) <= '{$fechaFin}') AND F.ClienteID = {$ClienteId} AND F.Estatus = 'A' ORDER BY F.FolioID ASC;";
            $query = mssql_query($sql, $this->_conn);
            $data = array();
            while ($row = mssql_fetch_assoc($query)) {
                $data[] = array(
                    "fecha_factura" => date("Y/m/d", strtotime($row["Fecha"])),
                    "referencia" => $row["Referencia"],
                    "pedimento" => $row["Pedimento"],
                    "rfc" => $row["RFCCliente"],
                    "nomCliente" => $row["NomCliente"],
                    "regimen" => $row["Regimen"],
                    "factura" => $row["FolioID"],
                    "patente" => $row["Patente"],
                    "aduana" => $row["AduanaID"],
                    "ie" => $row["IE"],
                    "anticipo" => $row["Anticipo"],
                    "honorarios" => $row["Honorarios"],
                    "valor" => $row["ValorFactura"],
                    "valor_aduana" => $row["ValorAduana"],
                    "iva" => $row["IVA"],
                    "fecha_pedimento" => date("Y/m/d", strtotime($row["FechaPedimento"])),
                    "ref_factura" => $row["RefFactura"],
                    "bultos" => $row["Bultos"],
                    "total" => $row["Total"],
                    "subtotal" => ($row["Total"] - $row["IVA"]),
                    "conceptos" => $this->getAllConcepts($row["FolioID"]),
                );
            }
            return $data;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("Zend DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        } catch (Exception $ex) {
            throw new Exception("Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    /**
     * 
     * @param string $rfc
     * @param String $fechaIni
     * @param String $fechaFin
     * @return array
     */
    public function getInvoicesByRfc($rfc, $fechaIni, $fechaFin) {
        try {
            $sql = "USE SICA
                SELECT 
                C.RFC AS RFCCliente,
                C.Nombre AS NomCliente,
                F.Fecha,
                F.Referencia,
                F.Pedimento,
                F.Regimen,
                F.FolioID,
                F.Patente,
                F.AduanaID,
                F.IE,
                F.Anticipo,
                F.Honorarios,
                F.ValorFactura,
                F.ValorAduana,
                F.IVA,
                F.FechaPedimento,
                F.RefFactura,
                F.Bultos,
                F.Total 
                FROM Factura F
                LEFT JOIN Cliente AS C ON C.ClienteID = F.ClienteID
                WHERE (CAST(F.Fecha AS date) >= '{$fechaIni}') and (CAST(F.Fecha AS date) <= '{$fechaFin}') AND C.RFC = '{$rfc}' AND F.Estatus = 'A' ORDER BY F.FolioID ASC;";
            $query = mssql_query($sql, $this->_conn);
            $data = array();
            while ($row = mssql_fetch_assoc($query)) {
                $data[] = array(
                    "fecha_factura" => date("Y/m/d", strtotime($row["Fecha"])),
                    "referencia" => $row["Referencia"],
                    "pedimento" => $row["Pedimento"],
                    "rfc" => $row["RFCCliente"],
                    "nomCliente" => $row["NomCliente"],
                    "regimen" => $row["Regimen"],
                    "factura" => $row["FolioID"],
                    "patente" => $row["Patente"],
                    "aduana" => $row["AduanaID"],
                    "ie" => $row["IE"],
                    "anticipo" => $row["Anticipo"],
                    "honorarios" => $row["Honorarios"],
                    "valor" => $row["ValorFactura"],
                    "valor_aduana" => $row["ValorAduana"],
                    "iva" => $row["IVA"],
                    "fecha_pedimento" => date("Y/m/d", strtotime($row["FechaPedimento"])),
                    "ref_factura" => $row["RefFactura"],
                    "bultos" => $row["Bultos"],
                    "total" => $row["Total"],
                    "subtotal" => ($row["Total"] - $row["IVA"]),
                    "conceptos" => $this->getAllConcepts($row["FolioID"]),
                );
            }
            return $data;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("Zend DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        } catch (Exception $ex) {
            throw new Exception("Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function getInvoicesAll($fechaIni, $fechaFin) {
        try {
            $sql = "USE SICA
                SELECT
                C.RFC AS RFCCliente,
                C.Nombre AS NomCliente,
                F.Fecha,
                F.Referencia,
                F.Pedimento,
                F.Regimen,
                F.FolioID,
                F.Patente,
                F.AduanaID,
                F.IE,
                F.Anticipo,
                F.Honorarios,
                F.ValorFactura,
                F.ValorAduana,
                F.IVA,
                F.FechaPedimento,
                F.RefFactura,
                F.Bultos,
                F.Total 
                FROM Factura AS F
                LEFT JOIN Cliente AS C ON C.ClienteID = F.ClienteID
                WHERE (CAST(F.Fecha AS date) >= '{$fechaIni}') and (CAST(F.Fecha AS date) <= '{$fechaFin}') AND F.Estatus = 'A' 
                ORDER BY F.FolioID ASC;";          
            $query = mssql_query($sql, $this->_conn);        
            $data = array();
            while ($row = mssql_fetch_assoc($query)) {
                $data[] = array(
                    "fecha_factura" => date("Y/m/d", strtotime($row["Fecha"])),
                    "referencia" => $row["Referencia"],
                    "pedimento" => $row["Pedimento"],
                    "rfc" => $row["RFCCliente"],
                    "nomCliente" => $row["NomCliente"],
                    "regimen" => $row["Regimen"],
                    "factura" => $row["FolioID"],
                    "patente" => $row["Patente"],
                    "aduana" => $row["AduanaID"],
                    "ie" => $row["IE"],
                    "anticipo" => $row["Anticipo"],
                    "honorarios" => $row["Honorarios"],
                    "valor" => $row["ValorFactura"],
                    "valor_aduana" => $row["ValorAduana"],
                    "iva" => $row["IVA"],
                    "fecha_pedimento" => date("Y/m/d", strtotime($row["FechaPedimento"])),
                    "ref_factura" => $row["RefFactura"],
                    "bultos" => $row["Bultos"],
                    "total" => $row["Total"],
                    "subtotal" => ($row["Total"] - $row["IVA"]),
                    "conceptos" => $this->getAllConcepts($row["FolioID"]),
                );
            }
            return $data;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

    public function getInvoiceDetails($folio) {
        try {
            $sql = "USE SICA
                SELECT 
                Fecha
                ,Referencia
                ,Pedimento
                ,Regimen
                ,FolioID
                ,Patente
                ,AduanaID
                ,IE
                ,Anticipo
                ,Honorarios
                ,ValorFactura
                ,ValorAduana
                ,UsuarioID
                ,IVA
                ,FechaPedimento
                ,RefFactura
                ,Bultos
                ,Total
                FROM Factura 
                WHERE FolioID = {$folio} AND Estatus = 'A';";
            $query = mssql_query($sql, $this->_conn);
            $data = array();
            while ($row = mssql_fetch_assoc($query)) {
                $data[] = array(
                    "fecha_factura" => date("d/m/Y", strtotime($row["Fecha"])),
                    "referencia" => $row["Referencia"],
                    "pedimento" => $row["Pedimento"],
                    "regimen" => $row["Regimen"],
                    "factura" => $row["FolioID"],
                    "patente" => $row["Patente"],
                    "aduana" => $row["AduanaID"],
                    "ie" => $row["IE"],
                    "anticipo" => $row["Anticipo"],
                    "honorarios" => $row["Honorarios"],
                    "valor" => $row["ValorFactura"],
                    "valor_aduana" => $row["ValorAduana"],
                    "usuarios" => $row["UsuarioID"],
                    "iva" => $row["IVA"],
                    "fecha_pedimento" => date("d/m/Y", strtotime($row["FechaPedimento"])),
                    "ref_factura" => $row["RefFactura"],
                    "bultos" => $row["Bultos"],
                    "total" => $row["Total"],
                    "subtotal" => ($row["Total"] - $row["IVA"]),
                    "conceptos" => $this->getAllConcepts($folio),
                );
            }
            return $data;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

    /**
     * 
     * @param int $FolioID
     * @return array
     */
    protected function getConcepts($FolioID) {
        try {
            $Query = "USE SICA
                SELECT 
                F.* 
                ,C.Nombre
                FROM FacturaGastos F
                LEFT JOIN Conceptos C ON F.ConceptoID = C.ConceptoID
                WHERE F.FolioID = {$FolioID}";
            $result = mssql_query($Query, $this->_conn);
            $conceptos = array(
                "GASTOS COMPLEMENTARIOS" => "gastos_complementarios",
                "GASTOS COMPLEMENTARIOS ALIJADORES" => "gastos_alijadores",
                "GASTOS COMPLEMENTARIOS MANIOBRAS" => "gastos_maniobras",
                "GASTOS COMPLEMENTARIOS DEMORAS" => "gastos_demoras",
                "GASTOS COMPLEMENTARIOS ALMACENAJE" => "gastos_almacenajes",
                "RECTIFICACIONES" => "rectificaciones",
                "IMPUESTOS ADUANALES" => "impuestos_aduanales",
                "REVALIDACION" => "revalidacion",
                "MANIOBRAS" => "maniobras",
                "ALMACENAJE" => "almacenaje",
                "DEMORAS" => "demoras",
                "FLETE AEREO" => "fleteaereo",
                "FLETE MARITIMO" => "fletemaritimo",
                "FLETE TERRESTRE" => "fleteterrestre",
                "FLETES Y ACARREOS" => "fletesacarreos"
            );
            $data = array();
            while ($row = mssql_fetch_assoc($result)) {
                foreach ($conceptos as $k => $v):
                    if ($row["Nombre"] == $k) {
                        $data[$v] = round($row["Importe"], 2);
                        $data["subtotal_" . $v] = round($row["Importe"] / 1.16, 2);
                        $data["iva_" . $v] = round($row["Importe"] - ($row["Importe"] / 1.16), 2);
                    }
                endforeach;
            }
            return $data;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

    /**
     * 
     * @param int $FolioID
     * @return array
     */
    public function getAllConcepts($FolioID) {
        try {
            $sql = "USE SICA
                SELECT 
                C.Nombre
                ,F.Tipo
                ,F.MonedaID
                ,F.Importe / 1.16 AS SubTotal
                ,F.Importe - (F.Importe / 1.16) AS IVA
                ,F.Importe
                FROM FacturaGastos F
                LEFT JOIN Conceptos C ON F.ConceptoID = C.ConceptoID
                WHERE F.FolioID = {$FolioID}";
            $result = mssql_query($sql, $this->_conn);
            $data = array();
            while ($row = mssql_fetch_assoc($result)) {
                $k = trim(strtolower(preg_replace("/\s+/", "_", str_replace(array(".", ",", ":", ";"), '', $row["Nombre"]))));
                $data[$k] = array(
                    "moneda" => $row["MonedaID"],
                    "subtotal" => $row["SubTotal"],
                    "iva" => $row["IVA"],
                    "total" => $row["Importe"],
                );
            }
            return $data;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

    public function getPronosticoCobranzaDesglose($fecha, $rfc = null, $sum = null) {
        try {
            $fechaLim = date("Y-m-d", strtotime($fecha));
            if (!$sum) {
                $Query = "USE SICA
                    SET LANGUAGE us_english;
                    DECLARE	@date	nvarchar (100)
                    DECLARE	@init	nvarchar (100)
                    DECLARE	@rfc	nvarchar (100)
                    SET	@date	=	'{$fechaLim} 00:15:15'
                    SET	@init	=	'2013-01-01 00:15:15'
                    SET @rfc	=	'%{$rfc}%'
                    SELECT
                    FolioID
                    ,RelacionID
                    ,Referencia
                    ,Regimen
                    ,FechaFactura
                    ,FechaAcuse
                    ,FechaPago
                    ,ClienteID
                    ,Nombre
                    ,RFC
                    ,EsHonorarios
                    ,Plazo
                    ,Dias
                    ,CASE 
                            WHEN Anticipo = 0 THEN Comprobados
                            WHEN (Anticipo > 0) AND ((Anticipo - Comprobados) > 0)  THEN 0
                            WHEN (Anticipo > 0) AND ((Anticipo - Comprobados) <= 0)  THEN ABS(Anticipo - Comprobados)
                            ELSE Comprobados
                    END AS Comprobados
                    ,CASE 
                            WHEN Anticipo = 0 THEN Complementarios
                            WHEN (Anticipo > 0) AND (Complementarios > 0) AND ((Anticipo - (Comprobados+Complementarios)) > 0)  THEN 0
                            WHEN (Anticipo > 0) AND (Complementarios > 0) AND ((Anticipo - Comprobados) > 0) AND ((Anticipo - (Comprobados+Complementarios)) <= 0)  THEN ABS(Anticipo - (Comprobados+Complementarios))
                            ELSE Complementarios
                    END AS Complementarios
                    ,CASE 
                            WHEN Anticipo = 0 THEN Honorarios
                            WHEN (Anticipo > 0) AND (Honorarios > 0) AND ((Anticipo - (Comprobados+Complementarios+Honorarios)) > 0)  THEN 0
                            WHEN (Anticipo > 0) AND (Honorarios > 0) AND ((Anticipo - Comprobados) > 0) AND ((Anticipo - (Comprobados+Complementarios)) > 0) AND ((Anticipo - (Comprobados+Complementarios+Honorarios)) <= 0)  THEN ABS(Anticipo - (Comprobados+Complementarios+Honorarios))
                            ELSE Honorarios
                    END AS Honorarios
                    ,CASE 
                            WHEN Anticipo = 0 THEN IVA
                            WHEN (Anticipo > 0) AND (Honorarios > 0) AND ((Anticipo - (Comprobados+Complementarios+Honorarios+IVA)) > 0)  THEN 0
                            WHEN (Anticipo > 0) AND (Honorarios > 0) AND ((Anticipo - (Comprobados+Complementarios)) > 0) AND ((Anticipo - (Comprobados+Complementarios+Honorarios)) > 0) AND ((Anticipo - (Comprobados+Complementarios)) > 0) AND ((Anticipo - (Comprobados+Complementarios+Honorarios+IVA)) <= 0)  THEN ABS(Anticipo - (Comprobados+Complementarios+Honorarios+IVA))
                            ELSE IVA
                    END AS IVA
                    ,Total
                    FROM (SELECT 
                            FolioID
                            ,RelacionID
                            ,Referencia
                            ,Regimen
                            ,CONVERT(char(10),FechaFactura,126) AS FechaFactura
                            ,CONVERT(char(10),FechaAcuse,126) AS FechaAcuse
                            ,CONVERT(char(10),DATEADD(day,Plazo,FechaAcuse),126) AS FechaPago
                            ,ClienteID
                            ,Nombre
                            ,RFC
                            ,EsHonorarios
                            ,Plazo
                            ,DATEDIFF(day,CONVERT(char(10),DATEADD(day,Plazo,FechaAcuse),126),@date) AS Dias
                            ,CASE
                               WHEN Comprobados IS NULL THEN 0
                               ELSE Comprobados
                            END AS Comprobados
                            ,CASE
                               WHEN Complementarios IS NULL THEN 0
                               ELSE Complementarios
                            END AS Complementarios
                            ,Honorarios
                            ,IVA
                            ,SubTotal
                            ,Anticipo
                            ,Total 
                            FROM (
                                    SELECT 
                                    F.FolioID
                                    ,R.RelacionID
                                    ,D.Referencia
                                    ,C.Regimen
                                    ,F.Fecha AS FechaFactura
                                    ,R.Fecha AS FechaAcuse
                                    ,CLI.ClienteID
                                    ,CLI.Nombre
                                    ,CLI.RFC
                                    ,F.Honorarios
                                    ,F.IVA
                                    ,F.SubTotal
                                    ,F.Anticipo
                                    ,EsHonorarios = CASE CHARINDEX('H', D.Referencia) WHEN 0 THEN '' ELSE 'S' END
                                    ,(CASE
                                            WHEN (P.Dias = 0) THEN 30
                                            WHEN (CHARINDEX('H', D.Referencia) > 0 AND CLI.RFC = 'BAP060906LEA') THEN 30
                                            WHEN (CHARINDEX('H', D.Referencia) > 0 AND CLI.RFC = 'MME921204HZ4') THEN 45
                                            ELSE P.Dias
                                    END) AS Plazo
                                    ,(SELECT 
                                            SUM(Abono - Cargo) AS Saldo
                                            FROM Diario AS D
                                            WHERE D.Referencia = F.Referencia AND D.MovimientoID = 'CXC'
                                            AND ((D.CuentaID >= 5101000000000000 AND D.CuentaID < 5102000000000000)
                                            OR (D.CuentaID >= 2101000000000000 AND D.CuentaID < 2102000000000000)) AND D.Estatus = 'A' AND D.Descripcion NOT LIKE "%HONORARIOS%"
                                    GROUP BY D.Referencia) AS Complementarios
                                    ,(SELECT 
                                                    SUM(Abono - Cargo) AS Saldo
                                            FROM Diario AS D
                                            WHERE D.Referencia = F.Referencia AND D.MovimientoID = 'CXC'
                                            AND ((D.CuentaID >= 1120000000000000 AND D.CuentaID < 1130000000000000) OR (D.CuentaID >= 2202000000000000 AND D.CuentaID < 2203000000000000)) AND D.Estatus = 'A'
                                    GROUP BY D.Referencia) AS Comprobados
                                    ,D.Saldo AS Total FROM (
                                            SELECT Referencia, CuentaID, SUM(Cargo - Abono) AS Saldo FROM (
                                                    SELECT Referencia, CuentaID, SUM(Cargo) AS Cargo, SUM(Abono) AS Abono
                                                    FROM Diario
                                                    WHERE CuentaID >= 1104000000000000 AND CuentaID < 1105000000000000 AND Estatus = 'A'
                                                    GROUP BY Referencia, CuentaID
                                    ) AS D
                                    GROUP BY Referencia, CuentaID
                                    ) AS D
                                    LEFT JOIN RelacionCuentas AS C ON D.Referencia = C.Referencia
                                    LEFT JOIN RelacionCtas AS R ON C.RelacionID = R.RelacionID
                                    LEFT JOIN Factura AS F ON F.FolioID = C.FolioID
                                    LEFT JOIN Cliente AS CLI ON CLI.ClienteID = R.ClienteID
                                    LEFT JOIN ClienteADD AS DD ON CLI.ClienteID = DD.ClienteID
                                    LEFT JOIN DetalleCliente AS De ON CLI.ClienteID = De.ClienteID
                                    LEFT JOIN Plazo AS P ON De.PlazoID = P.PlazoID
                                    WHERE R.Fecha IS NOT NULL AND Saldo > 0 AND CLI.RFC LIKE @rfc AND F.FechaCancel IS NULL
                            ) AS F
                            WHERE CONVERT(char(10),DATEADD(day,Plazo,FechaAcuse),126) <= @date AND FechaFactura >= @init
                            GROUP BY FolioID, Referencia,SubTotal,Anticipo,IVA,Honorarios,Comprobados,Complementarios, Total, Regimen, FechaFactura, RelacionID, FechaAcuse, Nombre, RFC, ClienteID, Plazo, EsHonorarios
                    ) AS FIN;";
                $result = mssql_query($Query, $this->_conn);
                if ($result) {
                    $data = array();
                    while ($row = mssql_fetch_assoc($result)) {
                        $data[] = array(
                            "cliente" => $row["Nombre"],
                            "folioid" => $row["FolioID"],
                            "fechafactura" => date("d/m/Y", strtotime($row["FechaFactura"])),
                            "relacionid" => $row["RelacionID"],
                            "fecha_acuse" => date("d/m/Y", strtotime($row["FechaAcuse"])),
                            "referencia" => $row["Referencia"],
                            "total" => $row["Total"],
                            "comprobados" => $row["Comprobados"],
                            "complementarios" => $row["Complementarios"],
                            "honorarios" => $row["Honorarios"],
                            "iva" => $row["IVA"],
                        );
                    }
                    return $data;
                } else {
                    die("MSSQL error: " . mssql_get_last_message());
                }
            } else {
                $Query = "USE SICA
                    SET LANGUAGE us_english;
                    DECLARE	@date	nvarchar (100)
                    DECLARE	@init	nvarchar (100)
                    DECLARE	@rfc	nvarchar (100)
                    SET	@date	=	'{$fechaLim} 00:15:15'
                    SET	@init	=	'2013-01-01 00:15:15'
                    SET @rfc	=	'%{$rfc}%'
                    SELECT
                    Nombre
                    ,SUM(Comprobados) AS Comprobados
                    ,SUM(Complementarios) AS Complementarios
                    ,SUM(Honorarios) AS Honorarios
                    ,SUM(IVA) AS IVA
                    ,SUM(Total) AS Total
                    FROM (SELECT
                            FolioID
                            ,RelacionID
                            ,Referencia
                            ,Regimen
                            ,FechaFactura
                            ,FechaAcuse
                            ,FechaPago
                            ,ClienteID
                            ,Nombre
                            ,RFC
                            ,EsHonorarios
                            ,Plazo
                            ,Dias
                            ,CASE 
                                    WHEN Anticipo = 0 THEN Comprobados
                                    WHEN (Anticipo > 0) AND ((Anticipo - Comprobados) > 0)  THEN 0
                                    WHEN (Anticipo > 0) AND ((Anticipo - Comprobados) <= 0)  THEN ABS(Anticipo - Comprobados)
                                    ELSE Comprobados
                            END AS Comprobados
                            ,CASE 
                                    WHEN Anticipo = 0 THEN Complementarios
                                    WHEN (Anticipo > 0) AND (Complementarios > 0) AND ((Anticipo - (Comprobados+Complementarios)) > 0)  THEN 0
                                    WHEN (Anticipo > 0) AND (Complementarios > 0) AND ((Anticipo - Comprobados) > 0) AND ((Anticipo - (Comprobados+Complementarios)) <= 0)  THEN ABS(Anticipo - (Comprobados+Complementarios))
                                    ELSE Complementarios
                            END AS Complementarios
                            ,CASE 
                                    WHEN Anticipo = 0 THEN Honorarios
                                    WHEN (Anticipo > 0) AND (Honorarios > 0) AND ((Anticipo - (Comprobados+Complementarios+Honorarios)) > 0)  THEN 0
                                    WHEN (Anticipo > 0) AND (Honorarios > 0) AND ((Anticipo - Comprobados) > 0) AND ((Anticipo - (Comprobados+Complementarios)) > 0) AND ((Anticipo - (Comprobados+Complementarios+Honorarios)) <= 0)  THEN ABS(Anticipo - (Comprobados+Complementarios+Honorarios))
                                    ELSE Honorarios
                            END AS Honorarios
                            ,CASE 
                                    WHEN Anticipo = 0 THEN IVA
                                    WHEN (Anticipo > 0) AND (Honorarios > 0) AND ((Anticipo - (Comprobados+Complementarios+Honorarios+IVA)) > 0)  THEN 0
                                    WHEN (Anticipo > 0) AND (Honorarios > 0) AND ((Anticipo - (Comprobados+Complementarios)) > 0) AND ((Anticipo - (Comprobados+Complementarios+Honorarios)) > 0) AND ((Anticipo - (Comprobados+Complementarios)) > 0) AND ((Anticipo - (Comprobados+Complementarios+Honorarios+IVA)) <= 0)  THEN ABS(Anticipo - (Comprobados+Complementarios+Honorarios+IVA))
                                    ELSE IVA
                            END AS IVA
                            ,Total
                            FROM (SELECT 
                                    FolioID
                                    ,RelacionID
                                    ,Referencia
                                    ,Regimen
                                    ,CONVERT(char(10),FechaFactura,126) AS FechaFactura
                                    ,CONVERT(char(10),FechaAcuse,126) AS FechaAcuse
                                    ,CONVERT(char(10),DATEADD(day,Plazo,FechaAcuse),126) AS FechaPago
                                    ,ClienteID
                                    ,Nombre
                                    ,RFC
                                    ,EsHonorarios
                                    ,Plazo
                                    ,DATEDIFF(day,CONVERT(char(10),DATEADD(day,Plazo,FechaAcuse),126),@date) AS Dias
                                    ,CASE
                                       WHEN Comprobados IS NULL THEN 0
                                       ELSE Comprobados
                                    END AS Comprobados
                                    ,CASE
                                       WHEN Complementarios IS NULL THEN 0
                                       ELSE Complementarios
                                    END AS Complementarios
                                    ,Honorarios
                                    ,IVA
                                    ,SubTotal
                                    ,Anticipo
                                    ,Total 
                                    FROM (
                                            SELECT 
                                            F.FolioID
                                            ,R.RelacionID
                                            ,D.Referencia
                                            ,C.Regimen
                                            ,F.Fecha AS FechaFactura
                                            ,R.Fecha AS FechaAcuse
                                            ,CLI.ClienteID
                                            ,CLI.Nombre
                                            ,CLI.RFC
                                            ,F.Honorarios
                                            ,F.IVA
                                            ,F.SubTotal
                                            ,F.Anticipo
                                            ,EsHonorarios = CASE CHARINDEX('H', D.Referencia) WHEN 0 THEN '' ELSE 'S' END
                                            ,(CASE
                                                    WHEN (P.Dias = 0) THEN 30
                                                    WHEN (CHARINDEX('H', D.Referencia) > 0 AND CLI.RFC = 'BAP060906LEA') THEN 30
                                                    WHEN (CHARINDEX('H', D.Referencia) > 0 AND CLI.RFC = 'MME921204HZ4') THEN 45
                                                    ELSE P.Dias
                                            END) AS Plazo
                                            ,(SELECT 
                                                    SUM(Abono - Cargo) AS Saldo
                                                    FROM Diario AS D
                                                    WHERE D.Referencia = F.Referencia AND D.MovimientoID = 'CXC'
                                                    AND ((D.CuentaID >= 5101000000000000 AND D.CuentaID < 5102000000000000)
                                                    OR (D.CuentaID >= 2101000000000000 AND D.CuentaID < 2102000000000000)) AND D.Estatus = 'A' AND D.Descripcion NOT LIKE "%HONORARIOS%"
                                            GROUP BY D.Referencia) AS Complementarios
                                            ,(SELECT 
                                                            SUM(Abono - Cargo) AS Saldo
                                                    FROM Diario AS D
                                                    WHERE D.Referencia = F.Referencia AND D.MovimientoID = 'CXC'
                                                    AND ((D.CuentaID >= 1120000000000000 AND D.CuentaID < 1130000000000000) OR (D.CuentaID >= 2202000000000000 AND D.CuentaID < 2203000000000000)) AND D.Estatus = 'A'
                                            GROUP BY D.Referencia) AS Comprobados
                                            ,D.Saldo AS Total FROM (
                                                    SELECT Referencia, CuentaID, SUM(Cargo - Abono) AS Saldo FROM (
                                                            SELECT Referencia, CuentaID, SUM(Cargo) AS Cargo, SUM(Abono) AS Abono
                                                            FROM Diario
                                                            WHERE CuentaID >= 1104000000000000 AND CuentaID < 1105000000000000 AND Estatus = 'A'
                                                            GROUP BY Referencia, CuentaID
                                            ) AS D
                                            GROUP BY Referencia, CuentaID
                                            ) AS D
                                            LEFT JOIN RelacionCuentas AS C ON D.Referencia = C.Referencia
                                            LEFT JOIN RelacionCtas AS R ON C.RelacionID = R.RelacionID
                                            LEFT JOIN Factura AS F ON F.FolioID = C.FolioID
                                            LEFT JOIN Cliente AS CLI ON CLI.ClienteID = R.ClienteID
                                            LEFT JOIN ClienteADD AS DD ON CLI.ClienteID = DD.ClienteID
                                            LEFT JOIN DetalleCliente AS De ON CLI.ClienteID = De.ClienteID
                                            LEFT JOIN Plazo AS P ON De.PlazoID = P.PlazoID
                                            WHERE R.Fecha IS NOT NULL AND Saldo > 0 AND CLI.RFC LIKE @rfc AND F.FechaCancel IS NULL
                                    ) AS F
                                    WHERE CONVERT(char(10),DATEADD(day,Plazo,FechaAcuse),126) <= @date AND FechaFactura >= @init
                                    GROUP BY FolioID, Referencia,SubTotal,Anticipo,IVA,Honorarios,Comprobados,Complementarios, Total, Regimen, FechaFactura, RelacionID, FechaAcuse, Nombre, RFC, ClienteID, Plazo, EsHonorarios
                    ) AS FIN ) AS SUM
                    GROUP BY Nombre ORDER BY Total DESC;";
                $result = mssql_query($Query, $this->_conn);
                if ($result) {
                    $data = array();
                    while ($row = mssql_fetch_assoc($result)) {
                        $data[] = array(
                            "cliente" => $row["Nombre"],
                            "total" => $row["Total"],
                            "comprobados" => $row["Comprobados"],
                            "complementarios" => $row["Complementarios"],
                            "honorarios" => $row["Honorarios"],
                            "iva" => $row["IVA"],
                        );
                    }
                    return $data;
                } else {
                    throw new Exception("MSSQL error: " . mssql_get_last_message() . " on " . __METHOD__);
                }
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

    public function getPronosticoCobranza($rfc = null, $date = null, $sum = null) {
        try {
            $date = date("Y-m-d", strtotime($date));
            if (!$sum) {
                $date = date("Y-m-d", strtotime($date));
                $Query = "USE SICA
                    DECLARE	@date	nvarchar (100)
                    DECLARE	@init	nvarchar (100)
                    DECLARE	@rfc	nvarchar (100)
                    SET	@date	=	'{$date} 00:15:15'
                    SET @rfc	=	'%{$rfc}%'
                    SELECT FolioID
                    ,RelacionID
                    ,Referencia
                    ,Regimen
                    ,CONVERT(char(10),FechaFactura,126) AS FechaFactura
                    ,CONVERT(char(10),FechaAcuse,126) AS FechaAcuse
                    ,CONVERT(char(10),DATEADD(day,Plazo,FechaAcuse),126) AS FechaPago
                    ,ClienteID
                    ,Nombre
                    ,RFC
                    ,Honorarios
                    ,Plazo
                    ,DATEDIFF(day,CONVERT(char(10),DATEADD(day,Plazo,FechaAcuse),126),@date) AS Dias
                    ,Total 
                    FROM (SELECT
                        CLI.Nombre
                        ,CLI.RFC 
                        ,F.*
                        ,Honorarios = CASE CHARINDEX('H', Referencia) WHEN 0 THEN '' ELSE 'S' END
                        ,(CASE
                            WHEN (P.Dias = 0) THEN 30
                            WHEN (CHARINDEX('H', Referencia) > 0 AND CLI.RFC = 'BAP060906LEA') THEN 30
                            WHEN (CHARINDEX('H', Referencia) > 0 AND CLI.RFC = 'MME921204HZ4') THEN 45
                            ELSE P.Dias
                        END) AS Plazo
                        ,Saldo AS Total
                        FROM (SELECT Referencia, Regimen, ClienteID, RelacionID, CuentaID, FolioID, FechaFactura, FechaAcuse, SUM(Cargo - Abono) AS Saldo 
                            FROM (SELECT D.Referencia, D.CuentaID, SUM(D.Cargo) AS Cargo, SUM(D.Abono) AS Abono, C.Regimen, R.RelacionID, R.ClienteID, F.FolioID, F.Fecha AS FechaFactura, R.Fecha AS FechaAcuse
                                FROM Diario D
                                LEFT JOIN RelacionCuentas C ON D.Referencia = C.Referencia
                                LEFT JOIN RelacionCtas R ON C.RelacionID = R.RelacionID
                                LEFT JOIN Factura F ON F.FolioID = C.FolioID
                                WHERE D.CuentaID >= 1104000000000000 AND D.CuentaID < 1105000000000000 AND D.Estatus = 'A' AND F.FolioID IS NOT NULL
                                GROUP BY D.Referencia, D.CuentaID, C.Regimen, R.RelacionID, F.FolioID, F.Fecha, R.Fecha, R.ClienteID
                            ) AS D GROUP BY Referencia, CuentaID, Regimen, RelacionID, FolioID, FechaFactura, FechaAcuse, ClienteID) 
                        AS F
                        LEFT JOIN Cliente CLI ON CLI.ClienteID = F.ClienteID
                        LEFT JOIN DetalleCliente De ON CLI.ClienteID = De.ClienteID
                        LEFT JOIN Plazo P ON De.PlazoID = P.PlazoID
                        WHERE Saldo > 1) AS X
                    WHERE CONVERT(char(10),DATEADD(day,Plazo,FechaAcuse),126) <= @date AND RFC LIKE @rfc AND DATEDIFF(day,CONVERT(char(10),DATEADD(day,Plazo,FechaAcuse),126),@date) > 0
                    GROUP BY FolioID, Referencia, Total, Regimen, FechaFactura, RelacionID, FechaAcuse, Nombre, RFC, ClienteID, Plazo, Honorarios;";
                $result = mssql_query($Query, $this->_conn);
                if ($result) {
                    $data = array();
                    while ($row = mssql_fetch_assoc($result)) {
                        $fechaPronostico = strtotime("+30 days", strtotime($row["FechaAcuse"]));
                        $data[] = array(
                            "cliente" => $row["Nombre"],
                            "plazo" => $row["Plazo"],
                            "vencimiento" => $row["Dias"],
                            "clienteid" => $row["ClienteID"],
                            "folioid" => $row["FolioID"],
                            "fechafactura" => date("d/m/Y", strtotime($row["FechaFactura"])),
                            "relacionid" => $row["RelacionID"],
                            "fecha_acuse" => date("d/m/Y", strtotime($row["FechaAcuse"])),
                            "fecha_pronostico" => date("d/m/Y", $fechaPronostico),
                            "referencia" => $row["Referencia"],
                            "regimen" => $row["Regimen"],
                            "total" => $row["Total"],
                        );
                    }
                    return $data;
                } else {
                    throw new Exception("MSSQL error: " . mssql_get_last_message() . " on " . __METHOD__);
                }
            } else {
                $Query = "USE SICA
                    DECLARE	@date	nvarchar (100)
                    DECLARE	@init	nvarchar (100)
                    DECLARE	@rfc	nvarchar (100)
                    SET	@date	=	'{$date} 00:15:15'
                    SET @rfc	=	'%{$rfc}%'
                    SELECT Nombre, SUM(Total) AS Total
                    FROM (SELECT FolioID
                        ,RelacionID
                        ,Referencia
                        ,Regimen
                        ,CONVERT(char(10),FechaFactura,126) AS FechaFactura
                        ,CONVERT(char(10),FechaAcuse,126) AS FechaAcuse
                        ,CONVERT(char(10),DATEADD(day,Plazo,FechaAcuse),126) AS FechaPago
                        ,ClienteID
                        ,Nombre
                        ,RFC
                        ,Honorarios
                        ,Plazo
                        ,DATEDIFF(day,CONVERT(char(10),DATEADD(day,Plazo,FechaAcuse),126),@date) AS Dias
                        ,Total 
                        FROM (SELECT
                            CLI.Nombre
                            ,CLI.RFC 
                            ,F.*
                            ,Honorarios = CASE CHARINDEX('H', Referencia) WHEN 0 THEN '' ELSE 'S' END
                            ,(CASE
                                WHEN (P.Dias = 0) THEN 30
                                WHEN (CHARINDEX('H', Referencia) > 0 AND CLI.RFC = 'BAP060906LEA') THEN 30
                                WHEN (CHARINDEX('H', Referencia) > 0 AND CLI.RFC = 'MME921204HZ4') THEN 45
                                ELSE P.Dias
                            END) AS Plazo
                            ,Saldo AS Total
                            FROM (SELECT Referencia, Regimen, ClienteID, RelacionID, CuentaID, FolioID, FechaFactura, FechaAcuse, SUM(Cargo - Abono) AS Saldo 
                                FROM (SELECT D.Referencia, D.CuentaID, SUM(D.Cargo) AS Cargo, SUM(D.Abono) AS Abono, C.Regimen, R.RelacionID, R.ClienteID, F.FolioID, F.Fecha AS FechaFactura, R.Fecha AS FechaAcuse
                                    FROM Diario D
                                    LEFT JOIN RelacionCuentas C ON D.Referencia = C.Referencia
                                    LEFT JOIN RelacionCtas R ON C.RelacionID = R.RelacionID
                                    LEFT JOIN Factura F ON F.FolioID = C.FolioID
                                    WHERE D.CuentaID >= 1104000000000000 AND D.CuentaID < 1105000000000000 AND D.Estatus = 'A' AND F.FolioID IS NOT NULL
                                    GROUP BY D.Referencia, D.CuentaID, C.Regimen, R.RelacionID, F.FolioID, F.Fecha, R.Fecha, R.ClienteID
                                ) AS D GROUP BY Referencia, CuentaID, Regimen, RelacionID, FolioID, FechaFactura, FechaAcuse, ClienteID) 
                            AS F
                            LEFT JOIN Cliente CLI ON CLI.ClienteID = F.ClienteID
                            LEFT JOIN DetalleCliente De ON CLI.ClienteID = De.ClienteID
                            LEFT JOIN Plazo P ON De.PlazoID = P.PlazoID
                            WHERE Saldo > 1) AS X
                        WHERE CONVERT(char(10),DATEADD(day,Plazo,FechaAcuse),126) <= @date AND RFC LIKE @rfc AND DATEDIFF(day,CONVERT(char(10),DATEADD(day,Plazo,FechaAcuse),126),@date) > 0
                        GROUP BY FolioID, Referencia, Total, Regimen, FechaFactura, RelacionID, FechaAcuse, Nombre, RFC, ClienteID, Plazo, Honorarios) AS S
                    GROUP BY Nombre ORDER BY Total DESC;";
                $result = mssql_query($Query, $this->_conn);
                $data = array();
                while ($row = mssql_fetch_assoc($result)) {
                    $data[] = array(
                        "cliente" => $row["Nombre"],
                        "total" => $row["Total"],
                    );
                }
                return $data;
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

    public function loadCustomers() {
        try {
            $Query = "SELECT Nombre, RFC FROM [SICA].[dbo].[Cliente] WHERE RFC IS NOT NULL ORDER BY Nombre ASC;";
            $result = mssql_query($Query, $this->_conn);
            $data = array();
            while ($row = mssql_fetch_assoc($result)) {
                $data[] = array(
                    "cliente_nombre" => $row["Nombre"],
                    "cliente_rfc" => $row["RFC"],
                );
            }
            return $data;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

    public function getAllSicaCustomer() {
        try {
            $Query = "SELECT ClienteID, NumeroInterno, Nombre, RFC, Email FROM [SICA].[dbo].[Cliente] WHERE ClienteID > 0;";
            $result = mssql_query($Query, $this->_conn);
            $data = array();
            while ($row = mssql_fetch_assoc($result)) {
                $data[] = array(
                    "sica_id" => (int) $row["ClienteID"],
                    "sica_num_interno" => (int) $row["NumeroInterno"],
                    "cliente_nombre" => $row["Nombre"],
                    "cliente_rfc" => $row["RFC"],
                    "email" => $row["Email"],
                );
            }
            return $data;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

    public function getCustomerDetailInfo($clienteId) {
        try {
            $Query = "SELECT CLI.*, DD.*
                        FROM [SICA].[dbo].[Cliente] AS CLI
                        LEFT JOIN [SICA].[dbo].[ClienteADD] AS DD ON CLI.ClienteID = DD.ClienteID
                        WHERE CLI.ClienteID = {$clienteId};";
            $result = mssql_query($Query, $this->_conn);
            $data = array();
            if ($result) {
                $row = mssql_fetch_assoc($result);
                $data = array(
                    "sica_id" => (int) $row["ClienteID"],
                    "sica_num_interno" => (int) $row["NumeroInterno"],
                    "cliente_nombre" => $row["Nombre"],
                    "cliente_rfc" => $row["RFC"],
                    "email" => $row["Email"],
                );
            }
            return $data;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

    /**
     * Provides customer data detail info from SICA database
     * @param String $rfc RFC string
     * @return array|null
     */
    public function getCustomerDetailInfoByRFC($rfc) {
        try {
            $Query = "SELECT * FROM [SICA].[dbo].[Cliente]
                WHERE RFC LIKE '{$rfc}'";
            $result = mssql_query($Query, $this->_conn);
            if ($result) {
                $row = mssql_fetch_assoc($result);
                if ($row) {
                    $data = array(
                        "sica_id" => (int) $row["ClienteID"],
                        "cliente_nombre" => $row["Nombre"],
                        "rfc" => $row["RFC"],
                        "curp" => $row["CURP"],
                        "direccion" => $row["Direccion"],
                        "colonia" => $row["Colonia"],
                        "ciudad" => $row["Ciudad"],
                        "telefono" => $row["Telefono"],
                        "contacto" => $row["CC"],
                        "atencion" => $row["ATTN"],
                        "email" => $row["Email"],
                        "cp" => $row["CP"],
                    );
                    return $data;
                }
                return null;
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

    public function getCustomersDetail() {
        try {
            $Query = "SELECT C.[ClienteID]
                        ,C.[Nombre]
                        ,C.[RFC]
                        ,C.[Direccion]
                        ,C.[Colonia]
                        ,C.[CP]
                        ,C.[Telefono]
                        ,C.[Fax]
                        ,C.[CC]
                        ,C.[ATTN]
                        ,C.[Email]
                        ,C.[NUMINT]
                        ,C.[NUMEXT]
                        ,D.PlazoID
                        ,P.Dias
                    FROM [SICA].[dbo].[Cliente] AS C
                    LEFT JOIN [SICA].[dbo].[DetalleCliente] AS D ON C.ClienteID = D.ClienteID
                    LEFT JOIN [SICA].[dbo].[Plazo] AS P ON D.PlazoID = P.PlazoID";
            $result = mssql_query($Query, $this->_conn);
            if ($result) {
                while ($row = mssql_fetch_assoc($result)) {
                    $data[] = array(
                        "ClienteID" => (int) $row["ClienteID"],
                        "Nombre" => $row["Nombre"],
                        "RFC" => $row["RFC"],
                        "Direccion" => $row["Direccion"],
                        "Colonia" => $row["Colonia"],
                        "Telefono" => $row["Telefono"],
                        "Contacto" => $row["CC"],
                        "Atencion" => $row["ATTN"],
                        "Email" => $row["Email"],
                        "NumInt" => $row["NUMINT"],
                        "NumExt" => $row["NUMEXT"],
                        "CP" => $row["CP"],
                        "Dias" => $row["Dias"],
                    );
                }
                return $data;
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

    public function getInvoiceInfo($id) {
        try {
            $Query = "SELECT TOP 1
                [Referencia]
                ,[Pedimento]
                ,[ClienteID]
                ,[FolioID]
                ,[Patente]
                ,[AduanaID]
                ,datepart(YYYY,Fecha) AS Fecha
            FROM [SICA].[dbo].[Factura]
            WHERE [SICA].[dbo].[Factura].[FolioID] = {$id}";
            $result = mssql_query($Query, $this->_conn);
            if ($result) {
                $row = mssql_fetch_assoc($result);
                $data = array(
                    "Referencia" => $row["Referencia"],
                    "Pedimento" => $row["Pedimento"],
                    "ClienteID" => $row["ClienteID"],
                    "FolioID" => $row["FolioID"],
                    "Patente" => $row["Patente"],
                    "AduanaID" => $row["AduanaID"],
                    "Year" => $row["Fecha"],
                );
                return $data;
            } else {
                return null;
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

    public function getCheckupCorrespondents($opcion) {
        try {
            $where = "Saldo > 0";
            if ($opcion == "1") {
                $where = "Abono = 0 AND Saldo > 0";
            } elseif ($opcion == "2") {
                $where = "Abono > 0 AND Saldo > 0";
            }
            $Query = "SELECT Corresponsal, Cliente, Referencia, FechaEnvio, Fecha, Cargo, Abono, Saldo 
                    FROM (
                            SELECT Corresponsal, Cliente, Referencia, MIN(FechaEnvio) AS FechaEnvio, MAX(Fecha) AS Fecha, SUM(Cargo) AS Cargo, SUM(Abono) AS Abono, SUM(Cargo - Abono) AS Saldo
                              FROM (
                              SELECT DISTINCT MIN(D.Fecha) AS FechaEnvio, MAX(D.Fecha) AS Fecha, D.Cargo, D.Abono, D.PolizaID, D.CuentaID, D.Referencia, D.Estatus, CU.Nombre AS Corresponsal, CL.Nombre AS Cliente
                              FROM [SICA].[dbo].[Diario] AS D
                              LEFT JOIN [SICA].[dbo].[SolicitudCheques] AS SC ON SC.Referencia = D.Referencia
                              LEFT JOIN [SICA].[dbo].[Cliente] AS CL ON CL.ClienteID = SC.ClienteID
                              LEFT JOIN [SICA].[dbo].[Cuentas] AS CU ON CU.CuentaID = D.CuentaID
                              WHERE D.CuentaID >= 1105000000000000 AND D.CuentaID < 1106000000000000	  
                              GROUP BY D.Fecha, D.Cargo, D.Abono, D.PolizaID, D.CuentaID, D.Referencia, D.Estatus, CU.Nombre, CL.Nombre
                              ) AS Suma
                            GROUP BY Corresponsal, Cliente, Referencia
                    ) AS Final
                    WHERE {$where} AND Fecha > '01-01-2013'
                    ORDER BY Fecha ASC;";
            $result = mssql_query($Query, $this->_conn);
            if ($result) {
                while ($row = mssql_fetch_assoc($result)) {
                    $data[] = array(
                        "corresponsal" => $row["Corresponsal"],
                        "referencia" => $row["Referencia"],
                        "cargo" => $row["Cargo"],
                        "abono" => $row["Abono"],
                        "saldo" => $row["Saldo"],
                        "fecha" => date("Y/m/d", strtotime($row["Fecha"])),
                        "fechaEnvio" => date("Y/m/d", strtotime($row["FechaEnvio"])),
                        "cliente" => ($row["Cliente"] == null) ? $this->findClientIDByPolicyID($this->findPolicyIDByReference($row["Referencia"])) : $row["Cliente"]
                    );
                }
                return $data;
            } else {
                return null;
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

    public function findPolicyIDByReference($reference) {
        try {
            $Query = "USE SICA
                    SELECT D.PolizaID
                    FROM Diario AS D
                    WHERE D.Referencia = '{$reference}' AND D.CuentaID >= 1105000000000000 AND D.CuentaID < 1106000000000000 AND D.PolizaID > 200000 AND D.PolizaID < 300000";
            $result = mssql_query($Query, $this->_conn);
            if ($result) {
                $row = mssql_fetch_assoc($result);
                return $row["PolizaID"];
            } else {
                return null;
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

    public function findClientIDByPolicyID($policyid) {
        try {
            $Query = "SELECT CL.Nombre
                    FROM Cheques AS CH
                    LEFT JOIN Cliente AS CL ON CL.ClienteID = CH.ClienteID
                    WHERE PolizaID = {$policyid} AND CH.ProveedorID = 0 AND CH.ClienteID > 0";

            $result = mssql_query($Query, $this->_conn);
            if ($result) {
                $row = mssql_fetch_assoc($result);
                return $row["Nombre"];
            } else {
                return null;
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

    public function getLastReferences($cuentaId) {
        try {
            $Query = "USE SICA
                    SELECT TOP 500 D.CuentaID, D.Referencia,DATENAME(yyyy, D.FechaModificacion) AS Year, D.FechaModificacion, C.Nombre
                    FROM Diario AS D
                    LEFT JOIN Cuentas AS C ON C.CuentaID = D.CuentaID
                    WHERE D.Referencia IS NOT NULL AND D.Referencia <> '' AND D.CuentaID = {$cuentaId} AND D.FechaModificacion > '01-08-2013'
                    GROUP BY D.Referencia, D.CuentaID, D.FechaModificacion, C.Nombre
                    ORDER BY D.FechaModificacion DESC;";
            $result = mssql_query($Query, $this->_conn);
            if ($result) {
                $data = array();
                while ($row = mssql_fetch_assoc($result)) {
                    $data[] = array(
                        "referencia" => $row["Referencia"],
                        "year" => $row["Year"],
                        "fecha" => $row["FechaModificacion"],
                    );
                }
                return $data;
            } else {
                return null;
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

    public function searchForReference($ref, $patente = null, $aduana = null) {
        try {
            if (!isset($patente) && !isset($aduana)) {
                $Query = "USE SICA
                            SELECT DISTINCT Referencia, Year, Patente, AduanaID, MAX(Fecha) AS FechaModificacion FROM (
                                    SELECT D.Referencia,DATENAME(yyyy, D.FechaModificacion) AS Year, D.FechaModificacion AS Fecha, F.Patente, F.AduanaID
                                    FROM Diario AS D
                                    LEFT JOIN Cuentas AS C ON C.CuentaID = D.CuentaID
                                    LEFT JOIN Factura AS F ON D.Referencia = F.Referencia
                                    WHERE D.Referencia IS NOT NULL AND D.Referencia LIKE '%{$ref}%' AND D.FechaModificacion > '01-01-2013'
                                    GROUP BY D.Referencia, F.Patente, F.AduanaID,D.FechaModificacion    
                        ) AS R
                        GROUP BY Referencia, Year, Patente, AduanaID
                        ORDER BY FechaModificacion ASC;";
            } else {
                $Query = "USE SICA
                        SELECT TOP 1 D.CuentaID, D.Referencia,DATENAME(yyyy, D.FechaModificacion) AS Year, D.FechaModificacion, C.Nombre, F.Patente, F.AduanaID
                        FROM Diario AS D
                        LEFT JOIN Cuentas AS C ON C.CuentaID = D.CuentaID
                        LEFT JOIN Factura AS F ON D.Referencia = F.Referencia
                        WHERE D.Referencia IS NOT NULL AND D.Referencia LIKE '%{$ref}%' AND F.Patente = {$patente} AND F.AduanaID = {$aduana} AND D.FechaModificacion > '01-08-2013'
                        GROUP BY D.Referencia, D.CuentaID, D.FechaModificacion, C.Nombre, F.Patente, F.AduanaID
                        ORDER BY D.FechaModificacion DESC;";
            }
            $result = mssql_query($Query, $this->_conn);
            if ($result) {
                $data = array();
                while ($row = mssql_fetch_assoc($result)) {
                    $data[] = array(
                        "referencia" => $row["Referencia"],
                        "patente" => $row["Patente"],
                        "aduana" => $row["AduanaID"],
                        "year" => $row["Year"],
                        "fecha" => $row["FechaModificacion"],
                    );
                }
                return $data;
            } else {
                return null;
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

    public function searchInvoice($ref) {
        try {
            $Query = "USE SICA
                    SELECT TOP 1 Referencia, Pedimento, Patente, AduanaID
                      FROM Factura WHERE Referencia LIKE '{$ref}';";
            $result = mssql_query($Query, $this->_conn);
            if ($result) {
                $data = array();
                while ($row = mssql_fetch_assoc($result)) {
                    $data[] = array(
                        "referencia" => $row["Referencia"],
                        "pedimento" => $row["Pedimento"],
                        "patente" => $row["Patente"],
                        "aduana" => $row["AduanaID"],
                    );
                }
                return $data;
            } else {
                return null;
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

    public function getLastCustomers() {
        try {
            $Query = "USE SICA
                    SELECT TOP 50 
                      CONVERT(VARCHAR(24),FechaAlta,120) AS Alta,
                      CONVERT(VARCHAR(24),FechaModificacion,120) AS Modificacion,
                      (
                        SELECT TOP 1 F.Fecha 
                        FROM Factura AS F
                        WHERE C.ClienteID = F.ClienteID
                      ) AS UltimaFactura,
                      *
                      FROM Cliente AS C
                      LEFT JOIN ClienteADD AS D ON D.ClienteID = C.ClienteID
                      ORDER BY FechaAlta DESC;";
            $result = mssql_query($Query, $this->_conn);
            if ($result) {
                while ($row = mssql_fetch_assoc($result)) {
                    $data[] = array(
                        "fechaalta" => $row["Alta"],
                        "fechamod" => $row["Modificacion"],
                        "ultfact" => $row["UltimaFactura"],
                        "clienteid" => $row["ClienteID"],
                        "nombre" => $row["Nombre"],
                        "rfc" => $row["RFC"],
                        "direccion" => $row["Direccion"],
                        "colonia" => $row["Colonia"],
                        "entidadid" => $row["EntidadID"],
                        "ciudad" => $row["Ciudad"],
                        "paisid" => $row["PaisID"],
                        "cp" => $row["CP"],
                        "telefono" => $row["Telefono"],
                        "fax" => $row["Fax"],
                        "cc" => $row["CC"],
                        "numint" => $row["NUMINT"],
                        "numext" => $row["NUMEXT"],
                        "obs" => $row["Observaciones"],
                        "teltrafico" => $row["TelTrafico"],
                        "obstra" => $row["ObservacionesTrafico"],
                        "domcobro" => $row["DomicilioCobro"],
                        "domentrega" => $row["DomicilioEntrega"],
                        "obscartera" => $row["ObservacionesCartera"],
                    );
                }
                return $data;
            } else {
                return null;
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

    public function tiemposDeComprobacion($rfc, $cuentaId, $fechaIni, $fechaFin) {
        try {
            $init = date("Y/m/d", strtotime($fechaIni));
            $end = date("Y/m/d", strtotime($fechaFin));
            $Query = "USE SICA
                DECLARE @cuenta varchar(16);
                DECLARE @rfc varchar(16);
                DECLARE @init varchar(10);
                DECLARE @end varchar(10);
                SET @rfc = '%{$rfc}%';
                SET @init = '{$init}';
                SET @end = '{$end}';
                SET @cuenta = '{$cuentaId}';
                SELECT FolioID, Referencia,Regimen,
                    CONVERT(char(10),FechaFactura,126) AS FechaFactura,
                    CONVERT(char(10),FechaAcuse,126) AS FechaAcuse, 
                    CONVERT(char(10),DATEADD(day,Plazo,FechaAcuse),126) AS FechaPago,Plazo 
                FROM (
                    SELECT 
                        F.FolioID, R.RelacionID, D.Referencia, C.Regimen,F.Fecha AS FechaFactura, R.Fecha AS FechaAcuse, Honorarios =
                        CASE CHARINDEX('H', D.Referencia)
                            WHEN 0 THEN ''
                            ELSE 'S'
                        END,
                        (CASE
                            WHEN (P.Dias = 0) THEN 30
                            WHEN (CHARINDEX('H', D.Referencia) > 0 AND CLI.RFC = 'BAP060906LEA') THEN 30
                            WHEN (CHARINDEX('H', D.Referencia) > 0 AND CLI.RFC = 'MME921204HZ4') THEN 45
                            ELSE P.Dias
                        END) AS Plazo,
                        D.Saldo AS Total FROM
                        (
                        SELECT Referencia, CuentaID, SUM(Cargo - Abono) AS Saldo FROM
                        (
                            SELECT Referencia, CuentaID, SUM(Cargo) AS Cargo, SUM(Abono) AS Abono
                            FROM Diario
                            WHERE CuentaID = @cuenta AND Estatus = 'A'
                            GROUP BY Referencia, CuentaID
                        ) AS D
                        GROUP BY Referencia, CuentaID
                        ) AS D
                    LEFT JOIN RelacionCuentas AS C ON D.Referencia = C.Referencia
                    LEFT JOIN RelacionCtas AS R ON C.RelacionID = R.RelacionID
                    LEFT JOIN Factura AS F ON F.FolioID = C.FolioID
                    LEFT JOIN Cliente AS CLI ON CLI.ClienteID = R.ClienteID
                    LEFT JOIN ClienteADD AS DD ON CLI.ClienteID = DD.ClienteID
                    LEFT JOIN DetalleCliente AS De ON CLI.ClienteID = De.ClienteID
                    LEFT JOIN Plazo AS P ON De.PlazoID = P.PlazoID
                    WHERE R.Fecha IS NOT NULL AND CLI.RFC LIKE @rfc AND Saldo = 0
                ) AS F
                WHERE FechaFactura BETWEEN @init AND @end
                GROUP BY FolioID, Referencia, Regimen, FechaFactura, FechaAcuse, Plazo;";                
            $result = mssql_query($Query, $this->_conn);
            if ($result) {
                while ($row = mssql_fetch_assoc($result)) {
                    $data[] = array(
                        "cta_gastos" => $row["FolioID"],
                        "referencia" => $row["Referencia"],
                        "regimen" => $row["Regimen"],
                        "fecha_factura" => $row["FechaFactura"],
                        "fecha_acuse" => $row["FechaAcuse"],
                        "fecha_pago" => $row["FechaPago"],
                        "fa_fp" => $this->daysBetween($row["FechaAcuse"], $row["FechaPago"]),
                        "ff_fp" => $this->daysBetween($row["FechaFactura"], $row["FechaPago"]),
                        "plazo" => $row["Plazo"],
                    );
                }
                return $data;
            } else {
                return null;
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

    protected function daysBetween($fechaIni, $fechaFin) {
        $datediff = strtotime($fechaFin) - strtotime($fechaIni);
        return floor($datediff / (60 * 60 * 24));
    }

    public function getReferences($ctaIngresos, $patente, $aduana, $referencia = null) {
        try {
            if (!isset($referencia)) {
                $Query = "USE SICA
                        SELECT TOP 500 D.CuentaID, D.Referencia,DATENAME(yyyy, D.FechaModificacion) AS Year, D.FechaModificacion, C.Nombre
                        FROM Diario AS D
                        LEFT JOIN Cuentas AS C ON C.CuentaID = D.CuentaID
                        WHERE D.Referencia IS NOT NULL AND D.Referencia <> '' AND C.CuentaID = {$ctaIngresos} AND C.Estatus = 'A' AND D.FechaModificacion > '01-01-2013'
                        GROUP BY D.Referencia, D.CuentaID, D.FechaModificacion, C.Nombre
                        ORDER BY D.FechaModificacion DESC;";
            } else {
                $Query = "USE SICA
                        SELECT TOP 1 D.CuentaID, D.Referencia,DATENAME(yyyy, D.FechaModificacion) AS Year, D.FechaModificacion, C.Nombre
                        FROM Diario AS D
                        LEFT JOIN Cuentas AS C ON C.CuentaID = D.CuentaID
                        WHERE D.Referencia IS NOT NULL AND D.Referencia = '{$referencia}' AND C.CuentaID = {$ctaIngresos} AND C.Estatus = 'A' AND D.FechaModificacion > '01-01-2013'    
                        ORDER BY D.FechaModificacion DESC;";
            }
            $result = mssql_query($Query, $this->_conn);
            if ($result) {
                $data = array();
                while ($row = mssql_fetch_assoc($result)) {
                    if (date("Y-m-d", strtotime($row["FechaModificacion"])) != date("Y-m-d")) {
                        $data[] = array(
                            "referencia" => $row["Referencia"],
                            "patente" => $patente,
                            "aduana" => $aduana,
                            "year" => $row["Year"],
                            "fecha" => $row["FechaModificacion"],
                        );
                    }
                }
                return $data;
            } else {
                return null;
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

    public function referenceDetails($ref) {
        try {
            $Query = "USE SICA
                    SELECT 
                            CONVERT(varchar, D.Fecha, 111) AS Fecha,
                            D.MovimientoID AS Mov,
                            D.PolizaID AS Poliza,
                            C.Nombre AS Cuenta,
                            D.Cargo AS Cargo,
                            D.Abono AS Abono,
                            D.Descripcion AS Descripcion,
                            D.Estatus AS Estatus
                    FROM Diario AS D
                    LEFT JOIN Cuentas AS C ON C.CuentaID = D.CuentaID
                    WHERE Referencia LIKE '{$ref}'
                    ORDER BY D.Fecha ASC;";

            $result = mssql_query($Query, $this->_conn);
            if ($result) {
                $data = array();
                while ($row = mssql_fetch_assoc($result)) {
                    $data[] = array(
                        "referencia" => $ref,
                        "movimiento" => $row["Mov"],
                        "poliza" => $row["Poliza"],
                        "cuenta" => $row["Cuenta"],
                        "cargo" => $row["Cargo"],
                        "abono" => $row["Abono"],
                        "descripcion" => $row["Descripcion"],
                        "estatus" => $row["Estatus"],
                        "fecha" => $row["Fecha"],
                    );
                }
                return $data;
            } else {
                return null;
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }   

    public function ingresos($cuenta, $year) {
        try {
            $sql = "SELECT TOP 1
  CASE 
      WHEN D.CuentaID > 5101000000000000 AND D.CuentaID < 5102000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 1 THEN D.Abono ELSE 0 END)
      WHEN D.CuentaID > 5203000000000000 AND D.CuentaID < 5204000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 1 THEN D.Cargo ELSE 0 END)
  END AS Ene
  ,CASE 
      WHEN D.CuentaID > 5101000000000000 AND D.CuentaID < 5102000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 2 THEN D.Abono ELSE 0 END)
      WHEN D.CuentaID > 5203000000000000 AND D.CuentaID < 5204000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 2 THEN D.Cargo ELSE 0 END)
  END AS Feb
  ,CASE 
      WHEN D.CuentaID > 5101000000000000 AND D.CuentaID < 5102000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 3 THEN D.Abono ELSE 0 END)
      WHEN D.CuentaID > 5203000000000000 AND D.CuentaID < 5204000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 3 THEN D.Cargo ELSE 0 END)
  END AS Mar
  ,CASE 
      WHEN D.CuentaID > 5101000000000000 AND D.CuentaID < 5102000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 4 THEN D.Abono ELSE 0 END)
      WHEN D.CuentaID > 5203000000000000 AND D.CuentaID < 5204000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 4 THEN D.Cargo ELSE 0 END)
  END AS Abr
  ,CASE 
      WHEN D.CuentaID > 5101000000000000 AND D.CuentaID < 5102000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 5 THEN D.Abono ELSE 0 END)
      WHEN D.CuentaID > 5203000000000000 AND D.CuentaID < 5204000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 5 THEN D.Cargo ELSE 0 END)
  END AS May
  ,CASE 
      WHEN D.CuentaID > 5101000000000000 AND D.CuentaID < 5102000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 6 THEN D.Abono ELSE 0 END)
      WHEN D.CuentaID > 5203000000000000 AND D.CuentaID < 5204000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 6 THEN D.Cargo ELSE 0 END)
  END AS Jun
  ,CASE 
      WHEN D.CuentaID > 5101000000000000 AND D.CuentaID < 5102000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 7 THEN D.Abono ELSE 0 END)
      WHEN D.CuentaID > 5203000000000000 AND D.CuentaID < 5204000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 7 THEN D.Cargo ELSE 0 END)
  END AS Jul
  ,CASE 
      WHEN D.CuentaID > 5101000000000000 AND D.CuentaID < 5102000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 8 THEN D.Abono ELSE 0 END)
      WHEN D.CuentaID > 5203000000000000 AND D.CuentaID < 5204000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 8 THEN D.Cargo ELSE 0 END)
  END AS Ago
  ,CASE 
      WHEN D.CuentaID > 5101000000000000 AND D.CuentaID < 5102000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 9 THEN D.Abono ELSE 0 END)
      WHEN D.CuentaID > 5203000000000000 AND D.CuentaID < 5204000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 9 THEN D.Cargo ELSE 0 END)
  END AS Sep
  ,CASE 
      WHEN D.CuentaID > 5101000000000000 AND D.CuentaID < 5102000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 10 THEN D.Abono ELSE 0 END)
      WHEN D.CuentaID > 5203000000000000 AND D.CuentaID < 5204000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 10 THEN D.Cargo ELSE 0 END)
  END AS Oct
  ,CASE 
      WHEN D.CuentaID > 5101000000000000 AND D.CuentaID < 5102000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 11 THEN D.Abono ELSE 0 END)
      WHEN D.CuentaID > 5203000000000000 AND D.CuentaID < 5204000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 11 THEN D.Cargo ELSE 0 END)
  END AS Nov
  ,CASE 
      WHEN D.CuentaID > 5101000000000000 AND D.CuentaID < 5102000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 12 THEN D.Abono ELSE 0 END)
      WHEN D.CuentaID > 5203000000000000 AND D.CuentaID < 5204000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 12 THEN D.Cargo ELSE 0 END)
  END AS Dic
  ,CASE 
      WHEN D.CuentaID > 5101000000000000 AND D.CuentaID < 5102000000000000 THEN SUM(D.Abono)
      WHEN D.CuentaID > 5203000000000000 AND D.CuentaID < 5204000000000000 THEN SUM(D.Cargo)
  END AS Total
  FROM Diario D WHERE D.CuentaID = {$cuenta}
  AND D.Fecha BETWEEN '{$year}/01/01' AND '{$year}/12/31'
  GROUP BY D.CuentaID;";
            $result = mssql_query($sql, $this->_conn);
            if ($result) {
                $row = mssql_fetch_assoc($result);
                return array(
                    "cuenta" => $cuenta,
                    "tipo" => "INGRESOS",
                    "valores" => array(
                        1 => $row["Ene"],
                        2 => $row["Feb"],
                        3 => $row["Mar"],
                        4 => $row["Abr"],
                        5 => $row["May"],
                        6 => $row["Jun"],
                        7 => $row["Jul"],
                        8 => $row["Ago"],
                        9 => $row["Sep"],
                        10 => $row["Oct"],
                        11 => $row["Nov"],
                        12 => $row["Dic"],
                        13 => $row["Total"],
                        14 => $this->_promedio($row)
                    )
                );
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }
    
    public function egresos($cuenta, $year) {
        try {
            $sql = "SELECT TOP 1
  CASE 
      WHEN D.CuentaID > 5101000000000000 AND D.CuentaID < 5102000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 1 THEN D.Abono ELSE 0 END)
      WHEN D.CuentaID > 5203000000000000 AND D.CuentaID < 5204000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 1 THEN D.Cargo ELSE 0 END)
  END AS Ene
  ,CASE 
      WHEN D.CuentaID > 5101000000000000 AND D.CuentaID < 5102000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 2 THEN D.Abono ELSE 0 END)
      WHEN D.CuentaID > 5203000000000000 AND D.CuentaID < 5204000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 2 THEN D.Cargo ELSE 0 END)
  END AS Feb
  ,CASE 
      WHEN D.CuentaID > 5101000000000000 AND D.CuentaID < 5102000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 3 THEN D.Abono ELSE 0 END)
      WHEN D.CuentaID > 5203000000000000 AND D.CuentaID < 5204000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 3 THEN D.Cargo ELSE 0 END)
  END AS Mar
  ,CASE 
      WHEN D.CuentaID > 5101000000000000 AND D.CuentaID < 5102000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 4 THEN D.Abono ELSE 0 END)
      WHEN D.CuentaID > 5203000000000000 AND D.CuentaID < 5204000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 4 THEN D.Cargo ELSE 0 END)
  END AS Abr
  ,CASE 
      WHEN D.CuentaID > 5101000000000000 AND D.CuentaID < 5102000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 5 THEN D.Abono ELSE 0 END)
      WHEN D.CuentaID > 5203000000000000 AND D.CuentaID < 5204000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 5 THEN D.Cargo ELSE 0 END)
  END AS May
  ,CASE 
      WHEN D.CuentaID > 5101000000000000 AND D.CuentaID < 5102000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 6 THEN D.Abono ELSE 0 END)
      WHEN D.CuentaID > 5203000000000000 AND D.CuentaID < 5204000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 6 THEN D.Cargo ELSE 0 END)
  END AS Jun
  ,CASE 
      WHEN D.CuentaID > 5101000000000000 AND D.CuentaID < 5102000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 7 THEN D.Abono ELSE 0 END)
      WHEN D.CuentaID > 5203000000000000 AND D.CuentaID < 5204000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 7 THEN D.Cargo ELSE 0 END)
  END AS Jul
  ,CASE 
      WHEN D.CuentaID > 5101000000000000 AND D.CuentaID < 5102000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 8 THEN D.Abono ELSE 0 END)
      WHEN D.CuentaID > 5203000000000000 AND D.CuentaID < 5204000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 8 THEN D.Cargo ELSE 0 END)
  END AS Ago
  ,CASE 
      WHEN D.CuentaID > 5101000000000000 AND D.CuentaID < 5102000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 9 THEN D.Abono ELSE 0 END)
      WHEN D.CuentaID > 5203000000000000 AND D.CuentaID < 5204000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 9 THEN D.Cargo ELSE 0 END)
  END AS Sep
  ,CASE 
      WHEN D.CuentaID > 5101000000000000 AND D.CuentaID < 5102000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 10 THEN D.Abono ELSE 0 END)
      WHEN D.CuentaID > 5203000000000000 AND D.CuentaID < 5204000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 10 THEN D.Cargo ELSE 0 END)
  END AS Oct
  ,CASE 
      WHEN D.CuentaID > 5101000000000000 AND D.CuentaID < 5102000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 11 THEN D.Abono ELSE 0 END)
      WHEN D.CuentaID > 5203000000000000 AND D.CuentaID < 5204000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 11 THEN D.Cargo ELSE 0 END)
  END AS Nov
  ,CASE 
      WHEN D.CuentaID > 5101000000000000 AND D.CuentaID < 5102000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 12 THEN D.Abono ELSE 0 END)
      WHEN D.CuentaID > 5203000000000000 AND D.CuentaID < 5204000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 12 THEN D.Cargo ELSE 0 END)
  END AS Dic
  ,CASE 
      WHEN D.CuentaID > 5101000000000000 AND D.CuentaID < 5102000000000000 THEN SUM(D.Abono)
      WHEN D.CuentaID > 5203000000000000 AND D.CuentaID < 5204000000000000 THEN SUM(D.Cargo)
  END AS Total
  FROM Diario D WHERE D.CuentaID = {$cuenta}
  AND D.Fecha BETWEEN '{$year}/01/01' AND '{$year}/12/31'
  GROUP BY D.CuentaID;";
            $result = mssql_query($sql, $this->_conn);
            if ($result) {
                $row = mssql_fetch_assoc($result);
                return array(
                    "cuenta" => $cuenta,
                    "tipo" => "COSTOS",
                    "valores" => array(
                        1 => $row["Ene"],
                        2 => $row["Feb"],
                        3 => $row["Mar"],
                        4 => $row["Abr"],
                        5 => $row["May"],
                        6 => $row["Jun"],
                        7 => $row["Jul"],
                        8 => $row["Ago"],
                        9 => $row["Sep"],
                        10 => $row["Oct"],
                        11 => $row["Nov"],
                        12 => $row["Dic"],
                        13 => $row["Total"],
                        14 => $this->_promedio($row)
                    )
                );
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }
    
    public function ingresosCorresponsal($ctaIngresos, $ctaEgresos, $year, $razon) {
        try {
            $Query = "USE SICA
                DECLARE @ingresos varchar(16);
                DECLARE @razon varchar(MAX);
                DECLARE @egresos varchar(16);
                DECLARE @init varchar(10);
                DECLARE @end varchar(10);
                SET @ingresos = {$ctaIngresos};
                SET @egresos = {$ctaEgresos};
                SET @razon = '{$razon}';
                SET @init = '{$year}/01/01';
                SET @end = '{$year}/12/31';

                SELECT TOP 10
                  CAST(CuentaID AS varchar(50)) AS CuentaID
                  ,CASE
                        WHEN D.CuentaID = @ingresos THEN 'INGRESOS'
                        WHEN D.CuentaID = @egresos THEN 'COSTOS'
                  END AS Tipo
                  ,CASE 
                      WHEN D.CuentaID > 5101000000000000 AND D.CuentaID < 5102000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 1 THEN D.Abono ELSE 0 END)
                      WHEN D.CuentaID > 5203000000000000 AND D.CuentaID < 5204000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 1 THEN D.Cargo ELSE 0 END)
                  END AS Ene
                  ,CASE 
                      WHEN D.CuentaID > 5101000000000000 AND D.CuentaID < 5102000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 2 THEN D.Abono ELSE 0 END)
                      WHEN D.CuentaID > 5203000000000000 AND D.CuentaID < 5204000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 2 THEN D.Cargo ELSE 0 END)
                  END AS Feb
                  ,CASE 
                      WHEN D.CuentaID > 5101000000000000 AND D.CuentaID < 5102000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 3 THEN D.Abono ELSE 0 END)
                      WHEN D.CuentaID > 5203000000000000 AND D.CuentaID < 5204000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 3 THEN D.Cargo ELSE 0 END)
                  END AS Mar
                  ,CASE 
                      WHEN D.CuentaID > 5101000000000000 AND D.CuentaID < 5102000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 4 THEN D.Abono ELSE 0 END)
                      WHEN D.CuentaID > 5203000000000000 AND D.CuentaID < 5204000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 4 THEN D.Cargo ELSE 0 END)
                  END AS Abr
                  ,CASE 
                      WHEN D.CuentaID > 5101000000000000 AND D.CuentaID < 5102000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 5 THEN D.Abono ELSE 0 END)
                      WHEN D.CuentaID > 5203000000000000 AND D.CuentaID < 5204000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 5 THEN D.Cargo ELSE 0 END)
                  END AS May
                  ,CASE 
                      WHEN D.CuentaID > 5101000000000000 AND D.CuentaID < 5102000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 6 THEN D.Abono ELSE 0 END)
                      WHEN D.CuentaID > 5203000000000000 AND D.CuentaID < 5204000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 6 THEN D.Cargo ELSE 0 END)
                  END AS Jun
                  ,CASE 
                      WHEN D.CuentaID > 5101000000000000 AND D.CuentaID < 5102000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 7 THEN D.Abono ELSE 0 END)
                      WHEN D.CuentaID > 5203000000000000 AND D.CuentaID < 5204000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 7 THEN D.Cargo ELSE 0 END)
                  END AS Jul
                  ,CASE 
                      WHEN D.CuentaID > 5101000000000000 AND D.CuentaID < 5102000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 8 THEN D.Abono ELSE 0 END)
                      WHEN D.CuentaID > 5203000000000000 AND D.CuentaID < 5204000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 8 THEN D.Cargo ELSE 0 END)
                  END AS Ago
                  ,CASE 
                      WHEN D.CuentaID > 5101000000000000 AND D.CuentaID < 5102000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 9 THEN D.Abono ELSE 0 END)
                      WHEN D.CuentaID > 5203000000000000 AND D.CuentaID < 5204000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 9 THEN D.Cargo ELSE 0 END)
                  END AS Sep
                  ,CASE 
                      WHEN D.CuentaID > 5101000000000000 AND D.CuentaID < 5102000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 10 THEN D.Abono ELSE 0 END)
                      WHEN D.CuentaID > 5203000000000000 AND D.CuentaID < 5204000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 10 THEN D.Cargo ELSE 0 END)
                  END AS Oct
                  ,CASE 
                      WHEN D.CuentaID > 5101000000000000 AND D.CuentaID < 5102000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 11 THEN D.Abono ELSE 0 END)
                      WHEN D.CuentaID > 5203000000000000 AND D.CuentaID < 5204000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 11 THEN D.Cargo ELSE 0 END)
                  END AS Nov
                  ,CASE 
                      WHEN D.CuentaID > 5101000000000000 AND D.CuentaID < 5102000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 12 THEN D.Abono ELSE 0 END)
                      WHEN D.CuentaID > 5203000000000000 AND D.CuentaID < 5204000000000000 THEN SUM(CASE WHEN MONTH(D.Fecha) = 12 THEN D.Cargo ELSE 0 END)
                  END AS Dic
                  ,CASE 
                      WHEN D.CuentaID > 5101000000000000 AND D.CuentaID < 5102000000000000 THEN SUM(D.Abono)
                      WHEN D.CuentaID > 5203000000000000 AND D.CuentaID < 5204000000000000 THEN SUM(D.Cargo)
                  END AS Total
                  FROM Diario D WHERE D.CuentaID IN (@ingresos,@egresos)
                  AND D.Fecha BETWEEN @init AND @end
                  GROUP BY D.CuentaID;";
            $result = mssql_query($Query, $this->_conn);
            if ($result) {
                $data = array();
                while ($row = mssql_fetch_assoc($result)) {
                    $data[] = array(
                        "Tipo" => $row["Tipo"],
                        "CuentaID" => (string) $row["CuentaID"],
                        "Ene" => $row["Ene"],
                        "Feb" => $row["Feb"],
                        "Mar" => $row["Mar"],
                        "Abr" => $row["Abr"],
                        "May" => $row["May"],
                        "Jun" => $row["Jun"],
                        "Jul" => $row["Jul"],
                        "Ago" => $row["Ago"],
                        "Sep" => $row["Sep"],
                        "Oct" => $row["Oct"],
                        "Nov" => $row["Nov"],
                        "Dic" => $row["Dic"],
                        "Total" => $row["Total"],
                        "Promedio" => $this->_promedio($row),
                    );
                }
                $data[] = array(
                    "Tipo" => "DIFERENCIA",
                    "CuentaID" => '',
                    "Ene" => ($data[0]["Ene"] - $data[1]["Ene"]),
                    "Feb" => ($data[0]["Feb"] - $data[1]["Feb"]),
                    "Mar" => ($data[0]["Mar"] - $data[1]["Mar"]),
                    "Abr" => ($data[0]["Abr"] - $data[1]["Abr"]),
                    "May" => ($data[0]["May"] - $data[1]["May"]),
                    "Jun" => ($data[0]["Jun"] - $data[1]["Jun"]),
                    "Jul" => ($data[0]["Jul"] - $data[1]["Jul"]),
                    "Ago" => ($data[0]["Ago"] - $data[1]["Ago"]),
                    "Sep" => ($data[0]["Sep"] - $data[1]["Sep"]),
                    "Oct" => ($data[0]["Oct"] - $data[1]["Oct"]),
                    "Nov" => ($data[0]["Nov"] - $data[1]["Nov"]),
                    "Dic" => ($data[0]["Dic"] - $data[1]["Dic"]),
                    "Total" => ($data[0]["Total"] - $data[1]["Total"]),
                    "Promedio" => ($data[0]["Promedio"] - $data[1]["Promedio"]),
                );
                $data[] = array(
                    "Tipo" => "PORCENTAJE",
                    "CuentaID" => '',
                    "Ene" => ($data[2]["Ene"] / $data[0]["Ene"]) * 100, 2,
                    "Feb" => ($data[2]["Feb"] / $data[0]["Feb"]) * 100, 2,
                    "Mar" => ($data[2]["Mar"] / $data[0]["Mar"]) * 100, 2,
                    "Abr" => ($data[2]["Abr"] / $data[0]["Abr"]) * 100, 2,
                    "May" => ($data[2]["May"] / $data[0]["May"]) * 100, 2,
                    "Jun" => ($data[2]["Jun"] / $data[0]["Jun"]) * 100, 2,
                    "Jul" => ($data[2]["Jul"] / $data[0]["Jul"]) * 100, 2,
                    "Ago" => ($data[2]["Ago"] / $data[0]["Ago"]) * 100, 2,
                    "Sep" => ($data[2]["Sep"] / $data[0]["Sep"]) * 100, 2,
                    "Oct" => ($data[2]["Oct"] / $data[0]["Oct"]) * 100, 2,
                    "Nov" => ($data[2]["Nov"] / $data[0]["Nov"]) * 100, 2,
                    "Dic" => ($data[2]["Dic"] / $data[0]["Dic"]) * 100, 2,
                );
                return $data;
            } else {
                return null;
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

    protected function _promedio($array) {
        $i = 0;
        $total = 0;
        foreach ($array as $key => $value) {
            if ($key != "Total" && $key != "Tipo" && $key != "CuentaID") {
                if ($value > 0) {
                    $total = $total + $value;
                    $i++;
                }
            }
        }
        return $total / $i;
    }

    public function facturacionDelDia($rfc, $fecha) {
        try {
            $date = explode("-", $fecha);
            $where = "C.RFC = '{$rfc}' AND YEAR(F.Fecha) = {$date[0]} AND MONTH(F.Fecha) = {$date[1]} AND DAY(F.Fecha) = {$date[2]}";
            $query = "USE SICA; \n"
                    . "SELECT DISTINCT \n"
                    . "C.RFC "
                    . ",F.FolioID AS CuentaDeGastos "
                    . ",F.UsuarioID AS Usuario "
                    . ",F.Patente "
                    . ",F.AduanaID "
                    . ",F.Pedimento "
                    . ",F.Referencia "
                    . ",CONVERT(VARCHAR(10), F.Fecha, 111) AS Fecha \n"
                    . "FROM Factura F \n"
                    . "LEFT JOIN Diario D ON F.Referencia = D.Referencia \n"
                    . "LEFT JOIN Cliente C ON C.ClienteID = F.ClienteID \n"
                    . "WHERE {$where}\n"
                    . "GROUP BY C.RFC, F.FolioID, F.UsuarioID, F.Patente, F.AduanaID, F.Pedimento, F.Referencia, F.Fecha\n"
                    . "ORDER BY F.FolioID DESC;";
            $result = mssql_query($query, $this->_conn);
            if (!$result) {
                throw new Exception("MSSQL error: " . mssql_get_last_message() . " on " . __METHOD__);
            }
            if ($result) {
                $data = array();
                while ($row = mssql_fetch_assoc($result)) {
                    $data[] = array(
                        "rfc" => $row["RFC"],
                        "cuentaDeGastos" => $row["CuentaDeGastos"],
                        "patente" => $row["Patente"],
                        "aduana" => $row["AduanaID"],
                        "pedimento" => $row["Pedimento"],
                        "referencia" => $row["Referencia"],
                        "usuario" => $row["Usuario"],
                    );
                }
                return $data;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }
    
    public function obtenerFolio($folio) {
        try {
            $query = "USE SICA; \n"
                    . "SELECT DISTINCT TOP 1 \n"
                    . "C.RFC "
                    . ",F.FolioID AS CuentaDeGastos "
                    . ",F.UsuarioID AS Usuario "
                    . ",F.Patente "
                    . ",CONVERT(VARCHAR(10), F.Fecha, 120) AS Fecha "
                    . ",F.AduanaID "
                    . ",F.Pedimento "
                    . ",F.Referencia "
                    . ",CONVERT(VARCHAR(10), F.Fecha, 111) AS Fecha \n"
                    . "FROM Factura F \n"
                    . "LEFT JOIN Diario D ON F.Referencia = D.Referencia \n"
                    . "LEFT JOIN Cliente C ON C.ClienteID = F.ClienteID \n"
                    . "WHERE F.FolioID = {$folio};";
            $result = mssql_query($query, $this->_conn);
            if (!$result) {
                throw new Exception("MSSQL error: " . mssql_get_last_message() . " on " . __METHOD__);
            }
            if ($result) {
                $data = array();
                while ($row = mssql_fetch_assoc($result)) {
                    $data[] = array(
                        "rfc" => $row["RFC"],
                        "cuentaDeGastos" => $row["CuentaDeGastos"],
                        "patente" => $row["Patente"],
                        "aduana" => $row["AduanaID"],
                        "pedimento" => $row["Pedimento"],
                        "referencia" => $row["Referencia"],
                        "usuario" => $row["Usuario"],
                        "fechaFactura" => date("Y-m-d", strtotime($row["Fecha"])),
                    );
                }
                return $data;
            } else {
                return null;
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }
    
    public function getAddress($rfc) {
        try {
            ini_set("mssql.charset", "UTF-8");
            $query = "USE SICA
                SELECT (CAST(C.Direccion AS VARCHAR(255)) + ', ' + C.Colonia + ', CP ' + CAST(C.CP AS VARCHAR(5)) + ', ' + C.Ciudad + ', ' + E.Estado + ', ' + P.Nombre) AS domicilio 
                FROM Cliente C
                LEFT JOIN Entidad E ON E.EntidadID = C.EntidadID AND E.PaisID = C.PaisID
                LEFT JOIN Paises P ON P.PaisID = C.PaisID
                WHERE C.RFC = '{$rfc}';";
            $result = mssql_query($query, $this->_conn);
            if (!$result) {
                throw new Exception("MSSQL error: " . mssql_get_last_message());
            }
            if ($result) {
                $row = mssql_fetch_assoc($result);
                return $row["domicilio"];
            } else {
                return null;
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

    public function facturasTerminal($rfc, $fecha = null, $pedimento = null) {
        try {
            $date = explode("-", $fecha);
            if (!isset($pedimento)) {
                $where = "D.FacturaProveedor IS NOT NULL AND C.RFC = '{$rfc}' AND YEAR(F.Fecha) = {$date[0]} AND MONTH(F.Fecha) = {$date[1]} AND DAY(F.Fecha) = {$date[2]}";
            } else {
                $where = "F.Pedimento = {$pedimento}";
            }
            $query = "USE SICA; \n"
                    . "SELECT DISTINCT \n"
                    . "C.Nombre "
                    . ",C.RFC "
                    . ",F.FolioID AS CuentaDeGastos "
                    . ",F.Patente "
                    . ",F.Pedimento "
                    . ",F.Referencia "
                    . ",D.FacturaProveedor "
                    . ",D.Factura "
                    . ",D.UUID "
                    . ",CONVERT(VARCHAR(10), F.Fecha, 111) AS Fecha \n"
                    . "FROM Factura F \n"
                    . "LEFT JOIN Diario D ON F.Referencia = D.Referencia \n"
                    . "LEFT JOIN Cliente C ON C.ClienteID = F.ClienteID \n"
                    . "WHERE {$where} AND (D.UUID IS NOT NULL AND D.UUID <> '')\n"
                    . "GROUP BY \n"
                    . "C.Nombre, C.RFC, F.FolioID, F.Patente, F.Pedimento, F.Referencia, D.FacturaProveedor, D.Factura, D.UUID, F.Fecha \n"
                    . "ORDER BY F.Fecha DESC;";
            $result = mssql_query($query, $this->_conn);
            if (!$result) {
                throw new Exception("MSSQL error: " . mssql_get_last_message());
            }
            if ($result) {
                $data = array();
                while ($row = mssql_fetch_assoc($result)) {
                    preg_match("/([0-9]+)/", $row["FacturaProveedor"], $matches);
                    $data[] = array(
                        "nombre" => $row["Nombre"],
                        "rfc" => $row["RFC"],
                        "cuentaDeGastos" => $row["CuentaDeGastos"],
                        "patente" => $row["Patente"],
                        "pedimento" => $row["Pedimento"],
                        "referencia" => $row["Referencia"],
                        "uuid" => $row["UUID"],
                        "facturaProveedor" => $matches[0],
                        "fecha" => $row["Fecha"],
                    );
                }
                return $data;
            } else {
                return null;
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

    public function facturasTerminalMes($rfc, $year, $mes) {
        try {
            $where = "D.FacturaProveedor IS NOT NULL AND C.RFC = '{$rfc}' AND YEAR(F.Fecha) = {$year} AND MONTH(F.Fecha) = {$mes}";
            $query = "USE SICA; \n"
                    . "SELECT DISTINCT \n"
                    . "C.Nombre "
                    . ",C.RFC "
                    . ",F.FolioID AS CuentaDeGastos "
                    . ",F.Patente "
                    . ",F.Pedimento "
                    . ",F.Referencia "
                    . ",D.FacturaProveedor "
                    . ",D.Factura "
                    . ",D.UUID "
                    . ",CONVERT(VARCHAR(10), F.Fecha, 111) AS Fecha \n"
                    . "FROM Factura F \n"
                    . "LEFT JOIN Diario D ON F.Referencia = D.Referencia \n"
                    . "LEFT JOIN Cliente C ON C.ClienteID = F.ClienteID \n"
                    . "WHERE {$where} AND (D.UUID IS NOT NULL AND D.UUID <> '')\n"
                    . "GROUP BY \n"
                    . "C.Nombre, C.RFC, F.FolioID, F.Patente, F.Pedimento, F.Referencia, D.FacturaProveedor, D.Factura, D.UUID, F.Fecha \n"
                    . "ORDER BY F.Fecha DESC;";
            $result = mssql_query($query, $this->_conn);
            if (!$result) {
                throw new Exception("MSSQL error: " . mssql_get_last_message());
            }
            if ($result) {
                $data = array();
                while ($row = mssql_fetch_assoc($result)) {
                    preg_match("/([0-9]+)/", $row["FacturaProveedor"], $matches);
                    $data[] = array(
                        "nombre" => $row["Nombre"],
                        "rfc" => $row["RFC"],
                        "cuentaDeGastos" => $row["CuentaDeGastos"],
                        "patente" => $row["Patente"],
                        "pedimento" => $row["Pedimento"],
                        "referencia" => $row["Referencia"],
                        "uuid" => $row["UUID"],
                        "facturaProveedor" => $matches[0],
                        "fecha" => $row["Fecha"],
                    );
                }
                return $data;
            } else {
                return null;
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

}
