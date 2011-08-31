<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
global $user;
include_once(drupal_get_path('module', 'apiary_project') . '/apiaryPermissionsClass.php');
include_once(drupal_get_path('module', 'apiary_project') . '/workflow/include/class.Workflow.php');
include_once(drupal_get_path('module', 'apiary_project') . '/workflow/include/class.Workflow_Users.php');
include_once(drupal_get_path('module', 'apiary_project') . '/workflow/include/class.Permission.php');
include_once(drupal_get_path('module', 'apiary_project') . '/workflow/include/class.Object_Pool.php');
include_once(drupal_get_path('module', 'apiary_project') . '/workflow/include/functions.php');
include_once(drupal_get_path('module', 'apiary_project') . '/workflow/include/search.php');

function send_request($function, $param1, $param2, $param3){
    //echo "function = ".$function." param1 = ".$param1." param2 = ".$param2." param3 = ".$param3;
	if($param3 != "0") {
	  call_user_func($function, $param1, $param2, $param3);
	}
	else if($param2 != "0") {
	  call_user_func($function, $param1, $param2);
	}
	else if($function == "clear_session") {
	  call_user_func($function);
	}
	else {
	  call_user_func($function, $param1);
	}
}

function remove_workflow($workflow_id){
	global $user;
    if(!user_access(apiaryPermissionsClass::$ADMINISTER_APIARY)) {
      $msg = "You do not have permission to delete this workflow.";
    }
    else {
		$success = Workflow::delete($workflow_id);
		if($success) {
		  $msg = "Workflow successfully deleted.";
		}
		else {
		  $msg = "An unknown error occurred attempting to delete this workflow.";
		}
	}
	echo $msg;
}

function workflow($workflow_id) {
    $permission_name_list = Permission::getPermissionNameList();
    $drupal_user_name_list = Workflow::getDrupalUserList();//we can modify this to only return drupal users with certain permissions
    $drupal_user_count = sizeof($drupal_user_name_list);
	$object_pool_name_list = Object_Pool::getObjectPoolNameList();
    $object_pool_count = sizeof($object_pool_name_list);

	$workflow_name = '';
	$workflow_description = '';
	$object_pool_id = '';
    $create_button_style = '';
    $update_button_style = '';
    if($workflow_id != '0') {
      if(Workflow::workflow_id_exists($workflow_id)) {
        $workflow_id = $workflow_id;
        $workflow_record = Workflow::get($workflow_id);
        $workflow_name = $workflow_record['workflow_name'];
        $workflow_description = $workflow_record['workflow_description'];
        $object_pool_id = $workflow_record['object_pool_id'];
        $create_button_style = ' style="display:none;"';
        $selected_permission_list = Workflow::getPermissionList($workflow_id);
        $selected_user_list = Workflow_Users::getUserList($workflow_id);
        $selected_object_pool = Object_Pool::getNameFromID($object_pool_id);
      }
    }
    else {
      $update_button_style = ' style="display:none;"';
      $selected_user_list = array($user->name);
    }
    $update_button = '<input type="button" id="update_workflow_btn" name="update_workflow_btn"'.$update_button_style.' onClick="update_workflow();" value="Update Workflow" />';
    $create_button = '<input type="button" id="create_workflow_btn" name="create_workflow_btn"'.$create_button_style.' onClick="create_workflow();" value="Create Workflow" />';

    $permission_checkboxes = generateWorkflowPermissionCheckboxes($permission_name_list, $selected_permission_list);
    $drupal_user_names_combobox = generateDrupalUserNamesComboBox($drupal_user_name_list, $selected_user_list);
    $object_pool_name_combobox = generateObjectPoolNameComboBox($object_pool_name_list, $selected_object_pool);

  $server_base = variable_get('apiary_research_base_url', 'http://localhost');
  $home_link = '<p><h3><a href="'.$server_base.'/drupal">Home</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="'.$server_base.'/drupal/apiary/admin">Administer Apiary</a></h3></p>';
  echo $home_link.'
  <div id="workflow_content">
  <h3>Workflow</h3>
    <label for="workflow_name">Workflow Name:</label>
   <br/>
    <input type="text" name="workflow_name" id="workflow_name" value="'.$workflow_name.'" style="width:100px" />
   <p/>
    <label for="workflow_description">Workflow Description:</label>
   <br/>
    <input type="text" name="workflow_description" id="workflow_description" value="'.$workflow_description.'" style="width:100px" />
   <p/>
    <label>Permissions:</label>
   <br/>
    <label>Users with access to this workflow are allowed the following permissions:</label>
   <p/>
   '.$permission_checkboxes.'
   <p/>
    <label>Select users allowed to access this workflow.</label>
   <div id="drupal_users_cbox">
    '.$drupal_user_names_combobox.' <a href="#" class="overlay_drupal_user">create new user</a>
   </div>
   <p/>
    <label>Strategy:</label>
    <br/>
   <div id="object_pool_cbox">
    <label>Object Pool: </label>'.$object_pool_name_combobox.' <a href="#" class="overlay_object_pool">create new object pool</a>
   </div>
   <br/>
   <p/>
    <label>Priority</label>
   <p/>
    <label>Current Queue</label>
   <p/>
    '.$create_button.''.$update_button.'
   <br/>
  </div>
  <div class="nothing" id="variables" name="variables" style="display:none;">
  	<input type="hidden" name="drupal_user_count" id="drupal_user_count" value="'.$drupal_user_count.'"/>
  	<input type="hidden" name="object_pool_count" id="object_pool_count"  value="'.$object_pool_count.'"/>
</div>
';
}

function process_workflow() {
  $workflow_successfully_created = "false";
  if(isset($_POST['workflow_name'])){
    $workflow_name = $_POST['workflow_name'];
    $workflow_description = $_POST['workflow_description'];
    if(isset($_POST['object_pool_name'])) {
      $object_pool_name = $_POST['object_pool_name'];
      $object_pool_id = Object_Pool::getIDFromName($object_pool_name);
    }
    if(isset($_POST['workflow_id']) && $_POST['workflow_id'] != '' && $_POST['workflow_id'] != '0'){
      //updating an existing workflow
      //update values
      $workflow_id = $_POST['workflow_id'];
      if(Workflow::update($workflow_id, $workflow_name, $workflow_description, $object_pool_id, $permission_list, $user_list)) {
        $workflow_successfully_created = "true";
        $msg = 'Workflow '.$workflow_name.' successfully updated.';
      }
      else {
        $msg = 'Workflow '.$workflow_name.' unable to be updated.';
      }
    }
    else {
      //creating new workflow
      if(Workflow::create($workflow_name, $workflow_description, $object_pool_id, $permission_list, $user_list)) {
        $workflow_successfully_created = "true";
        $workflow_id = Workflow::getIDFromName($workflow_name);
        $msg = 'Workflow '.$workflow_name.' successfully created.';
      }
      else {
        $msg = 'Workflow '.$workflow_name.' unable to be created.';
      }
    }
    $returnJSON['workflow_successfully_created'] = $workflow_successfully_created;
    $returnJSON['msg'] = $msg;
    if($workflow_successfully_created == "true") {
      $returnJSON['workflow_id'] = $workflow_id;
      $returnJSON = process_success($workflow_id, $returnJSON);
    }
  }
  else {
    $returnJSON['workflow_successfully_created'] = $workflow_successfully_created;
    $returnJSON['msg'] = 'A workflow name must be set to create a workflow.';
  }
  echo json_encode($returnJSON);
}

  function process_success($workflow_id, $returnJSON) {
    $returnJSON = insert_permissions($workflow_id, $returnJSON);
    $returnJSON = insert_users($workflow_id, $returnJSON);
    return $returnJSON;
  }

  function insert_permissions($workflow_id, $returnJSON) {
    $permissions = trim($_POST['permissions']);
    $permission_list = explode(',', $permissions);
    $permissions_to_insert = count($permission_list);
    $permissions_inserted = 0;
    $permissions_excluded = '';
    db_query("DELETE FROM {apiary_project_workflow_permission} WHERE workflow_id='$workflow_id'");
    for($i=0;$i<count($permission_list);$i++) {
      $permission_name = $permission_list[$i];
      if($permission_name != '') {
        $permission_id = Permission::getIDFromName($permission_name);
        if(db_query("INSERT INTO {apiary_project_workflow_permission} (workflow_id, permission_id) Values('%s','%s')", $workflow_id, $permission_id)) {
          $permissions_inserted++;
        }
        else {
          $permissions_excluded .= $permission_name;
        }
      }
    }
    if($permissions_inserted == $permissions_to_insert) {
      $returnJSON['workflow_permissions_successfully_created'] = "true";
      $returnJSON['msg'] .= " All permissions sucessfully set";
    }
    else {
      $returnJSON['workflow_permissions_successfully_created'] = "false";
      $returnJSON['workflow_permissions_successfully_entered'] = $permissions_inserted;
      $returnJSON['workflow_permissions_excluded'] = $permissions_excluded;
      $returnJSON['msg'] .= ' Not all permissions sucessfully set, only '.$permissions_inserted.' inserted. Excluded permission are '.$permissions_excluded.'.';
    }
    return $returnJSON;
  }

  function insert_users($workflow_id, $returnJSON) {
    if(isset($_POST['selected_users']) && $_POST['selected_users'] != '') {
      $selected_users = trim($_POST['selected_users']);
      $user_list = explode(',', $selected_users);
    }
    else {
      global $user;
      $user_list = array($user->name);
    }
    $users_to_insert = count($user_list);
    $users_inserted = 0;
    $users_excluded = '';
    db_query("DELETE FROM {apiary_project_workflow_users} WHERE workflow_id='$workflow_id'");
    for($i=0;$i<count($user_list);$i++) {
      $user_name = $user_list[$i];
      if($user_name != '') {
        $user_id = Workflow_Users::getUserIDFromName($user_name);
        if(db_query("INSERT INTO {apiary_project_workflow_users} (workflow_id, user_id) Values('%s','%s')", $workflow_id, $user_id)) {
          $users_inserted++;
        }
        else {
          $users_excluded .= $user_name;
        }
      }
    }
    if($users_inserted == $users_to_insert) {
      $returnJSON['workflow_users_successfully_created'] = "true";
      $returnJSON['msg'] .= " All users sucessfully set";
    }
    else {
      $returnJSON['workflow_users_successfully_created'] = "false";
      $returnJSON['workflow_users_successfully_entered'] = $users_inserted;
      $returnJSON['workflow_users_excluded'] = $users_excluded;
      $returnJSON['msg'] .= ' Not all users sucessfully set, only '.$users_inserted.' inserted. Excluded users are '.$users_excluded.'.';
    }
    return $returnJSON;
  }

function drupal_user_cbox($workflow_id, $working_selected_user_csv) {
  $working_selected_user_list = explode(",", $working_selected_user_csv);
  $drupal_user_name_list = Workflow::getDrupalUserList();//we can modify this to only return drupal users with certain permissions
  $drupal_user_count = sizeof($drupal_user_name_list);
  if($workflow_id != '0') {
    if(Workflow::workflow_id_exists($workflow_id)) {
      $selected_user_list = Workflow_Users::getUserList($workflow_id);
    }
  }
  else {
    $selected_user_list = array($user->name);
  }
  for($i=0; $i<sizeof($working_selected_user_list);$i++) {
    if(array_search($working_selected_user_list[$i], $selected_user_list) > -1) {
      //do not do anything
    }
    else {
      array_push($selected_user_list, $working_selected_user_list[$i]);
    }
  }
  $drupal_user_names_combobox = generateDrupalUserNamesComboBox($drupal_user_name_list, $selected_user_list);
  $returnJSON['drupal_user_count'] = $drupal_user_count;
  $returnJSON['drupal_user_names_combobox'] = $drupal_user_names_combobox;
  echo json_encode($returnJSON);
}

function drupal_user_cbox_option($user_name, $selected) {
  echo generateDrupalUserNamesComboBoxOption($user_name, $selected);
}

function object_pool_cbox_option($object_pool_name, $selected) {
  echo generateObjectPoolNameComboBoxOption($object_pool_name, $selected);
}

function solr_query_xml($q, $fl = '', $op = '', $rows = '') {
  $solr_search = new search();
  if(strpos('q=') === false){
    $q = 'q='.str_replace('&', '', $q);
  }
  $solr_query = $q;
  if(!empty($fl)) {
    if(strpos('fl=') === false){
      $fl = 'fl='.$fl;
    }
    $solr_query .= '&'.str_replace('&', '', $fl);
  }
  if(!empty($op)) {
    if(strpos('op=') === false){
      $op = 'op='.$op;
    }
    $solr_query .= '&'.str_replace('&', '', $op);
  }
  if(!empty($rows)) {
    if(strpos('rows=') === false){
      $rows = 'rows='.$rows;
    }
    $solr_query .= '&'.str_replace('&', '', $rows);
  }
  $solr_results = $solr_search->doSearch($solr_query);
  if($solr_results != false) {
    $solr_sxml = new SimpleXMLElement($solr_results);
    return $solr_sxml;
  }
  else {
    return false;
  }
}

?>