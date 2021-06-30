<?php

class Manifestacion_Model_ManifestacionEdocuments
{
    protected $_db_table;

    public function __construct()
    {
        $this->_db_table = new Manifestacion_Model_DbTable_ManifestacionEdocuments();
    }

    public function obtener($id)
    {
        try {
            $sql = $this->_db_table->select()
                ->from($this->_db_table, array("*"))
                ->where("id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $e) {
            throw new Exception("Db Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function todos($id)
    {
        try {
            $sql = $this->_db_table->select()
                ->from($this->_db_table, array("*"))
                ->where("idManifestacion = ?", $id)
                ->order("edocument ASC");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $e) {
            throw new Exception("Db Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function verificar($idManifestacion, $edocument)
    {
        try {
            $sql = $this->_db_table->select()
                ->from($this->_db_table, array("id"))
                ->where("idManifestacion = ?", $idManifestacion)
                ->where("edocument = ?", $edocument);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function agregar($id, $edocument)
    {
        try {
            $tipo = 'ED';
            if (preg_match('/^COVE/i', $edocument)) {
                $tipo = 'CV';
            }
            $data = array(
                "idManifestacion" => $id,
                "tipo" => $tipo,
                "edocument" => $edocument,
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

    public function actualizar($id, $arr)
    {
        try {
            $stmt = $this->_db_table->update($arr, array("id = ?" => $id));
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
}
