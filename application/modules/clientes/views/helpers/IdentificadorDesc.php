<?php

class Zend_View_Helper_IdentificadorDesc extends Zend_View_Helper_Abstract
{
    public function identificadorDesc($iden)
    {
        $misc = new OAQ_Misc();        
        return $misc->identificadorDesc($iden);
    }
    
}
