#!/bin/sh

DIR=`dirname $0`
cd $DIR

case $1 in
'') db=dms-test ;;
*) db=$1 ;;
esac
case $2 in
'') pass=password ;;
*) pass=$2 ;;
esac

mysqladmin -u root -p$pass -f drop $db
mysqladmin -u root -p$pass create $db
mysql -u root -p$pass $db < structure.sql
mysql -u root -p$pass $db < data.sql
mysql -u root -p$pass $db < user.sql

