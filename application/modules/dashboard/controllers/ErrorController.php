<?php

class Dashboard_ErrorController extends Zend_Controller_Action {

    protected $_config;
    protected $_appconfig;
    protected $_redirector;

    public function init() {
        $this->_helper->layout->setLayout("dashboard/default");
        $this->_appconfig = new Application_Model_ConfigMapper();
        $this->_redirector = $this->_helper->getHelper("Redirector");
        $this->view->headLink(array("rel" => "icon shortcut", "href" => "/favicon.png"));
        $this->view->headScript()->exchangeArray(array());
        $this->view->headLink()
                ->appendStylesheet("/gentelella/vendors/bootstrap/dist/css/bootstrap.min.css")
                ->appendStylesheet("/gentelella/vendors/font-awesome/css/font-awesome.min.css")
                ->appendStylesheet("/gentelella/vendors/nprogress/nprogress.css")
                ->appendStylesheet("/gentelella/build/css/custom.min.css");
        $this->view->headScript()
                ->appendFile("/gentelella/vendors/jquery/dist/jquery.min.js")
                ->appendFile("/gentelella/vendors/bootstrap/dist/js/bootstrap.min.js")
                ->appendFile("/gentelella/vendors/fastclick/lib/fastclick.js")
                ->appendFile("/gentelella/vendors/nprogress/nprogress.js")
                ->appendFile("/gentelella/vendors/bootstrap-progressbar/bootstrap-progressbar.min.js")
                ->appendFile("/gentelella/vendors/iCheck/icheck.min.js")
                ->appendFile("/gentelella/build/js/custom.js?" . time());
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
    }

    public function preDispatch() {
    }

    public function forbiddenAction() {
        $this->view->title = "403 Denegado";
        $this->view->headMeta()->appendName("description", "");
    }

}
