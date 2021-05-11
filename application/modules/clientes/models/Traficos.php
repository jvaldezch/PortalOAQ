<?php

class Clientes_Model_Traficos
{

    protected $_db_table;

    public function __construct()
    {
        $this->_db_table = new Clientes_Model_DbTable_Traficos();
    }

    public function obtenerTraficoCliente($rfcCliente, $fechaInicio = null, $fechaFin = null, $filterRules = null)
    {
        try {
            $sql = $this->_db_table->select()
                ->setIntegrityCheck(false)
                ->from(array("t" => "traficos"), array("*"))
                ->joinLeft(array("u" => "usuarios"), "t.idUsuario = u.id", array("nombre"))
                ->where("t.estatus NOT IN (4)")
                ->where("t.rfcCliente = ?", $rfcCliente);
            if (isset($fechaInicio) && isset($fechaFin)) {
                $sql->where("t.fechaLiberacion >= ?", date("Y-m-d H:i:s", strtotime($fechaInicio)))
                    ->where("t.fechaLiberacion <= ?", date("Y-m-d H:i:s", strtotime($fechaFin)));
            }
            if (isset($filterRules)) {
                $filter = json_decode(html_entity_decode($filterRules));
                foreach ($filter as $item) {
                    if ($item->field == "pedimento" && $item->value != "") {
                        $sql->where("t.pedimento LIKE ?", "%" . trim($item->value) . "%");
                    }
                    if ($item->field == "referencia" && $item->value != "") {
                        $sql->where("t.referencia LIKE ?", "%" . trim($item->value) . "%");
                    }
                    if ($item->field == "aduana" && $item->value != "") {
                        $sql->where("t.aduana LIKE ?", "%" . trim($item->value) . "%");
                    }
                    if ($item->field == "patente" && $item->value != "") {
                        $sql->where("t.patente LIKE ?", "%" . trim($item->value) . "%");
                    }
                    if ($item->field == "blGuia" && $item->value != "") {
                        $sql->where("t.blGuia LIKE ?", "%" . trim($item->value) . "%");
                    }
                    if ($item->field == "contenedorCaja" && $item->value != "") {
                        $sql->where("t.contenedorCaja LIKE ?", "%" . trim($item->value) . "%");
                    }
                }
            }
            return $sql;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
}
