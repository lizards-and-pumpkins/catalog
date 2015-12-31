#!/usr/bin/env bash

LOG=
EXIT_ON_ERROR=
RESTART_INTERVAL=3

while test $# -ne 0
do
    case "$1" in
        "-l")
                LOG="$2"
                shift
                ;;
        "-i")
                RESTART_INTERVAL="$2"
                shift
                ;;
        "-e")
                EXIT_ON_ERROR=true
                ;;
        *)
                CONSUMER_SCRIPT="$1"
                ;;
    esac
    shift
done

[ -z "$CONSUMER_SCRIPT" ] && {
    echo "ERROR: No script to run specified as an argument" >&2
    exit 2
}

[ ! -e "$CONSUMER_SCRIPT" ] && {
    echo "ERROR: Script \"$CONSUMER_SCRIPT\" not found." >&2
    exit 3
}

[ ! -x "$CONSUMER_SCRIPT" ] && {
    echo "ERROR: script \"$CONSUMER_SCRIPT\" is not executable." >&2
    exit 4
}

until false; do
    if [ "$LOG" ]; then
        "$CONSUMER_SCRIPT" 2>"$LOG" 2>&1
    else
        "$CONSUMER_SCRIPT"
    fi
    exitCode=$?
    [ "$exitCode" != "0" ] && [ $EXIT_ON_ERROR ] && {
        echo "The script \"$CONSUMER_SCRIPT\" died with the error code $exitCode." >&2
        exit 5
    }
    sleep $RESTART_INTERVAL
done
