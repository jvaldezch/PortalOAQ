<?php

/**
 * Description of Vucem_Xml
 * 
 * Esta clase conforma arreglos en archivos XML requeridos por la Ventanilla Única de Comercio Exterior (VUCEM)
 *
 * @author Jaime E. Valdez jvaldezch at gmail
 */
class OAQ_XmlPedimento {

    protected $_domtree;
    protected $_envelope;
    protected $_header;
    protected $_body;
    protected $_consulta;
    protected $_arr;
    protected $_cat;

    function set_arr($_arr) {
        $this->_arr = $_arr;
    }

    /**
     * 
     * Constructor, parametros obligatorios.
     * 
     * @param boolean $cove Si el XML va ser COVE
     * @param boolean $edoc Si el XML va ser EDOCUMENT
     */
    function __construct() {
        $this->_cat = new OAQ_XmlPedimentoCatalogos();
        $this->_domtree = new DOMDocument("1.0", "UTF-8");
        $this->_domtree->preserveWhiteSpace = false;
        $this->_domtree->formatOutput = true;
        $this->_envelope = $this->_domtree->createElementNS("http://schemas.xmlsoap.org/soap/envelope/", "S:Envelope");
        $this->_envelope->setAttributeNS("http://www.w3.org/2000/xmlns/", "xmlns:oxml", "http://www.ventanillaunica.gob.mx/cove/ws/oxml/");
        $this->_domtree->appendChild($this->_envelope);
        $this->_body = $this->_domtree->createElement("S:Body");
        $this->_header = $this->_domtree->createElement("S:Header");
        $this->_envelope->appendChild($this->_header);
        $this->_envelope->appendChild($this->_body);
    }

    public function security($time) {
        $security = $this->_domtree->createElement("wsse:Security");
        $security->setAttributeNS("http://www.w3.org/2000/xmlns/", "xmlns:wsse", "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd");
        $timestamp = $this->_domtree->createElement("wsu:Timestamp");
        $timestamp->setAttributeNS("http://www.w3.org/2000/xmlns/", "xmlns:wsu", "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd");
        $timestamp->appendChild($this->_domtree->createElement("wsu:Created", $time));
        $timestamp->appendChild($this->_domtree->createElement("wsu:Expires", $time));
        $security->appendChild($timestamp);
        $this->_header->appendChild($security);
    }

    public function consulta() {
        $this->_consulta = $this->_domtree->createElement("ns2:consultarPedimentoCompletoRespuesta");
        $this->_consulta->setAttributeNS("http://www.w3.org/2000/xmlns/", "xmlns:ns2", "http://www.ventanillaunica.gob.mx/pedimentos/ws/oxml/consultarpedimentocompleto");
        $this->_consulta->setAttributeNS("http://www.w3.org/2000/xmlns/", "xmlns:ns3", "http://www.ventanillaunica.gob.mx/common/ws/oxml/respuesta");
        $this->_consulta->setAttributeNS("http://www.w3.org/2000/xmlns/", "xmlns:ns4", "http://www.ventanillaunica.gob.mx/common/ws/oxml/resolucion");
        $this->_consulta->setAttributeNS("http://www.w3.org/2000/xmlns/", "xmlns:ns5", "http://www.ventanillaunica.gob.mx/common/ws/oxml/respuestatra");
        $this->_consulta->setAttributeNS("http://www.w3.org/2000/xmlns/", "xmlns:ns6", "http://www.ventanillaunica.gob.mx/common/ws/oxml/dictamen");
        $this->_consulta->setAttributeNS("http://www.w3.org/2000/xmlns/", "xmlns:ns7", "http://www.ventanillaunica.gob.mx/common/ws/oxml/observacion");
        $this->_consulta->setAttributeNS("http://www.w3.org/2000/xmlns/", "xmlns:ns8", "http://www.ventanillaunica.gob.mx/common/ws/oxml/requisito");
        $this->_consulta->setAttributeNS("http://www.w3.org/2000/xmlns/", "xmlns:ns9", "http://www.ventanillaunica.gob.mx/common/ws/oxml/opinion");
        $this->_consulta->appendChild($this->_domtree->createElement("ns3:tieneError", "false"));
        $this->_consulta->appendChild($this->_domtree->createElement("ns2:numeroOperacion", ""));
        $this->_body->appendChild($this->_consulta);
    }

    public function pedimento() {
        $pedimento = $this->_domtree->createElement("ns2:pedimento");
        $pedimento->appendChild($this->_domtree->createElement("ns2:pedimento", $this->_arr["pedimento"]));

        $encabezado = $this->_domtree->createElement("ns2:encabezado");
        $encabezado->appendChild($this->_domtree->createElement("ns2:tipoCambio", $this->_arr["tipoCambio"]));
        $encabezado->appendChild($this->_domtree->createElement("ns2:pesoBruto", $this->_arr["peroBruto"]));
        $encabezado->appendChild($this->_domtree->createElement("ns2:curpApoderadomandatario", $this->_arr["curpAgente"]));
        $encabezado->appendChild($this->_domtree->createElement("ns2:rfcAgenteAduanalSocFactura", $this->_arr["rfcSociedad"]));
        $encabezado->appendChild($this->_domtree->createElement("ns2:valorDolares", 0));
        $encabezado->appendChild($this->_domtree->createElement("ns2:valorAduanalTotal", 0));
        $encabezado->appendChild($this->_domtree->createElement("ns2:valorComercialTotal", 0));
        $encabezado->appendChild($this->_agregarClaveDescription("ns2", "tipoOperacion", ($this->_arr["tipoOperacion"] == "IMPO") ? 1 : 2, ($this->_arr["tipoOperacion"] == "IMPO") ? utf8_decode("Importacion") : utf8_decode("Exportacion")));
        $encabezado->appendChild($this->_agregarClaveDescription("ns2", "claveDocumento", $this->_arr["clavePedimento"], $this->_cat->clavePedimento($this->_arr["clavePedimento"])));
        $encabezado->appendChild($this->_agregarClaveDescription("ns2", "destino", $this->_arr["destino"], $this->_cat->destinoMercancia($this->_arr["destino"])));
        $encabezado->appendChild($this->_agregarClaveDescription("ns2", "aduanaEntradaSalida", $this->_arr["aduanaEntrada"], $this->_cat->aduanas($this->_arr["aduanaEntrada"])));
        $encabezado->appendChild($this->_agregarClaveDescription("ns2", "medioTrasnporteSalida", $this->_arr["transporteSalida"], $this->_cat->medioTransporte($this->_arr["transporteSalida"])));
        $encabezado->appendChild($this->_agregarClaveDescription("ns2", "medioTrasnporteArribo", $this->_arr["transporteArribo"], $this->_cat->medioTransporte($this->_arr["transporteArribo"])));
        $encabezado->appendChild($this->_agregarClaveDescription("ns2", "medioTrasnporteEntrada", $this->_arr["transporteEntrada"], $this->_cat->medioTransporte($this->_arr["transporteEntrada"])));

        $pedimento->appendChild($encabezado);

        $impoExp = $this->_domtree->createElement("ns2:importadorExportador");
        $impoExp->appendChild($this->_domtree->createElement("ns2:rfc", $this->_arr["rfcCliente"]));
        $impoExp->appendChild($this->_domtree->createElement("ns2:razonSocial", utf8_encode($this->_arr["nombreCliente"])));
        $dom = $this->_domtree->createElement("ns2:domicilio");
        $dom->appendChild($this->_domtree->createElement("ns2:calle", utf8_encode($this->_arr["calle"])));
        $dom->appendChild($this->_domtree->createElement("ns2:numeroExterior", utf8_encode($this->_arr["numExterior"])));
        $dom->appendChild($this->_domtree->createElement("ns2:numeroInterior", utf8_encode($this->_arr["numInterior"])));
        $dom->appendChild($this->_domtree->createElement("ns2:ciudadMunicipio", utf8_encode($this->_arr["municipio"])));
        $dom->appendChild($this->_domtree->createElement("ns2:codigoPostal", $this->_arr["codigoPostal"]));
        $impoExp->appendChild($dom);
        $impoExp->appendChild($this->_domtree->createElement("ns2:seguros", $this->_arr["seguros"]));
        $impoExp->appendChild($this->_domtree->createElement("ns2:fletes", $this->_arr["fletes"]));
        $impoExp->appendChild($this->_domtree->createElement("ns2:embalajes", $this->_arr["embalajes"]));
        $impoExp->appendChild($this->_domtree->createElement("ns2:incrementables", $this->_arr["otrosIncrementables"]));
        $impoExp->appendChild($this->_agregarClaveDescription("ns2", "aaduanaDespacho", $this->_arr["aduanaDespacho"], $this->_cat->aduanas($this->_arr["aduanaDespacho"])));
        foreach ($this->_arr["fechas"] as $k => $v) {
            $impoExp->appendChild($this->_fechas("ns2", $this->_convertirFecha($v), $k, $this->_cat->formaPago($v)));
        }
        $total = 0;
        foreach ($this->_arr["contribuciones"] as $k => $v) {
            $total += $v["importe"];
        }
        $impoExp->appendChild($this->_domtree->createElement("ns2:efectivo", $total));
        $impoExp->appendChild($this->_domtree->createElement("ns2:otros", 0));
        $impoExp->appendChild($this->_domtree->createElement("ns2:total", $total));
        $impoExp->appendChild($this->_agregarClaveDescription("ns2", "pais", $this->_arr["pais"], $this->_cat->pais($this->_arr["pais"])));

        $pedimento->appendChild($impoExp);

        foreach ($this->_arr["tasas"] as $k => $v) {
            $pedimento->appendChild($this->_tasas($k, $v["tipoTasa"], $this->_arr["contribuciones"][$k]["formaPago"], $this->_arr["contribuciones"][$k]["importe"], $v["tasaContribucion"]));
        }

        if (isset($this->_arr["facturas"])) {
            foreach ($this->_arr["facturas"] as $k => $v) {
                $proovComp = $this->_domtree->createElement("ns2:proveedoresCompradores");
                $proovComp->appendChild($this->_domtree->createElement("ns2:identificadorFiscal", $v["identificador"]));
                $proovComp->appendChild($this->_domtree->createElement("ns2:proveedorComprador", $v["proveedor"]));
                $proovComp->appendChild($this->_domicilio($v["domicilio"]));
                $proovComp->appendChild($this->_agregarClaveDescription("ns2", "moneda", $v["moneda"], $this->_cat->monedas($v["moneda"])));
                $proovComp->appendChild($this->_domtree->createElement("ns2:valorMonedaExtranjera", $v["valorComercial"]));
                $proovComp->appendChild($this->_domtree->createElement("ns2:valorDolares", $v["valorDolares"]));
                $proovComp->appendChild($this->_agregarClaveDescription("ns2", "pais", $v["pais"], $this->_cat->pais($v["pais"])));
                $pedimento->appendChild($proovComp);
            }
            foreach ($this->_arr["facturas"] as $k => $v) {
                $facturas = $this->_domtree->createElement("ns2:facturas");
                $facturas->appendChild($this->_domtree->createElement("ns2:fecha", $this->_convertirFecha($v["fechaFactura"])));
                $facturas->appendChild($this->_domtree->createElement("ns2:numero", $v["numFactura"]));
                $facturas->appendChild($this->_agregarClaveDescription("ns2", "terminoFacturacion", $v["incoterm"], $this->_cat->tipoFacturacion($v["incoterm"])));
                $facturas->appendChild($this->_agregarClaveDescription("ns2", "moneda", $v["moneda"], $this->_cat->monedas($v["moneda"])));
                $facturas->appendChild($this->_domtree->createElement("ns2:valorDolares", $v["valorDolares"]));
                $facturas->appendChild($this->_domtree->createElement("ns2:valorMonedaExtranjera", $v["valorComercial"]));
                $facturas->appendChild($this->_domtree->createElement("ns2:identificadorFiscalProveedorComprador", $v["identificador"]));
                $facturas->appendChild($this->_domtree->createElement("ns2:proveedorComprador", $v["proveedor"]));
                $pedimento->appendChild($facturas);
            }
        }

        if (isset($this->_arr["transportes"])) {
            $transportes = $this->_domtree->createElement("ns2:transportes");
            $transportes->appendChild($this->_domtree->createElement("ns2:identificador", $this->_arr["transportes"]["rfc"]));
            $pais = $this->_domtree->createElement("ns2:paisTransporte");
            $pais->appendChild($this->_domtree->createElement("clave", $this->_arr["transportes"]["pais"]));
            $pais->appendChild($this->_domtree->createElement("descripcion", $this->_cat->pais($this->_arr["transportes"]["pais"])));
            $transportes->appendChild($pais);
            $transportes->appendChild($this->_domtree->createElement("ns2:nombre", $this->_arr["transportes"]["nombre"]));
            $pedimento->appendChild($transportes);
        }

        if (isset($this->_arr["guias"])) {
            $guias = $this->_domtree->createElement("ns2:guias");
            $guias->appendChild($this->_domtree->createElement("ns2:guiaManifiesto", $this->_arr["guias"]["guiaManifiesto"]));
            $guias->appendChild($this->_domtree->createElement("ns2:tipoGuia", $this->_arr["guias"]["tipoGuia"]));
            $pedimento->appendChild($guias);
        }

        if (isset($this->_arr["identificadores"])) {
            $iden = $this->_domtree->createElement("ns2:identificadores");
            foreach ($this->_arr["identificadores"] as $item) {
                $iden->appendChild($this->_identificador($item));
            }
            $pedimento->appendChild($iden);
        }

        if (isset($this->_arr["contenedores"])) {
            $contenedores = $this->_domtree->createElement("ns2:contenedores");
            $contenedores->appendChild($this->_domtree->createElement("ns2:identificador", $this->_arr["contenedores"]["numero"]));
            $contenedores->appendChild($this->_agregarClaveDescription("ns2", "tipoContenedor", $this->_arr["contenedores"]["tipoContenedor"], $this->_cat->tipoTransporte($this->_arr["contenedores"]["tipoContenedor"])));
            $pedimento->appendChild($contenedores);
        }

        if (isset($this->_arr["observaciones"])) {
            $texto = "";
            foreach ($this->_arr["observaciones"] as $item) {
                $texto .= $item["texto"] . " ";
            }
            $pedimento->appendChild($this->_domtree->createElement("ns2:observaciones", $texto));
        }
        if (isset($this->_arr["partidas"])) {
            $partidas = $this->_domtree->createElement("ns2:partidas");
            foreach ($this->_arr["partidas"] as $item) {
                $partidas->appendChild($this->_partida($item));
            }
            $pedimento->appendChild($partidas);
        }
        if (isset($this->_arr["firma"])) {
            $pedimento->appendChild($this->_domtree->createElement("ns2:tipoFigura", $this->_arr["firma"][0]["tipoFigura"]));
            $pedimento->appendChild($this->_domtree->createElement("ns2:firma", $this->_arr["firma"][0]["firma"]));
            $pedimento->appendChild($this->_domtree->createElement("ns2:numeroSerie", $this->_arr["firma"][0]["numeroSerie"]));
        }
        if (isset($this->_arr["archivo"])) {
            $pedimento->appendChild($this->_domtree->createElement("ns2:archivoValidacion", $this->_arr["archivo"]));
        }
        $this->_consulta->appendChild($pedimento);
    }

    protected function _partida($item) {
        $element = $this->_domtree->createElement("ns9:partida");
        $element->appendChild($this->_domtree->createElement("ns8:numeroPartida", $item["numPartida"]));
        $element->appendChild($this->_domtree->createElement("ns8:fraccionArancelaria", $item["fraccion"]));
        $element->appendChild($this->_domtree->createElement("ns8:descripcionMercancia", utf8_encode($item["descripcion"])));
        $element->appendChild($this->_domtree->createElement("ns8:cantidadUnidadMedidaTarifa", $item["cantidadUmt"]));
        $element->appendChild($this->_agregarClaveDescription("ns8", "unidadMedidaTarifa", $item["umt"], $this->_cat->unidades($item["umt"])));
        $element->appendChild($this->_domtree->createElement("ns8:cantidadUnidadMedidaComercial", $item["cantidadUmc"]));
        $element->appendChild($this->_agregarClaveDescription("ns8", "unidadMedidaComercial", $item["umc"], $this->_cat->unidades($item["umc"])));
        $element->appendChild($this->_domtree->createElement("ns8:precioUnitario", $item["precioUnitario"]));
        $element->appendChild($this->_domtree->createElement("ns8:valorComercial", $item["valorComercial"]));
        $element->appendChild($this->_domtree->createElement("ns8:valorAduana", $item["valorAduana"]));
        $element->appendChild($this->_domtree->createElement("ns8:valorDolares", $item["valorDolares"]));
        $element->appendChild($this->_domtree->createElement("ns8:valorAgregado", ($item["valorAgregado"] !== null) ? $item["valorAgregado"] : "0"));
        $element->appendChild($this->_domtree->createElement("ns8:metodoValoracion", $this->_cat->metodoValoracion($item["metodoValoracion"])));
        $element->appendChild($this->_domtree->createElement("ns8:vinculacion", $this->_cat->vinculacion($item["vinculacion"])));
        $element->appendChild($this->_agregarClaveDescription("ns8", "paisOrigenDestino", $item["paisOrigen"], $this->_cat->pais($item["paisOrigen"])));
        $element->appendChild($this->_agregarClaveDescription("ns8", "paisVendedorComprador", $item["paisVendedor"], $this->_cat->pais($item["paisVendedor"])));
        if(isset($this->_arr["idenPartidas"][(int)$item["numPartida"]])) {
            foreach ($this->_arr["idenPartidas"][(int)$item["numPartida"]] as $iden) {
                $element->appendChild($this->_identificadorPartida($iden));
            }
        }
        if(isset($this->_arr["tasasPartidas"][(int)$item["numPartida"]])) {
            foreach ($this->_arr["tasasPartidas"][(int)$item["numPartida"]] as $iden) {
                $element->appendChild($this->_gravamenesPartida($iden));
            }
        }
        if(isset($this->_arr["obsPartidas"][(int)$item["numPartida"]][0])) {
            $element->appendChild($this->_domtree->createElement("ns8:observaciones", $this->_arr["obsPartidas"][(int)$item["numPartida"]][0]["observacion"]));
        }
        return $element;
    }

    protected function _gravamenesPartida($item) {
        $element = $this->_domtree->createElement("ns8:gravamenes");
        $contri = $this->_cat->contribucion($item["clave"]);
        $element->appendChild($this->_agregarClaveDescription("ns8", "claveGravamen", $item["clave"], $contri["abreviacion"]));
        $element->appendChild($this->_tasasPartida($item));
        if(isset($this->_arr["contribucionesPartidas"][(int)$item["numPartida"]][0])) {
            $element->appendChild($this->_importesPartida($this->_arr["contribucionesPartidas"][(int)$item["numPartida"]][0]));
        }
        return $element; 
    }
    
    protected function _tasasPartida($item) {
        $element = $this->_domtree->createElement("ns8:tasas");
        $element->appendChild($this->_agregarClaveDescription("ns8", "clave", $item["tipoTasa"], utf8_decode($this->_cat->tipoTasa($item["tipoTasa"]))));
        $element->appendChild($this->_domtree->createElement("ns8:tasaAplicable", $item["tasaGravamen"]));
        return $element;
    }
    
    protected function _importesPartida($item) {
        $element = $this->_domtree->createElement("ns8:importes");
        $element->appendChild($this->_agregarClaveDescription("ns8", "clave", $item["formaPago"], utf8_decode($this->_cat->formaPago($item["formaPago"]))));
        $element->appendChild($this->_domtree->createElement("ns8:importe", $item["importe"]));
        return $element;
    }
    
    protected function _identificadorPartida($item) {
        $element = $this->_domtree->createElement("ns8:identificadores");
        $clave = $this->_domtree->createElement("ns8:claveIdentificador");
        $clave->appendChild($this->_domtree->createElement("ns8:clave", $item["clave"]));
        $clave->appendChild($this->_domtree->createElement("ns8:descripcion", utf8_encode($this->_cat->tipoIdentificador($item["clave"]))));
        $element->appendChild($clave);
        if (isset($item["complemento1"])) {
            $element->appendChild($this->_domtree->createElement("ns8:complemento1", $item["complemento1"]));
        }
        if (isset($item["complemento2"])) {
            $element->appendChild($this->_domtree->createElement("ns8:complemento2", $item["complemento2"]));
        }
        if (isset($item["complemento3"])) {
            $element->appendChild($this->_domtree->createElement("ns8:complemento3", $item["complemento3"]));
        }
        return $element;
    }
    
    protected function _identificador($item) {
        $element = $this->_domtree->createElement("ns2:identificadores");
        $clave = $this->_domtree->createElement("claveIdentificador");
        $clave->appendChild($this->_domtree->createElement("clave", $item["clave"]));
        $clave->appendChild($this->_domtree->createElement("descripcion", utf8_encode($this->_cat->tipoIdentificador($item["clave"]))));
        $element->appendChild($clave);
        if (isset($item["complemento1"])) {
            $element->appendChild($this->_domtree->createElement("complemento1", $item["complemento1"]));
        }
        if (isset($item["complemento2"])) {
            $element->appendChild($this->_domtree->createElement("complemento2", $item["complemento2"]));
        }
        if (isset($item["complemento3"])) {
            $element->appendChild($this->_domtree->createElement("complemento3", $item["complemento3"]));
        }
        return $element;
    }

    protected function _domicilio($value) {
        $element = $this->_domtree->createElement("ns2:domicilio");
        $element->appendChild($this->_domtree->createElement("ns2:calle", utf8_encode($value["calle"])));
        $element->appendChild($this->_domtree->createElement("ns2:numeroExterior", $value["numExterior"]));
        if (isset($value["numInteior"])) {
            $element->appendChild($this->_domtree->createElement("ns2:numeroInterior", $value["numInteior"]));
        }
        $element->appendChild($this->_domtree->createElement("ns2:codigoPostal", $value["codigoPostal"]));
        return $element;
    }

    protected function _agregarClaveDescription($namespane, $attr, $clave, $decripcion) {
        $element = $this->_domtree->createElement($namespane . ":" . $attr);
        $element->appendChild($this->_domtree->createElement($namespane . ":clave", $clave));
        $element->appendChild($this->_domtree->createElement($namespane . ":descripcion", utf8_encode($decripcion)));
        return $element;
    }

    protected function _fechas($namespane, $fecha, $clave, $decripcion) {
        $element = $this->_domtree->createElement($namespane . ":fechas");
        $element->appendChild($this->_domtree->createElement($namespane . ":fecha", $fecha));
        $tipo = $this->_domtree->createElement($namespane . ":tipo");
        $tipo->appendChild($this->_domtree->createElement($namespane . ":clave", $clave));
        $tipo->appendChild($this->_domtree->createElement($namespane . ":descripcion", utf8_encode($this->_cat->tipoFecha($clave))));
        $element->appendChild($tipo);
        return $element;
    }

    protected function _tasas($contri, $tipoTasa, $formaPago, $valor, $tasaAplicable) {
        $element = $this->_domtree->createElement("ns2:tasas");
        $element->appendChild($this->_agregarClaveDescription("ns2", "contribucion", $contri, $this->_cat->tipoContri($contri)));
        $element->appendChild($this->_agregarClaveDescription("ns2", "tipoTasa", $tipoTasa, $this->_cat->tipoTasa($tipoTasa)));
        $element->appendChild($this->_domtree->createElement("ns2:tasaAplicable", $tasaAplicable));
        $element->appendChild($this->_agregarClaveDescription("ns2", "formaPago", $formaPago, $this->_cat->formaPago($formaPago)));
        $element->appendChild($this->_domtree->createElement("ns2:importe", $valor));
        return $element;
    }

    /**
     * 
     * Regresa el XML
     * 
     * @return string
     * @throws Exception
     */
    public function getXml() {
        try {
            return (string) $this->_domtree->saveXML();
        } catch (Exception $e) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    protected function _convertirFecha($value) {
        return date("c", strtotime(substr($value, 4, 4) . "-" . substr($value, 2, 2) . "-" . substr($value, 0, 2)));
    }

}
