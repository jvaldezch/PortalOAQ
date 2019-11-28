<?php

class Vucem_Model_VucemCoveMapper
{
    protected $_db_table;
    
    function __construct()
    {
        $this->_db_table = new Vucem_Model_DbTable_VucemCove();
    }
    
    public function verificar($rfcAgente,$patente,$aduana,$pedimento,$cove)
    {
        try {
            $select = $this->_db_table->select()
                    ->where('rfc = ?',$rfcAgente)
                    ->where('patente = ?',$patente)
                    ->where('aduana = ?',$aduana)
                    ->where('pedimento = ?',$pedimento)
                    ->where('cove = ?',$cove);                
            if(($result = $this->_db_table->fetchRow($select))) {
                return true;
            }
            return null;
        } catch (Exception $e) {
            echo "<b>Exception on ".__METHOD__."</b>" . $e->getMessage(); die();
        }
    }
    
    public function agregarNuevoCoveXml($rfcAgente,$patente,$aduana,$pedimento,$cove,$xml)
    {
        try {
            $data = array(
                'rfc' => $rfcAgente,
                'estado' => 0,
                'analizado' => 0,
                'num_operacion' => null,
                'patente' => $patente,
                'aduana' => $aduana,
                'pedimento' => $pedimento,
                'cove' => $cove,
                'xml' => $xml,
                'creado' => date('Y-m-d H:i:s'),
            );
            $added = $this->_db_table->insert($data);
            if($added) {
                return true;
            }
            return null;
        } catch (Exception $e) {
            echo "<b>Exception on ".__METHOD__."</b>" . $e->getMessage(); die();
        }
    }
    
    public function analizado($rfc,$patente,$aduana,$pedimento,$cove)
    {
        try {
            $data = array(
                'analizado' => 1,
                'actualizado' => date('Y-m-d H:i:s'),
            );
            $where = array(
                'rfc = ?' => $rfc,
                'patente = ?' => $patente,
                'aduana = ?' => $aduana,
                'pedimento = ?' => $pedimento,
                'cove = ?' => $cove,
            );
            $updated = $this->_db_table->update($data, $where);
            if($updated) {
                return true;
            }
            return null;
        } catch (Exception $e) {
            echo "<b>Exception on ".__METHOD__."</b>" . $e->getMessage(); die();
        }
    }
    
    public function covesSinAnalizar()
    {
        try {
            $select = $this->_db_table->select()
                    ->where('analizado = 0');                
            if(($result = $this->_db_table->fetchAll($select))) {
                return $result->toArray();
            }
            return null;
        } catch (Exception $e) {
            echo "<b>Exception on ".__METHOD__."</b>" . $e->getMessage(); die();
        }
    }
    
}