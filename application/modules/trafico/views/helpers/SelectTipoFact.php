<?php

class Zend_View_Helper_SelectTipoFact extends Zend_View_Helper_Abstract {

    public function selectTipoFact($idCliente, $idAduana, $id, $value = null) {

        $model = new Trafico_Model_TraficoTipoFacturacionMapper();
        $rows = $model->obtenerTiposFacturacion($idCliente, $idAduana);
        if (isset($rows) && $rows !== false && !empty($rows)) {
            $html = "<select id=\"{$id}\" name=\"{$id}\" class=\"traffic-select-medium\">";
            foreach ($rows as $item) {
                $html .= "<option value=\"{$item["nombre"]}\">{$item["nombre"]}</option>";
            }
            return $html .= '</select>';
        } else {
            return "<select id=\"{$id}\" name=\"{$id}\" class=\"traffic-select-medium\" readonly=\"true\"><option value=\"CLIENTE\">CLIENTE</option></select>";
        }
    }

}
