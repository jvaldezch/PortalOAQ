<?php

class Dashboard_MainController extends Zend_Controller_Action {

    protected $_session;
    protected $_config;
    protected $_appconfig;
    protected $_redirector;
    protected $_firephp;

    public function init() {
        $this->_helper->layout->setLayout("dashboard/default");
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_redirector = $this->_helper->getHelper("Redirector");
        $this->_firephp = Zend_Registry::get("firephp");
        $this->view->headLink(array("rel" => "icon shortcut", "href" => "/favicon.png"));
        $this->view->headScript()->exchangeArray(array());
        $this->view->headLink()
                ->appendStylesheet("/gentelella/vendors/bootstrap/dist/css/bootstrap.min.css")
                ->appendStylesheet("/gentelella/vendors/font-awesome/css/font-awesome.min.css")
                ->appendStylesheet("/gentelella/vendors/nprogress/nprogress.css")
                ->appendStylesheet("/gentelella/vendors/iCheck/skins/flat/green.css")
                ->appendStylesheet("/gentelella/vendors/bootstrap-progressbar/css/bootstrap-progressbar-3.3.4.min.css")
                ->appendStylesheet("/gentelella/vendors/jqvmap/dist/jqvmap.min.css")
                ->appendStylesheet("/gentelella/vendors/bootstrap-daterangepicker/daterangepicker.css")
                ->appendStylesheet("/gentelella/vendors/switchery/dist/switchery.min.css")
                ->appendStylesheet("/gentelella/vendors/dropzone/dist/min/dropzone.min.css")
                ->appendStylesheet("/gentelella/build/css/custom.min.css");
        $this->view->headScript()
                ->appendFile("/gentelella/vendors/jquery/dist/jquery.min.js")
                ->appendFile("/gentelella/vendors/bootstrap/dist/js/bootstrap.min.js")
                ->appendFile("/gentelella/vendors/fastclick/lib/fastclick.js")
                ->appendFile("/gentelella/vendors/nprogress/nprogress.js")
                ->appendFile("/gentelella/vendors/Chart.js/dist/Chart.min.js")
                ->appendFile("/gentelella/vendors/gauge.js/dist/gauge.min.js")
                ->appendFile("/gentelella/vendors/bootstrap-progressbar/bootstrap-progressbar.min.js")
                ->appendFile("/gentelella/vendors/iCheck/icheck.min.js")
                ->appendFile("/gentelella/vendors/skycons/skycons.js")
                ->appendFile("/gentelella/vendors/Flot/jquery.flot.js")
                ->appendFile("/gentelella/vendors/Flot/jquery.flot.pie.js")
                ->appendFile("/gentelella/vendors/Flot/jquery.flot.time.js")
                ->appendFile("/gentelella/vendors/Flot/jquery.flot.stack.js")
                ->appendFile("/gentelella/vendors/Flot/jquery.flot.resize.js")
                ->appendFile("/gentelella/vendors/flot.orderbars/js/jquery.flot.orderBars.js")
                ->appendFile("/gentelella/vendors/flot-spline/js/jquery.flot.spline.min.js")
                ->appendFile("/gentelella/vendors/flot.curvedlines/curvedLines.js")
                ->appendFile("/gentelella/vendors/DateJS/build/date.js")
                ->appendFile("/gentelella/vendors/jqvmap/dist/jquery.vmap.js")
                ->appendFile("/gentelella/vendors/jqvmap/dist/maps/jquery.vmap.world.js")
                ->appendFile("/gentelella/vendors/jqvmap/examples/js/jquery.vmap.sampledata.js")
                ->appendFile("/gentelella/vendors/moment/min/moment.min.js")
                ->appendFile("/gentelella/vendors/moment/locale/es.js")
                ->appendFile("/gentelella/vendors/parsleyjs/dist/parsley.js")
                ->appendFile("/gentelella/vendors/bootstrap-daterangepicker/daterangepicker.js")
                ->appendFile("/gentelella/vendors/dropzone/dist/min/dropzone.min.js")
                ->appendFile("/gentelella/vendors/switchery/dist/switchery.min.js")
                ->appendFile("/js/common/jquery.form.min.js")
                ->appendFile("/js/dashboard/main/custom.js?" . time());
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
    }

    public function preDispatch() {
        $this->_session = null ? $this->_session = new Zend_Session_Namespace("") : $this->_session = new Zend_Session_Namespace("Dashboard");
    }

    public function indexAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Dashboard";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
                ->appendFile("/js/dashboard/main/index.js?" . time());
        $f = array(
            "*" => array("StringTrim", "StripTags"),
            "year" => array("Digits"),
            "month" => array("Digits"),
        );
        $v = array(
            "code" => "NotEmpty",
            "year" => array("NotEmpty", new Zend_Validate_Int()),
            "month" => array("NotEmpty", new Zend_Validate_Int()),
        );
        $input = new Zend_Filter_Input($f, $v, $this->_request->getParams());
        if ($input->isValid("code")) {
            $mapper = new Dashboard_Model_ClientesDbs();
            $arr = $mapper->buscarIdentificador($input->code);
            if ($arr) {
                $this->view->nomCliente = $arr["nombre"];
                $mdl = new Trafico_Model_TraficoAduanasMapper();
                $this->view->aduanas = $mdl->aduanasDashboard();
            } else {
                $this->getResponse()->setRedirect("/dashboard/error/forbidden");
            }
        } else {
            $this->getResponse()->setRedirect("/dashboard/error/forbidden");
        }
    }
        
    public function panelAction() {
        $this->view->title = $this->_appconfig->getParam("title") . " Dashboard Panel";
        $this->view->headMeta()->appendName("description", "");
        $this->view->headScript()
                ->appendFile("/js/dashboard/main/panel.js?" . time());
        
        $this->_session = new Zend_Session_Namespace("OAQDashboard");

        if (isset($this->_session->code)) {            
            if (!isset($this->_session->rfcCliente)) {
                $this->view->login = true;
                $this->_helper->redirector->gotoUrl("/");
            }
            $this->view->nomCliente = $this->_session->nomCliente;
        } else {
            $this->_helper->redirector->gotoUrl("/");
        }
    }
        
    public function logoutAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $session = new Zend_Session_Namespace("OAQDashboard");
        try {
            $session->unsetAll();
            Zend_Session::destroy(true);
            $this->_helper->redirector->gotoUrl("/");
        } catch (Zend_Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function weatherAction() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $mapper = new Dashboard_Model_Clima();
        $arr = $mapper->obtener();
        if (isset($arr["json"])) {
            $json = json_decode($arr["json"]);
        }
    }

}
