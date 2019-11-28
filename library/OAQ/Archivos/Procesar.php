<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class OAQ_Archivos_Procesar {

    protected $inputFile;
    protected $outputFile;

    function setInputFile($inputFile) {
        $this->inputFile = $inputFile;
    }

    function setOutputFile($outputFile) {
        $this->outputFile = $outputFile;
    }
    
    public function __construct() {
    }

    public function reducirImagen($id) {
        $mppr = new Operaciones_Model_CatalogoPartesImagenes();
        $arr = $mppr->obtener($id);
        if (!empty($arr)) {
            if (APPLICATION_ENV == "production") {
                $inputfile = $arr["carpeta"] . DIRECTORY_SEPARATOR . $arr["imagen"];
                $outputfile = str_replace(".jpg", "_thumb.jpg", $inputfile);
                $cmd = "convert -resize 64x64 {$inputfile} {$outputfile}";
                exec($cmd, $output);
                if (file_exists($outputfile)) {
                    $mppr->actualizar($id, array("miniatura" => basename($outputfile)));
                    return true;
                }
                return false;
            } else if (APPLICATION_ENV == "staging") {
                $inputfile = $arr["carpeta"] . DIRECTORY_SEPARATOR . $arr["imagen"];
                $outputfile = str_replace(".jpg", "_thumb.jpg", $inputfile);
                $cmd = "convert -resize 64x64 {$inputfile} {$outputfile}";
                exec($cmd, $output);
                if (file_exists($outputfile)) {
                    $mppr->actualizar($id, array("miniatura" => basename($outputfile)));
                    return true;
                }
                return false;
            } else {
                $inputfile = preg_replace("/D:\\\\/", "/cygdrive/d/", $arr["carpeta"]);
                $inputfile = preg_replace("/\\\\/", "/", $inputfile) . "/" . $arr["imagen"];
                $outputfile = str_replace(".jpg", "_thumb.jpg", $inputfile);
                $cmd = "C:\\cygwin64\\bin\\convert.exe -resize 64x64 {$inputfile} {$outputfile}";
                exec($cmd, $output);
                if (file_exists(str_replace(array("/cygdrive/d/", "/"), array("D:\\", "\\"), $outputfile))) {
                    $mppr->actualizar($id, array("miniatura" => basename($outputfile)));
                    return true;
                }
                return false;
            }
        }
    }

    public function analizarArchivo($id) {
        $mppr = new Vucem_Model_VucemTmpEdocsMapper();
        $arr = $mppr->obtenerArchivo($id);
        $output = [];
        if (!empty($arr)) {
            if (APPLICATION_ENV == "production") {
                
            } else if (APPLICATION_ENV == "staging") {
                $this->inputFile = $arr["nomArchivo"];
                $this->outputFile = str_replace(".pdf", "_process.pdf", $this->inputFile);
                $cmd = "gs -q -o - -sDEVICE=inkcov {$this->inputFile}";
                exec($cmd, $output);
                $float = 0.0;
                if (!empty($output)) {
                    foreach ($output as $item) {
                        $exp = explode(" ", preg_replace('/\s+/', ' ', $item));
                        $float = + (float) $exp[1] + (float) $exp[1] + (float) $exp[1];
                    }
                    if ($float == 0) {
                        return array("success" => true);
                    } else {
                        return array("success" => false, "message" => "El archivo no esta en escala de grises.");
                    }
                } else {
                    return array("success" => false, "message" => "El archivo no se pudo analizar.");
                }
                return;
            } else {
                $this->inputFile = str_replace(array("D:\\", "\\"), array("/cygdrive/d/", "/"), $arr["nomArchivo"]);
                $cmd = "C:\\cygwin64\\bin\\gs.exe -q -o - -sDEVICE=inkcov {$this->inputFile}";
                exec($cmd, $output);
                $float = 0.0;
                if (!empty($output)) {
                    foreach ($output as $item) {
                        $exp = explode(" ", preg_replace('/\s+/', ' ', $item));
                        $float = + (float) $exp[1] + (float) $exp[1] + (float) $exp[1];
                    }
                    if ($float == 0) {
                        return array("success" => true);
                    } else {
                        return array("success" => false, "message" => "El archivo no esta en escala de grises.");
                    }
                } else {
                    return array("success" => false, "message" => "El archivo no se pudo analizar.");
                }
                return;
            }
        } else {
            return;
        }
    }

    public function procesarEdocument($id) {
        $mppr = new Vucem_Model_VucemTmpEdocsMapper();
        $arr = $mppr->obtenerArchivo($id);
        $output = [];
        if (!empty($arr)) {
            if (APPLICATION_ENV == "production") {
                $this->inputFile = $arr["nomArchivo"];
                $this->outputFile = str_replace(".pdf", "_process.pdf", $this->inputFile);                
                $cmd = "convert -colorspace GRAY -density 300 -auto-level -depth 8 -compress zip -threshold 80% -type bilevel {$this->inputFile} {$this->outputFile}";
                exec($cmd, $output);
                if (file_exists($this->outputFile)) {
                    return array("success" => true, "filename" => $this->outputFile);
                } else {
                    throw new Exception("Archivo no existe.");
                }
                return;
            } else if (APPLICATION_ENV == "staging") {
                $this->inputFile = $arr["nomArchivo"];
                $this->outputFile = str_replace(".pdf", "_process.pdf", $this->inputFile);                
                $cmd = "convert -colorspace GRAY -density 300 -auto-level -depth 8 -compress zip -threshold 80% -type bilevel {$this->inputFile} {$this->outputFile}";
                exec($cmd, $output);
                if (file_exists($this->outputFile)) {
                    return array("success" => true, "filename" => $this->outputFile);
                } else {
                    throw new Exception("Archivo no existe.");
                }
                return;
            } else {
                $this->inputFile = str_replace(array("D:\\", "\\"), array("/cygdrive/d/", "/"), $arr["nomArchivo"]);
                $this->outputFile = str_replace(".pdf", "_process.pdf", $this->inputFile);
                $cmd = "C:\\cygwin64\\bin\\convert.exe -colorspace GRAY -density 300 -auto-level -depth 8 -compress zip -threshold 80% -type bilevel {$this->inputFile} {$this->outputFile}";
                exec($cmd, $output);
                if (file_exists(str_replace(array("/cygdrive/d/", "/"), array("D:\\", "\\"), $this->outputFile))) {
                    return array("success" => true, "filename" => dirname($arr["nomArchivo"]) . DIRECTORY_SEPARATOR . basename($this->outputFile));
                }
                return;
            }
        } else {
            return;
        }
    }
    
    public function convertirArchivoEdocument($idArchivo) {
        
        $repo = new Archivo_Model_RepositorioMapper();
        $arr = $repo->informacionVucem($idArchivo);
        
        $output = [];
        
        if (!empty($arr)) {
            if (APPLICATION_ENV == "production") {
                
                $this->inputFile = $arr["ubicacion"];
                $this->outputFile = str_replace(".pdf", "_CONVERT.pdf", $this->inputFile);   
                
                $cmd = "convert -colorspace GRAY -density 300 -auto-level -depth 8 -compress zip -threshold 80% -type bilevel {$this->inputFile} {$this->outputFile}";
                exec($cmd, $output);
                if (file_exists($this->outputFile)) {
                    return array("success" => true, "filename" => $this->outputFile);
                } else {
                    throw new Exception("Archivo no existe.");
                }
                return;
                
            } else {
                
                $this->inputFile = str_replace(array("D:\\", "\\"), array("/cygdrive/d/", "/"), $arr["ubicacion"]);
                $this->outputFile = str_replace(".pdf", "_CONVERT.pdf", $this->inputFile);
                
                if (file_exists(str_replace(array("/cygdrive/d/", "/"), array("D:\\", "\\"), $this->outputFile))) {
                    unlink(str_replace(array("/cygdrive/d/", "/"), array("D:\\", "\\"), $this->outputFile));
                }                
                $cmd = "C:\\cygwin64\\bin\\convert.exe -colorspace GRAY -density 300 -auto-level -depth 8 -compress zip -threshold 80% -type bilevel {$this->inputFile} {$this->outputFile}";
                exec($cmd, $output);
                
                if (file_exists(str_replace(array("/cygdrive/d/", "/"), array("D:\\", "\\"), $this->outputFile))) {
                    return array("success" => true, "filename" => dirname($arr["ubicacion"]) . DIRECTORY_SEPARATOR . basename($this->outputFile));
                }
                return;
            }
        } else {
            return;
        }
    }

}
