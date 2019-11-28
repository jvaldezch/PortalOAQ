<?php

class Zend_View_Helper_AccionEdoc extends Zend_View_Helper_Abstract {

    public function accionEdoc($estatus, $uuid, $solicitud, $edoc = null, $expediente = null, $id = null) {
        $html = '';
        $html .= '<a title="Consultar el EDOC enviado." href="/vucem/index/consultar-edoc-enviado?uuid=' . $uuid . '&solicitud=' . $solicitud . '"><i class="icon icon-file"></i></a>';
        if ($estatus == 0) {
            $html .= '&nbsp;<a title="Borrar COVE" style="margin-left:2px; cursor:pointer" class="deleteedoc" data="' . $uuid . '"><i class="icon icon-trash"></i></a>';
        }
        if ($estatus == 2 && $edoc != '') {
            if ($expediente == 0) {
                $html .= '<a id="save_' . $id . '" style="margin-left: 3px; cursor: pointer" title="Mandar a repositorio" data-solicitud="' . $solicitud . '" data-id="' . $id . '" class="save"><i class="icon-hdd"></i></a>';
            }
            $html .= '<a style="margin-left: 3px;" onclick="enviaraPedimento(\'' . $uuid . '\',\'' . $solicitud . '\');"><i class="icon-qrcode" title="Enviar a pedimento." style="cursor: pointer;"></i></a>';
        }
        if (!isset($solicitud)) {
            $html .= '<a title="Borrar COVE" style="margin-left:2px; cursor:pointer" class="deleteedoc" data="' . $uuid . '"><i class="icon icon-trash"></i></a>';
        }
        return $html;
    }

}
