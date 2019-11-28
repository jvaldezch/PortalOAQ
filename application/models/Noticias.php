<?php

/**
 * Description of UserEmails
 *
 * @author Jaime E. Valdez <jvaldezch@gmail.com>
 */
class Application_Model_Noticias {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Application_Model_DbTable_Noticias();
    }

    public function obtener() {
        try {
            $select = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array('n' => 'noticias'), array('creado', 'id', 'clientes', 'interno', 'publico'))
                    ->joinLeft(array('c' => 'noticia_contenidos'), "n.id = c.idNoticia", array('titulo', 'contenido'))
                    ->joinLeft(array('u' => 'usuarios'), "n.creadoPor = u.usuario", array('nombre'))
                    ->where('n.activa = 1')
                    ->order("n.creado DESC");
            $result = $this->_db_table->fetchAll($select);
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
    
    public function obtenerNoticiasClientes() {
        try {
            $select = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array('n' => 'noticias'), array('creado', 'id', 'clientes', 'interno', 'publico'))
                    ->joinLeft(array('c' => 'noticia_contenidos'), "n.id = c.idNoticia", array('titulo', 'contenido'))
                    ->joinLeft(array('u' => 'usuarios'), "n.creadoPor = u.usuario", array('nombre'))
                    ->where('n.activa = 1')
                    ->where('n.clientes = 1')
                    ->orWhere('n.publico = 1')
                    ->order("n.creado DESC");
            $result = $this->_db_table->fetchAll($select);
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

    public function obtenerNoticiaCompleta($idNoticia) {
        try {
            $select = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array('n' => 'noticias'), array('creado', 'editado', 'id', 'clientes', 'interno', 'publico', new Zend_Db_Expr('(select nombre from usuarios where usuario = n.editadoPor) as actualizadoPor')))
                    ->joinLeft(array('c' => 'noticia_contenidos'), "n.id = c.idNoticia", array('titulo', 'contenido', 'fuente', 'url'))
                    ->joinLeft(array('u' => 'usuarios'), "n.creadoPor = u.usuario", array('nombre as publicadoPor'))
                    ->where('n.activa = 1')
                    ->where('n.id = ?', $idNoticia);
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
            $stmt = $this->_db_table->update($data, array('id = ?' => $idNoticia));
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

    public function borrar($idNoticia) {
        try {
            $stmt = $this->_db_table->update(array('borrada' => 0, 'activa' => 0), array('id = ?' => $idNoticia));
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
