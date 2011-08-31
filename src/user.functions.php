<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
global $user;
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

function new_drupal_user_html() {
  echo '
<div id="drupal_user">
<h3>Create New Apiary Authorized Drupal User</h3>
 <br/>
  <label for="drupal_user_name">Drupal User Name:</label>
 <br/>
  <input type="text" name="drupal_user_name" id="drupal_user_name" />
 <p/>
  <label for="drupal_user_pass">Drupal User Password:</label>
 <br/>
  <input type="password" name="drupal_user_pass" id="drupal_user_pass" />
 <p/>
  <label for="drupal_user_name">Drupal User Email:</label>
 <br/>
  <input type="text" name="drupal_user_email" id="drupal_user_email" />
 <p/>
 <span>
  <input type="button" name="create_drupal_user_btn" onClick="create_drupal_user(\'true\', \'true\');" value="Create Drupal User" />
  <input type="button" name="create_drupal_user_btn" onClick="create_drupal_user(\'true\', \'false\');" value="Create Drupal User and Close" />
  <input type="button" name="create_drupal_user_btn" onClick="create_drupal_user(\'false\', \'false\');" value="Cancel" />
  </span>
 <br/>
</div>';
}

function create_drupal_user() {
  $user_successfully_created = "false";
  $server_base = variable_get('apiary_research_base_url', 'http://localhost');
  include_once(drupal_get_path('module', 'apiary_project') . '/apiaryPermissionsClass.php');
  $user_name = '';
  if(user_access(apiaryPermissionsClass::$ADMINISTER_APIARY)) {
    if(isset($_POST['name']) && $_POST['name'] != '') {
      if(isset($_POST['mail']) && $_POST['mail'] != '') {
        $name = $_POST['name'];
        $mail = $_POST['mail'];
        if(isset($_POST['pass']) && $_POST['pass'] != '') {
          $pass =$_POST['pass']; //using drupals user_save function does the md5 hash
          //$pass = md5($_POST['pass']);
        }
        else {
          $pass = user_password(); //drupal function to create a md5 hash password
        }

        $require_role_to_use_apiary_workflow = 'administrator'; //this gets assigned to the created user

	    $results = db_query("SELECT rid FROM {role} WHERE NAME='%s'", $require_role_to_use_apiary_workflow);
        $result = db_fetch_object($results);
        $rid = $result->rid;
        $newuser = array(
          'name' => $name,
          'mail' => $mail,
          'status' => 1,
          'pass' => $pass,
          'roles' => array($rid => $require_role_to_use_apiary_workflow)
        );
        $new_user = user_save('', $newuser);
        if($new_user != false) {
          $user_successfully_created = "true";
          $user_name = $name;
          $msg = "User ".$new_user->name." successfully created.";
        }
        else {
          $msg = "User ".$new_user->name." failed to be created.";
        }
      }
      else {
        $msg = "No e-mail address was provided.";
      }
    }
    else {
      $msg = "No username was provided.";
    }
  }
  else {
    $msg = "You do not have permissions to create new users.";
  }
  $returnJSON['user_name'] = $user_name;
  $returnJSON['user_successfully_created'] = $user_successfully_created;
  $returnJSON['msg'] = $msg;
  echo json_encode($returnJSON);
}

?>