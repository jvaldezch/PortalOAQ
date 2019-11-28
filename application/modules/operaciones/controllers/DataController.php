<?php

class Operaciones_DataController extends Zend_Controller_Action {

    protected $_session;
    protected $_config;
    protected $_appconfig;

    public function init() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
    }

    public function preDispatch() {
        $this->_session = NULL ? $this->_session = new Zend_Session_Namespace('') : $this->_session = new Zend_Session_Namespace($this->_config->app->namespace);
        if ($this->_session->authenticated == true) {
            $session = new OAQ_Session($this->_session, $this->_appconfig);
            $session->actualizar();
            $session->actualizarSesion();
        } else {
            $this->getResponse()->setRedirect($this->_appconfig->getParam('link-logout'));
        }
        $this->_svucem = NULL ? $this->_svucem = new Zend_Session_Namespace('') : $this->_svucem = new Zend_Session_Namespace('OAQVucem');
        $this->_svucem->setExpirationSeconds($this->_appconfig->getParam('session-exp'));
    }

    public function verAnexoPedimentoAction() {
        $this->_helper->viewRenderer->setNoRender(false);
        $this->view->headLink()
                ->appendStylesheet("/bootstrap/css/bootstrap.min.css")
                ->appendStylesheet("/less/traffic-module.css?" . time());
        $gets = $this->_request->getParams();
        if (isset($gets["patente"]) && isset($gets["aduana"]) && isset($gets["pedimento"])) {
            $model = new Automatizacion_Model_WsAnexoPedimentosMapper();
            $data = $model->obtenerAnexo($gets["patente"], $gets["aduana"], $gets["pedimento"]);
            $this->view->data = $data;
        }
    }

    public function cargarCatalogoAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
        }
        $data = $this->_request->getParams();
        
    }

    public function sendFileAction() {
        // su - www-data -c 'php /var/www/workers/ftp_worker.php'
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
        }
        $request = $this->getRequest();
        if ($request->isPost()) {
            $client = new GearmanClient();
            $client->addServer('127.0.0.1', 4730);
            $post = $request->getPost();

            if (isset($post["patente"]) && isset($post["aduana"]) && isset($post["archivo"])) {
                $model = new Application_Model_DirectoriosValidacion();
                $validator = new Application_Model_Validador();
                $table = new Application_Model_ValidadorEnviados();
                $folder = $model->obtener($post["patente"], $post["aduana"]);
                if (isset($folder)) {
                    $filename = $folder . DIRECTORY_SEPARATOR . $post["archivo"];
                    if (file_exists($folder . DIRECTORY_SEPARATOR . $post["archivo"])) {
                        $server = $validator->obtener($post["patente"], $post["aduana"]);

                        if (isset($server)) {
                            if (!($conn_id = $this->_connectFtp($server))) {
                                exit();
                            }
                            ftp_chdir($conn_id, $server["carpeta"]);

                            if (!($id = $table->verificar(basename($filename), sha1_file($filename)))) {

                                if (ftp_put($conn_id, basename($filename), $filename, FTP_BINARY)) {
                                    $contenido = base64_encode(file_get_contents($filename));

                                    $added = $table->agregar($post["patente"], $post["aduana"], basename($filename), $contenido, sha1_file($filename), $this->_session->username);
                                    $data = array(
                                        'id' => $added,
                                    );
                                    $client->addTaskBackground("validador", serialize($data));
                                    $client->runTasks();

                                    $this->_analizarArchivos($data["id"], $post["archivo"], base64_encode(file_get_contents($folder . DIRECTORY_SEPARATOR . $post["archivo"])));
                                }
                            } else {
                                $data = array(
                                    'id' => $id,
                                );
                                $client->addTaskBackground("validador", serialize($data));
                                $client->runTasks();
                                $this->_analizarArchivos($data["id"], $post["archivo"], base64_encode(file_get_contents($folder . DIRECTORY_SEPARATOR . $post["archivo"])));
                            }
                        }
                    } else {
                    }
                }
            }
        }
    }

    public function validarArchivoM3Action() {
        // su - www-data -c 'php /var/www/workers/ftp_worker.php'
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
        }
        $request = $this->getRequest();
        if ($request->isPost()) {

            $post = $request->getPost();
            $misc = new OAQ_Misc();
            $model = new Operaciones_Model_ValidadorLog();

            $file = $model->obtenerNombre($post["id"]);
            if (!($model->verificarArchivo($file["patente"], $file["aduana"], $file["archivo"]))) {
                $run = $misc->runGearmanProcess("ftp_worker.php", 2);
                if ($run) {
                    $client = new GearmanClient();
                    $client->addServer('127.0.0.1', 4730);
                    $data = array('id' => $post["id"], 'username' => $this->_session->username);
                    $client->addTaskBackground("validadorplus", serialize($data));
                    $client->runTasks();
//                    $model->enviado($post["id"]);
                    echo Zend_Json::encode(array('success' => true));
                    exit();
                }
            } else {
                echo Zend_Json::encode(array('success' => false, 'message' => 'Un archivo con el mismo nombre ha sido previamente enviado.'));
                exit();
            }
        }
    }

    public function pagarArchivoAction() {
        // su - www-data -c 'php /var/www/workers/ftp_worker.php'
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
        }
        $request = $this->getRequest();
        if ($request->isPost()) {

            $post = $request->getPost();
            $misc = new OAQ_Misc();
            $model = new Operaciones_Model_ValidadorLog();
            $run = $misc->runGearmanProcess("ftp_worker.php", 2);
            if ($run) {
                $client = new GearmanClient();
                $client->addServer('127.0.0.1', 4730);
                $data = array('id' => $post["id"], 'username' => $this->_session->username);
                $client->addTaskBackground("validadorpagoplus", serialize($data));
                $client->runTasks();
            }
        }
    }

    public function revisarArchivoAction() {
        // su - www-data -c 'php /var/www/workers/ftp_worker.php'
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
        }
        $request = $this->getRequest();
        if ($request->isPost()) {
            $misc = new OAQ_Misc();
            $model = new Operaciones_Model_ValidadorLog();
            $run = $misc->runGearmanProcess("ftp_worker.php", 2);
            if ($run) {
                $post = $request->getPost();
                $model->noAgotado($post["id"]);
                $model->noEnviado($post["id"]);

                $client = new GearmanClient();
                $client->addServer('127.0.0.1', 4730);
                $data = array('id' => $post["id"], 'username' => $this->_session->username);
                $client->addTaskBackground("revisarvalidacion", serialize($data));
                $client->runTasks();
            }
        }
    }

    public function sendPaidAction() {
        // su - www-data -c 'php /var/www/workers/ftp_worker.php'
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
        }
        $request = $this->getRequest();
        if ($request->isPost()) {
            $client = new GearmanClient();
            $client->addServer('127.0.0.1', 4730);
            $post = $request->getPost();

            if (isset($post["patente"]) && isset($post["aduana"]) && isset($post["archivo"])) {
                $model = new Application_Model_DirectoriosValidacion();
                $validator = new Application_Model_Validador();
                $table = new Application_Model_ValidadorEnviados();
                $folder = $model->obtener($post["patente"], $post["aduana"]);
                if (isset($folder)) {
                    $filename = $folder . DIRECTORY_SEPARATOR . $post["archivo"];
                    if (file_exists($folder . DIRECTORY_SEPARATOR . $post["archivo"])) {
                        $server = $validator->obtener($post["patente"], $post["aduana"]);

                        if (isset($server)) {
                            if (!($conn_id = $this->_connectFtp($server))) {
                                exit();
                            }
                            ftp_chdir($conn_id, $server["carpeta"]);

                            $namePagado = "A" . substr(basename($filename), (-1 * (strlen(basename($filename)) - 1)));


                            if (!($id = $table->verificar(basename($filename), sha1_file($filename)))) {
                                $size = ftp_size($conn_id, $namePagado);
                                if ($size == -1) {
                                    if (ftp_put($conn_id, basename($filename), $filename, FTP_BINARY)) {
                                        $contenido = base64_encode(file_get_contents($filename));
                                        $added = $table->agregar($post["patente"], $post["aduana"], basename($filename), $contenido, sha1_file($filename), $this->_session->username);
                                        $data = array(
                                            'id' => $added,
                                        );
                                    }
                                } else {
                                    $contenido = base64_encode(file_get_contents($filename));
                                    $added = $table->agregar($post["patente"], $post["aduana"], basename($filename), $contenido, sha1_file($filename), $this->_session->username);
                                    $data = array(
                                        'id' => $added,
                                    );
                                }
                            } else {
                                $data = array(
                                    'id' => $id,
                                );
                            }
                            if (isset($data)) {
                                $client->addTaskBackground("validadorpago", serialize($data));
                                $client->runTasks();
                            }
                        }
                    } else {
                    }
                }
            }
        }
    }

    protected function _analizarArchivos($id, $filename, $content) {
        $functions = new OAQ_ArchivosM3();
        $array = $functions->analizarArchivo($filename, base64_decode($content));
        if (!empty($array)) {
            if ($array !== false) {
                $model = new Application_Model_ValidadorPedimentos();
                if (!($model->verificar($id))) {
                    foreach ($array as $item) {
                        $model->agregar($id, $item["patente"], $item["aduana"], $item["pedimento"], $item["tipoMovimiento"], $item["firmaDesistir"], $this->_session->username);
                    }
                }
            }
        } else {
            return false;
        }
    }

    protected function _connectFtp($server) {
        $conn_id = ftp_connect($server["host"], $server["puerto"]);
        $login_result = ftp_login($conn_id, $server["usuario"], $server["password"]);
        if ((!$conn_id) || (!$login_result)) {
            return false;
        }
        return $conn_id;
    }

    public function getCustomsAction() {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
            }
            $r = $this->getRequest();
            if ($r->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "patente" => array("Digits"),
                    "aduana" => array("Digits"),
                );
                $v = array(
                    "patente" => array("NotEmpty", new Zend_Validate_Int()),
                    "aduana" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $r->getPost());
                if($input->isValid("patente")) {
                    $html = new V2_Html();
                    $ads = new Trafico_Model_TraficoUsuAduanasValMapper();
                    $ad = $ads->obtenerAduanas($input->patente, $this->_session->id);
                    $html->select(null, "aduana", "width: 80px");
                    if(count($ad) > 0) {
                        $html->addSelectOption("", "---");
                        foreach ($ad as $k => $item) {
                            $html->addSelectOption($k, $item);
                        }
                    }
                    $this->_helper->json(array("success" => true, "html" => $html->getHtml()));
                } else {
                    throw new Exception("Invalid input!");
                }
            } else {
                throw new Exception("Invalid request type!");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function getFileAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $post = $this->_request->getParams();
        if (isset($post["patente"]) && isset($post["aduana"])) {
            try {
                $model = new Application_Model_DirectoriosValidacion();
                $folder = $model->obtener($post["patente"], $post["aduana"]);
                if (isset($folder)) {
                    $filename = $folder . DIRECTORY_SEPARATOR . $post["archivo"];
                    if (file_exists($filename)) {
                        $functions = new OAQ_ArchivosM3();
                        if (preg_match('/^m[0-9]{7}.[0-9]{3}$/i', $post["archivo"])) {
                            $registro = $functions->analizarM3($filename);
                            if ($registro !== false) {
                                $html = "";
                                foreach ($registro as $item) {
                                    $html .= "<option value=\"{$filename}\">{$item}</option>";
                                }
                            } else {
                                $html = "<option>---</option>";
                            }
                        }
                        if (preg_match('/^m[0-9]{7}.err$/i', $post["archivo"])) {
                            $registro = $functions->analizarValidados($filename);
                            $html = "";
                            if ($registro !== false) {
                                foreach ($registro as $item) {
                                    $html .= "<option value=\"" . $item["pedimento"] . "\">" . $item["pedimento"] . "|" . $item["firma"] . "</option>";
                                }
                            } else {
                                $html = "<option>---</option>";
                            }
                        }
                        if (preg_match('/^a[0-9]{7}.[0-9]{3}$/i', $post["archivo"])) {
                            $registro = $functions->analizarAPagados($filename);
                            $html = "";
                            if ($registro !== false) {
                                foreach ($registro as $item) {
                                    $html .= "<option value=\"" . $item["pedimento"] . "\">" . $item["pedimento"] . "|" . $item["firma"] . "|" . $item["rfcImportador"] . "</option>";
                                }
                            } else {
                                $html = "<option>---</option>";
                            }
                        }
                        if (preg_match('/^k[0-9]{7}.[0-9]{3}$/i', $post["archivo"])) {
                            $html = file_get_contents($filename);
                        }
                        echo Zend_Json::encode(array('success' => true, 'html' => $html));
                    }
                }
            } catch (Exception $ex) {
                
            }
        }
    }

    public function getDirectoryAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $post = $this->_request->getParams();
        if (isset($post["patente"]) && isset($post["aduana"])) {
            try {
                $model = new Application_Model_DirectoriosValidacion();
                $folder = $model->obtener($post["patente"], $post["aduana"]);
                if (isset($folder)) {
                    $directory = new RecursiveDirectoryIterator($folder);
                    $iterator = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::SELF_FIRST);
                    $files = new RegexIterator($iterator, "/^.+\.([0-9]{3})|.err$/i", RecursiveRegexIterator::GET_MATCH);
                    $data = array();
                    foreach ($files as $nombre => $object) {
                        $data[] = basename($nombre);
                    }
                    array_multisort($data, SORT_DESC);
                    if (!empty($data)) {
                        $htmlm = '<select class="" id="archivo-m" name="archivo-m" size="10">';
                        $htmlr = '<select class="" id="archivo-r" name="archivo-r" size="10">';
                        $htmlk = '<select class="" id="archivo-k" name="archivo-k" size="10">';
                        $htmle = '<select class="" id="archivo-e" name="archivo-e" size="10">';
                        $htmlp = '<select class="" id="archivo-p" name="archivo-p" size="10">';
                        foreach ($data as $k => $item) {
                            if (preg_match('/M[0-9]{7}.([0-9]{3})/i', $item)) {
                                $htmlm .= '<option value="' . $item . '">' . $item . '</option>';
                            }
                            if (preg_match('/k[0-9]{7}.([0-9]{3})/i', $item)) {
                                $htmlk .= '<option value="' . $item . '">' . $item . '</option>';
                            }
                            if (preg_match('/m[0-9]{7}.err/i', $item)) {
                                $htmlr .= '<option value="' . $item . '">' . $item . '</option>';
                            }
                            if (preg_match('/a[0-9]{7}.([0-9]{3})/i', $item)) {
                                $htmlp .= '<option value="' . $item . '">' . $item . '</option>';
                            }
                            if (preg_match('/e[0-9]{7}.([0-9]{3})/i', $item)) {
                                $htmle .= '<option value="' . $item . '">' . $item . '</option>';
                            }
                        }
                        $htmlm .= "</select>";
                        $htmlr .= "</select>";
                        $htmlk .= "</select>";
                        $htmle .= "</select>";
                        $htmlp .= "</select>";
                        echo Zend_Json::encode(array('success' => true, 'htmlm' => $htmlm, 'htmlp' => $htmlp, 'htmlr' => $htmlr, 'htmlk' => $htmlk, 'htmle' => $htmle));
                    }
                }
            } catch (Exception $ex) {
                $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
            }
        }
    }

    public function obtenerArchivoAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $post = $this->_request->getParams();
        try {
            if (isset($post["filename"])) {
                if (file_exists($post["filename"])) {
                    if (is_readable($post["filename"])) {
                        $file_handle = fopen($post["filename"], "r");
                        $data = fread($file_handle, filesize($post["filename"]));
                        fclose($file_handle);
                        if (isset($data) && $data !== null && $data !== '') {
                            echo Zend_Json::encode(array('success' => true, 'html' => base64_encode($data)));
                            exit();
                        } else {
                            echo Zend_Json::encode(array('success' => false));
                            exit();
                        }
                    } else {
                    }
                } else {
                }
            }
            echo Zend_Json::encode(array('success' => false));
            exit();
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function verReporteIvaAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(false);
        $this->view->headLink()
                ->appendStylesheet("/js/common/bootstrap/css/bootstrap.min.css")
                ->appendStylesheet("/less/traffic-module.css?" . time());
        try {
            $flt = array(
                "*" => array("StringTrim", "StripTags"),
                "mes" => array("Digits"),
                "year" => array("Digits"),
                "aduana" => array("Digits"),
                "rfc" => array("StringToUpper"),
            );
            $vld = array(
                "mes" => new Zend_Validate_Int(),
                "year" => new Zend_Validate_Int(),
                "aduana" => new Zend_Validate_Int(),
                "rfc" => array(new Zend_Validate_Regex("/^[-_a-zA-Z0-9.]+$/"), "presence" => "required"),
            );
            $input = new Zend_Filter_Input($flt, $vld, $this->_request->getParams());
            if (!$input->isValid()) {
                throw new Exception("Invalid input!");
            } else {
                if($input->aduana == 1) {
                    $sitawin = new OAQ_Sitawin(true, "192.168.200.5", "sa", "adminOAQ123", "SITA43589640", 1433, "Pdo_Mssql");
                } else {
                    $sitawin = new OAQ_Sitawin(true, "192.168.200.5", "sa", "adminOAQ123", "SITA43589240", 1433, "Pdo_Mssql");                    
                }
                $result = $sitawin->reporteIvaProveedores($input->rfc, $input->year, $input->mes);
                if(isset($result) && !empty($result)) {
                    $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Array($result));
                    $paginator->setItemCountPerPage(25);
                    $paginator->setCurrentPageNumber(isset($input->page) ? $input->page : 1);
                    $this->view->paginator = $paginator;                    
                }
                $this->view->data = array(
                    "rfc" => $input->rfc,
                    "year" => $input->year,
                    "mes" => $input->mes,
                );
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function excelReporteIvaAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        try {
            $flt = array(
                "*" => array("StringTrim", "StripTags"),
                "mes" => array("Digits"),
                "year" => array("Digits"),
                "aduana" => array("Digits"),
                "rfc" => array("StringToUpper"),
            );
            $vld = array(
                "mes" => new Zend_Validate_Int(),
                "year" => new Zend_Validate_Int(),
                "aduana" => new Zend_Validate_Int(),
                "rfc" => array(new Zend_Validate_Regex("/^[-_a-zA-Z0-9.]+$/"), "presence" => "required"),
            );
            $input = new Zend_Filter_Input($flt, $vld, $this->_request->getParams());
            if (!$input->isValid()) {
                throw new Exception("Invalid input!");
            } else {
                if($input->aduana == 1) {
                    $sitawin = new OAQ_Sitawin(true, "192.168.200.5", "sa", "adminOAQ123", "SITA43589640", 1433, "Pdo_Mssql");
                } else {
                    $sitawin = new OAQ_Sitawin(true, "192.168.200.5", "sa", "adminOAQ123", "SITA43589240", 1433, "Pdo_Mssql");                    
                }
                $data = $sitawin->reporteIvaProveedores($input->rfc, $input->year, $input->mes);
                $reportName = array(
                    'font' => array(
                        'name' => "Arial",
                        'bold' => true,
                        'size' => 15,
                    ),
                );
                $titles = array(
                    'font' => array(
                        'bold' => true,
                        'name' => "Arial",
                    ),
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
                            'color' => array('argb' => 'FF000000'),
                        )
                    ),
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'B8CCE4')
                    ),
                );

                $info = array(
                    'font' => array(
                        'name' => "Arial",
                        'size' => 10,
                    ),
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                            'color' => array('rgb' => '999999'),
                        )
                    ),
                );
                ini_set('include_path', ini_get('include_path') . ';../Classes/');
                include 'PHPExcel.php';
                include 'PHPExcel/Writer/Excel2007.php';
                $objPHPExcel = new PHPExcel();
                $headers = array(
                    'Operacion' => 'operacion',
                    'I/E' => 'impexp',
                    'Referencia' => 'trafico',
                    'Cve. Ped.' => 'cvePedimento',
                    'Tax Id' => 'taxID',
                    'Proveedor' => 'nomProveedor',
                    'Orden Fracc.' => 'ordenFraccion',
                    'Fraccion' => 'fraccion',
                    'Descripcion' => 'descripcion',
                    'Valor' => 'valor',
                    'I.V.A.' => 'iva',
                );
                $misc = new OAQ_Misc();
                $objPHPExcel->getProperties()->setCreator("Jaime E. Valdez");
                $objPHPExcel->getProperties()->setLastModifiedBy("Jaime E. Valdez");
                $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Reporte");
                $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Reporte");
                $objPHPExcel->getProperties()->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.");
                $objPHPExcel->setActiveSheetIndex(0);
                $column = 0;
                $row = 4;
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, 1, "REPORTE DE I.V.A.");
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->applyFromArray($reportName);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, 2, 'RFC CLIENTE:');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->applyFromArray($titles);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 2, $gets["rfc"]);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, 2)->applyFromArray($info);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, 3, 'AÑO:');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 3)->applyFromArray($titles);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 3, $gets["year"]);
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1, 3)->applyFromArray($info);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 3, 'MES:');
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2, 3)->applyFromArray($titles);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, 3, $misc->mes($gets["mes"]));
                $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3, 3)->applyFromArray($info);
                foreach ($headers as $k => $v):
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($column, $row, $k);
                    $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $row)->applyFromArray($titles);
                    $column++;
                endforeach;
                $monedas = array('iva', 'valor');
                $texto = array('descripcion');
                $num = count($data) + $row + 1;
                for ($i = $row + 1; $i < $num; $i++) {
                    $column = 0;
                    foreach ($headers as $k => $v) {
                        $pos = $i - $row - 1;
                        if (isset($data[$pos][$headers[$k]])) {
                            $value = $data[$pos][$headers[$k]];
                        } else {
                            $value = '';
                        }
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($column, $i, utf8_decode($value));
                        $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $i)->applyFromArray($info);
                        if (in_array($v, $monedas)) {
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $i)->getNumberFormat()->setFormatCode('$ #,##0.00');
                        } else if (in_array($v, $texto)) {
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $i)->getNumberFormat()->setFormatCode('0');
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($column, $i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                        }
                        $column++;
                    }
                }
                foreach (range('A', 'F') as $columnID) {
                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
                            ->setAutoSize(true);
                }
                $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(50);
                foreach (range('H', 'I') as $columnID) {
                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
                            ->setAutoSize(true);
                }
                $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="' . $gets["rfc"] . '_' . date('Y-m-d') . '.xlsx"');
                header('Cache-Control: max-age=0');
                $objWriter->save('php://output');
            } // valid input
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function obtenerArchivosValidacionAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
        }
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $post = $request->getPost();

                $misc = new OAQ_Misc();
                $table = new Operaciones_Model_ValidadorLog();
                $act = new Operaciones_Model_ValidadorActividad();

                if (($db = $misc->connectSitawin($post["patente"], $post["aduana"]))) {
                    $archivo = $db->ultimoArchivoValidacion($post["pedimento"]);
                    $model = new Application_Model_DirectoriosValidacion();
                    $folder = $model->obtener($post["patente"], $post["aduana"]);
                    if (isset($archivo) && $archivo != false) {
                        if (file_exists($folder . DIRECTORY_SEPARATOR . $archivo["archivo"])) {
                            if (!($table->verificar($post["patente"], $post["aduana"], $post["pedimento"], $archivo["archivo"]))) {
                                $content = file_get_contents($folder . DIRECTORY_SEPARATOR . $archivo["archivo"]);
                                $table->agregar($post["patente"], $post["aduana"], $post["pedimento"], $archivo["referencia"], $archivo["archivo"], base64_encode($content), $this->_session->username);
                            }
                        }
                    }
                    $pago = $db->ultimoArchivoPago($post["patente"], $archivo["referencia"]);
                    if ($pago != false) {
                        if (file_exists($folder . DIRECTORY_SEPARATOR . $pago)) {
                            if (!($table->verificar($post["patente"], $post["aduana"], $post["pedimento"], $pago))) {
                                $content = file_get_contents($folder . DIRECTORY_SEPARATOR . $pago);
                                $table->agregar($post["patente"], $post["aduana"], $post["pedimento"], $archivo["referencia"], $pago, base64_encode($content), $this->_session->username);
                            }
                        }
                    }
                    $eliminar = $db->ultimoArchivoEliminar($post["patente"], $archivo["referencia"]);
                    if ($eliminar != false) {
                        if (file_exists($folder . DIRECTORY_SEPARATOR . $eliminar)) {
                            if (!($table->verificar($post["patente"], $post["aduana"], $post["pedimento"], $eliminar))) {
                                $content = file_get_contents($folder . DIRECTORY_SEPARATOR . $eliminar);
                                $table->agregar($post["patente"], $post["aduana"], $post["pedimento"], $archivo["referencia"], $eliminar, base64_encode($content), $this->_session->username);
                            }
                        }
                    }
                }
                $todos = $table->obtenerTodos($post["patente"], $post["aduana"], $post["pedimento"]);
                if (isset($todos) && $todos != false) {
                    $html = "<table>";
                    $html .= "<tr><th></th><th>Archivo</th><th>Usuario</th><th>Firma/Pago</th><th>Fecha</th><th>Tamaño</th><th>&nbsp;</th></tr>";
                    foreach ($todos as $item) {
                        $html .= "<tr>";
                        $html .= "<td><img src=\"/images/icons/disk.png\" title=\"Guardar en disco.\" onclick=\"saveToDisk({$item["id"]});\"></td>";
                        $html .= "<td><a onclick=\"loadFile({$item["id"]})\" style=\"cursor: pointer;\">{$item["archivo"]}</a></td>";
                        $html .= "<td>{$item["usuario"]}</td>";
                        if ($item["validado"] == 1) {
                            $html .= "<td style=\"text-align: center;\"><img src=\"/images/icons/ok.png\"></td>";
                        } elseif ($item["pagado"] == 1) {
                            $html .= "<td style=\"text-align: center;\"><img src=\"/images/icons/ok.png\"></td>";
                        } else {
                            $html .= "<td style=\"text-align: center;\">&nbsp;</td>";
                        }
                        $html .= "<td>" . date('d/m/Y H:i a', strtotime($item["creado"])) . "</td>";
                        $html .= "<td>" . round(($item["size"] / 1024), 2) . " kb</td>";

                        if (preg_match('/M[0-9]{7}.[0-9]{3}/i', $item["archivo"]) && ($item["validado"] == 0 && $item["enviado"] == 0)) {
                            $td = "<td style=\"text-align: center;\"><img id=\"imgm_{$item["id"]}\" src=\"/images/icons/send.png\" onclick=\"validarArchivo({$item["id"]});\" style=\"cursor: pointer;\" ></td>";
                        }
                        if (preg_match('/M[0-9]{7}.[0-9]{3}/i', $item["archivo"]) && ($item["validado"] == 0 && $item["enviado"] == 1)) {
                            $td = "<td style=\"text-align: center;\"><img src=\"/images/icons/sent.png\" style=\"cursor: pointer;\" ></td>";
                        }
                        if (preg_match('/M[0-9]{7}.[0-9]{3}/i', $item["archivo"]) && ($item["enviado"] == 1 && $item["agotado"] == 1 && $item["validado"] == 0)) {
                            $td = "<td style=\"text-align: center;\"><img id=\"imgr_{$item["id"]}\" src=\"/images/icons/update.png\" style=\"cursor: pointer;\" onclick=\"revisarArchivo({$item["id"]});\"></td>";
                        }
                        if (preg_match('/M[0-9]{7}.[0-9]{3}/i', $item["archivo"]) && ($item["enviado"] == 1 && $item["error"] == 1)) {
                            $td = "<td style=\"text-align: center;\"><img src=\"/images/icons/exclamation.png\"></td>";
                        }
                        if (preg_match('/E[0-9]{7}.[0-9]{3}/i', $item["archivo"]) && $item["pagado"] == 0) {
                            $td = "<td style=\"text-align: center;\"><img id=\"imgp_{$item["id"]}\" src=\"/images/icons/send.png\" onclick=\"pagarArchivo({$item["id"]});\" style=\"cursor: pointer;\" ></td>";
                        }
                        if (preg_match('/E[0-9]{7}.[0-9]{3}/i', $item["archivo"]) && ($item["enviado"] == 1 && $item["pagado"] == 0)) {
                            $td = "<td style=\"text-align: center;\"><img src=\"/images/icons/sent.png\" style=\"cursor: pointer;\" ></td>";
                        }
                        if (preg_match('/E[0-9]{7}.[0-9]{3}/i', $item["archivo"]) && ($item["enviado"] == 1 && $item["pagado"] == 1)) {
                            $td = "<td style=\"text-align: center;\"></td>";
                        }
                        if (isset($td)) {
                            $html .= $td;
                        } else {
                            $html .= "<td>&nbsp;</td>";
                        }
                        unset($td);
                        $html .= "</tr>";
                    }

                    $html .= "</table>";

                    $log = $act->obtener($post["patente"], $post["aduana"], $post["pedimento"]);
                    if (isset($log)) {
                        $loghtml .= "<table style=\"background: #fff\">";
                        foreach ($log as $item) {
                            $loghtml .= "<tr>";
                            $loghtml .= "<td style=\"background: #fff\">{$item["mensaje"]}</td>";
                            $loghtml .= "<td style=\"background: #fff\">" . date('d/m/Y H:i a', strtotime($item["creado"])) . "</td>";
                            $loghtml .= "</tr>";
                        }
                        $loghtml .= "</table>";
                    }

                    echo Zend_Json::encode(array('success' => true, 'html' => $html, 'log' => $loghtml));
                    exit();
                } else {
                    echo Zend_Json::encode(array('success' => false, 'html' => "<p><em>No existen archivos.</em></p>"));
                    exit();
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function obtenerArchivosValidacionHistoricoAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
        }
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $post = $request->getPost();

                $misc = new OAQ_Misc();
                $table = new Operaciones_Model_ValidadorLog();
                $act = new Operaciones_Model_ValidadorActividad();
                $todos = $table->obtenerTodos($post["patente"], $post["aduana"], $post["pedimento"]);
                if (isset($todos) && $todos != false) {
                    $html = "<table>";
                    $html .= "<tr><th colspan=\"6\" style=\"text-align: center\">ARCHIVOS</th></tr>";
                    $html .= "<tr><th></th><th>Archivo</th><th>Usuario</th><th>Firma/Pago</th><th>Fecha</th><th>Tamaño</th></tr>";
                    foreach ($todos as $item) {
                        $html .= "<tr>";
                        $html .= "<td><img src=\"/images/icons/hdd.png\" title=\"Guardar en disco.\" onclick=\"saveToDisk({$item["id"]});\"></td>";
                        $html .= "<td><a onclick=\"loadFile({$item["id"]})\" style=\"cursor: pointer;\">{$item["archivo"]}</a></td>";
                        $html .= "<td>{$item["usuario"]}</td>";
                        if ($item["validado"] == 1) {
                            $html .= "<td style=\"text-align: center;\"><img src=\"/images/icons/ok.png\"></td>";
                        } elseif ($item["pagado"] == 1) {
                            $html .= "<td style=\"text-align: center;\"><img src=\"/images/icons/ok.png\"></td>";
                        } else {
                            $html .= "<td style=\"text-align: center;\">&nbsp;</td>";
                        }
                        $html .= "<td>" . date('d/m/Y H:i a', strtotime($item["creado"])) . "</td>";
                        $html .= "<td>" . round(($item["size"] / 1024), 2) . " kb</td>";
                        $html .= "</tr>";
                    }

                    $html .= "</table>";

                    $log = $act->obtener($post["patente"], $post["aduana"], $post["pedimento"]);
                    if (isset($log)) {
                        $loghtml .= "<table style=\"background: #fff\">";
                        $loghtml .= "<tr><th colspan=\"2\" style=\"text-align: center\">BITACORA (log)</th></tr>";
                        foreach ($log as $item) {
                            $loghtml .= "<tr>";
                            $loghtml .= "<td style=\"background: #fff\">{$item["mensaje"]}</td>";
                            $loghtml .= "<td style=\"background: #fff\">" . date('d/m/Y H:i a', strtotime($item["creado"])) . "</td>";
                            $loghtml .= "</tr>";
                        }
                        $loghtml .= "</table>";
                    }

                    echo Zend_Json::encode(array('success' => true, 'html' => $html, 'log' => $loghtml));
                    exit();
                } else {
                    echo Zend_Json::encode(array('success' => false, 'html' => "<p><em>No existen archivos.</em></p>"));
                    exit();
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function guardarEnDiscoAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
        }
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $post = $request->getPost();
                if (isset($post["id"])) {
                    $table = new Operaciones_Model_ValidadorLog();
                    $model = new Application_Model_DirectoriosValidacion();
                    $data = $table->obtener($post["id"]);
                    if (isset($data) && $data != false) {
                        $folder = $model->obtener($data["patente"], $data["aduana"]);
                        if (!file_exists($folder . DIRECTORY_SEPARATOR . $data["archivo"])) {
                            file_put_contents($folder . DIRECTORY_SEPARATOR . $data["archivo"], base64_decode($data["contenido"]));
                        }
                    }
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function obtenerActividadAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
        }
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $act = new Operaciones_Model_ValidadorActividad();
                $post = $request->getPost();
                $log = $act->obtener($post["patente"], $post["aduana"], $post["pedimento"]);
                if (isset($log)) {
                    $loghtml = "<form id=\"form-activity\">";
                    $loghtml .= "<input type=\"hidden\" name=\"act-patente\" id=\"act-patente\" value=\"{$post["patente"]}\">";
                    $loghtml .= "<input type=\"hidden\" name=\"act-aduana\" id=\"act-aduana\" value=\"{$post["aduana"]}\">";
                    $loghtml .= "<input type=\"hidden\" name=\"act-pedimento\" id=\"act-pedimento\" value=\"{$post["pedimento"]}\">";
                    $loghtml .= "</form>";
                    $loghtml .= "<table style=\"background: #fff\">";
                    foreach ($log as $item) {
                        $loghtml .= "<tr>";
                        $loghtml .= "<td style=\"background: #fff\">{$item["mensaje"]}</td>";
                        $loghtml .= "<td style=\"background: #fff\">" . date('d/m/Y H:i a', strtotime($item["creado"])) . "</td>";
                        $loghtml .= "</tr>";
                    }
                    $loghtml .= "</table>";
                }
                echo Zend_Json::encode(array('success' => true, 'log' => $loghtml));
                exit();
            } else {
                echo Zend_Json::encode(array('success' => false));
                exit();
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function obtenerArchivoLogAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
        }
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $post = $request->getPost();
                $table = new Operaciones_Model_ValidadorLog();
                if (isset($post["id"])) {
                    $file = $table->obtenerContenido($post["id"]);
                    if (isset($file) && $file != false) {
                        echo Zend_Json::encode(array('success' => true, 'html' => $file["contenido"], 'descarga' => "<a href=\"/operaciones/data/guardar-archivo?id={$post["id"]}\" style=\"font-size: 11px; font-family: sans-serif\">{$file["archivo"]}</a>"));
                        exit();
                    } else {
                        echo Zend_Json::encode(array('success' => false, 'html' => "No existen archivo."));
                        exit();
                    }
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function guardarArchivoAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $id = $this->_request->getParam("id", null);
        try {
            if (isset($id) && $id != null) {
                $table = new Operaciones_Model_ValidadorLog();
                $file = $table->obtenerContenido($id);
                header("Content-disposition: attachment; filename={$file["archivo"]}");
                header('Content-Length: ' . strlen(base64_decode($file["contenido"])));
                header("Content-type: text/plain");
                echo base64_decode($file["contenido"]);
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function operacionesPorUsuariosAction() {
        $year = $this->_request->getParam("year", (int) date("Y"));
        $patente = $this->_request->getParam("patente", 3589);
        $aduana = $this->_request->getParam("aduana", 640);
        if ($patente == 3589) {
            if ($aduana == 640) {
                $sitawin = new OAQ_Sitawin(true, "192.168.200.5", "sa", "adminOAQ123", "SITA43589640", 1433, "Pdo_Mssql");
            } elseif ($aduana == 646) {
                $sitawin = new OAQ_Sitawin(true, "192.168.200.5", "sa", "adminOAQ123", "SITA43589640", 1433, "Pdo_Mssql");
            } elseif ($aduana == 240) {
                $sitawin = new OAQ_Sitawin(true, "192.168.200.5", "sa", "adminOAQ123", "SITA43589240", 1433, "Pdo_Mssql");
            }
        }
        $data = $sitawin->operacionesPorUsuario($year);
        foreach ($data as $item) {
            $graph_data[] = array(
                "name" => $item["Usuario"],
                "data" => array(
                    (int) $item["Ene"],
                    (int) $item["Feb"],
                    (int) $item["Mar"],
                    (int) $item["Abr"],
                    (int) $item["May"],
                    (int) $item["Jun"],
                    (int) $item["Jul"],
                    (int) $item["Ago"],
                    (int) $item["Sep"],
                    (int) $item["Oct"],
                    (int) $item["Nov"],
                    (int) $item["Dic"]
                ),
                "pointPadding" => 10,
                "pointWidth" => 10
            );
        }
        echo json_encode($graph_data);
        exit;
    }

    public function operacionesTotalesAction() {
        $year = $this->_request->getParam("year", (int) date("Y"));
        $patente = $this->_request->getParam("patente", 3589);
        $aduana = $this->_request->getParam("aduana", 640);
        if ($patente == 3589) {
            $sitawin = new OAQ_Sitawin(true, "192.168.200.5", "sa", "adminOAQ123", "SITA43589640", 1433, "Pdo_Mssql");
            $sitawinaero = new OAQ_Sitawin(true, "192.168.200.5", "sa", "adminOAQ123", "SITA43589640", 1433, "Pdo_Mssql");
            $sitawinnl = new OAQ_Sitawin(true, "192.168.200.5", "sa", "adminOAQ123", "SITA43589240", 1433, "Pdo_Mssql");
        }
        $sum = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
        $data = $sitawin->operacionesTotales($year);
        foreach ($data as $item) {
            $graph_data[] = array(
                "name" => $patente . "-" . "640",
                "data" => array(
                    (int) $item["Ene"] ? (int) $item["Ene"] : null,
                    (int) $item["Feb"] ? (int) $item["Feb"] : null,
                    (int) $item["Mar"] ? (int) $item["Mar"] : null,
                    (int) $item["Abr"] ? (int) $item["Abr"] : null,
                    (int) $item["May"] ? (int) $item["May"] : null,
                    (int) $item["Jun"] ? (int) $item["Jun"] : null,
                    (int) $item["Jul"] ? (int) $item["Jul"] : null,
                    (int) $item["Ago"] ? (int) $item["Ago"] : null,
                    (int) $item["Sep"] ? (int) $item["Sep"] : null,
                    (int) $item["Oct"] ? (int) $item["Oct"] : null,
                    (int) $item["Nov"] ? (int) $item["Nov"] : null,
                    (int) $item["Dic"] ? (int) $item["Dic"] : null
                ),
                "pointPadding" => 10,
                "pointWidth" => 10
            );
            $sum[0] += $item["Ene"];
            $sum[1] += $item["Feb"];
            $sum[2] += $item["Mar"];
            $sum[3] += $item["Abr"];
            $sum[4] += $item["May"];
            $sum[5] += $item["Jun"];
            $sum[6] += $item["Jul"];
            $sum[7] += $item["Ago"];
            $sum[8] += $item["Sep"];
            $sum[9] += $item["Oct"];
            $sum[10] += $item["Nov"];
            $sum[11] += $item["Dic"];
        }
        $data = $sitawinaero->operacionesTotales($year);
        foreach ($data as $item) {
            $graph_data[] = array(
                "name" => $patente . "-" . "646",
                "data" => array(
                    (int) $item["Ene"] ? (int) $item["Ene"] : null,
                    (int) $item["Feb"] ? (int) $item["Feb"] : null,
                    (int) $item["Mar"] ? (int) $item["Mar"] : null,
                    (int) $item["Abr"] ? (int) $item["Abr"] : null,
                    (int) $item["May"] ? (int) $item["May"] : null,
                    (int) $item["Jun"] ? (int) $item["Jun"] : null,
                    (int) $item["Jul"] ? (int) $item["Jul"] : null,
                    (int) $item["Ago"] ? (int) $item["Ago"] : null,
                    (int) $item["Sep"] ? (int) $item["Sep"] : null,
                    (int) $item["Oct"] ? (int) $item["Oct"] : null,
                    (int) $item["Nov"] ? (int) $item["Nov"] : null,
                    (int) $item["Dic"] ? (int) $item["Dic"] : null
                ),
                "pointPadding" => 10,
                "pointWidth" => 10
            );
            $sum[0] += $item["Ene"];
            $sum[1] += $item["Feb"];
            $sum[2] += $item["Mar"];
            $sum[3] += $item["Abr"];
            $sum[4] += $item["May"];
            $sum[5] += $item["Jun"];
            $sum[6] += $item["Jul"];
            $sum[7] += $item["Ago"];
            $sum[8] += $item["Sep"];
            $sum[9] += $item["Oct"];
            $sum[10] += $item["Nov"];
            $sum[11] += $item["Dic"];
        }
        $data = $sitawinnl->operacionesTotales($year);
        foreach ($data as $item) {
            $graph_data[] = array(
                "name" => $patente . "-" . "240",
                "data" => array(
                    (int) $item["Ene"] ? (int) $item["Ene"] : null,
                    (int) $item["Feb"] ? (int) $item["Feb"] : null,
                    (int) $item["Mar"] ? (int) $item["Mar"] : null,
                    (int) $item["Abr"] ? (int) $item["Abr"] : null,
                    (int) $item["May"] ? (int) $item["May"] : null,
                    (int) $item["Jun"] ? (int) $item["Jun"] : null,
                    (int) $item["Jul"] ? (int) $item["Jul"] : null,
                    (int) $item["Ago"] ? (int) $item["Ago"] : null,
                    (int) $item["Sep"] ? (int) $item["Sep"] : null,
                    (int) $item["Oct"] ? (int) $item["Oct"] : null,
                    (int) $item["Nov"] ? (int) $item["Nov"] : null,
                    (int) $item["Dic"] ? (int) $item["Dic"] : null
                ),
                "pointPadding" => 10,
                "pointWidth" => 10
            );
            $sum[0] += $item["Ene"];
            $sum[1] += $item["Feb"];
            $sum[2] += $item["Mar"];
            $sum[3] += $item["Abr"];
            $sum[4] += $item["May"];
            $sum[5] += $item["Jun"];
            $sum[6] += $item["Jul"];
            $sum[7] += $item["Ago"];
            $sum[8] += $item["Sep"];
            $sum[9] += $item["Oct"];
            $sum[10] += $item["Nov"];
            $sum[11] += $item["Dic"];
        }
        $graph_data[] = array(
            "name" => "Totales",
            "data" => array(
                (int) $sum[0] ? (int) $sum[0] : null,
                (int) $sum[1] ? (int) $sum[1] : null,
                (int) $sum[2] ? (int) $sum[2] : null,
                (int) $sum[3] ? (int) $sum[3] : null,
                (int) $sum[4] ? (int) $sum[4] : null,
                (int) $sum[5] ? (int) $sum[5] : null,
                (int) $sum[6] ? (int) $sum[6] : null,
                (int) $sum[7] ? (int) $sum[7] : null,
                (int) $sum[8] ? (int) $sum[8] : null,
                (int) $sum[9] ? (int) $sum[9] : null,
                (int) $sum[10] ? (int) $sum[10] : null,
                (int) $sum[11] ? (int) $sum[11] : null
            ),
            "pointPadding" => 10,
            "pointWidth" => 10
        );
        $this->_helper->json($graph_data);
    }

    public function operacionesSumarizadasAction() {
        $year = $this->_request->getParam("year", (int) date("Y"));
        $patente = $this->_request->getParam("patente", 3589);
        $aduana = $this->_request->getParam("aduana", 640);
        if ($patente == 3589) {
            if ($aduana == 640) {
                $sitawin = new OAQ_Sitawin(true, "192.168.200.5", "sa", "adminOAQ123", "SITA43589640", 1433, "Pdo_Mssql");
            } elseif ($aduana == 646) {
                $sitawin = new OAQ_Sitawin(true, "192.168.200.5", "sa", "adminOAQ123", "SITA43589640", 1433, "Pdo_Mssql");
            } elseif ($aduana == 240) {
                $sitawin = new OAQ_Sitawin(true, "192.168.200.5", "sa", "adminOAQ123", "SITA43589240", 1433, "Pdo_Mssql");
            }
        }
        $data = $sitawin->operacionesSumarizadas($year);
        foreach ($data as $item) {
            $graph_data[] = array(
                "name" => $item["Usuario"],
                "y" => (int) $item["TotalPagados"]
            );
        }
        $this->_helper->json($graph_data);
    }

    public function operacionesClientesAction() {
        try {
            $year = $this->_request->getParam("year", (int) date("Y"));
            $rfc = $this->_request->getParam("rfc", null);
            $patente = $this->_request->getParam("patente", null);
            $aduana = $this->_request->getParam("aduana", null);
            $misc = new OAQ_Misc();
            $sitawin = $misc->sitawin($patente, $aduana);
            $data = $sitawin->operacionesCliente($year, $rfc);
            if (!empty($data)) {
                foreach ($data as $item) {
                    $graph_data[] = array(
                        "name" => ($item["TipoOperacion"] == 1) ? "IMPO" : "EXPO",
                        "data" => array(
                            (int) $item["Ene"] ? (int) $item["Ene"] : null,
                            (int) $item["Feb"] ? (int) $item["Feb"] : null,
                            (int) $item["Mar"] ? (int) $item["Mar"] : null,
                            (int) $item["Abr"] ? (int) $item["Abr"] : null,
                            (int) $item["May"] ? (int) $item["May"] : null,
                            (int) $item["Jun"] ? (int) $item["Jun"] : null,
                            (int) $item["Jul"] ? (int) $item["Jul"] : null,
                            (int) $item["Ago"] ? (int) $item["Ago"] : null,
                            (int) $item["Sep"] ? (int) $item["Sep"] : null,
                            (int) $item["Oct"] ? (int) $item["Oct"] : null,
                            (int) $item["Nov"] ? (int) $item["Nov"] : null,
                            (int) $item["Dic"] ? (int) $item["Dic"] : null
                        ),
                        "pointPadding" => 10,
                        "pointWidth" => 10
                    );
                    $sum[0] += $item["Ene"];
                    $sum[1] += $item["Feb"];
                    $sum[2] += $item["Mar"];
                    $sum[3] += $item["Abr"];
                    $sum[4] += $item["May"];
                    $sum[5] += $item["Jun"];
                    $sum[6] += $item["Jul"];
                    $sum[7] += $item["Ago"];
                    $sum[8] += $item["Sep"];
                    $sum[9] += $item["Oct"];
                    $sum[10] += $item["Nov"];
                    $sum[11] += $item["Dic"];
                }
                $graph_data[] = array(
                    "name" => "TOTAL",
                    "data" => array(
                        (int) $sum[0] ? (int) $sum[0] : null,
                        (int) $sum[1] ? (int) $sum[1] : null,
                        (int) $sum[2] ? (int) $sum[2] : null,
                        (int) $sum[3] ? (int) $sum[3] : null,
                        (int) $sum[4] ? (int) $sum[4] : null,
                        (int) $sum[5] ? (int) $sum[5] : null,
                        (int) $sum[6] ? (int) $sum[6] : null,
                        (int) $sum[7] ? (int) $sum[7] : null,
                        (int) $sum[8] ? (int) $sum[8] : null,
                        (int) $sum[9] ? (int) $sum[9] : null,
                        (int) $sum[10] ? (int) $sum[10] : null,
                        (int) $sum[11] ? (int) $sum[11] : null
                    ),
                    "pointPadding" => 10,
                    "pointWidth" => 10
                );
                echo json_encode($graph_data);
                exit;
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function operacionesClientesCvePedimentosAction() {
        try {
            $year = $this->_request->getParam("year", (int) date("Y"));
            $rfc = $this->_request->getParam("rfc", null);
            $patente = $this->_request->getParam("patente", null);
            $aduana = $this->_request->getParam("aduana", null);
            $misc = new OAQ_Misc();
            $sitawin = $misc->sitawin($patente, $aduana);
            $data = $sitawin->operacionesClienteCve($year, $rfc);
            if (!empty($data)) {
                foreach ($data as $item) {
                    $graph_data[] = array(
                        "name" => $item["CvePedimento"],
                        "data" => array(
                            (int) $item["Ene"] ? (int) $item["Ene"] : null,
                            (int) $item["Feb"] ? (int) $item["Feb"] : null,
                            (int) $item["Mar"] ? (int) $item["Mar"] : null,
                            (int) $item["Abr"] ? (int) $item["Abr"] : null,
                            (int) $item["May"] ? (int) $item["May"] : null,
                            (int) $item["Jun"] ? (int) $item["Jun"] : null,
                            (int) $item["Jul"] ? (int) $item["Jul"] : null,
                            (int) $item["Ago"] ? (int) $item["Ago"] : null,
                            (int) $item["Sep"] ? (int) $item["Sep"] : null,
                            (int) $item["Oct"] ? (int) $item["Oct"] : null,
                            (int) $item["Nov"] ? (int) $item["Nov"] : null,
                            (int) $item["Dic"] ? (int) $item["Dic"] : null
                        ),
                        "pointPadding" => 10,
                        "pointWidth" => 10
                    );
                }
                echo json_encode($graph_data);
                exit;
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function operacionesDiariasClientesAction() {
        try {
            $year = $this->_request->getParam("year", (int) date("Y"));
            $mes = $this->_request->getParam("month", null);
            $dia = $this->_request->getParam("day", null);
            $patente = $this->_request->getParam("patente", null);
            $aduana = $this->_request->getParam("aduana", null);
            if ($patente == 3589) {
                if ($aduana == 640) {
                    $sitawin = new OAQ_Sitawin(true, "192.168.200.5", "sa", "adminOAQ123", "SITA43589640", 1433, "Pdo_Mssql");
                } elseif ($aduana == 646) {
                    $sitawin = new OAQ_Sitawin(true, "192.168.200.5", "sa", "adminOAQ123", "SITA43589640", 1433, "Pdo_Mssql");
                } elseif ($aduana == 240) {
                    $sitawin = new OAQ_Sitawin(true, "192.168.200.5", "sa", "adminOAQ123", "SITA43589240", 1433, "Pdo_Mssql");
                }
            }
            $data = $sitawin->operacionesPorDiaClientes($year, $mes, $dia);
            if (!empty($data)) {
                foreach ($data as $item) {
                    $graph_data[] = array(
                        "name" => substr($item["NomCliente"], 0, 15) . " ...",
                        "y" => $item["TotalPagados"] ? (int) $item["TotalPagados"] : null
                    );
                }
                echo json_encode($graph_data);
                exit;
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function operacionesMensualesAction() {
        try {
            $year = $this->_request->getParam("year", (int) date("Y"));
            $mes = $this->_request->getParam("month", (int) date("m"));
            $sitawin = new OAQ_Sitawin(true, "192.168.200.5", "sa", "adminOAQ123", "SITA43589640", 1433, "Pdo_Mssql");
            $data = $sitawin->operacionesPorMes($year, $mes);
            unset($sitawin);
            $sitawin = new OAQ_Sitawin(true, "192.168.200.5", "sa", "adminOAQ123", "SITA43589640", 1433, "Pdo_Mssql");
            $aero = $sitawin->operacionesPorMes($year, $mes);
            unset($sitawin);
            $sitawin = new OAQ_Sitawin(true, "192.168.200.5", "sa", "adminOAQ123", "SITA43589240", 1433, "Pdo_Mssql");
            $nl = $sitawin->operacionesPorMes($year, $mes);
            if (!empty($data)) {
                foreach ($data as $item) {
                    $graph_data[] = array(
                        "name" => "OPE.ESP",
                        "data" => array(
                            (int) $item["1"] ? (int) $item["1"] : null,
                            (int) $item["2"] ? (int) $item["2"] : null,
                            (int) $item["3"] ? (int) $item["3"] : null,
                            (int) $item["4"] ? (int) $item["4"] : null,
                            (int) $item["5"] ? (int) $item["5"] : null,
                            (int) $item["6"] ? (int) $item["6"] : null,
                            (int) $item["7"] ? (int) $item["7"] : null,
                            (int) $item["8"] ? (int) $item["8"] : null,
                            (int) $item["9"] ? (int) $item["9"] : null,
                            (int) $item["10"] ? (int) $item["10"] : null,
                            (int) $item["11"] ? (int) $item["11"] : null,
                            (int) $item["12"] ? (int) $item["12"] : null,
                            (int) $item["13"] ? (int) $item["13"] : null,
                            (int) $item["14"] ? (int) $item["14"] : null,
                            (int) $item["15"] ? (int) $item["15"] : null,
                            (int) $item["16"] ? (int) $item["16"] : null,
                            (int) $item["17"] ? (int) $item["17"] : null,
                            (int) $item["18"] ? (int) $item["18"] : null,
                            (int) $item["19"] ? (int) $item["19"] : null,
                            (int) $item["20"] ? (int) $item["20"] : null,
                            (int) $item["21"] ? (int) $item["21"] : null,
                            (int) $item["22"] ? (int) $item["22"] : null,
                            (int) $item["23"] ? (int) $item["23"] : null,
                            (int) $item["24"] ? (int) $item["24"] : null,
                            (int) $item["25"] ? (int) $item["25"] : null,
                            (int) $item["26"] ? (int) $item["26"] : null,
                            (int) $item["27"] ? (int) $item["27"] : null,
                            (int) $item["28"] ? (int) $item["28"] : null,
                            (int) $item["29"] ? (int) $item["29"] : null,
                            (int) $item["30"] ? (int) $item["30"] : null,
                            (int) $item["31"] ? (int) $item["31"] : null,
                        )
                    );
                }
            }
            if (!empty($aero)) {
                foreach ($aero as $item) {
                    $graph_data[] = array(
                        "name" => "AEROPUERTO",
                        "data" => array(
                            (int) $item["1"] ? (int) $item["1"] : null,
                            (int) $item["2"] ? (int) $item["2"] : null,
                            (int) $item["3"] ? (int) $item["3"] : null,
                            (int) $item["4"] ? (int) $item["4"] : null,
                            (int) $item["5"] ? (int) $item["5"] : null,
                            (int) $item["6"] ? (int) $item["6"] : null,
                            (int) $item["7"] ? (int) $item["7"] : null,
                            (int) $item["8"] ? (int) $item["8"] : null,
                            (int) $item["9"] ? (int) $item["9"] : null,
                            (int) $item["10"] ? (int) $item["10"] : null,
                            (int) $item["11"] ? (int) $item["11"] : null,
                            (int) $item["12"] ? (int) $item["12"] : null,
                            (int) $item["13"] ? (int) $item["13"] : null,
                            (int) $item["14"] ? (int) $item["14"] : null,
                            (int) $item["15"] ? (int) $item["15"] : null,
                            (int) $item["16"] ? (int) $item["16"] : null,
                            (int) $item["17"] ? (int) $item["17"] : null,
                            (int) $item["18"] ? (int) $item["18"] : null,
                            (int) $item["19"] ? (int) $item["19"] : null,
                            (int) $item["20"] ? (int) $item["20"] : null,
                            (int) $item["21"] ? (int) $item["21"] : null,
                            (int) $item["22"] ? (int) $item["22"] : null,
                            (int) $item["23"] ? (int) $item["23"] : null,
                            (int) $item["24"] ? (int) $item["24"] : null,
                            (int) $item["25"] ? (int) $item["25"] : null,
                            (int) $item["26"] ? (int) $item["26"] : null,
                            (int) $item["27"] ? (int) $item["27"] : null,
                            (int) $item["28"] ? (int) $item["28"] : null,
                            (int) $item["29"] ? (int) $item["29"] : null,
                            (int) $item["30"] ? (int) $item["30"] : null,
                            (int) $item["31"] ? (int) $item["31"] : null,
                        )
                    );
                }
            }
            if (!empty($nl)) {
                foreach ($nl as $item) {
                    $graph_data[] = array(
                        "name" => "NVO.LAREDO",
                        "data" => array(
                            (int) $item["1"] ? (int) $item["1"] : null,
                            (int) $item["2"] ? (int) $item["2"] : null,
                            (int) $item["3"] ? (int) $item["3"] : null,
                            (int) $item["4"] ? (int) $item["4"] : null,
                            (int) $item["5"] ? (int) $item["5"] : null,
                            (int) $item["6"] ? (int) $item["6"] : null,
                            (int) $item["7"] ? (int) $item["7"] : null,
                            (int) $item["8"] ? (int) $item["8"] : null,
                            (int) $item["9"] ? (int) $item["9"] : null,
                            (int) $item["10"] ? (int) $item["10"] : null,
                            (int) $item["11"] ? (int) $item["11"] : null,
                            (int) $item["12"] ? (int) $item["12"] : null,
                            (int) $item["13"] ? (int) $item["13"] : null,
                            (int) $item["14"] ? (int) $item["14"] : null,
                            (int) $item["15"] ? (int) $item["15"] : null,
                            (int) $item["16"] ? (int) $item["16"] : null,
                            (int) $item["17"] ? (int) $item["17"] : null,
                            (int) $item["18"] ? (int) $item["18"] : null,
                            (int) $item["19"] ? (int) $item["19"] : null,
                            (int) $item["20"] ? (int) $item["20"] : null,
                            (int) $item["21"] ? (int) $item["21"] : null,
                            (int) $item["22"] ? (int) $item["22"] : null,
                            (int) $item["23"] ? (int) $item["23"] : null,
                            (int) $item["24"] ? (int) $item["24"] : null,
                            (int) $item["25"] ? (int) $item["25"] : null,
                            (int) $item["26"] ? (int) $item["26"] : null,
                            (int) $item["27"] ? (int) $item["27"] : null,
                            (int) $item["28"] ? (int) $item["28"] : null,
                            (int) $item["29"] ? (int) $item["29"] : null,
                            (int) $item["30"] ? (int) $item["30"] : null,
                            (int) $item["31"] ? (int) $item["31"] : null,
                        )
                    );
                }
            }
            if (isset($graph_data)) {
                echo json_encode($graph_data);
                exit;
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function operacionesMensualesImpExpAction() {
        try {
            $year = $this->_request->getParam("year", (int) date("Y"));
            $mes = $this->_request->getParam("month", (int) date("m"));
            $patente = $this->_request->getParam("patente", null);
            $aduana = $this->_request->getParam("aduana", null);

            if ($patente == 3589) {
                if ($aduana == 640) {
                    $sitawin = new OAQ_Sitawin(true, "192.168.200.5", "sa", "adminOAQ123", "SITA43589640", 1433, "Pdo_Mssql");
                } elseif ($aduana == 646) {
                    $sitawin = new OAQ_Sitawin(true, "192.168.200.5", "sa", "adminOAQ123", "SITA43589640", 1433, "Pdo_Mssql");
                } elseif ($aduana == 240) {
                    $sitawin = new OAQ_Sitawin(true, "192.168.200.5", "sa", "adminOAQ123", "SITA43589240", 1433, "Pdo_Mssql");
                }
            }
            $data = $sitawin->operacionesPorMesImpExp($year, $mes);
            if (!empty($data)) {
                foreach ($data as $item) {
                    $graph_data[] = array(
                        "name" => ($item["ImpExp"] == "1") ? "IMP" : "EXP",
                        "data" => array(
                            (int) $item["1"] ? (int) $item["1"] : null,
                            (int) $item["2"] ? (int) $item["2"] : null,
                            (int) $item["3"] ? (int) $item["3"] : null,
                            (int) $item["4"] ? (int) $item["4"] : null,
                            (int) $item["5"] ? (int) $item["5"] : null,
                            (int) $item["6"] ? (int) $item["6"] : null,
                            (int) $item["7"] ? (int) $item["7"] : null,
                            (int) $item["8"] ? (int) $item["8"] : null,
                            (int) $item["9"] ? (int) $item["9"] : null,
                            (int) $item["10"] ? (int) $item["10"] : null,
                            (int) $item["11"] ? (int) $item["11"] : null,
                            (int) $item["12"] ? (int) $item["12"] : null,
                            (int) $item["13"] ? (int) $item["13"] : null,
                            (int) $item["14"] ? (int) $item["14"] : null,
                            (int) $item["15"] ? (int) $item["15"] : null,
                            (int) $item["16"] ? (int) $item["16"] : null,
                            (int) $item["17"] ? (int) $item["17"] : null,
                            (int) $item["18"] ? (int) $item["18"] : null,
                            (int) $item["19"] ? (int) $item["19"] : null,
                            (int) $item["20"] ? (int) $item["20"] : null,
                            (int) $item["21"] ? (int) $item["21"] : null,
                            (int) $item["22"] ? (int) $item["22"] : null,
                            (int) $item["23"] ? (int) $item["23"] : null,
                            (int) $item["24"] ? (int) $item["24"] : null,
                            (int) $item["25"] ? (int) $item["25"] : null,
                            (int) $item["26"] ? (int) $item["26"] : null,
                            (int) $item["27"] ? (int) $item["27"] : null,
                            (int) $item["28"] ? (int) $item["28"] : null,
                            (int) $item["29"] ? (int) $item["29"] : null,
                            (int) $item["30"] ? (int) $item["30"] : null,
                            (int) $item["31"] ? (int) $item["31"] : null,
                        )
                    );
                }
            }
            if (isset($graph_data)) {
                echo json_encode($graph_data);
                exit;
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function operacionesDiariasUsuariosAction() {
        try {
            $year = $this->_request->getParam("year", (int) date("Y"));
            $mes = $this->_request->getParam("month", null);
            $dia = $this->_request->getParam("day", null);
            $patente = $this->_request->getParam("patente", null);
            $aduana = $this->_request->getParam("aduana", null);
            if ($patente == 3589) {
                if ($aduana == 640) {
                    $sitawin = new OAQ_Sitawin(true, "192.168.200.5", "sa", "adminOAQ123", "SITA43589640", 1433, "Pdo_Mssql");
                } elseif ($aduana == 646) {
                    $sitawin = new OAQ_Sitawin(true, "192.168.200.5", "sa", "adminOAQ123", "SITA43589640", 1433, "Pdo_Mssql");
                } elseif ($aduana == 240) {
                    $sitawin = new OAQ_Sitawin(true, "192.168.200.5", "sa", "adminOAQ123", "SITA43589240", 1433, "Pdo_Mssql");
                }
            }
            $data = $sitawin->operacionesPorDiaUsuarios($year, $mes, $dia);
            if (!empty($data)) {
                foreach ($data as $item) {
                    $graph_data[] = array(
                        "name" => ($item["Usuario"] == "1") ? "GMEJIA" : $item["Usuario"],
                        "y" => $item["TotalPagados"] ? (int) $item["TotalPagados"] : null
                    );
                }
                echo json_encode($graph_data);
                exit;
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

}
