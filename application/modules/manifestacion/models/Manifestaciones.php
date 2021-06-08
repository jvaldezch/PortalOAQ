<?php

class Manifestacion_Model_Manifestaciones
{
    protected $_db_table;

    public function __construct()
    {
        $this->_db_table = new Manifestacion_Model_DbTable_Manifestaciones();
    }

    public function datos($id)
    {
        try {
            $sql = $this->_db_table->select()
                ->setIntegrityCheck(false)
                ->from(array("m" => "manifestaciones"), array("*"))
                ->joinInner(array("c" => "trafico_clientes"), "c.id = m.idCliente", array("nombre AS nombreCliente", "rfc"))
                ->where("m.id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $e) {
            throw new Exception("Db Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function todas($select = false)
    {
        try {
            $sql = $this->_db_table->select()
                ->setIntegrityCheck(false)
                ->from(array("m" => "manifestaciones"), array("*"));
            if ($select == true) {
                return $sql;
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $e) {
            throw new Exception("Db Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function verificar($idAduana, $idCliente, $pedimento, $referencia)
    {
        try {
            $sql = $this->_db_table->select()
                ->from($this->_db_table, array("id"))
                ->where("idAduana = ?", $idAduana)
                ->where("idCliente = ?", $idCliente)
                ->where("pedimento = ?", $pedimento)
                ->where("referencia = ?", $referencia)
                ->where("estatus NOT IN (4)");
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function nueva($idAduana, $idCliente, $patente, $aduana, $tipoOperacion, $cvePedimento, $pedimento, $referencia, $regimenAduanero)
    {
        try {
            $data = array(
                "idCliente" => $idCliente,
                "idAduana" => $idAduana,
                "patente" => str_pad($patente, 4, '0', STR_PAD_LEFT),
                "aduana" => str_pad($aduana, 3, '0', STR_PAD_LEFT),
                "pedimento" => str_pad($pedimento, 7, '0', STR_PAD_LEFT),
                "tipoOperacion" => $tipoOperacion,
                "cvePedimento" => $cvePedimento,
                "referencia" => $referencia,
                "regimenAduanero" => $regimenAduanero,
            );
            $stmt = $this->_db_table->insert($data);
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
}
