#!/bin/sh
#
# script to checkout the latest tagged build from cvs and upload
# to the remote server of your choice

## functions

# displays the script usage message 
#
usage() {
    echo "usage: `basename $0` -b branch -v version"
    echo "       eg. `basename $0` -b BRANCH_1_0_1_20030728 -v 1.1.2"
    exit 1
}

deploy() {
    # cleanup
    rm -rf $tmp 2> /dev/null
    mkdir $tmp

    # export kt
    cd $tmp
    cvs -d $cvsroot co -r $branch -N knowledgeTree
    cvs -d $cvsroot co -r $branch -N "knowledgeTree/Documents/Root Folder/Default Unit"
    find . -name CVS -exec rm -rf {} \; 2> /dev/null

    # tar it up
    rm /tmp/knowledgeTree-$version.tgz 2> /dev/null
    tar -czvf /tmp/knowledgeTree-$version.tgz knowledgeTree

    # convert src to windoze line-endings
    find $tmp/knowledgeTree -name \*\.php -exec unix2dos {} \; 2> /dev/null
    find $tmp/knowledgeTree -name \*\.inc -exec unix2dos {} \; 2> /dev/null
    find $tmp/knowledgeTree -name \*\.txt -exec unix2dos {} \; 2> /dev/null
    
    # zip it up
    rm /tmp/knowledgeTree-$version.zip 2> /dev/null
    zip -r /tmp/knowledgeTree-$version.zip knowledgeTree
 
    # move them to this dir
    cd -
    mv /tmp/knowledgeTree-$version.* .

    # clean up
    rm -rf $tmp 2> /dev/null
}

# check the command line options
if [ $# -lt 2 ]; then
    usage
fi

# process the params
while getopts ":b:v:" Option
do
  case $Option in
    b   ) branch=$OPTARG;;
    v   ) version=$OPTARG;;
    *   ) usage;;
  esac
done

# check that everything we want is set
if [ -z $branch -o -z $version ]; then
    usage
fi 

# setup up some paths and stuff
cvsroot=/usr/local/cvsroot
tmp=/tmp/dms

# now just do it
deploy
