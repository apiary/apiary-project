<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
$rel_path = drupal_get_path('module','apiary_project');
session_start();
module_load_include('php', 'apiary_project', 'workflow/include/roiHandler');
include_once($rel_path.'/fedora_commons/class.AP_Specimen.php');
include_once($rel_path.'/fedora_commons/class.AP_Image.php');
include_once($rel_path.'/fedora_commons/class.AP_ROI.php');
include_once($rel_path.'/fedora_commons/class.FedoraObject.php');
include_once($rel_path.'/adore-djatoka/functions_djatoka.php');
include_once($rel_path.'/workflow/include/functions.php');
include_once($rel_path.'/workflow/include/getLevel1Parsing.php');
$server_base = variable_get('apiary_research_base_url', 'http://localhost');
$fedora_base_url = variable_get('fedora_base_url', 'http://localhost:8080/fedora');
include_once($rel_path.'/workflow/include/class.Workflow.php');
include_once($rel_path.'/workflow/include/class.Object_Pool.php');
include_once($rel_path.'/workflow/include/class.Workflow_Users.php');
include_once($rel_path.'/workflow/include/class.Workflow_Permission.php');
include_once($rel_path.'/workflow/include/class.Workflow_Sessions.php');
include_once($rel_path.'/workflow/include/class.Errorlog.php');
include_once($rel_path.'/workflow/include/search.php');
include_once("parse.functions.php");
include_once("transcribe.functions.php");
include_once("analyze.functions.php");
include_once($rel_path.'/fedora_commons/config_fedora.inc');

function define_workspace($workflow_id){
	global $user;
	if($workflow_id == "0" || empty($workflow_id)){
		return drupal_access_denied();
	}
	if(Workflow_Users::doesWorkflowHaveUserName($workflow_id, $user->name) && Workflow_Permission::doesWorkflowHavePermission($workflow_id, "canAnalyzeSpecimen")){
		$apiary_session_base = $user->name.'_'.$workflow_id.'_';
		if(empty($_SESSION['apiary_session_id'])) {
			$_SESSION['apiary_session_id'] = $apiary_session_base.date("Ymdhis");
		}
		echo get_workspace_mark_up($workflow_id);
	}
	else{
		echo "It seems you do not have permission to access this workflow. Please contact administrator for further instructions.";
	}
}

function get_workspace_mark_up($workflow_id){
	$content = file_get_contents(drupal_get_path('module', 'apiary_project') . '/mockup/index.php');
	return $content;
}

function send_response($requestType, $param1, $param2){
	if(!empty($_SESSION['apiary_session_id'])) {
	  $workflow_id = getWorkflowIdFromSessionId($_SESSION['apiary_session_id']);
	  call_user_func($requestType, $param1, $param2, $workflow_id);
	}
	else{
	  echo "Bad Session";
	}
}

function create_session($workflow_id) {
  global $user;
  $create_new_session = false;
  $_SESSION['workflow_id'] = $workflow_id;
  if(Workflow_Users::doesWorkflowHaveUserName($workflow_id, $user->name)){
    $apiary_session_base = $user->name.'_'.$workflow_id.'_';
    if(strpos($_SESSION['apiary_session_id'], $apiary_session_base) === false) {
      $create_new_session = true;
    }
    else {
      if(!Workflow_Sessions::session_id_exists($_SESSION['apiary_session_id'])) {
        $create_new_session = true;
      }
      else {
        if(!Workflow_Sessions::renew_session($_SESSION['apiary_session_id'])) {
          $create_new_session = true;
        }
      }
    }
    if($create_new_session) {
      if(!empty($_SESSION['apiary_session_id'])) {
        if(Workflow_Sessions::session_id_exists($_SESSION['apiary_session_id'])) {
	      Workflow_Sessions::delete($_SESSION['apiary_session_id']);
	    }
      }
      $_SESSION['apiary_session_id'] = $apiary_session_base.date("YmdHis");
      Workflow_Sessions::create($_SESSION['apiary_session_id']);
    }
    echo $_SESSION['apiary_session_id'];
  }
  else {
    echo "false";
  }
}

function clear_session() {
  if(emptyQueue()) {
    $_SESSION['apiary_session_id'] = '';
    echo "success";
  }
  else {
    echo "false";
  }
}

function delete_session() {
  if(Workflow_Sessions::delete($_SESSION['apiary_session_id']) > 0) {
    $_SESSION['apiary_session_id'] = '';
    echo "success";
  }
  else {
    echo "failed";
  }
}

function empty_session($session_id) {
  $_SESSION['apiary_cleared_session_id'] = $session_id;
  if(empty_queue($session_id)) {
    echo "success";
  }
  else {
    echo "false";
  }
  $_SESSION['apiary_cleared_session_id'] = '';
}

function getWorkflowIdFromSessionId($apiary_session_id){
//sessionId looks like user_workflowId_timestamp
//user might have _ so work from the end
  $last_ = strripos($apiary_session_id, '_');
  if (last_ > -1) {
    $bkwd_start = $last_ - strlen($apiary_session_id) - 1;
    $second_last_ = strrpos($apiary_session_id, '_', $bkwd_start);
    $id_length = $last_ - $second_last_ - 1;
    $workflow_id = substr($apiary_session_id, $second_last_+1, $id_length);
    return $workflow_id;
  }
  else {
    return "";
  }
}

function send_request($function, $workflow_id, $param1, $param2){
    //echo "function = ".$function." workflow_id = ".$workflow_id." param1 = ".$param1." param2 = ".$param2;
	if($param2 != "0") {
	  call_user_func($function, $workflow_id, $param1, $param2);
	}
	else if($param1 != "0") {
	  call_user_func($function, $workflow_id, $param1);
	}
	else if($function == "clear_session") {
	  call_user_func($function);
	}
	else {
	  call_user_func($function, $workflow_id);
	}
}

function get_workflow_items_via_solr($workflow_id, $entries_per_page, $page = 0) {
  $workflow = new Workflow();//by not sending the workflow_id here we avoid a lot of uneccessary pre-processing
  $workflow->workflow_id = $workflow_id;
  $workflow->loadDBWorkflow();
  $workflow_items = '';
  $totalEntries = 0;
  $object_pool = new Object_Pool($workflow->object_pool_id);
  $solr_q = create_item_browser_solr_q($object_pool->object_pool_pids);
  $solr_fl = 'fl=id';
  $solr_fl .= '+parent_id';
  $solr_fl .= '+status_analyzedStatus';
  $solr_fl .= '+imageMetadata_h';
  $solr_fl .= '+imageMetadata_w';
  $solr_fl .= '+imageMetadata_URL';
  $solr_fl .= '+imageMetadata_rft_id';
  $solr_fl .= '+status_locked';
  $solr_fl .= '+locked_time';
  $solr_fl .= '+status_locked_by';
  $solr_fl .= '+status_locked_session';
  $solr_op = '';
  $solr_rows = 'rows=10000';
  $solr_sxml = solr_query_xml($solr_q, $solr_fl, $solr_op, $solr_rows);
  if($solr_sxml != false) {
    $success = "true";
    list($workflow_items, $item_browser_items_order_array) = generate_workflow_items_via_solr($solr_sxml);
    $totalEntries = sizeOf($item_browser_items_order_array);
  }
  $returnJSON['workflow_items_successfully_created'] = $success;
  $returnJSON['workflow_items'] = $workflow_items;
  $returnJSON['workflow_item_count'] = $totalEntries;
  $returnJSON['item_browser'] = create_item_browser_bare($entries_per_page, $totalEntries);
  $returnJSON['item_browser_items_order_array'] = implode(",", $item_browser_items_order_array);
  echo json_encode($returnJSON);
}

function generate_workflow_items_via_solr($solr_sxml) {
  $workflow_items = '';
  $item_browser_items_order_array = array();
  if($solr_sxml != false) {
    foreach($solr_sxml->result[0]->doc as $doc) {
      $image_pid = '';
      $roi_count = '';
      $specimen_pid = '';
      $analyzedStatus = '';
      $h = '';
      $w = '';
      $url = '';
      $rft_id = '';
      $locked = '';
      $locked_time = '';
      $locked_by = '';
      $locked_session = '';
      $thumbnail_url = '';
      $workflow_status = '';
      foreach($doc->children() as $sxml_node) {
        if($sxml_node->attributes()->name == 'id') {
          $image_pid = (string)$sxml_node;
          array_push($item_browser_items_order_array, str_replace(':', '__', $image_pid));
          $roi_count = get_roi_count_via_solr($image_pid);
        }
        if($sxml_node->attributes()->name == 'parent_id') {
          $specimen_pid = (string)$sxml_node;
        }
        if($sxml_node->attributes()->name == 'status_analyzedStatus') {
          $analyzedStatus = (string)$sxml_node;
        }
        if($sxml_node->attributes()->name == 'imageMetadata_h') {
          $h = (string)$sxml_node;
        }
        if($sxml_node->attributes()->name == 'imageMetadata_w') {
          $w = (string)$sxml_node;
        }
        if($sxml_node->attributes()->name == 'imageMetadata_URL') {
          $url = (string)$sxml_node;
        }
        if($sxml_node->attributes()->name == 'imageMetadata_rft_id') {
          $rft_id = (string)$sxml_node;
        }
        if($sxml_node->attributes()->name == 'status_locked') {
          $locked = (string)$sxml_node;
        }
        if($sxml_node->attributes()->name == 'locked_time') {
          $locked_time = (string)$sxml_node;
        }
        if($sxml_node->attributes()->name == 'status_locked_by') {
          $locked_by = (string)$sxml_node;
        }
        if($sxml_node->attributes()->name == 'status_locked_session') {
          $locked_session = (string)$sxml_node;
        }
      }
      $thumbnail_url = djatoka_url_by_height_via_solr($image_pid, $rft_id, $h, $w, $url, "68");
      $workflow_status = workflow_status($locked, $locked_time, $locked_session);
      $workflow_items .= create_item_browser_page_image_item_via_solr($image_pid, $specimen_pid, $analyzedStatus, $roi_count, $thumbnail_url, $workflow_status);
    }
  }
  natsort($item_browser_items_order_array);
  return array($workflow_items, $item_browser_items_order_array);
}

function djatoka_url_by_height_via_solr($image_pid, $rft_id, $h, $w, $url, $height) {
	$djatoka_display_url = '';
	if($rft_id != '') {
	  $djatoka_display_url = getDjatokaURL($rft_id, 'getRegion', '3', '0', '0', $h, $w, '0', $height);
	}
	else {
	  $djatoka_display_url = getDjatokaURL($imageMetadata_record['URL'], 'getRegion', '3', '0', '0', $h, $w, '0', $height);
	}
	return $djatoka_display_url;
}

function get_parent_pid_via_solr($pid) {
  $solr_q = 'q=id:("'.$pid.'")';
  $solr_fl = 'fl=parent_id';
  $solr_op = '';
  $solr_rows = '';
  $solr_sxml = solr_query_xml($solr_q, $solr_fl, $solr_op, $solr_rows);
  //echo "parent_pid = ". (string)$solr_sxml->result[0]->doc[0]->str[0];
  return (string)$solr_sxml->result[0]->doc[0]->str[0];
}

function get_roi_list_via_solr($image_pid) {
  $roi_list = array();
  $solr_q = 'q=parent_id:("'.$image_pid.'")';
  $solr_fl = 'fl=id';
  $solr_op = '';
  $solr_rows = 'rows=10000';
  $solr_sxml = solr_query_xml($solr_q, $solr_fl, $solr_op, $solr_rows);
  foreach($solr_sxml->result[0]->doc as $doc) {
    foreach($doc->children() as $sxml_node) {
      if($sxml_node->attributes()->name == 'id') {
        array_push($roi_list, (string)$sxml_node);
      }
    }
  }
  return $roi_list;
}

function get_roi_count_via_solr($image_pid) {
  $solr_q = 'q=parent_id:("'.$image_pid.'")';
  $solr_fl = 'fl=id';
  $solr_op = '';
  $solr_rows = 'rows=10000';
  $solr_sxml = solr_query_xml($solr_q, $solr_fl, $solr_op, $solr_rows);
  return (string)$solr_sxml->result[0]['numFound'];
}

function create_item_browser_solr_q($object_pool) {
  $solr_q = "q=";
  $first = true;
  foreach($object_pool as $specimen_pid) {
    if(!$first) {
      $solr_q .= '+';
    }
    else {
      $first = false;
    }
    $solr_q .= 'parent_id:("'.$specimen_pid.'")';
  }
  return $solr_q;
}

function get_workflow_items($workflow_id, $entries_per_page, $page = 0) {
  //get_workflow_items_via_fedora($workflow_id, $entries_per_page, $page = 0);
  get_workflow_items_via_solr($workflow_id, $entries_per_page, $page = 0);
}

function get_workflow_items_via_fedora($workflow_id, $entries_per_page, $page = 0) {
  $workflow = new Workflow();//by not sending the workflow_id here we avoid a lot of uneccessary pre-processing
  $workflow->loadWorkflowDom($workflow_id);
  $workflow_dom = $workflow->workflow_dom;
  $totalEntries = 0; //do the count here instead of using arrays generated from the Workflow class
  $item_browser_items_order_array = array();
  $workflow_items = '';
  $success = "false";
  if($workflow_dom != null) {
    $specimen_elements = $workflow_dom->getElementsByTagName('specimen');
    foreach($specimen_elements as $specimen) {
      $specimen_pid = $specimen->getElementsByTagName('pid')->item(0)->nodeValue;
      $specimen_image_elements = $specimen->getElementsByTagName('image');
      foreach($specimen_image_elements as $image) {
        $totalEntries++;
        $image_pid = $image->getElementsByTagName('pid')->item(0)->nodeValue;
        array_push($item_browser_items_order_array, str_replace(':', '__', $image_pid));
        $analyzedStatus = $image->getElementsByTagName('analyzedStatus')->item(0)->nodeValue;
        $workflow_status = $image->getElementsByTagName('workflow_status')->item(0)->nodeValue;
        $workflow_items .= create_item_browser_page_image_item($image_pid, $specimen_pid, $analyzedStatus, $workflow_status);
      }
    }
    $success = "true";
  }
  //$workflow_items .= '</div>';
  $returnJSON['workflow_items_successfully_created'] = $success;
  $returnJSON['workflow_item_count'] = $totalEntries;
  $returnJSON['workflow_items'] = $workflow_items;
  //$returnJSON['add_items_list'] = create_add_items_list($entries_per_page, $totalEntries);
  $returnJSON['item_browser'] = create_item_browser_bare($entries_per_page, $totalEntries);
  $returnJSON['item_browser_items_order_array'] = implode(",", $item_browser_items_order_array);

  echo json_encode($returnJSON);
}

function create_bare_item_browser($entries_per_page, $totalEntries) {
  $returnJSON['bare_item_browser_successfully_created'] = "true";
  $returnJSON['item_browser'] = create_item_browser_bare($entries_per_page, $totalEntries);
  echo json_encode($returnJSON);
}

function create_item_browser_bare($entries_per_page, $totalEntries) {

  $item_browser_html .= '<div id="item_browser">'."\n";
  $item_browser_html .= '<div class="dialog_box" id="add_items">'."\n";
  $item_browser_html .= '    <div class="dialog_box_title">'."\n";
  $item_browser_html .= '        <div class="tl"><div class="tr">'."\n";
  $item_browser_html .= '        <h3>Select items to add to your queue</h3>'."\n";
  $item_browser_html .= '        </div></div>'."\n";
  $item_browser_html .= '    </div><!-- dialog_box_title -->'."\n";
  $item_browser_html .= create_add_items_list($entries_per_page, $totalEntries);
  $item_browser_html .= '</div><!-- add_items -->'."\n";
  $item_browser_html .= '</div><!-- item_browser -->'."\n";
  return $item_browser_html;
}

function create_add_items_list($entries_per_page, $totalEntries){
  $eppIsall = false;
  if($entries_per_page==0 || strtolower($entries_per_page)=='all') {
    $entries_per_page = $totalEntries;
    $eppIsall = true;
  }
  $maxpage = get_maxpage($totalEntries, $entries_per_page);
  $item_browser_html .= '    <div class="dialog_box_content" id="add_items_list" >'."\n";
  for($p=1; $p <= $maxpage; $p++) {
    $item_browser_html .= start_item_borwser_page($p);
    $item_browser_html .= start_item_browser_page_leftpanel();
    $item_browser_html .= create_item_browser_clearfix();
    $item_browser_html .= end_item_browser_page_leftpanel();
    $item_browser_html .= start_item_browser_page_rightpanel();
    $item_browser_html .= end_item_browser_page_rightpanel();
    $item_browser_html .= end_item_borwser_page($p);
  }

  $item_browser_html .= create_item_browser_clearfix();
  $item_browser_html .= create_item_browser_pagination($maxpage, "1", $entries_per_page, $eppIsall);
  $item_browser_html .= '    </div><!-- add_items_list -->'."\n";
  return $item_browser_html;
}

function item_browser_pagination($maxpage, $page, $epp) {
  $returnJSON['items_browser_pagination'] = create_item_browser_pagination($maxpage, $page, $epp);
  echo json_encode($returnJSON);
}

function item_browser_bare() {
  $item_browser_html .= '<div class="dialog_box" id="add_items">'."\n";
  $item_browser_html .= '    <div class="dialog_box_title">'."\n";
  $item_browser_html .= '        <div class="tl"><div class="tr">'."\n";
  $item_browser_html .= '        <h3>Select items to add to your queue</h3>'."\n";
  $item_browser_html .= '        </div></div>'."\n";
  $item_browser_html .= '    </div><!-- dialog_box_title -->'."\n";
  $item_browser_html .= '    <div class="dialog_box_content" id="add_items_list" >'."\n";
  $item_browser_html .= '    </div><!-- add_items_list -->'."\n";
  $item_browser_html .= '</div><!-- add_items -->'."\n";
  echo $item_browser_html;
}

function item_browser($workflow_id, $entries_per_page, $page = 0) {
  if(!isset($page)){
    $page = 0;
  }
  //echo file_get_contents('http://apiaryubuntu904.apiaryproject.org/drupal/modules/apiary_project/apiary_layout/add_items.html');
  $workflow = new Workflow($workflow_id, true);
  $workflow_dom = $workflow->workflow_dom;
  $image_pids = $workflow->image_pids;
  $totalEntries = sizeOf($image_pids);
  if($entries_per_page==0 || strtolower($entries_per_page)=='all') {
    $entries_per_page = $totalEntries;
  }
  $maxpage = get_maxpage($totalEntries, $entries_per_page);

  //echo 'entries_per_page = '.$entries_per_page.'totalEntries = '.$totalEntries.'maxpage = '.$maxpage."\n";

  $item_browser_html = create_item_browser($workflow_dom, $entries_per_page, $maxpage, $page);
  echo $item_browser_html;
}

function get_maxpage($totalEntries, $entries_per_page){
  return ceil($totalEntries/$entries_per_page);
}

function item_browser_page($workflow_id, $entries_per_page, $page) {
  //echo file_get_contents('http://apiaryubuntu904.apiaryproject.org/drupal/modules/apiary_project/apiary_layout/add_items.html');
  $workflow = new Workflow($workflow_id, true);
  $workflow_dom = $workflow->workflow_dom;
  $item_browser_page_html = create_item_browser_page($workflow_dom, $entries_per_page, $page);
  echo $item_browser_page_html;
}

function create_item_browser($workflow_dom, $epp, $maxpage, $page) {
  $item_browser_html = '';
  if($workflow_dom != null) {
        $item_browser_html .= '<div class="dialog_box" id="add_items">'."\n";
	    $item_browser_html .= '    <div class="dialog_box_title">'."\n";
	    $item_browser_html .= '        <div class="tl"><div class="tr">'."\n";
	    $item_browser_html .= '        <h3>Select items to add to your queue</h3>'."\n";
	    $item_browser_html .= '        </div></div>'."\n";
	    $item_browser_html .= '    </div><!-- dialog_box_title -->'."\n";
        $item_browser_html .= '    <div class="dialog_box_content" id="add_items_list" >'."\n";
        if($page == 0) { //get all pages
          for($p=1; $p <= $maxpage; $p++) {
            $item_browser_html .= create_item_browser_page($workflow_dom, $epp, $p);
          }
        }
        else {
            $item_browser_html .= create_item_browser_page($workflow_dom, $epp, $page);
        }
        $item_browser_html .= create_item_browser_clearfix();
        $item_browser_html .= create_item_browser_pagination($maxpage, $page, $epp);
        $item_browser_html .= '        </div><!-- add_items_list -->'."\n";
        $item_browser_html .= '    </div><!-- add_items -->'."\n";
  }
  return $item_browser_html;
}

function create_item_browser_pagination($maxpage, $page, $epp, $eppIsAll = false) {
  $max_paginations = 5;
  if($page ==0) {
    $page = 1;
  }
  $item_browser_pagination_html = '';
  $item_browser_pagination_html .= '            <div class="pagination" id="add_items_pagination">'."\n";
  $item_browser_pagination_html .= '            <div style="height:4px"></div>'."\n";
  $item_browser_pagination_html .= '            <ul>'."\n";
  $item_browser_pagination_html .= '            <li><a href=\'#\' id="add_items_first_control"  class="first inactive" onclick="select_first_page(); return false;">first</a></li>'."\n";
  $item_browser_pagination_html .= '            <li><a href=\'#\' id="add_items_prev_control"  class="previous inactive" onclick="select_prev_page(); return false;">previous</a></li>'."\n";
  for($i = 1; $i <= $maxpage; $i++) {
    if($i == $page) {
      $item_browser_pagination_html .= '            <li><a href=\'#\' id="add_items_page_'.$i.'_control" class="current" onclick="select_page(\'#add_items_page_'.$i.'\'); return false;">'.$i.'</a></li>'."\n";
    }
    else {
      $item_browser_pagination_html .= '            <li><a href=\'#\' id="add_items_page_'.$i.'_control" onclick="select_page(\'#add_items_page_'.$i.'\'); return false;">'.$i.'</a></li>'."\n";
    }
  }
  $item_browser_pagination_html .= '            <li><a href=\'#\' id="add_items_next_control"  class="next" onclick="select_next_page(); return false;">next</a></li>'."\n";
  $item_browser_pagination_html .= '            <li><a href=\'#\' id="add_items_last_control"  class="last" onclick="select_last_page(); return false;">last</a></li>'."\n";
  $item_browser_pagination_html .= '            <li>Display </li>'."\n";
  $item_browser_pagination_html .= '            <li>'."\n";
  $item_browser_pagination_html .= '            	<select id="epp" name="entriesperPage" onchange="reload_item_browser()">'."\n";
  $item_browser_pagination_html .= create_item_browser_epp_option("6", $epp);
  $item_browser_pagination_html .= create_item_browser_epp_option("10", $epp);
  $item_browser_pagination_html .= create_item_browser_epp_option("16", $epp);
  $item_browser_pagination_html .= create_item_browser_epp_option("20", $epp);
  $item_browser_pagination_html .= create_item_browser_epp_option("All", $epp, $eppIsAll);
  $item_browser_pagination_html .= '            	</select>'."\n";
  $item_browser_pagination_html .= '            </li>'."\n";
  $item_browser_pagination_html .= '            <li> items per page</li>'."\n";
  $item_browser_pagination_html .= '            </ul>'."\n";
  $item_browser_pagination_html .= '            <ul>'."\n";
  $item_browser_pagination_html .= '            <li><a href="#" class="action" onclick="add_items_to_queue();">Add to queue</a></li>'."\n";
  $item_browser_pagination_html .= '            <li><a href="#" class="action" id="cancel" onclick="add_items_cancel();">Cancel</a></li>'."\n";
  $item_browser_pagination_html .= '            </ul>'."\n";

  $item_browser_pagination_html .= '            </div><!-- /pagination -->'."\n";
  return $item_browser_pagination_html;
}

function create_item_browser_epp_option($epp_option, $current_epp, $isAll = false) {
  $item_browser_epp_option_html .= '            		<option value="'.$epp_option.'"';
  if($epp_option == $current_epp || $isAll) {
    $item_browser_epp_option_html .= ' selected=selected ';
  }
  $item_browser_epp_option_html .= '>'.$epp_option.'</option>'."\n";
  return $item_browser_epp_option_html;
}

function create_item_browser_page($workflow_dom, $epp, $page) {
  $image_display_width = '450';
  $leftpanel_item_start = ($page-1)*$epp+1;
  $rightpanel_item_start = $leftpanel_item_start  + (round($epp/2));
  $item_count = 0;
  //echo "leftpanel_item_start = ".$leftpanel_item_start." rightpanel_item_start = ".$rightpanel_item_start." epp = ".$epp." page = ".$page.'<br>'."\n";

  if($workflow_dom != null) {
    $item_browser_page_html = '';
    $specimen_elements = $workflow_dom->getElementsByTagName('specimen');
    foreach($specimen_elements as $specimen) {
      $specimen_pid = $specimen->getElementsByTagName('pid')->item(0)->nodeValue;
      $specimen_image_elements = $specimen->getElementsByTagName('image');
      foreach($specimen_image_elements as $image) {
        $item_count++;
        if($item_count == $leftpanel_item_start) {
          $item_browser_page_html .= start_item_borwser_page($page);
          $item_browser_page_html .= start_item_browser_page_leftpanel();
        }
        else if($item_count == $rightpanel_item_start) {
          $item_browser_page_html .= create_item_browser_clearfix();
          $item_browser_page_html .= end_item_browser_page_leftpanel();
          $item_browser_page_html .= start_item_browser_page_rightpanel();
        }
	    if($item_count >= $leftpanel_item_start && $item_count <= ($page*$epp)){
          $imageMetadata_record = AP_Image::getimageMetadata_record($image_pid);
          $rft_id = $imageMetadata_record['rft_id'];
          if($rft_id != '') {
            $djatoka_display_url = getDjatokaURL($rft_id, 'getRegion', '3', '0', '0', $imageMetadata_record['h'], $imageMetadata_record['w'], $image_display_width, '0');
            $thumbnail_url = getDjatokaURL($rft_id, 'getRegion', '3', '0', '0', $imageMetadata_record['h'], $imageMetadata_record['w'], '250', '0');
          }
          else {
            $djatoka_display_url = getDjatokaURL($imageMetadata_record['URL'], 'getRegion', '3', '0', '0', $imageMetadata_record['h'], $imageMetadata_record['w'], $image_display_width, '0');
            $thumbnail_url = getDjatokaURL($imageMetadata_record['URL'], 'getRegion', '3', '0', '0', $imageMetadata_record['h'], $imageMetadata_record['w'], $thumb_width, '0');
          }
          $image_pid = $image->getElementsByTagName('pid')->item(0)->nodeValue;
          $analyzedStatus = $image->getElementsByTagName('analyzedStatus')->item(0)->nodeValue;
          $workflow_status = $image->getElementsByTagName('workflow_status')->item(0)->nodeValue;
          $item_browser_page_html .= create_item_browser_page_image_item($image_pid, $specimen_pid, $analyzedStatus, $workflow_status);

          //$specimen_image_roi_elements = $image->getElementsByTagName('roi');
          //foreach($specimen_image_roi_elements as $roi) {
          //  $roi_pid = $roi->getElementsByTagName('pid')->item(0)->nodeValue;
          //  $transcribedStatus = $image->getElementsByTagName('transcribedStatus')->item(0)->nodeValue;
          //  $parsedL1Status = $image->getElementsByTagName('parsedL1Status')->item(0)->nodeValue;
          //  $workflow_status = $image->getElementsByTagName('workflow_status')->item(0)->nodeValue;
          //  $item_browser_page_html .= create_item_browser_page_roi_item($roi_pid, $specimen_pid, $image_pid, $transcribedStatus, $parsedL1Status, $workflow_status);
          //}
        }
      }
    }
    if($item_count >= $rightpanel_item_start) { //means there is an item on the right and the left has already been closed
    $item_browser_page_html .= end_item_browser_page_rightpanel();
    $item_browser_page_html .= end_item_borwser_page($page);
    }
    else {
      //still create and close the right panel to not break any code
      //gotta include the clearfix and close the left first
      $item_browser_page_html .= create_item_browser_clearfix();
      $item_browser_page_html .= end_item_browser_page_leftpanel();
      $item_browser_page_html .= start_item_browser_page_rightpanel();
      $item_browser_page_html .= end_item_browser_page_rightpanel();
      $item_browser_page_html .= end_item_borwser_page($page);
    }
  }
  return $item_browser_page_html;
}

function start_item_borwser_page($page) {
  $start_item_borwser_page_html = '            <div class="add_items_page" id="add_items_page_'.$page.'" style="display:none;">'."\n";
  return $start_item_borwser_page_html;
}

function end_item_borwser_page($page) {
  $end_item_borwser_page_html .= '            </div><!-- #add_items_page_'.$page.' -->'."\n";
  return $end_item_borwser_page_html;
}

function start_item_browser_page_leftpanel() {
  $start_item_browser_page_leftpanel_html .= '            <div class="leftpanel48">'."\n";
  return $start_item_browser_page_leftpanel_html;
}

function create_item_browser_clearfix() {
  $create_item_browser_clearfix_html = '                <div class="clearfix"></div>'."\n";
  return $create_item_browser_clearfix_html;
}

function end_item_browser_page_leftpanel() {
  $end_item_browser_page_leftpanel_html .= '            </div><!-- .leftpanel48 -->'."\n";
  return $end_item_browser_page_leftpanel_html;
}

function start_item_browser_page_rightpanel() {
  $start_item_browser_page_rightpanel_html = '            <div class="rightpanel48">'."\n";
  return $start_item_browser_page_rightpanel_html;
}

function end_item_browser_page_rightpanel() {
  $end_item_browser_page_rightpanel_html = '            </div><!-- .rightpanel48 -->'."\n";
  return $end_item_browser_page_rightpanel_html;
}

function create_item_browser_page_image_item_via_solr($image_pid, $specimen_pid, $analyzedStatus, $roi_count, $thumbnail_url, $workflow_status) {
  $selected_class = 'unselected';
  $roundedcornr_class = 'roundedcornr_ltgray';
  if($workflow_status == "queued") {
    $selected_class = 'selected';
    $roundedcornr_class = 'roundedcornr_dgray';
  }
  //workflow_status is a used class
  $item_browser_page_item_html = '                <div class="'.$roundedcornr_class.' pool_item '.$selected_class.' '.$workflow_status.'" id="'.str_replace(':', '__', $image_pid).'"  specimen_pid="'.str_replace(':', '__', $specimen_pid).'" onclick="toggle_selected(\'#'.str_replace(':', '__', $image_pid).'\');">'."\n";
  $item_browser_page_item_html .= '                <div class="t"><div class="b"><div class="l"><div class="r"><div class="bl"><div class="br"><div class="tl"><div class="tr">'."\n";
  $item_browser_page_item_html .= '                    <div class="pool_item_checkbox"></div>'."\n";
  $item_browser_page_item_html .= '                    <div class="pool_item_area pool_item_image">'."\n";

  //$djatoka_image_url = get_djatoka_url_by_height($image_pid, "68");
  $djatoka_image_url = $thumbnail_url;

  $item_browser_page_item_html .= '                        <img src="'.variable_get('apiary_research_base_url', 'http://localhost').'/drupal/modules/apiary_project/workflow/assets/img/blank.gif" class="delayLoad" onmouseover="this.src=\''.$djatoka_image_url.'\'" height=68px />'."\n";
  $item_browser_page_item_html .= '                    </div><!-- pool_item_image -->'."\n";
  $item_browser_page_item_html .= '                    <div class="pool_item_area pool_item_content">'."\n";
  $item_browser_page_item_html .= '                        <strong>'.$specimen_pid.'</strong><br/>'."\n";

  //$roi_pids = AP_Image::getROIListForImage($image_pid);
  //$roi_count = sizeOf($roi_pids);

  $item_browser_page_item_html .= create_item_browser_image_item_detail($analyzedStatus, $workflow_status, $roi_count);
  $item_browser_page_item_html .= '                    </div><!-- .pool_item_content -->'."\n";
  $item_browser_page_item_html .= '                </div></div></div></div></div></div></div></div>'."\n";
  $item_browser_page_item_html .= '                </div><!-- .roundedcornr_ltgray -->'."\n";
  return $item_browser_page_item_html;
}

function create_item_browser_page_image_item($image_pid, $specimen_pid, $analyzedStatus, $workflow_status) {
  $selected_class = 'unselected';
  $roundedcornr_class = 'roundedcornr_ltgray';
  if($workflow_status == "queued") {
    $selected_class = 'selected';
    $roundedcornr_class = 'roundedcornr_dgray';
  }
  //workflow_status is a used class
  $item_browser_page_item_html = '                <div class="'.$roundedcornr_class.' pool_item '.$selected_class.' '.$workflow_status.'" id="'.str_replace(':', '__', $image_pid).'"  specimen_pid="'.str_replace(':', '__', $specimen_pid).'" onclick="toggle_selected(\'#'.str_replace(':', '__', $image_pid).'\');">'."\n";
  $item_browser_page_item_html .= '                <div class="t"><div class="b"><div class="l"><div class="r"><div class="bl"><div class="br"><div class="tl"><div class="tr">'."\n";
  $item_browser_page_item_html .= '                    <div class="pool_item_checkbox"></div>'."\n";
  $item_browser_page_item_html .= '                    <div class="pool_item_area pool_item_image">'."\n";

  $djatoka_image_url = get_djatoka_url_by_height($image_pid, "68");

  $item_browser_page_item_html .= '                        <img src="'.$djatoka_image_url.'" height=68px />'."\n";
  $item_browser_page_item_html .= '                    </div><!-- pool_item_image -->'."\n";
  $item_browser_page_item_html .= '                    <div class="pool_item_area pool_item_content">'."\n";
  $item_browser_page_item_html .= '                        <strong>'.$specimen_pid.'</strong><br/>'."\n";

  //$imageMetadata_record = AP_Image::getimageMetadata_record($image_pid);

  $roi_pids = AP_Image::getROIListForImage($image_pid);
  $roi_count = sizeOf($roi_pids);

  $item_browser_page_item_html .= create_item_browser_image_item_detail($analyzedStatus, $workflow_status, $roi_count);
  $item_browser_page_item_html .= '                    </div><!-- .pool_item_content -->'."\n";
  $item_browser_page_item_html .= '                </div></div></div></div></div></div></div></div>'."\n";
  $item_browser_page_item_html .= '                </div><!-- .roundedcornr_ltgray -->'."\n";
  return $item_browser_page_item_html;
}

function create_item_browser_image_item_detail($analyzedStatus, $workflow_status, $roi_count) {
  $item_browser_image_item_detail_html = '';
  if($analyzedStatus != '' && $analyzedStatus != null) {
    $item_browser_image_item_detail_html .= '                        analyzed status: '.$analyzedStatus."<br/>\n";
  }
  $item_browser_image_item_detail_html .= '                        ROIs: '.$roi_count."<br/>\n";
  $item_browser_image_item_detail_html .= '                        workflow status: '.$workflow_status."<br/>\n";
  return $item_browser_image_item_detail_html;
}

function queue_list($workflow_id) {
	//queue_list_fedora($workflow_id);
	create_queue_list_via_solr($workflow_id);
}

function queue_list_fedora($workflow_id) {
  $workflow = new Workflow($workflow_id, true);
  $workflow_dom = $workflow->workflow_dom;
  //$queued_image_pids = $workflow->queued_image_pids;
  //$queued_roi_pids = $workflow->queued_roi_pids;
  $queue_list_html = create_queue_list($workflow_dom,$workflow_id);
  echo $queue_list_html;
}

function create_queue_list($workflow_dom,$workflow_id=null) {
  $queue_list_html = '';
  if($workflow_dom != null) {
    $queue_list_html .= '<!-- queue_list content begin -->'."\n";
    $specimen_elements = $workflow_dom->getElementsByTagName('specimen');
    foreach($specimen_elements as $specimen) {
      $specimen_pid = $specimen->getElementsByTagName('pid')->item(0)->nodeValue;
      $specimen_image_elements = $specimen->getElementsByTagName('image');
      foreach($specimen_image_elements as $image) {
        $workflow_status = $image->getElementsByTagName('workflow_status')->item(0)->nodeValue;
        if($workflow_status == "queued") {
          $image_pid = $image->getElementsByTagName('pid')->item(0)->nodeValue;
          $analyzedStatus = $image->getElementsByTagName('analyzedStatus')->item(0)->nodeValue;
          $locked_by = $image->getElementsByTagName('locked_by')->item(0)->nodeValue;
          $workflow_status = $image->getElementsByTagName('workflow_status')->item(0)->nodeValue;
          $specimen_image_roi_elements = $image->getElementsByTagName('roi');
          $roi_count = $specimen_image_roi_elements->length;

          $queue_list_html .= create_queue_list_image($image_pid, $specimen_pid, $analyzedStatus, $roi_count, $locked_by, $workflow_status,$workflow_id);
          $queue_list_html .= '<div class="widget-content" id="content-'.str_replace(':', '__', $image_pid).'">'."\n";
          foreach($specimen_image_roi_elements as $roi) {
            $workflow_status = $roi->getElementsByTagName('workflow_status')->item(0)->nodeValue;
            $roi_pid = $roi->getElementsByTagName('pid')->item(0)->nodeValue;
            $transcribedStatus = $roi->getElementsByTagName('transcribedStatus')->item(0)->nodeValue;
            $parsedL1Status = $roi->getElementsByTagName('parsedL1Status')->item(0)->nodeValue;
            $locked_by = $roi->getElementsByTagName('locked_by')->item(0)->nodeValue;
            $queue_list_html .= create_queue_list_roi($roi_pid, $image_pid, $transcribedStatus, $parsedL1Status, $locked_by, $workflow_status,$workflow_id);
          }
          $queue_list_html .= '</div><!-- widget-content -->'."\n";
        }
      }
    }
    $queue_list_html .= '<!-- queue_list content end -->'."\n";
  }
  else {
    return "false";
  }
  return $queue_list_html;
}

function create_queue_list_via_solr($workflow_id=null) {
  $apiary_session = $_SESSION['apiary_session_id'];
  $now = date("YmdHis");
  $apiary_timeout = variable_get('apiary_object_timeout', '1800');
  $unexpired = $now - $apiary_timeout;
  $locked_time_min = sprintf("%.0f", $unexpired);
  $solr_q = 'q=status_locked_session:("'.$apiary_session.'")';
  $solr_q .= '+status_locked:("true")';
  $solr_q .= '+locked_time:['.$locked_time_min.'+*]';
  $solr_fl = 'fl=id';
  $solr_fl .= '+parent_id';
  $solr_fl .= '+status_analyzedStatus';
  $solr_fl .= '+status_transcribedStatus';
  $solr_fl .= '+status_transcribedStatusUpdatedBy';
  $solr_fl .= '+status_parsedL1Status';
  $solr_fl .= '+status_parsedL1StausUpdatedBy';
  $solr_fl .= '+status_parsedL2Status';
  $solr_fl .= '+status_parsedL3Status';
  $solr_fl .= '+status_qcStatus';
  $solr_fl .= '+status_qcStatusUpdatedBy';
  $solr_fl .= '+status_locked';
  $solr_fl .= '+locked_time';
  $solr_fl .= '+status_locked_by';
  $solr_fl .= '+status_locked_session';
  $solr_op = 'q.op=AND';
  $solr_rows = 'rows=10000';
  $solr_sxml = solr_query_xml($solr_q, $solr_fl, $solr_op, $solr_rows);
  if($solr_sxml != false) {
    $workflow_dom = create_workflow_dom($solr_sxml);
    //$workflow_dom->save('/tmp/solr_workflow_dom.xml');
    $queue_list_html = create_queue_list($workflow_dom,$workflow_id);
    echo $queue_list_html;
  }
  else {
    echo "false<br>\n";
  }
}

function get_parent_via_solr($pid) {
  $solr_q = 'q=id:("'.$pid.'")';
  $solr_fl = 'fl=parent_id';
  $solr_sxml = solr_query_xml($solr_q, $solr_fl);
  if($solr_sxml != false) {
    foreach($solr_sxml->result[0]->doc[0]->children() as $str) {
      return (string)$str;
    }
  }
  else {
    return false;
  }
}
  function create_workflow_dom($solr_sxml) {
    $workflow_dom = new DOMDocument('1.0', 'iso-8859-1');
    $workflow_root_element = $workflow_dom->createElement('workflow', '');//rootElement
    $workflow_dom->appendChild($workflow_root_element);
    foreach($solr_sxml->result[0]->doc as $doc) {
      $pid = '';
      $parent_pid = '';
      $analyzedStatus = '';
      $transcribedStatus = '';
      $transcribedStatusUpdatedBy = '';
      $parsedL1Status = '';
      $parsedL1StausUpdatedBy = '';
      $parsedL2Status = '';
      $parsedL3Status = '';
      $qcStatus = '';
      $qcStatusUpdatedBy = '';
      $locked = '';
      $locked_time = '';
      $locked_by = '';
      $locked_session = '';
      foreach($doc->children() as $sxml_node) {
        if($sxml_node->attributes()->name == 'id') {
          $pid = (string)$sxml_node;
        }
        if($sxml_node->attributes()->name == 'parent_id') {
          $parent_pid = (string)$sxml_node;
        }
        if($sxml_node->attributes()->name == 'status_analyzedStatus') {
          $analyzedStatus = (string)$sxml_node;
        }
        if($sxml_node->attributes()->name == 'status_transcribedStatus') {
          $transcribedStatus = (string)$sxml_node;
        }
        if($sxml_node->attributes()->name == 'status_transcribedStatusUpdatedBy') {
          $transcribedStatusUpdatedBy = (string)$sxml_node;
        }
        if($sxml_node->attributes()->name == 'status_parsedL1Status') {
          $parsedL1Status = (string)$sxml_node;
        }
        if($sxml_node->attributes()->name == 'status_parsedL1StausUpdatedBy') {
          $parsedL1StausUpdatedBy = (string)$sxml_node;
        }
        if($sxml_node->attributes()->name == 'status_parsedL2Status') {
          $parsedL2Status = (string)$sxml_node;
        }
        if($sxml_node->attributes()->name == 'status_parsedL3Status') {
          $parsedL3Status = (string)$sxml_node;
        }
        if($sxml_node->attributes()->name == 'status_qcStatus') {
          $qcStatus = (string)$sxml_node;
        }
        if($sxml_node->attributes()->name == 'status_qcStatusUpdatedBy') {
          $qcStatusUpdatedBy = (string)$sxml_node;
        }
        if($sxml_node->attributes()->name == 'status_locked') {
          $locked = (string)$sxml_node;
        }
        if($sxml_node->attributes()->name == 'locked_time') {
          $locked_time = (double)$sxml_node;
        }
        if($sxml_node->attributes()->name == 'status_locked_by') {
          $locked_by = (string)$sxml_node;
        }
        if($sxml_node->attributes()->name == 'status_locked_session') {
          $locked_session = (string)$sxml_node;
        }
      }
      if(strpos($pid, 'ap-image:') > -1) {
        $specimenElement = get_workflow_dom_specimen($workflow_dom, $workflow_root_element, $parent_pid);
        $image = array();
        $image['pid'] = $pid;
        $image['parent_pid'] = $parent_pid;
        $image['analyzedStatus'] = $analyzedStatus;
        $image['locked'] = $locked;
        $image['locked_time'] = $locked_time;
        $image['locked_by'] = $locked_by;
        $image['locked_session'] = $locked_session;
        $image['qcStatus'] = $qcStatus;
        $image['qcStatusUpdatedBy'] = $qcStatusUpdatedBy;
        $image['workflow_status'] = workflow_status($locked, $locked_time, $locked_session);
        $imageElement = create_workflow_dom_image($workflow_dom, $specimenElement, $image);
      }
      else if(strpos($pid, 'ap-roi:') > -1) {
        $imageElement = get_workflow_dom_image($workflow_dom, $workflow_root_element, $parent_pid);
        $roi = array();
        $roi['pid'] = $pid;
        $roi['parent_pid'] = $parent_pid;
        $roi['transcribedStatus'] = $transcribedStatus;
        $roi['transcribedStatusUpdatedBy'] = $transcribedStatusUpdatedBy;
        $roi['parsedL1Status'] = $parsedL1Status;
        $roi['parsedL1StausUpdatedBy'] = $parsedL1StausUpdatedBy;
        $roi['parsedL2Status'] = $parsedL2Status;
        $roi['parsedL3Status'] = $parsedL3Status;
        $roi['locked'] = $locked;
        $roi['locked_time'] = $locked_time;
        $roi['locked_by'] = $locked_by;
        $roi['locked_session'] = $locked_session;
        $roi['qcStatus'] = $qcStatus;
        $roi['qcStatusUpdatedBy'] = $qcStatusUpdatedBy;
        $roi['workflow_status'] = workflow_status($locked, $locked_time, $locked_session);
        $roiElement = create_workflow_dom_roi($workflow_dom, $imageElement, $roi);
      }
    }
    add_non_queued_items($workflow_dom);
    return $workflow_dom;
  }

function add_non_queued_items($workflow_dom) {
  $imageElements = $workflow_dom->getElementsByTagName('image');
  foreach($imageElements as $imageElement) {
    $image_pid = $imageElement->getElementsByTagName('pid')->item(0)->nodeValue;
    $solr_sxml = get_roi_solr_sxml($image_pid);
    foreach($solr_sxml->result[0]->doc as $doc) {
      $pid = '';
      $parent_pid = '';
      $analyzedStatus = '';
      $transcribedStatus = '';
      $transcribedStatusUpdatedBy = '';
      $parsedL1Status = '';
      $parsedL1StausUpdatedBy = '';
      $parsedL2Status = '';
      $parsedL3Status = '';
      $qcStatus = '';
      $qcStatusUpdatedBy = '';
      $locked = '';
      $locked_time = '';
      $locked_by = '';
      $locked_session = '';
      foreach($doc->children() as $sxml_node) {
        if($sxml_node->attributes()->name == 'id') {
          $pid = (string)$sxml_node;
        }
        if($sxml_node->attributes()->name == 'parent_id') {
          $parent_pid = (string)$sxml_node;
        }
        if($sxml_node->attributes()->name == 'status_analyzedStatus') {
          $analyzedStatus = (string)$sxml_node;
        }
        if($sxml_node->attributes()->name == 'status_transcribedStatus') {
          $transcribedStatus = (string)$sxml_node;
        }
        if($sxml_node->attributes()->name == 'status_transcribedStatusUpdatedBy') {
          $transcribedStatusUpdatedBy = (string)$sxml_node;
        }
        if($sxml_node->attributes()->name == 'status_parsedL1Status') {
          $parsedL1Status = (string)$sxml_node;
        }
        if($sxml_node->attributes()->name == 'status_parsedL1StausUpdatedBy') {
          $parsedL1StausUpdatedBy = (string)$sxml_node;
        }
        if($sxml_node->attributes()->name == 'status_parsedL2Status') {
          $parsedL2Status = (string)$sxml_node;
        }
        if($sxml_node->attributes()->name == 'status_parsedL3Status') {
          $parsedL3Status = (string)$sxml_node;
        }
        if($sxml_node->attributes()->name == 'status_qcStatus') {
          $qcStatus = (string)$sxml_node;
        }
        if($sxml_node->attributes()->name == 'status_qcStatusUpdatedBy') {
          $qcStatusUpdatedBy = (string)$sxml_node;
        }
        if($sxml_node->attributes()->name == 'status_locked') {
          $locked = (string)$sxml_node;
        }
        if($sxml_node->attributes()->name == 'locked_time') {
          $locked_time = (double)$sxml_node;
        }
        if($sxml_node->attributes()->name == 'status_locked_by') {
          $locked_by = (string)$sxml_node;
        }
        if($sxml_node->attributes()->name == 'status_locked_session') {
          $locked_session = (string)$sxml_node;
        }
      }
      if(strpos($workflow_dom->saveXML($imageElement), $pid."</pid>") === false) {
        $roi = array();
        $roi['pid'] = $pid;
        $roi['parent_pid'] = $parent_pid;
        $roi['transcribedStatus'] = $transcribedStatus;
        $roi['transcribedStatusUpdatedBy'] = $transcribedStatusUpdatedBy;
        $roi['parsedL1Status'] = $parsedL1Status;
        $roi['parsedL1StausUpdatedBy'] = $parsedL1StausUpdatedBy;
        $roi['parsedL2Status'] = $parsedL2Status;
        $roi['parsedL3Status'] = $parsedL3Status;
        $roi['locked'] = $locked;
        $roi['locked_time'] = $locked_time;
        $roi['locked_by'] = $locked_by;
        $roi['locked_session'] = $locked_session;
        $roi['qcStatus'] = $qcStatus;
        $roi['qcStatusUpdatedBy'] = $qcStatusUpdatedBy;
        $roi['workflow_status'] = workflow_status($locked, $locked_time, $locked_session);
        $roiElement = create_workflow_dom_roi($workflow_dom, $imageElement, $roi);
      }
    }
  }
}

function get_roi_solr_sxml($image_pid) {
  $solr_q = 'q=parent_id:("'.$image_pid.'")';
  $solr_fl = 'fl=id';
  $solr_fl .= '+parent_id';
  $solr_fl .= '+status_analyzedStatus';
  $solr_fl .= '+status_transcribedStatus';
  $solr_fl .= '+status_transcribedStatusUpdatedBy';
  $solr_fl .= '+status_parsedL1Status';
  $solr_fl .= '+status_parsedL1StausUpdatedBy';
  $solr_fl .= '+status_parsedL2Status';
  $solr_fl .= '+status_parsedL3Status';
  $solr_fl .= '+status_qcStatus';
  $solr_fl .= '+status_qcStatusUpdatedBy';
  $solr_fl .= '+status_locked';
  $solr_fl .= '+locked_time';
  $solr_fl .= '+status_locked_by';
  $solr_fl .= '+status_locked_session';
  $solr_op = 'q.op=AND';
  $solr_rows = 'rows=10000';
  $solr_sxml = solr_query_xml($solr_q, $solr_fl, $solr_op, $solr_rows);
  if($solr_sxml != false) {
    return $solr_sxml;
  }
  else {
    return false;
  }
}

  function get_workflow_dom_specimen($workflow_dom, $workflow_root_element, $specimen_pid) {
    $specimens = $workflow_dom->getElementsByTagName('specimen');
    foreach($specimens as $specimen) {
      $pid_element = $specimen->getElementsByTagName('pid')->item(0);
      if($pid_element->nodeValue == $specimen_pid) {
        return $specimen;
      }
    }
    return create_workflow_dom_specimen($workflow_dom, $workflow_root_element, $specimen_pid);
  }

  function get_workflow_dom_image($workflow_dom, $workflow_root_element, $image_pid) {
    $images = $workflow_dom->getElementsByTagName('image');
    foreach($images as $image) {
      $pid_element = $image->getElementsByTagName('pid')->item(0);
      if($pid_element->nodeValue == $image_pid) {
        return $image;
      }
    }
    //means the image hasn't been added yet so check for specimen parent
    $specimen_pid = get_parent_via_solr($image_pid);
    $specimens = $workflow_dom->getElementsByTagName('specimen');
    $foundSpecimenElement = false;
    foreach($specimens as $specimen) {
      $pid_element = $specimen->getElementsByTagName('pid')->item(0);
      if($pid_element->nodeValue == $specimen_pid) {
        $specimenElement = $specimen;
        $foundSpecimenElement = true;
      }
    }
    if(!$foundSpecimenElement) {
      $specimenElement = create_workflow_dom_specimen($workflow_dom, $workflow_root_element, $specimen_pid);
      $image_array['pid'] = $image_pid;
      return create_workflow_dom_image($workflow_dom, $specimenElement, $image_array);
    }
    else {
      $images = $specimenElement->getElementsByTagName('image');
      foreach($images as $image) {
        $pid_element = $image->getElementsByTagName('pid')->item(0);
        if($pid_element->nodeValue == $image_pid) {
          return $image;
        }
      }
      $image_array['pid'] = $image_pid;
      return create_workflow_dom_image($workflow_dom, $specimenElement, $image_array);
    }
  }

  function append_workflow_dom_child($workflow_dom, $workflow_element, $child_name, $child_value) {
    $child_element = $workflow_dom->createElement($child_name, $child_value);
    $workflow_element->appendChild($child_element);
  }

  function create_workflow_dom_specimen($workflow_dom, $workflow_root_element, $specimen_pid) {
    $specimenElement = $workflow_dom->createElement("specimen");
    append_workflow_dom_child($workflow_dom, $specimenElement, "pid", $specimen_pid);
    $workflow_root_element->appendChild($specimenElement);
    return $specimenElement;
  }

  function create_workflow_dom_image($workflow_dom, $specimenElement, $image) {
    $imageElement = $workflow_dom->createElement("image");
    append_workflow_dom_child($workflow_dom, $imageElement, "pid", $image['pid']);
    append_workflow_dom_child($workflow_dom, $imageElement, "locked", $image['locked']);
    append_workflow_dom_child($workflow_dom, $imageElement, "locked_time", $image['locked_time']);
    append_workflow_dom_child($workflow_dom, $imageElement, "locked_by", $image['locked_by']);
    append_workflow_dom_child($workflow_dom, $imageElement, "locked_session", $image['locked_session']);
    append_workflow_dom_child($workflow_dom, $imageElement, "analyzedStatus", $image['analyzedStatus']);
    append_workflow_dom_child($workflow_dom, $imageElement, "qcStatus", $image['qcStatus']);
    append_workflow_dom_child($workflow_dom, $imageElement, "qcStatusUpdatedBy", $image['qcStatusUpdatedBy']);
    append_workflow_dom_child($workflow_dom, $imageElement, "workflow_status", $image['workflow_status']);
    $specimenElement->appendChild($imageElement);
    return $imageElement;
  }

  function create_workflow_dom_roi($workflow_dom, $imageElement, $roi) {
    $roiElement = $workflow_dom->createElement("roi");
    append_workflow_dom_child($workflow_dom, $roiElement, "pid", $roi['pid']);
    append_workflow_dom_child($workflow_dom, $roiElement, "locked", $roi['locked']);
    append_workflow_dom_child($workflow_dom, $roiElement, "locked_time", $roi['locked_time']);
    append_workflow_dom_child($workflow_dom, $roiElement, "locked_by", $roi['locked_by']);
    append_workflow_dom_child($workflow_dom, $roiElement, "locked_session", $roi['locked_session']);
    append_workflow_dom_child($workflow_dom, $roiElement, "transcribedStatus", $roi['transcribedStatus']);
    append_workflow_dom_child($workflow_dom, $roiElement, "transcribedStatusUpdatedBy", $roi['transcribedStatusUpdatedBy']);
    append_workflow_dom_child($workflow_dom, $roiElement, "parsedL1Status", $roi['parsedL1Status']);
    append_workflow_dom_child($workflow_dom, $roiElement, "parsedL1StausUpdatedBy", $roi['parsedL1StausUpdatedBy']);
    append_workflow_dom_child($workflow_dom, $roiElement, "parsedL2Status", $roi['parsedL2Status']);
    append_workflow_dom_child($workflow_dom, $roiElement, "parsedL3Status", $roi['parsedL3Status']);
    append_workflow_dom_child($workflow_dom, $roiElement, "qcStatus", $roi['qcStatus']);
    append_workflow_dom_child($workflow_dom, $roiElement, "qcStatusUpdatedBy", $roi['qcStatusUpdatedBy']);
    append_workflow_dom_child($workflow_dom, $roiElement, "workflow_status", $roi['workflow_status']);
    $imageElement->appendChild($roiElement);
    return $roiElement;
  }

function create_images_array($solr_sxml) {
  $images = array();
  foreach($solr_sxml->result[0]->doc as $doc) {
    $pid = '';
    $parent_pid = '';
    $analyzedStatus = '';
    $transcribedStatus = '';
    $transcribedStatusUpdatedBy = '';
    $parsedL1Status = '';
    $parsedL1StausUpdatedBy = '';
    $parsedL2Status = '';
    $parsedL3Status = '';
    $qcStatus = '';
    $qcStatusUpdatedBy = '';
    $locked = '';
    $locked_time = '';
    $locked_by = '';
    $locked_session = '';
    foreach($doc->children() as $sxml_node) {
      if($sxml_node->attributes()->name == 'id') {
        $pid = (string)$sxml_node;
      }
      if($sxml_node->attributes()->name == 'parent_id') {
        $parent_pid = (string)$sxml_node;
      }
      if($sxml_node->attributes()->name == 'status_analyzedStatus') {
        $analyzedStatus = (string)$sxml_node;
      }
      if($sxml_node->attributes()->name == 'status_transcribedStatus') {
        $transcribedStatus = (string)$sxml_node;
      }
      if($sxml_node->attributes()->name == 'status_transcribedStatusUpdatedBy') {
        $transcribedStatusUpdatedBy = (string)$sxml_node;
      }
      if($sxml_node->attributes()->name == 'status_parsedL1Status') {
        $parsedL1Status = (string)$sxml_node;
      }
      if($sxml_node->attributes()->name == 'status_parsedL1StausUpdatedBy') {
        $parsedL1StausUpdatedBy = (string)$sxml_node;
      }
      if($sxml_node->attributes()->name == 'status_parsedL2Status') {
        $parsedL2Status = (string)$sxml_node;
      }
      if($sxml_node->attributes()->name == 'status_parsedL3Status') {
        $parsedL3Status = (string)$sxml_node;
      }
      if($sxml_node->attributes()->name == 'status_qcStatus') {
        $qcStatus = (string)$sxml_node;
      }
      if($sxml_node->attributes()->name == 'status_qcStatusUpdatedBy') {
        $qcStatusUpdatedBy = (string)$sxml_node;
      }
      if($sxml_node->attributes()->name == 'status_locked') {
        $locked = (string)$sxml_node;
      }
      if($sxml_node->attributes()->name == 'locked_time') {
        $locked_time = (string)$sxml_node;
      }
      if($sxml_node->attributes()->name == 'status_locked_by') {
        $locked_by = (string)$sxml_node;
      }
      if($sxml_node->attributes()->name == 'status_locked_session') {
        $locked_session = (string)$sxml_node;
      }
    }
    if(strpos($pid, 'ap-image:') > -1) {
      $image = array();
      $image['pid'] = $pid;
      $image['parent_pid'] = $parent_pid;
      $image['analyzedStatus'] = $analyzedStatus;
      $image['locked'] = $locked;
      $image['locked_time'] = $locked_time;
      $image['locked_by'] = $locked_by;
      $image['locked_session'] = $locked_session;
      $image['qcStatus'] = $qcStatus;
      $image['qcStatusUpdatedBy'] = $qcStatusUpdatedBy;
      $image['workflow_status'] = workflow_status($locked, $locked_time, $locked_session);
    }
    else if(strpos($pid, 'ap-roi:') > -1) {
      $roi = array();
      $roi['pid'] = $pid;
      $roi['parent_pid'] = $parent_pid;
      $roi['transcribedStatus'] = $transcribedStatus;
      $roi['transcribedStatusUpdatedBy'] = $transcribedStatusUpdatedBy;
      $roi['parsedL1Status'] = $parsedL1Status;
      $roi['parsedL1StausUpdatedBy'] = $parsedL1StausUpdatedBy;
      $roi['parsedL2Status'] = $parsedL2Status;
      $roi['parsedL3Status'] = $parsedL3Status;
      $roi['locked'] = $locked;
      $roi['locked_time'] = $locked_time;
      $roi['locked_by'] = $locked_by;
      $roi['locked_session'] = $locked_session;
      $roi['qcStatus'] = $qcStatus;
      $roi['qcStatusUpdatedBy'] = $qcStatusUpdatedBy;
      $roi['workflow_status'] = workflow_status($locked, $locked_time, $locked_session);
    }
  }
}

function add_image_with_rois_to_queue($workflow_id, $image_pid) {
  $queue_item_html = '';
  $status_dom = AP_Image::getImageLock($image_pid);
  if($status_dom != false) {
    $analyzedStatus = $status_dom->getElementsByTagName('analyzedStatus')->item(0)->nodeValue;
    $locked_by = $status_dom->getElementsByTagName('locked_by')->item(0)->nodeValue;
    $locked = $status_dom->getElementsByTagName('locked')->item(0)->nodeValue;
    $locked_time = $status_dom->getElementsByTagName('locked_time')->item(0)->nodeValue;
    $locked_session = $status_dom->getElementsByTagName('locked_session')->item(0)->nodeValue;
    $workflow_status = workflow_status($locked, $locked_time, $locked_session);
    $specimen_pid = AP_Image::get_specimen_pid($image_pid);
    $roi_pids = AP_Image::getROIListForImage($image_pid);
    $roi_count = sizeOf($roi_pids);
    $queue_item_html .= create_queue_list_image($image_pid, $specimen_pid, $analyzedStatus, $roi_count, $locked_by, $workflow_status, $workflow_id);

    $queue_item_html .= '<div class="widget-content" id="content-'.str_replace(':', '__', $image_pid).'">'."\n";
    foreach($roi_pids as $roi_pid) {
      $roi_status_dom = AP_ROI::getROILock($roi_pid);
      if($roi_status_dom != false) {
        $transcribedStatus = $roi_status_dom->getElementsByTagName('transcribedStatus')->item(0)->nodeValue;
        $parsedL1Status = $roi_status_dom->getElementsByTagName('parsedL1Status')->item(0)->nodeValue;
        $locked_by = $roi_status_dom->getElementsByTagName('locked_by')->item(0)->nodeValue;
        $locked = $roi_status_dom->getElementsByTagName('locked')->item(0)->nodeValue;
        $locked_time = $roi_status_dom->getElementsByTagName('locked_time')->item(0)->nodeValue;
        $locked_session = $roi_status_dom->getElementsByTagName('locked_session')->item(0)->nodeValue;
        $workflow_status = workflow_status($locked, $locked_time, $locked_session);
        $queue_item_html .= create_queue_list_roi($roi_pid, $image_pid, $transcribedStatus, $parsedL1Status, $locked_by, $workflow_status,$workflow_id);
      }
    }
    $queue_item_html .= '</div><!-- widget-content -->'."\n";
  }
  else {
    $queue_item_html = "locked";
  }
  echo $queue_item_html;
}

function add_images_with_rois_to_queue($workflow_id, $image_list) {
  //add_images_with_rois_to_queue_via_fedora($workflow_id, $image_list);
  add_images_with_rois_to_queue_via_solr($workflow_id, $image_list);
}

function add_images_with_rois_to_queue_via_solr($workflow_id, $image_list) {
  $images = explode(",", $image_list);
  $queue_html = '';
  $queued_count = 0;
  $queued_list = '';
  $locked_count = 0;
  $locked_list = '';
  $message = '';
  for($i=0; $i < sizeOf($images); $i++) {
    $image_pid = $images[$i];
    $status_dom = AP_Image::getImageLock($image_pid); //cannot use solr here as we have to write to fedora when we get a successful lock
    if($status_dom != false) {
      $queued_count++;
      if(strlen($queued_list) > 0){
        $queued_list .= ',';
      }
      $queued_list .= str_replace(':', '__', $image_pid);
      $analyzedStatus = $status_dom->getElementsByTagName('analyzedStatus')->item(0)->nodeValue;
      $locked_by = $status_dom->getElementsByTagName('locked_by')->item(0)->nodeValue;
      $locked = $status_dom->getElementsByTagName('locked')->item(0)->nodeValue;
      $locked_time = $status_dom->getElementsByTagName('locked_time')->item(0)->nodeValue;
      $locked_session = $status_dom->getElementsByTagName('locked_session')->item(0)->nodeValue;
      $workflow_status = workflow_status($locked, $locked_time, $locked_session);

      $specimen_pid = get_parent_pid_via_solr($image_pid);
      $roi_pids = get_roi_list_via_solr($image_pid);
      $roi_count = sizeOf($roi_pids);
      $queue_html .= create_queue_list_image($image_pid, $specimen_pid, $analyzedStatus, $roi_count, $locked_by, $workflow_status,$workflow_id);
      $queue_html .= '<div class="widget-content" id="content-'.str_replace(':', '__', $image_pid).'">'."\n";
      foreach($roi_pids as $roi_pid) {
        $roi_status_dom = AP_ROI::getROILock($roi_pid);
        if($roi_status_dom != false) {
          $transcribedStatus = $roi_status_dom->getElementsByTagName('transcribedStatus')->item(0)->nodeValue;
          $parsedL1Status = $roi_status_dom->getElementsByTagName('parsedL1Status')->item(0)->nodeValue;
          $locked_by = $roi_status_dom->getElementsByTagName('locked_by')->item(0)->nodeValue;
          $locked = $roi_status_dom->getElementsByTagName('locked')->item(0)->nodeValue;
          $locked_time = $roi_status_dom->getElementsByTagName('locked_time')->item(0)->nodeValue;
          $locked_session = $roi_status_dom->getElementsByTagName('locked_session')->item(0)->nodeValue;
          $workflow_status = workflow_status($locked, $locked_time, $locked_session);
          $queue_html .= create_queue_list_roi($roi_pid, $image_pid, $transcribedStatus, $parsedL1Status, $locked_by, $workflow_status,$workflow_id);
        }
      }
      $queue_html .= '</div><!-- widget-content -->'."\n";
    }
    else {
      $locked_count++;
      if(strlen($locked_list) > 0){
        $locked_list .= ',';
      }
      $locked_list .= str_replace(':', '__', $image_pid);
    }
  }
  $returnHTML['queue_html'] = $queue_html;
  $returnHTML['queued_list'] = $queued_list;
  $returnHTML['locked_list'] = $locked_list;
  $message = $queued_count.' items and their ROIs added to the queue.';
  if($locked_count > 0) {
    $message .= ' '.$locked_count.' items locked and unable to be added to the queue.';
  }
  $returnHTML['message'] = $message;
  echo json_encode($returnHTML);
}

function create_queue_list_rois_via_solr($image_pid) {
  $solr_sxml = get_roi_solr_sxml($image_pid);
  $queue_html = '';
  if($solr_sxml != false) {
    foreach($solr_sxml->result[0]->doc as $doc) {
      $pid = '';
      $transcribedStatus = '';
      $parsedL1Status = '';
      $locked = '';
      $locked_time = '';
      $locked_by = '';
      $locked_session = '';
      foreach($doc->children() as $sxml_node) {
        if($sxml_node->attributes()->name == 'id') {
          $pid = (string)$sxml_node;
        }
        if($sxml_node->attributes()->name == 'status_transcribedStatus') {
          $transcribedStatus = (string)$sxml_node;
        }
        if($sxml_node->attributes()->name == 'status_parsedL1Status') {
          $parsedL1Status = (string)$sxml_node;
        }
        if($sxml_node->attributes()->name == 'status_locked') {
          $locked = (string)$sxml_node;
        }
        if($sxml_node->attributes()->name == 'locked_time') {
          $locked_time = (double)$sxml_node;
        }
        if($sxml_node->attributes()->name == 'status_locked_by') {
          $locked_by = (string)$sxml_node;
        }
        if($sxml_node->attributes()->name == 'status_locked_session') {
          $locked_session = (string)$sxml_node;
        }
      }
      $workflow_status = workflow_status($locked, $locked_time, $locked_session);
      $queue_html .= create_queue_list_roi($pid, $image_pid, $transcribedStatus, $parsedL1Status, $locked_by, $workflow_status, $workflow_id);
    }
  }
  return $queue_html;
}

function add_images_with_rois_to_queue_via_fedora($workflow_id, $image_list) {
    //echo '$image_list = '.$image_list.'<br>'."\n";
  $images = explode(",", $image_list);
    //echo '$images = '.$images.'<br>'."\n";
    //echo '$images[0] = '.$images[0].'<br>'."\n";
    //echo '$images[1] = '.$images[1].'<br>'."\n";
  $queue_html = '';
  $queued_count = 0;
  $queued_list = '';
  $locked_count = 0;
  $locked_list = '';
  $message = '';
  for($i=0; $i < sizeOf($images); $i++) {
    $image_pid = $images[$i];
    //echo '$image_pid = '.$image_pid.'<br>'."\n";
    $status_dom = AP_Image::getImageLock($image_pid);
    if($status_dom != false) {
      $queued_count++;
      if(strlen($queued_list) > 0){
        $queued_list .= ',';
      }
      $queued_list .= str_replace(':', '__', $image_pid);
      $analyzedStatus = $status_dom->getElementsByTagName('analyzedStatus')->item(0)->nodeValue;
      $locked_by = $status_dom->getElementsByTagName('locked_by')->item(0)->nodeValue;
      $locked = $status_dom->getElementsByTagName('locked')->item(0)->nodeValue;
      $locked_time = $status_dom->getElementsByTagName('locked_time')->item(0)->nodeValue;
      $locked_session = $status_dom->getElementsByTagName('locked_session')->item(0)->nodeValue;
      $workflow_status = workflow_status($locked, $locked_time, $locked_session);
      $specimen_pid = AP_Image::get_specimen_pid($image_pid);
      $roi_pids = AP_Image::getROIListForImage($image_pid);
      $roi_count = sizeOf($roi_pids);
      $queue_html .= create_queue_list_image($image_pid, $specimen_pid, $analyzedStatus, $roi_count, $locked_by, $workflow_status,$workflow_id);

      $queue_html .= '<div class="widget-content" id="content-'.str_replace(':', '__', $image_pid).'">'."\n";
      foreach($roi_pids as $roi_pid) {
        $roi_status_dom = AP_ROI::getROILock($roi_pid);
        if($roi_status_dom != false) {
          $transcribedStatus = $roi_status_dom->getElementsByTagName('transcribedStatus')->item(0)->nodeValue;
          $parsedL1Status = $roi_status_dom->getElementsByTagName('parsedL1Status')->item(0)->nodeValue;
          $locked_by = $roi_status_dom->getElementsByTagName('locked_by')->item(0)->nodeValue;
          $locked = $roi_status_dom->getElementsByTagName('locked')->item(0)->nodeValue;
          $locked_time = $roi_status_dom->getElementsByTagName('locked_time')->item(0)->nodeValue;
          $locked_session = $roi_status_dom->getElementsByTagName('locked_session')->item(0)->nodeValue;
          $workflow_status = workflow_status($locked, $locked_time, $locked_session);
          $queue_html .= create_queue_list_roi($roi_pid, $image_pid, $transcribedStatus, $parsedL1Status, $locked_by, $workflow_status, $workflow_id);
        }
      }
      $queue_html .= '</div><!-- widget-content -->'."\n";
    }
    else {
      $locked_count++;
      if(strlen($locked_list) > 0){
        $locked_list .= ',';
      }
      $locked_list .= str_replace(':', '__', $image_pid);
    }
  }
  $returnHTML['queue_html'] = $queue_html;
  $returnHTML['queued_list'] = $queued_list;
  $returnHTML['locked_list'] = $locked_list;
  $message = $queued_count.' items and their ROIs added to the queue.';
  if($locked_count > 0) {
    $message .= ' '.$locked_count.' items locked and unable to be added to the queue.';
  }
  $returnHTML['message'] = $message;
  echo json_encode($returnHTML);
}

function add_next_image_with_rois_to_queue($workflow_id) {
    $timeout = date("YmdHis",strtotime('now')-1800);
    $workflow = new Workflow($workflow_id, true);
    $workflow_dom = $workflow->workflow_dom;
    $image_pids = $workflow->image_pids;
    $totalEntries = sizeOf($image_pids);

    $specimen_elements = $workflow_dom->getElementsByTagName('specimen');
    foreach($specimen_elements as $specimen)
    {
        $specimen_pid = $specimen->getElementsByTagName('pid')->item(0)->nodeValue;
        $specimen_image_elements = $specimen->getElementsByTagName('image');
        foreach($specimen_image_elements as $image)
        {
            $image_pid = $image->getElementsByTagName('pid')->item(0)->nodeValue;
            $status_dom = AP_Image::getImageStatusDom($image_pid);
            if($status_dom != false && ($status_dom->getElementsByTagName('locked')->item(0)->nodeValue == "false" || $status_dom->getElementsByTagName('locked_time')->item(0)->nodeValue < $timeout)) {
              $status_dom = AP_Image::getImageLock($image_pid);
              $analyzedStatus = $status_dom->getElementsByTagName('analyzedStatus')->item(0)->nodeValue;
              $locked_by = $status_dom->getElementsByTagName('locked_by')->item(0)->nodeValue;
              $locked = $status_dom->getElementsByTagName('locked')->item(0)->nodeValue;
              $locked_time = $status_dom->getElementsByTagName('locked_time')->item(0)->nodeValue;
              $locked_session = $status_dom->getElementsByTagName('locked_session')->item(0)->nodeValue;
              $workflow_status = workflow_status($locked, $locked_time, $locked_session);
              $roi_pids = AP_Image::getROIListForImage($image_pid);
              $roi_count = sizeOf($roi_pids);
              $queue_item_html .= create_queue_list_image($image_pid, $specimen_pid, $analyzedStatus, $roi_count, $locked_by, $workflow_status,$workflow_id);

              //$queue_item_html .= '<div class="widget-content" id="content-'.str_replace(':', '__', $image_pid).'">'."\n";
              $found_queued_roi = false;
              foreach($roi_pids as $roi_pid) {
                $roi_status_dom = AP_ROI::getROILock($roi_pid);
                if($roi_status_dom != false) {
                  if(!$found_queued_roi) {
                    $queue_item_html .= '<div class="widget-content" id="content-'.str_replace(':', '__', $image_pid).'">'."\n";
                    $found_queued_roi = true;
                  }
                  $transcribedStatus = $roi_status_dom->getElementsByTagName('transcribedStatus')->item(0)->nodeValue;
                  $parsedL1Status = $roi_status_dom->getElementsByTagName('parsedL1Status')->item(0)->nodeValue;
                  $locked_by = $roi_status_dom->getElementsByTagName('locked_by')->item(0)->nodeValue;
                  $locked = $roi_status_dom->getElementsByTagName('locked')->item(0)->nodeValue;
                  $locked_time = $roi_status_dom->getElementsByTagName('locked_time')->item(0)->nodeValue;
                  $locked_session = $roi_status_dom->getElementsByTagName('locked_session')->item(0)->nodeValue;
                  $workflow_status = workflow_status($locked, $locked_time, $locked_session);
                  $queue_item_html .= create_queue_list_roi($roi_pid, $image_pid, $transcribedStatus, $parsedL1Status, $locked_by, $workflow_status, $workflow_id);
                }
              }
              if($found_queued_roi) {
                $queue_item_html .= '</div><!-- widget-content -->'."\n";
              }
              break 2;
            }
        }
    }
    echo $queue_item_html;
}

function getImageMetadata($image_pid, $operation, $workflow_id){
	$image_metadata = AP_Image::getimageMetadata_record($image_pid);
	if($operation == "rft_id"){
		echo json_encode($image_metadata);
	}
	else{
		$url = "http://demo.apiaryproject.org:8080/adore-djatoka/resolver?url_ver=Z39.88-2004&rft_id=" . $image_metadata['URL'] . "&svc_id=info:lanl-repo/svc/getMetadata";
		$send = curl_init();
		curl_setopt($send, CURLOPT_URL, $url);
		curl_setopt($send, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($send, CURLINFO_HEADER_OUT, 1);
		$output = curl_exec($send);
		if(curl_errno($send))
			return false;
		else{
			$info = curl_getinfo($send);
			if($info['http_code']==200){
				echo $output;
			}
			else
			return false;
		}
	}
}

/*
function empty_queue($session_id) {
  //$solr_query .= '&fl=id+status_analyzedStatus+status_transcribedStatus+status_parsedL1Status+status_parsedL2Status+status_parsedL3Status';
  $apiary_session = $session_id;
  $solr_q = 'q=status_locked_session:("'.$apiary_session.'")';
  $solr_fl = 'fl=id';
  //$solr_op = 'q.op=AND';
  $solr_op = '';
  $solr_rows = 'rows=10000';
  $solr_sxml = solr_query_xml($solr_q, $solr_fl, $solr_op, $solr_rows);
  if($solr_sxml != false) {
    $queue_count = 0;
    $cleared_count = 0;
    foreach($solr_sxml->result[0]->doc as $doc) {
      foreach($doc->children() as $sxml_node) {
        $queue_count++;
        $pid = $sxml_node;
        if(strpos($pid, 'ap-image:') > -1) {
          $success = AP_Image::releaseImageLock($pid);
          if($success != false) {
            $cleared_count++;
          }
        }
        else if(strpos($pid, 'ap-roi:') > -1) {
          $success = AP_ROI::releaseROILock($pid);
          if($success != false) {
            $cleared_count++;
          }
        }
      }
    }
    if($queue_count == $queue_count) {
      return true;
    }
    else {
      return false;
    }
  }
  else {
    return false;
  }
}
*/

function empty_queue($session_id) {
  //$solr_query .= '&fl=id+status_analyzedStatus+status_transcribedStatus+status_parsedL1Status+status_parsedL2Status+status_parsedL3Status';
  $apiary_session = $session_id;
  $solr_q = 'q=status_locked_session:("'.$apiary_session.'")';
  $solr_fl = 'fl=id+status_analyzedStatus+status_transcribedStatus+status_parsedL1Status+status_parsedL2Status+status_parsedL3Status';
  $solr_op = '';
  $solr_rows = 'rows=10000';
  $solr_sxml = solr_query_xml($solr_q, $solr_fl, $solr_op, $solr_rows);
  if($solr_sxml != false) {
    foreach($solr_sxml->result[0]->doc as $doc) {
      $pid = '';
      $analyzedStatus = '';
      $transcribedStatus = '';
      $parsedL1Status = '';
      $parsedL2Status = '';
      $parsedL3Status = '';
      foreach($doc->children() as $sxml_node) {
        if($sxml_node->attributes()->name == 'id') {
          $pid = $sxml_node;
        }
        if($sxml_node->attributes()->name == 'status_analyzedStatus') {
          $analyzedStatus = $sxml_node;
        }
        if($sxml_node->attributes()->name == 'status_transcribedStatus') {
          $transcribedStatus = $sxml_node;
        }
        if($sxml_node->attributes()->name == 'status_parsedL1Status') {
          $parsedL1Status = $sxml_node;
        }
        if($sxml_node->attributes()->name == 'status_parsedL2Status') {
          $parsedL2Status = $sxml_node;
        }
        if($sxml_node->attributes()->name == 'status_parsedL3Status') {
          $parsedL3Status = $sxml_node;
        }
      }
      if(strpos($pid, 'ap-image:') > -1) {
        if($analyzedStatus == "in progress") {
          updateStatus($pid, "analyzedStatus", "incomplete");
        }
        AP_Image::releaseImageLock($pid);
      }
      else if(strpos($pid, 'ap-roi:') > -1) {
        if($transcribedStatus == "in progress") {
          updateStatus($pid, "transcribedStatus", "incomplete");
        }
        if($parsedL1Status == "in progress") {
          updateStatus($pid, "parsedL1Status", "incomplete");
        }
        if($parsedL2Status == "in progress") {
          updateStatus($pid, "parsedL2Status", "incomplete");
        }
        if($parsedL3Status == "in progress") {
          updateStatus($pid, "parsedL3Status", "incomplete");
        }
        AP_ROI::releaseROILock($pid);
      }
    }
    return true;
  }
  else {
    return false;
  }
}

function emptyQueue() {
  //$solr_query .= '&fl=id+status_analyzedStatus+status_transcribedStatus+status_parsedL1Status+status_parsedL2Status+status_parsedL3Status';
  $apiary_session = $_SESSION['apiary_session_id'];
  $solr_q = 'q=status_locked_session:("'.$apiary_session.'")';
  $solr_fl = 'fl=id';
  $solr_op = 'q.op=AND';
  $solr_rows = 'rows=10000';
  $solr_sxml = solr_query_xml($solr_q, $solr_fl, $solr_op, $solr_rows);
  if($solr_sxml != false) {
    $queue_count = 0;
    $cleared_count = 0;
    foreach($solr_sxml->result[0]->doc as $doc) {
      foreach($doc->children() as $sxml_node) {
        $queue_count++;
        $pid = $sxml_node;
        if(strpos($pid, 'ap-image:') > -1) {
          $success = AP_Image::releaseImageLock($pid);
          if($success != false) {
            $cleared_count++;
          }
        }
        else if(strpos($pid, 'ap-roi:') > -1) {
          $success = AP_ROI::releaseROILock($pid);
          if($success != false) {
            $cleared_count++;
          }
        }
      }
    }
    if($queue_count == $queue_count) {
      return true;
    }
    else {
      return false;
    }
  }
  else {
    return false;
  }
}

function updateStatus($pid, $status_type, $status) {
  if(strpos($pid, 'ap-image:') > -1) {
    $success = AP_Image::setImageStatus($pid, $status_type, $status);
  }
  else if(strpos($pid, 'ap-roi:') > -1) {
    $success = AP_ROI::setROIStatus($pid, $status_type, $status);
  }
  else {
    $success = false;
  }
  if($success != false){
    echo '<status_successfully_updated>true</status_successfully_updated>'; //or whatever we want the return to be
  }
  else {
    echo '<status_successfully_updated>false</status_successfully_updated>'; //or whatever we want the return to be
  }
}

function releaseLock($pid){
  if(strpos($pid, 'ap-image:') > -1) {
    $success = AP_Image::releaseImageLock($pid);
    if($success != false) {
      echo "success";
    }
    else {
      echo "false";
    }
  }
  else if(strpos($pid, 'ap-roi:') > -1) {
    $success = AP_ROI::releaseROILock($pid);
    if($success != false) {
      echo "success";
    }
    else {
      echo "false";
    }
  }
}

function get_workflow_status_via_solr($pid) {
  $solr_q = 'q=id:("'.$pid.'")';
  $solr_fl = 'fl=status_locked+locked_time+status_locked_session';
  $solr_sxml = solr_query_xml($solr_q, $solr_fl);
  if($solr_sxml != false) {
    foreach($solr_sxml->result[0]->doc[0]->children() as $sxml_node) {
      $sxml_arr = $sxml_node->attributes();
      $sxml_attrib_name = $sxml_arr["name"];
      if($sxml_attrib_name == 'status_locked') {
        $locked = $sxml_node;
      }
      if($sxml_attrib_name == 'locked_time') {
        $locked_time = (double)$sxml_node;
      }
      if($sxml_attrib_name == 'status_locked_session') {
        $locked_session = $sxml_node;
      }
    }
    return workflow_status($locked, $locked_time, $locked_session);
  }
  else {
    return false;
  }
}

function workflow_status($locked, $locked_time, $locked_session) {
  $workflow_status = "locked";
  if($locked == "true") {
    if(Workflow::isLockedExpired($locked_time, $locked_session)) {
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

function solr_query_xml($q, $fl = '', $op = '', $rows = '') {
  $solr_search = new search();
  if(strpos($q, "q=") === false) {
    $q = 'q='.str_replace('&', '', $q);
  }
  $solr_query = $q;
  if(!empty($fl)) {
    if(strpos($fl, 'fl=') === false) {
      $fl = 'fl='.$fl;
    }
    $solr_query .= '&'.str_replace('&', '', $fl);
  }
  if(!empty($op)) {
    if(strpos($op, 'op=') === false) {
      $op = 'op='.$op;
    }
    $solr_query .= '&'.str_replace('&', '', $op);
  }
  if(!empty($rows)) {
    if(strpos($rows, 'rows=') === false) {
      $rows = 'rows='.$rows;
    }
    $solr_query .= '&'.str_replace('&', '', $rows);
  }
  //echo "solr_query = ".$solr_query."<br>\n";
  $solr_results = $solr_search->doSearch($solr_query);
  if($solr_results != false) {
    $solr_sxml = new SimpleXMLElement($solr_results);
    return $solr_sxml;
  }
  else {
    return false;
  }
}

function research_server_djatoka_records() {
  $curl_handle=curl_init();
  $url = 'http://research.apiaryproject.org/adore-djatoka/images.php';
  curl_setopt($curl_handle, CURLOPT_URL, $url);
  curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
  curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
  $output = curl_exec($curl_handle);
  curl_close($curl_handle);
  if(strpos($output, "404 Not Found")==FALSE) {
    $records = array();
    $lines = explode("\n",trim($output));
    $count = 0;
    foreach ($lines as $line) {
      if (trim($line)) {
        $values = explode(", ",$line);
        $records[$count]['rft_id'] = $values[0];
        $records[$count]['original_url'] = $values[1];
        $records[$count]['jp2_url'] = $values[2];
        $count++;
      }
    }
    return $records;
  }
  else {
    echo "Unable to get image list for this djatoka server";
  }
}

function research_server_image_list() {
  $records = research_server_djatoka_records();
  $specimen_list_html = '<div id="add_items" class="dialog_box">'."\n";
  $specimen_list_html .= '<div id="add_items_list" class="dialog_box_content">'."\n";
  $specimen_list_html .= '<div id="add_items_page_1" class="add_items_page" style="">'."\n";
  foreach($records as $record) {
    $specimen_list_html .= create_image_list_div($record['rft_id'], $record['original_url'], $record['jp2_url']);
  }
  $specimen_list_html .= '</div><!-- add_items_page_1 -->'."\n";
  $specimen_list_html .= '</div><!-- add_items_list -->'."\n";
  $specimen_list_html .= '</div><!-- add_items -->'."\n";
  $specimen_list_html .= '<input type="button" id="ingest_specimen_images_btn" name="ingest_specimen_images_btn" onClick="ingest_specimen_images();" value="Add Specimens" />';
  echo $specimen_list_html;
}

function get_exisitng_original_url_list(){
  $original_url_list = array();
  $solr_q = 'q=imageMetadata_sourceURL:(*)';
  $solr_fl = 'fl=imageMetadata_sourceURL';
  $solr_op = '';
  $solr_rows = 'rows=10000';
  $solr_sxml = solr_query_xml($solr_q, $solr_fl, $solr_op, $solr_rows);
  foreach($solr_sxml->result[0]->doc as $doc) {
    foreach($doc->children() as $sxml_node) {
      if($sxml_node->attributes()->name == 'imageMetadata_sourceURL') {
        array_push($original_url_list, (string)$sxml_node);
      }
    }
  }
  return $original_url_list;
}

function create_image_list_div($rft_id, $original_url, $jp2_url) {
  $original_url_list = get_exisitng_original_url_list();
  $selected_class = 'unselected';
  $roundedcornr_class = 'roundedcornr_ltgray';
  $ingested = '';
  if(array_search($original_url, $original_url_list) > -1) {
    $selected_class = 'selected ingested';
    $roundedcornr_class = 'roundedcornr_dgray';
    $ingested = ' -- Already Ingested!';
  }
  $id = str_replace(':', '__', $rft_id);
  $id = str_replace('/', '_s_', $id);
  $id = str_replace('.', '_d_', $id);
  $image_list_div_html = '            <div id="'.$id.'" class="pool_item '.$roundedcornr_class.' '.$selected_class.'" original_url="'.$original_url.'" jp2_url="'.$jp2_url.'">'."\n";

  $djatoka_server_base = variable_get('apiary_research_djatoka_url', 'http://img.apiaryproject.org');
  $djatoka_image_url = $djatoka_server_base.'/resolver?url_ver=Z39.88-2004';
  $djatoka_image_url .= '&rft_id='.$rft_id;
  $djatoka_image_url .= '&svc_id=info:lanl-repo/svc/getRegion';
  $djatoka_image_url .= '&svc_val_fmt=info:ofi/fmt:kev:mtx:jpeg2000';
  $djatoka_image_url .= '&svc.format=image/jpeg';
  $djatoka_image_url .= '&svc.level=3';
  $djatoka_image_url .= '&svc.rotate=0';
  $djatoka_image_url .= '&svc.scale=0,68';

  $image_list_div_html .= '              <div class="t"><div class="b"><div class="l"><div class="r"><div class="bl"><div class="br"><div class="tl"><div class="tr">'."\n";
  $image_list_div_html .= '                <div class="pool_item_checkbox" onclick="toggle_selected(\'#'.$id.'\');"></div>'."\n";
  $image_list_div_html .= '                <div class="pool_item_area pool_item_image" onclick="toggle_selected(\'#'.$id.'\');">'."\n";
  $image_list_div_html .= '                  <img src="'.$djatoka_image_url.'" height=68px />'."\n";
  $image_list_div_html .= '                </div><!-- .pool_item_area .pool_item_image -->'."\n";
  $image_list_div_html .= '                <div class="pool_item_area pool_item_content">'."\n";
  $image_list_div_html .= '                  <div class="no_specimen_image_metadata" style="display:block;">'."\n";
  $image_list_div_html .= '                    <strong>'.$rft_id.$ingested.'</strong><br/>'."\n";
  $image_list_div_html .= '                  </div><!-- no_specimen_image_metadata -->'."\n";
  $image_list_div_html .= '                  <div id="'.$id.'_metadata" class="specimen_image_metadata" style="display:none;">'."\n";
  $image_list_div_html .= '                    <strong>'.$rft_id.$ingested.'</strong><br/>'."\n";
  $image_list_div_html .= '                    Collector:<input type=text id="'.$id.'_collector"> Collection number:<input type=text id="'.$id.'_collection_number"><br/>'."\n";
  $image_list_div_html .= '                    Collection date:<input type=text id="'.$id.'_collection_date"> Scientific name:<input type=text id="'.$id.'_scientific_name"><br/>'."\n";
  $image_list_div_html .= '                  </div><!-- specimen_image_metadata -->'."\n";
  $image_list_div_html .= '                </div><!-- .pool_item_area .pool_item_content -->'."\n";
  $image_list_div_html .= '              </div></div></div></div></div></div></div></div>'."\n";
  $image_list_div_html .= '            </div><!-- '.$id.' -->'."\n";
  return $image_list_div_html;
}

function ingest_specimen_image() {
  $specimen_image_successfully_ingested = "false";
  $rft_id = $_POST['rft_id'];
  $original_url = $_POST['original_url'];
  $jp2_url = $_POST['jp2_url'];
  $institution = $_POST['institution'];
  $collector = $_POST['collector'];
  $collection_number = $_POST['collection_number'];
  $collection_date = $_POST['collection_date'];
  $scientific_name = $_POST['scientific_name'];
  if(isset($_POST['rft_id'])){
    include_once(drupal_get_path('module', 'apiary_project') . '/adore-djatoka/functions_djatoka.php');
    module_load_include('php', 'Apiary_Project', 'fedora_commons/class.AP_Specimen');
    $new_specimen = new AP_Specimen();
    $specimen_label = '';
    if($new_specimen->createSpecimenObject($collector, $collection_number, $collection_date, $scientific_name, $specimen_label)) {
      $specimen_image_successfully_ingested = "true";
      module_load_include('php', 'Apiary_Project', 'fedora_commons/class.AP_Image');
      $new_image = new AP_Image();
      list($width, $height) = getimagesize($jp2_url);
      $image_label = '';
      $jpeg_datastream_url = getDjatokaURL($rft_id, 'getRegion', '4', '0', '0', $height, $width, '', '');
      if($new_image->createImageObject($new_specimen->pid, $jp2_url, $rft_id, $original_url, $width, $height, $jpeg_datastream_url, $image_label)) {
        $msg = 'Specimen '.$new_specimen->pid.' and image '.$new_image->pid.' successfully created.<br>';
      }
      else {
        $msg = 'Unable to create a new image. Specimen '.$new_specimen->pid.' successfully created.<br>';;
      }
    }
    else {
      $msg = 'Unable to create a new specimen or image object.<br>';
    }
  }
  $returnJSON['specimen_image_successfully_ingested'] = $specimen_image_successfully_ingested;
  $returnJSON['msg'] = $msg;
  echo json_encode($returnJSON);
}

function solr_index_content() {
  $specimen_list_html = '<div id="solr_index_content">'."\n";
  $specimen_list_html .= "WARNING: Do not interrupt the process once it has been started!<br>\n";
  $specimen_list_html .= '</div><!-- solr_index_content -->'."\n";
  $specimen_list_html .= '<input type="button" id="solr_index_all_btn" name="solr_index_all_btn" onClick="solr_index_all();" value="Index Solr" />';
  echo $specimen_list_html;
}

function solr_index_all() {
  $specimen_list_html = '<div id="solr_index_content">'."\n";
  $specimen_list_html .= "WARNING: Do not interrupt the process once it has been started!<br>\n";
  $search_instance = new search();

  $specimen_list_html .= "Removing all current solr indexes <br>\n";
  $search_instance->delete_all_index();
  $specimen_list_html .= "Finished removing solr indexes <br>\n";

  $specimen_list_html .= "Beginning solr reindexing <br>\n";
  $search_instance->index_all();
  $specimen_list_html .= "Finished solr reindexing <br>\n";

  $specimen_list_html .= '</div><!-- solr_index_content -->'."\n";
  $specimen_list_html .= '<input type="button" id="solr_index_all_btn" name="solr_index_all_btn" onClick="solr_index_all();" value="Index Solr" />';
  echo $specimen_list_html;
}

function get_permissions($workflow_id) {
  $selected_permission_list = Workflow::getPermissionList($workflow_id);
  $returnJSON['selected_permission_list'] = $selected_permission_list;
  $returnJSON['workflow_id'] = $workflow_id;

  echo json_encode($returnJSON);
}

function get_messages(){
  $messages = $_SESSION['messages'];
  unset($_SESSION['messages']);
  echo json_encode($messages);
}

function groundtruth_content($specimen_pid = null) {
  global $base_url;
  if($specimen_pid != null && $specimen_pid != "0") {
    $groundtruth_xml .= specimenGroundtruthXML($specimen_pid);
  }
  else {
    $groundtruth_xml = '';
    $specimen_pid = '';
  }
  //$roi_type_combobox = generateROITypeComboBox();
  $html = '';
  $html .= '<div id="groundtruth_content">'."\n";
  $html .= '<label for="specimen_pid">Specimen:</label>'."\n";
  $html .= '<input type="text" name="specimen_pid" id="specimen_pid" onkeypress="display_groundtruth_keyPressEvent(event);" value="'.$specimen_pid.'" style="width: 300px;"/>'."\n";
  $html .= '<input type="button" name="display_groundtruth_btn" onClick="display_groundtruth();" value="Display Specimen Data" />'."\n";
  $html .= '<br/>'."\n";
  $html .= '<textarea name="groundtruth_data" id="groundtruth_data" style="width: 800px; height: 250px;">'."\n";
  $html .= $groundtruth_xml."\n";
  $html .= '</textarea><!-- groundtruth_data -->'."\n";
  $html .= '<br/>'."\n";
  $html .= '<span>'."\n";
  $html .= ' <input type="button" name="reset_groundtruth_btn" onClick="reset_groundtruth();" value="Reset" />'."\n";
  $html .= ' <input type="button" name="save_groundtruth_btn" onClick="save_groundtruth();" value="Save" />'."\n";
  $html .= ' <input type="button" name="cancel_groundtruth_btn" onClick="cancel_groundtruth();" value="Cancel" />'."\n";
  $html .= '</span>'."\n";
  $html .= '<br/>'."\n";
  $html .= '</div><!-- groundtruth_content -->'."\n";
  echo $html;
}

function generateROITypeComboBox($roi_type = null) {
  $roi_type_list = getROItypeList();
  $combobox_html = '';
  $combobox_html .= '<select name="roi_types" id="roi_types">'."\n";
  for($i = 0; $i < sizeof($roi_type_list); $i++) {
    $selected = '';
    if($roi_type_list[$i] == $roi_type) {
      $selected = ' selected';
    }
    $combobox_html .= '<option onClick="update_roi_type(\''.$roi_type_list[$i].'\');" value="'.$roi_type_list[$i].'"'.$selected.'>'.$roi_type_list[$i].'</option>'."\n";
  }
  $combobox_html .= '</select>'."\n";
  return $combobox_html;
}

function specimenGroundtruthXML($specimen_pid) {
  global $base_url;
  $roi_obj = new roiHandler($specimen_pid);
  if($roi_obj->ifExist("groundtruth")){
    $groundtruth_xml = $roi_obj->getDatastream("groundtruth");
  }
  else {
    $groundtruth_xml = '<groundtruth>'."\n";
    $groundtruth_xml .= "\t".'<apiary_instance_ID>'.$base_url.'</apiary_instance_ID>'."\n";
    $groundtruth_xml .= "\t".'<specimenID>'.$specimen_pid.'</specimenID>'."\n";
    $groundtruth_xml .= "\t".'<specimenMetadata>'."\n";
    $groundtruth_xml .= "\t".'</specimenMetadata>'."\n";
    $groundtruth_xml .= "\t".'<rois>'."\n";
    $groundtruth_xml .= "\t".'</rois>'."\n";
    $groundtruth_xml .= '</groundtruth>'."\n";
  }
  //$groundtruth_dom = new DOMDocument;
  //$groundtruth_dom->loadXML($groundtruth);
  return $groundtruth_xml;
}

function save_groundtruth() {
  $successfully_saved_groundtruth = "false";
  $msg = '';
  $specimen_pid = $_POST['specimen_pid'];
  $groundtruth_xml = $_POST['groundtruth_xml'];
  $groundtruth_dom = new DOMDocument;
  if($specimen_pid != null && $specimen_pid != '')
  {
    if($groundtruth_xml != null && $groundtruth_xml != '') {
      if($groundtruth_dom->loadXML($groundtruth_xml)) {
        if(saveSpecimenGroundtruth($specimen_pid, $groundtruth_dom)) {
          $successfully_saved_groundtruth = "true";
        }
        else {
          $msg = "Failed saving groundtruth xml for this specimen.";
        }
      }
      else {
        $msg = "Invalid xml for groundtruth";
      }
    }
    else {
      $msg = "No groundtruth xml was passed for this specimen.";
    }
  }
  else {
      $msg = "There is no Specimen pid passed to save this groundtruth.";
  }
  $returnJSON['successfully_saved_groundtruth'] = $successfully_saved_groundtruth;
  $returnJSON['msg'] = $msg;
  echo json_encode($returnJSON);

}

function saveSpecimenGroundtruth($specimen_pid, $groundtruth_dom) {
  $roi_obj = new roiHandler($specimen_pid);
  $result = $roi_obj->setDatastream("groundtruth", "Specimen+Ground+Truth", "text/xml", $groundtruth_dom->saveXML(), FEDORA_DATABASE_USERNAME.":".FEDORA_DATABASE_PASSWORD);
  if($result) {
    return true;
  }
  else {
    return false;
  }
}

function specimenMetadata_details_content($roi_pid = null) {
  global $base_url;
  $fedora_base_url = variable_get("fedora_base_url", "http://localhost:8080/fedora");
  $html = '';
  $html .= '<div id="specimenMetadata_details_content">'."\n";
  if($roi_pid != null && $roi_pid != "0") {
    $roi = new AP_ROI();
    $image_pid = $roi->get_image_pid($roi_pid);
    $sp_pid = AP_Image::get_specimen_pid($image_pid);
    $roiMetadata_record = $roi->getroiMetadata_record($roi_pid);
    $roiURL = $roiMetadata_record['roiURL'];
    $djatoka_url = scaleDjatokaURL($roiURL, '300', '0');
    $html .= "<h3>Details: $roi_pid</h3>";
    $html .= "<table><tr><td valign='top' width='300'>";
    $html .= "<img src='$djatoka_url'/></td>";
    $html .= "<td>Specimen: $sp_pid<br/>Image: $image_pid<br/>ROI: $roi_pid<br/>Datastream: specimenMetadata<br/><br/>";
    $text = shell_exec("curl -H - XGET $fedora_base_url/get/$roi_pid/specimenMetadata");
    $check=strpos($text, "404 Not Found");
    if($check === FALSE) {
      $specimenMetadata_xml_url = $server_base.'/drupal/modules/apiary_project/workflow/include/specimenMetadata_xml.php?pid='.$roi_pid;
      $html .= '<textarea style="width:500px; height:200px">'.$text.'</textarea></td>';
      $html .= "</tr></table>";
    }
    else {
      $html .= "No specimenMetadata found";
    }
  }
  else {
  }
  $html .= '</div><!-- specimenMetadata_details_content -->'."\n";
  echo $html;
}

?>