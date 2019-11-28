#!/bin/sh
mkdir - p /tmp/bakcuptmp
now="$(date +'%d_%m_%Y_%H_%M_%S')"
filename="db_oaqintranet_$now".gz
backupfolder="/tmp/backuptmp"
nasfolder="/mnt/nas/dbs/intranet"
fullpathbackupfile="$backupfolder/$filename"
fullpathnas="$nasfolder/$filename"
logfile="$backupfolder/"backup_log_oaqintranet_"$(date +'%Y_%m')".txt
echo "mysqldump started at $(date +'%d-%m-%Y %H:%M:%S')" >> "$logfile"
mysqldump --user=root --password=mysql11! --default-character-set=utf8 oaqintranet | gzip > "$fullpathbackupfile"
echo "mysqldump finished at $(date +'%d-%m-%Y %H:%M:%S')" >> "$logfile"
chown jvaldez:jvaldez "$fullpathbackupfile"
chown jvaldez:jvaldez "$logfile"
echo "file permission changed" >> "$logfile"
find "$backupfolder" -name db_backup_* -mtime +8 -exec rm {} \;
echo "old files deleted" >> "$logfile"
echo "operation finished at $(date +'%d-%m-%Y %H:%M:%S')" >> "$logfile"
cp "$fullpathbackupfile" "$fullpathnas"
echo "Backup copied to NAS" >> "$logfile"
echo "*****************" >> "$logfile"
exit 0
