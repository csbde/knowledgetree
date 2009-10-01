#!/bin/bash
rm ../../../../bin/schedulerTask.sh;
rm ../../../../var/log/lucene.log;
rm ../../../../var/log/scheduler.log;
rm ../../../../var/log/openoffice.log;
rm ../../../../bin/luceneserver/KnowledgeTreeIndexer.properties;
rm ../../../../setup/wizard/output/outJV;
pkill -f lucene;
pkill -f scheduler;
pkill -f openoffice;

