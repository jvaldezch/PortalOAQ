<?php

/**
 * Description of Vucem_Xml
 * 
 * Esta clase conforma arreglos en archivos XML requeridos por la Ventanilla Única de Comercio Exterior (VUCEM)
 *
 * @author Jaime E. Valdez jvaldezch at gmail
 * 
 * URL: https://www.banxico.org.mx/SieAPIRest/service/v1/doc/catalogoSeries#
 * 
 */
class V2_Banxico {

    protected $_domtree;
    protected $_envelope;
    protected $_header;
    protected $_body;
    protected $_endpoint = 'http://www.banxico.org.mx/DgieWSWeb/DgieWS';
    protected $_response;
    protected $_token = "9919edf12a7f4446088f28bfb4bbf953caa6edff8ef0337848cbe072a6f98ba1";

    function get_response() {
        return $this->_response;
    }

    function __construct() {
    }

    public function consumirServicio($today, $tomorrow) {
        try {

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://www.banxico.org.mx/SieAPIRest/service/v1/series/SF60653/datos/{$today}/{$tomorrow}?token=" . $this->_token,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "Accept: */*",
                    "Accept-Encoding: gzip, deflate",
                    "Cache-Control: no-cache",
                    "Connection: keep-alive",
                    "Host: www.banxico.org.mx",
                    "User-Agent: PostmanRuntime/7.18.0",
                    "cache-control: no-cache"
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                throw new Exception($err);
            } else {
                $this->_response = json_decode($response, true);
            }
        } catch (Exception $e) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

}
