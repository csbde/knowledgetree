#!/bin/sh
#
# script to checkout the latest tagged build from cvs and upload
# to the remote server of your choice

## functions

# displays the script usage message 
#
usage() {
    echo "usage: `basename $0` -t cvstag -h user@host -d remoteDir"
    echo "       eg. `basename $0` -t DMS_ITERATION1_29012003 -h michael@gobbler.jamwarehouse.com -d /usr/local/www/owl/dms"
    exit 1
}

deploy() {
    # cleanup
    rm -rf $tmp 2> /dev/null
    mkdir $tmp

    # export owl
    cd $tmp
    cvs -d $cvsroot co -r $tag owl
    cd owl/Documents
    cvs update -d

    # remove CVS directories
    find $tmp -name CVS -exec rm -rf {} \; 2> /dev/null

    # tar it up
    tar -czvf /tmp/owl.tgz $tmp
    
    # clean up
    rm -rf $tmp 2> /dev/null

    # punt it over the wall
    scp /tmp/owl.tgz $host:/tmp/

    # untar it remotely
    ssh $host "cd $remotePath; mv $remoteDir $remoteDir-`date +%Y-%m-%d`; tar -zxvf /tmp/owl.tgz; rm /tmp/owl.tgz; mv tmp/dms/owl $remoteDir; rm -rf tmp"
}

# check the command line options
if [ $# -lt 3 ]; then
    usage
fi

# process the params
while getopts ":t:h:d:" Option
do
  case $Option in
    t   ) tag=$OPTARG;;
    h   ) host=$OPTARG;;
    d   ) dir=$OPTARG ;;
    *   ) usage;;
  esac
done

# check that everything we want is set
if [ -z $tag -o -z $host -o -z $dir ]; then
    usage
fi 

# setup up some paths and stuff
cvsroot=/usr/local/cvsroot
tmp=/tmp/dms
remotePath=`dirname $dir`
remoteDir=`basename $dir`

# now just do it
# TODO: return code handling
deploy

# reminder
echo "don't forget to run fix_perms.sh on the remote machine as root to allow the www user access to the dms"
