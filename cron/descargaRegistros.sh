#!/bin/bash
FILENAME="descargaRegistros_`date +%Y%m%d_%H%M%S`.log"
curl -ks -m 600 --connect-timeout 600 --request GET "https://127.0.0.1/automatizacion/index/descarga-registros" > /var/www/portalprod/logs/$FILENAME

