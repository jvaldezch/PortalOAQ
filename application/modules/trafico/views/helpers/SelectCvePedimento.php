<?php

class Zend_View_Helper_SelectCvePedimento extends Zend_View_Helper_Abstract {

    public function selectCvePedimento($idAduana, $id, $value = null) {
        $mapper = new Trafico_Model_CvePedimentos();
        $arr = $mapper->obtener();
        $html = new V2_Html();
        if (count($arr)) {
            $html->select("traffic-select-xs", $id);
            $html->addSelectOption("", "---");
            foreach ($arr as $item) {
                if (isset($value) && $item["clave"] == $value) {
                    $html->addSelectOption($item["clave"], $item["clave"], true);
                } else {
                    $html->addSelectOption($item["clave"], $item["clave"]);
                }
            }
            return $html->getHtml();
        }
        return;
    }

}
