#!/bin/sh

DIR=`dirname $0`
cd $DIR

case $1 in
'') DB=dms_clean ;;
*) DB=$1 ;;
esac

PATH=$PATH:../../../../mysql/bin:/usr/local/mysql/bin
DATE=`date +%Y-%m-%d-%H-%M-%S`

# Create the Structure Dump
mysqldump -u root -p $DB --no-data --skip-add-drop-table > structure-$DATE.sql

# Create the Data Dump
mysqldump -u root -p $DB --no-create-info > data-$DATE.sql
