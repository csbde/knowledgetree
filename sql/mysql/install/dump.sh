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

cat structure-$DATE.sql | sed 's/ AUTO_INCREMENT=[0-9]*//g' > structure.tmp
mv structure.tmp structure-$DATE.sql

# Create the Data Dump
mysqldump -u root -p $DB --no-create-info > data-$DATE.sql


sed "s/[)],[(]/),\n(/g"  data-$DATE.sql > data.tmp
sed "s/VALUES [(]/VALUES\n(/g"  data.tmp >  data-$DATE.sql
rm data.tmp
