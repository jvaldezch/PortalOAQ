<?php

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Automatizacion_QueueController extends Zend_Controller_Action {

    protected $_config;
    protected $_appconfig;

    public function init() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
    }

    public function listenAction() {
        $worker = new OAQ_WorkerReceiver();
        $worker->listen();
    }

    public function emailAction() {
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "id" => "Digits",
        );
        $v = array(
            "id" => array(new Zend_Validate_Int()),
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($input->isValid("id")) {
            $sender = new OAQ_WorkerSender("emails");
            $sender->enviarEmail($input->id);
        }
    }

    public function enviarEmailAction() {
//        set_time_limit(180);
        $worker = new OAQ_WorkerReceiver("emails");
        $worker->listenEmails();
    }

    public function imprimirPedimentoAction() {
//        set_time_limit(180);
        $worker = new OAQ_WorkerReceiver("pedimentos");
        $worker->listenPedimentos();
    }
    
    public function estadoPedimentoAction() {        
        $worker = new OAQ_WorkerVucemConsumer("pedimentos");
        $worker->estadoPedimento();
    }
    
    public function consumeTestingAction() {
        $worker = new OAQ_WorkerVucemConsumer("pedimentos");
        $worker->testing();
    }
    
    
    /**
     * /automatizacion/queue/traficos-pagados?patente=3598&fecha=2017-04-02&limit=3
     * 
     */
    public function traficosPagadosAction() {
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "patente" => array("Digits"),
            "limit" => array("Digits"),
        );
        $v = array(
            "limit" => array("NotEmpty", new Zend_Validate_Int()),
            "patente" => array("NotEmpty", new Zend_Validate_Int()),
            "fecha" => array("NotEmpty", new Zend_Validate_Regex("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/"), "default" => date("Y-m-d")),
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if($input->isValid("patente") && $input->isValid("fecha")) {
            $sender = new OAQ_WorkerVucemSender("pedimentos");
            $mapper = new Vucem_Model_VucemPedimentosMapper();
            $model = new Trafico_Model_TraficosMapper();
            $array = $model->traficosPagados(3589, $input->limit, $input->fecha);
            if (is_array($array) && !empty($array)) {
                foreach ($array as $item) {
                    if (!($mapper->verificar($item["patente"], $item["aduana"], $item["pedimento"]))) {
                        $sender->estadoPedimento((int) $item["id"]);
                    }
                }
            }
        }
    }
    
    /**
     * /automatizacion/queue/testing
     */
    public function testingAction() {
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "id" => array("Digits"),
        );
        $v = array(
            "id" => array("NotEmpty", new Zend_Validate_Int()),
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if($input->isValid("id")) {
            $sender = new OAQ_WorkerVucemSender("pedimentos");
            $sender->testing($input->id);
        }
    }

    public function queueTestingAction() {
        if(APPLICATION_ENV == "production") {
            set_time_limit(20);
            ini_set('max_execution_time', 20);
            ini_set('max_input_time', 20);
        } else if (APPLICATION_ENV == "staging") {
            set_time_limit(30);            
        } else {
            set_time_limit(30);            
        }
        $sender = new OAQ_WorkerVucemSender("pedimentos");
        $arr = $sender->queueTesting();
        Zend_Debug::dump($arr);
    }
    
    public function arreglarAction() {
        $repo = new Archivo_Model_Repositorio();
        $arr = $repo->buscarMal();
        foreach($arr as $item) {
            $referencias = new OAQ_Referencias(["patente" => $item["patente"], "aduana" => $item["aduana"], "referencia" => $item["referencia"]]);
            $a = $referencias->buscarInfo();
            if(count($a) > 0) {
                $repo->actualizarRepositorio($item["id"], $a["pedimento"], $a["rfcCliente"]);
            }
            var_dump($a);
        }
        var_dump($arr);
    }
    
    /**
     * /automatizacion/queue/anexo-clientes
     * 
     */
    public function anexoClientesAction() {
        $mppr = new Operaciones_Model_ClientesAnexo24Mapper();
        $arr = $mppr->todos();
        if (!empty($arr)) {
            foreach ($arr as $k => $v) {
                if ($k == "") {
                    continue;
                }
                $output = array();
                if (APPLICATION_ENV == "production") {
                    $cmd = "wget --no-check-certificate -O - \"https://127.0.0.1/automatizacion/queue/anexo-curl?rfc=" . $k . "&year=" . date('Y') . "\" > /dev/null 2>&1";
                    exec($cmd, $output);
                } else if (APPLICATION_ENV == "staging") {
                    
                } else {
                    $cmd = "C:\\cygwin64\\bin\\wget.exe  --no-check-certificate -O - https://192.168.0.11/automatizacion/queue/anexo-curl?rfc=" . $k . "&year=" . date('Y') . " > /dev/null 2>&1";
                    exec($cmd, $output);
                }
            }
        }
        exec('su - www-data -c "php /var/www/portalprod/workers/trafico_worker.php" &', $output);
        exec('su - www-data -c "php /var/www/portalprod/workers/trafico_worker.php" &', $output);
        exec('su - www-data -c "php /var/www/portalprod/workers/trafico_worker.php" &', $output);
    }

    /**
     * /automatizacion/queue/anexo-curl?rfc=GCO980828GY0&year=2017 
     * 
     */
    public function anexoCurlAction() {
        try {
            if (APPLICATION_ENV == 'production') {
                $uri = "https://127.0.0.1/automatizacion/ws";
            } else if (APPLICATION_ENV == 'development' || APPLICATION_ENV == 'staging') {
                $uri = "https://192.168.200.11/automatizacion/ws";
            }
            $aduanas = array(
                "3589" => array("640", "240", "800"),
                "3574" => array("160", "470", "800", "240"),
            );
            $f = array(
                "*" => array("StringTrim", "StripTags"),
            );
            $v = array(
                "year" => array(new Zend_Validate_Regex("/^[0-9.]+$/"), "NotEmpty"),
                "rfc" => array(new Zend_Validate_Regex("/^[A-Z]{3,4}([0-9]{2})([0-9]{2})([0-9]{2})?[A-Z0-9]{3,4}/"), "NotEmpty"),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("rfc") && $input->isValid("year")) {
              foreach (range(1, date("m")) as $month) {
                foreach ($aduanas as $k => $v) {
                    foreach ($v as $a) {
                      $url = $uri . "/gearman-pedimentos?patente={$k}&aduana={$a}&rfc={$input->rfc}&year={$input->year}&month=" . $month;
                      $ch = curl_init();
                      curl_setopt($ch, CURLOPT_URL, $url);
                      curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                      curl_setopt($ch, CURLOPT_USERAGENT, "Codular Sample cURL Request");
                      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,0);
                      curl_setopt($ch, CURLOPT_TIMEOUT, 400);
                      curl_exec($ch);
                      curl_close($ch);
                    }
                    foreach ($v as $a) {
                      $url = $uri . "/gearman-detalle?patente={$k}&aduana={$a}&rfc={$input->rfc}&year={$input->year}&month=" . $month;
                      $ch = curl_init();
                      curl_setopt($ch, CURLOPT_URL, $url);
                      curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                      curl_setopt($ch, CURLOPT_USERAGENT, "Codular Sample cURL Request");
                      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,0);
                      curl_setopt($ch, CURLOPT_TIMEOUT, 400);
                      curl_exec($ch);
                      curl_close($ch);
                    }
                    foreach ($v as $a) {
                      $url = $uri . "/gearman-anexo?patente={$k}&aduana={$a}&rfc={$input->rfc}&year={$input->year}&month=" . $month;
                      $ch = curl_init();
                      curl_setopt($ch, CURLOPT_URL, $url);
                      curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                      curl_setopt($ch, CURLOPT_USERAGENT, "Codular Sample cURL Request");
                      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,0);
                      curl_setopt($ch, CURLOPT_TIMEOUT, 400);
                      curl_exec($ch);
                      curl_close($ch);
                    }
                }
              }
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

}
