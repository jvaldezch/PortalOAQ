<?php

/**
 * Description of UserEmails
 *
 * @author Jaime E. Valdez <jvaldezch@gmail.com>
 */
class Application_Model_Mensajes {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Application_Model_DbTable_Mensajes();
    }

    /**
     * 
     * @param int $idTrafico
     * @return type
     * @throws Exception
     */
    public function obtenerMensajes($idTrafico) {
        try {
            $fields = array(
                "*",
                new Zend_Db_Expr("(select u.usuario from usuarios u where u.id = m.idUsuarioDe) as usuarioDe"),
                new Zend_Db_Expr("(select u.usuario from usuarios u where u.id = m.idUsuarioPara) as usuarioPara")
            );
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("m" => "mensajes"), $fields)
                    ->joinLeft(array("t" => "repositorio_temporal"), "t.idMensaje = m.id", array("id AS idArchivo", "nombreArchivo"))
                    ->where("m.idTrafico = ?", $idTrafico)
                    ->order("m.creado ASC");
            $stmt = $this->_db_table->fetchAll($sql);
            if (0 == count($stmt)) {
                return;
            }
            return $stmt->toArray();
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param array $aduanas
     * @return null|array
     * @throws Exception
     */
    public function obtenerMensajesSinLeer($aduanas = null) {
        try {
            $fields = array(
                "*",
                new Zend_Db_Expr("(select u.usuario from usuarios u where u.id = m.idUsuarioDe) as usuarioDe"),
                new Zend_Db_Expr("(select u.usuario from usuarios u where u.id = m.idUsuarioPara) as usuarioPara")
            );
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("m" => "mensajes"), $fields)
                    ->joinLeft(array("t" => "traficos"), "t.id = m.idTrafico", array("patente", "aduana", "pedimento", "referencia", "id AS idTrafico"))
                    ->joinLeft(array("tt" => "repositorio_temporal"), "tt.idMensaje = m.id", array("id AS idArchivo", "nombreArchivo"))
                    ->where("leido = 0")
                    ->order("creado ASC");
            if (isset($aduanas)) {
                $sql->where("idAduana IN (?)", $aduanas);
            }
            $stmt = $this->_db_table->fetchAll($sql);
            if (0 == count($stmt)) {
                return;
            }
            return $stmt->toArray();
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param int $id
     * @return type
     * @throws Exception
     */
    public function obtenerMensaje($id) {
        try {
            $fields = array(
                "*",
                new Zend_Db_Expr("(select u.usuario from usuarios u where u.id = m.idUsuarioDe) as usuarioDe"),
                new Zend_Db_Expr("(select u.usuario from usuarios u where u.id = m.idUsuarioPara) as usuarioPara")
            );
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array("m" => "mensajes"), $fields)
                    ->joinLeft(array("t" => "repositorio_temporal"), "t.idMensaje = m.id", array("id AS idArchivo", "nombreArchivo"))
                    ->where("m.id = ?", $id);
            $stmt = $this->_db_table->fetchRow($sql);
            if (0 == count($stmt)) {
                return;
            }
            return $stmt->toArray();
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param array $arr
     * @return type
     * @throws Exception
     */
    public function agregar($arr) {
        try {
            $stmt = $this->_db_table->insert($arr);
            if ($stmt) {
                return $stmt;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param int $id
     * @return type
     * @throws Exception
     */
    public function leido($id) {
        try {
            $stmt = $this->_db_table->update(array("leido" => 1), array("id = ?" => $id));
            if ($stmt) {
                return true;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * 
     * @param int $idTrafico
     * @param int $idUsuario
     * @return type
     * @throws Exception
     */
    public function contarMisMensajes($idTrafico, $idUsuario) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array(new Zend_Db_Expr("count(*) AS cantidad")))
                    ->where("idTrafico = ?", $idTrafico)
                    ->where("idUsuarioPara = ?", $idUsuario);
            $stmt = $this->_db_table->fetchRow($sql);
            if ($stmt) {
                return (int) $stmt->cantidad;
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function cantidadMensajes($idUsuario, $ids) {
        try {
            $sql = $this->_db_table->select()
                    ->from($this->_db_table, array("idTrafico"))
                    ->where("idUsuarioPara = ?", $idUsuario)
                    ->where("idTrafico IN (?)", $ids)
                    ->where("leido = 0")
                    ->where("idUsuarioDe <> ?", $idUsuario)
                    ->group("idTrafico");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                return $stmt->toArray();
            }
            return;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("DB Exception found on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
