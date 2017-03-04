#!/usr/bin/env bash

########################################################

declare -a workers=("consume:command" "consume:event")
declare -a labels=("Command Consumers" "Event Consumers")

########################################################

function main() {
    init_vars
    declare runmode=1
    while [ $runmode -gt 0 ]; do
        get_valid_choice
        case $choice in
            [1-9])
                current_selection=$((choice -1))
                ;;
            +)
                increase_current_selection
                ;;
            -)
                decrease_current_selection
                ;;
            r)
                update_pid_list
                ;;
            q)
                runmode=0
                ;;
        esac
    done
    echo
}

function init_vars() {
    current_selection=0
    dir="$(dirname $0)"
    supervisor="$dir/consumerSupervisor.sh"
    manage_consumer="$dir/manage-consumer.sh"
    
    update_pid_list
}

function get_valid_choice() {
    choice=
    until [ ! -z "$choice" ]; do
        build_screen
        echo
        read -s -n 1 -p"Number to select or +/- to increase/decrease worker count (r to refresh, q to quit): " choice
        case $choice in
            [1-9])
                if [ $choice -gt ${#workers[@]} ]; then
                    choice=
                fi
                ;;
            +|-|q|r)
                ;;
            *)
                choice=
                ;;
        esac
    done
}

function build_screen()
{
    clear
    printf "\n %-20s     Count\n\n" "Worker Process"
    print_menu
}

function print_menu()
{
    for ((i=0; i < ${#workers[@]}; i++)); do
        [[ $current_selection = $i ]] && is_selected="*" || is_selected=" "
        printf "%d) %-20s %1s [ %2d ]\n" $((i + 1)) "${labels[$i]}" "$is_selected" $(get_pid_count_for $i)
        [ "$verbose" == "true" ] && echo "${pids[$i]}"
    done
}


function update_pid_list()
{
    for ((i=0; i < ${#workers[@]}; i++)); do
        pids[$i]=" "$(get_pids_for_worker ${workers[$i]})
    done
}

function get_pids_for_worker()
{
    local name=$1
    echo $(ps x|grep $name|grep "$(basename $supervisor)"|grep -v 'grep '|awk '{ print $1 }')
}

function get_pid_count_for()
{
    local index=$1
    echo ${pids[$index]} | wc -w
}

function increase_current_selection()
{
    local worker="${workers[$current_selection]#*:}"
    "$manage_consumer" "${worker%s}" start
    update_pid_list
}

function decrease_current_selection()
{
    local supervisor_pid="${pids[$current_selection]##* }"
    local worker="${workers[$current_selection]#*:}"
    if [ ! -z ${supervisor_pid} ]; then
        "$manage_consumer" "${worker%s}" stop
        pids[$current_selection]="${pids[$current_selection]% *}"
    fi
}

########################################################

while [ $# -ne 0 ]; do
    case "$1" in
        "-d"|"--debug"|"-v"|"--verbose")
            verbose=true
            ;;
    esac
    shift
done

main
