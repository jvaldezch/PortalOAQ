<?php

/**
 * Description of Facturas
 *
 * @author Jaime
 */
class SAT_Facturas {

    protected $_config;

    function __construct() {
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
    }

    public function satToArray($xml) {
        $clean = str_replace(array('ns2:', 'xsi:', 'fx:', 'sat:', 'cfd:', 'cfdi:', 'tfd:', 'xmlns:', 'ns3:', 'ns6:', 'ns9:', 'ns8:', 'S:', 'wsu:', 'wsse:', 'ns3:', 'wsu:', 'soapenv:', 'soap:', 'oxml:', 'egas:', '<![CDATA[', ']]>','s:','a:'), '', $xml);
        $xmlClean = simplexml_load_string($clean);
        unset($clean);
        return @json_decode(@json_encode($xmlClean), 1);
    }
    
    public function respuestaValidacion($xml) {
        $array = $this->satToArray($xml);
        if(isset($array["Body"]["ConsultaResponse"]["ConsultaResult"])) {
            $data = $array["Body"]["ConsultaResponse"]["ConsultaResult"];
            if(isset($data["CodigoEstatus"])) {
                if(preg_match('/Vigente/i', $data["Estado"])) {
                    return 1;
                }
                if(preg_match('/Cancelado/i', $data["Estado"])) {
                    return 2;
                }
                if(preg_match('/No Encontrado/i', $data["Estado"])) {
                    return 3;
                }
            } else {
                return 0;
            }
        } elseif (isset($array["Body"]["Fault"])) {
            if(isset($array["Body"]["Fault"]['faultcode'])) {
                return null;
            }
        } else {
            return 0;
        }
    }

    public function comprobarCDFXml($rfc, $serie, $folio, $numAprob, $yearAprob, $cert, $fecha, $id = 1) {
        $xml = "<cdf:ColleccionFoliosCfd xsi:schemaLocation=\"http://www.sat.gob.mx/Asf/Sicofi/ValidacionFoliosCDF/1.0.0 FoliosCDFNuevo.xsd\" xmlns:cdf=\"http://www.sat.gob.mx/Asf/Sicofi/ValidacionFoliosCFD/1.0.0\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">
            <cdf:Folio>
              <cdf:Id>{$id}</cdf:Id>
              <cdf:Rfc>{$rfc}</cdf:Rfc>
              <cdf:Serie>{$serie}</cdf:Serie>
              <cdf:NumeroFolio>{$folio}</cdf:NumeroFolio>
              <cdf:NumeroAprobacion>{$numAprob}</cdf:NumeroAprobacion>
              <cdf:AnioAprobacion>{$yearAprob}</cdf:AnioAprobacion>
              <cdf:CertificadoNumeroSerie>{$cert}</cdf:CertificadoNumeroSerie>
              <cdf:CertificadoFechaEmision>{$fecha}</cdf:CertificadoFechaEmision>
            </cdf:Folio>
          </cdf:ColleccionFoliosCfd>";
        return $xml;
    }

    public function solicitudValidarCDF($ColleccionFolios) {
        $xml = "<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:sat=\"http://www.sat.gob.mx/\">
            <soapenv:Header/>
            <soapenv:Body>
               <sat:ValidarXmlCFD>
                  <sat:xml>{$ColleccionFolios}</sat:xml>
               </sat:ValidarXmlCFD>
            </soapenv:Body>
         </soapenv:Envelope>";
        return $xml;
    }

    public function solicitudValidarCDFI($emisor, $receptor, $total, $uuid) {
        $xml = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/">'
                . '<soapenv:Header/>'
                . '<soapenv:Body>'
                . '<tem:Consulta>'
                . "<tem:expresionImpresa><![CDATA[?re={$emisor}&rr={$receptor}&tt={$total}&id={$uuid}]]></tem:expresionImpresa>"
                . '</tem:Consulta>'
                . '</soapenv:Body>'
                . '</soapenv:Envelope>';
        return $xml;
    }
    
    public function solicitudValidarCDFILinux($emisor, $receptor, $total, $uuid) {
        $xml = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/">'
                . '<soapenv:Header/>'
                . '<soapenv:Body>'
                . '<tem:Consulta>'
                . "<tem:expresionImpresa>?re={$emisor}&amp;rr={$receptor}&amp;tt={$total}&amp;id={$uuid}</tem:expresionImpresa>"
                . '</tem:Consulta>'
                . '</soapenv:Body>'
                . '</soapenv:Envelope>';
        return $xml;
    }

    public function satValidarCDF($xml) {
        try {
            $headers = array(
                "Content-type: text/xml; charset=UTF-8",
                "Accept: text/xml",
                "Cache-Control: no-cache",
                "Pragma: no-cache",
                "Content-length: " . strlen($xml) . "");
            $url = "https://tramitesdigitales.sat.gob.mx/Sicofi.wsExtValidacionCFD/WsValidacionCFDsExt.asmx";
            $soap = curl_init();
            curl_setopt($soap, CURLOPT_URL, $url);
            curl_setopt($soap, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($soap, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($soap, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($soap, CURLOPT_POST, true);
            curl_setopt($soap, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($soap, CURLOPT_POSTFIELDS, $xml);
            $result = curl_exec($soap);
            curl_close($soap);
            return $result;
        } catch (Exception $e) {
            echo "<b>Exception on " . __METHOD__ . "</b>" . $e->getMessage();
            die();
        }
    }

    public function satValidarCDFi($xml) {
        try {
            $headers = array(
                "Content-type: text/xml; charset=UTF-8",
                "Accept: text/xml",
                "Cache-Control: no-cache",
                'SOAPAction: "http://tempuri.org/IConsultaCFDIService/Consulta"',
                "Pragma: no-cache",
                "Content-length: " . strlen($xml) . "");
            $url = "https://consultaqr.facturaelectronica.sat.gob.mx/ConsultaCFDIService.svc";
            $soap = curl_init();
            curl_setopt($soap, CURLOPT_URL, $url);
            curl_setopt($soap, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($soap, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($soap, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($soap, CURLOPT_POST, true);
            curl_setopt($soap, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($soap, CURLOPT_POSTFIELDS, $xml);
            $result = curl_exec($soap);
            curl_close($soap);
            return $result;
        } catch (Exception $e) {
            echo "<b>Exception on " . __METHOD__ . "</b>" . $e->getMessage();
            die();
        }
    }

    public function obtenerDatosFactura($rfcEmisor, $atributos) {
        $notValid = array('cfdi', 'xsi', 'schemaLocation');
        foreach ($notValid as $item) {
            if (isset($atributos[$item])) {
                unset($atributos[$item]);
            }
        }
        $valid = array(
            'fecha' => 'fecha',
            'formadepago' => 'formaDePago',
            'nocertificado' => 'noCertificado',
            'subtotal' => 'subTotal',
            'tipocambio' => 'tipoCambio',
            'moneda' => 'moneda',
            'total' => 'total',
            'metododepago' => 'metodoDePago',
            'tipodecomprobante' => 'tipoDeComprobante',
            'lugarexpedicion' => 'lugarExpedicion',
            'numctapago' => 'numCtaPago',
            'serie' => 'serie',
            'folio' => 'folio',
            'version' => 'version',
            'certificado' => 'certificado',
            'sello' => 'sello',
            'version' => 'version',
        );
        $data["rfcEmisor"] = $rfcEmisor;
        foreach ($valid as $k => $v) {
            if (isset($atributos[$k]) && $k != 'fecha') {
                $data[$v] = strtoupper(utf8_decode($atributos[$k]));
            } else if (isset($atributos[$k]) && $k == 'fecha') {
                $data[$v] = date('Y-m-d H:i:s', strtotime($atributos[$k]));
            }
        }
        return $data;
    }

    public function obtenerGenerales($datos) {
        $values = $this->array_change_key_case_ext($datos);
        unset($datos);

        $data["rfc"] = $values["@attributes"]["rfc"];
        $data["razonSocial"] = $values["@attributes"]["nombre"];
        $valid = array(
            'calle' => 'calle',
            'noexterior' => 'numExt',
            'nointerior' => 'numInt',
            'localidad' => 'localidad',
            'colonia' => 'colonia',
            'ciudad' => 'ciudad',
            'municipio' => 'municipio',
            'estado' => 'estado',
            'pais' => 'pais',
            'codigopostal' => 'codigoPostal',
        );
        $dom = "domicilio";
        if (isset($values["domiciliofiscal"])) {
            $dom = "domiciliofiscal";
        } elseif (isset($values["domicilio"])) {
            $dom = "domicilio";
        } else {
            $dom = 0;
        }
        foreach ($valid as $k => $v) {
            if (isset($values[$dom]["@attributes"][$k])) {
                $data["domicilio"][$v] = isset($values[$dom]["@attributes"][$k]) ? $values[$dom]["@attributes"][$k] : null;
            }
        }
        $data["regimen"] = isset($values["regimenfiscal"]["@attributes"]["regimen"]) ? strtoupper($values["regimenfiscal"]["@attributes"]["regimen"]) : null;
        return $data;
    }

    public function obtenerImpuestos($datos) {
        $values = $this->array_change_key_case_ext($datos);
        unset($datos);

        if (isset($values["@attributes"]["totalimpuestostrasladados"])) {
            $data["impuestos"] = (float) $values["@attributes"]["totalimpuestostrasladados"];
        }
        if (isset($values["@attributes"]["totalimpuestosretenidos"])) {
            $data["retenciones"] = (float) $values["@attributes"]["totalimpuestosretenidos"];
        }
        if (isset($values["retenciones"]["retencion"])) {
            $retencion = $values["retenciones"]["retencion"];
            foreach ($retencion as $item) {
                if (isset($item["@attributes"]["impuesto"])) {
                    if ($item["@attributes"]["impuesto"] == "IVA") {
                        $data["retIVA"] = (float) $item["@attributes"]["importe"];
                    }
                    if ($item["@attributes"]["impuesto"] == "ISR") {
                        $data["retISR"] = (float) $item["@attributes"]["importe"];
                    }
                }
            }
        }
        if (isset($values["traslados"]["traslado"])) {
            $traslado = $values["traslados"]["traslado"];
            if (isset($traslado["@attributes"])) {
                $data["IVA"] = (float) $traslado["@attributes"]["importe"];
                $data["tasaIVA"] = $traslado["@attributes"]["tasa"];
            } else {
                foreach ($traslado as $item) {
                    if ($item["@attributes"]["impuesto"] == "IVA") {
                        $data["IVA"] = (float) $item["@attributes"]["importe"];
                        $data["tasaIVA"] = $item["@attributes"]["tasa"];
                    }
                    if ($item["@attributes"]["impuesto"] == "IEPS") {
                        $data["IEPS"] = (float) $item["@attributes"]["importe"];
                        $data["tasaIEPS"] = (float) $item["@attributes"]["tasa"];
                    }
                }
            }
        } elseif (isset($values["traslados"][0])) {
            $traslado = $values["traslados"];
            foreach ($traslado as $item) {
                if ($item["@attributes"]["impuesto"] == "IVA") {
                    $data["IVA"] = (float) $item["@attributes"]["importe"];
                    $data["tasaIVA"] = $item["@attributes"]["tasa"];
                }
                if ($item["@attributes"]["impuesto"] == "IEPS") {
                    $data["IEPS"] = (float) $item["@attributes"]["importe"];
                    $data["tasaIEPS"] = (float) $item["@attributes"]["tasa"];
                }
            }
        }
        return $data;
    }

    public function obtenerComplemento($datos) {
        $values = $this->array_change_key_case_ext($datos);
        unset($datos);
        if (!isset($values["@attributes"]) && isset($values[0]["@attributes"])) {
            $values = $values[0]["@attributes"];
        } elseif (isset($values["timbrefiscaldigital"])) {
            $values = $values["timbrefiscaldigital"]["@attributes"];
        }
        $data["uuid"] = strtoupper($values["uuid"]);
        $data["fechaTimbrado"] = date('Y-m-d H:i:s', strtotime($values["fechatimbrado"]));
        $data["noCertificadoSAT"] = $values["nocertificadosat"];
        $data["selloSAT"] = $values["sellosat"];
        $data["selloCFD"] = $values["sellocfd"];
        return $data;
    }

    public function obtenerConceptos($datos) {
        $values = $this->array_change_key_case_ext($datos);
        $total = 0;
        if (isset($values["@attributes"])) {
            $data[] = array(
                'cantidad' => (float) $values["@attributes"]["cantidad"],
                'unidad' => $values["@attributes"]["unidad"],
                'descripcion' => utf8_decode($values["@attributes"]["descripcion"]),
                'valorUnitario' => (float) $values["@attributes"]["valorunitario"],
                'importe' => (float) $values["@attributes"]["importe"],
            );
            $total = (float) $values["@attributes"]["importe"];
        } elseif (isset($values[0]["@attributes"])) {
            foreach ($values as $item) {
                $data[] = array(
                    'cantidad' => (float) $item["@attributes"]["cantidad"],
                    'unidad' => $item["@attributes"]["unidad"],
                    'descripcion' => utf8_decode($item["@attributes"]["descripcion"]),
                    'valorUnitario' => (float) $item["@attributes"]["valorunitario"],
                    'importe' => (float) $item["@attributes"]["importe"],
                );
                $total += (float) $item["@attributes"]["importe"];
            }
        } elseif (isset($values["concepto"][0])) {
            foreach ($values["concepto"] as $item) {
                $data[] = array(
                    'cantidad' => (float) $item["@attributes"]["cantidad"],
                    'unidad' => $item["@attributes"]["unidad"],
                    'descripcion' => utf8_decode($item["@attributes"]["descripcion"]),
                    'valorUnitario' => (float) $item["@attributes"]["valorunitario"],
                    'importe' => (float) $item["@attributes"]["importe"],
                );
                $total += (float) $item["@attributes"]["importe"];
            }
        } elseif (isset($values["concepto"]["@attributes"])) {
            $data[] = array(
                'cantidad' => (float) $values["concepto"]["@attributes"]["cantidad"],
                'unidad' => $values["concepto"]["@attributes"]["unidad"],
                'descripcion' => utf8_decode($values["concepto"]["@attributes"]["descripcion"]),
                'valorUnitario' => (float) $values["concepto"]["@attributes"]["valorunitario"],
                'importe' => (float) $values["concepto"]["@attributes"]["importe"],
            );
            $total = (float) $values["concepto"]["@attributes"]["importe"];
        }
        $data["total"] = $total;
        return $data;
    }
    
    public function nuevaFactura($array, $contenido,$object,$username) {
        $facturas = new Model_Doctrine_Facturas();
        $concepts = new Model_Doctrine_Conceptos();

        $clientes = Doctrine_Core::getTable('Model_Doctrine_Clientes');
        $proveedores = Doctrine_Core::getTable('Model_Doctrine_Proveedores');
        $cliFacturas = Doctrine_Core::getTable('Model_Doctrine_ClientesFacturas');

        $attr = $this->obtenerDatosFactura($array["Emisor"]["@attributes"]["rfc"], array_change_key_case($array["@attributes"], CASE_LOWER));
        $emisor = $this->obtenerGenerales($array["Emisor"]);
        $receptor = $this->obtenerGenerales($array["Receptor"]);
        $impuestos = $this->obtenerImpuestos($array["Impuestos"]);
        if (isset($array["Complemento"])) {
            $complemento = $this->obtenerComplemento($array["Complemento"]);
        }
        $conceptos = $this->obtenerConceptos($array["Conceptos"]);

        if ($attr["version"] == '3.2' || $attr["version"] == '2.2') {

            $ingresos = $clientes->findByRfc($emisor["rfc"])->toArray();
            $gastos = $clientes->findByRfc($receptor["rfc"])->toArray();

            if ($ingresos) {
                $tipo = array(
                    'tipo' => 1,
                    'idCliente' => $ingresos[0]["id"],
                );
                $cf = $cliFacturas->findByRfcAndIdCliente($receptor["rfc"], $ingresos[0]["id"]);
                $fprov = $cf->toArray();
                if (empty($fprov)) {
                    $cliProv = new Model_Doctrine_ClientesFacturas();
                    $cliProv->fromArray(array(
                        'rfc' => $receptor["rfc"],
                        'idCliente' => $ingresos[0]["id"],
                        'razonSocial' => $receptor["razonSocial"],
                        'calle' => isset($receptor["domicilio"]["calle"]) ? $receptor["domicilio"]["calle"] : null,
                        'numExt' => isset($receptor["domicilio"]["numExt"]) ? $receptor["domicilio"]["numExt"] : null,
                        'numInt' => isset($receptor["domicilio"]["numInt"]) ? $receptor["domicilio"]["numInt"] : null,
                        'localidad' => isset($receptor["domicilio"]["localidad"]) ? $receptor["domicilio"]["localidad"] : null,
                        'colonia' => isset($receptor["domicilio"]["colonia"]) ? $receptor["domicilio"]["colonia"] : null,
                        'ciudad' => isset($receptor["domicilio"]["ciudad"]) ? $receptor["domicilio"]["ciudad"] : null,
                        'municipio' => isset($receptor["domicilio"]["municipio"]) ? $receptor["domicilio"]["municipio"] : null,
                        'estado' => isset($receptor["domicilio"]["estado"]) ? $receptor["domicilio"]["estado"] : null,
                        'pais' => isset($receptor["domicilio"]["pais"]) ? $receptor["domicilio"]["pais"] : null,
                        'codigoPostal' => isset($receptor["domicilio"]["codigoPostal"]) ? $receptor["domicilio"]["codigoPostal"] : null,
                        'creado' => date('Y-m-d H:i:s'),
                        'creadoPor' => $username,
                    ));
                    $cliProv->save();
                    $idCliProv = $cliProv["id"];
                    unset($cliProv);
                } else {
                    $idCliProv = $fprov[0]["id"];
                }
            } else if ($gastos) {
                $tipo = array(
                    'tipo' => 2,
                    'idCliente' => $gastos[0]["id"],
                );
                $provee = $proveedores->findByRfcAndIdCliente($emisor["rfc"], $gastos[0]["id"]);
                $fprov = $provee->toArray();
                if (empty($fprov)) {
                    $provider = new Model_Doctrine_Proveedores();
                    $provider->fromArray(array(
                        'rfc' => $emisor["rfc"],
                        'idCliente' => $gastos[0]["id"],                        
                        'razonSocial' => $emisor["razonSocial"],
                        'calle' => isset($emisor["domicilio"]["calle"]) ? $emisor["domicilio"]["calle"] : null,
                        'numExt' => isset($emisor["domicilio"]["numExt"]) ? $emisor["domicilio"]["numExt"] : null,
                        'numInt' => isset($emisor["domicilio"]["numInt"]) ? $emisor["domicilio"]["numInt"] : null,
                        'localidad' => isset($emisor["domicilio"]["localidad"]) ? $emisor["domicilio"]["localidad"] : null,
                        'colonia' => isset($emisor["domicilio"]["colonia"]) ? $emisor["domicilio"]["colonia"] : null,
                        'ciudad' => isset($emisor["domicilio"]["ciudad"]) ? $emisor["domicilio"]["ciudad"] : null,
                        'municipio' => isset($emisor["domicilio"]["municipio"]) ? $emisor["domicilio"]["municipio"] : null,
                        'estado' => isset($emisor["domicilio"]["estado"]) ? $emisor["domicilio"]["estado"] : null,
                        'pais' => isset($emisor["domicilio"]["pais"]) ? $emisor["domicilio"]["pais"] : null,
                        'codigoPostal' => isset($emisor["domicilio"]["codigoPostal"]) ? $emisor["domicilio"]["codigoPostal"] : null,
                        'creado' => date('Y-m-d H:i:s'),
                        'creadoPor' => $username,
                    ));
                    $provider->save();                    
                    $idProveedor = $provider["id"];
                    unset($provider);
                } else {
                    $idProveedor = $fprov[0]["id"];
                }
            } else {
                return 0;
            }
            
            $data["idCliente"] = $tipo["idCliente"];
            $data["rfcEmisor"] = $emisor["rfc"];
            $data["rfcReceptor"] = $receptor["rfc"];
            $data["folio"] = $attr["folio"];
            $data["certificado"] = base64_encode($attr["certificado"]);
            $data["sello"] = base64_encode($attr["sello"]);
            $data["serie"] = isset($attr["serie"]) ? $attr["serie"] : null;
            $data["formaDePago"] = $attr["formaDePago"];
            $data["noCertificado"] = $attr["noCertificado"];
            $data["uuid"] = $complemento["uuid"];
            $data["version"] = $attr["version"];
            $data["subtotal"] = $conceptos["total"];
            $data["impuestos"] = isset($impuestos["impuestos"]) ? $impuestos["impuestos"] : 0;
            $data["retenciones"] = isset($impuestos["retenciones"]) ? $impuestos["retenciones"] : 0;
            $data["total"] = $data["subtotal"] + $data["impuestos"] - $data["retenciones"];
            $data["tipoFactura"] = $tipo["tipo"];
            $data["fechaFactura"] = $attr["fecha"];
            $data["fechaTimbrado"] = $complemento["fechaTimbrado"];
            $data["creado"] = date('Y-m-d H:i:s');
            $data["creadoPor"] = $username;

            $query = Doctrine_Query::create()
                    ->select("id")
                    ->from("Model_Doctrine_Facturas f")
                    ->where("f.rfcEmisor = ? ", $data["rfcEmisor"])
                    ->where("f.rfcReceptor = ? ", $data["rfcReceptor"])
                    ->where("f.folio = ? ", $data["folio"])
                    ->where("f.serie = ? ", $data["serie"])
                    ->where("f.noCertificado = ? ", $data["noCertificado"])
                    ->where("f.uuid = ? ", $data["uuid"]);
            $found = $query->execute()->toArray();

            if (empty($found)) {
                $facturas->fromArray($data);
                $facturas->save();
                $id = $facturas["id"];

                $arch["idFactura"] = $id;
                $arch["nombre"] = $object->getFilename();
                $arch["tipo"] = "xml";
                $arch["contenido"] = base64_encode($contenido);
                $arch["creado"] = $data["creado"];
                $arch["creadoPor"] = $data["creadoPor"];

                $repositorio = new Model_Doctrine_Repositorio();
                $repositorio->fromArray($arch);
                $repositorio->save();
                unset($repositorio);

                $smallPdfFile = substr($object->getPathname(), 0, -3) . 'pdf';
                $capPdfFile = substr($object->getPathname(), 0, -3) . 'PDF';
                if((file_exists($smallPdfFile) && is_readable($smallPdfFile))) {
                    $pdfName = $smallPdfFile;
                } else if((file_exists($capPdfFile) && is_readable($capPdfFile))) {
                    $pdfName = $capPdfFile;
                }                
                if (isset($pdfName)) {
                    
                    $repositorio = new Model_Doctrine_Repositorio();
                    $arch["idFactura"] = $id;
                    $arch["nombre"] = basename($pdfName);
                    $arch["tipo"] = "pdf";
                    $arch["contenido"] = base64_encode(file_get_contents($pdfName));
                    $arch["creado"] = $data["creado"];
                    $arch["creadoPor"] = $data["creadoPor"];
                    $repositorio->fromArray($arch);
                    $repositorio->save();
                    unset($repositorio);
                    //unlink($pdfName);
                }
                foreach ($conceptos as $con) {
                    if (is_array($con)) {
                        $con["idCliente"] = $tipo["idCliente"];
                        $con["idProv"] = ($tipo["tipo"] == 1) ? $idCliProv : $idProveedor;
                        $con["idFactura"] = $id;
                        $con["tipoConcepto"] = $tipo["tipo"];
                        $concepts->fromArray($con);
                        $concepts->save();
                    }
                }
                unset($facturas);
                unset($conceptos);
                //unlink($object->getPathname());
                return 1;
            } else {
                unset($facturas);
                unset($conceptos);
                unset($repositorio);
                return 0;
            }
        }        
    }

    public function renderFactura($attr, $emi, $rec, $con, $imp, $com) {
        $html = '<style>
            table.invoice {
                width: 100%;
                border-collapse: collapse;
                font-size: 11px;
                margin: 5px 0;
            }
            table.invoice td,
            table.invoice th {
                background: none;
                border: 1px #bbb solid;
                font-size: 11px;
                text-align: justify;
            }
            table.invoice th {
                background: none;
                background-color: #e2e2e2;
            }
        </style>';

        $html .= '<table class="invoice">'
                . '<tr>'
                . '<th>Folio</th>'
                . '<td>' . $attr["folio"] . '</td>'
                . '<th>Serie</th>'
                . '<td>' . (isset($attr["serie"]) ? $attr["serie"] : '&nbsp;') . '</td>'
                . '<th>Fecha</th>'
                . '<td>' . $attr["fecha"] . '</td>'
                . '<th>No. Certificado</th>'
                . '<td>' . $attr["noCertificado"] . '</td>'
                . '<th>Forma de Pago</th>'
                . '<td>' . utf8_encode($attr["formaDePago"]) . '</td>'
                . '</tr>'
                . '<tr>'
                . '<th>Metodo de Pago</th>'
                . '<td>' . $attr["metodoDePago"] . '</td>'
                . '<th>Tipo de Comprobante</th>'
                . '<td>' . $attr["tipoDeComprobante"] . '</td>'
                . '<th>Lugar de Expedición</th>'
                . '<td colspan="3">' . utf8_encode($attr["lugarExpedicion"]) . '</td>'
                . '<th>CDFi</th>'
                . '<td>' . $attr["version"] . '</td>'
                . '</tr>'
                . '</table>';

        $html .= '<table class="invoice">'
                . '<tr>'
                . '<th colspan="2">EMISOR</th>'
                . '<th colspan="2">RECEPTOR</th>'
                . '</tr>'
                . '<tr>'
                . '<th>RFC</th>'
                . '<td>' . $emi["rfc"] . '</td>'
                . '<th>RFC</th>'
                . '<td>' . $rec["rfc"] . '</td>'
                . '</tr>'
                . '<tr>'
                . '<th>Razón Social</th>'
                . '<td>' . $emi["razonSocial"] . '</td>'
                . '<th>Razón Social</th>'
                . '<td>' . $rec["razonSocial"] . '</td>'
                . '</tr>';

        $array = array(
            'calle' => 'Calle',
            'numExt' => 'Num. Exterior',
            'numInt' => 'Num. Interior',
            'colonia' => 'Colonia',
            'localidad' => 'Localidad',
            'ciudad' => 'Ciudad',
            'municipio' => 'Municipio',
            'estado' => 'Estado',
            'pais' => 'Pais',
            'codigoPostal' => 'C.P.',
        );

        foreach ($array as $k => $v) {
            $html .= '<tr>'
                    . '<th>' . $v . '</th>'
                    . '<td>' . (isset($emi["domicilio"][$k]) ? $emi["domicilio"][$k] : '&nbsp;') . '</td>'
                    . '<th>' . $v . '</th>'
                    . '<td>' . (isset($rec["domicilio"][$k]) ? $rec["domicilio"][$k] : '&nbsp;') . '</td>'
                    . '</tr>';
        }

        $html .= '<tr>'
                . '<th>Regimen</th>'
                . '<td>' . (isset($emi["regimen"]) ? $emi["regimen"] : '&nbsp;') . '</td>'
                . '<th>Regimen</th>'
                . '<td>' . (isset($rec["regimen"]) ? $rec["regimen"] : '&nbsp;') . '</td>'
                . '</tr>';

        $html .= '</table>';

        $html .= '<table class="invoice">'
                . '<tr>'
                . '<th colspan="5">CONCEPTOS</th>'
                . '</tr>'
                . '<tr>'
                . '<th>Cantidad</th>'
                . '<th>Unidad</th>'
                . '<th>Descripción</th>'
                . '<th>Valor Unitario</th>'
                . '<th>Importe</th>'
                . '</tr>';
        $subTotal = 0;
        foreach ($con as $concepto) {
            $html .= '<tr>'
                    . '<td>' . (isset($concepto["cantidad"]) ? $concepto["cantidad"] : '&nbsp;') . '</td>'
                    . '<td>' . (isset($concepto["unidad"]) ? $concepto["unidad"] : '&nbsp;') . '</td>'
                    . '<td>' . (isset($concepto["descripcion"]) ? utf8_encode($concepto["descripcion"]) : '&nbsp;') . '</td>'
                    . '<td>' . (isset($concepto["valorUnitario"]) ? $concepto["valorUnitario"] : '&nbsp;') . '</td>'
                    . '<td style="text-align: right">' . (isset($concepto["importe"]) ? $concepto["importe"] : '&nbsp;') . '</td>'
                    . '</tr>';
            $subTotal += $concepto["importe"];
        }

        /** ------------------------------------ TRASLADOS -------------------------------------------- * */
        $traslados = 0;
        if (isset($imp["IVA"])) {
            $html .= '<tr>'
                    . '<th colspan="3"></th>'
                    . '<td style="text-align: right">SubTotal</td>'
                    . '<td style="text-align: right">' . $subTotal . '</td>'
                    . '</tr>';
            $html .= '<tr>'
                    . '<th colspan="3"></th>'
                    . '<td style="text-align: right">IVA (' . $imp["tasaIVA"] . ' %)</td>'
                    . '<td style="text-align: right">' . $imp["IVA"] . '</td>'
                    . '</tr>';
            $traslados += $imp["IVA"];
        }
        if (isset($imp["IEPS"])) {
            $html .= '<tr>'
                    . '<th colspan="3"></th>'
                    . '<td style="text-align: right">IEPS (' . $imp["tasaIEPS"] . ' %)</td>'
                    . '<td style="text-align: right">' . $imp["IEPS"] . '</td>'
                    . '</tr>';
            $traslados += $imp["IEPS"];
        }

        /** ------------------------------------ RETENCIONES -------------------------------------------- * */
        $retenciones = 0;
        if (isset($imp["retIVA"])) {
            $html .= '<tr>'
                    . '<th colspan="3"></th>'
                    . '<td style="text-align: right">Ret. IVA</td>'
                    . '<td style="text-align: right">' . $imp["retIVA"] . '</td>'
                    . '</tr>';
            $retenciones += $imp["retIVA"];
        }
        if (isset($imp["retISR"])) {
            $html .= '<tr>'
                    . '<th colspan="3"></th>'
                    . '<td style="text-align: right">Ret. ISR</td>'
                    . '<td style="text-align: right">' . $imp["retISR"] . '</td>'
                    . '</tr>';
            $retenciones += $imp["retISR"];
        }

        $html .= '<tr>'
                . '<th colspan="3"></th>'
                . '<td style="text-align: right">Total</td>'
                . '<td style="text-align: right">' . ($subTotal + $traslados - $retenciones) . '</td>'
                . '</tr>';

        $html .= '</table>';

        if(isset($com)) {
            $html .= '<table class="invoice">'
                    . '<tr>'
                    . '<th>UUID</th>'
                    . '<td>' . (isset($com["uuid"]) ? strtoupper($com["uuid"]) : '&nbsp;') . '</td>'
                    . '<th>Fecha Timbrado</th>'
                    . '<td>' . (isset($com["fechaTimbrado"]) ? date('Y-m-d H:i:s', strtotime($com["fechaTimbrado"])) : '&nbsp;') . '</td>'
                    . '<th>No. Certificado SAT</th>'
                    . '<td>' . (isset($com["noCertificadoSAT"]) ? $com["noCertificadoSAT"] : '&nbsp;') . '</td>'
                    . '</tr>'
                    . '<tr>'
                    . '<th>Sello SAT</th>'
                    . '<td colspan="5">' . (isset($com["selloSAT"]) ? wordwrap($com["selloSAT"], 100, "<br />", true) : '&nbsp;') . '</td>'
                    . '</tr>'
                    . '<tr>'
                    . '<th>Sello CFD</th>'
                    . '<td colspan="5">' . (isset($com["selloCFD"]) ? wordwrap($com["selloCFD"], 100, "<br />", true) : '&nbsp;') . '</td>'
                    . '</tr>'
                    . '</table>';
        }

        return $html;
    }

    protected function array_change_key_case_ext(array $array, $case = 10, $useMB = false, $mbEnc = 'UTF-8') {
        $newArray = array();
        //for more speed define the runtime created functions in the global namespace
        //get function
        if ($useMB === false) {
            $function = 'strToUpper'; //default
            switch ($case) {
                //first-char-to-lowercase
                case 25:
                    //maybee lcfirst is not callable
                    if (!function_exists('lcfirst')) {
                        $function = create_function('$input', 'return strToLower($input[0]) . substr($input, 1, (strLen($input) - 1));');
                    } else {
                        $function = 'lcfirst';
                    }
                    break;
                //first-char-to-uppercase                
                case 20:
                    $function = 'ucfirst';
                    break;
                //lowercase
                case 10:
                    $function = 'strToLower';
            }
        } else {
            //create functions for multibyte support
            switch ($case) {
                //first-char-to-lowercase
                case 25:
                    $function = create_function('$input', '
                        return mb_strToLower(mb_substr($input, 0, 1, \'' . $mbEnc . '\')) . 
                            mb_substr($input, 1, (mb_strlen($input, \'' . $mbEnc . '\') - 1), \'' . $mbEnc . '\');
                    ');
                    break;
                //first-char-to-uppercase
                case 20:
                    $function = create_function('$input', '
                        return mb_strToUpper(mb_substr($input, 0, 1, \'' . $mbEnc . '\')) . 
                            mb_substr($input, 1, (mb_strlen($input, \'' . $mbEnc . '\') - 1), \'' . $mbEnc . '\');
                    ');
                    break;
                //uppercase
                case 15:
                    $function = create_function('$input', '
                        return mb_strToUpper($input, \'' . $mbEnc . '\');
                    ');
                    break;
                //lowercase
                default: //case 10:
                    $function = create_function('$input', '
                        return mb_strToLower($input, \'' . $mbEnc . '\');
                    ');
            }
        }
        //loop array
        foreach ($array as $key => $value) {
            if (is_array($value)) { //$value is an array, handle keys too
                $newArray[$function($key)] = $this->array_change_key_case_ext($value, $case, $useMB);
            } elseif (is_string($key)) {
                $newArray[$function($key)] = $value;
            } else {
                $newArray[$key] = $value; //$key is not a string
            }
        } //end loop
        return $newArray;
    }

}
