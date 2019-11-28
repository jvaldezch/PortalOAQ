#!/bin/bash
FILENAME="enviarCoveftp_`date +%Y%m%d_%H%M%S`.log"
curl -ks --data "fecha=`date +%Y-%m-%d`" --data "patente=3589" https://127.0.0.1//automatizacion/index/send-cove-xml > /var/www/portalprod/logs/$FILENAME
