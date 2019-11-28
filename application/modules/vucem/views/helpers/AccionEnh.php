<?php

class Zend_View_Helper_AccionEnh extends Zend_View_Helper_Abstract {

    public function accionEnh($estatus, $id, $cove = null, $factura = null) {
        $model = new Archivo_Model_RepositorioMapper();
        $html = '';
        if ($estatus == 0) {
            $html .= '<a title="Consultar el COVE enviado." href="/vucem/index/consultar-cove-enviado?id=' . $id . '"><i class="icon icon-file"></i></a>';
            $html .= '&nbsp;<a href="#" title="Borrar COVE" style="margin-left:5px" class="deletecove" data="' . $id . '"><i class="icon icon-trash"></i></a>';
            $html .= '&nbsp;<a href="/vucem/index/nuevo-cove-solicitante?id=' . $id . '&factura=' . $factura . '" title="Editar Factura" style="margin-left:5px" data="' . $id . '"><i class="icon icon-pencil"></i></a>';
        }
        if ($estatus == 2 && $cove != '') {
            $html .= '<a title="Consultar el COVE enviado." href="/vucem/index/consultar-cove-enviado?id=' . $id . '"><i class="icon icon-file"></i></a>';
            if (!($model->searchCove($cove))) {
                $html .= '<a style="margin-left: 5px" title="Mandar a repositorio" href="/automatizacion/vucem/print-cove?id=' . $id . '&save=true" class="send-to-disk"><i class="icon-hdd"></i></a>';
            }
            $html .= '<a style="margin-left: 5px" title="Crear adenda" href="/vucem/index/nuevo-cove-solicitante?id=' . $id . '&factura=' . $factura . '&cove=' . $cove . '"><i class="icon-flag"></i></a>';
            $html .= '<a style="margin-left: 5px" title="Reenviar factura" href="/vucem/index/nuevo-cove-solicitante?id=' . $id . '&factura=' . $factura . '&cove=' . $cove . '&reenviar=true"><i class="icon-retweet"></i></a>';
        }
        if ($estatus == 3) {
            $html .= '<a style="margin-left: 0" title="Reenviar factura" href="/vucem/data/reenviar?id=' . $id . '" data="' . $id . '" class="resent"><i class="icon-repeat"></i></a>';
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
