#!/bin/bash
FILENAME="terminalEnviar_`date +%Y%m%d_%H%M%S`.log"
curl -ks -m 600 --connect-timeout 600 --request GET "https://127.0.0.1/automatizacion/email/enviar-facturas-terminal?fecha=`date +%Y-%m-%d`" > /var/www/portalprod/logs/$FILENAME
