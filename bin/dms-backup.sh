#!/usr/local/bin/bash
#
# backup.sh - archives and copies project cvs root to windoze file server
# todo: return code checking, mail status

# define paths
BACKUP_USER=jambackup
BACKUP_SERVER="//$BACKUP_USER@Jam001/JAM Backups"
LOCAL_MOUNT=/tmp/backup
BACKUP_FROM=/usr/local/cvsroot
BACKUP_TO=/tmp/backup/Backups\ to\ tape/

# check that we're not mounted already
umount $LOCAL_MOUNT

# clear the backup mount point
rm -rf $LOCAL_MOUNT
mkdir -p $LOCAL_MOUNT

# mount the server
mount_smbfs -N "$BACKUP_SERVER" $LOCAL_MOUNT

# tar onto the local mount point
archive=mrc_dms_`date +%Y-%m-%d`.tgz
tar czvf /tmp/$archive $BACKUP_FROM

# copy to backup server
cp /tmp/$archive "$BACKUP_TO"

# check that its there
ls -al "$BACKUP_TO"

# disconnect twice (for safety and because the first try consistently doesn't work)
umount $LOCAL_MOUNT
umount $LOCAL_MOUNT

exit
