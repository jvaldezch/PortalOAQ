<?php

class Webservice_RestController extends Zend_Rest_Controller {

    protected $_config;
    protected $_appConfig;

    public function init() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_appConfig = new Application_Model_ConfigMapper();
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
    }

    public function headAction() {
        $this->getResponse()->setBody(null);
    }

    public function indexAction() {
        $this->getResponse()->setBody('REST Service');
        $this->getResponse()->setHttpResponseCode(200);
    }

    public function optionsAction() {
        $this->getResponse()->setBody(null);
        $this->getResponse()->setHeader('Allow', 'OPTIONS, HEAD, INDEX, GET, POST, PUT, DELETE');
    }

    public function getAction() {
        
    }

    protected function _token($value) {
        return base64_encode(password_hash($value, PASSWORD_BCRYPT, array('cost' => 5)));
    }

    public function loginAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "username" => "StringToLower",
                    "password" => "StringToLower",
                );
                $v = array(
                    "username" => "NotEmpty",
                    "password" => "NotEmpty",
                    "device" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("username") && $input->isValid("password") && $input->isValid("device")) {
                    $mpprSess = new Webservice_Model_UsuariosSesionesMovil();
                    $mppr = new Usuarios_Model_UsuariosMapper();
                    if (($id = $mppr->verificarUsuario($input->username))) {
                        $pws = $mppr->obtenerPassword($id);
                        if ($pws === $input->password) {
                            $token = $this->_token($input->username . time());
                            if (!($ids = $mpprSess->verificar($input->username, $input->device))) {
                                $mpprSess->agregar($input->username, $token, $input->device);
                            } else {
                                $mpprSess->actualizar($ids, $input->username, $token, $input->device);
                            }
                            $arr = $mppr->obtenerDatos($id);
                            $this->_helper->json(array(
                                'success' => true,
                                'token' => $token,
                                'patente' => $arr["patente"],
                                'aduana' => $arr["aduana"]
                            ));
                            $this->getResponse()->setHttpResponseCode(200);
                        } else {
                            throw new Exception('Contraseña no válida.');
                        }
                    } else {
                        throw new Exception('Usuario no existe.');
                    }
                } else {
                    throw new Exception('Paramétros no válidos.');
                }
            } else {
                throw new Exception('Tipo de solicitud no válida.');
            }
        } catch (Exception $ex) {
            $this->_helper->json(array('success' => false, 'message' => $ex->getMessage()));
        }
    }

    public function obtenerNavierasAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "username" => "StringToLower",
                    "patente" => "Digits",
                    "aduana" => "Digits",
                );
                $v = array(
                    "username" => "NotEmpty",
                    "token" => "NotEmpty",
                    "patente" => array("NotEmpty", new Zend_Validate_Int()),
                    "aduana" => array("NotEmpty", new Zend_Validate_Int()),
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("username") && $input->isValid("token") && $input->isValid("patente") && $input->isValid("aduana")) {
                    $mppr = new Trafico_Model_NavieraMapper();
                    $rows = $mppr->obtenerPorAduana($input->patente, $input->aduana);
                    $arr = [];
                    foreach ($rows as $item) {
                        $arr[] = array(
                            'id' => $item['id'],
                            'nombreNaviera' => $item['nombre'],
                        );
                    }
                    $this->_helper->json(array('success' => true, 'token' => $input->token, 'data' => $arr));
                } else {
                    throw new Exception('Parámetros no válidos.');
                }
            } else {
                throw new Exception('Tipo de solicitud no válida.');
            }
        } catch (Exception $ex) {
            $this->_helper->json(array('success' => false, 'message' => $ex->getMessage()));
        }
    }
    
    public function obtenerPaisesAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "username" => "StringToLower",
                );
                $v = array(
                    "username" => "NotEmpty",
                    "token" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("username") && $input->isValid("token")) {
                    $mppr = new Vucem_Model_VucemPaisesMapper();
                    $rows = $mppr->getAllCountries();
                    $arr = [];
                    foreach ($rows as $item) {
                        $arr[] = array(
                            'id' => $item['id'],
                            'nombre' => $item['nombre'],
                        );
                    }
                    $this->_helper->json(array('success' => true, 'token' => $input->token, 'data' => $arr));
                } else {
                    throw new Exception('Parámetros no válidos.');
                }
            } else {
                throw new Exception('Tipo de solicitud no válida.');
            }
        } catch (Exception $ex) {
            $this->_helper->json(array('success' => false, 'message' => $ex->getMessage()));
        }
    }

    public function obtenerGuiasAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "username" => "StringToLower",
                );
                $v = array(
                    "username" => "NotEmpty",
                    "token" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("username") && $input->isValid("token")) {
                    $mpprSess = new Webservice_Model_UsuariosSesionesMovil();
                    $mppr = new Bitacora_Model_BitacoraPedimentos();
                    $rows = $mppr->obtenerGuiasPorValidar();
                    $arr = [];
                    if (!empty($rows)) {
                        foreach ($rows as $item) {
                            $arr[] = array(
                                'id' => $item['id'],
                                'blGuia' => $item['blGuia'],
                                'nombreCliente' => isset($item['nombreCliente']) ? $item['nombreCliente'] : "",
                                'tipoOperacion' => isset($item['tipoOperacion']) ? $item['tipoOperacion'] : "",
                                'clavePedimento' => isset($item['clavePedimento']) ? $item['clavePedimento'] : "",
                            );
                        }
                    } else {
                        $arr[] = array();
                    }
                    $this->_helper->json(array('success' => true, 'token' => $input->token, 'data' => $arr));
                } else {
                    throw new Exception('Parámetros no válidos.');
                }
            } else {
                throw new Exception('Tipo de solicitud no válida.');
            }
        } catch (Exception $ex) {
            $this->_helper->json(array('success' => false, 'message' => $ex->getMessage()));
        }
    }

    public function obtenerGuiaAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $f = array(
                "*" => array("StringTrim", "StripTags"),
                "username" => "StringToLower",
                "id" => "Digits",
            );
            $v = array(
                "username" => "NotEmpty",
                "id" => array("NotEmpty", new Zend_Validate_Int()),
                "token" => "NotEmpty",
            );
            $input = new Zend_Filter_Input($f, $v, $request->getPost());
            if ($input->isValid("username") && $input->isValid("id") && $input->isValid("token")) {
                $mppr = new Bitacora_Model_BitacoraPedimentos();
                $arr = $mppr->obtenerDatos($input->id);
                $this->_helper->json(array('success' => true, "data" => $arr));
            } else {
                $this->_helper->json(array('success' => false, 'message' => 'Parámetros no válidos.'));
            }
        } else {
            $this->_helper->json(array('success' => false, 'message' => 'Tipo de solicitud no válida.'));
        }
    }

    public function iniciarPrevioGuiaAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "username" => "StringToLower",
                    "id" => "Digits",
                );
                $v = array(
                    "username" => "NotEmpty",
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                    "token" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("username") && $input->isValid("id") && $input->isValid("token")) {
                    $mppr = new Bitacora_Model_BitacoraPedimentos();
                    if ($mppr->iniciarPrevio($input->id) == true) {
                        $this->_helper->json(array('success' => true, 'token' => $input->token));
                    } else {
                        throw new Exception('No se pudo actualizar.');
                    }
                } else {
                    throw new Exception('Parámetros no válidos.');
                }
            } else {
                throw new Exception('Tipo de solicitud no válida.');
            }
        } catch (Exception $ex) {
            $this->_helper->json(array('success' => false, 'message' => $ex->getMessage()));
        }
    }
    
    public function revalidarGuiaAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "username" => "StringToLower",
                    "id" => "Digits",
                );
                $v = array(
                    "username" => "NotEmpty",
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                    "token" => "NotEmpty",
                    "lineaAerea" => "NotEmpty",
                    "fechaArribo" => "NotEmpty",
                    "averia" => array("NotEmpty", new Zend_Validate_Int()),
                    "completo" => array("NotEmpty", new Zend_Validate_Int()),
                    "observaciones" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("username") && $input->isValid("id") && $input->isValid("token")) {
                    $mppr = new Bitacora_Model_BitacoraPedimentos();
                    $arr = array(
                        "linea" => $input->lineaAerea,
                        "completa" => $input->completo,
                        "averia" => $input->averia,
                        "observaciones" => mb_strtoupper($input->observaciones),
                        "fechaEta" => date("Y-m-d H:i:s", strtotime($input->fechaArribo)),
                        "actualizado" => date("Y-m-d H:i:s"),
                        "actualizadoPor" => $input->username,
                    );
                    $mppr->update($input->id, $arr);
                    if (!$mppr->revalidado($input->id)) {
                        if ($mppr->revalidar($input->id) == true) {
                            $this->_helper->json(array('success' => true, 'token' => $input->token));
                        }
                    } else {
                        $this->_helper->json(array('success' => true, 'token' => $input->token));
                    }
                } else {
                    throw new Exception('Parámetros no válidos.');
                }
            } else {
                throw new Exception('Tipo de solicitud no válida.');
            }
        } catch (Exception $ex) {
            $this->_helper->json(array('success' => false, 'message' => $ex->getMessage()));
        }
    }

    public function addItemAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "username" => "StringToLower",
                    "idGuia" => "Digits",
                    "idFactura" => "Digits",
                    "idItem" => "Digits",
                );
                $v = array(
                    "username" => "NotEmpty",
                    "token" => "NotEmpty",
                    "idGuia" => "NotEmpty",
                    "idFactura" => "NotEmpty",
                    "idItem" => "NotEmpty",
                    "descripcion" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("username") && $input->isValid("token") && $input->isValid("idGuia") && $input->isValid("idFactura") && $input->isValid("idItem")) {
                    $mppr = new Webservice_Model_TraficoBitacoraItems();
                    if (!($id = $mppr->verificar($input->idGuia, $input->idFactura, $input->idItem))) {
                        $mppr->agregar($input->idGuia, $input->idFactura, $input->idItem, $input->descripcion, $input->username);
                    } else {
                        $arr = array(
                            "descripcion" => $input->descripcion,
                            "actualizado" => date("Y-m-d H:i:s"),
                            "actualizadoPor" => $input->username,
                        );
                        $mppr->actualizar($id, $arr);
                    }
                    $this->_helper->json(array('success' => true));
                } else {
                    throw new Exception('Parámetros no válidos para actualizar factura ' . $input->idGuia . " " . $input->idFactura . " " . $input->numFactura);
                }
            } else {
                throw new Exception('Tipo de solicitud no válida.');
            }
        } catch (Exception $ex) {
            $this->_helper->json(array('success' => false, 'message' => $ex->getMessage()));
        }
    }

    public function updateItemAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "username" => "StringToLower",
                    "idGuia" => "Digits",
                    "idFactura" => "Digits",
                    "idItem" => "Digits",
                    "descripcion" => "StringToUpper",
                    "marca" => "StringToUpper",
                    "modelo" => "StringToUpper",
                    "numeroSerie" => "StringToUpper",
                    "numeroParte" => "StringToUpper",
                    "numeroLote" => "StringToUpper",
                    "paisOrigen" => "StringToUpper",
                );
                $v = array(
                    "username" => "NotEmpty",
                    "token" => "NotEmpty",
                    "idGuia" => "NotEmpty",
                    "idFactura" => "NotEmpty",
                    "idItem" => "NotEmpty",
                    "descripcion" => "NotEmpty",
                    "cantidad" => "NotEmpty",
                    "peso" => "NotEmpty",
                    "marca" => "NotEmpty",
                    "modelo" => "NotEmpty",
                    "numeroSerie" => "NotEmpty",
                    "numeroParte" => "NotEmpty",
                    "numeroLote" => "NotEmpty",
                    "paisOrigen" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("username") && $input->isValid("token") && $input->isValid("idGuia") && $input->isValid("idFactura") && $input->isValid("idItem")) {
                    $mppr = new Webservice_Model_TraficoBitacoraItems();
                    if (!($id = $mppr->verificar($input->idGuia, $input->idFactura, $input->idItem))) {
                        throw new Exception('No existe ITEM ' . $input->idGuia . " " . $input->idFactura . " " . $input->idItem);
                    } else {
                        $arr = array(
                            "descripcion" => mb_strtoupper($input->descripcion),
                            "cantidad" => $input->cantidad,
                            "peso" => $input->peso,
                            "marca" => mb_strtoupper($input->marca),
                            "modelo" => mb_strtoupper($input->modelo),
                            "paisOrigen" => mb_strtoupper($input->paisOrigen),
                            "numeroSerie" => mb_strtoupper($input->numeroSerie),
                            "numeroParte" => mb_strtoupper($input->numeroParte),
                            "numeroLote" => mb_strtoupper($input->numeroLote),
                            "actualizado" => date("Y-m-d H:i:s"),
                            "actualizadoPor" => $input->username,
                        );
                        $mppr->actualizar($id, $arr);
                    }
                    $this->_helper->json(array('success' => true));
                } else {
                    throw new Exception('Parámetros no válidos para actualizar item ' . $input->idGuia . " " . $input->idFactura . " " . $input->idItem);
                }
            } else {
                throw new Exception('Tipo de solicitud no válida.');
            }
        } catch (Exception $ex) {
            $this->_helper->json(array('success' => false, 'message' => $ex->getMessage()));
        }
    }
    
    public function updateStatusAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "username" => "StringToLower",
                    "idGuia" => "Digits",
                    "estatus" => "Digits",
                );
                $v = array(
                    "username" => "NotEmpty",
                    "token" => "NotEmpty",
                    "idGuia" => "NotEmpty",
                    "estatus" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("username") && $input->isValid("token") && $input->isValid("idGuia") && $input->isValid("estatus")) {
                    $mppr = new Bitacora_Model_BitacoraPedimentos();
                    $mppr->update($input->idGuia, array("estatus" => $input->estatus));
                    $this->_helper->json(array('success' => true));
                } else {
                    throw new Exception('Parámetros no válidos para actualizar estatus ' . $input->idGuia . " " . $input->estatus);
                }
            } else {
                throw new Exception('Tipo de solicitud no válida.');
            }
        } catch (Exception $ex) {
            $this->_helper->json(array('success' => false, 'message' => $ex->getMessage()));
        }
    }

    public function updateInvoiceAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "username" => "StringToLower",
                    "idGuia" => "Digits",
                    "idFactura" => "Digits",
                    "numFactura" => "StringToUpper",
                    "nomProveedor" => "StringToUpper",
                );
                $v = array(
                    "username" => "NotEmpty",
                    "token" => "NotEmpty",
                    "idGuia" => "NotEmpty",
                    "idFactura" => "NotEmpty",
                    "numFactura" => "NotEmpty",
                    "nomProveedor" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("username") && $input->isValid("token") && $input->isValid("idGuia") && $input->isValid("idFactura")) {
                    $mppr = new Webservice_Model_TraficoBitacoraFacturas();
                    if (!($id = $mppr->verificar($input->idGuia, $input->idFactura))) {
                        $mppr->agregar($input->idGuia, $input->idFactura, $input->numFactura, $input->nomProveedor, $input->username);
                    } else {                        
                        if ($input->numFactura != $mppr->numFactura($id)) {
                            $mppr->actualizarNombre($id, $input->numFactura, $input->nomProveedor, $input->username);
                        }
                        if ($input->nomProveedor != $mppr->proveedorFactura($id)) {
                            $mppr->actualizarNombre($id, $input->numFactura, $input->nomProveedor, $input->username);                            
                        }
                    }
                    $this->_helper->json(array('success' => true));
                } else {
                    throw new Exception('Parámetros no válidos para actualizar factura ' . $input->idGuia . " " . $input->idFactura . " " . $input->numFactura);
                }
            } else {
                throw new Exception('Tipo de solicitud no válida.');
            }
        } catch (Exception $ex) {
            $this->_helper->json(array('success' => false, 'message' => $ex->getMessage()));
        }
    }

    public function checkUploadAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "username" => "StringToLower",
                    "idGuia" => "Digits",
                    "idFactura" => "Digits",
                    "idItem" => "Digits",
                );
                $v = array(
                    "username" => "NotEmpty",
                    "idGuia" => array("NotEmpty", new Zend_Validate_Int()),
                    "idFactura" => array("NotEmpty", new Zend_Validate_Int()),
                    "idItem" => array("NotEmpty", new Zend_Validate_Int()),
                    "token" => "NotEmpty",
                    "imageName" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("username") && $input->isValid("token") && $input->isValid("idGuia") && $input->isValid("idFactura") && $input->isValid("idItem") && $input->isValid("image")) {

                    $mppr = new Webservice_Model_TraficoBitacoraFotos();
                    if (!$mppr->verificar($input->idGuia, $input->idFactura, $input->idItem, trim($input->imageName))) {
                        $this->_helper->json(array('success' => false));
                    } else {
                        $this->_helper->json(array('success' => true));
                    }
                } else {
                    throw new Exception('Parámetros no válidos para subir fotos.');
                }
            } else {
                throw new Exception('Tipo de solicitud no válida.');
            }
        } catch (Exception $ex) {
            $this->_helper->json(array('success' => false, 'message' => $ex->getMessage()));
        }
    }

    public function uploadImageAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "username" => "StringToLower",
                    "idGuia" => "Digits",
                    "idFactura" => "Digits",
                    "idItem" => "Digits",
                );
                $v = array(
                    "username" => "NotEmpty",
                    "idGuia" => "NotEmpty",
                    "idFactura" => "NotEmpty",
                    "idItem" => "NotEmpty",
                    "token" => "NotEmpty",
                    "imageName" => "NotEmpty",
                    "image" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("username") && $input->isValid("token") && $input->isValid("idGuia") && $input->isValid("idFactura") && $input->isValid("idItem") && $input->isValid("imageName") && $input->isValid("image")) {

                    $mppr = new Webservice_Model_TraficoBitacoraFotos();
                    if (!$mppr->verificar($input->idGuia, $input->idFactura, $input->idItem, trim($input->imageName))) {
                        $directory = "D:\\Tmp\\movil";
                        if (APPLICATION_ENV == "production") {
                            $directory = "/home/samba-share/expedientes/fotos" . DIRECTORY_SEPARATOR . date("Y") . DIRECTORY_SEPARATOR . str_pad(date("m"), 2, '0', STR_PAD_LEFT) . DIRECTORY_SEPARATOR . str_pad(date("d"), 2, '0', STR_PAD_LEFT);
                        } else if (APPLICATION_ENV == "staging") {
                            $directory = "/home/samba-share/expedientes/fotos" . DIRECTORY_SEPARATOR . date("Y") . DIRECTORY_SEPARATOR . str_pad(date("m"), 2, '0', STR_PAD_LEFT) . DIRECTORY_SEPARATOR . str_pad(date("d"), 2, '0', STR_PAD_LEFT);
                            
                        }
                        if (!file_exists($directory)) {
                            mkdir($directory, 0777, true);
                        }
                        $filename = $directory . DIRECTORY_SEPARATOR . $input->imageName;
                        file_put_contents($directory . DIRECTORY_SEPARATOR . $input->imageName, base64_decode($input->image));
                        if (file_exists($filename)) {
                            if(($id = $mppr->agregar($input->idGuia, $input->idFactura, $input->idItem, $input->imageName, $filename, $input->username))) {
                                if (APPLICATION_ENV == "production" || APPLICATION_ENV == "staging") {
                                    $fileName = pathinfo($filename, PATHINFO_FILENAME);
                                    $ext = pathinfo($filename, PATHINFO_EXTENSION);
                                    if ($this->resize(250, $directory . DIRECTORY_SEPARATOR . $fileName . "_thumb", $filename)) {
                                        $mppr->actualizar($id, $directory . DIRECTORY_SEPARATOR . $fileName . "_thumb." . $ext);
                                    }
                                }
                            }                            
                        }
                    }
                    $this->_helper->json(array('success' => true));
                } else {
                    throw new Exception("Parámetros no válidos para subir fotos.");
                }
            } else {
                throw new Exception("Tipo de solicitud no válida.");
            }
        } catch (Exception $ex) {
            $this->_helper->json(array('success' => false, 'message' => $ex->getMessage()));
        }
    }
    
    protected function resize($newWidth, $targetFile, $originalFile) {
        $info = getimagesize($originalFile);
        $mime = $info['mime'];
        switch ($mime) {
                case 'image/jpeg':
                        $image_create_func = 'imagecreatefromjpeg';
                        $image_save_func = 'imagejpeg';
                        $new_image_ext = 'jpg';
                        break;

                case 'image/png':
                        $image_create_func = 'imagecreatefrompng';
                        $image_save_func = 'imagepng';
                        $new_image_ext = 'png';
                        break;

                case 'image/gif':
                        $image_create_func = 'imagecreatefromgif';
                        $image_save_func = 'imagegif';
                        $new_image_ext = 'gif';
                        break;
                default: 
                        throw new Exception('Unknown image type.');
        }

        $img = $image_create_func($originalFile);
        list($width, $height) = getimagesize($originalFile);
        $newHeight = ($height / $width) * $newWidth;
        $tmp = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($tmp, $img, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        if (file_exists($targetFile)) {
                unlink($targetFile);
        }
        $image_save_func($tmp, "$targetFile.$new_image_ext");
        return true;
    }

    public function updateHeaderInvoiceAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "username" => "StringToLower",
                    "id" => "Digits",
                );
                $v = array(
                    "username" => "NotEmpty",
                    "id" => array("NotEmpty", new Zend_Validate_Int()),
                    "token" => "NotEmpty",
                    "bultos" => "NotEmpty",
                    "numFacturas" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("username") && $input->isValid("id") && $input->isValid("token") && $input->isValid("bultos") && $input->isValid("numFacturas")) {
                    $mppr = new Bitacora_Model_BitacoraPedimentos();
                    $row = $mppr->obtenerDatos($input->id);
                    if (!empty($row)) {
                        if ((int) $input->bultos == (int) $row["bultos"] && (int) $input->numFacturas == (int) $row["numFacturas"]) {
                            $this->_helper->json(array('success' => true, 'token' => $input->token));
                        }
                        $arr = array(
                            "bultos" => $input->bultos,
                            "numFacturas" => $input->numFacturas,
                        );
                        if ($mppr->update($input->id, $arr) == true) {
                            $this->_helper->json(array('success' => true, 'token' => $input->token));
                        } else {
                            $this->_helper->json(array('success' => false, 'message' => 'No se puse actualizar.'));
                        }
                    } else {
                        $this->_helper->json(array('success' => false, 'message' => 'El registro no existe.'));
                    }
                } else {
                    throw new Exception('Parámetros no válidos.');
                }
            } else {
                throw new Exception('Tipo de solicitud no válida.');
            }
        } catch (Exception $ex) {
            $this->_helper->json(array('success' => false, 'message' => $ex->getMessage()));
        }
    }

    public function downloadVersionAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "username" => "StringToLower",
                );
                $v = array(
                    "username" => "NotEmpty",
                    "token" => "NotEmpty",
                    "filename" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("username") && $input->isValid("token") && $input->isValid("filename")) {
                    if (APPLICATION_ENV == 'production') {
                        $filename = realpath("/home/samba-share/app" . DIRECTORY_SEPARATOR . $input->filename);   
                    } else {
                        $filename = realpath("D:\\Tmp\\movil" . DIRECTORY_SEPARATOR . $input->filename);                        
                    }
                    if (file_exists($filename)) {
                        header("Cache-Control: no-cache private");
                        header("Content-Description: File Transfer");
                        header('Content-disposition: attachment; filename="' . basename($filename) . '"');
                        header("Content-Transfer-Encoding: binary");
                        header('Content-Length: '. filesize($filename));
                        readfile($filename);
                        exit;
                    }
                    $this->_helper->json(array('success' => true, 'filename' => $filename, 'token' => $input->token));
                } else {
                    throw new Exception('Parámetros no válidos.');
                }
            } else {
                throw new Exception('Tipo de solicitud no válida.');
            }
        } catch (Exception $ex) {
            $this->_helper->json(array('success' => false, 'message' => $ex->getMessage()));
        }
    }

    public function mercanciaLiberadaAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "token" => "StringToLower",
                );
                $v = array(
                    "token" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("token")) {
                    $db = Zend_Registry::get("oaqintranet");
                    $secret = "38f616560b12";
                    $token = sha1(date("YmdH") . $secret);
                    if ($input->token == $token) {
                        $sqlm = $db->select()
                                ->from(array("t" => "traficos"), array(
                                    "count(id) AS total",
                                ))
                                ->where("YEAR(t.fechaLiberacion) = ?", (int) date("Y"))
                                ->where("t.estatus <> 4");
                        $merc = $db->fetchRow($sqlm);
                        $sqlc = $db->select()
                                ->from(array("c" => "trafico_clientes"), array(
                            "count(id) AS total",
                        ));
                        $cli = $db->fetchRow($sqlc);
                        $sqld = $db->select()
                                ->from(array("t" => "traficos"), array(
                                    "count(id) AS total",
                                ))
                                ->where("t.fechaLiberacion >= ?", date("Y-m-d" . " 00:00:00"))
                                ->where("t.fechaLiberacion <= ?", date("Y-m-d" . " 23:59:59"))
                                ->where("t.estatus <> 4");
                        $mercd = $db->fetchRow($sqld);
                        $sqlv = $db->select()
                                ->from(array("v" => "vucem_solicitudes"), array(
                                    "count(id) AS total",
                                ))
                                ->where("YEAR(v.enviado) = ?", (int) date("Y"))
                                ->where("v.estatus = 2");
                        $cove = $db->fetchRow($sqlv);
                        $sqle = $db->select()
                                ->from(array("v" => "vucem_edoc"), array(
                                    "count(id) AS total",
                                ))
                                ->where("YEAR(v.enviado) = ?", (int) date("Y"))
                                ->where("v.estatus = 2");
                        $edocs = $db->fetchRow($sqle);
                        $this->_helper->json(array('success' => true, 'data' => array('mercancia' => $merc["total"], 'clientes' => $cli["total"], 'dia' => $mercd["total"], 'coves' => $cove["total"], 'edocs' => $edocs["total"])));
                    } else {
                        $this->_helper->json(array('success' => true, 'message' => 'Token no válido'));
                    }
                } else {
                    throw new Exception('Parámetros no válidos.');
                }
            } else {
                throw new Exception('Tipo de solicitud no válida.');
            }
        } catch (Exception $ex) {
            $this->_helper->json(array('success' => false, 'message' => $ex->getMessage()));
        }
    }

    public function appVersionAction() {
        try {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $f = array(
                    "*" => array("StringTrim", "StripTags"),
                    "username" => "StringToLower",
                );
                $v = array(
                    "username" => "NotEmpty",
                    "token" => "NotEmpty",
                );
                $input = new Zend_Filter_Input($f, $v, $request->getPost());
                if ($input->isValid("username") && $input->isValid("token")) {                    
                    $mppr = new Webservice_Model_AppVersion();
                    $arr = $mppr->ultimaVersion("oaq-movil");
                    if (!empty($arr)) {
                        $this->_helper->json(array('success' => true, 'versionName' => $arr['versionName'], 'versionCode' => $arr['versionCode'], 'appName' => $arr['filename'], 'token' => $input->token));                        
                    } else {
                        $this->_helper->json(array('success' => false, 'message' => 'No hay aplicaciones nuevas.', 'token' => $input->token));
                    }                    
                } else {
                    throw new Exception('Parámetros no válidos.');
                }
            } else {
                throw new Exception('Tipo de solicitud no válida.');
            }
        } catch (Exception $ex) {
            $this->_helper->json(array('success' => false, 'message' => $ex->getMessage()));
        }
    }

    public function postAction() {
        
    }

    public function putAction() {
        
    }

    public function deleteAction() {
        
    }

}
