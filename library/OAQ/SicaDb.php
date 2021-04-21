<?php

/**
 * Description of EmailNotifications
 *
 * @author Jaime E. Valdez <jvaldezch@gmail.com>
 */
class OAQ_SicaDb {

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
        }
    }

    public function getAdapter() {
        return $this->_db;
    }

    public function tiemposDeComprobacion($rfc, $cuentaId, $fechaIni, $fechaFin) {
        try {
            $init = date("Y/m/d", strtotime($fechaIni));
            $end = date("Y/m/d", strtotime($fechaFin));
            $select = $this->_db->select()
                    ->from(array("D" => "Diario"), array("Referencia", "CuentaID", "SUM(Cargo) AS Cargo", "SUM(Abono) AS Abono"))
                    ->where("CuentaID = '{$cuentaId}' AND Estatus = 'A'")
                    ->group(array("Referencia", "CuentaID"));
            $select1 = $this->_db->select()
                    ->from(array("D" => $select), array("Referencia", "CuentaID", "SUM(Cargo - Abono) AS Saldo"))
                    ->group(array("Referencia", "CuentaID"));
            $select2 = $this->_db->select()
                    ->from(array("D" => $select1), array("F.FolioID", "R.RelacionID", "D.Referencia", "C.Regimen", "F.Fecha AS FechaFactura", "R.Fecha AS FechaAcuse", new Zend_Db_Expr("Honorarios = CASE CHARINDEX('H', D.Referencia) WHEN 0 THEN '' ELSE 'S' END"), new Zend_Db_Expr("(CASE WHEN (P.Dias = 0) THEN 30 WHEN (CHARINDEX('H', D.Referencia) > 0 AND CLI.RFC = 'BAP060906LEA') THEN 30 WHEN (CHARINDEX('H', D.Referencia) > 0 AND CLI.RFC = 'MME921204HZ4') THEN 45 ELSE P.Dias END) AS Plazo"), "D.Saldo AS Total"))
                    ->joinLeft(array("C" => "RelacionCuentas"), "D.Referencia = C.Referencia", array())
                    ->joinLeft(array("R" => "RelacionCtas"), "C.RelacionID = R.RelacionID", array())
                    ->joinLeft(array("F" => "Factura"), "F.FolioID = C.FolioID", array())
                    ->joinLeft(array("CLI" => "Cliente"), "CLI.ClienteID = R.ClienteID", array())
                    ->joinLeft(array("DD" => "ClienteADD"), "CLI.ClienteID = DD.ClienteID", array())
                    ->joinLeft(array("De" => "DetalleCliente"), "CLI.ClienteID = De.ClienteID", array())
                    ->joinLeft(array("P" => "Plazo"), "De.PlazoID = P.PlazoID", array())
                    ->where("R.Fecha IS NOT NULL AND CLI.RFC LIKE '{$rfc}' AND Saldo = 0");
            $select3 = $this->_db->select()
                    ->from(array("F" => $select2), array("FolioID", "Referencia", new Zend_Db_Expr("CONVERT(char(10),FechaFactura,126) AS FechaFactura"), new Zend_Db_Expr("CONVERT(char(10),FechaAcuse,126) AS FechaAcuse"), new Zend_Db_Expr("CONVERT(char(10),DATEADD(day,Plazo,FechaAcuse),126) AS FechaPago"), "Regimen", "Plazo", new Zend_Db_Expr("DATEDIFF(day,FechaFactura,DATEADD(day,Plazo,FechaAcuse)) AS DiffPago"), new Zend_Db_Expr("DATEDIFF(day,FechaFactura,FechaAcuse) AS DiffAcuse")))
                    ->where("FechaFactura BETWEEN '{$init}' AND '{$end}'");
            $result = $this->_db->fetchAll($select3);
            if ($result) {
                return $result;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function enviosPorComprobar($opcion) {
        try {
            $select = $this->_db->select()
                    ->distinct()
                    ->from(array("D" => "Diario"), array(
                        new Zend_Db_Expr("CONVERT(char(10),MIN(D.Fecha),126) AS FechaEnvio"), 
                        new Zend_Db_Expr("CONVERT(char(10),MAX(D.Fecha),126) AS Fecha"), "Cargo", "Abono", "PolizaID", "CuentaID", "Referencia", "Estatus", "CU.Nombre AS Corresponsal", "CL.Nombre AS Cliente"))
                    ->joinLeft(array("SC" => "SolicitudCheques"), "SC.Referencia = D.Referencia", array())
                    ->joinLeft(array("CL" => "Cliente"), "CL.ClienteID = SC.ClienteID", array())
                    ->joinLeft(array("CU" => "Cuentas"), "CU.CuentaID = D.CuentaID", array())
                    ->where("D.CuentaID >= 1105000000000000 AND D.CuentaID < 1106000000000000")
                    ->group(array("D.Fecha", "D.Cargo", "D.Abono", "D.PolizaID", "D.CuentaID", "D.Referencia", "D.Estatus", "CU.Nombre", "CL.Nombre"));
            $select1 = $this->_db->select()
                    ->from(array("Suma" => $select), array("Corresponsal", "Cliente", "Referencia", "MIN(FechaEnvio) AS FechaEnvio", "MAX(Fecha) AS Fecha", "SUM(Cargo) AS Cargo", "SUM(Abono) AS Abono", "SUM(Cargo - Abono) AS Saldo"))
                    ->group(array("Corresponsal", "Cliente", "Referencia"));
            $select2 = $this->_db->select()
                    ->from(array("Final" => $select1), array("Corresponsal", "Cliente", "Referencia", "FechaEnvio", "Fecha", "Cargo", "Abono", "Saldo"))
                    ->where("Fecha > '01-01-2013'");
            if ($opcion == "1") {
                $select2->where("Abono = 0 AND Saldo > 0");
            } elseif ($opcion == "2") {
                $select2->where("Abono > 0 AND Saldo > 0");
            } else {
                $select2->where("Saldo > 0");
            }
            $select2->order("Corresponsal ASC");
            $result = $this->_db->fetchAll($select2);
            
            $mppr = new Trafico_Model_TraficoSolicitudesMapper();
            $model = new Trafico_Model_TraficoSolConceptoMapper();
            
            if ($result) {
                $arr = [];
                foreach ($result as $item) {
                    
                    $arr_trafico = array();
                    $row = $mppr->buscarReferencia($item["Referencia"]);
                    if ($row !== false) {
                        $total = $model->subtotal($row["id"]);
                        $traffic = new Trafico_Model_TraficosMapper();
                        $arr_trafico = $traffic->search($row['patente'], $row['aduana'], $row['referencia']);
                    }
                    
                    $arr[] = array(
                        "Corresponsal" => $item["Corresponsal"],
                        "Referencia" => $item["Referencia"],
                        "Cargo" => number_format($item["Cargo"], 2),
                        "Abono" => number_format($item["Abono"], 2),
                        "Saldo" => number_format($item["Saldo"], 2),
                        "FechaEnvio" => $item["FechaEnvio"],
                        "FechaEnviada" => (isset($row)) ? $row['enviada'] : null,
                        "Solicitudes" => (isset($total)) ? $total : null,
                        "FechaLiberacion" => (isset($arr_trafico['fechaLiberacion'])) ? $arr_trafico['fechaLiberacion'] : null,
                        "Cliente" => ($item["Cliente"] == null) ? $this->_clientePolizaId($this->_idPolizaReferencia($item["Referencia"])) : $item["Cliente"]
                    );
                }
                return $arr;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    protected function _clientePolizaId($idPoliza) {
        try {
            $sql = $this->_db->select()
                    ->from(array("k" => "Cheques"), array())
                    ->joinLeft(array("c" => "Cliente"), "k.ClienteID = c.ClienteID", array("Nombre"))
                    ->where("k.PolizaID = ?", $idPoliza)
                    ->where("k.ProveedorID = 0 AND k.ClienteID > 0");
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    protected function _idPolizaReferencia($referencia) {
        try {
            $sql = $this->_db->select()
                    ->from(array("d" => "Diario"), array("PolizaID"))
                    ->where("d.Referencia = ?", $referencia)
                    ->where("d.CuentaID >= 1105000000000000 AND d.CuentaID < 1106000000000000 AND d.PolizaID > 200000 AND d.PolizaID < 300000");
            $result = $this->_db->fetchRow($sql);
            if ($result) {
                return $result["PolizaID"];
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function pronosticoCobranza($rfc = null, $fecha = null, $sumarizado = 0) {
        try {
            $d = $this->_db->select()
                    ->from(array("D" => "Diario"), array("D.Referencia", "D.CuentaID", "SUM(D.Cargo) AS Cargo", "SUM(D.Abono) AS Abono", "C.Regimen", "R.RelacionID", "R.ClienteID", "F.FolioID", "F.Fecha AS FechaFactura", "R.Fecha AS FechaAcuse"))
                    ->joinLeft(array("C" => "RelacionCuentas"), "D.Referencia = C.Referencia", array())
                    ->joinLeft(array("R" => "RelacionCtas"), "C.RelacionID = R.RelacionID", array())
                    ->joinLeft(array("F" => "Factura"), "F.FolioID = C.FolioID", array())
                    ->where("D.CuentaID >= 1104000000000000 AND D.CuentaID < 1105000000000000 AND D.Estatus = 'A' AND F.FolioID IS NOT NULL")
                    ->group(array("D.Referencia", "D.CuentaID", "C.Regimen", "R.RelacionID", "F.FolioID", "F.Fecha", "R.Fecha", "R.ClienteID"));
            $f = $this->_db->select()
                    ->from(array("F" => $d), array("Referencia", "Regimen", "ClienteID", "RelacionID", "CuentaID", "FolioID", "FechaFactura", "FechaAcuse", "SUM(Cargo - Abono) AS Saldo"))
                    ->group(array("Referencia", "CuentaID", "Regimen", "RelacionID", "FolioID", "FechaFactura", "FechaAcuse", "ClienteID"));
            $x = $this->_db->select()
                    ->from(array("X" => $f), array("X.FechaFactura", "X.FechaAcuse", "X.FolioID", "X.ClienteID", "X.RelacionID", "Referencia", "X.Regimen", new Zend_Db_Expr("Honorarios = CASE CHARINDEX('H', Referencia) WHEN 0 THEN '' ELSE 'S' END"), "Saldo AS Total"))
                    ->joinLeft(array("CLI" => "Cliente"), "CLI.ClienteID = X.ClienteID", array("Nombre", "RFC"))
                    ->joinLeft(array("De" => "DetalleCliente"), "CLI.ClienteID = De.ClienteID", array())
                    ->joinLeft(array("P" => "Plazo"), "De.PlazoID = P.PlazoID", array(new Zend_Db_Expr("(CASE WHEN (P.Dias = 0) THEN 30 WHEN (CHARINDEX('H', Referencia) > 0 AND CLI.RFC = 'BAP060906LEA') THEN 30 WHEN (CHARINDEX('H', Referencia) > 0 AND CLI.RFC = 'MME921204HZ4') THEN 45 ELSE P.Dias END) AS Plazo")))
                    ->where("Saldo > 1");
            $sql = $this->_db->select()
                    ->from(array("M" => $x), array("FolioID", "M.RelacionID", "Referencia", "Regimen", new Zend_Db_Expr("CONVERT(char(10), FechaFactura, 126) AS FechaFactura"), new Zend_Db_Expr("CONVERT(char(10), FechaAcuse, 126) AS FechaAcuse"), new Zend_Db_Expr("CONVERT(char(10),DATEADD(day, Plazo, FechaAcuse), 126) AS FechaPago"), "ClienteID", "Nombre", "RFC", "Honorarios", "Plazo", new Zend_Db_Expr("DATEDIFF(day, CONVERT(char(10), DATEADD(day, Plazo, FechaAcuse), 126),'$fecha') AS Dias"), "Total"))
                    ->where("CONVERT(char(10),DATEADD(day, Plazo, FechaAcuse), 126) <= '$fecha' AND DATEDIFF(day, CONVERT(char(10),DATEADD(day, Plazo, FechaAcuse), 126), '$fecha') > 0")
                    ->group(array("FolioID", "Referencia", "Total", "Regimen", "FechaFactura", "RelacionID", "FechaAcuse", "Nombre", "RFC", "ClienteID", "Plazo", "Honorarios"));
            if (isset($rfc)) {
                $sql->where("RFC LIKE '{$rfc}'");
            }
            if ($sumarizado == 1) {
                $sql = $this->_db->select()
                        ->from(array("S" => $sql), array("Nombre", "SUM(Total) AS Total"))
                        ->group(array("Nombre"))
                        ->order("Total DESC");
            }
            $result = $this->_db->fetchAll($sql);
            if ($result && $sumarizado == 0) {
                $arr = [];
                foreach ($result as $item) {
                    $arr[] = array(
                        "Nombre" => $item["Nombre"],
                        "Plazo" => $item["Plazo"],
                        "Vencimiento" => $item["Dias"],
                        "RelacionID" => $item["RelacionID"],
                        "FolioID" => $item["FolioID"],
                        "FechaFactura" => $item["FechaFactura"],
                        "FechaAcuse" => $item["FechaAcuse"],
                        "FechaPronostico" => date("d/m/Y", strtotime("+30 days", strtotime($item["FechaAcuse"]))),
                        "Referencia" => $item["Referencia"],
                        "Total" => $item["Total"],
                    );
                }
                return $arr;
            } elseif ($result && $sumarizado == 1) {
                return $result;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function pronosticoCobranzaDesglose($rfc = null, $fecha = null, $sumarizado = 0) {
        try {
            $d = $this->_db->select()
                    ->from(array("D" => "Diario"), array("Referencia", "CuentaID", "SUM(Cargo) AS Cargo", "SUM(Abono) AS Abono"))
                    ->where("CuentaID >= 1104000000000000 AND CuentaID < 1105000000000000 AND Estatus = 'A'")
                    ->group(array("Referencia", "CuentaID"));
            $g = $this->_db->select()
                    ->from(array("G" => $d), array("Referencia", "CuentaID", "SUM(Cargo - Abono) AS Saldo"))
                    ->group(array("Referencia", "CuentaID"));
            $e = $this->_db->select()
                    ->from(array("E" => $g), array("E.Referencia", "E.CuentaID", "E.Saldo AS Total", new Zend_Db_Expr("(SELECT SUM(Abono - Cargo) AS Saldo FROM Diario AS D WHERE D.Referencia = F.Referencia AND D.MovimientoID = 'CXC' AND ((D.CuentaID >= 1120000000000000 AND D.CuentaID < 1130000000000000) OR (D.CuentaID >= 2202000000000000 AND D.CuentaID < 2203000000000000)) AND D.Estatus = 'A' GROUP BY D.Referencia ) AS Comprobados"), new Zend_Db_Expr("(SELECT SUM(Abono - Cargo) AS Saldo FROM Diario AS D WHERE D.Referencia = F.Referencia AND D.MovimientoID = 'CXC' AND ((D.CuentaID >= 5101000000000000 AND D.CuentaID < 5102000000000000) OR (D.CuentaID >= 2101000000000000 AND D.CuentaID < 2102000000000000)) AND D.Estatus = 'A' AND D.Descripcion NOT LIKE '%HONORARIOS%' GROUP BY D.Referencia) AS Complementarios")))
                    ->joinLeft(array("C" => "RelacionCuentas"), "E.Referencia = C.Referencia", array(""))
                    ->joinLeft(array("R" => "RelacionCtas"), "R.RelacionID = C.RelacionID", array("Fecha AS FechaAcuse"))
                    ->joinLeft(array("F" => "Factura"), "F.FolioID = C.FolioID", array("Fecha AS FechaFactura"))
                    ->joinLeft(array("CLI" => "Cliente"), "CLI.ClienteID = R.ClienteID", array(""))
                    ->joinLeft(array("DD" => "ClienteADD"), "CLI.ClienteID = DD.ClienteID", array(""))
                    ->joinLeft(array("De" => "DetalleCliente"), "CLI.ClienteID = De.ClienteID", array(""))
                    ->joinLeft(array("P" => "Plazo"), "De.PlazoID = P.PlazoID", array("Dias AS Plazo"))
                    ->where("R.Fecha IS NOT NULL AND Saldo > 0 AND CLI.RFC LIKE '{$rfc}' AND F.FechaCancel IS NULL");
            $sql = $this->_db->select()
                    ->from(array("F" => $e), array("*"))
                    ->where("CONVERT(char(10), DATEADD(day, Plazo, FechaAcuse), 126) >= '2013-01-01 00:15:15' AND FechaFactura >= '{$fecha}'");
            if ((int) $sumarizado == 1) {
                
            }
            $result = $this->_db->fetchAll($sql);
            if ($result && $sumarizado == 0) {
                $arr = [];
                foreach ($result as $item) {
                    $item["IVA"] = $item["IVA"];
                    $arr[] = $item;
                }
                return $arr;
            } elseif ($result && $sumarizado == 1) {
                return $result;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage() . " line " . $e->getLine());
        }
    }
    
    public function facturacionPatente($patente, $fecha) {
        try {
            $date = explode("-", $fecha);
            $sql = $this->_db->select()
                    ->from(array("F" => "Factura"), array("FolioID", "Patente", "AduanaID", "Pedimento", "Referencia", new Zend_Db_Expr("CONVERT(VARCHAR(10), F.Fecha, 111) AS Fecha"), "RefFactura AS Factura"))
                    ->joinLeft(array("D" => "FacturaCFDI"), "D.FolioID = F.FolioID", array("UUID"))
                    ->joinLeft(array("C" => "Cliente"), "C.ClienteID = F.ClienteID", array("C.RFC"))
                    ->where("F.Patente = {$patente} AND YEAR(F.Fecha) = {$date[0]} AND MONTH(F.Fecha) = {$date[1]} AND DAY(F.Fecha) = {$date[2]} AND F.Estatus = 'A' AND D.Estatus = 'A' AND D.FolioID IS NOT NULL AND C.RFC <> 'XEXX010101000'")
                    ->limit(10);
            $stmt = $this->_db->fetchAll($sql);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }
    
    public function facturacionCliente($rfc, $fechaIni, $fechaFin, $page = null, $limit = null) {
        try {
            $init = date("Y/m/d", strtotime($fechaIni));
            $end = date("Y/m/d", strtotime($fechaFin));
            $sql = $this->_db->select()
                    ->from(array("F" => "Factura"),array(
                        new Zend_Db_Expr("CONVERT(VARCHAR(10), F.Fecha, 126) AS fecha_factura"),
                        "F.Referencia AS referencia",
                        "F.Pedimento AS pedimento",
                        "F.Regimen AS regimen",
                        "F.FolioID AS factura",
                        "F.Patente AS patente",
                        "F.AduanaID AS aduana",
                        "F.IE AS ie",
                        "F.Anticipo AS anticipo",
                        "F.Honorarios AS honorarios",
                        "F.ValorFactura AS valor",
                        "F.ValorAduana AS valor_aduana",
                        "F.IVA AS iva",
                        new Zend_Db_Expr("CONVERT(VARCHAR(10), F.FechaPedimento, 126) AS fecha_pedimento"),
                        "F.RefFactura AS ref_factura",
                        "F.Bultos AS bultos",
                        new Zend_Db_Expr("(F.Total - F.IVA) AS sub_total"),
                        "F.Total AS total"
                    ))
                    ->joinLeft(array("C" => "Cliente"), "C.ClienteID = F.ClienteID", array("RFC AS rfc", "Nombre AS nomCliente"))
                    ->where(new Zend_Db_Expr("F.Fecha BETWEEN '{$init}' AND '{$end}'"))
                    ->where("C.RFC = ?", $rfc)
                    ->where("F.Estatus = 'A'")
                    ->order("F.Pedimento ASC");
            $stmt = $this->_db->fetchAll($sql);
            if ($stmt) {
                $data = array();
                foreach ($stmt as $item) {
                    $item["conceptos"] = $this->obtenerConceptos($item["factura"]);
                    $data[] = $item;
                }
                return $data;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function totalFacturacionCliente($rfc, $fechaIni, $fechaFin) {
        try {
            $init = date("Y/m/d", strtotime($fechaIni));
            $end = date("Y/m/d", strtotime($fechaFin));
            $sql = $this->_db->select()
                    ->from(array("F" => "Factura"),array("count(*) AS total"))
                    ->joinLeft(array("C" => "Cliente"), "C.ClienteID = F.ClienteID", array(""))
                    ->where(new Zend_Db_Expr("F.Fecha BETWEEN '{$init}' AND '{$end}'"))
                    ->where("C.RFC = ?", $rfc)
                    ->where("F.Estatus = 'A'");
            $stmt = $this->_db->fetchRow($sql);
            if ($stmt) {
                return $stmt['total'];
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function facturacion() {
        try {
            $sql = $this->_db->select()
                    ->from(array("F" => "Factura"), array(
                        "F.Patente AS patente",
                        "F.AduanaID AS aduana",
                        "F.UsuarioID AS usuario",
                        new Zend_Db_Expr("SUM(CASE MONTH(F.Fecha) WHEN 1 THEN 1 ELSE 0 END) AS 'ene'"),
                        new Zend_Db_Expr("SUM(CASE MONTH(F.Fecha) WHEN 2 THEN 1 ELSE 0 END) AS 'feb'"),
                        new Zend_Db_Expr("SUM(CASE MONTH(F.Fecha) WHEN 3 THEN 1 ELSE 0 END) AS 'mar'"),
                        new Zend_Db_Expr("SUM(CASE MONTH(F.Fecha) WHEN 4 THEN 1 ELSE 0 END) AS 'abr'"),
                        new Zend_Db_Expr("SUM(CASE MONTH(F.Fecha) WHEN 5 THEN 1 ELSE 0 END) AS 'may'"),
                        new Zend_Db_Expr("SUM(CASE MONTH(F.Fecha) WHEN 6 THEN 1 ELSE 0 END) AS 'jun'"),
                        new Zend_Db_Expr("SUM(CASE MONTH(F.Fecha) WHEN 7 THEN 1 ELSE 0 END) AS 'jul'"),
                        new Zend_Db_Expr("SUM(CASE MONTH(F.Fecha) WHEN 8 THEN 1 ELSE 0 END) AS 'ago'"),
                        new Zend_Db_Expr("SUM(CASE MONTH(F.Fecha) WHEN 9 THEN 1 ELSE 0 END) AS 'sep'"),
                        new Zend_Db_Expr("SUM(CASE MONTH(F.Fecha) WHEN 10 THEN 1 ELSE 0 END) AS 'oct'"),
                        new Zend_Db_Expr("SUM(CASE MONTH(F.Fecha) WHEN 11 THEN 1 ELSE 0 END) AS 'nov'"),
                        new Zend_Db_Expr("SUM(CASE MONTH(F.Fecha) WHEN 12 THEN 1 ELSE 0 END) AS 'dic'"),
                        new Zend_Db_Expr("SUM(CASE datepart(year,F.Fecha) WHEN YEAR(F.Fecha) THEN 1 ELSE 0 END) AS 'total'"),
                    ))
                    ->where("YEAR(F.Fecha) = " . date('Y'))
                    ->group(array("F.Patente", "F.AduanaID", "F.UsuarioID"))
                    ->order(array("F.Patente", "F.AduanaID", "F.UsuarioID"));
            $stmt = $this->_db->fetchAll($sql);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerConceptos($folio) {
        try {
            $sql = $this->_db->select()
                    ->from(array("F" => "FacturaGastos"), array(
                        "F.MonedaID AS moneda",
                        new Zend_Db_Expr("(F.Importe / 1.16) AS subTotal"),
                        new Zend_Db_Expr("F.Importe - (F.Importe / 1.16) AS iva"),
                        "F.Importe AS importe",
                    ))
                    ->joinLeft(array("C" => "Conceptos"), "F.ConceptoID = C.ConceptoID", array("C.Nombre AS nombre"))
                    ->where("F.FolioID = ?", $folio);
            $stmt = $this->_db->fetchAll($sql);
            if ($stmt) {
                $data = array();
                foreach ($stmt as $item) {
                    $k = trim(strtolower(preg_replace("/\s+/", "_", str_replace(array(".", ",", ":", ";"), '', $item["nombre"]))));
                    $data[$k] = array(
                        "moneda" => $item["moneda"],
                        "subtotal" => $item["subTotal"],
                        "iva" => $item["iva"],
                        "total" => $item["importe"],
                    );
                }
                return $data;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function facturacionRango($fechaIni, $fechaFin) {
        try {
            $init = date("Y/m/d", strtotime($fechaIni));
            $end = date("Y/m/d", strtotime($fechaFin));
            $sql = $this->_db->select()
                    ->from(array("F" => "Factura"), array(
                        "F.Patente AS patente",
                        "F.AduanaID AS aduana",
                        "F.Pedimento AS pedimento",
                        "F.Referencia AS referencia",
                        "F.FolioID AS folio",
                        new Zend_Db_Expr("CONVERT(VARCHAR(10), F.FechaPedimento, 126) AS fechaPedimento"),
                        new Zend_Db_Expr("CONVERT(VARCHAR(10), F.Fecha, 126) AS fechaFacturacion"),
                        "F.ValorFactura AS valorFactura",
                        "F.Honorarios AS honorarios",
                        "F.Total AS total",
                        "F.SubTotal AS subTotal",
                        "F.Anticipo AS anticipo",
                        new Zend_Db_Expr("CASE WHEN F.IE = 'I' THEN 'TOCE.IMP' ELSE 'TOCE.EXP' END AS tipoOperacion"),
                    ))
                    ->where("F.Fecha BETWEEN '{$init}' AND '{$end}'")
                    ->where("F.Estatus = 'A'");
            $stmt = $this->_db->fetchAll($sql);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function movimientosReferencia($referencia) {
        try {
            $sql = $this->_db->select()
                    ->from(array("D" => "Diario"), array(
                        "D.MovimientoID", 
                        "D.PolizaID", 
                        "D.Cargo AS cargo", 
                        "D.Abono AS abono", 
                        new Zend_Db_Expr("CAST(D.CuentaID AS varchar) AS cuenta"),
                    ))
                    ->where("D.Estatus = 'A'")
                    ->where("D.Referencia = ?", $referencia);
            $stmt = $this->_db->fetchAll($sql);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
