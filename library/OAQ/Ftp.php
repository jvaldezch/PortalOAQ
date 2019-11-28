<?php

/**
 * Clase para utilerias diversas o miscelaneas
 *
 * @author Jaime E. Valdez <jvaldezch@gmail.com>
 */
class OAQ_Ftp {

    protected $conn;
    protected $host;
    protected $port;
    protected $username;
    protected $password;
    protected $transmission = FTP_BINARY;

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

    function getHost() {
        return $this->host;
    }

    function getPort() {
        return $this->port;
    }

    function getUsername() {
        return $this->username;
    }

    function getPassword() {
        return $this->password;
    }

    function setHost($host) {
        $this->host = $host;
    }

    function setPort($port) {
        $this->port = $port;
    }

    function setUsername($username) {
        $this->username = $username;
    }

    function setPassword($password) {
        $this->password = $password;
    }
    
    function setTransmission($transmission) {
        $this->transmission = $transmission;
    }

    function getConn() {
        return $this->conn;
    }
    
    public function connect() {
        try {
            $this->conn = ftp_connect($this->host, $this->port);
            $login = ftp_login($this->conn, $this->username, $this->password);
            if ((!$this->conn) || (!$login)) {
                throw new Exception("No se puede conectar al servidor FTP {$this->host}:{$this->port} User: {$this->username} Pass: {$this->password}.");
            } else {
                return true;
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function changeRemoteDirectory($remoteFolder) {
        if (ftp_chdir($this->conn, $remoteFolder)) {
            return true;
        } else {
            return false;
        }
    }

    public function createRecursiveRemoteFolder($path) {
        $dir = explode("/", $path);
        $path = "";
        $ret = true;

        for ($i = 0; $i < count($dir); $i++) {
            $path .= "/" . $dir[$i];
            if (!@ftp_chdir($this->conn, $path)) {
                @ftp_chdir($this->conn, "/");
                if (!@ftp_mkdir($this->conn, $path)) {
                    $ret = false;
                    break;
                }
            }
        }
        return $ret;
    }

    public function createRemoteFolder($remoteFolder) {
        if (!ftp_chdir($this->conn, $remoteFolder)) {
            $this->_makeDir($this->conn, $remoteFolder);
        } else {
            return true;
        }
    }

    public function setPassive() {
        ftp_pasv($this->conn, true);
    }

    public function setActive() {
        ftp_pasv($this->conn, false);
    }

    protected function _makeDir($path) {
        $dir = split("/", $path);
        $path = "";
        $ret = true;
        for ($i = 0; $i < count($dir); $i++) {
            $path.="/" . $dir[$i];
            if (!ftp_chdir($this->conn, $path)) {
                ftp_chdir($this->conn, "/");
                if (!ftp_mkdir($this->conn, $path)) {
                    $ret = false;
                    break;
                }
            }
        }
        return $ret;
    }

    public function upload($filename, $prefix = null) {
        if (isset($prefix)) {
            if (ftp_put($this->conn, $prefix . basename($filename), $filename, FTP_BINARY)) {
                return $prefix . basename($filename);
            }
        } else {
            if (ftp_put($this->conn, basename($filename), $filename, FTP_BINARY)) {
                return basename($filename);
            }
        }
        return;
    }

    /**
     * 
     * @param string $localFile Path to local file
     * @param string $remoteFile Name of remote file
     * @return boolean
     */
    public function download($localFile, $remoteFile) {
        if (ftp_get($this->conn, $localFile, $remoteFile, $this->transmission)) {
            return true;
        }
        return false;
    }

    public function currentFolder() {
        return ftp_pwd($this->conn);
    }

    public function makeDirectory($dir) {
        if ($this->isDir($dir) || @ftp_mkdir($this->conn, $dir)) {
            return $dir;
        }
        if (!$this->makeDirectory(dirname($dir))) {
            return false;
        }
        return ftp_mkdir($this->conn, $dir);
    }

    public function setTimeout($secs = 60) {
        ftp_set_option($this->conn, FTP_TIMEOUT_SEC, $secs);
    }

    public function ftpSize($filename) {
        return ftp_size($this->conn, $filename);
    }

    public function rawList($directory) {
        return ftp_rawlist($this->conn, $directory);
    }

    protected function isDir($dir) {
        $originalDirectory = ftp_pwd($this->conn);
        if (@ftp_chdir($this->conn, $dir)) {
            ftp_chdir($this->conn, $originalDirectory);
            return true;
        } else {
            return false;
        }
    }

    public function disconnect() {
        ftp_close($this->conn);
    }
    
    public function checkFolder($newFolder) {
        if (ftp_nlist($this->conn, $newFolder) == true) {
            return true;
        }
    }
    
    public function estructuraDirectorio($idCliente, $row) {
        switch ($idCliente) {
            case 121: // STE071214BE7 Steeringmex
                return "/" . $row["rfcCliente"] . "/" . date("Y", strtotime($row["fechaLiberacion"])) . '/' . $row["patente"] . $row["aduana"] . $row["pedimento"];
            default:
                return $row["referencia"] . '_' . $row["pedimento"];
        }
    }

}
