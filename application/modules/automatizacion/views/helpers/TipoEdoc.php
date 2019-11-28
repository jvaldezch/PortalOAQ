<?php

class Zend_View_Helper_TipoEdoc extends Zend_View_Helper_Abstract
{
    public function tipoEdoc($id)
    {
        $vucemDoc = new Archivo_Model_DocumentosMapper();
        return $vucemDoc->tipoDocumento($id);        
    }
    
}
