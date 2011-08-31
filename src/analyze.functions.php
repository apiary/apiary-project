<?php
$rel_path = drupal_get_path('module','apiary_project');
/*include_once($rel_path.'/workflow/include/class.Workflow.php');
include_once($rel_path.'/workflow/include/class.Errorlog.php');
*/
include_once($rel_path.'/workflow/include/functions_error.php');
include_once($rel_path.'/workflow/include/functions_misc.php');
include_once($rel_path.'/workflow/include/db_utils.php');

function specimen_list($nothing, $nothing, $workflow_id){
	$workflow = new Workflow($workflow_id, true);
	$specimen_pids = $workflow->specimen_pids;
	$returnHTML = "";
	foreach ($specimen_pids as $specimen_pid){
		$specimen_id = str_replace(":","_",$specimen_pid);
	$returnHTML .= '<div class="widget-header" id="'.$specimen_id.'" onclick="toggle_specimen(\'content-'.$specimen_id.'\');">
	    <div class="widget-header-left  onclick="select_specimen(\''.$specimen_pid.'\')"">
    	    <div class="specimen_name">'. $specimen_pid.' - <span class="specimen_status">status</span></div>
    		<div class="specimen_header_detail">Contains <span id="roi_count_001">X ROIs</span> - <a href="#">analyze</a></div>
       	</div>
       	<div class="widget-header-right">
         	<div class="widget-control" id="control-'.$specimen_id.'"><img src="img/icon_closed.png"/></div>
       	</div>
    	<div class="clearfix" style="width:100%;"></div>
	</div><!-- widget-header -->
	<div class="widget-content" id="content-'.$specimen_id.'">';
	$image_pids = AP_Specimen::getImageListForSpecimen($specimen_pid);
	$roi_pids = AP_Image::getROIListForImage($image_pids[0]);
	foreach($roi_pids as $roi_pid){
		$returnHTML .= '<div class="widget-subheader">
    	    <div class="roi_name">ROI Name -'.$roi_pid.' <span class="roi_status" id="'. $specimen_pid.'_' . $roi_pid .'">status</span></div>
    	</div>
    	<div class="widget-subcontent">
    	    <div class="transcribe_link"><a href="#">Transcribe</a> - <span id="roi_001_transcribe_status">status</span></div>
    	    <div class="parse_link"><a href="#">Parse</a> - <span id="roi_001_parse_status">status</span></div>
    	</div>';
    }
	$returnHTML .= '</div><!-- widget-content -->'."\n";
	}
	echo $returnHTML;
}

function update_queue_list($nothing, $nothing, $workflow_id){
	global $user;
	$returnHTML = "";
	if(empty($_SESSION['apiary_session_id'])) {
		echo "Invalid Session";
		return;
	}
	else{
		$session_id = $_SESSION['apiary_session_id'];
	}
	$permission_map = get_permission_map($workflow_id);
	if(Workflow_Users::doesWorkflowHaveUserName($workflow_id, $user->name) && Workflow_Permission::doesWorkflowHavePermission($workflow_id, "canAnalyzeSpecimen")){
		list($roi_queue_list, $image_queue_list, $roi_counts) = get_queue_list($workflow_id);
		$specimens = $roi_queue_list;
		foreach($specimens as $specimen_pid => $images){
			foreach($images as $image_id => $rois){
				$specimen_id = str_replace(":","_",$specimen_pid);
				$showAll = false;$spHTMLDisplayed = false;
				$tempHTML = '<div class="widget-header" id="'.$specimen_id;
				if ( $permission_map['canAnalyzeSpecimen'] )
    				$tempHTML .= '" onclick="toggle_specimen(\'content-'.$specimen_id.'\');"';
				$tempHTML .= '>
			    <div class="widget-header-left  onclick="select_specimen(\''.$specimen_pid.'\')"">
		    	    <div class="specimen_name">'. $specimen_pid.'<span class="specimen_status">'.$image_queue_list[$specimen_pid][$image_id]["analyzedStatus"].'</span></div>
		    		<div class="specimen_header_detail">Contains <span id="roi_count_'.$specimen_id.'">'. $roi_counts[$specimen_pid]['total_rois'] .' ROIs</span>';
		        if ( $permission_map['canAnalyzeSpecimen'] )
    		    	$tempHTML .= ' - <a href="#">analyze</a>';
		    	$tempHTML .= '</div>
		       	</div>
		       	<div class="widget-header-right">
		         	<div class="widget-control" id="control-'.$specimen_id.'"><img src="img/icon_closed.png"/></div>
		       	</div>
		    	<div class="clearfix" style="width:100%;"></div>
				</div><!-- widget-header -->
				<div class="widget-content" id="content-'.$specimen_id.'">';
				if($image_queue_list[$specimen_pid][$image_id]['locked_session']==$session_id && $image_queue_list[$specimen_pid][$image_id]['workflow_status']=="queued"){
					$returnHTML .= $tempHTML;
					$showAll = true;
					$spHTMLDisplayed = true;
				}
				foreach($rois as $roi_pid => $status){
					$roi_id = str_replace(":","_",$roi_pid);
					if($showAll || ($roi_queue_list[$specimen_pid][$image_id][$roi_pid]['workflow_status'] == "queued" && $image_queue_list[$specimen_pid][$image_id][$roi_pid]['locked_session']==$session_id)){
						if(!$spHTMLDisplayed){
							$returnHTML .= $tempHTML;
							$spHTMLDisplayed = true;
						}
						$returnHTML .= '<div class="widget-subheader">
				    	    <div class="roi_name">ROI Name -'.$roi_pid.' <span class="roi_status" id="'. $specimen_id.'_' . $roi_id .'">['.$status["workflow_status"].']</span></div>
				    	</div>
				    	<div class="widget-subcontent">
				    	    <div class="transcribe_link"><a href="#">Transcribe</a> - <span id="roi_001_transcribe_status">'.$status["transcribedStatus"].'</span></div>
				    	    <div class="parse_link"><a href="#">Parse</a> - <span id="roi_001_parse_status">'.$status["parsedL1Status"].'</span></div>
				    	</div>';
					}
				}
			}
		}
		$returnHTML .= '</div><!-- widget-content -->'."\n";
	}
	echo $returnHTML;
}

function get_queue_list($workflow_id){
	global $user;
	$returnHTML = "";
	if(Workflow_Users::doesWorkflowHaveUserName($workflow_id, $user->name) && Workflow_Permission::doesWorkflowHavePermission($workflow_id, "canAnalyzeSpecimen")){
		$wf = new Workflow($workflow_id, true);
		$wf->loadWorkflowPools();
		$wDom = $wf->workflow_dom;
		$wDom = new DOMXPath($wDom);$i=0;
		$specimens = $wDom->query("//workflow/specimen");
		foreach ($specimens as $specimen){
			$sp_temp = $wDom->query("//workflow/specimen/pid");
			$specimen_pid = $sp_temp->item($i++)->nodeValue;
			$images = $wDom->query("//workflow/specimen[pid='$specimen_pid']/image");
			$j = 0;
			foreach($images as $image){
				$img_temp = $wDom->query("//workflow/specimen[pid='$specimen_pid']/image/pid");
				$image_pid = $img_temp->item($j++)->nodeValue;
				$rois = $wDom->query("//workflow/specimen[pid='$specimen_pid']/image[pid='$image_pid']/roi");
				if(!empty($image->getElementsByTagName('analyzedStatus')->item(0)->nodeValue))
					$image_queue_list[$specimen_pid][$image_pid]["analyzedStatus"] = $image->getElementsByTagName('analyzedStatus')->item(0)->nodeValue;
				else
					$image_queue_list[$specimen_pid][$image_pid]["analyzedStatus"] = "undefined";
				$image_queue_list[$specimen_pid][$image_pid]["workflow_status"] = $image->getElementsByTagName('workflow_status')->item(0)->nodeValue;
				$image_queue_list[$specimen_pid][$image_pid]["locked_session"] = $image->getElementsByTagName('locked_session')->item(0)->nodeValue;
				$k = 0;$roi_count=0;
				$total_rois = $rois->length;
				$roi_counts[$specimen_pid]["total_rois"] = $total_rois;
				if($total_rois == 0){
					$roi_queue_list[$specimen_pid][$image_pid] = $image_pid;
				}
				foreach($rois as $roi){
					$roi_temp = $wDom->query("//workflow/specimen[pid='$specimen_pid']/image[pid='$image_pid']/roi/pid");
					$roi_pid = $roi_temp->item($k++)->nodeValue;
					$queue_list[$specimen_pid][$image_pid][] = $roi_pid;
					$roi_queue_list[$specimen_pid][$image_pid][$roi_pid]["locked_session"] = $roi->getElementsByTagName('locked_session')->item(0)->nodeValue;
					if(!empty($roi->getElementsByTagName('workflow_status')->item(0)->nodeValue))
						$roi_queue_list[$specimen_pid][$image_pid][$roi_pid]["workflow_status"] = $roi->getElementsByTagName('workflow_status')->item(0)->nodeValue;
					else
						$roi_queue_list[$specimen_pid][$image_pid][$roi_pid]["workflow_status"] = "undefined";
					if(!empty($roi->getElementsByTagName('transcribedStatus')->item(0)->nodeValue))
						$roi_queue_list[$specimen_pid][$image_pid][$roi_pid]["transcribedStatus"] = $roi->getElementsByTagName('transcribedStatus')->item(0)->nodeValue;
					else
						$roi_queue_list[$specimen_pid][$image_pid][$roi_pid]["transcribedStatus"] = "undefined";
					if(!empty($roi->getElementsByTagName('transcribedStatus')->item(0)->nodeValue))
						$roi_queue_list[$specimen_pid][$image_pid][$roi_pid]["parsedL1Status"] = $roi->getElementsByTagName('parsedL1Status')->item(0)->nodeValue;
					else
						$roi_queue_list[$specimen_pid][$image_pid][$roi_pid]["parsedL1Status"] = "undefined";
				}
			}
		}
		return array($roi_queue_list, $image_queue_list, $roi_counts);
	}
}

function getImageROIList($image_pid, $nothing, $workflow_id){
	global $user;
	$returnHTML = "";
	if(Workflow_Users::doesWorkflowHaveUserName($workflow_id, $user->name) && Workflow_Permission::doesWorkflowHavePermission($workflow_id, "canAnalyzeSpecimen")){
		$roi_pids = AP_Image::getROIListForImage($image_pid);
		foreach($roi_pids as $roi_pid){
			$roiMetadata_record = AP_ROI::getroiMetadata_record($roi_pid);
			$roiURL = $roiMetadata_record['roiURL'];
			$changedHeight = thumbHeightReset($roiMetadata_record['h'], $roiMetadata_record['w']);
			if(!$changedHeight) {
			  if(strpos($roiURL, 'rft_id') > -1) {
			    $roi_thumb_url = scaleDjatokaURL($roiURL, '78', '0');
			  }
			  else {
			    $roi_thumb_url = getDjatokaURL($roiMetadata_record['sourceURL'], 'getRegion', '100', $roiMetadata_record['y'], $roiMetadata_record['x'], $roiMetadata_record['h'], $roiMetadata_record['w'], '78', '0');
			  }
			}
			else {
			  if(strpos($roiURL, 'rft_id') > -1) {
			    $next_amp = strpos($roiURL, '&', strpos($roiURL, 'rft_id'));
			    $rft_id = substr($roiURL, (strpos($roiURL, 'rft_id')+strlen('rft_id')+1), ($next_amp - (strpos($roiURL, 'rft_id')+strlen('rft_id')+1)));
			    $roi_thumb_url = getDjatokaURL($rft_id, 'getRegion', '100', $roiMetadata_record['y'], $roiMetadata_record['x'], $changedHeight , $roiMetadata_record['w'], '78', '0');
			  }
			  else {
			    $roi_thumb_url = getDjatokaURL($roiMetadata_record['sourceURL'], 'getRegion', '100', $roiMetadata_record['y'], $roiMetadata_record['x'], $changedHeight , $roiMetadata_record['w'], '78', '0');
			  }
		    }
			$status_dom = AP_ROI::getROIStatusDom($roi_pid);
			$transcribed_status = $status_dom->getElementsByTagName("transcribedStatus")->item(0)->nodeValue;
			$parsed_status = $status_dom->getElementsByTagName("parsedL1Status")->item(0)->nodeValue;
			if($transcribed_status == "")
				$transcribed_status = "Undefined";
			if($parsed_status == "")
				$parsed_status = "Undefined";
			$returnHTML .= generate_roi_html($roi_pid, $roi_thumb_url, $roiMetadata_record, $transcribed_status, $parsed_status, $workflow_id);
		}
	}
	else{
		//http_send_status(401);
		$returnHTML = "You do not have sufficient permission";
	}
	echo $returnHTML;
}

function getSpecimenROIList($specimen_pid, $workflow_id, $nothing){
	global $user;
	$returnHTML = "";
	if(Workflow_Users::doesWorkflowHaveUserName($workflow_id, $user->name) && Workflow_Permission::doesWorkflowHavePermission($workflow_id, "canAnalyzeSpecimen")){
		$image_pids = AP_Specimen::getImageListForSpecimen($specimen_pid);
		$roi_pids = AP_Image::getROIListForImage($image_pids[0]);
		foreach($roi_pids as $roi_pid){
			$roiMetadata_record = AP_ROI::getroiMetadata_record($roi_pid);
			$changedHeight = thumbHeightReset($roiMetadata_record['h'], $roiMetadata_record['w']);
			if(!$changedHeight)
				$roi_thumb_url = getDjatokaURL($roiMetadata_record['sourceURL'], 'getRegion', '100', $roiMetadata_record['y'], $roiMetadata_record['x'], $roiMetadata_record['h'], $roiMetadata_record['w'], '78', '0');
			else
				$roi_thumb_url = getDjatokaURL($roiMetadata_record['sourceURL'], 'getRegion', '100', $roiMetadata_record['y'], $roiMetadata_record['x'], $changedHeight , $roiMetadata_record['w'], '78', '0');
			$status_dom = AP_ROI::getROIStatusDom($roi_pid);
			$transcribed_status = $status_dom->getElementsByTagName("transcribedStatus")->item(0)->nodeValue;
			$parsed_status = $status_dom->getElementsByTagName("parsedL1Status")->item(0)->nodeValue;
			if($transcribed_status == "")
				$transcribed_status = "Undefined";
			if($parsed_status == "")
				$parsed_status = "Undefined";
			$returnHTML .= generate_roi_html($roi_pid, $roi_thumb_url, $roiMetadata_record, $transcribed_status, $parsed_status, $workflow_id);
		}
	}
	else{
		http_send_status(401);
		$returnHTML = "You do not have sufficient permission";
	}
	echo $returnHTML;
}

function get_roi_boxes($image_pid, $nothing, $workflow_id){
	global $user;
	$returnBoxes = "";
	if(Workflow_Users::doesWorkflowHaveUserName($workflow_id, $user->name) && Workflow_Permission::doesWorkflowHavePermission($workflow_id, "canAnalyzeSpecimen")){
		$roi_pids = AP_Image::getROIListForImage($image_pid);
		foreach($roi_pids as $roi_pid){
			$roiMetadata_record = AP_ROI::getroiMetadata_record($roi_pid);
			$returnBoxes[] = array("y"=>$roiMetadata_record['y'], "h"=>$roiMetadata_record['h'], "x"=>$roiMetadata_record['x'], "w"=>$roiMetadata_record['w'], "type"=>$roiMetadata_record['roiType'],"pid"=>$roi_pid);
		}
		echo json_encode($returnBoxes);
	}
	else{
		http_send_status(401);
		echo "You do not have sufficient permission";
	}
}

function remove_roi($roi_pid, $nothing, $workflow_id){
	global $user;
	$returnjs = "";
	if(Workflow_Users::doesWorkflowHaveUserName($workflow_id, $user->name) && Workflow_Permission::doesWorkflowHavePermission($workflow_id, "canAnalyzeSpecimen")){
		$roi_obj = new roiHandler($roi_pid);
		$success = $roi_obj->removeROI($roi_pid, FEDORA_DATABASE_USERNAME.":".FEDORA_DATABASE_PASSWORD);
		if($success) {
		  include_once(drupal_get_path('module', 'apiary_project') . '/workflow/include/search.php');
		  $search_instance = new search();
		  $search_instance->delete_index($roi_pid);
		  $returnjs .= "jQuery.jGrowl('ROI [$roi_pid] deleted successfully');";
		}
		else {
		  $returnjs .= "jQuery.jGrowl('ROI [$roi_pid] failed to delete');";
		  }
	}
	else{
		$returnjs .= "jQuery.jGrowl('Sorry! You do not have permission for this operation');";
	}
	echo $returnjs;
}

function create_roi($image_pid, $options, $workflow_id){
	global $user;

	if(Workflow_Users::doesWorkflowHaveUserName($workflow_id, $user->name) && Workflow_Permission::doesWorkflowHavePermission($workflow_id, "canAnalyzeSpecimen")){
		global $base_url;
		list($true_y1, $true_x1, $true_height, $true_width, $roi_type) = explode(":",$options);
		if($roi_type=="Annotation")
			$roi_type = "Annotation/Other";
		$roiMetadata_base_url = $base_url;
		$imageMetadata_record = AP_Image::getimageMetadata_record($image_pid);
		$parent_image_height = $imageMetadata_record["h"];//To Be Removed
		$parent_image_width = $imageMetadata_record["w"];//To Be Removed
		$sourceURL = $imageMetadata_record['URL'];//To Be Removed//The ROI sourceURL is the ap-image URL
		$parent_image_display_width = 650;//To Be Removed
		$parent_image_display_height = ($parent_image_height*$parent_image_display_width)/$parent_image_width;//To Be Removed
		$rft_id = $imageMetadata_record['rft_id'];
		$roiURL = getDjatokaURL($rft_id, 'getRegion', '100', $true_y1, $true_x1, $true_height, $true_width, '', '');
		$new_roi = new AP_ROI();
		$new_roi->roiMetadata_base_url = $roiMetadata_base_url;
		$status = $new_roi->createROIObject($image_pid, $sourceURL, $roiURL, $roi_type, $true_x1, $true_y1, $true_width, $true_height, $parent_image_width, $parent_image_height, $parent_image_display_width, $parent_image_display_height, "");
		$roi_pid = $new_roi->pid;
		$roiMetadata_record = AP_ROI::getroiMetadata_record($roi_pid);
		$changedHeight = thumbHeightReset($roiMetadata_record['h'], $roiMetadata_record['w']);
		if(!$changedHeight)
			$roi_thumb_url = getDjatokaURL($roiMetadata_record['sourceURL'], 'getRegion', '100', $roiMetadata_record['y'], $roiMetadata_record['x'], $roiMetadata_record['h'], $roiMetadata_record['w'], '78', '0');
		else
			$roi_thumb_url = getDjatokaURL($roiMetadata_record['sourceURL'], 'getRegion', '100', $roiMetadata_record['y'], $roiMetadata_record['x'], $changedHeight , $roiMetadata_record['w'], '78', '0');
		$status_dom = AP_ROI::getROIStatusDom($roi_pid);
		$transcribed_status = $status_dom->getElementsByTagName("transcribedStatus")->item(0)->nodeValue;
		$parsed_status = $status_dom->getElementsByTagName("parsedL1Status")->item(0)->nodeValue;
		if($transcribed_status == "")
			$transcribed_status = "Undefined";
		if($parsed_status == "")
			$parsed_status = "Undefined";
		$html = generate_roi_html($roi_pid, $roi_thumb_url, $roiMetadata_record, $transcribed_status, $parsed_status, $workflow_id);
		$returnHTML['html'] = $html;
		$returnHTML['pid'] = $roi_pid;
		$returnHTML['roi_queue_html'] = add_roi_to_queue($image_pid, $roi_pid, $workflow_id);
		if($status == true){
			//Echo JQuery function to add HTML for new ROI
		}
	}
	else{
		header('HTTP/1.1 401 Unauthorized');
		$returnHTML .= "Sorry! You do not have permission for this operation";
	}
	echo json_encode($returnHTML);
}

function generate_roi_html($roi_pid, $roi_thumb_url, $roiMetadata_record, $transcribe_status, $parsed_status, $workflow_id=null){
	$permission_map = get_permission_map($workflow_id);
	$roi_pid_replacement = str_replace(":", "_", $roi_pid);
	$html = '<div class="roi-section" id="'.$roi_pid.'-section" >
    	    					<div class="roi-preview" id="'.$roi_pid.'-preview">
    	        					<img src="'. $roi_thumb_url .'" />
    	    					</div>
    	    					<div class="roi-controls" id="'.$roi_pid.'-controls">
    	        					ROI Type:<span class="roi-type" id="'.$roi_pid_replacement.'-type">'.$roiMetadata_record["roiType"].'</span>
    	        					<a id="roi-type-edit-'.$roi_pid.'" href="javascript:editROIType(\''.$roi_pid.'\');">Edit</a><br/>';
    if ( $permission_map['canTranscribe'] )
        $html .= '<a href="#" onclick="transcribe_roi(\''.$roi_pid.'\');">Transcription:</a>';
    else
        $html .= 'Transcription:';
    $html .= '<span class="roi-transcription-status" id="'.$roi_pid.'-transcription-status">'.$transcribe_status.'</span><br/>';
    if ( $permission_map['canParseL1'] || $permission_map['canParseL2'] || $permission_map['canParseL3'] ) {
        //$html .= '<a href="#" onclick="parse_roi(\''.$roi_pid.'\', \'1\');">Parsing:</a>';
        $html .= '<a href="#" onclick="parse_roi(\''.$roi_pid.'\');">Parsing:</a>';
    }
    else {
        $html .= 'Parsing:';
    }
    $html .= '<span class="roi-parsing-status" id="'.$roi_pid.'parsing-status">'.$parsed_status.'</span>
    	        					<div class="clearfix"></div>';
    //$html .= '     					<div class="floatleft"><a href="javascript:delete_roi(\''.$roi_pid.'\')" class="delete-roi" id="'.$roi_pid.'-delete-roi">Delete ROI</a></div>';
    $html .= '     					<div class="floatleft"><a href="#" onclick="delete_roi(\''.$roi_pid.'\');" class="delete-roi" id="'.$roi_pid.'-delete-roi" >Delete ROI</a></div>';
    $html .= ' 					</div>
    						</div>';
	return $html;
}

function add_queue_roi_to_queue($image_pid, $roi_pid, $workflow_id) {
	echo add_roi_to_queue($image_pid, $roi_pid, $workflow_id);
}

function add_roi_to_queue($image_pid, $roi_pid, $workflow_id) {
  $queue_item_html = '';
  $status_dom = AP_ROI::getROILock($roi_pid);
  if($status_dom != false) {
    $transcribedStatus = $status_dom->getElementsByTagName('transcribedStatus')->item(0)->nodeValue;
    $parsedL1Status = $status_dom->getElementsByTagName('parsedL1Status')->item(0)->nodeValue;
    $locked = $status_dom->getElementsByTagName('locked')->item(0)->nodeValue;
    $locked_time = $status_dom->getElementsByTagName('locked_time')->item(0)->nodeValue;
    $locked_session = $status_dom->getElementsByTagName('locked_session')->item(0)->nodeValue;
    $workflow_status = workflow_status($locked, $locked_time, $locked_session);
    $queue_item_html .= create_queue_list_roi($roi_pid, $image_pid, $transcribedStatus, $parsedL1Status, $locked_by, $workflow_status, $workflow_id);
  }
  else {
    $queue_item_html = "locked";
  }
  return $queue_item_html;
}

function create_queue_list_image($image_pid, $specimen_pid, $analyzedStatus, $roi_count, $locked_by, $workflow_status,$workflow_id=null) {
  if(empty($analyzedStatus)) {
    $analyzedStatus = 'not started';
  }
  if($workflow_status == "queued") {
  	$queue_list_class = 'specimen_queued';
  }
  else if($workflow_status == "available") {
  	$queue_list_class = 'specimen_available';
  }
  else if($workflow_status == "locked") {
  	$queue_list_class = 'specimen_locked';
  }
  $permission_map = get_permission_map($workflow_id);
  $image_id_url = get_djatoka_url_by_height($image_pid, "100");
  $queue_list_image_html .= '<div class="widget-header '.$queue_list_class.'" id="'.str_replace(':', '__', $image_pid).'_queue">'."\n";
  $queue_list_image_html .= '<div class="widget-header-left"  ';
  if ( $permission_map['canAnalyzeSpecimen'] )
      $queue_list_image_html .= 'onclick="select_specimen(\''.$image_pid.'\')"';
  $queue_list_image_html .= '>'."\n";
  $queue_list_image_html .= '<div class="specimen_name" image_pid="'.$image_pid.'" image_id="'.$image_id_url.'" specimen_pid="'.$specimen_pid.'">Specimen: '. str_replace('ap-specimen:', '', $specimen_pid).'<span class="specimen_status"> - '.$analyzedStatus.'</span></div>'."\n";
  //$queue_list_image_html .= '<div class="specimen_header_detail">Contains <span id="roi_count_'.str_replace(':', '__', $image_pid).'">'. $roi_count .'</span> ROIs - <span class="specimen_header_utility"><a href="#" onclick="select_specimen(\''.$image_pid.'\')">analyze</a> <a href="#" onclick="remove_specimen(\''.$image_pid.'\')">remove</a></span></div>'."\n";
  $queue_list_image_html .= '<div class="specimen_header_detail">Contains <span id="roi_count_'.str_replace(':', '__', $image_pid).'">'. $roi_count .'</span> ROIs - <span class="specimen_header_utility">';
  if ( $permission_map['canAnalyzeSpecimen'] )
      $queue_list_image_html .= '<a href="#">analyze</a>';
  $queue_list_image_html .= ' </span></div>'."\n";
  $queue_list_image_html .= '</div>'."\n";
  $queue_list_image_html .= '<div class="widget-header-right">'."\n";
  $queue_list_image_html .= '<div class="widget-menu" id="menu-'.str_replace(':', '__', $image_pid).'" onclick="open_specimen_menu(\''.str_replace(':', '__', $image_pid).'\');"><img src="http://dev.apiaryproject.org/drupal/modules/apiary_project/workflow/assets/img/gear.png"/></div>'."\n";
  $queue_list_image_html .= '<div class="widget-control" id="control-'.str_replace(':', '__', $image_pid).'" onclick="toggle_specimen(\'content-'.str_replace(':', '__', $image_pid).'\');"><img src="http://dev.apiaryproject.org/drupal/modules/apiary_project/workflow/assets/img/icon_closed.png"/></div>'."\n";
  //$queue_list_image_html .= '<div class="widget-header-right" onclick="toggle_specimen(\'content-'.str_replace(':', '__', $image_pid).'\');">'."\n";
  //$queue_list_image_html .= '<div class="widget-control" id="control-'.str_replace(':', '__', $image_pid).'"><img src="assets/img/icon_closed.png"/></div>'."\n";
  $queue_list_image_html .= '</div>'."\n";
  $queue_list_image_html .= '</div><!-- widget-header -->'."\n";
  return $queue_list_image_html;
}

function create_queue_list_roi($roi_pid, $image_pid, $transcribedStatus, $parsedL1Status, $locked_by, $workflow_status, $workflow_id) {
  //default style is to be shown
  $transcribe_link_style = '';
  $parse_link_style = '';
  $queue_add_link_style = '';
  $queue_remove_link_style = '';
  $queue_locked_by_style = '';
  if($workflow_status == "queued") {
  	$queue_add_link_style = 'style="display:none;"';
  	$queue_locked_by_style = 'style="display:none;"';
  	$queue_list_class = 'roi_queued';
  }
  else if($workflow_status == "available") {
  	$transcribe_link_style = 'style="display:none;"';
  	$parse_link_style = 'style="display:none;"';
  	$queue_remove_link_style = 'style="display:none;"';
  	$queue_locked_by_style = 'style="display:none;"';
  	$queue_list_class = 'roi_available';
  }
  else if($workflow_status == "locked") {
  	$transcribe_link_style = 'style="display:none;"';
  	$parse_link_style = 'style="display:none;"';
  	$queue_add_link_style = 'style="display:none;"';
  	$queue_remove_link_style = 'style="display:none;"';
  	$queue_list_class = 'roi_locked';
  }
  if(empty($transcribedStatus)) {
    $transcribedStatus = 'not started';
  }
  if(empty($parsedL1Status)) {
    $parsedL1Status = 'not started';
  }
  $permission_map = get_permission_map($workflow_id);

  $queue_list_roi_html .= '<div id="'.str_replace(':', '__', $roi_pid).'_queue" class="'.$queue_list_class.'">'."\n";
  $queue_list_roi_html .= '<div class="widget-subheader">'."\n";
  $queue_list_roi_html .= '<div class="roi_name" roi_pid="'.$roi_pid.'" image_pid="'.$image_pid.'">Region: '.str_replace('ap-roi:', '', $roi_pid);
  $queue_list_roi_html .= '<span class="roi_status" id="'. str_replace(':', '__', $image_pid).'_' . $roi_pid .'_queue"> -- </span>';
  $queue_list_roi_html .= '<span class="queue_add_link" onclick="add_queue_roi_to_queue(\''.$image_pid.'\', \''.$roi_pid.'\')" '.$queue_add_link_style.'>not in queue <a href="#">add</a></span>';
  $queue_list_roi_html .= '<span class="queue_remove_link" onclick="remove_queue_item_from_queue(\''.$roi_pid.'\')" '.$queue_remove_link_style.'>in queue <a href="#">remove</a></span>'."\n";
  $queue_list_roi_html .= '<span class="queue_locked_by" '.$queue_locked_by_style.'>locked by '.$locked_by.'</span>'."\n";
  $queue_list_roi_html .= '</div><!-- roi-name -->'."\n";
  $queue_list_roi_html .= '</div><!-- widget-subheader -->'."\n";
  $queue_list_roi_html .= '<div class="widget-subcontent" id="widget-subcontent-'.str_replace(':', '__', $roi_pid).'">'."\n";
  if ( $permission_map['canTranscribe'] )
      $queue_list_roi_html .= '<div class="transcribe_link" onclick="transcribe_roi(\''.$roi_pid.'\');" '.$transcribe_link_style.'><a href="#">Transcribe</a> - <span class="transcribe_status" id="roi_001_transcribe_status">'.$transcribedStatus.'</span></div>'."\n";
  else
      $queue_list_roi_html .= '<div class="transcribe_link" '.$transcribe_link_style.'>Transcribe - <span class="transcribe_status" id="roi_001_transcribe_status">'.$transcribedStatus.'</span></div>'."\n";
  if ( $permission_map['canParseL1'] || $permission_map['canParseL2'] || $permission_map['canParseL3'] )
      $queue_list_roi_html .= '<div class="parse_link" onclick="parse_roi(\''.$roi_pid.'\')" '.$parse_link_style.'><a href="#">Parse</a> - <span class="parse_status" id="roi_001_parse_status">'.$parsedL1Status.'</span></div>'."\n";
  else
      $queue_list_roi_html .= '<div class="parse_link" '.$parse_link_style.'>Parse - <span class="parse_status" id="roi_001_parse_status">'.$parsedL1Status.'</span></div>'."\n";
  //$queue_list_roi_html .= '<div class="queue_add_link" onclick="add_queue_roi_to_queue(\''.$image_pid.'\', \''.$roi_pid.'\')" '.$queue_add_link_style.'><a href="#">Add to queue</a></div>'."\n";
  //$queue_list_roi_html .= '<div class="queue_remove_link" onclick="remove_queue_item_from_queue(\''.$roi_pid.'\')" '.$queue_remove_link_style.'><a href="#">Remove from queue</a></div>'."\n";
  //$queue_list_roi_html .= '<div class="queue_locked_by" '.$queue_locked_by_style.'>Locked by '.$locked_by.'</div>'."\n";
  $queue_list_roi_html .= '</div><!-- widget-subcontent -->'."\n";
  $queue_list_roi_html .= '</div><!-- '.str_replace(':', '__', $roi_pid).'_queue -->'."\n";
  return $queue_list_roi_html;
}

function get_djatoka_url_by_width($image_pid, $width) {
	$djatoka_display_url = '';
	$imageMetadata_record = AP_Image::getimageMetadata_record($image_pid);
	$rft_id = $imageMetadata_record['rft_id'];
	if($rft_id != '') {
	  $djatoka_display_url = getDjatokaURL($rft_id, 'getRegion', '3', '0', '0', $imageMetadata_record['h'], $imageMetadata_record['w'], $width, '0');
	}
	else {
	  $djatoka_display_url = getDjatokaURL($imageMetadata_record['URL'], 'getRegion', '3', '0', '0', $imageMetadata_record['h'], $imageMetadata_record['w'], $width, '0');
	}
	return $djatoka_display_url;
}

function get_djatoka_url_by_height($image_pid, $height) {
	$djatoka_display_url = '';
	$imageMetadata_record = AP_Image::getimageMetadata_record($image_pid);
	$rft_id = $imageMetadata_record['rft_id'];
	if($rft_id != '') {
	  $djatoka_display_url = getDjatokaURL($rft_id, 'getRegion', '3', '0', '0', $imageMetadata_record['h'], $imageMetadata_record['w'], '0', $height);
	}
	else {
	  $djatoka_display_url = getDjatokaURL($imageMetadata_record['URL'], 'getRegion', '3', '0', '0', $imageMetadata_record['h'], $imageMetadata_record['w'], '0', $height);
	}
	return $djatoka_display_url;
}

function get_images_list($specimen_pid, $nothing, $workflow_id){
	global $user;
	if(Workflow_Users::doesWorkflowHaveUserName($workflow_id, $user->name) && Workflow_Permission::doesWorkflowHavePermission($workflow_id, "canAnalyzeSpecimen")){
		$image_pids = AP_Specimen::getImageListForSpecimen($specimen_pid);
		echo json_encode($image_pids);
	}
}

function thumbHeightReset($height, $width){
	if($width/$height < 78/54){
		$y = ($width * 54)/78;
		return round($y);
	}
	else
		return false;
}

function get_specimen_pid($image_pid, $nothing, $workflow_id){
	$returnJSON[] = AP_Image::get_specimen_pid($image_pid);
	echo json_encode($returnJSON);
}

function change_roi_type($roi_pid, $roi_type){
	if($roi_type=="Annotation")
			$roi_type = "Annotation/Other";
	$roi = new roiHandler($roi_pid);
	$roiMetadata = $roi->getDatastream("roiMetadata");
	$dom = new DOMDocument();
	$dom->loadXML($roiMetadata);
	$dom->getElementsByTagName("roiType")->item(0)->nodeValue = $roi_type;
	$result = $roi->setDatastream("roiMetadata", "roi_Metadata", "text/xml", $dom->saveXML(), FEDORA_DATABASE_USERNAME.":".FEDORA_DATABASE_PASSWORD);
	if($result){
		echo "Success";
	}else{
		echo "Failed";
	}
}

function get_permission_map($workflow_id) {
    $selected_permission_list = Workflow::getPermissionList($workflow_id);
    if ( is_array($selected_permission_list) )
    {
        foreach($selected_permission_list as $permission_string)
        {
            $permission_map[$permission_string] = true;
        }
    }
    return $permission_map;
}
