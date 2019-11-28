<?php

class Archivo_Model_ImagenesAgenciaMapper
{
    protected $_db_table;
    
    public function __construct()
    {
        $this->_db_table = new Archivo_Model_DbTable_ImagenesAgencia();
    }
    /**
     * 
     * @return null
     */
    public function getImages()
    {
        $select = $this->_db_table->select()
                ->from('imagenes_agencia', array('id','aduana','pedimento','referencia','cve_doc','fecha_pago','factura','cuenta_gastos'));
        $result = $this->_db_table->fetchAll($select);
        if($result) {
            return $result->toArray();
        }
        return null;
    }
    
    public function searchForFile($referencia = null, $pedimento = null, $ctagastos = null)
    {
        if($referencia != '') {
            $where = $this->_db_table->getAdapter()->quoteInto("referencia LIKE ?", $referencia);
        }
        if($pedimento != '' && !isset($where)) {
            $where = $this->_db_table->getAdapter()->quoteInto("pedimento = ?", $pedimento);
        }
        if($ctagastos != '' && !isset($where)) {
            $where = $this->_db_table->getAdapter()->quoteInto("cuenta_gastos = ?", $ctagastos);
        }

        $select = $this->_db_table->select()
                ->from('imagenes_agencia', array('id','aduana','pedimento','referencia','cve_doc','fecha_pago','factura','cuenta_gastos'))
                ->where($where)
                ->limit(150);

        $result = $this->_db_table->fetchAll($select,array());        
        if($result) {
            return $result->toArray();
        }
        return null;
    }
    
    public function getSingleImage($id)
    {
        $select = $this->_db_table->select()
                ->from('imagenes_agencia',array('referencia','aduana','imagen','cuenta_gastos'))
                ->where('id = ?', $id);        
        $result = $this->_db_table->fetchRow($select,array());        
        if($result) {
            $data = array(
                'cuenta_gastos' => $result['cuenta_gastos'],
                'referencia' => $result['referencia'],
                'aduana' => $result['aduana'],
                'imagen' => $result['imagen'],
            );
            return $data;
        }
    }
    
    public function getReport($fechaIni, $fechaFin)
    {
        try {
            $select = $this->_db_table->select()
                ->from('imagenes_agencia',array('pedimento','rfc','referencia','aduana','fecha_factura','cuenta_gastos'))
                ->where('fecha_factura >= ?', $fechaIni)
                ->where('fecha_factura <= ?', $fechaFin);
            $result = $this->_db_table->fetchAll($select);
            if($result) {                
                return $result->toArray();
            }
            return null;
        } catch (Exception $e) {
            echo "<b>Exception on ".__METHOD__."</b>" . $e->getMessage(); die();
        } 
    }

    public function getByDateAndRfc($rfc,$fechaIni, $fechaFin)
    {
        try {
            $select = $this->_db_table->select()
                ->from('imagenes_agencia',array('id', 'pedimento','rfc','referencia','aduana','fecha_factura','cuenta_gastos'))
                ->where('fecha_factura >= ?', $fechaIni)
                ->where('fecha_factura <= ?', $fechaFin)
                ->where('rfc = ?',$rfc);
            $result = $this->_db_table->fetchAll($select);
            if($result) {
                $data = array();
                foreach ($result as $item) {
                    $data[] = array(
                        'id' => $item["id"],
                        'referencia' => $item["referencia"],
                        'aduana' => $item["aduana"],
                        'fecha' => $item["fecha_factura"],
                        'origen' => 'digitex',
                        'year' => date('Y',strtotime($item["fecha_factura"])),
                        );
                }
                return $data;
            }
            return null;
        } catch (Exception $e) {
            echo "<b>Exception on ".__METHOD__."</b>" . $e->getMessage(); die();
        } 
    }
}

