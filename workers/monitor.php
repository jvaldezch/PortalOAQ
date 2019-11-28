<?php

require "Zend/Loader/Autoloader.php";
$autoloader = Zend_Loader_Autoloader::getInstance();
ini_set("soap.wsdl_cache_enabled", 0);

class Monitor {

    protected $_db_table;
    protected $_connId;

    function __construct() {
        $this->_db_table = Zend_Db::factory('Pdo_Mysql', array(
                    'host' => '127.0.0.1',
                    'username' => 'root',
                    'password' => 'mysql11!',
                    'dbname' => 'oaqintranet'
        ));
    }
    
    public function obtenerWsdl($patente, $aduana) {
        try {
            if ($patente == 3589 && $aduana == 240) {
                return $this->_getWsdl($patente, $aduana, 'slam');
            }
            return false;
        } catch (Exception $e) {
            throw new Exception("<b>Exception while gettin user email credentials at " . __METHOD__ . "</b>" . $e->getMessage());
        }
    }

    protected function _getWsdl($patente, $aduana, $sistema) {
        try {
            $select = $this->_db_table->select()
                    ->from("ws_wsdl", array('wsdl'))
                    ->where('patente = ?', $patente)
                    ->where('aduana = ?', $aduana)
                    ->where('sistema = ?', $sistema);
            $result = $this->_db_table->fetchRow($select);
            if ($result) {
                return $result["wsdl"];
            }
            return null;
        } catch (Exception $e) {
            throw new Exception("<b>Exception while gettin user email credentials at " . __METHOD__ . "</b>" . $e->getMessage());
        }
    }

    public function actualizarEmbarque($id, $arr) {
        try {
            unset($arr['referencia']);
            unset($arr['aduana']);
            unset($arr['fecha']);
            $where = array(
                'id = ?' => $id,
            );
            $updated = $this->_db_table->update("embarques", $arr, $where);
            if ($updated) {
                return true;
            }
            return null;
        } catch (Exception $e) {
            throw new Exception("<b>Exception while gettin user email credentials at " . __METHOD__ . "</b>" . $e->getMessage());
        }
    }
    
    public function agregarEstatus($arr) {
        try {
            $arr["creado"] = date('Y-m-d H:i:s');
            $added = $this->_db_table->insert('embarques_estatus',$arr);
            if ($added) {
                return $added;
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("Db Exception found at " . __METHOD__ . ': ' . $ex->getMessage());
        } catch (Zend_Exception $ex) {
            throw new Exception("Exception found at " . __METHOD__ . ': ' . $ex->getMessage());
        }
    }

}
