<?php

class Bitacora_Model_BitacoraPedimentos {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Bitacora_Model_DbTable_BitacoraPedimentos();
    }

    protected function _filters(Zend_Db_Select $sql, $filterRules) {
        if (isset($filterRules)) {
            $filter = json_decode(html_entity_decode($filterRules));
            foreach ($filter AS $item) {
                if ($item->field == "blGuia" && $item->value != "") {
                    $sql->where("blGuia LIKE ?", "%" . trim($item->value) . "%");
                }
                if ($item->field == "pedimento" && $item->value != "") {
                    $sql->where("pedimento LIKE ?", "%" . trim($item->value) . "%");
                }
                if ($item->field == "referencia" && $item->value != "") {
                    $sql->where("referencia LIKE ?", "%" . trim($item->value) . "%");
                }
            }
        }
    }

    protected function _total($filterRules = null) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("count(*) as total"))
                    ->where("idTrafico IS NULL")
                    ->where("estatus NOT IN (3)");
            $this->_filters($sql, $filterRules);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return (int) $stmt->total;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtener($page = null, $rows = null, $filterRules = null, $sort = null, $order = null) {
        try {
            $sql = $this->_db_table->select()
                    ->where("idTrafico IS NULL")
                    ->where("estatus NOT IN (3)")
                    ->where("pedimento IS NOT NULL AND referencia IS NOT NULL")
                    ->order("pedimento ASC");
            if (isset($page) && isset($rows)) {
                $sql->limit($rows, ($page - 1) * $rows);
            }
            if (isset($sort) && isset($order)) {
                $sql->order($sort . " " . $order);
            } else {
                $sql->order("creado DESC");
            }
            $this->_filters($sql, $filterRules);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return array(
                    "total" => (int) $this->_total($filterRules),
                    "rows" => $stmt->toArray()
                );
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function obtenerGuiasPorValidar() {
        try {
            $sql = $this->_db_table->select()
                    ->where("idTrafico IS NULL")
                    ->where("estatus = 1 AND blGuia IS NOT NULL")
                    ->order("blGuia DESC");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function obtenerGuias($page = null, $rows = null, $filterRules = null, $sort = null, $order = null) {
        try {
            $sql = $this->_db_table->select()
                    ->where("idTrafico IS NULL")
                    ->where("estatus NOT IN (3) AND blGuia IS NOT NULL")
                    ->order("blGuia ASC");
            if (isset($page) && isset($rows)) {
                $sql->limit($rows, ($page - 1) * $rows);
            }
            if (isset($sort) && isset($order)) {
                $sql->order($sort . " " . $order);
            } else {
                $sql->order("creado DESC");
            }
            $this->_filters($sql, $filterRules);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return array(
                    "total" => (int) $this->_total($filterRules),
                    "rows" => $stmt->toArray()
                );
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function ultimoPedimento($patente, $aduana) {
        try {
            $sql = $this->_db_table->select()
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana)
                    ->order("pedimento DESC");
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->pedimento;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function agregar($arr) {
        try {
            $array = array(
                "idTrafico" => isset($arr["idTrafico"]) ? $arr["idTrafico"] : null,
                "idCliente" => isset($arr["idCliente"]) ? $arr["idCliente"] : null,
                "idAduana" => isset($arr["idAduana"]) ? $arr["idAduana"] : null,
                "patente" => isset($arr["patente"]) ? $arr["patente"] : null,
                "aduana" => isset($arr["aduana"]) ? $arr["aduana"] : null,
                "pedimento" => isset($arr["pedimento"]) ? $arr["pedimento"] : null,
                "referencia" => isset($arr["referencia"]) ? $arr["referencia"] : null,
                "estatus" => isset($arr["estatus"]) ? $arr["estatus"] : null,
                "rfcCliente" => isset($arr["rfcCliente"]) ? $arr["rfcCliente"] : null,
                "nombreCliente" => isset($arr["nombreCliente"]) ? $arr["nombreCliente"] : null,
                "estatus" => isset($arr["estatus"]) ? $arr["estatus"] : null,
                "tipoOperacion" => isset($arr["tipoOperacion"]) ? $arr["tipoOperacion"] : null,
                "clavePedimento" => isset($arr["clavePedimento"]) ? $arr["clavePedimento"] : null,
                "blGuia" => isset($arr["blGuia"]) ? $arr["blGuia"] : null,
                "observaciones" => isset($arr["observaciones"]) ? $arr["observaciones"] : null,
                "agrupados" => isset($arr["agrupados"]) ? $arr["agrupados"] : null,
                "creado" => isset($arr["creado"]) ? $arr["creado"] : null,
                "creadoPor" => isset($arr["creadoPor"]) ? $arr["creadoPor"] : null,
            );
            $stmt = $this->_db_table->insert($array);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function actualizarConsecutivo($id, $pedimento, $referencia) {
        try {
            $arr = array(
                "pedimento" => $pedimento,
                "referencia" => $referencia,
            );
            $stmt = $this->_db_table->update($arr, array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function obtenerDatos($id) {
        try {
            $sql = $this->_db_table->select()
                    ->where("id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function iniciarPrevio($id) {
        try {
            $stmt = $this->_db_table->update(array("fechaApertura" => date("Y-m-d H:i:s")), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function revalidado($id) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("fechaRevalidacion"))
                    ->where("fechaRevalidacion IS NULL")
                    ->where("id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->fechaRevalidacion;
            }
            return;            
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function revalidar($id) {
        try {
            $stmt = $this->_db_table->update(array("fechaRevalidacion" => date("Y-m-d H:i:s")), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function actualizarGuia($id, $arr) {
        try {
            $array = array(
                "nombreProveedor" => isset($arr["nombreProveedor"]) ? $arr["nombreProveedor"] : null,
                "pesoBruto" => isset($arr["pesoBruto"]) ? (float) $arr["pesoBruto"] : null,
                "paisOrigen" => isset($arr["paisOrigen"]) ? $arr["paisOrigen"] : null,
                "numeroFotos" => isset($arr["numeroFotos"]) ? $arr["numeroFotos"] : null,
                "completa" => isset($arr["completa"]) ? $arr["completa"] : null,
                "modelo" => isset($arr["modelo"]) ? $arr["modelo"] : null,
                "averia" => isset($arr["averia"]) ? $arr["averia"] : null,
                "marca" => isset($arr["marca"]) ? $arr["marca"] : null,
                "numeroParte" => isset($arr["numeroParte"]) ? $arr["numeroParte"] : null,
                "numeroSerie" => isset($arr["numeroSerie"]) ? $arr["numeroSerie"] : null,
                "numeroPiezas" => isset($arr["numeroPiezas"]) ? $arr["numeroPiezas"] : null,
                "selloFiscal" => isset($arr["selloFiscal"]) ? $arr["selloFiscal"] : null,
                "fechaEta" => isset($arr["fechaEta"]) ? $arr["fechaEta"] : null,
                "fechaColocacion" => isset($arr["fechaColocacion"]) ? $arr["fechaColocacion"] : null,
                "fechaApertura" => isset($arr["fechaApertura"]) ? $arr["fechaApertura"] : null,
                "actualizado" => isset($arr["actualizado"]) ? $arr["actualizado"] : null,
                "actualizadoPor" => isset($arr["actualizadoPor"]) ? $arr["actualizadoPor"] : null,
            );
            $stmt = $this->_db_table->update($array, array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function update($id, $arr) {
        try {
            $stmt = $this->_db_table->update($arr, array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function actualizar($id, $arr) {
        try {
            $array = array(
                "idTrafico" => isset($arr["idTrafico"]) ? $arr["idTrafico"] : null,
                "idCliente" => isset($arr["idCliente"]) ? $arr["idCliente"] : null,
                "idAduana" => isset($arr["idAduana"]) ? $arr["idAduana"] : null,
                "patente" => isset($arr["patente"]) ? $arr["patente"] : null,
                "aduana" => isset($arr["aduana"]) ? $arr["aduana"] : null,
                "pedimento" => isset($arr["pedimento"]) ? $arr["pedimento"] : null,
                "referencia" => isset($arr["referencia"]) ? $arr["referencia"] : null,
                "estatus" => isset($arr["estatus"]) ? $arr["estatus"] : null,
                "rfcCliente" => isset($arr["rfcCliente"]) ? $arr["rfcCliente"] : null,
                "nombreCliente" => isset($arr["nombreCliente"]) ? $arr["nombreCliente"] : null,
                "tipoOperacion" => isset($arr["tipoOperacion"]) ? $arr["tipoOperacion"] : null,
                "clavePedimento" => isset($arr["clavePedimento"]) ? $arr["clavePedimento"] : null,
                "blGuia" => isset($arr["blGuia"]) ? $arr["blGuia"] : null,
                "nombreProveedor" => isset($arr["nombreProveedor"]) ? $arr["nombreProveedor"] : null,
                "observaciones" => isset($arr["observaciones"]) ? $arr["observaciones"] : null,
                "actualizado" => isset($arr["actualizado"]) ? $arr["actualizado"] : null,
                "actualizadoPor" => isset($arr["actualizadoPor"]) ? $arr["actualizadoPor"] : null,
            );
            $stmt = $this->_db_table->update($array, array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function limpiar($id) {
        try {
            $array = array(
                "idTrafico" => null,
                "idCliente" => null,
                "estatus" => 1,
                "rfcCliente" => null,
                "nombreCliente" => null,
                "tipoOperacion" => null,
                "clavePedimento" => null,
                "blGuia" => null,
                "nombreProveedor" => null,
                "observaciones" => null,
                "pesoBruto" => null,
                "paisOrigen" => null,
                "numeroFotos" => null,
                "completa" => null,
                "modelo" => null,
                "averia" => null,
                "marca" => null,
                "numeroParte" => null,
                "numeroSerie" => null,
                "numeroPiezas" => null,
                "selloFiscal" => null,
                "fechaEta" => null,
                "fechaColocacion" => null,
                "fechaApertura" => null,
                "actualizado" => null,
                "actualizadoPor" => null,
            );
            $stmt = $this->_db_table->update($array, array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function borrar($id) {
        try {
            $stmt = $this->_db_table->delete(array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function actualizarEstatus($id, $estatus) {
        try {
            $stmt = $this->_db_table->update(array("pedimento" => null, "referencia" => null, "estatus" => $estatus), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
