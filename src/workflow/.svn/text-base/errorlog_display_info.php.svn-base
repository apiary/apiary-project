<?php
# errorlog_list.php will allow the user to search for collections using different search criteria.
# $Revision: 1903 $

include_once("include/functions_error.php");
include_once("include/functions_misc.php");
include_once("include/class.Errorlog.php");

require('include/Smarty.class.php');
$smarty = new Smarty;

session_start(); 
$smarty->assign('admin_section_on',true);
$smarty->assign('login_name',$_SESSION['email_address']);
$smarty->assign('admin_type',$_SESSION['admin_type']);
$smarty->assign('header_title','Error Log');


$errorlog_id = $_GET['id'];
    
$errorlog = new Errorlog();
    
$record = $errorlog->get($errorlog_id);
$record['errorlog_string'] = html_entity_decode(str_replace("\n","\n<br>",$record['errorlog_string']), ENT_QUOTES);
$record['errorlog_environment'] = str_replace("\n","\n<br>",$record['errorlog_environment']);
$smarty->assign('errorlog_record',$record);

$last_insert_id = $errorlog->getLastInsertID();
$smarty->assign('last_insert_id',$last_insert_id);

$system_message = merge_arrays( $_SESSION['system_message'], $system_message );
$error_message = merge_arrays( $_SESSION['error_message'], $error_message );
$smarty->assign('system_message',$system_message);
$smarty->assign('error_message',$error_message);
$_SESSION['system_message'] = '';
$_SESSION['error_message']  = '';

$smarty->display('errorlog_display_info.tpl.html');


?>


