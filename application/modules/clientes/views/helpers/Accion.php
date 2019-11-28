<?php

class Zend_View_Helper_Accion extends Zend_View_Helper_Abstract
{
    public function accion($estatus,$id,$cove=null,$factura=null)
    {
        $html = '';        
        if($estatus == 2 && $cove != '') {
            $html .= '<a title="Consultar el COVE enviado." href="/clientes/index/consultar-cove-enviado?id='.$id.'"><i class="icon icon-file"></i></a>';
        }
        return $html;
        
    }
    
}
