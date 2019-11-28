<?php

/**
 * Description of EmailNotifications
 *
 * @author Jaime E. Valdez <jvaldezch@gmail.com>
 */
class OAQ_VucemArchivos {

    protected $id;
    protected $sello;
    protected $data;
    protected $dir;
    protected $edoc;
    protected $solicitud;
    protected $tipoDocumento;
    protected $pdfFilename;
    protected $xmlFilename;
    protected $sourceFilename;
    protected $archivoOriginal;
    protected $repositorio;
    protected $username;

    public function __construct(array $options = null) {
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    public function __set($name, $value) {
        $method = "set" . $name;
        if (("mapper" == $name) || !method_exists($this, $method)) {
            throw new Exception("Invalid property");
        }
        $this->$method($value);
    }

    public function __get($name) {
        $method = "get" . $name;
        if (("mapper" == $name) || !method_exists($this, $method)) {
            throw new Exception("Invalid property");
        }
        return $this->$method();
    }

    public function setOptions(array $options) {
        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = "set" . ucfirst($key);
            if (in_array($method, $methods)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    function getId() {
        return $this->id;
    }

    function getDir() {
        return $this->dir;
    }

    function getData() {
        return $this->data;
    }

    function setData($data) {
        $this->data = $data;
    }

    function setUsername($username) {
        $this->username = $username;
    }

    function getEdoc() {
        return $this->edoc;
    }

    function getSolicitud() {
        return $this->solicitud;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setDir($dir) {
        $this->dir = $dir;
    }

    function setEdoc($edoc) {
        $this->edoc = $edoc;
    }

    function setSolicitud($solicitud) {
        $this->solicitud = $solicitud;
    }

    function getSello() {
        return $this->sello;
    }

    function setSello($sello) {
        $this->sello = $sello;
    }
    
    function getArchivoOriginal() {
        return $this->archivoOriginal;
    }

    function setArchivoOriginal($archivoOriginal) {
        $this->archivoOriginal = $archivoOriginal;
    }

    protected function _checkForFile($filename) {
        if (file_exists($this->dir . DIRECTORY_SEPARATOR . $filename)) {
            return true;
        }
        return false;
    }

    protected function _saveEdocumentPdf() {
        $print = new OAQ_PrintEdocuments();
        $data["rfc"] = $this->data["rfc"];
        $data["hash"] = $this->data["hash"];
        $data["edoc"] = $this->data["edoc"];
        $data["numTramite"] = $this->data["numTramite"];
        $data["actualizado"] = $this->data["actualizado"];
        $data["cadena"] = $this->data["cadena"];
        $data["firma"] = $this->data["firma"];
        $data["nomArchivo"] = $this->data["nomArchivo"];
        $data["tipoDoc"] = $this->data["tipoDoc"];
        $data["rfcConsulta"] = $this->data["rfcConsulta"];
        $data["razonSocial"] = isset($this->sello) ? $this->sello["razon"] : "";
        $print->set_data($data);
        $print->set_dir($this->dir);
        if (isset($this->data["titulo"])) {
            $print->saveEdocument($this->data["titulo"]);
        } else {
            $print->saveEdocument();            
        }
    }

    protected function _saveEdocumentXml() {
        $mapper = new Vucem_Model_VucemEdocMapper();
        $file = $mapper->archivoDigitalizado($this->id);
        if (empty($file)) {
            $mdd = new Archivo_Model_RepositorioMapper();
            $archivo = $mdd->getFileById($this->id);
            if (!empty($archivo)) {
                $file = array();
                $file["nomArchivo"] = $archivo["nom_archivo"];
            }
        }
        if (!file_exists($this->dir . DIRECTORY_SEPARATOR . $file["nomArchivo"])) {
            if (isset($this->archivoOriginal)) {
                copy($this->archivoOriginal, $this->dir . DIRECTORY_SEPARATOR . "EDOC_" . $this->data["tipoDoc"] . "_" . $file["nomArchivo"]);
                $this->sourceFilename = "EDOC_" . $this->data["tipoDoc"] . "_" . $file["nomArchivo"];
            } else {
                file_put_contents($this->dir . DIRECTORY_SEPARATOR . "EDOC_" . $file["nomArchivo"], base64_decode($file["archivo"]));  
                $this->sourceFilename = "EDOC_" . $file["nomArchivo"];
            }
        } else {
            $this->sourceFilename = $file["nomArchivo"];
        }
        $data["archivo"] = array(
            "idTipoDocumento" => $this->data["tipoDoc"],
            "nombreDocumento" => $this->data["nomArchivo"],
            "archivo" => base64_encode(file_get_contents($this->dir . DIRECTORY_SEPARATOR . $this->sourceFilename)),
            "hash" => $this->data["hash"],
            "correoElectronico" => $this->data["email"],
            "rfcConsulta" => $this->data["rfcConsulta"]
        );
        $data["usuario"] = array(
            "username" => $this->sello["rfc"],
            "password" => $this->sello["ws_pswd"],
            "certificado" => $this->sello["cer"],
            "key" => openssl_get_privatekey(base64_decode($this->sello["spem"]), $this->sello["spem_pswd"]),
            "new" => null,
        );
        $xml = new OAQ_Xml(false, true);
        $xml->set_dir($this->dir);
        $xml->xmlEdocument($data, true);
        if (isset($this->data["titulo"])) {
            $xml->saveToDisk(null, $this->data["titulo"] . ".xml");            
        } else {
            $xml->saveToDisk(null, "EDOC_" . $this->xmlFilename);
        }
    }

    public function guardarEdoc($saveSource = true) {
        $this->repositorio = new Archivo_Model_Repositorio();
        if (isset($this->data["titulo"])) {
            $this->pdfFilename = $this->data["titulo"] . ".pdf";
            $this->xmlFilename = $this->data["titulo"] . ".xml"; 
        } else {
            $this->pdfFilename = $this->data["edoc"] . ".pdf";
            $this->xmlFilename = $this->data["edoc"] . ".xml";            
        }
        if (!$this->_checkForFile($this->pdfFilename)) {
            $this->_saveEdocumentPdf();
        }
        if (!$this->_checkForFile($this->xmlFilename)) {
            $this->_saveEdocumentXml();
        }
        $this->_verificarRepositorioEdocument($this->pdfFilename, 27, $this->data["edoc"]);
        $this->_verificarRepositorioEdocument($this->xmlFilename, 56, $this->data["edoc"]);
        if ($saveSource == true) {
            if(isset($this->sourceFilename)) {            
                $this->_verificarRepositorio($this->sourceFilename, $this->data["tipoDoc"]);
            }
        }
        return true;
    }

    protected function _verificarRepositorio($filename, $tipoArchivo) {
        $table = new Archivo_Model_Table_Repositorio();
        $table->setReferencia($this->data["referencia"]);
        $table->setPatente($this->data["patente"]);
        $table->setPedimento($this->data["pedimento"]);
        $table->setAduana($this->data["aduana"]);
        $table->setTipo_archivo($tipoArchivo);
        if (!preg_match('/^EDOC_/', $filename)) {
            $filename = "EDOC_" . $filename;
        }
        $table->setNom_archivo($filename);
        $table->setUbicacion($this->dir . DIRECTORY_SEPARATOR . $filename);
        $table->setCreado(date("Y-m-d H:i:s"));
        if ($this->sello["figura"] == 1) {
            $table->setRfc_cliente($this->data["rfcConsulta"]);
        } else {
            $table->setRfc_cliente($this->data["rfc"]);
        }
        $table->setUsuario($this->username);
        $this->repositorio->findFile($table);
        if (null == ($table->getId())) {
            $this->repositorio->save($table);
        }
    }
    
    protected function _verificarRepositorioEdocument($filename, $tipoArchivo, $edoc) {
        $table = new Archivo_Model_Table_Repositorio();
        $table->setReferencia($this->data["referencia"]);
        $table->setPatente($this->data["patente"]);
        $table->setPedimento($this->data["pedimento"]);
        $table->setAduana($this->data["aduana"]);
        $table->setTipo_archivo($tipoArchivo);
        $table->setEdocument($edoc);
        if (!preg_match('/^EDOC_/', $filename)) {
            $filename = "EDOC_" . $filename;
        }
        $table->setNom_archivo($filename);
        $table->setUbicacion($this->dir . DIRECTORY_SEPARATOR . $filename);
        $table->setCreado(date("Y-m-d H:i:s"));
        if ($this->sello["figura"] == 1) {
            $table->setRfc_cliente($this->data["rfcConsulta"]);
        } else {
            $table->setRfc_cliente($this->data["rfc"]);
        }
        $table->setUsuario($this->username);
        $this->repositorio->findEdocument($table);
        if (null == ($table->getId())) {
            $this->repositorio->save($table);
        }
    }

}
