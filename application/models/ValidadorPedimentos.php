<?php

/**
 * Description of 
 *
 * @author Jaime E. Valdez <jvaldezch@gmail.com>
 */
class Application_Model_ValidadorPedimentos {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Application_Model_DbTable_ValidadorPedimentos();
    }

    public function agregar($idArchivo, $patente, $aduana, $pedimento, $tipoMovimiento, $firmaDesistir, $usuario) {
        try {
            $data = array(
                "idArchivo" => $idArchivo,
                "patente" => $patente,
                "aduana" => $aduana,
                "pedimento" => $pedimento,
                "tipoMovimiento" => $tipoMovimiento,
                "firmaDesistir" => $firmaDesistir,
                "usuario" => $usuario,
                "creado" => date("Y-m-d H:i:s"),
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

    public function verificar($idArchivo) {
        try {
            $select = $this->_db_table->select()
                    ->from($this->_db_table, array("id"))
                    ->where("idArchivo = ?", $idArchivo);
            $result = $this->_db_table->fetchRow($select);
            if ($result) {
                return true;
            }
            return false;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
