#!/bin/sh

cvsroot=/usr/local/cvsroot
tmp=/tmp/dms
tag=DMS_ITERATION1_29012003
scpUser=michael
remote=gobbler.jamwarehouse.com
remotePath=/usr/local/www/owl
remoteDir=dms-test

# cleanup
rm -rf $tmp* 2> /dev/null
mkdir $tmp

# export owl
cd $tmp
cvs -d $cvsroot co -r $tag owl
cd owl/Documents
cvs update -d

# tar it up
tar -czvf /tmp/owl.tgz $tmp

# punt it over the wall
scp /tmp/owl.tgz $scpUser@$remote:$remotePath

# untar it remotely
ssh $scpUser@$remote "cd $remotePath; mv $remoteDir $remoteDir-`date +%Y-%m-%d`; tar -zxvf owl.tgz; rm owl.tgz; mv tmp/dms/owl/* $remoteDir; rmdir tmp"
