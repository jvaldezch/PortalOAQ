<?php

class Automatizacion_Model_RptCuentas
{

    protected $_db_table;

    public function __construct()
    {
        $this->_db_table = new Automatizacion_Model_DbTable_RptCuentas();
    }

    public function verificar($idSucursal, $folio, $patente, $aduana, $pedimento, $referencia)
    {
        try {
            $sql = $this->_db_table->select()
                ->where('idSucursal = ?', $idSucursal)
                ->where('folio = ?', $folio)
                ->where('patente = ?', $patente)
                ->where('aduana = ?', $aduana)
                ->where('pedimento = ?', $pedimento)
                ->where('referencia = ?', $referencia);
            $stmt = $this->_db_table->fetchRow($sql, array());
            if ($stmt) {
                return true;
            }
            return;
        } catch (Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function agregar($arr)
    {
        try {
            $stmt = $this->_db_table->insert($arr);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function sinAnalizar($folio = null, $limit = 250)
    {
        try {
            $sql = $this->_db_table->select()
                ->from($this->_db_table, array('id', 'folio'));
            if (isset($folio)) {
                $sql->where("folio = ?", $folio);
            } else {
                $sql->where('analizado IS NULL')
                    ->where('conceptos IS NULL')
                    ->where('noConceptos IS NULL')
                    ->limit($limit);
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function folio($id)
    {
        try {
            $sql = $this->_db_table->select()
                ->from($this->_db_table, array('*'))
                ->where('folio = ?', $id);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function sinTrafico($limit = null)
    {
        try {
            $sql = $this->_db_table->select()
                ->from($this->_db_table, array('id', 'folio', 'patente', 'aduana', 'pedimento', 'referencia'))
                ->where('idTrafico IS NULL');
            if (isset($limit)) {
                $sql->limit($limit);
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function sinPagar($limit = null, $fecha = null)
    {
        try {
            $sql = $this->_db_table->select()
                ->from($this->_db_table, array('id', 'folio', 'patente', 'aduana', 'pedimento', 'referencia', 'total'))
                ->where('analizado = 1')
                ->where('cancelada IS NULL')
                ->where('pagada IS NULL')
                ->order('folio ASC');
            if (isset($fecha)) {
                $sql->where('fechaFacturacion >= ?', date('Y-m-d', strtotime($fecha)));
            }
            if (isset($limit)) {
                $sql->limit($limit);
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function obtenerFolio($folio)
    {
        try {
            $sql = $this->_db_table->select()
                ->from($this->_db_table, array('id', 'folio', 'patente', 'aduana', 'pedimento', 'referencia', 'total'))
                ->where('folio = ?', $folio);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function actualizar($id, $arr)
    {
        try {
            $stmt = $this->_db_table->update($arr, array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    protected function _filters(Zend_Db_Select $sql, $filterRules = null)
    {
        if (isset($filterRules)) {
            $filter = json_decode(html_entity_decode($filterRules));
            foreach ($filter AS $item) {
                if ($item->field == "pedimento" && $item->value != "") {
                    $sql->where("pedimento LIKE ?", "%" . trim($item->value) . "%");
                }
                if ($item->field == "referencia" && $item->value != "") {
                    $sql->where("referencia LIKE ?", "%" . trim($item->value) . "%");
                }
                if ($item->field == "aduana" && $item->value != "") {
                    $sql->where("aduana LIKE ?", "%" . trim($item->value) . "%");
                }
                if ($item->field == "patente" && $item->value != "") {
                    $sql->where("patente LIKE ?", "%" . trim($item->value) . "%");
                }
                if ($item->field == "folio" && $item->value != "") {
                    $sql->where("folio LIKE ?", "%" . trim($item->value) . "%");
                }
            }
        }
    }

    public function obtener($filterRules = null)
    {
        try {
            $sql = $this->_db_table->select()
                ->order('fechaFacturacion DESC');
            if (isset($filterRules)) {
                $this->_filters($sql, $filterRules);
            }
            return $sql;
        } catch (Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function obtenerDatos($id)
    {
        try {
            $sql = $this->_db_table->select()
                ->where('id = ?', $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

}
