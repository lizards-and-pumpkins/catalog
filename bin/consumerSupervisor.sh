#!/usr/bin/env bash

consumerScript="$1"
restartInterval=3

[ -z "$consumerScript" ] && {
    echo "ERROR: No script to run specified as an argument" >&2
    exit 2
}

[ ! -e "$consumerScript" ] && {
    echo "ERROR: Script \"$consumerScript\" not found." >&2
    exit 3
}

[ ! -x "$consumerScript" ] && {
    echo "ERROR: script \"$consumerScript\" is not executable." >&2
    exit 4
}

until false; do
    "$consumerScript"
    exitCode=$?
    [ "$exitCode" != "0" ] && {
        echo "The script \"$consumerScript\" died with the error code $exitCode." >&2
        exit 5
    }
    sleep $restartInterval
done
