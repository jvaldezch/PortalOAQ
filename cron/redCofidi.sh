#!/bin/bash
FILENAME="redCofidi_`date +%Y%m%d_%H%M%S`.log"
curl -ks -m 600 --connect-timeout 600 "https://127.0.0.1/automatizacion/email/enviar-cdfi-cofidi" > /var/www/portalprod/logs/$FILENAME
