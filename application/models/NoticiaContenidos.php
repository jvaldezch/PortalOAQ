<?php

/**
 * Description of UserEmails
 *
 * @author Jaime E. Valdez <jvaldezch@gmail.com>
 */
class Application_Model_NoticiaContenidos {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Application_Model_DbTable_NoticiaContenidos();
    }

    public function obtenerContenido($idNoticia) {
        try {
            $select = $this->_db_table->select()
                    ->where('idNoticia = ?', $idNoticia);
            $result = $this->_db_table->fetchRow($select);
            if ($result) {
                return $result->toArray();
            }
            return false;
        } catch (Zend_Db_Exception $e) {
            throw new Exception("<b>DB Exception at " . __METHOD__ . "</b>" . $e->getMessage());
        } catch (Exception $e) {
            throw new Exception("<b>Exception at " . __METHOD__ . "</b>" . $e->getMessage());
        }
    }

    public function actualizar($idNoticia, $data) {
        try {
            $stmt = $this->_db_table->update($data, array('idNoticia = ?' => $idNoticia));
            if ($stmt) {
                return true;
            }
            return false;
        } catch (Zend_Db_Exception $e) {
            throw new Exception("<b>DB Exception at " . __METHOD__ . "</b>" . $e->getMessage());
        } catch (Exception $e) {
            throw new Exception("<b>Exception at " . __METHOD__ . "</b>" . $e->getMessage());
        }
    }

    public function agregar($data) {
        try {
            $stmt = $this->_db_table->insert($data);
            if ($stmt) {
                return $stmt;
            }
            return false;
        } catch (Zend_Db_Exception $e) {
            throw new Exception("<b>DB Exception at " . __METHOD__ . "</b>" . $e->getMessage());
        } catch (Exception $e) {
            throw new Exception("<b>Exception at " . __METHOD__ . "</b>" . $e->getMessage());
        }
    }

}
