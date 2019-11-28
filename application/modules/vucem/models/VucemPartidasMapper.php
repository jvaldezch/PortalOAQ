<?php

class Vucem_Model_VucemPartidasMapper {

    protected $_db_table;
    protected $_key = "5203bfec0c3db@!b2295";

    function __construct() {
        $this->_db_table = new Vucem_Model_DbTable_VucemPartidas();
    }

    public function verificar($num_operacion, $patente, $aduana, $pedimento, $partida) {
        try {
            $select = $this->_db_table->select();
            $select->where('numOperacion = ?', $num_operacion)
                    ->where('patente = ?', $patente)
                    ->where('aduana = ?', $aduana)
                    ->where('pedimento = ?', $pedimento)
                    ->where('partida = ?', $partida);
            if ($this->_db_table->fetchRow($select)) {
                return true;
            }
            return null;
        } catch (Exception $e) {
            if (isset($select))
                echo $select;
            echo "<b>Exception on " . __METHOD__ . "</b>" . $e->getMessage();
            die();
        }
    }

    public function agregarPartidaXml($rfcAgente, $estado, $num_operacion, $patente, $aduana, $pedimento, $partida, $xml) {
        try {
            $data = array(
                'rfc' => $rfcAgente,
                'estado' => $estado,
                'numOperacion' => $num_operacion,
                'patente' => $patente,
                'aduana' => $aduana,
                'pedimento' => $pedimento,
                'partida' => $partida,
                'xml' => $xml,
                'creado' => date('Y-m-d H:i:s'),
            );
            $added = $this->_db_table->insert($data);
            if ($added) {
                return true;
            }
            return null;
        } catch (Exception $e) {
            echo "<b>Exception on " . __METHOD__ . "</b>" . $e->getMessage();
            die();
        }
    }

    public function obtenerPartidasSinAnalizar() {
        try {
            $select = $this->_db_table->select();
            $select->where('analizado = 0')
                    ->where('estado = 1');
            if (($result = $this->_db_table->fetchAll($select))) {
                return $result->toArray();
            }
            return null;
        } catch (Exception $e) {
            echo "<b>Exception on " . __METHOD__ . "</b>" . $e->getMessage();
            die();
        }
    }

    public function establecerAnalizado() {
        try {
            
        } catch (Exception $e) {
            echo "<b>Exception on " . __METHOD__ . "</b>" . $e->getMessage();
            die();
        }
    }

}
