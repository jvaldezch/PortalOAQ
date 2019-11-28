<?php

class Zend_View_Helper_Valid extends Zend_View_Helper_Abstract
{
    public function valid($estatus,$cove)
    {
        if($estatus == 1) {
            return "<span style=\"color: blue\">{EN ESPERA DE RESPUESTA}</span>";
        } elseif($estatus == 0) {
            return "<span style=\"color: red\">{COMPROBANTE NO VÁLIDO}</span>";
        } else {
            return "<span style=\"color: #005500\">{$cove}</span>";
        }
        
    }
    
}
