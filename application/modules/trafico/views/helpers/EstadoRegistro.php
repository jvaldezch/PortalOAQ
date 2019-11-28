<?php

class Zend_View_Helper_EstadoRegistro extends Zend_View_Helper_Abstract
{
    public function estadoRegistro($edo1, $edo2, $subedo)
    {
        if($edo1 == 2) {
            if($edo2 == 2 && $subedo == 3) {
                return "PAGADO | HSBC";
            } elseif($edo2 == 2 && $subedo == 5) {
                return "PAGADO | BANAMEX";                
            } elseif($edo2 == 2 && $subedo == 11) {
                return "PAGADO | BBVA BANCOMER";
            } elseif($edo2 == 2 && $subedo == 8) {
                return "PAGADO | BANORTE";
            } elseif($edo2 == 3 && $subedo == 320) {
                return "PRIMERA SELECCIÓN AUTOMATIZADA | VERDE EN PRIMERA SELECCIÓN";
            } elseif($edo2 == 7 && $subedo == 710) {
                return "DESADUANADO/CUMPLIDO | DESADUANADO";
            } elseif($edo2 == 7 && $subedo == 730) {
                return "DESADUANADO/CUMPLIDO | CUMPLIDO";
            } elseif($edo2 == 1 && $subedo == 110) {
                return "VALIDACIÓN | VALIDACIÓN DE PREVIO";
            } elseif($edo2 == 4 && $subedo == 410) {
                return "PRIMER RECONOCIMIENTO | INICIO PRIMER RECONOCIMIENTO";
            } elseif($edo2 == 4 && $subedo == 450) {
                return "PRIMER RECONOCIMIENTO | RESULTADO SIN INCIDENCIAS";
            } elseif($edo2 == 3 && $subedo == 310) {
                return "PRIMERA SELECCIÓN AUTOMATIZADA | ROJO EN PRIMERA SELECCIÓN";
            } elseif($edo2 == 7 && $subedo == 760) {
                return "DESADUANADO/CUMPLIDO | RECTIFICADO";
            }
        }
        if($edo1 == 1) {
            if($edo2 == 2 && $subedo == 3) {
                return "PAGADO | HSBC";
            } elseif($edo2 == 2 && $subedo == 11) {
                return "PAGADO | BBVA BANCOMER";
            } elseif($edo2 == 2 && $subedo == 5) {
                return "PAGADO | BANAMEX";
            } elseif($edo2 == 2 && $subedo == 8) {
                return "PAGADO | BANORTE";
            } elseif($edo2 == 3 && $subedo == 320) {
                return "PRIMERA SELECCIÓN AUTOMATIZADA | VERDE EN PRIMERA SELECCIÓN";
            } elseif($edo2 == 7 && $subedo == 710) {
                return "DESADUANADO/CUMPLIDO | DESADUANADO";
            } elseif($edo2 == 7 && $subedo == 730) {
                return "DESADUANADO/CUMPLIDO | CUMPLIDO";
            } elseif($edo2 == 1 && $subedo == 110) {
                return "VALIDACIÓN | VALIDACIÓN DE PREVIO";
            } elseif($edo2 == 4 && $subedo == 410) {
                return "PRIMER RECONOCIMIENTO | INICIO PRIMER RECONOCIMIENTO";
            } elseif($edo2 == 4 && $subedo == 450) {
                return "PRIMER RECONOCIMIENTO | RESULTADO SIN INCIDENCIAS";
            } elseif($edo2 == 3 && $subedo == 310) {
                return "PRIMERA SELECCIÓN AUTOMATIZADA | ROJO EN PRIMERA SELECCIÓN";
            } elseif($edo2 == 7 && $subedo == 760) {
                return "DESADUANADO/CUMPLIDO | RECTIFICADO";
            }
            
        }
    }
    
}
