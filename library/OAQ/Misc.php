<?php

/**
 * Clase para utilerias diversas o miscelaneas
 *
 * @author Jaime E. Valdez <jvaldezch@gmail.com>
 */
class OAQ_Misc {

    protected $_config;
    protected $_frontendOptions;
    protected $_backendOptions;
    protected $_queryCache;
    protected $_logger;
    protected $_baseDir;
    protected $_newDir;
    
    function get_baseDir() {
        return $this->_baseDir;
    }

    function set_baseDir($_baseDir) {
        $this->_baseDir = $_baseDir;
    }
    
    function __construct() {
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
        $this->_frontendOptions = array(
            'lifetime' => 60 * 20, // 20 minutes
            'automatic_serialization' => true,
        );
        $this->_backendOptions = array(
            'cache_dir' => APPLICATION_PATH . DIRECTORY_SEPARATOR . 'cache/',
            'file_name_prefix' => 'zend_cache',
            'hashed_directory_level' => 2,
        );
        $this->_queryCache = Zend_Cache::factory('Core', 'File', $this->_frontendOptions, $this->_backendOptions);
        $this->_logger = Zend_Registry::get("logDb");
    }

    /**
     * 
     * @param String $postedDate
     * @return boolean
     */
    public function myCheckDateFormat($postedDate) {
        if (preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/', $postedDate)) {
            list($year, $month, $day) = explode('-', $postedDate);
            return checkdate($month, $day, $year);
        } else {
            return false;
        }
    }

    /**
     * 
     * @param String $val
     * @return String
     */
    public function myEncrypt($val) {
        $filter = new Zend_Filter_Encrypt(array(
            'key' => $this->_config->app->key,
        ));
        $filter->setAdapter('mcrypt')
                ->setVector($this->_config->app->vector);
        return urlencode(base64_encode($filter->filter($val)));
    }

    /**
     * 
     * @param String $val
     * @return String
     */
    public function myDecrypt($val) {
        $dfilter = new Zend_Filter_Decrypt(array(
            'key' => $this->_config->app->key,
        ));
        $dfilter->setAdapter('mcrypt')
                ->setVector($this->_config->app->vector);
        return $dfilter->filter(base64_decode(urldecode($val)));
    }
    
    protected $_key = "bcb04b7e103a0cd8b54763051cef08bc55abe029fdebae5e1d417e2ffb2a00a3";
    
    public function encrypter($plaintext) {
        $key = pack('H*', $this->_key);
        # crear una aleatoria IV para utilizarla co condificación CBC
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        # crea un texto cifrado compatible con AES (tamaño de bloque Rijndael = 128)
        # para hacer el texto confidencial 
        # solamente disponible para entradas codificadas que nunca finalizan con el
        # el valor  00h (debido al relleno con ceros)
        $ciphertext = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $plaintext, MCRYPT_MODE_CBC, $iv);
        # anteponer la IV para que esté disponible para el descifrado
        $ciphertext = $iv . $ciphertext;
        # codificar el texto cifrado resultante para que pueda ser representado por un string
        $ciphertext_base64 = base64_encode($ciphertext);
        return $ciphertext_base64;
    }
    
    public function decrypter($ciphertext_base64) {
        $key = pack('H*', $this->_key);        
        $ciphertext_dec = base64_decode($ciphertext_base64);    
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        # recupera la IV, iv_size debería crearse usando mcrypt_get_iv_size()
        $iv_dec = substr($ciphertext_dec, 0, $iv_size);
        # recupera el texto cifrado (todo excepto el $iv_size en el frente)
        $ciphertext_dec = substr($ciphertext_dec, $iv_size);
        # podrían eliminarse los caracteres con valor 00h del final del texto puro
        $plaintext_dec = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $ciphertext_dec, MCRYPT_MODE_CBC, $iv_dec);
        return preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $plaintext_dec);
    }

    /**
     * 
     * @param String $date
     * @param String $aduana
     * @return String
     */
    public function fixDate($date, $aduana) {
        if ($aduana == '240' || $aduana == '430') {
            $remove = str_replace(array(' PM', ' AM'), '', $date);
            $datetime = explode(' ', $remove);
            list($month, $day, $year) = explode('/', $datetime[0]);
            return $year . '-' . str_pad($month, 2, 0, STR_PAD_LEFT) . '-' . str_pad($day, 2, 0, STR_PAD_LEFT) . ' ' . $datetime[1];
        } else if ($aduana == '470' || $aduana == '160') {
            $remove = str_replace(array(' p.m.', ' a.m.'), '', $date);
            $datetime = explode(' ', $remove);
            list($day, $month, $year) = explode('/', $datetime[0]);
            return $year . '-' . str_pad($month, 2, 0, STR_PAD_LEFT) . '-' . str_pad($day, 2, 0, STR_PAD_LEFT) . ' ' . $datetime[1];
        }
    }

    protected function changeDate($fecha) {
        $day = substr($fecha, 0, 2);
        $month = substr($fecha, 2, 2);
        $year = substr($fecha, 4, 4);

        return date('Y-m-d H:i:s', strtotime($year . '-' . $month . '-' . $day));
    }

    public function processValidationFiles($dir) {
        try {
            $dir = new DirectoryIterator($dir);
            $validacion = array();
            $preValidacion = array();
            $respuesta = array();
            $pagoEnvio = array();
            $pagoRespuesta = array();

            foreach ($dir as $fileinfo) {
                if (!$fileinfo->isDot()) {
                    $tmp = array(
                        'archivo_nombre' => strtolower($fileinfo->getFilename()),
                        'archivo_num' => substr(strtoupper(substr($fileinfo->getFilename(), 0, 8)), 1),
                        'archivo_num_juliano' => substr($fileinfo->getFilename(), 1),
                    );
                    $fp = fopen($fileinfo->getPathname(), "r") or die("Couldn't open $fileinfo->getPathname()");

                    if (preg_match('/^error/i', $fileinfo->getFilename())) {
                        continue;
                    }

                    /////////////////////////////// ------------------------- ARCHIVO DE VALIDACIÓN                
                    if (preg_match('/^M/i', $fileinfo->getFilename()) && !preg_match('/.err$/', $fileinfo->getFilename())) {
                        while (!feof($fp)) {
                            $line = fgets($fp, 4096);
                            if (preg_match('/^500/', $line)) {
                                $val = explode("|", $line);
                                $tmp['tipo_mov'] = $val[1];
                                $tmp['patente'] = $val[2];
                                $tmp['pedimento'] = $val[3];
                                $tmp['aduana'] = $val[4];
                            }
                            if (preg_match('/^501/', $line)) {
                                $val = explode("|", $line);
                                $tmp['rfc'] = $val[8];
                                $tmp['cve_doc'] = $val[5];
                            }
                            if (preg_match('/^506/', $line)) {
                                $val = explode("|", $line);
                                switch ($val[2]) {
                                    case '1':
                                        $tmp['fecha_entrada'] = $this->changeDate($val[3]);
                                        break;
                                    case '2':
                                        $tmp['fecha_pago'] = $this->changeDate($val[3]);
                                        break;
                                    case '3':
                                        $tmp['fecha_extraccion'] = $this->changeDate($val[3]);
                                        break;
                                    case '5':
                                        $tmp['fecha_presentacion'] = $this->changeDate($val[3]);
                                        break;
                                    case '6':
                                        $tmp['fecha_impeuacan'] = $this->changeDate($val[3]);
                                        break;
                                    case '7':
                                        $tmp['fecha_original'] = $this->changeDate($val[3]);
                                        break;
                                    default:
                                        break;
                                }
                            }
                            if (preg_match('/^505/', $line)) {
                                $val = explode("|", $line);
                                if (!isset($tmp['coves'])) {
                                    $tmp['coves'] = $val['3'] . ',';
                                } else {
                                    $tmp['coves'] .= $val['3'] . ',';
                                }
                            }
                        }
                        $tmp['pedimento_content'] = utf8_encode(file_get_contents($fileinfo->getPathname()));
                        $validacion[] = $tmp;
                        unset($tmp);
                    }
                    /////////////////////////////// ------------------------- ARCHIVO DE PRE VALIDACIÓN
                    if (preg_match('/^K/i', $fileinfo->getFilename())) {
                        while (!feof($fp)) {
                            $line = fgets($fp, 4096);
                            if (strlen($line) != 0) {
                                if (strpos($line, 'FECHA: ')) {
                                    $posFecha = strpos($line, 'FECHA: ') + 7;

                                    list($day, $month, $year) = explode('/', substr($line, $posFecha, 11));
                                    //$tmpDate = date('Y-m-d', strtotime($year . '-' . $month . '-' . $day));
                                    //$tmp['hora'] = substr($line,$posFecha + 17, 9);
                                    //$tmpDate = date('Y-m-d H:i:s', strtotime($year . '-' . $month . '-' . $day) . ' '. substr($line,$posFecha + 17, 9));
                                    $tmp['fecha'] = date('Y-m-d H:i:s', strtotime($year . '-' . $month . '-' . $day . ' ' . substr($line, $posFecha + 17, 9)));
                                }
                            }
                        }
                        $tmp['prevalidacion_content'] = utf8_encode(file_get_contents($fileinfo->getPathname()));
                        $preValidacion[] = $tmp;
                        unset($tmp);
                    }
                    /////////////////////////////// ------------------------- ARCHIVO DE REPUESTA DE VALIDACIÓN
                    if (preg_match('/^M/i', $fileinfo->getFilename()) && preg_match('/.err$/', $fileinfo->getFilename())) {
                        while (!feof($fp)) {
                            $line = fgets($fp, 4096);
                            if (strlen($line) != 0) {
                                if (preg_match('/^F/i', $line)) {
                                    if (!isset($tmp['pedimento'])) {
                                        $tmp['pedimento'] = substr($line, 1, 7);
                                    }
                                    if (!isset($tmp['firma'])) {
                                        $tmp['firma'] = substr($line, 8, 8);
                                    }
                                } else if (preg_match('/^E/i', $line)) {
                                    $tmp['pedimento'] = substr($line, 1, 7);
                                    $tmp['error'] = true;
                                }
                            }
                        }
                        $tmp['respuesta_content'] = utf8_encode(file_get_contents($fileinfo->getPathname()));
                        $respuesta[] = $tmp;
                        unset($tmp);
                    }
                    /////////////////////////////// ------------------------- ARCHIVO DE ENVIO DE PAGO
                    if (preg_match('/^E/i', $fileinfo->getFilename())) {
                        while (!feof($fp)) {
                            $line = fgets($fp, 4096);
                            if (strlen($line) != 0) {
                                if (strpos($line, '3589')) {
                                    $posPedimento = strpos($line, '3589') + 4;
                                    $tmp['pedimento'] = substr($line, $posPedimento, 7);
                                    $tmp['cve_doc'] = substr($line, $posPedimento + 7, 2);
                                }
                            }
                        }
                        $tmp['enviopago_content'] = utf8_encode(file_get_contents($fileinfo->getPathname()));
                        $pagoEnvio[] = $tmp;
                        unset($tmp);
                    }
                    /////////////////////////////// ------------------------- ARCHIVO DE RESPUESTA DE PAGO
                    if (preg_match('/^A/i', $fileinfo->getFilename())) {
                        while (!feof($fp)) {
                            $line = fgets($fp, 4096);
                            $posPatente = strpos($line, '3589') + 4;
                            $posRfc = $posPatente + 7;
                            if (!isset($tmp['pedimento'])) {
                                $tmp['pedimento'] = substr($line, $posPatente, 7);
                            }
                            if (!isset($tmp['rfc'])) {
                                $tmp['rfc'] = substr($line, $posRfc, 12);
                            }
                            if (!isset($tmp['fecha'])) {
                                $pago = substr(trim($line), strlen(trim($line)) - 17, 16);
                                $tmpDate = substr($pago, 4, 4) . '-' . substr($pago, 2, 2) . '-' . substr($pago, 0, 2);
                                $tmpHour = substr($pago, 8, 2) . ':' . substr($pago, 11, 2) . ':' . substr($pago, 14, 2);
                                $tmp['fecha'] = date('Y-m-d H:i:s', strtotime($tmpDate . ' ' . $tmpHour));
                            }
                        }
                        $tmp['respuesta_content'] = utf8_encode(file_get_contents($fileinfo->getPathname()));
                        $pagoRespuesta[] = $tmp;
                        unset($tmp);
                    }
                    fclose($fp);
                    //unlink($fileinfo->getPathname());
                } // is position is not file                
            } // foreach file

            $fechas = array('fecha_entrada', 'fecha_pago', 'fecha_extraccion', 'fecha_presentacion', 'fecha_impeuacan', 'fecha_original', 'coves');
            $cleanValidation = array();

            if ($validacion) {
                foreach ($validacion as $valFile):
                    foreach ($fechas as $ftype):
                        if (!isset($valFile[$ftype]))
                            $valFile[$ftype] = NULL;
                    endforeach;
                    $cleanValidation[] = $valFile;
                endforeach;
            }
            $validationMapper = new Operaciones_Model_ValidationsMapper();

            if ($cleanValidation) {
                foreach ($cleanValidation as $valFile) {
                    if (isset($valFile['rfc'])) {
                        if (!$validationMapper->checkForValidationFile($valFile['archivo_nombre'], $valFile['patente'], $valFile['pedimento'], $valFile['aduana'])) {
                            $validationMapper->addNewM3File($valFile['archivo_nombre'], $valFile['archivo_num'], $valFile['archivo_num_juliano'], $valFile['patente'], $valFile['pedimento'], $valFile['aduana'], $valFile['rfc'], $valFile['cve_doc'], $valFile['pedimento_content'], $valFile['fecha_entrada'], $valFile['fecha_pago'], $valFile['fecha_extraccion'], $valFile['fecha_impeuacan'], $valFile['fecha_impeuacan'], $valFile['fecha_original'], $valFile['tipo_mov'], $valFile['coves']);
                        }
                    }
                }
            }

            if ($preValidacion) {
                foreach ($preValidacion as $preValFile) {
                    if (strlen($preValFile["prevalidacion_content"]) > 0 && isset($preValFile['fecha'])) {
                        if (!$validationMapper->checkForPreValidationFile($preValFile['archivo_nombre'])) {
                            $validationMapper->addNewPrevalidationFile($preValFile['archivo_nombre'], $preValFile['archivo_num'], $preValFile['archivo_num_juliano'], $preValFile['fecha'], $preValFile['prevalidacion_content']);
                        }
                    }
                }
            }

            //Zend_Debug::dump($respuesta);
            if ($respuesta) {
                foreach ($respuesta as $resp) {
                    if (strlen($resp["respuesta_content"]) > 0) {
                        if (!$validationMapper->checkForResponseFile($resp['archivo_nombre'])) {
                            if (!isset($resp['firma']))
                                $resp['firma'] = NULL;
                            if (!isset($resp['error']))
                                $resp['error'] = NULL;
                            $validationMapper->addNewResponseFile($resp['archivo_nombre'], $resp['archivo_num'], $resp['archivo_num_juliano'], $resp['pedimento'], $resp['firma'], $resp['error'], $resp['respuesta_content']);
                        }
                    }
                }
            }

            //Zend_Debug::dump($pagoEnvio);
            if ($pagoEnvio) {
                foreach ($pagoEnvio as $ePago) {
                    if (strlen($ePago["enviopago_content"]) > 0) {
                        if (!$validationMapper->checkForSendPayFile($ePago['archivo_nombre'])) {
                            $validationMapper->addNewSendPayFile($ePago['archivo_nombre'], $ePago['archivo_num'], $ePago['archivo_num_juliano'], $ePago['pedimento'], $ePago['cve_doc'], $ePago['enviopago_content']);
                        }
                    }
                }
            }

            if ($pagoRespuesta) {
                foreach ($pagoRespuesta as $rPago) {
                    if (strlen($rPago["respuesta_content"]) > 0) {
                        if (isset($rPago['pedimento']) && strlen($rPago['rfc']) > 6) {
                            if (!$validationMapper->checkForResponsePayFile($rPago['archivo_nombre'])) {
                                $validationMapper->addNewResponsePayFile($rPago['archivo_nombre'], $rPago['archivo_num'], $rPago['archivo_num_juliano'], $rPago['pedimento'], $rPago['rfc'], $rPago['fecha'], $rPago['respuesta_content']);
                            }
                        }
                    }
                }
            }

            $m3 = $validationMapper->getAllM3DataNull('prevalidacionid');
            $m3s = $validationMapper->getAllM3DataNull('validacionid');

            if ($m3) {
                foreach ($m3 as $archivoM3):
                    $preId = $validationMapper->getFileIsIfExists('archivos_prevalidacion', 'archivo_num_juliano', $archivoM3['archivo_num_juliano']);
                    if ($preId) {
                        $validationMapper->updateM3ColumnId('prevalidacionid', $archivoM3['id'], $preId['id']);
                    }
                endforeach;
            }


            foreach ($m3s as $archivoM3):
                $valId = $validationMapper->getFileIsIfExists('archivos_respuesta', 'archivo_num', $archivoM3['archivo_num']);
                if ($valId) {
                    $validationMapper->updateM3ColumnId('validacionid', $archivoM3['id'], $valId['id']);
                }
            endforeach;

            $signedM3 = $validationMapper->getAllSignedM3();

            if ($signedM3) {
                foreach ($signedM3 as $pagado):
                    $validationMapper->updatePayM3($pagado['m3id'], $pagado['pedimento'], $pagado['envioid'], $pagado['respuestaid']);
                endforeach;
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function organizarCtasDeGatos($directorio) {
        try {
            error_reporting(0);

            ini_set('track_errors', '1');
            if (file_exists($this->_config->app->exporig . DIRECTORY_SEPARATOR . 'error_' . date('Y-m-d') . '.log')) {
                unlink($this->_config->app->exporig . DIRECTORY_SEPARATOR . 'error_' . date('Y-m-d') . '.log');
            }
            $writer = new Zend_Log_Writer_Stream($this->_config->app->exporig . DIRECTORY_SEPARATOR . 'error_' . date('Y-m-d') . '.log');
            $logger = new Zend_Log($writer);

            $sica = new OAQ_Sica();
            $dir = new DirectoryIterator($directorio);
            $ctas = array();
            foreach ($dir as $fileinfo) {
                if (!$fileinfo->isDot()) {
                    if (preg_match('/^SIGN_Factura_/i', $fileinfo->getFilename()) && preg_match('/.xml$/', $fileinfo->getFilename())) {
                        $numCta = str_replace(array('SIGN_Factura_', '.xml'), '', $fileinfo->getFilename());
                        $info = $sica->getInvoiceInfo($numCta);

                        //Zend_Debug::dump( strpos($info["Referencia"], '*') );
                        if (strpos($info["Referencia"], '*') != false && APPLICATION_ENV == 'production') {
                            $from = $directorio . DIRECTORY_SEPARATOR . $fileinfo->getFilename();
                            $fromPdf = $directorio . DIRECTORY_SEPARATOR . substr($fileinfo->getFilename(), 0, -4) . ".pdf";
                            $to = "C:\SICASQL\FacturacionElectronica\Cancelado" . DIRECTORY_SEPARATOR . $fileinfo->getFilename();
                            $toPdf = "C:\SICASQL\FacturacionElectronica\Cancelado" . DIRECTORY_SEPARATOR . substr($fileinfo->getFilename(), 0, -4) . ".pdf";
                            Zend_Debug::dump($info["Referencia"]);
                            continue;
                        }

                        if ($info) {
                            $ctas[] = array(
                                'filename' => $fileinfo->getFilename(),
                                'type' => 'xml',
                                'num' => $numCta,
                                'referencia' => $info['Referencia'],
                                'patente' => $info['Patente'],
                                'aduana' => $info['AduanaID'],
                                'year' => $info['Year'],
                                'path' => $this->_appconfig->getParam('expdest') . DIRECTORY_SEPARATOR . $info['Year'] . DIRECTORY_SEPARATOR . $info['Patente'] . DIRECTORY_SEPARATOR . $info['AduanaID'] . DIRECTORY_SEPARATOR . $info['Referencia'],
                                'moveto' => $this->_appconfig->getParam('expdest') . DIRECTORY_SEPARATOR . $info['Year'] . DIRECTORY_SEPARATOR . $info['Patente'] . DIRECTORY_SEPARATOR . $info['AduanaID'] . DIRECTORY_SEPARATOR . $info['Referencia'] . DIRECTORY_SEPARATOR . $fileinfo->getFilename(),
                            );

                            if (!file_exists($this->_appconfig->getParam('expdest') . DIRECTORY_SEPARATOR . $info['Year'])) {
                                mkdir($this->_appconfig->getParam('expdest') . DIRECTORY_SEPARATOR . $info['Year']);
                            }
                            if (!file_exists($this->_appconfig->getParam('expdest') . DIRECTORY_SEPARATOR . $info['Year'] . DIRECTORY_SEPARATOR . $info['Patente'])) {
                                mkdir($this->_appconfig->getParam('expdest') . DIRECTORY_SEPARATOR . $info['Year'] . DIRECTORY_SEPARATOR . $info['Patente']);
                            }
                            if (!file_exists($this->_appconfig->getParam('expdest') . DIRECTORY_SEPARATOR . $info['Year'] . DIRECTORY_SEPARATOR . $info['Patente'] . DIRECTORY_SEPARATOR . $info['AduanaID'])) {
                                mkdir($this->_appconfig->getParam('expdest') . DIRECTORY_SEPARATOR . $info['Year'] . DIRECTORY_SEPARATOR . $info['Patente'] . DIRECTORY_SEPARATOR . $info['AduanaID']);
                            }
                            if (!file_exists($this->_appconfig->getParam('expdest') . DIRECTORY_SEPARATOR . $info['Year'] . DIRECTORY_SEPARATOR . $info['Patente'] . DIRECTORY_SEPARATOR . $info['AduanaID'] . DIRECTORY_SEPARATOR . $info['Referencia'])) {
                                if (!mkdir($this->_appconfig->getParam('expdest') . DIRECTORY_SEPARATOR . $info['Year'] . DIRECTORY_SEPARATOR . $info['Patente'] . DIRECTORY_SEPARATOR . $info['AduanaID'] . DIRECTORY_SEPARATOR . $info['Referencia'])) {
                                    $tmp = $this->_appconfig->getParam('expdest') . DIRECTORY_SEPARATOR . $info['Year'] . DIRECTORY_SEPARATOR . $info['Patente'] . DIRECTORY_SEPARATOR . $info['AduanaID'] . DIRECTORY_SEPARATOR . $info['Referencia'];
                                    $logger->info("Error al crear directorio {$tmp}... " . $php_errormsg . "\n");
                                }
                            }
                        }
                    }
                    if (preg_match('/^SIGN_Factura_/i', $fileinfo->getFilename()) && preg_match('/.pdf$/', $fileinfo->getFilename())) {
                        $numCta = str_replace(array('SIGN_Factura_', '.pdf'), '', $fileinfo->getFilename());
                        $info = $sica->getInvoiceInfo((int) $numCta);
                        if ($info) {
                            $ctas[] = array(
                                'filename' => $fileinfo->getFilename(),
                                'type' => 'pdf',
                                'num' => $numCta,
                                'referencia' => $info['Referencia'],
                                'patente' => $info['Patente'],
                                'aduana' => $info['AduanaID'],
                                'year' => $info['Year'],
                                'path' => $this->_appconfig->getParam('expdest') . DIRECTORY_SEPARATOR . $info['Year'] . DIRECTORY_SEPARATOR . $info['Patente'] . DIRECTORY_SEPARATOR . $info['AduanaID'] . DIRECTORY_SEPARATOR . $info['Referencia'],
                                'moveto' => $this->_appconfig->getParam('expdest') . DIRECTORY_SEPARATOR . $info['Year'] . DIRECTORY_SEPARATOR . $info['Patente'] . DIRECTORY_SEPARATOR . $info['AduanaID'] . DIRECTORY_SEPARATOR . $info['Referencia'] . DIRECTORY_SEPARATOR . $fileinfo->getFilename(),
                            );

                            if (!file_exists($this->_appconfig->getParam('expdest') . DIRECTORY_SEPARATOR . $info['Year'])) {
                                mkdir($this->_appconfig->getParam('expdest') . DIRECTORY_SEPARATOR . $info['Year']);
                            }
                            if (!file_exists($this->_appconfig->getParam('expdest') . DIRECTORY_SEPARATOR . $info['Year'] . DIRECTORY_SEPARATOR . $info['Patente'])) {
                                mkdir($this->_appconfig->getParam('expdest') . DIRECTORY_SEPARATOR . $info['Year'] . DIRECTORY_SEPARATOR . $info['Patente']);
                            }
                            if (!file_exists($this->_appconfig->getParam('expdest') . DIRECTORY_SEPARATOR . $info['Year'] . DIRECTORY_SEPARATOR . $info['Patente'] . DIRECTORY_SEPARATOR . $info['AduanaID'])) {
                                mkdir($this->_appconfig->getParam('expdest') . DIRECTORY_SEPARATOR . $info['Year'] . DIRECTORY_SEPARATOR . $info['Patente'] . DIRECTORY_SEPARATOR . $info['AduanaID']);
                            }
                            if (!file_exists($this->_appconfig->getParam('expdest') . DIRECTORY_SEPARATOR . $info['Year'] . DIRECTORY_SEPARATOR . $info['Patente'] . DIRECTORY_SEPARATOR . $info['AduanaID'] . DIRECTORY_SEPARATOR . $info['Referencia'])) {
                                if (!mkdir($this->_appconfig->getParam('expdest') . DIRECTORY_SEPARATOR . $info['Year'] . DIRECTORY_SEPARATOR . $info['Patente'] . DIRECTORY_SEPARATOR . $info['AduanaID'] . DIRECTORY_SEPARATOR . $info['Referencia'])) {
                                    $tmp = $this->_appconfig->getParam('expdest') . DIRECTORY_SEPARATOR . $info['Year'] . DIRECTORY_SEPARATOR . $info['Patente'] . DIRECTORY_SEPARATOR . $info['AduanaID'] . DIRECTORY_SEPARATOR . $info['Referencia'];
                                    $logger->info("Error al crear directorio {$tmp}... " . $php_errormsg . "\n");
                                }
                            }
                        }
                    }
                }
            }

            foreach ($ctas as $item) {
                $fromFile = $directorio . DIRECTORY_SEPARATOR . $item['filename'];
                $histFile = $this->_config->app->ctahist . DIRECTORY_SEPARATOR . $item['filename'];
                $toFile = $item['moveto'];
                if (!file_exists($toFile)) {
                    if (!copy($fromFile, $toFile)) {
                        $logger->info("Error al copiar archivo {$fromFile}... " . $php_errormsg . "\n");
                        continue;
                    } else {
                    }
                }
                if (!file_exists($histFile)) {
                    if (!copy($fromFile, $histFile)) {
                        $logger->info("Error al copiar archivo {$fromFile}... " . $php_errormsg . "\n");
                        continue;
                    } else {
                    }
                }
                if (file_exists($fromFile) && file_exists($histFile) && file_exists($toFile)) {
                }
            }
            return $ctas;
        } catch (Exception $e) {
            echo '<b>Exception found while scanning dir</b>:' . $e->getMessage();
            die();
        }
    }

    public function checkCache($item) {
        try {
            if (($cacheData = $this->_queryCache->load($item))) {
                return $cacheData;
            }
            return null;
        } catch (Exception $e) {
            echo "<b>Exception found on " . __METHOD__ . "</b>: " . $e->getMessage();
            die();
        }
    }

    public function saveCache($item, $data) {
        try {
            $this->_queryCache->save($data, $item);
        } catch (Exception $e) {
            echo "<b>Exception found on " . __METHOD__ . "</b>: " . $e->getMessage();
            die();
        }
    }

    public function deleteCache($item) {
        try {
            $this->_queryCache->remove($item);
        } catch (Exception $e) {
            echo "<b>Exception found on " . __METHOD__ . "</b>: " . $e->getMessage();
            die();
        }
    }

    public function fileHeader($filename) {
        if (preg_match('/.pdf$/i', $filename)) {
            return $this->appHeader('application/pdf');
        }
        if (preg_match('/.xls$/i', $filename) || preg_match('/.xlsx$/i', $filename)) {
            return $this->appHeader('application/octet-stream');
        }
        if (preg_match('/.doc$/i', $filename) || preg_match('/.docx$/i', $filename)) {
            return $this->appHeader('application/octet-stream');
        }
        if (preg_match('/.xml$/i', $filename)) {
            return $this->appHeader('text/xml; charset=utf-8');
        }
        if (preg_match('/.zip$/i', $filename)) {
            return $this->appHeader('application/octet-stream');
        }
    }

    protected function appHeader($app) {
        return "Content-type: {$app}";
    }

    public function createZip($files = array(), $destination = '', $pdf = '0') {
        $zip = new ZipArchive();
        if ($zip->open($destination, ZIPARCHIVE::CREATE) !== TRUE) {
            return null;
        }
        foreach ($files as $file) {
            if (file_exists($file)) {
                $zip->addFile($file, basename($file));
            }
            if ($pdf == '1') {
                $pdfilename = str_replace('.xml', '.pdf', $file);
                if (file_exists($pdfilename)) {
                    $zip->addFile($pdfilename, basename($pdfilename));
                }
            }
        }
        $zip->close();
        return true;
    }

    public function createZipLinux($files = array(), $destination = '') {
        $zip = new ZipArchive();
        if ($zip->open($destination, ZIPARCHIVE::CREATE) !== TRUE) {
            return null;
        }
        foreach ($files as $file) {
            if (file_exists($file)) {
                $zip->addFile($file, basename($file));
            }
        }
        $zip->close();
        return true;
    }

    public function createZipFile($files = array(), $destination = '') {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        try {
            $zip = new ZipArchive();
            if ($zip->open($destination, ZIPARCHIVE::CREATE) !== TRUE) {
                return null;
            }
            foreach ($files as $file) {
                if (file_exists($file)) {
                    $zip->addFile($file, basename($file));
                }
            }
            $zip->close();
            return true;
        } catch (Exception $e) {
            echo "<b>Exception on " . __METHOD__ . "</b>" . $e->getMessage();
            die();
        }
    }

    public function createZipCofidi($file, $destination = '') {
        $zip = new ZipArchive();
        if ($zip->open($destination, ZIPARCHIVE::CREATE) !== TRUE) {
            return null;
        }
        $zip->addFile($file, basename($file));
        $zip->close();
        return true;
    }

    public function facturasTerminal($directory) {
        $dir = new DirectoryIterator($directory);
        $fact = array();
        foreach ($dir as $fileinfo) {
            if (!$fileinfo->isDot()) {
                if (preg_match('/.xml$/', $fileinfo->getFilename())) {

                    $clean = str_replace(array('ns2:', 'S:', 'wsse:', 'ns3:', 'wsu:'), '', file_get_contents($directory . DIRECTORY_SEPARATOR . $fileinfo->getFilename()));
                    $xmlClean = simplexml_load_string($clean);
                    unset($clean);
                    $array = @json_decode(@json_encode($xmlClean), 1);
                    //Zend_Debug::dump($array); die();

                    $concepts = array();
                    if (isset($array["Conceptos"]["Concepto"])) {
                        foreach ($array["Conceptos"]["Concepto"] as $con) {
                            $concepts[] = array(
                                'concepto' => $con["@attributes"]["cantidad"],
                                'descripcion' => $con["@attributes"]["descripcion"],
                                'valor_unitario' => $con["@attributes"]["valorUnitario"],
                                'concepto' => $con["@attributes"]["importe"],
                            );
                        }
                    }

                    $fact[] = array(
                        'folio' => $array["@attributes"]["folio"],
                        'fecha' => $array["@attributes"]["fecha"],
                        'rfc_cliente' => $array["Receptor"]["@attributes"]["rfc"],
                        'nom_cliente' => $array["Receptor"]["@attributes"]["nombre"],
                        'conceptos' => $concepts,
                        'subtotal' => $array["@attributes"]["subTotal"],
                        'descuento' => $array["@attributes"]["descuento"],
                        'total' => $array["@attributes"]["total"],
                    );

                    unset($array);
                }
            }
        }
        return $fact;
    }

    public function mes($mes) {
        switch ($mes) {
            case 1:
                return "Enero";
            case 2:
                return "Febrero";
            case 3:
                return "Marzo";
            case 4:
                return "Abril";
            case 5:
                return "Mayo";
            case 6:
                return "Junio";
            case 7:
                return "Julio";
            case 8:
                return "AgostoEnero";
            case 9:
                return "Septimbre";
            case 10:
                return "Octubre";
            case 11:
                return "Noviembre";
            case 12:
                return "Diciembre";
        }
    }

    /**
     * Translates a number to a short alhanumeric version
     *
     * Translated any number up to 9007199254740992
     * to a shorter version in letters e.g.:
     * 9007199254740989 --> PpQXn7COf
     *
     * specifiying the second argument true, it will
     * translate back e.g.:
     * PpQXn7COf --> 9007199254740989
     *
     * this function is based on any2dec && dec2any by
     * fragmer[at]mail[dot]ru
     * see: http://nl3.php.net/manual/en/function.base-convert.php#52450
     *
     * If you want the alphaID to be at least 3 letter long, use the
     * $pad_up = 3 argument
     *
     * In most cases this is better than totally random ID generators
     * because this can easily avoid duplicate ID's.
     * For example if you correlate the alpha ID to an auto incrementing ID
     * in your database, you're done.
     *
     * The reverse is done because it makes it slightly more cryptic,
     * but it also makes it easier to spread lots of IDs in different
     * directories on your filesystem. Example:
     * $part1 = substr($alpha_id,0,1);
     * $part2 = substr($alpha_id,1,1);
     * $part3 = substr($alpha_id,2,strlen($alpha_id));
     * $destindir = "/".$part1."/".$part2."/".$part3;
     * // by reversing, directories are more evenly spread out. The
     * // first 26 directories already occupy 26 main levels
     *
     * more info on limitation:
     * - http://blade.nagaokaut.ac.jp/cgi-bin/scat.rb/ruby/ruby-talk/165372
     *
     * if you really need this for bigger numbers you probably have to look
     * at things like: http://theserverpages.com/php/manual/en/ref.bc.php
     * or: http://theserverpages.com/php/manual/en/ref.gmp.php
     * but I haven't really dugg into this. If you have more info on those
     * matters feel free to leave a comment.
     *
     * The following code block can be utilized by PEAR's Testing_DocTest
     * <code>
     * // Input //
     * $number_in = 2188847690240;
     * $alpha_in  = "SpQXn7Cb";
     *
     * // Execute //
     * $alpha_out  = alphaID($number_in, false, 8);
     * $number_out = alphaID($alpha_in, true, 8);
     *
     * if ($number_in != $number_out) {
     *    echo "Conversion failure, ".$alpha_in." returns ".$number_out." instead of the ";
     *    echo "desired: ".$number_in."\n";
     * }
     * if ($alpha_in != $alpha_out) {
     *    echo "Conversion failure, ".$number_in." returns ".$alpha_out." instead of the ";
     *    echo "desired: ".$alpha_in."\n";
     * }
     *
     * // Show //
     * echo $number_out." => ".$alpha_out."\n";
     * echo $alpha_in." => ".$number_out."\n";
     * echo alphaID(238328, false)." => ".alphaID(alphaID(238328, false), true)."\n";
     *
     * // expects:
     * // 2188847690240 => SpQXn7Cb
     * // SpQXn7Cb => 2188847690240
     * // aaab => 238328
     *
     * </code>
     *
     * @author   Kevin van Zonneveld <kevin@vanzonneveld.net>
     * @author   Simon Franz
     * @author   Deadfish
     * @copyright 2008 Kevin van Zonneveld (http://kevin.vanzonneveld.net)
     * @license   http://www.opensource.org/licenses/bsd-license.php New BSD Licence
     * @version   SVN: Release: $Id: alphaID.inc.php 344 2009-06-10 17:43:59Z kevin $
     * @link   http://kevin.vanzonneveld.net/
     *
     * @param mixed   $in      String or long input to translate
     * @param boolean $to_num  Reverses translation when true
     * @param mixed   $pad_up  Number or boolean padds the result up to a specified length
     * @param string  $passKey Supplying a password makes it harder to calculate the original ID
     *
     * @return mixed string or long
     */
    public function alphaID($in, $to_num = false, $pad_up = false, $passKey = null) {
        $index = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        if ($passKey !== null) {
            // Although this function's purpose is to just make the
            // ID short - and not so much secure,
            // with this patch by Simon Franz (http://blog.snaky.org/)
            // you can optionally supply a password to make it harder
            // to calculate the corresponding numeric ID
            for ($n = 0; $n < strlen($index); $n++) {
                $i[] = substr($index, $n, 1);
            }

            $passhash = hash('sha256', $passKey);
            $passhash = (strlen($passhash) < strlen($index)) ? hash('sha512', $passKey) : $passhash;

            for ($n = 0; $n < strlen($index); $n++) {
                $p[] = substr($passhash, $n, 1);
            }

            array_multisort($p, SORT_DESC, $i);
            $index = implode($i);
        }

        $base = strlen($index);

        if ($to_num) {
            // Digital number  <<--  alphabet letter code
            $in = strrev($in);
            $out = 0;
            $len = strlen($in) - 1;
            for ($t = 0; $t <= $len; $t++) {
                $bcpow = bcpow($base, $len - $t);
                $out = $out + strpos($index, substr($in, $t, 1)) * $bcpow;
            }

            if (is_numeric($pad_up)) {
                $pad_up--;
                if ($pad_up > 0) {
                    $out -= pow($base, $pad_up);
                }
            }
            $out = sprintf('%F', $out);
            $out = substr($out, 0, strpos($out, '.'));
        } else {
            // Digital number  -->>  alphabet letter code
            if (is_numeric($pad_up)) {
                $pad_up--;
                if ($pad_up > 0) {
                    $in += pow($base, $pad_up);
                }
            }

            $out = "";
            for ($t = floor(log($in, $base)); $t >= 0; $t--) {
                $bcp = bcpow($base, $t);
                $a = floor($in / $bcp) % $base;
                $out = $out . substr($index, $a, 1);
                $in = $in - ($a * $bcp);
            }
            $out = strrev($out); // reverse
        }

        return $out;
    }

    /**
     * Translates a number to a short alhanumeric version
     *
     * Translated any number up to 9007199254740992
     * to a shorter version in letters e.g.:
     * 9007199254740989 --> PpQXn7COf
     *
     * specifiying the second argument true, it will
     * translate back e.g.:
     * PpQXn7COf --> 9007199254740989
     *
     * this function is based on any2dec && dec2any by
     * fragmer[at]mail[dot]ru
     * see: http://nl3.php.net/manual/en/function.base-convert.php#52450
     *
     * If you want the alphaID to be at least 3 letter long, use the
     * $pad_up = 3 argument
     *
     * In most cases this is better than totally random ID generators
     * because this can easily avoid duplicate ID's.
     * For example if you correlate the alpha ID to an auto incrementing ID
     * in your database, you're done.
     *
     * The reverse is done because it makes it slightly more cryptic,
     * but it also makes it easier to spread lots of IDs in different
     * directories on your filesystem. Example:
     * $part1 = substr($alpha_id,0,1);
     * $part2 = substr($alpha_id,1,1);
     * $part3 = substr($alpha_id,2,strlen($alpha_id));
     * $destindir = "/".$part1."/".$part2."/".$part3;
     * // by reversing, directories are more evenly spread out. The
     * // first 26 directories already occupy 26 main levels
     *
     * more info on limitation:
     * - http://blade.nagaokaut.ac.jp/cgi-bin/scat.rb/ruby/ruby-talk/165372
     *
     * if you really need this for bigger numbers you probably have to look
     * at things like: http://theserverpages.com/php/manual/en/ref.bc.php
     * or: http://theserverpages.com/php/manual/en/ref.gmp.php
     * but I haven't really dugg into this. If you have more info on those
     * matters feel free to leave a comment.
     *
     * The following code block can be utilized by PEAR's Testing_DocTest
     * <code>
     * // Input //
     * $number_in = 2188847690240;
     * $alpha_in  = "SpQXn7Cb";
     *
     * // Execute //
     * $alpha_out  = alphaID($number_in, false, 8);
     * $number_out = alphaID($alpha_in, true, 8);
     *
     * if ($number_in != $number_out) {
     *   echo "Conversion failure, ".$alpha_in." returns ".$number_out." instead of the ";
     *   echo "desired: ".$number_in."\n";
     * }
     * if ($alpha_in != $alpha_out) {
     *   echo "Conversion failure, ".$number_in." returns ".$alpha_out." instead of the ";
     *   echo "desired: ".$alpha_in."\n";
     * }
     *
     * // Show //
     * echo $number_out." => ".$alpha_out."\n";
     * echo $alpha_in." => ".$number_out."\n";
     * echo alphaID(238328, false)." => ".alphaID(alphaID(238328, false), true)."\n";
     *
     * // expects:
     * // 2188847690240 => SpQXn7Cb
     * // SpQXn7Cb => 2188847690240
     * // aaab => 238328
     *
     * </code>
     *
     * @author  Kevin van Zonneveld &lt;kevin@vanzonneveld.net>
     * @author  Simon Franz
     * @author  Deadfish
     * @author  SK83RJOSH
     * @copyright 2008 Kevin van Zonneveld (http://kevin.vanzonneveld.net)
     * @license   http://www.opensource.org/licenses/bsd-license.php New BSD Licence
     * @version   SVN: Release: $Id: alphaID.inc.php 344 2009-06-10 17:43:59Z kevin $
     * @link    http://kevin.vanzonneveld.net/
     *
     * @param mixed   $in   String or long input to translate
     * @param boolean $to_num  Reverses translation when true
     * @param mixed   $pad_up  Number or boolean padds the result up to a specified length
     * @param string  $pass_key Supplying a password makes it harder to calculate the original ID
     *
     * @return mixed string or long
     */
    public function alphaID2($in, $to_num = false, $pad_up = false, $pass_key = null) {
        $out = '';
        $index = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $base = strlen($index);

        if ($pass_key !== null) {
            // Although this function's purpose is to just make the
            // ID short - and not so much secure,
            // with this patch by Simon Franz (http://blog.snaky.org/)
            // you can optionally supply a password to make it harder
            // to calculate the corresponding numeric ID

            for ($n = 0; $n < strlen($index); $n++) {
                $i[] = substr($index, $n, 1);
            }

            $pass_hash = hash('sha256', $pass_key);
            $pass_hash = (strlen($pass_hash) < strlen($index) ? hash('sha512', $pass_key) : $pass_hash);

            for ($n = 0; $n < strlen($index); $n++) {
                $p[] = substr($pass_hash, $n, 1);
            }

            array_multisort($p, SORT_DESC, $i);
            $index = implode($i);
        }

        if ($to_num) {
            // Digital number  <<--  alphabet letter code
            $len = strlen($in) - 1;

            for ($t = $len; $t >= 0; $t--) {
                $bcp = bcpow($base, $len - $t);
                $out = $out + strpos($index, substr($in, $t, 1)) * $bcp;
            }

            if (is_numeric($pad_up)) {
                $pad_up--;

                if ($pad_up > 0) {
                    $out -= pow($base, $pad_up);
                }
            }
        } else {
            // Digital number  -->>  alphabet letter code
            if (is_numeric($pad_up)) {
                $pad_up--;

                if ($pad_up > 0) {
                    $in += pow($base, $pad_up);
                }
            }

            for ($t = ($in != 0 ? floor(log($in, $base)) : 0); $t >= 0; $t--) {
                $bcp = bcpow($base, $t);
                $a = floor($in / $bcp) % $base;
                $out = $out . substr($index, $a, 1);
                $in = $in - ($a * $bcp);
            }
        }

        return $out;
    }

    public function getUuid($key) {
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
        require_once 'UUID.php';
        return UUID::v5($this->_config->app->uuid, $key);
    }

    /**
     * Identa una cadena XML y la deja con mejor formato.
     * 
     * @param String $xml
     * @return String
     * */
    public function xmlIdent($xml) {
        try {
            $xmlDoc = new DOMDocument ();
            $xmlDoc->preserveWhiteSpace = false;
            $xmlDoc->formatOutput = true;
            $xmlDoc->loadXML($xml);
            return $xmlDoc->saveXML();
        } catch (Exception $e) {
            echo "<b>Exception on " . __METHOD__ . "</b>" . $e->getMessage();
            die();
        }
    }

    public function monedaVucem($value) {
        return '$ ' . number_format($value, 3, '.', ',');
    }

    public function numeroVucem($value) {
        return number_format($value, 3, '.', '');
    }

    public function tipoIdentificador($rfc, $pais) {
        // Tax ID/RFC/CURP : El tipo de indentificador no es v?lido. Valores permitidos [0-TAX_ID, 1-RFC, 2-CURP,3-SIN_TAX_ID]
        $regRfc = '/^[A-Z]{3,4}([0-9]{2})(1[0-2]|0[1-9])([0-3][0-9])([A-Z0-9]{3,4})$/';
        $regTaxId = '/^[0-9]{2,3}/';
        if ($pais == 'MEX' && preg_match($regRfc, str_replace(' ', '', $rfc))) {
            if (strlen($rfc) > 12) {
                return '2-CURP';
            }
            return '1-RFC';
        }
        if ($pais == 'MEX' && !preg_match($regRfc, str_replace(' ', '', $rfc))) {
            return '0-TAX_ID';
        }

        if ($pais != 'MEX' && $rfc != '') {
            return '0-TAX_ID';
        }
        if ($pais != 'MEX' && $rfc == '') {
            return '3-SIN_TAX_ID';
        }
    }

    public function identificadorDesc($iden) {
        switch ((int) $iden) {
            case 0:
                return '0-TAX_ID';
            case 1:
                return '1-RFC';
            case 2:
                return '2-CURP';
            case 3:
                return '3-SIN_TAX_ID';
            default:
                break;
        }
    }

    public function tipoMoneda($val) {
        switch ($val) {
            case 'MXP':
                return 'MXN';
                break;
            case 'STG':
                return 'GBP';
                break;
            default:
                return $val;
                break;
        }
    }

    public function deleteDir($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir . "/" . $object) == "dir")
                        rrmdir($dir . "/" . $object);
                    else
                        unlink($dir . "/" . $object);
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }

    public function saveFile($dir, $filename, $content) {
        if (!file_exists($dir . DIRECTORY_SEPARATOR . $filename)) {
            file_put_contents($dir . DIRECTORY_SEPARATOR . $filename, $content);
        }
    }

    public function loadFile() {
        
    }

    public function arrayKeys($arr, $param1 = null, $param2 = null, $param3 = null, $param4 = null) {
        if (isset($arr)) {
            if ($param1 && !$param2 && !$param3 && !$param4) {
                if (isset($arr[$param1]))
                    return trim($arr[$param1]);
                else
                    null;
            }
            if ($param1 && $param2 && !$param3 && !$param4) {
                if (isset($arr[$param1][$param2]))
                    return trim($arr[$param1][$param2]);
                else
                    null;
            }
            if ($param1 && $param2 && $param3 && !$param4) {
                if (isset($arr[$param1][$param2][$param3]))
                    return trim($arr[$param1][$param2][$param3]);
                else
                    null;
            }
            if ($param1 && $param2 && $param3 && $param4) {
                if (isset($arr[$param1][$param2][$param3][$param4]))
                    return trim($arr[$param1][$param2][$param3][$param4]);
                else
                    null;
            }
        }
    }

    public function stringInsideTags($string, $tagname) {
        $pattern = "/<$tagname\b[^>]*>(.*?)<\/$tagname>/is";
        preg_match_all($pattern, $string, $matches);
        if (!empty($matches[1]))
            return $matches[1];
        return array();
    }

    public function crearExpedienteDir($baseDir, $year, $patente, $aduana, $referencia) {
        $fullDir = $baseDir . DIRECTORY_SEPARATOR . $patente . DIRECTORY_SEPARATOR . $aduana . DIRECTORY_SEPARATOR . $referencia;
        if (!file_exists($fullDir)) {
            if (!file_exists($baseDir . DIRECTORY_SEPARATOR . $patente)) {
                try {
                    mkdir($baseDir . DIRECTORY_SEPARATOR . $patente);
                } catch (ErrorException $e) {
                    return null;
                }
            }
            if (!file_exists($baseDir . DIRECTORY_SEPARATOR . $patente . DIRECTORY_SEPARATOR . $aduana)) {
                try {
                    mkdir($baseDir . DIRECTORY_SEPARATOR . $patente . DIRECTORY_SEPARATOR . $aduana);
                } catch (ErrorException $e) {
                    return null;
                }
            }
            if (!file_exists($fullDir)) {
                try {
                    mkdir($fullDir);
                } catch (ErrorException $e) {
                    return null;
                }
            }
            return $fullDir;
        } else {
            return $fullDir;
        }
    }

    public function crearDirectorio($patente, $aduana, $referencia) {
        if (APPLICATION_ENV == 'production') {
            $base_dir = '/home/samba-share/expedientes';
        } else {
            $base_dir = 'D:\\xampp\\tmp\\expedientes';            
        }
        $folder = $base_dir . DIRECTORY_SEPARATOR . $patente;
        if (!file_exists($base_dir . DIRECTORY_SEPARATOR . $patente)) {
            mkdir($base_dir . DIRECTORY_SEPARATOR . $patente);
        }
        if (!file_exists($base_dir . DIRECTORY_SEPARATOR . $patente . DIRECTORY_SEPARATOR . $aduana)) {
            mkdir($base_dir . DIRECTORY_SEPARATOR . $patente . DIRECTORY_SEPARATOR . $aduana);
        }
        if (!file_exists($base_dir . DIRECTORY_SEPARATOR . $patente . DIRECTORY_SEPARATOR . $aduana . DIRECTORY_SEPARATOR . $referencia)) {
            mkdir($base_dir . DIRECTORY_SEPARATOR . $patente . DIRECTORY_SEPARATOR . $aduana . DIRECTORY_SEPARATOR . $referencia);
        }
        $folder = $base_dir . DIRECTORY_SEPARATOR . $patente . DIRECTORY_SEPARATOR . $aduana . DIRECTORY_SEPARATOR . $referencia;
        if (file_exists($folder)) {
            return $folder;
        } else {
            return false;
        }
    }

    public function createNewDir($newSubdirectory) {
        $baseDir = (isset($this->_baseDir)) ? $this->_baseDir : "/home/samba-share/expedientes";
        if (!file_exists($baseDir . DIRECTORY_SEPARATOR . $newSubdirectory)) {
            if (is_readable($baseDir)) {
                mkdir($baseDir . DIRECTORY_SEPARATOR . $newSubdirectory, 0777, true);
            }
            if (file_exists($baseDir . DIRECTORY_SEPARATOR . $newSubdirectory)) {
                return $baseDir . DIRECTORY_SEPARATOR . $newSubdirectory;
            }
        } else {
            return $baseDir . DIRECTORY_SEPARATOR . $newSubdirectory;
        }
        return false;
    }

    public function createReferenceDir($basedir, $patente, $aduana, $referencia) {
        if (!file_exists($basedir)) {
            mkdir($basedir, 0777, true);
        }
        if (!file_exists($basedir . DIRECTORY_SEPARATOR . $patente)) {
            mkdir($basedir . DIRECTORY_SEPARATOR . $patente);
        }
        if (!file_exists($basedir . DIRECTORY_SEPARATOR . $patente . DIRECTORY_SEPARATOR . $aduana)) {
            mkdir($basedir . DIRECTORY_SEPARATOR . $patente . DIRECTORY_SEPARATOR . $aduana);
        }
        if (!file_exists($basedir . DIRECTORY_SEPARATOR . $patente . DIRECTORY_SEPARATOR . $aduana . DIRECTORY_SEPARATOR . $referencia)) {
            mkdir($basedir . DIRECTORY_SEPARATOR . $patente . DIRECTORY_SEPARATOR . $aduana . DIRECTORY_SEPARATOR . $referencia);
        }
        if (file_exists($basedir . DIRECTORY_SEPARATOR . $patente . DIRECTORY_SEPARATOR . $aduana . DIRECTORY_SEPARATOR . $referencia)) {
            return $basedir . DIRECTORY_SEPARATOR . $patente . DIRECTORY_SEPARATOR . $aduana . DIRECTORY_SEPARATOR . $referencia;
        } else {
            return false;
        }
    }
    
    public function nuevoDirectorioExpediente($patente, $aduana, $referencia) {
        $baseDir = (isset($this->_baseDir)) ? $this->_baseDir : "/home/samba-share/expedientes";
        if (!file_exists($baseDir)) {
            mkdir($baseDir, 0777, true);
        }
        if (file_exists($baseDir)) {
            if (is_readable($baseDir) && is_writable($baseDir)) {
                $ndir = $baseDir . DIRECTORY_SEPARATOR . $patente . DIRECTORY_SEPARATOR . $aduana . DIRECTORY_SEPARATOR . $this->_replace($referencia);
                if (!file_exists($ndir)) {
                    mkdir($ndir, 0777, true);
                    if (file_exists($ndir)) {
                        return $ndir;                        
                    } else {
                        throw new Exception(__METHOD__ . "Unable to create directory: {$ndir}");                        
                    }
                } else {
                    return $ndir;
                }
            } else {
                throw new Exception(__METHOD__ . "Base directory not readable or writable: {$baseDir}");
            }
        } else {
            throw new Exception(__METHOD__ . "Base directory doesn't exists: {$baseDir}");
        }
    }
    
    public function nuevoDirectorio($base, $patente, $aduana, $referencia) {
        if (!file_exists($base)) {
            mkdir($base, 0777, true);
        }
        if (file_exists($base)) {
            if (is_readable($base) && is_writable($base)) {
                $ndir = $base . DIRECTORY_SEPARATOR . $patente . DIRECTORY_SEPARATOR . $aduana . DIRECTORY_SEPARATOR . $this->_replace($referencia);
                if (!file_exists($ndir)) {
                    mkdir($ndir, 0777, true);
                    if (file_exists($ndir)) {
                        return $ndir;                        
                    } else {
                        throw new Exception(__METHOD__ . "Unable to create directory: {$ndir}");                        
                    }
                } else {
                    return $ndir;
                }
            } else {
                throw new Exception(__METHOD__ . "Base directory not readable or writable: {$base}");
            }
        } else {
            throw new Exception(__METHOD__ . "Base directory doesn't exists: {$base}");
        }
    }
    
    public function limpiarNombreReferencia($referencia) {
        return $this->_replace($referencia);
    }

    public function replace($string) {
        return trim(preg_replace(array("/\s/", "/\.[\.]+/", "/[^\w_\.\-]/"), array("-", ".", "_"), $this->_specialCharacters($string)));
    }
    
    protected function _replace($string) {
        return trim(preg_replace(array("/\s/", "/\.[\.]+/", "/[^\w_\.\-]/"), array("-", ".", "_"), $this->_specialCharacters($string)));
    }

    protected function _specialCharacters($string) {
        $array = array('Š' => 'S', 'š' => 's', 'Ž' => 'Z', 'ž' => 'z', 'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Þ' => 'B', 'ß' => 'Ss', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'a', 'ç' => 'c', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ý' => 'y', 'þ' => 'b', 'ÿ' => 'y');
        return strtr($string, $array);
    }

    public function trimArray($value) {
        $pre = str_replace(array("\r\n", "\r", "\n", "\t"), ' ', trim(preg_replace('/[\x00-\x1F\x7f-\xFF]/', '', $value)));
        return trim(preg_replace('/\t/', '', preg_replace('/\s+/', ' ', $pre)));
    }

    public function trimUc($value) {
        $pre = str_replace(array("\r\n", "\r", "\n", "\t"), ' ', strtoupper(trim($value)));
        return trim(preg_replace('/\t/', '', preg_replace('/\s+/', ' ', $pre)));
    }

    public function trimUcUtf8($value) {
        $pre = str_replace(array("\r\n", "\r", "\n", "\t"), ' ', strtoupper(trim(utf8_encode($value))));
        return utf8_decode(trim(preg_replace('/\t/', '', preg_replace('/\s+/', ' ', $pre))));
    }

    public function trimUpper($value) {
        $pre = str_replace(array("\r\n", "\r", "\n", "\t"), ' ', trim(strtoupper($value)));
        return trim(preg_replace('/\t/', '', preg_replace('/\s+/', ' ', $pre)));
    }

    public function filterInput($value) {
        $pre = str_replace(array("\r\n", "\r", "\n", "\t"), ' ', trim(strtoupper($value)));
        return trim(preg_replace('/\t/', '', preg_replace('/\s+/', ' ', $pre)));
    }

    public function formatURL($source_file, $space = null) {
        $search[] = " ";
        $search[] = ".";
        $search[] = "&";
        $search[] = "$";
        $search[] = ",";
        $search[] = "!";
        $search[] = "@";
        $search[] = "#";
        $search[] = "^";
        $search[] = "(";
        $search[] = ")";
        $search[] = "+";
        $search[] = "=";
        $search[] = "[";
        $search[] = "]";
        $search[] = "ñ";
        $search[] = "Ñ";
        $search[] = '[aâàá]';
        $search[] = '[eèêé]';
        $search[] = '[iìí]';
        $search[] = '[oôòó]';
        $search[] = '[uûùú]';

        $replace[] = (isset($space)) ? " " : "_";
        $replace[] = "";
        $replace[] = "and";
        $replace[] = "S";
        $replace[] = "_";
        $replace[] = "";
        $replace[] = "";
        $replace[] = "";
        $replace[] = "";
        $replace[] = "";
        $replace[] = "";
        $replace[] = "";
        $replace[] = "";
        $replace[] = "";
        $replace[] = "";
        $replace[] = "n";
        $replace[] = "N";
        $replace[] = "a";
        $replace[] = "e";
        $replace[] = "i";
        $replace[] = "o";
        $replace[] = "u";
        $basic = str_replace($search, $replace, $source_file);
        $search = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ');
        $replace = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o');
        $advanced = str_replace($search, $replace, $basic);
        return strtoupper(str_replace(array(' ', '&', '\r\n', '\n', '+', ',', '//'), '', $advanced));
    }

    public function formatFilename($source_file, $space = null) {
        $filename = substr($source_file, 0, -4);
        $ext = pathinfo($source_file, PATHINFO_EXTENSION);
        $search[] = " ";
        $search[] = ".";
        $search[] = "&";
        $search[] = "$";
        $search[] = ",";
        $search[] = "!";
        $search[] = "@";
        $search[] = "#";
        $search[] = "^";
        $search[] = "(";
        $search[] = ")";
        $search[] = "+";
        $search[] = "=";
        $search[] = "[";
        $search[] = "]";
        $search[] = "ñ";
        $search[] = "Ñ";
        $search[] = '[aâàá]';
        $search[] = '[eèêé]';
        $search[] = '[iìí]';
        $search[] = '[oôòó]';
        $search[] = '[uûùú]';
        $search[] = '°';
        $search[] = '´';
        $search[] = 'ª';
        $search[] = ';';

        $replace[] = "_";
        $replace[] = "";
        $replace[] = "and";
        $replace[] = "S";
        $replace[] = "_";
        $replace[] = "";
        $replace[] = "";
        $replace[] = "";
        $replace[] = "";
        $replace[] = "";
        $replace[] = "";
        $replace[] = "";
        $replace[] = "";
        $replace[] = "";
        $replace[] = "";
        $replace[] = "n";
        $replace[] = "N";
        $replace[] = "a";
        $replace[] = "e";
        $replace[] = "i";
        $replace[] = "o";
        $replace[] = "u";
        $replace[] = "";
        $replace[] = "";
        $replace[] = "a";
        $replace[] = "";
        $basic = str_replace($search, $replace, $filename);
        $search = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ');
        $replace = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o');
        $advanced = str_replace($search, $replace, $basic);
        $pre = strtoupper(str_replace(array('&', '\r\n', '\n', '+', ',', '//'), '', $advanced)) . '.' . strtolower($ext);
        return preg_replace('/\//', '_', preg_replace('/_+/', '_', (preg_replace('/\s+/', '_', $pre))));
    }

    public function stripAccents($string) {
        if (!preg_match('/[\x80-\xff]/', $string))
            return $string;
        $chars = array(
            // Decompositions for Latin-1 Supplement
            chr(195) . chr(128) => 'A', chr(195) . chr(129) => 'A',
            chr(195) . chr(130) => 'A', chr(195) . chr(131) => 'A',
            chr(195) . chr(132) => 'A', chr(195) . chr(133) => 'A',
            chr(195) . chr(135) => 'C', chr(195) . chr(136) => 'E',
            chr(195) . chr(137) => 'E', chr(195) . chr(138) => 'E',
            chr(195) . chr(139) => 'E', chr(195) . chr(140) => 'I',
            chr(195) . chr(141) => 'I', chr(195) . chr(142) => 'I',
            chr(195) . chr(143) => 'I', chr(195) . chr(145) => 'N',
            chr(195) . chr(146) => 'O', chr(195) . chr(147) => 'O',
            chr(195) . chr(148) => 'O', chr(195) . chr(149) => 'O',
            chr(195) . chr(150) => 'O', chr(195) . chr(153) => 'U',
            chr(195) . chr(154) => 'U', chr(195) . chr(155) => 'U',
            chr(195) . chr(156) => 'U', chr(195) . chr(157) => 'Y',
            chr(195) . chr(159) => 's', chr(195) . chr(160) => 'a',
            chr(195) . chr(161) => 'a', chr(195) . chr(162) => 'a',
            chr(195) . chr(163) => 'a', chr(195) . chr(164) => 'a',
            chr(195) . chr(165) => 'a', chr(195) . chr(167) => 'c',
            chr(195) . chr(168) => 'e', chr(195) . chr(169) => 'e',
            chr(195) . chr(170) => 'e', chr(195) . chr(171) => 'e',
            chr(195) . chr(172) => 'i', chr(195) . chr(173) => 'i',
            chr(195) . chr(174) => 'i', chr(195) . chr(175) => 'i',
            chr(195) . chr(177) => 'n', chr(195) . chr(178) => 'o',
            chr(195) . chr(179) => 'o', chr(195) . chr(180) => 'o',
            chr(195) . chr(181) => 'o', chr(195) . chr(182) => 'o',
            chr(195) . chr(182) => 'o', chr(195) . chr(185) => 'u',
            chr(195) . chr(186) => 'u', chr(195) . chr(187) => 'u',
            chr(195) . chr(188) => 'u', chr(195) . chr(189) => 'y',
            chr(195) . chr(191) => 'y',
            // Decompositions for Latin Extended-A
            chr(196) . chr(128) => 'A', chr(196) . chr(129) => 'a',
            chr(196) . chr(130) => 'A', chr(196) . chr(131) => 'a',
            chr(196) . chr(132) => 'A', chr(196) . chr(133) => 'a',
            chr(196) . chr(134) => 'C', chr(196) . chr(135) => 'c',
            chr(196) . chr(136) => 'C', chr(196) . chr(137) => 'c',
            chr(196) . chr(138) => 'C', chr(196) . chr(139) => 'c',
            chr(196) . chr(140) => 'C', chr(196) . chr(141) => 'c',
            chr(196) . chr(142) => 'D', chr(196) . chr(143) => 'd',
            chr(196) . chr(144) => 'D', chr(196) . chr(145) => 'd',
            chr(196) . chr(146) => 'E', chr(196) . chr(147) => 'e',
            chr(196) . chr(148) => 'E', chr(196) . chr(149) => 'e',
            chr(196) . chr(150) => 'E', chr(196) . chr(151) => 'e',
            chr(196) . chr(152) => 'E', chr(196) . chr(153) => 'e',
            chr(196) . chr(154) => 'E', chr(196) . chr(155) => 'e',
            chr(196) . chr(156) => 'G', chr(196) . chr(157) => 'g',
            chr(196) . chr(158) => 'G', chr(196) . chr(159) => 'g',
            chr(196) . chr(160) => 'G', chr(196) . chr(161) => 'g',
            chr(196) . chr(162) => 'G', chr(196) . chr(163) => 'g',
            chr(196) . chr(164) => 'H', chr(196) . chr(165) => 'h',
            chr(196) . chr(166) => 'H', chr(196) . chr(167) => 'h',
            chr(196) . chr(168) => 'I', chr(196) . chr(169) => 'i',
            chr(196) . chr(170) => 'I', chr(196) . chr(171) => 'i',
            chr(196) . chr(172) => 'I', chr(196) . chr(173) => 'i',
            chr(196) . chr(174) => 'I', chr(196) . chr(175) => 'i',
            chr(196) . chr(176) => 'I', chr(196) . chr(177) => 'i',
            chr(196) . chr(178) => 'IJ', chr(196) . chr(179) => 'ij',
            chr(196) . chr(180) => 'J', chr(196) . chr(181) => 'j',
            chr(196) . chr(182) => 'K', chr(196) . chr(183) => 'k',
            chr(196) . chr(184) => 'k', chr(196) . chr(185) => 'L',
            chr(196) . chr(186) => 'l', chr(196) . chr(187) => 'L',
            chr(196) . chr(188) => 'l', chr(196) . chr(189) => 'L',
            chr(196) . chr(190) => 'l', chr(196) . chr(191) => 'L',
            chr(197) . chr(128) => 'l', chr(197) . chr(129) => 'L',
            chr(197) . chr(130) => 'l', chr(197) . chr(131) => 'N',
            chr(197) . chr(132) => 'n', chr(197) . chr(133) => 'N',
            chr(197) . chr(134) => 'n', chr(197) . chr(135) => 'N',
            chr(197) . chr(136) => 'n', chr(197) . chr(137) => 'N',
            chr(197) . chr(138) => 'n', chr(197) . chr(139) => 'N',
            chr(197) . chr(140) => 'O', chr(197) . chr(141) => 'o',
            chr(197) . chr(142) => 'O', chr(197) . chr(143) => 'o',
            chr(197) . chr(144) => 'O', chr(197) . chr(145) => 'o',
            chr(197) . chr(146) => 'OE', chr(197) . chr(147) => 'oe',
            chr(197) . chr(148) => 'R', chr(197) . chr(149) => 'r',
            chr(197) . chr(150) => 'R', chr(197) . chr(151) => 'r',
            chr(197) . chr(152) => 'R', chr(197) . chr(153) => 'r',
            chr(197) . chr(154) => 'S', chr(197) . chr(155) => 's',
            chr(197) . chr(156) => 'S', chr(197) . chr(157) => 's',
            chr(197) . chr(158) => 'S', chr(197) . chr(159) => 's',
            chr(197) . chr(160) => 'S', chr(197) . chr(161) => 's',
            chr(197) . chr(162) => 'T', chr(197) . chr(163) => 't',
            chr(197) . chr(164) => 'T', chr(197) . chr(165) => 't',
            chr(197) . chr(166) => 'T', chr(197) . chr(167) => 't',
            chr(197) . chr(168) => 'U', chr(197) . chr(169) => 'u',
            chr(197) . chr(170) => 'U', chr(197) . chr(171) => 'u',
            chr(197) . chr(172) => 'U', chr(197) . chr(173) => 'u',
            chr(197) . chr(174) => 'U', chr(197) . chr(175) => 'u',
            chr(197) . chr(176) => 'U', chr(197) . chr(177) => 'u',
            chr(197) . chr(178) => 'U', chr(197) . chr(179) => 'u',
            chr(197) . chr(180) => 'W', chr(197) . chr(181) => 'w',
            chr(197) . chr(182) => 'Y', chr(197) . chr(183) => 'y',
            chr(197) . chr(184) => 'Y', chr(197) . chr(185) => 'Z',
            chr(197) . chr(186) => 'z', chr(197) . chr(187) => 'Z',
            chr(197) . chr(188) => 'z', chr(197) . chr(189) => 'Z',
            chr(197) . chr(190) => 'z', chr(197) . chr(191) => 's'
        );
        $string = strtr($string, $chars);
        return $string;
    }

    protected function htmlspanishchars($str) {
        return str_replace(array("&lt;", "&gt;"), array("<", ">"), htmlentities($str, ENT_NOQUOTES, "UTF-8"));
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

    public function WordSum($word) {
        $cnt = 0;
        $word = strtoupper(trim($word));
        $len = strlen($word);
        for ($i = 0; $i < $len; $i++) {
            $cnt += ord($word[$i]) + 64;
        }
        return $cnt;
    }

    public function fn($value, $bold = null, $colspan = null) {
        if ($value != 0 && !$bold) {
            return '<td style="text-align: right;" ' . (isset($colspan) ? ' colspan="' . $colspan . '"' : '') . '>' . number_format($value, 2, '.', ',') . '</td>';
        } elseif ($value != 0 && $bold) {
            return '<td style="text-align: right; font-weight:bold;">' . number_format($value, 2, '.', ',') . '</td>';
        } else {
            return '<td' . (isset($colspan) ? ' colspan="' . $colspan . '"' : '') . '>&nbsp;</td>';
        }
    }

    public function transformDate($date, $type) {
        switch ($type) {
            case 1: // date came in form dd/mm/YYYY
                $arr = explode('/', $date);
                $value = $arr[2] . '/' . $arr[1] . '/' . $arr[0];
                return $value;
            default:
                return $value;
        }
    }

    public function sumArray($array, $concepts) {
        $sum = 0;
        foreach ($concepts as $con) {
            if (isset($array[$con])) {
                $sum += $array[$con]["total"];
            }
        }
        return $sum;
    }

    public function xmlToArray($xml) {
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
            die();
        }
    }

    public function isRunning($pid) {
        try {
            $result = shell_exec(sprintf('ps %d', $pid));
            if (count(preg_split("/\n/", $result)) > 2) {
                return true;
            }
        } catch (Exception $e) {
            return false;
        }
        return false;
    }

    public function estadoValidador($edo1, $edo2, $subedo) {
        if ($edo1 == 2) {
            if ($edo2 == 2 && $subedo == 3) {
                return "PAGADO | HSBC";
            } elseif ($edo2 == 2 && $subedo == 5) {
                return "PAGADO | BANAMEX";
            } elseif ($edo2 == 2 && $subedo == 11) {
                return "PAGADO | BBVA BANCOMER";
            } elseif ($edo2 == 2 && $subedo == 8) {
                return "PAGADO | BANORTE";
            } elseif ($edo2 == 3 && $subedo == 320) {
                return "PRIMERA SELECCIÓN AUTOMATIZADA | VERDE EN PRIMERA SELECCIÓN";
            } elseif ($edo2 == 7 && $subedo == 710) {
                return "DESADUANADO/CUMPLIDO | DESADUANADO";
            } elseif ($edo2 == 7 && $subedo == 730) {
                return "DESADUANADO/CUMPLIDO | CUMPLIDO";
            } elseif ($edo2 == 1 && $subedo == 110) {
                return "VALIDACIÓN | VALIDACIÓN DE PREVIO";
            } elseif ($edo2 == 4 && $subedo == 410) {
                return "PRIMER RECONOCIMIENTO | INICIO PRIMER RECONOCIMIENTO";
            } elseif ($edo2 == 4 && $subedo == 450) {
                return "PRIMER RECONOCIMIENTO | RESULTADO SIN INCIDENCIAS";
            } elseif ($edo2 == 4 && $subedo == 460) {
                return "PRIMER RECONOCIMIENTO | RESULTADO CON INCIDENCIAS";
            } elseif ($edo2 == 3 && $subedo == 310) {
                return "PRIMERA SELECCIÓN AUTOMATIZADA | ROJO EN PRIMERA SELECCIÓN";
            } elseif ($edo2 == 7 && $subedo == 760) {
                return "DESADUANADO/CUMPLIDO | RECTIFICADO";
            } else {
                return "N/D";
            }
        }
        if ($edo1 == 1) {
            if ($edo2 == 2 && $subedo == 3) {
                return "PAGADO | HSBC";
            } elseif ($edo2 == 2 && $subedo == 11) {
                return "PAGADO | BBVA BANCOMER";
            } elseif ($edo2 == 2 && $subedo == 5) {
                return "PAGADO | BANAMEX";
            } elseif ($edo2 == 2 && $subedo == 8) {
                return "PAGADO | BANORTE";
            } elseif ($edo2 == 3 && $subedo == 320) {
                return "PRIMERA SELECCIÓN AUTOMATIZADA | VERDE EN PRIMERA SELECCIÓN";
            } elseif ($edo2 == 7 && $subedo == 710) {
                return "DESADUANADO/CUMPLIDO | DESADUANADO";
            } elseif ($edo2 == 7 && $subedo == 730) {
                return "DESADUANADO/CUMPLIDO | CUMPLIDO";
            } elseif ($edo2 == 1 && $subedo == 110) {
                return "VALIDACIÓN | VALIDACIÓN DE PREVIO";
            } elseif ($edo2 == 4 && $subedo == 410) {
                return "PRIMER RECONOCIMIENTO | INICIO PRIMER RECONOCIMIENTO";
            } elseif ($edo2 == 4 && $subedo == 450) {
                return "PRIMER RECONOCIMIENTO | RESULTADO SIN INCIDENCIAS";
            } elseif ($edo2 == 4 && $subedo == 460) {
                return "PRIMER RECONOCIMIENTO | RESULTADO CON INCIDENCIAS";
            } elseif ($edo2 == 3 && $subedo == 310) {
                return "PRIMERA SELECCIÓN AUTOMATIZADA | ROJO EN PRIMERA SELECCIÓN";
            } elseif ($edo2 == 7 && $subedo == 760) {
                return "DESADUANADO/CUMPLIDO | RECTIFICADO";
            } else {
                return "N/D";
            }
        }
    }

    public function tipoArchivo($basename) {
        try {
            $mapper = new Archivo_Model_RepositorioPrefijos();
            $table = new Archivo_Model_Table_RepositorioPrefijos();
            $ex = explode("_", $basename);
            if (isset($ex[0])) {
                $table->setPrefijo(strtoupper($ex[0]));
                $mapper->findPrefix($table);
                if (null !== ($table->getIdDocumento())) {
                    return $table->getIdDocumento();
                } else {
                    return 99;
                }
            } else {
                return 99;                
            }
            return 99;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function runGearmanProcess($workerName, $maxProcess) {
        $gm = new Application_Model_GearmanMapper();
        $workerPath = $gm->getProcessPath($workerName);
        if (isset($workerPath)) {
            if (file_exists($workerPath)) {
                $process = new Archivo_Model_PidMapper();
                for ($i = 0; $i < $maxProcess; $i++) {
                    if (!($pids = $process->checkRunnigProcess($workerName))) {
                        $newPid = shell_exec(sprintf('%s > /dev/null 2>&1 & echo $!', "php " . $workerPath));
                        $process->addNewProcess($newPid, $workerName, "php " . $workerPath);
                        $this->_logger->logEntry("misc:runGearmanProcess", "GEARMAN NEW WORKER: " . $newPid, "127.0.0.1", "Misc");
                    } else {
                        foreach ($pids as $k => $p) {
                            if (!$this->isRunning($p['pid'])) {
                                $process->deleteProcess($p['pid']);
                                unset($pids[$k]);
                                $this->_logger->logEntry("misc:runGearmanProcess", "GEARMAN NOT RUNNING: " . isset($p["id"]) ? $p["id"] : '', "127.0.0.1", "Misc");
                            }
                        }
                        if (count($pids) < $maxProcess) {
                            $newPid = shell_exec(sprintf('%s > /dev/null 2>&1 & echo $!', "php " . $workerPath));
                            $process->addNewProcess($newPid, $workerName, "php " . $workerPath);
                            $this->_logger->logEntry("misc:runGearmanProcess", "GEARMAN NEW WORKER: " . $newPid, "127.0.0.1", "Misc");
                        }
                    }
                }
            } else {
                throw new Exception("El archivo no existe.");
            }
        } else {
            throw new Exception("La ubicacion del proceso no existe en la base de datos.");
        }
        return true;
    }

    public function basicoReferencia($patente, $aduana, $referencia) {
        $con = new Application_Model_WsWsdl();
        $context = stream_context_create(array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
                "allow_self_signed" => true
            )
        ));
        if ($patente == 3589 && preg_match('/64/', $aduana)) {
            if (($wsdl = $con->getWsdl(3589, 640, "sitawin"))) {
                $soapSitawin = new Zend_Soap_Client($wsdl, array("stream_context" => $context));
                $referencia = $soapSitawin->basicoReferencia($patente, 640, $referencia);
                if ($referencia === false) {
                    $referencia = $soapSitawin->basicoReferencia($patente, 646, $referencia);
                }
            }
        }
        if (isset($referencia) && $referencia != false) {
            return $referencia;
        }
        return false;
    }

    /**
     * 
     * @param type $status
     * @param type $data
     * @param type $tabindex
     * @param type $id
     * @param type $name
     * @param type $class
     * @param type $style
     * @return string
     */
    public function mySelect($status, $data, $tabindex, $id, $name, $class, $style) {
        if ($status == false) {
            $html = "<select class=\"{$class}\" disabled=\"disabled\" tabindex=\"{$tabindex}\" style=\"{$style}\" id=\"{$id}\" name=\"{$name}\">";
            $html .= "<option value=\"\">---</option>";
            $html .= "</select>";
        } else {
            if (isset($data) && $data != false) {
                $html = "<select class=\"{$class}\" tabindex=\"{$tabindex}\" style=\"{$style}\" id=\"{$id}\" name=\"{$name}\">";
                $html .= "<option label=\"---\" value=\"\">---</option>";
                foreach ($data as $k => $v) {
                    $html .= "<option value=\"{$k}\">{$v}</option>";
                }
                $html .= "</select>";
            } else {
                $html = "<select class=\"{$class}\" disabled=\"disabled\" tabindex=\"{$tabindex}\" style=\"{$style}\" id=\"{$id}\" name=\"{$name}\">";
                $html .= "<option value=\"\">---</option>";
                $html .= "</select>";
            }
        }
        return $html;
    }

    public function connectSitawin($patente, $aduana) {
        try {
            if (preg_match("/^64/", $aduana) && $patente == 3589) {
                $conn = new OAQ_Sitawin(true, "192.168.200.5", "sa", "adminOAQ123", "SITA43589640", 1433, "Pdo_Mssql");
            }
            if (preg_match("/^24/", $aduana) && $patente == 3589) {
                $conn = new OAQ_Sitawin(true, "192.168.200.5", "sa", "adminOAQ123", "SITA43589240", 1433, "Pdo_Mssql");
            }
            if (!isset($conn)) {
                return;
            }
            return $conn;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
    
    public function execCurl($type) {
        if ($type == "enviar-email") {
            if (APPLICATION_ENV === "production") {
                shell_exec('curl -k -m 180 --request GET "https://127.0.0.1/automatizacion/queue/enviar-email" > /dev/null &');
            } else {
                shell_exec('curl -k -m 180 --request GET "http://127.0.0.1:8090/automatizacion/queue/enviar-email" > /dev/null &');
            }
        }
        if ($type == "imprimir-pedimento") {
            if (APPLICATION_ENV === "production") {
                shell_exec('curl -k -m 180 --request GET "https://127.0.0.1/automatizacion/queue/imprimir-pedimento" > /dev/null &');
            } else {
                shell_exec('curl -k -m 180 --request GET "http://127.0.0.1:8090/automatizacion/queue/imprimir-pedimento" > /dev/null &');
            }
        }
        if ($type == "estado-pedimento") {
            if (APPLICATION_ENV === "production") {
                shell_exec('curl -k -m 180 --request GET "https://127.0.0.1/automatizacion/queue/estado-pedimento" > /dev/null &');
            } else {
                $handle = curl_init();
                curl_setopt($handle, CURLOPT_URL, "http://127.0.0.1:8090/automatizacion/queue/estado-pedimento");
                curl_setopt($handle, CURLOPT_HEADER, false);
                curl_exec($handle);
                curl_close($handle);
            }
        }
    }

//    public function obtenerConsecutivoAduana($idAduana, $tipoOperacion = null) {
//        $mppr = new Trafico_Model_TraficoAduanasMapper();
//        $arr = $mppr->aduana($idAduana);
//        if ($idAduana == 1) { // ops espec
//            
//        } elseif ($idAduana == 2) {
//        } else {
//            
//        }
//        return $arr;
//    }
//    
    public function obtenerConsecutivo($patente, $aduana, $rfc, $tipoOperacion) {
        try {
            $prefijos = new Trafico_Model_TraficoPrefijosMapper();
            if ($patente == 3589 & preg_match('/^64/i', $aduana)) {
                $data = $prefijos->prefijoAduana($patente, $aduana, $tipoOperacion);
                if (isset($data) && $data !== false) {
                    if (($db = $this->connectSitawin($patente, $aduana)) != false) {
                        if (($cveImp = $db->revisarCliente($rfc))) {
                            $tc = $db->tipoCambio(date('Y-m-d H:i:s'));
                            $year = substr(date('y'), -1);
                            $data["digitosPedimento"] = $data["digitosPedimento"] - 1;
                            $curr = (float) "1e+0{$data["digitosPedimento"]}" * $year;
                            $next = (float) "1e+0{$data["digitosPedimento"]}" * ($year + 1);

                            $last = $db->ultimoPedimento($curr, $next, $data["prefijo"]);
                            if (isset($last) && $last != false) {
                                return array('success' => true, 'last' => $last["NUM_PED"], 'prefijo' => $data["prefijo"], 'digitos' => $data["digitosPedimento"], 'cveImportador' => (string) $cveImp, 'tc' => $tc);
                            } else {
                                return array('success' => false, 'message' => 'No fue posible determinar el ulitmo pedimento.');
                            }
                        } else {
                            return array('success' => false, 'message' => 'RFC de cliente no existe en la base de datos.');
                        }
                    }
                } else {
                    return array('success' => false, 'message' => 'No se encontraron perfijos.');
                }
            } else {
                return array('success' => false, 'message' => 'No existe aduana.');
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function plantillaFecha($value) {
        $matches = null;
        if (preg_match("/([0-9]{2})\/([0-9]{2})\/([0-9]{4})/", $value, $matches)) {
            return date('Y-m-d H:i:s', strtotime($matches[3] . '-' . $matches[2] . '-' . $matches[1]));
        } elseif (preg_match("/([0-9]{2})-([0-9]{2})-([0-9]{4})/", $value, $matches)) {
            return date('Y-m-d H:i:s', strtotime($matches[3] . '-' . $matches[2] . '-' . $matches[1]));
        } elseif (preg_match("/([0-9]{4})-([0-9]{2})-([0-9]{2})/", $value, $matches)) {
            return date('Y-m-d H:i:s', strtotime($matches[1] . '-' . $matches[2] . '-' . $matches[3]));
        } elseif (preg_match("/([0-9]{4})\/([0-9]{2})\/([0-9]{2})/", $value, $matches)) {
            return date('Y-m-d H:i:s', strtotime($matches[1] . '-' . $matches[2] . '-' . $matches[3]));
        }
        return $value;
    }

    public function diaJuliano($mes, $dia, $year) {
        $init = gregoriantojd(1, 1, $year);
        $current = gregoriantojd($mes, $dia, $year);
        return (($current - $init) + 1);
    }

    /**
     * Este funcion crea un nuevo directorio, se asume que directorio base debe existir.
     * 
     * @param string $baseDir
     * @param string $newDir
     * @return string
     * @throws Exception
     */
    public function crearNuevoDirectorio($baseDir, $newDir) {
        if (!file_exists($baseDir)) {
            mkdir($baseDir, 0777, true);
            if (!file_exists($baseDir)) {
                throw new Exception("Cannot create base directory! [{$baseDir}]");
            }
        }
        if (file_exists($baseDir)) {
            $path = preg_replace('/([^:])(\/{2,})/', '$1/', $baseDir . DIRECTORY_SEPARATOR . $newDir);
            if (file_exists($path)) {
                return $path;
            } else {
                mkdir($path, 0777, true);
                if (file_exists($path)) {
                    return $path;
                } else {
                    throw new Exception("Cannot create new directory!");
                }
            }
        }
    }

    /**
     * 
     * @param string $xml
     * @return string
     */
    public function removeSecurityHeaders($xml) {
        return preg_replace('#<soapenv:Header(.*?)>(.*?)</soapenv:Header>#is', '', $xml);
    }

    public function directorioExpedienteDigital($patente, $aduana, $referencia) {
        if (isset($this->_baseDir)) {
            if (file_exists($this->_baseDir)) {
                if(is_readable($this->_baseDir) && is_writable($this->_baseDir)) {
                    $this->_newDir = $this->_baseDir . DIRECTORY_SEPARATOR . $patente . DIRECTORY_SEPARATOR . $aduana . DIRECTORY_SEPARATOR . $referencia;
                    if (!file_exists($this->_newDir)) {
                        mkdir($this->_newDir, 0777, true);                        
                    }
                    if (file_exists($this->_newDir)) {
                        return $this->_newDir;
                    }
                    return false;
                } else {
                    throw new Exception("Base is not readable nor writable!");                    
                }
            } else {
                throw new Exception("Base not exists!");                    
            }
        } else {
            throw new Exception("Base dir not set!");
        }
    }

    public function directorioExpedienteDigitalBodega($siglas, $fechaEta, $referencia) {        
        if (isset($this->_baseDir)) {
            if (file_exists($this->_baseDir)) {
                if(is_readable($this->_baseDir) && is_writable($this->_baseDir)) {
                    $this->_newDir = $this->_baseDir . DIRECTORY_SEPARATOR . $siglas . DIRECTORY_SEPARATOR . 
                            date("Y", strtotime($fechaEta)) . DIRECTORY_SEPARATOR . date("m", strtotime($fechaEta)) . 
                            DIRECTORY_SEPARATOR . date("d", strtotime($fechaEta)) . DIRECTORY_SEPARATOR . $referencia;
                    if (!file_exists($this->_newDir)) {
                        mkdir($this->_newDir, 0777, true);                        
                    }
                    if (file_exists($this->_newDir)) {
                        return $this->_newDir;
                    } else {
                        throw new Exception("Base cannot be created!");
                    }
                } else {
                    throw new Exception("Base is not readable nor writable!");                    
                }
            } else {
                throw new Exception("Base not exists!");                    
            }
        } else {
            throw new Exception("Base dir not set!");
        }
    }

    public function renombrarArchivo($path, $sourceFile, $newFile) {
        if (!rename($path . DIRECTORY_SEPARATOR . $sourceFile, $path . DIRECTORY_SEPARATOR . $newFile)) {
            return false;
        }
        return true;
    }
    
    /**
     * 
     * @param type $patente
     * @param type $aduana
     * @return type
     */
    public function sitawin($patente, $aduana) {
        $mapper = new Application_Model_SisPedimentos();
        if(($s = $mapper->sisPedimentos($patente, $aduana))) {
            // Pdo_Mssql
            return new OAQ_Sitawin(true, $s["direccion_ip"], $s["usuario"], $s["pwd"], $s["dbname"], $s["puerto"], $s["tipo"]);
        }
        return;
    }
    
    public function sitawinTrafico($patente, $aduana) {
        $mapper = new Application_Model_SisPedimentos();
        if(($s = $mapper->sisPedimentos($patente, $aduana))) {
            return new OAQ_SitawinTrafico(true, $s["direccion_ip"], $s["usuario"], $s["pwd"], $s["dbname"], $s["puerto"], $s["tipo"]);
        }
        return;
    }
    
    /**
     * 
     * @param type $patente
     * @param type $aduana
     * @return type
     */
    public function sitawinCargoquin($patente, $aduana) {
        $mapper = new Application_Model_SisPedimentos();
        if(($s = $mapper->sisPedimentos($patente, $aduana))) {
            // Pdo_Mssql
            return new OAQ_SitawinCargoQuin($s["direccion_ip"], $s["usuario"], $s["pwd"], $s["dbname"], $s["puerto"], $s["tipo"]);
        }
        return;
    }
    
    public function buscarReferenciaWsdl($patente, $aduana, $referencia) {
        $con = new Application_Model_WsWsdl();
        $context = stream_context_create(array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
                "allow_self_signed" => true
            )
        ));
        if ($patente == 3589 && preg_match('/64/', $aduana)) {
            if (($wsdl = $con->getWsdl(3589, 640, "sitawin"))) {
                $soapSitawin = new Zend_Soap_Client($wsdl, array("stream_context" => $context));
                $r = $soapSitawin->basicoReferencia($patente, 640, $referencia);
                if ($r === false) {
                    $r = $soapSitawin->basicoReferencia($patente, 646, $referencia);
                }
            }
        }
        if ($patente == 3589 && preg_match('/24/', $aduana)) {
            if (($wsdl = $con->getWsdl(3589, 240, "sitawin"))) {
                $soapSitawin = new Zend_Soap_Client($wsdl, array("stream_context" => $context));
                $r = $soapSitawin->basicoReferencia($patente, 240, $referencia);
            }
        }
        if(isset($r) && $r !== false) {
            return $r;
        }
        return false;
    }
    
    public function sistemaPedimentos($patente, $aduana) {
        if ((int) $patente == 3589) {
            if ((int) $aduana == 640) {
                return new OAQ_Sitawin(true, '192.168.200.5', 'sa', 'adminOAQ123', 'SITA43589640', 1433, 'Pdo_Mssql');
            } elseif ((int) $aduana == 646) {
                return new OAQ_Sitawin(true, '192.168.0.253', 'sa', 'sqlcointer', 'SITA43589640', 1433, 'Pdo_Mssql');
            } elseif ((int) $aduana == 240) {
                return new OAQ_Sitawin(true, '192.168.200.5', 'sa', 'adminOAQ123', 'SITA43589240', 1433, 'Pdo_Mssql');
            } elseif ((int) $aduana == 645) {
                return new OAQ_Sitawin(true, '192.168.200.5', 'sa', 'adminOAQ123', 'SITA43589640', 1433, 'Pdo_Mssql');
            }
        }
        return;
    }
    
    public function datosFacturaProveedor($arr) {
        $vucem = new OAQ_VucemEnh();
        return array(
            "ProIden" => $vucem->tipoIdentificador($arr["rfc"], $arr["pais"]),
            "ProTaxID" => $arr["rfc"],
            "ProNombre" => $arr["razon_soc"],
            "ProCalle" => $arr["calle"],
            "ProNumExt" => $arr["numext"],
            "ProNumInt" => $arr["numint"],
            "ProColonia" => $arr["colonia"],
            "ProLocalidad" => $arr["localidad"],
            "ProCP" => $arr["cp"],
            "ProMun" => $arr["municipio"],
            "ProEdo" => $arr["estado"],
            "ProPais" => $arr["pais"],
        );
    }

    public function datosFacturaCliente($arr) {
        $vucem = new OAQ_VucemEnh();        
        return array(
            "CteIden" => $vucem->tipoIdentificador($arr["rfc"], $arr["pais"]),
            "CteRfc" => $arr["rfc"],
            "CteNombre" => $arr["razon_soc"],
            "CteCalle" => $arr["calle"],
            "CteNumExt" => $arr["numext"],
            "CteNumInt" => $arr["numint"],
            "CteColonia" => $arr["colonia"],
            "CteLocalidad" => $arr["localidad"],
            "CteCP" => $arr["cp"],
            "CteMun" => $arr["municipio"],
            "CteEdo" => $arr["estado"],
            "CtePais" => $arr["pais"],
        );
    }
    
    public function populateArrayExpo($arr) {
        $vucem = new OAQ_VucemEnh();
        return array(
            "CteNombre" => $arr["ProNombre"],
            "CteIden" => isset($arr["ProIden"]) ? $arr["ProIden"] : $vucem->tipoIdentificador($arr["ProTaxID"], $arr["ProPais"]),
            "CteRfc" => $arr["ProTaxID"],
            "CteNombre" => $arr["ProNombre"],
            "CteCalle" => $arr["ProCalle"],
            "CteNumExt" => $arr["ProNumExt"],
            "CteNumInt" => $arr["ProNumInt"],
            "CteColonia" => $arr["ProColonia"],
            "CteLocalidad" => $arr["ProLocalidad"],
            "CteCP" => $arr["ProCP"],
            "CteMun" => $arr["ProMun"],
            "CteEdo" => $arr["ProEdo"],
            "CtePais" => $arr["ProPais"],
            "ProIden" => $vucem->tipoIdentificador($arr["CteRfc"], $arr["CtePais"]),
            "ProTaxID" => $arr["CteRfc"],
            "ProNombre" => $arr["CteNombre"],
            "ProCalle" => $arr["CteCalle"],
            "ProNumExt" => $arr["CteNumExt"],
            "ProNumInt" => $arr["CteNumInt"],
            "ProColonia" => $arr["CteColonia"],
            "ProLocalidad" => $arr["CteLocalidad"],
            "ProCP" => $arr["CteCP"],
            "ProMun" => $arr["CteMun"],
            "ProEdo" => $arr["CteEdo"],
            "ProPais" => $arr["CtePais"],
            "Observaciones" => $arr["Observaciones"],
        );
    }
    
    public function populateArrayImpo($arr) {
        $vucem = new OAQ_VucemEnh();
        return array(
            "CteNombre" => $arr["CteNombre"],
            "CteIden" => isset($arr["CteIden"]) ? $arr["CteIden"] : $vucem->tipoIdentificador($arr["CteRfc"], $arr["CtePais"]),
            "CteRfc" => $arr["CteRfc"],
            "CteNombre" => $arr["CteNombre"],
            "CteCalle" => $arr["CteCalle"],
            "CteNumExt" => $arr["CteNumExt"],
            "CteNumInt" => $arr["CteNumInt"],
            "CteColonia" => $arr["CteColonia"],
            "CteLocalidad" => $arr["CteLocalidad"],
            "CteCP" => $arr["CteCP"],
            "CteMun" => $arr["CteMun"],
            "CteEdo" => $arr["CteEdo"],
            "CtePais" => $arr["CtePais"],
            "ProNombre" => $arr["ProNombre"],
            "ProIden" => $vucem->tipoIdentificador($arr["ProTaxID"], $arr["ProPais"]),
            "ProTaxID" => $arr["ProTaxID"],
            "ProNombre" => $arr["ProNombre"],
            "ProCalle" => $arr["ProCalle"],
            "ProNumExt" => $arr["ProNumExt"],
            "ProNumInt" => $arr["ProNumInt"],
            "ProColonia" => $arr["ProColonia"],
            "ProLocalidad" => $arr["ProLocalidad"],
            "ProCP" => $arr["ProCP"],
            "ProMun" => $arr["ProMun"],
            "ProEdo" => $arr["ProEdo"],
            "ProPais" => $arr["ProPais"],
            "Observaciones" => $arr["Observaciones"],
        );
    }
    
//    public function rfcConsulta($patente) {
//        
//    }
    
    /**
     * 
     * @param string $referencia
     * @return array
     */
    public function analisisReferencia($referencia) {
        if(preg_match("/^16ME/", $referencia)) {
            return array(
                "year" => 2016,
                "patente" => 3589,
                "aduana" => 240,
                "tipo" => "EXP",
            );
        }
        if(preg_match("/^15ME/", $referencia)) {
            return array(
                "year" => 2016,
                "patente" => 3589,
                "aduana" => 240,
                "tipo" => "EXP",
            );
        }
        if(preg_match("/^19TQ/", $referencia)) {
            return array(
                "year" => 2019,
                "patente" => 3589,
                "aduana" => 240,
                "tipo" => "IMP",
            );
        }
        if(preg_match("/^18TQ/", $referencia)) {
            return array(
                "year" => 2018,
                "patente" => 3589,
                "aduana" => 240,
                "tipo" => "IMP",
            );
        }
        if(preg_match("/^17TQ/", $referencia)) {
            return array(
                "year" => 2017,
                "patente" => 3589,
                "aduana" => 240,
                "tipo" => "IMP",
            );
        }
        if(preg_match("/^16TQ/", $referencia)) {
            return array(
                "year" => 2016,
                "patente" => 3589,
                "aduana" => 240,
                "tipo" => "IMP",
            );
        }
        if(preg_match("/^15TQ/", $referencia)) {
            return array(
                "year" => 2015,
                "patente" => 3589,
                "aduana" => 240,
                "tipo" => "IMP",
            );
        }
        if(preg_match("/^Q19/", $referencia)) {
            return array(
                "year" => 2019,
                "patente" => 3589,
                "aduana" => 640,
                "tipo" => "IMP",
            );
        }
        if(preg_match("/^Q18/", $referencia)) {
            return array(
                "year" => 2018,
                "patente" => 3589,
                "aduana" => 640,
                "tipo" => "IMP",
            );
        }
        if(preg_match("/^Q17/", $referencia)) {
            return array(
                "year" => 2017,
                "patente" => 3589,
                "aduana" => 640,
                "tipo" => "IMP",
            );
        }
        if(preg_match("/^Q16/", $referencia)) {
            return array(
                "year" => 2016,
                "patente" => 3589,
                "aduana" => 640,
                "tipo" => "IMP",
            );
        }
        if(preg_match("/^Q16/", $referencia)) {
            return array(
                "year" => 2016,
                "patente" => 3589,
                "aduana" => 640,
                "tipo" => "IMP",
            );
        }
        if(preg_match("/^Q15/", $referencia)) {
            return array(
                "year" => 2015,
                "patente" => 3589,
                "aduana" => 640,
                "tipo" => "IMP",
            );
        }        
        return;
    }
    
    public function newBackgroundWorker($worker, $maximum) {
        $appBasePath = realpath(__DIR__ . DIRECTORY_SEPARATOR . "../../workers");
        $process = new Archivo_Model_PidMapper();
        if ($worker == "trafico_worker") {
            for ($i = 0; $i < $maximum; $i++) {
                if (!($pids = $process->checkRunnigProcess("trafico_worker"))) {
                    $newPid = shell_exec(sprintf("%s > /dev/null 2>&1 & echo $!", "php /var/www/portalprod/workers/trafico_worker.php"));
                    $process->addNewProcess($newPid, "trafico_worker", "php /var/www/portalprod/workers/trafico_worker.php");
                } else {
                    foreach ($pids as $k => $p) {
                        if (!$this->_isRunning($p["pid"])) {
                            $process->deleteProcess($p['pid']);
                            unset($pids[$k]);
                        }
                    }
                    if (count($pids) < $maximum) {
                        $newPid = shell_exec(sprintf("%s > /dev/null 2>&1 & echo $!", "php /var/www/portalprod/workers/trafico_worker.php"));
                        $process->addNewProcess($newPid, "trafico_worker", "php /var/www/portalprod/workers/trafico_worker.php");
                    }
                }
            }
        } elseif ($worker == "ftp_worker") {
            $workerPath = $appBasePath . DIRECTORY_SEPARATOR . $worker . ".php";
            if(file_exists($workerPath) && is_readable($workerPath)) {
                for ($i = 0; $i < $maximum; $i++) {
                    if (!($pids = $process->checkRunnigProcess($worker))) {
                        $newPid = shell_exec(sprintf("%s > /dev/null 2>&1 & echo $!", "php " . $workerPath));
                        $process->addNewProcess($newPid, $worker, "php " . $workerPath);
                    } else {
                        foreach ($pids as $k => $p) {
                            if (!$this->_isRunning($p["pid"])) {
                                $process->deleteProcess($p['pid']);
                                unset($pids[$k]);
                            }
                        }
                        if (count($pids) < $maximum) {
                            $newPid = shell_exec(sprintf("%s > /dev/null 2>&1 & echo $!", "php " . $workerPath));
                            $process->addNewProcess($newPid, $worker, "php " . $workerPath);
                        }
                    }
                }
                return true;                
            } else {
                return "File not readable";                
            }
        }
    }

    protected function _isRunning($pid) {
        try {
            $result = shell_exec(sprintf("ps %d", $pid));
            if (count(preg_split("/\n/", $result)) > 2) {
                return true;
            }
        } catch (Exception $e) {
            
        }
        return false;
    }
    
    public function formatXmlString($xmlString) {
        $xml = preg_replace('/(>)(<)(\/*)/', "$1\n$2$3", $xmlString);
        $token = strtok($xml, "\n");
        $result = '';
        $pad = 0;
        $matches = array();
        while ($token !== false) :
            if (preg_match('/.+<\/\w[^>]*>$/', $token, $matches)) :
                $indent = 0;
            elseif (preg_match('/^<\/\w/', $token, $matches)) :
                $pad--;
                $indent = 0;
            elseif (preg_match('/^<\w[^>]*[^\/]>.*$/', $token, $matches)) :
                $indent = 1;
            else :
                $indent = 0;
            endif;
            $line = str_pad($token, strlen($token) + $pad, ' ', STR_PAD_LEFT);
            $result .= $line . "\n";
            $token = strtok("\n");
            $pad += $indent;
        endwhile;
        return $result;
    }

}
