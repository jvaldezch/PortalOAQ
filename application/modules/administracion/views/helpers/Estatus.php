<?php

class Zend_View_Helper_Estatus extends Zend_View_Helper_Abstract {

    public function estatus($autorizada, $tramite, $deposito, $hsbc = null, $banamex = null) {
        $html = '<div class="semaphore-black"></div>';
        if (isset($autorizada) && $autorizada >= 1) {
            $html = '<div class="semaphore-yellow"></div>';
        }
        if (isset($tramite) && $tramite == 1) {
            $html = '<div class="semaphore-blue"></div>';
        }
        if (isset($deposito) && $deposito == 1) {
            $html = '<div class="semaphore-green"></div>';
        }
        if (isset($autorizada) && $hsbc == 1) {
            $html = '<div class="semaphore-violet"></div>';
        }
        if (isset($autorizada) && $banamex == 1) {
            $html = '<div class="semaphore-palegreen"></div>';
        }
        return $html;
    }

}
