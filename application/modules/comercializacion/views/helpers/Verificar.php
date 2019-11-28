<?php

class Zend_View_Helper_Verificar extends Zend_View_Helper_Abstract
{
    public function verificar($rfc)
    {
        $cust = new Comercializacion_Model_ClientesMapper();
        if(($sica = $cust->verifyCustomer($rfc))) {
            return "<a href =\"/comercializacion/index/datos-cliente?id={$sica["sica_id"]}&internal={$sica["id"]}\">{$rfc}</a>";
        } else {
            return $rfc;
        }
        
    }
    
}
