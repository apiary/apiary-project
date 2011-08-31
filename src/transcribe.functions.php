<?php
function process_ocr($workflow_id, $roi_pid, $ocr_type){
	global $user;
	$returnJSON = "";
	if(Workflow_Users::doesWorkflowHaveUserName($workflow_id, $user->name) && Workflow_Permission::doesWorkflowHavePermission($workflow_id, "canTranscribe")){
		$ap_roi = new AP_ROI();
		$roiMetadata_record = $ap_roi->getroiMetadata_record($roi_pid);
		$ocrURL = $roiMetadata_record['roiURL'];
		//$ocrURL = getDjatokaURL($roiMetadata_record['sourceURL'], 'getRegion', '100', $roiMetadata_record['y'], $roiMetadata_record['x'], $roiMetadata_record['h'], $roiMetadata_record['w'], '0', '0');
		$roi_obj = new roiHandler($roi_pid);
		$returnJSON['gocr'] = "";
		$returnJSON['ocrad'] = "";
		$returnJSON['ocropus'] = "";
		$curl_userpd = FEDORA_DATABASE_USERNAME.":".FEDORA_DATABASE_PASSWORD;
		$file_name = "/tmp/".str_replace(':', '_', $roi_pid).".jpg";
		if(!file_exists($file_name)){
			$command = "wget -O $file_name \"$ocrURL\"";
			shell_exec($command);
		}
		if($ocr_type == "all" || $ocr_type == "ocrad"){
			$ocrad_output = shell_exec("djpeg -pnm -gray $file_name | ocrad -");
			$ocrad_success = $roi_obj->setDatastream("ocrad", "OCRAD-result", "text/plain", $ocrad_output, $curl_userpd);
			if($ocrad_success) {
				$returnJSON['ocrad'] = $ocrad_output;
				$msg = "OCRAD";
			}
			else {
				$returnJSON['ocrad'] = "Error processing OCRAD.";
			}
		}
		if($ocr_type == "all" || $ocr_type == "ocropus"){
			$ocropus_output = shell_exec("ocropus page $file_name");
			$ocropus_success = $roi_obj->setDatastream("OCRopus", "OCRopus-result", "text/plain", $ocropus_output, $curl_userpd);
			if($ocropus_success) {
				$returnJSON['ocropus'] = $ocropus_output;
				if(strlen($msg) > 0) {
				  $msg .= ', ';
				}
				$msg .= "OCRopus";
			}
			else {
				$returnJSON['ocropus'] = "Error processing OCRopus.";
			}
		}
		if($ocr_type == "all" || $ocr_type == "gocr"){
			$gocr_output = shell_exec("gocr $file_name");
			$gocr_success = $roi_obj->setDatastream("GOCR", "GOCR-result", "text/plain", $gocr_output, $curl_userpd);
			if($gocr_success) {
				$returnJSON['gocr'] = $gocr_output;
				if(strlen($msg) > 0) {
				  $msg .= ', ';
				}
				$msg .= "GOCR";
			}
			else {
				$returnJSON['gocr'] = "Error processing GOCR.";
			}
		}
		if(file_exists("$file_name")){
			unlink($file_name);
		}
		if(strlen($msg) > 0) {
		  $msg .= ' processed for '.$roi_pid.'.';
		}
		else {
		  $msg .= 'Unable to process any OCR for '.$roi_pid.'.';
		}
	}
	else{
		$msg = "Sorry! You do not have permission for this operation";
	}
	$returnJSON['msg'] = $msg;
	echo json_encode($returnJSON);
}

function perform_ocr($roi_pid, $ocr_type, $workflow_id){
	global $user;
	if(Workflow_Users::doesWorkflowHaveUserName($workflow_id, $user->name) && Workflow_Permission::doesWorkflowHavePermission($workflow_id, "canTranscribe")){
		$ap_roi = new AP_ROI();
		$roiMetadata_record = $ap_roi->getroiMetadata_record($roi_pid);
		$ocrURL = $roiMetadata_record['roiURL'];
		//$ocrURL = getDjatokaURL($roiMetadata_record['sourceURL'], 'getRegion', '100', $roiMetadata_record['y'], $roiMetadata_record['x'], $roiMetadata_record['h'], $roiMetadata_record['w'], '0', '0');
		$roi_obj = new roiHandler($roi_pid);
		$returnjs = "";
		$curl_userpd = FEDORA_DATABASE_USERNAME.":".FEDORA_DATABASE_PASSWORD;
		$file_name = "/tmp/".str_replace(':', '_', $roi_pid).".jpg";
		if(!file_exists($file_name)){
			$command = "wget -O $file_name \"$ocrURL\"";
			shell_exec($command);
		}
		if($ocr_type == "all" || $ocr_type == "gocr"){
			$gocr_output = shell_exec("gocr $file_name");
			$gocr_success = $roi_obj->setDatastream("GOCR", "GOCR-result", "text/plain", $gocr_output, $curl_userpd);
		}
		if($ocr_type == "all" || $ocr_type == "ocrad"){
			$ocrad_output = shell_exec("djpeg -pnm -gray $file_name | ocrad -");
			$ocrad_success = $roi_obj->setDatastream("ocrad", "OCRAD-result", "text/plain", $ocrad_output, $curl_userpd);
		}
		if($ocr_type == "all" || $ocr_type == "ocropus"){
			$ocropus_output = shell_exec("ocropus page $file_name");
			$ocropus_success = $roi_obj->setDatastream("OCRopus", "OCRopus-result", "text/plain", $ocropus_output, $curl_userpd);
		}
		if(file_exists("$file_name")){
			unlink($file_name);
		}
	}
	else{
		$returnjs .= "jQuery.jGrowl('Sorry! You do not have permission for this operation');";
	}
	echo $returnjs;
}

function get_ocr_text($roi_pid, $nothing, $workflow_id){
	global $user;
	$returnjs = "";
	if(Workflow_Users::doesWorkflowHaveUserName($workflow_id, $user->name) && Workflow_Permission::doesWorkflowHavePermission($workflow_id, "canTranscribe")){
		$roi_obj = new roiHandler($roi_pid);
		$ocrs = array();
		$returnjs = "";
		if($roi_obj->ifExist("GOCR")){
			$ocrs['GOCR'] = $roi_obj->getDatastream("GOCR");
			$returnjs .= "jQuery.('#gocr').text('" . $ocrs['GOCR']."');";
		}
		if($roi_obj->ifExist("ocrad")){
			$ocrs['ocrad'] = $roi_obj->getDatastream("ocrad");
			$returnjs .= "jQuery.('#ocrad').text('" . $ocrs['ocrad']."');";
		}
		if($roi_obj->ifExist("OCRopus")){
			$ocrs['OCRopus'] = $roi_obj->getDatastream("OCRopus");
			$returnjs .= "jQuery.('#ocropus').text('" . $ocrs['OCRopus']."');";
		}
	}
	else{
		$returnjs .= "jQuery.jGrowl('Sorry! You do not have permission for this operation');";
	}
	echo $returnjs;
}

function save_transcribe_text($roi_pid, $nothing, $workflow_id){
	global $user;
	$returnjs = "";
	if(Workflow_Users::doesWorkflowHaveUserName($workflow_id, $user->name) && Workflow_Permission::doesWorkflowHavePermission($workflow_id, "canTranscribe")){
		$roi_obj = new roiHandler($roi_pid);
		$text = $_REQUEST['text'];
		$text = str_replace('&nbsp;', ' ', $text);
		$success = $roi_obj->setDatastream("Text", "Transcribed", "text/plain", $text, FEDORA_DATABASE_USERNAME.":".FEDORA_DATABASE_PASSWORD);
		if($success)
			$returnjs .= "\$.jGrowl('Trascribed Text for ROI [$roi_pid] saved successfully.');";
		else
			$returnjs .= "\$.jGrowl('Trascribed Text for ROI [$roi_pid] failed to save.');";
	}
	else{
		$returnjs .= "\$.jGrowl('Sorry! You do not have permission for this operation');";
	}
	echo $returnjs;
}

function get_transcribe_content($roi_pid, $size, $workflow_id){
	global $user;
	list($height, $width) = explode(":", $size);
	$returnJSON = "";
	if(Workflow_Users::doesWorkflowHaveUserName($workflow_id, $user->name) && Workflow_Permission::doesWorkflowHavePermission($workflow_id, "canTranscribe")){
		$roiMetadata_record = AP_ROI::getroiMetadata_record($roi_pid);
		$roiURL = $roiMetadata_record['roiURL'];
		if($height > (($roiMetadata_record['h']/ $roiMetadata_record['w']) * $width)){
			$roi_image_url = scaleDjatokaURL($roiURL, $width, '0');
			//$roi_image_url = getDjatokaURL($roiMetadata_record['sourceURL'], 'getRegion', '100', $roiMetadata_record['y'], $roiMetadata_record['x'], $roiMetadata_record['h'], $roiMetadata_record['w'], $width, '0');
			$returnJSON['image_html'] = "<img class='transcribe_roi_image' src='$roi_image_url' />";
		}
		else{
			$roi_image_url = scaleDjatokaURL($roiURL, '0', $height);
			//$roi_image_url = getDjatokaURL($roiMetadata_record['sourceURL'], 'getRegion', '100', $roiMetadata_record['y'], $roiMetadata_record['x'], $roiMetadata_record['h'], $roiMetadata_record['w'], '0', $height);
			$returnJSON['image_html'] = "<img class='transcribe_roi_image' src='$roi_image_url' />";
		}
		$roi_obj = new roiHandler($roi_pid);
		$returnJSON['gocr'] = "";
		$returnJSON['ocrad'] = "";
		$returnJSON['ocropus'] = "";
		$returnJSON['text'] = "";
		if($roi_obj->ifExist("GOCR")){
			$returnJSON['gocr'] = $roi_obj->getDatastream("GOCR");
		}
		if($roi_obj->ifExist("ocrad")){
			$returnJSON['ocrad'] = $roi_obj->getDatastream("ocrad");
		}
		if($roi_obj->ifExist("OCRopus")){
			$returnJSON['ocropus'] = $roi_obj->getDatastream("OCRopus");
		}
		if($roi_obj->ifExist("Text")){
			$returnJSON['text'] = $roi_obj->getDatastream("Text");
		}
	}
	else{
		echo "Sorry! You do not have permission for this operation";
	}
	echo json_encode($returnJSON);
}


function template($param1, $param2, $workflow_id){
	global $user;
	$returnjs = "";
	if(Workflow_Users::doesWorkflowHaveUserName($workflow_id, $user->name) && Workflow_Permission::doesWorkflowHavePermission($workflow_id, "canTranscribe")){

	}
	else{
		$returnjs .= "jQuery.jGrowl('Sorry! You do not have permission for this operation');";
	}
	echo $returnjs;
}
