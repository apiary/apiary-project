<?php
/*
* Smarty plugin
* ————————————————————-
* File:     function.days_from_now.php
* Type:     function
* Name:     recurse_array
* Purpose:  prints out elements of an array recursively
* ————————————————————-
*/

function smarty_function_days_from_now($params, &$smarty)
{
    $target_date = strtotime($params['date']);
    $today = strtotime(date("F d, Y",strtotime('today')));
    $days = floor(($target_date - $today) / 86400);
    
    if ( $days > 2 )
        $output = $days.' days from now';
    else if ( $days > 0 )
        $output = 'tomorrow';
    else if ( $days == 0 )
        $output = 'today';
    else if ( $days > -2 )
        $output = 'yesterday';
    else if ( $days <= -2 )
        $output = -$days.' days ago';
    else
        $output = $days;
    return $output;

}
