<?php

class Automatizacion_Model_ArchivosValidacionBancoMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Automatizacion_Model_DbTable_ArchivosValidacionBanco();
    }

    public function findFile($patente, $aduana, $pedimento) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("p" => "archivos_validacion_pago"), array("patente", "aduana", "pedimento", "rfcImportador", "firmaBanco", "numOperacion", "idArchivoValidacion"))
                    ->joinLeft(array("a" => "archivos_validacion"), "p.idArchivoValidacion = a.id", array("archivo", "archivoNombre", "contenido"))
                    ->where("p.patente = ?", $patente)
                    ->where("p.aduana LIKE ?", substr($aduana, 0, 2) . "%")
                    ->where("p.pedimento = ?", $pedimento);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return null;
        } catch (Zend_Db_Exception $ex) {
            throw new Exception("Zend DB Exception " . __METHOD__ . ": " . $ex->getMessage());
        }
    }

}
