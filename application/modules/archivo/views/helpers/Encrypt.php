<?php

class Zend_View_Helper_Encrypt extends Zend_View_Helper_Abstract
{
    public function encrypt($val)
    {
        $misc = new OAQ_Misc();
        
        return $misc->myEncrypt($val);
    }
}
