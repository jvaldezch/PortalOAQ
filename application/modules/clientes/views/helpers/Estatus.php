<?php

class Zend_View_Helper_Estatus extends Zend_View_Helper_Abstract
{
    public function estatus($estatus,$id)
    {
        if($estatus == 1) {
            return "<div class=\"statusCove sent\"></div>";
        } elseif($estatus == 0) {
            return "<a href=\"/vucem/index/ver-error-cove?id={$id}\"><div class=\"statusCove error\"></div></a>";
        } elseif($estatus == 3) {
            return "<a href=\"/vucem/index/ver-error-cove?id={$id}\"><div class=\"statusCove notsent\"></div></a>";
        } else {
            return "<div class=\"statusCove cove\"></div>";
        }
        
    }
    
}
