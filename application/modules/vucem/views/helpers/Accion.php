<?php

class Zend_View_Helper_Accion extends Zend_View_Helper_Abstract {

    public function accion($estatus, $id, $cove = null, $factura = null, $expediente = null, $patente = null, $aduana = null) {
        $html = '';
        if ($estatus == 0) {
            $html .= '<a title="Consultar el COVE enviado." href="/vucem/index/consultar-cove-enviado?id=' . $id . '"><i class="icon icon-file"></i></a>';
            $html .= '&nbsp;<a href="#" title="Borrar COVE" style="margin-left:5px" class="deletecove" data="' . $id . '"><i class="icon icon-trash"></i></a>';
            $html .= '<a class="reenviar" style="margin-left: 5px; cursor: pointer" title="Reenviar factura" data-id="' . $id . '" data-factura="' . $factura . '"><i class="icon-retweet"></i></a>';
        }
        if ($estatus == 2 && $cove != '') {
            $html .= '<a title="Consultar el COVE enviado." href="/vucem/index/consultar-cove-enviado?id=' . $id . '"><i class="icon icon-file"></i></a>';
            if ($expediente == 0) {
                $html .= '<a style="margin-left: 5px" title="Mandar a repositorio" href="/automatizacion/vucem/print-cove?id=' . $id . '&save=true" class="send-to-disk"><i class="icon-hdd"></i></a>';
            }
            $html .= '<a class="adenda" style="margin-left: 5px; cursor: pointer" title="Crear adenda" data-id="' . $id . '" data-factura="' . $factura . '" data-cove="' . $cove . '"><i class="icon-flag"></i></a>';
            $html .= '<a class="reenviar" style="margin-left: 5px; cursor: pointer" title="Reenviar factura" data-id="' . $id . '" data-factura="' . $factura . '"><i class="icon-retweet"></i></a>';
            if ($patente == 3589) {
                $html .= '<a style="margin-left: 5px; cursor: pointer" title="Enviar a pedimento" onclick="enviaraPedimento(\'' . $id . '\',\'' . $cove . '\');"><i class="icon-qrcode"></i></a>';
            }
        }
        if ($estatus == 3) {
            $html .= '<a class="reenviar" style="margin-left: 5px; cursor: pointer" title="Reenviar Factura" data-id="' . $id . '" data-factura="' . $factura . '"><i class="icon-retweet"></i></a>';
            $html .= '<a href="/vucem/data/download-xml?id=' . $id . '" title="Descarga XML" style="margin-left:3px"><i class="icon icon-arrow-down"></i></a>';
            $html .= '<a href="#" title="Borrar COVE" style="margin-left:3px" class="removecove" data="' . $id . '"><i class="icon icon-trash"></i></a>';
        }
        if ($estatus == 1) {
            $html .= '<a title="Consultar el COVE enviado." href="/vucem/index/consultar-cove-enviado?id=' . $id . '"><i class="icon icon-file"></i></a>';
            $html .= '<a href="#" title="Borrar COVE" style="margin-left:3px" class="removecove" data="' . $id . '"><i class="icon icon-trash"></i></a>';
        }
        return $html;
    }

}
