<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class OAQ_Archivos_SellosVucem {

    protected $id;
    protected $idCliente;
    protected $idAgente;
    protected $patente;
    protected $idSelloCliente;
    protected $rfc;
    protected $razonSocial;
    protected $vuPass;
    protected $wsPass;
    protected $keyFile;
    protected $cerFile;
    protected $pemFile;
    protected $spemFile;
    protected $cerFileName;
    protected $keyFileName;
    protected $validFrom;
    protected $validTo;
    protected $tipo;
    protected $figura;
    protected $usuario;
    protected $_key = "5203bfec0c3db@!b2295";
    protected $_log;
    protected $_db;

    function setId($id) {
        $this->id = $id;
    }

    function setIdCliente($idCliente) {
        $this->idCliente = $idCliente;
    }
    
    function setIdAgente($idAgente) {
        $this->idAgente = $idAgente;
    }
    
    function setPatente($patente) {
        $this->patente = $patente;
    }

    function setIdSelloCliente($idSelloCliente) {
        $this->idSelloCliente = $idSelloCliente;
    }

    function setRfc($rfc) {
        $this->rfc = $rfc;
    }

    function setRazonSocial($razonSocial) {
        $this->razonSocial = $razonSocial;
    }

    function setVuPass($vuPass) {
        $this->vuPass = $vuPass;
    }

    function setWsPass($wsPass) {
        $this->wsPass = $wsPass;
    }

    function setKeyFile($keyFile) {
        $this->keyFile = $keyFile;
    }

    function setCerFile($cerFile) {
        $this->cerFile = $cerFile;
    }

    function setCerFileName($cerFileName) {
        $this->cerFileName = $cerFileName;
    }

    function setKeyFileName($keyFileName) {
        $this->keyFileName = $keyFileName;
    }

    function setTipo($tipo) {
        $this->tipo = $tipo;
    }

    function setFigura($figura) {
        $this->figura = $figura;
    }

    function setUsuario($usuario) {
        $this->usuario = $usuario;
    }

    public function __set($name, $value) {
        $method = "set" . $name;
        if (("mapper" == $name) || !method_exists($this, $method)) {
            throw new Exception("Invalid property: " . $name);
        }
        $this->$method($value);
    }

    public function __get($name) {
        $method = "get" . $name;
        if (("mapper" == $name) || !method_exists($this, $method)) {
            throw new Exception("Invalid property: " . $name);
        }
        return $this->$method();
    }

    public function setOptions(array $options) {
        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = "set" . ucfirst($key);
            if (in_array($method, $methods)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    public function __construct(array $options = null) {
        if (is_array($options)) {
            $this->setOptions($options);
        }
        $this->_log = new Trafico_Model_SellosLogs();
    }
    
    public function actualizarWs() {
        $mppr = new Trafico_Model_SellosClientes();
        if (isset($this->id)) {
            $arr["password_ws"] = new Zend_Db_Expr("AES_ENCRYPT('{$this->wsPass}', '{$this->_key}')");
            $arr["actualizado"] = date("Y-m-d H:i:s");
            $arr["actualizadoPor"] = $this->usuario;
            if ($mppr->actualizar($this->id, $arr)) {
                $this->_addToLog($this->rfc . ": Se contrseña de Servicios Web para sellos con Id {$this->id}.");
                return true;
            }
            
        } else {
            throw new Exception("Id is not set!");
        }
    }

    public function guardarSello() {
        $mppr = new Trafico_Model_SellosClientes();
        $arr = array(
            "certificado_nom" => basename($this->cerFileName),
            "certificado" => new Zend_Db_Expr("AES_ENCRYPT('" . base64_encode(file_get_contents($this->cerFile, FILE_USE_INCLUDE_PATH)) . "', '{$this->_key}')"),
            "key_nom" => basename($this->keyFileName),
            "key" => new Zend_Db_Expr("AES_ENCRYPT('" . base64_encode(file_get_contents($this->keyFile, FILE_USE_INCLUDE_PATH)) . "', '{$this->_key}')"),
            "req_nom" => null,
            "req" => null,
            "pem_nom" => basename($this->pemFile),
            "pem" => new Zend_Db_Expr("AES_ENCRYPT('" . base64_encode(file_get_contents($this->pemFile, FILE_USE_INCLUDE_PATH)) . "', '{$this->_key}')"),
            "spem_nom" => basename($this->spemFile),
            "spem" => new Zend_Db_Expr("AES_ENCRYPT('" . base64_encode(file_get_contents($this->spemFile, FILE_USE_INCLUDE_PATH)) . "', '{$this->_key}')"),
            "password_vu" => new Zend_Db_Expr("AES_ENCRYPT('{$this->vuPass}', '{$this->_key}')"),
            "password_fiel" => new Zend_Db_Expr("AES_ENCRYPT('{$this->vuPass}', '{$this->_key}')"),
            "password_ws" => new Zend_Db_Expr("AES_ENCRYPT('{$this->wsPass}', '{$this->_key}')"),
            "password_spem" => new Zend_Db_Expr("AES_ENCRYPT('{$this->vuPass}', '{$this->_key}')"),
            "valido_desde" => isset($this->validFrom) ? $this->validFrom : null,
            "valido_hasta" => isset($this->validTo) ? $this->validTo : null,
        );
        if ($this->tipo == 1) {
            $arr["sha"] = "sha256";
        }
        if (!($id = $mppr->verificar($this->idCliente))) {
            $arr["idCliente"] = $this->idCliente;
            $arr["rfc"] = $this->rfc;
            $arr["razon"] = $this->razonSocial;
            $arr["creado"] = date("Y-m-d H:i:s");
            $arr["creadoPor"] = $this->usuario;
            if ($mppr->agregar($arr)) {
                $this->_addToLog($this->rfc . ": Se dió de alta nuevo sello.");
                return true;
            }
        } else {
            $arr["actualizado"] = date("Y-m-d H:i:s");
            $arr["actualizadoPor"] = $this->usuario;
            if ($mppr->actualizar($id, $arr)) {
                $this->_addToLog($this->rfc . ": Se actualizó sello.");
                return true;
            }
        }
        return;
    }

    public function guardarSelloAgente() {
        $mppr = new Trafico_Model_SellosAgentes();
        $arr = array(
            "certificado_nom" => basename($this->cerFileName),
            "certificado" => new Zend_Db_Expr("AES_ENCRYPT('" . base64_encode(file_get_contents($this->cerFile, FILE_USE_INCLUDE_PATH)) . "', '{$this->_key}')"),
            "key_nom" => basename($this->keyFileName),
            "key" => new Zend_Db_Expr("AES_ENCRYPT('" . base64_encode(file_get_contents($this->keyFile, FILE_USE_INCLUDE_PATH)) . "', '{$this->_key}')"),
            "req_nom" => null,
            "req" => null,
            "pem_nom" => basename($this->pemFile),
            "pem" => new Zend_Db_Expr("AES_ENCRYPT('" . base64_encode(file_get_contents($this->pemFile, FILE_USE_INCLUDE_PATH)) . "', '{$this->_key}')"),
            "spem_nom" => basename($this->spemFile),
            "spem" => new Zend_Db_Expr("AES_ENCRYPT('" . base64_encode(file_get_contents($this->spemFile, FILE_USE_INCLUDE_PATH)) . "', '{$this->_key}')"),
            "password_vu" => new Zend_Db_Expr("AES_ENCRYPT('{$this->vuPass}', '{$this->_key}')"),
            "password_fiel" => new Zend_Db_Expr("AES_ENCRYPT('{$this->vuPass}', '{$this->_key}')"),
            "password_ws" => new Zend_Db_Expr("AES_ENCRYPT('{$this->wsPass}', '{$this->_key}')"),
            "password_spem" => new Zend_Db_Expr("AES_ENCRYPT('{$this->vuPass}', '{$this->_key}')"),
            "valido_desde" => isset($this->validFrom) ? $this->validFrom : null,
            "valido_hasta" => isset($this->validTo) ? $this->validTo : null,
        );
        if ($this->tipo == 1) {
            $arr["sha"] = "sha256";
        }
        if (!($id = $mppr->verificar($this->idAgente))) {
            if ($arr["idCliente"]) {
                unset($arr["idCliente"]);
            }
            $arr["idAgente"] = $this->idAgente;
            $arr["patente"] = $this->patente;
            $arr["activo"] = 1;
            $arr["creado"] = date("Y-m-d H:i:s");
            $arr["creadoPor"] = $this->usuario;
            if ($mppr->agregar($arr)) {
                $this->_addToLog($this->rfc . ": Se dió de alta nuevo sello.");
                return true;
            }
        } else {
            $arr["actualizado"] = date("Y-m-d H:i:s");
            $arr["actualizadoPor"] = $this->usuario;
            if ($mppr->actualizar($id, $arr)) {
                $this->_addToLog($this->rfc . ": Se actualizó sello.");
                return true;
            }
        }
        return;
    }

    public function analizarSello() {
        $msg = array(
            "success" => true,
            "messages" => array()
        );
        if (!$this->_probarWs()) {
            $msg["success"] = false;
            $msg["messages"][] = array(
                "error" => "ws",
                "message" => "Contraseña de Servicios Web no válida"
            );
            $this->_addToLog($this->rfc . ": Contraseña de Servicios Web no válida.");
        }
        if (!$this->_probarKey()) {
            $msg["success"] = false;
            $msg["messages"][] = array(
                "error" => "vu",
                "message" => "Contraseña de VUCEM no válida"
            );
            $this->_addToLog($this->rfc . ": Contraseña de VUCEM no válida para sello " . $this->keyFileName);
        }
        return $msg;
    }

    protected function _replaceExtension($filename, $new_extension, $subfix = null) {
        $pathinfo = pathinfo($filename);
        return $pathinfo['dirname'] . DIRECTORY_SEPARATOR . $pathinfo['filename'] . (isset($subfix) ? $subfix : '') . '.' . $new_extension;
    }

    protected function _probarKey() {

        $this->pemFile = $this->_replaceExtension($this->keyFile, 'pem');
        $this->spemFile = $this->_replaceExtension($this->keyFile, 'pem', '_secure');

        if (file_exists($this->pemFile)) {
            unlink($this->pemFile);
        }
        if (file_exists($this->spemFile)) {
            unlink($this->spemFile);
        }

        if (APPLICATION_ENV == 'production') {
            exec("openssl pkcs8 -inform DER -outform PEM -in {$this->keyFile} -out {$this->pemFile} -passin pass:\"{$this->vuPass}\"");
            if (filesize($this->pemFile) > 0) {
                $cmd2 = "openssl rsa -in {$this->pemFile} -des3 -out {$this->spemFile} -passout pass:\"{$this->vuPass}\"";
                exec($cmd2);
            } else {
                return;
            }
            if (filesize($this->pemFile) > 0 && filesize($this->spemFile) > 0) {
                $output = array();
                exec("openssl x509 -inform DER -in {$this->cerFile} -dates -noout", $output);
                if (!empty($output)) {
                    $this->_analizarValidez($output);
                }
                return true;
            }
        } else {
            $pkey_f = str_replace("D:", "/cygdrive/d", str_replace('\\', '/', $this->keyFile));
            $pem_f = str_replace("D:", "/cygdrive/d", str_replace('\\', '/', $this->pemFile));
            $cmd = "C:\\cygwin64\\bin\\openssl.exe pkcs8 -inform DER -outform PEM -in {$pkey_f} -out {$pem_f} -passin pass:\"{$this->vuPass}\"";
            exec($cmd);
            if (filesize($this->pemFile) > 0) {
                $spem_f = str_replace("D:", "/cygdrive/d", str_replace('\\', '/', $this->spemFile));
                $cmd2 = "C:\\cygwin64\\bin\\openssl.exe rsa -in {$pem_f} -des3 -out {$spem_f} -passout pass:\"{$this->vuPass}\"";
                exec($cmd2);
            } else {
                return;
            }
            if (filesize($this->pemFile) > 0 && filesize($this->spemFile) > 0) {
                $cer_f = str_replace("D:", "/cygdrive/d", str_replace('\\', '/', $this->cerFile));
                $output = array();
                exec("C:\\cygwin64\\bin\\openssl.exe x509 -inform DER -in {$cer_f} -dates -noout", $output);
                if (!empty($output)) {
                    $this->_analizarValidez($output);
                }
                return true;
            }
        }
    }

    protected function _analizarValidez($output) {
        if (isset($output[0])) {
            $exp = explode("=", $output[0]);
            if (isset($exp[1])) {
                $this->validFrom = date("Y-m-d H:i:s", strtotime($exp[1]));
            }
        }
        if (isset($output[1])) {
            $exp = explode("=", $output[1]);
            if (isset($exp[1])) {
                $this->validTo = date("Y-m-d H:i:s", strtotime($exp[1]));
            }
        }
    }

    protected function _probarWs() {
        $doc = new DOMDocument("1.0", "utf-8");
        $doc->formatOutput = true;
        $root = $doc->createElementNS("http://schemas.xmlsoap.org/soap/envelope/", "soapenv:Envelope");
        $doc->appendChild($root);
        $doc->createElementNS("http://www.ventanillaunica.gob.mx/cove/ws/oxml/", "p:x", "test");
        $root->setAttributeNS("http://www.w3.org/2000/xmlns/", "xmlns:oxml", "http://www.ventanillaunica.gob.mx/cove/ws/oxml/");

        $header = $doc->createElement("soapenv:Header");
        $root->appendChild($header);
        $security = $doc->createElementNS("http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd", "wsse:Security");
        $usernameToken = $doc->createElement("wsse:UsernameToken");
        $usernameToken->appendChild($doc->createElement("wsse:Username", $this->rfc));
        $password = $doc->createElement("wsse:Password", $this->wsPass);
        $password->setAttribute("Type", "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText");

        $usernameToken->appendChild($password);
        $security->appendChild($usernameToken);
        $header->appendChild($security);

        $body = $doc->createElement("soapenv:Body");
        $root->appendChild($body);

        $service = $doc->createElementNS("http://www.ventanillaunica.gob.mx/cove/ws/oxml/", "oxml:solicitarRecibirCoveServicio");
        $body->appendChild($service);

        $vucem = new OAQ_VucemEnh();

        file_put_contents("D:\\wamp64\\tmp\\vucem" . DIRECTORY_SEPARATOR . $this->rfc . ".xml", $doc->saveXML());
        $response = $vucem->enviarCoveVucem($doc->saveXML(), "https://www.ventanillaunica.gob.mx/ventanilla/RecibirCoveService");
        $array = $vucem->vucemXmlToArray($response);

        unset($array["Header"]);
        if (isset($array["Body"]["solicitarRecibirCoveServicioResponse"])) {
            if (isset($array["Body"]["solicitarRecibirCoveServicioResponse"]["mensajeInformativo"])) {
                if (preg_match("/No se recibieron/i", $array["Body"]["solicitarRecibirCoveServicioResponse"]["mensajeInformativo"])) {
                    return true;
                }
            }
        } elseif (isset($array["Body"]["Fault"])) {
            if (isset($array["Body"]["Fault"]["faultcode"])) {
                if (preg_match("/FailedAuthentication/i", $array["Body"]["Fault"]["faultcode"])) {
                    return;
                }
                return;
            }
        }
    }

    protected function _addToLog($msg) {
        $arr = array(
            "idAgente" => isset($this->idAgente) ? $this->idAgente : null,
            "idCliente" => isset($this->idCliente) ? $this->idCliente : null,
            "idSelloCliente" => isset($this->idSelloCliente) ? $this->idSelloCliente : null,
            "idSelloAgente" => isset($this->idSelloAgente) ? $this->idSelloAgente : null,
            "bitacora" => $msg,
            "creado" => date("Y-m-d H:i:s"),
            "creadoPor" => $this->usuario,
        );
        $this->_log->agregar($arr);
    }
    
    public function actualizarSelloDesdeTrafico($idSelloCliente, $idVucemFirmante) {
        try {
            $this->_db = Zend_Registry::get("oaqintranet");
            $sql = "UPDATE trafico_sellos_clientes AS i, (SELECT
		ii.certificado,
		ii.certificado_nom,
		ii.`key`,
		ii.key_nom,
		ii.req,
		ii.req_nom,
		ii.pem,
		ii.pem_nom,
		ii.spem,
		ii.spem_nom,
		ii.password_vu,
		ii.password_fiel,
		ii.password_ws,
		ii.password_spem,
		ii.sha FROM vucem_firmante ii WHERE id = {$idSelloCliente}) AS copy SET i.certificado = copy.certificado
                    ,i.certificado_nom = copy.certificado_nom
                    ,i.`key` = copy.`key`
                    ,i.key_nom = copy.key_nom
                    ,i.req = null
                    ,i.req_nom = null
                    ,i.pem = null
                    ,i.pem_nom = null
                    ,i.spem = copy.spem
                    ,i.spem_nom = copy.spem_nom
                    ,i.password_vu = copy.password_vu
                    ,i.password_fiel = copy.password_fiel
                    ,i.password_ws = copy.password_ws
                    ,i.password_spem = copy.password_spem
                    ,i.sha = copy.sha
            WHERE i.id = {$idVucemFirmante};";
            $stmt = $this->_db->execute($sql);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

}
