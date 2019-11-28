<?php

/**
 * Administración, validación de archivos M3
 *
 * @author Jaime E. Valdez <jvaldezch@gmail.com>
 */
class OAQ_Emails {

    protected $_validFrom;
    protected $_validSubject;
    protected $_imap;
    protected $_content;
    protected $_attachments;
    protected $_dir;
    protected $_transport;    
    protected $_mail;    

    function set_validFrom($_validFrom) {
        $this->_validFrom = $_validFrom;
    }

    function set_validSubject($_validSubject) {
        $this->_validSubject = $_validSubject;
    }
    
    function set_dir($_dir) {
        $this->_dir = $_dir;
    }
        
    function get_content() {
        return $this->_content;
    }

    function get_attachments() {
        return $this->_attachments;
    }
    
    function __construct() {
        
    }

    public function isValid($from, $subject) {
        try {
            if (is_array($this->_validFrom)) {
                foreach ($this->_validFrom as $item) {
                    if (preg_match('/' . trim($item) . '/i', $from)) {
                        return true;
                    }
                }
                throw new Exception("Invalid from!");
            } elseif (is_string($this->_validFrom)) {
                if (!preg_match('/' . trim($this->_validFrom) . '/i', $from)) {
                    throw new Exception("Invalid from!");
                }
            }
            if (is_array($this->_validSubject)) {
                foreach ($this->_validSubject as $item) {
                    if (preg_match('/' . trim($item) . '/i', $subject)) {
                        return true;
                    }
                }
                throw new Exception("Invalid subject!");
            } elseif (is_string($this->_validSubject)) {
                if (!preg_match('/' . trim($this->_validSubject) . '/i', $subject)) {
                    throw new Exception("Invalid subject!");
                }
            }
            return true;
        } catch (Exception $ex) {
            return array("success" => false, "message" => $ex->getMessage());
        }
    }

    public function findParts(OAQ_IMAP $imap, $i) {
        try {
            $mailStruct = $imap->getStructure($i);
            $flattenedParts = $imap->flattenParts($mailStruct->parts);
            $this->_attachments = array();
            foreach ($flattenedParts as $partNumber => $part) {
                switch ($part->type) {
                    case 0:
                        $this->_content = $imap->getPart($i, $partNumber, $part->encoding);
                        break;
                    case 1:
                        // multi-part headers, can ignore
                        break;
                    case 2:
                        // attached message headers, can ignore
                        break;
                    case 3: // application
                    case 4: // audio
                    case 5: // image
                    case 6: // video
                    case 7: // other
                        $name = $imap->getFilenameFromPart($part);
                        $filename = preg_replace("/[^-_a-z0-9]+/i", "_", pathinfo($name, PATHINFO_FILENAME)) . '.' . strtolower(pathinfo($name, PATHINFO_EXTENSION));
                        $this->_attachments[] = $filename;
                        if(isset($this->_dir)) {
                            $attachment = $imap->getPart($i, $partNumber, $part->encoding);
                            if (!file_exists($this->_dir . DIRECTORY_SEPARATOR . $filename)) {
                                $fh = fopen($this->_dir . DIRECTORY_SEPARATOR . $filename, 'w');
                                fwrite($fh, $attachment);
                                fclose($fh);
                            }
                        } else {
                            throw new Exception("Directory not been set!");
                        }
                        break;
                }
            } // foreach part
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function findPartsGmail(OAQ_Gmail $imap, $i) {
        try {
            $mailStruct = $imap->getStructure($i);
            $flattenedParts = $imap->flattenParts($mailStruct->parts);
            $this->_attachments = array();
            foreach ($flattenedParts as $partNumber => $part) {
                switch ($part->type) {
                    case 0:
                        $this->_content = $imap->getPart($i, $partNumber, $part->encoding);
                        break;
                    case 1:
                        // multi-part headers, can ignore
                        break;
                    case 2:
                        // attached message headers, can ignore
                        break;
                    case 3: // application
                    case 4: // audio
                    case 5: // image
                    case 6: // video
                    case 7: // other
                        $name = $imap->getFilenameFromPart($part);
                        $filename = preg_replace("/[^-_a-z0-9]+/i", "_", pathinfo($name, PATHINFO_FILENAME)) . '.' . strtolower(pathinfo($name, PATHINFO_EXTENSION));
                        $this->_attachments[] = $filename;
                        if(isset($this->_dir)) {
                            $attachment = $imap->getPart($i, $partNumber, $part->encoding);
                            if (!file_exists($this->_dir . DIRECTORY_SEPARATOR . $filename)) {
                                $fh = fopen($this->_dir . DIRECTORY_SEPARATOR . $filename, 'w');
                                fwrite($fh, $attachment);
                                fclose($fh);
                            }
                        } else {
                            throw new Exception("Directory not been set!");
                        }
                        break;
                }
            } // foreach part
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
    
    public function cofidi() {
        $arr = array(
            "host" => "smtp.gmail.com",
            "port" => 587,
            "ssl" => "tls",
            "auth" => "login",
            "username" => "cofidi.envio@gmail.com",
            "password" => "F4ctC0f1d1#",
        );
        $this->_transport = new Zend_Mail_Transport_Smtp("smtp.gmail.com", $arr);
        $this->_mail = new Zend_Mail("UTF-8");
    }

    public function codifiSetup($subject = null, $ccs = null, $uuid = null) {
        if (isset($this->_mail)) {
            $this->_mail->setFrom("cofidi.envio@gmail.com");
            $this->_mail->setBodyText("UUID: {$uuid}\n\n-- Email generado de forma automática, no responder. --");
            if (APPLICATION_ENV == "development") {
                $this->_mail->setSubject("[PRUEBA] " . $subject);
            } else {
                $this->_mail->setSubject($subject);
            }
            if (APPLICATION_ENV == "development") {
                $this->_mail->addTo("jvaldezch@gmail.com");
            } else {
                $this->_mail->addTo("red.cofidi.inbox@ateb.com.mx");
                if (isset($ccs) && !empty($ccs)) {
                    foreach ($ccs as $cc) {
                        $this->_mail->addCc($cc["email"]);
                    }
                }
            }
        } else {
            throw new Exception("Not set mail " . __METHOD__);
        }
    }

    public function addAttachment($fileContent, $filename) {
        if (isset($this->_mail)) {
            $attach = $this->_mail->createAttachment($fileContent);
            $attach->type = "application/octet-stream";
            $attach->disposition = Zend_Mime::DISPOSITION_INLINE;
            $attach->encoding = Zend_Mime::ENCODING_BASE64;
            $attach->filename = $filename;
        } else {
            throw new Exception("Not set mail " . __METHOD__);
        }
    }

    public function send() {
        return $this->_mail->send($this->_transport);
    }
    
    protected function _dato($line) {
        $l = explode(":", $line);
        if (isset($l[1])) {
            return trim($l[1]);
        }
        return;
    }
    
    protected function _fecha($line) {
        $l = explode(":", $line);
        $d = explode("/", trim($l[1]));
        return date("Y-m-d H:i:s", strtotime($d[2] . "-" . $d[1] . "-" . $d[0]));
    }

    public function analizarArchivoProexi($filename, $body) {
        $info = array();
        $lines = explode(PHP_EOL, $body);
        if (!empty($lines)) {
            foreach ($lines as $line) {
                if (preg_match('/^Patente/i', $line)) {
                    $info["patente"] = (int) $this->_dato($line);
                }
                if (preg_match('/^Aduana/i', $line)) {
                    $info["aduana"] = (int) $this->_dato($line);
                }
                if (preg_match('/^Pedimento/i', $line)) {
                    $info["pedimento"] = (int) $this->_dato($line);
                }
                if (preg_match('/RFC Importador/i', $line)) {
                    $info["rfcCliente"] = $this->_dato($line);
                }
                if (preg_match('/Fecha de entrada/i', $line)) {
                    $info["fechaEntrada"] = $this->_fecha($line);
                }
                if (preg_match('/Fecha de pago/i', $line)) {
                    $info["fechaPago"] = $this->_fecha($line);
                }
                if (preg_match('/Guia House/i', $line)) {
                    $info["guiaHouse"] = $this->_dato($line);
                }
                if (preg_match('/Guia Master/i', $line)) {
                    $info["guiaMaster"] = $this->_dato($line);
                }
            }
        }
        $info["referencia"] = str_replace(array('ArchivoDig_', '.zip'), '', basename($filename));        
        $validacion = new OAQ_ArchivosValidacion();
        $referencias = new OAQ_Referencias();
        $referencias->crearRepositorio($info["patente"], $info["aduana"], $info["referencia"], "AutoEmail", $info["rfcCliente"], $info["pedimento"]);        
        $referencias->crearDirectorio($info["patente"], $info["aduana"], $info["referencia"]);
        if (APPLICATION_ENV == "production" || APPLICATION_ENV == "staging") {
            if ((int) $info["patente"] == 3574 && (int) $info["aduana"] == 160) {
                $outdir = "/home/samba-share/validacion/3574_160";
            }
            if ((int) $info["patente"] == 3574 && (int) $info["aduana"] == 240) {
                $outdir = "/home/samba-share/validacion/3574_240";
            }
            if ((int) $info["patente"] == 3574 && (int) $info["aduana"] == 800) {
                $outdir = "/home/samba-share/validacion/3574_800";
            }
            if ((int) $info["patente"] == 3574 && (int) $info["aduana"] == 470) {
                $outdir = "/home/samba-share/validacion/3574_470";
            }
            if ((int) $info["patente"] == 3457 && (int) $info["aduana"] == 270) {
                $outdir = "/home/samba-share/validacion/3457_270";
            }
        } else {
            $outdir = "C:\\wamp64\\tmp\\validacion";
        }
        $outdir .= DIRECTORY_SEPARATOR . date("Y") . DIRECTORY_SEPARATOR . date("m") . DIRECTORY_SEPARATOR . date("d");
        var_dump($outdir);
        if (!file_exists($outdir)) {
            mkdir($outdir, 0777, true);
        }
        if (file_exists($filename) && preg_match('/^ArchivoDig_(.*).zip$/i', basename($filename))) {
            $folder = dirname($filename) . DIRECTORY_SEPARATOR . substr(basename($filename), 0, -4);
            if (!file_exists($folder)) {
                mkdir($folder, 0777, true);
            }
            $zip = new ZipArchive;
            $res = $zip->open($filename);
            if ($res === TRUE) {
                $zip->extractTo($folder);
                $zip->close();
                $files = glob($folder . DIRECTORY_SEPARATOR . "*", GLOB_BRACE);
                if (!empty($files)) {
                    foreach ($files as $file) {
                        $tipoArchivo = null;
                        if (preg_match("/{$info["referencia"]}.pdf$/i", basename($file))) {
                            $tipoArchivo = 23;
                        }
                        if (preg_match("/_SIMP.pdf$/i", basename($file))) {
                            $tipoArchivo = 33;
                        }
                        if (($mtype = $validacion->tipoArchivo($file))) {
                            if (file_exists($outdir)) {
                                if ($validacion->copiarArchivo(dirname($file), $outdir, basename($file))) {
                                    $validacion->agregarArchivoValidacion($info["patente"], $info["aduana"], $outdir, basename($file), file_get_contents($file), $mtype, "AutoEmail");
                                }
                            }
                        }
                        if ($tipoArchivo != null) {
                            $referencias->agregarArchivo($tipoArchivo, $file);
                        }
                    }
                    return true;
                }
                return;
            } else {
                return;
            }
        }
        return;
    }

    public function analizarArriboTerminal($filename) {
        if (file_exists($filename) && preg_match('/.xml$/i', basename($filename))) {
            $get = file_get_contents($filename);
            $array = simplexml_load_string($get);
            $arr = json_decode(json_encode((array)$array), true);
            if (isset($arr["Entrada"]["@attributes"])) {
                return true;
            }
            return;
        }
        return;
    }

}
