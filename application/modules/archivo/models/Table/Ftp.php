<?php

class Archivo_Model_Table_Ftp {

    protected $id;
    protected $type;
    protected $rfc;
    protected $url;
    protected $user;
    protected $password;
    protected $port;
    protected $remoteFolder;
    protected $active;

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

    function getId() {
        return $this->id;
    }

    function getRfc() {
        return $this->rfc;
    }

    function getUrl() {
        return $this->url;
    }

    function getUser() {
        return $this->user;
    }

    function getPassword() {
        return $this->password;
    }

    function getPort() {
        return $this->port;
    }

    function getRemoteFolder() {
        return $this->remoteFolder;
    }

    function getActive() {
        return $this->active;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setRfc($rfc) {
        $this->rfc = $rfc;
    }

    function setUrl($url) {
        $this->url = $url;
    }

    function setUser($user) {
        $this->user = $user;
    }

    function setPassword($password) {
        $this->password = $password;
    }

    function setPort($port) {
        $this->port = $port;
    }

    function setRemoteFolder($remoteFolder) {
        $this->remoteFolder = $remoteFolder;
    }

    function setActive($active) {
        $this->active = $active;
    }
    
    function getType() {
        return $this->type;
    }

    function setType($type) {
        $this->type = $type;
    }

}
