<?php

class Workflow_Permission {
  public $msg;

  function Workflow_Permission() {
    module_load_include('nc', 'Workflow_Permission', '');
    drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
  }

  static function getPermissionList($workflow_id) {
	module_load_include('php', 'apiary_project', 'workflow/include/class.Permission');
	$workflow_permission_results = db_query("SELECT permission_id FROM {apiary_project_workflow_permission} WHERE workflow_id='%s'", $workflow_id);
	$workflow_permission_list = array();
    while($workflow_permission_result = db_fetch_object($workflow_permission_results)) {
      $permission_id = $workflow_permission_result->permission_id;
      $permission_name = Permission::getNameFromID($permission_id);
      array_push($workflow_permission_list, $permission_name);
    }
    return $workflow_permission_list;
  }

  static function getParseLevelList($workflow_id) {
	$parse_levels_results = db_query("SELECT apiary_project_permission.permission_name FROM apiary_project_workflow_permission LEFT JOIN apiary_project_permission ON apiary_project_workflow_permission.permission_id=apiary_project_permission.permission_id WHERE workflow_id='%s' AND apiary_project_permission.permission_name LIKE 'canParse%' GROUP BY apiary_project_permission.permission_name ORDER BY apiary_project_permission.permission_name", $workflow_id);
	$parse_level_list = array();
    while($parse_levels_result = db_fetch_object($parse_levels_results)) {
      $permission_name = $parse_levels_result->permission_name;
      array_push($parse_level_list, $permission_name);
    }
    return $parse_level_list;
  }

  static function getWorkflowsForPermission($permission_id) {
	$permission_workflow_result = db_query("SELECT workflow_id FROM {apiary_project_workflow_permission} WHERE permission_id='%s'", $permission_id);
	$permission_workflow_list = array();
	while ($permission_workflow_result = db_fetch_object($permission_workflow_result)) {
      $workflow_id = $workflow_permission_result->workflow_id;
      array_push($permission_workflow_list, trim($workflow_id));
    }
    return $permission_workflow_list;
  }

  static function doesWorkflowHavePermission($workflow_id, $permission_name) {
	module_load_include('php', 'apiary_project', 'workflow/include/class.Permission');
    $permission_id = Permission::getIDFromName($permission_name);
	$permission_workflow_result = db_query("SELECT workflow_id FROM {apiary_project_workflow_permission} WHERE workflow_id='%s' AND permission_id='%s'", $workflow_id, $permission_id);
	while ($permission_workflow_result = db_fetch_object($permission_workflow_result)) {
	  //a record exists
      return true;
    }
    return false;
  }
}

?>