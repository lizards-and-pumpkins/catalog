#!/usr/bin/env bash

LOG=
EXIT_ON_ERROR=
RESTART_INTERVAL=3

while test $# -ne 0
do
    case "$1" in
        "-l"|"--log")
                LOG="$2"
                shift
                ;;
        "-i"|"--restart-interval")
                RESTART_INTERVAL="$2"
                shift
                ;;
        "-e"|"--exit-on-error")
                EXIT_ON_ERROR=true
                ;;
        "-h"|"--help")
                echo "Usage:"
                echo "${0} [-l|--log logfile] [-i|--restart-interval secs] [-e|--exit-on-error] script-to-supervise"
                exit 1
                ;;
        *)
                CONSUMER_SCRIPT="$1"
                ;;
    esac
    shift
done

ARGUMENTS="${CONSUMER_SCRIPT#* }"
COMMAND="${CONSUMER_SCRIPT% *}"

[ -z "$CONSUMER_SCRIPT" ] && {
    echo "ERROR: No script to run specified as an argument" >&2
    exit 2
}

[ ! -e "$COMMAND" ] && {
    echo "ERROR: Script \"$COMMAND\" not found." >&2
    exit 3
}

[ ! -x "$COMMAND" ] && {
    echo "ERROR: script \"$CONSUMER_SCRIPT\" is not executable." >&2
    exit 4
}

function clean_exit()
{
    runmode=0
}

trap clean_exit TERM

runmode=1
until [ $runmode -eq 0 ]; do
    if [ "$LOG" ]; then
        "$COMMAND" ${ARGUMENTS} 2>"$LOG" 2>&1
    else
        "$COMMAND" ${ARGUMENTS}
    fi
    exitCode=$?
    [ "$exitCode" != "0" ] && [ $EXIT_ON_ERROR ] && {
        echo "The script \"$CONSUMER_SCRIPT\" died with the error code $exitCode." >&2
        exit 5
    }
    if [ $runmode -eq 1 ]; then
        sleep $RESTART_INTERVAL;
    fi
done
