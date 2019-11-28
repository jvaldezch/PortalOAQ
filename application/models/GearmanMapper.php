<?php

/**
 * Description of UserEmails
 *
 * @author Jaime E. Valdez <jvaldezch@gmail.com>
 */
class Application_Model_GearmanMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Application_Model_DbTable_Gearman();
    }

    public function getProcessPath($proceso) {
        try {
            $select = $this->_db_table->select()
                    ->from("gearman", array("ruta"))
                    ->where("proceso = ?", $proceso);
            $result = $this->_db_table->fetchRow($select);
            if ($result) {
                return $result["ruta"];
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
}
