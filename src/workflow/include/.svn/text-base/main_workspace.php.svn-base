<?php
  $server_base = variable_get('apiary_research_base_url', 'http://localhost');
  include_once(drupal_get_path('module', 'apiary_project') . '/apiaryPermissionsClass.php');
  $server_base = variable_get('apiary_research_base_url', 'http://localhost');
  module_load_include('php', 'apiary_project', 'workflow/include/class.Workflow');
  global $user;
  $home_link = '<p><h2><a href="'.$server_base.'/drupal">Home</a></h2></p>';
  $admin_link = '<p><b><a href="'.$server_base.'/drupal/apiary/admin">Administer Apiary</a></b></p>'."\n";
  $assigned_workflows = array();
  if(user_access(apiaryPermissionsClass::$ADMINISTER_APIARY)) {
    $workflows = Workflow::getWorkflows();
    $assigned_workflows = Workflow::getWorkflowsForUserID($user->uid);
  }
  else {
    $workflows = Workflow::getWorkflowsForUserID($user->uid);
  }
  $workflow_list_html = '';
  for($i=0; $i<sizeof($workflows); $i++) {
    $note = '';
    if(sizeof($workflows) > sizeof($assigned_workflows) && sizeof($assigned_workflows) > 0) {
      if(!assigned_workflow_search($workflows[$i]['workflow_id'], $assigned_workflows)) {
        $note = ' *Note: You are not assigned to this workflow';
      }
    }
    $workflow_list_html .= '<p><b><a href="'.$server_base.'/drupal/modules/apiary_project/workflow/index.php?workflow_id='.$workflows[$i]['workflow_id'].'">'.$workflows[$i]['workflow_name'].'</a>'.$note.'</b>'."\n";
    $workflow_list_html .= '<br>'.$workflows[$i]['workflow_description'].'</p>'."\n";
  }

  function assigned_workflow_search($workflow_id, $assigned_workflows) {
    for($i=0; $i<sizeof($assigned_workflows); $i++) {
      if($assigned_workflows[$i]['workflow_id'] == $workflow_id) {
        return true;
      }
    }
    return false;
  }
?>
<?php echo $home_link;?>
<p><h3><span><span>Choose the Apiary workflow you wish to work on from the list below:</span></span></h3></p>
<?php echo $workflow_list_html;?>

<?
  if(user_access(apiaryPermissionsClass::$ADMINISTER_APIARY)) {
    echo $admin_link;
  }
?>