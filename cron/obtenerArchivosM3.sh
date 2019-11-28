#!/bin/bash
chown www-data:www-data -R /home/samba-share/archivos_646/saai/*
chown www-data:www-data -R /home/samba-share/archivos_640/saai/*
FILENAME="obtenerArchivosM3_`date +%Y%m%d_%H%M%S`.log"
curl -ks --data "date=`date +%Y-%m-%d`" https://127.0.0.1/automatizacion/index/obtener-archivos-m3 > /var/www/portalprod/logs/$FILENAME
FILENAME="analizarArchivosM3_`date +%Y%m%d_%H%M%S`.log"
curl -ks --data "date=`date +%Y-%m-%d`" https://127.0.0.1/automatizacion/index/analizar-archivos-m3 > /var/www/portalprod/logs/$FILENAME
