<?php

/**
 * Description of EmailNotifications
 *
 * @author Jaime E. Valdez <jvaldezch@gmail.com>
 */
class OAQ_VucemEnh {

    protected $_config;
    protected $_appconfig;
    protected $_dir;
    
    function set_dir($_dir) {
        $this->_dir = $_dir;
    }
    
    function __construct() {
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
        $this->_appconfig = new Application_Model_ConfigMapper();
    }

    protected function calculateHeader($len) {
        return array(
            "Content-type: text/xml; charset=UTF-8",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "Content-length: " . $len . "");
    }

    protected function add($value) {
        return $value . '|';
    }

    protected function valorReservado($valor) {
        $data = array(
            'SubDivision',
            'CertificadoOrigen',
            'ExportadorAutorizdo',
            'CteNumInt',
            'CteColonia',
            'ProNumInt',
            'ProColonia',
            'ProEdo',
        );
        if (in_array($valor, $data)) {
            return true;
        }
        return null;
    }

    protected function valorMoneda($valor) {
        $data = array(
            'PrecioUnitario',
            'Total',
        );
        if (in_array($valor, $data)) {
            return true;
        }
        return null;
    }

    protected function rfcsConsulta($rfcs) {
        $xmlRfcs = '';
        if (is_array($rfcs)) {
            foreach ($rfcs as $r) {
                $xmlRfcs .= "<oxml:rfcConsulta>" . $r . "</oxml:rfcConsulta>";
            }
        } else {
            $xmlRfcs .= "<oxml:rfcConsulta>" . $rfcs . "</oxml:rfcConsulta>";
        }
        return $xmlRfcs;
    }

    protected function emails($emails) {
        $xmlEmails = '';
        if (is_array($emails)) {
            foreach ($emails as $e) {
                $xmlEmails .= "<oxml:correoElectronico>" . $e . "</oxml:correoElectronico>";
            }
        } else {
            $xmlEmails .= "<oxml:correoElectronico>" . $emails . "</oxml:correoElectronico>";
        }
        return $xmlEmails;
    }

    public function crearComprobante($factura, $tipoFigura, $tipoOp, $rfcs, $emails, $cert, $pkeyid, $patente = null, $adenda = null, $sha = null) {
        try {
            $xml = '';
            $xmlRfcs = $this->rfcsConsulta($rfcs);
            $xmlEmails = $this->emails($emails);

            $emisor = $this->crearEmisor($factura, $tipoOp);
            $dest = $this->crearDestinatario($factura, $tipoOp);
            $merc = $this->crearProductos($factura["Productos"], $factura["ValDls"]);
            $tmpXml = "<oxml:solicitarRecibirCoveServicio>";
            $arrXml = $this->preXml($tipoOp, $patente, $factura["FechaFactura"], $xmlRfcs, $tipoFigura, $xmlEmails, $factura["NumFactura"], $factura["CertificadoOrigen"], $factura["NumExportador"], $factura["Subdivision"], $emisor, $dest, $merc, null, null, null, null, (isset($factura["Manual"])) ? $factura["Manual"] : null, $adenda, (isset($factura["Observaciones"])) ? $factura["Observaciones"] : null, isset($sha) ? $sha : null);
            $tmpXml .= $arrXml["xml"];
            $tmpXml .= "</oxml:solicitarRecibirCoveServicio>";
            $cadena = $this->cadenaOriginal($tmpXml);
            $xml = $this->preXml($tipoOp, $patente, $factura["FechaFactura"], $xmlRfcs, $tipoFigura, $xmlEmails, $factura["NumFactura"], $factura["CertificadoOrigen"], $factura["NumExportador"], $factura["Subdivision"], $emisor, $dest, $merc, true, $cert, $pkeyid, $cadena, (isset($factura["Manual"])) ? $factura["Manual"] : null, $adenda, (isset($factura["Observaciones"])) ? $factura["Observaciones"] : null, $factura["Reenvio"], isset($sha) ? $sha : null);
            unset($tmpXml);
            unset($arrXml);
            unset($emisor);
            unset($dest);
            unset($merc);             
            return $xml;
        } catch (Exception $e) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function crearComprobanteRelacion($relFact, $numRelacion, $facturas, $tipoFigura, $tipoOp, $rfcs, $emails, $cert, $pkeyid) {
        try {
            $xml = '';
            $xmlRfcs = $this->rfcsConsulta($rfcs);
            $xmlEmails = $this->emails($emails);
            if ($relFact == true) {
                $emisor = $this->crearEmisor($facturas[0]);
                $dest = $this->crearDestinatario($facturas[0]);
                $tmpXml = $this->preXmlRelacion($numRelacion, $facturas, $tipoOp, $xmlRfcs, $xmlEmails, $tipoFigura, $emisor, $dest);
                $cadena = $this->cadenaOriginal($tmpXml["xml"]);
                $xml = $this->preXmlRelacion($numRelacion, $facturas, $tipoOp, $xmlRfcs, $xmlEmails, $tipoFigura, $emisor, $dest, true, $cadena, $cert, $pkeyid);
            }
            return preg_replace('/&(?!#?[a-z0-9]+;)/', '&amp;', $xml);
        } catch (Exception $e) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    protected function crearFacturaRelacion($fact) {
        try {
            $numExportador = "";
            if ($fact["CertificadoOrigen"] == '1' && trim($fact["NumExportador"]) != '') {
                $numExportador = "\n\t\t\t<oxml:numeroExportadorAutorizado>{$fact["NumExportador"]}</oxml:numeroExportadorAutorizado>";
            }
            $factura = "\n\t\t\t<oxml:facturas>";
            $factura .= "\n\t\t\t   <oxml:certificadoOrigen>".$fact["CertificadoOrigen"]."</oxml:certificadoOrigen>{$numExportador}
               \t\t   <oxml:subdivision>{$fact["Subdivision"]}</oxml:subdivision>
               \t\t   <oxml:numeroFactura>" . $this->vuChar($fact["NumFactura"], true) . "</oxml:numeroFactura>\n\t\t\t   ";

            $factura .= $this->crearProductos($fact["Productos"]);

            $factura .= "\n\t\t\t</oxml:facturas>";
            return preg_replace('/&(?!#?[a-z0-9]+;)/', '&amp;', $factura);
        } catch (Exception $e) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    protected function fixCadena($cadena) {
        return strtr(utf8_decode($cadena), array('&amp;' => '&'));
    }

    protected function preXml($tipoOp, $patente, $fechaFact, $xmlRfcs, $tipoFigura, $xmlEmails, $numFact, $certOri, $numExp, $subDiv, $emisor, $dest, $merc, $firma = null, $cert = null, $pkeyid = null, $cadena = null, $manual = null, $adenda = null, $obs = null, $reenvio = null, $sha = null) {        
        error_reporting(E_ERROR | E_WARNING | E_PARSE);
        try {
            $xml = '';
            $firmaXml = '';
            $firmaElect = '';
            if ($firma) {
                $fixCadena = $this->fixCadena($cadena); 
                if(isset($sha) && $sha == "sha256") {
                    openssl_sign($this->fixCadena($cadena), $signature, $pkeyid, OPENSSL_ALGO_SHA256);
                } else {
                    openssl_sign($this->fixCadena($cadena), $signature, $pkeyid);
                }
                $firmaElect = base64_encode($signature);
                $firmaXml = "<oxml:firmaElectronica>
                            <oxml:certificado>{$cert}</oxml:certificado>
                            <oxml:cadenaOriginal>{$cadena}</oxml:cadenaOriginal>
                            <oxml:firma>" . $firmaElect . "</oxml:firma>
                        </oxml:firmaElectronica>";
            } else {
                $cert = '';
                $cadena = '';
                $firma = '';
            }
            $exportador = "";
            if ($certOri == '1' && $numExp != '') {
                $exportador = "\n\t\t\t\t<oxml:numeroExportadorAutorizado>{$numExp}</oxml:numeroExportadorAutorizado>";
            }
            ///******************************************************************************** FALLO, PROVEEDOR ES EMISOR, Y DESTINATARIO CLIENTE.
            if ($tipoOp == 'TOCE.IMP' && !isset($reenvio)) {
                $emi = "<oxml:destinatario>" . $emisor . "\t\t\t    </oxml:destinatario>";
                $des = "<oxml:emisor>" . $dest . "\t\t\t    </oxml:emisor>";
            } elseif ($tipoOp == 'TOCE.IMP' && isset($reenvio) && $reenvio == 0) {
                $emi = "<oxml:destinatario>" . $emisor . "\t\t\t    </oxml:destinatario>";
                $des = "<oxml:emisor>" . $dest . "\t\t\t    </oxml:emisor>";
            } elseif ($tipoOp == 'TOCE.EXP' && isset($manual) && $manual == 1) {
                $emi = "<oxml:emisor>" . $dest . "\t\t\t    </oxml:emisor>";
                $des = "<oxml:destinatario>" . $emisor . "\t\t\t    </oxml:destinatario>";
            } elseif ($tipoOp == 'TOCE.EXP' && isset($manual) && $manual == 0) {
                $emi = "<oxml:emisor>" . $emisor . "\t\t\t    </oxml:emisor>";
                $des = "<oxml:destinatario>" . $dest . "\t\t\t    </oxml:destinatario>";
            } elseif ($tipoOp == 'TOCE.EXP' && isset($reenvio) && $reenvio == 1) {
                $emi = "<oxml:emisor>" . $dest . "\t\t\t    </oxml:emisor>";
                $des = "<oxml:destinatario>" . $emisor . "\t\t\t    </oxml:destinatario>";
            } elseif ($tipoOp == 'TOCE.IMP' && isset($reenvio) && $reenvio == 1) {
                $emi = "<oxml:emisor>" . $dest . "\t\t\t    </oxml:emisor>";
                $des = "<oxml:destinatario>" . $emisor . "\t\t\t    </oxml:destinatario>";
            }
            if((!isset($emi) || !isset($dest)) && $manual == null && $tipoOp == 'TOCE.EXP') {
                $emi = "<oxml:emisor>" . $dest . "\t\t\t    </oxml:emisor>";
                $des = "<oxml:destinatario>" . $emisor . "\t\t\t    </oxml:destinatario>";
            }
            if ($adenda) {
                $adenda = "\n\t\t\t    <oxml:e-document>{$adenda}</oxml:e-document>";
            } else {
                $adenda = "";
            }
            if (isset($obs) && $obs != '') {
                $observaciones = trim(preg_replace('/\s\s+/', ' ', $obs));
                $obs = "\n\t\t\t    <oxml:observaciones>" . preg_replace('!\s+!', ' ',trim($observaciones)) . "</oxml:observaciones>";
            } else {
                $obs = "";
            }
            if ($tipoOp == 'TOCE.EXP' && $tipoFigura == 5) {
                $impExp = 4;
            } else {
                $impExp = $tipoFigura;
            }
            $xml = "<oxml:comprobantes>{$adenda}
                            <oxml:tipoOperacion>" . preg_replace('!\s+!', ' ',trim($tipoOp)) . "</oxml:tipoOperacion>
                            <oxml:patenteAduanal>" . preg_replace('!\s+!', ' ',trim($patente)) . "</oxml:patenteAduanal>
                            <oxml:fechaExpedicion>" . date("Y-m-d", strtotime($fechaFact)) . "</oxml:fechaExpedicion>{$obs}
                            " . $xmlRfcs . "
                            <oxml:tipoFigura>" . $impExp . "</oxml:tipoFigura>
                            " . $xmlEmails . "" . $firmaXml . "
                            <oxml:numeroFacturaOriginal>" . $this->vuChar(preg_replace('!\s+!', ' ',trim($numFact)), true) . "</oxml:numeroFacturaOriginal>
                            <oxml:factura>
                                <oxml:certificadoOrigen>" . preg_replace('!\s+!', ' ',trim($certOri)) . "</oxml:certificadoOrigen>" . $exportador . "
                                <oxml:subdivision>" . (isset($subDiv) ? $subDiv : '0') . "</oxml:subdivision>
                            </oxml:factura>
                            " . $emi . "
                            " . $des . "
                            " . $merc . "
                        </oxml:comprobantes>";
            return array(
                'xml' => $xml,
                'cert' => $cert,
                'cadena' => isset($fixCadena) ? $fixCadena : '',
                //'cadena' => $fixCadena,
                'firma' => $firmaElect,
            );
        } catch (Exception $e) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    protected function preXmlRelacion($numRelacion, $facturas, $tipoOp, $xmlRfcs, $xmlEmails, $tipoFigura, $emisor, $dest, $firma = null, $cadena = null, $cert = null, $pkeyid = null) {
        try {
            $firmaXml = '';
            if ($firma) {
                openssl_sign($this->fixCadena($cadena), $signature, $pkeyid);
                $firma = base64_encode($signature);
                $firmaXml = "<oxml:firmaElectronica>"
                        . "<oxml:certificado>{$cert}</oxml:certificado>"
                        . "<oxml:cadenaOriginal>{$cadena}</oxml:cadenaOriginal>"
                        . "<oxml:firma>" . $firma . "</oxml:firma>"
                        . "</oxml:firmaElectronica>";
            } else {
                $cert = '';
                $cadena = '';
                $firma = '';
            }
            $tmpXml = '<oxml:solicitarRecibirRelacionFacturasIAServicio>';
            $tmpXml .= "\n\t\t<oxml:comprobantes>";
            $tmpXml .= "\n\t\t    <oxml:tipoOperacion>" . $tipoOp . "</oxml:tipoOperacion>
                    <oxml:patenteAduanal>" . $facturas[0]["Patente"] . "</oxml:patenteAduanal>
                    <oxml:fechaExpedicion>" . date("Y-m-d", strtotime($facturas[0]["FechaFactura"])) . "</oxml:fechaExpedicion>
                    <oxml:observaciones>Solicitud de Relacion de Facturas automatica</oxml:observaciones>
                    " . $xmlRfcs . "
                    <oxml:tipoFigura>" . $tipoFigura . "</oxml:tipoFigura>
                    " . $xmlEmails . "" . $firmaXml . "
                    <oxml:numeroRelacionFacturas>{$facturas[0]["NumFactura"]}</oxml:numeroRelacionFacturas>
                    <oxml:emisor>{$emisor}\t\t\t</oxml:emisor>
                    <oxml:destinatario>{$dest}\t\t\t</oxml:destinatario>";

            foreach ($facturas as $fact) {
                $tmpXml .= $this->crearFacturaRelacion($fact);
            }
            $tmpXml .= "\n\t\t</oxml:comprobantes>";
            $tmpXml .= "\n</oxml:solicitarRecibirRelacionFacturasIAServicio>";

            if (!$firma) {
                return array(
                    'xml' => $tmpXml,
                );
            } else {
                return array(
                    'xml' => $tmpXml,
                    'cert' => $cert,
                    'cadena' => $cadena,
                    'firma' => $firma,
                );
            }
        } catch (Exception $e) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * Preparar envio con contraseñas para VU.
     * 
     * @param String $comprobantes
     * @param String $username
     * @param String $password
     * @return String
     */
    public function prepararEnvio($comprobantes, $username, $password) {
        try {
            $comprobantes = preg_replace('/&/', '&amp;', $comprobantes);
            $xml = "<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:oxml=\"http://www.ventanillaunica.gob.mx/cove/ws/oxml/\">
                        <soapenv:Header>
                                <wsse:Security soapenv:mustUnderstand=\"1\" xmlns:wsse=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd\">
                                        <wsse:UsernameToken>
                                                <wsse:Username>{$username}</wsse:Username>
                                                <wsse:Password Type=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText\">{$password}</wsse:Password>
                                        </wsse:UsernameToken>
                                </wsse:Security>
                        </soapenv:Header>
                        <soapenv:Body>
                        <oxml:solicitarRecibirCoveServicio>
                        {$comprobantes}
                        </oxml:solicitarRecibirCoveServicio>
                    </soapenv:Body>
                </soapenv:Envelope>";

            return $xml;
        } catch (Exception $e) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * Convierte el XML en la cadena original a partir del archivo Cove02.xsl del SAT.
     * 
     * @param String $xml
     * @return String
     */
    protected function cadenaOriginal($xml) {
        try {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);        
            $xsl = file_get_contents(APPLICATION_PATH . "/../library/Cove02.xsl");
            $preXml = str_replace(array('oxml:', 'wsse:', 'soapenv:'), '', strtr($xml, $this->htmlNumeric()));
            $cleanXml = $preXml;
            unset($preXml);
            $xslt = new XSLTProcessor();
            $xslt->importStylesheet(new SimpleXMLElement($xsl));
            $tmp = str_replace('&', '&amp;', $cleanXml);
            $cadena = $xslt->transformToXml(new SimpleXMLElement( $tmp ));
            $cadenaOriginal = rtrim(ltrim(str_replace(array('<html>', '</html>', '<br>', "\r\n", "\r", "\n", 'Cadena Orignal :'), '', $cadena)));
            unset($cadena);
            unset($xslt);
            unset($cleanXml);
            unset($xsl);

            return $cadenaOriginal;
        } catch (Exception $e) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function crearProductos($productos) {
        try {
            $misc = new OAQ_Misc();
            $xml = '';

            foreach ($productos as $prod) {
                if ($xml == '') {
                    $xml .= "<oxml:mercancias>\n";
                } else {
                    $xml .= "\n\t\t\t    <oxml:mercancias>\n";
                }
                if (isset($prod["DESC_COVE"]) && $misc->trimUc($prod["DESC_COVE"]) != '') {
                    $xml .= "\t\t\t\t<oxml:descripcionGenerica>" . $misc->trimUc($prod["DESC_COVE"]) . "</oxml:descripcionGenerica>\n";
                } elseif (isset($prod["DESC1"]) && $misc->trimUc($prod["DESC1"]) != '') {
                    $xml .= "\t\t\t\t<oxml:descripcionGenerica>" . $misc->trimUc($prod["DESC1"]) . "</oxml:descripcionGenerica>\n";
                } elseif (isset($prod["DESC_COVE"])) {
                    $xml .= "\t\t\t\t<oxml:descripcionGenerica>" . $misc->trimUc($prod["DESC_COVE"]) . "</oxml:descripcionGenerica>\n";
                }
                if (isset($prod["PARTE"]) && $misc->trimUc($prod["PARTE"]) != '') {
                    $xml .= isset($prod["PARTE"]) ? "\t\t\t\t<oxml:numparte>" . $misc->trimUc(strtoupper($prod["PARTE"])) . "</oxml:numparte>\n" : '';
                }
                if (isset($prod["ORDEN"]) && $misc->trimUc($prod["ORDEN"]) != '') {
                    $xml .= isset($prod["ORDEN"]) ? "\t\t\t\t<oxml:secuencial>" . strtoupper($prod["ORDEN"]) . "</oxml:secuencial>\n" : '';
                }
                $xml .= isset($prod["UMC_OMA"]) ? "\t\t\t\t<oxml:claveUnidadMedida>" . trim($this->cleanString($prod["UMC_OMA"])) . "</oxml:claveUnidadMedida>\n" : '';
                $xml .= isset($prod["MONVAL"]) ? "\t\t\t\t<oxml:tipoMoneda>" . $misc->tipoMoneda($prod["MONVAL"]) . "</oxml:tipoMoneda>\n" : '';
                if(isset($prod["CAN_OMA"]) && $prod["CAN_OMA"] != 0) {
                    $xml .= isset($prod["CAN_OMA"]) ? "\t\t\t\t<oxml:cantidad>" . $this->formatNumber($prod["CAN_OMA"]) . "</oxml:cantidad>\n" : '';
                } elseif(isset($prod["CANTFAC"]) && $prod["CANTFAC"] != 0) {
                    $xml .= isset($prod["CANTFAC"]) ? "\t\t\t\t<oxml:cantidad>" . $this->formatNumber($prod["CANTFAC"]) . "</oxml:cantidad>\n" : '';
                }
                $xml .= isset($prod["PREUNI"]) ? "\t\t\t\t<oxml:valorUnitario>" . $this->formatNumber6($prod["PREUNI"]) . "</oxml:valorUnitario>\n" : '';
                $xml .= isset($prod["VALCOM"]) ? "\t\t\t\t<oxml:valorTotal>" . $this->formatNumber6($prod["VALCOM"]) . "</oxml:valorTotal>\n" : '';
                if(isset($prod["VALCEQ"]) && !isset($prod["VALDLS"])) {
                    $xml .= isset($prod["VALDLS"]) ? "\t\t\t\t<oxml:valorDolares>" . $this->formatNumber4($prod["VALCOM"] * $prod["VALCEQ"]) . "</oxml:valorDolares>\n" : '';
                } elseif(isset($prod["VALCEQ"]) && isset($prod["VALDLS"])) {
                    $xml .= "\t\t\t\t<oxml:valorDolares>" . $this->formatNumber4($prod["VALDLS"]) . "</oxml:valorDolares>\n";
                } else {
                    $xml .= isset($prod["VALDLS"]) ? "\t\t\t\t<oxml:valorDolares>" . $this->formatNumber4($prod["VALDLS"]) . "</oxml:valorDolares>\n" : '';
                }                
                $xml .= $this->descripcionesEspecificas($prod["MARCA"], $prod["MODELO"], $prod["SUBMODELO"], $prod["NUMSERIE"]);
                $xml .= "\t\t\t    </oxml:mercancias>";
            }
            return $xml;
        } catch (Exception $e) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    protected function cleanString($string) {
        return strtoupper(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '',mb_convert_encoding($string, 'UTF-8', 'UTF-8')));
    }

    protected function descripcionesEspecificas($marca, $modelo, $subModelo, $numSerie) {
        try {
            $misc = new OAQ_Misc();
            $desc = null;
            if (isset($marca) && $misc->trimUc($marca) != '') {
                $desc .= "\n\t\t\t\t\t<oxml:marca>" . $misc->trimUc($marca) . '</oxml:marca>';
            }
            if (isset($modelo) && $misc->trimUc($modelo) != '') {
                $desc .= "\n\t\t\t\t\t<oxml:modelo>" . $misc->trimUc($modelo) . '</oxml:modelo>';
            }
            if (isset($subModelo) && $misc->trimUc($subModelo) != '') {
                $desc .= "\n\t\t\t\t\t<oxml:subModelo>" . $misc->trimUc($subModelo) . '</oxml:subModelo>';
            }
            if (isset($numSerie) && $misc->trimUc($numSerie) != '') {
                $desc .= "\n\t\t\t\t\t<oxml:numeroSerie>" . $misc->trimUc($numSerie) . '</oxml:numeroSerie>';
            }
            $xml = '';
            if ($desc) {
                $xml .= "\t\t\t\t<oxml:descripcionesEspecificas>{$desc}\n\t\t\t\t</oxml:descripcionesEspecificas>\n";
            }
            return $xml;
        } catch (Exception $e) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $e->getMessage());
        }        
    }

    protected function observaciones($obs) {
        try {
            $split = explode("\r\n", $obs);
            $xml = '';
            foreach ($split as $line) {
                if (preg_match('/Marca:/i', $line)) {
                    $marca = explode(':', $line);
                    $xml .= "\n\t\t\t\t\t<oxml:marca>" . $this->vuChar(trim($marca[1]), true) . '</oxml:marca>';
                }
                if (preg_match('/Modelo:/i', $line)) {
                    $modelo = explode(':', $line);
                    $xml .= "\n\t\t\t\t\t<oxml:modelo>" . $this->vuChar(trim($modelo[1]), true) . '</oxml:modelo>';
                }
                if (preg_match('/Numero de Serie:/i', $line)) {
                    $serie = explode(':', $line);
                    $xml .= "\n\t\t\t\t\t<oxml:numeroSerie>" . $this->vuChar(trim($serie[1]), true) . '</oxml:numeroSerie>';
                }
                if (preg_match('/Sub Modelo:/i', $line)) {
                    $modelo = explode(':', $line);
                    $xml .= "\n\t\t\t\t\t<oxml:subModelo>" . $this->vuChar(trim($modelo[1]), true) . '</oxml:subModelo>';
                }
            }
            if (count($xml) > 0) {
                $xml = "\t\t\t\t<oxml:descripcionesEspecificas>" . $xml . "\n\t\t\t\t</oxml:descripcionesEspecificas>\n";
            }
            return $this->htmlspanishchars($xml);
        } catch (Exception $e) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $e->getMessage());
        }        
    }

    protected function vuChar($value, $special = null) {
        return preg_replace('/\s+/', ' ',$value);
    }

    protected function crearEmisor($factura, $tipoOperacion = null) {
        try {            
            $misc = new OAQ_Misc();
            if (isset($factura["CteIden"]) && $factura["CteIden"] == '1') {
                $data = "\n\t\t\t\t<oxml:tipoIdentificador>1</oxml:tipoIdentificador>\n";
                $data .= "\t\t\t\t<oxml:identificacion>" . $factura["CteRfc"] . "</oxml:identificacion>\n";
            } elseif (isset($factura["CteIden"]) && $factura["CteIden"] == '3') {
                $data = "\n\t\t\t\t<oxml:tipoIdentificador>3</oxml:tipoIdentificador>\n";
            } else {
                $data = "\n\t\t\t\t<oxml:tipoIdentificador>" . $this->tipoIdentificador($factura["CteRfc"], $factura["CtePais"]) . "</oxml:tipoIdentificador>\n";
                if(isset($factura["CteRfc"]) && trim($factura["CteRfc"]) != '') {
                    $data .= "\t\t\t\t<oxml:identificacion>" . $factura["CteRfc"] . "</oxml:identificacion>\n";
                } 
                else {
                    $data .= "\t\t\t\t<oxml:identificacion>S/N</oxml:identificacion>\n";
                }
            }
            $data .= $this->datosEmisorDestinatario($factura, 'CteNombre', 'nombre');
            $data .= "\t\t\t\t<oxml:domicilio>\n";
            $data .= $this->datosEmisorDestinatario($factura, 'CteCalle', 'calle', true);
            $data .= $this->datosEmisorDestinatario($factura, 'CteNumExt', 'numeroExterior', true);
            $data .= $this->datosEmisorDestinatario($factura, 'CteNumInt', 'numeroInterior', true);
            $data .= $this->datosEmisorDestinatario($factura, 'CteColonia', 'colonia', true);
            $data .= $this->datosEmisorDestinatario($factura, 'CteLocalidad', 'localidad', true);
            $data .= $this->datosEmisorDestinatario($factura, 'CteMun', 'municipio', true);
            $data .= $this->datosEmisorDestinatario($factura, 'CteEdo', 'entidadFederativa', true);
            $data .= $this->datosEmisorDestinatario($factura, 'CteCP', 'codigoPostal', true);
            $data .= $this->datosEmisorDestinatario($factura, 'CtePais', 'pais', true);
            $data .= "\t\t\t\t</oxml:domicilio>\n";
            return $data;
        } catch (Exception $ex) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    protected function crearDestinatario($factura, $tipoOperacion = null) {
        try {
            if (isset($factura["ProIden"]) && $factura["ProIden"] == '1') {
                $data = "\n\t\t\t\t<oxml:tipoIdentificador>1</oxml:tipoIdentificador>\n";
                $data .= "\t\t\t\t<oxml:identificacion>" . $factura["ProTaxID"] . "</oxml:identificacion>\n";
            } elseif (isset($factura["ProIden"]) && $factura["ProIden"] == '3') {
                $data = "\n\t\t\t\t<oxml:tipoIdentificador>3</oxml:tipoIdentificador>\n";
            } else {
                $data = "\n\t\t\t\t<oxml:tipoIdentificador>" . $this->tipoIdentificador($factura["ProTaxID"], $factura["ProPais"]) . "</oxml:tipoIdentificador>\n";
                if(isset($factura["ProTaxID"]) && trim($factura["ProTaxID"]) != '') {
                    $data .= "\t\t\t\t<oxml:identificacion>" . $factura["ProTaxID"] . "</oxml:identificacion>\n";
                } 
                else {
                    $data .= "\t\t\t\t<oxml:identificacion>S/N</oxml:identificacion>\n";
                }
            }

            $data .= $this->datosEmisorDestinatario($factura, 'ProNombre', 'nombre');
            $data .= "\t\t\t\t<oxml:domicilio>\n";
            $data .= $this->datosEmisorDestinatario($factura, 'ProCalle', 'calle', true);
            $data .= $this->datosEmisorDestinatario($factura, 'ProNumExt', 'numeroExterior', true);
            $data .= $this->datosEmisorDestinatario($factura, 'ProNumInt', 'numeroInterior', true);
            $data .= $this->datosEmisorDestinatario($factura, 'ProColonia', 'colonia', true);
            $data .= $this->datosEmisorDestinatario($factura, 'ProLocalidad', 'localidad', true);
            $data .= $this->datosEmisorDestinatario($factura, 'ProMun', 'municipio', true);
            $data .= $this->datosEmisorDestinatario($factura, 'ProEdo', 'entidadFederativa', true);
            $data .= $this->datosEmisorDestinatario($factura, 'ProCP', 'codigoPostal', true);
            $data .= $this->datosEmisorDestinatario($factura, 'ProPais', 'pais', true);
            $data .= "\t\t\t\t</oxml:domicilio>\n";
            return $data;            
        } catch (Exception $ex) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    protected function datosEmisorDestinatario($array, $arrayField, $xmlField, $dom = null) {
        $misc = new OAQ_Misc();
        if (isset($array[$arrayField]) && $misc->trimUcUtf8($array[$arrayField]) != "" && !$dom) {
            return "\t\t\t\t<oxml:{$xmlField}>" . $misc->trimUcUtf8($array[$arrayField]) . "</oxml:{$xmlField}>\n";
        } elseif (isset($array[$arrayField]) && $misc->trimUcUtf8($array[$arrayField]) != "" && $dom) {
            return "\t\t\t\t\t<oxml:{$xmlField}>" . $misc->trimUcUtf8($array[$arrayField]) . "</oxml:{$xmlField}>\n";
        }
        return null;
    }

    /**
     * Envia a la ventanillael XML del COVE.
     * 
     * @param string $xml
     * @param string $url
     * @param int $timeout
     * @return String
     */
    public function vucemServicio($xml, $url, $timeout = null) {
        try {
            $headers = array(
                "Content-type: text/xml; charset=UTF-8",
                "Accept: text/xml",
                "Cache-Control: no-cache",
                "Pragma: no-cache",
                "Content-length: " . strlen($xml) . "");
            $soap = curl_init();
            curl_setopt($soap, CURLOPT_URL, $url);
            curl_setopt($soap, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($soap, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($soap, CURLOPT_POST, true);
            curl_setopt($soap, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($soap, CURLOPT_POSTFIELDS, $xml);
            curl_setopt($soap, CURLOPT_TIMEOUT, isset($timeout) ? $timeout : 600);
            $result = curl_exec($soap);
            curl_close($soap);
            return $result;
        } catch (Exception $e) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * Envia a la ventanillael XML del COVE.
     * 
     * @param String $xml
     * @return String
     */
    public function enviarCoveVucem($xml, $url = null) {
        try {
            $headers = array(
                "Content-type: text/xml;charset=windows-1252",
                "Accept: text/xml",
                "Accept-Encoding: gzip,deflate",
                "Cache-Control: no-cache",
                "Pragma: no-cache",
                "User-Agent: Apache-HttpClient/4.1.1",
                "Connection: Keep-Alive",
                "Content-length: " . strlen($xml));
            if (!isset($url)) {
                $url = $this->_config->app->vucem . "RecibirCoveService";
            }
            $soap = curl_init();
            curl_setopt($soap, CURLOPT_URL, $url);
            curl_setopt($soap, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($soap, CURLOPT_SSL_VERIFYPEER, false);
//            curl_setopt($soap, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($soap, CURLOPT_POST, true);
            curl_setopt($soap, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($soap, CURLOPT_POSTFIELDS, $xml);
            $result = curl_exec($soap);
            curl_close($soap);
            return $result;
        } catch (Exception $e) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * Envia a la ventanillael XML del COVE.
     * 
     * @param String $xml
     * @return String
     */
    public function pruebaVucem($xml, $env) {
        try {
            $headers = array(
                "Content-type: text/xml;charset=utf-8",
                "Accept: text/xml",
                "Accept-Encoding: gzip,deflate",
                "Cache-Control: no-cache",
                "Pragma: no-cache",
                "User-Agent: Apache-HttpClient/4.1.1",
                "Connection: Keep-Alive",
                "Content-length: " . strlen($xml));
            if ($env == 'prod') {
                $url = "https://www.ventanillaunica.gob.mx/ventanilla/" . "RecibirCoveService";
            } else {
                $url = "https://www2.ventanillaunica.gob.mx/procesamiento-cove-0/" . "RecibirCoveService";
            }
            $soap = curl_init();
            curl_setopt($soap, CURLOPT_URL, $url);
            curl_setopt($soap, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($soap, CURLOPT_SSL_VERIFYPEER, false);
//            curl_setopt($soap, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($soap, CURLOPT_POST, true);
            curl_setopt($soap, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($soap, CURLOPT_POSTFIELDS, $xml);
            $result = curl_exec($soap);
            curl_close($soap);
            return $result;
        } catch (Exception $e) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * Envia a la ventanillael XML del COVE.
     * 
     * @param string $xml
     * @param string $url
     * @param int $timeout
     * @return String
     */
    public function solicitarRespuestaCove($xml, $url = null, $timeout = null) {
        try {
            $headers = array(
                "Content-type: text/xml; charset=UTF-8",
                "Accept: text/xml",
                "Cache-Control: no-cache",
                "Pragma: no-cache",
                "Content-length: " . strlen($xml) . "");
            if (!isset($url)) {
                $url = $this->_config->app->vucem . "ConsultarRespuestaCoveService";
            }
            $soap = curl_init();
            curl_setopt($soap, CURLOPT_URL, $url);
            curl_setopt($soap, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($soap, CURLOPT_SSL_VERIFYPEER, false);
//            curl_setopt($soap, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($soap, CURLOPT_POST, true);
            curl_setopt($soap, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($soap, CURLOPT_POSTFIELDS, $xml);
            curl_setopt($soap, CURLOPT_TIMEOUT, isset($timeout) ? $timeout : 600);
            $result = curl_exec($soap);
            curl_close($soap);
            return $result;
        } catch (Exception $e) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * Regresa la respuesta de VUCEM en un arreglo.
     * 
     * @param String $xml
     * @return array
     */
    public function respuestaVucem($xml) {
        try {
            $xml = simplexml_load_string(str_replace(array('wsu:', 'S:'), '', $xml));
            $xmlArray = @json_decode(@json_encode($xml), 1);

            return array(
                'hora' => $xmlArray["Body"]["solicitarRecibirCoveServicioResponse"]["horaRecepcion"],
                'operacion' => $xmlArray["Body"]["solicitarRecibirCoveServicioResponse"]["numeroDeOperacion"],
            );
        } catch (Exception $e) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    /**
     * Regresa la respuesta de VUCEM en un arreglo.
     * 
     * @param String $xml
     * @return array
     */
    public function respuestaVucemRel($xml) {
        try {
            $xml = simplexml_load_string(str_replace(array('wsu:', 'S:'), '', $xml));
            $xmlArray = @json_decode(@json_encode($xml), 1);

            return array(
                'hora' => $xmlArray["Body"]["solicitarRecibirRelacionFacturasIAServicioResponse"]["horaRecepcion"],
                'operacion' => $xmlArray["Body"]["solicitarRecibirRelacionFacturasIAServicioResponse"]["numeroDeOperacion"],
            );
        } catch (Exception $e) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * Regresa la respuesta de VUCEM en un arreglo.
     * 
     * @param String $xml
     * @return array
     */
    public function respuestaVucemRelacion($xml) {
        try {
            $xml = simplexml_load_string(str_replace(array('wsu:', 'S:'), '', $xml));
            $xmlArray = @json_decode(@json_encode($xml), 1);

            return array(
                'hora' => $xmlArray["Body"]["solicitarRecibirRelacionFacturasIAServicioResponse"]["horaRecepcion"],
                'operacion' => $xmlArray["Body"]["solicitarRecibirRelacionFacturasIAServicioResponse"]["numeroDeOperacion"],
            );
        } catch (Exception $e) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function tipoIdentificador($rfc, $pais) {
        // Tax ID/RFC/CURP : El tipo de indentificador no es v?lido. 
        // Valores permitidos [0-TAX_ID, 1-RFC, 2-CURP,3-SIN_TAX_ID]        
        $regRfc = '/^[A-Z]{3,4}([0-9]{2})(1[0-2]|0[1-9])([0-3][0-9])([A-Z0-9]{3,4})$/';
        $regTaxId = '/^[0-9]{2,3}/';

        if ( ($pais == 'MEX' || $pais == 'MEXICO') && preg_match($regRfc, str_replace(' ', '', trim($rfc)))) {
            if($rfc != 'EXTR920901TS4') {
                if (strlen($rfc) > 12) {
                    return '2';
                }
                return '1';
            } else {
                return '0';
            }
        }
        if ( ($pais == 'MEX' || $pais == 'MEXICO') && !preg_match($regRfc, str_replace(' ', '', trim($rfc)))) {
            return '0';
        }
        if ($pais != 'MEX' && trim($rfc) != '') {
            return '0';
        }
        if ($pais != 'MEX' && trim($rfc) == '') {
            return '3';
        }
    }

    public function addRfcsConsulta($rfc, $pais) {
        if ($this->tipoIdentificador($rfc, $pais) == '1') {
            return true;
        }
        return null;
    }

    protected function formatNumber($number) {
        return number_format($number, 3, '.', '');
    }

    protected function formatNumber6($number) {
        return number_format($number, 6, '.', '');
    }

    protected function formatNumber4($number) {
        return number_format($number, 4, '.', '');
    }

    public function vucemXmlToArray($xml) {
        try {
            $clean = str_replace(array('ns2:', 'ns3:', 'ns9:', 'ns8:', 'S:', 'wsu:', 'wsse:', 'ns3:', 'wsu:', 'soapenv:', 'env:', 'oxml:', '<![CDATA[', ']]>', '&lt;'), '', $xml);

            if (preg_match('/html/i', $clean)) {
                return null;
            }
            $xmlClean = simplexml_load_string($clean);
            unset($clean);
            return @json_decode(@json_encode($xmlClean), 1);
        } catch (Exception $e) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function fixAduana($aduana) {
        if ($aduana == '24') {
            return '240';
        }
        if ($aduana == '64') {
            return '640';
        }
        if ($aduana == '37') {
            return '370';
        }
        return $aduana;
    }

    public function vucemPedimento($servicio, $xml) {
        // ConsultarPedimentoCompletoService
        // ListarPedimentosService        
        try {
            $headers = array(
                "Content-type: text/xml; charset=UTF-8",
                "Accept: text/xml",
                "Cache-Control: no-cache",
                "Pragma: no-cache",
                "Content-length: " . strlen($xml) . "");
            $url = $this->_config->app->vucemped . $servicio;
            $soap = curl_init();
            curl_setopt($soap, CURLOPT_URL, $url);
            curl_setopt($soap, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($soap, CURLOPT_SSL_VERIFYPEER, false);
//            curl_setopt($soap, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($soap, CURLOPT_POST, true);
            curl_setopt($soap, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($soap, CURLOPT_POSTFIELDS, $xml);
            $result = curl_exec($soap);
            curl_close($soap);
            return $result;
        } catch (Exception $e) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function vucemCove($servicio, $xml) {
        try {
            $headers = array(
                "Content-type: text/xml; charset=UTF-8",
                "Accept: text/xml",
                "Cache-Control: no-cache",
                "Pragma: no-cache",
                "Content-length: " . strlen($xml) . "");
            //$url = $this->_config->app->vucem . $servicio;
            $url = "https://www.ventanillaunica.gob.mx/ventanilla/" . $servicio;
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
            throw new Exception("Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function pruebaCove($username, $password) {
        $xml = "<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:oxml=\"http://www.ventanillaunica.gob.mx/cove/ws/oxml/\">
                        <soapenv:Header>
                                <wsse:Security soapenv:mustUnderstand=\"1\" xmlns:wsse=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd\">
                                        <wsse:UsernameToken>
                                                <wsse:Username>{$username}</wsse:Username>
                                                <wsse:Password Type=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText\">{$password}</wsse:Password>
                                        </wsse:UsernameToken>
                                </wsse:Security>
                        </soapenv:Header>
                        <soapenv:Body>
                        <oxml:solicitarRecibirCoveServicio></oxml:solicitarRecibirCoveServicio>
                    </soapenv:Body>
                </soapenv:Envelope>";
        return $xml;
    }

    /**
     * Preparar envio con contraseñas para VU.
     * 
     * @param String $relFact
     * @param String $comprobantes
     * @param String $username
     * @param String $password
     * @return String
     */
    public function prepararEnvioRelacion($comprobantes, $username, $password) {
        try {
            $xml = "<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:oxml=\"http://www.ventanillaunica.gob.mx/cove/ws/oxml/\">"
                    . "<soapenv:Header>"
                    . "<wsse:Security soapenv:mustUnderstand=\"1\" xmlns:wsse=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd\">"
                    . "<wsse:UsernameToken>"
                    . "<wsse:Username>{$username}</wsse:Username>"
                    . "<wsse:Password Type=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText\">{$password}</wsse:Password>"
                    . "</wsse:UsernameToken>"
                    . "</wsse:Security>"
                    . "</soapenv:Header>"
                    . "<soapenv:Body>"
                    . "{$comprobantes["xml"]}"
                    . "</soapenv:Body>"
                    . "</soapenv:Envelope>";
            return $xml;
        } catch (Exception $e) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function consultaRespuestaCove($data) {
        try {
            $xml = "<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:oxml=\"http://www.ventanillaunica.gob.mx/cove/ws/oxml/\">
                        <soapenv:Header>
                                <wsse:Security soapenv:mustUnderstand=\"1\" xmlns:wsse=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd\">
                                        <wsse:UsernameToken>
                                                <wsse:Username>{$data["username"]}</wsse:Username>
                                                <wsse:Password Type=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText\">{$data["ws_pswd"]}</wsse:Password>
                                        </wsse:UsernameToken>
                                </wsse:Security>
                        </soapenv:Header>
                        <soapenv:Body>
                                <oxml:solicitarConsultarRespuestaCoveServicio>
                                <oxml:numeroOperacion>{$data["solicitud"]}</oxml:numeroOperacion>
                                <oxml:firmaElectronica>
                                        <oxml:certificado>{$data["cert"]}</oxml:certificado>
                                        <oxml:cadenaOriginal>{$data["cadena"]}</oxml:cadenaOriginal>
                                        <oxml:firma>{$data["firma"]}</oxml:firma>
                                </oxml:firmaElectronica>
                                </oxml:solicitarConsultarRespuestaCoveServicio>
                        </soapenv:Body>
                </soapenv:Envelope>";
            return $xml;
        } catch (Exception $e) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function solicitudPedimentoCompleto($rfc, $pwd, $patente, $aduana, $pedimento) {
        $xml = "<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:con=\"http://www.ventanillaunica.gob.mx/pedimentos/ws/oxml/consultarpedimentocompleto\" xmlns:com=\"http://www.ventanillaunica.gob.mx/pedimentos/ws/oxml/comunes\">
            <soapenv:Header>
                <wsse:Security soapenv:mustUnderstand=\"1\" xmlns:wsse=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd\">
                    <wsse:UsernameToken>
                        <wsse:Username>{$rfc}</wsse:Username>
                        <wsse:Password Type=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText\">{$pwd}</wsse:Password>
                    </wsse:UsernameToken>
            </wsse:Security></soapenv:Header>
            <soapenv:Body>
               <con:consultarPedimentoCompletoPeticion>
                  <con:peticion>
                     <com:aduana>{$aduana}</com:aduana>
                     <com:patente>{$patente}</com:patente>
                     <com:pedimento>{$pedimento}</com:pedimento>
                  </con:peticion>
               </con:consultarPedimentoCompletoPeticion>
            </soapenv:Body>
         </soapenv:Envelope>";
        return $xml;
    }

    public function solicitudEstadoPedimento($rfc, $pwd, $patente, $aduana, $pedimento, $numOperacion) {
        $xml = "<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:con=\"http://www.ventanillaunica.gob.mx/pedimentos/ws/oxml/consultarestadopedimentos\" xmlns:com=\"http://www.ventanillaunica.gob.mx/pedimentos/ws/oxml/comunes\">
            <soapenv:Header>
                <wsse:Security soapenv:mustUnderstand=\"1\" xmlns:wsse=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd\">
                    <wsse:UsernameToken>
                        <wsse:Username>{$rfc}</wsse:Username>
                        <wsse:Password Type=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText\">{$pwd}</wsse:Password>
                    </wsse:UsernameToken>
                </wsse:Security>
            </soapenv:Header>
            <soapenv:Body>
               <con:consultarEstadoPedimentosPeticion>
                  <con:numeroOperacion>{$numOperacion}</con:numeroOperacion>
                  <con:peticion>
                     <com:aduana>{$aduana}</com:aduana>
                     <com:patente>{$patente}</com:patente>
                     <com:pedimento>{$pedimento}</com:pedimento>
                  </con:peticion>
               </con:consultarEstadoPedimentosPeticion>
            </soapenv:Body>
         </soapenv:Envelope>";
        return $xml;
    }

    public function xmlListadoPedimentos($rfc, $pwd, $fecha, $patente, $aduana) {
        $xml = "<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:lis=\"http://www.ventanillaunica.gob.mx/pedimentos/ws/oxml/listarpedimentos\">
                <soapenv:Header>
                             <wsse:Security soapenv:mustUnderstand=\"1\" xmlns:wsse=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd\">
                                     <wsse:UsernameToken>
                                             <wsse:Username>{$rfc}</wsse:Username>
                                             <wsse:Password Type=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText\">{$pwd}</wsse:Password>
                                     </wsse:UsernameToken>
                             </wsse:Security>
                </soapenv:Header>
                <soapenv:Body>
                   <lis:consultarPedimentosPeticion>
                      <lis:peticion>
                         <lis:aduana>{$aduana}</lis:aduana>
                         <lis:patente>{$patente}</lis:patente>
                         <lis:fechaInicio>{$fecha}</lis:fechaInicio>
                         <lis:fechaFin>{$fecha}</lis:fechaFin>
                      </lis:peticion>
                   </lis:consultarPedimentosPeticion>
                </soapenv:Body>
             </soapenv:Envelope>";
        return $xml;
    }

    public function partidaXml($rfc, $pwd, $patente, $aduana, $pedimento, $numOperacion, $partida) {
        $xml = "<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:con=\"http://www.ventanillaunica.gob.mx/pedimentos/ws/oxml/consultarpartida\" xmlns:com=\"http://www.ventanillaunica.gob.mx/pedimentos/ws/oxml/comunes\">
            <soapenv:Header>
                         <wsse:Security soapenv:mustUnderstand=\"1\" xmlns:wsse=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd\">
                                 <wsse:UsernameToken>
                                         <wsse:Username>{$rfc}</wsse:Username>
                                         <wsse:Password Type=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText\">{$pwd}</wsse:Password>
                                 </wsse:UsernameToken>
                         </wsse:Security>
            </soapenv:Header>
            <soapenv:Body>
               <con:consultarPartidaPeticion>
                  <con:peticion>
                     <com:aduana>{$aduana}</com:aduana>
                     <com:patente>{$patente}</com:patente>
                     <com:pedimento>{$pedimento}</com:pedimento>
                     <con:numeroOperacion>{$numOperacion}</con:numeroOperacion>
                     <con:numeroPartida>{$partida}</con:numeroPartida>
                  </con:peticion>
               </con:consultarPartidaPeticion>
            </soapenv:Body>
         </soapenv:Envelope>";
        return $xml;
    }

    public function consultaEDocument($rfcAgente, $pwd, $cer, $cadena, $firma, $cove) {
        $xml = "<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:con=\"http://www.ventanillaunica.gob.mx/ConsultarEdocument/\" xmlns:oxml=\"http://www.ventanillaunica.gob.mx/cove/ws/oxml/\">
   <soapenv:Header>
        <wsse:Security soapenv:mustUnderstand=\"1\" xmlns:wsse=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd\">
            <wsse:UsernameToken>
                <wsse:Username>{$rfcAgente}</wsse:Username>
                <wsse:Password Type=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText\">{$pwd}</wsse:Password>
            </wsse:UsernameToken>
        </wsse:Security>
            </soapenv:Header>
            <soapenv:Body>
               <con:ConsultarEdocumentRequest>
                  <con:request>
                     <con:firmaElectronica>
                        <oxml:certificado>{$cer}</oxml:certificado>
                        <oxml:cadenaOriginal>{$cadena}</oxml:cadenaOriginal>
                        <oxml:firma>{$firma}</oxml:firma>
                     </con:firmaElectronica>
                     <con:criterioBusqueda>
                        <con:eDocument>{$cove}</con:eDocument>
                     </con:criterioBusqueda>
                  </con:request>
               </con:ConsultarEdocumentRequest>
            </soapenv:Body>
         </soapenv:Envelope>";
        return $xml;
    }

    public function envioEdocument($username, $password, $correoElectronico, $idTipoDocumento, $nombreDocumento, $rfcConsulta, $archivo, $certificado, $cadenaOriginal, $firma) {
        $xml = "<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:dig=\"http://www.ventanillaunica.gob.mx/aga/digitalizar/ws/oxml/DigitalizarDocumento\" xmlns:res=\"http://www.ventanillaunica.gob.mx/common/ws/oxml/respuesta\">
            <soapenv:Header>
                 <wsse:Security soapenv:mustUnderstand=\"1\" xmlns:wsse=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd\">
                         <wsse:UsernameToken>
                                 <wsse:Username>{$username}</wsse:Username>
                                 <wsse:Password Type=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText\">{$password}</wsse:Password>
                         </wsse:UsernameToken>
                 </wsse:Security>
            </soapenv:Header>
            <soapenv:Body>
               <dig:registroDigitalizarDocumentoServiceRequest>
                  <dig:correoElectronico>{$correoElectronico}</dig:correoElectronico>
                  <dig:documento>
                     <dig:idTipoDocumento>{$idTipoDocumento}</dig:idTipoDocumento>
                     <dig:nombreDocumento>{$nombreDocumento}</dig:nombreDocumento>
                     <dig:rfcConsulta>{$rfcConsulta}</dig:rfcConsulta>
                     <dig:archivo>{$archivo}</dig:archivo>
                  </dig:documento>
                  <dig:peticionBase>
                     <res:firmaElectronica>
                        <res:certificado>{$certificado}</res:certificado>
                        <res:cadenaOriginal>{$cadenaOriginal}</res:cadenaOriginal>
                        <res:firma>{$firma}</res:firma>
                     </res:firmaElectronica>
                  </dig:peticionBase>
               </dig:registroDigitalizarDocumentoServiceRequest>
            </soapenv:Body>
         </soapenv:Envelope>";
        return $xml;
    }
    
    public function envioEdocumentNoConsulta($username, $password, $correoElectronico, $idTipoDocumento, $nombreDocumento, $archivo, $certificado, $cadenaOriginal, $firma) {
        $xml = "<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:dig=\"http://www.ventanillaunica.gob.mx/aga/digitalizar/ws/oxml/DigitalizarDocumento\" xmlns:res=\"http://www.ventanillaunica.gob.mx/common/ws/oxml/respuesta\">
            <soapenv:Header>
                 <wsse:Security soapenv:mustUnderstand=\"1\" xmlns:wsse=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd\">
                         <wsse:UsernameToken>
                                 <wsse:Username>{$username}</wsse:Username>
                                 <wsse:Password Type=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText\">{$password}</wsse:Password>
                         </wsse:UsernameToken>
                 </wsse:Security>
            </soapenv:Header>
            <soapenv:Body>
               <dig:registroDigitalizarDocumentoServiceRequest>
                  <dig:correoElectronico>{$correoElectronico}</dig:correoElectronico>
                  <dig:documento>
                     <dig:idTipoDocumento>{$idTipoDocumento}</dig:idTipoDocumento>
                     <dig:nombreDocumento>{$nombreDocumento}</dig:nombreDocumento>
                     <dig:archivo>{$archivo}</dig:archivo>
                  </dig:documento>
                  <dig:peticionBase>
                     <res:firmaElectronica>
                        <res:certificado>{$certificado}</res:certificado>
                        <res:cadenaOriginal>{$cadenaOriginal}</res:cadenaOriginal>
                        <res:firma>{$firma}</res:firma>
                     </res:firmaElectronica>
                  </dig:peticionBase>
               </dig:registroDigitalizarDocumentoServiceRequest>
            </soapenv:Body>
         </soapenv:Envelope>";
        return $xml;
    }

    public function estatusEDocument($username, $password, $numeroOperacion, $certificado, $cadenaOriginal, $firma) {
        $xml = "<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:dig=\"http://www.ventanillaunica.gob.mx/aga/digitalizar/ws/oxml/DigitalizarDocumento\" xmlns:res=\"http://www.ventanillaunica.gob.mx/common/ws/oxml/respuesta\">
   <soapenv:Header>
       <wsse:Security soapenv:mustUnderstand=\"1\" xmlns:wsse=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd\">
               <wsse:UsernameToken>
                       <wsse:Username>{$username}</wsse:Username>
                       <wsse:Password Type=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText\">{$password}</wsse:Password>
               </wsse:UsernameToken>
       </wsse:Security>
  </soapenv:Header>
   <soapenv:Body>
      <dig:consultaDigitalizarDocumentoServiceRequest>
         <dig:numeroOperacion>{$numeroOperacion}</dig:numeroOperacion>
         <dig:peticionBase>
            <res:firmaElectronica>
               <res:certificado>{$certificado}</res:certificado>
               <res:cadenaOriginal>{$cadenaOriginal}</res:cadenaOriginal>
               <res:firma>{$firma}</res:firma>
            </res:firmaElectronica>
         </dig:peticionBase>
      </dig:consultaDigitalizarDocumentoServiceRequest>
   </soapenv:Body>
</soapenv:Envelope>";
        return $xml;
    }

    public function consultaCove($username, $password, $certificado, $cadenaOriginal, $firma, $eDocument) {
        $xml = "<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:con=\"http://www.ventanillaunica.gob.mx/ConsultarEdocument/\" xmlns:oxml=\"http://www.ventanillaunica.gob.mx/cove/ws/oxml/\">
   <soapenv:Header>
        <wsse:Security soapenv:mustUnderstand=\"1\" xmlns:wsse=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd\">
            <wsse:UsernameToken>
                <wsse:Username>{$username}</wsse:Username>
                <wsse:Password Type=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText\">{$password}</wsse:Password>
            </wsse:UsernameToken>
        </wsse:Security>
    </soapenv:Header>
   <soapenv:Body>
      <con:ConsultarEdocumentRequest>
         <con:request>
            <con:firmaElectronica>
               <oxml:certificado>{$certificado}</oxml:certificado>
            <oxml:cadenaOriginal>{$cadenaOriginal}</oxml:cadenaOriginal>
            <oxml:firma>{$firma}</oxml:firma>
            </con:firmaElectronica>
            <con:criterioBusqueda>
               <con:eDocument>{$eDocument}</con:eDocument>               
            </con:criterioBusqueda>
         </con:request>
      </con:ConsultarEdocumentRequest>
   </soapenv:Body>
</soapenv:Envelope>";
        return $xml;
    }
    
    public function tiposDocumentos($username, $password, $certificado, $cadenaOriginal, $firma) {
        return "<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:dig=\"http://www.ventanillaunica.gob.mx/aga/digitalizar/ws/oxml/DigitalizarDocumento\" xmlns:res=\"http://www.ventanillaunica.gob.mx/common/ws/oxml/respuesta\">
   <soapenv:Header>
       <wsse:Security soapenv:mustUnderstand=\"1\" xmlns:wsse=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd\">
               <wsse:UsernameToken>
                       <wsse:Username>{$username}</wsse:Username>
                       <wsse:Password Type=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText\">{$password}</wsse:Password>
               </wsse:UsernameToken>
       </wsse:Security>
  </soapenv:Header>
   <soapenv:Body>
      <dig:consultaTipoDocumentoServiceRequest>
         <dig:peticionBase>
            <res:firmaElectronica>
               <res:certificado>{$certificado}</res:certificado>
               <res:cadenaOriginal>{$cadenaOriginal}</res:cadenaOriginal>
               <res:firma>{$firma}</res:firma>
            </res:firmaElectronica>
         </dig:peticionBase>
      </dig:consultaTipoDocumentoServiceRequest>
   </soapenv:Body>
</soapenv:Envelope>";
    }

    public function cadenaEdocument($rfcFirmante, $correoElectronico, $idTipoDocumento, $nombreDocumento, $rfcConsulta, $hash) {
        $cadena = "|{$rfcFirmante}|{$correoElectronico}|{$idTipoDocumento}|{$nombreDocumento}|{$rfcConsulta}|{$hash}|";
        return $cadena;
    }
    
    public function cadenaEdocumentNoConsulta($rfcFirmante, $correoElectronico, $idTipoDocumento, $nombreDocumento, $hash) {
        $cadena = "|{$rfcFirmante}|{$correoElectronico}|{$idTipoDocumento}|{$nombreDocumento}|{$hash}|";
        return $cadena;
    }

    protected function htmlspanishchars($str) {
        return str_replace(array("&lt;", "&gt;"), array("<", ">"), htmlentities($str, ENT_NOQUOTES, "UTF-8"));
    }

    public function html_convert_entities($string) {
        // https://gist.github.com/inanimatt/879249
        return preg_replace_callback('/&([a-zA-Z][a-zA-Z0-9]+);/S', array($this, 'convert_entity'), $string);
    }

    public function htmlSpanish($string) {
        try {            
            $decoded = html_entity_decode($string, ENT_QUOTES, "UTf-8");
            $special = array('/&amp;/', '/&Ntilde;/');
            $replace = array('&amp;', 'Ñ');
            return utf8_decode(preg_replace($special, $replace, $decoded));
        } catch (Exception $ex) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    protected function htmlNumeric() {
        $HTML401NamedToNumeric = array(
            '&nbsp;' => '&#160;', # no-break space = non-breaking space, U+00A0 ISOnum
            '&iexcl;' => '&#161;', # inverted exclamation mark, U+00A1 ISOnum
            '&cent;' => '&#162;', # cent sign, U+00A2 ISOnum
            '&pound;' => '&#163;', # pound sign, U+00A3 ISOnum
            '&curren;' => '&#164;', # currency sign, U+00A4 ISOnum
            '&yen;' => '&#165;', # yen sign = yuan sign, U+00A5 ISOnum
            '&brvbar;' => '&#166;', # broken bar = broken vertical bar, U+00A6 ISOnum
            '&sect;' => '&#167;', # section sign, U+00A7 ISOnum
            '&uml;' => '&#168;', # diaeresis = spacing diaeresis, U+00A8 ISOdia
            '&copy;' => '&#169;', # copyright sign, U+00A9 ISOnum
            '&ordf;' => '&#170;', # feminine ordinal indicator, U+00AA ISOnum
            '&laquo;' => '&#171;', # left-pointing double angle quotation mark = left pointing guillemet, U+00AB ISOnum
            '&not;' => '&#172;', # not sign, U+00AC ISOnum
            '&shy;' => '&#173;', # soft hyphen = discretionary hyphen, U+00AD ISOnum
            '&reg;' => '&#174;', # registered sign = registered trade mark sign, U+00AE ISOnum
            '&macr;' => '&#175;', # macron = spacing macron = overline = APL overbar, U+00AF ISOdia
            '&deg;' => '&#176;', # degree sign, U+00B0 ISOnum
            '&plusmn;' => '&#177;', # plus-minus sign = plus-or-minus sign, U+00B1 ISOnum
            '&sup2;' => '&#178;', # superscript two = superscript digit two = squared, U+00B2 ISOnum
            '&sup3;' => '&#179;', # superscript three = superscript digit three = cubed, U+00B3 ISOnum
            '&acute;' => '&#180;', # acute accent = spacing acute, U+00B4 ISOdia
            '&micro;' => '&#181;', # micro sign, U+00B5 ISOnum
            '&para;' => '&#182;', # pilcrow sign = paragraph sign, U+00B6 ISOnum
            '&middot;' => '&#183;', # middle dot = Georgian comma = Greek middle dot, U+00B7 ISOnum
            '&cedil;' => '&#184;', # cedilla = spacing cedilla, U+00B8 ISOdia
            '&sup1;' => '&#185;', # superscript one = superscript digit one, U+00B9 ISOnum
            '&ordm;' => '&#186;', # masculine ordinal indicator, U+00BA ISOnum
            '&raquo;' => '&#187;', # right-pointing double angle quotation mark = right pointing guillemet, U+00BB ISOnum
            '&frac14;' => '&#188;', # vulgar fraction one quarter = fraction one quarter, U+00BC ISOnum
            '&frac12;' => '&#189;', # vulgar fraction one half = fraction one half, U+00BD ISOnum
            '&frac34;' => '&#190;', # vulgar fraction three quarters = fraction three quarters, U+00BE ISOnum
            '&iquest;' => '&#191;', # inverted question mark = turned question mark, U+00BF ISOnum
            '&Agrave;' => '&#192;', # latin capital letter A with grave = latin capital letter A grave, U+00C0 ISOlat1
            '&Aacute;' => '&#193;', # latin capital letter A with acute, U+00C1 ISOlat1
            '&Acirc;' => '&#194;', # latin capital letter A with circumflex, U+00C2 ISOlat1
            '&Atilde;' => '&#195;', # latin capital letter A with tilde, U+00C3 ISOlat1
            '&Auml;' => '&#196;', # latin capital letter A with diaeresis, U+00C4 ISOlat1
            '&Aring;' => '&#197;', # latin capital letter A with ring above = latin capital letter A ring, U+00C5 ISOlat1
            '&AElig;' => '&#198;', # latin capital letter AE = latin capital ligature AE, U+00C6 ISOlat1
            '&Ccedil;' => '&#199;', # latin capital letter C with cedilla, U+00C7 ISOlat1
            '&Egrave;' => '&#200;', # latin capital letter E with grave, U+00C8 ISOlat1
            '&Eacute;' => '&#201;', # latin capital letter E with acute, U+00C9 ISOlat1
            '&Ecirc;' => '&#202;', # latin capital letter E with circumflex, U+00CA ISOlat1
            '&Euml;' => '&#203;', # latin capital letter E with diaeresis, U+00CB ISOlat1
            '&Igrave;' => '&#204;', # latin capital letter I with grave, U+00CC ISOlat1
            '&Iacute;' => '&#205;', # latin capital letter I with acute, U+00CD ISOlat1
            '&Icirc;' => '&#206;', # latin capital letter I with circumflex, U+00CE ISOlat1
            '&Iuml;' => '&#207;', # latin capital letter I with diaeresis, U+00CF ISOlat1
            '&ETH;' => '&#208;', # latin capital letter ETH, U+00D0 ISOlat1
            '&Ntilde;' => '&#209;', # latin capital letter N with tilde, U+00D1 ISOlat1
            '&Ograve;' => '&#210;', # latin capital letter O with grave, U+00D2 ISOlat1
            '&Oacute;' => '&#211;', # latin capital letter O with acute, U+00D3 ISOlat1
            '&Ocirc;' => '&#212;', # latin capital letter O with circumflex, U+00D4 ISOlat1
            '&Otilde;' => '&#213;', # latin capital letter O with tilde, U+00D5 ISOlat1
            '&Ouml;' => '&#214;', # latin capital letter O with diaeresis, U+00D6 ISOlat1
            '&times;' => '&#215;', # multiplication sign, U+00D7 ISOnum
            '&Oslash;' => '&#216;', # latin capital letter O with stroke = latin capital letter O slash, U+00D8 ISOlat1
            '&Ugrave;' => '&#217;', # latin capital letter U with grave, U+00D9 ISOlat1
            '&Uacute;' => '&#218;', # latin capital letter U with acute, U+00DA ISOlat1
            '&Ucirc;' => '&#219;', # latin capital letter U with circumflex, U+00DB ISOlat1
            '&Uuml;' => '&#220;', # latin capital letter U with diaeresis, U+00DC ISOlat1
            '&Yacute;' => '&#221;', # latin capital letter Y with acute, U+00DD ISOlat1
            '&THORN;' => '&#222;', # latin capital letter THORN, U+00DE ISOlat1
            '&szlig;' => '&#223;', # latin small letter sharp s = ess-zed, U+00DF ISOlat1
            '&agrave;' => '&#224;', # latin small letter a with grave = latin small letter a grave, U+00E0 ISOlat1
            '&aacute;' => '&#225;', # latin small letter a with acute, U+00E1 ISOlat1
            '&acirc;' => '&#226;', # latin small letter a with circumflex, U+00E2 ISOlat1
            '&atilde;' => '&#227;', # latin small letter a with tilde, U+00E3 ISOlat1
            '&auml;' => '&#228;', # latin small letter a with diaeresis, U+00E4 ISOlat1
            '&aring;' => '&#229;', # latin small letter a with ring above = latin small letter a ring, U+00E5 ISOlat1
            '&aelig;' => '&#230;', # latin small letter ae = latin small ligature ae, U+00E6 ISOlat1
            '&ccedil;' => '&#231;', # latin small letter c with cedilla, U+00E7 ISOlat1
            '&egrave;' => '&#232;', # latin small letter e with grave, U+00E8 ISOlat1
            '&eacute;' => '&#233;', # latin small letter e with acute, U+00E9 ISOlat1
            '&ecirc;' => '&#234;', # latin small letter e with circumflex, U+00EA ISOlat1
            '&euml;' => '&#235;', # latin small letter e with diaeresis, U+00EB ISOlat1
            '&igrave;' => '&#236;', # latin small letter i with grave, U+00EC ISOlat1
            '&iacute;' => '&#237;', # latin small letter i with acute, U+00ED ISOlat1
            '&icirc;' => '&#238;', # latin small letter i with circumflex, U+00EE ISOlat1
            '&iuml;' => '&#239;', # latin small letter i with diaeresis, U+00EF ISOlat1
            '&eth;' => '&#240;', # latin small letter eth, U+00F0 ISOlat1
            '&ntilde;' => '&#241;', # latin small letter n with tilde, U+00F1 ISOlat1
            '&ograve;' => '&#242;', # latin small letter o with grave, U+00F2 ISOlat1
            '&oacute;' => '&#243;', # latin small letter o with acute, U+00F3 ISOlat1
            '&ocirc;' => '&#244;', # latin small letter o with circumflex, U+00F4 ISOlat1
            '&otilde;' => '&#245;', # latin small letter o with tilde, U+00F5 ISOlat1
            '&ouml;' => '&#246;', # latin small letter o with diaeresis, U+00F6 ISOlat1
            '&divide;' => '&#247;', # division sign, U+00F7 ISOnum
            '&oslash;' => '&#248;', # latin small letter o with stroke, = latin small letter o slash, U+00F8 ISOlat1
            '&ugrave;' => '&#249;', # latin small letter u with grave, U+00F9 ISOlat1
            '&uacute;' => '&#250;', # latin small letter u with acute, U+00FA ISOlat1
            '&ucirc;' => '&#251;', # latin small letter u with circumflex, U+00FB ISOlat1
            '&uuml;' => '&#252;', # latin small letter u with diaeresis, U+00FC ISOlat1
            '&yacute;' => '&#253;', # latin small letter y with acute, U+00FD ISOlat1
            '&thorn;' => '&#254;', # latin small letter thorn, U+00FE ISOlat1
            '&yuml;' => '&#255;', # latin small letter y with diaeresis, U+00FF ISOlat1
            '&fnof;' => '&#402;', # latin small f with hook = function = florin, U+0192 ISOtech
            '&Alpha;' => '&#913;', # greek capital letter alpha, U+0391
            '&Beta;' => '&#914;', # greek capital letter beta, U+0392
            '&Gamma;' => '&#915;', # greek capital letter gamma, U+0393 ISOgrk3
            '&Delta;' => '&#916;', # greek capital letter delta, U+0394 ISOgrk3
            '&Epsilon;' => '&#917;', # greek capital letter epsilon, U+0395
            '&Zeta;' => '&#918;', # greek capital letter zeta, U+0396
            '&Eta;' => '&#919;', # greek capital letter eta, U+0397
            '&Theta;' => '&#920;', # greek capital letter theta, U+0398 ISOgrk3
            '&Iota;' => '&#921;', # greek capital letter iota, U+0399
            '&Kappa;' => '&#922;', # greek capital letter kappa, U+039A
            '&Lambda;' => '&#923;', # greek capital letter lambda, U+039B ISOgrk3
            '&Mu;' => '&#924;', # greek capital letter mu, U+039C
            '&Nu;' => '&#925;', # greek capital letter nu, U+039D
            '&Xi;' => '&#926;', # greek capital letter xi, U+039E ISOgrk3
            '&Omicron;' => '&#927;', # greek capital letter omicron, U+039F
            '&Pi;' => '&#928;', # greek capital letter pi, U+03A0 ISOgrk3
            '&Rho;' => '&#929;', # greek capital letter rho, U+03A1
            '&Sigma;' => '&#931;', # greek capital letter sigma, U+03A3 ISOgrk3
            '&Tau;' => '&#932;', # greek capital letter tau, U+03A4
            '&Upsilon;' => '&#933;', # greek capital letter upsilon, U+03A5 ISOgrk3
            '&Phi;' => '&#934;', # greek capital letter phi, U+03A6 ISOgrk3
            '&Chi;' => '&#935;', # greek capital letter chi, U+03A7
            '&Psi;' => '&#936;', # greek capital letter psi, U+03A8 ISOgrk3
            '&Omega;' => '&#937;', # greek capital letter omega, U+03A9 ISOgrk3
            '&alpha;' => '&#945;', # greek small letter alpha, U+03B1 ISOgrk3
            '&beta;' => '&#946;', # greek small letter beta, U+03B2 ISOgrk3
            '&gamma;' => '&#947;', # greek small letter gamma, U+03B3 ISOgrk3
            '&delta;' => '&#948;', # greek small letter delta, U+03B4 ISOgrk3
            '&epsilon;' => '&#949;', # greek small letter epsilon, U+03B5 ISOgrk3
            '&zeta;' => '&#950;', # greek small letter zeta, U+03B6 ISOgrk3
            '&eta;' => '&#951;', # greek small letter eta, U+03B7 ISOgrk3
            '&theta;' => '&#952;', # greek small letter theta, U+03B8 ISOgrk3
            '&iota;' => '&#953;', # greek small letter iota, U+03B9 ISOgrk3
            '&kappa;' => '&#954;', # greek small letter kappa, U+03BA ISOgrk3
            '&lambda;' => '&#955;', # greek small letter lambda, U+03BB ISOgrk3
            '&mu;' => '&#956;', # greek small letter mu, U+03BC ISOgrk3
            '&nu;' => '&#957;', # greek small letter nu, U+03BD ISOgrk3
            '&xi;' => '&#958;', # greek small letter xi, U+03BE ISOgrk3
            '&omicron;' => '&#959;', # greek small letter omicron, U+03BF NEW
            '&pi;' => '&#960;', # greek small letter pi, U+03C0 ISOgrk3
            '&rho;' => '&#961;', # greek small letter rho, U+03C1 ISOgrk3
            '&sigmaf;' => '&#962;', # greek small letter final sigma, U+03C2 ISOgrk3
            '&sigma;' => '&#963;', # greek small letter sigma, U+03C3 ISOgrk3
            '&tau;' => '&#964;', # greek small letter tau, U+03C4 ISOgrk3
            '&upsilon;' => '&#965;', # greek small letter upsilon, U+03C5 ISOgrk3
            '&phi;' => '&#966;', # greek small letter phi, U+03C6 ISOgrk3
            '&chi;' => '&#967;', # greek small letter chi, U+03C7 ISOgrk3
            '&psi;' => '&#968;', # greek small letter psi, U+03C8 ISOgrk3
            '&omega;' => '&#969;', # greek small letter omega, U+03C9 ISOgrk3
            '&thetasym;' => '&#977;', # greek small letter theta symbol, U+03D1 NEW
            '&upsih;' => '&#978;', # greek upsilon with hook symbol, U+03D2 NEW
            '&piv;' => '&#982;', # greek pi symbol, U+03D6 ISOgrk3
            '&bull;' => '&#8226;', # bullet = black small circle, U+2022 ISOpub
            '&hellip;' => '&#8230;', # horizontal ellipsis = three dot leader, U+2026 ISOpub
            '&prime;' => '&#8242;', # prime = minutes = feet, U+2032 ISOtech
            '&Prime;' => '&#8243;', # double prime = seconds = inches, U+2033 ISOtech
            '&oline;' => '&#8254;', # overline = spacing overscore, U+203E NEW
            '&frasl;' => '&#8260;', # fraction slash, U+2044 NEW
            '&weierp;' => '&#8472;', # script capital P = power set = Weierstrass p, U+2118 ISOamso
            '&image;' => '&#8465;', # blackletter capital I = imaginary part, U+2111 ISOamso
            '&real;' => '&#8476;', # blackletter capital R = real part symbol, U+211C ISOamso
            '&trade;' => '&#8482;', # trade mark sign, U+2122 ISOnum
            '&alefsym;' => '&#8501;', # alef symbol = first transfinite cardinal, U+2135 NEW
            '&larr;' => '&#8592;', # leftwards arrow, U+2190 ISOnum
            '&uarr;' => '&#8593;', # upwards arrow, U+2191 ISOnum
            '&rarr;' => '&#8594;', # rightwards arrow, U+2192 ISOnum
            '&darr;' => '&#8595;', # downwards arrow, U+2193 ISOnum
            '&harr;' => '&#8596;', # left right arrow, U+2194 ISOamsa
            '&crarr;' => '&#8629;', # downwards arrow with corner leftwards = carriage return, U+21B5 NEW
            '&lArr;' => '&#8656;', # leftwards double arrow, U+21D0 ISOtech
            '&uArr;' => '&#8657;', # upwards double arrow, U+21D1 ISOamsa
            '&rArr;' => '&#8658;', # rightwards double arrow, U+21D2 ISOtech
            '&dArr;' => '&#8659;', # downwards double arrow, U+21D3 ISOamsa
            '&hArr;' => '&#8660;', # left right double arrow, U+21D4 ISOamsa
            '&forall;' => '&#8704;', # for all, U+2200 ISOtech
            '&part;' => '&#8706;', # partial differential, U+2202 ISOtech
            '&exist;' => '&#8707;', # there exists, U+2203 ISOtech
            '&empty;' => '&#8709;', # empty set = null set = diameter, U+2205 ISOamso
            '&nabla;' => '&#8711;', # nabla = backward difference, U+2207 ISOtech
            '&isin;' => '&#8712;', # element of, U+2208 ISOtech
            '&notin;' => '&#8713;', # not an element of, U+2209 ISOtech
            '&ni;' => '&#8715;', # contains as member, U+220B ISOtech
            '&prod;' => '&#8719;', # n-ary product = product sign, U+220F ISOamsb
            '&sum;' => '&#8721;', # n-ary sumation, U+2211 ISOamsb
            '&minus;' => '&#8722;', # minus sign, U+2212 ISOtech
            '&lowast;' => '&#8727;', # asterisk operator, U+2217 ISOtech
            '&radic;' => '&#8730;', # square root = radical sign, U+221A ISOtech
            '&prop;' => '&#8733;', # proportional to, U+221D ISOtech
            '&infin;' => '&#8734;', # infinity, U+221E ISOtech
            '&ang;' => '&#8736;', # angle, U+2220 ISOamso
            '&and;' => '&#8743;', # logical and = wedge, U+2227 ISOtech
            '&or;' => '&#8744;', # logical or = vee, U+2228 ISOtech
            '&cap;' => '&#8745;', # intersection = cap, U+2229 ISOtech
            '&cup;' => '&#8746;', # union = cup, U+222A ISOtech
            '&int;' => '&#8747;', # integral, U+222B ISOtech
            '&there4;' => '&#8756;', # therefore, U+2234 ISOtech
            '&sim;' => '&#8764;', # tilde operator = varies with = similar to, U+223C ISOtech
            '&cong;' => '&#8773;', # approximately equal to, U+2245 ISOtech
            '&asymp;' => '&#8776;', # almost equal to = asymptotic to, U+2248 ISOamsr
            '&ne;' => '&#8800;', # not equal to, U+2260 ISOtech
            '&equiv;' => '&#8801;', # identical to, U+2261 ISOtech
            '&le;' => '&#8804;', # less-than or equal to, U+2264 ISOtech
            '&ge;' => '&#8805;', # greater-than or equal to, U+2265 ISOtech
            '&sub;' => '&#8834;', # subset of, U+2282 ISOtech
            '&sup;' => '&#8835;', # superset of, U+2283 ISOtech
            '&nsub;' => '&#8836;', # not a subset of, U+2284 ISOamsn
            '&sube;' => '&#8838;', # subset of or equal to, U+2286 ISOtech
            '&supe;' => '&#8839;', # superset of or equal to, U+2287 ISOtech
            '&oplus;' => '&#8853;', # circled plus = direct sum, U+2295 ISOamsb
            '&otimes;' => '&#8855;', # circled times = vector product, U+2297 ISOamsb
            '&perp;' => '&#8869;', # up tack = orthogonal to = perpendicular, U+22A5 ISOtech
            '&sdot;' => '&#8901;', # dot operator, U+22C5 ISOamsb
            '&lceil;' => '&#8968;', # left ceiling = apl upstile, U+2308 ISOamsc
            '&rceil;' => '&#8969;', # right ceiling, U+2309 ISOamsc
            '&lfloor;' => '&#8970;', # left floor = apl downstile, U+230A ISOamsc
            '&rfloor;' => '&#8971;', # right floor, U+230B ISOamsc
            '&lang;' => '&#9001;', # left-pointing angle bracket = bra, U+2329 ISOtech
            '&rang;' => '&#9002;', # right-pointing angle bracket = ket, U+232A ISOtech
            '&loz;' => '&#9674;', # lozenge, U+25CA ISOpub
            '&spades;' => '&#9824;', # black spade suit, U+2660 ISOpub
            '&clubs;' => '&#9827;', # black club suit = shamrock, U+2663 ISOpub
            '&hearts;' => '&#9829;', # black heart suit = valentine, U+2665 ISOpub
            '&diams;' => '&#9830;', # black diamond suit, U+2666 ISOpub
            '&quot;' => '&#34;', # quotation mark = APL quote, U+0022 ISOnum
            '&amp;' => '&#38;', # ampersand, U+0026 ISOnum
            '&lt;' => '&#60;', # less-than sign, U+003C ISOnum
            '&gt;' => '&#62;', # greater-than sign, U+003E ISOnum
            '&OElig;' => '&#338;', # latin capital ligature OE, U+0152 ISOlat2
            '&oelig;' => '&#339;', # latin small ligature oe, U+0153 ISOlat2
            '&Scaron;' => '&#352;', # latin capital letter S with caron, U+0160 ISOlat2
            '&scaron;' => '&#353;', # latin small letter s with caron, U+0161 ISOlat2
            '&Yuml;' => '&#376;', # latin capital letter Y with diaeresis, U+0178 ISOlat2
            '&circ;' => '&#710;', # modifier letter circumflex accent, U+02C6 ISOpub
            '&tilde;' => '&#732;', # small tilde, U+02DC ISOdia
            '&ensp;' => '&#8194;', # en space, U+2002 ISOpub
            '&emsp;' => '&#8195;', # em space, U+2003 ISOpub
            '&thinsp;' => '&#8201;', # thin space, U+2009 ISOpub
            '&zwnj;' => '&#8204;', # zero width non-joiner, U+200C NEW RFC 2070
            '&zwj;' => '&#8205;', # zero width joiner, U+200D NEW RFC 2070
            '&lrm;' => '&#8206;', # left-to-right mark, U+200E NEW RFC 2070
            '&rlm;' => '&#8207;', # right-to-left mark, U+200F NEW RFC 2070
            '&ndash;' => '&#8211;', # en dash, U+2013 ISOpub
            '&mdash;' => '&#8212;', # em dash, U+2014 ISOpub
            '&lsquo;' => '&#8216;', # left single quotation mark, U+2018 ISOnum
            '&rsquo;' => '&#8217;', # right single quotation mark, U+2019 ISOnum
            '&sbquo;' => '&#8218;', # single low-9 quotation mark, U+201A NEW
            '&ldquo;' => '&#8220;', # left double quotation mark, U+201C ISOnum
            '&rdquo;' => '&#8221;', # right double quotation mark, U+201D ISOnum
            '&bdquo;' => '&#8222;', # double low-9 quotation mark, U+201E NEW
            '&dagger;' => '&#8224;', # dagger, U+2020 ISOpub
            '&Dagger;' => '&#8225;', # double dagger, U+2021 ISOpub
            '&permil;' => '&#8240;', # per mille sign, U+2030 ISOtech
            '&lsaquo;' => '&#8249;', # single left-pointing angle quotation mark, U+2039 ISO proposed
            '&rsaquo;' => '&#8250;', # single right-pointing angle quotation mark, U+203A ISO proposed
            '&euro;' => '&#8364;', # euro sign, U+20AC NEW
        );
        return $HTML401NamedToNumeric;
    }

    public function convert_entity($matches) {
        // https://gist.github.com/inanimatt/879249
        static $table = array('quot' => '&#34;',
            'amp' => '&#38;',
            'lt' => '&#60;',
            'gt' => '&#62;',
            'OElig' => '&#338;',
            'oelig' => '&#339;',
            'Scaron' => '&#352;',
            'scaron' => '&#353;',
            'Yuml' => '&#376;',
            'circ' => '&#710;',
            'tilde' => '&#732;',
            'ensp' => '&#8194;',
            'emsp' => '&#8195;',
            'thinsp' => '&#8201;',
            'zwnj' => '&#8204;',
            'zwj' => '&#8205;',
            'lrm' => '&#8206;',
            'rlm' => '&#8207;',
            'ndash' => '&#8211;',
            'mdash' => '&#8212;',
            'lsquo' => '&#8216;',
            'rsquo' => '&#8217;',
            'sbquo' => '&#8218;',
            'ldquo' => '&#8220;',
            'rdquo' => '&#8221;',
            'bdquo' => '&#8222;',
            'dagger' => '&#8224;',
            'Dagger' => '&#8225;',
            'permil' => '&#8240;',
            'lsaquo' => '&#8249;',
            'rsaquo' => '&#8250;',
            'euro' => '&#8364;',
            'fnof' => '&#402;',
            'Alpha' => '&#913;',
            'Beta' => '&#914;',
            'Gamma' => '&#915;',
            'Delta' => '&#916;',
            'Epsilon' => '&#917;',
            'Zeta' => '&#918;',
            'Eta' => '&#919;',
            'Theta' => '&#920;',
            'Iota' => '&#921;',
            'Kappa' => '&#922;',
            'Lambda' => '&#923;',
            'Mu' => '&#924;',
            'Nu' => '&#925;',
            'Xi' => '&#926;',
            'Omicron' => '&#927;',
            'Pi' => '&#928;',
            'Rho' => '&#929;',
            'Sigma' => '&#931;',
            'Tau' => '&#932;',
            'Upsilon' => '&#933;',
            'Phi' => '&#934;',
            'Chi' => '&#935;',
            'Psi' => '&#936;',
            'Omega' => '&#937;',
            'alpha' => '&#945;',
            'beta' => '&#946;',
            'gamma' => '&#947;',
            'delta' => '&#948;',
            'epsilon' => '&#949;',
            'zeta' => '&#950;',
            'eta' => '&#951;',
            'theta' => '&#952;',
            'iota' => '&#953;',
            'kappa' => '&#954;',
            'lambda' => '&#955;',
            'mu' => '&#956;',
            'nu' => '&#957;',
            'xi' => '&#958;',
            'omicron' => '&#959;',
            'pi' => '&#960;',
            'rho' => '&#961;',
            'sigmaf' => '&#962;',
            'sigma' => '&#963;',
            'tau' => '&#964;',
            'upsilon' => '&#965;',
            'phi' => '&#966;',
            'chi' => '&#967;',
            'psi' => '&#968;',
            'omega' => '&#969;',
            'thetasym' => '&#977;',
            'upsih' => '&#978;',
            'piv' => '&#982;',
            'bull' => '&#8226;',
            'hellip' => '&#8230;',
            'prime' => '&#8242;',
            'Prime' => '&#8243;',
            'oline' => '&#8254;',
            'frasl' => '&#8260;',
            'weierp' => '&#8472;',
            'image' => '&#8465;',
            'real' => '&#8476;',
            'trade' => '&#8482;',
            'alefsym' => '&#8501;',
            'larr' => '&#8592;',
            'uarr' => '&#8593;',
            'rarr' => '&#8594;',
            'darr' => '&#8595;',
            'harr' => '&#8596;',
            'crarr' => '&#8629;',
            'lArr' => '&#8656;',
            'uArr' => '&#8657;',
            'rArr' => '&#8658;',
            'dArr' => '&#8659;',
            'hArr' => '&#8660;',
            'forall' => '&#8704;',
            'part' => '&#8706;',
            'exist' => '&#8707;',
            'empty' => '&#8709;',
            'nabla' => '&#8711;',
            'isin' => '&#8712;',
            'notin' => '&#8713;',
            'ni' => '&#8715;',
            'prod' => '&#8719;',
            'sum' => '&#8721;',
            'minus' => '&#8722;',
            'lowast' => '&#8727;',
            'radic' => '&#8730;',
            'prop' => '&#8733;',
            'infin' => '&#8734;',
            'ang' => '&#8736;',
            'and' => '&#8743;',
            'or' => '&#8744;',
            'cap' => '&#8745;',
            'cup' => '&#8746;',
            'int' => '&#8747;',
            'there4' => '&#8756;',
            'sim' => '&#8764;',
            'cong' => '&#8773;',
            'asymp' => '&#8776;',
            'ne' => '&#8800;',
            'equiv' => '&#8801;',
            'le' => '&#8804;',
            'ge' => '&#8805;',
            'sub' => '&#8834;',
            'sup' => '&#8835;',
            'nsub' => '&#8836;',
            'sube' => '&#8838;',
            'supe' => '&#8839;',
            'oplus' => '&#8853;',
            'otimes' => '&#8855;',
            'perp' => '&#8869;',
            'sdot' => '&#8901;',
            'lceil' => '&#8968;',
            'rceil' => '&#8969;',
            'lfloor' => '&#8970;',
            'rfloor' => '&#8971;',
            'lang' => '&#9001;',
            'rang' => '&#9002;',
            'loz' => '&#9674;',
            'spades' => '&#9824;',
            'clubs' => '&#9827;',
            'hearts' => '&#9829;',
            'diams' => '&#9830;',
            'nbsp' => '&#160;',
            'iexcl' => '&#161;',
            'cent' => '&#162;',
            'pound' => '&#163;',
            'curren' => '&#164;',
            'yen' => '&#165;',
            'brvbar' => '&#166;',
            'sect' => '&#167;',
            'uml' => '&#168;',
            'copy' => '&#169;',
            'ordf' => '&#170;',
            'laquo' => '&#171;',
            'not' => '&#172;',
            'shy' => '&#173;',
            'reg' => '&#174;',
            'macr' => '&#175;',
            'deg' => '&#176;',
            'plusmn' => '&#177;',
            'sup2' => '&#178;',
            'sup3' => '&#179;',
            'acute' => '&#180;',
            'micro' => '&#181;',
            'para' => '&#182;',
            'middot' => '&#183;',
            'cedil' => '&#184;',
            'sup1' => '&#185;',
            'ordm' => '&#186;',
            'raquo' => '&#187;',
            'frac14' => '&#188;',
            'frac12' => '&#189;',
            'frac34' => '&#190;',
            'iquest' => '&#191;',
            'Agrave' => '&#192;',
            'Aacute' => '&#193;',
            'Acirc' => '&#194;',
            'Atilde' => '&#195;',
            'Auml' => '&#196;',
            'Aring' => '&#197;',
            'AElig' => '&#198;',
            'Ccedil' => '&#199;',
            'Egrave' => '&#200;',
            'Eacute' => '&#201;',
            'Ecirc' => '&#202;',
            'Euml' => '&#203;',
            'Igrave' => '&#204;',
            'Iacute' => '&#205;',
            'Icirc' => '&#206;',
            'Iuml' => '&#207;',
            'ETH' => '&#208;',
            'Ntilde' => '&#209;',
            'Ograve' => '&#210;',
            'Oacute' => '&#211;',
            'Ocirc' => '&#212;',
            'Otilde' => '&#213;',
            'Ouml' => '&#214;',
            'times' => '&#215;',
            'Oslash' => '&#216;',
            'Ugrave' => '&#217;',
            'Uacute' => '&#218;',
            'Ucirc' => '&#219;',
            'Uuml' => '&#220;',
            'Yacute' => '&#221;',
            'THORN' => '&#222;',
            'szlig' => '&#223;',
            'agrave' => '&#224;',
            'aacute' => '&#225;',
            'acirc' => '&#226;',
            'atilde' => '&#227;',
            'auml' => '&#228;',
            'aring' => '&#229;',
            'aelig' => '&#230;',
            'ccedil' => '&#231;',
            'egrave' => '&#232;',
            'eacute' => '&#233;',
            'ecirc' => '&#234;',
            'euml' => '&#235;',
            'igrave' => '&#236;',
            'iacute' => '&#237;',
            'icirc' => '&#238;',
            'iuml' => '&#239;',
            'eth' => '&#240;',
            'ntilde' => '&#241;',
            'ograve' => '&#242;',
            'oacute' => '&#243;',
            'ocirc' => '&#244;',
            'otilde' => '&#245;',
            'ouml' => '&#246;',
            'divide' => '&#247;',
            'oslash' => '&#248;',
            'ugrave' => '&#249;',
            'uacute' => '&#250;',
            'ucirc' => '&#251;',
            'uuml' => '&#252;',
            'yacute' => '&#253;',
            'thorn' => '&#254;',
            'yuml' => '&#255;'
        );
        // Entity not found? Destroy it.
        return isset($table[$matches[1]]) ? $table[$matches[1]] : '';
    }

    public function identificadorDesc($iden) {
        $misc = new OAQ_Misc();
        return $misc->identificadorDesc($iden);
    }

    public function number($value) {
        return number_format($value, 3, '.', ',');
    }

    public function number4($value) {
        return number_format($value, 4, '.', ',');
    }

    public function number6($value) {
        return number_format($value, 6, '.', ',');
    }

    public function printCove($id, $download, $view, $save, $debug = null) {
        try {            
            require_once "tcpdf/mytcpdf.php";
            error_reporting(E_ALL & ~E_NOTICE);
            $vucem = new OAQ_Vucem();
            $config = new Application_Model_ConfigMapper();
            $vucemSol = new Vucem_Model_VucemSolicitudesMapper();
            $xml = $vucemSol->obtenerSolicitudPorId($id);
            $fechas = array(
                "enviado" => $xml["enviado"],
                "actualizado" => $xml["actualizado"]
            );
            $array = $vucem->xmlStrToArray($xml["xml"]);
            if (!isset($xml) || empty($array)) {
                return false;
            }
            $firmante = $array["Header"]["Security"]["UsernameToken"]["Username"];

            if (isset($array["Body"]["solicitarRecibirCoveServicio"])) {
                $data = $array["Body"]["solicitarRecibirCoveServicio"]["comprobantes"];
            } elseif (isset($array["Body"]["solicitarRecibirRelacionFacturasIAServicio"])) {
                $data = $array["Body"]["solicitarRecibirRelacionFacturasIAServicio"]["comprobantes"];
            }
            unset($array);
            $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'Letter', true, 'UTF-8', false);
            $pdf->setFontSubsetting(false);
            $pdf->setCellHeightRatio(1);
            $tagvs = array(
                'p' => array(0 => array('h' => 0, 'n' => 0), 1 => array('h' => 0, 'n' => 0)),
                'h3' => array(0 => array('h' => 0, 'n' => 0), 1 => array('h' => 0, 'n' => 0)),
                'table' => array(0 => array('h' => 0, 'n' => 0), 1 => array('h' => 0, 'n' => 0)),
            );
            $pdf->setHtmlVSpace($tagvs);
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('Jaime E. Valdez');
            $pdf->SetTitle('EDOC');
            $pdf->SetSubject('EDOC');
            $pdf->SetKeywords('EDOCUMENT');
            $appconfig = new Application_Model_ConfigMapper();
            $pdf->setHeaderData($appconfig->getParam('tcpdf-logo'), "35", "ACUSE COVE", $xml["cove"], array(0, 0, 0), array(150, 150, 150));
            $pdf->setFooterData(array(0, 0, 0), array(0, 0, 0));
            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
            if (file_exists(dirname(__FILE__) . '/lang/es.php')) {
                require_once(dirname(__FILE__) . '/lang/es.php');
                $pdf->setLanguageArray($l);
            }
            $pdf->setFontSubsetting(true);
            $pdf->SetFont('helvetica', 'C', 8);
            $pdf->AddPage();

            switch ((int) $data["tipoFigura"]) {
                case 1:
                    $tipoFigura = 'AGENTE ADUANAL';
                    break;
                case 2:
                    $tipoFigura = 'APODERADO ADUANAL';
                    break;
                case 3:
                    $tipoFigura = 'MANDATARIO';
                    break;
                case 4:
                    $tipoFigura = 'EXPORTADOR';
                    break;
                case 5:
                    $tipoFigura = 'IMPORTADOR';
                    break;
            }
            $thtitle = 'background-color: #e3e3e3; font-weight: bold; border: 1px #f1f1f1 solid; padding: 2px;';
            $thsec = 'background-color: #444; font-weight: bold; border: 1px #f1f1f1 solid; color: #fff; text-align:center;';
            $tdhl = 'background-color: none; font-weight: bold; width: 250px; border: 1px #f1f1f1 solid;';
            $tdn = 'background-color: none; border: 1px #f1f1f1 solid;';

            $html = '<h3 style="text-align:center; line-height: 12px; margin:0; padding: 0;">DATOS DEL COMPROBANTE</h3>'
                    . "<p style=\"text-align:center; line-height: 12px; margin:0; padding: 0;\"><strong>REFERENCIA:</strong> {$xml["referencia"]}, <strong>PEDIMENTO:</strong> {$xml["pedimento"]}, <strong>SOLICITUD:</strong> {$xml["solicitud"]}</p>"
                    . '<table>'
                    . '<tr>'
                    . '<th style="' . $thtitle . '">TIPO DE OPERACIÓN</th>'
                    . '<th style="' . $thtitle . '">RELACIÓN DE FACTURAS</th>'
                    . '<th style="' . $thtitle . '">NO. DE FACTURA</th>'
                    . '</tr>'
                    . '<tr>'
                    . '<td style="' . $tdn . '">' . (($data["tipoOperacion"] == 'TOCE.EXP') ? 'EXPORTACIÓN' : 'IMPORTACIÓN') . '</td>'
                    . '<td style="' . $tdn . '">' . ((isset($data["numeroRelacionFacturas"])) ? 'CON RELACIÓN DE FACTURAS' : 'SIN RELACIÓN DE FACTURAS') . '</td>'
                    . '<td style="' . $tdn . '">' . $data["numeroFacturaOriginal"] . '</td>'
                    . '</tr>'
                    . '<tr>'
                    . '<th style="' . $thtitle . '">TIPO DE FIGURA</th>'
                    . '<th colspan="2" style="' . $thtitle . '">FECHA EXP.</th>'
                    . '</tr>'
                    . '<tr>'
                    . '<td style="' . $tdn . '">' . $tipoFigura . '</td>'
                    . '<td colspan="2" style="' . $tdn . '">' . $data["fechaExpedicion"] . '</td>'
                    . '</tr>'
                    . '<tr>'
                    . '<th colspan="3" style="' . $thtitle . '">OBERVACIONES</th>'
                    . '</tr>'
                    . '<tr>'
                    . '<td colspan="3" style="' . $tdn . '">' . (isset($data["observaciones"]) ? $data["observaciones"] : '&nbsp;') . '</td>'
                    . '</tr>'
                    . '</table>';

            $html .= '<table>'
                    . '<tr>
                        <th style="' . $thtitle . '">SUBDIVISIÓN</th>
                        <th style="' . $thtitle . '">CERTIFICADO DE ORIGEN</th>
                        <th style="' . $thtitle . '">NO. DE EXPORTADOR AUTORIZADO</th>
                        </tr>'
                    . '<tr>'
                    . '<td style="' . $tdn . '">' . ( (isset($data["factura"]["subdivision"]) && (int)$data["factura"]["subdivision"] != 0) ? 'CON SUBDIVISIÓN' : 'SIN SUBDIVISIÓN' ) . '</td>
                        <td style="' . $tdn . '">' . ( (isset($data["factura"]["certificadoOrigen"]) && $data["factura"]["certificadoOrigen"] != '0' && !is_array($data["factura"]["certificadoOrigen"])) ? "SI FUNGE COMO CERTIFICADO DE ORIGEN" : 'NO FUNGE COMO CERTIFICADO DE ORIGEN' ) . '</td>
                        <td style="' . $tdn . '">' . ( (isset($data["factura"]["numeroExportadorAutorizado"]) && $data["factura"]["numeroExportadorAutorizado"] != '') ? $data["factura"]["numeroExportadorAutorizado"] : '' ) . '</td>'
                    . '</tr>'
                    . '</table>';

            $consulta = '';
            if (is_array($data["rfcConsulta"])) {
                foreach ($data["rfcConsulta"] as $rfcConsulta) {
                    $consulta .= "<tr><td style=\"{$tdn}\">{$rfcConsulta}</td></tr>";
                }
            } else {
                $consulta = "<tr><td style=\"{$tdn}\">{$data["rfcConsulta"]}</td></tr>";
            }
            $mppr = new Vucem_Model_VucemPaisesMapper();
            $html .= '<br /> <br /><table>'
                    . '<tr>'
                    . '<th style="' . $thtitle . '">RFC DE CONSULTA</th>'
                    . '</tr>'
                    . $consulta
                    . '<tr>'
                    . '<th style="' . $thtitle . '">PATENTE ADUANAL</th>'
                    . '</tr>'
                    . '<tr>'
                    . "<td style=\"{$tdn}\">" . ((isset($data["patenteAduanal"])) ? $data["patenteAduanal"] : '&nbsp;') . "</td>"
                    . '</tr>'
                    . '</table>';
            $html .= '<br /><br /><table>'
                    . '<tr>'
                    . '<th colspan="4" style="' . $thsec . '">DATOS DEL EMISOR</th>'
                    . '</tr>'
                    . '<tr>
                        <th style="' . $thtitle . '">IDENTIFICADOR EMISOR</th>
                        <th style="' . $thtitle . '" colspan="3">TAX ID/RFC/CURP</th>
                    </tr>
                    <tr>
                        <td style="' . $tdn . '">' . ( isset($data["emisor"]["tipoIdentificador"]) ? $this->identificadorDesc($data["emisor"]["tipoIdentificador"]) : '&nbsp;' ) . '</td>
                        <td style="' . $tdn . '" colspan="3">' . ( (isset($data["emisor"]["identificacion"]) && $data["emisor"]["identificacion"] != '0') ? $data["emisor"]["identificacion"] : '&nbsp;' ) . '</td>
                    </tr>
                    <tr>
                        <th style="' . $thtitle . '">NOMBRES(S) O RAZÓN SOCIAL</th>
                        <th style="' . $thtitle . '">APELLIDO PATERNO</th>
                        <th style="' . $thtitle . '" colspan="2">APELLIDO MATERNO</th>
                    </tr>
                    <tr>
                        <td style="' . $tdn . '">' . ( (isset($data["emisor"]["nombre"]) && $data["emisor"]["nombre"] != '') ? $this->utf8Fix($data["emisor"]["nombre"]) : '&nbsp;' ) . '</td>
                        <td style="' . $tdn . '">&nbsp;</td>
                        <td style="' . $tdn . '" colspan="2">&nbsp;</td>
                    </tr>
                    <tr>
                        <th style="' . $thtitle . '">CALLE</th>
                        <th style="' . $thtitle . '">NUM. EXTERIOR</th>
                        <th style="' . $thtitle . '">NUM. INTERIOR</th>
                        <th style="' . $thtitle . '">CÓDIGO POSTAL</th>
                    </tr>
                    <tr>
                        <td style="' . $tdn . '">' . ( (isset($data["emisor"]["domicilio"]["calle"]) && $data["emisor"]["domicilio"]["calle"] != '') ? $this->utf8Fix($data["emisor"]["domicilio"]["calle"]) : '&nbsp;' ) . '</td>
                        <td style="' . $tdn . '">' . ( (isset($data["emisor"]["domicilio"]["numeroExterior"]) && $data["emisor"]["domicilio"]["numeroExterior"] != '') ? $this->utf8Fix($data["emisor"]["domicilio"]["numeroExterior"]) : '&nbsp;' ) . '</td>
                        <td style="' . $tdn . '">' . ( (isset($data["emisor"]["domicilio"]["numeroInterior"]) && $data["emisor"]["domicilio"]["numeroInterior"] != '') ? $this->utf8Fix($data["emisor"]["domicilio"]["numeroInterior"]) : '&nbsp;' ) . '</td>
                        <td style="' . $tdn . '">' . ( (isset($data["emisor"]["domicilio"]["codigoPostal"]) && $data["emisor"]["domicilio"]["codigoPostal"] != '') ? $this->utf8Fix($data["emisor"]["domicilio"]["codigoPostal"]) : '&nbsp;' ) . '</td>
                    </tr>
                    <tr>
                        <th style="' . $thtitle . '">COLONIA</th>
                        <th style="' . $thtitle . '" colspan="3">LOCALIDAD</th>
                    </tr>
                    <tr>
                        <td style="' . $tdn . '">' . ( (isset($data["emisor"]["domicilio"]["colonia"]) && $data["emisor"]["domicilio"]["colonia"] != '') ? $this->utf8Fix($data["emisor"]["domicilio"]["colonia"]) : '&nbsp;' ) . '</td>
                        <td style="' . $tdn . '" colspan="3">' . ( (isset($data["emisor"]["domicilio"]["localidad"]) && $data["emisor"]["domicilio"]["localidad"] != '') ? $this->utf8Fix($data["emisor"]["domicilio"]["localidad"]) : '&nbsp;' ) . '</td>
                    </tr>
                    <tr>
                        <th style="' . $thtitle . '">MUNICIPIO</th>
                        <th style="' . $thtitle . '" colspan="3">ENTIDAD FEDERATIVA (ESTADO)</th>
                    </tr>
                    <tr>
                        <td style="' . $tdn . '">' . ( (isset($data["emisor"]["domicilio"]["municipio"]) && $data["emisor"]["domicilio"]["municipio"] != '') ? $this->utf8Fix($data["emisor"]["domicilio"]["municipio"]) : '&nbsp;' ) . '</td>
                        <td style="' . $tdn . '" colspan="3">' . ( (isset($data["emisor"]["domicilio"]["entidadFederativa"]) && $data["emisor"]["domicilio"]["entidadFederativa"] != '') ? $this->utf8Fix($data["emisor"]["domicilio"]["entidadFederativa"]) : '&nbsp;' ) . '</td>
                    </tr>
                    <tr>
                        <th style="' . $thtitle . '" colspan="4">PAÍS</th>
                    </tr>
                    <tr>
                        <td style="' . $tdn . '" colspan="4">' . ( (isset($data["emisor"]["domicilio"]["pais"]) && $data["emisor"]["domicilio"]["pais"] != '') ? $mppr->getName($data["emisor"]["domicilio"]["pais"]) : '&nbsp;' ) . '</td>
                    </tr>'
                    . '</table>';

            $html .= '<br /><br /><table>'
                    . '<tr>'
                    . '<th colspan="4" style="' . $thsec . '">DATOS DEL DESTINATARIO</th>'
                    . '</tr>'
                    . '<tr>
                        <th style="' . $thtitle . '">IDENTIFICADOR DESTINATARIO</th>
                        <th style="' . $thtitle . '" colspan="3">TAX ID/RFC/CURP</th>                
                    </tr>
                    <tr>
                        <td style="' . $tdn . '">' . ( (isset($data["destinatario"]["tipoIdentificador"])) ? $this->identificadorDesc($data["destinatario"]["tipoIdentificador"]) : '&nbsp;' ) . '</td>
                        <td style="' . $tdn . '" colspan="3">' . ( (isset($data["destinatario"]["identificacion"]) && $data["destinatario"]["identificacion"] != '') ? $data["destinatario"]["identificacion"] : '&nbsp;' ) . '</td>
                    </tr>
                    <tr>
                        <th style="' . $thtitle . '">NOMBRES(S) O RAZÓN SOCIAL</th>
                        <th style="' . $thtitle . '">APELLIDO PATERNO</th>
                        <th style="' . $thtitle . '" colspan="2">APELLIDO MATERNO</th>
                    </tr>
                    <tr>
                        <td style="' . $tdn . '">' . ( (isset($data["destinatario"]["nombre"]) && $data["destinatario"]["nombre"] != '') ? $this->utf8Fix($data["destinatario"]["nombre"]) : '&nbsp;' ) . '</td>
                        <td style="' . $tdn . '">&nbsp;</td>
                        <td style="' . $tdn . '" colspan="2">&nbsp;</td>
                    </tr>
                    <tr>
                        <th style="' . $thtitle . '">CALLE</th>
                        <th style="' . $thtitle . '">NUM. EXTERIOR</th>
                        <th style="' . $thtitle . '">NUM. INTERIOR</th>
                        <th style="' . $thtitle . '">CÓDIGO POSTAL postal</th>
                    </tr>
                    <tr>
                        <td style="' . $tdn . '">' . ( (isset($data["destinatario"]["domicilio"]["calle"]) && $data["destinatario"]["domicilio"]["calle"] != '') ? $this->utf8Fix($data["destinatario"]["domicilio"]["calle"]) : '&nbsp;' ) . '</td>
                        <td style="' . $tdn . '">' . ( (isset($data["destinatario"]["domicilio"]["numeroExterior"]) && $data["destinatario"]["domicilio"]["numeroExterior"] != '') ? $this->utf8Fix($data["destinatario"]["domicilio"]["numeroExterior"]) : '&nbsp;' ) . '</td>
                        <td style="' . $tdn . '">' . ( (isset($data["destinatario"]["domicilio"]["numeroInterior"]) && $data["destinatario"]["domicilio"]["numeroInterior"] != '') ? $this->utf8Fix($data["destinatario"]["domicilio"]["numeroInterior"]) : '&nbsp;' ) . '</td>
                        <td style="' . $tdn . '">' . ( (isset($data["destinatario"]["domicilio"]["codigoPostal"]) && $data["destinatario"]["domicilio"]["codigoPostal"] != '') ? $this->utf8Fix($data["destinatario"]["domicilio"]["codigoPostal"]) : '&nbsp;' ) . '</td>
                    </tr>
                    <tr>
                        <th style="' . $thtitle . '">COLONIA</th>
                        <th style="' . $thtitle . '" colspan="3">LOCALIDAD</th>
                    </tr>
                    <tr>
                        <td style="' . $tdn . '">' . ( (isset($data["destinatario"]["domicilio"]["colonia"]) && $data["destinatario"]["domicilio"]["colonia"] != '') ? $this->utf8Fix($data["destinatario"]["domicilio"]["colonia"]) : '&nbsp;' ) . '</td>
                        <td style="' . $tdn . '" colspan="3">' . ( (isset($data["destinatario"]["domicilio"]["localidad"]) && $data["destinatario"]["domicilio"]["localidad"] != '') ? $this->utf8Fix($data["destinatario"]["domicilio"]["localidad"]) : '&nbsp;' ) . '</td>
                    </tr>
                    <tr>
                        <th style="' . $thtitle . '">MUNICIPIO</th>
                        <th style="' . $thtitle . '" colspan="3">ENTIDAD FEDERATIVA (ESTADO)</th>
                    </tr>
                    <tr>        
                        <td style="' . $tdn . '">' . ((isset($data["destinatario"]["domicilio"]["municipio"]) && $data["destinatario"]["domicilio"]["municipio"] != '') ? $this->utf8Fix($data["destinatario"]["domicilio"]["municipio"]) : '&nbsp;' ) . '</td>
                        <td style="' . $tdn . '" colspan="3">' . ( (isset($data["destinatario"]["domicilio"]["entidadFederativa"]) && $data["destinatario"]["domicilio"]["entidadFederativa"] != '') ? $this->utf8Fix($data["destinatario"]["domicilio"]["entidadFederativa"]) : '&nbsp;' ) . '</td>
                    </tr>
                    <tr>
                        <th style="' . $thtitle . '" colspan="4">PAÍS</th>
                    </tr>
                    <tr>
                        <td style="' . $tdn . '" colspan="4">' . ( (isset($data["destinatario"]["domicilio"]["pais"]) && $data["destinatario"]["domicilio"]["pais"] != '') ? $this->utf8Fix($mppr->getName($data["destinatario"]["domicilio"]["pais"])) : '&nbsp;' ) . '</td>
                    </tr>'
                    . '</table>';
            $pdf->writeHTML($html, true, false, true, false, ''); // EMISOR - DESTINATARIO
            $html = '<table>'
                    . '<thead><tr>'
                    . '<th colspan="8" style="' . $thsec . '">DATOS DE LA MERCANCIA</th>'
                    . '</tr>'
                    . '<tr>
                        <th style="' . $thtitle . ' width:157px;">DESCRIPCION GENERICA</th>
                        <th style="' . $thtitle . '">NUM. PARTE</th>
                        <th style="' . $thtitle . ' width:45px;">MONEDA</th>
                        <th style="' . $thtitle . '">VALOR UNITARIO</th>
                        <th style="' . $thtitle . '">VALOR TOTAL</th>
                        <th style="' . $thtitle . '">VALOR USD</th>
                        <th style="' . $thtitle . ' width:45px;">OMA</th>
                        <th style="' . $thtitle . '">CANT. OMA</th>
                    </tr></thead>';
            $html .= '</table>';
            $pdf->writeHTML($html, false, false, false, false, '');
            if (isset($data["mercancias"]["descripcionGenerica"])) {
                $html = '<table>'
                        . '<tr nobr="true">
                    <td style="' . $tdn . ' width:157px;">' . ((isset($data["mercancias"]["descripcionGenerica"]) && $data["mercancias"]["descripcionGenerica"] != '') ? $this->utf8Fix($data["mercancias"]["descripcionGenerica"]) : '&nbsp;' ) . '</td>
                    <td style="' . $tdn . '">' . ((isset($data["mercancias"]["numparte"]) && $data["mercancias"]["numparte"] != '') ? $data["mercancias"]["numparte"] : '&nbsp;' ) . '</td>
                    <td style="' . $tdn . ' width:45px;">' . ((isset($data["mercancias"]["tipoMoneda"]) && $data["mercancias"]["tipoMoneda"] != '') ? $data["mercancias"]["tipoMoneda"] : '&nbsp;' ) . '</td>
                    <td style="' . $tdn . '">' . ((isset($data["mercancias"]["valorUnitario"]) && $data["mercancias"]["valorUnitario"] != '') ? '$ ' . $this->number6($data["mercancias"]["valorUnitario"]) : '&nbsp;' ) . '</td>
                    <td style="' . $tdn . '">' . ((isset($data["mercancias"]["valorTotal"]) && $data["mercancias"]["valorTotal"] != '') ? '$ ' . $this->number4($data["mercancias"]["valorTotal"]) : '&nbsp;' ) . '</td>
                    <td style="' . $tdn . '">' . ((isset($data["mercancias"]["valorDolares"]) && $data["mercancias"]["valorDolares"] != '') ? '$ ' . $this->number4($data["mercancias"]["valorDolares"]) : '&nbsp;' ) . '</td>        
                    <td style="' . $tdn . ' width:45px;">' . ((isset($data["mercancias"]["claveUnidadMedida"]) && $data["mercancias"]["claveUnidadMedida"] != '') ? $data["mercancias"]["claveUnidadMedida"] : '&nbsp;' ) . '</td>
                    <td style="' . $tdn . '">' . ((isset($data["mercancias"]["cantidad"]) && $data["mercancias"]["cantidad"] != '') ? $this->number($data["mercancias"]["cantidad"]) : '&nbsp;' ) . '</td>
                    </tr>';
                if (isset($data["mercancias"]["descripcionesEspecificas"])) {
                    $html .= '<tr nobr="true">
                            <td colspan="8" style="border: 1px #999999 solid;">
                                <table>
                                    <tr>
                                        <th style="font-weight: bold;">MARCA</th>
                                        <th style="font-weight: bold;">MODELO</th>
                                        <th style="font-weight: bold;">SUBMODELO</th>
                                        <th style="font-weight: bold;">NUM. DE SERIE</th>
                                    </tr>
                                    <tr>
                                        <td>' . ( (isset($data["mercancias"]["descripcionesEspecificas"]["marca"])) ? $data["mercancias"]["descripcionesEspecificas"]["marca"] : '&nbsp;' ) . '</td>
                                        <td>' . ( (isset($data["mercancias"]["descripcionesEspecificas"]["modelo"])) ? $data["mercancias"]["descripcionesEspecificas"]["modelo"] : '&nbsp;' ) . '</td>
                                        <td>' . ( (isset($data["mercancias"]["descripcionesEspecificas"]["subModelo"])) ? $data["mercancias"]["descripcionesEspecificas"]["subModelo"] : '&nbsp;' ) . '</td>
                                        <td>' . ( (isset($data["mercancias"]["descripcionesEspecificas"]["numeroSerie"])) ? $data["mercancias"]["descripcionesEspecificas"]["numeroSerie"] : '&nbsp;' ) . '</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>';
                }
                $html .= '</table>';
                $pdf->writeHTML($html, false, false, false, false, '');
            } elseif (isset($data["mercancias"][0]["descripcionGenerica"])) {
                foreach ($data["mercancias"] as $merc) {
                    $html = '<table>
                        <tr nobr="true">
                        <td style="' . $tdn . ' width:157px;">' . ( (isset($merc["descripcionGenerica"]) && $merc["descripcionGenerica"] != '') ? $this->utf8Fix($merc["descripcionGenerica"]) : '&nbsp;' ) . '</td>
                        <td style="' . $tdn . '">' . ( (isset($merc["numparte"]) && $merc["numparte"] != '') ? $merc["numparte"] : '&nbsp;' ) . '</td>
                        <td style="' . $tdn . ' width:45px;">' . ( (isset($merc["tipoMoneda"]) && $merc["tipoMoneda"] != '') ? $merc["tipoMoneda"] : '&nbsp;' ) . '</td>
                        <td style="' . $tdn . '">' . ( (isset($merc["valorUnitario"]) && $merc["valorUnitario"] != '') ? '$ ' . $this->number6($merc["valorUnitario"]) : '&nbsp;' ) . '</td>
                        <td style="' . $tdn . '">' . ( (isset($merc["valorTotal"]) && $merc["valorTotal"] != '') ? '$ ' . $this->number6($merc["valorTotal"]) : '&nbsp;' ) . '</td>
                        <td style="' . $tdn . '">' . ( (isset($merc["valorDolares"]) && $merc["valorDolares"] != '') ? '$ ' . $this->number4($merc["valorDolares"]) : '&nbsp;' ) . '</td>        
                        <td style="' . $tdn . ' width:45px;">' . ( (isset($merc["claveUnidadMedida"]) && $merc["claveUnidadMedida"] != '') ? $merc["claveUnidadMedida"] : '&nbsp;' ) . '</td>
                        <td style="' . $tdn . '">' . ( (isset($merc["cantidad"]) && $merc["cantidad"] != '') ? $this->number($merc["cantidad"]) : '&nbsp;' ) . '</td>
                    </tr>';
                    if (isset($merc["descripcionesEspecificas"])) {
                        $html .= '<tr nobr="true">
                            <td colspan="8" style="border: 1px #999999 solid;">
                                <table>
                                    <tr>
                                        <th style="font-weight: bold;">MARCA</th>
                                        <th style="font-weight: bold;">MODELO</th>
                                        <th style="font-weight: bold;">SUBMODELO</th>
                                        <th style="font-weight: bold;">NUM. DE SERIE</th>
                                    </tr>
                                    <tr>
                                        <td>' . ( (isset($merc["descripcionesEspecificas"]["marca"])) ? $merc["descripcionesEspecificas"]["marca"] : '&nbsp;' ) . '</td>
                                        <td>' . ( (isset($merc["descripcionesEspecificas"]["modelo"])) ? $merc["descripcionesEspecificas"]["modelo"] : '&nbsp;' ) . '</td>
                                        <td>' . ( (isset($merc["descripcionesEspecificas"]["subModelo"])) ? $merc["descripcionesEspecificas"]["subModelo"] : '&nbsp;' ) . '</td>
                                        <td>' . ( (isset($merc["descripcionesEspecificas"]["numeroSerie"])) ? $merc["descripcionesEspecificas"]["numeroSerie"] : '&nbsp;' ) . '</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>';
                    }
                    $html .= '</table>';
                    $pdf->writeHTML($html, false, false, false, false, '');
                }
            }
            $html = '<br /><br />';
            $html .= '<table>'
                    . '<tr nobr="true">
                        <th style="' . $thtitle . '">RFC FIRMANTE</th>
                    </tr>
                    <tr nobr="true">
                        <td style="' . $tdn . '" class="signature">' . $firmante . '</td>
                    </tr>'
                    . '<tr>
                        <th style="' . $thtitle . '">CADENA ORIGINAL</th>
                    </tr>
                    <tr nobr="true">
                        <td style="' . $tdn . '" class="signature">' . ((isset($data["firmaElectronica"]["cadenaOriginal"]) && $data["firmaElectronica"]["cadenaOriginal"] != '') ? wordwrap($this->utf8Fix($data["firmaElectronica"]["cadenaOriginal"]), 100, "<br />", true) : '&nbsp;' ) . '</td>
                    </tr>
                    <tr>
                        <th style="' . $thtitle . '">FIRMA</th>
                    </tr>
                    <tr nobr="true">
                        <td style="' . $tdn . '" class="signature">' . ((isset($data["firmaElectronica"]["firma"]) && $data["firmaElectronica"]["firma"] != '') ? wordwrap($data["firmaElectronica"]["firma"], 100, "<br />", true) : '&nbsp;' ) . '</td>
                    </tr>'
                    . '</table>';
            $html .= strtoupper('<br /><br /><p style="text-align: justify;">Esto es una representaciÓn grÁfica del XML de un COVE su uso es exclusivo para interpretar la informaciÓn de una forma mÁs clara, su valides ante la autoridad es de 240 días a partir de la fecha de solicitud ya que es borrado de la base de datos de Ventanilla Única, pasado este tiempo su valides es meramente histÓrica. Este acuse no sustituye el de Ventanilla Única y es generado por el sistema VUCEM OAQ como alternativa. Solicitud generada el dÍa ' . $fechas["enviado"] . ' y fue actualizada el dÍa ' . $fechas["actualizado"] . '.</p>');
            $pdf->writeHTML($html, true, false, true, false, '');
            if (isset($view)) {
                if (isset($xml["cove"]) && $xml["cove"] != '') {
                    $pdf->Output('/tmp' . DIRECTORY_SEPARATOR . $xml["cove"] . '.pdf', 'I');
                    unlink('/tmp' . DIRECTORY_SEPARATOR . $xml["cove"] . '.pdf');
                } else {
                    $pdf->Output('/tmp' . DIRECTORY_SEPARATOR . $xml["solicitud"] . '.pdf', 'I');
                    unlink('/tmp' . DIRECTORY_SEPARATOR . $xml["solicitud"] . '.pdf');
                }
            } elseif (isset($download)) {
                if (isset($xml["cove"]) && $xml["cove"] != '') {
                    $pdf->Output('/tmp' . DIRECTORY_SEPARATOR . $xml["cove"] . '.pdf', 'FD');
                    unlink('/tmp' . DIRECTORY_SEPARATOR . $xml["cove"] . '.pdf');
                } else {
                    $pdf->Output('/tmp' . DIRECTORY_SEPARATOR . $xml["solicitud"] . '.pdf', 'FD');
                    unlink('/tmp' . DIRECTORY_SEPARATOR . $xml["solicitud"] . '.pdf');
                }
            } elseif (isset($save)) {
                $referencias = new OAQ_Referencias(array("patente" => $xml["patente"], "aduana" => $xml["aduana"], "referencia" => $xml["referencia"], "usuario" => "VucemCove"));
                //$arr = $referencias->crearRepositorioSitawin();
                $arr = $referencias->crearRepositorioRest($xml["patente"], $xml["aduana"], $xml["referencia"]);
                $session = null ? $session = new Zend_Session_Namespace('') : $session = new Zend_Session_Namespace($this->_config->app->namespace);
                $arch = new Archivo_Model_RepositorioMapper();
                $folder = $this->_appconfig->getParam("expdest") . DIRECTORY_SEPARATOR . $xml["patente"] . DIRECTORY_SEPARATOR . $xml["aduana"] . DIRECTORY_SEPARATOR . $xml["referencia"];
                if (!file_exists($folder)) {
                    if (!mkdir($folder, 0777, true)) {
                        throw new Exception('Failed to create folder...');
                    }
                }
                $filenamePdf = $folder . DIRECTORY_SEPARATOR . $xml["cove"] . ".pdf";
                $filenameXml = $folder . DIRECTORY_SEPARATOR . $xml["cove"] . ".xml";                
                $pdf->Output($filenamePdf, "F");
                $misc = new OAQ_Misc();
                /*if(!isset($arr) || empty($arr) || $arr === false) {
                    //$arr = $misc->buscarReferenciaWsdl($xml["patente"], $xml["aduana"], $xml["referencia"]);
                }*/
                if (file_exists($filenameXml)) {
                    unlink($filenameXml);
                }
                file_put_contents($filenameXml, $xml["xml"]);
                if (file_exists($filenameXml)) {
                    if (!($arch->checkIfFileExists($xml["referencia"], $xml["patente"], $xml["aduana"], basename($filenameXml)))) {
                        $x = $arch->addNewFile(21, null, $xml["referencia"], $xml["patente"], $xml["aduana"], basename($filenameXml), $filenameXml, (isset($session)) ? $session->username : null, $xml["cove"], ($arr === false || !isset($arr)) ? null : $arr["rfcCliente"], ($arr === false || !isset($arr)) ? $xml["pedimento"] : $arr["pedimento"]);
                    }
                }
                if (file_exists($filenamePdf)) {
                    if (!($arch->checkIfFileExists($xml["referencia"], $xml["patente"], $xml["aduana"], basename($filenamePdf)))) {
                        $p = $arch->addNewFile(22, null, $xml["referencia"], $xml["patente"], $xml["aduana"], basename($filenamePdf), $filenamePdf, (isset($session)) ? $session->username : null, $xml["cove"], ($arr === false|| !isset($arr)) ? null : $arr["rfcCliente"], ($arr === false || !isset($arr)) ? $xml["pedimento"] : $arr["pedimento"]);
                    }
                }
                if($p || $x) {
                    $vucemSol->enExpediente($id);
                }
            }
        } catch (Exception $ex) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    protected function utf8Fix($value) {
        return htmlentities(utf8_decode($value));
    }
    
    public function printInvoice($uuid, $download, $view, $save) {
        try {
            $vucem = new OAQ_Vucem();
            $tmpFactura = new Vucem_Model_VucemTmpFacturasMapper();
            $tmpProductos = new Vucem_Model_VucemTmpProductosMapper();
            $factura = $tmpFactura->obtenerFactura($uuid);
            $productos = $tmpProductos->obtenerProductos($uuid);        
            require 'tcpdf/mytcpdf.php';
            $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'Letter', true, 'UTF-8', false);
            $pdf->setFontSubsetting(false);
            $pdf->setCellHeightRatio(1);
            $tagvs = array(
                'p' => array(0 => array('h' => 0, 'n' => 0), 1 => array('h' => 0, 'n' => 0)),
                'h3' => array(0 => array('h' => 0, 'n' => 0), 1 => array('h' => 0, 'n' => 0)),
                'table' => array(0 => array('h' => 0, 'n' => 0), 1 => array('h' => 0, 'n' => 0)),
            );
            $pdf->setHtmlVSpace($tagvs);
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('Jaime E. Valdez');
            $pdf->SetTitle('EDOC');
            $pdf->SetSubject('EDOC');
            $pdf->SetKeywords('EDOCUMENT');
            $appconfig = new Application_Model_ConfigMapper();
            $pdf->setHeaderData($appconfig->getParam('tcpdf-logo'), "35", "FACTURA", $factura["NumFactura"], array(0, 0, 0), array(150, 150, 150));
            $pdf->setFooterData(array(0, 0, 0), array(0, 0, 0));
            $pdf->setHeaderFont(Array("pdfacourier", '', PDF_FONT_SIZE_MAIN));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
            if (@file_exists(dirname(__FILE__) . '/lang/es.php')) {
                require_once(dirname(__FILE__) . '/lang/es.php');
                $pdf->setLanguageArray($l);
            }
            $pdf->setFontSubsetting(true);
            $pdf->SetFont('pdfacourier', '', 8);
            $pdf->AddPage();
            switch ($factura["figura"]) {
                case '1':
                    $tipoFigura = 'AGENTE ADUANAL';
                    break;
                case '2':
                    $tipoFigura = 'APODERADO ADUANAL';
                    break;
                case '3':
                    $tipoFigura = 'MANDATARIO';
                    break;
                case ('4' && $this->data["tipoOperacion"] == 'TOCE.EXP'):
                    $tipoFigura = 'EXPORTADOR';
                    break;
                case ('4' && $this->data["tipoOperacion"] == 'TOCE.IMP'):
                    $tipoFigura = 'IMPORTADOR';
                    break;
            }
            if($factura["TipoOperacion"] == 'TOCE.EXP') {
                $factura["CteIden"] = $vucem->tipoIdentificador($factura["CteRfc"], $factura["CtePais"]);
                $factura["ProIden"] = $vucem->tipoIdentificador($factura["ProTaxID"], $factura["ProPais"]);
            } else {
                $factura["ProIden"] = $vucem->tipoIdentificador($factura["CteRfc"], $factura["CtePais"]);
                $factura["CteIden"] = $vucem->tipoIdentificador($factura["ProTaxID"], $factura["ProPais"]);
            }
            $thtitle = 'background-color: #e3e3e3; font-weight: bold; border: 1px #999999 solid;';
            $thsec = 'background-color: #444; font-weight: bold; border: 1px #999999 solid; color: #fff; text-align:center;';
            $tdhl = 'background-color: none; font-weight: bold; width: 250px; border: 1px #999999 solid;';
            $tdn = 'background-color: none; border: 1px #999999 solid;';
            $html = '<h3 style="text-align:center; line-height: 12px; margin:0; padding: 0;">DATOS DEL COMPROBANTE</h3>'
                    . "<p style=\"text-align:center; line-height: 12px; margin:0; padding: 0;\"><strong>REFERENCIA:</strong> {$factura["Referencia"]}, <strong>PEDIMENTO:</strong> {$factura["Pedimento"]}</p>"
                    . '<table>'
                    . '<tr>'
                    . '<th style="' . $thtitle . '">TIPO DE OPERACIÓN</th>'
                    . '<th style="' . $thtitle . '">RELACIÓN DE FACTURAS</th>'
                    . '<th style="' . $thtitle . '">NO. DE FACTURA</th>'
                    . '</tr>'
                    . '<tr>'
                    . '<td style="' . $tdn . '">' . (($factura["TipoOperacion"] == 'TOCE.EXP') ? 'EXPORTACIÓN' : 'IMPORTACIÓN') . '</td>'
                    . '<td style="' . $tdn . '">' . ((isset($factura["RelFact"])) ? 'CON RELACIÓN DE FACTURAS' : 'SIN RELACIÓN DE FACTURAS') . '</td>'
                    . '<td style="' . $tdn . '">' . $factura["NumFactura"] . '</td>'
                    . '</tr>'
                    . '<tr>'
                    . '<th style="' . $thtitle . '">TIPO DE FIGURA</th>'
                    . '<th colspan="2" style="' . $thtitle . '">FECHA EXP.</th>'
                    . '</tr>'
                    . '<tr>'
                    . '<td style="' . $tdn . '">' . $tipoFigura . '</td>'
                    . '<td colspan="2" style="' . $tdn . '">' . date('Y-m-d',  strtotime($factura["FechaFactura"])) . '</td>'
                    . '</tr>'
                    . '<tr>'
                    . '<th colspan="3" style="' . $thtitle . '">OBERVACIONES</th>'
                    . '</tr>'
                    . '<tr>'
                    . '<td colspan="3" style="' . $tdn . '">' . (isset($factura["Observaciones"]) ? (mb_check_encoding($factura["Observaciones"], 'UTF-8')) ? utf8_decode($factura["Observaciones"]) : $factura["Observaciones"] : '&nbsp;') . '</td>'
                    . '</tr>'
                    . '</table>';

            $html .= '<table>'
                    . '<tr>
                        <th style="' . $thtitle . '">SUBDIVISIÓN</th>
                        <th style="' . $thtitle . '">CERTIFICADO DE ORIGEN</th>
                        <th style="' . $thtitle . '">NO. DE EXPORTADOR AUTORIZADO</th>
                        </tr>'
                    . '<tr>'
                    . '<td style="' . $tdn . '">' . ( (isset($factura["Subdivision"]) && $factura["Subdivision"] != '0') ? 'CON SUBDIVISIÓN' : 'SIN SUBDIVISIÓN' ) . '</td>
                        <td style="' . $tdn . '">' . ( (isset($factura["CertificadoOrigen"]) && $factura["CertificadoOrigen"] != '0') ? "SI FUNGE COMO CERTIFICADO DE ORIGEN" : 'NO FUNGE COMO CERTIFICADO DE ORIGEN' ) . '</td>
                        <td style="' . $tdn . '">' . ( (isset($factura["NumExportador"]) && $factura["NumExportador"] != '') ? $factura["NumExportador"] : '' ) . '</td>'
                    . '</tr>'
                    . '</table>';

            if($factura["TipoOperacion"] == 'TOCE.IMP') {
                $factura["emisor"] = array(
                    'tipoIdentificador' => $factura["ProIden"],
                    'identificacion' => $factura["ProTaxID"],
                    'nombre' => $factura["ProNombre"],
                    'calle' => $factura["ProCalle"],
                    'numeroExterior' => $factura["ProNumExt"],
                    'numeroInterior' => $factura["ProNumInt"],
                    'codigoPostal' => $factura["ProCP"],
                    'colonia' => $factura["ProColonia"],
                    'localidad' => $factura["ProLocalidad"],
                    'municipio' => $factura["ProMun"],
                    'entidadFederativa' => $factura["ProEdo"],
                    'pais' => $factura["ProPais"],
                );
                $factura["destinatario"] = array(
                    'tipoIdentificador' => $factura["CteIden"],
                    'identificacion' => $factura["CteRfc"],
                    'nombre' => $factura["CteNombre"],
                    'calle' => $factura["CteCalle"],
                    'numeroExterior' => $factura["CteNumExt"],
                    'numeroInterior' => $factura["CteNumInt"],
                    'codigoPostal' => $factura["CteCP"],
                    'colonia' => $factura["CteColonia"],
                    'localidad' => $factura["CteLocalidad"],
                    'municipio' => $factura["CteMun"],
                    'entidadFederativa' => $factura["CteEdo"],
                    'pais' => $factura["CtePais"],
                );
            } elseif($factura["TipoOperacion"] == 'TOCE.EXP' && $factura["Manual"] == 1) {
                $factura["destinatario"] = array(
                    'tipoIdentificador' => $factura["ProIden"],
                    'identificacion' => $factura["ProTaxID"],
                    'nombre' => $factura["ProNombre"],
                    'calle' => $factura["ProCalle"],
                    'numeroExterior' => $factura["ProNumExt"],
                    'numeroInterior' => $factura["ProNumInt"],
                    'codigoPostal' => $factura["ProCP"],
                    'colonia' => $factura["ProColonia"],
                    'localidad' => $factura["ProLocalidad"],
                    'municipio' => $factura["ProMun"],
                    'entidadFederativa' => $factura["ProEdo"],
                    'pais' => $factura["ProPais"],
                );
                $factura["emisor"] = array(
                    'tipoIdentificador' => $factura["CteIden"],
                    'identificacion' => $factura["CteRfc"],
                    'nombre' => $factura["CteNombre"],
                    'calle' => $factura["CteCalle"],
                    'numeroExterior' => $factura["CteNumExt"],
                    'numeroInterior' => $factura["CteNumInt"],
                    'codigoPostal' => $factura["CteCP"],
                    'colonia' => $factura["CteColonia"],
                    'localidad' => $factura["CteLocalidad"],
                    'municipio' => $factura["CteMun"],
                    'entidadFederativa' => $factura["CteEdo"],
                    'pais' => $factura["CtePais"],
                );
            } elseif($factura["TipoOperacion"] == 'TOCE.EXP' && $factura["Manual"] == null) {
                $factura["emisor"] = array(
                    'tipoIdentificador' => $factura["ProIden"],
                    'identificacion' => $factura["ProTaxID"],
                    'nombre' => $factura["ProNombre"],
                    'calle' => $factura["ProCalle"],
                    'numeroExterior' => $factura["ProNumExt"],
                    'numeroInterior' => $factura["ProNumInt"],
                    'codigoPostal' => $factura["ProCP"],
                    'colonia' => $factura["ProColonia"],
                    'localidad' => $factura["ProLocalidad"],
                    'municipio' => $factura["ProMun"],
                    'entidadFederativa' => $factura["ProEdo"],
                    'pais' => $factura["ProPais"],
                );
                $factura["destinatario"] = array(
                    'tipoIdentificador' => $factura["CteIden"],
                    'identificacion' => $factura["CteRfc"],
                    'nombre' => $factura["CteNombre"],
                    'calle' => $factura["CteCalle"],
                    'numeroExterior' => $factura["CteNumExt"],
                    'numeroInterior' => $factura["CteNumInt"],
                    'codigoPostal' => $factura["CteCP"],
                    'colonia' => $factura["CteColonia"],
                    'localidad' => $factura["CteLocalidad"],
                    'municipio' => $factura["CteMun"],
                    'entidadFederativa' => $factura["CteEdo"],
                    'pais' => $factura["CtePais"],
                );
            } elseif($factura["TipoOperacion"] == 'TOCE.EXP' && $factura["Manual"] == 0) {
                $factura["destinatario"] = array(
                    'tipoIdentificador' => $factura["ProIden"],
                    'identificacion' => $factura["ProTaxID"],
                    'nombre' => $factura["ProNombre"],
                    'calle' => $factura["ProCalle"],
                    'numeroExterior' => $factura["ProNumExt"],
                    'numeroInterior' => $factura["ProNumInt"],
                    'codigoPostal' => $factura["ProCP"],
                    'colonia' => $factura["ProColonia"],
                    'localidad' => $factura["ProLocalidad"],
                    'municipio' => $factura["ProMun"],
                    'entidadFederativa' => $factura["ProEdo"],
                    'pais' => $factura["ProPais"],
                );
                $factura["emisor"] = array(
                    'tipoIdentificador' => $factura["CteIden"],
                    'identificacion' => $factura["CteRfc"],
                    'nombre' => $factura["CteNombre"],
                    'calle' => $factura["CteCalle"],
                    'numeroExterior' => $factura["CteNumExt"],
                    'numeroInterior' => $factura["CteNumInt"],
                    'codigoPostal' => $factura["CteCP"],
                    'colonia' => $factura["CteColonia"],
                    'localidad' => $factura["CteLocalidad"],
                    'municipio' => $factura["CteMun"],
                    'entidadFederativa' => $factura["CteEdo"],
                    'pais' => $factura["CtePais"],
                );
            }

            $html .= '<br /> <br /><table>'
                    . '<tr>'
                    . '<th style="' . $thtitle . '">PATENTE ADUANAL</th>'
                    . '</tr>'
                    . '<tr>'
                    . "<td style=\"{$tdn}\">" . ((isset($factura["Patente"])) ? $factura["Patente"] : '&nbsp;') . "</td>"
                    . '</tr>'
                    . '</table>';

            $html .= '<br /><br /><table>'
                    . '<tr>'
                    . '<th colspan="4" style="' . $thsec . '">DATOS DEL EMISOR</th>'
                    . '</tr>'
                    . '<tr>
                        <th style="' . $thtitle . '">IDENTIFICADOR EMISOR</th>
                        <th style="' . $thtitle . '" colspan="3">TAX ID/RFC/CURP</th>
                    </tr>
                    <tr>
                        <td style="' . $tdn . '">' . ( isset($factura["emisor"]["tipoIdentificador"]) ? $this->identificadorDesc($factura["emisor"]["tipoIdentificador"]) : '&nbsp;' ) . '</td>
                        <td style="' . $tdn . '" colspan="3">' . ( (isset($factura["emisor"]["identificacion"]) && $factura["emisor"]["identificacion"] != '0') ? $factura["emisor"]["identificacion"] : '&nbsp;' ) . '</td>
                    </tr>
                    <tr>
                        <th style="' . $thtitle . '">NOMBRES(S) O RAZÓN SOCIAL</th>
                        <th style="' . $thtitle . '">APELLIDO PATERNO</th>
                        <th style="' . $thtitle . '" colspan="2">APELLIDO MATERNO</th>
                    </tr>
                    <tr>
                        <td style="' . $tdn . '">' . ( (isset($factura["emisor"]["nombre"]) && $factura["emisor"]["nombre"] != '') ? utf8_encode($factura["emisor"]["nombre"]) : '&nbsp;' ) . '</td>
                        <td style="' . $tdn . '">&nbsp;</td>
                        <td style="' . $tdn . '" colspan="2">&nbsp;</td>
                    </tr>
                    <tr>
                        <th style="' . $thtitle . '">CALLE</th>
                        <th style="' . $thtitle . '">NUM. EXTERIOR</th>
                        <th style="' . $thtitle . '">NUM. INTERIOR</th>
                        <th style="' . $thtitle . '">CÓDIGO POSTAL</th>
                    </tr>
                    <tr>
                        <td style="' . $tdn . '">' . ( (isset($factura["emisor"]["calle"]) && $factura["emisor"]["calle"] != '') ? $factura["emisor"]["calle"] : '&nbsp;' ) . '</td>
                        <td style="' . $tdn . '">' . ( (isset($factura["emisor"]["numeroExterior"]) && $factura["emisor"]["numeroExterior"] != '') ? $factura["emisor"]["numeroExterior"] : '&nbsp;' ) . '</td>
                        <td style="' . $tdn . '">' . ( (isset($factura["emisor"]["numeroInterior"]) && $factura["emisor"]["numeroInterior"] != '') ? $factura["emisor"]["numeroInterior"] : '&nbsp;' ) . '</td>
                        <td style="' . $tdn . '">' . ( (isset($factura["emisor"]["codigoPostal"]) && $factura["emisor"]["codigoPostal"] != '') ? $factura["emisor"]["codigoPostal"] : '&nbsp;' ) . '</td>
                    </tr>
                    <tr>
                        <th style="' . $thtitle . '">COLONIA</th>
                        <th style="' . $thtitle . '" colspan="3">LOCALIDAD</th>
                    </tr>
                    <tr>
                        <td style="' . $tdn . '">' . ( (isset($factura["emisor"]["colonia"]) && $factura["emisor"]["colonia"] != '') ? $factura["emisor"]["colonia"] : '&nbsp;' ) . '</td>
                        <td style="' . $tdn . '" colspan="3">' . ( (isset($factura["emisor"]["localidad"]) && $factura["emisor"]["localidad"] != '') ? $factura["emisor"]["localidad"] : '&nbsp;' ) . '</td>
                    </tr>
                    <tr>
                        <th style="' . $thtitle . '">MUNICIPIO</th>
                        <th style="' . $thtitle . '" colspan="3">ENTIDAD FEDERATIVA (ESTADO)</th>
                    </tr>
                    <tr>
                        <td style="' . $tdn . '">' . ( (isset($factura["emisor"]["municipio"]) && $factura["emisor"]["municipio"] != '') ? $factura["emisor"]["municipio"] : '&nbsp;' ) . '</td>
                        <td style="' . $tdn . '" colspan="3">' . ( (isset($factura["emisor"]["entidadFederativa"]) && $factura["emisor"]["entidadFederativa"] != '') ? $factura["emisor"]["entidadFederativa"] : '&nbsp;' ) . '</td>
                    </tr>
                    <tr>
                        <th style="' . $thtitle . '" colspan="4">PAÍS</th>
                    </tr>
                    <tr>
                        <td style="' . $tdn . '" colspan="4">' . ( (isset($factura["emisor"]["pais"]) && $factura["emisor"]["pais"] != '') ? $factura["emisor"]["pais"] : '&nbsp;' ) . '</td>
                    </tr>'
                    . '</table>';

            $html .= '<br /><br /><table>'
                    . '<tr>'
                    . '<th colspan="4" style="' . $thsec . '">DATOS DEL DESTINATARIO</th>'
                    . '</tr>'
                    . '<tr>
                        <th style="' . $thtitle . '">IDENTIFICADOR DESTINATARIO</th>
                        <th style="' . $thtitle . '" colspan="3">TAX ID/RFC/CURP</th>                
                    </tr>
                    <tr>
                        <td style="' . $tdn . '">' . ( (isset($factura["destinatario"]["tipoIdentificador"])) ? $this->identificadorDesc($factura["destinatario"]["tipoIdentificador"]) : '&nbsp;' ) . '</td>
                        <td style="' . $tdn . '" colspan="3">' . ( (isset($factura["destinatario"]["identificacion"]) && $factura["destinatario"]["identificacion"] != '') ? $factura["destinatario"]["identificacion"] : '&nbsp;' ) . '</td>
                    </tr>
                    <tr>
                        <th style="' . $thtitle . '">NOMBRES(S) O RAZÓN SOCIAL</th>
                        <th style="' . $thtitle . '">APELLIDO PATERNO</th>
                        <th style="' . $thtitle . '" colspan="2">APELLIDO MATERNO</th>
                    </tr>
                    <tr>
                        <td style="' . $tdn . '">' . ( (isset($factura["destinatario"]["nombre"]) && $factura["destinatario"]["nombre"] != '') ? $factura["destinatario"]["nombre"] : '&nbsp;' ) . '</td>
                        <td style="' . $tdn . '">&nbsp;</td>
                        <td style="' . $tdn . '" colspan="2">&nbsp;</td>
                    </tr>
                    <tr>
                        <th style="' . $thtitle . '">CALLE</th>
                        <th style="' . $thtitle . '">NUM. EXTERIOR</th>
                        <th style="' . $thtitle . '">NUM. INTERIOR</th>
                        <th style="' . $thtitle . '">CÓDIGO POSTAL postal</th>
                    </tr>
                    <tr>
                        <td style="' . $tdn . '">' . ( (isset($factura["destinatario"]["calle"]) && $factura["destinatario"]["calle"] != '') ? $factura["destinatario"]["calle"] : '&nbsp;' ) . '</td>
                        <td style="' . $tdn . '">' . ( (isset($factura["destinatario"]["numeroExterior"]) && $factura["destinatario"]["numeroExterior"] != '') ? $factura["destinatario"]["numeroExterior"] : '&nbsp;' ) . '</td>
                        <td style="' . $tdn . '">' . ( (isset($factura["destinatario"]["numeroInterior"]) && $factura["destinatario"]["numeroInterior"] != '') ? $factura["destinatario"]["numeroInterior"] : '&nbsp;' ) . '</td>
                        <td style="' . $tdn . '">' . ( (isset($factura["destinatario"]["codigoPostal"]) && $factura["destinatario"]["codigoPostal"] != '') ? $factura["destinatario"]["codigoPostal"] : '&nbsp;' ) . '</td>
                    </tr>
                    <tr>
                        <th style="' . $thtitle . '">COLONIA</th>
                        <th style="' . $thtitle . '" colspan="3">LOCALIDAD</th>
                    </tr>
                    <tr>
                        <td style="' . $tdn . '">' . ( (isset($factura["destinatario"]["colonia"]) && $factura["destinatario"]["colonia"] != '') ? $factura["destinatario"]["colonia"] : '&nbsp;' ) . '</td>
                        <td style="' . $tdn . '" colspan="3">' . ( (isset($factura["destinatario"]["localidad"]) && $factura["destinatario"]["localidad"] != '') ? $factura["destinatario"]["localidad"] : '&nbsp;' ) . '</td>
                    </tr>
                    <tr>
                        <th style="' . $thtitle . '">MUNICIPIO</th>
                        <th style="' . $thtitle . '" colspan="3">ENTIDAD FEDERATIVA (ESTADO)</th>
                    </tr>
                    <tr>        
                        <td style="' . $tdn . '">' . ((isset($factura["destinatario"]["municipio"]) && $factura["destinatario"]["municipio"] != '') ? $factura["destinatario"]["municipio"] : '&nbsp;' ) . '</td>
                        <td style="' . $tdn . '" colspan="3">' . ( (isset($factura["destinatario"]["entidadFederativa"]) && $factura["destinatario"]["entidadFederativa"] != '') ? $factura["destinatario"]["entidadFederativa"] : '&nbsp;' ) . '</td>
                    </tr>
                    <tr>
                        <th style="' . $thtitle . '" colspan="4">PAÍS</th>
                    </tr>
                    <tr>
                        <td style="' . $tdn . '" colspan="4">' . ( (isset($factura["destinatario"]["pais"]) && $factura["destinatario"]["pais"] != '') ? $factura["destinatario"]["pais"] : '&nbsp;' ) . '</td>
                    </tr>'
                    . '</table>';

            $pdf->writeHTML($html, true, false, true, false, '');

            $html = '<table>'
                    . '<thead><tr>'
                    . '<th colspan="8" style="' . $thsec . '">DATOS DE LA MERCANCIA</th>'
                    . '</tr>'
                    . '<tr>
                        <th style="' . $thtitle . ' width:157px;">DESCRIPCION GENERICA</th>
                        <th style="' . $thtitle . '">NUM. PARTE</th>
                        <th style="' . $thtitle . ' width:45px;">MONEDA</th>
                        <th style="' . $thtitle . '">VALOR UNITARIO</th>
                        <th style="' . $thtitle . '">VALOR TOTAL</th>
                        <th style="' . $thtitle . '">VALOR USD</th>
                        <th style="' . $thtitle . ' width:45px;">OMA</th>
                        <th style="' . $thtitle . '">CANT. OMA</th>
                    </tr></thead>';
            $html .= '</table>';
            $pdf->writeHTML($html, false, false, false, false, '');

            if(isset($productos) && !empty($productos)) {
                $html = '<table>';
                $valcom = 0;
                $valusd = 0;
                foreach ($productos as $item) {
                    $html .= '<tr nobr="true">';
                    $html .= '<td style="' . $tdn . ' width:157px;">' . ((isset($item["DESC_COVE"]) && $item["DESC_COVE"] != '') ? $item["DESC_COVE"] : '&nbsp;' ) . '</td>';
                    $html .= '<td style="' . $tdn . '">' . ((isset($item["PARTE"]) && $item["PARTE"] != '') ? $item["PARTE"] : '&nbsp;' ) . '</td>';
                    $html .= '<td style="' . $tdn . ' width:45px;">' . ((isset($item["MONVAL"]) && $item["MONVAL"] != '') ? $item["MONVAL"] : '&nbsp;' ) . '</td>';
                    $html .= '<td style="' . $tdn . '">' . ((isset($item["PREUNI"]) && $item["PREUNI"] != '') ? $this->number6($item["PREUNI"]) : '&nbsp;' ) . '</td>';
                    $html .= '<td style="' . $tdn . '">' . ((isset($item["VALDLS"]) && $item["VALCOM"] != '') ? $this->number4($item["VALCOM"]) : '&nbsp;' ) . '</td>';
                    $html .= '<td style="' . $tdn . '">' . ((isset($item["VALDLS"]) && $item["VALDLS"] != '') ? $this->number4($item["VALDLS"]) : '&nbsp;' ) . '</td>';
                    $html .= '<td style="' . $tdn . ' width:45px;">' . ((isset($item["MONVAL"]) && $item["UMC_OMA"] != '') ? $item["UMC_OMA"] : '&nbsp;' ) . '</td>';
                    $html .= '<td style="' . $tdn . '">' . ((isset($item["CANTFAC"]) && $item["CANTFAC"] != '') ? $this->number($item["CANTFAC"]) : '&nbsp;' ) . '</td>';
                    $html .= '</tr>';
                    if ((isset($item["MARCA"]) && $item["MARCA"] != '') || (isset($item["MODELO"]) && $item["MODELO"] != '') || (isset($item["SUBMODELO"]) && $item["SUBMODELO"] != '') || (isset($item["NUMSERIE"]) && $item["NUMSERIE"] != '')) {
                        $html .= '<tr nobr="true">
                            <td colspan="8" style="border: 1px #999999 solid;">
                                <table>
                                    <tr>
                                        <th style="font-weight: bold;">MARCA</th>
                                        <th style="font-weight: bold;">MODELO</th>
                                        <th style="font-weight: bold;">SUBMODELO</th>
                                        <th style="font-weight: bold;">NUM. DE SERIE</th>
                                    </tr>
                                    <tr>
                                        <td>' . ( (isset($item["MARCA"])) ? $item["MARCA"] : '&nbsp;' ) . '</td>
                                        <td>' . ( (isset($item["MODELO"])) ? $item["MODELO"] : '&nbsp;' ) . '</td>
                                        <td>' . ( (isset($item["SUBMODELO"])) ? $item["SUBMODELO"] : '&nbsp;' ) . '</td>
                                        <td>' . ( (isset($item["NUMSERIE"])) ? $item["NUMSERIE"] : '&nbsp;' ) . '</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>';
                    }
                    $valcom += $item["VALCOM"];
                    $valusd += $item["VALDLS"];   
                }                     
            }
            $html .= '<tr nobr="true">';
            $html .= '<td style="' . $thtitle . ' text-align: right; font-weight: bold;" colspan="4">TOTAL</td><td style="' . $tdn . '">' . $this->number4($valcom) . '</td><td style="' . $tdn . '">' . $this->number4($valusd) . '</td><td style="' . $thtitle . '" colspan="2"></td>';
            $html .= '</tr>';
            $html .= '</table>';

            $pdf->writeHTML($html, false, false, false, false, '');
            if (isset($download)) {
                $pdf->Output('/tmp' . DIRECTORY_SEPARATOR . $factura["Aduana"] . '_' . $factura["Patente"] . '_' . $factura["Pedimento"] . '_' . $factura["Referencia"] . '_' . str_replace(array('\\', '/', "#"), '_', $factura["NumFactura"]) . '.pdf', 'FD');
                unlink('/tmp' . DIRECTORY_SEPARATOR . $factura["Aduana"] . '_' . $factura["Patente"] . '_' . $factura["Pedimento"] . '_' . $factura["Referencia"] . '_' . str_replace(array('\\', '/', "#"), '_', $factura["NumFactura"]) . '.pdf');
            }
        } catch (Exception $ex) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function printPedimento() {
        try {
            require 'tcpdf/pedimentocomp.php';
            require 'tcpdf/tcpdf_barcodes_2d.php';
            // http://www.ibm.com/developerworks/library/os-tcpdf/        
            $data["colors"]["line"] = array(5, 5, 5);
            $data["rfcCliente"] = 'CIN0309091D3';
            $data["cvePed"] = 'G1';
            $data["regimen"] = 'IMD';
            $data["tipoOp"] = 'IMP';
            $data["patente"] = '3589';
            $data["aduana"] = '640';
            $data["pedimento"] = '5001190';
            $data["referencia"] = 'Q1501190';
            $data["usuario"] = 'JAIME';
            $data["fechaPago"] = '30/01/2015';
            $data["destino"] = 'DESTINO/ORIGEN: INTERIOR DEL PAÍS';
            $data["copia"] = '2a COPIA: IMPORTADOR / EXPORTADOR';
            $data["copia"] = '2a COPIA: IMPORTADOR / EXPORTADOR';
            $pdf = new PedimentoCompleto($data, 'P', 'pt', 'LETTER');
            $pdf->CreateDocument();
            $pdf->Output($data['aduana'] . '-' . $data["patente"] . '-' . $data["pedimento"] . '.pdf', 'I');
        } catch (Exception $ex) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    public function printPedimentoSitawin($data) {
        try {
            require 'tcpdf/pedimentositawin.php';
            require 'tcpdf/tcpdf_barcodes_2d.php';
            $data["colors"]["line"] = array(5, 5, 5);
            $data["copia"] = 2;
            $data["codigoBarras"] = true;
            $data["sis"] = 'SITA';
            $pdf = new PedimentoSitawin($data, 'P', 'pt', 'LETTER');
            $pdf->PedimentoUnico();
            $pdf->Output($data['aduana'] . '-' . $data["patente"] . '-' . $data["pedimento"] . '.pdf', 'I');
        } catch (Exception $ex) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    public function printPedimentoAduanet($data) {
        try {
            require 'tcpdf/pedimentositawin.php';
            require 'tcpdf/tcpdf_barcodes_2d.php';
            $data["colors"]["line"] = array(5, 5, 5);
            $data["copia"] = 2;
            $data["codigoBarras"] = true;
            $data["sis"] = 'ADNT';
            $pdf = new PedimentoSitawin($data, 'P', 'pt', 'LETTER');
            $pdf->PedimentoUnico();
            $pdf->Output($data['aduana'] . '-' . $data["patente"] . '-' . $data["pedimento"] . '.pdf', 'I');
        } catch (Exception $ex) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }    
    
    public function printPedimentoSimplificadoSitawin($data) {
        try {
            require 'tcpdf/pedimentositawin.php';
            require 'tcpdf/tcpdf_barcodes_2d.php';
            $data["colors"]["line"] = array(5, 5, 5);
            $data["copia"] = 2;
            $data["transportista"] = true;
            $data["codigoBarras"] = true;
            $pdf = new PedimentoSitawin($data, 'P', 'pt', 'LETTER');
            $pdf->PedimentoSimplificado();
            $pdf->Output($data['aduana'] . '-' . $data["patente"] . '-' . $data["pedimento"] . '_SIMP.pdf', 'I');
        } catch (Exception $ex) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function printEdoc($uuid, $solicitud, $download, $view, $save, $debug = null) {
        try {
            error_reporting(E_ALL & ~E_NOTICE);        
            $vucemEdoc = new Vucem_Model_VucemEdocMapper();
            $data = $vucemEdoc->obtenerEdocPorUuid($uuid, $solicitud);
            if (!isset($data) || empty($data)) {
            throw new Exception("No data set!");
            }
            require "tcpdf/mytcpdf.php";
            $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'Letter', true, 'UTF-8', false);
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor("Jaime E. Valdez");
            $pdf->SetTitle('EDOC');
            $pdf->SetSubject('EDOC');
            $pdf->SetKeywords('EDOCUMENT');
            $appconfig = new Application_Model_ConfigMapper();
            $pdf->setHeaderData($appconfig->getParam('tcpdf-logo'), "35", "COMPROBRANTE E-DOCUMENT", $data["edoc"], array(0, 0, 0), array(150, 150, 150));
            $pdf->setFooterData(array(0, 0, 0), array(0, 0, 0));
            $pdf->setHeaderFont(Array("pdfacourier", '', PDF_FONT_SIZE_MAIN));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
            if (file_exists(dirname(__FILE__) . '/lang/es.php')) {
                require_once(dirname(__FILE__) . '/lang/es.php');
                $pdf->setLanguageArray($l);
            }
            $pdf->setFontSubsetting(true);
            $pdf->SetFont('pdfacourier', '', 9);
            $pdf->AddPage();
            $thtitle = 'background-color: #e3e3e3; font-weight: bold; border: 1px #999999 solid;';
            $tdhl = 'background-color: none; font-weight: bold; width: 250px; border: 1px #999999 solid;';
            $tdn = 'background-color: none; border: 1px #999999 solid;';
            $date = date('d/m/Y H:i:s');
            $hora = date('h:i a', strtotime($data["enviado"]));
            $fecha = date('d/m/Y', strtotime($data["enviado"]));
            $rfcConsulta = isset($data["rfcConsulta"]) ? $data["rfcConsulta"] : 'OAQ030623UL8';
            $html = <<<EOD
<h3 style="text-align:center; line-height: 12px; margin:0; padding: 0;">ACUSE DIGITALIZACIÓN DE DOCUMENTOS</h3>
<p style="text-align:center; line-height: 12px; margin:0; padding: 0;"><strong>REFERENCIA:</strong> {$data["referencia"]}, <strong>PEDIMENTO:</strong> {$data["pedimento"]}</p>
<p style="text-align:right; line-height: 12px; margin:0; padding: 0;"><strong>FOLIO DE LA SOLICITUD:</strong> {$data["numTramite"]}</p>
<p style="text-align:justify; line-height: 12px; margin:0; padding: 0;"><strong>RFC FIRMANTE:</strong> {$data["rfc"]}</p>
<p style="text-align:justify; line-height: 12px; margin:0; padding: 0;">Siendo las {$hora} del {$fecha} se tiene por recibida y atendida la solicitud de registro de Documentos Digitalizados presentado a través de la ventanilla única (Web Service).</p>
<p><strong>DATOS DEL DOCUMENTO:</strong></p>
<table style="width:750px">
<tr>
<th style="{$thtitle} width:250px">OPERACIÓN</th>
<th style="{$thtitle}">REGISTRO DE DOCUMENTOS DIGITALIZADOS</th>
</tr>
<tr>
<td style="{$tdhl}">NÚMERO DE E-DOCUMENT</td>
<td style="{$tdn}">{$data["edoc"]}</td>
</tr>
<tr>
<td style="{$tdhl}">TIPO DE DOCUMENTO</td>
<td style="{$tdn}">{$data["tipoDoc"]}</td>
</tr>
<tr>
<td style="{$tdhl}">NOMBRE DEL DOCUMENTO</td>
<td style="{$tdn}">{$data["nomArchivo"]}</td>
</tr>
<tr>
<td style="{$tdhl}">RFC DE CONSULTA</td>
<td style="{$tdn}">{$rfcConsulta}</td>
</tr>
<tr>
<td style="{$tdhl}">CADENA ORIGINAL</td>
<td style="{$tdn}">{$data["hash"]}</td>
</tr>
<tr>
<td style="{$tdhl}">SELLO DIGITAL DEL SOLICITANTE (DEL DOCUMENTO)</td>
<td style="{$tdn}">{$data["cadena"]}</td>
</tr>
<tr>
<td style="{$tdhl}">LEYENDA</td>
<td style="{$tdn}">Tiene 90 días a partir de esta fecha para utilizar su documento digitalizado, si en ese tiempo no lo utiliza, será dado de baja del sistema de la Ventanilla Única.</td>
</tr>
</table>
<p><strong>DOCUMENTO IMPRESO CON FECHA DE:</strong> {$date} <strong>USUARIO:</strong> {$data["usuario"]}<p>
EOD;
            $pdf->writeHTML($html, true, false, true, false, '');
            if (isset($view)) {
                $pdf->Output('/tmp' . DIRECTORY_SEPARATOR . $data["edoc"] . '.pdf', 'I');
                unlink('/tmp' . DIRECTORY_SEPARATOR . $data["edoc"] . '.pdf');
            } elseif (isset($download)) {
                $pdf->Output('/tmp' . DIRECTORY_SEPARATOR . $data["edoc"] . '.pdf', 'FD');
                unlink('/tmp' . DIRECTORY_SEPARATOR . $data["edoc"] . '.pdf');
            } elseif (isset($save)) {
                $context = stream_context_create(array(
                    "ssl" => array(
                        "verify_peer" => false,
                        "verify_peer_name" => false,
                        "allow_self_signed" => true
                    )
                ));
                $session = null ? $session = new Zend_Session_Namespace('') : $session = new Zend_Session_Namespace($this->_config->app->namespace);
                $folder = $this->_appconfig->getParam('expdest') . DIRECTORY_SEPARATOR . $data["patente"] . DIRECTORY_SEPARATOR . $data["aduana"] . DIRECTORY_SEPARATOR . $data["referencia"];
                if (!file_exists($folder)) {
                    if (!mkdir($folder, 0777, true)) {
                        throw new Exception("Failed to create folder!");
                    }
                }
                $arch = new Archivo_Model_RepositorioMapper();
                $acuseEdoc = $folder . DIRECTORY_SEPARATOR . 'EDOC' . $data["edoc"] . '.pdf';
                if (file_exists($acuseEdoc)) {
                    $vucemEdoc->enExpediente($uuid);
                }
                $digitalizado = $folder . DIRECTORY_SEPARATOR . $data["nomArchivo"];
                $pdf->Output($acuseEdoc, 'F');
                $con = new Application_Model_WsWsdl();
                if ($data["patente"] == 3589 && preg_match('/64/', $data["aduana"])) {
                    if (($wsdl = $con->getWsdl(3589, 640, "sitawin"))) {
                        $soapSitawin = new Zend_Soap_Client($wsdl, array("stream_context" => $context));
                        $referencia = $soapSitawin->basicoReferencia($data["patente"], 640, $data["referencia"]);
                        if ($referencia === false) {
                            $referencia = $soapSitawin->basicoReferencia($data["patente"], 646, $data["referencia"]);
                        }
                    }
                }
                if (file_exists($acuseEdoc)) {
                    if (!($arch->checkIfFileExists($data["referencia"], $data["patente"], $data["aduana"], basename($acuseEdoc)))) {
                        if (isset($referencia) && !empty($referencia)) {
                            $arch->addNewFile(27, $data["subTipoArchivo"], $data["referencia"], $data["patente"], $data["aduana"], basename($acuseEdoc), $acuseEdoc, (isset($session)) ? $session->username : null, $data["edoc"], $referencia["rfcCliente"], $referencia["pedimento"]);
                        } else {
                            $arch->addNewFile(27, $data["subTipoArchivo"], $data["referencia"], $data["patente"], $data["aduana"], basename($acuseEdoc), $acuseEdoc, (isset($session)) ? $session->username : null, $data["edoc"], null, $data["pedimento"]);
                        }
                        if (file_exists($digitalizado)) {
                            $vucemEdoc->enExpediente($uuid);
                        }
                        if (!file_exists($digitalizado)) {
                            $file = $vucemEdoc->obtenerEdocDigitalizado($uuid);
                            file_put_contents($digitalizado, base64_decode($file["archivo"]));
                            if (isset($referencia) && !empty($referencia)) {
                                $arch->addNewFile($file["tipoDoc"], $file["subTipoArchivo"], $data["referencia"], $data["patente"], $data["aduana"], $data["nomArchivo"], $digitalizado, (isset($session)) ? $session->username : null, $data["edoc"], $referencia["rfcCliente"], $referencia["pedimento"]);
                            } else {
                                $arch->addNewFile($file["tipoDoc"], $file["subTipoArchivo"], $data["referencia"], $data["patente"], $data["aduana"], $data["nomArchivo"], $digitalizado, (isset($session)) ? $session->username : null, $data["edoc"], null, $data["pedimento"]);
                            }
                        }
                        $vucemEdoc->enExpediente($uuid);
                    }
                }
            }
        } catch (Exception $ex) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    public function analizarCertificado($filename) {
        try {
            $data = array();
            exec("openssl x509 -inform DER -in {$filename} -dates -subject -noout", $output);
            if(isset($output) && !empty($output)) {
                if(isset($output[0])) {
                    $exp = explode("=", $output[0]);
                    if(isset($exp[1])) {
                        $data["valido_desde"] = date("Y-m-d H:i:s", strtotime($exp[1]));
                    }
                }
                if(isset($output[1])) {
                    $exp = explode("=", $output[1]);
                    if(isset($exp[1])) {
                        $data["valido_hasta"] = date("Y-m-d H:i:s", strtotime($exp[1]));
                    }
                }
                if(isset($output[2])) {
                    
                }
            }
            return $data;
        } catch (Exception $ex) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    public function secureXml($xml) {
        return preg_replace('#<soapenv:Header(.*?)>(.*?)</soapenv:Header>#is', '', $xml);
    }
    
    public function xmlStrToArray($xmlstr) {
        $doc = new DOMDocument();
        $doc->loadXML($this->_replace($xmlstr));
        return $this->_domnodeToArray($doc->documentElement);
    }
    
    protected function _replace($string) {
        try {
            return str_replace(array("S:", "soapenv:", "oxml:", "con:", "wsse:", "wsu:", "env:", "ns3:", "ns2:", "ns5:", "ns4:", "ns6:", "ns7:", "ns8:", "ns9:"), "", $string);
        } catch (Exception $e) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    protected function _domnodeToArray($node) {
        $output = array();
        switch ($node->nodeType) {
            case XML_CDATA_SECTION_NODE:
            case XML_TEXT_NODE:
                $output = trim($node->textContent);
                break;
            case XML_ELEMENT_NODE:
                for ($i = 0, $m = $node->childNodes->length; $i < $m; $i++) {
                    $child = $node->childNodes->item($i);
                    $v = $this->_domnodeToArray($child);
                    if (isset($child->tagName)) {
                        $t = $child->tagName;
                        if (!isset($output[$t])) {
                            $output[$t] = array();
                        }
                        $output[$t][] = $v;
                    } elseif ($v) {
                        $output = (string) $v;
                    }
                }
                if (is_array($output)) {
                    if ($node->attributes->length) {
                        $a = array();
                        foreach ($node->attributes as $attrName => $attrNode) {
                            $a[$attrName] = (string) $attrNode->value;
                        }
                        $output['@attributes'] = $a;
                    }
                    foreach ($output as $t => $v) {
                        if (is_array($v) && count($v) == 1 && $t != '@attributes') {
                            $output[$t] = $v[0];
                        }
                    }
                }
                break;
        }
        return $output;
    }

}
