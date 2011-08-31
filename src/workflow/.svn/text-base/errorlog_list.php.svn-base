<?php
# errorlog_list.php will allow the user to search for collections using different search criteria.
# $Revision: 2547 $

include_once("include/functions_error.php");
include_once("include/functions_misc.php");
include_once("include/class.Errorlog.php");

require('include/Smarty.class.php');
$smarty = new Smarty;

session_start(); 

$smarty->assign('admin_section_on',true);
$smarty->assign('login_name',$_SESSION['email_address']);
$smarty->assign('admin_type',$_SESSION['admin_type']);
$smarty->assign('header_title','Error Log List');

if ( $_GET['debug'] != '' )
    $smarty->debugging = true;
 
//$page_control_number = $_COOKIE['page_control_number'];
//if ( $page_control_number == 0 )
//    $page_control_number = 50;
$page_control_number = 20;    
$num = $_GET['num'];
$start = $_GET['start'];	
$sort = $_GET['sort'];	
    
    //	setcookie('page_control_number',$page_control_number,time()+60*60*24*30 );
$errorlog = new Errorlog();
$errorlog->page_control_number = $page_control_number;
$errorlog->start               = $start;
$errorlog->num                 = $num;

//$errorlog->paged               = FALSE;
//$errorlog->sort_type           = $sort;
//$records = array();

//$records = $errorlog->get();


$errorlog->paged               = TRUE;
$errorlog->sort_type           = $sort;

$records = $errorlog->get(null,$_GET['search']);

$num_rows = $errorlog->found_rows;
$num_pages = $num_rows / $page_control_number;
$current_page = round( $num_pages - ( ( $num_rows - $start ) / $page_control_number ) + 1 );
for ( $i = $current_page - 10; $i < $current_page + 11; $i++ )
{
    if ( $i > 0 && $i < $num_pages + 1 )
        $page_values[$i] = ($i - 1) * $page_control_number;
}
$smarty->assign('num_rows',$num_rows);
$smarty->assign('page_values',$page_values);
$smarty->assign('page_control_number',$page_control_number);
$smarty->assign('num_pages',$num_pages);
$last_rows_start  = (floor($num_pages))*$page_control_number;
if ( $num_rows == $last_rows_start )
    $last_rows_start -= $page_control_number;
$smarty->assign('last_rows_start',$last_rows_start);

//$smarty->assign('last_rows_start',(floor($num_pages))*$page_control_number);
$smarty->assign('current_page',$current_page);
if ( $current_page > 1 )
    $smarty->assign('previous_page',$current_page-1);
if ( $current_page < $num_pages )
    $smarty->assign('next_page',$current_page+1);
$url_for_sortlink = "errorlog_list.php?";
if ( $sort )
    $url_for_pagelink = "errorlog_list.php?search={$_GET['search']}&sort=$sort";
else
    $url_for_pagelink = "errorlog_list.php?search={$_GET['search']}";
$smarty->assign('url_for_pagelink',$url_for_pagelink);
$smarty->assign('url_for_sortlink',$url_for_sortlink);
$smarty->assign('sort_type',$sort);
$smarty->assign('num_rows_shown', count($records));
$smarty->assign('errorlog_records',$records);
$smarty->assign('record_count',count($records));

$system_message = merge_arrays( $_SESSION['system_message'], $system_message );
$error_message = merge_arrays( $_SESSION['error_message'], $error_message );
$smarty->assign('system_message',$system_message);
$smarty->assign('error_message',$error_message);
$_SESSION['system_message'] = '';
$_SESSION['error_message']  = '';

$smarty->display('errorlog_list.tpl.html');


?>


