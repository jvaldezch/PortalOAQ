<?php

/**
 * Description of Vucem_Xml
 * 
 * Esta clase conforma arreglos en archivos XML requeridos por la Ventanilla Única de Comercio Exterior (VUCEM)
 *
 * @author Jaime E. Valdez jvaldezch at gmail
 */
class OAQ_XmlPedimentoCatalogos {

    public function tipoIdentificador($value) {
        switch ($value) {
            case "AC": return "ALMACEN GENERAL DE DEPOSITO CERTIFICADO.";
            case "AE": return "EMPRESA DE COMERCIO EXTERIOR.";
            case "AF": return "ACTIVO FIJO";
            case "AG": return "ALMACEN GENERAL DE DEPOSITO FISCAL.";
            case "AI": return "OPERACIONES DE COMERCIO EXTERIOR CON AMPARO.";
            case "AL": return "MERCANCIA ORIGINARIA IMPORTADA AL AMPARO DE ALADI";
            case "AR": return "CONSULTA ARANCELARIA";
            case "AT": return "AVISO DE TRANSITO.";
            case "A3": return "REGULARIZACION DE MERCANCIAS (IMPORTACION DEFINITIVA).";
            case "BB": return "EXPORTACION DEFINITIVA Y RETORNO VIRTUAL.";
            case "BR": return "EXPORTACION TEMPORAL DE MERCANCIAS FUNGIBLES Y SU RETORNO.";
            case "CC": return "CARTA DE CUPO.";
            case "CD": return "CERTIFICADO CON DISPENSA TEMPORAL";
            case "CE": return "CERTIFICADO DE ELEGIBILIDAD.";
            case "CF": return "REGISTRO ANTE LA SECRETARIA DE ECONOMIA DE EMPRESAS UBICADAS EN LA FRANJA O REGION FRONTERIZA";
            case "CI": return "CERTIFICACIÓN EN MATERIA DE IVA E IEPS.";
            case "CO": return "CONDONACIÓN DE CRÉDITOS FISCALES.";
            case "CR": return "RECINTO FISCALIZADO";
            case "CS": return "COPIA SIMPLE.";
            case "C2": return "IMPORTACION DEFINITIVA DE VEHICULOS USADOS A LA FRANJA O REGION FRONTERIZA NORTE, POR EMPRESAS COMERCIALES. (DEROGADO)";
            case "C5": return "DEPOSITO FISCAL PARA LA INDUSTRIA AUTOMOTRIZ.";
            case "C9": return "CERTIFICADO DE USO FINAL.";
            case "DC": return "CLASIFICACION DEL CUPO.";
            case "DD": return "DESPACHO A DOMICILIO A LA EXPORTACION.";
            case "DE": return "DESPERDICIOS.";
            case "DN": return "DONACION POR PARTE DE LAS EMPRESAS CON PROGRAMA IMMEX.";
            case "DP": return "INTRODUCCION Y EXTRACCION DE DEPOSITO FISCAL PARA EXPOSICION Y VENTA DE ARTICULOS PROMOCIONALES.";
            case "DR": return "RECTIFICACION POR DISCREPANCIA DOCUMENTAL.";
            case "DS": return "DESTRUCCION DE MERCANCIAS EN DEPOSITO FISCAL PARA LA EXPOSICION Y VENTA.";
            case "DT": return "OPERACIONES SUJETAS AL ART. 303 DEL TLCAN.";
            case "DU": return "OPERACIONES SUJETAS A LOS ARTS. 14 DE LA DECISION O 15 DEL TLCAELC.";
            case "DV": return "VENTA DE MERCANCIAS A MISIONES DIPLOMATICAS Y CONSULARES CUANDO CUENTE CON FRANQUICIA DIPLOMATICA.";
            case "EA": return "EXCEPCION DE AVISO AUTOMATICO DE IMPORTACION/EXPORTACION.";
            case "EB": return "ENVASES Y EMPAQUES.";
            case "EC": return "EXCEPCION DE PAGO DE CUOTA COMPENSATORIA.";
            case "ED": return "DOCUMENTO DIGITALIZADO";
            case "EF": return "ESTIMULO FISCAL.";
            case "EI": return "AUTORIZACION DE DEPOSITO FISCAL TEMPORAL PARA EXPOSICIONES INTERNACIONALES DE MERCANCIAS.";
            case "EM": return "EMPRESA DE MENSAJERIA Y PAQUETERIA.";
            case "EN": return "NO APLICACION DE LA NORMA OFICIAL MEXICANA.";
            case "EP": return "DECLARACION DE CURP.";
            case "EP": return "EXCEPCION DE INSCRIPCION AL PADRON DE IMPORTADORES.";
            case "ES": return "ESTADO DE LA MERCANCIA.";
            case "EX": return "EXENCION DE CUENTA ADUANERA DE GARANTIA.";
            case "FI": return "FACTOR DE ACTUALIZACION CON INDICE NACIONAL DE PRECIOS AL CONSUMIDOR.";
            case "FR": return "FECHA QUE RIGE.";
            case "FV": return "FACTOR DE ACTUALIZACION CON VARIACION CAMBIARIA.";
            case "F8": return "DEPOSITO FISCAL PARA EXPOSICION Y VENTA (MERCANCIAS NACIONALES O NACIONALIZADAS).";
            case "GA": return "CUENTA ADUANERA DE GARANTIA";
            case "IA": return "CERTIFICADO DE APROBACION PARA PRODUCCION DE PARTES AERONAUTICAS";
            case "IC": return "EMPRESA CERTIFICADA";
            case "ID": return "IMPORTACION DEFINITIVA DE VEHICULOS CON AUTORIZACION DE LA ADMINISTRACION GENERAL JURIDICA O EN FRANQUICIA DIPLOMATICA.";
            case "II": return "INVENTARIO INICIAL DE EMPRESAS DENOMINADAS DUTY FREE.";
            case "IM": return "EMPRESAS CON PROGRAMA IMMEX";
            case "IN": return "INCIDENCIA.";
            case "IR": return "RECINTO FISCALIZADO ESTRATEGICO.";
            case "IS": return "MERCANCIAS EXENTAS DE IMPUESTOS AL COMERCIO EXTERIOR";
            case "J4": return "RETORNO DE MERCANCIA DE PROCEDENCIA EXTRANJERA.";
            case "LD": return "DESPACHO POR LUGAR DISTINTO.";
            case "LR": return "IMPORTACION POR PEQUEÑOS CONTRIBUYENTES.";
            case "MA": return "EMBALAJES DE MADERA.";
            case "MB": return "MARBETES Y/O PRECINTOS.";
            case "MC": return "MARCA NOMINATIVA";
            case "MD": return "MENAJE DE DIPLOMATICOS.";
            case "ME": return "MATERIAL DE ENSAMBLE.";
            case "MI": return "IMPORTACION DEFINITIVA DE MUESTRAS AMPARADAS BAJO UN PROTOCOLO DE INVESTIGACION.";
            case "MJ": return "OPERACIONES DE EMPRESAS DE MENSAJERIA Y PAQUETERIA DE MERCANCIAS NO SUJETAS AL PAGO DE IGIE E IVA.";
            case "MM": return "IMPORTACION DEFINITIVA DE MUESTRAS Y MUESTRARIOS.";
            case "MP": return "PEDIMENTO SIMPLIFICADO.";
            case "MR": return "REGISTRO PARA LA TOMA DE MUESTRAS. , peligrosas o para las que se requiera de instalaciones o equipos especiales para la toma de las mismas.";
            case "MS": return "MODALIDAD DE SERVICIOS DE EMPRESAS CON PROGRAMA IMMEX.";
            case "MV": return "AÑO-MODELO DEL VEHICULO.";
            case "M7": return "OPINION FAVORABLE DE LA SE.";
            case "NA": return "MERCANCIAS CON PREFERENCIA ARANCELARIA ALADI SEÑALADAS EN EL ACUERDO.";
            case "NE": return "EXCEPCION DE CUMPLIR CON EL ANEXO 21.";
            case "NR": return "OPERACION EN LA QUE LAS MERCANCIAS NO INGRESAN A RECINTO FISCALIZADO.";
            case "NS": return "EXCEPCION DE INSCRIPCION EN LOS PADRONES DE IMPORTADORES EXPORTADORES SECTORIALES.";
            case "NT": return "NOTA DE TRATADO";
            case "NZ": return "MERCANCIA QUE NO SE HA BENEFICIADO DEL 'SUGAR REEXPORT PROGRAM' DE LOS ESTADOS UNIDOS DE AMERICA.";
            case "OC": return "OPERACIÓN TRAMITADA EN FASE DE CONTINGENCIA DE LA VENTANILLA DIGITAL.";
            case "OE": return "OPERADOR ECONOMICO AUTORIZADO.";
            case "OM": return "MERCANCIA ORIGINARIA DE MEXICO.";
            case "OV": return "OPERACIÓN VULNERABLE.";
            case "PA": return "CUMPLIMIENTO DE LA NORMA OFICIAL MEXICANA, PARA VERIFICARSE EN UN ALMACEN GENERAL DE DEPOSITO AUTORIZADO.";
            case "PB": return "CUMPLIMIENTO DE NORMA OFICIAL MEXICANA PARA SU VERIFICACION DENTRO DEL TERRITORIO NACIONAL, EN UN DOMICILIO PARTICULAR.";
            case "PC": return "PEDIMENTO CONSOLIDADO.";
            case "PD": return "PARTE II.";
            case "PG": return "MERCANCIA PELIGROSA.";
            case "PH": return "PEDIMENTO ELECTRONICO SIMPLIFICADO.";
            case "PI": return "INSPECCION PREVIA.";
            case "PL": return "PRELIBERACION DE MERCANCIAS.";
            case "PM": return "PRESENTACION DE LA MERCANCIA.";
            case "PP": return "PROGRAMA DE PROMOCION SECTORIAL.";
            case "PR": return "PROPORCION DETERMINADA.";
            case "PS": return "SECTOR AUTORIZADO AL AMPARO DE PROSEC.";
            case "PT": return "EXPORTACION O RETORNO DE PRODUCTO TERMINADO.";
            case "PV": return "PRUEBA DE VALOR.";
            case "PZ": return "AMPLIACION DEL PLAZO PARA EL RETORNO DE MERCANCIA IMPORTADA O EXPORTADA TEMPORALMENTE.";
            case "RA": return "RETORNO DE RACKS.";
            case "RC": return "CONSECUTIVO DE FACTURAS O REMESAS.";
            case "RD": return "RETORNO A DEPOSITO FISCAL DE LA INDUSTRIA AUTOMOTRIZ DE MERCANCIA EXPORTADA EN DEFINITIVA.";
            case "RE": return "IMPORTACION DEFINITIVA DE MERCANCIAS (REGULARIZACION).";
            case "RL": return "RESPONSIBLE SOLIDARIO.";
            case "RO": return "REVISION EN ORIGEN POR PARTE DE EMPRESAS CERTIFICADAS.";
            case "RP": return "RETORNO de residuos peligrosos generados por empresas con Programa IMMEX.";
            case "RQ": return "IMPORTACION DEFINITIVA DE REMOLQUES, SEMIRREMOLQUES Y PORTACONTENEDORES.";
            case "RT": return "REEXPEDICION POR TERCEROS.";
            case "SB": return "IMPORTACION DE ORGANISMOS GENETICAMENTE MODIFICADOS.";
            case "SC": return "EXCEPCION DE PAGO DE MEDIDA DE TRANSICION.";
            case "SF": return "CLAVE DE UNIDAD AUTORIZADA DEL ALMACEN GENERAL DE DEPOSITO";
            case "SH": return "AUTORIZACION DEL SAT.";
            case "SH": return "AUTORIZACION DEL SAT.";
            case "SM": return "EXCEPCION DE LA DECLARACION DE MARBETES.";
            case "SP": return "SIN PRESENTACIÓN FÍSICA DEL PEDIMENTO.";
            case "ST": return "OPERACIONES SUJETAS AL ART. 303 DEL TLCAN.";
            case "SU": return "OPERACIONES SUJETAS A LOS ARTICULOS 14 DE LA DECISION O 15 DEL TLCAELC.";
            case "TB": return "TRANSITO INTERNO POR ADUANAS Y MERCANCIAS ESPECIFICAS.";
            case "TC": return "CORRELACION DE LAS FRACCIONES ARANCELARIAS.";
            case "TD": return "TIPO DE DESISTIMIENTO Y RETORNO.";
            case "TF": return "TRANSMISION DE FACTURAS.";
            case "TI": return "TRANSITO INTERFRONTERIZO.";
            case "TL": return "MERCANCIA ORIGINARIA AL AMPARO DE TRATADOS DE LIBRE COMERCIO.";
            case "TM": return "TRANSITO INTERNACIONAL.";
            case "TR": return "TRASPASO DE MERCANCIAS EN DEPOSITO FISCAL.";
            case "TV": return "TOTAL DE MERCANCIA EXTRAIDA DE DEPOSITO FISCAL.";
            case "UM": return "USO DE LA MERCANCIA.";
            case "UP": return "UNIDADES PROTOTIPO.";
            case "VC": return "IMPORTACION DEFINITIVA DE VEHICULOS USADOS EN EL ESTADO DE CHIHUAHUA.";
            case "VF": return "IMPORTACION DEFINITIVA DE VEHICULOS USADOS A LA FRANJA O REGION FRONTERIZA NORTE.";
            case "VJ": return "FRONTERIZACION DE VEHICULOS";
            case "VN": return "IMPORTACION DEFINITIVA DE VEHICULOS NUEVOS.";
            case "VU": return "IMPORTACION DEFINITIVA DE VEHICULOS USADOS.";
            case "VT": return "IMPORTACION DE AUTOBUSES, CAMIONES Y TRACTOCAMIONES USADOS PARA EL TRANSPORTE DE PERSONAS Y MERCANCIAS.";
            case "V1": return "TRANSFERENCIAS DE MERCANCIAS.";
            case "V2": return "TRANSFERENCIA DE MERCANCIAS IMPORTADAS CON CUENTA ADUANERA.";
            case "V4": return "RETORNO VIRTUAL DERIVADO DE LA CONSTANCIA DE TRANSFERENCIA DE MERCANCIAS.";
            case "V5": return "TRANSFERENCIAS DE MERCANCIAS DE EMPRESAS CERTIFICADAS A EMPRESAS RESIDENTES EN EL PAIS.";
            case "V6": return "TRANSFERENCIAS DE MERCANCIAS SUJETAS A CUPO.";
            case "V7": return "TRANSFERENCIAS DEL SECTOR AZUCARERO.";
            case "V8": return "TRANSFERENCIAS DE MERCANCIAS EXTRANJERAS, NACIONALES Y NACIONALIZADAS DE TIENDAS LIBRES DE IMPUESTOS (DUTY FREE).";
            case "V9": return "TRANSFERENCIAS DE MERCANCIAS POR DONACION.";
            case "XP": return "EXCEPCION AL CUMPLIMIENTO DE REGULACIONES Y RESTRICCIONES NO ARANCELARIAS.";
            case "XV": return "EXPORTACIÓN DE VEHÍCULOS DE LA INDUSTRIA AUTOMOTRIZ TERMINAL O MANUFACTURERA DE VEHÍCULOS DE AUTOTRANSPORTE.";
            case "ZC": return "CONTENIDO DE AZUCAR.";
            case "V3": return "EXTRACCION DE DEPOSITO FISCAL DE BIENES PARA SURETORNO O EXPORTACION VIRTUAL (IA).";
            case "RF": return "CUOTA COMPENSATORIA BASADA EN PRECIOS DE REFERENCIA";
            case "MT": return "MONTO TOTAL DEL VALOR EN DÓLARES A EJERCER POR MERCANCÍA TEXTIL";
            case "TU": return "TRANSFERENCIA DE MERCANCIAS (OPERACIONES VIRTUALES), CON PEDIMENTO UNICO";
            case "AV": return "AVISO ELECTRONICO DE IMPORTACION Y EXPORTACION";
            case "HC": return "OPERACIONES DEL SECTOR DE HIDROCARBUROS.";
            case "GS": return "EXPORTACION TEMPORAL Y RETORNO DE DISPOSITIVOS ELECTRONICOS QUE ESTABLECE LA REGLA 3.7.34";
            case "XL": return "PRESENTACION DE MERCANCIA EN TRANSPORTE SSOBREDIMENSIONADO";
            default:
                return "UNDEFINED";
        }
    }

    public function clavePedimento($value) {
        switch ($value) {
            case "A1": return "IMPORTACION O EXPORTACION DEFINITIVA.";
            case "A3": return "REGULARIZACION DE MERCANCIAS (IMPORTACION DEFINITIVA).";
            case "C1": return "IMPORTACION DEFINITIVA A LA FRANJA FRONTERIZA NORTE Y REGION FRONTERIZA AL AMPARO DEL DECRETO DE LA FRANJA O REGION FRONTERIZA (DOF 24/12/2008 Y SUS POSTERIORES MODIFICACIONES).";
            case "C2": return "IMPORTACION DEFINITIVA DE VEHICULOS A LA FRANJA FRONTERIZA NORTE, A LOS ESTADOS DE BAJA CALIFORNIA Y BAJA CALIFORNIA SUR, A LA REGION PARCIAL DEL ESTADO DE SONORA Y A LOS MUNICIPIOS DE CANANEA Y CABORCA, ESTADO DE SONORA.";
            case "D1": return "RETORNO POR SUSTITUCION.";
            case "K1": return "DESISTIMIENTO DE REGIMEN Y RETORNO DE MERCANCIAS POR DEVOLUCION.";
            case "L1": return "PEQUEÑA IMPORTACION DEFINITIVA.";
            case "P1": return "REEXPEDICION DE MERCANCIAS DE FRANJA FRONTERIZA O REGION FRONTERIZA AL INTERIOR DEL PAIS.";
            case "S2": return "IMPORTACION Y EXPORTACION DE MERCANCIAS PARA RETORNAR EN SU MISMO ESTADO (ARTICULO 86 DE LA LEY).";
            case "T1": return "IMPORTACION Y EXPORTACION POR EMPRESAS DE MENSAJERIA.";
            case "VF": return "IMPORTACION DEFINITIVA DE VEHICULOS USADOS A LA FRANJA O REGION FRONTERIZA NORTE.";
            case "VU": return "IMPORTACION DEFINITIVA DE VEHICULOS USADOS.";
            case "V1": return "TRANSFERENCIAS DE MERCANCIAS (IMPORTACION TEMPORAL VIRTUAL; INTRODUCCION VIRTUAL A DEPOSITO FISCAL O A RECINTO FISCALIZADO ESTRATEGICO; RETORNO VIRTUAL; EXPORTACION VIRTUAL DE PROVEEDORES NACIONALES).";
            case "V2": return "TRANSFERENCIAS DE MERCANCIAS IMPORTADAS CON CUENTA ADUANERA (EXPORTACION E IMPORTACION VIRTUAL).";
            case "V5": return "TRANSFERENCIAS DE MERCANCIAS DE EMPRESAS CERTIFICADAS (RETORNO VIRTUAL PARA IMPORTACION DEFINITIVA).";
            case "V6": return "TRANSFERENCIAS DE MERCANCIAS SUJETAS A CUPO (IMPORTACION DEFINITIVA Y RETORNO VIRTUAL).";
            case "V7": return "TRANSFERENCIAS DEL SECTOR AZUCARERO (EXPORTACION VIRTUAL E IMPORTACION TEMPORAL VIRTUAL).";
            case "V9": return "TRANSFERENCIAS DE MERCANCIAS POR DONACION (IMPORTACION DEFINITIVA Y RETORNOVIRTUAL).";
            case "VD": return "VIRTUALES DIVERSOS.";
            case "AD": return "IMPORTACION TEMPORAL DE MERCANCIAS DESTINADAS A CONVENCIONES Y CONGRESOS INTERNACIONALES (ARTICULO 106, FRACCION III, INCISO A) DE LA LEY).";
            case "AJ": return "IMPORTACION Y EXPORTACION TEMPORAL DE ENVASES DE MERCANCIAS (ARTICULO 106, FRACCION II, INCISO B) Y 116 FRACCION II, INCISO A) DE LA LEY).";
            case "BA": return "IMPORTACION Y EXPORTACION TEMPORAL DE BIENES PARA SER RETORNADOS EN SU MISMO ESTADO. (ARTICULO 106, FRACCION II, INCISO A) Y FRACCIÓN IV, INCISO B) DE LA LEY).";
            case "BB": return "EXPORTACION, IMPORTACION Y RETORNOS VIRTUALES.";
            case "BC": return "IMPORTACION Y EXPORTACION TEMPORAL DE MERCANCIAS DESTINADAS A EVENTOS CULTURALES O DEPORTIVOS (ARTICULO 106, FRACCION III, INCISO B DE LA LEY).";
            case "BD": return "IMPORTACION Y EXPORTACION TEMPORAL DE EQUIPO PARA FILMACION (ARTICULOS 106, FRACCION III, INCISO C) Y 116, FRACCION II INCISO D) DE LA LEY).";
            case "BE": return "IMPORTACION Y EXPORTACION TEMPORAL DE VEHICULOS DE PRUEBA (ARTICULO 106, FRACCION III, INCISO D) DE LA LEY).";
            case "BF": return " EXPORTACION TEMPORAL DE MERCANCIAS DESTINADAS A EXPOSICIONES, CONVENCIONES O EVENTOS CULTURALES O DEPORTIVOS (ARTICULO 116, FRACCION III DE LA LEY).";
            case "BH": return "IMPORTACION TEMPORAL DE CONTENEDORES, AVIONES, HELICOPTEROS, EMBARCACIONES Y CARROS DE FERROCARRIL (ARTICULO 106, FRACCION V, INCISOS A), B) Y E) DE LA LEY).";
            case "BI": return "IMPORTACION TEMPORAL (ARTICULO 106, FRACCION III, INCISO E) DE LA LEY).";
            case "BM": return "EXPORTACION TEMPORAL DE MERCANCIAS PARA SU TRANSFORMACION, ELABORACION O REPARACION (ARTICULO 117 DE LA LEY).";
            case "BO": return "EXPORTACION TEMPORAL PARA REPARACION O SUSTITUCION Y RETORNO AL PAIS (IMMEX, RFE Y EMPRESAS CERTIFICADAS).";
            case "BP": return "IMPORTACION Y EXPORTACION TEMPORAL DE MUESTRAS O MUESTRARIOS (ARTICULOS 106, FRACCION II, INCISO D) Y 116, FRACCION II, INCISO C) DE LA LEY).";
            case "BR": return "EXPORTACION TEMPORAL Y RETORNO DE MERCANCIAS FUNGIBLES.";
            case "H1": return " RETORNO DE MERCANCIAS EN SU MISMO ESTADO.";
            case "H8": return "RETORNO DE ENVASES.";
            case "I1": return "IMPORTACION, EXPORTACION Y RETORNO DE MERCANCIAS ELABORADAS, TRANSFORMADAS O REPARADAS.";
            case "F4": return "CAMBIO DE REGIMEN DE INSUMOS O DE MERCANCIA EXPORTADA TEMPORALMENTE.";
            case "F5": return "CAMBIO DE REGIMEN DE MERCANCÍAS DE IMPORTACIÓN TEMPORAL A DEFINITIVA.";
            case "IN": return "IMPORTACION TEMPORAL DE BIENES QUE SERAN SUJETOS A TRANSFORMACION, ELABORACION O REPARACION (IMMEX).";
            case "AF": return "IMPORTACION TEMPORAL DE BIENES DE ACTIVO FIJO (IMMEX).";
            case "RT": return "RETORNO DE MERCANCIAS (IMMEX).";
            case "A4": return "INTRODUCCION PARA DEPOSITO FISCAL (AGD).";
            case "E1": return "EXTRACCION DE DEPOSITO FISCAL DE BIENES QUE SERAN SUJETOS A TRANSFORMACION, ELABORACION O REPARACION (AGD).";
            case "E2": return "EXTRACCION DE DEPOSITO FISCAL DE BIENES DE ACTIVO FIJO (AGD).";
            case "G1": return "EXTRACCION DE DEPOSITO FISCAL (AGD).";
            case "C3": return "EXTRACCION DE DEPOSITO FISCAL DE FRANJA O REGION FRONTERIZA (AGD).";
            case "K2": return "EXTRACCION DE DEPOSITO FISCAL POR DESISTIMIENTO O TRANSFERENCIAS (AGD).";
            case "A5": return "INTRODUCCION A DEPOSITO FISCAL EN LOCAL AUTORIZADO.";
            case "E3": return "EXTRACCION DE DEPOSITO FISCAL EN LOCAL AUTORIZADO (INSUMOS).";
            case "E4": return "EXTRACCION DE DEPOSITO FISCAL EN LOCAL. AUTORIZADO (ACTIVO FIJO).";
            case "G2": return "EXTRACCION DE DEPOSITO FISCAL EN LOCAL AUTORIZADO PARA SU IMPORTACION DEFINITIVA.";
            case "K3": return "EXTRACCION DE DEPOSITO FISCAL EN LOCAL AUTORIZADO PARA RETORNO O TRANSFERENCIA.";
            case "F2": return "INTRODUCCION A DEPOSITO FISCAL (IA).";
            case "F3": return "EXTRACCION DE DEPOSITO FISCAL (IA).";
            case "V3": return "EXTRACCION DE DEPOSITO FISCAL DE BIENES PARA SU RETORNO VIRTUAL (IA).";
            case "V4": return "RETORNO VIRTUAL DERIVADO DE LA CONSTANCIA DE TRANSFERENCIA DE MERCANCIAS (IA).";
            case "F8": return "INTRODUCCION Y EXTRACCION DE DEPOSITO FISCAL DE MERCANCIAS NACIONALES O NACIONALIZADAS EN TIENDAS LIBRES DE IMPUESTOS (DUTY FREE).";
            case "F9": return "INTRODUCCION Y EXTRACCION DE DEPOSITO FISCAL DE MERCANCIAS EXTRANJERAS PARA EXPOSICION Y VENTA DE MERCANCIAS EN TIENDAS LIBRES DE IMPUESTOS (DUTY FREE).";
            case "G6": return "INFORME DE EXTRACCION DE DEPOSITO FISCAL DE MERCANCIAS NACIONALES O NACIONALIZADAS VENDIDAS EN TIENDAS LIBRES DE IMPUESTOS (DUTY FREE).";
            case "G7": return "INFORME DE EXTRACCION DE DEPOSITO FISCAL DE MERCANCIAS EXTRANJERAS VENDIDAS EN TIENDAS LIBRES DE IMPUESTOS (DUTY FREE).";
            case "V8": return "TRANSFERENCIA DE MERCANCIAS EN DEPOSITO FISCAL PARA LA EXPOSICION Y VENTA DE MERCANCIAS EXTRANJERAS, NACIONALES Y NACIONALIZADAS DE TIENDAS LIBRES DE IMPUESTOS (DUTY FREE).";
            case "M1": return "INTRODUCCION Y EXPORTACION DE INSUMOS.";
            case "M2": return "INTRODUCCION Y EXPORTACION DE MAQUINARIA Y EQUIPO.";
            case "J3": return "RETORNO Y EXPORTACION DE INSUMOS ELABORADOS O TRANSFORMADOS EN RECINTO FISCALIZADO.";
            case "M3": return "INTRODUCCION DE MERCANCIAS (RFE).";
            case "M4": return "INTRODUCCION DE ACTIVO FIJO (RFE).";
            case "J4": return "RETORNO DE MERCANCIAS EXTRANJERAS (RFE).";
            case "T3": return "TRANSITO INTERNO.";
            case "T6": return "TRANSITO INTERNACIONAL POR TERRITORIO EXTRANJERO.";
            case "T7": return "TRANSITO INTERNACIONAL POR TERRITORIO NACIONAL.";
            case "T9": return "TRANSITO INTERNACIONAL DE TRANSMIGRANTES.";
            case "R1": return "RECTIFICACION DE PEDIMENTOS.";
            case "CT": return "PEDIMENTO COMPLEMENTARIO.";
            case "GC": return "GLOBAL COMPLEMENTARIO.";
            case "G8": return "REINCORPORAR AL MERCADO NACIONAL (REF).";
            case "M5": return "INTRODUCCIÓN DE MERCANCÍA NACIONAL O NACIONALIZADA  (RFE).";
            default:
                return "UNDEFINED";
        }
    }

    public function tipoContri($value) {
        switch ($value) {
            case 1:
                return "DTA";
            case 15:
                return "PREVALIDAAAA";
            case 21:
                return "CONTRAPRESTA";
            default:
                return "UNDEFINED";
        }
    }
    
    public function destinoMercancia($value) {
        switch ($value) {
            case 1: return "ESTADO DE BAJA CALIFORNIA Y PARCIAL DE SONORA.";
            case 2: return "ESTADO DE BAJA CALIFORNIA SUR.";
            case 3: return "ESTADO DE QUINTANA ROO.";
            case 5: return "MUNICIPIO DE SALINA CRUZ, OAX.";
            case 6: return "MUNICIPIO DE CANANEA, SON.";
            case 7: return "FRANJA FRONTERIZA NORTE.";
            case 8: return "FRANJA FRONTERIZA SUR, COLINDANTE CON GUATEMALA.";
            case 9: return "INTERIOR DEL PAIS.";
            case 10: return "MUNICIPIO DE CABORCA, SON.";
            default:
                return "UNDEFINED";
        }
    }

    public function regimen($value) {
        switch ($value) {
            case "IMD": return "DEFINITIVO DE IMPORTACION.";
            case "EXD": return "DEFINITIVO DE EXPORTACION.";
            case "ITR": return "TEMPORALES DE IMPORTACION PARA RETORNAR AL EXTRANJERO EN EL MISMO ESTADO.";
            case "ITE": return "TEMPORALES DE IMPORTACION PARA ELABORACION, TRANSFORMACION O REPARACION PARA EMPRESAS CON PROGRAMA IMMEX.";
            case "ETR": return "TEMPORALES DE EXPORTACION PARA RETORNAR AL PAIS EN EL MISMO ESTADO.";
            case "ETE": return "TEMPORALES DE EXPORTACION PARA ELABORACION, TRANSFORMACION O REPARACION.";
            case "DFI": return "DEPOSITO FISCAL.";
            case "RFE": return "ELABORACION, TRANSFORMACION O REPARACION EN RECINTO FISCALIZADO.";
            case "TRA": return "TRANSITOS.";
            case "RFS": return "RECINTO FISCALIZADO ESTRATEGICO";
            default:
                return "UNDEFINED";
        }
    }

    public function tipoFacturacion($value) {
        switch ($value) {
            case "CFR": return "COSTE Y FLETE (PUERTO DE DESTINO CONVENIDO).";
            case "CIF": return "COSTE, SEGURO Y FLETE (PUERTO DE DESTINO CONVENIDO).";
            case "CPT": return "TRANSPORTE PAGADO HASTA (EL LUGAR DE DESTINO CONVENIDO).";
            case "CIP": return "TRANSPORTE Y SEGURO PAGADOS HASTA (LUGAR DE DESTINO CONVENIDO).";
            case "DAF": return "ENTREGADA EN FRONTERA (LUGAR CONVENIDO).";
            case "DAP": return "ENTREGADA EN LUGAR.";
            case "DAT": return "ENTREGADA EN TERMINAL.";
            case "DES": return "ENTREGADA SOBRE BUQUE (PUERTO DE DESTINO CONVENIDO).";
            case "DEQ": return "ENTREGADA EN MUELLE (PUERTO DE DESTINO CONVENIDO).";
            case "DDU": return "ENTREGADA DERECHOS NO PAGADOS (LUGAR DE DESTINO CONVENIDO).";
            case "DDP": return "ENTREGADA DERECHOS PAGADOS (LUGAR DE DESTINO CONVENIDO).";
            case "EXW": return "EN FABRICA (LUGAR CONVENIDO).";
            case "FCA": return "FRANCO TRANSPORTISTA (LUGAR DESIGNADO).";
            case "FAS": return "FRANCO AL COSTADO DEL BUQUE (PUERTO DE CARGA CONVENIDO).";
            case "FOB": return "FRANCO A BORDO (PUERTO DE CARGA CONVENIDO).";
            default:
                return "UNDEFINED";
        }
    }

    public function tipoTasa($value) {
        switch ($value) {
            case 1: return "PORCENTUAL.";
            case 2: return "ESPECIFICO.";
            case 3: return "CUOTA MINIMA (DTA).";
            case 4: return "CUOTA FIJA.";
            case 5: return "TASA DE DESCUENTO SOBRE AD VALOREM.";
            case 6: return "FACTOR DE APLICACION SOBRE TIGIE.";
            case 7: return "AL MILLAR (DTA).";
            case 8: return "TASA DE DESCUENTO SOBRE EL ARANCEL ESPECIFICO.";
            case 9: return "TASA ESPECIFICA SOBRE PRECIOS DE REFERENCIA.";
            case 10: return "TASA ESPECIFICA SOBRE PRECIOS DE REFERENCIA CON UM.";
            default:
                return "UNDEFINED";
        }
    }

    public function metodoValoracion($value) {
        switch ($value) {
            case 0: return "VALOR COMERCIAL (CLAVE USADA SOLO A LA EXPORTACION).";
            case 1: return "VALOR DE TRANSACCION DE LAS MERCANCIAS.";
            case 2: return "VALOR DE TRANSACCION DE MERCANCIAS IDENTICAS.";
            case 3: return "VALOR DE TRANSACCION DE MERCANCIAS SIMILARES.";
            case 4: return "VALOR DE PRECIO UNITARIO DE VENTA.";
            case 5: return "VALOR RECONSTRUIDO.";
            case 6: return "ULTIMO RECURSO.";
            case 7: return "DECLARACIÓN DE VALOR PROVISIONAL CONFORME A LA REGLA 1.5.3.";
            default:
                return "UNDEFINED";
        }
    }

    public function contribucion($value) {
        switch ($value) {
            case 1: return array("descripcion" => "DERECHO DE TRAMITE ADUANERO.", "abreviacion" => "DTA");
            case 2: return array("descripcion" => "CUOTAS COMPENSATORIAS.", "abreviacion" => "C.C.");
            case 3: return array("descripcion" => "IMPUESTO AL VALOR AGREGADO.", "abreviacion" => "IVA");
            case 4: return array("descripcion" => "IMPUESTO SOBRE AUTOMOVILES NUEVOS.", "abreviacion" => "ISAN");
            case 5: return array("descripcion" => "IMPUESTO SOBRE PRODUCCION Y SERVICIOS.", "abreviacion" => "IEPS");
            case 6: return array("descripcion" => "IMPUESTO GENERAL DE IMPORTACION/EXPORTACION.", "abreviacion" => "IGI/IGE");
            case 7: return array("descripcion" => "RECARGOS.", "abreviacion" => "REC.");
            case 9: return array("descripcion" => "OTROS.", "abreviacion" => "OTROS");
            case 11: return array("descripcion" => "MULTAS.", "abreviacion" => "MULT.");
            case 12: return array("descripcion" => "CONTRIBUCIONES POR APLICACION DEL ART. 303 DEL TLCAN.", "abreviacion" => "303");
            case 13: return array("descripcion" => "RECARGOS POR APLICACION DEL ART. 303 DEL TLCAN.", "abreviacion" => "RT");
            case 14: return array("descripcion" => "BIENES Y SERVICIOS SUNTUARIOS.", "abreviacion" => "BSS");
            case 15: return array("descripcion" => "PREVALIDACION.", "abreviacion" => "PRV");
            case 16: return array("descripcion" => "CONTRIBUCIONES POR APLICACION DE LOS ARTICULOS 14 DE LA DECISION Y 15 DEL TLCAELC.", "abreviacion" => "EUR");
            case 17: return array("descripcion" => "RECARGOS POR APLICACION DE LOS ARTICULOS 14 DE LA DECISION Y 15 DEL TLCAELC.", "abreviacion" => "REU");
            case 18: return array("descripcion" => "EXPEDICION DE CERTIFICADO DE IMPORTACION (SAGAR).", "abreviacion" => "ECI");
            case 19: return array("descripcion" => "IMPUESTO SOBRE TENENCIA Y USO DE VEHICULOS.", "abreviacion" => "ITV");
            case 20: return array("descripcion" => "MEDIDA DE TRANSICION.", "abreviacion" => "MT");
            case 21: return array("descripcion" => "CONTRAPRESTACIÓN PARA EFECTOS DE LA PREVALIDACION.", "abreviacion" => "CNT");
            case 50: return array("descripcion" => "DIFERENCIA A FAVOR DEL CONTRIBUYENTE.", "abreviacion" => "DFC");
            default:
                return "UNDEFINED";
        }
    }

    public function tipoTransporte($value) {
        switch ($value) {
            case 1: return "CONTENEDOR ESTANDAR 20' (STANDARD CONTAINER 20').";
            case 2: return "CONTENEDOR ESTANDAR 40' (STANDARD CONTAINER 40').";
            case 3: return "CONTENEDOR ESTANDAR DE CUBO ALTO 40' (HIGH CUBE STANDARD CONTAINER 40').";
            case 4: return "CONTENEDOR TAPA DURA 20' (HARDTOP CONTAINER 20').";
            case 5: return "CONTENEDOR TAPA DURA 40' (HARDTOP CONTAINER 40').";
            case 6: return "CONTENEDOR TAPA ABIERTA 20' (OPEN TOP CONTAINER 20').";
            case 7: return "CONTENEDOR TAPA ABIERTA 40' (OPEN TOP CONTAINER 40').";
            case 8: return "FLAT 20' (FLAT 20').";
            case 9: return "FLAT 40' (FLAT 40').";
            case 10: return "PLATAFORMA 20' (PLATFORM 20').";
            case 11: return "PLATAFORMA 40' (PLATFORM 40').";
            case 12: return "CONTENEDOR VENTILADO 20' (VENTILATED CONTAINER 20').";
            case 13: return "CONTENEDOR TERMICO 20' (INSULATED CONTAINER 20').";
            case 14: return "CONTENEDOR TERMICO 40' (INSULATED CONTAINER 40').";
            case 15: return "CONTENEDOR REFRIGERANTE 20' (REFRIGERATED CONTAINER 20').";
            case 16: return "CONTENEDOR REFRIGERANTE 40' (REFRIGERATED CONTAINER 40').";
            case 17: return "CONTENEDOR REFRIGERANTE CUBO ALTO 40' (HIGH CUBE REFRIGERATED CONTAINER 40').";
            case 18: return "CONTENEDOR CARGA A GRANEL 20' (BULK CONTAINER 20').";
            case 19: return "CONTENEDOR TIPO TANQUE 20' (TANK CONTAINER 20').";
            case 20: return "CONTENEDOR ESTANDAR 45' (STANDARD CONTAINER 45').";
            case 21: return "CONTENEDOR ESTANDAR 48' (STANDARD CONTAINER 48').";
            case 22: return "CONTENEDOR ESTANDAR 53' (STANDARD CONTAINER 53').";
            case 23: return "CONTENEDOR ESTANDAR 8' (STANDARD CONTAINER 8').";
            case 24: return "CONTENEDOR ESTANDAR 10' (STANDARD CONTAINER 10').";
            case 25: return "CONTENEDOR ESTANDAR DE CUBO ALTO 45' (HIGH CUBE STANDARD CONTAINER 45').";
            case 26: return "SEMIRREMOLQUE CON RACKS PARA ENVASES DE BEBIDAS.";
            case 27: return "SEMIRREMOLQUE CUELLO DE GANZO.";
            case 28: return "SEMIRREMOLQUE TOLVA CUBIERTO.";
            case 29: return "SEMIRREMOLQUE TOLVA (ABIERTO).";
            case 30: return "AUTO-TOLVA CUBIERTO/DESCARGA NEUMATICA.";
            case 31: return "SEMIRREMOLQUE CHASIS.";
            case 32: return "SEMIRREMOLQUE AUTOCARGABLE (CON SISTEMA DE ELEVACION).";
            case 33: return "SEMIRREMOLQUE CON TEMPERATURA CONTROLADA.";
            case 34: return "SEMIRREMOLQUE CORTO TRASERO.";
            case 35: return "SEMIRREMOLQUE DE CAMA BAJA.";
            case 36: return "PLATAFORMA DE 28'.";
            case 37: return "PLATAFORMA DE 45'.";
            case 38: return "PLATAFORMA DE 48'.";
            case 39: return "SEMIRREMOLQUE PARA TRANSPORTE DE CABALLOS.";
            case 40: return "SEMIRREMOLQUE PARA TRANSPORTE DE GANADO.";
            case 41: return "SEMIRREMOLQUE TANQUE (LIQUIDOS)/SIN CALEFACCION/SIN AISLAR.";
            case 42: return "SEMIRREOLQUE TANQUE (LIQUIDOS)/CON CALEFACCION/SIN AISLAR.";
            case 43: return "SEMIRREMOLQUE TANQUE (LIQUIDOS)/SIN CALEFACCION/AISLADO.";
            case 44: return "SEMIRREMOLQUE TANQUE (LIQUIDOS)/CON CALEFACCION/AISLADO.";
            case 45: return "SEMIRREMOLQUE TANQUE (GAS)/SIN CALEFACCION/SIN AISLAR.";
            case 46: return "SEMIRREMOLQUE TANQUE (GAS)/CON CALEFACCION/SIN AISLAR.";
            case 47: return "SEMIRREMOLQUE TANQUE (GAS)/SIN CALEFACCION/AISLADO.";
            case 48: return "SEMIRREMOLQUE TANQUE (GAS)/CON CALEFACCION/AISLADO.";
            case 49: return "SEMIRREMOLQUE TANQUE (QUIMICOS)/SIN CALEFACCION/SIN AISLAR.";
            case 50: return "SEMIRREMOLQUE TANQUE (QUIMICOS)/CON CALEFACCION/SIN AISLAR.";
            case 51: return "SEMIRREMOLQUE TANQUE (QUIMICOS)/SIN CALEFACCION/AISLADO.";
            case 52: return "SEMIRREMOLQUE TANQUE (QUIMICOS)/CON CALEFACCION/AISLADO.";
            case 53: return "SEMIRREMOLQUE GONDOLA-CERRADA.";
            case 54: return "SEMIRREMOLQUE GONDOLA-ABIERTA.";
            case 55: return "SEMIRREMOLQUE TIPO CAJA CERRADA 48'.";
            case 56: return "SEMIRREMOLQUE TIPO CAJA CERRADA 53'.";
            case 57: return "SEMIRREMOLQUE TIPO CAJA REFRIGERADA 48'.";
            case 58: return "SEMIRREMOLQUE TIPO CAJA REFRIGERADA 53'.";
            case 59: return "DOBLE SEMIRREMOLQUE.";
            case 60: return "OTROS.";
            case 61: return "TANQUE 20'.";
            case 62: return "TANQUE 40'.";
            case 63: return "CARRO DE FERROCARRIL";
            case 64: return "HIGH CUBE 20'";
            default:
                return "UNDEFINED";
        }
    }

    public function medioTransporte($value) {
        switch ($value) {
            case 1: return "MARITIMO.";
            case 2: return "FERROVIARIO DE DOBLE ESTIBA.";
            case 3: return "CARRETERO-FERROVIARIO.";
            case 4: return "AEREO.";
            case 5: return "POSTAL.";
            case 6: return "FERROVIARIO.";
            case 7: return "CARRETERO.";
            case 8: return "TUBERIA.";
            case 10: return "CABLES.";
            case 11: return "DUCTOS.";
            case 98: return "NO SE DECLARA MEDIO DE TRANSPORTE POR NO HABER PRESENTACION FISICA DE MERCANCIAS ANTE LA ADUANA.";
            case 99: return "OTROS.";
            default:
                return "UNDEFINED";
        }
    }

    public function tipoFecha($value) {
        switch ($value) {
            case 1: return "FECHA DE ENTRADA A TERRITORIO NACIONAL";
            case 2: return "FECHA DE PAGO DE LAS CONTRIBUCIONES Y CUOTAS COMPENSATORIAS O MEDIDA DE TRANSICIÓN";
            case 3: return "FECHA DE EXTRACCIÓN DE DEPÓSITO FISCAL";
            case 5: return "FECHA DE PRESENTACIÓN";
            case 6: return "FECHA DE IMPORTACIÓN A EUA/CAN A CANADÁ";
            case 7: return "FECHA DE PAGO DEL PEDIMENTO ORIGINAL";
            default:
                return "UNDEFINED";
        }
    }
    
    public function unidades($value) {
        switch ($value) {
            case 1: return "KILO";
            case 2: return "GRAMO";
            case 3: return "METRO LINEAL";
            case 4: return "METRO CUADRADO";
            case 5: return "METRO CUBICO";
            case 6: return "PIEZA";
            case 7: return "CABEZA";
            case 8: return "LITRO";
            case 9: return "PAR";
            case 10: return "KILOWATT";
            case 11: return "MILLAR";
            case 12: return "JUEGO";
            case 13: return "KILOWATT/HORA";
            case 14: return "TONELADA";
            case 15: return "BARRIL";
            case 16: return "GRAMO NETO";
            case 17: return "DECENAS";
            case 18: return "CIENTOS";
            case 19: return "DOCENAS";
            case 20: return "CAJA";
            case 21: return "BOTELLA";
            default:
                return "UNDEFINED";
        }
    }
    
    public function vinculacion($value) {
        switch ($value) {
            case 0: return "NO EXISTE VINCULACIÓN";
            case 1: return "SÍ EXISTE VINCULACIÓN Y NO AFECTA EL VALOR DE LA ADUANA";
            case 2: return "SÍ EXISTE VINCULACIÓN Y AFECTA EL VALOR EN ADUANA";
            default:
                return "UNDEFINED";
        }
    }

    public function formaPago($value) {
        switch ($value) {
            case 0: return "EFECTIVO";
            case 2: return "FIANZA";
            case 4: return "DEPOSITO EN CUENTA ADUANERA";
            case 5: return "TEMPORAL NO SUJETA A IMPUESTOS";
            case 6: return "PENDIENTE DE PAGO";
            case 7: return "CARGO A PARTIDA PRESUPUESTAL GOBIERNO FEDERAL";
            case 8: return "FRANQUICIA";
            case 9: return "EXENTO DE PAGO";
            case 10: return "CERTIFICADOS ESPECIALES DE TESORERIA PUBLICO";
            case 11: return "CERTIFICADOS ESPECIALES DE TESORERIA PRIVADO";
            case 12: return "COMPENSACION";
            case 13: return "PAGO YA EFECTUADO";
            case 14: return "CONDONACIONES";
            case 15: return "CUENTAS ADUANERAS DE GARANTIA POR PRECIOS ESTIMADOS";
            case 16: return "ACREDITAMIENTO";
            case 18: return "ESTIMULO FISCAL";
            case 19: return "OTROS MEDIOS DE GARANTIA";
            case 20: return "PAGO CONFORME AL ARTICULO 7 DE LA LEY DE INGRESOS DE LA FEDERACION, VIGENTE";
            case 21: return "CRÉDITO EN IVA E IEPS";
            case 22: return "GARANTÍA EN IVA E IEPS";
            default:
                return "UNDEFINED";
        }
    }
    
    public function monedas($value) {
        switch ($value) {
            case "XOF": return "FRANCO";
            case "ALL": return "LEK";
            case "EUR": return "EURO";
            case "ANG": return "FLORIN";
            case "SAR": return "RIYAL";
            case "DZD": return "DINAR";
            case "ARP": return "PESO";
            case "AUD": return "DOLAR";
            case "EUR": return "EURO";
            case "BSD": return "DOLAR";
            case "BHD": return "DINAR";
            case "BBD": return "DOLAR";
            case "EUR": return "EURO";
            case "BZD": return "DOLAR";
            case "BMD": return "DOLAR";
            case "BOP": return "BOLIVIANO";
            case "BRC": return "REAL";
            case "BGL": return "LEV";
            case "CAD": return "DOLAR";
            case "CLP": return "PESO";
            case "CNY": return "YUAN CONTINENTAL";
            case "CNE": return "YUAN EXTRACONTINENTAL";
            case "EUR": return "EURO";
            case "COP": return "PESO";
            case "KPW": return "WON";
            case "KRW": return "WON";
            case "CRC": return "COLON";
            case "CUP": return "PESO";
            case "DKK": return "CORONA";
            case "ECS": return "DOLAR";
            case "EGP": return "LIBRA";
            case "SVC": return "COLON";
            case "AED": return "DIRHAM";
            case "EUR": return "EURO";
            case "EUR": return "EURO";
            case "EUR": return "EURO";
            case "ETB": return "BIRR";
            case "USD": return "DOLAR";
            case "RUR": return "RUBLO";
            case "FJD": return "DOLAR";
            case "PHP": return "PESO";
            case "EUR": return "EURO";
            case "EUR": return "EURO";
            case "GHC": return "CEDI";
            case "STG": return "LIBRA ESTERLINA";
            case "EUR": return "EURO";
            case "GTO": return "QUETZAL";
            case "GYD": return "DOLAR";
            case "HTG": return "GOURDE";
            case "EUR": return "EURO";
            case "HNL": return "LEMPIRA";
            case "HKD": return "DOLAR";
            case "HUF": return "FORIN";
            case "INR": return "RUPIA";
            case "IDR": return "RUPIA";
            case "IQD": return "DINAR";
            case "IRR": return "RIYAL";
            case "EUR": return "EURO";
            case "ISK": return "CORONA";
            case "ILS": return "SHEKEL";
            case "EUR": return "EURO";
            case "JMD": return "DOLAR";
            case "JPY": return "YEN";
            case "JOD": return "DINAR";
            case "KES": return "CHELIN";
            case "KWD": return "DINAR";
            case "EUR": return "EURO";
            case "LBP": return "LIBRA";
            case "LYD": return "DINAR";
            case "LTT": return "LITAS";
            case "EUR": return "EURO";
            case "MYR": return "RINGGIT";
            case "EUR": return "EURO";
            case "MAD": return "DIRHAM";
            case "MXP": return "PESO";
            case "EUR": return "EURO";
            case "NIC": return "CORDOBA";
            case "NGN": return "NAIRA";
            case "NOK": return "CORONA";
            case "NZD": return "DOLAR";
            case "PKR": return "RUPIA";
            case "ILS": return "SHEKEL";
            case "PAB": return "BALBOA";
            case "PYG": return "GUARANI";
            case "PES": return "N. SOL";
            case "PLZ": return "ZLOTY";
            case "EUR": return "EURO";
            case "USD": return "DOLAR";
            case "CSK": return "CORONA";
            case "ZRZ": return "FRANCO";
            case "RSD": return "DINAR";
            case "DOP": return "PESO";
            case "EUR": return "EURO";
            case "ROL": return "LEU";
            case "SGD": return "DOLAR";
            case "SYP": return "LIBRA";
            case "LKR": return "RUPIA";
            case "SEK": return "CORONA";
            case "CHF": return "FRANCO";
            case "SRG": return "DOLAR";
            case "THB": return "BAHT";
            case "TWD": return "NUEVO DOLAR";
            case "TZS": return "CHELIN";
            case "TTD": return "DOLAR";
            case "TRL": return "LIRA";
            case "UAK": return "HRYVNA";
            case "ZAR": return "RAND";
            case "UYP": return "PESO";
            case "EUR": return "EURO";
            case "VEB": return "BOLIVAR FUERTE";
            case "VND": return "DONG";
            case "YDD": return "RIAL";
            case "YUD": return "DINAR";
            case "XXX": return "OTRAS MONEDAS";
            default:
                return "UNDEFINED";
        }
    }
    
    public function pais($value) {
        switch ($value) {
            case "AFG": return "AFGANISTAN (EMIRATO ISLAMICO DE)";
            case "ALB": return "ALBANIA (REPUBLICA DE)";
            case "DEU": return "ALEMANIA (REPUBLICA FEDERAL DE)";
            case "AND": return "ANDORRA (PRINCIPADO DE)";
            case "AGO": return "ANGOLA (REPUBLICA DE)";
            case "AIA": return "ANGUILA";
            case "ATA": return "ANTARTIDA";
            case "ATG": return "ANTIGUA Y BARBUDA (COMUNIDAD BRITANICA DE NACIONES)";
            case "ANT": return "ANTILLAS NEERLANDESAS (TERRITORIO HOLANDES DE ULTRAMAR)";
            case "SAU": return "ARABIA SAUDITA (REINO DE)";
            case "DZA": return "ARGELIA (REPUBLICA DEMOCRATICA Y POPULAR DE)";
            case "ARG": return "ARGENTINA (REPUBLICA)";
            case "ARM": return "ARMENIA (REPUBLICA DE)";
            case "ABW": return "ARUBA (TERRITORIO HOLANDES DE ULTRAMAR)";
            case "AUS": return "AUSTRALIA (COMUNIDAD DE)";
            case "AUT": return "AUSTRIA (REPUBLICA DE)";
            case "AZE": return "AZERBAIJAN (REPUBLICA AZERBAIJANI)";
            case "BHS": return "BAHAMAS (COMUNIDAD DE LAS)";
            case "BHR": return "BAHREIN (ESTADO DE)";
            case "BGD": return "BANGLADESH (REPUBLICA POPULAR DE)";
            case "BRB": return "BARBADOS (COMUNIDAD BRITANICA DE NACIONES)";
            case "BEL": return "BELGICA (REINO DE)";
            case "BLZ": return "BELICE";
            case "BEN": return "BENIN (REPUBLICA DE)";
            case "BMU": return "BERMUDAS";
            case "BES": return "BONAIRE, SAN EUSTAQUIO Y SABA";
            case "BLR": return "BIELORRUSIA (REPUBLICA DE)";
            case "BOL": return "BOLIVIA (REPUBLICA DE)";
            case "BIH": return "BOSNIA Y HERZEGOVINA";
            case "BWA": return "BOTSWANA (REPUBLICA DE)";
            case "BRA": return "BRASIL (REPUBLICA FEDERATIVA DE)";
            case "BRN": return "BRUNEI (ESTADO DE) (RESIDENCIA DE PAZ)";
            case "BGR": return "BULGARIA (REPUBLICA DE)";
            case "BFA": return "BURKINA FASO";
            case "BDI": return "BURUNDI (REPUBLICA DE)";
            case "BTN": return "BUTAN (REINO DE)";
            case "CPV": return "CABO VERDE (REPUBLICA DE)";
            case "TCD": return "CHAD (REPUBLICA DEL)";
            case "CYM": return "CAIMAN (ISLAS)";
            case "KHM": return "CAMBOYA (REINO DE)";
            case "CMR": return "CAMERUN (REPUBLICA DEL)";
            case "CAN": return "CANADA";
            case "RKE": return "CANAL, ISLAS DEL (ISLAS NORMANDAS)";
            case "CHL": return "CHILE (REPUBLICA DE)";
            case "CHN": return "CHINA (REPUBLICA POPULAR)";
            case "CYP": return "CHIPRE (REPUBLICA DE)";
            case "CIA": return "CIUDAD DEL VATICANO (ESTADO DE LA)";
            case "CCK": return "COCOS (KEELING, ISLAS AUSTRALIANAS)";
            case "COL": return "COLOMBIA (REPUBLICA DE)";
            case "COM": return "COMORAS (ISLAS)";
            case "EMU": return "COMUNIDAD EUROPEA";
            case "COG": return "CONGO (REPUBLICA DEL)";
            case "COK": return "COOK (ISLAS)";
            case "PRK": return "COREA (REPUBLICA POPULAR DEMOCRATICA DE) (COREA DEL NORTE)";
            case "KOR": return "COREA (REPUBLICA DE) (COREA DEL SUR)";
            case "CIV": return "COSTA DE MARFIL (REPUBLICA DE LA)";
            case "CRI": return "COSTA RICA (REPUBLICA DE)";
            case "HRV": return "CROACIA (REPUBLICA DE)";
            case "CUB": return "CUBA (REPUBLICA DE)";
            case "CUR": return "CURAZAO (TERRITORIO HOLANDES DE ULTRAMAR)";
            case "DNK": return "DINAMARCA (REINO DE)";
            case "DJI": return "DJIBOUTI (REPUBLICA DE)";
            case "DMA": return "DOMINICA (COMUNIDAD DE)";
            case "ECU": return "ECUADOR (REPUBLICA DEL)";
            case "EGY": return "EGIPTO (REPUBLICA ARABE DE)";
            case "SLV": return "EL SALVADOR (REPUBLICA DE)";
            case "ARE": return "EMIRATOS ARABES UNIDOS";
            case "ERI": return "ERITREA (ESTADO DE)";
            case "SVN": return "ESLOVENIA (REPUBLICA DE)";
            case "ESP": return "ESPAÑA (REINO DE)";
            case "DSM": return "ESTADO FEDERADO DE MICRONESIA";
            case "USA": return "ESTADOS UNIDOS DE AMERICA";
            case "EST": return "ESTONIA (REPUBLICA DE)";
            case "ETH": return "ETIOPIA (REPUBLICA DEMOCRATICA FEDERAL)";
            case "FJI": return "FIDJI (REPUBLICA DE)";
            case "PHL": return "FILIPINAS (REPUBLICA DE LAS)";
            case "FIN": return "FINLANDIA (REPUBLICA DE)";
            case "FRA": return "FRANCIA (REPUBLICA FRANCESA)";
            case "GZA": return "FRANJA DE GAZA";
            case "GAB": return "GABONESA (REPUBLICA)";
            case "GMB": return "GAMBIA (REPUBLICA DE LA)";
            case "GEO": return "GEORGIA (REPUBLICA DE)";
            case "SGS": return "GEORGIA DEL SUR E ISLAS SANDWICH DEL SUR Clave Reformada D.O.F. 09/05/2016";
            case "GHA": return "GHANA (REPUBLICA DE)";
            case "GIB": return "GIBRALTAR (R.U.)";
            case "GRD": return "GRANADA";
            case "GRC": return "GRECIA (REPUBLICA HELENICA)";
            case "GRL": return "GROENLANDIA (DINAMARCA)";
            case "GLP": return "GUADALUPE (DEPARTAMENTO DE)";
            case "GUM": return "GUAM (E.U.A.)";
            case "GTM": return "GUATEMALA (REPUBLICA DE)";
            case "GGY": return "GUERNSEY";
            case "GNB": return "GUINEA-BISSAU (REPUBLICA DE)";
            case "GNQ": return "GUINEA ECUATORIAL (REPUBLICA DE)";
            case "GIN": return "GUINEA (REPUBLICA DE)";
            case "GUF": return "GUYANA FRANCESA";
            case "GUY": return "GUYANA (REPUBLICA COOPERATIVA DE)";
            case "HTI": return "HAITI (REPUBLICA DE)";
            case "HND": return "HONDURAS (REPUBLICA DE)";
            case "HKG": return "HONG KONG (REGION ADMINISTRATIVA ESPECIAL DE LA REPUBLICA)";
            case "HUN": return "HUNGRIA (REPUBLICA DE)";
            case "IND": return "INDIA (REPUBLICA DE)";
            case "IDN": return "INDONESIA (REPUBLICA DE)";
            case "IRQ": return "IRAK (REPUBLICA DE)";
            case "IRN": return "IRAN (REPUBLICA ISLAMICA DEL)";
            case "IRL": return "IRLANDA (REPUBLICA DE)";
            case "ISL": return "ISLANDIA (REPUBLICA DE)";
            case "ALA": return "ISLAS ALAND Clave Reformada D.O.F. 09/05/2016";
            case "BVT": return "ISLA BOUVET";
            case "IMN": return "ISLA DE MAN";
            case "FRO": return "ISLA FEROE (LAS)";
            case "LHM": return "ISLAS HEARD Y MCDONALD";
            case "FLK": return "ISLAS MALVINAS (R.U.)";
            case "MNP": return "ISLAS MARIANAS SEPTENTRIONALES";
            case "MHL": return "ISLAS MARSHALL";
            case "SLB": return "ISLAS SALOMON (COMUNIDAD BRITANICA DE NACIONES)";
            case "SJM": return "ISLAS SVALBARD Y JAN MAYEN (NORUEGA)";
            case "TKL": return "ISLAS TOKELAU";
            case "WLF": return "ISLAS WALLIS Y FUTUNA";
            case "ISR": return "ISRAEL (ESTADO DE)";
            case "ITA": return "ITALIA (REPUBLICA ITALIANA)";
            case "JAM": return "JAMAICA";
            case "JPN": return "JAPON";
            case "JEY": return "JERSEY";
            case "JOR": return "JORDANIA (REINO HACHEMITA DE)";
            case "KAZ": return "KAZAKHSTAN (REPUBLICA DE)";
            case "KEN": return "KENYA (REPUBLICA DE)";
            case "KIR": return "KIRIBATI (REPUBLICA DE)";
            case "KWT": return "KUWAIT (ESTADO DE)";
            case "KGZ": return "KYRGYZSTAN (REPUBLICA KIRGYZIA)";
            case "LSO": return "LESOTHO (REINO DE)";
            case "LVA": return "LETONIA (REPUBLICA DE)";
            case "LBN": return "LIBANO (REPUBLICA DE)";
            case "LBR": return "LIBERIA (REPUBLICA DE)";
            case "LBY": return "LIBIA (JAMAHIRIYA LIBIA ARABE POPULAR SOCIALISTA)";
            case "LIE": return "LIECHTENSTEIN (PRINCIPADO DE)";
            case "LTU": return "LITUANIA (REPUBLICA DE)";
            case "LUX": return "LUXEMBURGO (GRAN DUCADO DE)";
            case "MAC": return "MACAO";
            case "MKD": return "MACEDONIA (ANTIGUA REPUBLICA YUGOSLAVA DE)";
            case "MDG": return "MADAGASCAR (REPUBLICA DE)";
            case "MYS": return "MALASIA";
            case "MWI": return "MALAWI (REPUBLICA DE)";
            case "MDV": return "MALDIVAS (REPUBLICA DE)";
            case "MLI": return "MALI (REPUBLICA DE)";
            case "MLT": return "MALTA (REPUBLICA DE)";
            case "MAR": return "MARRUECOS (REINO DE)";
            case "MTQ": return "MARTINICA (DEPARTAMENTO DE) (FRANCIA)";
            case "MUS": return "MAURICIO (REPUBLICA DE)";
            case "MRT": return "MAURITANIA (REPUBLICA ISLAMICA DE)";
            case "MYT": return "MAYOTTE";
            case "MEX": return "MEXICO (ESTADOS UNIDOS MEXICANOS)";
            case "MDA": return "MOLDAVIA (REPUBLICA DE)";
            case "MCO": return "MONACO (PRINCIPADO DE)";
            case "MNG": return "MONGOLIA";
            case "MSR": return "MONSERRAT (ISLA)";
            case "MNE": return "MONTENEGRO";
            case "MOZ": return "MOZAMBIQUE (REPUBLICA DE)";
            case "MMR": return "MYANMAR (UNION DE)";
            case "NAM": return "NAMIBIA (REPUBLICA DE)";
            case "NRU": return "NAURU";
            case "CXI": return "NAVIDAD (CHRISTMAS) (ISLAS)";
            case "NPL": return "NEPAL (REINO DE)";
            case "NIC": return "NICARAGUA (REPUBLICA DE)";
            case "NER": return "NIGER (REPUBLICA DE)";
            case "NGA": return "NIGERIA (REPUBLICA FEDERAL DE)";
            case "NIU": return "NIVE (ISLA)";
            case "NFK": return "NORFOLK (ISLA)";
            case "NOR": return "NORUEGA (REINO DE)";
            case "NCL": return "NUEVA CALEDONIA (TERRITORIO FRANCES DE ULTRAMAR)";
            case "NZL": return "NUEVA ZELANDIA";
            case "OMN": return "OMAN (SULTANATO DE)";
            case "PIK": return "PACIFICO, ISLAS DEL (ADMON. E.U.A.)";
            case "ZYA": return "PAISES BAJOS (REINO DE LOS) (HOLANDA)";
            case "KCD": return "PAISES NO DECLARADOS";
            case "PAK": return "PAKISTAN (REPUBLICA ISLAMICA DE)";
            case "PLW": return "PALAU (REPUBLICA DE)";
            case "PSE": return "PALESTINA";
            case "PAN": return "PANAMA (REPUBLICA DE)";
            case "PNG": return "PAPUA NUEVA GUINEA (ESTADO INDEPENDIENTE DE)";
            case "PRY": return "PARAGUAY (REPUBLICA DEL)";
            case "PER": return "PERU (REPUBLICA DEL)";
            case "PCN": return "PITCAIRNS (ISLAS DEPENDENCIA BRITANICA)";
            case "PYF": return "POLINESIA FRANCESA";
            case "POL": return "POLONIA (REPUBLICA DE)";
            case "PRT": return "PORTUGAL (REPUBLICA PORTUGUESA)";
            case "PRI": return "PUERTO RICO (ESTADO LIBRE ASOCIADO DE LA COMUNIDAD DE)";
            case "QAT": return "QATAR (ESTADO DE)";
            case "GBR": return "REINO UNIDO DE LA GRAN BRETAÑA E IRLANDA DEL NORTE";
            case "CZE": return "REPUBLICA CHECA";
            case "CAF": return "REPUBLICA CENTROAFRICANA";
            case "LAO": return "REPUBLICA DEMOCRATICA POPULAR LAOS";
            case "SRB": return "REPUBLICA DE SERBIA";
            case "DOM": return "REPUBLICA DOMINICANA";
            case "SVK": return "REPUBLICA ESLOVACA";
            case "COD": return "REPUBLICA POPULAR DEL CONGO";
            case "RWA": return "REPUBLICA RUANDESA";
            case "REU": return "REUNION (DEPARTAMENTO DE LA) (FRANCIA)";
            case "ROM": return "RUMANIA";
            case "RUS": return "RUSIA (FEDERACION RUSA)";
            case "ESH": return "SAHARA OCCIDENTAL (REPUBLICA ARABE SAHARAVI DEMOCRATICA)";
            case "WSM": return "SAMOA (ESTADO INDEPENDIENTE DE)";
            case "ASM": return "SAMOA AMERICANA";
            case "BLM": return "SAN BARTOLOME";
            case "KNA": return "SAN CRISTOBAL Y NIEVES (FEDERACION DE) (SAN KITTS-NEVIS)";
            case "SMR": return "SAN MARINO (SERENISIMA REPUBLICA DE)";
            case "MAF": return "SAN MARTIN (PARTE FRANCESA)";
            case "SPM": return "SAN PEDRO Y MIQUELON";
            case "VCT": return "SAN VICENTE Y LAS GRANADINAS";
            case "SHN": return "SANTA ELENA";
            case "LCA": return "SANTA LUCIA";
            case "STP": return "SANTO TOME Y PRINCIPE (REPUBLICA DEMOCRATICA DE)";
            case "SEN": return "SENEGAL (REPUBLICA DEL)";
            case "SYC": return "SEYCHELLES (REPUBLICA DE LAS)";
            case "SLE": return "SIERRA LEONA (REPUBLICA DE)";
            case "SGP": return "SINGAPUR (REPUBLICA DE)";
            case "SXM": return "SINT MAARTEN (PARTE HOLANDESA)";
            case "SYR": return "SIRIA (REPUBLICA ARABE)";
            case "SOM": return "SOMALIA";
            case "LKA": return "SRI LANKA (REPUBLICA DEMOCRATICA SOCIALISTA DE)";
            case "ZAF": return "SUDAFRICA (REPUBLICA DE)";
            case "SDN": return "SUDAN (REPUBLICA DEL)";
            case "SSD": return "SUDÁN DEL SUR";
            case "SWE": return "SUECIA (REINO DE)";
            case "CHE": return "SUIZA (CONFEDERACION)";
            case "SUR": return "SURINAME (REPUBLICA DE)";
            case "SWZ": return "SWAZILANDIA (REINO DE)";
            case "TJK": return "TADJIKISTAN (REPUBLICA DE)";
            case "THA": return "TAILANDIA (REINO DE)";
            case "TWN": return "TAIWAN (REPUBLICA DE CHINA)";
            case "TZA": return "TANZANIA (REPUBLICA UNIDA DE)";
            case "XCH": return "TERRITORIOS BRITANICOS DEL OCEANO INDICO";
            case "FXA": return "TERRITORIOS FRANCESES, AUSTRALES Y ANTARTICOS";
            case "TMP": return "TIMOR ORIENTAL";
            case "TGO": return "TOGO (REPUBLICA TOGOLESA)";
            case "TON": return "TONGA (REINO DE)";
            case "TTO": return "TRINIDAD Y TOBAGO (REPUBLICA DE)";
            case "TUN": return "TUNEZ (REPUBLICA DE)";
            case "TCA": return "TURCAS Y CAICOS (ISLAS)";
            case "TKM": return "TURKMENISTAN (REPUBLICA DE)";
            case "TUR": return "TURQUIA (REPUBLICA DE)";
            case "TUV": return "TUVALU (COMUNIDAD BRITANICA DE NACIONES)";
            case "UKR": return "UCRANIA";
            case "UGA": return "UGANDA (REPUBLICA DE)";
            case "URY": return "URUGUAY (REPUBLICA ORIENTAL DEL)";
            case "UZB": return "UZBEJISTAN (REPUBLICA DE)";
            case "VUT": return "VANUATU";
            case "VEN": return "VENEZUELA (REPUBLICA DE)";
            case "VNM": return "VIETNAM (REPUBLICA SOCIALISTA DE)";
            case "VGB": return "VIRGENES. ISLAS (BRITANICAS)";
            case "VIR": return "VIRGENES. ISLAS (NORTEAMERICANAS)";
            case "YEM": return "YEMEN (REPUBLICA DE)";
            case "ZMB": return "ZAMBIA (REPUBLICA DE)";
            case "ZWE": return "ZIMBABWE (REPUBLICA DE)";
            case "PTY": return "ZONA DEL CANAL DE PANAMA";
            case "RUH": return "ZONA NEUTRAL IRAQ-ARABIA SAUDITA";
            default:
                return "UNDEFINED";
        }
    }

    public function aduanas($value) {
        switch ($value) {
            case "10": return "ACAPULCO, ACAPULCO DE JUAREZ, GUERRERO.";
            case "12": return "AEROPUERTO INTERNACIONAL GENERAL JUAN N. ALVAREZ, ACAPULCO, GUERRERO. Sección Adicionada D.O.F. 09/05/2016";
            case "20": return "AGUA PRIETA, AGUA PRIETA, SONORA.";
            case "50": return "SUBTENIENTE LOPEZ, SUBTENIENTE LOPEZ, QUINTANA ROO.";
            case "51": return "SUBTENIENTE LOPEZ II 'CHACTEMAL', OTHÓN P. BLANCO, CHETUMAL, QUINTANA ROO.";
            case "60": return "CIUDAD DEL CARMEN, CIUDAD DEL CARMEN, CAMPECHE.";
            case "63": return "SEYBAPLAYA, CHAMPOTON, CAMPECHE.";
            case "70": return "CIUDAD JUAREZ, CIUDAD JUAREZ, CHIHUAHUA.";
            case "71": return "PUENTE INTERNACIONAL ZARAGOZA-ISLETA, CIUDAD JUAREZ, CHIHUAHUA.";
            case "72": return "SAN JERONIMO-SANTA TERESA, CIUDAD JUAREZ, CHIHUAHUA.";
            case "73": return "AEROPUERTO INTERNACIONAL ABRAHAM GONZALEZ, CIUDAD JUAREZ, CHIHUAHUA.";
            case "74": return "GUADALUPE-TORNILLO, GUADALUPE DE BRAVO, CHIHUAHUA.";
            case "80": return "COATZACOALCOS, COATZACOALCOS, VERACRUZ.";
            case "8": return "ISLA PAJARITOS, COATZACOALCOS, VERACRUZ. Sección Eliminada D.O.F. 09/05/2016";
            case "110": return "ENSENADA, ENSENADA, BAJA CALIFORNIA.";
            case "11": return "ISLA DE LOS CEDROS, ENSENADA, BAJA CALIFORNIA. Sección Eliminada D.O.F. 09/05/2016";
            case "120": return "GUAYMAS, GUAYMAS, SONORA.";
            case "121": return "AEROPUERTO INTERNACIONAL GENERAL IGNACIO PESQUEIRA GARCIA, HERMOSILLO, SONORA.";
            case "123": return "CIUDAD OBREGON ADYACENTE AL AEROPUERTO DE CIUDAD OBREGON, CAJEME, SONORA.";
            case "140": return "LA PAZ, LA PAZ, BAJA CALIFORNIA SUR.";
            case "14": return "LOS OLIVOS, LA PAZ, BAJA CALIFORNIA SUR. Sección Eliminada D.O.F. 09/05/2016";
            case "142": return "SAN JOSE DEL CABO, LOS CABOS, BAJA CALIFORNIA SUR.";
            case "143": return "CABO SAN LUCAS, LOS CABOS, BAJA CALIFORNIA SUR.";
            case "144": return "SANTA ROSALIA, MULEGE, BAJA CALIFORNIA SUR.";
            case "145": return "LORETO, LORETO, BAJA CALIFORNIA SUR.";
            case "147": return "PICHILINGÛE, LA PAZ, BAJA CALIFORNIA SUR.";
            case "160": return "MANZANILLO, MANZANILLO, COLIMA.";
            case "16": return "TECOMAN, TECOMAN, COLIMA. Sección Eliminada D.O.F. 09/05/2016";
            case "161": return "ARMERÍA, ARMERÍA, COLIMA.";
            case "170": return "MATAMOROS, MATAMOROS, TAMAULIPAS.";
            case "171": return "LUCIO BLANCO-LOS INDIOS, MATAMOROS, TAMAULIPAS.";
            case "172": return "SECCION ADUANERA FERROVIARIA DE MATAMOROS.";
            case "17": return "PUERTO EL MEZQUITAL, MATAMOROS, TAMAULIPAS.";
//            case "17": return "AEROPUERTO INTERNACIONAL GENERAL SERVANDO CANALES, MATAMOROS, TAMAULIPAS.";
            case "180": return "MAZATLAN, MAZATLAN, SINALOA.";
            case "183": return "TOPOLOBAMPO, AHOME, SINALOA.";
            case "184": return "AEROPUERTO INTERNACIONAL DE CULIACAN, CULIACAN, SINALOA.";
            case "190": return "MEXICALI, MEXICALI, BAJA CALIFORNIA.";
            case "192": return "LOS ALGODONES, MEXICALI, BAJA CALIFORNIA.";
            case "193": return "SAN FELIPE, MEXICALI, BAJA CALIFORNIA.";
            case "200": return "MÉXICO, CIUDAD DE MÉXICO. Denominación Reformada D.O.F. 25/10/2016";
            case "202": return "IMPORTACION Y EXPORTACION DE CONTENEDORES, DELEGACION AZCAPOTZALCO, CIUDAD DE MÉXICO. Denominación Reformada D.O.F. 25/10/2016";
            case "220": return "NACO, NACO, SONORA.";
            case "230": return "NOGALES, NOGALES, SONORA.";
            case "231": return "SASABE, SARIC, SONORA.";
            case "240": return "NUEVO LAREDO, NUEVO LAREDO, TAMAULIPAS.";
            case "24": return "ESTACION SANCHEZ, NUEVO LAREDO, TAMAULIPAS";
//            case "24": return "AEROPUERTO INTERNACIONAL DE NUEVO LAREDO 'QUETZALCOATL', NUEVO LAREDO, TAMAULIPAS.";
            case "250": return "OJINAGA, OJINAGA, CHIHUAHUA.";
            case "260": return "PUERTO PALOMAS, PUERTO PALOMAS, CHIHUAHUA.";
            case "270": return "PIEDRAS NEGRAS, PIEDRAS NEGRAS, COAHUILA.";
            case "271": return "AEROPUERTO INTERNACIONAL PLAN DE GUADALUPE, RAMOS ARIZPE, COAHUILA.";
            case "27": return "RIO ESCONDIDO, NAVA, COAHUILA.";
            case "280": return "PROGRESO, PROGRESO, YUCATAN.";
            case "282": return "AEROPUERTO INTERNACIONAL LIC. MANUEL CRESCENCIO REJON, MERIDA, YUCATAN.";
            case "300": return "CIUDAD REYNOSA, CIUDAD REYNOSA, TAMAULIPAS.";
            case "302": return "LAS FLORES, RIO BRAVO, TAMAULIPAS.";
            case "304": return "AEROPUERTO INTERNACIONAL GENERAL. LUCIO BLANCO, CIUDAD REYNOSA, TAMAULIPAS.";
            case "305": return "RIO BRAVO-DONNA, RIO BRAVO, TAMAULIPAS.";
            case "306": return "ANZALDUAS, CIUDAD REYNOSA, TAMAULIPAS.";
            case "310": return "SALINA CRUZ, SALINA CRUZ, OAXACA.";
            case "311": return "AEROPUERTO INTERNACIONAL DE OAXACA, SANTA CRUZ XOXOCOTLAN, OAXACA.";
            case "330": return "SAN LUIS RIO COLORADO, SAN LUIS RIO COLORADO, SONORA.";
            case "340": return "CIUDAD MIGUEL ALEMAN, CIUDAD MIGUEL ALEMAN, TAMAULIPAS.";
            case "342": return "GUERRERO, GUERRERO, TAMAULIPAS.";
            case "370": return "CIUDAD HIDALGO, CIUDAD HIDALGO, CHIAPAS.";
            case "372": return "CIUDAD TALISMAN, TUXTLA CHICO, CHIAPAS.";
            case "376": return "CIUDAD CUAUHTEMOC, FRONTERA COMALAPA, CHIAPAS.";
            case "375": return "PUERTO CHIAPAS, TAPACHULA, CHIAPAS.";
            case "37": return "AEROPUERTO INTERNACIONAL DE TAPACHULA, TAPACHULA, CHIAPAS.";
            case "380": return "TAMPICO, TAMPICO, TAMAULIPAS.";
            case "390": return "TECATE, TECATE, BAJA CALIFORNIA.";
            case "400": return "TIJUANA, TIJUANA, BAJA CALIFORNIA.";
            case "402": return "AEROPUERTO INTERNACIONAL GENERAL ABELARDO L. RODRIGUEZ, TIJUANA, BAJA CALIFORNIA.";
            case "420": return "TUXPAN, TUXPAN DE RODRIGUEZ CANO, VERACRUZ.";
            case "421": return "TUXPAN, TUXPAN, VERACRUZ.";
            case "430": return "VERACRUZ, VERACRUZ, VERACRUZ.";
            case "432": return "AEROPUERTO INTERNACIONAL GENERAL HERIBERTO JARA CORONA, VERACRUZ, VERACRUZ.";
            case "440": return "CIUDAD ACUÑA, CIUDAD ACUÑA, COAHUILA.";
            case "460": return "TORREON, TORREON, COAHUILA.";
            case "46": return "GOMEZ PALACIO, GOMEZ PALACIO, DURANGO.";
//            case "46": return "AEROPUERTO INTERNACIONAL GENERAL GUADALUPE VICTORIA, DURANGO, DURANGO.";
            case "461": return "AEROPUERTO DE TORREÓN, COAHUILA DE ZARAGOZA.";
            case "462": return "GOMEZ PALACIO, GOMEZ PALACIO, DURANGO. Sección Adicionada D.O.F. 09/05/2016 ";
            case "463": return "AEROPUERTO INTERNACIONAL GENERAL GUADALUPE VICTORIA, DURANGO, DURANGO. Sección Adicionada D.O.F. 09/05/2016 ";
            case "470": return "AEROPUERTO INTERNACIONAL DE LA CIUDAD DE MEXICO,";
            case "471": return "SATELITE, PARA IMPORTACION Y EXPORTACION POR VIA AEREA, AEROPUERTO INTERNACIONAL BENITO JUAREZ DE LA CIUDAD DE MEXICO.";
            case "472": return "CENTRO POSTAL MECANIZADO, POR VIA POSTAL Y POR TRAFICO AEREO, AEROPUERTO INTERNACIONAL BENITO JUAREZ DE LA CIUDAD DE MEXICO.";
            case "480": return "GUADALAJARA, TLACOMULCO DE ZUÑIGA, JALISCO.";
            case "481": return "PUERTO VALLARTA, PUERTO VALLARTA, JALISCO.";
            case "484": return "TERMINAL INTERMODAL FERROVIARIA, GUADALAJARA, JALISCO.";
            case "500": return "SONOYTA, SONOYTA, SONORA.";
            case "501": return "SAN EMETERIO, GENERAL PLUTARCO ELIAS CALLES, SONORA.";
            case "50": return "SONORA, PITIQUITO, SONORA.";
            case "510": return "LAZARO CARDENAS, LAZARO CARDENAS, MICHOACAN.";
            case "511": return "AEROPUERTO INTERNACIONAL IXTAPA-ZIHUATANEJO, ZIHUATANEJO DE AZUETA, GUERRERO.";
            case "520": return "MONTERREY, GENERAL MARIANO ESCOBEDO, NUEVO LEON.";
            case "521": return "AEROPUERTO INTERNACIONAL GENERAL MARIANO ESCOBEDO, APODACA, NUEVO LEON.";
            case "523": return "SALINAS VICTORIA A (TERMINAL FERROVIARIA), SALINAS VICTORIA, NUEVO LEON.";
            case "524": return "GENERAL ESCOBEDO, GENERAL ESCOBEDO, NUEVO LEON.";
            case "525": return "SALINAS VICTORIA B (INTERPUERTO), SALINAS VICTORIA, NUEVO LEÓN. Sección Adicionada D.O.F. 09/05/2016";
            case "530": return "CANCUN, CANCUN, QUINTANA ROO.";
            case "532": return "AEROPUERTO INTERNACIONAL DE COZUMEL, COZUMEL, QUINTANA ROO.";
            case "533": return "PUERTO MORELOS, BENITO JUAREZ, QUINTANA ROO.";
            case "640": return "QUERETARO, EL MARQUES Y COLON, QUERETARO.";
            case "647": return "HIDALGO, ATOTONILCO DE TULA, HIDALGO.";
            case "650": return "TOLUCA, TOLUCA, ESTADO DE MEXICO.";
            case "651": return "SAN CAYETANO MORELOS, TOLUCA, ESTADO DE MÉXICO. Sección Adicinada D.O.F. 09/05/2016";
            case "670": return "CHIHUAHUA, CHIHUAHUA, CHIHUAHUA.";
            case "671": return "PARQUE INDUSTRIAL LAS AMERICAS, CHIHUAHUA, CHIHUAHUA.";
            case "672": return "AEROPUERTO INTERNACIONAL GENERAL ROBERTO FIERRO VILLALOBOS, CHIHUAHUA, CHIHUAHUA.";
            case "730": return "AGUASCALIENTES, AGUASCALIENTES, AGUASCALIENTES.";
            case "731": return "PARQUE MULTIMODAL INTERPUERTO, SAN LUIS POTOSI, SAN LUIS POTOSI.";
            case "733": return "AEROPUERTO INTERNACIONAL PONCIANO ARRIAGA, SOLEDAD DE GRACIANO SANCHEZ, SAN LUIS POTOSI.";
            case "732": return "AEROPUERTO INTERNACIONAL GENERAL LEOBARDO C. RUIZ, EN CALERA ZACATECAS.";
            case "734": return "LA PILA-VILLA, VILLA DE REYES, SAN LUIS POTOSI.";
            case "73": return "CHICALOTE, SAN FRANCISCO DE LOS ROMO, AGUASCALIENTES.";
            case "735": return "AEROPUERTO INTERNACIONAL LIC. JESUS TERAN PEREDO, AGUASCALIENTES, AGUASCALIENTES.";
            case "750": return "PUEBLA, HEROICA PUEBLA DE ZARAGOZA, PUEBLA.";
            case "751": return "CUERNAVACA, JIUTEPEC, MORELOS.";
            case "752": return "TLAXCALA, ATLANGATEPEC, TLAXCALA. Sección Eliminada D.O.F. 09/05/2016";
            case "754": return "AEROPUERTO INTERNACIONAL HERMANOS SERDAN, HUEJOTZINGO, PUEBLA.";
            case "800": return "COLOMBIA, COLOMBIA, NUEVO LEON.";
            case "810": return "ALTAMIRA, ALTAMIRA, TAMAULIPAS.";
            case "81": return "AEROPUERTO INTERNACIONAL GENERAL PEDRO JOSE MENDEZ, VICTORIA, TAMAULIPAS. Sección Eliminada D.O.F. 09/05/2016";
            case "820": return "CIUDAD CAMARGO, CIUDAD CAMARGO, TAMAULIPAS.";
            case "830": return "DOS BOCAS, PARAISO, TABASCO.";
            case "831": return "AEROPUERTO INTERNACIONAL C.P.A. CARLOS ROVIROSA PEREZ, CIUDAD DE VILLAHERMOSA, CENTRO, TABASCO.";
            case "834": return "EL CEIBO, TENOSIQUE, TABASCO.";
            case "840": return "GUANAJUATO, SILAO, GUANAJUATO.";
            case "841": return "CELAYA, CELAYA, GUANAJUATO.";
            case "842": return "AEROPUERTO INTERNACIONAL DE GUANAJUATO, SILAO, GUANAJUATO.";
            default:
                return "UNDEFINED";
        }
    }

}
