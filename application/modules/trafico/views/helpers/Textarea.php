<?php

class Zend_View_Helper_Textarea extends Zend_View_Helper_Abstract {

    public function textarea($id, $value = null, $style = null) {
        $html = '<textarea id="' . $id . '" name="' . $id . '" style="' . $style . '">' . $value . '</textarea>';
        return $html;
    }

}
