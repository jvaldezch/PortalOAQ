<?php

class Automatizacion_Model_RptPedimentoDesglose
{
    protected $_db_table;

    public function __construct()
    {
        $this->_db_table = new Automatizacion_Model_DbTable_RptPedimentoDesglose();
    }

    public function verificarDesglosePedimento($idPedimento)
    {
        try {
            $sql = $this->_db_table->select()
                ->where('idPedimento = ?', $idPedimento);
            $stmt = $this->_db_table->fetchRow($sql, array());
            if ($stmt) {
                return true;
            }
            return;
        } catch (Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function wsObtenerFacturas($idPedimento)
    {
        try {
            $sql = $this->_db_table->select()
                ->distinct()
                ->from($this->_db_table, array('numFactura', 'cove', 'taxId', 'incoterm', 'fechaFactura', 'valorFacturaUsd', 'valorFacturaMonExt', 'paisFactura', 'divisa', 'factorMonExt', 'nomProveedor'))
                ->where('idPedimento = ?', $idPedimento);
                // ->group('numFactura', 'cove');
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function wsObtenerPartes($idPedimento, $numFactura)
    {
        try {
            $sql = $this->_db_table->select()
                ->from($this->_db_table, array('*'))
                ->where('idPedimento = ?', $idPedimento)
                ->where('numFactura = ?', $numFactura);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function agregarDesglosePedimento($idPedimento, $row)
    {
        try {
            $arr = array();
            $arr["idPedimento"] = $idPedimento;
            $arr["fechaFactura"] = date("Y-m-d H:i:s", strtotime($row["fechaFactura"]));
            $values = array(
                'numFactura',
                'fraccion',
                'cove',
                'taxId',
                'nomProveedor' => array('nomProveedor', 'nombreProveedor'),
                'incoterm',
                'valorFacturaUsd',
                'valorFacturaMonExt',
                'divisa',
                'paisFactura',
                'factorMonExt',
                'numParte',
                'descripcion',
                'ordenFraccion',
                'precioUnitario',
                'umc',
                'cantUmc',
                'umt',
                'cantUmt',
                'tasaAdvalorem',
                'paisOrigen',
                'tlc',
                'prosec',
                'paisVendedor',
                'patenteOrig',
                'aduanaOrig',
                'pedimentoOrig',
                'ieps',
                'tlcue',
                'observacion',
                'ordenFactura',
                'ordenCaptura',
                'ordenFraccion',
                'ordenAgrupacion',
                'iva'
            );
            foreach ($values as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $v) {
                        if (isset($arr[$key]) && $arr[$key] !== null) {
                            break;
                        }
                        $arr[$key] = $this->_check($row, $v);
                    }
                } else {
                    $arr[$value] = $this->_check($row, $value);
                }
            }
            $arr["creado"] = date('Y-m-d H:i:s');
            $stmt = $this->_db_table->insert($arr);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    protected function _check(array $arr, $value)
    {
        if (isset($arr[$value]) && !is_array(isset($arr[$value]))) {
            return $arr[$value];
        }
        return null;
    }
}
