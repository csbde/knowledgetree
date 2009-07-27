#!/bin/sh

cd /home/jarrett/ktdms/knowledgeTree/bin
while true; do
/home/jarrett/ktdms/php/bin/php -Cq scheduler.php
  sleep 30
done
