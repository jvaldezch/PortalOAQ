<?php

class Vucem_Model_VucemIdentificadoresMapper
{
    protected $_db_table;
    
    function __construct()
    {
        $this->_db_table = new Vucem_Model_DbTable_VucemIdentificadores();
    }
    
    public function getAll()
    {
        try {            
            $select = $this->_db_table->select();
            if(($result = $this->_db_table->fetchAll($select))) {
                return $result->toArray();
            }
            return null;
        } catch (Exception $e) {
            echo "<b>Exception on ".__METHOD__."</b>" . $e->getMessage(); die();
        }
    }
    
}