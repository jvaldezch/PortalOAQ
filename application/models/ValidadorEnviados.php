<?php

/**
 * Description of UserEmails
 *
 * @author Jaime E. Valdez <jvaldezch@gmail.com>
 */
class Application_Model_ValidadorEnviados {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Application_Model_DbTable_ValidadorEnviados();
    }

    public function agregar($patente, $aduana, $nomArchivo, $contenido, $hash, $usuario) {
        try {
            $data = array(
                "patente" => $patente,
                "aduana" => $aduana,
                "nomArchivo" => $nomArchivo,
                "contenido" => $contenido,
                "hash" => $hash,
                "usuario" => $usuario,
                "enviado" => date("Y-m-d H:i:s"),
            );
            $added = $this->_db_table->insert($data);
            if ($added) {
                return $added;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    public function verificar($nomArchivo, $hash) {
        try {
            $select = $this->_db_table->select()
                    ->from($this->_db_table,array("id"))
                    ->where("nomArchivo = ?", $nomArchivo)
                    ->where("hash = ?", $hash)
                    ->where("respuesta IS NULL");
            $result = $this->_db_table->fetchRow($select);
            if($result) {
                return $result["id"];
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
