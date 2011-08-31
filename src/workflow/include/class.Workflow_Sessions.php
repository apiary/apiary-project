<?php

class Workflow_Sessions {

  function Workflow_Sessions() {
    module_load_include('nc', 'Workflow_Sessions', '');
    drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
  }

  static function create($session_id) {
  	if($session_id == null || $session_id == '') {
  	  echo 'A session_id is required.<br>';
  	  return false;
  	}
	if(!Workflow_Sessions::session_id_exists($workflow_name)) {
  	  $expiration_date = date("YmdHis")+1000000; //add a day to it using the YmdHis format
      $session_expiration = sprintf("%.0f", $expiration_date);
	  $insert_record = db_query("INSERT into {apiary_project_workflow_sessions} (session_id, session_expiration)
	                             VALUES ('%s', '%s')", $session_id, $session_expiration);
      return $insert_record;
    }
    else {
	  echo "Session ID already exists. <br>";
    }
    return false;
  }

  static function get($session_id) {
	$results = db_query("SELECT * FROM {apiary_project_workflow_sessions} WHERE session_id='%s'", $session_id);
	while ($fields = db_fetch_array($results)) {
	  foreach($fields as $key => $value) {
	    $session[$key] = $value;
	  }
    }
    return $session;
  }

  static function session_id_exists($session_id) {
	$sql = "SELECT session_id FROM {apiary_project_workflow_sessions} WHERE session_id='$session_id'";
	$results = db_query($sql);
	while ($result_session_id = db_result($results)) {
	  if($result_session_id != '' && $result_session_id !=  null)  {
	    return true;
	  }
    }
    return false;
  }

  static function active_session($session_id) {
    $now_d = date("YmdHis");
    $now = sprintf("%.0f", $now_d);
	$sql = "SELECT session_id FROM {apiary_project_workflow_sessions} WHERE session_id='$session_id' AND session_expiration>$now";
	$results = db_query($sql);
	while ($result_session_id = db_result($results)) {
	  if($result_session_id != '' && $result_session_id !=  null)  {
	    return true;
	  }
    }
    return false;
  }

  static function renew_session($session_id) {
  	$expiration_date = date("YmdHis")+1000000; //add a day to it using the YmdHis format
    $session_expiration = sprintf("%.0f", $expiration_date);
	$update_query = "UPDATE {apiary_project_workflow_sessions} SET session_expiration='%s' WHERE session_id='%s'";
	return db_query($update_query, $session_expiration, $session_id);
  }

  static function getExpirationForID($session_id) {
	$results = db_query("SELECT session_expiration FROM {apiary_project_workflow_sessions} WHERE session_id='%s'", $session_id);
	while ($result = db_fetch_object($results)) {
      $session_expiration = $result->session_expiration;
    }
    return $session_expiration;
  }

  static function delete($session_id) {
	return db_query("DELETE FROM {apiary_project_workflow_sessions} WHERE session_id='%s'", $session_id);
  }
}

?>