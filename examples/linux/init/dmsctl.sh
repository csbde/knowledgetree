#!/bin/sh

# Boot KnowledgeTree services
# chkconfig: 2345 55 25
# description: KnowledgeTree Services
#
# processname: ktdms 

HOSTNAME=`hostname`
RETVAL=0
PID=""
ERROR=0
SERVER=all
VDISPLAY="99"
INSTALL_PATH=/opt/ktdms
JAVABIN=$INSTALL_PATH/java/jre/bin/java
export MAGICK_HOME=$INSTALL_PATH/common
export LD_LIBRARY_PATH="$INSTALL_PATH/apache2/lib:$INSTALL_PATH/common/lib:$INSTALL_PATH/mysql/lib:$LD_LIBRARY_PATH"
export PATH=$PATH:$INSTALL_PATH/php/bin
export PHPRC=$INSTALL_PATH/php/etc

# LDAP
export LDAPCONF=$INSTALL_PATH/common/etc/openldap/ldap.conf

# Apache
HTTPD_PIDFILE=$INSTALL_PATH/apache2/logs/httpd.pid
HTTPD_PID=""
HTTPD="$INSTALL_PATH/apache2/bin/httpd -f $INSTALL_PATH/apache2/conf/httpd.conf"
HTTPD_STATUS=""

# MySQL
MYSQL_PIDFILE=$INSTALL_PATH/mysql/data/mysqld.pid
MYSQL_PID=""
#MYSQL_START="$INSTALL_PATH/mysql/bin/safe_mysqld --port=3306 --socket=$INSTALL_PATH/mysql/tmp/mysql.sock --old-passwords --datadir=$INSTALL_PATH/mysql/data --pid-file=$INSTALL_PATH/mysql/data/mysqld.pid"
MYSQL_START="$INSTALL_PATH/mysql/bin/safe_mysqld --defaults-file=${INSTALL_PATH}/mysql/my.cnf --old-passwords --datadir=$INSTALL_PATH/mysql/data --log-error=$INSTALL_PATH/mysql/data/mysqld.log --pid-file=$INSTALL_PATH/mysql/data/mysqld.pid"
MYSQL_STOP="$INSTALL_PATH/mysql/bin/mysqladmin --defaults-file=${INSTALL_PATH}/mysql/my.cnf -u root -p shutdown"
MYSQL_STATUS=""
MYSQL_PASSWORD=""

# Agent
AGENT_PIDFILE="$INSTALL_PATH/updates/agent.pid"
AGENT_PID=""
AGENT="$INSTALL_PATH/updates/agent.bin"
AGENT_STATUS=""
AGENT_BIN=agent.bin

# OpenOffice
SOFFICE_PATH="$INSTALL_PATH/openoffice/program"
SOFFICE_PIDFILE=$INSTALL_PATH/openoffice/soffice.bin.pid
SOFFICE_PID=""
SOFFICE_PORT="8100"
SOFFICEBIN=$INSTALL_PATH/openoffice/program/soffice.bin
#SOFFICE="$SOFFICEBIN -nofirststartwizard -nologo -headless -accept=pipe,name=pypipe;urp;StarOffice.ServiceManager"
SOFFICE="$SOFFICEBIN -nofirststartwizard -nologo -headless -accept=socket,host=127.0.0.1,port=$SOFFICE_PORT;urp;StarOffice.ServiceManager"
SOFFICE_STATUS=""

# Lucene
LUCENE_PIDFILE=$INSTALL_PATH/knowledgeTree/bin/luceneserver/lucene.pid
LUCENE_PID=""
LUCENE="$JAVABIN -Xms512M -Xmx512M -jar ktlucene.jar"
LUCENE_STATUS=""

# Scheduler
SCHEDULER_PATH="$INSTALL_PATH/bin/"
SCHEDULER_PIDFILE=$INSTALL_PATH/bin/scheduler.pid
SCHEDULER_PID=""
SCHEDULERBIN="$INSTALL_PATH/bin/schedulerTask.sh"
SCHEDULER="$SCHEDULERBIN"
SCHEDULER_STATUS=""

get_pid() {
    PID=""
    PIDFILE=$1
    # check for pidfile
    if [ -f $PIDFILE ] ; then
        exec 6<&0
        exec < $PIDFILE
        read pid
        PID=$pid
        exec 0<&6 6<&-
    fi
}

get_apache_pid() {
    get_pid $HTTPD_PIDFILE
    if [ ! $PID ]; then
        return 
    fi
    if [ $PID -gt 0 ]; then
        HTTPD_PID=$PID
    fi
}

get_agent_pid() {
    get_pid $AGENT_PIDFILE
    if [ ! $PID ]; then
        return 
    fi
    if [ $PID -gt 0 ]; then
        AGENT_PID=$PID
    fi
}

get_mysql_pid() {
    get_pid $MYSQL_PIDFILE
    if [ ! $PID ]; then
        return 
    fi
    if [ $PID -gt 0 ]; then
        MYSQL_PID=$PID
    fi
}

get_soffice_pid() {
    get_pid $SOFFICE_PIDFILE
    if [ ! $PID ]; then
        return 
    fi
    if [ $PID -gt 0 ]; then
        SOFFICE_PID=$PID
    fi
}

get_lucene_pid() {
    get_pid $LUCENE_PIDFILE
    if [ ! $PID ]; then
        return 
    fi
    if [ $PID -gt 0 ]; then
        LUCENE_PID=$PID
    fi
}

get_scheduler_pid() {
    get_pid $SCHEDULER_PIDFILE
    if [ ! $PID ]; then
        return 
    fi
    if [ $PID -gt 0 ]; then
        SCHEDULER_PID=$PID
    fi
}

is_service_running() {
    PID=$1
    if [ "x$PID" != "x" ] && kill -0 $PID 2>/dev/null ; then
        RUNNING=1
    else
        RUNNING=0
    fi
    return $RUNNING
}

is_mysql_running() {
    get_mysql_pid
    is_service_running $MYSQL_PID
    RUNNING=$?
    if [ $RUNNING -eq 0 ]; then
        MYSQL_STATUS="mysql not running"
    else
        MYSQL_STATUS="mysql already running"
    fi
    return $RUNNING
}

is_agent_running() {
    get_agent_pid
    is_service_running $AGENT_PID
    RUNNING=$?
    if [ $RUNNING -eq 0 ]; then
        AGENT_STATUS="agent not running"
    else
        AGENT_STATUS="agent already running"
    fi
    return $RUNNING
}

is_apache_running() {
    get_apache_pid
    is_service_running $HTTPD_PID
    RUNNING=$?
    if [ $RUNNING -eq 0 ]; then
        HTTPD_STATUS="apache not running"
    else
        HTTPD_STATUS="apache already running"
    fi
    return $RUNNING
}

is_soffice_running() {
    get_soffice_pid
    is_service_running $SOFFICE_PID
    RUNNING=$?
    if [ $RUNNING -eq 0 ]; then
        SOFFICE_STATUS="openoffice not running"
    else
        SOFFICE_STATUS="openoffice already running"
    fi
    return $RUNNING
}

is_lucene_running() {
    get_lucene_pid
    is_service_running $LUCENE_PID
    RUNNING=$?
    if [ $RUNNING -eq 0 ]; then
        LUCENE_STATUS="lucene not running"
    else
        LUCENE_STATUS="lucene already running"
    fi
    return $RUNNING
}

is_scheduler_running() {
    get_scheduler_pid
    is_service_running $SCHEDULER_PID
    RUNNING=$?
    if [ $RUNNING -eq 0 ]; then
        SCHEDULER_STATUS="scheduler not running"
    else
        SCHEDULER_STATUS="scheduler already running"
    fi
    return $RUNNING
}

test_apache_config() {
    if $HTTPD -t; then
        ERROR=0
    else
        ERROR=8
        echo "apache config test fails, aborting"
        exit $ERROR
    fi
}

start_mysql() {
    is_mysql_running
    RUNNING=$?
    if [ $RUNNING -eq 1 ]; then
        echo "$0 $ARG: mysql  (pid $MYSQL_PID) already running"
    else
        $MYSQL_START &> $INSTALL_PATH/var/log/dmsctl.log &
        if [ $? -eq 0 ]; then
            echo "$0 $ARG: mysql started at port 3306"
            sleep 2
        else
            echo "$0 $ARG: mysql could not be started"
            ERROR=3
        fi
    fi
}

stop_mysql() {
    NO_EXIT_ON_ERROR=$1
    is_mysql_running
    RUNNING=$?
    if [ $RUNNING -eq 0 ]; then
        echo "$0 $ARG: $MYSQL_STATUS"
        if [ "x$NO_EXIT_ON_ERROR" != "xno_exit" ]; then
            exit
        else
            return
        fi
	fi
    kill -15 $MYSQL_PID
    sleep 5
    
    is_mysql_running
    RUNNING=$?
    if [ $RUNNING -eq 0 ]; then
	    echo "$0 $ARG: mysql stopped"
	else
	    echo "$0 $ARG: mysql could not be stopped"
	    ERROR=4
	fi
}

start_agent() {
    is_agent_running
    RUNNING=$?
    if [ $RUNNING -eq 1 ]; then
        echo "$0 $ARG: agent (pid $AGENT_PID) already running"
    else
         $AGENT &> $INSTALL_PATH/var/log/dmsctl.log &
         sleep 5
         get_agent_pid
         if [ $AGENT_PID -gt 0 ]; then
             echo "$0 $ARG: agent started"
         else
             echo "$0 $ARG: agent could not be started"
             ERROR=3
         fi
    fi
}

stop_agent() {
    NO_EXIT_ON_ERROR=$1
    is_agent_running
    RUNNING=$?

    if [ $RUNNING -eq 0 ]; then
        echo "$0 $ARG: $AGENT_STATUS"
        if [ "x$NO_EXIT_ON_ERROR" != "xno_exit" ]; then
            exit
        else
            return
        fi
    fi
    get_agent_pid
    if kill $AGENT_PID ; then
        echo "$0 $ARG: agent stopped"
    else
        echo "$0 $ARG: agent could not be stopped"
        ERROR=4
    fi
}

start_apache() {
    test_apache_config
    is_apache_running
    RUNNING=$?

    if [ $RUNNING -eq 1 ]; then
        echo "$0 $ARG: httpd (pid $HTTPD_PID) already running"
    else
        if $HTTPD &> $INSTALL_PATH/var/log/dmsctl.log; then
            echo "$0 $ARG: httpd started at port 8080"
        else
            echo "$0 $ARG: httpd could not be started"
            ERROR=3
        fi
fi
}

stop_apache() {
    NO_EXIT_ON_ERROR=$1
    test_apache_config
    is_apache_running
    RUNNING=$?

    if [ $RUNNING -eq 0 ]; then
        echo "$0 $ARG: $HTTPD_STATUS"
        if [ "x$NO_EXIT_ON_ERROR" != "xno_exit" ]; then
            exit
        else
            return
        fi
	fi
    get_apache_pid
	if kill $HTTPD_PID ; then
	    echo "$0 $ARG: httpd stopped"
	else
	    echo "$0 $ARG: httpd could not be stopped"
	    ERROR=4
	fi
}

start_soffice() {
    is_soffice_running
    RUNNING=$?

    if [ $RUNNING -eq 1 ]; then
        echo "$0 $ARG: openoffice (pid $SOFFICE_PID) already running"
    else
        nohup $SOFFICE &> $INSTALL_PATH/var/log/dmsctl.log &
        if [ $? -eq 0 ]; then
            echo "$0 $ARG: openoffice started at port $SOFFICE_PORT"
            ps ax | grep $SOFFICEBIN | awk {'print $1'} > $SOFFICE_PIDFILE
            sleep 2
        else
            echo "$0 $ARG: openoffice could not be started"
            ERROR=3
        fi
fi
}

stop_soffice() {
    NO_EXIT_ON_ERROR=$1
    is_soffice_running
    RUNNING=$?

    if [ $RUNNING -eq 0 ]; then
        echo "$0 $ARG: $SOFFICE_STATUS"
        if [ "x$NO_EXIT_ON_ERROR" != "xno_exit" ]; then
            exit
        else
            return
        fi
    fi
    get_soffice_pid
	if killall $SOFFICEBIN ; then
	    echo "$0 $ARG: openoffice stopped"
	else
	    echo "$0 $ARG: openoffice could not be stopped"
	    ERROR=4
	fi
}

start_lucene() {
    is_lucene_running
    RUNNING=$?

    if [ $RUNNING -eq 1 ]; then
        echo "$0 $ARG: lucene (pid $LUCENE_PID) already running"
    else
        cd $INSTALL_PATH/knowledgeTree/bin/luceneserver
        nohup $LUCENE  &> $INSTALL_PATH/var/log/dmsctl.log &
        if [ $? -eq 0 ]; then
            echo "$0 $ARG: lucene started"
            ps ax | grep ktlucene.jar | awk {'print $1'} > $LUCENE_PIDFILE
            sleep 2
        else
            echo "$0 $ARG: lucene could not be started"
            ERROR=3
        fi
        cd $INSTALL_PATH
fi
}

stop_lucene() {
    NO_EXIT_ON_ERROR=$1
    is_lucene_running
    RUNNING=$?

    if [ $RUNNING -eq 0 ]; then
        echo "$0 $ARG: $LUCENE_STATUS"
        if [ "x$NO_EXIT_ON_ERROR" != "xno_exit" ]; then
            exit
        else
            return
        fi
	fi
    get_lucene_pid
    cd $INSTALL_PATH/knowledgeTree/search2/indexing/bin
    $INSTALL_PATH/php/bin/php shutdown.php positive &> $INSTALL_PATH/var/log/dmsctl.log
    sleep 5
    if [ $? -eq 0 ]; then
	    echo "$0 $ARG: lucene stopped"
	else
	    echo "$0 $ARG: lucene could not be stopped"
	    ERROR=4
	fi
}

start_scheduler() {
    is_scheduler_running
    RUNNING=$?

    if [ $RUNNING -eq 1 ]; then
        echo "$0 $ARG: scheduler (pid $SCHEDULER_PID) already running"
    else
        cd $SCHEDULER_PATH
        nohup $SCHEDULER  &> $INSTALL_PATH/var/log/dmsctl.log &
        if [ $? -eq 0 ]; then
            echo "$0 $ARG: scheduler started"
            ps ax | grep $SCHEDULERBIN | awk {'print $1'} > $SCHEDULER_PIDFILE
            sleep 2
        else
            echo "$0 $ARG: scheduler could not be started"
            ERROR=3
        fi
    fi
}

stop_scheduler() {
    NO_EXIT_ON_ERROR=$1
    is_scheduler_running
    RUNNING=$?

    if [ $RUNNING -eq 0 ]; then
        echo "$0 $ARG: $SCHEDULER_STATUS"
        if [ "x$NO_EXIT_ON_ERROR" != "xno_exit" ]; then
            exit
        else
            return
        fi
	fi
    get_scheduler_pid
	if kill $SCHEDULER_PID ; then
	    echo "$0 $ARG: scheduler stopped"
	else
	    echo "$0 $ARG: scheduler could not be stopped"
	    ERROR=4
	fi
}

help() {
	echo "usage: $0 help"
	echo "       $0 (start|stop|restart)"
	echo "       $0 (start|stop|restart) apache"
	echo "       $0 (start|stop|restart) mysql"
	echo "       $0 (start|stop|restart) agent"
	echo "       $0 (start|stop|restart) scheduler"
	echo "       $0 (start|stop|restart) soffice"
	echo "       $0 (start|stop|restart) lucene"
	cat <<EOF

help       - this screen
start      - start the service(s)
stop       - stop  the service(s)
restart    - restart or start the service(s)

EOF
exit 0
}

noserver() {
       echo -e "ERROR: $1 is not a valid server. Please, select 'mysql', 'apache', 'agent', 'scheduler', 'soffice' or 'lucene'\n"
       help
}

[ $# -lt 1 ] && help

if [ ! -z ${2} ]; then
       [ "${2}" != "mysql" ] && [ "${2}" != "apache" ] && [ "${2}" != "agent" ] && [ "${2}" != "scheduler" ] && [ "${2}" != "soffice" ] && [ "${2}" != "lucene" ] && noserver $2
       SERVER=$2
fi
       

if [ "x$3" != "x" ]; then
    MYSQL_PASSWORD=$3
fi


case $1 in
       help)   help
               ;;
       start)
               if [ "${SERVER}" != "all" ]; then
                       start_${2}
               else
                       start_mysql
                       start_apache
                       start_agent
                       start_soffice
                       start_lucene
                       start_scheduler
               fi
               ;;
       stop)   if [ "${SERVER}" != "all" ]; then
                       stop_${2}
               else
                       stop_scheduler "no_exit"
                       stop_lucene "no_exit"
                       stop_soffice "no_exit"
                       stop_apache "no_exit"
                       stop_agent "no_exit"
                       stop_mysql
               fi
               ;;
       restart)        if [ "${SERVER}" != "all" ]; then
                               stop_${2} "no_exit"
                               sleep 2
                               start_${2}
                       else
                               stop_scheduler "no_exit"
                               stop_lucene "no_exit"
                               stop_soffice "no_exit"
                               stop_apache "no_exit"
                               stop_agent "no_exit"
                               stop_mysql "no_exit"
                               start_mysql
                               start_apache
                               start_agent
                               start_soffice
                               start_lucene
                               start_scheduler
                       fi
               ;;
esac

exit $ERROR
