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

//we need the drupal host url as it can be installed at drupal.site.org or my.site.org/drupal etc
//APIARY_DRUPAL_URL is defined in the apiary_variables.php file
$smarty->assign('drupal_url',APIARY_DRUPAL_URL);
$smarty->assign('djatoka_url',APIARY_DJATOKA_URL);

if(isset($_GET['workflow_id'])) {
  $workflow_id = $_GET['workflow_id'];
}
$smarty->assign('workflow_id', $workflow_id);

$epp = '10';
if(isset($_GET['epp'])) {
  $epp = $_GET['epp'];
}
$smarty->assign('epp', $epp);

$page = '1';
if(isset($_GET['page'])) {
  $page = $_GET['page'];
}
$smarty->assign('page', $page);

cycle_system_messages();
$smarty->display('index.tpl.html');


?>