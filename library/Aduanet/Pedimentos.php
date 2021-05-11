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

    protected $_endpoint = "http://69.28.88.157:5004";
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
                return;
            }
        } catch (Exception $e) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function csvAduanet($idTrafico)
    {
        try {
            $trafico = new OAQ_Trafico(array("idTrafico" => $idTrafico));

            $arr = $trafico->obtenerDatos();
            $partidas = $trafico->obtenerPartidas();

            $e = new OAQ_ExcelReportes();
            $e->csvAduanet($arr['patente'], $arr['aduana'], $arr['pedimento'], $arr['referencia'], $partidas);
        } catch (Exception $e) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function trafica($fraccion)
    {
        try {

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $this->_endpoint . "/api/tarifa",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_POSTFIELDS => "fraccion=" . $fraccion,
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/x-www-form-urlencoded"
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            $row = json_decode($response, true);

            if ($row['success'] == true) {
                return $row;
            } else {
                return;
            }
        } catch (Exception $e) {
            throw new Exception("Exception on " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    public function importarFactura($idTrafico, $numFactura)
    {
        $ch = curl_init();
        $nf = urlencode($numFactura);

        curl_setopt($ch, CURLOPT_URL, "https://oaq.dnsalias.net/cgi-bin/cgi_facturas.py?id_trafico={$idTrafico}&num_factura={$nf}");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);

        curl_close($ch);

        return null;
    }

    public function actualizarCove($patente, $aduana, $pedimento, $numFactura, $cove)
    {
        $ch = curl_init();

        $data = array(
            "patente" => $patente,
            "aduana" => $aduana,
            "pedimento" => $pedimento,
            "cove" => $cove,
            "num_factura" => $numFactura,
        );

        $uri = "http://localhost:5003/enviar-cove-aduanet";

        curl_setopt($ch, CURLOPT_URL, $uri);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:application/json"));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);

        curl_close($ch);

        return $server_output;
    }

    public function actualizarEdocument($patente, $aduana, $pedimento, $referencia, $edocument)
    {
        $ch = curl_init();

        $data = array(
            "patente" => $patente,
            "aduana" => $aduana,
            "pedimento" => $pedimento,
            "referencia" => $referencia,
            "edocument" => $edocument,
        );

        $uri = "http://localhost:5003/enviar-edocuments-aduanet";

        curl_setopt($ch, CURLOPT_URL, $uri);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:application/json"));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);

        curl_close($ch);

        return $server_output;
    }
}
