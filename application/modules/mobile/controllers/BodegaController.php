<?php

class Mobile_BodegaController extends Zend_Controller_Action
{

    protected $_session;
    protected $_config;
    protected $_appconfig;
    protected $_redirector;
    protected $_firephp;

    public function init()
    {
        $this->_helper->layout->setLayout("mobile/traficos");
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_redirector = $this->_helper->getHelper("Redirector");
        $this->view->headScript()
            ->appendFile("/js/common/jquery-1.9.1.min.js")
            ->appendFile("/mobile/bootstrap/js/bootstrap.min.js")
            ->appendFile("/mobile/popper.js/dist/umd/popper.min.js")
            ->appendFile("/js/common/jquery.form.min.js")
            ->appendFile("/js/common/jquery.validate.min.js")
            ->appendFile("/js/common/js.cookie.js")
            ->appendFile("/js/common/jquery.blockUI.js");
        $this->view->headLink()
            ->appendStylesheet("/mobile/bootstrap/css/bootstrap.min.css")
            ->appendStylesheet("/mobile/fontawesome/css/all.css")
            ->appendStylesheet("/mobile/common/styles.css");
        $this->view->headLink(array("rel" => "icon shortcut", "href" => "/favicon.png"));
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
        $this->_firephp = Zend_Registry::get("firephp");
    }

    public function preDispatch()
    {
        $this->_session = NULL ? $this->_session = new Zend_Session_Namespace("") : $this->_session = new Zend_Session_Namespace("OAQmobile");
        if ($this->_session->authenticated == true) {
            $session = new OAQ_Session($this->_session, $this->_appconfig);
            $session->actualizar();
            $session->actualizarSesion(Zend_Controller_Front::getInstance()->getRequest()->getRequestUri());
        } else {
            $this->getResponse()->setRedirect("/mobile/main/logout");
        }
    }

    public function indexAction()
    {
        $this->view->title = "Bodega";
        $this->view->headMeta()->appendName("description", "");

        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "search" => array("StringToUpper")
        );
        $v = array(
            "search" => "NotEmpty"
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($input->isValid("search")) {
            $this->view->search = $input->search;
        }

        $mppr = new Mobile_Model_Traficos();
        $rows = $mppr->obtenerReferenciasBodega($input->search);

        $this->view->rows = $rows;
    }

    public function entradaAction()
    {
        $this->view->title = "Bodega";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
            ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css");
        $this->view->headScript()
            ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
            ->appendFile("/js/common/loadingoverlay.min.js")
            ->appendFile("/mobile/common/html5-qrcode-master/minified/html5-qrcode.min.js")
            ->appendFile("/mobile/common/bodega/entrada.js?" . time());
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "id" => array("Digits"),
            "estatus" => array("StringToLower"),
        );
        $v = array(
            "id" => array(new Zend_Validate_Int(), new Zend_Validate_NotEmpty()),
            "estatus" => array(new Zend_Validate_NotEmpty()),
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($input->isValid("id") && $input->isValid("estatus")) {
            $mppr = new Mobile_Model_Traficos();
            $arr = $mppr->obtener($input->id);
            $this->view->row = $arr;
            $this->view->estatus = $input->estatus;
        }
    }

    public function escanearAction()
    {
        $this->view->title = "Bodega";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
            ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css");
        $this->view->headScript()
            ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
            ->appendFile("/js/common/loadingoverlay.min.js")
            ->appendFile("/mobile/common/html5-qrcode-master/minified/html5-qrcode.min.js")
            ->appendFile("/mobile/common/bodega/escanear.js?" . time());
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "id" => array("Digits"),
            "estatus" => array("StringToLower"),
        );
        $v = array(
            "id" => array(new Zend_Validate_Int(), new Zend_Validate_NotEmpty()),
            "estatus" => array(new Zend_Validate_NotEmpty()),
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($input->isValid("id") && $input->isValid("estatus")) {
            $this->view->id = $input->id;
            $this->view->estatus = $input->estatus;
        } else {
            throw new Exception("Invalid input!");
        }
    }

    public function editarBultoAction()
    {
        $this->view->title = "Bodega";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
            ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css");
        $this->view->headScript()
            ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
            ->appendFile("/js/common/loadingoverlay.min.js")
            ->appendFile("/mobile/common/html5-qrcode-master/minified/html5-qrcode.min.js")
            ->appendFile("/mobile/common/bodega/editar-bulto.js?" . time());
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "id" => array("Digits"),
            "estatus" => array("StringToLower"),
        );
        $v = array(
            "id" => array(new Zend_Validate_Int(), new Zend_Validate_NotEmpty()),
            "estatus" => array(new Zend_Validate_NotEmpty()),
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($input->isValid("id") && $input->isValid("estatus")) {
            $this->view->id = $input->id;

            $mppr = new Bodega_Model_Bultos();
            $row = $mppr->obtenerBulto($input->id);

            $this->view->row = $row;
            $this->view->estatus = $input->estatus;

            // Zend_Debug::dump($row);
        }
    }

    public function archivosAction()
    {
        $this->view->title = "Bodega";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
            ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css");
        $this->view->headScript()
            ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
            ->appendFile("/js/common/loadingoverlay.min.js")
            ->appendFile("/mobile/common/bodega/archivos.js?" . time());
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "id" => array("Digits"),
            "estatus" => array("StringToLower"),
        );
        $v = array(
            "id" => array(new Zend_Validate_Int(), new Zend_Validate_NotEmpty()),
            "estatus" => array(new Zend_Validate_NotEmpty()),
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($input->isValid("id") && $input->isValid("estatus")) {
            $mppr = new Mobile_Model_Traficos();
            $arr = $mppr->obtener($input->id);
            
            $this->view->row = $arr;
            $this->view->estatus = $input->estatus;

            $mppr = new Trafico_Model_TraficosMapper();
            $array = $mppr->obtenerPorId($input->id);

            $repo = new Archivo_Model_RepositorioMapper();

            $traficos = new OAQ_Bodega(array("idTrafico" => $input->id, "idBodega" => $array['idBodega']));
            if (($arr = $traficos->traficosConsolidados())) {
                $referencias = array($array["referencia"]);
                foreach ($arr as $item) {
                    $referencias[] = $item["referencia"];
                }
                $archivos = $repo->obtenerArchivosReferencia($referencias);
            } else {
                $archivos = $repo->obtenerArchivosReferencia($array["referencia"]);
            }
            $this->view->archivos = $archivos;

            $trafico = new OAQ_Bodega(array("idTrafico" => $input->id, "idBodega" => $array['idBodega'], "usuario" => $this->_session->username, "idUsuario" => $this->_session->id));
            $index = $trafico->verificarIndexRepositorios();

            if (in_array($this->_session->role, array("super", "gerente", "trafico_ejecutivo", "trafico"))) {
                $this->view->canDelete = true;
            }
        } else {
            throw new Exception("Invalid input!");
        }
    }

    public function fotosAction()
    {
        $this->view->title = "Bodega";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headLink()
            ->appendStylesheet("/v2/js/common/confirm/jquery-confirm.min.css");
        $this->view->headScript()
            ->appendFile("/v2/js/common/confirm/jquery-confirm.min.js")
            ->appendFile("/js/common/loadingoverlay.min.js")
            ->appendFile("/mobile/common/bodega/fotos.js?" . time());
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "id" => array("Digits"),
            "estatus" => array("StringToLower"),
        );
        $v = array(
            "id" => array(new Zend_Validate_Int(), new Zend_Validate_NotEmpty()),
            "estatus" => array(new Zend_Validate_NotEmpty()),
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($input->isValid("id") && $input->isValid("estatus")) {

            $mppr = new Mobile_Model_Traficos();
            $arr = $mppr->obtener($input->id);

            $this->view->row = $arr;
            $this->view->estatus = $input->estatus;

            $gallery = new Trafico_Model_Imagenes();
            $thumbs = $gallery->miniaturas($input->id);
            $this->view->gallery = $thumbs;
        } else {
            throw new Exception("Invalid input!");
        }
    }

    public function comentariosAction()
    {
        $this->view->title = "Bodega";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
            ->appendFile("/mobile/common/bodega/comentarios.js?" . time());
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "id" => array("Digits"),
            "estatus" => array("StringToLower"),
        );
        $v = array(
            "id" => array(new Zend_Validate_Int(), new Zend_Validate_NotEmpty()),
            "estatus" => array(new Zend_Validate_NotEmpty()),
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($input->isValid("id") && $input->isValid("estatus")) {
            $mppr = new Mobile_Model_Traficos();
            $arr = $mppr->obtener($input->id);

            $this->view->row = $arr;
            $this->view->estatus = $input->estatus;

            $trafico = new OAQ_Trafico(array("idTrafico" => $input->id, "usuario" => $this->_session->username, "idUsuario" => $this->_session->id));
            $this->view->comments = $trafico->obtenerComentarios();;
        } else {
            throw new Exception("Invalid input!");
        }
    }

    public function bitacoraAction()
    {
        $this->view->title = "Bodega";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
            ->appendFile("/mobile/common/bodega/comentarios.js?" . time());
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "id" => array("Digits"),
            "estatus" => array("StringToLower"),
        );
        $v = array(
            "id" => array(new Zend_Validate_Int(), new Zend_Validate_NotEmpty()),
            "estatus" => array(new Zend_Validate_NotEmpty()),
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($input->isValid("id") && $input->isValid("estatus")) {
            $mppr = new Mobile_Model_Traficos();
            $arr = $mppr->obtener($input->id);
            
            $this->view->row = $arr;
            $this->view->estatus = $input->estatus;

            $trafico = new OAQ_Trafico(array("idTrafico" => $input->id, "usuario" => $this->_session->username, "idUsuario" => $this->_session->id));
            $this->view->bitacora = $trafico->obtenerBitacoraBodega();
        } else {
            throw new Exception("Invalid input!");
        }
    }

    public function agregarFacturaAction()
    {
        $this->view->title = "Bodega";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
            ->appendFile("/mobile/common/bodega/agregar-factura.js?" . time());
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "id" => array("Digits"),
            "estatus" => array("StringToLower"),
        );
        $v = array(
            "id" => array(new Zend_Validate_Int(), new Zend_Validate_NotEmpty()),
            "estatus" => array(new Zend_Validate_NotEmpty()),
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($input->isValid("id") && $input->isValid("estatus")) {
            $mppr = new Mobile_Model_Traficos();
            $arr = $mppr->obtener($input->id);

            $this->view->row = $arr;
            $this->view->estatus = $input->estatus;
        }
    }

    public function agregarBultoAction()
    {
        $this->view->title = "Bodega";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
            ->appendFile("/mobile/common/bodega/agregar-bulto.js?" . time());
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "id" => array("Digits"),
            "estatus" => array("StringToLower"),
        );
        $v = array(
            "id" => array(new Zend_Validate_Int(), new Zend_Validate_NotEmpty()),
            "estatus" => array(new Zend_Validate_NotEmpty()),
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($input->isValid("id") && $input->isValid("estatus")) {
            $this->view->id = $input->id;
            
            $mppr = new Mobile_Model_Traficos();
            $arr = $mppr->obtener($input->id);

            $this->view->row = $arr;            
            $this->view->estatus = $input->estatus;

        } else {
            throw new Exception("Invalid input!");
        }
    }

    public function editarFacturaAction()
    {
        $this->view->title = "Bodega";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
            ->appendFile("/mobile/common/bodega/agregar-factura.js?" . time());
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "id" => array("Digits"),
            "estatus" => array("StringToLower"),
        );
        $v = array(
            "id" => array(new Zend_Validate_Int(), new Zend_Validate_NotEmpty())
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($input->isValid("id")) {
            $mppr = new Trafico_Model_TraficoFacturasMapper();
            $row = $mppr->informacionFactura($input->id);

            $mdl = new Mobile_Model_Traficos();
            $arr = $mdl->obtener($row['idTrafico']);
            $this->view->row = $arr;
        }
    }

    public function agregarGuiaAction()
    {
        $this->view->title = "Bodega";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
            ->appendFile("/mobile/common/bodega/agregar-guia.js?" . time());
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "id" => array("Digits"),
            "estatus" => array("StringToLower"),
        );
        $v = array(
            "id" => array(new Zend_Validate_Int(), new Zend_Validate_NotEmpty())
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($input->isValid("id")) {
            $mppr = new Mobile_Model_Traficos();
            $arr = $mppr->obtener($input->id);
            $this->view->row = $arr;
        }
    }

    public function editarGuiaAction()
    {
        $this->view->title = "Bodega";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
            ->appendFile("/mobile/common/bodega/agregar-guia.js?" . time());
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "id" => array("Digits"),
            "estatus" => array("StringToLower"),
        );
        $v = array(
            "id" => array(new Zend_Validate_Int(), new Zend_Validate_NotEmpty())
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($input->isValid("id")) {
            $model = new Trafico_Model_TraficoGuiasMapper();
            $row = $model->obtenerGuia($input->id);

            $mppr = new Mobile_Model_Traficos();
            $arr = $mppr->obtener($row['idTrafico']);
            $this->view->row = $arr;
        }
    }
}
