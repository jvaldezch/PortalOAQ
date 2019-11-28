<?php

class Zend_View_Helper_SelectTipoAduana extends Zend_View_Helper_Abstract {

    public function selectTipoAduana($id, $value = null) {
        $mapper = new Trafico_Model_TraficoTipoAduanaMapper();
        $arr = $mapper->obtenerTodas();
        $html = new V2_Html();
        if (count($arr)) {
            $html->select("traffic-select-small", $id);
            $html->addSelectOption("", "---");
            foreach ($arr as $item) {
                $html->addSelectOption($item["id"], $item["tipoAduana"]);
            }
            return $html->getHtml();
        }
        return;
    }

}
