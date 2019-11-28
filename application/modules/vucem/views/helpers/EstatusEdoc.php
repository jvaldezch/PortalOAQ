<?php

class Zend_View_Helper_EstatusEdoc extends Zend_View_Helper_Abstract {

    public function estatusEdoc($estatus, $id) {
        if ($estatus == 1) {
            return "<div class=\"statusCove sent\"></div>";
        } elseif ($estatus == 0) {
            return "<a href=\"/vucem/index/ver-error-edoc?id={$id}\"><div class=\"statusCove error\"></div></a>";
        } else {
            return "<div class=\"statusCove cove\"></div>";
        }
    }

}
