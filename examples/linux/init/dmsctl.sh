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
USEXVFB=0
VDISPLAY="99"
INSTALL_PATH=@@BITROCK_INSTALLDIR@@
JAVABIN=$INSTALL_PATH/j2re/bin/java
export LD_LIBRARY_PATH="$INSTALL_PATH/apache2/lib:$INSTALL_PATH/common/lib:$LD_LIBRARY_PATH"
export PATH=$PATH:$INSTALL_PATH/php/bin
export PHPRC=$INSTALL_PATH/php/etc

# Apache
HTTPD_PIDFILE=$INSTALL_PATH/apache2/logs/httpd.pid
HTTPD_PID=""
HTTPD="$INSTALL_PATH/apache2/bin/httpd -f $INSTALL_PATH/apache2/conf/httpd.conf"
HTTPD_STATUS=""

# MySQL
MYSQL_PIDFILE=$INSTALL_PATH/mysql/data/mysqld.pid
MYSQL_PID=""
MYSQL_START="$INSTALL_PATH/mysql/bin/safe_mysqld --port=3306 --socket=$INSTALL_PATH/mysql/tmp/mysql.sock --old-passwords --datadir=$INSTALL_PATH/mysql/data --log-error=$INSTALL_PATH/mysql/data/mysqld.log --pid-file=$INSTALL_PATH/mysql/data/mysqld.pid"
MYSQL_STOP="$INSTALL_PATH/mysql/bin/mysqladmin --socket=$INSTALL_PATH/mysql/tmp/mysql.sock -u root -p shutdown"
MYSQL_STATUS=""
MYSQL_PASSWORD=""

# Xvfb
XVFB_PIDFILE=$INSTALL_PATH/Xvfb/xvfb.pid
XVFB_PID=""
XVFBBIN=$INSTALL_PATH/Xvfb/bin/Xvfb
XVFB="$XVFBBIN :$VDISPLAY -screen 0 800x600x8 -fbdir $INSTALL_PATH/Xvfb/var/run"
XVFB_STATUS=""

# OpenOffice
SOFFICE_PATH="$INSTALL_PATH/openoffice/program"
SOFFICE_PIDFILE=$INSTALL_PATH/openoffice/soffice.bin.pid
SOFFICE_PID=""
SOFFICE_PORT="8100"
SOFFICEBIN=$INSTALL_PATH/openoffice/program/soffice.bin
if [ $USEXVFB -eq 1 ]; then
    SOFFICE="$SOFFICEBIN -nofirststartwizard -nologo -headless -display :$VDISPLAY -accept=socket,host=localhost,port=$SOFFICE_PORT;urp;StarOffice.ServiceManager"
else
    SOFFICE="$SOFFICEBIN -nofirststartwizard -nologo -headless -accept=socket,host=localhost,port=$SOFFICE_PORT;urp;StarOffice.ServiceManager"
fi
SOFFICE_STATUS=""

# Lucene
LUCENE_PIDFILE=$INSTALL_PATH/knowledgeTree/bin/luceneserver/lucene.pid
LUCENE_PID=""
LUCENE="$JAVABIN -jar ktlucene.jar"
LUCENE_STATUS=""

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

get_mysql_pid() {
    get_pid $MYSQL_PIDFILE
    if [ ! $PID ]; then
        return 
    fi
    if [ $PID -gt 0 ]; then
        MYSQL_PID=$PID
    fi
}

get_xvfb_pid() {
    get_pid $XVFB_PIDFILE
    if [ ! $PID ]; then
        return 
    fi
    if [ $PID -gt 0 ]; then
        XVFB_PID=$PID
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

is_xvfb_running() {
    get_xvfb_pid
    is_service_running $XVFB_PID
    RUNNING=$?
    if [ $RUNNING -eq 0 ]; then
        XVFB_STATUS="Xvfb not running"
    else
        XVFB_STATUS="Xvfb already running"
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
        $MYSQL_START &
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
    echo "MySQL will prompt you for the root password."
    if [ "x$MYSQL_PASSWORD" != "x" ]; then
        MYSQL_STOP="$MYSQL_STOP --password=$MYSQL_PASSWORD"
    fi
    $MYSQL_STOP
    
    is_mysql_running
    RUNNING=$?
    if [ $RUNNING -eq 0 ]; then
	    echo "$0 $ARG: mysql stopped"
	else
	    echo "$0 $ARG: mysql could not be stopped"
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
        if $HTTPD ; then
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

start_xvfb() {
if [ $USEXVFB -eq 1 ]; then
    is_xvfb_running
    RUNNING=$?

    if [ $RUNNING -eq 1 ]; then
        echo "$0 $ARG: Xvfb (pid $XVFB_PID) already running"
    else
        $XVFB  >/dev/null 2>&1 &
        if [ $? -eq 0 ]; then
            echo "$0 $ARG: Xvfb started on display $VDISPLAY"
            ps ax | grep $XVFBBIN | awk {'print $1'} > $XVFB_PIDFILE
            sleep 2
        else
            echo "$0 $ARG: xvfb could not be started"
            ERROR=3
        fi
    fi
fi
}

stop_xvfb() {
if [ $USEXVFB -eq 1 ]; then
    NO_EXIT_ON_ERROR=$1
    is_xvfb_running
    RUNNING=$?

    if [ $RUNNING -eq 0 ]; then
        echo "$0 $ARG: $XVFB_STATUS"
        if [ "x$NO_EXIT_ON_ERROR" != "xno_exit" ]; then
            exit
        else
            return
        fi
	fi
    get_xvfb_pid
	if killall $XVFBBIN ; then
	    echo "$0 $ARG: Xvfb stopped"
	else
	    echo "$0 $ARG: Xvfb could not be stopped"
	    ERROR=4
	fi
fi
}

start_soffice() {
    is_soffice_running
    RUNNING=$?

    if [ $RUNNING -eq 1 ]; then
        echo "$0 $ARG: openoffice (pid $SOFFICE_PID) already running"
    else
        $SOFFICE >/dev/null 2>&1 &
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
        $LUCENE  >/dev/null 2>&1 &
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
    $INSTALL_PATH/php/bin/php shutdown.php positive  >/dev/null 2>&1 &
    if [ $? -eq 0 ]; then
	    echo "$0 $ARG: lucene stopped"
	else
	    echo "$0 $ARG: lucene could not be stopped"
	    ERROR=4
	fi
}

help() {
	echo "usage: $0 help"
	echo "       $0 (start|stop|restart)"
	echo "       $0 (start|stop|restart) apache"
	echo "       $0 (start|stop|restart) mysql"
	echo "       $0 (start|stop|restart) soffice"
	echo "       $0 (start|stop|restart) lucene"
	echo "       $0 (start|stop|restart) xvfb"
	cat <<EOF

help       - this screen
start      - start the service(s)
stop       - stop  the service(s)
restart    - restart or start the service(s)

EOF
exit 0
}

noserver() {
       echo -e "ERROR: $1 is not a valid server. Please, select 'mysql', 'apache', 'soffice', 'lucene' or 'xvfb'\n"
       help
}

[ $# -lt 1 ] && help

if [ ! -z ${2} ]; then
       [ "${2}" != "mysql" ] && [ "${2}" != "apache" ] && [ "${2}" != "soffice" ] && [ "${2}" != "lucene" ] && [ "${2}" != "xvfb" ] && noserver $2
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
                       start_xvfb
                       sleep 2
                       start_soffice
                       start_lucene
               fi
               ;;
       stop)   if [ "${SERVER}" != "all" ]; then
                       stop_${2}
               else
                       stop_lucene "no_exit"
                       stop_soffice "no_exit"
                       stop_xvfb "no_exit"
                       stop_apache "no_exit"
                       stop_mysql
               fi
               ;;
       restart)        if [ "${SERVER}" != "all" ]; then
                               stop_${2} "no_exit"
                               sleep 2
                               start_${2}
                       else
                               stop_lucene "no_exit"
                               stop_soffice "no_exit"
                               stop_xvfb "no_exit"
                               stop_apache "no_exit"
                               stop_mysql "no_exit"
                               start_mysql
                               start_apache
                               start_xvfb
                               sleep 2
                               start_soffice
                               start_lucene
                       fi
               ;;
esac

exit $ERROR
