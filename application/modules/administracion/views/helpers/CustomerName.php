<?php

class Zend_View_Helper_CustomerName extends Zend_View_Helper_Abstract
{
    public function customerName($reference)
    {
        $sica = new OAQ_Sica();                
        $name = $sica->findClientIDByPolicyID($sica->findPolicyIDByReference($reference));
         
        if($name) {
            return $name;
        } else {
            return 'n/d';
        }
    }
}
