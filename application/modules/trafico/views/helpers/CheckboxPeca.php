<?php

class Zend_View_Helper_CheckboxPeca extends Zend_View_Helper_Abstract {

    public function checkboxPeca($id, $value = null) {
        $html = "";
        if (isset($value)) {
            if ($value == '1') {
                $html .= '<input type="checkbox" name="' . $id . '" id="' . $id . '" value="1" checked>&nbsp;Si';
            } else {
                $html .= '<input type="checkbox" name="' . $id . '" id="' . $id . '" value="0">&nbsp;Si';
            }
        } else {
            $html .= '<input type="checkbox" name="' . $id . '" id="' . $id . '" value="0">&nbsp;Si';
        }
        return $html;
    }

}
