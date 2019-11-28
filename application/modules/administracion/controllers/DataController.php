<?php

class Administracion_DataController extends Zend_Controller_Action {

    protected $_session;
    protected $_appconfig;
    protected $_soapClient;
    protected $_config;
    protected $_redirector;
    protected $_logger;
    protected $_arch;

    public function init() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
        $context = stream_context_create(array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
                "allow_self_signed" => true
            )
        ));
        $this->_soapClient = new Zend_Soap_Client($this->_config->app->endpoint, array("stream_context" => $context));
        $this->_logger = Zend_Registry::get("logDb");
    }

    public function preDispatch() {
        $this->_session = NULL ? $this->_session = new Zend_Session_Namespace("") : $this->_session = new Zend_Session_Namespace($this->_config->app->namespace);
        if ($this->_session->authenticated == true) {
            $session = new OAQ_Session($this->_session, $this->_appconfig);
            $session->actualizar();
            $session->actualizarSesion();
        } else {
            $this->getResponse()->setRedirect("/default/index/logout");
        }
    }

    public function indexAction() {
        
    }

    public function consultaCuentaDeGastosAction() {
        try {
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            ini_set("soap.wsdl_cache_enabled", 0);
            $gets = $this->_request->getParams();
            if (isset($gets["rfc"]) && isset($gets["fechaIni"]) && isset($gets["fechaFin"]) && isset($gets["desglose"])) {
                $sica = new OAQ_Sica;
                $misc = new OAQ_Misc();
//                $clientes = new Comercializacion_Model_ClientesMapper();
//                $sicaId = $clientes->getCustomerSicaIdByRfc($gets['rfc']);
                if ($gets["rfc"] != "") {
                    $result = $sica->getInvoicesByRfc($gets["rfc"], $gets['fechaIni'], $gets['fechaFin']);
                } else {
                    $result = $sica->getInvoicesAll($gets['fechaIni'], $gets['fechaFin']);
                }
                echo "<!doctype html>\n<html lang=\"en\">\n"
                . "<head>\n"
                . "<meta charset=\"utf-8\">\n"
                . "<title>Cuentas de Gastos</title>\n"
                . "<style>body {margin:0;padding:0; font-family:sans-serif;}\n"
                . "table {border-collapse:collapse; }\n"
                . "table th, table td {font-size: 12px; border: 1px #555 solid; padding: 2px 5px;}\n"
                . "table td {font-size: 12px; border: 1px #BBB solid;}\n"
                . "table th {background: #f1f1f1;\n"
                . "}\n"
                . "h3 {\n"
                . "margin: 0; padding:0;\n"
                . "}\n"
                . "table tr:hover { background-color: #DDFFDD; }\n"
                . "button#export { cursor: pointer; margin: 2px; color: #fff; background-color: green; font-weight: bold; border: 1px #080 solid; border-radius: 3px; padding: 2px 5px; }\n"
                . "button#export:disabled { color: #555; background-color: #d5d5d5; border: 1px #e6e6e6 solid; }\n"
                . "span.warning { color: #d75; font-size: 11px; margin-left: 5px; }\n"
                . "</style>"
                . "<script src=\"https://oaq.dnsalias.net/js/common/jquery-1.9.1.min\"></script>\n"
                . "<script src=\"https://oaq.dnsalias.net/js/common/jquery.table2excel.min.js\"></script>\n"
                . "<script src=\"https://oaq.dnsalias.net/js/common/tableToExcel.js\"></script>\n"
                . "<script type=\"text/javascript\">\n"
                . "\t$(document).ready(function() {\n"
                . "\t\t$(\"button#export\").click(function(){\n"
                . "\t\t\t$(this).attr(\"disabled\", true);\n"
                . "\t\t});\n"
                . "\t});\n"
                . "</script>\n"
                . "</head>\n"
                . "<body>\n";
                if ($gets["desglose"] == 0) {
                    $html = "\t<button id=\"export\" style=\"margin-bottom: 5px;\" onclick=\"tableToExcel('table2excel', 'Excel Table')\" value=\"Export to Excel\">Exportar Excel</button><span class=\"warning\">Dependiendo de la cantidad de datos exportar a Excel puede considerar un poco de tiempo.</span>\n";
                    $html .= "\t<table class=\"table table-striped table-bordered table-hover small\" id=\"table2excel\" summary=\"\" rules=\"groups\" frame=\"hsides\" border=\"2\">\n"
                            . "\t\t<tr>\n"
                            . "\t\t\t<th>Fecha</th>\n"
                            . "\t\t\t<th>Folio</th>\n"
                            . "\t\t\t<th>Patente</th>\n"
                            . "\t\t\t<th>Aduana</th>\n"
                            . "\t\t\t<th>Num.Pedimento</th>\n"
                            . "\t\t\t<th>Referencia</th>\n"
                            . "\t\t\t<th>Nom. Cliente</th>\n"
                            . "\t\t\t<th>I/E</th>\n"
                            . "\t\t\t<th>Cve. Doc</th>\n"
                            . "\t\t\t<th>Fecha Pago Pedimento</th>\n"
                            . "\t\t\t<th>Fecha de Entrada</th>\n"
                            . "\t\t\t<th>Tipo de Cambio</th>\n"
                            . "\t\t\t<th>Factura Pedimento</th>\n"
                            . "\t\t\t<th>Bultos/Piezas</th>\n"
                            . "\t\t\t<th>Valor Aduana</th>\n"
                            . "\t\t\t<th>DTA</th>\n"
                            . "\t\t\t<th>IVA (Ped)</th>\n"
                            . "\t\t\t<th>CNT</th>\n"
                            . "\t\t\t<th>IGI (Adv)</th>\n"
                            . "\t\t\t<th>Prevalidación</th>\n"
                            . "\t\t\t<th>Guías</th>\n"
                            . "\t\t\t<th>Maniobras</th>\n"
                            . "\t\t\t<th>Almacenajes</th>\n"
                            . "\t\t\t<th>Demoras</th>\n"
                            . "\t\t\t<th>Flete aereo</th>\n"
                            . "\t\t\t<th>Flete marítimo</th>\n"
                            . "\t\t\t<th>Fletes y acarreos</th>\n"
                            . "\t\t\t<th>Fletes</th>\n"
                            . "\t\t\t<th>Alijadores</th>\n"
                            . "\t\t\t<th>Total de Comprobados</th>\n"
                            . "\t\t\t<th>Gastos complementarios</th>\n"
                            . "\t\t\t<th>Impuestos aduanales</th>\n"
                            . "\t\t\t<th>Revalidación</th>\n"
                            . "\t\t\t<th>Rectificaciones</th>\n"
                            . "\t\t\t<th>Honorarios</th>\n"
                            . "\t\t\t<th>SubTotal</th>\n"
                            . "\t\t\t<th>IVA</th>\n"
                            . "\t\t\t<th>Anticipo</th>\n"
                            . "\t\t\t<th>Total</th>\n"
                            . "\t\t</tr>\n";
                } else {
                    $html = "\t<button id=\"export\" style=\"margin-bottom: 5px;\" onclick=\"tableToExcel('table2excel', 'Excel Table')\" value=\"Export to Excel\">Exportar Excel</button><span class=\"warning\">Dependiendo de la cantidad de datos exportar a Excel puede considerar un poco de tiempo.</span>\n";
                    $html .= "\t<table class=\"table table-striped table-bordered table-hover small\" id=\"table2excel\" summary=\"\" rules=\"groups\" frame=\"hsides\" border=\"2\">\n"
                            . "\t\t" . '<tr>' . "\n"
                            . "\t\t\t" . '<th rowspan="2">Fecha</th>' . "\n"
                            . "\t\t\t" . '<th rowspan="2">Folio</th>' . "\n"
                            . "\t\t\t" . '<th rowspan="2">Patente</th>' . "\n"
                            . "\t\t\t" . '<th rowspan="2">Aduana</th>' . "\n"
                            . "\t\t\t" . '<th rowspan="2">Num.Pedimento</th>' . "\n"
                            . "\t\t\t" . '<th rowspan="2">Referencia</th>' . "\n"
                            . "\t\t\t" . '<th rowspan="2">Nom. Cliente</th>' . "\n"
                            . "\t\t\t" . '<th rowspan="2">I/E</th>' . "\n"
                            . "\t\t\t" . '<th colspan="13">PEDIMENTO</th>' . "\n"
                            . "\t\t\t" . '<th colspan="12">GASTOS COMPROBADOS</th>' . "\n"
                            . "\t\t\t" . '<th colspan="11">GASTOS COMPLEMENTARIOS</th>' . "\n"
                            . "\t\t\t" . '<th rowspan="2">Rectificaciones</th>' . "\n"
                            . "\t\t\t" . '<th rowspan="2">Honorarios</th>' . "\n"
                            . "\t\t\t" . '<th rowspan="2">SubTotal</th>' . "\n"
                            . "\t\t\t" . '<th rowspan="2">IVA</th>' . "\n"
                            . "\t\t\t" . '<th rowspan="2">Anticipo</th>' . "\n"
                            . "\t\t\t" . '<th rowspan="2">Total</th>' . "\n"
                            . "\t\t" . '</tr>' . "\n"
                            . "\t\t" . '<tr>' . "\n"
                            . "\t\t\t" . '<th>Cve. Doc</th>' . "\n"
                            . "\t\t\t" . '<th>Fecha Pago Pedimento</th>' . "\n"
                            . "\t\t\t" . '<th>Fecha de Entrada</th>' . "\n"
                            . "\t\t\t" . '<th>Tipo de Cambio</th>' . "\n"
                            . "\t\t\t" . '<th>Factura Pedimento</th>' . "\n"
                            . "\t\t\t" . '<th>Bultos/Piezas</th>' . "\n"
                            . "\t\t\t" . '<th>Valor Aduana</th>' . "\n"
                            . "\t\t\t" . '<th>DTA</th>' . "\n"
                            . "\t\t\t" . '<th>IVA (Ped)</th>' . "\n"
                            . "\t\t\t" . '<th>CNT</th>' . "\n"
                            . "\t\t\t" . '<th>IGI (Adv)</th>' . "\n"
                            . "\t\t\t" . '<th>Prevalidación</th>' . "\n"
                            . "\t\t\t" . '<th>Guías</th>' . "\n"
                            . "\t\t\t" . '<th>Maniobras</th>' . "\n"
                            . "\t\t\t" . '<th>Almacenajes</th>' . "\n"
                            . "\t\t\t" . '<th>Demoras</th>' . "\n"
                            . "\t\t\t" . '<th>Flete aereo</th>' . "\n"
                            . "\t\t\t" . '<th>Flete marítimo</th>' . "\n"
                            . "\t\t\t" . '<th>Fletes y acarreos</th>' . "\n"
                            . "\t\t\t" . '<th>Fletes</th>' . "\n"
                            . "\t\t\t" . '<th>Alijadores</th>' . "\n"
                            . "\t\t\t" . '<th>Impuestos aduanales</th>' . "\n"
                            . "\t\t\t" . '<th>Revalidación</th>' . "\n"
                            . "\t\t\t" . '<th>Broker USA</th>' . "\n"
                            . "\t\t\t" . '<th>Total de Comprobados</th>' . "\n"
                            . "\t\t\t" . '<th>Gastos complementarios</th>' . "\n"
                            . "\t\t\t" . '<th>Gastos Maniobras</th>' . "\n"
                            . "\t\t\t" . '<th>Gastos Fletes</th>' . "\n"
                            . "\t\t\t" . '<th>Gastos Almacenajes</th>' . "\n"
                            . "\t\t\t" . '<th>Gastos Alijadores</th>' . "\n"
                            . "\t\t\t" . '<th>Gastos Demoras</th>' . "\n"
                            . "\t\t\t" . '<th>Gastos Serv. Extra.</th>' . "\n"
                            . "\t\t\t" . '<th>Gastos Charter</th>' . "\n"
                            . "\t\t\t" . '<th>Gastos Complementarios Cruce</th>' . "\n"
                            . "\t\t\t" . '<th>Gastos Complementarios Rect.</th>' . "\n"
                            . "\t\t\t" . '<th>Total de Complementarios</th>' . "\n"
                            . "\t\t" . '</tr>' . "\n";
                }
                echo $html;
                $totales["vaduana"] = 0;
                $totales["maniobras"] = 0;
                $totales["almacenajes"] = 0;
                $totales["demoras"] = 0;
                $totales["totalcomprobados"] = 0;
                ini_set('default_socket_timeout', 100);                
                foreach ($result as $item) {
                    if ($gets["desglose"] == 0) {
                        echo "\t\t<tr>\n"
                        . "\t\t\t<td>" . $item["fecha_factura"] . "</td>\n"
                        . "\t\t\t<td><a style=\"cursor:pointer; color:blue; text-decoration: underline;\" onclick=\"window.open('/administracion/data/consulta-de-factura?folio={$item["factura"]}', '_blank', 'toolbar=0,location=0,menubar=0,height=550,width=900,scrollbars=yes');\">" . $item["factura"] . '</a></td>'
                        . "\t\t\t" . '<td>' . $item["patente"] . '</td>' . "\n"
                        . "\t\t\t" . '<td>' . $item["aduana"] . '</td>' . "\n"
                        . "\t\t\t" . '<td>' . $item["pedimento"] . '</td>' . "\n"
                        . "\t\t\t" . '<td>' . $item["referencia"] . '</td>' . "\n"
                        . "\t\t\t" . '<td>' . $item["nomCliente"] . '</td>' . "\n"
                        . "\t\t\t" . '<td>' . $item["ie"] . '</td>' . "\n"
                        . "\t\t\t" . '<td>' . $item["regimen"] . '</td>' . "\n"
                        . "\t\t\t" . '<td>' . $item["fecha_pedimento"] . '</td>' . "\n"
                        . "\t\t\t" . '<td>' . (isset($pedimento[0]["FechaEntrada"]) ? $misc->transformDate($pedimento[0]["FechaEntrada"], 1) : '&nbsp;') . '</td>' . "\n"
                        . "\t\t\t" . '<td>' . (isset($pedimento[0]["TipoCambio"]) ? $pedimento[0]["TipoCambio"] : '&nbsp;') . '</td>' . "\n"
                        . "\t\t\t" . '<td>' . $item["ref_factura"] . '</td>' . "\n"
                        . "\t\t\t" . '<td>' . $item["bultos"] . '</td>' . "\n";

                        echo "\t\t\t" . $misc->fn($item["valor_aduana"]) . "\n";
                        $valadu += $item["valor_aduana"];
                        $gmani = $misc->sumArray($item["conceptos"], array('maniobras'));
                        echo (isset($pedimento[0]["DTA"]) ? "\t\t\t" . $misc->fn($pedimento[0]["DTA"]) . "\n" : "\t\t\t" . '<td>&nbsp;</td>' . "\n");
                        echo (isset($pedimento[0]["IVA"]) ? "\t\t\t" . $misc->fn($pedimento[0]["IVA"]) . "\n" : "\t\t\t" . '<td>&nbsp;</td>' . "\n");
                        echo (isset($pedimento[0]["CNT"]) ? "\t\t\t" . $misc->fn($pedimento[0]["CNT"]) . "\n" : "\t\t\t" . '<td>&nbsp;</td>' . "\n");
                        echo (isset($pedimento[0]["IGI"]) ? "\t\t\t" . $misc->fn($pedimento[0]["IGI"]) . "\n" : "\t\t\t" . '<td>&nbsp;</td>' . "\n");
                        echo (isset($pedimento[0]["Prevalidacion"]) ? "\t\t\t" . $misc->fn($pedimento[0]["Prevalidacion"]) . "\n" : "\t\t\t" . '<td>&nbsp;</td>' . "\n");
                        echo "\t\t\t" . '<td>' . (isset($pedimento[0]["Guias"]) ? (preg_match('/,$/', $pedimento[0]["Guias"])) ? substr($pedimento[0]["Guias"], 0, -1) : $pedimento[0]["Guias"] : '&nbsp;') . '</td>' . "\n";
                        echo "\t\t\t" . $misc->fn($gmani) . "\n";
                        $gmaniobras += $gmani;
                        $galma = $misc->sumArray($item["conceptos"], array('almacenaje', 'almacenajes'));
                        echo "\t\t\t" . $misc->fn($galma) . "\n";
                        $galmacenaj +=$galma;
                        $gdemo = $misc->sumArray($item["conceptos"], array('demoras'));
                        echo "\t\t\t" . $misc->fn($gdemo) . "\n";
                        $gdemoras += $gdemo;
                        $gfaer = $misc->sumArray($item["conceptos"], array('flete_aereo'));
                        echo "\t\t\t" . $misc->fn($gfaer) . "\n";
                        $gfaereo += $gfaer;
                        $gfm = $misc->sumArray($item["conceptos"], array('flete_maritimo'));
                        echo "\t\t\t" . $misc->fn($gfm) . "\n";
                        $gfmari += $gfm;
                        $gft = $misc->sumArray($item["conceptos"], array('fletes_y_acarreos', 'flete_terrestre'));
                        echo "\t\t\t" . $misc->fn($gft) . "\n";
                        $gfterre += $gft;
                        $gfle = $misc->sumArray($item["conceptos"], array('flete', 'fletes'));
                        echo "\t\t\t" . $misc->fn($gfle) . "\n";
                        $gfletes += $gfle;
                        $gali = $misc->sumArray($item["conceptos"], array('alijadores'));
                        echo "\t\t\t" . $misc->fn($gali) . "\n";
                        $galijadores += $gali;
                        // GASTOS COMPROBADOS
                        $gcomp = $misc->sumArray($item["conceptos"], array(
                            'maniobras',
                            'almacenaje', 'almacenajes',
                            'demoras', '',
                            'flete_aereo',
                            'flete_maritimo',
                            'fletes_y_acarreos', 'flete_terrestre',
                            'flete', 'fletes',
                            'alijadores'));
                        echo "\t\t\t" . $misc->fn($gcomp) . "\n";
                        $gcomprob += $gcomp;
                        /// COMPLEMENTARIOS
                        $gcomple = $misc->sumArray($item["conceptos"], array('gastos_complementarios_maniobras', 'gastos_complementarios_alijadores', 'gastos_complementarios', 'gastos_complementarios_almacenajes', 'gastos_complementarios_almacenaje', 'gastos_complementarios_demoras', 'gastos_complementarios_fletes', 'gastos_compl_fletes', 'servicios_de_charter', 'tiempo_extra', 'servicio_extraordinario', 'gastos_fletes', 'gastos_alijadores', 'gastos_maniobras', 'gastos_almacenajes', 'gastos_demoras', 'gastos_alijadores', 'servicio_extra_sab_dom_y_fest', 'rectificacion_de_pedimento', 'servicio_extra_despues_de_las_18hrs'));
                        echo "\t\t\t" . $misc->fn($gcomple) . "\n";
                        $gcomplementarios += $gcomple;
                        $impa = $misc->sumArray($item["conceptos"], array('impuestos_aduanales'));
                        echo "\t\t\t" . $misc->fn($impa) . "\n";
                        $impadu += $impa;
                        $reva = $misc->sumArray($item["conceptos"], array('revalidacion'));
                        echo "\t\t\t" . $misc->fn($reva) . "\n";
                        $revalidacion += $reva;
                        $recti = $misc->sumArray($item["conceptos"], array('rectificaciones'));
                        echo "\t\t\t" . $misc->fn($recti) . "\n";
                        $rectificacion += $recti;
                        $honorarios += $item["honorarios"];
                        echo "\t\t\t" . $misc->fn($item["honorarios"]) . "\n";
                        $subtotal += $item["subtotal"];
                        echo "\t\t\t" . $misc->fn($item["subtotal"]) . "\n";
                        $iva += $item["iva"];
                        echo "\t\t\t" . $misc->fn($item["iva"]) . "\n";
                        $anticipo += $item["anticipo"];
                        echo "\t\t\t" . $misc->fn($item["anticipo"]) . "\n";
                        $total += $item["total"];
                        echo "\t\t\t" . $misc->fn($item["total"]) . "\n";
                        echo "\t\t</tr>\n";
                        unset($pedimento);
                    } else {
                        echo "\t\t" . '<tr>' . "\n"
                        . "\t\t\t" . '<td>' . $item["fecha_factura"] . '</td>' . "\n"
                        . "\t\t\t" . "<td><a style=\"cursor:pointer; color:blue; text-decoration: underline;\" onclick=\"window.open('/administracion/data/consulta-de-factura?folio={$item["factura"]}', '_blank', 'toolbar=0,location=0,menubar=0,height=550,width=900,scrollbars=yes');\">" . $item["factura"] . '</a></td>' . "\n"
                        . "\t\t\t" . '<td>' . $item["patente"] . '</td>' . "\n"
                        . "\t\t\t" . '<td>' . $item["aduana"] . '</td>' . "\n"
                        . "\t\t\t" . '<td>' . $item["pedimento"] . '</td>' . "\n"
                        . "\t\t\t" . '<td>' . $item["referencia"] . '</td>' . "\n"
                        . "\t\t\t" . '<td>' . $item["nomCliente"] . '</td>' . "\n"
                        . "\t\t\t" . '<td>' . $item["ie"] . '</td>' . "\n"
                        . "\t\t\t" . '<td>' . $item["regimen"] . '</td>' . "\n"
                        . "\t\t\t" . '<td>' . $item["fecha_pedimento"] . '</td>' . "\n"
                        . "\t\t\t" . '<td>' . (isset($pedimento[0]["FechaEntrada"]) ? $misc->transformDate($pedimento[0]["FechaEntrada"], 1) : '&nbsp;') . '</td>' . "\n"
                        . "\t\t\t" . '<td>' . (isset($pedimento[0]["TipoCambio"]) ? $pedimento[0]["TipoCambio"] : '&nbsp;') . '</td>' . "\n"
                        . "\t\t\t" . '<td>' . $item["ref_factura"] . '</td>' . "\n"
                        . "\t\t\t" . '<td>' . $item["bultos"] . '</td>' . "\n";

                        if (!preg_match('/H$/', $item["referencia"])) {
                            echo "\t\t\t" . $misc->fn($item["valor_aduana"]) . "\n";
                        } else {
                            echo "\t\t\t" . '<td>&nbsp;</td>' . "\n";
                        }
                        $totales["vaduana"] += $item["valor_aduana"];
                        echo (isset($pedimento[0]["DTA"]) ? "\t\t\t" . $misc->fn($pedimento[0]["DTA"]) . "\n" : "\t\t\t" . '<td>&nbsp;</td>' . "\n");
                        echo (isset($pedimento[0]["IVA"]) ? "\t\t\t" . $misc->fn($pedimento[0]["IVA"]) . "\n" : "\t\t\t" . '<td>&nbsp;</td>' . "\n");
                        echo (isset($pedimento[0]["CNT"]) ? "\t\t\t" . $misc->fn($pedimento[0]["CNT"]) . "\n" : "\t\t\t" . '<td>&nbsp;</td>' . "\n");
                        echo (isset($pedimento[0]["IGI"]) ? "\t\t\t" . $misc->fn($pedimento[0]["IGI"]) . "\n" : "\t\t\t" . '<td>&nbsp;</td>' . "\n");
                        echo (isset($pedimento[0]["Prevalidacion"]) ? "\t\t\t" . $misc->fn($pedimento[0]["Prevalidacion"]) . "\n" : "\t\t\t" . '<td>&nbsp;</td>' . "\n");
                        echo "\t\t\t" . '<td>' . (isset($pedimento[0]["Guias"]) ? (preg_match('/,$/', $pedimento[0]["Guias"])) ? substr($pedimento[0]["Guias"], 0, -1) : $pedimento[0]["Guias"] : '&nbsp;') . '</td>' . "\n";

                        echo "\t\t\t" . $misc->fn($misc->sumArray($item["conceptos"], array('maniobras'))) . "\n";
                        $totales["maniobras"] += $tmaniobra = $misc->sumArray($item["conceptos"], array('maniobras'));
                        echo "\t\t\t" . $misc->fn($misc->sumArray($item["conceptos"], array('almacenaje', 'almacenajes'))) . "\n";
                        $totales["almacenajes"] += $talmacenjes = $misc->sumArray($item["conceptos"], array('almacenaje', 'almacenajes'));
                        echo "\t\t\t" . $misc->fn($misc->sumArray($item["conceptos"], array('demoras'))) . "\n";
                        $totales["demoras"] += $tdemoras = $misc->sumArray($item["conceptos"], array('demoras'));
                        echo "\t\t\t" . $misc->fn($misc->sumArray($item["conceptos"], array('flete_aereo'))) . "\n";
                        $totales["flete_aereo"] += $tfaereo = $misc->sumArray($item["conceptos"], array('flete_aereo'));
                        echo "\t\t\t" . $misc->fn($misc->sumArray($item["conceptos"], array('flete_maritimo'))) . "\n";
                        $totales["flete_maritimo"] += $tfletmari = $misc->sumArray($item["conceptos"], array('flete_maritimo'));
                        echo "\t\t\t" . $misc->fn($misc->sumArray($item["conceptos"], array('flete_terrestre'))) . "\n";
                        $totales["flete_terrestre"] += $tfleteterr = $misc->sumArray($item["conceptos"], array('flete_terrestre'));
                        echo "\t\t\t" . $misc->fn($misc->sumArray($item["conceptos"], array('fletes'))) . "\n";
                        $totales["fletes"] += $tfletes = $misc->sumArray($item["conceptos"], array('fletes'));
                        echo "\t\t\t" . $misc->fn($misc->sumArray($item["conceptos"], array('alijadores'))) . "\n";
                        $totales["alijadores"] += $talija = $misc->sumArray($item["conceptos"], array('alijadores'));
                        echo "\t\t\t" . $misc->fn($misc->sumArray($item["conceptos"], array('impuestos_aduanales'))) . "\n";
                        $totales["impuestos_aduanales"] += $timpadu = $misc->sumArray($item["conceptos"], array('impuestos_aduanales'));
                        echo "\t\t\t" . $misc->fn($misc->sumArray($item["conceptos"], array('revalidacion'))) . "\n";
                        $totales["revalidacion"] += $trevalidacion = $misc->sumArray($item["conceptos"], array('revalidacion'));
                        echo "\t\t\t" . $misc->fn($misc->sumArray($item["conceptos"], array('broker_usa'))) . "\n";
                        $totales["broker_usa"] += $tbroker = $misc->sumArray($item["conceptos"], array('broker_usa'));

                        $totales["totalcomprobados"] += $totacomprobados = $tmaniobra + $talmacenjes + $tdemoras + $tfaereo + $trevalidacion + $timpadu + $talija + $tfletes + $tfleteterr + $tfletmari + $tbroker;
                        echo "\t\t\t" . $misc->fn($totacomprobados) . "\n";

                        echo "\t\t\t" . $misc->fn($misc->sumArray($item["conceptos"], array('gastos_complementarios'))) . "\n";
                        $totales["gastos_complementarios"] += $tgcomplementarios = $misc->sumArray($item["conceptos"], array('gastos_complementarios'));
                        echo "\t\t\t" . $misc->fn($misc->sumArray($item["conceptos"], array('gastos_complementarios_maniobras', 'gastos_maniobras'))) . "\n";
                        $totales["gastos_complementarios_maniobras"] += $tgcmaniobra = $misc->sumArray($item["conceptos"], array('gastos_complementarios_maniobras', 'gastos_maniobras'));
                        echo "\t\t\t" . $misc->fn($misc->sumArray($item["conceptos"], array('gastos_complementarios_fletes', 'gastos_compl_fletes', 'gastos_fletes'))) . "\n";
                        $totales["gastos_complementarios_fletes"] += $tgcfletes = $misc->sumArray($item["conceptos"], array('gastos_complementarios_fletes', 'gastos_compl_fletes', 'gastos_fletes'));
                        echo "\t\t\t" . $misc->fn($misc->sumArray($item["conceptos"], array('gastos_complementarios_almacenajes', 'gastos_almacenajes', 'gastos_complementarios_almacenaje'))) . "\n";
                        $totales["gastos_complementarios_almacenajes"] += $tgcalmacenajes = $misc->sumArray($item["conceptos"], array('gastos_complementarios_almacenajes', 'gastos_almacenajes', 'gastos_complementarios_almacenaje'));
                        echo "\t\t\t" . $misc->fn($misc->sumArray($item["conceptos"], array('gastos_complementarios_alijadores', 'gastos_alijadores'))) . "\n";
                        $totales["gastos_complementarios_alijadores"] += $tgcalijadores = $misc->sumArray($item["conceptos"], array('gastos_complementarios_alijadores', 'gastos_alijadores'));
                        echo "\t\t\t" . $misc->fn($misc->sumArray($item["conceptos"], array('gastos_complementarios_demoras', 'gastos_demoras'))) . "\n";
                        $totales["gastos_complementarios_demoras"] += $tgcdemoras = $misc->sumArray($item["conceptos"], array('gastos_complementarios_demoras', 'gastos_demoras'));
                        echo "\t\t\t" . $misc->fn($misc->sumArray($item["conceptos"], array('tiempo_extra', 'servicio_extraordinario', 'servicio_extra_sab_dom_y_fest'))) . "\n";
                        $totales["gastos_complementarios_servextra"] += $tgcservextra = $misc->sumArray($item["conceptos"], array('tiempo_extra', 'servicio_extraordinario', 'servicio_extra_sab_dom_y_fest', 'servicio_extra_despues_de_las_18hrs'));
                        echo "\t\t\t" . $misc->fn($misc->sumArray($item["conceptos"], array('servicios_de_charter'))) . "\n";
                        $totales["gastos_complementarios_charter"] += $tgccharter = $misc->sumArray($item["conceptos"], array('servicios_de_charter'));
                        echo "\t\t\t" . $misc->fn($misc->sumArray($item["conceptos"], array('gastos_complementarios_cruce'))) . "\n";
                        $totales["gastos_complementarios_cruce"] += $tgccruce = $misc->sumArray($item["conceptos"], array('gastos_complementarios_cruce'));
                        echo "\t\t\t" . $misc->fn($misc->sumArray($item["conceptos"], array('rectificacion_de_pedimento'))) . "\n";
                        $totales["gastos_complementarios_recti"] += $tgcrect = $misc->sumArray($item["conceptos"], array('rectificacion_de_pedimento'));

                        $totales["totalcomplementarios"] += $totalcomplementarios = $tgcomplementarios + $tgcmaniobra + $tgcfletes + $tgcalmacenajes + $tgcdemoras + $tgcservextra + $tgccharter + $tgccruce + $tgcalijadores + $tgcrect;
                        echo "\t\t\t" . $misc->fn($totalcomplementarios) . "\n";

                        echo "\t\t\t" . $misc->fn($misc->sumArray($item["conceptos"], array('rectificaciones'))) . "\n";
                        $totales["rectificaciones"] += $misc->sumArray($item["conceptos"], array('rectificaciones'));
                        echo "\t\t\t" . $misc->fn($item["honorarios"]) . "\n";
                        $totales["honorarios"] += $item["honorarios"];
                        echo "\t\t\t" . $misc->fn($item["subtotal"]) . "\n";
                        $totales["subtotal"] += $item["subtotal"];
                        echo "\t\t\t" . $misc->fn($item["iva"]) . "\n";
                        $totales["iva"] += $item["iva"];
                        echo "\t\t\t" . $misc->fn($item["anticipo"]) . "\n";
                        $totales["anticipo"] += $item["anticipo"];
                        echo "\t\t\t" . $misc->fn($item["total"]) . "\n";
                        $totales["total"] += $item["total"];
                        echo "\t\t" . '</tr>' . "\n";
                        unset($pedimento);
                    }
                }
                if ($gets["desglose"] == 0) {
                    // TOTALES
                    echo "\t\t<tr>\n"
                    . "\t\t\t<td colspan=\"14\">&nbsp;</td>" . "\n"
                    . "\t\t\t" . $misc->fn($valadu, true) . "\n"
                    . "\t\t\t" . "<td>&nbsp;</td>" . "\n"// dta
                    . "\t\t\t" . "<td>&nbsp;</td>" . "\n"// iva ped
                    . "\t\t\t" . "<td>&nbsp;</td>" . "\n"// cnt
                    . "\t\t\t" . "<td>&nbsp;</td>" . "\n"// igi
                    . "\t\t\t" . "<td>&nbsp;</td>" . "\n"// prev
                    . "\t\t\t" . "<td>&nbsp;</td>" . "\n"// guias
                    . "\t\t\t" . $misc->fn($gmaniobras, true) . "\n"
                    . "\t\t\t" . $misc->fn($galmacenaj, true) . "\n"
                    . "\t\t\t" . $misc->fn($gdemoras, true) . "\n"
                    . "\t\t\t" . $misc->fn($gfaer, true) . "\n"
                    . "\t\t\t" . $misc->fn($gfmari, true) . "\n"
                    . "\t\t\t" . $misc->fn($gfterre, true) . "\n"
                    . "\t\t\t" . $misc->fn($gfletes, true) . "\n"
                    . "\t\t\t" . $misc->fn($galijadores, true) . "\n"
                    . "\t\t\t" . $misc->fn($gcomprob, true) . "\n"
                    . "\t\t\t" . $misc->fn($gcomplementarios, true) . "\n"
                    . "\t\t\t" . $misc->fn($impadu, true) . "\n"
                    . "\t\t\t" . $misc->fn($revalidacion, true) . "\n"
                    . "\t\t\t" . $misc->fn($rectificacion, true) . "\n"
                    . "\t\t\t" . $misc->fn($honorarios, true) . "\n"
                    . "\t\t\t" . $misc->fn($subtotal, true) . "\n"
                    . "\t\t\t" . $misc->fn($iva, true) . "\n"
                    . "\t\t\t" . $misc->fn($anticipo, true) . "\n"
                    . "\t\t\t" . $misc->fn($total, true) . "\n"
                    . "\t\t</tr>\n";
                    $html = "\t</table>\n"
                            . "</body>\n"
                            . "</html>\n";
                    echo $html;
                } else {
                    // TOTALES DESGLOSE COMPLEMENTARIOS
                    echo "\t\t" . '<tr>' . "\n"
                    . "\t\t\t" . '<td colspan="14">&nbsp;</td>' . "\n"
                    . "\t\t\t" . $misc->fn($totales["vaduana"], true) . "\n"
                    . "\t\t\t" . "<td>&nbsp;</td>" . "\n" // dta
                    . "\t\t\t" . "<td>&nbsp;</td>" . "\n" // iva ped
                    . "\t\t\t" . "<td>&nbsp;</td>" . "\n" // cnt
                    . "\t\t\t" . "<td>&nbsp;</td>" . "\n" // igi
                    . "\t\t\t" . "<td>&nbsp;</td>" . "\n" // prev
                    . "\t\t\t" . "<td>&nbsp;</td>" . "\n" // guias
                    . "\t\t\t" . $misc->fn($totales["maniobras"], true) . "\n"
                    . "\t\t\t" . $misc->fn($totales["almacenajes"], true) . "\n"
                    . "\t\t\t" . $misc->fn($totales["demoras"], true) . "\n"
                    . "\t\t\t" . $misc->fn($totales["flete_aereo"], true) . "\n"
                    . "\t\t\t" . $misc->fn($totales["flete_maritimo"], true) . "\n"
                    . "\t\t\t" . $misc->fn($totales["flete_terrestre"], true) . "\n"
                    . "\t\t\t" . $misc->fn($totales["fletes"], true) . "\n"
                    . "\t\t\t" . $misc->fn($totales["alijadores"], true) . "\n"
                    . "\t\t\t" . $misc->fn($totales["impuestos_aduanales"], true) . "\n"
                    . "\t\t\t" . $misc->fn($totales["revalidacion"], true) . "\n"
                    . "\t\t\t" . $misc->fn($totales["broker_usa"], true) . "\n"
                    . "\t\t\t" . $misc->fn($totales["totalcomprobados"], true) . "\n"
                    . "\t\t\t" . $misc->fn($totales["gastos_complementarios"], true) . "\n"
                    . "\t\t\t" . $misc->fn($totales["gastos_complementarios_maniobras"], true) . "\n"
                    . "\t\t\t" . $misc->fn($totales["gastos_complementarios_fletes"], true) . "\n"
                    . "\t\t\t" . $misc->fn($totales["gastos_complementarios_almacenajes"], true) . "\n"
                    . "\t\t\t" . $misc->fn($totales["gastos_complementarios_alijadores"], true) . "\n"
                    . "\t\t\t" . $misc->fn($totales["gastos_complementarios_demoras"], true) . "\n"
                    . "\t\t\t" . $misc->fn($totales["gastos_complementarios_servextra"], true) . "\n"
                    . "\t\t\t" . $misc->fn($totales["gastos_complementarios_charter"], true) . "\n"
                    . "\t\t\t" . $misc->fn($totales["gastos_complementarios_cruce"], true) . "\n"
                    . "\t\t\t" . $misc->fn($totales["gastos_complementarios_recti"], true) . "\n"
                    . "\t\t\t" . $misc->fn($totales["totalcomplementarios"], true) . "\n"
                    . "\t\t\t" . $misc->fn($totales["rectificaciones"], true) . "\n"
                    . "\t\t\t" . $misc->fn($totales["honorarios"], true) . "\n"
                    . "\t\t\t" . $misc->fn($totales["subtotal"], true) . "\n"
                    . "\t\t\t" . $misc->fn($totales["iva"], true) . "\n"
                    . "\t\t\t" . $misc->fn($totales["anticipo"], true) . "\n"
                    . "\t\t\t" . $misc->fn($totales["total"], true) . "\n"
                    . "\t\t" . '</tr>' . "\n";
                    $html = "\t</table>\n"
                            . "</body>\n"
                            . "</html>\n";
                    echo $html;
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    public function consultaDeFacturaAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        ini_set("soap.wsdl_cache_enabled", 0);
        $sica = new OAQ_Sica;
        $misc = new OAQ_Misc();
        $gets = $this->_request->getParams();
        if (isset($gets["folio"])) {
            $data = $sica->getInvoiceDetails($gets["folio"]);
            echo '<!doctype html><html lang="en">'
            . '<head>'
            . '<meta charset="utf-8">'
            . '<title>Detalle de cuenta de gastos (OAQ)</title>'
            . '</head>'
            . "<style>body {margin:0;padding:0; font-family:sans-serif;}"
            . "table {border-collapse:collapse; }"
            . "table th, table td {font-size: 12px; border: 1px #555 solid; padding: 2px 5px;}"
            . "table th {background: #f1f1f1;"
            . "}"
            . "h3 {"
            . 'margin: 0; padding:0; background-color: #225566; color: #fff; text-align: left; font-family: "Courier New", Courier, monospace; padding: 2px 5px;'
            . "}"
            . "</style>"
            . "<body>";
            echo '<h3>' . $data[0]["factura"] . '</h3>';
            echo '<table width="100%">'
            . '<tr>'
            . '<th>Patente</th>'
            . '<td>' . $data[0]["patente"] . '</td>'
            . '<th>Aduana</th>'
            . '<td>' . $data[0]["aduana"] . '</td>'
            . '<th>Pedimento</th>'
            . '<td>' . $data[0]["pedimento"] . '</td>'
            . '<th>Referencia Fac.</th>'
            . '<td>' . $data[0]["referencia"] . '</td>'
            . '<th>Usuario</th>'
            . '<td>' . $data[0]["usuarios"] . '</td>'
            . '</tr>'
            . '<tr>'
            . '<th>Regimen</th>'
            . '<td>' . $data[0]["regimen"] . '</td>'
            . '<th>I/E</th>'
            . '<td>' . $data[0]["ei"] . '</td>'
            . '<th>Valor Aduana</th>'
            . $misc->fn($data[0]["valor_aduana"])
            . '<th>Fecha Pedimento</th>'
            . '<td>' . $data[0]["fecha_pedimento"] . '</td>'
            . '<th>Fecha Factura</th>'
            . '<td>' . $data[0]["fecha_factura"] . '</td>'
            . '</tr>'
            . '<tr>'
            . '<th colspan="10">Conceptos</th>'
            . '</tr>';
            if (!empty($data[0]["conceptos"])) {
                echo '<tr>'
                . '<th colspan="5">Nombre</th>'
                . '<th colspan="1">Moneda</th>'
                . '<th colspan="4">Importe</th>'
                . '</tr>';
                foreach ($data[0]["conceptos"] as $k => $item) {
                    echo '<tr>'
                    . '<td colspan="5">' . strtoupper(preg_replace('/_/', ' ', $k)) . '</td>'
                    . '<td colspan="1">' . $item["moneda"] . '</td>'
                    . $misc->fn($item["total"], null, 4)
                    . '</tr>';
                }
            }
            echo '<tr>'
            . '<th colspan="6">SubTotal</th>'
            . $misc->fn($data[0]["subtotal"], null, 4)
            . '</tr>'
            . '<tr>'
            . '<th colspan="6">IVA</th>'
            . $misc->fn($data[0]["iva"], null, 4)
            . '</tr>'
            . '<tr>'
            . '<th colspan="6">Anticipo</th>'
            . $misc->fn($data[0]["anticipo"], null, 4)
            . '</tr>'
            . '<tr>'
            . '<th colspan="6">Total</th>'
            . $misc->fn($data[0]["total"], null, 4)
            . '</tr>';
            echo '</table>';
            $context = stream_context_create(array(
                "ssl" => array(
                    "verify_peer" => false,
                    "verify_peer_name" => false,
                    "allow_self_signed" => true
                )
            ));
            if ($data[0]["patente"] == '3589' && $data[0]["aduana"] == '640') {
                $sisped = new Zend_Soap_Client("https://192.168.200.5/casaws/zfsoapsitawin?wsdl", array('compression' => SOAP_COMPRESSION_ACCEPT, "stream_context" => $context));
                $pedimento = $sisped->detallePedimento($data[0]["patente"], 640, $data[0]["pedimento"]);
                if (empty($pedimento)) {
                    $pedimento = $sisped->detallePedimento($data[0]["patente"], 646, $data[0]["pedimento"]);
                    $pedimento[0]["Origen"] = "Aeropuerto Querétaro";
                } else {
                    $pedimento[0]["Origen"] = "Operaciones Especiales Querétaro";
                }
            }
            if ($data[0]["patente"] == '3933' && $data[0]["aduana"] == '430') {
                $sisped = new Zend_Soap_Client("http://162.253.186.242:8081/zfsoapaduanet?wsdl", array('compression' => SOAP_COMPRESSION_ACCEPT, "stream_context" => $context));
                $pedimento = $sisped->detallePedimento(3933, 430, $data[0]["pedimento"]);
                $pedimento[0]["Origen"] = "Aduanet Veracruz (Pat. 3933)";
            }
            if (!empty($pedimento)) {
                echo '<h3>' . $pedimento[0]["Referencia"] . '</h3>';
                echo '<table width="100%">'
                . '<tr>'
                . '<th>Nom. Cliente</th>'
                . '<td colspan="5">' . $pedimento[0]["NomCliente"] . '</td>'
                . '<th>RFC</th>'
                . '<td>' . $pedimento[0]["RFCCliente"] . '</td>'
                . '<th>Ref. Corresponsal</th>'
                . '<td>' . $pedimento[0]["Trafico"] . '</td>'
                . '</tr>'
                . '<tr>'
                . '<th>Operación</th>'
                . '<td>' . $pedimento[0]["Operacion"] . '</td>'
                . '<th>Aduana</th>'
                . '<td>' . $pedimento[0]["Aduana"] . '</td>'
                . '<th>Sección descargo</th>'
                . '<td>' . $pedimento[0]["SeccionDescargo"] . '</td>'
                . '<th>Fecha Entrada</th>'
                . '<td>' . $pedimento[0]["FechaEntrada"] . '</td>'
                . '<th>Fecha Pago</th>'
                . '<td>' . $pedimento[0]["FechaPago"] . '</td>'
                . '</tr>'
                . '<tr>'
                . '<th>T. Entrada</th>'
                . '<td>' . $pedimento[0]["TransporteEntrada"] . '</td>'
                . '<th>T. Arribo</th>'
                . '<td>' . $pedimento[0]["TransporteArribo"] . '</td>'
                . '<th>T. Salida</th>'
                . '<td>' . $pedimento[0]["TransporteSalida"] . '</td>'
                . '<th>Firma Validación</th>'
                . '<td>' . $pedimento[0]["FirmaValidacion"] . '</td>'
                . '<th>Firma Banco</th>'
                . '<td>' . $pedimento[0]["FirmaBanco"] . '</td>'
                . '</tr>'
                . '<tr>'
                . '<th>Consolidado</th>'
                . '<td>' . $pedimento[0]["Consolidado"] . '</td>'
                . '<th>Aduana Entrada</th>'
                . '<td>' . $pedimento[0]["AduanaEntrada"] . '</td>'
                . '<th>Sección Entrada</th>'
                . '<td>' . $pedimento[0]["SeccionEntrada"] . '</td>'
                . '<th>Rectificación</th>'
                . '<td>' . $pedimento[0]["Rectificacion"] . '</td>'
                . '<th>Cve. Ped</th>'
                . '<td>' . $pedimento[0]["CvePed"] . '</td>'
                . '</tr>'
                . '<tr>'
                . '<th>Valor Dolares</th>'
                . $misc->fn($pedimento[0]["ValorDolares"])
                . '<th>Valor Aduana</th>'
                . $misc->fn($pedimento[0]["ValorAduana"])
                . '<th>Fletes</th>'
                . $misc->fn($pedimento[0]["Fletes"])
                . '<th>Seguros</th>'
                . $misc->fn($pedimento[0]["Seguros"])
                . '<th>Embalajes</th>'
                . $misc->fn($pedimento[0]["Embalajes"])
                . '</tr>'
                . '<tr>'
                . '<th>Otros Incr.</th>'
                . $misc->fn($pedimento[0]["OtrosIncrementales"])
                . '<th>DTA</th>'
                . $misc->fn($pedimento[0]["DTA"])
                . '<th>IVA</th>'
                . $misc->fn($pedimento[0]["IVA"])
                . '<th>IGI</th>'
                . $misc->fn($pedimento[0]["IGI"])
                . '<th>Prevalidación</th>'
                . $misc->fn($pedimento[0]["Prevalidacion"])
                . '</tr>'
                . '<tr>'
                . '<th>TotalEfectivo</th>'
                . $misc->fn($pedimento[0]["TotalEfectivo"])
                . '<th>Peso Bruto</th>'
                . '<td>' . $pedimento[0]["PesoBruto"] . '</td>'
                . '<th>Bultos</th>'
                . '<td>' . $pedimento[0]["Bultos"] . '</td>'
                . '<td colspan="4">&nbsp;</td>'
                . '</tr>'
                . '<tr>'
                . '<th>Usuario Alta</th>'
                . '<td colspan="3">' . $pedimento[0]["UsuarioAlta"] . '</td>'
                . '<th>Usuario Modif.</th>'
                . '<td colspan="2">' . $pedimento[0]["UsuarioModif"] . '</td>'
                . '<th>Origen</th>'
                . '<td colspan="2">' . $pedimento[0]["Origen"] . '</td>'
                . '</tr>'
                . '</table>';
            }
            echo '</body>'
            . '</html>';
            $arch = new Archivo_Model_RepositorioMapper();
            $archivos = $arch->getInvoice($gets["folio"], "OAQ030623UL8");
            if (!empty($archivos)) {
                echo '<h3>Archivos (CDF/CDFi, PDF)</h3>';
                echo '<table width="100%">';
                foreach ($archivos as $file) {
                    echo '<tr>'
                    . '<td><a href="/archivo/index/download-file?id=' . $file["id"] . '">' . $file["nom_archivo"] . '</a></td>'
                    . '</tr>';
                }
                echo '</table>';
            }
        }
    }

    public function xmlFileUploadAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $search = NULL ? $search = new Zend_Session_Namespace('') : $search = new Zend_Session_Namespace('OAQCtaGastos');
        if (isset($search->uploadDir) && !file_exists($search->uploadDir)) {
            mkdir($search->uploadDir, 0777, true);
        }
        $adapter = new Zend_File_Transfer_Adapter_Http();
        $adapter->setDestination($search->uploadDir);
        $adapter->addValidator('Extension', false, array('extension' => 'xml', 'case' => true));
        if (!$adapter->receive()) {
            echo '<span class="error">Error al subir</span>';
            return true;
        }
        echo '<span style="color:green">OK</span>';
        return true;
    }

    public function readInvoicesDirectoryAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $sat = new SAT_Facturas();
        $search = NULL ? $search = new Zend_Session_Namespace('') : $search = new Zend_Session_Namespace('OAQCtaGastos');

        if (isset($search->uploadDir) && !file_exists($search->uploadDir)) {
            mkdir($search->uploadDir, 0777, true);
        }
        if (file_exists($search->uploadDir)) {
            $html = "<table class=\"table table-striped table-bordered table-hover\">"
                    . "<tr>"
                    . "<th>Nombre archivo</th>"
                    . "<th>Emisor RFC</th>"
                    . "<th>Receptor RFC</th>"
                    . "<th>Folio</th>"
                    . "<th>Serie</th>"
                    . "<th>Total</th>"
                    . "<th>Fecha</th>"
                    . "<th>UUID</th>"
                    . "<th>&nbsp;</th>"
                    . "</tr>";
            $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($search->uploadDir), RecursiveIteratorIterator::SELF_FIRST);
            foreach ($objects as $name => $object) {
                if (is_dir($name)) {
                    continue;
                }
                if (!preg_match('/xml$/i', $name)) {
                    continue;
                } else {
                    if (!is_readable($object->getPathname())) {
                        continue;
                    }
                    $xml = file_get_contents($object->getPathName());
                    $array = $sat->satToArray($xml);
                    $emisor = $sat->obtenerGenerales($array["Emisor"]);
                    $receptor = $sat->obtenerGenerales($array["Receptor"]);
                    $gral = $sat->obtenerDatosFactura($emisor["rfc"], $array["@attributes"]);
                    $comp = $sat->obtenerComplemento($array["Complemento"]);
                    $html .="<tr>"
                            . "<td>" . basename($object->getPathname()) . "</td>"
                            . "<td>{$emisor["rfc"]}</td>"
                            . "<td>{$receptor["rfc"]}</td>"
                            . "<td>{$gral["folio"]}</td>"
                            . "<td>{$gral["serie"]}</td>"
                            . "<td><span style=\"float:left;\">$&nbsp;</span>{$gral["total"]}</td>"
                            . "<td>{$gral["fecha"]}</td>"
                            . "<td>" . (isset($comp["uuid"]) ? $comp["uuid"] : '&nbsp;') . "</td>"
                            . "<td>&nbsp;</td>"
                            . "</tr>";
                    unset($array);
                    unset($emisor);
                    unset($receptor);
                    unset($gral);
                    unset($comp);
                }
            }
            $html .= "</table>";
            echo $html;
        } else {
            echo "No se han cargado facturas.";
        }
        return true;
    }

    public function processInvoicesAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $search = NULL ? $search = new Zend_Session_Namespace('') : $search = new Zend_Session_Namespace('OAQCtaGastos');
        if (file_exists($search->uploadDir)) {
            $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($search->uploadDir), RecursiveIteratorIterator::SELF_FIRST);
            foreach ($objects as $name => $object) {
                if (is_dir($name)) {
                    continue;
                }
                if (!preg_match('/xml$/i', $name)) {
                    continue;
                } else {
                    if (!is_readable($object->getPathname())) {
                        continue;
                    }
                    $xml = file_get_contents($object->getPathName());
                }
            }
        } else {
            echo "No existen facturas que procesar.";
        }
        return true;
    }

    public function polizaCargarArchivosAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_arch = NULL ? $this->_arch = new Zend_Session_Namespace('') : $this->_arch = new Zend_Session_Namespace('Navigation');
        $archive = new Archivo_Model_RepositorioContaMapper();
        if (isset($this->_arch->poliza)) {
            $files = $archive->archivosDePoliza($this->_arch->poliza);
        } else {
            die("No numero de poliza.");
        }
        if (isset($files) && !empty($files)) {
            $html .= '<table class="traffic-table">'
                    . '<thead>'
                    . '<tr>'
                    . '<th>&nbsp;</th>'
                    . '<th>Nombre de archivo</th>'
                    . '<th>Tipo</th>'
                    . '<th>UUID</th>'
                    . '<th>Fecha</th>'
                    . '<th>Usuario</th>'
                    . '<th>Tamaño</th>'
                    . '<th>&nbsp;</th>'
                    . '</tr>'
                    . '</thead>'
                    . '<tbody>';
            foreach ($files as $item) {
                if (preg_match('/Acuse E-Document/i', $item["nombre"])) {
                    $edoc = substr($item["nom_archivo"], 0, -4);
                } else {
                    $edoc = '&nbsp;';
                }
                if (file_exists($item["ubicacion"])) {
                    $filesize = number_format(filesize($item["ubicacion"]) / 1024 / 1024, 2);
                }
                if ($item["tipo_archivo"] == 16 && $item["cfdi_valido"] == null) {
                    $vucem = '<a style="cursor:pointer;" onclick="sendSat(' . $item["id"] . ')"><img src="/images/icons/sat_logo_notvalidated.png" title="Validar ante el SAT." /></a>';
                } elseif ($item["tipo_archivo"] == 16 && $item["cfdi_valido"] == 1) {
                    $vucem = '<img src="/images/icons/sat_logo.png" title="CDFi Válido." />';
                } elseif ($item["tipo_archivo"] == 16 && $item["cfdi_valido"] == 2) {
                    $vucem = '<img src="/images/icons/sat_logo_cancelled.png" title="CDFi Cancelado." />';
                } elseif ($item["tipo_archivo"] == 16 && $item["cfdi_valido"] == 3) {
                    $vucem = '<img src="/images/icons/sat_logo_notvalid.png" title="CDFi No Válido." />';
                } else {
                    $vucem = '&nbsp;';
                }
                $html .= '<tr>'
                        . '<td>' . $vucem . '</td>'
                        . "<td><a href=\"/archivo/index/download-file?id={$item["id"]}\">{$item["nom_archivo"]}</a></td>"
                        . "<td><div id=\"edit_{$item["id"]}\">" . wordwrap($item["nombre"], 45, '<br />') . "</div></td>"
                        . "<td>" . $item["uuid"] . "</td>"
                        . "<td>" . date('d/m/Y H:i a', strtotime($item["creado"])) . "</td>"
                        . "<td>" . $item["usuario"] . "</td>"
                        . "<td>" . $filesize . " Mb</td>"
                        . '<td>'
                        . '<a class="openfile" href="/archivo/index/load-file-repo?id=' . $item["id"] . '"><img src="/images/icons/open-file.png" /></a>&nbsp;'
                        . '<div id="icon_' . $item["id"] . '" style="display:inline-block;"><a style="cursor:pointer;" onclick="editFile(' . $item["id"] . ')"><img src="/images/icons/edit.png" /></a></div>&nbsp;'
                        . '<a style="cursor:pointer;" onclick="deleteFile(' . $item["id"] . ')"><img src="/images/icons/trash.png" /></a>&nbsp;'
                        . '</td>'
                        . '</tr>';
            }
            $html . '</tbody>'
                    . '</table>';
            echo $html;
        } else {
            echo "<h4>La poliza aun no cuenta con archivos. </h4><br>";
        }
    }

    public function verifyInvoiceAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw new Zend_Controller_Request_Exception('Not an AJAX request detected');
        }
        $data = $this->_request->getParams();
        $repo = new Archivo_Model_RepositorioContaMapper();
        if (isset($data["id"])) {
            $factura = $repo->obtenerCdfi($data["id"]);
            if (!empty($factura)) {
                if (isset($factura["version"]) && $factura["version"] == 3.2) {
                    $sat = new SAT_Facturas();
                    $xml = $sat->solicitudValidarCDFILinux($factura["emisor_rfc"], $factura["receptor_rfc"], $factura["total"], $factura["uuid"]);
                    $validar = $sat->satValidarCDFi($xml);
                    if (isset($validar) && $validar != '') {
                        $respuesta = $sat->respuestaValidacion($validar);
                        if ($respuesta != null) {
                            $repo->actualizarFolio($data["id"], $respuesta);
                            echo Zend_Json_Encoder::encode(array('success' => true));
                            return true;
                        }
                        $this->_helper->json(array('success' => true));
                    }
                }
            } else {
                $this->_helper->json(array('success' => false));
            }
        } else {
            $this->_helper->json(array('success' => false));
        }
    }

    public function newFileUploadAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_arch = NULL ? $this->_arch = new Zend_Session_Namespace('') : $this->_arch = new Zend_Session_Namespace('Navigation');
        if (isset($this->_arch->poliza)) {
            if (!file_exists('/home/samba-share/repositorio' . DIRECTORY_SEPARATOR . $this->_arch->poliza)) {
                mkdir('/home/samba-share/repositorio' . DIRECTORY_SEPARATOR . $this->_arch->poliza);
            }
            $folder = '/home/samba-share/repositorio' . DIRECTORY_SEPARATOR . $this->_arch->poliza;
            $request = $this->getRequest();
            if ($request->isPost()) {
                $adapter = new Zend_File_Transfer_Adapter_Http();
                $adapter->setDestination($folder)
                        ->addValidator('Extension', false, 'pdf,xml');
                if ($adapter->isValid()) {
                    $info = $adapter->getFileInfo();
                    if (!$adapter->receive()) {
                        return true;
                    }
                    $misc = new OAQ_Misc();
                    if (!preg_match('/.xml$/i', $info["file"]["name"])) {
                        $noExtension = $misc->formatURL(substr($info["file"]["name"], 0, -4));
                        $newFile = $info["file"]["destination"] . DIRECTORY_SEPARATOR . $noExtension . '.pdf';
                        if (!rename($info["file"]["destination"] . DIRECTORY_SEPARATOR . $info["file"]["name"], $newFile)) {
                            return true;
                        }
                        $repo = new Archivo_Model_RepositorioContaMapper();
                        $added = $repo->agregarArchivo(99, $this->_arch->poliza, $this->_arch->tipo, $newFile, $this->_session->username);
                        if ($added === true) {
                            echo Zend_Json_Encoder::encode(array('success' => true));
                            return true;
                        }
                    } elseif (preg_match('/.xml$/i', $info["file"]["name"])) {
                        $sat = new SAT_Facturas();
                        $noExtension = $misc->formatURL(substr($info["file"]["name"], 0, -4));
                        $newFile = $info["file"]["destination"] . DIRECTORY_SEPARATOR . $noExtension . '.xml';
                        if (!rename($info["file"]["destination"] . DIRECTORY_SEPARATOR . $info["file"]["name"], $newFile)) {
                            return true;
                        }
                        $array = $sat->satToArray(file_get_contents($newFile));
                        if (isset($array['@attributes']['cfdi'])) {
                            $emisor = $sat->obtenerGenerales($array['Emisor']);
                            $receptor = $sat->obtenerGenerales($array['Receptor']);
                            $complemento = $sat->obtenerComplemento($array["Complemento"]);
                            $datos = $sat->obtenerDatosFactura($emisor["rfc"], $array["@attributes"]);
                            $repo = new Archivo_Model_RepositorioContaMapper();
                            $added = $repo->agregarArchivo(16, $this->_arch->poliza, $this->_arch->tipo, $newFile, $this->_session->username, $complemento["uuid"], $datos["folio"], $datos["fecha"], $emisor["rfc"], $emisor["razonSocial"], $receptor["rfc"], $receptor["razonSocial"], $datos["version"], $datos["total"]);
                            if ($added === true) {
                                echo Zend_Json_Encoder::encode(array('success' => true));
                                return true;
                            }
                        }
                    }
                } else {
                    echo Zend_Json_Encoder::encode(array('success' => false));
                    return true;
                }
            }
        } else {
            
        }
        return false;
    }

    public function validarPolizaAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();
        if ($request->isPost()) {
            $mapper = new Administracion_Model_RepositorioContaMapper();
            $table = new Administracion_Model_Table_RepositorioConta($request->getPost());            
            $directory = $this->_createDir("/home/samba-share/fiscal");
            if (!file_exists($directory)) {
                mkdir($directory, 0777, true);
            }
            $upload = new Zend_File_Transfer_Adapter_Http();
            $upload->addValidator("Count", false, array("min" => 1, "max" => 1))
                    ->addValidator("Size", false, array('min' => '1kB', 'max' => '6MB'))
                    ->addValidator("Extension", false, array("extension" => "pdf", "case" => false));
            $upload->setDestination($directory);
            $files = $upload->getFileInfo();
            foreach ($files as $fieldname => $fileinfo) {
                if (($upload->isUploaded($fieldname)) && ($upload->isValid($fieldname))) {
                    $ext = strtolower(pathinfo($fileinfo['name'], PATHINFO_EXTENSION));
                    $sha = sha1_file($fileinfo['tmp_name']);
                    $filename = $sha . '.' . $ext;
                    $upload->addFilter('Rename', $filename, $fieldname);

                    $upload->receive($fieldname);
                    if (file_exists($directory . DIRECTORY_SEPARATOR . $filename)) {
                        $table->setNombreArchivo($fileinfo['name']);
                        $table->setHash($sha);
                        $table->setUbicacion($directory . DIRECTORY_SEPARATOR . $filename);
                        $table->setUsuario($this->_session->username);
                    }
                    if (null == ($mapper->find($table))) {
                        $mapper->save($table);
                    }
                    if(null !== ($table->getId())) {
                        echo Zend_Json_Encoder::encode(array('success' => true));
                        return true;
                    }
                }
            }
            
        }
    }

    /**
     * 
     * @param string $baseDir
     * @throws Exception
     */
    public function _createDir($baseDir) {
        if (isset($baseDir)) {
            if (is_readable($baseDir)) {
                $newDir = $baseDir . DIRECTORY_SEPARATOR . date('Y') . DIRECTORY_SEPARATOR . date('m') . DIRECTORY_SEPARATOR . str_pad(date('d'), 2, '0', STR_PAD_LEFT);
                if (!file_exists($newDir)) {
                    mkdir($newDir, 0777, true);
                    if (file_exists($newDir)) {
                        return $newDir;
                    }
                } else {
                    return $newDir;
                }
            } else {
                throw new Exception("Craps! Files directory is not readable.");
            }
        } else {
            throw new Exception("Craps! Files directory not found.");
        }
    }

    public function tipoArchivoPolizaAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $request = $this->getRequest();
        if ($request->isPost()) {
            $filters = array(
                '*' => array('StringTrim', 'StripTags'),
                'id' => 'Digits',
            );
            $validators = array(
                'id' => array('Digits'),
            );
            $input = new Zend_Filter_Input($filters, $validators, $request->getPost());
            if ($input->isValid()) {
                $mapper = new Administracion_Model_DocumentosArchivosMapper();
                if (in_array($input->id, array(1, 2, 4))) {
                    $result = $mapper->archivos(1);
                } else {
                    $result = $mapper->archivos($input->id);
                }
                if (isset($result) && !empty($result)) {
                    foreach ($result as $item) {
                        $data[$item["id"]] = mb_strtoupper($item["tipoArchivo"], 'UTF-8');
                    }
                    echo Zend_Json::encode(array(
                        'success' => true,
                        'values' => $data,
                    ));
                    return true;
                }
            }
        }
    }
    
    public function descargarArchivoAction() {
        try {
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => "Digits",
            );
            $v = array(
                "id" => array("Alnum", array("stringLength", array("min" => 1, "max" => 9999999))),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            $mapper = new Administracion_Model_RepositorioContaMapper();
            if ($input->isValid("id")) {
                $row = new Administracion_Model_Table_RepositorioConta(array("id" => $input->id));
                $mapper->buscarId($row);
                if (null !== ($row->getNombreArchivo())) {
                    if (file_exists($row->getUbicacion() . DIRECTORY_SEPARATOR . $row->getNombreArchivo())) {
                        header("Cache-Control: public");
                        header("Content-Description: File Transfer");
                        header("Content-Disposition: attachment; filename=" . $row->getNombreArchivo() . "");
                        header("Content-length: " . filesize($row->getUbicacion() . DIRECTORY_SEPARATOR . $row->getNombreArchivo()));
                        header("Content-Transfer-Encoding: binary");
                        header("Content-Type: binary/octet-stream");
                        readfile($row->getUbicacion() . DIRECTORY_SEPARATOR . $row->getNombreArchivo());
                    } else {
                        throw new Exception("File doesn't exists!");
                    }
                } else {
                    throw new Exception("DB Error!");
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function descargarArchivoClienteAction() {
        try {
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => "Digits",
            );
            $v = array(
                "id" => array("Alnum", array("stringLength", array("min" => 1, "max" => 9999999))),
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id")) {
                $mapper = new Administracion_Model_RepositorioContaMapper();
                $arr = $mapper->buscar($input->id);
                if(count($arr)) {
                    if (file_exists($arr["ubicacion"] . DIRECTORY_SEPARATOR . $arr["nombreArchivo"])) {
                        header("Cache-Control: public");
                        header("Content-Description: File Transfer");
                        header("Content-Disposition: attachment; filename=" . $arr["nombreArchivo"] . "");
                        header("Content-length: " . filesize($arr["ubicacion"] . DIRECTORY_SEPARATOR . $arr["nombreArchivo"]));
                        header("Content-Transfer-Encoding: binary");
                        header("Content-Type: binary/octet-stream");
                        readfile($arr["ubicacion"] . DIRECTORY_SEPARATOR . $arr["nombreArchivo"]);
                    }
                } else {
                    throw new Exception("File does not exists!");
                }
            }
        } catch (Exception $ex) {
            $this->_helper->json(array("success" => false, "message" => $ex->getMessage()));
        }
    }
    
    public function xmlPolizasAction() {
        try {
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "id" => "Digits",
                "poliza" => "Digits",
            );
            $v = array(
                "id" => array("NotEmpty", new Zend_Validate_Int(), array("stringLength", array("min" => 1, "max" => 9999999))),
                "poliza" => array("NotEmpty", new Zend_Validate_Int()),
                "cuenta" => "NotEmpty",
            );
            $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
            if ($input->isValid("id") && $input->isValid("poliza") && $input->isValid("cuenta")) {
                $misc = new OAQ_Misc();
                $db = $misc->sitawinTrafico(3589, 640);
                if (isset($db)) {
                    $arr = $db->pedimentoPagados("2017-05-19", "2017-05-19");
                }
                $domtree = new DOMDocument("1.0", "UTF-8");
                $polizas = $domtree->createElement("Polizas");
                $xmlRoot = $domtree->appendChild($polizas);                
                if (isset($arr) && !empty($arr)) {
                    foreach ($arr as $item) {
                        $poliza = $domtree->createElement("Poliza");
                        $poliza->appendChild($domtree->createElement("PolizaID", $input->poliza));
                        $poliza->appendChild($domtree->createElement("Fecha"));
                        $poliza->appendChild($domtree->createElement("Encabezado"));
                        $xmlRoot->appendChild($poliza);
                        $detalle = $domtree->createElement("DetallePoliza");
                        $detalle->appendChild($domtree->createElement("CuentaID", $input->cuenta));
                        $detalle->appendChild($domtree->createElement("Referencia", $item["referencia"]));
                        $detalle->appendChild($domtree->createElement("Factura"));
                        $detalle->appendChild($domtree->createElement("FacturaProv"));
                        $detalle->appendChild($domtree->createElement("Descripcion"));
                        $detalle->appendChild($domtree->createElement("Cargo", number_format(150.0, 2)));
                        $detalle->appendChild($domtree->createElement("Abono"));
                        $detalle->appendChild($domtree->createElement("UUID"));
                        $poliza->appendChild($detalle);
                    }
                }
                header("Content-type: text/xml");
                echo $domtree->saveXML();
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

}
