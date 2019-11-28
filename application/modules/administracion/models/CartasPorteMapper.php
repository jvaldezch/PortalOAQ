<?php

class Administracion_Model_CartasPorteMapper
{
    
    // ALTER TABLE cartas_porte AUTO_INCREMENT=184;
    protected $_db_table;
    
    public function __construct()
    {
        $this->_db_table = new Administracion_Model_DbTable_CartasPorte();
    }
    
    public function obtenerFolio($folio) {
        try {
            $select = $this->_db_table->select()
                    ->where('id = ?', $folio);
            $result = $this->_db_table->fetchRow($select);
            if ($result) {
                return $result->toArray();
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            echo "Zend DB Exception found on <strong>" . __METHOD__ . "</strong> >> " . $e->getMessage();
            die();
        } catch (Zend_Exception $e) {
            echo "Zend Exception found on <strong>" . __METHOD__ . "</strong> >> " . $e->getMessage();
            die();
        }
    }
    
    public function obtenerCartas() {
        try {
            $select = $this->_db_table->select()
                    ->order('id DESC');
            $result = $this->_db_table->fetchAll($select);
            if ($result) {
                return $result->toArray();
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            echo "Zend DB Exception found on <strong>" . __METHOD__ . "</strong> >> " . $e->getMessage();
            die();
        } catch (Zend_Exception $e) {
            echo "Zend Exception found on <strong>" . __METHOD__ . "</strong> >> " . $e->getMessage();
            die();
        }
    }
    
    public function nuevaCarta($data, $username) {
        try {
            $data["creado"] = date('Y-m-d H:i:s');
            $data["creadoPor"] = $username;
            $insert = $this->_db_table->insert($data);
            if($insert) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            echo "Zend DB Exception found on <strong>" . __METHOD__ . "</strong> >> " . $e->getMessage();
            die();
        } catch (Zend_Exception $e) {
            echo "Zend Exception found on <strong>" . __METHOD__ . "</strong> >> " . $e->getMessage();
            die();
        }
    }
    public function actualizarCarta($folio, $data, $username) {
        try {
            unset($data["folio"]);
            $data["actualizado"] = date('Y-m-d H:i:s');
            $data["actualizadoPor"] = $username;            
            $where = array(
                'id = ?' => $folio,
            );
            $updated = $this->_db_table->update($data, $where);
            if($updated) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            echo "Zend DB Exception found on <strong>" . __METHOD__ . "</strong> >> " . $e->getMessage();
            die();
        } catch (Zend_Exception $e) {
            echo "Zend Exception found on <strong>" . __METHOD__ . "</strong> >> " . $e->getMessage();
            die();
        }
    }
    
}

