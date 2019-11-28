<?php

/**
 * Description of Vucem_Misc
 * 
 * Clase miscelanea para operaciones previas a la construcción de XML para COVE e EDOCUMENT
 *
 * @author Jaime E. Valdez jvaldezch at gmail
 */
class OAQ_Arreglos {

    protected $patente;
    protected $email;
    protected $tipoFigura;
    protected $idTipoDocumento;
    protected $nombreDocumento;
    protected $archivo;
    protected $hash;
    protected $rfcConsulta;
    protected $username;
    protected $password;
    protected $certificado;
    protected $key;
    protected $new;
    protected $xml;
    protected $preview = false;

    function __construct() {
    }

    function setPatente($patente) {
        $this->patente = $patente;
    }

    function setEmail($email) {
        $this->email = $email;
    }

    function setTipoFigura($tipoFigura) {
        $this->tipoFigura = $tipoFigura;
    }

    function setRfcConsulta($rfcConsulta) {
        $this->rfcConsulta = $rfcConsulta;
    }

    function getPatente() {
        return $this->patente;
    }

    function getEmail() {
        return $this->email;
    }

    function getTipoFigura() {
        return $this->tipoFigura;
    }

    function getRfcConsulta() {
        return $this->rfcConsulta;
    }

    function getUsername() {
        return $this->username;
    }

    function getPassword() {
        return $this->password;
    }

    function getKey() {
        return $this->key;
    }

    function getNew() {
        return $this->new;
    }

    function setUsername($username) {
        $this->username = $username;
    }

    function setPassword($password) {
        $this->password = $password;
    }

    function setKey($key) {
        $this->key = $key;
    }

    function setNew($new) {
        $this->new = $new;
    }

    function getCertificado() {
        return $this->certificado;
    }

    function setCertificado($certificado) {
        $this->certificado = $certificado;
    }

    function getIdTipoDocumento() {
        return $this->idTipoDocumento;
    }

    function getNombreDocumento() {
        return $this->nombreDocumento;
    }

    function getArchivo() {
        return $this->archivo;
    }

    function getHash() {
        return $this->hash;
    }

    function setIdTipoDocumento($idTipoDocumento) {
        $this->idTipoDocumento = $idTipoDocumento;
    }

    function setNombreDocumento($nombreDocumento) {
        $this->nombreDocumento = $nombreDocumento;
    }

    function setArchivo($archivo) {
        $this->archivo = $archivo;
    }

    function setHash($hash) {
        $this->hash = $hash;
    }
    
    function getPreview() {
        return $this->preview;
    }

    function setPreview($preview) {
        $this->preview = $preview;
    }
    
    function getXml() {
        return $this->xml;
    }
    
    protected function _value($value) {
        return htmlentities($value);
    }

    /**
     * 
     * Crea arreglo para funcion xmlCove
     * 
     * @param array $factura
     * @param array $productos
     * @param array $cliente
     * @param array $proveedor
     * @return type
     * @throws Exception
     */
    public function arregloCove($factura, $productos, $cliente, $proveedor) {
        try {
            $data = array();
            $data["factura"] = array(
                'tipoOperacion' => $factura["tipoOperacion"],
                'numeroFacturaOriginal' => $this->_value($factura["numFactura"]),
                'patenteAduanal' => $this->getPatente(),
                'fechaExpedicion' => date("Y-m-d", strtotime($factura["fechaFactura"])),
                'certificadoOrigen' => $factura["certificadoOrigen"],
                'subdivision' => $factura["subdivision"],
                'divisa' => $factura["divisa"],
                'observaciones' => isset($factura["observaciones"]) ? $this->_value($factura["observaciones"]) : null,
                'factorMonExt' => $factura["factorMonExt"],
                'tipoFigura' => $this->getTipoFigura(),
                'correoElectronico' => $this->getEmail(),
                'rfcConsulta' => $this->getRfcConsulta(),
            );
            if($this->preview == false) {
                $data["usuario"] = $this->credenciales();
            }
            foreach ($productos as $prod) {
                $data["mercancias"][] = array(
                    'descripcionGenerica' => isset($prod["descripcion"]) ? $this->_value($prod["descripcion"]) : null,
                    'numParte' => isset($prod["numParte"]) ? $this->_value($prod["numParte"]) : null,
                    'secuencial' => isset($prod["orden"]) ? $prod["orden"] : null,
                    'claveUnidadMedida' => isset($prod["oma"]) ? $prod["oma"] : null,
                    'tipoMoneda' => $prod["divisa"], // FIX PARA OAQ
                    'cantidad' => isset($prod["cantidadFactura"]) ? number_format($prod["cantidadFactura"], 3, '.', '') : null,
                    'valorUnitario' => isset($prod["precioUnitario"]) ? number_format($prod["precioUnitario"], 6, '.', '') : null,
                    'valorTotal' => isset($prod["valorComercial"]) ? number_format($prod["valorComercial"], 6, '.', '') : null,
                    'valorDolares' => (isset($prod["valorDolares"]) && (float) $prod["valorDolares"] != 0) ? number_format($prod["valorDolares"], 4, '.', '') : number_format(($prod["valorComercial"] * $factura["factorMonedaExtranjera"]), 4, '.', ''),
                );
            }
            if ($factura["tipoOperacion"] == "TOCE.IMP") {            
                $data["destinatario"] = $this->_setAddress($cliente);
                $data["emisor"] = $this->_setAddress($proveedor);
            } elseif ($factura["tipoOperacion"] == "TOCE.EXP") {
                $data["emisor"] = $this->_setAddress($cliente);
                $data["destinatario"] = $this->_setAddress($proveedor);                
            }
            return $data;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    /**
     * 
     * Crea el arreglo para ser enviado a xmlConsultaCove
     * 
     * @param stirng $edocument
     * @return type
     * @throws Exception
     */
    public function arregloConsultaCove($edocument) {
        try {
            $data = array();
            $data["consulta"] = array(
                'cove' => $edocument,
            );
            $data["usuario"] = $this->credenciales();
            return $data;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    /**
     * 
     * Crea arreglo para ser enviado a xmlConsultaEdocument
     * 
     * @param string $edocument
     * @return type
     * @throws Exception
     */
    public function arregloConsultaEdocument($edocument) {
        try {
            $data = array();
            $data["consulta"] = array(
                "edocument" => $edocument,
            );
            $data["usuario"] = $this->credenciales();
            return $data;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
    
    public function consultaEdocument($edocument) {
        try {
            $data = array();
            $data["consulta"] = array(
                "operacion" => $edocument,
            );
            $data["usuario"] = $this->credenciales();
            return $data;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    /**
     * 
     * Genera arreglo para credenciales de VUCEM del sello
     * 
     * @return type
     * @throws Exception
     */
    public function credenciales() {
        try {
            return array(
                "username" => $this->getUsername(),
                "password" => $this->getPassword(),
                "certificado" => $this->getCertificado(),
                "key" => $this->getKey(),
                "new" => $this->getNew(),
            );
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    /**
     * 
     * Crea arreglo para la dirección de Emisor/Destinatario en COVE
     * 
     * @param array $array
     * @return type
     * @throws Exception
     */
    protected function _setAddress($array) {
        try {
            if (isset($array["domicilio"])) {
                $dom = $array["domicilio"];
            }
            return array(
                'tipoIdentificador' => $array["idIdentificador"],
                'identificacion' => $array["identificador"],
                'nombre' => $this->_value($array["razonSocial"]),
                'calle' => isset($dom["calle"]) ? $this->_value($dom["calle"]) : null,
                'numeroExterior' => isset($dom["numExt"]) ? $dom["numExt"] : null,
                'numeroInterior' => isset($dom["numInt"]) ? $dom["numInt"] : null,
                'colonia' => isset($dom["colonia"]) ? $this->_value($dom["colonia"]) : null,
                'localidad' => isset($dom["localidad"]) ? $this->_value($dom["localidad"]) : null,
                'municipio' => isset($dom["municipio"]) ? $this->_value($dom["municipio"]) : null,
                'entidadFederativa' => isset($dom["estado"]) ? $this->_value($dom["estado"]) : null,
                'codigoPostal' => isset($dom["codigoPostal"]) ? $dom["codigoPostal"] : null,
                'pais' => isset($dom["pais"]) ? $dom["pais"] : null,
            );
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    /**
     * 
     * Crea arreglo para funcion consultaEstatusOperacionCove
     * 
     * @param int $solicitud
     * @return type
     * @throws Exception
     */
    public function arregloSolicitudCove($solicitud) {
        try {
            if (isset($solicitud)) {
                $data["consulta"] = array(
                    "operacion" => $solicitud,
                );
            } else {
                throw new Exception("Solicitud no existe.");
            }
            $data["usuario"] = $this->credenciales();
            return $data;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    /**
     * 
     * Crea arreglo para funcion consultaEstatusOperacionEdocument
     * 
     * @param int $solicitud
     * @return type
     * @throws Exception
     */
    public function arregloSolicitudEdocument($solicitud) {
        try {
            if (isset($solicitud)) {
                $data["consulta"] = array(
                    'operacion' => $solicitud,
                );
            } else {
                throw new Exception("Solicitud no existe.");
            }
            $data["usuario"] = array(
                'username' => $this->getUsername(),
                'password' => $this->getPassword(),
                'certificado' => $this->getCertificado(),
                'key' => $this->getKey(),
                'new' => $this->getNew(),
            );
            return $data;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    /**
     * 
     * Remueve de la cadena de respuesta de VUCEM todos los namespaces innecesarios
     * 
     * @param string $xml
     * @return boolean
     */
    public function vucemXmlToArray($xml) {
        try {
            $clean = str_replace(array('ns2:', 'ns3:', 'ns9:', 'ns8:', 'S:', 'dig:', 'res:', 'wsu:', 'wsse:', 'ns3:', 'wsu:', 'soapenv:', 'env:', 'oxml:', '<![CDATA[', ']]>'), '', $xml);

            if (preg_match('/html/i', $clean)) {
                return null;
            }
            $xmlClean = simplexml_load_string($clean);
            unset($clean);
            return @json_decode(@json_encode($xmlClean), 1);
        } catch (Exception $e) {
            echo "<b>Exception on " . __METHOD__ . "</b>" . $e->getMessage();
            return false;
        }
    }

    /**
     * 
     * Crea arreglo para funcion xmlEdocument
     * 
     * @return type
     * @throws Exception
     */
    public function arregloEdocument() {
        try {
            $data = array();
            $data["archivo"] = array(
                "idTipoDocumento" => $this->getIdTipoDocumento(),
                "nombreDocumento" => $this->getNombreDocumento(),
                "archivo" => $this->getArchivo(),
                "hash" => $this->getHash(),
                'correoElectronico' => $this->getEmail(),
                'rfcConsulta' => $this->getRfcConsulta()
            );
            $data["usuario"] = $this->credenciales();
            return $data;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
    
    /**
     * 
     * @param int $idCliente
     * @param string $edocument
     * @param string $ws
     * @return array
     * @throws Exception
     */
    public function consultaEdoc($idCliente, $edocument, $ws = null) {
        try {
            $misc = new Vucem_Misc();
            $servicios = new Vucem_Servicios();
            $respuesta = new Vucem_Respuestas();
            $cliente = new Usuarios_Model_Table_Clientes();
            $clientes = new Usuarios_Model_ClientesMapper();
            $key = new Usuarios_Model_SellosMapper();
            $vucem = new Vucem_Xml(true);

            $clientes->find($idCliente, $cliente);
            $sello = $key->findCustomerKey($cliente->getId(), $cliente->getIdentificador());
            $misc->setUsername($cliente->getIdentificador());
            if (isset($ws)) {
                $misc->setPassword($ws);
            } else {
                $misc->setPassword($sello["webservice"]);
            }
            $misc->setCertificado($sello["certificado"]);
            $misc->setKey(openssl_get_privatekey(base64_decode($sello['secure']), $sello["password"]));
            $misc->setNew(isset($sello["encriptacion"]) ? true : false);
            $data = array();
            $data["usuario"] = $misc->credenciales();
            $data["consulta"] = array(
                'cove' => $edocument,
            );
            $vucem->xmlConsultaCove($data);
            $servicios->setXml($vucem->getXml());
            $servicios->consultaEdocument();
            $res = $respuesta->analizarRespuesta($servicios->getResponse());
            if (isset($res) && !empty($res)) {
                return $res;
            }
            return;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    /**
     * 
     * @param int $idCliente
     * @param string $edocument
     * @param string $ws
     * @return array
     * @throws Exception
     */
    public function enviarCove($idCliente, $ws = null) {
        try {
            $misc = new Vucem_Misc();
            $servicios = new Vucem_Servicios();
            $respuesta = new Vucem_Respuestas();
            $cliente = new Usuarios_Model_Table_Clientes();
            $clientes = new Usuarios_Model_ClientesMapper();
            $key = new Usuarios_Model_SellosMapper();
            $vucem = new Vucem_Xml(true);

            $clientes->find($idCliente, $cliente);
            $sello = $key->findCustomerKey($cliente->getId(), $cliente->getIdentificador());
            $misc->setUsername($cliente->getIdentificador());
            if (isset($ws)) {
                $misc->setPassword($ws);
            } else {
                $misc->setPassword($sello["webservice"]);
            }
            $misc->setCertificado($sello["certificado"]);
            $misc->setKey(openssl_get_privatekey(base64_decode($sello['secure']), $sello["password"]));
            $misc->setNew(isset($sello["encriptacion"]) ? true : false);
            $data["usuario"] = $misc->credenciales();
            $vucem->xmlCove($data);
            $servicios->setXml($vucem->getXml());
            $servicios->consultaEdocument();
            $res = $respuesta->analizarRespuesta($servicios->getResponse());
            if (isset($res) && !empty($res)) {
                return $res;
            }
            return;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
    
    public function obtenerSello($idCliente, $identificador, $patente) {
        try {
            $key = new Usuarios_Model_SellosMapper();
            $agentes = new Usuarios_Model_Agentes();
            $sello = $key->findCustomerKey($idCliente, $identificador);
            if(isset($sello) && !empty($sello)) {
                $sello["figura"] = 5;
                return $sello;
            } else {
                $agente = $agentes->buscar($patente);
                if(isset($agente) && !empty($agente)) {
                    $sello = $key->findAgentKey($agente["id"], $agente["rfc"]);
                    $sello["figura"] = 1;
                    return $sello;                    
                }
            }
            return false;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

}
