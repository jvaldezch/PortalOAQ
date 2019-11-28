<?php

class Zend_View_Helper_RadioPeca extends Zend_View_Helper_Abstract {

    public function radioPeca($id, $idCliente = null) {
        $html = '<input type="radio" name="'.$id.'" value="1">&nbsp;Si<br>
            <input type="radio" name="'.$id.'" value="0">&nbsp;No';
        return $html;
    }

}
