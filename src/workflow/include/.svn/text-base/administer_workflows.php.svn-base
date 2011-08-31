<?php
  $server_base = variable_get('apiary_research_base_url', 'http://localhost');
  include_once(drupal_get_path('module', 'apiary_project') . '/apiaryPermissionsClass.php');
  module_load_include('php', 'apiary_project', 'workflow/include/class.Workflow');
  $home_link = '<p><h2><a href="'.$server_base.'/drupal">Home</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="'.$server_base.'/drupal/apiary/admin">Administer Apiary</a></h2></p>';
  $ajax_url = $server_base."/drupal/apiary/workflow_editor/remove_workflow";
  $workflow_url = $server_base.'/drupal/modules/apiary_project/workflow/workflow.php';
  if(!user_access(apiaryPermissionsClass::$ADMINISTER_APIARY)) {
    return false;
  }
  $workflows = Workflow::getWorkflows();
  //global $user;
  //$workflows = Workflow::getWorkflowsForUserID($user->uid);
  $workflow_list_html = '';
  for($i=0; $i<sizeof($workflows); $i++) {
    $workflow_list_html .= '<div id="'.$workflows[$i]['workflow_id'].'"><p><a href="'.$workflow_url.'?workflow_id='.$workflows[$i]['workflow_id'].'">'.$workflows[$i]['workflow_name'].'</a> - <a href="javascript:delete_workflow(\''.$workflows[$i]['workflow_id'].'\', \''.$ajax_url.'\')">Delete</a></p></div>'."\n";
  }
  drupal_add_js('modules/apiary_project/workflow/assets/js/apiary.administer_workflows.js');
?>
<?php echo $home_link;?>

<p><h2><span><span>Choose Task to Perform</span></span></h2></p>
<p><a href="<?php echo $workflow_url;?>">New Workflow</a></p>
<p>Select a Workflow to Edit</p>
<?php echo $workflow_list_html;?>

<p>&nbsp;</p>