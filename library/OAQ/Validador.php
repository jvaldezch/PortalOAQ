<?php

/**
 * Administración, validación de archivos M3
 *
 * @author Jaime E. Valdez <jvaldezch@gmail.com>
 */
class OAQ_Validador {

    protected $filename;
    protected $directory;
    protected $_m;
    protected $_k;
    protected $_a;
    protected $_ext;
    protected $_ftp;
    protected $_pago = false;
    protected $_respuesta = false;
    protected $_validacion = false;
    protected $_error = false;

    public function __construct(array $options = null) {
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    public function __set($name, $value) {
        $method = "set" . $name;
        if (("mapper" == $name) || !method_exists($this, $method)) {
            throw new Exception("Invalid invoice property");
        }
        $this->$method($value);
    }

    public function __get($name) {
        $method = "get" . $name;
        if (("mapper" == $name) || !method_exists($this, $method)) {
            throw new Exception("Invalid invoice property");
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

    function getFilename() {
        return $this->filename;
    }

    function setFilename($filename) {
        $this->filename = $filename;
    }

    function getDirectory() {
        return $this->directory;
    }

    function setDirectory($directory) {
        $this->directory = $directory;
    }

    function get_respuesta() {
        return $this->_respuesta;
    }

    function get_validacion() {
        return $this->_validacion;
    }
    
    function get_pago() {
        return $this->_pago;
    }

    function get_error() {
        return $this->_error;
    }

    function get_m() {
        return $this->_m;
    }

    function get_k() {
        return $this->_k;
    }
    
    function get_a() {
        return $this->_a;
    }

    public function pagarArchivo(OAQ_Ftp $ftp) {
        $this->_ftp = $ftp;
        $this->_ext = pathinfo($this->filename, PATHINFO_EXTENSION);
        $this->_a = "A" . trim(substr($this->filename, 1, 7)) . "." . $this->_ext;
        if ($this->_pago === false) {
            if ($this->_checarArchivo($this->_a)) {
                if ($this->_descargarArchivo($this->_a)) {
                    $this->_pago = true;
                }
            }
        }
    }
    
    public function validarArchivo(OAQ_Ftp $ftp) {
        $this->_ftp = $ftp;
        $this->_ext = pathinfo($this->filename, PATHINFO_EXTENSION);
        $this->_m = "M" . trim(substr($this->filename, 1, 7)) . ".err";
        $this->_k = "k" . trim(substr($this->filename, 1, 7)) . "." . $this->_ext;
        if ($this->_respuesta === false) {
            if ($this->_checarArchivo($this->_k)) {
                if ($this->_descargarArchivo($this->_k)) {
                    $this->_respuesta = true;
                }
            }
        }
        if ($this->_validacion === false) {
            if ($this->_checarArchivo($this->_m)) {
                if ($this->_descargarArchivo($this->_m)) {
                    $this->_validacion = true;
                    if ($this->_tieneError($this->_m)) {
                        $this->_error = true;
                    }
                }
            }
        }
    }

    public function contenidoArchivoBase64($filename) {
        if (file_exists($this->directory . DIRECTORY_SEPARATOR . $filename)) {
            return base64_encode(file_get_contents($this->directory . DIRECTORY_SEPARATOR . $filename));
        }
        return false;
    }

    public function contenidoArchivo($filename) {
        if (file_exists($this->directory . DIRECTORY_SEPARATOR . $filename)) {
            return file_get_contents($this->directory . DIRECTORY_SEPARATOR . $filename);
        }
        return false;
    }

    protected function _descargarArchivo($filename) {
        if (!$this->_existeLocalmente($filename)) {
            $this->_ftp->download($this->directory . DIRECTORY_SEPARATOR . $filename, $filename);
            if (file_exists($this->directory . DIRECTORY_SEPARATOR . $filename) && filesize($this->directory . DIRECTORY_SEPARATOR . $filename) > 0) {
                return true;
            }
            return false;
        } else {
            return true;
        }
    }

    protected function _checarArchivo($filename) {
        if (!$this->_existeLocalmente($filename)) {
            $size = $this->_ftp->ftpSize($filename);
            if ($size > 1) {
                return true;
            }
            return false;
        } else {
            return true;
        }
    }

    protected function _existeLocalmente($filename) {
        if (file_exists($this->directory . DIRECTORY_SEPARATOR . $filename) && filesize($this->directory . DIRECTORY_SEPARATOR . $filename) > 0) {
            return true;
        }
        return false;
    }

    protected function _tieneError($filename) {
        if (strpos(file_get_contents($this->directory . DIRECTORY_SEPARATOR . $filename), "ERRORES") !== false || strpos(file_get_contents($this->directory . DIRECTORY_SEPARATOR . $filename), "E0000000001000150056") !== false) {
            return true;
        }
        return false;
    }

}
