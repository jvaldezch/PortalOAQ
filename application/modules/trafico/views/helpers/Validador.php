<?php

class Zend_View_Helper_Validador extends Zend_View_Helper_Abstract
{
    public function validador($validador)
    {
        switch ($validador) {
            case 1588:
                return "C.A.A.A.R.E.M., A.C.";
            case 1563:
                return "C.A.A.A.R.E.M., A.C.";
            case 1610:
                return "C.A.A.A.R.E.M., A.C.";
            case 1541:
                return "C.A.A.A.R.E.M., A.C.";
            case 1597:
                return "C.A.A.A.R.E.M., A.C.";
            case 1549:
                return "C.A.A.A.R.E.M., A.C.";
            case 1615:
                return "C.A.A.A.R.E.M., A.C.";
            default:
                return $validador;
        }
    }
    
}
