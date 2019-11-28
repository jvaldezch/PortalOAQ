<?php

class Automatizacion_WsController extends Zend_Controller_Action {

    protected $_config;
    protected $_servers;
    protected $sistema;
    protected $patente;
    protected $aduana;

    public function init() {
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
    }

    /**
     * /automatizacion/ws/gearman-pedimentos?patente=3589&aduana=640&fecha=2014-07-31&rfc=CTM990607US8
     * /automatizacion/ws/gearman-pedimentos?patente=3589&aduana=640&year=2016&month=7&rfc=CIN0309091D3
     * /automatizacion/ws/gearman-pedimentos?patente=3589&aduana=640&year=2014&month=7&rfc=CTM990607US8
     * /automatizacion/ws/gearman-pedimentos?patente=3589&aduana=370&year=2014&month=8&rfc=CTM990607US8
     * /automatizacion/ws/gearman-pedimentos?patente=3589&aduana=240&year=2016&month=1&rfc=CTM990607US8
     */
    public function gearmanPedimentosAction() {
        ini_set("default_socket_timeout", 1200);
        ini_set("soap.wsdl_cache_enabled", 0);
        $filters = array(
            "*" => array("StringTrim", "StripTags"),
            "patente" => array("Digits"),
            "aduana" => array("Digits"),
            "month" => array("Digits"),
            "year" => array("Digits"),
            "rfc" => "StringToUpper",
        );
        $validator = array(
            "patente" => array(new Zend_Validate_Int(), "NotEmpty"),
            "aduana" => array(new Zend_Validate_Int(), "NotEmpty"),
            "month" => new Zend_Validate_Int(),
            "year" => new Zend_Validate_Int(),
            "rfc" => array("NotEmpty", new Zend_Validate_Regex("/^[A-Z0-9]+$/")),
            "fecha" => array(new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")),
        );
        $input = new Zend_Filter_Input($filters, $validator, $this->_request->getParams());
        $context = stream_context_create(array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
                "allow_self_signed" => true
            )
        ));
        if ($input->isValid()) {
            $mapper = new Automatizacion_Model_WsPedimentosMapper();
            $model = new Application_Model_WsWsdl();
            $wsdl = $model->getWsdlPedimentos($input->patente, $input->aduana);
            if (isset($wsdl)) {
                $soap = new Zend_Soap_Client($wsdl, array("encoding" => "UTF-8", "compression" => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_DEFLATE, "stream_context" => $context));
                if (isset($input->fecha)) {
                    $array = $soap->pedimentosDelDia($input->patente, $input->aduana, $input->fecha, $input->rfc);
                } elseif (isset($input->year)) {
                    $array = $soap->pedimentosDelMes($input->patente, $input->aduana, $input->year, $input->month, $input->rfc);
                }
            }
            $view = new Zend_View();
            $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/index/");
            if (isset($array) && !empty($array)) {
                $view->arr = $array;
                foreach ($array as $item) {
                    if (($mapper->verificar($item["patente"], $item["aduana"], $item["pedimento"], $item["referencia"])) == false) {
                        $mapper->agregar($item["operacion"], $item["tipoOperacion"], $item["patente"], $item["aduana"], $item["pedimento"], $item["referencia"], $item["fechaPago"], $item["rfc"]);
                    }
                }
                unset($item);
            }
            echo $view->render("gearman-pedimento.phtml");
            return false;
        }
    }

    /**
     * /automatizacion/ws/gearman-detalle?rfc=CTM990607US8
     * /automatizacion/ws/gearman-detalle?rfc=CTM990607US8&patente=3589&aduana=240&year=2016&month=1
     * su - www-data -c 'php /var/www/workers/trafico_worker.php'
     * 
     */
    public function gearmanDetalleAction() {
        $filters = array(
            "*" => array("StringTrim", "StripTags"),
            "patente" => array("Digits"),
            "aduana" => array("Digits"),
            "month" => array("Digits"),
            "year" => array("Digits"),
            "rfc" => "StringToUpper",
        );
        $validator = array(
            "patente" => array(new Zend_Validate_Int()),
            "aduana" => array(new Zend_Validate_Int()),
            "month" => new Zend_Validate_Int(),
            "year" => new Zend_Validate_Int(),
            "rfc" => array(new Zend_Validate_Regex("/^[A-Z0-9]+$/")),
            "fecha" => array(new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")),
        );
        $input = new Zend_Filter_Input($filters, $validator, $this->_request->getParams());
        if ($input->isValid()) {
            $client = new GearmanClient();
            $client->addServer("127.0.0.1", 4730);
            $mapper = new Automatizacion_Model_WsPedimentosMapper();
            $array = $mapper->obtenerSinDetalle($input->rfc, $input->patente, $input->aduana, $input->year, $input->month);
            $view = new Zend_View();
            $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/index/");
            if (isset($array) && !empty($array)) {
                $view->arr = $array;
                foreach ($array as $item) {
                    $array = array(
                        'patente' => $item["patente"],
                        'aduana' => $item["aduana"],
                        'pedimento' => $item["pedimento"],
                        'operacion' => $item["operacion"],
                        'tipoOperacion' => $item["tipoOperacion"],
                        'referencia' => $item["referencia"],
                    );
                    $client->addTaskBackground("detalle", serialize($array));
                    $client->runTasks();
                }
            }
            echo $view->render("gearman-detalle.phtml");
        }
    }

    /**
     * /automatizacion/ws/gearman-anexo?rfc=CTM990607US8&patente=3589&aduana=640
     * /automatizacion/ws/gearman-anexo?rfc=JMM931208JY9&patente=3589&aduana=240&pedimento=4002084
     * /automatizacion/ws/gearman-anexo?rfc=CTM990607US8&patente=3933&aduana=430
     * 
     */
    public function gearmanAnexoAction() {
        $filters = array(
            '*' => array('StringTrim', 'StripTags'),
            'patente' => array('Digits'),
            'aduana' => array('Digits'),
            'month' => array('Digits'),
            'year' => array('Digits'),
            'rfc' => 'StringToUpper',
            'pedimento' => array('Digits'),
        );
        $validator = array(
            'patente' => array(new Zend_Validate_Int()),
            'aduana' => array(new Zend_Validate_Int()),
            'pedimento' => array(new Zend_Validate_Int()),
            'month' => new Zend_Validate_Int(),
            'year' => new Zend_Validate_Int(),
            'rfc' => array(new Zend_Validate_Regex('/^[A-Z0-9]+$/')),
            'fecha' => array(new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")),
        );
        $input = new Zend_Filter_Input($filters, $validator, $this->_request->getParams());
        if ($input->isValid()) {
            $client = new GearmanClient();
            $client->addServer('127.0.0.1', 4730);
            $mapper = new Automatizacion_Model_WsPedimentosMapper();
            $array = $mapper->obtenerSinAnexo($input->rfc, $input->patente, $input->aduana, $input->year, $input->month, $input->pedimento, $input->fecha);
            $view = new Zend_View();
            $view->setScriptPath(realpath(dirname(__FILE__)) . "/../views/scripts/index/");
            if (isset($array) && !empty($array)) {
                $view->arr = $array;
                foreach ($array as $item) {
                    $array = array(
                        'patente' => $item["patente"],
                        'aduana' => $item["aduana"],
                        'pedimento' => $item["pedimento"],
                        'operacion' => $item["operacion"],
                        'tipoOperacion' => $item["tipoOperacion"],
                        'referencia' => $item["referencia"],
                    );
                    $client->addTaskBackground("anexo", serialize($array));
                    $client->runTasks();
                }
            }
            echo $view->render("gearman-anexo.phtml");
        }
    }

    /**
     * /automatizacion/ws/detalle-pedimentos?patente=3589&aduana=640&fecha=2014-07-31&rfc=CIN0309091D3
     * /automatizacion/ws/detalle-pedimentos?patente=3589&aduana=640&year=2014&month=7&rfc=CIN0309091D3
     * /automatizacion/ws/detalle-pedimentos?patente=3589&aduana=640&year=2014&month=8&rfc=CTM990607US8
     */
    public function detallePedimentosAction() {
        ini_set('default_socket_timeout', 1200);
        ini_set("soap.wsdl_cache_enabled", 0);
        $det = new Automatizacion_Model_WsDetallePedimentosMapper();
        $fecha = $this->_request->getParam("fecha", null);
        $rfc = $this->_request->getParam("rfc", null);
        $month = $this->_request->getParam("month", null);
        $year = $this->_request->getParam("year", null);
        $patente = $this->_request->getParam("patente", null);
        $aduana = $this->_request->getParam("aduana", null);
        $wsdl = $this->getWsdl($patente, $aduana);
        $context = stream_context_create(array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
                "allow_self_signed" => true
            )
        ));
        if (isset($wsdl)) {
            $soap = new Zend_Soap_Client($wsdl, array('compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_DEFLATE, array("stream_context" => $context)));
            if (isset($fecha) && isset($rfc)) {
                $pedimentos = $soap->pedimentosDelDia($patente, $aduana, $fecha, $rfc);
            } elseif (isset($year) && isset($month) && isset($rfc)) {
                $pedimentos = $soap->pedimentosDelMes($patente, $aduana, $year, $month, $rfc);
            }
            if (isset($pedimentos) && !empty($pedimentos)) {
                foreach ($pedimentos as $item) {
                    if (($det->verificar($item["patente"], $item["aduana"], $item["pedimento"], $item["referencia"])) == false) {
                        $detalle = $soap->detallePedimento($item["patente"], $item["aduana"], $item["pedimento"]);
                        $det->agregar($item["operacion"], $item["tipoOperacion"], $item["patente"], $item["aduana"], $item["pedimento"], $item["referencia"], $detalle);
                    }
                    unset($detalle);
                }
            }
            unset($pedimentos);
        }
    }

    /**
     * /automatizacion/ws/descarga-detalle?rfc=CIN0309091D3
     * /automatizacion/ws/descarga-detalle?rfc=JMM931208JY9
     * /automatizacion/ws/descarga-detalle?rfc=CTM990607US8
     */
    public function descargaDetalleAction() {
        $ped = new Automatizacion_Model_WsPedimentosMapper();
        $det = new Automatizacion_Model_WsDetallePedimentosMapper();
        $rfc = $this->_request->getParam("rfc", null);
        $fecha = $this->_request->getParam("fecha", null);
        $patente = $this->_request->getParam("patente", null);
        $aduana = $this->_request->getParam("aduana", null);
        $pedimentos = $ped->obtenerSinDetalle($rfc, $patente, $aduana, null, null, $fecha);
        $context = stream_context_create(array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
                "allow_self_signed" => true
            )
        ));
        if (isset($pedimentos) && !empty($pedimentos)) {
            foreach ($pedimentos as $item) {
                $wsdl = $this->getWsdl($item["patente"], $item["aduana"]);
                if (isset($wsdl)) {
                    $soap = new Zend_Soap_Client($wsdl, array('compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_DEFLATE, array("stream_context" => $context)));
                    if (($det->verificar($item["patente"], $item["aduana"], $item["pedimento"], $item["referencia"])) == false) {
                        $detalle = $soap->detallePedimento($item["patente"], $item["aduana"], $item["pedimento"]);
                        Zend_Debug::Dump($detalle);
                        $det->agregar($item["operacion"], $item["tipoOperacion"], $item["patente"], $item["aduana"], $item["pedimento"], $item["referencia"], $detalle);
                    }
                }
            }
        }
    }

    /**
     * /automatizacion/ws/descarga-anexo?rfc=JMM931208JY9
     * /automatizacion/ws/descarga-anexo?rfc=CTM990607US8
     * /automatizacion/ws/descarga-anexo?rfc=CTM990607US8&patente=3574&aduana=160
     */
    public function descargaAnexoAction() {
        $ane = new Automatizacion_Model_WsAnexoPedimentosMapper();
        $ped = new Automatizacion_Model_WsPedimentosMapper();
        $rfc = $this->_request->getParam("rfc", null);
        $year = $this->_request->getParam("year", null);
        $month = $this->_request->getParam("month", null);
        $aduana = $this->_request->getParam("aduana", null);
        $patente = $this->_request->getParam("patente", null);
        $pedimentos = $ped->obtenerSinAnexo($rfc, $patente, $aduana, $year, $month);
        $context = stream_context_create(array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
                "allow_self_signed" => true
            )
        ));
        if (isset($pedimentos) && !empty($pedimentos)) {
            $i = 0;
            foreach ($pedimentos as $item) {
                $wsdl = $this->getWsdl($item["patente"], $item["aduana"]);
                if (isset($wsdl)) {
                    $soap = new Zend_Soap_Client($wsdl, array('compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_DEFLATE, array("stream_context" => $context)));
                    if (($ane->verificar($item["patente"], $item["aduana"], $item["pedimento"], $item["referencia"])) == false) {
                        $anexo = $soap->anexo24ExtendidoPedimento($item["patente"], $item["aduana"], $item["pedimento"]);
                        foreach ($anexo as $anexoped) {
                            $ane->agregar($item["operacion"], $item["tipoOperacion"], $item["patente"], $item["aduana"], $item["pedimento"], $item["referencia"], $anexoped);
                            unset($anexoped);
                        }
                        unset($anexo);
                    }
                    $i++;
                    if ($i == 2) {
                        break;
                    }
                }
            }
        }
    }

    /**
     * /automatizacion/ws/anexo-pedimentos?fecha=2014-07-31
     */
    public function anexoPedimentosAction() {
        ini_set('default_socket_timeout', 1200);
        ini_set("soap.wsdl_cache_enabled", 0);
        $ane = new Automatizacion_Model_WsAnexoPedimentosMapper();
        $ped = new Automatizacion_Model_WsPedimentosMapper();
        $fecha = $this->_request->getParam("fecha", null);
        $patente = $this->_request->getParam("patente", null);
        $aduana = $this->_request->getParam("aduana", null);
        $pedimento = $this->_request->getParam("pedimento", null);
        $context = stream_context_create(array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
                "allow_self_signed" => true
            )
        ));
        if (!isset($fecha)) {
            $fecha = date('Y-m-d');
        }
        if (!isset($patente) && !isset($aduana) && !isset($pedimento)) {
            foreach ($this->_servers as $ws) {
                $soap = new Zend_Soap_Client($ws["url"], array("stream_context" => $context));
                $pedimentos = $soap->pedimentosDelDia($ws["patente"], $ws["aduana"], $fecha);
                if (isset($pedimentos) && !empty($pedimentos)) {
                    foreach ($pedimentos as $item) {
                        $anexo = $soap->anexo24ExtendidoPedimento($item["patente"], $item["aduana"], $item["pedimento"]);
                        if (($ane->verificar($item["patente"], $item["aduana"], $item["pedimento"], $item["referencia"])) == false) {
                            foreach ($anexo as $anexoped) {
                                $ane->agregar($item["operacion"], $item["tipoOperacion"], $item["patente"], $item["aduana"], $item["pedimento"], $item["referencia"], $anexoped);
                                unset($anexoped);
                            }
                        }
                        unset($anexo);
                    }
                    unset($item);
                }
                unset($pedimentos);
            }
        } else {
            $buscar = $ped->buscar($patente, $aduana, $pedimento);
            if (!empty($buscar)) {
                $wsdl = str_replace('?wsdl', '', $this->getWsdl($patente, $aduana));
                if (($ane->verificar($patente, $aduana, $pedimento, $buscar["referencia"])) == false) {
                    $anexo = $this->anexo24ExtendidoPedimento($wsdl, $patente, $aduana, $pedimento);
                    foreach ($anexo as $anexoped) {
                        $ane->agregar($buscar["operacion"], $buscar["tipoOperacion"], $patente, $aduana, $pedimento, $buscar["referencia"], $anexoped);
                        unset($anexoped);
                    }
                } else {
                    echo "Ya existe";
                }
            }
        }
    }

    protected function anexo24ExtendidoPedimento($wsdl, $patente, $aduana, $pedimento) {
        $envelope = '<soapenv:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:zfs="' . $wsdl . '">
   <soapenv:Header/>
   <soapenv:Body>
      <zfs:anexo24ExtendidoPedimento>
         <patente>' . $patente . '</patente>
         <aduana>' . $aduana . '</aduana>
         <pedimento>' . $pedimento . '</pedimento>
      </zfs:anexo24ExtendidoPedimento>
   </soapenv:Body>
</soapenv:Envelope>';
        $misc = new OAQ_Misc();
        $result = $this->makeCurlAction($wsdl, $envelope, "anexo24ExtendidoPedimento");
        $array = $misc->xmlToArray($result);
        $data = array();
        if (count($array["Body"]["anexo24ExtendidoPedimentoResponse"]["return"]["item"]) > 1) {
            foreach ($array["Body"]["anexo24ExtendidoPedimentoResponse"]["return"]["item"] as $k => $v) {
                foreach ($v as $param) {
                    foreach ($param as $value) {
                        if (!isset($data[$k][$value["key"]])) {
                            $data[$k][$value["key"]] = is_array($value["value"]) ? null : $value["value"];
                        }
                    }
                }
            }
        } elseif (count($array["Body"]["anexo24ExtendidoPedimentoResponse"]["return"]["item"]) == 1) {
            foreach ($array["Body"]["anexo24ExtendidoPedimentoResponse"]["return"]["item"]["item"] as $param) {
                if (!isset($data[0][$param["key"]])) {
                    $data[0][$param["key"]] = is_array($param["value"]) ? null : $param["value"];
                }
            }
        }
        return $data;
    }

    protected function makeCurlAction($wsdl, $envelope, $action) {
        $headers = array(
            "Accept-Encoding: gzip,deflate",
            "Content-Type: text/xml;charset=UTF-8",
            "SOAPAction: \"{$wsdl}#{$action}\"",
            "Content-length: " . strlen($envelope),
            "Keep-Alive: 300",
            "Connection: Keep-Alive",
            "User-Agent: Apache-HttpClient/4.1.1 (java 1.5)",
            "Cache-Control: max-age=0"
        );
        $soap_do = curl_init();
        curl_setopt($soap_do, CURLOPT_URL, $wsdl);
        curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 300);
        curl_setopt($soap_do, CURLOPT_TIMEOUT, 300);
        curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($soap_do, CURLOPT_POSTFIELDS, $envelope);
        curl_setopt($soap_do, CURLOPT_VERBOSE, TRUE);
        curl_setopt($soap_do, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($soap_do);
        if ($result === false) {
            return 'Curl error: ' . curl_error($soap_do);
        }
        return $result;
    }

    protected function isRunning($pid) {
        try {
            $result = shell_exec(sprintf('ps %d', $pid));
            if (count(preg_split("/\n/", $result)) > 2) {
                return true;
            }
        } catch (Exception $e) {
            
        }
        return false;
    }

    public function pedimentosPagadosCurlAction() {
        $wsdl = "https://aamarron.dynamic-dns.net:8443/zfsoapcasa";
        $rfc = "CTM990607US8";
        $patente = 3589;
        $aduana = 370;
        $year = 2013;
        $month = 1;

        $envelope = '<soapenv:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:zfs="https://aamarron.dynamic-dns.net:8443/zfsoapcasa">
   <soapenv:Header/>
   <soapenv:Body>
      <zfs:pedimentoPagados soapenv:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
         <rfc xsi:type="xsd:string" xs:type="type:string" xmlns:xs="http://www.w3.org/2000/XMLSchema-instance">CTM990607US8</rfc>
         <patente xsi:type="xsd:int" xs:type="type:int" xmlns:xs="http://www.w3.org/2000/XMLSchema-instance">3589</patente>
         <aduana xsi:type="xsd:int" xs:type="type:int" xmlns:xs="http://www.w3.org/2000/XMLSchema-instance">370</aduana>
         <year xsi:type="xsd:int" xs:type="type:int" xmlns:xs="http://www.w3.org/2000/XMLSchema-instance">2013</year>
         <mes xsi:type="xsd:int" xs:type="type:int" xmlns:xs="http://www.w3.org/2000/XMLSchema-instance">1</mes>
      </zfs:pedimentoPagados>
   </soapenv:Body>
</soapenv:Envelope>';

        $misc = new OAQ_Misc();
        $result = $this->makeCurlAction($wsdl, $envelope, "pedimentoPagados");
        Zend_Debug::dump($result);
        $array = $misc->xmlToArray($result);
        Zend_Debug::dump($array);
    }

    public function _obtenerSistema($idAduana) {
        try {
            $sis = new Application_Model_ServiciosRestAduana();
            $row = $sis->obtenerSistema($idAduana);
            if (!empty($row) && isset($row["idServicio"])) {
                $mppr = new Application_Model_ServiciosRest();
                if (($arr = $mppr->obtener($row["idServicio"]))) {

                    $client = new Zend_Rest_Client($arr['url']);
                    $httpClient = $client->getHttpClient();
                    $httpClient->setConfig(array('timeout' => 360));
                    $client->setHttpClient($httpClient);

                    $this->sistema = $arr['sistema'];
                    $this->patente = $arr['patente'];
                    $this->aduana = $arr['aduana'];
                }
            }
            if (isset($client)) {
                return $client;
            }
            return;
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function _obtenerServicio($idAduana, $sistema) {
        try {
            $sis = new Application_Model_ServiciosRestAduana();
            $row = $sis->obtenerServicio($idAduana, $sistema);
            if (!empty($row) && isset($row["idServicio"])) {
                $mppr = new Application_Model_ServiciosRest();
                if (($arr = $mppr->obtener($row["idServicio"]))) {

                    $client = new Zend_Rest_Client($arr['url']);
                    $httpClient = $client->getHttpClient();
                    $httpClient->setConfig(array('timeout' => 360));
                    $client->setHttpClient($httpClient);

                    $this->sistema = $arr['sistema'];
                    $this->patente = $arr['patente'];
                    $this->aduana = $arr['aduana'];
                }
            }
            if (isset($client)) {
                return $client;
            }
            return;
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    /**
     * /automatizacion/ws/rest-pedimentos?idAduana=3&year=2018&month=3&rfcCliente=JMM931208JY9
     * /automatizacion/ws/rest-pedimentos?idAduana=3&year=2018&month=8&rfcCliente=STE071214BE7
     * 
     */
    public function restPedimentosAction() {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idAduana" => "Digits",
                "month" => "Digits",
                "year" => "Digits",
                "rfcCliente" => "StringToUpper",
                "sistema" => "StringToLower",
            );
            $v = array(
                "idAduana" => array("NotEmpty", new Zend_Validate_Int()),
                'fecha' => array(new Zend_Validate_Regex("/^\d{4}\-\d{2}\-\d{2}$/")),
                'month' => array("NotEmpty", new Zend_Validate_Int()),
                'year' => array("NotEmpty", new Zend_Validate_Int()),
                'rfcCliente' => array("NotEmpty"),
                'sistema' => array("NotEmpty"),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            $mppr = new Operaciones_Model_ClientesAnexo24Mapper();
            if ($input->isValid("rfcCliente")) {
                $customers = $mppr->todosAnexo($input->rfcCliente);
            } else {
                $customers = $mppr->todosAnexo();
            }
            $sis = new Application_Model_ServiciosRestAduana();
            if ($input->isValid("idAduana")) {
                $systems = $sis->obtenerSistemas($input->idAduana);
            } else {
                $systems = $sis->obtenerSistemas();
            }
            $arr_data = array();
            if (isset($customers) && !empty($customers)) {

                foreach ($customers as $rfcCliente) {

                    if (isset($systems) && !empty($systems)) {

                        foreach ($systems as $value) {
                            if (!$input->isValid('sistema')) {
                                $client = $this->_obtenerSistema($value["idAduana"]);
                            } else {
                                $client = $this->_obtenerServicio($value["idAduana"], $input->sistema);
                            }
                            /* if (in_array($value["idServicio"], array(6))) {
                              continue;
                              } */
                            $arr = array();
                            if ($client) {
                                $mppr = new Automatizacion_Model_RptPedimentos();
                                if ($input->isValid("fecha")) {
                                    $response = $client->restPost("/{$this->sistema}/operaciones", array(
                                        'patente' => $this->patente,
                                        'aduana' => $this->aduana,
                                        'fecha' => $input->fecha,
                                        'rfcCliente' => $rfcCliente,
                                    ));
                                    if ($response->getBody()) {
                                        $arr[$input->fecha] = $this->_pedimentoResponse($mppr, $value["idAduana"], $response->getBody());
                                    }
                                    $arr_data[$rfcCliente][$value["idAduana"]] = $arr;
                                }
                                if ($input->isValid("month") && $input->isValid("year")) {
                                    $response = $client->restPost("/{$this->sistema}/operaciones-mes", array(
                                        'patente' => $this->patente,
                                        'aduana' => $this->aduana,
                                        'year' => $input->year,
                                        'mes' => $input->month,
                                        'rfcCliente' => $rfcCliente,
                                    ));
                                    if ($response->getBody()) {
                                        $arr[] = $this->_pedimentoResponse($mppr, $value["idAduana"], $response->getBody());
                                    }
                                    $arr_data[$rfcCliente][$value["idAduana"]] = $arr;
                                }
                            } // client 
                        }
                    }
                }
            } else {
                throw new Exception("RFC no existe en la base de datos.");
            }
            $this->_helper->json(array("success" => true, "rows" => $arr_data));
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    protected function _pedimentoResponse(Automatizacion_Model_RptPedimentos $mppr, $idAduana, $body) {
        $arr = array();
        if ($body) {
            $row = json_decode($body, true);
            if (isset($row["response"]["error"]) && $row["response"]["error"] == false) {
                if (!empty($row["response"]["results"])) {
                    $results = $row["response"]["results"];
                    for ($i = 0; $i < count($results); $i++) {
                        $arr[] = $this->_verificarPedimento($mppr, $idAduana, $results[$i]);
                    }
                }
                return $arr;
            } else {
                return $arr[] = $row["response"]["message"];
            }
        } else {
            return $arr[] = "No response from server.";
        }
    }

    /**
     * /automatizacion/ws/rest-detalle
     * https://oaq.dnsalias.net/automatizacion/ws/rest-detalle?idAduana=4
     * 
     */
    public function restDetalleAction() {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idAduana" => "Digits",
                "limit" => "Digits",
                "sistema" => "StringToLower",
            );
            $v = array(
                "idAduana" => array("NotEmpty", new Zend_Validate_Int()),
                "limit" => array("NotEmpty", new Zend_Validate_Int()),
                'sistema' => array("NotEmpty"),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());

            $mppr = new Automatizacion_Model_RptPedimentos();
            $mdl = new Automatizacion_Model_RptPedimentoDetalle();

            $arr = $mppr->wsSinDetalle($input->idAduana, $input->limit);
            if (!empty($arr)) {
                $array = array();
                for ($i = 0; $i < count($arr); $i++) {

                    if (!$input->isValid('sistema')) {
                        $client = $this->_obtenerSistema($arr[$i]["idAduana"]);
                    } else {
                        $client = $this->_obtenerServicio($arr[$i]["idAduana"], $input->sistema);
                    }

                    if ($client) {
                        $response = $client->restPost("/{$this->sistema}/detalle-operacion", array(
                            'patente' => $this->patente,
                            'aduana' => $this->aduana,
                            'pedimento' => $arr[$i]["pedimento"],
                            'referencia' => $arr[$i]["referencia"],
                        ));
                        if ($response->getBody()) {
                            $row = json_decode($response->getBody(), true);
                            if (isset($row["response"]["error"]) && $row["response"]["error"] == false) {
                                $results = $row["response"]["results"];
                                if (($added = $this->_verificarDetallePedimento($mdl, $arr[$i]["id"], $results))) {
                                    $array[] = $added;
                                    $mppr->actualizar($arr[$i]["id"], array(
                                        "detalle" => 1,
                                    ));
                                }
                            } else {
                                continue;
                            }
                        }
                    }
                }
                $this->_helper->json(array("success" => true, "rows" => $array));
            } else {
                $this->_helper->json(array("success" => true, "message" => 'Nothing to process!'));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    /**
     * /automatizacion/ws/rest-desglose?idAduana=1
     * /automatizacion/ws/rest-desglose?idPedimento=1352
     * /automatizacion/ws/rest-desglose?limit=1
     * https://oaq.dnsalias.net/automatizacion/ws/rest-desglose?idAduana=4
     * 
     */
    public function restDesgloseAction() {
        try {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "idAduana" => "Digits",
                "idPedimento" => "Digits",
                "limit" => "Digits",
                "sistema" => "StringToLower",
            );
            $v = array(
                "idAduana" => array("NotEmpty", new Zend_Validate_Int()),
                "idPedimento" => array("NotEmpty", new Zend_Validate_Int()),
                "limit" => array("NotEmpty", new Zend_Validate_Int()),
                'sistema' => array("NotEmpty"),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());

            $mppr = new Automatizacion_Model_RptPedimentos();
            $mdl = new Automatizacion_Model_RptPedimentoDesglose();

            $arr = $mppr->wsSinAnexo($input->idAduana, $input->idPedimento, $input->limit);

            if (!empty($arr)) {
                $array = array();
                for ($i = 0; $i < count($arr); $i++) {

                    if (!$input->isValid('sistema')) {
                        $client = $this->_obtenerSistema($arr[$i]["idAduana"]);
                    } else {
                        $client = $this->_obtenerServicio($arr[$i]["idAduana"], $input->sistema);
                    }

                    if (!$client) {
                        $array[] = array(
                            "estatus" => "No system found.",
                            "idPedimento" => $arr[$i]['id'],
                        );
                    }
                    if ($client) {
                        if (in_array($arr[$i]["idAduana"], array(3, 6, 4, 19))) {
                            $response = $client->restPost("/slam/desglose-operacion", array(
                                'patente' => $this->patente,
                                'aduana' => $this->aduana,
                                'pedimento' => $arr[$i]["pedimento"],
                                'referencia' => $arr[$i]["referencia"],
                            ));
                            $source = '/slam/desglose-operacion';
                            if ($response->getBody()) {
                                $row = json_decode($response->getBody(), true);
                                if (isset($row["response"]["error"]) && $row["response"]["error"] == true) {
                                    $response = $client->restPost("/slam/desglose-operacion-alt", array(
                                        'patente' => $this->patente,
                                        'aduana' => $this->aduana,
                                        'pedimento' => $arr[$i]["pedimento"],
                                        'referencia' => $arr[$i]["referencia"],
                                    ));
                                }
                                $source = '/slam/desglose-operacion-alt';
                            }
                        } else {
                            $response = $client->restPost("/{$this->sistema}/desglose-operacion", array(
                                'patente' => $this->patente,
                                'aduana' => $this->aduana,
                                'pedimento' => $arr[$i]["pedimento"],
                                'referencia' => $arr[$i]["referencia"],
                            ));
                            $source = '/slam/desglose-operacion';
                        }

                        if ($response->getBody()) {
                            $row = json_decode($response->getBody(), true);

                            if (isset($row["response"]["error"]) && $row["response"]["error"] == true) {
                                $array[] = array(
                                    "estatus" => $row["response"]["message"],
                                    "idPedimento" => $arr[$i]['id'],
                                    "source" => $source,
                                );
                                $updated = $mppr->actualizar($arr[$i]["id"], array(
                                    "error" => 1,
                                    "mensaje" => $row["response"]["message"],
                                ));
                            }

                            if (isset($row["response"]["error"]) && $row["response"]["error"] == false) {
                                $results = $row["response"]["results"];

                                if (($added = $this->_verificarDesglosePedimento($mdl, $arr[$i]["id"], $results))) {
                                    $array[] = $added;
                                    $updated = $mppr->actualizar($arr[$i]["id"], array(
                                        "anexo" => 1,
                                    ));
                                }
                            } else {
                                // OPERACION DE EXPORTACION, CON SLAM SIN DATOS
                                if ($arr[$i]["idAduana"] == 7 && $arr[$i]["tipoOperacion"] == 2) {
                                    $updated = $mppr->actualizar($arr[$i]["id"], array(
                                        "anexo" => 1,
                                    ));
                                    $array[] = array(
                                        "estatus" => "No data found idAduana = {$arr[$i]["idAduana"]} and reference = {$arr[$i]["referencia"]}.",
                                        "idPedimento" => $arr[$i]['id'],
                                    );
                                } else if ($arr[$i]["idAduana"] == 7 && (preg_match('/A$/i', $arr[$i]["referencia"]) || preg_match('/R$/i', $arr[$i]["referencia"]))) {
                                    $updated = $mppr->actualizar($arr[$i]["id"], array(
                                        "anexo" => 1,
                                    ));
                                    $array[] = array(
                                        "estatus" => "No data found idAduana = {$arr[$i]["idAduana"]} and reference = {$arr[$i]["referencia"]}.",
                                        "idPedimento" => $arr[$i]['id'],
                                    );
                                } else {
                                    
                                }
                                continue;
                            }
                        }
                    }
                }
                $this->_helper->json(array("success" => true, "rows" => $array));
            } else {
                $this->_helper->json(array("success" => true, "message" => 'Nothing to process!'));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    protected function _verificarPedimento(Automatizacion_Model_RptPedimentos $mppr, $idAduana, $row) {
        if (($mppr->verificarPedimento($idAduana, $row["patente"], $row["aduana"], $row["pedimento"], $row["referencia"])) == false) {
            if ($mppr->agregarPedimento($idAduana, $row)) {
                return array(
                    "estatus" => "Record added.",
                    "patente" => $row["patente"],
                    "aduana" => $row["aduana"],
                    "pedimento" => $row["pedimento"],
                    "referencia" => $row["referencia"],
                );
            }
        }
        return array(
            "estatus" => "Record already exists.",
            "patente" => $row["patente"],
            "aduana" => $row["aduana"],
            "pedimento" => $row["pedimento"],
            "referencia" => $row["referencia"],
        );
    }

    protected function _verificarDetallePedimento(Automatizacion_Model_RptPedimentoDetalle $mppr, $idPedimento, $row) {
        if (!($mppr->verificarDetallePedimento($idPedimento))) {
            $mppr->agregarDetallePedimento($idPedimento, $row);
            return array(
                "estatus" => "Record added.",
                "idPedimento" => $idPedimento,
            );
        }
        return;
    }

    protected function _verificarDesglosePedimento(Automatizacion_Model_RptPedimentoDesglose $mppr, $idPedimento, $rows) {
        if (!($mppr->verificarDesglosePedimento($idPedimento))) {
            if (!empty($rows)) {
                for ($i = 0; $i < count($rows); $i++) {
                    $mppr->agregarDesglosePedimento($idPedimento, $rows[$i]);
                }
            }
            return array(
                "estatus" => "Record(s) added: " . count($rows) . " parts.",
                "idPedimento" => $idPedimento,
            );
        }
        return;
    }

    /**
     * /automatizacion/ws/anexo-veinticuatro
     * $this->runWorkers(4, "/var/www/workers/trafico_worker.php");
     * su - www-data -c 'php /var/www/workers/trafico_worker.php'  
     */
    public function anexoVeinticuatroAction() {
        ini_set('default_socket_timeout', 1200);
        ini_set("soap.wsdl_cache_enabled", 0);
        $ped = new Automatizacion_Model_WsPedimentosMapper();
        $mapper = new Operaciones_Model_ClientesAnexo24Mapper();
        $fecha = $this->_request->getParam("fecha", null);
        $listaRfc = $mapper->todosAnexo();
        $aduanas = array(
//            "3589" => array("640", "240", "800"),
            "3574" => array("160", "470", "800", "240"),
        );
        if (!isset($fecha)) {
            $fecha = date("Y-m-d");
        }
        $month = date("m");
        $context = stream_context_create(array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
                "allow_self_signed" => true
            )
        ));
        $email = new OAQ_EmailNotifications();
        $m = new Application_Model_WsWsdl();
        foreach ($listaRfc as $rfc) {
            foreach ($aduanas as $k => $v) {
                foreach ($v as $a) {
                    $wsdl = $m->getWsdlPedimentos($k, $a);
                    if (isset($wsdl) && $wsdl != "") {
                        try {
                            $soap = new Zend_Soap_Client($wsdl, array("compression" => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_DEFLATE, "stream_context" => $context));
                            if (isset($fecha) && isset($rfc)) {
                                $pedimentos = $soap->pedimentosDelMes($k, $a, date("Y"), date("m"), $rfc);
                                if (isset($pedimentos) && !empty($pedimentos)) {
                                    foreach ($pedimentos as $item) {
                                        if (($ped->verificar($item["patente"], $item["aduana"], $item["pedimento"], $item["referencia"])) == false) {
                                            $ped->agregar($item["operacion"], $item["tipoOperacion"], $item["patente"], $item["aduana"], $item["pedimento"], $item["referencia"], $item["fechaPago"], $item["rfc"]);
                                        }
                                    }
                                    $params = "patente={$k}&aduana={$a}&year=" . date("Y") . "&month=" . date("m") . "&rfc={$rfc}";
                                    $ch = curl_init();
                                    curl_setopt($ch, CURLOPT_URL, "https://127.0.0.1//automatizacion/ws/gearman-detalle?" . $params);
                                    curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
                                    curl_setopt($ch, CURLOPT_TIMEOUT, 1);
                                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                                    curl_exec($ch);
                                    curl_close($ch);
                                    $ch2 = curl_init();
                                    curl_setopt($ch2, CURLOPT_URL, "https://127.0.0.1/automatizacion/ws/gearman-anexo?" . $params);
                                    curl_setopt($ch2, CURLOPT_FRESH_CONNECT, 1);
                                    curl_setopt($ch2, CURLOPT_TIMEOUT, 1);
                                    curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, 0);
                                    curl_setopt($ch2, CURLOPT_SSL_VERIFYHOST, 0);
                                    curl_exec($ch2);
                                    curl_close($ch2);
                                }
                            }
                        } catch (Exception $ex) {
                            $email->sendInfraEmail(" WSDL Error : ", "<p>" . "Error: " . $wsdl . " not responding or not available!" . "</p>");
                            continue;
                        }
                    }
                }
            }
            $this->newBackgroundWorker("trafico_worker", 5);
        }
    }

    protected function newBackgroundWorker($worker, $maximum) {
        $process = new Archivo_Model_PidMapper();
        if ($worker == 'trafico_worker') {
            for ($i = 0; $i < $maximum; $i++) {
                if (!($pids = $process->checkRunnigProcess("trafico_worker"))) {
                    $newPid = shell_exec(sprintf('%s > /dev/null 2>&1 & echo $!', "php /var/www/portalprod/workers/trafico_worker.php"));
                    $process->addNewProcess($newPid, "trafico_worker", "php /var/www/portalprod/workers/trafico_worker.php");
                } else {
                    foreach ($pids as $k => $p) {
                        if (!$this->isRunning($p['pid'])) {
                            $process->deleteProcess($p['pid']);
                            unset($pids[$k]);
                        }
                    }
                    if (count($pids) < $maximum) {
                        $newPid = shell_exec(sprintf('%s > /dev/null 2>&1 & echo $!', "php /var/www/portalprod/workers/trafico_worker.php"));
                        $process->addNewProcess($newPid, "trafico_worker", "php /var/www/portalprod/workers/trafico_worker.php");
                    }
                }
            }
        }
    }

    /**
     * /automatizacion/ws/pedimentos-curl
     * /automatizacion/ws/pedimentos-pagados?patente=3589&aduana=370&year=2014&month=10&rfc=CTM990607US8
     * 
     */
    public function pedimentosCurlAction() {
        $client = new GearmanClient();
        $client->addServer('127.0.0.1', 4730);
        $array = array(
            'rfc' => "WMO1004098Z6",
            'fecha' => date('Y-m-d'),
            'patente' => 3589,
            'aduana' => 640,
        );
        $client->addTaskBackground("pagados", serialize($array));
        $client->runTasks();
    }

    public function automatizacionAnexoAction() {
        $fecha = $this->_request->getParam('fecha', null);
        if (isset($fecha)) {
            echo $fecha . "\n";
        }
        $process = new Archivo_Model_PidMapper();
        for ($i = 0; $i < 4; $i++) {
            if (!($pids = $process->checkRunnigProcess("trafico_worker.php"))) {
                $newPid = shell_exec(sprintf('%s > /dev/null 2>&1 & echo $!', "php /var/www/workers/trafico_worker.php"));
                $process->addNewProcess($newPid, "trafico_worker.php", "php /var/www/workers/trafico_worker.php");
            } else {
                foreach ($pids as $k => $p) {
                    if (!$this->isRunning($p['pid'])) {
                        echo "{$p['pid']} is not runnig.\n";
                        $process->deleteProcess($p['pid']);
                        unset($pids[$k]);
                    } else {
                        echo "{$p['pid']} is runnig.\n";
                    }
                }
                if (count($pids) < 4) {
                    $newPid = shell_exec(sprintf('%s > /dev/null 2>&1 & echo $!', "php /var/www/workers/trafico_worker.php"));
                    $process->addNewProcess($newPid, "trafico_worker.php", "php /var/www/workers/trafico_worker.php");
                }
            }
        }

        $rfcs = array(
            'JMM931208JY9' => array(
                '3589' => array('640', '646', '240'),
                '3574' => array('160')
            ),
            'TEM670628A19' => array(
                '3589' => array('640', '646')
            ),
            'CTM990607US8' => array(
                '3589' => array('640', '646', '240', '370'),
                '3574' => array('160')
            ),
            'GCO980828GY0' => array(
                '3589' => array('640', '646', '240')
            ),
            'SED020516NM8' => array(
                '3589' => array('640', '646')
            ),
            'VEN940203EU6' => array(
                '3589' => array('640', '646')
            ),
            'GIV021204B1A' => array(
                '3589' => array('640', '646', '240')
            ),
            'DAL870401MGA' => array(
                '3589' => array('640', '646')
            ),
            'WMO1004098Z6' => array(
                '3589' => array('640', '646', '240')
            ),
            'MQU971209RQ1' => array(
                '3589' => array('640', '646', '240')
            ),
        );
        $client = new GearmanClient();
        $client->addServer('127.0.0.1', 4730);
        foreach ($rfcs as $r => $rfc) {
            foreach ($rfc as $p => $patente) {
                foreach ($patente as $aduana) {
                    $array = array(
                        'rfc' => $r,
                        'patente' => $p,
                        'aduana' => $aduana,
                        'year' => date('Y'),
                        'month' => date('m'),
                    );
                    $client->addTaskBackground("automatizacion", serialize($array));
                }
                $client->runTasks();
            }
        }
    }

    /**
     * /automatizacion/ws/new-extent
     * /automatizacion/ws/new-extent?patente=3574&aduana=160&pedimento=4000100&referencia=MI4-00100
     * @return type
     */
    public function newExtentAction() {
        $model = new Application_Model_WsWsdl();
        $array = $this->_request->getParams();
        $url = $model->getWsdl($array["patente"], $array["aduana"], 'casa');
        if (!isset($url)) {
            $url = $model->getWsdl($array["patente"], $array["aduana"], 'sitawin');
        }
        if (!isset($url)) {
            $url = $model->getWsdl($array["patente"], $array["aduana"], 'aduanet');
        }
        if (isset($url)) {
            $wsdl = str_replace('?wsdl', '', $url);
        } else {
            echo "No WSDL found";
            return;
        }
        ///////////////////////////////////////////////////////////////////////
        if (preg_match('/^470/', $array["aduana"])) {
            $result = $this->_anexo24ExtendidoPedimentoSecundario($wsdl, $array["patente"], $array["aduana"], $array["pedimento"]);
        } else {
            $result = $this->_anexo24ExtendidoPedimento($wsdl, $array["patente"], $array["aduana"], $array["pedimento"]);
            if (!isset($result) && isset($array["referencia"])) {
                $slamWsdl = str_replace('?wsdl', '', $model->getWsdl($array["patente"], $array["aduana"], 'slam'));
                if (isset($slamWsdl)) {
                    $result = $this->_facturasDeReferencia($slamWsdl, 'facturasDeReferencia', $array["referencia"]);
                    if (!isset($result) || $result == false) {
                        $result = $this->_facturasDeReferencia($slamWsdl, 'facturasDeReferenciaExp', $array["referencia"]);
                    }
                    $data = $this->_arrayToDatabase($result);
                }
            }
        }
        if ($result === false && preg_match('/^646/', $array["aduana"])) {
            $result = $this->_anexo24ExtendidoPedimento($wsdl, $array["patente"], 640, $array["pedimento"]);
        }
    }

    protected function _facturasDeReferencia($wsdl, $service, $referencia) {
        $envelope = '<soapenv:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:zfs="' . $wsdl . '">
   <soapenv:Header/>
   <soapenv:Body>
      <zfs:' . $service . ' soapenv:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
         <referencia xsi:type="xsd:string" xs:type="type:string" xmlns:xs="http://www.w3.org/2000/XMLSchema-instance">' . $referencia . '</referencia>
      </zfs:' . $service . '>
   </soapenv:Body>
</soapenv:Envelope>';

        $result = $this->_makeCurlAction($wsdl, $envelope, $service);
        $array = $this->_xmlToArray($result);
        Zend_Debug::dump($array);
        $data = array();
        if (isset($array["Body"][$service . "Response"]["return"]["item"])) {
            if (count($array["Body"][$service . "Response"]["return"]["item"]) > 1) {
                foreach ($array["Body"][$service . "Response"]["return"]["item"] as $k => $v) {
                    foreach ($v as $param) {
                        foreach ($param as $value) {
                            if (!isset($data[$k][$value["key"]]) && $value["key"] != 'Productos') {
                                $data[$k][$value["key"]] = is_array($value["value"]) ? null : $value["value"];
                            } elseif (!isset($data[$k][$value["key"]]) && $value["key"] == 'Productos') {
                                foreach ($value["value"]["item"] as $z => $prod) {
                                    if (isset($prod["item"])) {
                                        foreach ($prod["item"] as $r) {
                                            $data[$k][$value["key"]][$z][$r["key"]] = $r["value"];
                                        }
                                    } else {
                                        foreach ($prod as $r) {
                                            $data[$k][$value["key"]][$z][$r["key"]] = $r["value"];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            } elseif (count($array["Body"][$service . "Response"]["return"]["item"]) == 1) {
                foreach ($array["Body"][$service . "Response"]["return"]["item"]["item"] as $param) {
                    if (!isset($data[0][$param["key"]]) && $param["key"] != 'Productos') {
                        $data[0][$param["key"]] = is_array($param["value"]) ? null : $param["value"];
                    } elseif (!isset($data[0][$param["key"]]) && $param["key"] == 'Productos') {
                        if (is_array($param["value"]["item"])) {
                            foreach ($param["value"]["item"] as $k => $prod) {
                                if (isset($prod["item"])) {
                                    foreach ($prod["item"] as $r) {
                                        if (!is_array($r["value"])) {
                                            $data[0][$param["key"]][$k][$r["key"]] = $r["value"];
                                        }
                                    }
                                } else {
                                    foreach ($prod as $r) {
                                        if (!is_array($r["value"])) {
                                            $data[0][$param["key"]][$k][$r["key"]] = $r["value"];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } else {
            return false;
        }
        return $data;
    }

    protected function _arrayToDatabase($data) {
        if (is_array($data)) {
            foreach ($data as $item) {
                foreach ($item["Productos"] as $p) {
                    if ($item["Divisa"] != 'USD') {
                        $item["ValorFacturaUsd"] = $item["FactorMonExt"] * $item["ValorFactura"];
                    } else {
                        $item["ValorFacturaUsd"] = $item["ValorFactura"];
                    }
                    $p["PrecioUnitario"] = ($p["Total"] / $p["Cantidad"]);
                    $p["ValorMonExt"] = $p["Total"];

                    $temp['numFactura'] = $this->_value(array('NumFactura'), $item);
                    $temp['cove'] = $this->_value(array('cove', 'Cove'), $item);
                    $temp['ordenFactura'] = $this->_value(array('ordenFactura', 'OrdenFactura'), $item);
                    $temp['ordenCaptura'] = $this->_value(array('ordenCaptura', 'OrdenCaptura'), $item);
                    $temp['fechaFactura'] = $this->_value(array('fechaFactura', 'FechaFactura'), $item, true);
                    $temp['incoterm'] = $this->_value(array('incoterm', 'Incoterm'), $item);
                    $temp['valorFacturaUsd'] = $this->_value(array('valorFacturaUsd', 'ValorFacturaUsd'), $item);
                    $temp['valorFacturaMonExt'] = $this->_value(array('valorFacturaMonExt', 'ValorFacturaMonExt', 'ValorFactura'), $item);
                    $temp['taxId'] = $this->_value(array('taxId', 'TaxId'), $item);
                    $temp['cveProveedor'] = $this->_value(array('cveProveedor', 'CveProveedor'), $item);
                    $temp['nomProveedor'] = $this->_value(array('nomProveedor', 'NomProveedor'), $item);
                    $temp['paisFactura'] = $this->_value(array('paisFactura', 'PaisFactura'), $item);
                    $temp['factorMonExt'] = $this->_value(array('factorMonExt', 'FactorMonExt'), $item);
                    $temp['divisa'] = $this->_value(array('divisa', 'Divisa'), $item);
                    // productos
                    $temp['numParte'] = $this->_value(array('numParte', 'NumParte'), $p);
                    $temp['descripcion'] = $this->_value(array('descripcion', 'Descripcion'), $p);
                    $temp['fraccion'] = $this->_value(array('fraccion', 'Fraccion', 'NumFraccion'), $p);
                    $temp['ordenFraccion'] = $this->_value(array('ordenFraccion', 'OrdenFraccion', 'OrdenPedimento'), $p);
                    $temp['valorMonExt'] = $this->_value(array('valorMonExt', 'ValorMonExt'), $p);
                    $temp['valorAduanaMXN'] = $this->_value(array('valorAduanaMXN'), $p);
                    $temp['cantUMC'] = $this->_value(array('cantUMC', 'CantUMC', 'Cantidad'), $p);
                    $temp['abrevUMC'] = $this->_value(array('abrevUMC'), $p);
                    $temp['cantUMT'] = $this->_value(array('cantUMT', 'CantUMT', 'CantidadTarifa'), $p);
                    $temp['umc'] = $this->_value(array('umc', 'UMC'), $p);
                    $temp['umt'] = $this->_value(array('umt', 'UMT'), $p);
                    $temp['abrevUMT'] = $this->_value(array('abrevUMT'), $p);
                    $temp['cantOMA'] = $this->_value(array('cantOMA'), $p);
                    $temp['oma'] = $this->_value(array('oma'), $p);
                    $temp['umc'] = $this->_value(array('umc', 'UMC'), $p);
                    $temp['paisOrigen'] = $this->_value(array('paisOrigen', 'PaisOrigen'), $p);
                    $temp['paisVendedor'] = $this->_value(array('paisVendedor', 'PaisVendedor'), $p);
                    $temp['tasaAdvalorem'] = $this->_value(array('tasaAdvalorem', 'TasaAdvalorem', 'ADV'), $p);
                    $temp['formaPagoAdvalorem'] = $this->_value(array('formaPagoAdvalorem'), $p);
                    $temp['umc'] = $this->_value(array('umc', 'UMC'), $p);
                    $temp['iva'] = $this->_value(array('iva', 'IVA'), $p);
                    $temp['ieps'] = $this->_value(array('ieps', 'IEPS'), $p);
                    $temp['isan'] = $this->_value(array('isan', 'ISAN'), $p);
                    $temp['tlc'] = $this->_value(array('tlc', 'TLC'), $p);
                    $temp['tlcan'] = $this->_value(array('tlcan', 'TLCAN'), $p);
                    $temp['tlcue'] = $this->_value(array('tlcue', 'TLCUE'), $p);
                    $temp['prosec'] = $this->_value(array('prosec', 'PROSEC'), $p);
                    $temp['observacion'] = $this->_value(array('observacion', 'Observacion'), $p);
                    $temp['patenteOrig'] = $this->_value(array('patenteOrig', 'PatenteOriginal'), $p);
                    $temp['aduanaOrig'] = $this->_value(array('aduanaOrig', 'AduanaOriginal'), $p);
                    $temp['pedimentoOrig'] = $this->_value(array('pedimentoOrig', 'PedimentoOriginal'), $p);
                    $array[] = $temp;
                    unset($temp);
                }
            }
        } else {
            return false;
        }
        return $array;
    }

    protected function _value($values, $array, $date = null) {
        if (is_array($values)) {
            foreach ($values as $value) {
                if (isset($array[$value])) {
                    if (isset($date) && $date === true) {
                        return date('Y-m-d H:i:s', strtotime($array[$value]));
                    }
                    return $array[$value];
                }
            }
            return null;
        }
    }

    protected function _anexo24ExtendidoPedimento($wsdl, $patente, $aduana, $pedimento) {
        $envelope = '<soapenv:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:zfs="' . $wsdl . '">
    <soapenv:Header/>
    <soapenv:Body>
      <zfs:anexo24ExtendidoPedimento>
         <patente>' . $patente . '</patente>
         <aduana>' . $aduana . '</aduana>
         <pedimento>' . $pedimento . '</pedimento>
      </zfs:anexo24ExtendidoPedimento>
    </soapenv:Body>
    </soapenv:Envelope>';

        $result = $this->_makeCurlAction($wsdl, $envelope, "anexo24ExtendidoPedimento");
        $array = $this->_xmlToArray($result);
    }

    protected function _anexo24ExtendidoPedimentoSecundario($wsdl, $patente, $aduana, $pedimento) {
        $envelope = '<soapenv:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:zfs="' . $wsdl . '">
    <soapenv:Header/>
    <soapenv:Body>
      <zfs:anexo24ExtendidoPedimentoSecundario>
         <patente>' . $patente . '</patente>
         <aduana>' . $aduana . '</aduana>
         <pedimento>' . $pedimento . '</pedimento>
      </zfs:anexo24ExtendidoPedimentoSecundario>
    </soapenv:Body>
    </soapenv:Envelope>';

        $result = $this->_makeCurlAction($wsdl, $envelope, "anexo24ExtendidoPedimentoSecundario");
        $array = $this->_xmlToArray($result);

        $data = array();
        if (isset($array["Body"]["anexo24ExtendidoPedimentoSecundarioResponse"]["return"]["item"])) {
            if (count($array["Body"]["anexo24ExtendidoPedimentoSecundarioResponse"]["return"]["item"]) > 1) {
                foreach ($array["Body"]["anexo24ExtendidoPedimentoSecundarioResponse"]["return"]["item"] as $k => $v) {
                    foreach ($v as $param) {
                        foreach ($param as $value) {
                            if (!isset($data[$k][$value["key"]])) {
                                $data[$k][$value["key"]] = is_array($value["value"]) ? null : $value["value"];
                            }
                        }
                    }
                }
            } elseif (count($array["Body"]["anexo24ExtendidoPedimentoSecundarioResponse"]["return"]["item"]) == 1) {
                foreach ($array["Body"]["anexo24ExtendidoPedimentoSecundarioResponse"]["return"]["item"]["item"] as $param) {
                    if (!isset($data[0][$param["key"]])) {
                        $data[0][$param["key"]] = is_array($param["value"]) ? null : $param["value"];
                    }
                }
            }
        } else {
            return false;
        }
        return $data;
    }

    protected function _makeCurlAction($wsdl, $envelope, $action) {
        $headers = array(
            "Accept-Encoding: gzip,deflate",
            "Content-Type: text/xml;charset=UTF-8",
            "SOAPAction: \"{$wsdl}#{$action}\"",
            "Content-length: " . strlen($envelope),
            "Keep-Alive: 500",
            "Connection: Keep-Alive",
            "User-Agent: Apache-HttpClient/4.1.1 (java 1.5)",
            "Cache-Control: max-age=0"
        );
        $soap_do = curl_init();
        curl_setopt($soap_do, CURLOPT_URL, $wsdl);
        curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 500);
        curl_setopt($soap_do, CURLOPT_TIMEOUT, 500);
        curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($soap_do, CURLOPT_POSTFIELDS, $envelope);
        curl_setopt($soap_do, CURLOPT_HTTPAUTH, CURLAUTH_NTLM);
        curl_setopt($soap_do, CURLOPT_VERBOSE, TRUE);
        curl_setopt($soap_do, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($soap_do);
        if ($result === false) {
            return 'Curl error: ' . curl_error($soap_do);
        }
        return $result;
    }

    protected function _xmlToArray($xml) {
        try {
            $clean = str_replace(array('ns2:', 'ns1:', 'ns3:', 'xs:', 'ns9:', 'ns8:', 'S:', 'wsu:', 'wsse:', 'ns3:', 'wsu:', 'SOAP-ENV:', 'soapenv:', 'env:', 'oxml:', '<![CDATA[', ']]>'), '', $xml);

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
     * /automatizacion/ws/cargo-quin
     * ?rfc=PPT0702197L2&patente=3589&aduana=646&year=2015&month=9
     * ?rfc=PPT0702197L2&patente=3589&aduana=646&year=2015&month=9&lang=en
     */
    public function cargoQuinAction() {
        try {
            $gets = $this->_request->getParams();
            $filters = array(
                '*' => array('StringTrim', 'StripTags'),
                'patente' => array('Digits'),
                'aduana' => array('Digits'),
                'year' => array('Digits'),
                'month' => array('Digits'),
                'rfc' => array('StringToUpper'),
            );
            $validators = array(
                'patente' => array(
                    'Digits',
                    new Zend_Validate_Int(),
                    array('Between', 3589, 3589)
                ),
                'aduana' => array(
                    'Digits',
                    new Zend_Validate_Int(),
                    array('Between', 640, 646)
                ),
                'year' => array(
                    'Digits',
                    new Zend_Validate_Int(),
                    array('Between', 2015, 2025)
                ),
                'month' => array(
                    'Digits',
                    new Zend_Validate_Int(),
                    array('Between', 1, 12)
                ),
                'rfc' => array(
                    'Alnum',
                    new Zend_Validate_Alnum(),
                    array('StringLength', 12, 15)
                ),
                'lang' => array(
                    'Alnum',
                    new Zend_Validate_Alnum(),
                    array('StringLength', 2, 2)
                )
            );
            $input = new Zend_Filter_Input($filters, $validators, $gets);
            if ($input->isValid()) {
                $repo = new Archivo_Model_RepositorioMapper();

                $data = $input->getEscaped();
                if ($data["aduana"] == 646 && $data["patente"] == 3589) {
                    $db = new OAQ_Sitawin(true, "192.168.200.5", "sa", "adminOAQ123", "SITA43589640", 1433, "Pdo_Mssql");
                } elseif ($data["aduana"] == 640 && $data["patente"] == 3589) {
                    $db = new OAQ_Sitawin(true, "192.168.200.5", "sa", "adminOAQ123", "SITA43589640", 1433, "Pdo_Mssql");
                } elseif ($data["aduana"] == 240 && $data["patente"] == 3589) {
                    $db = new OAQ_Sitawin(true, "192.168.200.5", "sa", "adminOAQ123", "SITA43589240", 1433, "Pdo_Mssql");
                }
                $rows = $db->grupoCargoQuin($data["rfc"], $data["year"], $data["month"]);
                $result = array();
                foreach ($rows as $row) {
                    $factura = $repo->facturasTerminalPedimento($row["pedimento"]);
                    if (isset($factura) && !empty($factura)) {
                        if (file_exists($factura["ubicacion"])) {
                            $doc = new DOMDocument();
                            $doc->loadXML(str_replace(array('cfdi:', 'xmlns:', 'tfd:', 'xsi:'), '', file_get_contents($factura["ubicacion"])));
                            $domXpath = new DOMXPath($doc);
                            $conceptos = $domXpath->query("//Conceptos/*");
                            $array = array();
                            foreach ($conceptos as $con) {
                                $array[$con->getAttribute("descripcion")] = array(
                                    'folio' => $factura["folio"],
                                    'cantidad' => $con->getAttribute("cantidad"),
                                    'importe' => $con->getAttribute("importe"),
                                    'valorUnitario' => $con->getAttribute("valorUnitario"),
                                );
                            }
                        }
                    }
                    if (!empty($array)) {
                        $row["conceptos"] = $array;
                    }
                    if (isset($row["dta"])) {
                        $row["impuestos"]["DTA"] = array(
                            'importe' => $row["dta"],
                        );
                    }
                    if (isset($row["prev"])) {
                        $row["impuestos"]["PREV"] = array(
                            'importe' => $row["prev"],
                        );
                    }
                    if (isset($row["cnt"])) {
                        $row["impuestos"]["CNT"] = array(
                            'importe' => $row["cnt"],
                        );
                    }
                    $result[] = $row;
                }
                $viewFolder = realpath(dirname(__FILE__)) . '/../views/scripts/index/';
                $helperFolder = realpath(dirname(__FILE__)) . '/../views/helpers/';
                $view = new Zend_View();
                $view->setScriptPath($viewFolder);
                $view->setHelperPath($helperFolder);
                $view->data = $result;
                if ($data["lang"] == 'en') {
                    echo $view->render('cargo-quin-en.phtml');
                } else {
                    echo $view->render('cargo-quin.phtml');
                }
            }
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }

    /**
     * /automatizacion/ws/cargo-quin-fracciones
     * ?rfc=PPT0702197L2&patente=3589&aduana=646&year=2015&month=9
     * ?rfc=PPT0702197L2&patente=3589&aduana=646&year=2015&month=9&lang=en
     */
    public function cargoQuinFraccionesAction() {
        try {
            $gets = $this->_request->getParams();
            $filters = array(
                '*' => array('StringTrim', 'StripTags'),
                'patente' => array('Digits'),
                'aduana' => array('Digits'),
                'year' => array('Digits'),
                'month' => array('Digits'),
                'rfc' => array('StringToUpper'),
            );
            $validators = array(
                'patente' => array(
                    'Digits',
                    new Zend_Validate_Int(),
                    array('Between', 3589, 3589)
                ),
                'aduana' => array(
                    'Digits',
                    new Zend_Validate_Int(),
                    array('Between', 640, 646)
                ),
                'year' => array(
                    'Digits',
                    new Zend_Validate_Int(),
                    array('Between', 2015, 2025)
                ),
                'month' => array(
                    'Digits',
                    new Zend_Validate_Int(),
                    array('Between', 1, 12)
                ),
                'rfc' => array(
                    'Alnum',
                    new Zend_Validate_Alnum(),
                    array('StringLength', 12, 15)
                ),
                'lang' => array(
                    'Alnum',
                    new Zend_Validate_Alnum(),
                    array('StringLength', 2, 2)
                )
            );
            $input = new Zend_Filter_Input($filters, $validators, $gets);
            if ($input->isValid()) {
                $data = $input->getEscaped();
                if ($data["aduana"] == 646 && $data["patente"] == 3589) {
                    $db = new OAQ_Sitawin(true, "192.168.200.5", "sa", "adminOAQ123", "SITA43589640", 1433, "Pdo_Mssql");
                } elseif ($data["aduana"] == 640 && $data["patente"] == 3589) {
                    $db = new OAQ_Sitawin(true, "192.168.200.5", "sa", "adminOAQ123", "SITA43589640", 1433, "Pdo_Mssql");
                } elseif ($data["aduana"] == 240 && $data["patente"] == 3589) {
                    $db = new OAQ_Sitawin(true, "192.168.200.5", "sa", "adminOAQ123", "SITA43589240", 1433, "Pdo_Mssql");
                }
                $rows = $db->grupoCargoQuinFracciones($data["rfc"], $data["year"], $data["month"]);
                $result = array();
                $tbl = new Vucem_Model_VucemUmcMapper();
                foreach ($rows as $row) {
                    $row["umc"] = $tbl->getUmcDesc($row["umc"]);
                    $result[] = $row;
                }
                $viewFolder = realpath(dirname(__FILE__)) . '/../views/scripts/index/';
                $helperFolder = realpath(dirname(__FILE__)) . '/../views/helpers/';

                $view = new Zend_View();
                $view->setScriptPath($viewFolder);
                $view->setHelperPath($helperFolder);
                $view->data = $result;
                if ($data["lang"] == 'en') {
                    echo $view->render('cargo-quin-fracciones-en.phtml');
                } else {
                    echo $view->render('cargo-quin.phtml');
                }
            }
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }

    /**
     * /automatizacion/ws/cargo-quin-partes
     * ?rfc=PPT0702197L2&patente=3589&aduana=646&year=2015&month=9
     * ?rfc=PPT0702197L2&patente=3589&aduana=646&year=2015&month=9&lang=en
     */
    public function cargoQuinPartesAction() {
        try {
            $gets = $this->_request->getParams();
            $filters = array(
                '*' => array('StringTrim', 'StripTags'),
                'patente' => array('Digits'),
                'aduana' => array('Digits'),
                'year' => array('Digits'),
                'month' => array('Digits'),
                'rfc' => array('StringToUpper'),
            );
            $validators = array(
                'patente' => array(
                    'Digits',
                    new Zend_Validate_Int(),
                    array('Between', 3589, 3589)
                ),
                'aduana' => array(
                    'Digits',
                    new Zend_Validate_Int(),
                    array('Between', 640, 646)
                ),
                'year' => array(
                    'Digits',
                    new Zend_Validate_Int(),
                    array('Between', 2015, 2025)
                ),
                'month' => array(
                    'Digits',
                    new Zend_Validate_Int(),
                    array('Between', 1, 12)
                ),
                'rfc' => array(
                    'Alnum',
                    new Zend_Validate_Alnum(),
                    array('StringLength', 12, 15)
                ),
                'lang' => array(
                    'Alnum',
                    new Zend_Validate_Alnum(),
                    array('StringLength', 2, 2)
                )
            );
            $input = new Zend_Filter_Input($filters, $validators, $gets);
            if ($input->isValid()) {
                $data = $input->getEscaped();
                if ($data["aduana"] == 646 && $data["patente"] == 3589) {
                    $db = new OAQ_Sitawin(true, "192.168.200.5", "sa", "adminOAQ123", "SITA43589640", 1433, "Pdo_Mssql");
                } elseif ($data["aduana"] == 640 && $data["patente"] == 3589) {
                    $db = new OAQ_Sitawin(true, "192.168.200.5", "sa", "adminOAQ123", "SITA43589640", 1433, "Pdo_Mssql");
                } elseif ($data["aduana"] == 240 && $data["patente"] == 3589) {
                    $db = new OAQ_Sitawin(true, "192.168.200.5", "sa", "adminOAQ123", "SITA43589240", 1433, "Pdo_Mssql");
                }
                $rows = $db->grupoCargoQuinPartes($data["rfc"], $data["year"], $data["month"]);
                $result = array();
                $tbl = new Vucem_Model_VucemUmcMapper();
                foreach ($rows as $row) {
                    $result[] = $row;
                }
                $viewFolder = realpath(dirname(__FILE__)) . '/../views/scripts/index/';
                $helperFolder = realpath(dirname(__FILE__)) . '/../views/helpers/';
                $view = new Zend_View();
                $view->setScriptPath($viewFolder);
                $view->setHelperPath($helperFolder);
                $view->data = $result;
                if ($data["lang"] == 'en') {
                    echo $view->render('cargo-quin-partes-en.phtml');
                }
            }
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }

    public function tipoCambioAction() {
        try {
            $mppr = new Application_Model_TipoCambio();
            $datetime = new DateTime('tomorrow');
            $tomorrow = $datetime->format('Y-m-d');
            $today = date("Y-m-d");
            if (!($mppr->verificar($today))) {
                $banxico = new V2_Banxico();
                $banxico->consumirServicio($today, $tomorrow);
                $resp = $banxico->get_response();
                if (isset($resp['bmx']['series'][0]['datos'])) {
                    $datos = $resp['bmx']['series'][0]['datos'];
                    $arr = array(
                        "value" => $today,
                        "today" => $datos[0]['dato'],
                        "tomorrow" => $datos[1]['dato'],
                        "created" => date("Y-m-d H:i:s")
                    );
                    if ($mppr->agregar($arr)) {
                        $this->_helper->json(array("success" => true, "value" => number_format($datos[0]['dato'], 4)));                        
                    }
                } else {
                    $this->_helper->json(array("success" => false));
                }
            } else {
                $this->_helper->json(array("success" => true, "value" => number_format($mppr->obtener($today), 4)));
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

}
