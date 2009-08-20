#!/bin/sh
cd /var/www/installers/knowledgetree/bin/
while true; do
php -Cq scheduler.php
sleep 30
done