<?php

class Archivo_Model_CorresponsalesMapper
{
    protected $_db_table;
    
    public function __construct()
    {
        $this->_db_table = new Archivo_Model_DbTable_Corresponsales();
    }
    
    public function getAll()
    {
        try {
            $select = $this->_db_table->select();
            $result = $this->_db_table->fetchAll($select);
            if($result) {
                return $result->toArray();
            }
            return null;
        } catch (Zend_Exception $e) {
            echo "<b>Exception found on ".__METHOD__."</b>: ".$e->getMessage(); die();
        }
    }
    
    public function getInfoByAccount($cuentaId)
    {
        try {
            $select = $this->_db_table->select()
                    ->where('cta_ingresos = ?',$cuentaId);
            $result = $this->_db_table->fetchRow($select);
            if($result) {
                return $result->toArray();
            }
            return null;
        } catch (Zend_Exception $e) {
            echo "<b>Exception found on ".__METHOD__."</b>: ".$e->getMessage(); die();
        }
    }
    
    public function getByPatentAndCustom($patente, $aduana)
    {
        try {
            $select = $this->_db_table->select()
                    ->where('patente = ?',$patente)
                    ->where('aduana = ?', $aduana);
            $result = $this->_db_table->fetchRow($select);
            if($result) {
                return $result->toArray();
            }
            return null;
        } catch (Zend_Exception $e) {
            echo "<b>Exception found on ".__METHOD__."</b>: ".$e->getMessage(); die();
        }
    }
    
}

