<?php

class V2_Model_Trafico_UsuarioClientes {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new V2_Model_Trafico_DbTable_UsuarioClientes();
    }

    /**
     * Esta funcion obtiene la lista de clientes asignados a un usuario.
     * 
     * @param int $idUsuario
     * @param int $idAduana
     * @return type
     * @throws Exception
     */
    public function obtenerClientes($idUsuario, $idAduana) {
        try {
            $sql = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->distinct()
                    ->from(array("u" => "v2_usuario_clientes"), array("*"))
                    ->joinLeft(array("c" => "trafico_clientes"), "u.idCliente = c.id", array("*"))
                    ->where("u.idUsuario = ?", $idUsuario)
                    ->where("u.idAduana = ?", $idAduana)
                    ->where("c.activo = 1")
                    ->order("c.nombre ASC");
            $stmt = $this->_db_table->fetchAll($sql);
            if ($stmt) {
                $arr = array();
                foreach ($stmt as $item) {
                    $arr[$item["idCliente"]] = $item["nombre"];
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
