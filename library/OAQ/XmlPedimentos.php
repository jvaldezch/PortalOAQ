<?php

/**
 * Description of Vucem_Xml
 * 
 * Esta clase conforma arreglos en archivos XML requeridos por la Ventanilla Única de Comercio Exterior (VUCEM)
 *
 * @author Jaime E. Valdez jvaldezch at gmail
 */
class OAQ_XmlPedimentos
{

    protected $_dir;
    protected $_array;
    protected $_domtree;
    protected $_envelope;
    protected $_cadena;
    protected $_body;
    protected $_document;
    protected $_service;
    protected $_header;
    protected $_request;
    protected $_aduana;
    protected $_patente;
    protected $_pedimento;
    protected $_numeroOperacion;
    protected $_partida;

    function get_dir()
    {
        return $this->_dir;
    }

    function set_dir($_dir)
    {
        $this->_dir = $_dir;
    }

    function set_aduana($_aduana)
    {
        $this->_aduana = $_aduana;
    }

    function set_patente($_patente)
    {
        $this->_patente = $_patente;
    }

    function set_pedimento($_pedimento)
    {
        $this->_pedimento = $_pedimento;
    }

    function set_array($_array)
    {
        $this->_array = $_array;
    }

    function set_numeroOperacion($_numeroOperacion)
    {
        $this->_numeroOperacion = $_numeroOperacion;
    }

    function set_partida($_partida)
    {
        $this->_partida = $_partida;
    }



    /**
     * 
     * Constructor de la clase donde se especifica que tipo de XML se va generar
     * 
     * @param boolean $partida
     * @throws Exception
     */
    function __construct($partida = null, $estado = null)
    {
        try {
            $this->_domtree = new DOMDocument("1.0", "UTF-8");
            $this->_domtree->formatOutput = true;
            $this->_envelope = $this->_domtree->createElementNS("http://schemas.xmlsoap.org/soap/envelope/", "soapenv:Envelope");
            if (!isset($partida) && !isset($estado)) {
                $this->_envelope->setAttributeNS("http://www.w3.org/2000/xmlns/", "xmlns:con", "http://www.ventanillaunica.gob.mx/pedimentos/ws/oxml/consultarpedimentocompleto");
            } else if (isset($partida)) {
                $this->_envelope->setAttributeNS("http://www.w3.org/2000/xmlns/", "xmlns:con", "http://www.ventanillaunica.gob.mx/pedimentos/ws/oxml/consultarpartida");
            } else if (isset($estado)) {
                $this->_envelope->setAttributeNS("http://www.w3.org/2000/xmlns/", "xmlns:con", "http://www.ventanillaunica.gob.mx/pedimentos/ws/oxml/consultarestadopedimentos");
            }
            $this->_envelope->setAttributeNS("http://www.w3.org/2000/xmlns/", "xmlns:com", "http://www.ventanillaunica.gob.mx/pedimentos/ws/oxml/comunes");
            $this->_domtree->appendChild($this->_envelope);
            $this->_body = $this->_domtree->createElement("soapenv:Body");
            $this->_header = $this->_domtree->createElement("soapenv:Header");
            $this->_envelope->appendChild($this->_header);
            $this->_envelope->appendChild($this->_body);
        } catch (Exception $e) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * Reemplaza namespaces no necesarios para hacer parsing en PHP del XML resultante.
     * 
     * @param string $string
     * @return string
     * @throws Exception
     */
    public function replace($string)
    {
        try {
            return str_replace(array("S:", "soapenv:", "oxml:", "con:", "wsse:", "wsu:", "env:"), "", $string);
        } catch (Exception $e) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * Regresa el XML
     * 
     * @return string
     * @throws Exception
     */
    public function getXml()
    {
        try {
            return (string) $this->_domtree->saveXML();
        } catch (Exception $e) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * Guarda el XML en disco.
     * 
     * @return type
     * @throws Exception
     */
    public function saveToDisk($type)
    {
        try {
            if ($this->get_dir() !== null) {
                if (file_exists($this->get_dir())) {
                    $this->_domtree->save($this->get_dir() . DIRECTORY_SEPARATOR . $type . "_" . sha1((string) $this->_domtree->saveXML()) . ".xml");
                } else {
                    throw new Exception("Directory do not exists.");
                }
            } else {
                throw new Exception("Directory is not set.");
            }
            return (string) $this->_domtree->saveXML();
        } catch (Exception $e) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * Agrega las credenciales de acceso a VUCEM, username es RFC del sello y el password de Web Service
     * 
     * @return type
     */
    protected function _credenciales()
    {
        try {
            $security = $this->_domtree->createElement("wsse:Security");
            $security->setAttributeNS("http://www.w3.org/2000/xmlns/", "xmlns:wsse", "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd");
            $security->setAttributeNS("http://schemas.xmlsoap.org/soap/envelope/", "soapenv:mustUnderstand", "1");
            $username = $this->_domtree->createElement("wsse:UsernameToken");
            $username->appendChild($this->_domtree->createElement("wsse:Username", $this->_array["usuario"]["username"]));
            $pass = $this->_domtree->createElement("wsse:Password", $this->_array["usuario"]["password"]);
            $pass->setAttribute("Type", "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText");
            $username->appendChild($pass);
            $security->appendChild($username);
            $this->_header->appendChild($security);
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }

    /**
     * 
     * Consulta XML para la consulta de un pedimento
     * 
     * @throws Exception
     */
    public function consultaPedimentoCompleto()
    {
        try {
            $this->_service = $this->_domtree->createElement("con:consultarPedimentoCompletoPeticion");
            $this->_peticion = $this->_domtree->createElement("con:peticion");
            $this->_peticion->appendChild($this->_domtree->createElement("com:aduana", $this->_aduana));
            $this->_peticion->appendChild($this->_domtree->createElement("com:patente", $this->_patente));
            $this->_peticion->appendChild($this->_domtree->createElement("com:pedimento", $this->_pedimento));
            $this->_service->appendChild($this->_peticion);
            $this->_body->appendChild($this->_service);
            $this->_credenciales();
        } catch (Exception $ex) {
            throw new Exception("Zend Exception found on <strong>" . __METHOD__ . "</strong> : " . $ex->getMessage());
        }
    }

    /**
     * 
     * Consulta XML para la consulta de un pedimento
     * 
     * @throws Exception
     */
    public function consultaEstadoPedimento()
    {
        try {
            $this->_service = $this->_domtree->createElement("con:consultarEstadoPedimentosPeticion");
            $this->_service->appendChild($this->_domtree->createElement("con:numeroOperacion", $this->_numeroOperacion));
            $this->_peticion = $this->_domtree->createElement("con:peticion");
            $this->_peticion->appendChild($this->_domtree->createElement("com:aduana", $this->_aduana));
            $this->_peticion->appendChild($this->_domtree->createElement("com:patente", $this->_patente));
            $this->_peticion->appendChild($this->_domtree->createElement("com:pedimento", $this->_pedimento));
            $this->_service->appendChild($this->_peticion);
            $this->_body->appendChild($this->_service);
            $this->_credenciales();
        } catch (Exception $ex) {
            throw new Exception("Zend Exception found on <strong>" . __METHOD__ . "</strong> : " . $ex->getMessage());
        }
    }

    /**
     * 
     * Genera XML para a consulta de una partida
     * 
     * @throws Exception
     */
    public function consultaPartida()
    {
        try {
            $this->_service = $this->_domtree->createElement("con:consultarPartidaPeticion");
            $this->_peticion = $this->_domtree->createElement("con:peticion");
            $this->_peticion->appendChild($this->_domtree->createElement("com:aduana", $this->_aduana));
            $this->_peticion->appendChild($this->_domtree->createElement("com:patente", $this->_patente));
            $this->_peticion->appendChild($this->_domtree->createElement("com:pedimento", $this->_pedimento));
            $this->_peticion->appendChild($this->_domtree->createElement("con:numeroOperacion", $this->_numeroOperacion));
            $this->_peticion->appendChild($this->_domtree->createElement("con:numeroPartida", $this->_partida));
            $this->_service->appendChild($this->_peticion);
            $this->_body->appendChild($this->_service);
            $this->_credenciales();
        } catch (Exception $ex) {
            throw new Exception("Zend Exception found on <strong>" . __METHOD__ . "</strong> : " . $ex->getMessage());
        }
    }
}
