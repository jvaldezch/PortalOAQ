#!/bin/bash
FILENAME="terminalFacturas_`date +%Y%m%d_%H%M%S`.log"
curl -ks -m 1200 --connect-timeout 1200 --request GET "https://127.0.0.1/automatizacion/index/facturas?date=`date +%Y-%m-%d`" > /var/www/portalprod/logs/$FILENAME
