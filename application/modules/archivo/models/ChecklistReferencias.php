<?php

class Archivo_Model_ChecklistReferencias
{

    protected $_db_table;

    public function __construct()
    {
        $this->_db_table = new Archivo_Model_DbTable_ChecklistReferencias();
    }

    public function save(Archivo_Model_Table_ChecklistReferencias $t)
    {
        try {
            $row = array(
                "id" => $t->getId(),
                "idTrafico" => $t->getIdTrafico(),
                "patente" => $t->getPatente(),
                "aduana" => $t->getAduana(),
                "pedimento" => $t->getPedimento(),
                "referencia" => $t->getReferencia(),
                "checklist" => $t->getChecklist(),
                "revision" => $t->getRevision(),
                "observaciones" => $t->getObservaciones(),
                "revisionOperaciones" => $t->getRevisionOperaciones(),
                "fechaRevisionOperaciones" => $t->getFechaRevisionOperaciones(),
                "revisionAdministracion" => $t->getRevisionAdministracion(),
                "fechaRevisionAdministracion" => $t->getFechaRevisionAdministracion(),
                "completo" => $t->getCompleto(),
                "fechaCompleto" => $t->getFechaCompleto(),
                "creado" => $t->getCreado(),
                "actualizado" => $t->getActualizado(),
            );
            if (null === ($id = $t->getId())) {
                unset($row["id"]);
                $id = $this->_db_table->insert($row);
                $t->setId($id);
            } else {
                $this->_db_table->update($row, array("id = ?" => $id));
            }
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function update(Archivo_Model_Table_ChecklistReferencias $t)
    {
        try {
            $row = array(
                "checklist" => $t->getChecklist(),
                "revision" => $t->getRevision(),
                "revisionOperaciones" => $t->getRevisionOperaciones(),
                "fechaRevisionOperaciones" => $t->getFechaRevisionOperaciones(),
                "revisionAdministracion" => $t->getRevisionAdministracion(),
                "fechaRevisionAdministracion" => $t->getFechaRevisionAdministracion(),
                "completo" => $t->getCompleto(),
                "fechaCompleto" => $t->getFechaCompleto(),
                "observaciones" => $t->getObservaciones(),
                "actualizado" => $t->getActualizado(),
            );
            $stmt = $this->_db_table->update($row, array("id = ?" => $t->getId()));
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function find(Archivo_Model_Table_ChecklistReferencias $t)
    {
        try {
            $stmt = $this->_db_table->fetchRow(
                $this->_db_table->select()
                    ->where("patente = ?", $t->getPatente())
                    ->where("aduana = ?", $t->getAduana())
                    ->where("referencia = ?", $t->getReferencia())
            );
            if (0 == count($stmt)) {
                return;
            }
            $t->setId($stmt->id);
            $t->setPatente($stmt->patente);
            $t->setAduana($stmt->aduana);
            $t->setPedimento($stmt->pedimento);
            $t->setReferencia($stmt->referencia);
            $t->setChecklist($stmt->checklist);
            $t->setRevision($stmt->revision);
            $t->setObservaciones($stmt->observaciones);
            $t->setRevisionOperaciones($stmt->revisionOperaciones);
            $t->setFechaRevisionOperaciones($stmt->fechaRevisionOperaciones);
            $t->setRevisionAdministracion($stmt->revisionAdministracion);
            $t->setFechaRevisionAdministracion($stmt->fechaRevisionAdministracion);
            $t->setCompleto($stmt->completo);
            $t->setFechaCompleto($stmt->fechaCompleto);
            $t->setCreado($stmt->creado);
            $t->setActualizado($stmt->actualizado);
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function buscar($patente, $aduana, $referencia)
    {
        try {
            $stmt = $this->_db_table->fetchRow(
                $this->_db_table->select()
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana)
                    ->where("referencia = ?", $referencia)
            );
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

    public function buscarChecklist($patente, $aduana, $pedimento, $referencia)
    {
        try {
            $stmt = $this->_db_table->fetchRow(
                $this->_db_table->select()
                    ->where("patente = ?", $patente)
                    ->where("aduana = ?", $aduana)
                    ->where("pedimento = ?", $pedimento)
                    ->where("referencia = ?", $referencia)
            );
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }
}
