<?php

/**
 * http://www.sitepoint.com/exploring-phps-imap-library-1/
 */
class OAQ_TerminalLogistics {

    protected $_filename;
    protected $_idEmail;
    protected $_db;
    protected $_sat;
    protected $_rows = array();
    protected $_total = 0;
    protected $_usuario;
    protected $_firephp;

    function set_filename($_filename) {
        $this->_filename = $_filename;
    }

    function set_usuario($_usuario) {
        $this->_usuario = $_usuario;
    }

    function get_idEmail() {
        return $this->_idEmail;
    }

    function set_idEmail($_idEmail) {
        $this->_idEmail = $_idEmail;
    }

    function get_rows() {
        return $this->_rows;
    }

    function get_total() {
        return $this->_total;
    }

    function __construct() {
        $this->_db = Zend_Registry::get("oaqintranet");
        $this->_sat = new OAQ_SATValidar();
        $this->_firephp = Zend_Registry::get("firephp");
    }
    
    public function createDir($base_dir, $email_date) {        
        $directory = $base_dir . DIRECTORY_SEPARATOR . date("Y", $email_date) . DIRECTORY_SEPARATOR . date("m", $email_date) . DIRECTORY_SEPARATOR . date("d", $email_date);
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }
        return $directory;
    }

    protected function _filters(Zend_Db_Select $sql, $filterRules, $fechaInicio = null, $fechaFin = null, $noData = null) {
        if (isset($filterRules)) {
            $filter = json_decode(html_entity_decode($filterRules));
            foreach ($filter AS $item) {
                if ($item->field == "pedimento" && $item->value != "") {
                    $sql->where("pedimento LIKE ?", "%" . trim($item->value) . "%");
                }
                if ($item->field == "referencia" && $item->value != "") {
                    $sql->where("referencia LIKE ?", "%" . trim($item->value) . "%");
                }
                if ($item->field == "folio" && $item->value != "") {
                    $sql->where("folio LIKE ?", "%" . trim($item->value) . "%");
                }
                if ($item->field == "fechaFolio" && $item->value != "") {
                    $sql->where("fechaFolio LIKE ?", "%" . trim($item->value) . "%");
                }
                if ($item->field == "rfcCliente" && $item->value != "") {
                    $sql->where("rfcCliente LIKE ?", "%" . trim($item->value) . "%");
                }
                if ($item->field == "rfcEmisor" && $item->value != "") {
                    $sql->where("rfcEmisor LIKE ?", "%" . trim($item->value) . "%");
                }
                if ($item->field == "rfcReceptor" && $item->value != "") {
                    $sql->where("rfcReceptor LIKE ?", "%" . trim($item->value) . "%");
                }
                if ($item->field == "guia" && $item->value != "") {
                    $sql->where("guia LIKE ?", "%" . trim($item->value) . "%");
                }
                if ($item->field == "nombreArchivo" && $item->value != "") {
                    $sql->where("nombreArchivo LIKE ?", "%" . trim($item->value) . "%");
                }
            }
        }
        if ($noData == true) {
            $sql->where("pedimento IS NULL");
        }
        if (isset($fechaInicio) && isset($fechaFin)) {
            $sql->where("creado >= ?", date("Y-m-d", strtotime($fechaInicio)) . " 00:00:00")
                    ->where("creado <= ?", date("Y-m-d", strtotime($fechaFin)) . " 23:59:59");
        }
    }

    public function total($filterRules = null, $fechaInicio = null, $fechaFin = null, $noData = null) {
        try {
            $sql = $this->_db->select()
                    ->from("repositorio_tmp_terminal", array("count(*) AS total"))
                    ->where("enviado IS NULL");
            $this->_filters($sql, $filterRules, $fechaInicio, $fechaFin, $noData);
            $stmt = $this->_db->fetchRow($sql);
            if ($stmt) {
                $this->_total = (int) $stmt["total"];
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function reporte($fechaInicio = null, $fechaFin = null, $noData = null) {
        try {
            $sql = $this->_db->select()
                    ->from("repositorio_tmp_terminal", array("*"))
                    ->where("enviado IS NULL")
                    ->where("creado >= ?", $fechaInicio)
                    ->where("creado <= ?", $fechaFin)
                    ->order(array('fechaFolio DESC', 'nombreArchivo ASC'));
            if ($noData == true) {
                $sql->where("pedimento IS NULL");
            }
            $stmt = $this->_db->fetchAll($sql);
            if ($stmt) {
                $this->_rows = $stmt;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function todas($page = null, $rows = null, $filterRules = null, $fechaInicio = null, $fechaFin = null, $noData = null) {
        try {
            $sql = $this->_db->select()
                    ->from("repositorio_tmp_terminal", array("*"))
                    ->where("enviado IS NULL")
                    ->order(array('creado DESC', 'nombreArchivo ASC'));
            if (isset($page) && isset($rows)) {
                $sql->limit($rows, ($page - 1) * $rows);
            }
            $this->total($filterRules, $fechaInicio, $fechaFin);
            $this->_filters($sql, $filterRules, $fechaInicio, $fechaFin, $noData);
            $stmt = $this->_db->fetchAll($sql);
            if ($stmt) {
                $this->_rows = $stmt;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    protected function _ubicacion($id) {
        try {
            $sql = $this->_db->select()
                    ->from("repositorio_tmp_terminal", array("ubicacion"))
                    ->where("id = ?", $id);
            $stmt = $this->_db->fetchRow($sql);
            if ($stmt) {
                return $stmt["ubicacion"];
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    protected function _buscar() {
        try {
            $sql = $this->_db->select()
                    ->from("repositorio_tmp_terminal", array("id"))
                    ->where("nombreArchivo = ?", basename($this->_filename));
            $stmt = $this->_db->fetchRow($sql);
            if ($stmt) {
                return $stmt["id"];
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    protected function _noHaSidoenviado($id) {
        try {
            $sql = $this->_db->select()
                    ->from("repositorio_tmp_terminal", array("id"))
                    ->where("id = ?", $id)
                    ->where("enviado IS NULL");
            $stmt = $this->_db->fetchRow($sql);
            if ($stmt) {
                return $id;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    protected function _actualizar($nombreArchivo, $arr) {
        try {
            $stmt = $this->_db->update("repositorio_tmp_terminal", $arr, array("nombreArchivo LIKE ?" => $nombreArchivo . "%"));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }
    
    protected function _actualizarUbicacion($id, $filename) {
        try {
            $arr = array(
                "idEmail" => $this->_idEmail,
                "nombreArchivo" => basename($filename),
                "ubicacion" => $filename,
                "creado" => date("Y-m-d H:i:s"),
                "usuario" => $this->_usuario,
            );
            $stmt = $this->_db->insert("repositorio_tmp_terminal", $arr, array("id = ?" => $id));
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    protected function _agregar($fecha = null) {
        try {
            $arr = array(
                "idEmail" => $this->_idEmail,
                "fechaEmail" => isset($fecha) ? $fecha : date("Y-m-d H:i:s"),
                "nombreArchivo" => basename($this->_filename),
                "ubicacion" => $this->_filename,
                "creado" => date("Y-m-d H:i:s"),
                "usuario" => $this->_usuario,
            );
            $stmt = $this->_db->insert("repositorio_tmp_terminal", $arr);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function descargar($id, $serverProtocol) {
        if (($filename = $this->_ubicacion($id))) {
            if (file_exists($filename)) {
                if (!is_file($filename)) {
                    header($serverProtocol . " 404 Not Found");
                    echo "File not found";
                } else if (!is_readable($filename)) {
                    header($serverProtocol . " 403 Forbidden");
                    echo "File not readable";
                }
                header($serverProtocol . " 200 OK");
                header("Content-Transfer-Encoding: application/pdf");
                header("Content-Length: " . filesize($filename));
                header("Content-Disposition: attachment; filename=\"" . basename($filename) . "\"");
                readfile($filename);
                return true;
            }
        }
        return;
    }

    public function verPdf($id) {
        if (($filename = $this->_ubicacion($id))) {
            if (file_exists($filename)) {
                $content = base64_encode(file_get_contents($filename));
                echo '<embed src="data:application/pdf;base64,' . $content . '" type="application/pdf" width="100%" height="100%">';
            }
        }
        return;
    }

    public function verXml($id) {
        if (($filename = $this->_ubicacion($id))) {
            if (file_exists($filename)) {
                $content = file_get_contents($filename);
                $dom = new DOMDocument;
                $dom->preserveWhiteSpace = FALSE;
                $dom->loadXML($content);
                $dom->formatOutput = TRUE;
                echo '<pre style="padding: 2px; line-height: 12px; margin: 0">
                <code style="padding: 2px; line-height: 12px">' . htmlentities($dom->saveXml()) . '</code>
                </pre>
                <script>
                    $(document).ready(function() {
                        $("pre code").each(function(i, block) {
                            hljs.highlightBlock(block);
                        });
                    });
                </script>';
            }
        }
        return;
    }

    protected function _buscarEnCliente() {
        
    }

    protected function _buscarEnRepositorio($patente, $aduana, $pedimento, $referencia) {
        $mppr = new Archivo_Model_RepositorioIndex();
        if (($id = $mppr->buscar($patente, $aduana, $referencia, $pedimento))) {
            return $id;
        }
        return;
    }

    protected function _sinAnalizar($limit, $id = null) {
        try {
            $sql = $this->_db->select()
                    ->from("repositorio_tmp_terminal", array("id"))
                    ->where("(folio IS NULL OR pedimento IS NULL) AND enviado IS NULL")
                    ->where("nombreArchivo REGEXP '.xml$'")
                    ->limit($limit);
            if (isset($id)) {
                $sql->where("id = ?", $id);
            }
            $stmt = $this->_db->fetchAll($sql);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function analizarPendientes($limit, $id = null) {
        $arr = $this->_sinAnalizar($limit, $id);
        if (!empty($arr)) {
            foreach ($arr as $item) {
                if (!($this->_noHaSidoenviado($item["id"]))) {
                    $this->analizar($item["id"]);
                }
            }
        }
    }

    public function analizar($id) {
        if (($filename = $this->_ubicacion($id))) {
            if (!file_exists($filename)) {
                return array("success" => false, "message" => "[{$id}] file doesn't exists! [$filename]");
            }
            if (preg_match('/.xml$/', $filename)) {
                $arr = $this->_analizarFactura($filename);
            }
            if (!empty($arr)) {
                $update = array(
                    "patente" => $arr["patente"],
                    "guia" => $arr["guia"],
                    "fechaFolio" => $arr["fechaFolio"],
                    "folio" => $arr["folio"],
                    "rfcEmisor" => $arr["rfcEmisor"],
                    "rfcReceptor" => $arr["rfcReceptor"],
                );
                $this->_actualizar(pathinfo($filename, PATHINFO_FILENAME), $update);
                $array = $this->_buscarPedimento($arr["guia"]);
                if (!empty($array)) {
                    if (isset($array["pedimento"]) && isset($array["referencia"])) {
                        $trafico = array(
                            "idRepositorio" => $this->_buscarEnRepositorio(3589, 640, $array["pedimento"], $array["referencia"]),
                            "patente" => $array["patente"],
                            "aduana" => $array["aduana"],
                            "pedimento" => $array["pedimento"],
                            "referencia" => $array["referencia"],
                            "rfcCliente" => $array["rfcCliente"],
                        );
                        $this->_actualizar(pathinfo($filename, PATHINFO_FILENAME), $trafico);
                        return array("success" => true, "idGuia" => (int) $id, "guia" => $arr["guia"], "results" => array("pedimento" => (int) $array["pedimento"], "referencia" => $array["referencia"], "sistema" => $array["sis"]));
                    } else {
                        return array("success" => true, "idGuia" => (int) $id, "guia" => $arr["guia"], "results" => "No pedimento o referencia");
                    }
                } else {
                    return array("success" => false, "idGuia" => (int) $id, "guia" => $arr["guia"], "results" => null);
                }
            } else {
                return;
            }
        }
        return;
    }

    protected function _buscarPedimento($guia) {
        $guia = preg_replace('/-|\s+/', '', $guia);
        $mppr = new Trafico_Model_TraficoGuiasMapper();
        if (($arr = $mppr->buscarGuia($guia))) {
            $arr["sis"] = "table";
            return $arr;
        }
        if (($arr = $mppr->buscarNumGuia($guia))) {
            $arr["sis"] = "table";
            return $arr;
        }
        $traficos = new OAQ_Trafico();
        if (($arr = $traficos->buscarGuia($guia))) {
            $arr["sis"] = "rest";
            return $arr;
        }
        return;
    }

    protected function _analizarFactura($filename) {
        $this->_sat->analizarArchivoXml($filename);
        $arr = array();
        if ($this->_sat->isCdfi()) {
            $arr["folio"] = (int) $this->_sat->get_folio();
            $arr["fechaFolio"] = $this->_sat->get_fechaFolio();
            $arr["rfcEmisor"] = $this->_sat->get_rfcEmisor();
            $arr["rfcReceptor"] = $this->_sat->get_rfcReceptor();
            $arr["patente"] = $this->_sat->get_patente();
            $arr["guia"] = $this->_sat->get_guia();
            $this->_agregarLog(array(
                "idExpediente" => null,
                "origen" => __METHOD__,
                "mensaje" => "Archivo: " . basename($filename) . " folio: " . $arr["folio"] . " guía: " . $this->_sat->get_guia(),
                "estatus" => 1,
                "isFile" => 1,
                "ip" => "localhost",
                "creado" => date("Y-m-d H:i:s"),
                "usuario" => "RabbitMQ",
            ));
        }
        return $arr;
    }
    
    protected function _agregarLog($arr) {
        try {
            $stmt = $this->_db->insert("emails_log", $arr);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function verificarArchivo($fecha = null) {
        if (isset($this->_filename)) {
            if (file_exists($this->_filename)) {
                if (!($id = $this->_buscar())) {
                    $id = $this->_agregar($fecha);
                } else {
                    $this->_actualizarUbicacion($id, $this->_filename);
                }
                if (preg_match('/.xml$/', $this->_filename)) {
                    $arr = $this->analizar($id);
                    if (isset($arr) && $arr['success'] == false) {
                        
                    }
                }
            }
        } else {
            throw new Exception("Filename not set!");
        }
    }

    protected function _obtenerPorNombre($nombreArchivo) {
        try {
            $sql = $this->_db->select()
                    ->from("repositorio_tmp_terminal", array("*"))
                    ->where("nombreArchivo LIKE ?", $nombreArchivo . "%");
            $stmt = $this->_db->fetchAll($sql);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    protected function _obtenerPorFolio($folio) {
        try {
            $sql = $this->_db->select()
                    ->from("repositorio_tmp_terminal", array("*"))
                    ->where("folio = ?", $folio);
            $stmt = $this->_db->fetchAll($sql);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    protected function _obtenerNombre($id) {
        try {
            $sql = $this->_db->select()
                    ->from("repositorio_tmp_terminal", array("*"))
                    ->where("id = ?", $id);
            $stmt = $this->_db->fetchRow($sql);
            if ($stmt) {
                return substr($stmt["nombreArchivo"], 0, -4);
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    protected function _obtenerFolio($id) {
        try {
            $sql = $this->_db->select()
                    ->from("repositorio_tmp_terminal", array("*"))
                    ->where("id = ?", $id);
            $stmt = $this->_db->fetchRow($sql);
            if ($stmt) {
                return $stmt["folio"];
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    protected function _prepareForInsert($arr) {
        if ($arr["idRepositorio"]) {
            $insert = array(
                "tipo_archivo" => 40,
                "rfc_cliente" => $arr["rfcCliente"],
                "patente" => $arr["patente"],
                "aduana" => $arr["aduana"],
                "pedimento" => $arr["pedimento"],
                "referencia" => $arr["referencia"],
                "folio" => $arr["folio"],
                "emisor_rfc" => $arr["rfcEmisor"],
                "receptor_rfc" => $arr["rfcReceptor"],
                "nom_archivo" => $arr["nombreArchivo"],
                "ubicacion" => $arr["ubicacion"],
                "creado" => date("Y-m-d H:i:s"),
                "usuario" => isset($this->_usuario) ? $this->_usuario : null,
            );
            return $insert;
        } else if (isset($arr["patente"]) && isset($arr["aduana"]) && isset($arr["pedimento"]) && isset($arr["referencia"]) && isset($arr["rfcCliente"])) {
            return;
        }
        return;
    }

    protected function _existeEnRepositorio($patente, $aduana, $pedimento, $referencia, $nombreArchivo) {
        try {
            $sql = $this->_db->select()
                    ->from("repositorio", array("id"))
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana)
                    ->where("pedimento = ?", $pedimento)
                    ->where("referencia = ?", $referencia)
                    ->where("nom_archivo = ?", $nombreArchivo);
            $stmt = $this->_db->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    protected function _agregarEnRepositorio($arr) {
        try {
            if (!($this->_existeEnRepositorio($arr["patente"], $arr["aduana"], $arr["pedimento"], $arr["referencia"], $arr["nom_archivo"]))) {
                $stmt = $this->_db->insert("repositorio", $arr);
                if ($stmt) {
                    return $stmt;
                }
                return;
            } else {
                return true;
            }
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    protected function _enviado($id) {
        try {
            $stmt = $this->_db->update("repositorio_tmp_terminal", array("enviado" => 1), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception on " . __METHOD__ . " : " . $ex->getMessage());
        }
    }

    public function borrarDeTemporal($id) {
        if ($id != null) {
            if (($nombreArchivo = $this->_obtenerNombre($id))) {
                $array = $this->_obtenerPorNombre($nombreArchivo);
                if (!empty($array)) {
                    foreach ($array as $item) {
                        $this->_enviado($item["id"]);
                    }
                }
            }
            return;
        }
        return;
    }

    public function enviarARepositorio($id) {
        if ($id != null) {
            if (($folio = $this->_obtenerFolio($id))) {
                $array = $this->_obtenerPorFolio($folio);
                if (!empty($array)) {
                    foreach ($array as $item) {
                        if (($insert = $this->_prepareForInsert($item))) {
                            if ($this->_agregarEnRepositorio($insert)) {
                                $this->_enviado($item["id"]);
                            }
                        }
                    }
                }
            }
            return;
        }
        return;
    }

    public function crearZip($zipFilename, $files) {
        $tmpDirectory = "D:\\Tmp\zips";
        if (APPLICATION_ENV === "production" || APPLICATION_ENV === "staging") {
            $tmpDirectory = "/tmp/zips";
        }
        if (!file_exists($tmpDirectory)) {
            mkdir($tmpDirectory, 0777, true);
        }
        $zipFilename = $tmpDirectory . DIRECTORY_SEPARATOR . $zipFilename;
        if (file_exists($zipFilename)) {
            unlink($zipFilename);
        }
        $zip = new ZipArchive();
        if ($zip->open($zipFilename, ZIPARCHIVE::CREATE) !== TRUE) {
            return;
        }
        foreach ($files as $file) {
            if (file_exists($file["ubicacion"])) {
                $tmpfile = $tmpDirectory . DIRECTORY_SEPARATOR . sha1($file["ubicacion"]);
                copy($file["ubicacion"], $tmpfile);
                if (($zip->addFile($tmpfile, basename($file["ubicacion"]))) === true) {
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
        if (file_exists($zipFilename)) {
            return $zipFilename;
        }
        return;
    }

}
