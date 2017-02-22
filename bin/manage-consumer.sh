#!/usr/bin/env bash

function init_vars() {
    dir="$(dirname $0)"
    supervisor="$dir/consumerSupervisor.sh"
    send_shutdown_message="$dir/lp shutdown:consumer"
    count=1
    get_input_args "$@"
    build_pid_list
}

function get_input_args() {
    case "$1" in
        c|command|commandConsumer)
            worker="lp comsume:commands"
            ;;
        e|event|eventConsumer)
            worker="lp comsume:events"
            ;;
        *)
            echo "Invalid consumer"
            usage
            ;;
    esac
    
    case "$2" in
        "start")
            action="start"
            count=$(as_numeric_value $3)
            ;;
        "stop")
            action="stop"
            count=$(as_numeric_value $3)
            ;;
        "stop-all")
            action="stop-all"
            ;;
        *)
            echo "Invalid action"
            usage
            ;;
    esac
}

function as_numeric_value
{
    local input=$(echo $1 | sed -e 's/[^0-9]//g')
    echo ${input:-1}
}

function build_pid_list()
{
    pids=" "$(get_pids_for_worker ${worker})
}

function get_pids_for_worker()
{
    local name=$1
    echo $(ps x|grep $name|grep "$supervisor"|grep -v 'grep '|awk '{ print $1 }')
}

function start_consumer()
{
    local count=$1
    while [ $count -gt 0 ]; do
        count=$((count -1))
        "$supervisor" "$dir/${worker}" &
    done
}

function stop_consumer()
{
    local supervisor_pid
    local count=$1
    while [ $count -gt 0 ]; do
        count=$((count -1))
        supervisor_pid="${pids##* }"
        if [ ! -z ${supervisor_pid} ]; then
            shutdown_worker ${worker} ${supervisor_pid} 
            pids="${pids% *}"
        fi
    done
}

function shutdown_worker()
{
    local type=$1
    local supervisor_pid=$2
    
    kill -TERM ${supervisor_pid}
    send_shutdown_message ${type} ${supervisor_pid}
}

function send_shutdown_message
{
    local type="${1%Consumer.php}"
    local consumer_pid=$(pgrep -P $2 php)
    
    if [ ! -z ${consumer_pid} ]; then
        ${send_shutdown_message} --quiet ${type} ${consumer_pid}
    fi
}

function usage()
{
    echo "Usage:"
    echo "$0 (event|command) (start|stop) [count]"
    echo "$0 (event|command) stop-all"
    exit 2;
}

init_vars "$@"

case "$action" in
    "start")
        start_consumer "$count"
        ;;
    "stop")
        stop_consumer "$count"
        ;;
    "stop-all")
        for pid in $pids; do
            shutdown_worker "$worker" "$pid"
        done
        ;;
esac
