<?php
include_once(drupal_get_path('module', 'apiary_project') . '/apiaryPermissionsClass.php');
function upScaleCoordinates($x1, $y1, $width, $height, $parent_image_width, $parent_image_height, $parent_image_display_width, $parent_image_display_height) {
  //We display the source image at a different size than it actually it, we need to convert the cropped coordinates
  $true_width = $parent_image_width;
  $true_height = $parent_image_height;
  $width_ratio = $true_width/$parent_image_display_width;
  $height_ratio = $true_height/$parent_image_display_height;
  $x1r = $x1*$width_ratio;
  $y1r = $y1*$height_ratio;
  $widthr = $width*$width_ratio;
  $heightr = $height*$height_ratio;
  $x1 = (int)$x1r;
  $y1 = (int)$y1r;
  $width = (int)$widthr;
  $height = (int)$heightr;
  $true_x1 = $x1;
  $true_y1 = $y1;
  $true_width = $width;
  $true_height = $height;

  return array($true_x1, $true_y1, $true_width, $true_height);
}

function generateWorkflowPermissionCheckboxes($permission_name_list, $selected_permission_list) {
  $workflow_permission_checkbox_html = '';
  for($i = 0; $i < sizeof($permission_name_list); $i++) {
    $workflow_permission_checkbox_html .= '<input type="checkbox" name="permissions" value="'.$permission_name_list[$i].'"';
    if(array_search($permission_name_list[$i], $selected_permission_list) > -1) {
      $workflow_permission_checkbox_html .= ' checked';
    }
    $workflow_permission_checkbox_html .= '>'.$permission_name_list[$i].'<br/>'."\n";
  }
  return $workflow_permission_checkbox_html;
}

function generateObjectPoolNameComboBox($object_pool_name_list, $selected_object_pool) {
  $combobox_html = '';
  $combobox_html .= '<select class="input" name="object_pool_names" id="object_pool_names">'."\n";
  array_unshift($object_pool_name_list, ""); //adds blank selection to the beginning so it is the first option in this cbox
  for($i = 0; $i < sizeof($object_pool_name_list); $i++) {
    $object_pool_name = $object_pool_name_list[$i];
    $selected = '';
    if($object_pool_name_list[$i] == $selected_object_pool) {
      $selected .= ' selected';
    }
    $combobox_html .= generateObjectPoolNameComboBoxOption($object_pool_name, $selected);
  }
  $combobox_html .= '</select>'."\n";
  return $combobox_html;
}

function generateObjectPoolNameComboBoxOption($object_pool_name, $selected) {
  $combobox_option_html = '<option value="'.$object_pool_name.'"';
  if(strlen($selected) > 0) {
    $combobox_option_html .= ' '.trim($selected);
  }
  $combobox_option_html .= '>'.$object_pool_name.'</option>'."\n";
  return $combobox_option_html;
}

function generateDrupalUserNamesComboBox($drupal_user_name_list, $selected_user_list) {
  $combobox_html .= '<select name="drupal_user_names" id="drupal_user_names" multiple="multiple">'."\n";
  for($i = 0; $i < sizeof($drupal_user_name_list); $i++) {
    $user_name = $drupal_user_name_list[$i]['name'];
    if($user_name != '') {
      $selected = '';
      if(array_search($user_name, $selected_user_list) > -1) {
        $selected .= ' selected';
      }
      $combobox_html .= generateDrupalUserNamesComboBoxOption($user_name, $selected);
    }
  }
  $combobox_html .= '</select>'."\n";
  return $combobox_html;
}

function generateDrupalUserNamesComboBoxOption($user_name, $selected) {
  $combobox_option_html = '<option value="'.$user_name.'"';
  if(strlen($selected) > 0) {
    $combobox_option_html .= ' '.trim($selected);
  }
  $combobox_option_html .= '>'.$user_name.'</option>'."\n";
  return $combobox_option_html;
}

function getPriorityTypeList() {
  return array("ROI Count", "ROI Type", "ROI Metadata Keyword", "ROI General Keyword", "ROI Status", "Text Extraction Complete");
}

function generatePriorityTypeComboBox() {
  $priority_type_list = getPriorityTypeList();
  $combobox_html = '';
  $combobox_html .= '<select name="priority_types" id="priority_types" onchange="javascript:showParameters(this.options[this.selectedIndex].value);">';
  $combobox_html .= '<option value="" selected> </option>';
  for($i = 0; $i < sizeof($priority_type_list); $i++) {
    $combobox_html .= '<option value="'.$priority_type_list[$i].'">'.$priority_type_list[$i].'</option>';
  }
  $combobox_html .= '</select>';
  return $combobox_html;
}

function getMathCompareOptions() {
  return array("greater than", "less than", "equal to");
}

function generateROICountCompareComboBox() {
  $roicount_compare_list = getMathCompareOptions();
  $combobox_html = '';
  $combobox_html .= '<select name="roicount_compare" id="roicount_compare">';
  $combobox_html .= '<option value="" selected> </option>';
  for($i = 0; $i < sizeof($roicount_compare_list); $i++) {
    $combobox_html .= '<option value="'.$roicount_compare_list[$i].'">'.$roicount_compare_list[$i].'</option>';
  }
  $combobox_html .= '</select>';
  return $combobox_html;
}

function getStatusOptions() {
  return array("not started", "in progress", "incomplete", "completed", "in QC", "passed QC", "problem");
}

function generateAnalyzeSpecimenStatusComboBox() {
  $statuses = getStatusOptions();
  $combobox_html = '';
  $combobox_html .= '<select name="as_statuses" id="as_statuses">';
  $combobox_html .= '<option value="" selected> </option>';
  for($i = 0; $i < sizeof($statuses); $i++) {
    $combobox_html .= '<option value="'.$statuses[$i].'">'.$statuses[$i].'</option>';
  }
  $combobox_html .= '</select>';
  return $combobox_html;
}

function generateTranscribeTextStatusComboBox() {
  $statuses = getStatusOptions();
  $combobox_html = '';
  $combobox_html .= '<select name="tt_statuses" id="tt_statuses">';
  $combobox_html .= '<option value="" selected> </option>';
  for($i = 0; $i < sizeof($statuses); $i++) {
    $combobox_html .= '<option value="'.$statuses[$i].'">'.$statuses[$i].'</option>';
  }
  $combobox_html .= '</select>';
  return $combobox_html;
}

function generateParseTextStatusComboBox() {
  $statuses = getStatusOptions();
  $combobox_html = '';
  $combobox_html .= '<select name="pt_statuses" id="pt_statuses">';
  $combobox_html .= '<option value="" selected> </option>';
  for($i = 0; $i < sizeof($statuses); $i++) {
    $combobox_html .= '<option value="'.$statuses[$i].'">'.$statuses[$i].'</option>';
  }
  $combobox_html .= '</select>';
  return $combobox_html;
}

function getColorList() {
  //order is blue-0000ff, red-ff0000, limegreen-00ff00, magenta-ff00ff, yellow-ffff00, orange-ffa500, aqua-00ffff, yellowgreen-9acd32, orangered-ff4500, deeppink-ff1493
  return array('0000ff', 'ff0000', '00ff00', 'ff00ff', 'ffff00', 'ffa500', '00ffff', '9acd32', 'ff4500', 'ff1493');
}

function getROItypeList() {
  return array("Primary Label", "Determination Label", "Barcode", "Type", "Annotation/Other");
}

function getROItypeBorderColor($ROItype) {
  switch ($ROItype) {
    //order is blue-0000ff, red-ff0000, limegreen-00ff00, magenta-ff00ff, yellow-ffff00, orange-ffa500, aqua-00ffff, yellowgreen-9acd32, orangered-ff4500, deeppink-ff1493
    case "Primary Label":
        //blue-0000ff
        return "0000ff";
    case "Annotation":
        //red-ff0000
        return "ff0000";
    case "Barcode":
        //limegreen-00ff00
        return "00ff00";
    case "Determination Label":
        //magenta-ff00ff
        return "ff00ff";
    case "Other":
        //yellow-ffff00
        return "ffff00";
    case "Type":
        //orange-ffa500
        return "ffa500";
    case "Unknown":
        //aqua-00ffff
        return "00ffff";
  }
  //if there is no match return deeppink-ff1493
  return "ff1493";
}

function getPreviousPid($index, $pids) {
  $previous_index = $index - 1;
  if($previous_index < 0) {
    $previous_index = sizeOf($pids) - 1;
  }
  return $pids[$previous_index];
}

function getNextPid($index, $pids) {
  $next_index = $index + 1;
  if($next_index >= sizeOf($pids)) {
    $next_index = $next_index - sizeOf($pids);
  }
  return $pids[$next_index];
}
function getPidIndex($pid, $pids) {
  $index = array_search($pid, $pids);
  if($index > -1) {
    $index = $index;
  }
  else {
    $index = 0; //fail safe
  }
  return $index;
}

function test_print_workflow_dom($workflow_dom) {
  //echo "<br>workflow_dom saveXML <br>\n";
  //echo $workflow_dom->saveXML();
  $specimen_elements = $workflow_dom->getElementsByTagName('specimen');
  foreach($specimen_elements as $specimen) {
    echo $workflow_dom->saveXML($specimen)."<br>"."\n";
    $specimen_image_elements = $specimen->getElementsByTagName('image');
    foreach($specimen_image_elements as $image) {
      echo $workflow_dom->saveXML($image)."<br>"."\n";
      $specimen_image_roi_elements = $image->getElementsByTagName('roi');
      foreach($specimen_image_roi_elements as $roi) {
        echo $workflow_dom->saveXML($roi)."<br>"."\n";
      }
    }
  }
}

function generateWQ_TeeesFromDom($workflow_dom) {
  list($wq_workflow_treeitems_html, $wq_queue_treeitems_html) = generateWQ_WorkflowAndQueueTreeItemsFromDom($workflow_dom);
  $wq_workflow_tree_html = '';
  $wq_workflow_tree_html .= generateWQ_WorkflowTreeControl();
  $wq_workflow_tree_html .= '<ul id="wq_workflow_tree_ul" class="treeview-gray">';
  $wq_workflow_tree_html .= $wq_workflow_treeitems_html;
  $wq_workflow_tree_html .= '</ul>';

  $wq_queue_tree_html = '';
  $wq_queue_tree_html .= generateWQ_QueueTreeControl();
  $wq_queue_tree_html .= '<ul id="wq_queue_tree_ul" class="treeview-gray">';
  $wq_queue_tree_html .= $wq_queue_treeitems_html;
  $wq_queue_tree_html .= '</ul>';

  return array($wq_workflow_tree_html, $wq_queue_tree_html);
}

function createWQ_WorkflowTree($workflow_dom = null) {
  $wq_workflow_tree_html = '';
  $wq_workflow_tree_html .= generateWQ_WorkflowTreeControl();
  $wq_workflow_tree_html .= '<ul id="wq_workflow_tree_ul" class="treeview-gray">';
  if($workflow_dom != null) {
    $wq_workflow_tree_html .= generateWQ_WorkflowTreeItemsFromDom($workflow_dom);
  }
  $wq_workflow_tree_html .= '</ul>';
  return $wq_workflow_tree_html;
}

function createWQ_QueueTree($workflow_dom = null) {
  $wq_queue_tree_html = '';
  $wq_queue_tree_html .= generateWQ_QueueTreeControl();
  $wq_queue_tree_html .= '<ul id="wq_queue_tree_ul" class="treeview-gray">';
  if($workflow_dom != null) {
    $wq_queue_tree_html .= generateWQ_QueueTreeItemsFromDom($workflow_dom);
  }
  $wq_queue_tree_html .= '</ul>';
  return $wq_queue_tree_html;
}

function generateWQ_WorkflowTreeControl() {
   $wq_tree_control_html = '<div id="wq_workflowtreeview_toggler">';
   $wq_tree_control_html .= '<a title="Collapse entire tree" href="#"> Collapse All</a> |';
   $wq_tree_control_html .= '<a title="Expand entire tree" href="#"> Expand All</a>';
   $wq_tree_control_html .= '</div>';
   return $wq_tree_control_html;
}

function generateWQ_QueueTreeControl() {
   $wq_tree_control_html = '<div id="wq_queuetreeview_toggler">';
   $wq_tree_control_html .= '<a title="Collapse entire tree" href="#"> Collapse All</a> |';
   $wq_tree_control_html .= '<a title="Expand entire tree" href="#"> Expand All</a>';
   $wq_tree_control_html .= '</div>';
   return $wq_tree_control_html;
}

function generateWQ_WorkflowTreeItemsFromDom($workflow_dom) {
  $specimen_elements = $workflow_dom->getElementsByTagName('specimen');
  foreach($specimen_elements as $specimen) {
    $specimen_pid = $specimen->getElementsByTagName('pid')->item(0)->nodeValue;
    //$wq_tree_html .= '<li><span>'.generateWQ_WorkflowSpecimen($specimen_pid).'</span>';
    $specimen_image_elements = $specimen->getElementsByTagName('image');
    //$wq_tree_html .= '<ul id="wq_workflow_'.$specimen_id.'_ul">';
    foreach($specimen_image_elements as $image) {
      $image_pid = $image->getElementsByTagName('pid')->item(0)->nodeValue;
      $analyzedStatus = $image->getElementsByTagName('analyzedStatus')->item(0)->nodeValue;
      //$locked_time = (double)$image->getElementsByTagName('locked_time')->item(0)->nodeValue;
      //$locked_by = $image->getElementsByTagName('locked_by')->item(0)->nodeValue;
      //$locked_session = $image->getElementsByTagName('locked_session')->item(0)->nodeValue;
      //$locked = $image->getElementsByTagName('locked')->item(0)->nodeValue;
      $workflow_status = $image->getElementsByTagName('workflow_status')->item(0)->nodeValue;
      $wq_tree_html .= '<li><span>'.generateWQ_WorkflowImage($image_pid, $specimen_pid, $analyzedStatus, $workflow_status).'</span>';
      $specimen_image_roi_elements = $image->getElementsByTagName('roi');
      $wq_tree_html .= '<ul id="wq_workflow_'.$image_pid.'_ul">';
      foreach($specimen_image_roi_elements as $roi) {
        $roi_pid = $roi->getElementsByTagName('pid')->item(0)->nodeValue;
        $transcribedStatus = $image->getElementsByTagName('transcribedStatus')->item(0)->nodeValue;
        $parsedL1Status = $image->getElementsByTagName('parsedL1Status')->item(0)->nodeValue;
        $workflow_status = $image->getElementsByTagName('workflow_status')->item(0)->nodeValue;
        $wq_tree_html .= generateWQ_WorkflowROI($roi_pid, $specimen_pid, $image_pid, $transcribedStatus, $parsedL1Status, $workflow_status);
      }
      $wq_tree_html .= '</ul>';//end roi ul
      $wq_tree_html .= '</li>';//end image li
    }
    //$wq_tree_html .= '</ul>';//end image ul
    //$wq_tree_html .= '</li>';//end specimen li
  }
  return $wq_tree_html;
}

function generateWQ_QueueTreeItemsFromDom($workflow_dom) {
  $specimen_elements = $workflow_dom->getElementsByTagName('specimen');
  foreach($specimen_elements as $specimen) {
    $specimen_pid = $specimen->getElementsByTagName('pid')->item(0)->nodeValue;
    $specimen_image_elements = $specimen->getElementsByTagName('image');
    foreach($specimen_image_elements as $image) {
      $workflow_status = $image->getElementsByTagName('workflow_status')->item(0)->nodeValue;
      if($workflow_status == "queued") {
        $image_pid = $image->getElementsByTagName('pid')->item(0)->nodeValue;
        $analyzedStatus = $image->getElementsByTagName('analyzedStatus')->item(0)->nodeValue;
        $specimen_image_roi_elements = $image->getElementsByTagName('roi');
        foreach($specimen_image_roi_elements as $roi) {
          $workflow_status = $image->getElementsByTagName('workflow_status')->item(0)->nodeValue;
          $roi_li_html .= '';
          if($workflow_status == "queued") {
            $roi_pid = $roi->getElementsByTagName('pid')->item(0)->nodeValue;
            $transcribedStatus = $image->getElementsByTagName('transcribedStatus')->item(0)->nodeValue;
            $parsedL1Status = $image->getElementsByTagName('parsedL1Status')->item(0)->nodeValue;
            $roi_li_html .= generateWQ_QueueROI($roi_pid, $specimen_pid, $image_pid, $transcribedStatus, $parsedL1Status);
          }
        }
        $wq_tree_html .= generateWQ_QueueImage($image_pid, $specimen_pid, $analyzedStatus, $roi_li_html);
      }
    }
  }
  return $wq_tree_html;
}

function generateWQ_WorkflowAndQueueTreeItemsFromDom($workflow_dom) {
 //this function returns both workflow_html and queue_html in one iteration
  $wq_workflowtree_html = '';
  $wq_queuetree_html = '';
  $specimen_elements = $workflow_dom->getElementsByTagName('specimen');
  foreach($specimen_elements as $specimen) {
    $specimen_pid = $specimen->getElementsByTagName('pid')->item(0)->nodeValue;
    $specimen_image_elements = $specimen->getElementsByTagName('image');
    foreach($specimen_image_elements as $image) {
      $image_pid = $image->getElementsByTagName('pid')->item(0)->nodeValue;
      $specimen_image_roi_elements = $image->getElementsByTagName('roi');
      $wq_workflow_roi_li_html= '';
      $wq_queue_roi_li_html= '';
      $wq_queue_roi_count = 0;
      foreach($specimen_image_roi_elements as $roi) {
        $roi_pid = $roi->getElementsByTagName('pid')->item(0)->nodeValue;
        $transcribedStatus = $roi->getElementsByTagName('transcribedStatus')->item(0)->nodeValue;
        $parsedL1Status = $roi->getElementsByTagName('parsedL1Status')->item(0)->nodeValue;
        $roi_workflow_status = $roi->getElementsByTagName('workflow_status')->item(0)->nodeValue;
        if($roi_workflow_status == "queued") {
          $wq_queue_roi_li_html .= generateWQ_QueueROI($roi_pid, $specimen_pid, $image_pid, $transcribedStatus, $parsedL1Status);
          $wq_queue_roi_count = $wq_queue_roi_count + 1;
        }
        $wq_workflow_roi_li_html .= generateWQ_WorkflowROI($roi_pid, $specimen_pid, $image_pid, $transcribedStatus, $parsedL1Status, $roi_workflow_status);
      }

	  $analyzedStatus = $image->getElementsByTagName('analyzedStatus')->item(0)->nodeValue;
      $image_workflow_status = $image->getElementsByTagName('workflow_status')->item(0)->nodeValue;
      if($image_workflow_status == "queued") {
        $wq_queuetree_html .= generateWQ_QueueImage($image_pid, $specimen_pid, $analyzedStatus, $wq_queue_roi_li_html);
      }
      else if(wq_queue_roi_count > 0) {
        //queued ROIs by not a queued image. We generate the image with msg that it has not yet been queued
        $wq_queuetree_html .= generateWQ_QueueImage($image_pid, $specimen_pid, $analyzedStatus, $wq_queue_roi_li_html, false);
      }
      $wq_workflowtree_html .= generateWQ_WorkflowImage($image_pid, $specimen_pid, $analyzedStatus, $image_workflow_status, $wq_workflow_roi_li_html);
    }
  }
  return array($wq_workflowtree_html, $wq_queuetree_html);
}

function generateWQ_WorkflowROI($pid, $specimen_pid, $image_pid, $tt_status = '', $pt_status = '', $workflow_status = ''){
  $workflow_pool_obj_html = '<li>';
  $workflow_pool_obj_html .= '<span>';
  $workflow_pool_obj_html .= '<div name="wq_workflow_roi" id="wq_workflow_'.$pid.'" style="border:solid 1px green;" pid="'.$pid.'" image_pid="'.$image_pid.'" specimen_pid="'.$specimen_pid.'" workflow_status="'.$workflow_status.'" tt_status="'.$tt_status.'" pt_status="'.$pt_status.'">';
  $workflow_pool_obj_html .= '<table>';
  $workflow_pool_obj_html .= '<tr>';
  $workflow_pool_obj_html .= '<td style="border: hidden;">';
  $workflow_pool_obj_html .= '<div name="workflow_obj_'.$pid.'" id="workflow_obj_'.$pid.'" style="width:auto;">';
  $workflow_pool_obj_html .=  $pid;
  $workflow_pool_obj_html .= '</div>';
  $workflow_pool_obj_html .= '</td>';
  $workflow_pool_obj_html .= '<td style="border: hidden;">';
  $workflow_pool_obj_html .= '<div name="workflow_addToQueue_'.$pid.'" id="workflow_addToQueue_'.$pid.'">';
  $workflow_pool_obj_html .= '<a href="javascript:addToQueue(\''.$pid.'\', \''.$specimen_pid.'\', \''.$image_pid.'\')" style="float:right; padding-right:5px">Queue</a>';
  $workflow_pool_obj_html .= '</div>';
  $workflow_pool_obj_html .= '</td>';
  $workflow_pool_obj_html .= '</tr>';
  $workflow_pool_obj_html .= '<tr colspan=2>';
  $workflow_pool_obj_html .= '<td colspan=2 style="border: hidden;">';
  $workflow_pool_obj_html .= '<div name="workflow_tt_status_'.$pid.'" id="workflow_tt_status_'.$pid.'">';
  if(strlen($tt_status) > 0) {
    $workflow_pool_obj_html .= 'transcribed: '.$tt_status;
  }
  $workflow_pool_obj_html .= '</div>';
  $workflow_pool_obj_html .= '<div name="workflow_pt_status_'.$pid.'" id="workflow_pt_status_'.$pid.'">';
  if(strlen($pt_status) > 0) {
    $workflow_pool_obj_html .= 'parsed: '.$pt_status;
  }
  $workflow_pool_obj_html .= '</div>';
  $workflow_pool_obj_html .= '</td>';
  $workflow_pool_obj_html .= '</tr>';
  $workflow_pool_obj_html .= '</table>';
  $workflow_pool_obj_html .= '</div>';
  $workflow_pool_obj_html .= '</span>';
  $workflow_pool_obj_html .= '</li>';
  return $workflow_pool_obj_html;
}

function generateWQ_WorkflowImage($pid, $specimen_pid, $as_status, $workflow_status, $wq_workflow_roi_li_html){
  $workflow_pool_obj_html = '<li>';
  $workflow_pool_obj_html .= '<span>';
  $workflow_pool_obj_html .= '<div name="wq_workflow_image" id="wq_workflow_'.$pid.'" style="padding:0px; border:solid 1px green;" pid="'.$pid.'" specimen_pid="'.$specimen_pid.'" workflow_status="'.$workflow_status.'" as_status="'.$as_status.'">';
  $workflow_pool_obj_html .= '<table>';
  $workflow_pool_obj_html .= '<tr>';
  $workflow_pool_obj_html .= '<td style="border: hidden;">';
  $workflow_pool_obj_html .= '<div name="workflow_obj_'.$pid.'" id="workflow_obj_'.$pid.'" style="width:auto;">';
  $workflow_pool_obj_html .=  $specimen_pid.'<br>';
  $workflow_pool_obj_html .=  $pid;
  $workflow_pool_obj_html .= '</div>';
  $workflow_pool_obj_html .= '</td>';
  $workflow_pool_obj_html .= '<td style="border: hidden;">';
  $workflow_pool_obj_html .= '<div name="workflow_addToQueue_'.$pid.'" id="workflow_addToQueue_'.$pid.'">';
  $workflow_pool_obj_html .= '<a href="javascript:addToQueue(\''.$pid.'\', \''.$specimen_pid.'\', \'\')" style="float:right; padding-right:5px">Queue</a>';
  $workflow_pool_obj_html .= '</div>';
  $workflow_pool_obj_html .= '</td>';
  $workflow_pool_obj_html .= '</tr>';
  $workflow_pool_obj_html .= '<tr colspan=2>';
  $workflow_pool_obj_html .= '<td colspan=2 style="border: hidden;">';
  $workflow_pool_obj_html .= '<div name="workflow_as_status_'.$pid.'" id="workflow_as_status_'.$pid.'">';
  if(strlen($as_status) > 0) {
    $workflow_pool_obj_html .= 'analyzed: '.$as_status;
  }
  $workflow_pool_obj_html .= '</div>';
  $workflow_pool_obj_html .= '</td>';
  $workflow_pool_obj_html .= '</tr>';
  $workflow_pool_obj_html .= '</table>';
  $workflow_pool_obj_html .= '</div>';
  $workflow_pool_obj_html .= '</span>';
  $workflow_pool_obj_html .= '<ul id="wq_workflow_'.$pid.'_ul">';
  if(strlen($wq_workflow_roi_li_html) > 0) {
    $workflow_pool_obj_html .= $wq_workflow_roi_li_html;
  }
  $workflow_pool_obj_html .= '</ul>';
  $workflow_pool_obj_html .= '</li>';
  return $workflow_pool_obj_html;
}

function generateWQ_WorkflowSpecimen($pid){
  $workflow_pool_obj_html = '<div name="wq_workflow_specimen" id="wq_workflow_'.$pid.'" style="border:solid 1px green;" pid="'.$pid.'">';
  $workflow_pool_obj_html .= '<table>';
  $workflow_pool_obj_html .= '<tr>';
  $workflow_pool_obj_html .= '<td style="border: hidden;">';
  $workflow_pool_obj_html .= '<div name="workflow_obj_'.$pid.'" id="workflow_obj_'.$pid.'">';
  $workflow_pool_obj_html .=  $pid;
  $workflow_pool_obj_html .= '</div>';
  $workflow_pool_obj_html .= '</td>';
  $workflow_pool_obj_html .= '<td style="border: hidden;">';
  $workflow_pool_obj_html .= '<div name="workflow_status_'.$pid.'" id="workflow_status_'.$pid.'">';
  $workflow_pool_obj_html .= '</div>';
  $workflow_pool_obj_html .= '</td>';
  $workflow_pool_obj_html .= '</tr>';
  $workflow_pool_obj_html .= '</table>';
  $workflow_pool_obj_html .= '</div>';
  return $workflow_pool_obj_html;
}

function generateWQ_QueueROI($pid, $specimen_pid, $image_pid, $tt_status, $pt_status){
  $queue_pool_obj_html = '<li>';
  $queue_pool_obj_html .= '<span>';
  $queue_pool_obj_html .= '<div name="wq_queue_roi" id="wq_queue_'.$pid.'" style="border:solid 1px green;" pid="'.$pid.'" image_pid="'.$image_pid.'" specimen_pid="'.$specimen_pid.'">';
  $queue_pool_obj_html .= '<table>';
  $queue_pool_obj_html .= '<tr colspan=2>';
  $queue_pool_obj_html .= '<td colspan=2 style="border: hidden;">';
  $queue_pool_obj_html .= '<div name="queue_obj'.$pid.'" id="queue_obj'.$pid.'" style="width:auto; text-align: center;">';
  $queue_pool_obj_html .=  $pid;
  $queue_pool_obj_html .= '</div>';
  $queue_pool_obj_html .= '</td>';
  $queue_pool_obj_html .= '</tr>';
  $queue_pool_obj_html .= '<tr>';
  $queue_pool_obj_html .= '<td style="border: hidden;">';
  $queue_pool_obj_html .= '<div name="queue_tt_status_'.$pid.'" id="queue_tt_status_'.$pid.'">';
  if(strlen($tt_status) > 0) {
    $queue_pool_obj_html .= 'transcribed: '.$tt_status;
  }
  $queue_pool_obj_html .= '</div>';
  $queue_pool_obj_html .= '</td>';
  $queue_pool_obj_html .= '<td style="border: hidden;">';
  $queue_pool_obj_html .= '<div name="queue_roi_extract_status" id="queue_'.$pid.'_extract_status">';
  $queue_pool_obj_html .= '<a class="overlay" roi_pid="'.$pid.'" href="#" style="float:right; padding-right:5px">Transcribe</a>';
  $queue_pool_obj_html .= '</div>';
  $queue_pool_obj_html .= '</td>';
  $queue_pool_obj_html .= '</tr>';
  $queue_pool_obj_html .= '<tr>';
  $queue_pool_obj_html .= '<td style="border: hidden;">';
  $queue_pool_obj_html .= '<div name="queue_pt_status_'.$pid.'" id="queue_pt_status_'.$pid.'">';
  if(strlen($pt_status) > 0) {
    $queue_pool_obj_html .= 'parsed: '.$pt_status;
  }
  $queue_pool_obj_html .= '</div>';
  $queue_pool_obj_html .= '</td>';
  $queue_pool_obj_html .= '<td style="border: hidden;">';
  $queue_pool_obj_html .= '<div name="queue_roi_parse_status" id="queue_'.$pid.'_parse_status">';
  $queue_pool_obj_html .= '<a class="parse_overlay" roi_pid="'.$pid.'" href="#" style="float:right; padding-right:5px">Parse</a>';
  $queue_pool_obj_html .= '</div>';
  $queue_pool_obj_html .= '</td>';
  $queue_pool_obj_html .= '</tr>';
  $queue_pool_obj_html .= '</table>';
  $queue_pool_obj_html .= '</div>';
  $queue_pool_obj_html .= '</span>';
  $queue_pool_obj_html .= '</li>';
  return $queue_pool_obj_html;
}

function generateWQ_QueueImage($pid, $specimen_pid, $as_status, $wq_queue_roi_li_html = '',  $inQueue = true){
  $queue_pool_obj_html = '<li>';
  $queue_pool_obj_html .= '<span>';
  $queue_pool_obj_html .= '<div name="wq_queue_image" id="wq_queue_'.$pid.'" style="padding:0px; border:solid 1px green;" pid="'.$pid.'" specimen_pid="'.$specimen_pid.'">';
  $queue_pool_obj_html .= '<table>';
  $queue_pool_obj_html .= '<tr colspan=2>';
  $queue_pool_obj_html .= '<td colspan=2 style="border: hidden;">';
  $queue_pool_obj_html .= '<div name="queue_obj'.$pid.'" id="queue_obj'.$pid.'" style="width:auto; text-align: center;">';
  $queue_pool_obj_html .=  $specimen_pid.'<br>';
  $queue_pool_obj_html .=  $pid;
  $queue_pool_obj_html .= '</div>';
  $queue_pool_obj_html .= '</td>';
  $queue_pool_obj_html .= '</tr>';
  $queue_pool_obj_html .= '<tr colspan=2>';
  $queue_pool_obj_html .= '<td colspan=2 style="border: hidden;">';
  $queue_pool_obj_html .= generateWQ_QueueImage_QueueStatus_Div($pid, $specimen_pid, $as_status, $inQueue);
  $queue_pool_obj_html .= '</td>';
  $queue_pool_obj_html .= '</tr>';
  $queue_pool_obj_html .= '</table>';
  $queue_pool_obj_html .= '</div>';
  $queue_pool_obj_html .= '</span>';
  $queue_pool_obj_html .= '<ul id="wq_queue_'.$pid.'_ul">';
  if(strlen($wq_queue_roi_li_html) > 0) {
    $queue_pool_obj_html .= $wq_queue_roi_li_html;
  }
  $queue_pool_obj_html .= '</ul>';
  $queue_pool_obj_html .= '</li>';
  return $queue_pool_obj_html;
}

function generateWQ_QueueImage_QueueStatus_Div($pid, $specimen_pid, $as_status, $inQueue = true) {
  $queued_display = 'block';
  $unqueued_display = 'none';
  if(!$inQueue) {
    $queued_display = 'none';
    $unqueued_display = 'block';
  }

  $queue_status_html = '<div name="queue_status" id="queue_status_'.$pid.'">';

//Begin queue_image_queued
  $queue_status_html .= '<div name="queue_image_queued" id="queue_'.$pid.'_queued" style="display:'.$queued_display.';">';
  $queue_status_html .= '<table>';
  $queue_status_html .= '<tr>';
  $queue_status_html .= '<td style="border: hidden;">';
  $queue_status_html .= '<div name="queue_as_status" id="queue_as_status_'.$pid.'">';
  if(strlen($as_status) > 0) {
    $queue_status_html .= 'analyzed: '.$as_status;
  }
  $queue_status_html .= '</div>';
  $queue_status_html .= '</td>';
  $queue_status_html .= '<td style="border: hidden;">';
  $queue_status_html .= '<div name="queue_image_analyze_status" id="queue_'.$pid.'_analyze_status">';
  $queue_status_html .= '<a class="image_load" image_pid="'.$pid.'" href="#" style="float:right; padding-right:5px">Analyze</a>';
  $queue_status_html .= '</div>';
  $queue_status_html .= '</td>';
  $queue_status_html .= '</tr>';
  $queue_status_html .= '</table>';
  $queue_status_html .= '</div>';
//End queue_image_queued

//Begin queue_image_unqueued
  $queue_status_html .= '<div name="queue_image_unqueued" id="queue_'.$pid.'_unqueued" style="display:'.$unqueued_display.';">';
  $queue_status_html .= '<table>';
  $queue_status_html .= '<tr>';
  $queue_status_html .= '<td style="border: hidden;">';
  $queue_status_html .= '<div name="queue_as_status_'.$pid.'" id="queue_as_status_'.$pid.'">';
  $queue_status_html .= 'Image not Queued:';
  $queue_status_html .= '</div>';
  $queue_status_html .= '</td>';
  $queue_status_html .= '<td style="border: hidden;">';
  $queue_status_html .= '<div name="queue_addToQueue_'.$pid.'" id="queue_addToQueue_'.$pid.'">';
  $queue_status_html .= '<a href="javascript:toggleWQ_Queue_Image_Status(\''.$pid.'\', \''.$specimen_pid.'\')" style="float:right; padding-right:5px">Queue</a>';
  $queue_status_html .= '</div>';
  $queue_status_html .= '</td>';
  $queue_status_html .= '</tr>';
  $queue_status_html .= '</table>';
  $queue_status_html .= '</div>';
//End queue_image_unqueued

  $queue_status_html .= '</div>';

  return $queue_status_html;
}

function generateImageQueueItem($image_pid){
  $queue_list_obj_html = '<div name="wq_queue_image" id="wq_queue_'.$image_pid.'" style="border:solid 1px green;" pid="'.$image_pid.'">';
  $queue_list_obj_html .= '<table>';
  $queue_list_obj_html .= '<tr>';
  $queue_list_obj_html .= '<td style="border: hidden;">';
  $queue_list_obj_html .= '<div name="queue_obj'.$image_pid.'" id="queue_obj'.$image_pid.'">';
  $queue_list_obj_html .=  $image_pid;
  $queue_list_obj_html .= '</div>';
  $queue_list_obj_html .= '</td>';
  $queue_list_obj_html .= '<td style="border: hidden;">';
  $queue_list_obj_html .= '<div name="queue_status'.$image_pid.'" id="queue_status'.$image_pid.'">';
  $queue_list_obj_html .= '<div name="queue_'.$image_pid.'_analyze_status" id="queue_'.$image_pid.'_analyze_status">';
  $queue_list_obj_html .= '<a class="image_load" image_pid="'.$image_pid.'" href="#" style="float:right; padding-right:5px">Analyze</a>';
  $queue_list_obj_html .= '</div>';
  $queue_list_obj_html .= '</div>';
  $queue_list_obj_html .= '</td>';
  $queue_list_obj_html .= '</tr>';
  $queue_list_obj_html .= '</table>';
  $queue_list_obj_html .= '</div>';
  return $queue_list_obj_html;
}

function generateROIQueueItem($roi_pid){
  $queue_list_obj_html = '<div name="wq_queue_roi" id="wq_queue_'.$roi_pid.'" style=" border:solid 1px green;" pid="'.$roi_pid.'">';
  $queue_list_obj_html .= '<table>';
  $queue_list_obj_html .= '<tr>';
  $queue_list_obj_html .= '<td style="border: hidden;">';
  $queue_list_obj_html .= '<div name="queue_obj'.$roi_pid.'" id="queue_obj'.$roi_pid.'">';
  $queue_list_obj_html .=  $roi_pid;
  $queue_list_obj_html .= '</div>';
  $queue_list_obj_html .= '</td>';
  $queue_list_obj_html .= '<td style="border: hidden;">';
  $queue_list_obj_html .= '<div name="queue_status'.$roi_pid.'" id="queue_status'.$roi_pid.'">';
  $queue_list_obj_html .= '<div name="queue_'.$roi_pid.'_extract_status" id="queue_'.$roi_pid.'_extract_status">';
  $queue_list_obj_html .= '<a class="overlay" roi_pid="'.$roi_pid.'" href="#" style="float:right; padding-right:5px">Transcribe</a>';
  $queue_list_obj_html .= '</div>';
  $queue_list_obj_html .= '<div name="queue_'.$roi_pid.'_parse_status" id="queue_'.$roi_pid.'_parse_status">';
  $queue_list_obj_html .= '<a class="parse_overlay" roi_pid="'.$roi_pid.'" href="#" style="float:right; padding-right:5px">Parse</a>';
  $queue_list_obj_html .= '</div>';
  $queue_list_obj_html .= '</div>';
  $queue_list_obj_html .= '</td>';
  $queue_list_obj_html .= '</tr>';
  $queue_list_obj_html .= '</table>';
  $queue_list_obj_html .= '</div>';
  return $queue_list_obj_html;
}
?>
