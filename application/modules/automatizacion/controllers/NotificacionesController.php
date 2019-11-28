<?php

class Automatizacion_NotificacionesController extends Zend_Controller_Action {

    protected $_config;
    protected $_logger;

    public function init() {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_logger = Zend_Registry::get("logDb");
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
    }

    protected function _mailStorage($tipo) {
        try {
            if ($tipo == 'notificaciones') {
                return new Zend_Mail_Transport_Smtp($this->_config->app->notificaciones->smtp, array(
                    'user' => $this->_config->app->notificaciones->email,
                    'password' => $this->_config->app->notificaciones->pass
                        )
                );
            }
        } catch (Exception $e) {
            throw new Exception("<p><b>IMAP storage exception " . __METHOD__ . " :</b> <br><b>Exception found:</b>{$e->getMessage()}, <br><b>Line:</b> {$e->getLine()}, <br><b>File:</b> {$e->getFile()}, <br><b>Trace:</b> {$e->getTraceAsString()}</p>");
        }
    }

    /**
     * /automatizacion/notificaciones/enviar-notificacion
     * 
     * @return boolean
     */
    public function enviarNotificacionAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
        } else {
            return false;
        }
        if (isset($post["tipo"])) {
            $transport = $this->_mailStorage('notificaciones');
            Zend_Mail::setDefaultReplyTo('no-responder@oaq.com.mx', 'No responder');
            $mail = new Zend_Mail('UTF-8');
            if ($post["tipo"] == 200) {  // nuevo comentario
                $agenda = new Trafico_Model_AgendaMapper();
                $traffic = new Trafico_Model_TraficosMapper();

                $model = new Trafico_Model_ComentariosMapper();
                $message = $model->obtener($post["id"]);
                if (isset($message) && !empty($message) && $post["tipo"] == 200) {

                    $arr = $traffic->infoTraficoComentario($post["id"]);
                    $contacts = $agenda->obtenerContactosComentario($post["id"], "operacion");

                    if (isset($contacts) && !empty($contacts)) {
                        foreach ($contacts as $contact) {
                            $mail->addTo($contact["email"], $contact["nombre"]);
                        }
                        $mail->addTo("soporte@oaq.com.mx", "Soporte OAQ");
                        $mail->setFrom($this->_config->app->notificaciones->email, 'Notificaciones OAQ');
                        $mail->setSubject("[" . $arr["patente"] . "-" . $arr["aduana"] . "]Actualizacion de referencia {$message["referencia"]}: nuevo mensaje ");
                        $mail->setBodyHtml($this->_nuevoMensaje($message["nombre"], $arr["aduana"] . '-' . $arr["patente"] . '-' . $arr["pedimento"], $message["referencia"], $message["mensaje"]));
                        $mail->send($transport);
                        echo serialize(array('success' => true));
                    }
                } else {
                    echo serialize(array('success' => false));
                }
            }
            if ($post["tipo"] == 100) { // creacion de referencia                
                $arr = $traffic->obtenerPorId($post["id"]);
                $contacts = $agenda->obtenerContactosTrafico($post["id"], "operacion");
                if (isset($contacts) && !empty($contacts)) {
                    
                }
            }
        }
    }

    protected function _nuevoMensaje($usuario, $pedimento, $referencia, $mensaje) {
        $fontfamily = "font-family: sans-serif; ";
        $fontsize = "font-size: 12px; ";
        $mp = "margin: 5px; padding: 4px 5px; ";
        $html = "<p style=\"{$mp}{$fontfamily}{$fontsize}\">El usuario {$usuario} ha agregado un nuevo mensaje al pedimento {$pedimento} referencia {$referencia}.</p>"
                . "<p style=\"{$mp}{$fontfamily}{$fontsize}\"><em>Mensaje:</em> {$mensaje}</p>";
        return $this->_body("Nuevo comentario en referencia", $html);
    }

    protected function _body($title, $html) {
        $fontfamily = "font-family: sans-serif; ";
        $fontsizemini = "font-size: 11px; ";
        $mp = "margin: 5px; padding: 4px 5px; ";
        return "<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />
<body>
    <img style=\"{$mp}\" src=\"http://oaq.com.mx/img/oaq_notificacion.png\" >
    <h3 style=\"{$mp}{$fontfamily}\">{$title}</h3>
    <br>{$html}<br>
    <p style=\"{$mp}{$fontfamily}{$fontsizemini}\">AVISO: Este mensaje (incluyendo cualquier archivo anexo) es confidencial y personal. En caso de que usted lo reciba por error favor de notificar a soporte@oaq.com.mx regresando este correo electrónico y eliminando este mensaje de su sistema. Cualquier uso o difusión en forma total o parcial del mensaje así como la distribución o copia de este comunicado está estrictamente prohibida. Favor de tomar en cuenta que los e-mails son susceptibles de cambio.</p>
    <p style=\"{$mp}{$fontfamily}{$fontsizemini}\"><em>DISCLAIMER: This message (including any attachments) is confidential and may be privileged. If you have received it by mistake please notify soporte@oaq.com.mx by returning this e-mail and delete this message from your system. Any unauthorized use or dissemination of this message in whole or in part and distribution or copying of this communication is strictly prohibited. Please note that e-mails are susceptible to change.</em></p>
</body>
</html>";
    }

}
