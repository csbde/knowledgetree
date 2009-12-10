#!/bin/bash

# Boot KnowledgeTree services
# chkconfig: 2345 55 25
# description: KnowledgeTree Services
#
# processname: ktdms

cd $(dirname $0)

HOSTNAME=`hostname`
RETVAL=0
PID=""
ERROR=0
SERVER=all
VDISPLAY="99"
INSTALL_PATH=`pwd`
JAVABIN=/usr/bin/java
ZEND_DIR=/usr/local/zend

# exits if the UID is not 0 [root]
check_root_privileges()
{
    ID="id -u"
        MYUID=`$ID 2> /dev/null`
	    if [ ! -z "$MYUID" ]; then
	           if [ $MYUID != 0 ]; then
		           echo "You need root privileges to run this script!";
			   exit 1
		   fi
	    else
		echo "Could not detect UID";
		exit 1
	   fi
}


if [ -f /etc/zce.rc ];then
    . /etc/zce.rc
else
    echo "/etc/zce.rc doesn't exist!"
    exit 1;
fi
check_root_privileges

# OpenOffice
SOFFICEFILE=soffice
SOFFICE_PIDFILE=$INSTALL_PATH/var/log/soffice.bin.pid
SOFFICE_PID=""
SOFFICE_PORT="8100"
SOFFICEBIN=/usr/share/ktdms-office/ktdms-office/openoffice/program/soffice
SOFFICE="$SOFFICEBIN -nofirststartwizard -nologo -headless -accept=socket,host=127.0.0.1,port=$SOFFICE_PORT;urp;StarOffice.ServiceManager"
SOFFICE_STATUS=""

# Lucene
LUCENE_PIDFILE=$INSTALL_PATH/var/log/lucene.pid
LUCENE_PID=""
LUCENE="$JAVABIN -Xms512M -Xmx512M -jar ktlucene.jar"
LUCENE_STATUS=""

# Scheduler
SCHEDULER_PATH="$INSTALL_PATH/bin/"
SCHEDULER_PIDFILE=$INSTALL_PATH/var/log/scheduler.pid
SCHEDULER_PID=""
SCHEDULERBIN="$INSTALL_PATH/var/bin/schedulerTask.sh"
SCHEDULER="$SCHEDULERBIN"
SCHEDULER_STATUS=""

# MySQL: modify if needed for your installation
MYSQL_PATH=/etc/init.d
MYSQLBIN=mysql
MYSQL_PIDFILE=/var/run/mysqld/mysqld.pid
MYSQL_PID=""
MYSQL_STATUS=""

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

get_mysql_pid() {
    get_pid $MYSQL_PIDFILE
    if [ ! $PID ]; then
        return
    fi
    if [ $PID -gt 0 ]; then
        MYSQL_PID=$PID
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

start_soffice() {
    is_soffice_running
    RUNNING=$?

    if [ $RUNNING -eq 1 ]; then
        echo "$0 $ARG: openoffice (pid $SOFFICE_PID) already running"
    else
	if [ -x $SOFFICEBIN ]; then
        nohup $SOFFICE &> $INSTALL_PATH/var/log/dmsctl.log &
        if [ $? -eq 0 ]; then
            echo "$0 $ARG: openoffice started at port $SOFFICE_PORT"
            ps ax | grep $SOFFICEBIN | awk {'print $1'} > $SOFFICE_PIDFILE
            sleep 2
        else
            echo "$0 $ARG: openoffice could not be started"
            ERROR=3
        fi
    else
        echo "$0 $ARG: path to openoffice binary ($SOFFICEBIN) could not be found"
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
	if killall $SOFFICEFILE; then
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
        cd $INSTALL_PATH/bin/luceneserver
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
    cd $INSTALL_PATH/search2/indexing/bin
    $ZEND_DIR/bin/php shutdown.php positive &> $INSTALL_PATH/var/log/dmsctl.log
    exit=$?
    sleep 5
    if [ $exit -eq 0 ]; then
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

start_mysql() {
    is_mysql_running
    RUNNING=$?

    if [ $RUNNING -eq 1 ]; then
        echo "$0 $ARG: mysql (pid $MYSQL_PID) already running"
    else
        nohup  $MYSQL_PATH/$MYSQLBIN start &> $INSTALL_PATH/var/log/dmsctl.log &
		if [ $? -eq 0 ]; then
            echo "$0 $ARG: mysql started"
            ps ax | grep $MYSQLBIN | awk {'print $1'} > $MYSQL_PIDFILE
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
    get_mysql_pid
	if kill $MYSQL_PID ; then
	    echo "$0 $ARG: mysql stopped"
	else
	    echo "$0 $ARG: mysql could not be stopped"
	    ERROR=4
	fi
}

help() {
	echo "usage: $0 help"
	echo "       $0 (start|stop|restart)"
	echo "       $0 (start|stop|restart) scheduler"
	echo "       $0 (start|stop|restart) soffice"
	echo "       $0 (start|stop|restart) lucene"
	echo "       $0 (start|stop|restart) mysql"
	cat <<EOF

help       - this screen
start      - start the service(s)
stop       - stop  the service(s)
restart    - restart or start the service(s)

EOF
exit 0
}

noserver() {
       echo -e "ERROR: $1 is not a valid server. Please, select 'scheduler', 'soffice' or 'lucene'\n"
       help
}

firstrun() {
	echo "We running for the first time, FIX ZEND"
	if grep --quiet LD_LIBRARAY_PATH /etc/zce.rc ; then
        	echo "Nothing to be done ... maybe"
	else
        	echo "PATH=/usr/local/zend/bin:$PATH" >> /etc/zce.rc
        	if [ -z $LD_LIBRARY_PATH ] ; then
                	echo "LD_LIBRARY_PATH=$ZEND_DIR/lib" >> /etc/zce.rc
        	else
                	echo "LD_LIBRARY_PATH=$ZEND_DIR/lib:$LD_LIBRARY_PATH" >> /etc/zce.rc
        	fi
	fi

	touch $INSTALL_PATH/var/bin/dmsinit.lock

	$ZEND_DIR/bin/zendctl.sh restart
}

[ $# -lt 1 ] && help

if [ ! -z ${2} ]; then
       [ "${2}" != "mysql" ] && [ "${2}" != "apache" ] && [ "${2}" != "agent" ] && [ "${2}" != "scheduler" ] && [ "${2}" != "soffice" ] && [ "${2}" != "lucene" ] && noserver $2
       SERVER=$2
fi


if [ "x$3" != "x" ]; then
    MYSQL_PASSWORD=$3
fi

# Are we running for first time
if [ -e "/usr/share/knowledgetree/var/bin/dmsinit.lock" ]
then
echo "";
else
	if grep --quiet LD_LIBRARAY_PATH /etc/zce.rc ; then
        	echo "Nothing to be done ... maybe"
	else
        	echo "PATH=/usr/local/zend/bin:$PATH" >> /etc/zce.rc
        	if [ -z $LD_LIBRARY_PATH ] ; then
                	echo "LD_LIBRARY_PATH=$ZEND_DIR/lib" >> /etc/zce.rc
        	else
                	echo "LD_LIBRARY_PATH=$ZEND_DIR/lib:$LD_LIBRARY_PATH" >> /etc/zce.rc
        	fi
	fi
	touch $INSTALL_PATH/var/bin/dmsinit.lock
	$ZEND_DIR/bin/zendctl.sh restart
fi

#if [ "ls /usr/share/knowledgetree/var/bin/dmsinit.lock" != "" ] ; then 
#	echo "No lock"
#e#lse
#	echo "lock"
#f#i

#if [ -f "/usr/share/knowledgetree/var/bin/dmsinit.lock"] ; then
#     firstrun
#else
#	echo 'safd';
#     exit 1;
#fi

#[[ -e $INSTALL_PATH/var/bin/dmsinit.lock ]] || firstrun

case $1 in
       help)   help
               ;;
       start)
               if [ "${SERVER}" != "all" ]; then
                       start_${2}
               else
                       start_soffice
                       start_lucene
                       start_scheduler
		       #[[ -e $ZEND_DIR/bin/zendctl.sh ]] && $ZEND_DIR/bin/zendctl.sh restart
               fi
               ;;
       stop)   if [ "${SERVER}" != "all" ]; then
                       stop_${2}
               else
                       stop_scheduler "no_exit"
                       stop_lucene "no_exit"
                       stop_soffice "no_exit"
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
                               start_soffice
                               start_lucene
                               start_scheduler
                       fi
               ;;
esac

exit $ERROR
