<?php

class V2_Model_Trafico_UsuarioAduanas {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new V2_Model_Trafico_DbTable_UsuarioAduanas();
    }
    
    public function obtenerAduanas($idUsuario) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->distinct()
                    ->from(array("a" => "v2_usuario_aduanas"), array("idAduana"))
                    ->joinLeft(array("p" => "v2_aduanas"), "a.idAduana = p.id", array("patente", "aduana", "nombre"))
                    ->where("a.idUsuario = ?", $idUsuario);
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                $arr[""] = "---";
                foreach ($stmt as $item) {
                    $arr[$item["idAduana"]] = $item["patente"] . "-" . $item["aduana"] . " " . $item["nombre"];
                }
                return $arr;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
