<?php

/**
 * Administración, validación de archivos M3
 *
 * @author Jaime E. Valdez <jvaldezch@gmail.com>
 */
class OAQ_ArchivosValidacion {

    protected $_dir;
    protected $_outputDir;
    protected $_patente;
    protected $_contenido;
    protected $_aduana;
    protected $_data;

    function set_dir($_dir) {
        $this->_dir = $_dir;
    }

    function set_patente($_patente) {
        $this->_patente = $_patente;
    }

    function set_aduana($_aduana) {
        $this->_aduana = $_aduana;
    }

    function set_outputDir($_outputDir) {
        $this->_outputDir = $_outputDir;
    }

    function get_data() {
        return $this->_data;
    }

    function set_contenido($_contenido) {
        $this->_contenido = $_contenido;
    }

    function __construct() {
        $this->_data = array();
    }

    protected function JDtoISO8601($JD) {
        if ($JD <= 1721425)
            $JD += 365;
        list($month, $day, $year) = explode('/', jdtogregorian($JD));
        return sprintf('%+05d-%02d-%02d', $year, $month, $day);
    }

    public function obtenerTodos() {
        try {
            $mapper = new Automatizacion_Model_ArchivosValidacionMapper();
            if (isset($this->_aduana) && isset($this->_aduana) && isset($this->_patente)) {
                if (file_exists($this->_dir) && is_readable($this->_dir)) {
                    $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->_dir), RecursiveIteratorIterator::SELF_FIRST);                    
                    foreach ($objects as $name => $object) {
                        if (preg_match("/.svn/", $name)) {
                            continue;
                        }
                        if (file_exists($object->getPathname()) && !is_dir($object->getPathname())) {
                            $filename = $object->getFilename();
                            if (preg_match("/^error/i", $filename)) {
                                continue;
                            }
                            $explode = explode(".", $filename);
                            $match = array();
                            $operacion = null;
                            $archivoNum = null;
                            if (isset($explode[0])) {
                                if (preg_match("/([0-9]+)/", $explode[0], $match)) {
                                    if (isset($match[0])) {
                                        $archivoNum = (int) $match[0];
                                    }
                                }
                            }
                            if (isset($explode[1])) {
                                if (preg_match("/([0-9]+)/", $explode[1], $match)) {
                                    if (isset($match[0])) {
                                        $operacion = (int) $match[0];
                                    }
                                }
                            }
                            $table = new Automatizacion_Model_Table_ArchivosValidacion();
                            $table->setPatente($this->_patente);
                            $table->setAduana($this->_aduana);
                            $table->setArchivoNombre($object->getFilename());
                            $table->setHash(sha1_file($object->getPathname()));
                            $mapper->find($table);
                            if (null !== ($table->getId())) {
                                unset($table);
                                continue;
                            }
                            unset($table);
                            $tmp = array(
                                "patente" => $this->_patente,
                                "aduana" => $this->_aduana,
                                "archivo" => $object->getPathname(),
                                "archivoNombre" => $object->getFilename(),
                                "analizado" => 0,
                                "contenido" => base64_encode(file_get_contents($object->getPathname())),
                                "usuario" => "cron",
                                "diaJuliano" => $operacion,
                                "archivoNum" => $archivoNum,
                                "hash" => sha1_file($object->getPathname()),
                                "creado" => date("Y-m-d H:i:s", filemtime($object->getPathname())),
                            );
                            $tmp["tipo"] = $this->tipoArchivo($filename);
                            /*if (preg_match("/^M/i", $filename) && preg_match("/.err$/", $filename)) {
                                $tmp["tipo"] = "validacion";
                            }
                            if (preg_match("/^k/i", $filename) && preg_match("/.[0-9]{3}$/i", $filename)) {
                                $tmp["tipo"] = "resultado";
                            }
                            if (preg_match("/^m/i", $filename) && preg_match("/.[0-9]{3}$/i", $filename)) {
                                $tmp["tipo"] = "m3";
                            }
                            if (preg_match("/^e/i", $filename) && preg_match("/.[0-9]{3}$/i", $filename)) {
                                $tmp["tipo"] = "pago";
                            }
                            if (preg_match("/a/i", $filename) && preg_match("/.[0-9]{3}$/i", $filename)) {
                                $tmp["tipo"] = "pagado";
                            }
                            if (preg_match("/^x/i", $filename) && preg_match("/.[0-9]{3}$/i", $filename)) {
                                $tmp["tipo"] = "ok";
                            }*/
                            if (isset($tmp["tipo"])) {
                                $this->_data[] = $tmp;
                            }
                            unset($tmp);
                        }
                    }
                }
            } else {
                throw new Exception("Not proper setup!");
            }
        } catch (Exception $ex) {
            throw new Exception("Exception found: " . $ex->getMessage());
        }
    }
    
    public function tipoArchivo($filename) {
        if (preg_match("/m[0-9]{7}.err/i", $filename)) {
            return "validacion";            
        }
        if (preg_match("/k[0-9]{7}.[0-9]{3}/i", $filename)) {
            return "resultado";            
        }
        if (preg_match("/m[0-9]{7}.[0-9]{3}/i", $filename)) {
            return "m3";
        }
        if (preg_match("/e[0-9]{7}.[0-9]{3}/i", $filename)) {
            return "pago";
        }
        if (preg_match("/a[0-9]{7}.[0-9]{3}/i", $filename)) {
            return "pagado";
        }
        if (preg_match("/x[0-9]{7}.[0-9]{3}/i", $filename)) {
            return "ok";
        }
        return;
    }

    protected function _array($contenido) {
        return preg_split('/\r\n|\r|\n/', $contenido);
    }

    protected function _changeDate($fecha, $short = null) {
        $day = substr($fecha, 0, 2);
        $month = substr($fecha, 2, 2);
        $year = substr($fecha, 4, 4);
        if (!$short) {
            return date('Y-m-d H:i:s', strtotime($year . '-' . $month . '-' . $day));
        } else {
            return date('Y-m-d', strtotime($year . '-' . $month . '-' . $day));
        }
    }

    public function extraerPedimento($pedimento) {
        try {
            if (!isset($this->_contenido)) {
                throw new Exception("Content not set!");
            } else {
                $array = preg_split('/\r\n|\r|\n/', $this->_contenido);
            }
            $this->_data = array();
            if (isset($array) && !empty($array)) {
                $_500 = array_values(preg_grep("/^500\|[0-9]{1}\|[0-9]{4}\|{$pedimento}\|[0-9]{3}/i", $array));
                $_501 = array_values(preg_grep("/^501\|[0-9]{4}\|{$pedimento}\|[0-9]{3}/i", $array));
                $_506 = array_values(preg_grep("/^506\|{$pedimento}\|/i", $array));
                $paid = array_values(preg_grep("/^506\|{$pedimento}\|2/i", $array));
                $_601 = array_values(preg_grep("/^601\|/i", $array));                
                if (!empty($_500)) {
                    $info = explode('|', reset($_500));
                    $tmp['aduana'] = (int) $info[4];
                    $tmp['patente'] = (int) $info[2];
                }
                if (!empty($paid)) {
                    $fecha = explode('|', reset($paid));
                    $fechas = array();
                    foreach ($_506 as $fecha) {
                        $lineFecha = explode("|", $fecha);
                        if (!isset($fechas[$lineFecha[1]])) {
                            $fechas[$lineFecha[1]][(int) $lineFecha[2]] = $this->_changeDate($lineFecha[3]);
                        } else {
                            $fechas[$lineFecha[1]][(int) $lineFecha[2]] = $this->_changeDate($lineFecha[3]);
                        }
                        unset($fecha);
                    }
                }
                foreach ($_500 as $k => $ped) {
                    $reg500 = explode("|", $ped);
                    $reg501 = (isset($_501)) ? explode("|", $_501[$k]) : null;
                    $reg601 = (isset($_601) && !empty($_601)) ? explode("|", $_601[$k]) : null;
                    if (isset($reg501)) {
                        $cveDoc = $reg501[5];
                        $rfc = $reg501[8];
                    } elseif (!isset($reg501) && isset($reg601)) {
                        $cveDoc = $reg601[4];
                        $rfc = $reg601[7];
                    } else {
                        $cveDoc = null;
                        $rfc = null;
                    }
                    if (isset($_501)) {
                        if (isset($reg501[29])) {
                            $rfcSociedad = $reg501[29];
                        } else {
                            $rfcSociedad = null;
                        }
                        if (isset($reg501[9])) {
                            $curp = $reg501[9];
                        } else {
                            $curp = null;
                        }
                    }
                    if (isset($_601) && isset($reg601)) {
                        if (isset($reg601[10])) {
                            $rfcSociedad = $reg601[10];
                        } else {
                            $rfcSociedad = null;
                        }
                        if (isset($reg601[8])) {
                            $curp = $reg601[8];
                        } else {
                            $curp = null;
                        }
                        $consolidado = true;
                        $cveDoc = $reg601[4];
                        $rfc = $reg601[7];
                    }
                    $tmp = array(
                        'patente' => (int) $reg500[2],
                        'aduana' => (int) $reg500[4],
                        'pedimento' => str_pad($reg500[3], 7, '0', STR_PAD_LEFT),
                        'tipoMovimiento' => (int) $reg500[1],
                        'pedimentoDesistir' => ($reg500[5] != '') ? $reg500[5] : null,
                        'cveDoc' => $cveDoc,
                        'rfcCliente' => $rfc,
                        'rfcSociedad' => $rfcSociedad,
                        'curpAgente' => $curp,
                        'fechaEntrada' => (isset($fechas)) ? isset($fechas[(int) $reg500[3]][1]) ? $fechas[(int) $reg500[3]][1] : null : null,
                        'fechaPago' => (isset($fechas)) ? isset($fechas[(int) $reg500[3]][2]) ? $fechas[(int) $reg500[3]][2] : null : null,
                        'fechaExtraccion' => (isset($fechas)) ? isset($fechas[(int) $reg500[3]][3]) ? $fechas[(int) $reg500[3]][3] : null : null,
                        'fechaPresentacion' => (isset($fechas)) ? isset($fechas[(int) $reg500[3]][5]) ? $fechas[(int) $reg500[3]][5] : null : null,
                        'fechaUsaCan' => (isset($fechas)) ? isset($fechas[(int) $reg500[3]][6]) ? $fechas[(int) $reg500[3]][6] : null : null,
                        'fechaOriginal' => (isset($fechas)) ? isset($fechas[(int) $reg500[3]][7]) ? $fechas[(int) $reg500[3]][7] : null : null,
                        'consolidado' => (isset($consolidado)) ? 1 : 0,
                        'remesa' => null,
                    );
                    $this->_data = $tmp;
                    return;
                }
            }
        } catch (Exception $ex) {
            throw new Exception("Exception found: " . $ex->getMessage());
        }
    }
    
    public function analizaArchivoPedimento() {
        try {
            if (!isset($this->_contenido)) {
                throw new Exception("Content not set!");
            } else {
                $array = preg_split('/\r\n|\r|\n/', $this->_contenido);
            }
            $this->_data = array();
            if (isset($array) && !empty($array)) {
                $_500 = array_values(preg_grep('/^500\|[0-9]{1}\|[0-9]{4}\|[0-9]{7}\|[0-9]{3}/i', $array));
                $_501 = array_values(preg_grep('/^501\|[0-9]{4}\|[0-9]{7}\|[0-9]{3}/i', $array));
                $_506 = array_values(preg_grep('/^506\|[0-9]{7}\|/i', $array));
                $paid = array_values(preg_grep('/^506\|[0-9]{7}\|2/i', $array));
                $_601 = array_values(preg_grep('/^601\|/i', $array));
                $_800 = array_values(preg_grep('/^800\|/i', $array));
                $_801 = array_values(preg_grep('/^801\|/i', $array));
                if (isset($_800) && !empty($_800)) {
                    foreach ($_800 as $i800) {
                        $ex800 = explode("|", $i800);
                        if (isset($ex800[1])) {
                            $reg800[$ex800[1]] = $ex800[3];
                        }
                    }
                }
                if (isset($_801[0]) && !empty($_801[0])) {
                    $ex801 = explode("|", $_801[0]);
                }
                if (!empty($_500)) {
                    $info = explode('|', reset($_500));
                    $tmp['aduana'] = (int) $info[4];
                    $tmp['patente'] = (int) $info[2];
                }
                if (!empty($paid)) {
                    $fecha = explode('|', reset($paid));
                    $fechas = array();
                    foreach ($_506 as $fecha) {
                        $lineFecha = explode("|", $fecha);
                        if (!isset($fechas[$lineFecha[1]])) {
                            $fechas[$lineFecha[1]][(int) $lineFecha[2]] = $this->_changeDate($lineFecha[3]);
                        } else {
                            $fechas[$lineFecha[1]][(int) $lineFecha[2]] = $this->_changeDate($lineFecha[3]);
                        }
                        unset($fecha);
                    }
                }
                foreach ($_500 as $k => $ped) {
                    $reg500 = explode("|", $ped);
                    $reg501 = (isset($_501)) ? explode("|", $_501[$k]) : null;
                    $reg601 = (isset($_601) && !empty($_601)) ? explode("|", $_601[$k]) : null;
                    if (isset($reg501)) {
                        $cveDoc = $reg501[5];
                        $rfc = $reg501[8];
                    } elseif (!isset($reg501) && isset($reg601)) {
                        $cveDoc = $reg601[4];
                        $rfc = $reg601[7];
                    } else {
                        $cveDoc = null;
                        $rfc = null;
                    }
                    if (isset($_501)) {
                        if (isset($reg501[29])) {
                            $rfcSociedad = $reg501[29];
                        } else {
                            $rfcSociedad = null;
                        }
                        if (isset($reg501[9])) {
                            $curp = $reg501[9];
                        } else {
                            $curp = null;
                        }
                    }
                    if (isset($_601) && isset($reg601)) {
                        if (isset($reg601[10])) {
                            $rfcSociedad = $reg601[10];
                        } else {
                            $rfcSociedad = null;
                        }
                        if (isset($reg601[8])) {
                            $curp = $reg601[8];
                        } else {
                            $curp = null;
                        }
                        $consolidado = true;
                        $cveDoc = $reg601[4];
                        $rfc = $reg601[7];
                    }
                    $tmp = array(
                        'archivoNombre' => isset($ex801[1]) ? $ex801[1] : null,
                        'patente' => (int) $reg500[2],
                        'aduana' => (int) $reg500[4],
                        'pedimento' => str_pad($reg500[3], 7, '0', STR_PAD_LEFT),
                        'tipoMovimiento' => (int) $reg500[1],
                        'pedimentoDesistir' => ($reg500[5] != '') ? $reg500[5] : null,
                        'cveDoc' => $cveDoc,
                        'rfcCliente' => $rfc,
                        'rfcSociedad' => $rfcSociedad,
                        'curpAgente' => $curp,
                        'fechaEntrada' => (isset($fechas)) ? isset($fechas[(int) $reg500[3]][1]) ? $fechas[(int) $reg500[3]][1] : null : null,
                        'fechaPago' => (isset($fechas)) ? isset($fechas[(int) $reg500[3]][2]) ? $fechas[(int) $reg500[3]][2] : null : null,
                        'fechaExtraccion' => (isset($fechas)) ? isset($fechas[(int) $reg500[3]][3]) ? $fechas[(int) $reg500[3]][3] : null : null,
                        'fechaPresentacion' => (isset($fechas)) ? isset($fechas[(int) $reg500[3]][5]) ? $fechas[(int) $reg500[3]][5] : null : null,
                        'fechaUsaCan' => (isset($fechas)) ? isset($fechas[(int) $reg500[3]][6]) ? $fechas[(int) $reg500[3]][6] : null : null,
                        'fechaOriginal' => (isset($fechas)) ? isset($fechas[(int) $reg500[3]][7]) ? $fechas[(int) $reg500[3]][7] : null : null,
                        'consolidado' => (isset($consolidado)) ? 1 : 0,
                        'remesa' => null,
                        'firmaDigital' => (isset($reg800[$reg500[3]])) ? $reg800[$reg500[3]] : null,
                    );
                    $this->_data[] = $tmp;
                }
            }
        } catch (Exception $ex) {
            throw new Exception("Exception found: " . $ex->getMessage());
        }
    }

    public function analizaArchivoPago() {
        try {
            if (!isset($this->_contenido)) {
                throw new Exception("Content not set!");
            } else {
                $array = preg_split('/\r\n|\r|\n/', $this->_contenido);
            }
            $this->_data = array();
            if (isset($array) && !empty($array)) {
                $lines = array_values(preg_grep('/^3/i', $array));
                if (!empty($lines)) {
                    foreach ($lines as $line) {
                        if (preg_match('/^3/i', $line)) {
                            $parts = explode(" ", preg_replace('/\s\s+/', ' ', $line));
                            if (count($parts) == 1) {
                                $tmp = array(
                                    "aduana" => (int) substr($parts[0], 2, 2),
                                    "patente" => (int) substr($parts[0], 4, 4),
                                    "pedimento" => str_pad(substr($parts[0], 8, 7), 7, '0', STR_PAD_LEFT),
                                    "rfcImportador" => substr($parts[0], 15, 12),
                                    "caja" => (int) substr($parts[0], 13, 2),
                                    "numOperacion" => substr($parts[0], 30, 10),
                                    "firmaBanco" => substr($parts[0], 40, 10),
                                    "error" => 0,
                                    "fecha" => $this->_changeDate(substr($parts[0], 50, 8)),
                                    "hora" => substr($parts[0], 58, 8),
                                    "fechaPago" => $this->_changeDate(substr($parts[0], 50, 8), true) . ' ' . substr($parts[0], 58, 8),
                                );
                            } elseif (count($parts) > 1) {
                                $tmp = array(
                                    "aduana" => (int) substr($parts[0], 2, 2),
                                    "patente" => (int) substr($parts[0], 4, 4),
                                    "pedimento" => str_pad(substr($parts[0], 8, 7), 7, '0', STR_PAD_LEFT),
                                    "rfcImportador" => substr($parts[0], 15, 12),
                                    "caja" => (isset($parts[2])) ? substr($parts[1], 0, 2) : substr($parts[1], 0, 2),
                                    "numOperacion" => (isset($parts[2])) ? substr($parts[1], 2, 10) : substr($parts[1], 2, 10),
                                    "firmaBanco" => (isset($parts[2])) ? substr($parts[2], 0, 10) : substr($parts[1], 12, 10),
                                    "error" => 0,
                                    "fecha" => (isset($parts[2])) ? $this->_changeDate(substr($parts[2], 10, 8)) : $this->_changeDate(substr($parts[1], 22, 8)),
                                    "hora" => (isset($parts[2])) ? substr($parts[2], 18, 8) : substr($parts[1], 30, 8),
                                    "fechaPago" => (isset($parts[2])) ? $this->_changeDate(substr($parts[2], 10, 8), true) . ' ' . substr($parts[2], 18, 8) : $this->_changeDate(substr($parts[1], 22, 8), true) . ' ' . substr($parts[1], 30, 8),
                                );
                                if ($tmp["caja"] == '13') {
                                    $tmp["numOperacion"] = substr($parts[2], 0, 7);
                                    $tmp["firmaBanco"] = substr($parts[2], 7, 10);
                                    $tmp["fecha"] = $this->_changeDate(substr($parts[2], 17, 8));
                                    $tmp["hora"] = substr($parts[2], 25, 8);
                                    $tmp["fechaPago"] = $this->_changeDate(substr($parts[2], 17, 8), true) . ' ' . substr($parts[2], 25, 8);
                                }
                                if ($tmp["caja"] == '24') {
                                    $tmp["numOperacion"] = substr($parts[1], 2, 10);
                                    $tmp["firmaBanco"] = substr($parts[1], -8);
                                    $tmp["fecha"] = $this->_changeDate(substr($parts[2], 0, 8));
                                    $tmp["hora"] = substr($parts[2], 8, 8);
                                    $tmp["fechaPago"] = $this->_changeDate(substr($parts[2], 0, 8), true) . ' ' . substr($parts[2], 8, 8);
                                }
                                if ($tmp["caja"] == '64') {
                                    $tmp["numOperacion"] = substr($parts[1], 2, 10);
                                    $tmp["firmaBanco"] = substr($parts[1], -8);
                                    $tmp["fecha"] = $this->_changeDate(substr($parts[2], 0, 8));
                                    $tmp["hora"] = substr($parts[2], 8, 8);
                                    $tmp["fechaPago"] = $this->_changeDate(substr($parts[2], 0, 8), true) . ' ' . substr($parts[2], 8, 8);
                                }
                            } else {
                                continue;
                            }
                            if (isset($tmp) && (isset($tmp["firmaBanco"]) && isset($tmp["numOperacion"]))) {
                                $this->_data[] = $tmp;
                                unset($tmp);
                            }
                        }
                    }
                }
                $lines = array_values(preg_grep('/^4/i', $array));
                if (!empty($lines)) {
                    foreach ($lines as $line) {
                        $tmp = array(
                            "aduana" => (int) substr($line, 2, 2),
                            "patente" => (int) substr($line, 4, 4),
                            "pedimento" => str_pad(substr($line, 8, 7), 7, '0', STR_PAD_LEFT),
                            "firmaBanco" => substr($line, 15, 3),
                            "rfcImportador" => "ND",
                            "fecha" => date('Y-m-d'),
                            "hora" => date('H:i:s'),
                            "error" => 1,
                            "fechaPago" => date('Y-m-d H:i:s'),
                        );
                        if (isset($tmp)) {
                            $this->_data[] = $tmp;
                            unset($tmp);
                        }
                    }
                }
            }
        } catch (Exception $ex) {
            throw new Exception("Exception found: " . $ex->getMessage());
        }
    }

    public function analizaArchivoFirmas() {
        try {
            if (!isset($this->_contenido)) {
                throw new Exception("Content not set!");
            } else {
                $array = preg_split('/\r\n|\r|\n/', $this->_contenido);
            }
            $this->_data = array();
            if (isset($array) && !empty($array)) {
                $lines = array_values(preg_grep('/^F[0-9]{7}/i', $array));
                if (empty($lines)) {
                    $lines = array_values(preg_grep('/^E[0-9]{7}/i', $array));
                }
                if (!empty($lines)) {
                    foreach ($lines as $line) {
                        if (preg_match('/^F/i', $line) && !preg_match('/BORRADO/i', trim($line))) {
                            $this->_data[] = array(
                                'patente' => (int) $this->_patente,
                                'pedimento' => str_pad(substr($line, 1, 7), 7, '0', STR_PAD_LEFT),
                                'firma' => substr($line, 8, 8)
                            );
                        }
                        if (preg_match('/^E/i', $line) && !preg_match('/BORRADO/i', trim($line))) {
                            $this->_data[] = array(
                                'patente' => (int) $this->_patente,
                                'pedimento' => str_pad(substr($line, 1, 7), 7, '0', STR_PAD_LEFT),
                                'firma' => substr($line, 8, strlen($line) - 7)
                            );
                        }
                        if (preg_match('/^F/i', $line) && preg_match('/BORRADO/i', trim($line))) {
                            $this->_data[] = array(
                                'patente' => (int) $this->_patente,
                                'pedimento' => str_pad(substr($line, 1, 7), 7, '0', STR_PAD_LEFT),
                                'firma' => "BORRADO"
                            );
                        }
                    }
                }
            }
        } catch (Exception $ex) {
            throw new Exception("Exception found: " . $ex->getMessage());
        }
    }
    
    /**
     * 
     * @param int $patente
     * @param int $aduana
     * @param string $path
     * @param string $filename
     * @param string $contenido
     * @param string $tipo
     * @param string $usuario
     * @return boolean
     */
    public function agregarArchivoValidacion($patente, $aduana, $path, $filename, $contenido, $tipo, $usuario) {
        $explode = explode(".", $filename);
        $match = array();
        $operacion = null;
        $archivoNum = null;
        if (isset($explode[0])) {
            if (preg_match("/([0-9]+)/", $explode[0], $match)) {
                if (isset($match[0])) {
                    $archivoNum = (int) $match[0];
                }
            }
        }
        if (isset($explode[1])) {
            if (preg_match("/([0-9]+)/", $explode[1], $match)) {
                if (isset($match[0])) {
                    $operacion = (int) $match[0];
                }
            }
        }
        $mapper = new Automatizacion_Model_ArchivosValidacionMapper();
        $table = new Automatizacion_Model_Table_ArchivosValidacion();
        $table->setPatente($patente);
        $table->setAduana($aduana);
        $table->setArchivo($path . DIRECTORY_SEPARATOR . $filename);
        $table->setArchivoNombre($filename);
        $table->setHash(sha1_file($path . DIRECTORY_SEPARATOR . $filename));
        $table->setContenido($contenido);
        $table->setUsuario(strtolower($usuario));
        $table->setAnalizado(0);
        $table->setTipo($tipo);
        $table->setDiaJuliano($operacion);
        $table->setArchivoNum($archivoNum);
        $table->setCreado(date("Y-m-d H:i:s", filemtime($path . DIRECTORY_SEPARATOR . $filename)));
        $mapper->find($table);
        if (null === ($table->getId())) {
            $mapper->save($table);
            return true;
        }
        return;
    }

    public function copiarArchivo($indir, $outdir, $filename) {
        if (!file_exists($outdir)) {
            mkdir($outdir, 0777, true);
        }
        if (file_exists($outdir) && is_readable($outdir)) {
            if (!file_exists($outdir . DIRECTORY_SEPARATOR . $filename)) {
                copy($indir . DIRECTORY_SEPARATOR . $filename, $outdir . DIRECTORY_SEPARATOR . $filename);
            }
            if (file_exists($outdir . DIRECTORY_SEPARATOR . $filename)) {
                return true;
            }
            return;
        } else {
            throw new Exception("Unable to write on directory {$outdir}");
        }
    }
    
    public function archivosDePedimento($patente, $aduana, $pedimento) {
        $arr = array();
        $val = new Automatizacion_Model_ArchivosValidacionPedimentosMapper();
        $validacion = $val->obtenerUltimo($patente, $pedimento, $aduana);
        if (isset($validacion) && !empty($validacion)) {
            $arr["validacion"] = $validacion;
        }
        $pre = new Automatizacion_Model_ArchivosValidacionFirmasMapper();
        $firma = $pre->obtenerUltimo($patente, $pedimento);
        if (isset($firma) && !empty($firma)) {
            $arr["firma"] = $firma;
        }
        $pag = new Automatizacion_Model_ArchivosValidacionPagosMapper();
        $pago = $pag->findFile($patente, $aduana, $pedimento);
        if (isset($pago) && !empty($pago)) {
            $arr["pago"] = $pago;
        }
        $pag = new Automatizacion_Model_ArchivosValidacionBancoMapper();
        $pago = $pag->findFile($patente, $aduana, $pedimento);
        if (isset($pago) && !empty($pago)) {
            $arr["banco"] = $pago;
        }
        return $arr;
    }

}
