#!/bin/bash
FILES640="M3_640_`date +%Y%m%d_%H%M%S`.tar.bz2"
tar -cvjf /home/samba-share/expedientes/m3/backups/$FILES640 /home/samba-share/archivos_640/saai/`date +%Y`/*.*
FILES240="M3_240_`date +%Y%m%d_%H%M%S`.tar.bz2"
tar -cvjf /home/samba-share/expedientes/m3/backups/$FILES240 /home/samba-share/archivos_240/saai/`date +%Y`/*.*

