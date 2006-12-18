mysqladmin -u root -p -f drop dms
mysqladmin -u root -p create dms
mysql -u root dms<structure.sql
mysql -u root -p dms< data.sql
mysql -u root -p dms<user.sql
