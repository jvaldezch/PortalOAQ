<?php

class V2_Model_Usuarios {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new V2_Model_DbTable_Usuarios();
    }

    /**
     * 
     * @return type
     * @throws Exception
     */
    public function fetchAll() {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("id", "usuario"))
                    ->order("usuario ASC");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param Trafico_Model_Table_Contactos $tbl
     * @return type
     * @throws Exception
     */
    public function find(Trafico_Model_Table_Contactos $tbl) {
        try {
            $slc = $this->_db_table->select()
                    ->where("idAduana = ?", $tbl->getIdAduana())
                    ->where("email = ?", $tbl->getEmail());
            $stmt = $this->_db_table->fetchAll($slc);
            if ($stmt) {
                $tbl->setId($stmt->id);
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param Trafico_Model_Table_Contactos $tbl
     * @throws Exception
     */
    public function save(Trafico_Model_Table_Contactos $tbl) {
        try {
            $arr = array(
                "idAduana" => $tbl->getIdAduana(),
                "nombre" => $tbl->getNombre(),
                "email" => $tbl->getEmail(),
                "tipoContacto" => $tbl->getTipoContacto(),
                "creacion" => $tbl->getCreacion(),
                "cancelacion" => $tbl->getCancelacion(),
                "comentario" => $tbl->getComentario(),
                "deposito" => $tbl->getDeposito(),
                "habilitado" => $tbl->getHabilitado(),
                "creado" => $tbl->getCreado(),
                "creadoPor" => $tbl->getCreadoPor(),
            );
            if (null === ($id = $tbl->getId())) {
                $id = $this->_db_table->insert($arr);
                $tbl->setId($id);
            } else {
                $this->_db_table->update($arr, array("id = ?" => $id));
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
    
    /**
     * 
     * @param int $idContacto
     * @return boolean
     * @throws Exception
     */
    public function delete($idContacto) {
        try {            
            $stmt = $this->_db_table->delete(array("id = ?" => $idContacto));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
