<?php

require "Zend/Loader/Autoloader.php";
$autoloader = Zend_Loader_Autoloader::getInstance();
ini_set("soap.wsdl_cache_enabled", 0);

class Ftp {

    protected $_db;
    protected $_connId;
    protected $_remoteFolder;
    protected $_key = "5203bfec0c3db@!b2295";
    protected $_localDir = '/tmp/ftptmp';

    function __construct() {
        $this->_db = Zend_Db::factory('Pdo_Mysql', array(
                    'host' => '127.0.0.1',
                    'username' => 'root',
                    'password' => 'mysql11!',
                    'dbname' => 'oaqintranet'
        ));
        if (!file_exists($this->_localDir)) {
            mkdir($this->_localDir, 0777, true);
        }
    }

    /**
     * 
     * @param string $rfc
     * @return boolean
     * @throws Exception
     */
    public function getServerCredentials($rfc) {
        try {
            try {
                $select = $this->_db->select()
                        ->from("ftp")
                        ->where("rfc = ?", $rfc)
                        ->where("type = 'm3'")
                        ->where("active = 1");
                $result = $this->_db->fetchRow($select);
                if ($result) {
                    if ($this->_connectFtp($result) === true) {
                        return true;
                    }
                }
                return false;
            } catch (Zend_Db_Exception $e) {
                throw new Exception("Zend Db Exception on " . __METHOD__ . ":" . $e->getMessage());
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    /**
     * 
     * @param array $server
     * @return boolean
     * @throws Exception
     */
    protected function _connectFtp($server) {
        $this->_connId = ftp_connect($server["url"], $server["port"]);
        $login_result = ftp_login($this->_connId, $server["user"], $server["password"]);
        if (isset($server['remoteFolder']) && $server['remoteFolder'] != '') {
            ftp_chdir($this->_connId, $server['remoteFolder']);
        }
        if ((!$this->_connId) || (!$login_result)) {
            $error = "UNABLE TO CONNECT TO CLIENT FTP {$server["rfc"]}\n"
                    . "Url: {$server["url"]}\n"
                    . "User: {$server["user"]}\n"
                    . "Pass: {$server["password"]}\n";
            throw new Exception($error);
        }
        return true;
    }

    /**
     * 
     * @param string $table
     * @param int $id
     * @return boolean
     * @throws Exception
     */
    public function _getFileContent($table, $id) {
        try {
            try {
                $select = $this->_db->select()
                        ->from($table, array('contenido'))
                        ->where("id = ?", $id);
                $result = $this->_db->fetchRow($select);
                if ($result) {
                    return base64_decode($result["contenido"]);
                }
                return false;
            } catch (Zend_Db_Exception $e) {
                throw new Exception("Zend Db Exception on " . __METHOD__ . ":" . $e->getMessage());
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    /**
     * 
     * @param string $filename
     * @return boolean
     * @throws Exception
     */
    protected function _uploadFile($filename) {
        try {
            if (!isset($this->_connId)) {
                throw new Exception("No connection.");
            }
            if (file_exists($filename) && is_readable($filename)) {
                $uploaded = ftp_put($this->_connId, basename($filename), $filename, FTP_BINARY);
                if ($uploaded) {
                    return true;
                }
            } else {
                return false;
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    /**
     * 
     * @param string $table
     * @param int $id
     * @param string $basename
     * @param string $rfc
     * @return boolean
     * @throws Exception
     */
    public function uploadToFtp($table, $id, $basename, $rfc) {
        try {
            $content = $this->_getFileContent($table, $id);
            if ($content) {
                $tmpDir = $this->_localDir . DIRECTORY_SEPARATOR . date('Ymd') . '_m3_' . $rfc;
                if (!file_exists($tmpDir)) {
                    mkdir($tmpDir, 0777, true);
                }
                $filename = $tmpDir . DIRECTORY_SEPARATOR . $basename;
                file_put_contents($filename, $content);
                if (file_exists($filename)) {
                    if (($this->_uploadFile($filename)) === true) {
                        return true;
                    } else {
                        return false;
                    }
                }
            } else {
                return false;
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

}
