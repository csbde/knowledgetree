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
/sbin/umount $LOCAL_MOUNT

# clear the backup mount point
/bin/rm -rf $LOCAL_MOUNT
/bin/mkdir -p $LOCAL_MOUNT

# mount the server
/sbin/mount_smbfs -N "$BACKUP_SERVER" $LOCAL_MOUNT

# tar up the cvs repository
archive=mrc_dms_`date +%Y-%m-%d`.tgz
/usr/bin/tar czvf /tmp/$archive $BACKUP_FROM

# copy to backup server
/bin/cp /tmp/$archive "$BACKUP_TO"

# check that its there
/bin/ls -al "$BACKUP_TO"

# clean up
/bin/rm /tmp/$archive

# disconnect twice (for safety and because the first try consistently doesn't work)
/sbin/umount $LOCAL_MOUNT
/sbin/umount $LOCAL_MOUNT

exit
