#!/bin/sh

# displays the script usage message 
#
usage() {
    echo "usage: `basename $0` user:group"
    echo "       eg. `basename $0` www:wheel"
    exit 1
}

# check the command line options
if [ $# -lt 1 ]; then
    usage
fi

wwwroot=../`dirname $0`

chown -R $1 $wwwroot
chmod -R 750 $wwwroot/Documents/*
