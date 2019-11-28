<?php

class Zend_View_Helper_Identificador extends Zend_View_Helper_Abstract
{
    public function identificador($rfc, $pais)
    {
        $misc = new OAQ_Misc();        
        return $misc->tipoIdentificador($rfc, $pais);
    }
    
}
