#!/bin/bash
curl -k -X GET "https://127.0.0.1/automatizacion/index/enviar-m3?rfc=DCM030212ET4&fecha=`date +%Y-%m-%d`" > /dev/null
curl -k -X GET "https://127.0.0.1/automatizacion/index/enviar-m3?rfc=ARB820712U77,CCO030908FU8,CCO0309098N8,CIN0309091D3,CME950209J18,CME930831D89,SME751021B90,RHM720412B61,FDQ7904066U0,DAM980101SR0&fecha=`date +%Y-%m-%d`" > /dev/null
curl -k -X GET "https://127.0.0.1/automatizacion/index/enviar-m3?rfc=JMM931208JY9&fecha=`date +%Y-%m-%d`" > /dev/null
su - www-data -c 'php /var/www/portalprod/m3_worker.php'
