<?php

/**
 * Description of Vucem_Xml
 * 
 * Esta clase conforma arreglos en archivos XML requeridos por la Ventanilla Única de Comercio Exterior (VUCEM)
 *
 * @author Jaime E. Valdez jvaldezch at gmail
 */
class Aduanet_Pedimentos
{

    protected $_endpoint = "http://199.167.184.210:5004";
    protected $_firephp;
    protected $_token = null;

    function __construct()
    {
        $this->_firephp = Zend_Registry::get("firephp");
    }

    public function login()
    {
        try {

            $client = new Zend_Rest_Client($this->_endpoint);
            $httpClient = $client->getHttpClient();
            $httpClient->setConfig(array('timeout' => 30));
            $client->setHttpClient($httpClient);

            $response = $client->restPost("/api/login", array(
                'password' => "255851BBDEC8561B",
            ));

            if (($body = $response->getBody())) {

                $row = json_decode($body, true);
                if ($row['success'] == true) {
                    $this->_token = $row['token'];
                }
                return true;
            } else {
                $this->_firephp->warn("No response");
                return false;
            }
        } catch (Exception $e) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function pedimento()
    {
        try {

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $this->_endpoint . "/api/pedimento",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => "patente=3589&aduana=640&pedimento=0001903",
                CURLOPT_HTTPHEADER => array(
                    "Authorization: Bearer " . $this->_token,
                    "Content-Type: application/x-www-form-urlencoded"
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            $row = json_decode($response, true);

            if ($row['success'] == true) {
                return $row['results'];
            } else {
                $this->_firephp->warn("No response");
                return;
            }
        } catch (Exception $e) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function agregarFacturas()
    {
        try {

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $this->_endpoint . "/api/agregar-facturas",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => "patente=3589&aduana=640&pedimento=0001903&referencia=PRUEBA2020",
                CURLOPT_HTTPHEADER => array(
                    "Authorization: Bearer " . $this->_token,
                    "Content-Type: application/x-www-form-urlencoded"
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            $row = json_decode($response, true);

            if ($row['success'] == true) {
                return $row;
            } else {
                $this->_firephp->warn($row);
                return;
            }
        } catch (Exception $e) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }
}
