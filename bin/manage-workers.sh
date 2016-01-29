#!/usr/bin/env bash

########################################################

declare -a workers=(commandConsumer.php eventConsumer.php)

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
}

function init_vars() {
    current_selection=0
    dir="$(dirname $0)"
    supervisor="$dir/consumerSupervisor.sh"
    
    update_pid_list
}

function get_valid_choice() {
    choice=
    until [ ! -z "$choice" ]; do
        build_screen
        echo
        read -s -n 1 -p"Select script or +/- to increase/decrease workers (r to refresh, q to quit): " choice
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
        printf "%d) %-20s %1s [ %2d ]\n" $((i + 1)) ${workers[$i]} "$is_selected" $(get_pid_count_for $i)
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
    name=$1
    echo $(ps x|grep $name|grep -v 'grep '|awk '{ print $1 }')
}

function get_pid_count_for()
{
    index=$1
    echo ${pids[$index]} | wc -w
}

function increase_current_selection()
{
    "$supervisor" "$dir/${workers[$current_selection]}" &
    pids[$current_selection]="${pids[$current_selection]} $!"
}

function decrease_current_selection()
{
    child_pid="${pids[$current_selection]##* }"
    if [ ! -z $child_pid ]; then
        kill -TERM $child_pid
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


