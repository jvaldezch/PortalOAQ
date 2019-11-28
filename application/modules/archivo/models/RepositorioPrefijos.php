<?php

class Archivo_Model_RepositorioPrefijos {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Archivo_Model_DbTable_RepositorioPrefijos();
    }

    public function find(Archivo_Model_Table_RepositorioPrefijos $table) {
        try {
            $stmt = $this->_db_table->fetchRow(
                    $this->_db_table->select()
                            ->where("idDocumento = ?", $table->getIdDocumento())
            );
            if (0 == count($stmt)) {
                return;
            }
            $table->setId($stmt->id);
            $table->setIdDocumento($stmt->idDocumento);
            $table->setPrefijo($stmt->prefijo);
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function findPrefix(Archivo_Model_Table_RepositorioPrefijos $table) {
        try {
            $stmt = $this->_db_table->fetchRow(
                    $this->_db_table->select()
                            ->where("prefijo REGEXP '^{$table->getPrefijo()}_'")
            );
            if (0 == count($stmt)) {
                return;
            }
            $table->setId($stmt->id);
            $table->setIdDocumento($stmt->idDocumento);
            $table->setPrefijo($stmt->prefijo);
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        } catch (Exception $ex) {
            throw new Exception("Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function fetchAll() {
        try {
            $stmt = $this->_db_table->fetchAll(
                    $this->_db_table->select()
                            ->setIntegrityCheck(false)
                            ->from(array("p" => "repositorio_prefijos"), array("*"))
                            ->joinLeft(array("d" => "documentos"), "d.id = p.idDocumento", array("nombre"))
                            ->order("nombre ASC")
            );
            if (0 == count($stmt)) {
                return;
            }
            return $stmt->toArray();
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        } catch (Exception $ex) {
            throw new Exception("Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function save(Archivo_Model_Table_RepositorioPrefijos $table) {
        try {
            $data = array(
                "id" => $table->getId(),
                "idDocumento" => $table->getIdDocumento(),
                "prefijo" => $table->getPrefijod(),
            );
            if (null === ($id = $table->getId())) {
                unset($data["id"]);
                $id = $this->_db_table->insert($data);
                $table->setId($id);
            } else {
                $this->_db_table->update($data, array("id = ?" => $id));
            }
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    public function todos() {
        try {
            $sql = $this->_db_table->select()
                    ->where("activo = 1")
                    ->where("visible = 1")
                    ->order("descripcion ASC");
            $stmt = $this->_db_table->fetchAll($sql);
            if (count($stmt)) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
    
    public function obtenerPrefijo($tipoArchivo) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("prefijo"))
                    ->where("idDocumento = ?", $tipoArchivo);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->prefijo;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

}
