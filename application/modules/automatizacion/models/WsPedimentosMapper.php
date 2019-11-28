<?php

class Automatizacion_Model_WsPedimentosMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Automatizacion_Model_DbTable_WsPedimentos();
    }

    /**
     * 
     * @param String $patente
     * @param String $aduana
     * @param String $pedimento
     * @param String $referencia
     * @return boolean
     */
    public function verificar($patente, $aduana, $pedimento, $referencia) {
        $sql = $this->_db_table->select()
                ->where('patente = ?', $patente)
                ->where('aduana = ?', $aduana)
                ->where('pedimento = ?', $pedimento)
                ->where('referencia = ?', $referencia);
        $stmt = $this->_db_table->fetchRow($sql, array());
        if ($stmt) {
            return true;
        }
        return false;
    }

    /**
     * 
     * @param String $patente
     * @param String $aduana
     * @param String $pedimento
     * @return boolean
     */
    public function buscar($patente, $aduana, $pedimento) {
        $sql = $this->_db_table->select()
                ->where('patente = ?', $patente)
                ->where('aduana = ?', $aduana)
                ->where('pedimento = ?', $pedimento);
        $stmt = $this->_db_table->fetchRow($sql, array());
        if ($stmt) {
            return $stmt->toArray();
        }
        return false;
    }

    /**
     * 
     * @param string $operacion
     * @param string $tipoOperacion
     * @param int $patente
     * @param int $aduana
     * @param int $pedimento
     * @param string $referencia
     * @param string $fechaPago
     * @param string $rfc
     * @return boolean|null
     */    
    public function agregar($operacion, $tipoOperacion, $patente, $aduana, $pedimento, $referencia, $fechaPago, $rfc) {
        $data = array(
            'operacion' => $operacion,
            'tipoOperacion' => $tipoOperacion,
            'patente' => $patente,
            'aduana' => $aduana,
            'pedimento' => $pedimento,
            'referencia' => $referencia,
            'fechaPago' => date('Y-m-d H:i:s', strtotime($fechaPago)),
            'rfc' => $rfc,
            'creado' => date('Y-m-d H:i:s'),
        );
        $added = $this->_db_table->insert($data);
        if ($added) {
            return true;
        }
        return false;
    }
    
    public function wsAgregarPedimento($idAduana, $operacion, $tipoOperacion, $patente, $aduana, $pedimento, $referencia, $fechaPago, $rfc) {
        try {
            $arr = array(
                'idAduana' => $idAduana,
                'operacion' => $operacion,
                'tipoOperacion' => $tipoOperacion,
                'patente' => $patente,
                'aduana' => $aduana,
                'pedimento' => $pedimento,
                'referencia' => $referencia,
                'fechaPago' => date('Y-m-d H:i:s', strtotime($fechaPago)),
                'rfc' => $rfc,
                'creado' => date('Y-m-d H:i:s'),
            );
            $stmt = $this->_db_table->insert($arr);
            if ($stmt) {
                return $stmt;
            }
            return;            
        } catch (Exception $ex) {
            throw new Exception("Zend DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    public function wsVerificarPedimento($idAduana, $patente, $aduana, $pedimento, $referencia) {
        try {
            $sql = $this->_db_table->select()
                    ->where('idAduana = ?', $idAduana)
                    ->where('patente = ?', $patente)
                    ->where('aduana = ?', $aduana)
                    ->where('pedimento = ?', $pedimento)
                    ->where('referencia = ?', $referencia);
            $stmt = $this->_db_table->fetchRow($sql, array());
            if ($stmt) {
                return true;
            }
            return;            
        } catch (Exception $ex) {
            throw new Exception("Zend DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function wsSinDetalle() {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array('*'))
                    ->where('detalle IS NULL');
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("Zend DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    public function wsSinAnexo() {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array('*'))
                    ->where('anexo IS NULL');
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("Zend DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    public function obtenerTodo() {
        $sql = $this->_db_table->select()
                ->where('cove IS NOT NULL');
        $stmt = $this->_db_table->fetchAll($sql, array());
        if ($stmt) {
            return $stmt->toArray();
        }
        return null;
    }

    /**
     * 
     * @param type $rfc
     * @param int $patente
     * @param int $aduana
     * @param int $year
     * @param int $month
     * @param string $fecha
     * @return array|null
     */
    public function obtenerSinDetalle($rfc, $patente = null, $aduana = null, $year = null, $month = null, $fecha = null) {
        $sql = $this->_db_table->select()
                ->from(array("p" => "ws_pedimentos"), array("p.operacion", "p.tipoOperacion as tipoOperacion", "p.patente", "p.aduana", "p.pedimento", "p.referencia"))
                ->where("p.rfc = ?", $rfc)
                ->where("(select count(*) from ws_detalle_pedimentos a WHERE a.patente = p.patente AND a.aduana = p.aduana AND a.pedimento = p.pedimento GROUP BY a.pedimento, a.aduana, a.pedimento) IS NULL")
                ->order("p.pedimento");
        if (isset($patente)) {
            $sql->where("p.patente = ?", $patente);
        }
        if (isset($aduana)) {
            $sql->where("p.aduana = ?", $aduana);
        }
        if (isset($year)) {
            $sql->where("YEAR(p.fechaPago) = ?", $year);
        }
        if (isset($month)) {
            $sql->where("MONTH(p.fechaPago) = ?", $month);
        }
        if (isset($fecha)) {
            $sql->where("p.fechaPago LIKE ?", $fecha . "%");
        }
        $stmt = $this->_db_table->fetchAll($sql, array());
        if ($stmt) {
            return $stmt->toArray();
        }
        return;
    }

    /**
     * 
     * @param string $rfc
     * @param int $patente
     * @param int $aduana
     * @param int $year
     * @param int $month
     * @param int $pedimento
     * @param string $fecha
     * @return array|null
     */
    public function obtenerSinAnexo($rfc, $patente = null, $aduana = null, $year = null, $month = null, $pedimento = null, $fecha = null) {
        if (!isset($pedimento)) {
            $sql = $this->_db_table->select()
                    ->from(array("p" => "ws_pedimentos"), array("p.operacion", "p.tipoOperacion", "p.patente", "p.aduana", "p.pedimento", "p.referencia"))
                    ->where("(select count(*) from ws_anexo_pedimentos a WHERE a.patente = p.patente AND a.aduana = p.aduana AND a.pedimento = p.pedimento GROUP BY a.pedimento, a.aduana, a.pedimento) IS NULL")
                    ->order("p.pedimento");
        } else {
            $sql = $this->_db_table->select()
                    ->from(array("p" => "ws_pedimentos"), array("p.operacion", "p.tipoOperacion", "p.patente", "p.aduana", "p.pedimento", "p.referencia"))
                    ->where("p.pedimento = ?", $pedimento)
                    ->order("p.pedimento");
        }
        if (isset($rfc)) {
            $sql->where("p.rfc = ?", $rfc);
        }
        if (isset($patente)) {
            $sql->where("p.patente = ?", $patente);
        }
        if (isset($aduana)) {
            $sql->where("p.aduana = ?", $aduana);
        }
        if (isset($year)) {
            $sql->where("YEAR(p.fechaPago) = ?", $year);
        }
        if (isset($month)) {
            $sql->where("MONTH(p.fechaPago) = ?", $month);
        }
        if (isset($fecha)) {
            $sql->where("p.fechaPago LIKE ?", $fecha . "%");
        }
        $stmt = $this->_db_table->fetchAll($sql, array());
        if ($stmt) {
            return $stmt->toArray();
        }
        return;
    }

    /**
     * 
     * @param int $patente
     * @param int $aduana
     * @param string $tipoOperacion
     * @return array|null
     */
    public function obtenerSinAnexoTipo($patente, $aduana, $tipoOperacion = null) {
        if (isset($tipoOperacion)) {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("p" => "ws_pedimentos"), array("p.operacion", "p.tipoOperacion", "p.patente", "p.aduana", "p.pedimento", "p.referencia", "(select count(numFactura) as conteo from ws_anexo_pedimentos a WHERE a.patente = p.patente AND a.aduana = p.aduana AND a.pedimento = p.pedimento GROUP BY a.pedimento, a.aduana, a.pedimento) as conteo"))
                    ->joinLeft(array("d" => "ws_detalle_pedimentos"), "d.patente = p.patente AND d.aduana = p.aduana AND d.pedimento = p.pedimento", array("d.fechaPago", "d.rfcCliente", "d.usuarioAlta"))
                    ->where("p.patente = ?", $patente)
                    ->where("p.aduana = ?", $aduana)
                    ->where("p.tipoOperacion = ?", $tipoOperacion)
                    ->order("d.fechaPago DESC");
        } else {
            $sql = $this->_db_table->select()
                    ->from(array("p" => "ws_pedimentos"), array("p.operacion", "p.tipoOperacion", "p.patente", "p.aduana", "p.pedimento", "p.referencia"))
                    ->where("p.patente = ?", $patente)
                    ->where("p.aduana = ?", $aduana);
        }
        $stmt = $this->_db_table->fetchAll($sql, array());
        if ($stmt) {
            return $stmt->toArray();
        }
        return;
    }

    public function obtenerAnexoHtml($patente, $aduana, $year = null, $month = null, $rfc = null, $pedimento = null, $download = null) {
        if ($download) {
            $fields = array(
                "p.referencia",
                "p.operacion",
                "p.tipoOperacion",
                "d.patente",
                "d.aduana",
                "d.pedimento",
                "d.referencia",
                "d.transporteEntrada",
                "d.transporteArribo",
                "d.transporteSalida",
                "DATE_FORMAT(d.fechaEntrada, '%Y-%m-%d') AS fechaEntrada",
                "DATE_FORMAT(d.fechaPago, '%Y-%m-%d') AS fechaPago",
                "d.firmaValidacion",
                "d.firmaBanco",
                "ROUND(d.tipoCambio,4) AS tipoCambio",
                "d.cvePed",
                "d.regimen",
                "d.aduanaEntrada",
                "ROUND(d.valorDolares,0) AS valorDolares",
                "ROUND(d.valorAduana,0) AS valorAduana",
                "ROUND(d.fletes,2) AS fletes",
                "ROUND(d.seguros,2) AS seguros",
                "ROUND(d.embalajes,2) AS embalajes",
                "ROUND(d.otrosIncrementales,2) AS otrosIncrementales",
                "ROUND(d.dta,2) AS dta",
                "ROUND(d.iva,2) AS iva",
                "ROUND(d.igi,2) AS igi",
                "ROUND(d.prev,2) AS prev",
                "ROUND(d.cnt,2) AS cnt",
                "ROUND(d.totalEfectivo,2) AS totalEfectivo",
                "ROUND(d.pesoBruto,4) AS pesoBruto",
                new Zend_Db_Expr("(CASE WHEN d.bultos IS NULL THEN CAST('' as CHAR(1)) ELSE d.bultos END) AS bultos"),
                new Zend_Db_Expr("(CASE WHEN d.guias IS NULL THEN CAST('' as CHAR(1)) ELSE d.guias END) AS guias"),
                "a.numFactura",
                "a.cove",
                "DATE_FORMAT(a.fechaFactura, '%Y-%m-%d') AS fechaFactura",
                "a.incoterm",
                "ROUND(a.valorFacturaMonExt,2) AS valorFacturaUsd",
                "ROUND(a.valorFacturaMonExt,2) AS valorFacturaMonExt",
                "a.nomProveedor",
                "a.paisFactura",
                "a.taxId",
                "a.divisa",
                new Zend_Db_Expr("(CASE WHEN a.divisa = 'USD' THEN 1.00000 WHEN a.divisa = 'MXN' THEN ROUND(a.valorFacturaMonExt / d.tipoCambio ,5) ELSE a.factorMonExt END) AS factorMonExt"),
                "a.numParte",
                "a.descripcion",
                "a.fraccion",
                "a.ordenFraccion",
                "ROUND(a.valorMonExt / a.cantUMC,2) AS precioUnitario",
                "ROUND(a.ValorMonExt,2) AS ValorMonExt",
                "ROUND(a.ValorMonExt * d.tipoCambio,2) AS valorAduanaMxn",
//                "ROUND(a.valorMonExt,2) AS valorFacturaMonExt",
                "ROUND(a.cantUMC,4) AS cantUmc",
                "a.umc",
                "ROUND(a.cantUMT,4) AS cantUtm",
                "a.umt",
                "a.paisOrigen",
                "a.paisVendedor",
                "ROUND(a.tasaAdvalorem,2) AS tasaAdvalorem",
                "a.tlc",
                "a.tlcan",
                "a.tlcue",
                "a.prosec",
                "a.patenteOrig",
                "a.aduanaOrig",
                "a.pedimentoOrig",
            );
        } else {
            $fields = array(
                "p.referencia AS Referencia",
                "p.operacion AS Operacion",
                "p.tipoOperacion AS TipoOperacion",
                "d.patente AS Patente",
                "d.aduana AS Aduana",
                "d.pedimento AS Pedimento",
                "d.referencia AS Trafico",
                "d.transporteEntrada AS TransporteEntrada",
                "d.transporteArribo AS TransporteArribo",
                "d.transporteSalida AS TransporteSalida",
                "d.fechaEntrada AS FechaEntrada",
                "d.fechaPago AS FechaPago",
                "d.firmaValidacion AS FirmaValidacion",
                "d.firmaBanco AS FirmaBanco",
                "d.tipoCambio AS TipoCambio",
                "d.cvePed AS CvePed",
                "d.regimen AS Regimen",
                "d.aduanaEntrada AS AduanaEntrada",
                "ROUND(d.valorDolares,0) AS ValorDolares",
                "ROUND(d.valorAduana,0) AS ValorAduana",
                "ROUND(d.fletes,2) AS Fletes",
                "ROUND(d.seguros,2) AS Seguros",
                "ROUND(d.embalajes,2) AS Embalajes",
                "ROUND(d.otrosIncrementales,2) AS OtrosIncrementales",
                "ROUND(d.dta,2) AS DTA",
                "ROUND(d.iva,2) AS IVA",
                "ROUND(d.igi,2) AS IGI",
                "ROUND(d.prev,2) AS PREV",
                "ROUND(d.cnt,2) AS CNT",
                "ROUND(d.totalEfectivo,2) AS TotalEfectivo",
                "ROUND(d.pesoBruto,4) AS PesoBruto",
                "d.bultos AS Bultos",
                "d.guias AS Guias",
                "d.bl AS BL",
                "d.talon AS Talon",
                "d.contenedores AS Contenedores",
                "d.observaciones AS ObservacionesPedimento",
                "a.numFactura AS NumFactura",
                "a.cove AS Cove",
                "a.fechaFactura AS FechaFactura",
                "a.incoterm AS Incoterm",
                "ROUND(a.valorFacturaUsd,2) AS ValorFacturaUsd",
                "ROUND(a.valorFacturaMonExt,2) AS ValorFacturaMonExt",
                "a.nomProveedor AS NomProveedor",
                "a.paisFactura AS PaisFactura",
                "a.taxId AS TaxId",
                "a.divisa AS Divisa",
                "ROUND(a.factorMonExt,4) AS FactorMonExt",
                "a.numParte AS NumParte",
                "a.descripcion AS Descripcion",
                "a.fraccion AS Fraccion",
                "a.ordenFraccion AS OrdenFraccion",
                "ROUND(a.valorMonExt,2) AS ValorMonExt",
                "ROUND(a.ValorMonExt * d.tipoCambio,2) AS ValorAduanaMXN",
                "ROUND(a.cantUMC,4) AS CantUMC",
                "a.umc AS UMC",
                "ROUND(a.cantUMT,4) AS CantUMT",
                "a.umt AS UMT",
                "a.paisOrigen AS PaisOrigen",
                "a.paisVendedor AS PaisVendedor",
                "ROUND(a.tasaAdvalorem,2) AS TasaAdvalorem",
                "a.tlc AS TLC",
                "a.tlcan AS TLCAN",
                "a.tlcue AS TLCUE",
                "a.prosec AS PROSEC",
                "a.patenteOrig AS PatenteOrig",
                "a.aduanaOrig AS AduanaOrig",
                "a.pedimentoOrig AS PedimentoOrig",
                "a.observacion AS Observacion",
            );
        }
        $sql = $this->_db_table->select()
                ->setIntegrityCheck(false)
                ->from(array("p" => "ws_pedimentos"), $fields)
                ->joinLeft(array("d" => "ws_detalle_pedimentos"), "p.operacion = d.operacion AND p.patente = d.patente AND p.aduana = d.aduana AND p.pedimento = d.pedimento AND p.tipoOperacion = d.tipoOperacion", array(""))
                ->joinLeft(array("a" => "ws_anexo_pedimentos"), "p.operacion = a.operacion AND p.patente = a.patente AND p.aduana = a.aduana AND p.pedimento = a.pedimento AND p.tipoOperacion = a.tipoOperacion", array(""))
                ->where("p.rfc = ?", $rfc);
        if ($pedimento) {
            $sql->where("p.pedimento = ?", $pedimento);
        }
        if ($patente == 9999 && $aduana == 999) {
            $sql->where("YEAR(p.fechaPago) = ?", $year)
                    ->where("MONTH(p.fechaPago) = ?", $month);
        } else {
            $sql->where("p.patente = ?", $patente)
                    ->where("p.aduana = ?", $aduana)
                    ->where("YEAR(p.fechaPago) = ?", $year)
                    ->where("MONTH(p.fechaPago) = ?", $month);
        }
        $stmt = $this->_db_table->fetchAll($sql, array());
        if ($stmt) {
            return $stmt->toArray();
        }
        return;
    }

    /**
     * 
     * @param int $patente
     * @param int $aduana
     * @param int $year
     * @param int $month
     * @param type $rfc
     * @param type $tipo
     * @param int $pedimento
     * @param string $ie
     * @return array|null
     */
    public function obtenerAnexo($patente, $aduana, $year = null, $month = null, $rfc = null, $tipo = null, $pedimento = null, $ie = null) {
        if (isset($pedimento)) {
            $q = " AND p.pedimento = {$pedimento}";
        } else {
            $q = "";
        }
        if (isset($ie) && $ie != '') {
            $q .= " AND p.tipoOperacion = '{$ie}'";
        } else {
            $q .= "";
        }
        if ($patente == 9999 && $aduana == 999) {
            if (!isset($month)) {
                $where = "p.rfc = '{$rfc}' AND YEAR(p.fechaPago) = {$year}";
            } else {
                $where = "p.rfc = '{$rfc}' AND YEAR(p.fechaPago) = {$year} AND MONTH(p.fechaPago) = {$month}";
            }
        } else {
            $where = "p.rfc = '{$rfc}' AND YEAR(p.fechaPago) = {$year} AND MONTH(p.fechaPago) = {$month} AND p.patente = {$patente} AND p.aduana = {$aduana}{$q}";
        }
        if ($tipo == "extendido") {
            $sql = "SELECT 
                p.operacion AS Operacion
                ,p.tipoOperacion AS TipoOperacion
                ,d.patente AS Patente
                ,d.aduana AS Aduana
                ,d.pedimento AS Pedimento
                ,d.referencia AS Trafico
                ,d.transporteEntrada AS TransporteEntrada
                ,d.transporteArribo AS TransporteArribo
                ,d.transporteSalida AS TransporteSalida
                ,d.fechaEntrada AS FechaEntrada
                ,d.fechaPago AS FechaPago
                ,d.firmaValidacion AS FirmaValidacion
                ,d.firmaBanco AS FirmaBanco
                ,d.tipoCambio AS TipoCambio
                ,d.cvePed AS CvePed
                ,d.regimen AS Regimen
                ,d.aduanaEntrada AS AduanaEntrada
                ,ROUND(d.valorDolares,0) AS ValorDolares
                ,ROUND(d.valorAduana,0) AS ValorAduana
                ,ROUND(d.fletes,2) AS Fletes
                ,ROUND(d.seguros,2) AS Seguros
                ,ROUND(d.embalajes,2) AS Embalajes
                ,ROUND(d.otrosIncrementales,2) AS OtrosIncrementales
                ,ROUND(d.dta,2) AS DTA
                ,ROUND(d.iva,2) AS IVA
                ,ROUND(d.igi,2) AS IGI
                ,ROUND(d.prev,2) AS PREV
                ,ROUND(d.cnt,2) AS CNT
                ,ROUND(d.totalEfectivo,2) AS TotalEfectivo
                ,ROUND(d.pesoBruto,4) AS PesoBruto
                ,d.bultos AS Bultos
                ,d.guias AS Guias
                ,d.bl AS BL
                ,d.talon AS Talon
                ,d.contenedores AS Contenedores
                ,d.observaciones AS ObservacionesPedimento
                ,a.numFactura AS NumFactura
                ,a.cove AS Cove
                ,a.fechaFactura AS FechaFactura
                ,a.incoterm AS Incoterm
                ,ROUND(a.valorFacturaUsd,2) AS ValorFacturaUsd
                ,ROUND(a.valorFacturaMonExt,2) AS ValorFacturaMonExt
                ,a.nomProveedor AS NomProveedor
                ,a.paisFactura AS PaisFactura
                ,a.taxId AS TaxId
                ,a.divisa AS Divisa
                ,ROUND(a.factorMonExt,4) AS FactorMonExt
                ,a.numParte AS NumParte
                ,a.descripcion AS Descripcion
                ,a.fraccion AS Fraccion
                ,a.ordenFraccion AS OrdenFraccion
                ,ROUND(SUM(a.valorMonExt),2) AS ValorMonExt
                ,ROUND(SUM(a.ValorMonExt * d.tipoCambio),2) AS ValorAduanaMXN
                ,ROUND(SUM(a.cantUMC),4) AS CantUMC
                ,a.umc AS UMC
                ,ROUND(SUM(a.cantUMT),4) AS CantUMT
                ,a.umt AS UMT
                ,a.paisOrigen AS PaisOrigen
                ,a.paisVendedor AS PaisVendedor
                ,ROUND(a.tasaAdvalorem,2) AS TasaAdvalorem
                ,a.tlc AS TLC
                ,a.tlcan AS TLCAN
                ,a.tlcue AS TLCUE
                ,a.prosec AS PROSEC
                ,a.patenteOrig AS PatenteOrig
                ,a.aduanaOrig AS AduanaOrig
                ,a.pedimentoOrig AS PedimentoOrig
                ,a.observacion AS Observacion
                FROM ws_pedimentos p
                LEFT JOIN ws_detalle_pedimentos d ON d.patente = p.patente AND d.aduana = p.aduana AND d.pedimento = p.pedimento
                LEFT JOIN ws_anexo_pedimentos a ON a.patente = a.patente AND a.aduana = p.aduana AND a.pedimento = p.pedimento
                WHERE {$where}
                GROUP BY p.operacion, p.patente, d.patente, d.aduana, d.pedimento, d.referencia, d.transporteEntrada, d.transporteArribo, d.transporteSalida, d.fechaEntrada, d.fechaPago, d.firmaValidacion, d.cvePed, d.regimen, d.firmaBanco, d.aduanaEntrada, d.valorDolares, d.valorAduana, d.fletes, d.seguros, d.embalajes, d.otrosIncrementales, d.dta, d.iva, d.igi, d.prev, d.cnt, d.totalEfectivo, d.pesoBruto, d.bultos, d.guias, d.bl, d.talon, d.contenedores, d.observaciones, a.incoterm, a.valorFacturaUsd, p.tipoOperacion, p.referencia, d.tipoCambio, a.numFactura, a.cove, a.fechaFactura, a.paisFactura, a.paisOrigen, a.paisVendedor, a.divisa, a.numParte, a.fraccion, a.ordenFraccion, a.umc, a.valorFacturaMonExt, a.nomProveedor, a.taxId, a.factorMonExt, a.descripcion, a.umt, a.tasaAdvalorem, a.tlc, a.tlcan, a.tlcue, a.prosec, a.patenteOrig, a.aduanaOrig, a.pedimentoOrig, a.observacion
                ORDER BY d.patente, d.aduana, d.pedimento, d.fechaPago, a.ordenFraccion ASC;";
        } else {
            $sql = "SELECT 
                p.operacion AS Operacion
                ,p.referencia AS Trafico   
                ,d.tipoCambio AS TipoCambio
                ,a.numFactura AS NumFactura
                ,d.cvePed AS CvePedimento
                ,a.cove AS Factura
                ,a.fechaFactura AS FechaFactura                
                ,a.paisFactura AS PaisFactura
                ,a.numParte AS NumParte
                ,a.fraccion AS Fraccion
                ,a.ordenFraccion AS Secuencia
                -- ,SUM(a.valorMonExt) AS Total
                ,a.valorMonExt AS Total
                -- ,(a.valorMonExt / a.cantUMC) AS PrecioUnitario
                -- ,SUM(a.cantUMC) AS CantUMC
                ,a.cantUMC AS CantUMC
                ,a.umc AS UMC
                ,a.paisOrigen AS PaisOrigen                
                ,a.patenteOrig AS PatenteOrig
                ,a.aduanaOrig AS AduanaOrig
                ,a.pedimentoOrig AS PedimentoOrig
                FROM ws_pedimentos p
                LEFT JOIN ws_detalle_pedimentos d ON d.patente = p.patente AND d.aduana = p.aduana AND d.pedimento = p.pedimento
                LEFT JOIN ws_anexo_pedimentos a ON a.patente = a.patente AND a.aduana = p.aduana AND a.pedimento = p.pedimento
                WHERE {$where}
                -- GROUP BY p.operacion, p.referencia, d.tipoCambio, a.numFactura, a.cove, a.fechaFactura, a.paisFactura, a.paisOrigen, a.paisVendedor, a.divisa, a.numParte, a.fraccion, a.ordenFraccion, a.umc
                ORDER BY d.patente, d.aduana, d.pedimento, d.fechaPago, a.ordenFraccion ASC;";
        }
        $db = $this->_db_table->getAdapter();
        $stm = $db->query($sql);
        $stmt = $stm->fetchAll();
        if ($stmt) {
            $data = array();
            foreach ($stmt as $item) {
                if ($tipo == 'parcial') {
                    $item["Referencia"] = $item["Operacion"];
                } else {
                    $item["Referencia"] = $item["Operacion"];
                    $item["Operacion"] = $item["TipoOperacion"];
                }
                $item["FechaFactura"] = date('Y/m/d', strtotime($item["FechaFactura"]));
                $item["FechaEntrada"] = date('Y/m/d', strtotime($item["FechaEntrada"]));
                $item["FechaPago"] = date('Y/m/d', strtotime($item["FechaPago"]));
                $data[] = $item;
            }
            return $data;
        }
        return null;
    }

    /**
     * 
     * @param int $patente
     * @param int $aduana
     * @param int $pedimento
     * @return array
     * @throws Exception
     */
    public function obtenerPedimento($patente, $aduana, $pedimento) {
        try {
            $sql = "SELECT 
                p.operacion AS Operacion
                ,p.tipoOperacion AS TipoOperacion
                ,d.patente AS Patente
                ,d.aduana AS Aduana
                ,d.pedimento AS Pedimento
                ,d.referencia AS Trafico
                ,d.transporteEntrada AS TransporteEntrada
                ,d.transporteArribo AS TransporteArribo
                ,d.transporteSalida AS TransporteSalida
                ,d.fechaEntrada AS FechaEntrada
                ,d.fechaPago AS FechaPago
                ,d.firmaValidacion AS FirmaValidacion
                ,d.firmaBanco AS FirmaBanco
                ,d.tipoCambio AS TipoCambio
                ,d.cvePed AS CvePed
                ,d.regimen AS Regimen
                ,d.aduanaEntrada AS AduanaEntrada
                ,ROUND(d.valorDolares,0) AS ValorDolares
                ,ROUND(d.valorAduana,0) AS ValorAduana
                ,ROUND(d.fletes,2) AS Fletes
                ,ROUND(d.seguros,2) AS Seguros
                ,ROUND(d.embalajes,2) AS Embalajes
                ,ROUND(d.otrosIncrementales,2) AS OtrosIncrementales
                ,ROUND(d.dta,2) AS DTA
                ,ROUND(d.iva,2) AS IVA
                ,ROUND(d.igi,2) AS IGI
                ,ROUND(d.prev,2) AS PREV
                ,ROUND(d.cnt,2) AS CNT
                ,ROUND(d.totalEfectivo,2) AS TotalEfectivo
                ,ROUND(d.pesoBruto,4) AS PesoBruto
                ,d.bultos AS Bultos
                ,d.guias AS Guias
                ,d.bl AS BL
                ,d.talon AS Talon
                ,d.contenedores AS Contenedores
                ,d.observaciones AS ObservacionesPedimento                
                FROM ws_pedimentos p
                LEFT JOIN ws_detalle_pedimentos d ON d.patente = p.patente AND d.aduana = p.aduana AND d.pedimento = p.pedimento
                WHERE p.patente = {$patente} AND p.pedimento = {$pedimento} AND p.aduana LIKE '" . substr($aduana, 0, 2) . "%'
                GROUP BY p.operacion, p.referencia, d.tipoCambio
                ORDER BY d.patente, d.aduana, d.pedimento, d.fechaPago ASC;";
            $db = $this->_db_table->getAdapter();
            $stm = $db->query($sql);
            $stmt = $stm->fetchAll();
            if ($stmt) {
                $data = array();
                foreach ($stmt as $item) {
                    $item["Referencia"] = $item["Operacion"];
                    $item["Operacion"] = $item["TipoOperacion"];
                    $item["FechaEntrada"] = date('Y/m/d', strtotime($item["FechaEntrada"]));
                    $item["FechaPago"] = date('Y/m/d', strtotime($item["FechaPago"]));
                    $data[] = $item;
                }
                return $data;
            }
            return null;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("Zend DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    /**
     * 
     * @param int $patente
     * @param int $aduana
     * @param int $pedimento
     * @return array
     * @throws Exception
     */
    public function obtenerPedimentoFacturas($patente, $aduana, $pedimento) {
        try {
            $sql = "SELECT 
                a.numFactura AS NumFactura
                ,a.cove AS Cove
                ,a.fechaFactura AS FechaFactura
                ,a.incoterm AS Incoterm
                ,ROUND(a.valorFacturaUsd,2) AS ValorFacturaUsd
                ,ROUND(a.valorFacturaMonExt,2) AS ValorFacturaMonExt
                ,a.nomProveedor AS NomProveedor
                ,a.paisFactura AS PaisFactura
                ,a.taxId AS TaxId
                ,a.divisa AS Divisa
                ,ROUND(a.factorMonExt,4) AS FactorMonExt          
                FROM ws_pedimentos p
                LEFT JOIN ws_anexo_pedimentos a ON a.patente = p.patente AND a.aduana = p.aduana AND a.pedimento = p.pedimento
                WHERE p.patente = {$patente} AND p.pedimento = {$pedimento} AND p.aduana LIKE '" . substr($aduana, 0, 2) . "%'
                GROUP BY a.numFactura;";
            $db = $this->_db_table->getAdapter();
            $stm = $db->query($sql);
            $stmt = $stm->fetchAll();
            if ($stmt) {
                $data = array();
                foreach ($stmt as $item) {
                    $item["FechaFactura"] = date('Y/m/d', strtotime($item["FechaFactura"]));
                    $data[] = $item;
                }
                return $data;
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("Zend DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    /**
     * 
     * @param int $patente
     * @param int $aduana
     * @param int $pedimento
     * @param string $numFactura
     * @return array
     * @throws Exception
     */
    public function obtenerPedimentoPartes($patente, $aduana, $pedimento, $numFactura) {
        try {
            $sql = "SELECT                 
                a.numParte AS NumParte
                ,a.descripcion AS Descripcion
                ,a.fraccion AS Fraccion
                ,a.ordenFraccion AS OrdenFraccion
                ,ROUND(SUM(a.valorMonExt),2) AS ValorMonExt
                ,ROUND(SUM(a.cantUMC),4) AS CantUMC
                ,a.umc AS UMC
                ,ROUND(SUM(a.cantUMT),4) AS CantUMT
                ,a.umt AS UMT
                ,a.paisOrigen AS PaisOrigen
                ,a.paisVendedor AS PaisVendedor
                ,ROUND(a.tasaAdvalorem,2) AS TasaAdvalorem
                ,a.tlc AS TLC
                ,a.tlcan AS TLCAN
                ,a.tlcue AS TLCUE
                ,a.prosec AS PROSEC
                ,a.patenteOrig AS PatenteOrig
                ,a.aduanaOrig AS AduanaOrig
                ,a.pedimentoOrig AS PedimentoOrig
                ,a.observacion AS Observacion           
                FROM ws_pedimentos p
                LEFT JOIN ws_anexo_pedimentos a ON a.patente = p.patente AND a.aduana = p.aduana AND a.pedimento = p.pedimento
                WHERE p.patente = {$patente} AND p.pedimento = {$pedimento} AND p.aduana LIKE '" . substr($aduana, 0, 2) . "%' AND a.numFactura = '{$numFactura}'
                GROUP BY a.numFactura;";
            $db = $this->_db_table->getAdapter();
            $stm = $db->query($sql);
            $stmt = $stm->fetchAll();
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("Zend DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    /**
     * 
     * @param int $patente
     * @param int $aduana
     * @param int $year
     * @param string $fechaIni
     * @param string $fechaFin
     * @param type $rfc
     * @param type $tipo
     * @param int $pedimento
     * @param string $ie
     * @return array|null
     */
    public function obtenerAnexoFechas($patente, $aduana, $year, $fechaIni, $fechaFin, $rfc, $tipo, $pedimento = null, $ie = null) {
        try {
            if (isset($pedimento)) {
                $q = " AND p.pedimento = {$pedimento}";
            } else {
                $q = "";
            }
            if (isset($ie) && $ie != '') {
                $q .= " AND p.tipoOperacion = '{$ie}'";
            } else {
                $q .= "";
            }
            if ($patente == 9999 && $aduana == 999) {
                $where = "p.rfc = '{$rfc}' AND (p.fechaPago BETWEEN '{$fechaIni}' AND '{$fechaFin}')";
            } else {
                $where = "p.rfc = '{$rfc}' AND YEAR(p.fechaPago) = {$year} AND MONTH(p.fechaPago) = {$month} AND p.patente = {$patente} AND p.aduana = {$aduana}{$q}";
            }
            if ($tipo == 'extendido') {
                $sql = "SELECT 
                        p.operacion AS Operacion
                        ,p.tipoOperacion AS TipoOperacion
                        ,d.patente AS Patente
                        ,d.aduana AS Aduana
                        ,d.pedimento AS Pedimento
                        ,d.referencia AS Trafico
                        ,d.transporteEntrada AS TransporteEntrada
                        ,d.transporteArribo AS TransporteArribo
                        ,d.transporteSalida AS TransporteSalida
                        ,d.fechaEntrada AS FechaEntrada
                        ,d.fechaPago AS FechaPago
                        ,d.firmaValidacion AS FirmaValidacion
                        ,d.firmaBanco AS FirmaBanco
                        ,d.tipoCambio AS TipoCambio
                        ,d.cvePed AS CvePed
                        ,d.regimen AS Regimen
                        ,d.aduanaEntrada AS AduanaEntrada
                        ,ROUND(d.valorDolares,0) AS ValorDolares
                        ,ROUND(d.valorAduana,0) AS ValorAduana
                        ,ROUND(d.fletes,2) AS Fletes
                        ,ROUND(d.seguros,2) AS Seguros
                        ,ROUND(d.embalajes,2) AS Embalajes
                        ,ROUND(d.otrosIncrementales,2) AS OtrosIncrementales
                        ,ROUND(d.dta,2) AS DTA
                        ,ROUND(d.iva,2) AS IVA
                        ,ROUND(d.igi,2) AS IGI
                        ,ROUND(d.prev,2) AS PREV
                        ,ROUND(d.cnt,2) AS CNT
                        ,ROUND(d.totalEfectivo,2) AS TotalEfectivo
                        ,ROUND(d.pesoBruto,4) AS PesoBruto
                        ,d.bultos AS Bultos
                        ,d.guias AS Guias
                        ,d.bl AS BL
                        ,d.talon AS Talon
                        ,d.contenedores AS Contenedores
                        ,d.observaciones AS ObservacionesPedimento
                        ,a.numFactura AS NumFactura
                        ,a.cove AS Cove
                        ,a.fechaFactura AS FechaFactura
                        ,a.incoterm AS Incoterm
                        ,ROUND(a.valorFacturaUsd,2) AS ValorFacturaUsd
                        ,ROUND(a.valorFacturaMonExt,2) AS ValorFacturaMonExt
                        ,a.nomProveedor AS NomProveedor
                        ,a.paisFactura AS PaisFactura
                        ,a.taxId AS TaxId
                        ,a.divisa AS Divisa
                        ,ROUND(a.factorMonExt,4) AS FactorMonExt
                        ,a.numParte AS NumParte
                        ,a.descripcion AS Descripcion
                        ,a.fraccion AS Fraccion
                        ,a.ordenFraccion AS OrdenFraccion
                        ,ROUND(SUM(a.valorMonExt),2) AS ValorMonExt
                        ,ROUND(SUM(a.ValorMonExt * d.tipoCambio),2) AS ValorAduanaMXN
                        ,ROUND(SUM(a.cantUMC),4) AS CantUMC
                        ,a.umc AS UMC
                        ,ROUND(SUM(a.cantUMT),4) AS CantUMT
                        ,a.umt AS UMT
                        ,a.paisOrigen AS PaisOrigen
                        ,a.paisVendedor AS PaisVendedor
                        ,ROUND(a.tasaAdvalorem,2) AS TasaAdvalorem
                        ,a.tlc AS TLC
                        ,a.tlcan AS TLCAN
                        ,a.tlcue AS TLCUE
                        ,a.prosec AS PROSEC
                        ,a.patenteOrig AS PatenteOrig
                        ,a.aduanaOrig AS AduanaOrig
                        ,a.pedimentoOrig AS PedimentoOrig
                        ,a.observacion AS Observacion
                        FROM ws_pedimentos p
                        LEFT JOIN ws_detalle_pedimentos d ON d.patente = p.patente AND LEFT(d.aduana, 2) = LEFT(p.aduana, 2) AND d.pedimento = p.pedimento
                        LEFT JOIN ws_anexo_pedimentos a ON a.patente = a.patente AND LEFT(d.aduana, 2) = LEFT(p.aduana, 2) AND a.pedimento = p.pedimento
                        WHERE {$where}
                        GROUP BY p.operacion, p.referencia, d.tipoCambio, a.numFactura, a.cove, a.fechaFactura, a.paisFactura, a.paisOrigen, a.paisVendedor, a.divisa, a.numParte, a.fraccion, a.ordenFraccion, a.umc
                        ORDER BY d.patente, d.aduana, d.pedimento, d.fechaPago, a.ordenFraccion ASC;";
            } else {
                $sql = "SELECT 
                        p.operacion AS Operacion
                        ,p.referencia AS Trafico   
                        ,d.tipoCambio AS TipoCambio
                        ,a.numFactura AS NumFactura
                        ,d.cvePed AS CvePedimento
                        ,a.cove AS Factura
                        ,a.fechaFactura AS FechaFactura                
                        ,a.paisFactura AS PaisFactura
                        ,a.numParte AS NumParte
                        ,a.fraccion AS Fraccion
                        ,a.ordenFraccion AS Secuencia
                        -- ,SUM(a.valorMonExt) AS Total
                        ,a.valorMonExt AS Total
                        -- ,(a.valorMonExt / a.cantUMC) AS PrecioUnitario
                        -- ,SUM(a.cantUMC) AS CantUMC
                        ,a.cantUMC AS CantUMC
                        ,a.umc AS UMC
                        ,a.paisOrigen AS PaisOrigen                
                        ,a.patenteOrig AS PatenteOrig
                        ,a.aduanaOrig AS AduanaOrig
                        ,a.pedimentoOrig AS PedimentoOrig
                        FROM ws_pedimentos p
                        LEFT JOIN ws_detalle_pedimentos d ON d.patente = p.patente AND d.aduana = p.aduana AND d.pedimento = p.pedimento
                        LEFT JOIN ws_anexo_pedimentos a ON a.patente = a.patente AND a.aduana = p.aduana AND a.pedimento = p.pedimento
                        WHERE {$where}
                        -- GROUP BY p.operacion, p.referencia, d.tipoCambio, a.numFactura, a.cove, a.fechaFactura, a.paisFactura, a.paisOrigen, a.paisVendedor, a.divisa, a.numParte, a.fraccion, a.ordenFraccion, a.umc
                        ORDER BY d.patente, d.aduana, d.pedimento, d.fechaPago, a.ordenFraccion ASC;";
            }

            $db = $this->_db_table->getAdapter();
            $stm = $db->query($sql);
            $stmt = $stm->fetchAll();
            if ($stmt) {
                $data = array();
                foreach ($stmt as $item) {
                    if ($tipo == 'parcial') {
                        $item["Referencia"] = $item["Operacion"];
                    } else {
                        $item["Referencia"] = $item["Operacion"];
                        $item["Operacion"] = $item["TipoOperacion"];
                    }
                    $item["FechaFactura"] = date('Y/m/d', strtotime($item["FechaFactura"]));
                    $item["FechaEntrada"] = date('Y/m/d', strtotime($item["FechaEntrada"]));
                    $item["FechaPago"] = date('Y/m/d', strtotime($item["FechaPago"]));
                    $data[] = $item;
                }
                return $data;
            }
            return null;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("Zend DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    /**
     * 
     * @param int $patente
     * @param int $aduana
     * @param int $year
     * @param int $month
     * @param type $rfc
     * @param int $pedimento
     * @param string $ie
     * @return array|null
     */
    public function obtenerEncabezados($patente, $aduana, $year, $month, $rfc, $tipo, $pedimento = null, $ie = null) {
        if (isset($pedimento)) {
            $q = " AND p.pedimento = {$pedimento}";
        } else {
            $q = "";
        }
        if (isset($ie) && $ie != '') {
            $q .= " AND p.tipoOperacion = '{$ie}'";
        } else {
            $q .= "";
        }
        if ($patente == 9999 && $aduana == 999) {
            $where = "p.rfc = '{$rfc}' AND YEAR(p.fechaPago) = {$year} AND MONTH(p.fechaPago) = {$month}{$q}";
        } else {
            $where = "p.rfc = '{$rfc}' AND YEAR(p.fechaPago) = {$year} AND MONTH(p.fechaPago) = {$month} AND p.patente = {$patente} AND p.aduana = {$aduana}{$q}";
        }
        $sql = "SELECT 
            p.operacion AS Operacion
            ,p.tipoOperacion AS TipoOperacion
            ,d.patente AS Patente
            ,d.aduana AS Aduana
            ,d.pedimento AS Pedimento
            ,d.referencia AS Trafico
            ,d.transporteEntrada AS TransporteEntrada
            ,d.transporteArribo AS TransporteArribo
            ,d.transporteSalida AS TransporteSalida
            ,d.fechaEntrada AS FechaEntrada
            ,d.fechaPago AS FechaPago
            ,d.firmaValidacion AS FirmaValidacion
            ,d.firmaBanco AS FirmaBanco
            ,TRUNCATE(d.tipoCambio,4) AS TipoCambio
            ,d.cvePed AS CvePed
            ,d.regimen AS Regimen
            ,d.aduanaEntrada AS AduanaEntrada
            ,ROUND(d.valorDolares,0) AS ValorDolares
            ,ROUND(d.valorAduana,0) AS ValorAduana
            ,ROUND(d.fletes,0) AS Fletes
            ,ROUND(d.seguros,0) AS Seguros
            ,ROUND(d.embalajes,0) AS Embalajes
            ,ROUND(d.otrosIncrementales,0) AS OtrosIncrementales
            ,ROUND(d.dta,0) AS DTA
            ,ROUND(d.iva,0) AS IVA
            ,ROUND(d.igi,0) AS IGI
            ,ROUND(d.prev,0) AS PREV
            ,ROUND(d.cnt,0) AS CNT
            ,ROUND(d.totalEfectivo,0) AS TotalEfectivo
            ,ROUND(d.pesoBruto,4) AS PesoBruto
            ,d.bultos AS Bultos
            ,d.guias AS Guias
            ,d.bl AS BL
            ,d.talon AS Talon
            ,d.contenedores AS Contenedores
            ,d.observaciones AS ObservacionesPedimento                
            FROM ws_pedimentos p
            LEFT JOIN ws_detalle_pedimentos d ON d.patente = p.patente AND d.aduana = p.aduana AND d.pedimento = p.pedimento
            WHERE {$where}
            ORDER BY d.patente, d.aduana, d.pedimento, d.fechaPago ASC;";
        $db = $this->_db_table->getAdapter();
        $stm = $db->query($sql);
        $stmt = $stm->fetchAll();
        if ($stmt) {
            $data = array();
            foreach ($stmt as $item) {
                if ($tipo == 'parcial') {
                    $item["Referencia"] = $item["Operacion"];
                } else {
                    $item["Referencia"] = $item["Operacion"];
                    $item["Operacion"] = $item["TipoOperacion"];
                }
                $item["FechaFactura"] = date('Y/m/d', strtotime($item["FechaFactura"]));
                $item["FechaEntrada"] = date('Y/m/d', strtotime($item["FechaEntrada"]));
                $item["FechaPago"] = date('Y/m/d', strtotime($item["FechaPago"]));
                $data[] = $item;
            }
            return $data;
        }
        return null;
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
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array('p' => 'ws_pedimentos'), array('p.operacion AS operacion', 'p.tipoOperacion AS tipoOperacion'))
                    ->joinLeft(array('d' => 'ws_detalle_pedimentos'), "d.patente = p.patente AND d.aduana = p.aduana AND d.pedimento = p.pedimento", array('patente AS patente', 'aduana AS aduana', 'pedimento AS pedimento', 'referencia AS trafico', 'transporteEntrada AS transporteEntrada', 'transporteArribo AS transporteArribo', 'transporteSalida AS transporteSalida', 'fechaEntrada AS fechaEntrada', 'fechaPago AS fechaPago', 'firmaValidacion AS firmaValidacion', 'firmaBanco AS firmaBanco', 'TRUNCATE(tipoCambio,4) AS tipoCambio', 'cvePed AS cvePed', 'regimen AS regimen', 'aduanaEntrada AS aduanaEntrada', 'ROUND(valorDolares,0) AS valorDolares', 'ROUND(valorAduana,0) AS valorAduana', 'ROUND(fletes,0) AS fletes', 'ROUND(seguros,0) AS seguros', 'ROUND(embalajes,0) AS embalajes', 'ROUND(otrosIncrementales,0) AS otrosIncrementales', 'ROUND(dta,0) AS dta', 'ROUND(iva,0) AS iva', 'ROUND(igi,0) AS igi', 'ROUND(prev,0) AS prev', 'ROUND(cnt,0) AS cnt', 'ROUND(totalEfectivo,0) AS totalEfectivo', 'ROUND(pesoBruto,4) AS pesoBruto', 'bultos AS bultos', 'guias AS guias', 'bl AS bl', 'talon AS talon', 'contenedores AS contenedores', 'observaciones AS observacionesPedimento'))
                    ->where('p.fechaPago >= ?', date('Y-m-d H:i:s', strtotime($fechaIni)))
                    ->where('p.fechaPago <= ?', date('Y-m-d', strtotime($fechaFin)) . ' 23:59:59')
                    ->where('p.patente = ?', $patente)
                    ->where('p.aduana = ?', $aduana)
                    ->where('p.rfc = ?', $rfc);
            if (!isset($fechaIni) && !isset($fechaFin)) {
                $sql->where('YEAR(p.fechaPago) = ?', $year)
                        ->where('MONTH(p.fechaPago) = ?', $month);
            }
            return;
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("Zend DB Exception " . __METHOD__ . ": " . $ex->getMessage());
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
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array('p' => 'ws_pedimentos'), array('p.operacion AS operacion', 'p.tipoOperacion AS tipoOperacion'))
                    ->joinLeft(array('d' => 'ws_detalle_pedimentos'), "d.patente = p.patente AND d.aduana = p.aduana AND d.pedimento = p.pedimento", array('d.patente AS patente', 'd.aduana AS aduana', 'pedimento AS pedimento', 'referencia AS trafico', 'cvePed'))
                    ->joinLeft(array('a' => 'ws_anexo_pedimentos'), "a.patente = a.patente AND a.aduana = p.aduana AND a.pedimento = p.pedimento", array('fechaFactura AS fechaFactura', 'paisFactura AS paisFactura', 'numParte AS numParte', 'fraccion AS fraccion', 'ordenFraccion AS secuencia', 'valorMonExt AS total', 'cantUMC AS cantUmc', 'umc AS umc', 'paisOrigen AS paisOrigen', 'patenteOrig AS patenteOrig', 'aduanaOrig AS aduanaOrig', 'pedimentoOrig AS pedimentoOrig', 'numFactura as numFactura', '(valorMonExt / cantUMC) as precioUnitario'))
                    ->where('p.fechaPago >= ?', date('Y-m-d H:i:s', strtotime($fechaIni)))
                    ->where('p.fechaPago <= ?', date('Y-m-d', strtotime($fechaFin)) . ' 23:59:59')
                    ->where('p.patente = ?', $patente)
                    ->where('p.aduana = ?', $aduana)
                    ->where('p.rfc = ?', $rfc);
            if (!isset($fechaIni) && !isset($fechaFin)) {
                $sql->where('YEAR(p.fechaPago) = ?', $year)
                        ->where('MONTH(p.fechaPago) = ?', $month);
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("Zend DB Exception " . __METHOD__ . ": " . $ex->getMessage());
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
    public function anexo($patente, $aduana, $rfc, $fechaIni = null, $fechaFin = null, $year = null, $month = null) {
        try {
            $detailsFields = array(
                "patente AS patente",
                "aduana AS aduana",
                "pedimento AS pedimento",
                "referencia AS trafico",
                "transporteEntrada AS transporteEntrada",
                "transporteArribo AS transporteArribo",
                "transporteSalida AS transporteSalida",
                "fechaEntrada AS fechaEntrada",
                "fechaPago AS fechaPago",
                "firmaValidacion AS firmaValidacion",
                "firmaBanco AS firmaBanco",
                new Zend_Db_Expr("TRUNCATE(tipoCambio,4) AS tipoCambio"),
                "cvePed AS cvePed", "regimen AS regimen",
                "aduanaEntrada AS aduanaEntrada",
                new Zend_Db_Expr("ROUND(valorDolares,0) AS valorDolares"),
                new Zend_Db_Expr("(CASE WHEN d.patente = 3574 AND d.aduana = 240 THEN ROUND(valorDolares * tipoCambio,0) ELSE ROUND(valorAduana,0) END) AS valorAduana"),
                new Zend_Db_Expr("(CASE WHEN d.patente = 3574 THEN ROUND(fletes * tipoCambio,0) ELSE ROUND(fletes,0) END) AS fletes"),
                new Zend_Db_Expr("(CASE WHEN d.patente = 3574 THEN ROUND(seguros * tipoCambio,0) ELSE ROUND(seguros,0) END) AS seguros"),
                new Zend_Db_Expr("(CASE WHEN d.patente = 3574 THEN ROUND(embalajes * tipoCambio,0) ELSE ROUND(embalajes,0) END) AS embalajes"),
                new Zend_Db_Expr("(CASE WHEN d.patente = 3574 THEN ROUND(otrosIncrementales * tipoCambio,0) ELSE ROUND(otrosIncrementales,0) END) AS otrosIncrementales"),
                new Zend_Db_Expr("ROUND(dta,0) AS dta"),
                new Zend_Db_Expr("ROUND(d.iva,0) AS iva"),
                new Zend_Db_Expr("ROUND(igi,0) AS igi"),
                new Zend_Db_Expr("(CASE WHEN d.patente = 3574 AND d.aduana = 160 THEN 210 ELSE prev END) AS prev"),
                new Zend_Db_Expr("(CASE WHEN d.patente = 3574 AND d.aduana = 160 THEN 57 ELSE cnt END) AS cnt"),
                new Zend_Db_Expr("ROUND(totalEfectivo,0) AS totalEfectivo"),
                new Zend_Db_Expr("ROUND(pesoBruto,4) AS pesoBruto"),
                "bultos AS bultos",
                "guias AS guias",
                "bl AS bl",
                "talon AS talon",
                "contenedores AS contenedores",
                "observaciones AS observacionesPedimento",
                "d.guias as guias"
            );
            $anexoFields = array(
                "fechaFactura",
                "cove",
                "paisFactura",
                "numParte",
                "fraccion",
                "ordenFraccion AS secuencia",
                "ordenFraccion",
                "valorMonExt AS total",
                "umc",
                "cantUMC AS cantUmc",
                "paisOrigen",
                "patenteOrig",
                "aduanaOrig",
                "pedimentoOrig",
                "numFactura",
                new Zend_Db_Expr("(valorMonExt / cantUMC) as precioUnitario"),
                "incoterm",
                "valorFacturaUsd",
                "valorFacturaMonExt",
                "nomProveedor",
                "taxId",
                "divisa",
                "descripcion",
                new Zend_Db_Expr("(CASE WHEN divisa = 'MXP' THEN (1 / d.tipoCambio) WHEN divisa = 'USD' THEN 1.0000 ELSE factorMonExt END) AS factorMonExt"),
                "umt",
                "cantUMT as cantUmt",
                "paisOrigen",
                "paisVendedor",
                "tasaAdvalorem",
                "tlc",
                "tlcan",
                "tlcue",
                "prosec",
                new Zend_Db_Expr("CONVERT('NA' USING utf8) as formaPagoArt"),
                new Zend_Db_Expr("CONVERT('NA' USING utf8) as formaPagoRt")
            );
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("p" => "ws_pedimentos"), array("p.operacion AS operacion", "p.tipoOperacion AS tipoOperacion"))
                    ->joinLeft(array("d" => "ws_detalle_pedimentos"), "d.patente = p.patente AND d.aduana = p.aduana AND d.pedimento = p.pedimento", $detailsFields)
                    ->joinLeft(array("a" => "ws_anexo_pedimentos"), "a.patente = a.patente AND a.aduana = p.aduana AND a.pedimento = p.pedimento", $anexoFields)
                    ->where("p.fechaPago >= ?", date("Y-m-d H:i:s", strtotime($fechaIni)))
                    ->where("p.fechaPago <= ?", date("Y-m-d", strtotime($fechaFin)) . " 23:59:59")
                    ->where("p.rfc = ?", $rfc);
            if ((int) $patente !== 9999 || (int) $aduana !== 999) {
                $sql->where("p.patente = ?", $patente)
                        ->where("p.aduana LIKE ?", substr($aduana, 0, 2) . "%");
            }
            if (!isset($fechaIni) && !isset($fechaFin)) {
                $sql->where("YEAR(p.fechaPago) = ?", $year)
                        ->where("MONTH(p.fechaPago) = ?", $month);
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("Zend DB Exception " . __METHOD__ . ": " . $ex->getMessage());
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
    public function anexoCnh($patente, $aduana, $rfc, $fechaIni = null, $fechaFin = null, $year = null, $month = null) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array('p' => 'ws_pedimentos'), array('p.operacion AS operacion', 'p.tipoOperacion AS tipoOperacion'))
                    ->joinLeft(array('d' => 'ws_detalle_pedimentos'), "d.patente = p.patente AND d.aduana = p.aduana AND d.pedimento = p.pedimento", array('patente AS patente', new Zend_Db_Expr('LEFT(d.aduana, 2) AS aduana'), 'pedimento AS pedimento', "guias as guias", new Zend_Db_Expr("RIGHT(d.aduana, 1) as seccion"), new Zend_Db_Expr("CAST('9' AS CHAR) as destino"), 'referencia AS trafico', 'transporteEntrada AS transporteEntrada', 'transporteArribo AS transporteArribo', 'transporteSalida AS transporteSalida', 'fechaEntrada AS fechaEntrada', 'fechaPago AS fechaPago', 'firmaValidacion AS firmaValidacion', 'firmaBanco AS firmaBanco', new Zend_Db_Expr('TRUNCATE(tipoCambio,4) AS tipoCambio'), 'cvePed AS cvePed', 'regimen AS regimen', 'aduanaEntrada AS aduanaEntrada', new Zend_Db_Expr('ROUND(valorDolares,0) AS valorDolares'), new Zend_Db_Expr('ROUND(valorAduana,0) AS valorAduana'), new Zend_Db_Expr('ROUND(fletes,0) AS fletes'), new Zend_Db_Expr('ROUND(seguros,0) AS seguros'), new Zend_Db_Expr('ROUND(embalajes,0) AS embalajes'), new Zend_Db_Expr('ROUND(otrosIncrementales,0) AS otrosIncrementales'), new Zend_Db_Expr('ROUND(dta,0) AS dta'), new Zend_Db_Expr('ROUND(d.iva,0) AS iva'), new Zend_Db_Expr('ROUND(igi,0) AS igi'), new Zend_Db_Expr('ROUND(prev,0) AS prev'), new Zend_Db_Expr('ROUND(cnt,0) AS cnt'), new Zend_Db_Expr('ROUND(totalEfectivo,0) AS totalEfectivo'), new Zend_Db_Expr('ROUND(pesoBruto,4) AS pesoBruto'), 'bultos AS bultos', 'guias AS guias', 'bl AS bl', 'talon AS talon', 'contenedores AS contenedores', 'observaciones AS observacionesPedimento'))
                    ->joinLeft(array('a' => 'ws_anexo_pedimentos'), "a.patente = a.patente AND a.aduana = p.aduana AND a.pedimento = p.pedimento", array('fechaFactura', 'cove', 'paisFactura', 'numParte', 'fraccion', 'ordenFraccion AS secuencia', 'valorMonExt AS total', 'cantUMC AS cantUmc', 'umc', 'paisOrigen', 'patenteOrig', 'aduanaOrig', 'pedimentoOrig', 'numFactura', new Zend_Db_Expr('(valorMonExt / cantUMC) as precioUnitario'), 'incoterm', 'valorFacturaUsd', 'valorFacturaMonExt', 'nomProveedor', 'taxId', 'divisa', 'descripcion', 'factorMonExt', 'umt', 'cantUMT as cantUmt', 'paisOrigen', 'paisVendedor', 'tasaAdvalorem', 'tlc', 'tlcan', 'tlcue', 'prosec', new Zend_Db_Expr("CONVERT('NA' USING utf8) as formaPagoArt"), new Zend_Db_Expr("CONVERT('NA' USING utf8) as formaPagoRt"), new Zend_Db_Expr("CONVERT('NA' USING utf8) as art"), new Zend_Db_Expr("CONVERT('NA' USING utf8) as rt"), new Zend_Db_Expr("CONVERT('NA' USING utf8) as otros")))
                    ->where('p.fechaPago >= ?', date('Y-m-d H:i:s', strtotime($fechaIni)))
                    ->where('p.fechaPago <= ?', date('Y-m-d', strtotime($fechaFin)) . ' 23:59:59')
                    ->where('p.patente = ?', $patente)
                    ->where('p.aduana LIKE ?', substr($aduana, 0, 2) . '%')
                    ->where('p.rfc = ?', $rfc);
            if (!isset($fechaIni) && !isset($fechaFin)) {
                $sql->where('YEAR(p.fechaPago) = ?', $year)
                        ->where('MONTH(p.fechaPago) = ?', $month);
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("Zend DB Exception " . __METHOD__ . ": " . $ex->getMessage());
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
    public function proveedores($patente, $aduana, $rfc) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->distinct()
                    ->from(array('p' => 'ws_pedimentos'), array())
                    ->joinLeft(array('a' => 'ws_anexo_pedimentos'), "a.patente = a.patente AND a.aduana = p.aduana AND a.pedimento = p.pedimento", array('nomProveedor', 'taxId'))
                    ->where('p.patente = ?', $patente)
                    ->where('p.aduana = ?', $aduana)
                    ->where('p.rfc = ?', $rfc)
                    ->where("a.nomProveedor <> ''")
                    ->order('nomProveedor');
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("Zend DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function layoutTecnico($patente, $aduana, $rfc) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->distinct()
                    ->from(array('p' => 'ws_pedimentos'), array())
                    ->joinLeft(array('a' => 'ws_anexo_pedimentos'), "a.patente = a.patente AND a.aduana = p.aduana AND a.pedimento = p.pedimento", array('nomProveedor', 'taxId'))
                    ->where('p.patente = ?', $patente)
                    ->where('p.aduana = ?', $aduana)
                    ->where('p.rfc = ?', $rfc)
                    ->where("a.nomProveedor <> ''")
                    ->order('nomProveedor');
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("Zend DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function reporte($year, $patente = null, $aduana = null) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array('patente', 'COUNT(pedimento) as cuenta'))
                    ->where("YEAR(fechaPago) = ?", $year)
                    ->group("patente");

            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("Zend DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function pedimentosPagados($patente, $aduana, $rfc, $year, $month) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array('patente', 'aduana', 'referencia'))
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana)
                    ->where("YEAR(fechaPago) = ?", $year)
                    ->where("MONTH(fechaPago) = ?", $month)
                    ->where("rfc = ?", $rfc);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("Zend DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function pedimentosPagadosWs($rfc, $fechaIni, $fechaFin) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array('*'))
                    ->where('fechaPago >= ?', $fechaIni)
                    ->where('fechaPago <= ?', $fechaFin)
                    ->where("rfc = ?", $rfc);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("Zend DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function actualizar($id, $arr) {
        try {
            $stmt = $this->_db_table->update($arr, array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("Zend DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

}
