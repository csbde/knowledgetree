#!/bin/sh

DIR=`dirname $0`
cd $DIR

case $1 in
'') db=dms-test ;;
*) db=$1 ;;
esac

mysqladmin -u root -f drop $db
mysqladmin -u root create $db
mysql -u root $db < structure.sql
mysql -u root $db < data.sql
mysql -u root $db < user.sql

