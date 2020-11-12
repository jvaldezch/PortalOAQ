<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class OAQ_Archivos_FtpDescarga {

    protected $_idRepositorio;
    protected $_directory;
    protected $_username;

    function __construct($_idRepositorio, $_directory, $_username = null) {
        $this->_idRepositorio = $_idRepositorio;
        $this->_directory = $_directory;
        $this->_username = $_username;
    }

    public function obtenerLink() {
        $mppr = new Clientes_Model_FtpLinks();
        $exp = new OAQ_Expediente_Descarga();
        $mapper = new Clientes_Model_Repositorio();
        $arr = $mapper->datos($this->_idRepositorio, $this->_username);
        $files = $mapper->archivos($arr["referencia"], $arr["patente"], $arr["aduana"]);
        if (isset($files) && !empty($files)) {
            $zipName = $exp->zipFilename($arr["patente"], $arr["aduana"], $arr["pedimento"], $arr["referencia"], $arr["rfcCliente"]);
            $zipDir = $this->_directory;
            $zipFilename = $zipDir . DIRECTORY_SEPARATOR . $zipName;
            if (!($arrz = $mppr->verificar($zipName))) {
                if (file_exists($zipFilename)) {
                    unlink($zipFilename);
                }
                if (!file_exists($zipDir)) {
                    mkdir($zipDir, 0777, true);
                }
                $zip = new ZipArchive();
                if ($zip->open($zipFilename, ZIPARCHIVE::CREATE) !== TRUE) {
                    return null;
                }
                foreach ($files as $file) {
                    if (file_exists($file["ubicacion"])) {
                        $tmpfile = $zipDir . DIRECTORY_SEPARATOR . sha1($file["ubicacion"]);
                        copy($file["ubicacion"], $tmpfile);
                        if (($zip->addFile($tmpfile, $exp->filename($arr["patente"], $arr["aduana"], $arr["pedimento"], basename($file["ubicacion"]), $file["tipo_archivo"], $this->_session->username))) === true) {
                            $added[] = $tmpfile;
                        }
                        unset($tmpfile);
                    }
                }
                if (($zip->close()) === TRUE) {
                    $closed = true;
                }
                if ($closed === true) {
                    foreach ($added as $tmp) {
                        unlink($tmp);
                    }
                }
                $arrz = array(
                    "ubicacion" => $zipFilename,
                    "archivoNombre" => $zipName,
                    "creado" => date("Y-m-d H:i:s"),
                );
                if ($mppr->agregar($arrz)) {
                    $link = "ftp://oaq.dnsalias.net/" . $zipName;
                }
            } else {
                $link = "ftp://oaq.dnsalias.net/" . $zipName;
            }
            if (isset($arrz)) {
                return "<p style=\"font-size: 12px\">Debido a que el expediente es muy grande utilice el siguiente link para descargar:<br><br><a href=\"{$link}\" target=\"_blank\">{$link}</a><br><br>Este link será removido en un lapso de 24 horas y se recomienda pegarlo en <strong>Internet Explorer</strong> o usando un cliente FTP como <a href=\"https://filezilla-project.org/\">FileZilla</a>.</p>";
            }
        } else {
            throw new Exception("No data found!");
        }
    }

    public function obtenerArchivo() {
        $mppr = new Clientes_Model_FtpLinks();
        $exp = new OAQ_Expediente_Descarga();
        $mapper = new Clientes_Model_Repositorio();
        $arr = $mapper->datos($this->_idRepositorio, $this->_username);
        $files = $mapper->archivos($arr["referencia"], $arr["patente"], $arr["aduana"]);
        if (isset($files) && !empty($files)) {
            $zipName = $exp->zipFilename($arr["patente"], $arr["aduana"], $arr["pedimento"], $arr["referencia"], $arr["rfcCliente"]);
            $zipDir = $this->_directory;
            $zipFilename = $zipDir . DIRECTORY_SEPARATOR . $zipName;
            if (!($arrz = $mppr->verificar($zipName))) {
                if (file_exists($zipFilename)) {
                    unlink($zipFilename);
                }
                if (!file_exists($zipDir)) {
                    mkdir($zipDir, 0777, true);
                }
                $zip = new ZipArchive();
                if ($zip->open($zipFilename, ZIPARCHIVE::CREATE) !== TRUE) {
                    return null;
                }
                foreach ($files as $file) {
                    if (file_exists($file["ubicacion"])) {
                        $tmpfile = $zipDir . DIRECTORY_SEPARATOR . sha1($file["ubicacion"]);
                        copy($file["ubicacion"], $tmpfile);
                        if (($zip->addFile($tmpfile, $exp->filename($arr["patente"], $arr["aduana"], $arr["pedimento"], basename($file["ubicacion"]), $file["tipo_archivo"], $this->_session->username))) === true) {
                            $added[] = $tmpfile;
                        }
                        unset($tmpfile);
                    }
                }
                if (($zip->close()) === TRUE) {
                    $closed = true;
                }
                if ($closed === true) {
                    foreach ($added as $tmp) {
                        unlink($tmp);
                    }
                }
                $arrz = array(
                    "ubicacion" => $zipFilename,
                    "archivoNombre" => $zipName,
                    "creado" => date("Y-m-d H:i:s"),
                );
                if ($mppr->agregar($arrz)) {
                    $link = $zipName;
                }
            } else {
                $link = $zipName;
            }
            return $zipName;
        } else {
            throw new Exception("No data found!");
        }
    }

}
