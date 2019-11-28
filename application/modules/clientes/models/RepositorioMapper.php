<?php

class Clientes_Model_RepositorioMapper {

    protected $_db_table;

    public function __construct() {
        $this->_db_table = new Clientes_Model_DbTable_Repositorio();
    }

    public function getReferences($rfc, $fechaIni = null, $fechaFin = null) {
        try {
            $sica = new OAQ_Sica();
            $select = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array('r' => 'repositorio'), array('r.referencia', 'r.patente', 'r.aduana', 'r.creado'))
                    ->where('r.receptor_rfc = ?', $rfc)
                    ->where('tipo_archivo = 2');
            if (isset($fechaIni) && $fechaFin) {
                $select->where('r.creado >= ?', $fechaIni)
                        ->where('r.creado <= ?', $fechaFin);
            }
            $result = $this->_db_table->fetchAll($select, array());
            if ($result) {
                $data = array();
                foreach ($result as $file) {
                    $reference = $sica->searchForReference($file['referencia'], $file['patente'], $file['aduana']);
                    $data[] = array(
                        'referencia' => $file['referencia'],
                        'patente' => $file['patente'],
                        'aduana' => $file['aduana'],
                        'fecha' => $file['creado'],
                        'year' => $reference[0]["year"],
                    );
                }
                return $data;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

    public function getFiles($rfc, $patente, $referencia) {
        try {
            $select = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array('r' => 'repositorio'), array('r.id', 'r.referencia', 'r.nom_archivo', 'r.ubicacion_pdf', 'r.ubicacion_xml', 'r.creado'))
                    ->join(array('d' => 'documentos'), 'r.tipo_archivo = d.id', array('d.nombre'))
                    ->where('r.patente = ?', $patente)
                    ->where('r.referencia = ?', $referencia);
            $result = $this->_db_table->fetchAll($select, array());
            if ($result) {
                $data = array();
                foreach ($result as $file) {
                    $data[] = array(
                        'id' => $file['id'],
                        'referencia' => $file['referencia'],
                        'nombre' => $file['nombre'],
                        'nom_archivo' => $file['nom_archivo'],
                        'ubicacion_pdf' => $file['ubicacion_pdf'],
                        'ubicacion_xml' => $file['ubicacion_xml'],
                        'creado' => $file['creado'],
                    );
                }
                return $data;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

    public function verifyReference($rfc, $referencia) {
        try {
            $select = $this->_db_table->select()
                    ->where('receptor_rfc = ?', $rfc)
                    ->where('referencia = ?', $referencia);
            $result = $this->_db_table->fetchRow($select);
            if ($result) {
                return true;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

    public function searchReferences($rfc, $reference) {
        try {
            $sica = new OAQ_Sica();
            $select = $this->_db_table->select()
                    ->setIntegrityCheck(false)
                    ->from(array('r' => 'repositorio'), array('r.referencia', 'r.patente', 'r.aduana', 'r.creado'))
                    ->where('r.receptor_rfc = ?', $rfc)
                    ->where('tipo_archivo = 2')
                    ->where('r.referencia = ?', $reference);
            $result = $this->_db_table->fetchAll($select, array());
            if ($result) {
                $data = array();
                foreach ($result as $file) {
                    $reference = $sica->searchForReference($file['referencia'], $file['patente'], $file['aduana']);
                    $data[] = array(
                        'referencia' => $file['referencia'],
                        'patente' => $file['patente'],
                        'aduana' => $file['aduana'],
                        'fecha' => $file['creado'],
                        'year' => $reference[0]["year"],
                    );
                }
                return $data;
            }
            return null;
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new Exception("Zend DB Exception found on " . __METHOD__ . " > " . $e->getMessage());
        } catch (Zend_Exception $e) {
            throw new Exception("Zend Exception found on " . __METHOD__ . " > " . $e->getMessage());
        }
    }

}
