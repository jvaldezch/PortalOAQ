<?php

/**
 * Description of Gearman_Worker
 * http://blog.digitalstruct.com/2010/10/17/integrating-gearman-into-zend-framework/
 *
 */
class Gearman_Worker {

    /**
     * Register Function
     * @var string
     */
    protected $_registerFunction;

    /**
     * Gearman Timeout
     * @var int
     */
    protected $_timeout = 60;

    /**
     * Alloted Memory Limit in MB
     * @var int
     */
    protected $_memory = 256;

    /**
     * Error Message
     * @var string
     */
    protected $_error = null;

    /**
     * Gearman Worker
     * @var GearmanWorker
     */
    protected $_worker;

    /**
     * Memory limit activation
     * @var bool
     */
    protected $_enableMemoryLimit = false;
    protected $_logFile = null;

    /**
     * Constructor
     * Checks for the required gearman extension,
     * fetches the bootstrap and loads in the gearman worker
     */
    public function __construct($logFile = null) {
        $this->initRegisterFunctions();
        // Check extension
        if (!extension_loaded('gearman')) {
            throw new RuntimeException('The PECL::gearman extension is required.');
        }
        if (!empty($logFile)) {
            $this->_logFile = $logFile;
        }
        // Creates a new Gearman Worker and set its servers
        $this->_worker = Gearman_Manager::getWorker();
        $this->_worker->setTimeout($this->_timeout);
        // Checks the registerFunction
        if (empty($this->_registerFunction)) {
            throw new InvalidArgumentException(get_class($this)
            . ' must implement a registerFunction');
        }
        // allow for a small memory gap:
        if ($this->_enableMemoryLimit) {
            $memoryLimit = ($this->_memory + 128) * 1024 * 1024;
            ini_set('memory_limit', $memoryLimit);
        }
        // Registers
        if (is_array($this->_registerFunction)) {
            foreach ($this->_registerFunction as $alias => $func) {
                $this->_worker->addFunction($alias, array(&$this, $func));
            }
        } else {
            $this->_worker->addFunction($this->_registerFunction, array(&$this, 'work'));
        }
        $this->init();
        if (!empty($this->_logFile)) {
            Gearman_Manager::getLogger($this->_logFile)->log('[Worker initialized]', Zend_Log::INFO);
        }
        while ($this->_worker->work() || $this->_worker->returnCode() == GEARMAN_TIMEOUT) {
            // if a timeout ocurrs
            if ($this->_worker->returnCode() == GEARMAN_TIMEOUT) {
                $this->timeout();
                echo '[' . date('Y-m-dH:i:s') . "]: Timeout" . "\n";
                continue;
            }
            // if an error ocurrs
            if ($this->_worker->returnCode() != GEARMAN_SUCCESS) {
                $this->setError($this->_worker->returnCode() . ': ' . $this->_worker->getErrno() . ': ' . $this->_worker->error());
                break;
            }
        }
        $this->shutdown();
    }

    /**
     * Initialization of Register functions
     *
     * @return void
     */
    protected function initRegisterFunctions() {
        
    }

    /**
     * Initialization
     *
     * @return void
     */
    protected function init() {
        
    }

    /**
     * Handle Timeout
     *
     * @return void
     */
    protected function timeout() {
        
    }

    /**
     * Handle Shutdown
     *
     * @return void
     */
    protected function shutdown() {
        
    }

    /**
     * Set Error Message
     *
     * @param string $error
     * @return void
     */
    public function setError($error) {
        $this->_error = $error;
    }

    /**
     * Get Error Message
     *
     * @return string|null
     */
    public function getError() {
        return $this->_error;
    }

}
