<?php

class Vucem_Model_VucemPeticionesMapper
{
    protected $_db_table;
    protected $_key = "5203bfec0c3db@!b2295";
    
    function __construct()
    {
        $this->_db_table = new Vucem_Model_DbTable_VucemPeticiones();
    }
    
    public function nuevaPeticion($xml)
    {
        try {            
            $inserted = $this->_db_table->insert(array(
                'creado' => date('Y-m-d H:i:s'),
                'peticion' => $xml
            ));
            return $inserted;
        } catch (Exception $e) {
            echo "<b>Exception on ".__METHOD__."</b>" . $e->getMessage(); die();
        }
    }
    
    public function actualizarPeticion($id,$xml,$error,$mensaje)
    {
        try {
            $updated = $this->_db_table->update(array(
                'actualizado' => date('Y-m-d H:i:s'),
                'respuesta' => $xml,
                'error' => $error,
                'mensaje' => (isset($mensaje)) ? $mensaje : null,
            ), array(
                'id = ?' => $id,
            ));
            if($updated) {
                return true;
            }
            return null;
        } catch (Exception $e) {
            echo "<b>Exception on ".__METHOD__."</b>" . $e->getMessage(); die();
        }
    }
    
}