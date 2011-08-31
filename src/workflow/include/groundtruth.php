<?php
  $server_base = variable_get('apiary_research_base_url', 'http://localhost');
  include_once(drupal_get_path('module', 'apiary_project') . '/apiaryPermissionsClass.php');
  $home_link = '<p><h2><a href="'.$server_base.'/drupal">Home</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="'.$server_base.'/drupal/apiary/admin">Administer Apiary</a></h2></p>';
  $ajax_url = $server_base."/drupal/apiary/workflow_ajax";
  if(!user_access(apiaryPermissionsClass::$ADMINISTER_APIARY)) {
    return false;
  }
  drupal_add_js('modules/apiary_project/workflow/assets/js/jquery-1.5.1.min.js');
  drupal_add_js('modules/apiary_project/workflow/assets/js/apiary.groundtruth.js');
  if($_GET['specimen_pid'] != '') {
    $specimen_pid = $_GET['specimen_pid'];
  }
?>
<?php echo $home_link;?>

<p><h2><span><span>Ground Truth</span></span></h2></p>
<div id="groundtruth_content">
<input type="text" name="specimen_pid" id="specimen_pid" value="<?php echo $specimen_pid; ?>"/>
</div> <!-- groundtruth_content -->
<div class="nothing" style="display:none;">
<div id="ajax_url"><?php echo $ajax_url; ?></div>
</div>