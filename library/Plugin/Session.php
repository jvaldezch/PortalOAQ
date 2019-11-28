<?php

class Plugin_Session extends Zend_Controller_Plugin_Abstract {
    
    protected $session;
    
    /**
     * 
     * @param Zend_Controller_Request_Abstract $request
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request) {
        echo "Sample";        
    }

}
