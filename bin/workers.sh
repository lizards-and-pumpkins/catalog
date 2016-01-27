#!/usr/bin/env bash

########################################################

declare -a names=(commandConsumer.php eventConsumer.php)

########################################################

function main() {
    init_vars

    runmode=1
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
            q)
                runmode=0
                ;;
        esac
    done
}

function init_vars() {
    declare -a pids=();
    current_selection=0
    dir="$(dirname $0)"
    supervisor="$dir/consumerSupervisor.sh"
}

function get_valid_choice() {
    choice=
    until [ ! -z "$choice" ]; do
        build_screen
        read -s -n 1 -p"Select script or +/- to increase/decrease workers (q to quit): " choice
        case $choice in
            [1-9])
                if [ $choice -gt ${#names[@]} ]; then
                    choice=
                fi
                ;;
            +|-|q)
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
    echo
    print_menu 
}

function print_menu()
{
    for ((i=0; i < ${#names[@]}; i++)); do
        [[ $current_selection = $i ]] && is_selected="*" || is_selected=" "
        printf "%d) %-20s %1s [ %2d ]\n" $((i + 1)) ${names[$i]} "$is_selected" $(get_pid_count_at $i)
    done
}

function get_pid_count_at()
{
    index=$1
    echo ${pids[$i]} | wc -w
}

function increase_current_selection()
{
    "$supervisor" "$dir/${names[$current_selection]}" &
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

main


