<?php

class Automatizacion_GearmanController extends Zend_Controller_Action {

    /**
     * Initialization
     */
    public function init() {
        // Disables the render of any Layout and View
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
    }

    /**
     * Asynchronous message example
     */
    public function sendAsyncAction() {
        $clientGearman = Gearman_Manager::getClient();
        $data = array('message' => "Hello World Async");
        $clientGearman->doBackground("asyncMessage", serialize($data));
    }

    /**
     * Synchronous message example
     */
    public function sendSyncAction() {
        $clientGearman = Gearman_Manager::getClient();
        $data = array('message' => "Hello World Sync");
        $clientGearman->do("syncMessage", serialize($data));
    }
    
    public function killProcessAction() {
        exec("ps aux | grep 'magister' | grep -v grep | awk '{ print $2 }' | head -1", $out);
        print "PID:" . $out[0];
    }
    
    public function runWorkerAction() {
        $command = "php " . realpath(APPLICATION_PATH . '/../library/Gearman/run.php') . " magister DEVEL > /tmp/gearman.log &";
        echo $command;
        $output = shell_exec($command);
        Zend_Debug::Dump($output);
    }

}
