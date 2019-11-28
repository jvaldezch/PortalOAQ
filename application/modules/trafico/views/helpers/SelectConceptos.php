<?php

class Zend_View_Helper_SelectConceptos extends Zend_View_Helper_Abstract {

    public function selectConceptos($id) {
        $model = new Trafico_Model_TipoConceptoMapper();
        $arr = $model->obtener();
        $html = new V2_Html();
        if (count($arr)) {
            $html->select("traffic-select-small", $id);
            $html->addSelectOption("", "---");
            foreach ($arr as $item) {
                $html->addSelectOption($item["id"], strtoupper($item["tipoConcepto"]));
            }
            return $html->getHtml();
        }
        return;
    }

}
