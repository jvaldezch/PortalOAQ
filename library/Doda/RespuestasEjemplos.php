<?php

/**
 * Description of Vucem_Xml
 * 
 * Esta clase conforma arreglos en archivos XML requeridos por la Ventanilla Única de Comercio Exterior (VUCEM)
 *
 * @author Jaime E. Valdez jvaldezch at gmail
 */
class Doda_RespuestasEjemplos {

    protected $debug = false;

    function __construct() {
    }
    
    public function ejemploRespuesta($value) {
        return $this->_getEjemplo($value);
    }
    
    protected function _getEjemplo($value) {        
        $array[1] = '<NS1:Envelope xmlns:NS1="http://schemas.xmlsoap.org/soap/envelope/"><NS1:Body><NS1:Fault><faultcode>E003</faultcode><faultstring>Error</faultstring><detail>EL DOCUMENTO NO ES VALIDO</detail></NS1:Fault></NS1:Body></NS1:Envelope>';
        $array[2] = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
   <soapenv:Body>
      <NS1:altaDodaResponse xmlns:NS1="http://impl.service.qrws.ce.siat.sat.gob.mx/siatbus/matce">
         <doda>
            <respuesta>
               <fecha>19-09-2017 10:52:20</fecha>
               <mensajes>
                  <mensaje>Su solicitud ha sido recibida satisfactoriamente</mensaje>
               </mensajes>
               <ticket>19092017105220456</ticket>
            </respuesta>
         </doda>
      </NS1:altaDodaResponse>
   </soapenv:Body>
</soapenv:Envelope>';
        $array[3] = '<NS1:Envelope xmlns:NS1="http://schemas.xmlsoap.org/soap/envelope/">
   <NS1:Body>
      <NS1:Fault>
         <faultcode>E001</faultcode>
         <faultstring>Error de Sintaxis en la petición</faultstring>
         <detail>Error: [24:42] cvc-pattern-valid: Value \'6\' is not facet-valid with respect to pattern \'(1|2)\' for type \'tipoOperacion\'.</detail>
      </NS1:Fault>
   </NS1:Body>
</NS1:Envelope>';
        return $array[$value];
    }

}
