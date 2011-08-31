<?php

class Permission {
  public $permission_id;
  public $permission_name;
  public $permission_description;
  public $msg;

  function Permission() {
    module_load_include('nc', 'Permission', '');
    drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
  }

  static function getPermissionNameList() {
	$sql = "SELECT permission_name FROM {apiary_project_permission}";
	$permission_results = db_query($sql);
	$permission_name_list = array();
    while($permission_result = db_fetch_object($permission_results)) {
      $permission_name = $permission_result->permission_name;
      array_push($permission_name_list, trim($permission_name));
    }
    return $permission_name_list;
  }

  static function get($permission_id) {
	$results = db_query("SELECT * FROM {apiary_project_permission} WHERE permission_id='%s'", $permission_id);
	while ($fields = db_fetch_array($results)) {
	  foreach($fields as $key => $value) {
	    $permission[$key] = $value;
	  }
    }
    return $permission;
  }

  static function getNameFromID($permission_id) {
	$results = db_query("SELECT permission_name FROM {apiary_project_permission} WHERE permission_id='%s'", $permission_id);
	while ($result = db_fetch_object($results)) {
      $permission_name = $result->permission_name;
    }
    return $permission_name;
  }

  static function getIDFromName($permission_name) {
	$results = db_query("SELECT permission_id FROM {apiary_project_permission} WHERE permission_name='%s'", $permission_name);
	while ($result = db_fetch_object($results)) {
      $permission_id = $result->permission_id;
    }
    return $permission_id;
  }
}

?>