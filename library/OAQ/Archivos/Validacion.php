<?php

class OAQ_Archivos_Validacion {

    protected $patente;
    protected $aduana;
    protected $contenido;
    protected $nombreArchivo;
    protected $arr;
    protected $pedimentos;
    protected $edocuments;
    protected $coves;

    function setPatente($patente) {
        $this->patente = $patente;
    }

    function setAduana($aduana) {
        $this->aduana = $aduana;
    }

    function setContenido($contenido) {
        $this->contenido = $contenido;
    }

    function setNombreArchivo($nombreArchivo) {
        $this->nombreArchivo = $nombreArchivo;
    }

    public function __set($name, $value) {
        $method = "set" . $name;
        if (("mapper" == $name) || !method_exists($this, $method)) {
            throw new Exception("Invalid property: " . $name);
        }
        $this->$method($value);
    }

    public function __get($name) {
        $method = "get" . $name;
        if (("mapper" == $name) || !method_exists($this, $method)) {
            throw new Exception("Invalid property: " . $name);
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

    public function __construct(array $options = null) {
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    public function analizar() {
        if (isset($this->contenido)) {
            $arrayFile = preg_split('/\r\n|\r|\n/', $this->contenido);
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
            if (isset($content["500"])) {
                $this->arr = $content;
            } else {
                return false;
            }
            $this->_extrearPedimentos();
            $this->_extraerEdocuments();
            $this->_estraerCoves();
        }
    }
    
    protected function _extrearPedimentos() {
        if (isset($this->arr) && !empty($this->arr[500])) {
            foreach ($this->arr[500] as $item) {
                $tmp = array();
                $arr = explode('|', $item);
                $tmp['aduana'] = (int) $arr[4];
                $tmp['pedimento'] = (int) $arr[3];
                $tmp['patente'] = (int) $arr[2];
                $this->pedimentos[$arr[3]] = $tmp;
            }
        }
    }
    
    protected function _extraerEdocuments() {
        if (isset($this->arr) && !empty($this->arr[500])) {
            foreach ($this->arr[507] as $item) {
                $arr = explode('|', $item);
                if ($arr[2] == 'ED') {
                    $this->edocuments[] = $arr[3];
                }
            }
        }
    }
    
    protected function _estraerCoves() {
        if (isset($this->arr) && !empty($this->arr[500])) {
            foreach ($this->arr[505] as $item) {
                $arr = explode('|', $item);
                $this->coves[] = $arr[3];
            }
        }
    }

}
