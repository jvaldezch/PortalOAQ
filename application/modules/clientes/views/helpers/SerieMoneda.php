<?php

class Zend_View_Helper_SerieMoneda extends Zend_View_Helper_Abstract {

    public function serieMoneda($value) {
        switch ($value) {
            case 'SF43718':
                return 'USD (FIX)';
            case 'SF60653':
                return 'USD';
            case 'SF46410':
                return 'Euro (€)';
            case 'SF60632':
                return 'CAD';
            case 'SF46406':
                return 'Yen (¥)';
            case 'SF46407':
                return 'Libra Esterlina (£)';
            default:
                return 'n/d';
        }
    }

}
