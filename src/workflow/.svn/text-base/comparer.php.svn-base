<?php
# $Revision: 2305 $
require('include/Smarty.class.php');
include_once("include/functions_misc.php");
include_once("apiary_variables.php");

session_start();

$smarty = new Smarty;
if(!empty($_SESSION['debug']) || $_GET['debug'] != '') {
  $smarty->debugging = true;
}

//APIARY_DRUPAL_URL is defined in the apiary_variables.php file
$smarty->assign('drupal_url',APIARY_DRUPAL_URL);

cycle_system_messages();
$smarty->display('comparer.tpl.html');


?>