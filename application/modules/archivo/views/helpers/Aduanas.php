<?php

class Zend_View_Helper_Aduanas extends Zend_View_Helper_Abstract {

    public function aduanas($id = null, $patente = null, $selected = null, $idUsuario = null) {
        if (isset($patente) && isset($idUsuario)) {
            $model = new Application_Model_UsuariosAduanasMapper();
            $arr = $model->getCustoms($idUsuario, $patente);
        }
        if ((!isset($arr) || (isset($arr) && $arr === false)) && isset($patente)) {
            $model = new Application_Model_CustomsMapper();
            $arr = $model->getCustomsByPatent($patente);
        }
        $html = new V2_Html();
        $html->select("traffic-select-small", $id);
        $html->addSelectOption("", "---");
        if (isset($arr) && !empty($arr)) {
            foreach ($arr as $item) {
                if (isset($selected) && $selected != 0 && $selected == $item) {
                    $html->addSelectOption($item, $item, true);
                } else {
                    $html->addSelectOption($item, $item);
                }
            }
        }
        return $html->getHtml();
    }

}
