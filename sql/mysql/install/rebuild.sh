#!/bin/sh

db=ktpristine

mysqladmin -u root -f drop $db
mysqladmin -u root create $db
mysql -u root $db < structure.sql
mysql -u root $db < data.sql
mysql -u root $db < user.sql

