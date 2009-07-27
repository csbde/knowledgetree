# Network Service Agent Control Script

export INSTALL_PATH=/home/jarrett/ktdms

AGENT_PIDFILE="$INSTALL_PATH/updates/agent.pid"
AGENT_PID=""
AGENT="$INSTALL_PATH/updates/agent.bin"
AGENT_STATUS=""
AGENT_BIN=agent.bin

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

is_service_running() {
    PID=$1
    if [ "x$PID" != "x" ] && kill -0 $PID 2>/dev/null ; then
        RUNNING=1
    else
        RUNNING=0
    fi
    return $RUNNING
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

start_agent() {
    is_agent_running
    RUNNING=$?
    if [ $RUNNING -eq 1 ]; then
        echo "$0 $ARG: agent (pid $AGENT_PID) already running"
    else
         $AGENT &
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

case $1 in
    start)
	start_agent
	;;
    stop)
	stop_agent
	;;
esac

exit 0
