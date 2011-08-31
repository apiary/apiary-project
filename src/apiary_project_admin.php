<?php
include_once(drupal_get_path('module', 'apiary_project') . '/apiaryPermissionsClass.php');
global $user;

if($user->uid && module_exists('apiary_project')){
// Logged in user
  if(user_access(apiaryPermissionsClass::$ADMINISTER_APIARY)) {
    $file = './' . drupal_get_path('module', 'apiary_project') . '/workflow/include/admin_workspace.php';
    include_once("$file");
  }
  else {
    echo '<p>You do not have permission to administer the apiary project</a></p>'."\n";
  }
}
else {
  header('location: ' . $server_base . '/drupal');
}
?>