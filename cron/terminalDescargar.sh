#!/bin/bash
FILENAME="terminalDescargar_`date +%Y%m%d_%H%M%S`.log"
curl -ksS -m 1200 --connect-timeout 1200 "https://127.0.0.1/automatizacion/email/facturas-terminal?fecha=`date +%Y-%m-%d`" > /dev/null
