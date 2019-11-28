<?php

/**
 * Description of Vucem_Respuestas
 * 
 * Esta clase analiza los XML de respuesta de VUCEM y los convierte a un arreglo
 * que puede traer false o true dependiendo del tipo de respuesta.
 *
 * @author Jaime E. Valdez jvaldezch at gmail
 */
class OAQ_RespuestasEjemplos {

    protected $debug = false;

    function __construct() {
    }
    
    /**
     * 
     * @param int $value
     * @return type
     */
    public function ejemploRespuesta($value) {
        return $this->_getEjemplo($value);
    }

    /**
     * 
     * @param int $value
     * @return string
     */
    protected function _getEjemplo($value) {        
        $array[1] = '<?xml version="1.0" encoding="UTF-8"?>
<S:Envelope xmlns:S="http://schemas.xmlsoap.org/soap/envelope/">
  <S:Header>
    <wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" S:mustUnderstand="1">
      <wsu:Timestamp xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
        <wsu:Created>2015-11-10T21:18:34Z</wsu:Created>
        <wsu:Expires>2015-11-10T21:19:34Z</wsu:Expires>
      </wsu:Timestamp>
    </wsse:Security>
  </S:Header>
  <S:Body>
    <ConsultarEdocumentResponse xmlns="http://www.ventanillaunica.gob.mx/ConsultarEdocument/" xmlns:ns2="http://www.ventanillaunica.gob.mx/cove/ws/oxml/">
      <response>
        <mensaje>El Cove o Adenda no existe, no está firmado o no cuenta con la autorización para consultarlo</mensaje>
        <contieneError>false</contieneError>
        <resultadoBusqueda/>
      </response>
    </ConsultarEdocumentResponse>
  </S:Body>
</S:Envelope>';
        $array[2] = '<?xml version="1.0" encoding="UTF-8"?>
<S:Envelope xmlns:S="http://schemas.xmlsoap.org/soap/envelope/">
  <S:Header>
    <wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" S:mustUnderstand="1">
      <wsu:Timestamp xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
        <wsu:Created>2015-11-10T18:01:23Z</wsu:Created>
        <wsu:Expires>2015-11-10T18:02:23Z</wsu:Expires>
      </wsu:Timestamp>
    </wsse:Security>
  </S:Header>
  <S:Body>
    <solicitarRecibirCoveServicioResponse xmlns="http://www.ventanillaunica.gob.mx/cove/ws/oxml/">
      <mensajeInformativo>No se recibieron comprobantes</mensajeInformativo>
    </solicitarRecibirCoveServicioResponse>
  </S:Body>
</S:Envelope>';
        $array[3] = '<?xml version="1.0" encoding="UTF-8"?>
<env:Envelope xmlns:env="http://schemas.xmlsoap.org/soap/envelope/">
  <env:Body>
    <env:Fault xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
      <faultcode>wsse:FailedAuthentication</faultcode>
      <faultstring>Failed to assert identity with UsernameToken.</faultstring>
    </env:Fault>
  </env:Body>
</env:Envelope>';
        $array[4] = '<?xml version="1.0" encoding="UTF-8"?>
<S:Envelope xmlns:S="http://schemas.xmlsoap.org/soap/envelope/">
  <S:Header>
    <wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" S:mustUnderstand="1">
      <wsu:Timestamp xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
        <wsu:Created>2015-11-10T18:29:13Z</wsu:Created>
        <wsu:Expires>2015-11-10T18:30:13Z</wsu:Expires>
      </wsu:Timestamp>
    </wsse:Security>
  </S:Header>
  <S:Body>
    <ConsultarEdocumentResponse xmlns="http://www.ventanillaunica.gob.mx/ConsultarEdocument/" xmlns:ns2="http://www.ventanillaunica.gob.mx/cove/ws/oxml/">
      <response>
        <mensaje>Existen errores en los parámetros de entrada</mensaje>
        <contieneError>true</contieneError>
        <errores>
          <error>Firma Electrónica : Firma inválida</error>
        </errores>
      </response>
    </ConsultarEdocumentResponse>
  </S:Body>
</S:Envelope>';
        $array[5] = '<?xml version="1.0" encoding="UTF-8"?>
<S:Envelope xmlns:S="http://schemas.xmlsoap.org/soap/envelope/">
  <S:Body>
    <S:Fault xmlns:ns4="http://www.w3.org/2003/05/soap-envelope">
      <faultcode>S:Server</faultcode>
      <faultstring>javax.xml.soap.SOAPException: com.ctc.wstx.exc.WstxParsingException: Undeclared namespace prefix "res"
at [row,col {unknown-source}]: [15,27]</faultstring>
    </S:Fault>
  </S:Body>
</S:Envelope>';
        $array[5] = '<?xml version="1.0" encoding="UTF-8"?>
<S:Envelope xmlns:S="http://schemas.xmlsoap.org/soap/envelope/">
  <S:Header>
    <wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" S:mustUnderstand="1">
      <wsu:Timestamp xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
        <wsu:Created>2015-11-10T18:37:13Z</wsu:Created>
        <wsu:Expires>2015-11-10T18:38:13Z</wsu:Expires>
      </wsu:Timestamp>
    </wsse:Security>
  </S:Header>
  <S:Body>
    <ConsultarEdocumentResponse xmlns="http://www.ventanillaunica.gob.mx/ConsultarEdocument/" xmlns:ns2="http://www.ventanillaunica.gob.mx/cove/ws/oxml/">
      <response>
        <mensaje>Existen errores en los parámetros de entrada</mensaje>
        <contieneError>true</contieneError>
        <errores>
          <error>Cadena original : La cadena original es inválida</error>
        </errores>
      </response>
    </ConsultarEdocumentResponse>
  </S:Body>
</S:Envelope>';
        $array[6] = '<?xml version="1.0" encoding="UTF-8"?>
<S:Envelope xmlns:S="http://schemas.xmlsoap.org/soap/envelope/">
  <S:Header>
    <wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" S:mustUnderstand="1">
      <wsu:Timestamp xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
        <wsu:Created>2015-11-10T18:39:55Z</wsu:Created>
        <wsu:Expires>2015-11-10T18:40:55Z</wsu:Expires>
      </wsu:Timestamp>
    </wsse:Security>
  </S:Header>
  <S:Body>
    <ConsultarEdocumentResponse xmlns="http://www.ventanillaunica.gob.mx/ConsultarEdocument/" xmlns:ns2="http://www.ventanillaunica.gob.mx/cove/ws/oxml/">
      <response>
        <mensaje>Existen errores en los parámetros de entrada</mensaje>
        <contieneError>true</contieneError>
        <errores>
          <error>Firma Electrónica : La firma de la firma electronica es obligatoria</error>
          <error>Firma Electrónica : Firma inválida</error>
        </errores>
      </response>
    </ConsultarEdocumentResponse>
  </S:Body>
</S:Envelope>';
        $array[7] = '<?xml version="1.0" encoding="UTF-8"?>
<S:Envelope xmlns:S="http://schemas.xmlsoap.org/soap/envelope/">
  <S:Header>
    <wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" S:mustUnderstand="1">
      <wsu:Timestamp xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
        <wsu:Created>2015-11-10T22:33:59Z</wsu:Created>
        <wsu:Expires>2015-11-10T22:34:59Z</wsu:Expires>
      </wsu:Timestamp>
    </wsse:Security>
  </S:Header>
  <S:Body>
    <solicitarConsultarRespuestaCoveServicioResponse xmlns="http://www.ventanillaunica.gob.mx/cove/ws/oxml/">
      <leyenda>No existe el número de operación para este usuario.</leyenda>
    </solicitarConsultarRespuestaCoveServicioResponse>
  </S:Body>
</S:Envelope>';
        $array[8] = '<?xml version="1.0" encoding="UTF-8"?>
<S:Envelope xmlns:S="http://schemas.xmlsoap.org/soap/envelope/">
  <S:Header>
    <wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" S:mustUnderstand="1">
      <wsu:Timestamp xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
        <wsu:Created>2015-11-10T22:36:21Z</wsu:Created>
        <wsu:Expires>2015-11-10T22:37:21Z</wsu:Expires>
      </wsu:Timestamp>
    </wsse:Security>
  </S:Header>
  <S:Body>
    <solicitarConsultarRespuestaCoveServicioResponse xmlns="http://www.ventanillaunica.gob.mx/cove/ws/oxml/">
      <numeroOperacion>65108518</numeroOperacion>
      <horaRecepcion>2015-11-10T16:30:40.000-06:00</horaRecepcion>
      <respuestasOperaciones>
        <numeroFacturaORelacionFacturas>88583</numeroFacturaORelacionFacturas>
        <contieneError>false</contieneError>
        <eDocument>COVE151A38OD3</eDocument>
      </respuestasOperaciones>
      <leyenda>Tiene 240 d&amp;iacute;as a partir de esta fecha para utilizar su Acuse de Valor, si en ese tiempo no es utilizado, ser&amp;aacute; dado de baja del sistema.</leyenda>
    </solicitarConsultarRespuestaCoveServicioResponse>
  </S:Body>
</S:Envelope>';
        $array[9] = '<?xml version="1.0" encoding="UTF-8"?>
<S:Envelope xmlns:S="http://schemas.xmlsoap.org/soap/envelope/">
  <S:Header>
    <wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" S:mustUnderstand="1">
      <wsu:Timestamp xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
        <wsu:Created>2015-11-10T22:38:06Z</wsu:Created>
        <wsu:Expires>2015-11-10T22:39:06Z</wsu:Expires>
      </wsu:Timestamp>
    </wsse:Security>
  </S:Header>
  <S:Body>
    <solicitarConsultarRespuestaCoveServicioResponse xmlns="http://www.ventanillaunica.gob.mx/cove/ws/oxml/">
      <numeroOperacion>65081350</numeroOperacion>
      <horaRecepcion>2015-11-10T12:43:17.000-06:00</horaRecepcion>
      <respuestasOperaciones>
        <numeroFacturaORelacionFacturas>1101628</numeroFacturaORelacionFacturas>
        <contieneError>true</contieneError>
        <errores>
          <mensaje>Cadena original : La cadena original es inválida</mensaje>
          <mensaje>Tipo de operación : El tipo de operacion es inválido.</mensaje>
          <mensaje>Datos del Destinatario : El comprobante no tiene datos del Destinatario</mensaje>
          <mensaje>Datos del Proveedor : El comprobante no tiene datos del Emisor</mensaje>
        </errores>
      </respuestasOperaciones>
      <leyenda>Tiene 240 d&amp;iacute;as a partir de esta fecha para utilizar su Acuse de Valor, si en ese tiempo no es utilizado, ser&amp;aacute; dado de baja del sistema.</leyenda>
    </solicitarConsultarRespuestaCoveServicioResponse>
  </S:Body>
</S:Envelope>';
        $array[10] = '<?xml version="1.0" encoding="UTF-8"?>
<S:Envelope xmlns:S="http://schemas.xmlsoap.org/soap/envelope/">
  <S:Header>
    <wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" S:mustUnderstand="1">
      <wsu:Timestamp xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
        <wsu:Created>2015-11-10T22:39:58Z</wsu:Created>
        <wsu:Expires>2015-11-10T22:40:58Z</wsu:Expires>
      </wsu:Timestamp>
    </wsse:Security>
  </S:Header>
  <S:Body>
    <solicitarConsultarRespuestaCoveServicioResponse xmlns="http://www.ventanillaunica.gob.mx/cove/ws/oxml/">
      <numeroOperacion>65109766</numeroOperacion>
      <horaRecepcion>2015-11-10T16:39:47.000-06:00</horaRecepcion>
      <respuestasOperaciones>
        <numeroFacturaORelacionFacturas>52108</numeroFacturaORelacionFacturas>
        <contieneError>true</contieneError>
        <errores>
          <mensaje>Cadena original : La cadena original es inválida</mensaje>
          <mensaje>País : no existe el pais en el catálogo</mensaje>
        </errores>
      </respuestasOperaciones>
      <leyenda>La operación fue procesada con errores.</leyenda>
    </solicitarConsultarRespuestaCoveServicioResponse>
  </S:Body>
</S:Envelope>';
        $array[11] = '<?xml version="1.0" encoding="UTF-8"?>
<S:Envelope xmlns:S="http://schemas.xmlsoap.org/soap/envelope/">
  <S:Header>
    <wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" S:mustUnderstand="1">
      <wsu:Timestamp xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
        <wsu:Created>2015-11-10T22:41:35Z</wsu:Created>
        <wsu:Expires>2015-11-10T22:42:35Z</wsu:Expires>
      </wsu:Timestamp>
    </wsse:Security>
  </S:Header>
  <S:Body>
    <solicitarConsultarRespuestaCoveServicioResponse xmlns="http://www.ventanillaunica.gob.mx/cove/ws/oxml/">
      <numeroOperacion>65109953</numeroOperacion>
      <horaRecepcion>2015-11-10T16:41:22.000-06:00</horaRecepcion>
      <respuestasOperaciones>
        <numeroFacturaORelacionFacturas>15109149-1</numeroFacturaORelacionFacturas>
        <contieneError>false</contieneError>
        <eDocument>COVE151A10RF7</eDocument>
        <numeroAdenda>ADEN152881830</numeroAdenda>
      </respuestasOperaciones>
    </solicitarConsultarRespuestaCoveServicioResponse>
  </S:Body>
</S:Envelope>';
        $array[12] = '--uuid:7c45c6fd-fddc-46a9-870e-667ab2e85164
Content-Id: <rootpart*7c45c6fd-fddc-46a9-870e-667ab2e85164@example.jaxws.sun.com>
Content-Type: application/xop+xml;charset=utf-8;type="text/xml"
Content-Transfer-Encoding: binary

<?xml version=\'1.0\' encoding=\'UTF-8\'?><S:Envelope xmlns:S="http://schemas.xmlsoap.org/soap/envelope/"><S:Header><wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" S:mustUnderstand="1"><wsu:Timestamp xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd"><wsu:Created>2015-11-10T23:22:23Z</wsu:Created><wsu:Expires>2015-11-10T23:23:23Z</wsu:Expires></wsu:Timestamp></wsse:Security></S:Header><S:Body><ns3:consultaDigitalizarDocumentoServiceResponse xmlns="http://www.ventanillaunica.gob.mx/common/ws/oxml/respuesta" xmlns:ns2="http://www.ventanillaunica.gob.mx/aga/digitalizar/ws/oxml/DigitalizarDocumento" xmlns:ns3="http://www.ventanillaunica.gob.mx/aga/digitalizar/ws/oxml/"><ns2:respuestaBase><tieneError>true</tieneError><error><mensaje>La cadena original es inválida</mensaje></error></ns2:respuestaBase></ns3:consultaDigitalizarDocumentoServiceResponse></S:Body></S:Envelope>
--uuid:7c45c6fd-fddc-46a9-870e-667ab2e85164--'; // tiene error la cadena
        $array[13] = '--uuid:098797c9-e792-470b-abd7-9a31bd8451b4
Content-Id: <rootpart*098797c9-e792-470b-abd7-9a31bd8451b4@example.jaxws.sun.com>
Content-Type: application/xop+xml;charset=utf-8;type="text/xml"
Content-Transfer-Encoding: binary

<?xml version=\'1.0\' encoding=\'UTF-8\'?><S:Envelope xmlns:S="http://schemas.xmlsoap.org/soap/envelope/"><S:Header><wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" S:mustUnderstand="1"><wsu:Timestamp xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd"><wsu:Created>2015-11-10T23:40:12Z</wsu:Created><wsu:Expires>2015-11-10T23:41:12Z</wsu:Expires></wsu:Timestamp></wsse:Security></S:Header><S:Body><ns3:consultaDigitalizarDocumentoServiceResponse xmlns="http://www.ventanillaunica.gob.mx/common/ws/oxml/respuesta" xmlns:ns2="http://www.ventanillaunica.gob.mx/aga/digitalizar/ws/oxml/DigitalizarDocumento" xmlns:ns3="http://www.ventanillaunica.gob.mx/aga/digitalizar/ws/oxml/"><ns2:numeroDeTramite>0100700100020150012236617</ns2:numeroDeTramite><ns2:eDocument>0192150697E06</ns2:eDocument><ns2:cadenaOriginal>|0100700100020150012236617|0192150697E06|</ns2:cadenaOriginal><ns2:respuestaBase><tieneError>false</tieneError></ns2:respuestaBase></ns3:consultaDigitalizarDocumentoServiceResponse></S:Body></S:Envelope>
--uuid:098797c9-e792-470b-abd7-9a31bd8451b4--'; // tiene edoc
        $array[14] = '--uuid:8b9b2726-734f-4c56-86b2-b7435b502a6c
Content-Id: <rootpart*8b9b2726-734f-4c56-86b2-b7435b502a6c@example.jaxws.sun.com>
Content-Type: application/xop+xml;charset=utf-8;type="text/xml"
Content-Transfer-Encoding: binary

<?xml version=\'1.0\' encoding=\'UTF-8\'?><S:Envelope xmlns:S="http://schemas.xmlsoap.org/soap/envelope/"><S:Header><wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" S:mustUnderstand="1"><wsu:Timestamp xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd"><wsu:Created>2015-11-10T23:45:52Z</wsu:Created><wsu:Expires>2015-11-10T23:46:52Z</wsu:Expires></wsu:Timestamp></wsse:Security></S:Header><S:Body><ns3:consultaDigitalizarDocumentoServiceResponse xmlns="http://www.ventanillaunica.gob.mx/common/ws/oxml/respuesta" xmlns:ns2="http://www.ventanillaunica.gob.mx/aga/digitalizar/ws/oxml/DigitalizarDocumento" xmlns:ns3="http://www.ventanillaunica.gob.mx/aga/digitalizar/ws/oxml/"><ns2:respuestaBase><tieneError>true</tieneError><error><mensaje>El RFC del solicitante ACM080307L15 no tiene privilegios para consultar el resultado de la digitalización del documento para el número de operación 37650528</mensaje></error></ns2:respuestaBase></ns3:consultaDigitalizarDocumentoServiceResponse></S:Body></S:Envelope>
--uuid:8b9b2726-734f-4c56-86b2-b7435b502a6c--'; // el rfc no tiene privilegios de consulta
        $array[15] = '--uuid:39bdf130-c487-4fc0-b08f-5db2acc5992e
Content-Id: <rootpart*39bdf130-c487-4fc0-b08f-5db2acc5992e@example.jaxws.sun.com>
Content-Type: application/xop+xml;charset=utf-8;type="text/xml"
Content-Transfer-Encoding: binary

<?xml version=\'1.0\' encoding=\'UTF-8\'?><S:Envelope xmlns:S="http://schemas.xmlsoap.org/soap/envelope/"><S:Header><wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" S:mustUnderstand="1"><wsu:Timestamp xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd"><wsu:Created>2015-11-10T23:52:55Z</wsu:Created><wsu:Expires>2015-11-10T23:53:55Z</wsu:Expires></wsu:Timestamp></wsse:Security></S:Header><S:Body><ns3:consultaDigitalizarDocumentoServiceResponse xmlns="http://www.ventanillaunica.gob.mx/common/ws/oxml/respuesta" xmlns:ns2="http://www.ventanillaunica.gob.mx/aga/digitalizar/ws/oxml/DigitalizarDocumento" xmlns:ns3="http://www.ventanillaunica.gob.mx/aga/digitalizar/ws/oxml/"><ns2:respuestaBase><tieneError>true</tieneError><error><mensaje>El archivo no cumple con las especificaciones de digitalización.</mensaje></error></ns2:respuestaBase></ns3:consultaDigitalizarDocumentoServiceResponse></S:Body></S:Envelope>
--uuid:39bdf130-c487-4fc0-b08f-5db2acc5992e--';
        $array[16] = '<S:Envelope xmlns:S="http://schemas.xmlsoap.org/soap/envelope/">
   <S:Header>
      <wsse:Security S:mustUnderstand="1" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
         <wsu:Timestamp xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
            <wsu:Created>2015-11-10T23:55:00Z</wsu:Created>
            <wsu:Expires>2015-11-10T23:56:00Z</wsu:Expires>
         </wsu:Timestamp>
      </wsse:Security>
   </S:Header>
   <S:Body>
      <solicitarRecibirCoveServicioResponse xmlns="http://www.ventanillaunica.gob.mx/cove/ws/oxml/">
         <numeroDeOperacion>65120127</numeroDeOperacion>
         <horaRecepcion>2015-11-10T17:55:00.623-06:00</horaRecepcion>
         <mensajeInformativo>La recepción del COVE fue exitosa.</mensajeInformativo>
      </solicitarRecibirCoveServicioResponse>
   </S:Body>
</S:Envelope>'; // recepcion de cove exitosa
        $array[17] = '<S:Envelope xmlns:S="http://schemas.xmlsoap.org/soap/envelope/">
   <S:Header>
      <wsse:Security S:mustUnderstand="1" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
         <wsu:Timestamp xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
            <wsu:Created>2015-11-11T00:01:42Z</wsu:Created>
            <wsu:Expires>2015-11-11T00:02:42Z</wsu:Expires>
         </wsu:Timestamp>
      </wsse:Security>
   </S:Header>
   <S:Body>
      <ns3:registroDigitalizarDocumentoServiceResponse xmlns="http://www.ventanillaunica.gob.mx/common/ws/oxml/respuesta" xmlns:ns2="http://www.ventanillaunica.gob.mx/aga/digitalizar/ws/oxml/DigitalizarDocumento" xmlns:ns3="http://www.ventanillaunica.gob.mx/aga/digitalizar/ws/oxml/">
         <ns2:respuestaBase>
            <tieneError>false</tieneError>
         </ns2:respuestaBase>
         <ns2:acuse>
            <ns2:numeroOperacion>37694508</ns2:numeroOperacion>
            <ns2:horaRecepcion>2015-11-10T18:01:42.495-06:00</ns2:horaRecepcion>
            <ns2:mensaje>Su petición se encuentra procesando</ns2:mensaje>
         </ns2:acuse>
      </ns3:registroDigitalizarDocumentoServiceResponse>
   </S:Body>
</S:Envelope>';
        $array[18] = '<?xml version=\'1.0\' encoding=\'UTF-8\'?><S:Envelope xmlns:S="http://schemas.xmlsoap.org/soap/envelope/"><S:Body><ns2:Fault xmlns:ns2="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns3="http://www.w3.org/2003/05/soap-envelope"><faultcode>ns2:Client</faultcode><faultstring>Cannot find dispatch method for {http://www.ventanillaunica.gob.mx/ConsultarEdocument/}ConsultarEdocumentRequest</faultstring></ns2:Fault></S:Body></S:Envelope>';
        $array[19] = '<?xml version="1.0" encoding="UTF-8"?>
<S:Envelope xmlns:S="http://schemas.xmlsoap.org/soap/envelope/">
  <S:Header>
    <wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" S:mustUnderstand="1">
      <wsu:Timestamp xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
        <wsu:Created>2015-11-11T00:40:14Z</wsu:Created>
        <wsu:Expires>2015-11-11T00:41:14Z</wsu:Expires>
      </wsu:Timestamp>
    </wsse:Security>
  </S:Header>
  <S:Body>
    <ConsultarEdocumentResponse xmlns="http://www.ventanillaunica.gob.mx/ConsultarEdocument/" xmlns:ns2="http://www.ventanillaunica.gob.mx/cove/ws/oxml/">
      <response>
        <mensaje>La consulta se realizó exitosamente</mensaje>
        <contieneError>false</contieneError>
        <resultadoBusqueda>
          <cove>
            <eDocument>COVE151A28AD3</eDocument>
            <tipoOperacion>TOCE.IMP</tipoOperacion>
            <numeroFacturaRelacionFacturas>1292</numeroFacturaRelacionFacturas>
            <relacionFacturas>0</relacionFacturas>
            <automotriz>0</automotriz>
            <fechaExpedicion>2015-11-05-06:00</fechaExpedicion>
            <tipoFigura>1</tipoFigura>
            <patentesAduanales>
              <patenteAduanal>3589</patenteAduanal>
            </patentesAduanales>
            <rfcsConsulta>
              <rfcConsulta>OAQ030623UL8</rfcConsulta>
              <rfcConsulta>CIN0309091D3</rfcConsulta>
            </rfcsConsulta>
            <observaciones/>
            <facturas>
              <factura>
                <certificadoOrigen>0</certificadoOrigen>
                <subdivision>0</subdivision>
                <mercancias>
                  <mercancia>
                    <descripcionGenerica>ABRAZADERAS SOPORTE</descripcionGenerica>
                    <claveUnidadMedida>C62_1</claveUnidadMedida>
                    <tipoMoneda>EUR</tipoMoneda>
                    <cantidad>100</cantidad>
                    <valorUnitario>1.88</valorUnitario>
                    <valorTotal>188</valorTotal>
                    <valorDolares>207.7776</valorDolares>
                  </mercancia>
                  <mercancia>
                    <descripcionGenerica>PERNO</descripcionGenerica>
                    <claveUnidadMedida>C62_1</claveUnidadMedida>
                    <tipoMoneda>EUR</tipoMoneda>
                    <cantidad>91</cantidad>
                    <valorUnitario>0.318681</valorUnitario>
                    <valorTotal>29</valorTotal>
                    <valorDolares>32.0508</valorDolares>
                  </mercancia>
                </mercancias>
              </factura>
            </facturas>
            <emisor>
              <tipoIdentificador>0</tipoIdentificador>
              <identificacion>01947980015</identificacion>
              <nombre>RMA S.R.L.</nombre>
              <domicilio>
                <calle>VIA CAVALLO</calle>
                <numeroExterior>18</numeroExterior>
                <colonia>STABILIMENTO VENARIA</colonia>
                <pais>ITA</pais>
                <codigoPostal>10078</codigoPostal>
              </domicilio>
            </emisor>
            <destinatario>
              <tipoIdentificador>1</tipoIdentificador>
              <identificacion>CIN0309091D3</identificacion>
              <nombre>CNH INDUSTRIAL SA DE CV</nombre>
              <domicilio>
                <calle>AVENIDA 5 DE FEBRERO</calle>
                <numeroExterior>2117</numeroExterior>
                <colonia>INDUSTRIAL BENITO JUAREZ</colonia>
                <municipio>QUERETARO</municipio>
                <entidadFederativa>QUERETARO</entidadFederativa>
                <pais>MEX</pais>
                <codigoPostal>76130</codigoPostal>
              </domicilio>
            </destinatario>
          </cove>
        </resultadoBusqueda>
      </response>
    </ConsultarEdocumentResponse>
  </S:Body>
</S:Envelope>';
        $array[20] = '<?xml version=\'1.0\' encoding=\'UTF-8\'?><S:Envelope xmlns:S="http://schemas.xmlsoap.org/soap/envelope/"><S:Header><wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" S:mustUnderstand="1"><wsu:Timestamp xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd"><wsu:Created>2015-11-11T01:02:12Z</wsu:Created><wsu:Expires>2015-11-11T01:03:12Z</wsu:Expires></wsu:Timestamp></wsse:Security></S:Header><S:Body><ConsultarEdocumentResponse xmlns="http://www.ventanillaunica.gob.mx/ConsultarEdocument/" xmlns:ns2="http://www.ventanillaunica.gob.mx/cove/ws/oxml/"><response><mensaje>El Cove o Adenda no existe, no está firmado o no cuenta con la autorización para consultarlo</mensaje><contieneError>false</contieneError><resultadoBusqueda/></response></ConsultarEdocumentResponse></S:Body></S:Envelope>';
        $array[21] = '--uuid:f0872bea-9b66-48a8-b06f-4e76b24ea4fd Content-Id: <rootpart*f0872bea-9b66-48a8-b06f-4e76b24ea4fd@example.jaxws.sun.com> Content-Type: application/xop+xml;charset=utf-8;type="text/xml" Content-Transfer-Encoding: binary <S:Envelope xmlns:S="http://schemas.xmlsoap.org/soap/envelope/"><S:Header><wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" S:mustUnderstand="1"><wsu:Timestamp xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd"><wsu:Created>2015-11-24T23:17:45Z</wsu:Created><wsu:Expires>2015-11-24T23:18:45Z</wsu:Expires></wsu:Timestamp></wsse:Security></S:Header><S:Body><ns3:registroDigitalizarDocumentoServiceResponse xmlns="http://www.ventanillaunica.gob.mx/common/ws/oxml/respuesta" xmlns:ns2="http://www.ventanillaunica.gob.mx/aga/digitalizar/ws/oxml/DigitalizarDocumento" xmlns:ns3="http://www.ventanillaunica.gob.mx/aga/digitalizar/ws/oxml/"><ns2:respuestaBase><tieneError>true</tieneError><error><mensaje>La cadena original es inválida</mensaje></error></ns2:respuestaBase></ns3:registroDigitalizarDocumentoServiceResponse></S:Body></S:Envelope> --uuid:f0872bea-9b66-48a8-b06f-4e76b24ea4fd--';
        $array[22] = '<S:Envelope xmlns:S="http://schemas.xmlsoap.org/soap/envelope/"><S:Header><wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" S:mustUnderstand="1"><wsu:Timestamp xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd"><wsu:Created>2015-11-24T23:17:45Z</wsu:Created><wsu:Expires>2015-11-24T23:18:45Z</wsu:Expires></wsu:Timestamp></wsse:Security></S:Header><S:Body><ns3:registroDigitalizarDocumentoServiceResponse xmlns="http://www.ventanillaunica.gob.mx/common/ws/oxml/respuesta" xmlns:ns2="http://www.ventanillaunica.gob.mx/aga/digitalizar/ws/oxml/DigitalizarDocumento" xmlns:ns3="http://www.ventanillaunica.gob.mx/aga/digitalizar/ws/oxml/"><ns2:respuestaBase><tieneError>true</tieneError><error><mensaje>La cadena original es inválida</mensaje></error></ns2:respuestaBase></ns3:registroDigitalizarDocumentoServiceResponse></S:Body></S:Envelope>';
        $array[22] = '--uuid:22f19c56-ac82-4912-9361-2afe81a9a501 Content-Id: <rootpart*22f19c56-ac82-4912-9361-2afe81a9a501@example.jaxws.sun.com> Content-Type: application/xop+xml;charset=utf-8;type="text/xml" Content-Transfer-Encoding: binary <?xml version=\'1.0\' encoding=\'UTF-8\'?><S:Envelope xmlns:S="http://schemas.xmlsoap.org/soap/envelope/"><S:Header><wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" S:mustUnderstand="1"><wsu:Timestamp xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd"><wsu:Created>2015-11-24T23:52:25Z</wsu:Created><wsu:Expires>2015-11-24T23:53:25Z</wsu:Expires></wsu:Timestamp></wsse:Security></S:Header><S:Body><ns3:registroDigitalizarDocumentoServiceResponse xmlns="http://www.ventanillaunica.gob.mx/common/ws/oxml/respuesta" xmlns:ns2="http://www.ventanillaunica.gob.mx/aga/digitalizar/ws/oxml/DigitalizarDocumento" xmlns:ns3="http://www.ventanillaunica.gob.mx/aga/digitalizar/ws/oxml/"><ns2:respuestaBase><tieneError>false</tieneError></ns2:respuestaBase><ns2:acuse><ns2:numeroOperacion>38277864</ns2:numeroOperacion><ns2:horaRecepcion>2015-11-24T17:52:25.417-06:00</ns2:horaRecepcion><ns2:mensaje>Su petición se encuentra procesando</ns2:mensaje></ns2:acuse></ns3:registroDigitalizarDocumentoServiceResponse></S:Body></S:Envelope> --uuid:22f19c56-ac82-4912-9361-2afe81a9a501--';
        $array[23] = '<?xml version="1.0" encoding="UTF-8"?>
<S:Envelope xmlns:S="http://schemas.xmlsoap.org/soap/envelope/">
  <S:Header>
    <wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" S:mustUnderstand="1">
      <wsu:Timestamp xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
        <wsu:Created>2015-11-26T22:30:32Z</wsu:Created>
        <wsu:Expires>2015-11-26T22:31:32Z</wsu:Expires>
      </wsu:Timestamp>
    </wsse:Security>
  </S:Header>
  <S:Body>
    <ns2:consultarPedimentoCompletoRespuesta xmlns="http://www.ventanillaunica.gob.mx/pedimentos/ws/oxml/comunes" xmlns:ns2="http://www.ventanillaunica.gob.mx/pedimentos/ws/oxml/consultarpedimentocompleto" xmlns:ns3="http://www.ventanillaunica.gob.mx/common/ws/oxml/respuesta" xmlns:ns4="http://www.ventanillaunica.gob.mx/common/ws/oxml/resolucion" xmlns:ns5="http://www.ventanillaunica.gob.mx/common/ws/oxml/respuestatra" xmlns:ns6="http://www.ventanillaunica.gob.mx/common/ws/oxml/dictamen" xmlns:ns7="http://www.ventanillaunica.gob.mx/common/ws/oxml/observacion" xmlns:ns8="http://www.ventanillaunica.gob.mx/common/ws/oxml/requisito" xmlns:ns9="http://www.ventanillaunica.gob.mx/common/ws/oxml/opinion">
      <ns3:tieneError>true</ns3:tieneError>
      <ns3:error>
        <ns3:mensaje>No hay información para la búsqueda solicitada</ns3:mensaje>
      </ns3:error>
    </ns2:consultarPedimentoCompletoRespuesta>
  </S:Body>
</S:Envelope>'; // consulta pedimento fallida
        $array[24] = '<?xml version="1.0" encoding="UTF-8"?>
<S:Envelope xmlns:S="http://schemas.xmlsoap.org/soap/envelope/">
  <S:Header>
    <wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" S:mustUnderstand="1">
      <wsu:Timestamp xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
        <wsu:Created>2015-11-26T22:46:07Z</wsu:Created>
        <wsu:Expires>2015-11-26T22:47:07Z</wsu:Expires>
      </wsu:Timestamp>
    </wsse:Security>
  </S:Header>
  <S:Body>
    <ns2:consultarPedimentoCompletoRespuesta xmlns="http://www.ventanillaunica.gob.mx/pedimentos/ws/oxml/comunes" xmlns:ns2="http://www.ventanillaunica.gob.mx/pedimentos/ws/oxml/consultarpedimentocompleto" xmlns:ns3="http://www.ventanillaunica.gob.mx/common/ws/oxml/respuesta" xmlns:ns4="http://www.ventanillaunica.gob.mx/common/ws/oxml/resolucion" xmlns:ns5="http://www.ventanillaunica.gob.mx/common/ws/oxml/respuestatra" xmlns:ns6="http://www.ventanillaunica.gob.mx/common/ws/oxml/dictamen" xmlns:ns7="http://www.ventanillaunica.gob.mx/common/ws/oxml/observacion" xmlns:ns8="http://www.ventanillaunica.gob.mx/common/ws/oxml/requisito" xmlns:ns9="http://www.ventanillaunica.gob.mx/common/ws/oxml/opinion">
      <ns3:tieneError>false</ns3:tieneError>
      <ns2:numeroOperacion>1254196605</ns2:numeroOperacion>
      <ns2:pedimento>
        <ns2:pedimento>5006792</ns2:pedimento>
        <ns2:encabezado>
          <ns2:tipoOperacion>
            <ns2:clave>1</ns2:clave>
            <ns2:descripcion>Importacion</ns2:descripcion>
          </ns2:tipoOperacion>
          <ns2:claveDocumento>
            <ns2:clave>A1</ns2:clave>
            <ns2:descripcion>IMPORTACION DEFINITIVA</ns2:descripcion>
          </ns2:claveDocumento>
          <ns2:destino>
            <ns2:clave>9</ns2:clave>
            <ns2:descripcion>INTERIOR DEL PAIS</ns2:descripcion>
          </ns2:destino>
          <ns2:aduanaEntradaSalida>
            <ns2:clave>160</ns2:clave>
            <ns2:descripcion>MANZANILLO, COL.</ns2:descripcion>
          </ns2:aduanaEntradaSalida>
          <ns2:tipoCambio>16.76360</ns2:tipoCambio>
          <ns2:pesoBruto>4817.480</ns2:pesoBruto>
          <ns2:medioTrasnporteSalida>
            <ns2:clave>7</ns2:clave>
            <ns2:descripcion>CARRETERO</ns2:descripcion>
          </ns2:medioTrasnporteSalida>
          <ns2:medioTrasnporteArribo>
            <ns2:clave>1</ns2:clave>
            <ns2:descripcion>MARITIMO</ns2:descripcion>
          </ns2:medioTrasnporteArribo>
          <ns2:medioTrasnporteEntrada>
            <ns2:clave>1</ns2:clave>
            <ns2:descripcion>MARITIMO</ns2:descripcion>
          </ns2:medioTrasnporteEntrada>
          <ns2:curpApoderadomandatario>PEPJ561122HNLRRL06</ns2:curpApoderadomandatario>
          <ns2:rfcAgenteAduanalSocFactura>OAQ030623UL8</ns2:rfcAgenteAduanalSocFactura>
          <ns2:valorDolares>72155.88</ns2:valorDolares>
          <ns2:valorAduanalTotal>1209592.00</ns2:valorAduanalTotal>
          <ns2:valorComercialTotal>1209592.00</ns2:valorComercialTotal>
        </ns2:encabezado>
        <ns2:importadorExportador>
          <ns2:rfc>ACM080307L15</ns2:rfc>
          <ns2:razonSocial>ANSELL COMMERCIAL MEXICO SA DE CV                                                                                       </ns2:razonSocial>
          <ns2:domicilio>
            <ns2:calle>AVE ANTONIO J BERMUDEZ, PARQUE INDUSTRIAL BERMUDEZ                              </ns2:calle>
            <ns2:numeroExterior>1050      </ns2:numeroExterior>
            <ns2:numeroInterior>INT 1     </ns2:numeroInterior>
            <ns2:ciudadMunicipio>JUAREZ                                                                          </ns2:ciudadMunicipio>
            <ns2:codigoPostal>32470     </ns2:codigoPostal>
          </ns2:domicilio>
          <ns2:seguros>0.00</ns2:seguros>
          <ns2:fletes>0.00</ns2:fletes>
          <ns2:embalajes>0.00</ns2:embalajes>
          <ns2:incrementables>0.00</ns2:incrementables>
          <ns2:aaduanaDespacho>
            <ns2:clave>160</ns2:clave>
            <ns2:descripcion>MANZANILLO, COL.</ns2:descripcion>
          </ns2:aaduanaDespacho>
          <ns2:bultos>0</ns2:bultos>
          <ns2:fechas>
            <ns2:fecha>2015-11-24-06:00</ns2:fecha>
            <ns2:tipo>
              <ns2:clave>2</ns2:clave>
              <ns2:descripcion>FECHA DE PAGO DE LAS CONTRIBUCIONES</ns2:descripcion>
            </ns2:tipo>
          </ns2:fechas>
          <ns2:fechas>
            <ns2:fecha>2015-11-20-06:00</ns2:fecha>
            <ns2:tipo>
              <ns2:clave>1</ns2:clave>
              <ns2:descripcion>FECHA DE ENTRADA A TERRITORIO NAL.</ns2:descripcion>
            </ns2:tipo>
          </ns2:fechas>
          <ns2:efectivo>9944.00</ns2:efectivo>
          <ns2:otros>0</ns2:otros>
          <ns2:total>9944.00</ns2:total>
          <ns2:pais>
            <clave>MEX</clave>
            <descripcion>MEXICO (ESTADOS UNIDOS MEXICANOS)</descripcion>
          </ns2:pais>
        </ns2:importadorExportador>
        <ns2:tasas>
          <ns2:contribucion>
            <ns2:clave>15</ns2:clave>
            <ns2:descripcion>PREVALIDAAAA</ns2:descripcion>
          </ns2:contribucion>
          <ns2:tipoTasa>
            <clave>2</clave>
            <descripcion>ESPECIFICO</descripcion>
          </ns2:tipoTasa>
          <ns2:tasaAplicable>210.0000000000</ns2:tasaAplicable>
          <ns2:formaPago>
            <clave>0</clave>
            <descripcion>EFECTIVO</descripcion>
          </ns2:formaPago>
          <ns2:importe>210.00</ns2:importe>
        </ns2:tasas>
        <ns2:tasas>
          <ns2:contribucion>
            <ns2:clave>21</ns2:clave>
            <ns2:descripcion>CONTRAPRESTA</ns2:descripcion>
          </ns2:contribucion>
          <ns2:tipoTasa>
            <clave>2</clave>
            <descripcion>ESPECIFICO</descripcion>
          </ns2:tipoTasa>
          <ns2:tasaAplicable>20.0000000000</ns2:tasaAplicable>
          <ns2:formaPago>
            <clave>0</clave>
            <descripcion>EFECTIVO</descripcion>
          </ns2:formaPago>
          <ns2:importe>57.00</ns2:importe>
        </ns2:tasas>
        <ns2:tasas>
          <ns2:contribucion>
            <ns2:clave>1</ns2:clave>
            <ns2:descripcion>DTA</ns2:descripcion>
          </ns2:contribucion>
          <ns2:tipoTasa>
            <clave>7</clave>
            <descripcion>AL MILLAR DTA</descripcion>
          </ns2:tipoTasa>
          <ns2:tasaAplicable>8.0000000000</ns2:tasaAplicable>
          <ns2:formaPago>
            <clave>0</clave>
            <descripcion>EFECTIVO</descripcion>
          </ns2:formaPago>
          <ns2:importe>9677.00</ns2:importe>
        </ns2:tasas>
        <ns2:proveedoresCompradores>
          <ns2:identificadorFiscal>C2333355502</ns2:identificadorFiscal>
          <ns2:proveedorComprador>ANSELL GLOBAL TRADING CENTER (MALAYSIA) SDN. BHD.</ns2:proveedorComprador>
          <ns2:domicilio>
            <ns2:calle>PRIMA AVENUE, JALAN TEKNOKRAT 6,                                                </ns2:calle>
            <ns2:numeroExterior>BLOCK 3512</ns2:numeroExterior>
            <ns2:numeroInterior>PRIMA 6</ns2:numeroInterior>
            <ns2:ciudadMunicipio>CYBERJAYA, SELANGOR                                                             </ns2:ciudadMunicipio>
            <ns2:codigoPostal>63000</ns2:codigoPostal>
          </ns2:domicilio>
          <ns2:moneda>
            <ns2:clave>USD</ns2:clave>
            <ns2:descripcion>AMERICAN DOLLARS</ns2:descripcion>
          </ns2:moneda>
          <ns2:valorMonedaExtranjera>62500.00</ns2:valorMonedaExtranjera>
          <ns2:valorDolares>62500.00</ns2:valorDolares>
          <ns2:pais>
            <clave>MYS</clave>
            <descripcion>MALASIA</descripcion>
          </ns2:pais>
        </ns2:proveedoresCompradores>
        <ns2:proveedoresCompradores>
          <ns2:identificadorFiscal>C2333355502</ns2:identificadorFiscal>
          <ns2:proveedorComprador>ANSELL GLOBAL TRADING CENTER (MALAYSIA) SDN. BHD.</ns2:proveedorComprador>
          <ns2:domicilio>
            <ns2:calle>PRIMA AVENUE, JALAN TEKNOKRAT 6,                                                </ns2:calle>
            <ns2:numeroExterior>BLOCK 3512</ns2:numeroExterior>
            <ns2:numeroInterior>PRIMA 6</ns2:numeroInterior>
            <ns2:ciudadMunicipio>CYBERJAYA, SELANGOR                                                             </ns2:ciudadMunicipio>
            <ns2:codigoPostal>63000</ns2:codigoPostal>
          </ns2:domicilio>
          <ns2:moneda>
            <ns2:clave>USD</ns2:clave>
            <ns2:descripcion>AMERICAN DOLLARS</ns2:descripcion>
          </ns2:moneda>
          <ns2:valorMonedaExtranjera>9655.88</ns2:valorMonedaExtranjera>
          <ns2:valorDolares>9655.88</ns2:valorDolares>
          <ns2:pais>
            <clave>MYS</clave>
            <descripcion>MALASIA</descripcion>
          </ns2:pais>
        </ns2:proveedoresCompradores>
        <ns2:facturas>
          <ns2:fecha>2015-10-29-06:00</ns2:fecha>
          <ns2:numero>COVE151AP7WJ5</ns2:numero>
          <ns2:terminoFacturacion>
            <ns2:clave>CFR</ns2:clave>
            <ns2:descripcion>COSTE Y FLETE (... PUERTO DE DESTINO CONVENIDO)</ns2:descripcion>
          </ns2:terminoFacturacion>
          <ns2:moneda>
            <ns2:clave>USD</ns2:clave>
            <ns2:descripcion>AMERICAN DOLLARS</ns2:descripcion>
          </ns2:moneda>
          <ns2:valorDolares>62500.00</ns2:valorDolares>
          <ns2:valorMonedaExtranjera>62500.00</ns2:valorMonedaExtranjera>
          <ns2:identificadorFiscalProveedorComprador>C2333355502</ns2:identificadorFiscalProveedorComprador>
          <ns2:proveedorComprador>ANSELL GLOBAL TRADING CENTER (MALAYSIA) SDN. BHD.</ns2:proveedorComprador>
        </ns2:facturas>
        <ns2:facturas>
          <ns2:fecha>2015-10-28-06:00</ns2:fecha>
          <ns2:numero>COVE151AP7WG8</ns2:numero>
          <ns2:terminoFacturacion>
            <ns2:clave>CFR</ns2:clave>
            <ns2:descripcion>COSTE Y FLETE (... PUERTO DE DESTINO CONVENIDO)</ns2:descripcion>
          </ns2:terminoFacturacion>
          <ns2:moneda>
            <ns2:clave>USD</ns2:clave>
            <ns2:descripcion>AMERICAN DOLLARS</ns2:descripcion>
          </ns2:moneda>
          <ns2:valorDolares>9655.88</ns2:valorDolares>
          <ns2:valorMonedaExtranjera>9655.88</ns2:valorMonedaExtranjera>
          <ns2:identificadorFiscalProveedorComprador>C2333355502</ns2:identificadorFiscalProveedorComprador>
          <ns2:proveedorComprador>ANSELL GLOBAL TRADING CENTER (MALAYSIA) SDN. BHD.</ns2:proveedorComprador>
        </ns2:facturas>
        <ns2:transportes>
          <ns2:identificador>COYHAIQUE</ns2:identificador>
          <ns2:paisTransporte>
            <clave>LBR</clave>
            <descripcion>LIBERIA (REPUBLICA DE)</descripcion>
          </ns2:paisTransporte>
          <ns2:nombre>HAPAG LLOYD</ns2:nombre>
        </ns2:transportes>
        <ns2:guias>
          <ns2:guiaManifiesto>HLCUSGN151017523</ns2:guiaManifiesto>
          <ns2:tipoGuia>M</ns2:tipoGuia>
        </ns2:guias>
        <ns2:identificadores>
          <ns2:identificadores>
            <claveIdentificador>
              <clave>ED</clave>
              <descripcion>E_DOCUMENT DOCUMENTO DIGITALIZADO</descripcion>
            </claveIdentificador>
            <complemento1>043815054VP77</complemento1>
          </ns2:identificadores>
          <ns2:identificadores>
            <claveIdentificador>
              <clave>ED</clave>
              <descripcion>E_DOCUMENT DOCUMENTO DIGITALIZADO</descripcion>
            </claveIdentificador>
            <complemento1>04361503ME5H5</complemento1>
          </ns2:identificadores>
          <ns2:identificadores>
            <claveIdentificador>
              <clave>ED</clave>
              <descripcion>E_DOCUMENT DOCUMENTO DIGITALIZADO</descripcion>
            </claveIdentificador>
            <complemento1>01701500BH681</complemento1>
          </ns2:identificadores>
          <ns2:identificadores>
            <claveIdentificador>
              <clave>ED</clave>
              <descripcion>E_DOCUMENT DOCUMENTO DIGITALIZADO</descripcion>
            </claveIdentificador>
            <complemento1>01921506D3M46</complemento1>
          </ns2:identificadores>
          <ns2:identificadores>
            <claveIdentificador>
              <clave>IC</clave>
              <descripcion>IMPORTADOR CERTIFICADO</descripcion>
            </claveIdentificador>
            <complemento1>L</complemento1>
          </ns2:identificadores>
          <ns2:identificadores>
            <claveIdentificador>
              <clave>ED</clave>
              <descripcion>E_DOCUMENT DOCUMENTO DIGITALIZADO</descripcion>
            </claveIdentificador>
            <complemento1>01701500BK2S1</complemento1>
          </ns2:identificadores>
          <ns2:identificadores>
            <claveIdentificador>
              <clave>CR</clave>
              <descripcion>CLAVE DE RECINTO FISCAL O FISCALIZADO</descripcion>
            </claveIdentificador>
            <complemento1>39</complemento1>
          </ns2:identificadores>
          <ns2:identificadores>
            <claveIdentificador>
              <clave>ED</clave>
              <descripcion>E_DOCUMENT DOCUMENTO DIGITALIZADO</descripcion>
            </claveIdentificador>
            <complemento1>01921506CVAN4</complemento1>
          </ns2:identificadores>
        </ns2:identificadores>
        <ns2:observaciones>IMPORTACION DEFINITIVA CON FUNDAMENTO EN LO ESTABLECIDO EN EL ART. 96 DE LA LEY ADUANERA EN VIGOR. SE DECLARAN: LOS NUMEROS DE ACUSE DE VALOR CONFORME LA REGLA 1.9.14 DE R.G.C.E. VIGENTE,LOS E-DOCUMENT DE CADA UNO DE LOS DOCUMENTOS DIGITALIZADOS CONFORME LA REGLA 3.1.29 DE R.G.C.E. VIGENTE.                                                                     - CONOCIMIENTO MARITIMO REVALIDADO.                                                                                     - FACTURA                                                                                                               - CARTA 3.1.5                                                                                                           - CARTA EXCENCION DE NOM.                                                                                               - RELACION DE DATOS DE IDENTIFICACION INDIVIDUAL.                                                                       B/M: COYHAIQUE V-543E                                                                                                   </ns2:observaciones>
        <ns2:partidas>1</ns2:partidas>
        <ns2:contenedores>
          <ns2:identificador>INKU6271465</ns2:identificador>
          <ns2:tipoContenedor>
            <ns2:clave>3</ns2:clave>
            <ns2:descripcion>CONTENEDOR ESTANDAR DE CUBO ALTO 40</ns2:descripcion>
          </ns2:tipoContenedor>
        </ns2:contenedores>
      </ns2:pedimento>
    </ns2:consultarPedimentoCompletoRespuesta>
  </S:Body>
</S:Envelope>';
        $array[25] = '<?xml version="1.0" encoding="UTF-8"?>
<S:Envelope xmlns:S="http://schemas.xmlsoap.org/soap/envelope/">
  <S:Header>
    <wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" S:mustUnderstand="1">
      <wsu:Timestamp xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
        <wsu:Created>2015-12-02T01:07:22Z</wsu:Created>
        <wsu:Expires>2015-12-02T01:08:22Z</wsu:Expires>
      </wsu:Timestamp>
    </wsse:Security>
  </S:Header>
  <S:Body>
    <ns9:consultarPartidaRespuesta xmlns="http://www.ventanillaunica.gob.mx/common/ws/oxml/respuesta" xmlns:ns2="http://www.ventanillaunica.gob.mx/common/ws/oxml/resolucion" xmlns:ns3="http://www.ventanillaunica.gob.mx/common/ws/oxml/respuestatra" xmlns:ns4="http://www.ventanillaunica.gob.mx/common/ws/oxml/dictamen" xmlns:ns5="http://www.ventanillaunica.gob.mx/common/ws/oxml/observacion" xmlns:ns6="http://www.ventanillaunica.gob.mx/common/ws/oxml/requisito" xmlns:ns7="http://www.ventanillaunica.gob.mx/common/ws/oxml/opinion" xmlns:ns8="http://www.ventanillaunica.gob.mx/pedimentos/ws/oxml/comunes" xmlns:ns9="http://www.ventanillaunica.gob.mx/pedimentos/ws/oxml/consultarpartida">
      <tieneError>false</tieneError>
      <ns9:partida>
        <ns8:numeroPartida>1</ns8:numeroPartida>
        <ns8:fraccionArancelaria>61161099</ns8:fraccionArancelaria>
        <ns8:descripcionMercancia>GUANTES DE PUNTO RECUBIERTO O REVESTIDO CON PLASTICO                                                                                                                                                                                                      </ns8:descripcionMercancia>
        <ns8:unidadMedidaTarifa>
          <ns8:clave>9</ns8:clave>
          <ns8:descripcion>PAR</ns8:descripcion>
        </ns8:unidadMedidaTarifa>
        <ns8:cantidadUnidadMedidaTarifa>24480.00000</ns8:cantidadUnidadMedidaTarifa>
        <ns8:unidadMedidaComercial>
          <ns8:clave>20</ns8:clave>
          <ns8:descripcion>CAJA</ns8:descripcion>
        </ns8:unidadMedidaComercial>
        <ns8:cantidadUnidadMedidaComercial>170.000</ns8:cantidadUnidadMedidaComercial>
        <ns8:precioUnitario>3405.26471</ns8:precioUnitario>
        <ns8:valorComercial>578895.00</ns8:valorComercial>
        <ns8:valorAduana>588634.00</ns8:valorAduana>
        <ns8:valorDolares>34342.36</ns8:valorDolares>
        <ns8:valorAgregado>0.00</ns8:valorAgregado>
        <ns8:metodoValoracion>VALOR DE TRANSACCION DE LAS MERCANCIAS</ns8:metodoValoracion>
        <ns8:vinculacion>SI EXISTE VINCULACION Y NO AFECTA EL VALOR ADUANA</ns8:vinculacion>
        <ns8:paisOrigenDestino>
          <ns8:clave>KOR</ns8:clave>
          <ns8:descripcion>COREA (REPUBLICA DE) (COREA DEL SUR)</ns8:descripcion>
        </ns8:paisOrigenDestino>
        <ns8:paisVendedorComprador>
          <ns8:clave>AUS</ns8:clave>
          <ns8:descripcion>AUSTRALIA (COMUNIDAD DE)</ns8:descripcion>
        </ns8:paisVendedorComprador>
        <ns8:identificadores>
          <ns8:claveIdentificador>
            <ns8:clave>EX</ns8:clave>
            <ns8:descripcion>EXCEDNTO DE CUENTA ADUANERA DE GARANTIA</ns8:descripcion>
          </ns8:claveIdentificador>
          <ns8:complemento1>31</ns8:complemento1>
        </ns8:identificadores>
        <ns8:identificadores>
          <ns8:claveIdentificador>
            <ns8:clave>EN</ns8:clave>
            <ns8:descripcion>NO APLICACION LA NORMA OFICIAL MEXICANA</ns8:descripcion>
          </ns8:claveIdentificador>
          <ns8:complemento1>U</ns8:complemento1>
          <ns8:complemento2>NOM-015-SCFI-2007</ns8:complemento2>
        </ns8:identificadores>
        <ns8:identificadores>
          <ns8:claveIdentificador>
            <ns8:clave>EN</ns8:clave>
            <ns8:descripcion>NO APLICACION LA NORMA OFICIAL MEXICANA</ns8:descripcion>
          </ns8:claveIdentificador>
          <ns8:complemento1>VIII</ns8:complemento1>
          <ns8:complemento2>NOM-004-SCFI-2006</ns8:complemento2>
        </ns8:identificadores>
        <ns8:gravamenes>
          <ns8:claveGravamen>
            <ns8:clave>3</ns8:clave>
            <ns8:descripcion>IVA</ns8:descripcion>
          </ns8:claveGravamen>
          <ns8:tasas>
            <ns8:clave>
              <ns8:clave>1</ns8:clave>
              <ns8:descripcion>PORCENTUAL</ns8:descripcion>
            </ns8:clave>
            <ns8:tasaAplicable>16.0000000000</ns8:tasaAplicable>
          </ns8:tasas>
          <ns8:importes>
            <ns8:formaPago>
              <ns8:clave>0</ns8:clave>
              <ns8:descripcion>EFECTIVO</ns8:descripcion>
            </ns8:formaPago>
            <ns8:importe>113771.00</ns8:importe>
          </ns8:importes>
        </ns8:gravamenes>
        <ns8:gravamenes>
          <ns8:claveGravamen>
            <ns8:clave>6</ns8:clave>
            <ns8:descripcion>IGI/IGE</ns8:descripcion>
          </ns8:claveGravamen>
          <ns8:tasas>
            <ns8:clave>
              <ns8:clave>1</ns8:clave>
              <ns8:descripcion>PORCENTUAL</ns8:descripcion>
            </ns8:clave>
            <ns8:tasaAplicable>20.0000000000</ns8:tasaAplicable>
          </ns8:tasas>
          <ns8:importes>
            <ns8:formaPago>
              <ns8:clave>0</ns8:clave>
              <ns8:descripcion>EFECTIVO</ns8:descripcion>
            </ns8:formaPago>
            <ns8:importe>117727.00</ns8:importe>
          </ns8:importes>
        </ns8:gravamenes>
        <ns8:observaciones>ESTILO:48-135                                                                                                           
NUMERO DE PARTE:124588,124590                                                                                           
ESTILO:11-627,11-630                                                                                                    
NUMERO DE PARTE:103400,111990,111991                                                                                    
MARCA:ANSELL,HYFLEX,HYFLEX CR2                                                                                          
MARCA:SENSILITE,ANSELL                                                                                                  
</ns8:observaciones>
      </ns9:partida>
    </ns9:consultarPartidaRespuesta>
  </S:Body>
</S:Envelope>';
        $array[26] = '<?xml version="1.0" encoding="UTF-8"?>
<S:Envelope xmlns:S="http://schemas.xmlsoap.org/soap/envelope/">
  <S:Header>
    <wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" S:mustUnderstand="1">
      <wsu:Timestamp xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
        <wsu:Created>2015-12-15T17:28:40Z</wsu:Created>
        <wsu:Expires>2015-12-15T17:29:40Z</wsu:Expires>
      </wsu:Timestamp>
    </wsse:Security>
  </S:Header>
  <S:Body>
    <ConsultarEdocumentResponse xmlns="http://www.ventanillaunica.gob.mx/ConsultarEdocument/" xmlns:ns2="http://www.ventanillaunica.gob.mx/cove/ws/oxml/">
      <response>
        <mensaje>El Cove o Adenda no existe, no estÃ¡ firmado o no cuenta con la autorizaciÃ³n para consultarlo</mensaje>
        <contieneError>false</contieneError>
        <resultadoBusqueda/>
      </response>
    </ConsultarEdocumentResponse>
  </S:Body>
</S:Envelope>';
        $array[27] = '<S:Envelope xmlns:S="http://schemas.xmlsoap.org/soap/envelope/">
   <S:Header>
      <wsse:Security S:mustUnderstand="1" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
         <wsu:Timestamp xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
            <wsu:Created>2016-03-01T23:48:43Z</wsu:Created>
            <wsu:Expires>2016-03-01T23:49:43Z</wsu:Expires>
         </wsu:Timestamp>
      </wsse:Security>
   </S:Header>
   <S:Body>
      <solicitarRecibirCoveServicioResponse xmlns="http://www.ventanillaunica.gob.mx/cove/ws/oxml/">
         <mensajeInformativo>No recibira correo de respuesta ya que no se encontro dirección de correo para enviarla.</mensajeInformativo>
      </solicitarRecibirCoveServicioResponse>
   </S:Body>
</S:Envelope>';
        $array[28] = '<?xml version="1.0" encoding="UTF-8"?>
<S:Envelope xmlns:S="http://schemas.xmlsoap.org/soap/envelope/">
  <S:Body>
    <S:Fault xmlns:ns4="http://www.w3.org/2003/05/soap-envelope">
      <faultcode>S:Client</faultcode>
      <faultstring>Couldn\'t create SOAP message due to exception: XML reader error: com.ctc.wstx.exc.WstxEOFException: Unexpected EOF in prolog at [row,col {unknown-source}]: [1,0]</faultstring>
    </S:Fault>
  </S:Body>
</S:Envelope>';
        $array[29] = '<env:Envelope xmlns:env="http://schemas.xmlsoap.org/soap/envelope/">
   <env:Body>
      <env:Fault xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
         <faultcode>wsse:InvalidSecurity</faultcode>
         <faultstring>Found more than one Security header for role null</faultstring>
      </env:Fault>
   </env:Body>
</env:Envelope>';
        $array[30] = '<?xml version="1.0" encoding="UTF-8"?>
<S:Envelope xmlns:S="http://schemas.xmlsoap.org/soap/envelope/">
  <S:Header>
    <wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" S:mustUnderstand="1">
      <wsu:Timestamp xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
        <wsu:Created>2016-03-11T20:58:02Z</wsu:Created>
        <wsu:Expires>2016-03-11T20:59:02Z</wsu:Expires>
      </wsu:Timestamp>
    </wsse:Security>
  </S:Header>
  <S:Body>
    <solicitarConsultarRespuestaCoveServicioResponse xmlns="http://www.ventanillaunica.gob.mx/cove/ws/oxml/">
      <numeroOperacion>71807703</numeroOperacion>
      <horaRecepcion>2016-03-11T14:33:11.000-06:00</horaRecepcion>
      <respuestasOperaciones>
        <numeroFacturaORelacionFacturas>EDC00830B</numeroFacturaORelacionFacturas>
        <contieneError>false</contieneError>
        <eDocument>COVE161F731E4</eDocument>
        <numeroAdenda>ADEN163182149</numeroAdenda>
      </respuestasOperaciones>
      <leyenda>Tiene 240 días a partir de esta fecha para utilizar su Acuse de Valor, si en ese tiempo no es utilizado, será dado de baja del sistema.</leyenda>
    </solicitarConsultarRespuestaCoveServicioResponse>
  </S:Body>
</S:Envelope>';
        // consulta de estado de pedimento
        $array[31] = '<?xml version="1.0" encoding="UTF-8"?>
<S:Envelope xmlns:S="http://schemas.xmlsoap.org/soap/envelope/">
  <S:Header>
    <wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" S:mustUnderstand="1">
      <wsu:Timestamp xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
        <wsu:Created>2017-04-10T18:25:32Z</wsu:Created>
        <wsu:Expires>2017-04-10T18:26:32Z</wsu:Expires>
      </wsu:Timestamp>
    </wsse:Security>
  </S:Header>
  <S:Body>
    <consultarEstadoPedimentosRespuesta xmlns="http://www.ventanillaunica.gob.mx/pedimentos/ws/oxml/consultarestadopedimentos" xmlns:ns2="http://www.ventanillaunica.gob.mx/pedimentos/ws/oxml/comunes" xmlns:ns3="http://www.ventanillaunica.gob.mx/common/ws/oxml/respuesta" xmlns:ns4="http://www.ventanillaunica.gob.mx/common/ws/oxml/resolucion" xmlns:ns5="http://www.ventanillaunica.gob.mx/common/ws/oxml/respuestatra" xmlns:ns6="http://www.ventanillaunica.gob.mx/common/ws/oxml/dictamen" xmlns:ns7="http://www.ventanillaunica.gob.mx/common/ws/oxml/observacion" xmlns:ns8="http://www.ventanillaunica.gob.mx/common/ws/oxml/requisito" xmlns:ns9="http://www.ventanillaunica.gob.mx/common/ws/oxml/opinion">
      <ns3:tieneError>true</ns3:tieneError>
      <ns3:error>
        <ns3:mensaje>El valor de [Número de Operacion] es obligatorio</ns3:mensaje>
      </ns3:error>
    </consultarEstadoPedimentosRespuesta>
  </S:Body>
</S:Envelope>';
        $array[32] = '<?xml version="1.0" encoding="UTF-8"?>
<S:Envelope xmlns:S="http://schemas.xmlsoap.org/soap/envelope/">
  <S:Header>
    <wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" S:mustUnderstand="1">
      <wsu:Timestamp xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
        <wsu:Created>2017-04-10T18:31:38Z</wsu:Created>
        <wsu:Expires>2017-04-10T18:32:38Z</wsu:Expires>
      </wsu:Timestamp>
    </wsse:Security>
  </S:Header>
  <S:Body>
    <consultarEstadoPedimentosRespuesta xmlns="http://www.ventanillaunica.gob.mx/pedimentos/ws/oxml/consultarestadopedimentos" xmlns:ns2="http://www.ventanillaunica.gob.mx/pedimentos/ws/oxml/comunes" xmlns:ns3="http://www.ventanillaunica.gob.mx/common/ws/oxml/respuesta" xmlns:ns4="http://www.ventanillaunica.gob.mx/common/ws/oxml/resolucion" xmlns:ns5="http://www.ventanillaunica.gob.mx/common/ws/oxml/respuestatra" xmlns:ns6="http://www.ventanillaunica.gob.mx/common/ws/oxml/dictamen" xmlns:ns7="http://www.ventanillaunica.gob.mx/common/ws/oxml/observacion" xmlns:ns8="http://www.ventanillaunica.gob.mx/common/ws/oxml/requisito" xmlns:ns9="http://www.ventanillaunica.gob.mx/common/ws/oxml/opinion">
      <ns3:tieneError>false</ns3:tieneError>
      <pedimento>
        <numeroPrevalidador>2755</numeroPrevalidador>
        <descripcionPrevalidador>CONFEDERACION DE ASOCIACIONES DE AGENTES ADUANALES DE LA REPUBLICA MEXICANA, A.C</descripcionPrevalidador>
        <fechaEstado>2017-03-16T00:27:34.000-06:00</fechaEstado>
        <estadosPedimento>
          <estado>2</estado>
          <descripcionEstado>PAGADO</descripcionEstado>
          <subEstado>3</subEstado>
          <descripcionSubEstado>BITAL</descripcionSubEstado>
          <secuencia>0</secuencia>
        </estadosPedimento>
        <estadosPedimento>
          <estado>3</estado>
          <descripcionEstado>PRIMERA SELECCIÓN AUTOMATIZADA</descripcionEstado>
          <subEstado>320</subEstado>
          <descripcionSubEstado>VERDE EN PRIMERA SELECCIÓN</descripcionSubEstado>
          <secuencia>0</secuencia>
        </estadosPedimento>
        <estadosPedimento>
          <estado>7</estado>
          <descripcionEstado>DESADUANADO/CUMPLIDO</descripcionEstado>
          <subEstado>710</subEstado>
          <descripcionSubEstado>DESADUANADO</descripcionSubEstado>
          <secuencia>0</secuencia>
        </estadosPedimento>
        <estadosPedimento>
          <estado>7</estado>
          <descripcionEstado>DESADUANADO/CUMPLIDO</descripcionEstado>
          <subEstado>730</subEstado>
          <descripcionSubEstado>CUMPLIDO</descripcionSubEstado>
          <secuencia>0</secuencia>
        </estadosPedimento>
      </pedimento>
    </consultarEstadoPedimentosRespuesta>
  </S:Body>
</S:Envelope>';
        $array[33] = '<?xml version="1.0" encoding="UTF-8"?>
<S:Envelope xmlns:S="http://schemas.xmlsoap.org/soap/envelope/">
  <S:Header>
    <wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" S:mustUnderstand="1">
      <wsu:Timestamp xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
        <wsu:Created>2017-04-10T18:32:36Z</wsu:Created>
        <wsu:Expires>2017-04-10T18:33:36Z</wsu:Expires>
      </wsu:Timestamp>
    </wsse:Security>
  </S:Header>
  <S:Body>
    <consultarEstadoPedimentosRespuesta xmlns="http://www.ventanillaunica.gob.mx/pedimentos/ws/oxml/consultarestadopedimentos" xmlns:ns2="http://www.ventanillaunica.gob.mx/pedimentos/ws/oxml/comunes" xmlns:ns3="http://www.ventanillaunica.gob.mx/common/ws/oxml/respuesta" xmlns:ns4="http://www.ventanillaunica.gob.mx/common/ws/oxml/resolucion" xmlns:ns5="http://www.ventanillaunica.gob.mx/common/ws/oxml/respuestatra" xmlns:ns6="http://www.ventanillaunica.gob.mx/common/ws/oxml/dictamen" xmlns:ns7="http://www.ventanillaunica.gob.mx/common/ws/oxml/observacion" xmlns:ns8="http://www.ventanillaunica.gob.mx/common/ws/oxml/requisito" xmlns:ns9="http://www.ventanillaunica.gob.mx/common/ws/oxml/opinion">
      <ns3:tieneError>true</ns3:tieneError>
      <ns3:error>
        <ns3:mensaje>El número de operacion no corresponde con los datos de petición.</ns3:mensaje>
      </ns3:error>
    </consultarEstadoPedimentosRespuesta>
  </S:Body>
</S:Envelope>';
        $array[34] = '<?xml version="1.0" encoding="UTF-8"?>
<S:Envelope xmlns:S="http://schemas.xmlsoap.org/soap/envelope/">
  <S:Header>
    <wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" S:mustUnderstand="1">
      <wsu:Timestamp xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
        <wsu:Created>2017-04-10T18:52:31Z</wsu:Created>
        <wsu:Expires>2017-04-10T18:53:31Z</wsu:Expires>
      </wsu:Timestamp>
    </wsse:Security>
  </S:Header>
  <S:Body>
    <ns2:consultarPedimentoCompletoRespuesta xmlns="http://www.ventanillaunica.gob.mx/pedimentos/ws/oxml/comunes" xmlns:ns2="http://www.ventanillaunica.gob.mx/pedimentos/ws/oxml/consultarpedimentocompleto" xmlns:ns3="http://www.ventanillaunica.gob.mx/common/ws/oxml/respuesta" xmlns:ns4="http://www.ventanillaunica.gob.mx/common/ws/oxml/resolucion" xmlns:ns5="http://www.ventanillaunica.gob.mx/common/ws/oxml/respuestatra" xmlns:ns6="http://www.ventanillaunica.gob.mx/common/ws/oxml/dictamen" xmlns:ns7="http://www.ventanillaunica.gob.mx/common/ws/oxml/observacion" xmlns:ns8="http://www.ventanillaunica.gob.mx/common/ws/oxml/requisito" xmlns:ns9="http://www.ventanillaunica.gob.mx/common/ws/oxml/opinion">
      <ns3:tieneError>true</ns3:tieneError>
      <ns3:error>
        <ns3:mensaje>No hay información para la búsqueda solicitada</ns3:mensaje>
      </ns3:error>
    </ns2:consultarPedimentoCompletoRespuesta>
  </S:Body>
</S:Envelope>';
        $array[35] = '<env:Envelope xmlns:env="http://schemas.xmlsoap.org/soap/envelope/">
   <env:Body>
      <env:Fault xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
         <faultcode>wsse:FailedAuthentication</faultcode>
         <faultstring>Failed to assert identity with UsernameToken.</faultstring>
      </env:Fault>
   </env:Body>
</env:Envelope>';
        $array[36] = '<?xml version="1.0" encoding="UTF-8"?><S:Envelope xmlns:S="http://schemas.xmlsoap.org/soap/envelope/"><S:Header><wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" S:mustUnderstand="1"><wsu:Timestamp xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd"><wsu:Created>2017-04-11T18:37:31Z</wsu:Created><wsu:Expires>2017-04-11T18:38:31Z</wsu:Expires></wsu:Timestamp></wsse:Security></S:Header><S:Body><consultarEstadoPedimentosRespuesta xmlns="http://www.ventanillaunica.gob.mx/pedimentos/ws/oxml/consultarestadopedimentos" xmlns:ns2="http://www.ventanillaunica.gob.mx/pedimentos/ws/oxml/comunes" xmlns:ns3="http://www.ventanillaunica.gob.mx/common/ws/oxml/respuesta" xmlns:ns4="http://www.ventanillaunica.gob.mx/common/ws/oxml/resolucion" xmlns:ns5="http://www.ventanillaunica.gob.mx/common/ws/oxml/respuestatra" xmlns:ns6="http://www.ventanillaunica.gob.mx/common/ws/oxml/dictamen" xmlns:ns7="http://www.ventanillaunica.gob.mx/common/ws/oxml/observacion" xmlns:ns8="http://www.ventanillaunica.gob.mx/common/ws/oxml/requisito" xmlns:ns9="http://www.ventanillaunica.gob.mx/common/ws/oxml/opinion"><ns3:tieneError>false</ns3:tieneError><pedimento><numeroPrevalidador>2755</numeroPrevalidador><descripcionPrevalidador>CONFEDERACION DE ASOCIACIONES DE AGENTES ADUANALES DE LA REPUBLICA MEXICANA, A.C</descripcionPrevalidador><fechaEstado>2017-03-16T00:27:34.000-06:00</fechaEstado><estadosPedimento><estado>2</estado><descripcionEstado>PAGADO</descripcionEstado><subEstado>3</subEstado><descripcionSubEstado>BITAL</descripcionSubEstado><secuencia>0</secuencia></estadosPedimento><estadosPedimento><estado>3</estado><descripcionEstado>PRIMERA SELECCIÓN AUTOMATIZADA</descripcionEstado><subEstado>320</subEstado><descripcionSubEstado>VERDE EN PRIMERA SELECCIÓN</descripcionSubEstado><secuencia>0</secuencia></estadosPedimento><estadosPedimento><estado>7</estado><descripcionEstado>DESADUANADO/CUMPLIDO</descripcionEstado><subEstado>710</subEstado><descripcionSubEstado>DESADUANADO</descripcionSubEstado><secuencia>0</secuencia></estadosPedimento><estadosPedimento><estado>7</estado><descripcionEstado>DESADUANADO/CUMPLIDO</descripcionEstado><subEstado>730</subEstado><descripcionSubEstado>CUMPLIDO</descripcionSubEstado><secuencia>0</secuencia></estadosPedimento></pedimento></consultarEstadoPedimentosRespuesta></S:Body></S:Envelope>';
        $array[37] = '<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
  <s:Body>
    <DocumentoOut xmlns="http://tempuri.org/">
      <CadenaOriginal xmlns:i="http://www.w3.org/2001/XMLSchema-instance" i:nil="true"/>
      <Errores>Le informamos  que el  horario para poder generar consultas es de 22:00 p.m. a las 08:00 a.m.</Errores>
      <File xmlns:i="http://www.w3.org/2001/XMLSchema-instance" i:nil="true"/>
      <SelloDigital xmlns:i="http://www.w3.org/2001/XMLSchema-instance" i:nil="true"/>
      <TieneError>true</TieneError>
      <TipoDocumento>0</TipoDocumento>
    </DocumentoOut>
  </s:Body>
</s:Envelope>';
        return $array[$value];
    }

}
