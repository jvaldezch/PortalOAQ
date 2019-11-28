#!/bin/bash
FILENAME="descargaAnexo24_`date +%Y%m%d_%H%M%S`.log"
curl -ks -m 1200 --connect-timeout 1200 https://127.0.0.1/automatizacion/ws/anexo-veinticuatro > /var/www/portalprod/logs/$FILENAME
