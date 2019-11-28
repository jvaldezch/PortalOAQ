<?php

class Vucem_Model_VucemDestinatarioMapper
{
    protected $_db_table;
    
    function __construct()
    {
        $this->_db_table = new Vucem_Model_DbTable_VucemDestinatario();
    }
    
    public function getProviders($patente, $aduana, $cvecli = null)
    {
        try {
            $select = $this->_db_table->select()
                    ->from('vucem_destinatario',array('cvedest','rfc','razon_soc'))
                    ->where('patente = ?',$patente)
                    ->where('aduana = ?',$aduana);          
            if(isset($cvecli)) {
                $select->where('cvecli = ?',$cvecli);
            }
            if(($result = $this->_db_table->fetchAll($select))) {
                return $result->toArray();
            }
            return null;
        } catch (Exception $e) {
            echo "<b>Exception on ".__METHOD__."</b>" . $e->getMessage(); die();
        }
    }
    
    public function getProviderDetail($patente, $aduana, $cvedest)
    {
        try {
            $select = $this->_db_table->select()
                    ->where('patente = ?',$patente)
                    ->where('aduana = ?',$aduana)
                    ->where('cvedest = ?',$cvedest);
            if(($result = $this->_db_table->fetchRow($select))) {
                return $result->toArray();
            }
            return null;
        } catch (Exception $e) {
            echo "<b>Exception on ".__METHOD__."</b>" . $e->getMessage(); die();
        }
    }
    
    public function obtenerDestinarios($patente,$aduana,$cve,$rfc)
    {
        try {
            $select = $this->_db_table->select()
                    ->from('vucem_destinatario',array('cvedest','rfc','razon_soc'))
                    ->where('patente = ?',$patente)
                    ->where('aduana = ?',$aduana)
                    ->where('cvecli = ?',$cve)
                    ->where('cvecli = ?',$rfc);
            if(($result = $this->_db_table->fetchAll($select))) {
                return $result->toArray();
            }
            return null;
        } catch (Exception $e) {
            echo "<b>Exception on ".__METHOD__."</b>" . $e->getMessage(); die();
        }
    }
    
}