<?php

require_once 'PhpAmqpLib/Wire/GenericContent.php';
require_once 'PhpAmqpLib/Wire/IO/AbstractIO.php';
require_once 'PhpAmqpLib/Wire/IO/SocketIO.php';
require_once 'PhpAmqpLib/Wire/IO/StreamIO.php';
require_once 'PhpAmqpLib/Wire/AbstractClient.php';
require_once 'PhpAmqpLib/Wire/AMQPAbstractCollection.php';
require_once 'PhpAmqpLib/Wire/AMQPReader.php';
require_once 'PhpAmqpLib/Wire/AMQPWriter.php';
require_once 'PhpAmqpLib/Channel/AbstractChannel.php';
require_once 'PhpAmqpLib/Connection/AbstractConnection.php';
require_once 'PhpAmqpLib/Connection/AMQPStreamConnection.php';
require_once 'PhpAmqpLib/Connection/AMQPConnection.php';
require_once 'PhpAmqpLib/Message/AMQPMessage.php';
require_once 'PhpAmqpLib/Helper/DebugHelper.php';
require_once 'PhpAmqpLib/Helper/MiscHelper.php';
require_once 'PhpAmqpLib/Wire/Constants091.php';
require_once 'PhpAmqpLib/Helper/Protocol/Protocol091.php';
require_once 'PhpAmqpLib/Helper/Protocol/Wait091.php';
require_once 'PhpAmqpLib/Helper/Protocol/MethodMap091.php';
require_once 'PhpAmqpLib/Channel/AMQPChannel.php';
require_once 'PhpAmqpLib/Exception/AMQPException.php';
require_once 'PhpAmqpLib/Exception/AMQPExceptionInterface.php';
require_once 'PhpAmqpLib/Exception/AMQPChannelException.php';
require_once 'PhpAmqpLib/Exception/AMQPProtocolException.php';
require_once 'PhpAmqpLib/Exception/AMQPProtocolChannelException.php';
require_once 'PhpAmqpLib/Exception/AMQPProtocolConnectionException.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Description of Queues
 *
 * @author Jaime
 */
class OAQ_Workers_Queues {

    protected $host= (APPLICATION_ENV == "production") ? "192.168.200.11" : "127.0.0.1";

    function __construct() {
        
    }

    public function queues() {
        $cmd = "curl -i -u admin:rabbitmq11! http://127.0.0.1:15672/api/queues";
        $op = array();
        exec($cmd ,$op);
        if (isset($op[8])) {
            $arr = json_decode($op[8], true);
            if (!empty($arr)) {
                return $arr;
            }
            return;
        }
    }
    
    public function deleteQueue($queue) {
        $conn = new AMQPStreamConnection($this->host, 5672, 'admin', 'rabbitmq11!');
        $channel = $conn->channel();
        $channel->queue_declare($queue, false, true, false, false);
        $channel->basic_get($queue, true, true, false, false);
        $channel->close();
        $conn->close();
        return true;
    }

    protected function getChannelMessages($user) {
        $conn = new AMQPStreamConnection($this->host, 5672, $user, $this->password($user));
        if ($user == "pedimentos") {
            $channel = $conn->channel();
            $channel->queue_declare("estadoPedimento", false, true, false, false);
            $get = $channel->basic_get("estadoPedimento", false, true, false, false);
            Zend_Debug::Dump($get, "PEDIMENTOS");
            $channel->close();
            $conn->close();            
        }
        if ($user == "emails") {
            $channel = $conn->channel();
            $channel->queue_declare("enviarEmail", false, true, false, false);
            $get = $channel->basic_get("enviarEmail", false, true, false, false);
            Zend_Debug::Dump($get, "EMAILS");
            $channel->close();
            $conn->close();
        }
        if ($user == "ftp") {
            $channel = $conn->channel();
            $channel->queue_declare("ftpExpedientes", false, true, false, false);
            $get = $channel->basic_get("ftpExpedientes", false, true, false, false);
            Zend_Debug::Dump($get, "FTP");
            $channel->close();
            $conn->close();
        }
        if ($user == "validacion") {
            $channel = $conn->channel();
            $channel->queue_declare("archivosDePago", false, true, false, false);
            $get = $channel->basic_get("archivosDePago", false, true, false, false);
            Zend_Debug::Dump($get, "VALIDACION");
            $channel->close();
            $conn->close();
        }
    }
    
    protected function password($user) {
        switch ($user) {
            case "admin":
                return "rabbitmq11!";
            case "pedimentos":
                return "pedis2017!";
            case "emails":
                return "email2017!";
            case "coves":
                return "cove2017!";
            case "edocs":
                return "edoc2017!";
            case "ftp":
                return "ftp2017!";
            case "vucem":
                return "vucem2017!";
            case "validacion":
                return "validacion2017!";
            default:
                break;
        }
    }

}
