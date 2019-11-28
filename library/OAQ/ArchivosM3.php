<?php

/**
 * Administración, validación de archivos M3
 *
 * @author Jaime E. Valdez <jvaldezch@gmail.com>
 */
class OAQ_ArchivosM3 {

    protected $_folder;
    protected $_file;
    protected $_m3Folder;
    protected $_array;
    
    function get_array() {
        return $this->_array;
    }

    function __construct($m3 = null) {
        $this->_m3Folder = $m3;
    }

    protected function JDtoISO8601($JD) {
        if ($JD <= 1721425)
            $JD += 365;
        list($month, $day, $year) = explode('/', jdtogregorian($JD));
        return sprintf('%+05d-%02d-%02d', $year, $month, $day);
    }
    
    public function analizarM3($filename) {
        if(file_exists($filename)) {
            $arrayFile = preg_split('/\r\n|\r|\n/', file_get_contents($filename));
            $content = array();
            foreach ($arrayFile as $line) {
                $key = substr($line, 0, 3);
                if ($key != '') {
                    if (key_exists($key, $content)) {
                        $content[$key][] = trim($line);
                    } else {
                        $content[$key][] = trim($line);
                    }
                }
            }
            if(isset($content["500"])) {
                return $content["500"];
            } else {
                return false;
            }
        }        
    }
    
    public function analizarM3Contenido($contenido) {
        try {
            $arrayFile = preg_split('/\r\n|\r|\n/', $contenido);
            $content = array();
            foreach ($arrayFile as $line) {
                $key = substr($line, 0, 3);
                if ($key != '') {
                    if (key_exists($key, $content)) {
                        $content[$key][] = trim($line);
                    } else {
                        $content[$key][] = trim($line);
                    }
                }
            }
            if(isset($content["500"])) {
                return $content["500"];
            } else {
                return false;
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
                
    }
    
    public function analizarArchivo($filename, $content) {
        $array = preg_split('/\r\n|\r|\n/', $content);
        if(!empty($array)) {
            if (preg_match('/^m[0-9]{7}.[0-9]{3}$/i', $filename)) {
                $data = $this->_pedimentosArchivoM($array);
                return $data;
            }
            if (preg_match('/^m[0-9]{7}.err$/i', $filename)) {
                $data = $this->_firmasValidacion($array);
                return $data;
            }
            if (preg_match('/^a[0-9]{7}.[0-9]{3}$/i', $filename)) {
                $data = $this->_pedimentosPagados($array);
                return $data;
            }
        } else {
            return false;
        }
        
    }
    
    protected function _pedimentosArchivoM($array) {
        if (isset($array) && !empty($array)) {
            $data = array();
            foreach ($array as $line) {
                if (preg_match('/^500/', trim($line))) {
                    $exp = explode('|', $line);
                    $data[] = array(
                        'patente' => (int) $exp[2],
                        'aduana' => (int) $exp[4],
                        'pedimento' => (int) $exp[3],
                        'tipoMovimiento' => (int) $exp[1],
                        'firmaDesistir' => ($exp[5] != '') ? $exp[5] : null,
                    );
                }
            }
            return $data;
        }
    }

    protected function _firmasValidacion($array) {
        if (isset($array) && !empty($array)) {
            $data = array();
            foreach ($array as $line) {
                if ((preg_match('/^F/i', $line) || preg_match('/^E/i', $line) || preg_match('/^A/i', $line)) && !preg_match('/BORRADO/i', trim($line))) {
                    $data[] = array(
                        'pedimento' => substr($line, 1, 7),
                        'firma' => trim(substr($line, -8)),
                    );
                }
            }
            return $data;
        }
    }

    protected function _pedimentosPagados($array) {
        if (isset($array) && !empty($array)) {
            $data = array();
            foreach ($array as $line) {
                if (trim($line) !== '' && (preg_match('/^3/', trim($line)))) {
                    $data[] = array(
                        "aduana" => (int) substr($line, 2, 2),
                        "patente" => (int) substr($line, 4, 4),
                        "pedimento" => (int) substr($line, 8, 7),
                        "rfcImportador" => substr($line, 15, 12),
                        "caja" => substr($line, 28, 2),
                        "numOperacion" => substr($line, 30, 10),
                        "firmaBanco" => substr($line, 40, 10),
                        "fechaPago" => $this->changeDate(substr($line, 50, 8), true) . ' ' . substr($line, 58, 8),
                        "fecha" => $this->changeDate(substr($line, 50, 8)),
                        "hora" => substr($line, 58, 8),
                    );
                } elseif (trim($line) !== '' && preg_match('/^4/', trim($line))) {
                    $data[] = array(
                        "aduana" => (int) substr($line, 2, 2),
                        "patente" => (int) substr($line, 4, 4),
                        "pedimento" => (int) substr($line, 8, 7),
                    );
                }
            }
            return $data;
        }
    }

    public function analizarValidados($filename) {
        if(file_exists($filename)) {
            $array = preg_split('/\r\n|\r|\n/', file_get_contents($filename));
            $arrayFile =  array();
            foreach ($array as $line) {
                if ((preg_match('/^F/i', $line) || preg_match('/^E/i', $line) || preg_match('/^A/i', $line)) && !preg_match('/BORRADO/i', trim($line))) {
                    $arrayFile[] = array(
                        'pedimento' => substr($line, 1, 7),
                        'firma' => trim(substr($line, -8)),
                    );
                }
            }
            return $arrayFile;
        }
    }
    
    public function analizarAPagados($filename) {
        if(file_exists($filename)) {
            $array = preg_split('/\r\n|\r|\n/', file_get_contents($filename));
            $arrayFile =  array();
            foreach ($array as $line) {
                if (trim($line) !== '' && preg_match('/^3/', trim($line))) {
                    $arrayFile[] = array(
                        'pedimento' => (int) substr($line, 8, 7),
                        'firma' => substr($line, 40, 10),
                        'rfcImportador' => substr($line, 15, 12),
                    );
                }
            }
            return $arrayFile;
        }
    }

    protected function _fileM3Exists($year, $patente, $aduana, $juliano, $archivo) {
        if (!file_exists($this->_m3Folder . DIRECTORY_SEPARATOR . $year)) {
            mkdir($this->_m3Folder . DIRECTORY_SEPARATOR . $year);
        }
        if (!file_exists($this->_m3Folder . DIRECTORY_SEPARATOR . $year . DIRECTORY_SEPARATOR . $patente)) {
            mkdir($this->_m3Folder . DIRECTORY_SEPARATOR . $year . DIRECTORY_SEPARATOR . $patente);
        }
        if (!file_exists($this->_m3Folder . DIRECTORY_SEPARATOR . $year . DIRECTORY_SEPARATOR . $patente . DIRECTORY_SEPARATOR . $aduana)) {
            mkdir($this->_m3Folder . DIRECTORY_SEPARATOR . $year . DIRECTORY_SEPARATOR . $patente . DIRECTORY_SEPARATOR . $aduana);
        }
        if (!file_exists($this->_m3Folder . DIRECTORY_SEPARATOR . $year . DIRECTORY_SEPARATOR . $patente . DIRECTORY_SEPARATOR . $aduana . DIRECTORY_SEPARATOR . $juliano)) {
            mkdir($this->_m3Folder . DIRECTORY_SEPARATOR . $year . DIRECTORY_SEPARATOR . $patente . DIRECTORY_SEPARATOR . $aduana . DIRECTORY_SEPARATOR . $juliano);
        }
        if (!file_exists($this->_m3Folder . DIRECTORY_SEPARATOR . $year . DIRECTORY_SEPARATOR . $patente . DIRECTORY_SEPARATOR . $aduana . DIRECTORY_SEPARATOR . $juliano . DIRECTORY_SEPARATOR . $archivo)) {
            return null;
        }
        return true;
    }

    protected function _copyM3File($from, $year, $patente, $aduana, $juliano, $archivo) {
        if (!copy(realpath($from), $this->_m3Folder . DIRECTORY_SEPARATOR . $year . DIRECTORY_SEPARATOR . $patente . DIRECTORY_SEPARATOR . $aduana . DIRECTORY_SEPARATOR . $juliano . DIRECTORY_SEPARATOR . $archivo)) {
            echo "Error al copiar " . realpath($from) . " ...\n";
        }
    }

    protected function changeDate($fecha, $short = null) {
        $day = substr($fecha, 0, 2);
        $month = substr($fecha, 2, 2);
        $year = substr($fecha, 4, 4);

        if (!$short) {
            return date('Y-m-d H:i:s', strtotime($year . '-' . $month . '-' . $day));
        } else {
            return date('Y-m-d', strtotime($year . '-' . $month . '-' . $day));
        }
    }

    public function otrosArchivos($aduana, $directory) {
        $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory), RecursiveIteratorIterator::SELF_FIRST);
        foreach ($objects as $name => $object) {
            if (preg_match('/.svn/', $name) || preg_match('/^error/i', $name)) {
                continue;
            }
            $filename = $object->getFilename();
            if (file_exists($object->getPathname()) && !is_dir($object->getPathname())) {
                if (preg_match('/^E[0-9]{7}.[0-9]{3}/i', $filename) || preg_match('/^X[0-9]{7}.[0-9]{3}/i', $filename) || preg_match('/^k[0-9]{7}.[0-9]{3}/', $filename)) {
                    $tmp['year'] = substr($directory, -4);
                    $tmp['patente'] = (int) substr($filename, 1, 4);
                    $tmp["aduana"] = $aduana;
                    $tmp['diaJuliano'] = substr($filename, -3);
                    $this->_copyM3File($object->getPathname(), $tmp["year"], $tmp["patente"], $tmp['aduana'], $tmp["diaJuliano"], basename($object->getPathname()));
                    if(file_exists($object->getPathname())) {
                        unlink($object->getPathname());
                    }
                    unset($tmp);
                }
            }
        }
    }
    
    public function fileToArray($contenido, $registro = null, $pedimento = null) {
        try {
            $array = preg_split('/\r\n|\r|\n/', $contenido);
            $content = array();
            foreach ($array as $line) {
                $key = substr($line, 0, 3);
                if ($key != '') {
                    if (key_exists($key, $content)) {
                        if (isset($pedimento) && strlen($pedimento) == 7) {
                            if (strpos($line, "|" . $pedimento . "|") > 0) {
                                $content[$key][] = trim($line);
                            }
                        } else {
                            $content[$key][] = trim($line);
                        }
                    } else {
                        if (isset($pedimento) && strlen($pedimento) == 7) {
                            if (strpos($line, "|" . $pedimento . "|") > 0) {
                                $content[$key][] = trim($line);
                            }
                        } else {
                            $content[$key][] = trim($line);
                        }
                    }
                    if ($key == "801") {
                        $content[801][] = trim($line);
                    }
                }
            }
            if (isset($registro)) {
                if (isset($content[$registro])) {
                    return $content[$registro];
                }
                return;
            }
            return $content;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
    
    public function arrayM3To($array) {
        if (isset($array) && is_array($array)) {
            if (isset($array[500])) {
                $arr = $this->_explode($array[500][0]);
                $this->_array["pedimento"] = $arr[3];
                $this->_array["aduana"] = $arr[4];
                $this->_array["patente"] = $arr[2];
            }
            if (isset($array[501])) {
                $arr = $this->_explode($array[501][0]);
                $this->_array["aduanaDespacho"] = $arr[3];
                $this->_array["tipoOperacion"] = ($arr[4] == '1') ? "IMPO" : "EXPO";
                $this->_array["clavePedimento"] = $arr[5];
                $this->_array["aduanaEntrada"] = $arr[6];
                $this->_array["curp"] = ($arr[7] != "") ? $arr[7] : null;
                $this->_array["rfcCliente"] = $arr[8];
                $this->_array["nombreCliente"] = $arr[21];
                $this->_array["curpAgente"] = $arr[9];
                $this->_array["tipoCambio"] = $arr[10];
                $this->_array["fletes"] = $arr[11];
                $this->_array["seguros"] = $arr[12];
                $this->_array["embalajes"] = $arr[13];
                $this->_array["otrosIncrementables"] = $arr[14];
                $this->_array["pesoBruto"] = $arr[16];
                $this->_array["transporteSalida"] = $arr[17];
                $this->_array["transporteArribo"] = $arr[18];
                $this->_array["transporteEntrada"] = $arr[19];
                $this->_array["destino"] = $arr[20];
                $this->_array["calle"] = $arr[22];
                $this->_array["numExterior"] = $arr[24];
                $this->_array["numInterior"] = ($arr[23] != "") ? $arr[23] : null;
                $this->_array["municipio"] = $arr[26];
                $this->_array["estado"] = ($arr[27] != "") ? $arr[27] : null;
                $this->_array["codigoPostal"] = $arr[25];
                $this->_array["pais"] = ($arr[28] != "") ? $arr[28] : null;
                $this->_array["rfcSociedad"] = ($arr[29] != "") ? $arr[29] : null;
            }
            if (isset($array[505])) {
                foreach ($array[505] as $ide) {
                    $arr = $this->_explode($ide);
                    $this->_array["facturas"][] = array(
                        "fechaFactura" => $arr[2],
                        "numFactura" => $arr[3],
                        "incoterm" => $arr[4],
                        "moneda" => $arr[5],
                        "valorDolares" => $arr[6],
                        "valorComercial" => $arr[7],
                        "entidad" => ($arr[9] != "") ? $arr[9] : null,
                        "identificador" => ($arr[10] != "") ? $arr[10] : null,
                        "proveedor" => ($arr[11] != "") ? $arr[11] : null,
                        "pais" => $arr[8],
                        "domicilio" => array(
                            "calle" => ($arr[12] != "") ? $arr[12] : null,
                            "numExterior" => ($arr[14] != "") ? $arr[14] : null,
                            "numInterior" => ($arr[13] != "") ? $arr[13] : null,
                            "municipio" => ($arr[16] != "") ? $arr[16] : null,
                            "estado" => ($arr[17] != "") ? $arr[17] : null,
                            "codigoPostal" => ($arr[15] != "") ? $arr[15] : null,
                        )
                    );
                }
            }
            if (isset($array[601])) {
                foreach ($array[601] as $ide) {
                    $arr = $this->_explode($ide);
                    $this->_array["previoConsolidado"][] = array(
                        "patente" => $arr[1],
                        "aduana" => $arr[3],
                        "pedimento" => $arr[2],
                        "clavePedimento" => $arr[4],
                        "tipoOperacion" => $arr[5],
                        "curpImportador" => ($arr[6] != "") ? $arr[6] : null,
                        "rfcImportador" => $arr[7],
                        "curpAgente" => $arr[8],
                        "destino" => $arr[9],
                        "rfcSociedad" => $arr[10],
                    );
                }
            }
            if (isset($array[507])) {
                foreach ($array[507] as $ide) {
                    $arr = $this->_explode($ide);
                    $this->_array["identificadores"][] = array(
                        "clave" => $arr[2],
                        "complemento1" => ($arr[3] != "") ? $arr[3] : null,
                        "complemento2" => ($arr[4] != "") ? $arr[4] : null,
                        "complemento3" => ($arr[5] != "") ? $arr[5] : null,
                    );
                }
            }
            if (isset($array[506])) {
                foreach ($array[506] as $ide) {
                    $arr = $this->_explode($ide);
                    $this->_array["fechas"][$arr[2]] = $arr[3];
                }
            }
            if (isset($array[509])) {
                foreach ($array[509] as $ide) {
                    $arr = $this->_explode($ide);
                    $this->_array["tasas"][$arr[2]] = array(
                        "tasaContribucion" => $arr[3],
                        "tipoTasa" => $arr[4],
                    );
                }
            }
            if (isset($array[510])) {
                foreach ($array[510] as $ide) {
                    $arr = $this->_explode($ide);
                    $this->_array["contribuciones"][$arr[2]] = array(
                        "formaPago" => $arr[3],
                        "importe" => $arr[4],
                    );
                }
            }
            if (isset($array[511])) {
                foreach ($array[511] as $ide) {
                    $arr = $this->_explode($ide);
                    $this->_array["observaciones"][] = array(
                        "texto" => $arr[3],
                    );
                }
            }
            if (isset($array[551])) {
                foreach ($array[551] as $ide) {
                    $arr = $this->_explode($ide);
                    $this->_array["partidas"][] = array(
                        "fraccion" => $arr[2],
                        "numPartida" => $arr[3],
                        "subdivision" => ($arr[4] != "") ? $arr[4] : null,
                        "descripcion" => $arr[5],
                        "precioUnitario" => $arr[6],
                        "valorAduana" => $arr[7],
                        "valorComercial" => $arr[8],
                        "valorDolares" => $arr[9],
                        "cantidadUmc" => $arr[10],
                        "umc" => $arr[11],
                        "cantidadUmt" => $arr[12],
                        "umt" => $arr[13],
                        "valorAgregado" => ($arr[14] != "") ? $arr[14] : null,
                        "vinculacion" => $arr[15],
                        "metodoValoracion" => ($arr[16] != "") ? $arr[16] : null,
                        "codigoProducto" => ($arr[17] != "") ? $arr[17] : null,
                        "marca" => ($arr[18] != "") ? $arr[18] : null,
                        "modelo" => ($arr[19] != "") ? $arr[19] : null,
                        "paisOrigen" => $arr[20],
                        "paisVendedor" => $arr[21],
                        "entidadOrigen" => ($arr[22] != "") ? $arr[22] : null,
                        "entidadDestino" => ($arr[23] != "") ? $arr[23] : null,
                        "entidadComprador" => ($arr[24] != "") ? $arr[24] : null,
                        "entidadVendedor" => ($arr[25] != "") ? $arr[25] : null,
                    );
                }
            }
            if (isset($array[554])) {
                foreach ($array[554] as $ide) {
                    $arr = $this->_explode($ide);
                    $this->_array["idenPartidas"][$arr[3]][] = array(
                        "fraccion" => $arr[2],
                        "numPartida" => $arr[3],
                        "clave" => $arr[4],
                        "complemento1" => ($arr[5] != "") ? $arr[5] : null,
                        "complemento2" => ($arr[6] != "") ? $arr[6] : null,
                        "complemento3" => ($arr[7] != "") ? $arr[7] : null,
                    );
                }
            }
            if (isset($array[558])) {
                foreach ($array[558] as $ide) {
                    $arr = $this->_explode($ide);
                    $this->_array["obsPartidas"][$arr[3]][] = array(
                        "fraccion" => $arr[2],
                        "numPartida" => $arr[3],
                        "secuencia" => $arr[4],
                        "observacion" => $arr[5],
                    );
                }
            }
            if (isset($array[556])) {
                foreach ($array[556] as $ide) {
                    $arr = $this->_explode($ide);
                    $this->_array["tasasPartidas"][$arr[3]][] = array(
                        "fraccion" => $arr[2],
                        "numPartida" => $arr[3],
                        "clave" => $arr[4],
                        "tasaGravamen" => $arr[5],
                        "tipoTasa" => $arr[6],
                    );
                }
            }
            if (isset($array[557])) {
                foreach ($array[557] as $ide) {
                    $arr = $this->_explode($ide);
                    $this->_array["contribucionesPartidas"][$arr[3]][] = array(
                        "fraccion" => $arr[2],
                        "numPartida" => $arr[3],
                        "clave" => $arr[4],
                        "formaPago" => $arr[5],
                        "importe" => $arr[6],
                    );
                }
            }
            if (isset($array[801])) {
                $arr = $this->_explode($array[801][0]);
                $this->_array["archivo"] = $arr[1];
            }
            if (isset($array[800])) {
                $arr = $this->_explode($array[800][0]);
                $this->_array["firma"][] = array(
                    "tipoFigura" => $arr[2],
                    "firma" => $arr[3],
                    "numeroSerie" => $arr[4],
                );
            }
            if (isset($array[502])) {
                $arr = $this->_explode($array[502][0]);
                $this->_array["transportes"] = array(
                    "rfc" => $arr[2],
                    "curp" => ($arr[3] != "") ? $arr[3] : null,
                    "identificacion" => ($arr[6] != "") ? $arr[6] : null,
                    "nombre" => ($arr[4] != "") ? $arr[4] : null,
                    "pais" => ($arr[5] != "") ? $arr[5] : null,
                    "bultos" => ($arr[7] != "") ? $arr[7] : null,
                    "domicilio" => ($arr[8] != "") ? $arr[8] : null,
                );
            }
            if (isset($array[503])) {
                $arr = $this->_explode($array[503][0]);
                $this->_array["guias"] = array(
                    "tipoGuia" => $arr[3],
                    "guiaManifiesto" => $arr[2],
                );
            }
            if (isset($array[504])) {
                $arr = $this->_explode($array[504][0]);
                $this->_array["contenedores"] = array(
                    "numero" => $arr[2],
                    "tipoContenedor" => $arr[3],
                );
            }
            if (isset($array[701])) {
                $arr = $this->_explode($array[701][0]);
                $this->_array["retificacion"] = array(
                    "patente" => $arr[1],
                    "aduana" => $arr[3],
                    "pedimento" => $arr[2],
                    "clave" => $arr[4],
                    "fechaPago" => $arr[5],
                    "patenteOriginal" => $arr[7],
                    "aduanaOriginal" => $arr[8],
                    "pedimentoOriginal" => $arr[6],
                    "claveOriginal" => $arr[9],
                    "fechaOriginal" => $arr[10],
                );
            }
            if (isset($array[702])) {
                $arr = $this->_explode($array[702][0]);
                $this->_array["diferenciasContribuciones"] = array(
                    "clave" => $arr[2],
                    "formaPago" => $arr[3],
                    "importe" => $arr[4],
                );
            }
            if (isset($array[512])) {
                foreach ($array[512] as $ide) {
                    $arr = $this->_explode($ide);
                    $this->_array["descargos"][] = array(
                        "patente" => $arr[2],
                        "aduana" => $arr[4],
                        "pedimento" => $arr[3],
                        "clave" => $arr[5],
                        "fecha" => $arr[6],
                        "fraccion" => $arr[7],
                        "umc" => $arr[8],
                        "cantidadUmt" => $arr[9],
                    );
                }
            }
            if (isset($array[516])) {
                foreach ($array[516] as $ide) {
                    $arr = $this->_explode($ide);
                    $this->_array["candados"][] = array(
                        "identificador" => $arr[2],
                        "numero" => $arr[3],
                    );
                }
            }
        } else {
            return;
        }
    }

    protected function _explode($line) {
        $arr = explode("|", $line);
        if (is_array($arr)) {
            return $arr;
        }
        return;
    }

}
