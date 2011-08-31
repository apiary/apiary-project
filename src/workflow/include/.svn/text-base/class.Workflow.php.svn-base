<?php

class Workflow {
  public $workflow_id;
  public $workflow_name;
  public $workflow_description;
  public $object_pool_id;
  public $object_pool;
  public $specimen_pids;
  public $image_pids;
  public $queued_image_pids = array();
  public $roi_pids;
  public $queued_roi_pids = array();
  public $specimens = array();
  public $images = array();
  public $rois = array();
  public $priority_id;
  public $workflow_pool;
  public $prioritized_workflow_pool;
  public $canAnalyzeSpecimen = false;
  public $canTranscribe = false;
  public $canParseL1 = false;
  public $canParseL2 = false;
  public $canParseL3 = false;
  public $canQC = false;
  //public $object_pool_list;
  //public $priority_list;
  public $permission_list;
  public $user_list;
  public $workflow_dom;
  private $workflow_added_by;
  private $workflow_added_date;
  private $workflow_updated_by;
  private $workflow_updated_date;
  private $fill_fedora = false;
  public $msg;

  function Workflow($workflow_id = null, $fill_fedora = false) {
    module_load_include('nc', 'Workflow', '');
    drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
    if(!empty($fill_fedora)) {
      $this->fill_fedora = $fill_fedora;
    }
    if(!empty($workflow_id)) {
      $this->workflow_id = $workflow_id;
      $this->loadDBWorkflow();
      $this->loadPermissions();
      $this->loadUsers();
      $this->loadWorkflowPools();
      $this->prioritizeWorkflowPool();
    }
  }

  function load_workflow_for_ui() {
    global $user;
    //add user check
    $workflow_db_record = $this->get($this->workflow_id);
    $this->workflow_name = $workflow_db_record['workflow_name'];
    $this->workflow_description = $workflow_db_record['workflow_description'];
    $this->object_pool_id = $workflow_db_record['object_pool_id'];
    $this->priority_id = $workflow_db_record['priority_id'];
  }

  function loadDBWorkflow() {
    global $user;
    //add user check
    $workflow_db_record = $this->get($this->workflow_id);
    $this->workflow_name = $workflow_db_record['workflow_name'];
    $this->workflow_description = $workflow_db_record['workflow_description'];
    $this->object_pool_id = $workflow_db_record['object_pool_id'];
    $this->priority_id = $workflow_db_record['priority_id'];
  }

  function loadPermissions() {
    $this->permission_list = $this->getPermissionList($this->workflow_id);
	if(array_search('canAnalyzeSpecimen', $this->permission_list) > -1){
	  $this->canAnalyzeSpecimen = true;
	}
	if(array_search('canTranscribe', $this->permission_list) > -1){
	  $this->canTranscribe = true;
	}
	if(array_search('canParseL1', $this->permission_list) > -1){
	  $this->canParseL1 = true;
	}
	if(array_search('canParseL2', $this->permission_list) > -1){
	  $this->canParseL2 = true;
	}
	if(array_search('canParseL3', $this->permission_list) > -1){
	  $this->canParseL3 = true;
	}
	if(array_search('canQC', $this->permission_list) > -1){
	  $this->canQC = true;
	}
  }

  function loadUsers() {
    $this->user_list = $this->getUserList($this->workflow_id);
  }

  static function getPermissionList($workflow_id) {
	module_load_include('php', 'apiary_project', 'workflow/include/class.Workflow_Permission');
	return Workflow_Permission::getPermissionList($workflow_id);
  }

  static function getUserList($workflow_id) {
	module_load_include('php', 'apiary_project', 'workflow/include/class.Workflow_Users');
	return Workflow_Users::getUserList($workflow_id);
  }

  static function doesWorkflowHavePermission($workflow_id, $permission_name) {
	module_load_include('php', 'apiary_project', 'workflow/include/class.Workflow_Permission');
	return Workflow_Permission::doesWorkflowHavePermission($workflow_id, $permission_name);
  }

  function addSolrIndexItems($solr_search, $pid, $workflow_dom, $workflow_dom_element) {
    $base_list = array('status_locked', 'status_lockedBy', 'locked_time', 'status_locked_session', 'status_qcStatus', 'status_qcStatus', status_qcStatusUpdatedBy);
    $image_index_list = array('status_analyzedStatus', 'analyzedStatusUpdatedBy');
    $roi_index_list = array('status_transcribedStatus', 'status_transcribedStatusUpdatedBy', 'status_parsedL1Status', 'status_parsedL1StausUpdatedBy', 'status_parsedL2Status', 'status_parsedL2StausUpdatedBy', 'status_parsedL3Status', 'status_parsedL3StausUpdatedBy');
    $solr_query = 'q=id:("'.$pid.'")';
    $solr_results = $solr_search->doSearch($solr_query);
    if($solr_results != false) {
      if(strpos($pid, 'ap-image:') > -1) {
        $pid_index_list = $image_index_list;
      }
      else if(strpos($pid, 'ap-roi:') > -1) {
        $pid_index_list = $roi_index_list;
      }
      $solr_sxml = new SimpleXMLElement($solr_results);
      if($solr_sxml->result[0]->doc[0] == null) {
        for($i=0; $i<count($base_list);$i++) {
	      $new_element = $workflow_dom->createElement(str_replace('status_', '', $base_list[$i]), '');
          $workflow_dom_element->appendChild($new_element);
        }
        for($i=0; $i<count($pid_index_list);$i++) {
	      $new_element = $workflow_dom->createElement(str_replace('status_', '', $pid_index_list[$i]), '');
          $workflow_dom_element->appendChild($new_element);
        }
      }
      else {
        foreach($solr_sxml->result[0]->doc[0]->children() as $sxml_node) {
          $sxml_arr = $sxml_node->attributes();
          $sxml_attrib_name = $sxml_arr["name"];
          if(array_search($sxml_attrib_name, $base_list) > -1 || array_search($sxml_attrib_name, $pid_index_list) > -1) {
	        $new_element = $workflow_dom->createElement(str_replace('status_', '', $sxml_attrib_name), $sxml_node);
            $workflow_dom_element->appendChild($new_element);
            if(str_replace('status_', '', $sxml_attrib_name) == 'locked') {
              $locked = $sxml_node;
            }
            if(str_replace('status_', '', $sxml_attrib_name) == 'locked_time') {
              $locked_time = (double)$sxml_node;
            }
            if(str_replace('status_', '', $sxml_attrib_name) == 'locked_session') {
              $locked_session = $sxml_node;
            }
          }
        }
      }
      $workflow_status = $this->getWorkflowStatus($locked, $locked_time, $locked_session);
	  $new_element = $workflow_dom->createElement('workflow_status', $workflow_status);
	  if($workflow_status == "queued") {
        if(strpos($pid, 'ap-image:') > -1) {
          $this->addToQueuedImageList($pid);
        }
        else if(strpos($pid, 'ap-roi:') > -1) {
          $this->addToQueuedROIList($pid);
        }
	  }
      $workflow_dom_element->appendChild($new_element);
    }
  }

  function getWorkflowStatus($locked, $locked_time, $locked_session) {
    $workflow_status = "locked";
    if($locked == "true") {
      if($this->isLockedExpired($locked_time, $locked_session)) {
        $workflow_status = "available";
      }
      else if($_SESSION['apiary_session_id'] == $locked_session) {
        $workflow_status = "queued";
      }
    }
    else {
      $workflow_status = "available";
    }
    return $workflow_status;
  }

  function addToQueuedImageList($image_pid) {
    $index = array_search($image_pid, $this->queued_image_pids);
    if($index > -1) {
      //it's already in there
    }
    else {
      array_push($this->queued_image_pids, $image_pid);
    }
  }

  function addToQueuedROIList($roi_pid) {
    $index = array_search($roi_pid, $this->queued_roi_pids);
    if($index > -1) {
      //it's already in there
    }
    else {
      array_push($this->queued_roi_pids, $roi_pid);
    }
  }

  static function isLockedExpired($locked_time, $locked_session) {
    $now = date("YmdHis");
    $apiary_timeout = variable_get('apiary_object_timeout', '1800');
    if($now > ($locked_time + $apiary_timeout)) {
      return true;
    }
    else if(!Workflow_Sessions::active_session($locked_session)) {
      return true;
    }
    else {
      return false;
    }
  }

  function loadWorkflowDom($workflow_id = null) {
    if(empty($this->workflow_id)) {
      $this->workflow_id = $workflow_id;
      $this->loadDBWorkflow(); //loads this->object_pool_id
    }
    module_load_include('php', 'apiary_project', 'workflow/include/class.Object_Pool');
    $this->object_pool = new Object_Pool($this->object_pool_id);

    include_once(drupal_get_path('module', 'apiary_project') . '/workflow/include/search.php');
    $solr_search = new search();

    //$time_start = $this->microtime_float();
	$workflow_dom = new DOMDocument('1.0', 'iso-8859-1');
    $workflowElement = $workflow_dom->createElement('workflow', '');//rootElement
    $workflow_dom->appendChild($workflowElement);
    foreach($this->object_pool->object_pool_pids as $pid) {
	  switch($pid) {
	    case (strpos($pid, 'ap-specimen:') > -1) :
	      $specimenElement = $workflow_dom->createElement("specimen");
	      $specimen_pidElement = $workflow_dom->createElement("pid", $pid);
          $specimenElement->appendChild($specimen_pidElement);
          $specimen_images = AP_Specimen::getImageListForSpecimen($pid);
          if(sizeOf($specimen_images) > 0) {
            foreach($specimen_images as $image_pid) {
	          $imageElement = $workflow_dom->createElement("image");
	          $image_pidElement = $workflow_dom->createElement("pid", $image_pid);
              $imageElement->appendChild($image_pidElement);
	          $this->addSolrIndexItems($solr_search, $image_pid, $workflow_dom, $imageElement);
              $specimen_image_rois = AP_Image::getROIListForImage($image_pid);
              if(sizeOf($specimen_image_rois) > 0) {
                foreach($specimen_image_rois as $roi_pid) {
	              $roiElement = $workflow_dom->createElement("roi");
	              $roi_pidElement = $workflow_dom->createElement("pid", $roi_pid);
                  $roiElement->appendChild($roi_pidElement);
	              $this->addSolrIndexItems($solr_search, $roi_pid, $workflow_dom, $roiElement);
                  $imageElement->appendChild($roiElement);
                }
              }
              $specimenElement->appendChild($imageElement);
            }
          }
          $workflowElement->appendChild($specimenElement);
	      break;
	  }
	}
    //$time_end = $this->microtime_float();
    //$time = $time_end - $time_start;
    //echo "Did it in $time seconds\n";
    $this->workflow_dom = $workflow_dom;
  }

  function loadWorkflowPools() {
    module_load_include('php', 'apiary_project', 'workflow/include/class.Object_Pool');
    $this->object_pool = new Object_Pool($this->object_pool_id);

	$specimens = array();
	$images = array();
	$rois = array();
	$specimen_pids = array();
	$image_pids = array();
	$roi_pids = array();
	$queued_image_pids = array();
	$queued_roi_pids = array();
	$workflow_pids = array();
	$workflow_dom = new DOMDocument('1.0', 'iso-8859-1');
    $workflowElement = $workflow_dom->createElement('workflow', '');//rootElement
    $workflow_dom->appendChild($workflowElement);
    //$time_start = $this->microtime_float();

    include_once(drupal_get_path('module', 'apiary_project') . '/workflow/include/search.php');
    $solr_search = new search();

	foreach($this->object_pool->object_pool_pids as $pid) {
	  switch($pid) {
	    case (strpos($pid, 'ap-specimen:') > -1) :
	      $specimenElement = $workflow_dom->createElement("specimen");
	      $specimen_pidElement = $workflow_dom->createElement("pid", $pid);
          $specimenElement->appendChild($specimen_pidElement);
		  array_push($specimen_pids, $pid);
		  array_push($workflow_pids, $pid);
		  $specimen['pid'] = $pid;
		  if($this->fill_fedora) {
		    $specimenMetadata = AP_Specimen::getspecimenMetadata_record($pid);
		    $specimen['specimenMetadata'] = $specimenMetadata;
		  }
          $specimen_images = AP_Specimen::getImageListForSpecimen($pid);
          //$specimen['images'] = $specimen_images;
          if(sizeOf($specimen_images) > 0) {
            foreach($specimen_images as $image_pid) {
              $image = array();
              $image['pid'] = $image_pid;
	          $imageElement = $workflow_dom->createElement("image");
	          $image_pidElement = $workflow_dom->createElement("pid", $image_pid);
              $imageElement->appendChild($image_pidElement);
	          $this->addSolrIndexItems($solr_search, $image_pid, $workflow_dom, $imageElement);
              if($this->canAnalyzeSpecimen || $this->canQC) {
                array_push($image_pids, $image_pid);
		        array_push($workflow_pids, $image_pid);
		      }
              $specimen_image_rois = AP_Image::getROIListForImage($image_pid);
              $image['rois'] = $specimen_image_rois;
              $specimen['rois'] .= $specimen_image_rois;
              if(sizeOf($specimen_image_rois) > 0) {
                foreach($specimen_image_rois as $roi_pid) {
	              $roiElement = $workflow_dom->createElement("roi");
	              $roi_pidElement = $workflow_dom->createElement("pid", $roi_pid);
                  $roiElement->appendChild($roi_pidElement);
	              $this->addSolrIndexItems($solr_search, $roi_pid, $workflow_dom, $roiElement);
                  array_push($roi_pids, $roi_pid);
		          array_push($workflow_pids, $roi_pid);
                  $imageElement->appendChild($roiElement);
                }
              }
             // $specimen['images'] .= $specimen_images;
              $specimenElement->appendChild($imageElement);
            }
          }
		  array_push($specimens, $specimen);
          $workflowElement->appendChild($specimenElement);
	      break;

	    case (strpos($pid, 'ap-image:') > -1) :
	      //see if already loaded
		  array_push($image_pids, $pid);
		  array_push($workflow_pids, $pid);
	      break;
	    case (strpos($pid, 'ap-roi:') > -1) :
		  //see if already loaded
		  array_push($roi_pids, $pid);
		  array_push($workflow_pids, $pid);
	      break;
	  }
	}
    //echo "Session_ID = ".$_SESSION['apiary_session_id']."<br>\n";
	//echo $workflow_dom->saveXML()."<br>\n";
    //$time_end = $this->microtime_float();
    //$time = $time_end - $time_start;
    //echo "Did it in $time seconds\n";

    $this->workflow_dom = $workflow_dom;
    //echo $workflow_dom->saveXML();
	$this->specimens = $specimens;
	$this->specimen_pids = $specimen_pids;
	$this->image_pids = $image_pids;
    //$this->queued_image_pids = $queued_image_pids;
    $this->roi_pids = $roi_pids;
    //$this->queued_roi_pids = $queued_roi_pids;
    $this->workflow_pool = $workflow_pids;
  }

  function microtime_float() {
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
  }

  function addROIsForImage($image_pid) {
    $image_rois = AP_Specimen::getImageListForSpecimen($specimen_pid);
    $rois = array();
    for($i; $i<sizeOf($image_rois); $i++) {
      $roi_pid = $image_rois[$i];
      if(array_search($roi_pid, $this->permission_list) > -1) {
        //don't add anything, it's already there!
      }
      else {
        //array_push((array) $this->roi_pids, $roi_pid);
        $roi['pid'] = $roi_pid;
        $roiMetadata = AP_ROI::getROIMetadata_record($roi_pid);
        $roi['roiMetadata'] = $roiMetadata;
        array_push($rois, $roi);
      }
    }
    return $rois;
  }

  function prioritizeWorkflowPool() {
  }

  static function prioritizeWorkflowObjectPool($workflow_id, $priority_id) {

  }

  static function getWorkflows() {
	$sql = "SELECT * FROM {apiary_project_workflow}";
	$results = db_query($sql);
	while ($workflow = db_fetch_array($results)) {
	  $workflows[] = $workflow;
    }
    return $workflows;
  }

  static function getWorkflowsForUserID($user_id) {
	$results = db_query("SELECT * FROM apiary_project_workflow LEFT JOIN apiary_project_workflow_users ON apiary_project_workflow.workflow_id=apiary_project_workflow_users.workflow_id WHERE apiary_project_workflow_users.user_id='%s'", $user_id);
	while ($workflow = db_fetch_array($results)) {
	  $workflows[] = $workflow;
    }
    return $workflows;
  }

  static function get($workflow_id) {
	$sql = "SELECT * FROM {apiary_project_workflow} WHERE workflow_id = '$workflow_id'";
	$results = db_query($sql);
	while ($fields = db_fetch_array($results)) {
	  foreach($fields as $key => $value) {
	    $workflow[$key] = $value;
	  }
    }
    return $workflow;
  }

  static function getNameFromID($workflow_id) {
	$result = db_query("SELECT workflow_name FROM {apiary_project_workflow} WHERE workflow_id=:workflow_id", array(':workflow_id' => $workflow_id));
	while ($record = db_fetch_object($result)) {
      $workflow_name = $record->workflow_name;
    }
    return $workflow_name;
  }

  static function getIDFromName($workflow_name) {
	$results = db_query("SELECT workflow_id FROM {apiary_project_workflow} WHERE workflow_name='%s'", $workflow_name);
	while ($result = db_fetch_object($results)) {
      $workflow_id = $result->workflow_id;
    }
    return $workflow_id;
  }

  static function delete($workflow_id) {
	$sql = "DELETE FROM {apiary_project_workflow_users} WHERE workflow_id = '$workflow_id'";
	if(db_query($sql)) {
	  $sql = "DELETE FROM {apiary_project_workflow_permission} WHERE workflow_id = '$workflow_id'";
	  if(db_query($sql)) {
	    $sql = "DELETE FROM {apiary_project_workflow} WHERE workflow_id = '$workflow_id'";
	    return db_query($sql);
	  }
	  else {
	    return false;
	  }
	}
	else {
	  return false;
	}
  }

  function save($workflow_id = null, $workflow_name = null, $workflow_description = null, $object_pool_id = null, $permission_list = null, $user_list = null) {
  	if($workflow_id == null && $this->workflow_id != null && $this->workflow_id != '') {
  	  $workflow_id = $this->workflow_id;
  	}
  	if($workflow_name == null && $this->workflow_name != null && $this->workflow_name != '') {
  	  $workflow_name = $this->workflow_name;
  	}
  	else {
  	  $this->msg = 'A Workflow Name is required.<br>';
  	  return false;
  	}
  	if($workflow_description == null && $this->workflow_description != null && $this->workflow_description != '') {
  	  $workflow_description = $this->workflow_description;
  	}
  	if($object_pool_id == null && $this->object_pool_id != null && $this->object_pool_id != '') {
  	  $object_pool_id = $this->object_pool_id;
  	}
  	if($permission_list == null && count($this->permission_list) > 0) {
  	  $permission_list = $this->permission_list;
  	}
  	if($user_list == null && count($this->user_list) > 0) {
  	  $user_list = $this->user_list;
  	}
  	if($workflow_id == null || $workflow_id = '') {
  	  return create($workflow_name, $workflow_description, $object_pool_id, $permission_list, $user_list);
  	}
  	else {
  	  return update($workflow_id, $workflow_description, $workflow_name, $object_pool_id, $permission_list, $user_list);
  	}
  }

  static function create($workflow_name, $workflow_description = null, $object_pool_id = null, $permission_list = null, $user_list = null) {
  	if($workflow_name == null || $workflow_name == '') {
  	  echo 'A workflow name is required.<br>';
  	  return false;
  	}
    global $user;
    $workflow_added_by = $user->uid;
  	$workflow_added_date = date("Y-m-d H:i:s");
	if(!Workflow::workflow_name_exists($workflow_name)) {
	  $insert_record = db_query("INSERT into {apiary_project_workflow} (workflow_name, workflow_description, object_pool_id, workflow_added_by, workflow_added_date)
	                             VALUES ('%s', '%s', '%s', '%s', '%s')", $workflow_name, $workflow_description, $object_pool_id, $workflow_added_by, $workflow_added_date);
      return $insert_record;
    }
    else {
	  echo "A unique workflow name is required. <br>";
    }
    return false;
  }

  static function update($workflow_id, $workflow_name, $workflow_description = null, $object_pool_id = null, $permission_list = null, $user_list = null) {
  	if($workflow_id == null || $workflow_id == '') {
  	  echo 'A workflow id is required.<br>';
  	  return false;
  	}
  	global $user;
  	$workflow_updated_by = $user->id;
  	$workflow_updated_date = date("Y-m-d H:i:s");
  	//this check needs to make sure the workflow_name does not exist anywhere except for the current id
	if(Workflow::workflow_name_is_valid($workflow_name, $workflow_id)) {
	  $update_query = "UPDATE {apiary_project_workflow} SET workflow_name='%s', workflow_description='%s', object_pool_id='%s', workflow_updated_by='%s', workflow_updated_date='%s' WHERE workflow_id='%s'";
	  $update_record = db_query($update_query, $workflow_name, $workflow_description, $object_pool_id, $workflow_updated_by, $workflow_updated_date, $workflow_id);
      return $update_record;
    }
    else {
	  echo "The workflow name ".$workflow_name." is not unique to workflow id ".$workflow_id.". <br>\n";
    }
    return false;
  }

  static function workflow_id_exists($workflow_id) {
	$sql = 'SELECT workflow_id FROM {apiary_project_workflow}';
	$results = db_query($sql);
	while ($result_workflow_id = db_result($results)) {
	  if($result_workflow_id != '' && $result_workflow_id !=  null)  {
	    return true;
	  }
    }
    return false;
  }

  static function workflow_name_exists($workflow_name) {
	$sql = "SELECT workflow_name FROM {apiary_project_workflow} WHERE workflow_name='$workflow_name'";
	$results = db_query($sql);
	while ($result_workflow_name = db_result($results)) {
	  if($result_workflow_name != '' && $result_workflow_name !=  null)  {
	    return true;
	  }
    }
    return false;
  }

  static function workflow_name_is_valid($workflow_name, $workflow_id = 0) {
	$sql = "SELECT workflow_name, workflow_id FROM {apiary_project_workflow} WHERE workflow_name='$workflow_name'";
	$results = db_query($sql);
	while ($result = db_fetch_object($results)) {
      $result_workflow_name = $result->workflow_name;
      $result_workflow_id = $result->workflow_id;
	  if(!empty($result_workflow_name) && !empty($result_workflow_id) && $result_workflow_id != $workflow_id)  {
	    //name exists for another workflow
	    return false;
	  }
    }
    return true;
  }

  static function getDrupalUserList() {
	//$sql = "SELECT name, mail FROM {users} ORDER BY name";
	//$results = db_query($sql);

	//Only returns user who can access Apiary
    $require_role_to_use_apiary_workflow = 'administrator';
    $results = db_query("SELECT rid FROM {role} WHERE NAME='%s'", $require_role_to_use_apiary_workflow);
    $result = db_fetch_object($results);
    $rid = $result->rid;
	$results = db_query("SELECT name, mail FROM {users} users LEFT JOIN {users_roles} users_roles ON users.uid=users_roles.uid WHERE users_roles.rid='%s' ORDER BY name", $rid);
	while ($drupal_user = db_fetch_array($results)) {
	  $drupal_users[] = $drupal_user;
    }
    return $drupal_users;
  }
}

?>