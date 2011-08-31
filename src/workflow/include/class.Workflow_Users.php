<?php

class Workflow_Users {
  public $msg;

  function Workflow_Users() {
    module_load_include('nc', 'Workflow_Users', '');
    drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
  }

  static function getUserList($workflow_id) {
	$workflow_users_results = db_query("SELECT user_id FROM {apiary_project_workflow_users} WHERE workflow_id='%s'", $workflow_id);
	$workflow_users_list = array();
    while($workflow_users_result = db_fetch_object($workflow_users_results)) {
      $user_id = $workflow_users_result->user_id;
      $user_name = Workflow_Users::getUserNameFromID($user_id);
      array_push($workflow_users_list, $user_name);
    }
    return $workflow_users_list;
  }

  static function getWorkflowIDsForUserName($user_name) {
    $user_id = Workflow_Users::getUserIDFromName($user_name);
    return Workflow_Users::getWorkflowsForUserID($user_id);
  }

  static function getWorkflowIDsForUserID($user_id) {
	$user_workflow_result = db_query("SELECT workflow_id FROM {apiary_project_workflow_users} WHERE user_id='%s' Order By workflow_id", $user_id);
	$user_workflow_list = array();
	while ($user_workflow_result = db_fetch_object($user_workflow_result)) {
      $workflow_id = $workflow_users_result->workflow_id;
      array_push($user_workflow_list, trim($workflow_id));
    }
    return $user_workflow_list;
  }

  static function doesWorkflowHaveUserName($workflow_id, $user_name) {
    $user_id = Workflow_Users::getUserIDFromName($user_name);
	return Workflow_Users::doesWorkflowHaveUserID($workflow_id, $user_id);
  }

  static function doesWorkflowHaveUserID($workflow_id, $user_id) {
	$user_workflow_result = db_query("SELECT workflow_id FROM {apiary_project_workflow_users} WHERE workflow_id='%s' AND user_id='%s'", $workflow_id, $user_id);
	while ($user_workflow_result = db_fetch_object($user_workflow_result)) {
	  //a record exists
      return true;
    }
    return false;
  }

  static function getUserNameFromID($user_id) {
    $results = db_query("SELECT name FROM {users} WHERE uid='%s'", $user_id);
    $result = db_fetch_object($results);
    $user_name = $result->name;
    return $user_name;
  }

  static function getUserIDFromName($user_name) {
    $results = db_query("SELECT uid FROM {users} WHERE name='%s'", $user_name);
    $result = db_fetch_object($results);
    $user_id = $result->uid;
    return $user_id;
  }
}

?>