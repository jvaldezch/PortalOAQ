<?php

class Automatizacion_Model_ClientesMapper
{
    protected $_db_table;
    
    public function __construct()
    {
        $this->_db_table = new Automatizacion_Model_DbTable_Clientes();
    }
    
    /**
     * 
     * @param String $rfc
     */
    public function verifyCustomer($rfc)
    {
        $select = $this->_db_table->select()
                ->where('rfc LIKE ?',$rfc);
        
        $result = $this->_db_table->fetchRow($select,array());
        
        if($result) {
            return true;
        }
        return NULL;
    }
    /**
     * 
     * @param String $rfc
     * @param String $nombre
     * @param String $email
     * @param int $sicaId
     * @param int $sicaNumInt
     * @return boolean
     */
    public function addNewSicaCustomer($rfc,$nombre,$email,$sicaId,$sicaNumInt)
    {
        try {
            $data = array(
                'rfc' => $rfc,
                'nombre' => utf8_decode($nombre),
                'email' => utf8_decode($email),
                'creado' => date('Y-m-d H:i:s'),
                'sica_id' => $sicaId,
                'sica_numint' => $sicaNumInt,
            );            
            $inserted = $this->_db_table->insert($data);
            if($inserted) {
                return true;
            }
        } catch (Exception $e) {
            echo $e->getMessage() .'<br>';
            echo utf8_decode($nombre) . ' ' . $email . '<br>';
        }
    }
    
    public function getAllCustomers()
    {
        try {
            $select = $this->_db_table->select();
            $result = $this->_db_table->fetchAll($select,array());

            if($result) {
                return $result;
            }
            return NULL;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}

