<?php
global $user;

if ($user->uid || $_GET['ref'] == 'about') {
  // Logged in user
  if(module_exists('apiary_project')){
    $ref = $_GET['ref'];
    if(!isset($_GET['ref'])) {
      $file = './' . drupal_get_path('module', 'apiary_project') . '/workflow/include/main_workspace.php';
    }
    else {
      $ref= $_GET['ref'];
      if($ref=="about") {
        $file = './' . drupal_get_path('module', 'apiary_project') . '/workflow/include/'.$ref.'.html';
      }
      else {
        $file = './' . drupal_get_path('module', 'apiary_project') . '/workflow/include/'.$ref.'.php';
      }
    }
    include_once("$file");
  }
  else {
    echo 'The Apiary Project module is either not enabled or not installed.<br>Please verify the module is enabled or contact your system administrator for futher support.<br>';
  }
}
else {
  header('location: ' . $server_base . '/drupal');
}
?>