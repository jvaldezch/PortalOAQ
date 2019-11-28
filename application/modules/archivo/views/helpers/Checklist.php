<?php

class Zend_View_Helper_Checklist extends Zend_View_Helper_Abstract {

    public function checklist($patente, $aduana, $referencia) {
        $html = "";
        $model = new Archivo_Model_RepositorioMapper();
        if (($model->tipoDocumentoExiste($patente, $referencia, array(21, 22))) == true) {
            $html.= "<span class=\"doctype\" title=\"Comprobante de valor electrónico\">COVE</span>";
        }
        if (($model->tipoDocumentoExiste($patente, $referencia, array(17))) == true) {
            $html.= "<span class=\"doctype\" title=\"Expediente completo\">EC</span>";
        }
        if (($model->tipoDocumentoExiste($patente, $referencia, array(1, 23, 24, 25))) == true) {
            $html.= "<span class=\"doctype\" title=\"Pedimento\">PED</span>";
        }
        if (($model->tipoDocumentoExiste($patente, $referencia, array(33))) == true) {
            $html.= "<span class=\"doctype\" title=\"Pedimento simplicado\">PS</span>";
        }
        if (($model->tipoDocumentoExiste($patente, $referencia, array(26, 27, 28))) == true) {
            $html.= "<span class=\"doctype\" title=\"Edocument\">EDOC</span>";
        }
        if (($model->tipoDocumentoExiste($patente, $referencia, array(29, 30))) == true) {
            $html.= "<span class=\"doctype\" title=\"Factura de corresponsal\">FC</span>";
        }
        if (($model->tipoDocumentoExiste($patente, $referencia, array(31))) == true) {
            $html.= "<span class=\"doctype\" title=\"Solicitud de anticipos\">SA</span>";
        }
        if (($model->tipoDocumentoExiste($patente, $referencia, array(2))) == true) {
            $html.= "<span class=\"doctype\" title=\"Cuenta de gastos\">CG</span>";
        }
        if (($model->tipoDocumentoExiste($patente, $referencia, array(3))) == true) {
            $html.= "<span class=\"doctype\" title=\"Fscturas\">FAC</span>";
        }
        if (($model->tipoDocumentoExiste($patente, $referencia, array(40))) == true) {
            $html.= "<span class=\"doctype\" title=\"Factura de terceros\">FT</span>";
        }
        if (($model->tipoDocumentoExiste($patente, $referencia, array(42))) == true) {
            $html.= "<span class=\"doctype\" title=\"Factura comprobados\">FCM</span>";
        }
        if (($model->tipoDocumentoExiste($patente, $referencia, array(11))) == true) {
            $html.= "<span class=\"doctype\" title=\"Hoja de calculo\">HC</span>";
        }
        if (($model->tipoDocumentoExiste($patente, $referencia, array(10))) == true) {
            $html.= "<span class=\"doctype\" title=\"Manifestación de valor\">MV</span>";
        }
        if (($model->tipoDocumentoExiste($patente, $referencia, array(12))) == true) {
            $html.= "<span class=\"doctype\" title=\"Bill of lading\">BL</span>";
        }
        if (($model->tipoDocumentoRango($patente, $referencia, 168, 445)) == true) {
            $html.= "<span class=\"doctype\" title=\"Documentos digitalizados\">DIG</span>";
        }
        if (($model->tipoDocumentoExiste($patente, $referencia, array(99))) == true) {
            $html.= "<span class=\"notdoctype\" title=\"El usuario no ha clasificado archivos.\">N/D</span>";
        }
        return $html;
    }

}
