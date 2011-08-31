<?php
$path = getcwd();
include_once(drupal_get_path('module', 'apiary_project') . '/workflow/include/class.Errorlog.php');

function get_parse_elements($roi_pid, $parse_level, $workflow_id){
	global $user;
	$accordian = "";
	$roiMetadata_record = AP_ROI::getroiMetadata_record($roi_pid);
	$type = $roiMetadata_record['roiType'];
	$parse_level_num = '0';
	if(strpos($parse_level, 'canParseL1') > -1) {
	  $parse_level_num = '1';
	}
	else if(strpos($parse_level, 'canParseL2') > -1) {
	  $parse_level_num = '2';
	}
	else if(strpos($parse_level, 'canParseL3') > -1) {
	  $parse_level_num = '3';
	}
	switch($type){
		case "Primary Label":		$level = "parse".$parse_level_num."-primaryLabel";break;
		case "Annotation/Other":	$level = "parse".$parse_level_num."-annotationLabel";break;
		case "Barcode":				$level = "parse".$parse_level_num."-barcodeLabel";break;
		case "Undefined":			$level = "parse".$parse_level_num."-noLabel";break;
		default: 					$level = "parse".$parse_level_num."-noLabel";break;
	}
	if(Workflow_Users::doesWorkflowHaveUserName($workflow_id, $user->name) && Workflow_Permission::doesWorkflowHavePermission($workflow_id, $parse_level)) {
      $roi_obj = new roiHandler($roi_pid);
      $specimen_metaData_elements = array();
      if($roi_obj->parseXMLExist){
        $span_text = $roi_obj->getDatastream("Text");
        $xml = new DOMDocument;
        $xml->loadXML($span_text);
        $span_elements = $xml->getElementsByTagName("span");
        foreach($span_elements as $span_element) {
        	$class = $span_element->getAttribute("class");
        	$value = (string)$span_element->nodeValue;
        	//echo "value = ".$value."<br>\n";
        	if($specimen_metaData_elements[$class] != null && $specimen_metaData_elements[$class] != "") {
        	  $specimen_metaData_elements[$class] = $specimen_metaData_elements[$class]." ; ".$value;
        	}
        	else {
        	  $specimen_metaData_elements[$class] = $value;
        	}
        }
      }
		$dom = new DOMDocument();
		$parse_elements_template = drupal_get_path('module', 'apiary_project') . "/workflow/assets/xml/parse_elements_template.xml";
		$dom->load($parse_elements_template);
		$xpath = new DOMXPath( $dom);
		$groups = $xpath->query("//apiary_ui/ui[@id='$level']/group");
		$accordian .= '		<div class="widget-title">
	    <div class="widget-title-help-button floatright" onclick="open_popup(\'help_parse_controls.html\');">
	        &nbsp;
        </div><!-- .widget-title-help-button -->
        <h3>Parse</h3>
        <div class="widget-title-content">
        </div>
        </div>
        <div id="parse-control-content" style="">
        <ul id="parse-treeview" class="treeview-gray">';
		$accordian .= "<form name='parse_text_elements' id='parse_text_form'>";
		$count = 0;
    	foreach($groups as $key=>$group){
    	    ++$count;
    	}
    	foreach($groups as $key=>$group){
			$id = $group->getAttribute('id');
			$accordian .= '<li>
			<span>'.$group->getAttribute('label')."</span>
			<ul>\n";

		    $elements = $xpath->query("//apiary_ui/ui[@id='$level']/group[@id='$id']/item");
		    $ecount = 0;
			foreach($elements as $element){
			    ++$ecount;
			}
			$element_count = 0;
			foreach($elements as $ekey=>$element){
			  $element_class = $element->getAttribute("name");
				if($specimen_metaData_elements[$element_class] != null && $specimen_metaData_elements[$element_class] != '') {
				  $val = (string)$specimen_metaData_elements[$element_class];
				}
				else {
				  $val = "";
				}
				$accordian .= "<li>" . $element->getAttribute("label").': ';
				if($element->getAttribute("inputType") == "text" || $element->getAttribute("inputType") == "textarea" || $element->getAttribute("inputType") == "list")
				{
					//$accordian .= "<input type='text' id='" . $element->getAttribute("name") . "' name='". $element->getAttribute("name") . "' value='".$val."'/>";
					$accordian .= "<span type='text' id='" . $element->getAttribute("name") . "' name='". $element->getAttribute("name") . "' >".$val."</span>";
				}
				$accordian .= "</li>";
			}
		    $accordian .= '</ul>
		    </li>';
		}
		$accordian .= "</form></ul><div id=\"divPush\"></div></div><div id=\"parse_buttons\">";
		$accordian .= "<button class=\"customBtn\" onclick='save_parse_text();'><span>Save Parsed Text</span></button>";
		$accordian .= "<button class=\"customBtn\" onclick='find_taxa_via_ubio();'><span>Find taxa via uBio</span></button>";
		$accordian .= "<button class=\"customBtn\" onclick='herbis_nlp();' ><span>Parse using HERBIS</span></button></div>";
		return $accordian;
	}
	else{
		return false;
	}
}

function get_parse_elements_specimenMetadata($roi_pid, $parse_level, $workflow_id){
	global $user;
	$accordian = "";
	$roiMetadata_record = AP_ROI::getroiMetadata_record($roi_pid);
	$type = $roiMetadata_record['roiType'];
	$parse_level_num = '0';
	if(strpos($parse_level, 'canParseL1') > -1) {
	  $parse_level_num = '1';
	}
	else if(strpos($parse_level, 'canParseL2') > -1) {
	  $parse_level_num = '2';
	}
	else if(strpos($parse_level, 'canParseL3') > -1) {
	  $parse_level_num = '3';
	}
	switch($type){
		case "Primary Label":		$level = "parse".$parse_level_num."-primaryLabel";break;
		case "Annotation/Other":	$level = "parse".$parse_level_num."-annotationLabel";break;
		case "Barcode":				$level = "parse".$parse_level_num."-barcodeLabel";break;
		case "Undefined":			$level = "parse".$parse_level_num."-noLabel";break;
		default: 					$level = "parse".$parse_level_num."-noLabel";break;
	}
	if(Workflow_Users::doesWorkflowHaveUserName($workflow_id, $user->name) && Workflow_Permission::doesWorkflowHavePermission($workflow_id, $parse_level)){
		$roi_obj = new roiHandler($roi_pid);
		if($roi_obj->ifExist("specimenMetadata")){
			$sp_metadata = $roi_obj->getDatastream("specimenMetadata");
			$xml = new DOMDocument;
			$xml->loadXML($sp_metadata);
		}
		$dom = new DOMDocument();
		$parse_elements_template = drupal_get_path('module', 'apiary_project') . "/workflow/assets/xml/parse_elements_template.xml";
		$dom->load($parse_elements_template);
		$xpath = new DOMXPath( $dom);
		$groups = $xpath->query("//apiary_ui/ui[@id='$level']/group");
		$accordian .= '		<div class="widget-title">
	    <div class="widget-title-help-button floatright" onclick="open_popup(\'help_parse_controls.html\');">
	        &nbsp;
        </div><!-- .widget-title-help-button -->
        <h3>Parse</h3>
        <div class="widget-title-content">
        </div>
        </div>
        <div id="parse-control-content" style="">
        <ul id="parse-treeview" class="treeview-gray">';
		$accordian .= "<form name='parse_text_elements' id='parse_text_form'>";
		$count = 0;
    	foreach($groups as $key=>$group){
    	    ++$count;
    	}
    	foreach($groups as $key=>$group){
			$id = $group->getAttribute('id');
			$accordian .= '<li>
			<span>'.$group->getAttribute('label')."</span>
			<ul>\n";

		    $elements = $xpath->query("//apiary_ui/ui[@id='$level']/group[@id='$id']/item");
		    $ecount = 0;
			foreach($elements as $element){
			    ++$ecount;
			}
			$element_count = 0;
			foreach($elements as $ekey=>$element){
				if($roi_obj->parseXMLExist)
					$val = $xml->getElementsByTagName($element->getAttribute("name"))->item(0)->nodeValue;
				else
					$val = "";
				$accordian .= "<li>" . $element->getAttribute("label").': ';
				//if($element->getAttribute("inputType") == "textarea"){
				//	$accordian .= "<textarea cols='" . $element->getAttribute('cols') .  "' rows= '" . $element->getAttribute('rows') . "' name='". $element->getAttribute("name") . "' id='" . $element->getAttribute('name') . "'>".$val."</textarea>";
				//}
				if($element->getAttribute("inputType") == "text" || $element->getAttribute("inputType") == "textarea" || $element->getAttribute("inputType") == "list")
				{
					//$accordian .= "<input type='text' id='" . $element->getAttribute("name") . "' name='". $element->getAttribute("name") . "' value='".$val."'/>";
					$accordian .= "<span type='text' id='" . $element->getAttribute("name") . "' name='". $element->getAttribute("name") . "' >".$val."</span>";
				}
				/*if($element->getAttribute("inputType") == "list"){
					$accordian .= "<select id='" . $element->getAttribute('name') . "' name='" . $element->getAttribute('name') . "'>";
					$options = $element->getAttribute("options");
					$options = explode("|", $options);
					$accordian .= "<option value=''></option>";
					foreach($options as $option)
						$accordian .= "<option value='". trim($option);
						if($option==$val || trim($option)==$val) $accordian .= " selected='selected'";
						$accordian .= " '>". trim($option) . "</option>";
					$accordian .= "</select>";
				}*/
				$accordian .= "</li>";
			}
		    $accordian .= '</ul>
		    </li>';
		}
		$accordian .= "</form></ul><div id=\"divPush\"></div></div><div id=\"parse_buttons\">";
		$accordian .= "<button class=\"customBtn\" onclick='save_parse_text();'><span>Save Parsed Text</span></button>";
		$accordian .= "<button class=\"customBtn\" onclick='find_taxa_via_ubio();'><span>Find taxa via uBio</span></button>";
		$accordian .= "<button class=\"customBtn\" onclick='herbis_nlp();' ><span>Parse using HERBIS</span></button></div>";
		return $accordian;
	}
	else{
		return false;
	}
}

function get_parse_level_elements($roi_pid, $workflow_id, $parse_level){
	global $user;
	$roiMetadata_record = AP_ROI::getroiMetadata_record($roi_pid);
	$type = $roiMetadata_record['roiType'];
    $whowhenNames = '';
    $whowhenLabels = '';
    $whatNames = '';
    $whatLabels = '';
    $whereNames = '';
    $whereLabels = '';
	$parse_level_num = '0';
	if(strpos($parse_level, 'canParseL1') > -1) {
	  $parse_level_num = '1';
	}
	else if(strpos($parse_level, 'canParseL2') > -1) {
	  $parse_level_num = '2';
	}
	else if(strpos($parse_level, 'canParseL3') > -1) {
	  $parse_level_num = '3';
	}
	switch($type){
		case "Primary Label":		$level = "parse".$parse_level_num."-primaryLabel";break;
		case "Annotation/Other":	$level = "parse".$parse_level_num."-annotationLabel";break;
		case "Barcode":				$level = "parse".$parse_level_num."-barcodeLabel";break;
		case "Undefined":			$level = "parse".$parse_level_num."-noLabel";break;
		default: 					$level = "parse".$parse_level_num."-noLabel";break;
	}
	if(Workflow_Users::doesWorkflowHaveUserName($workflow_id, $user->name) && Workflow_Permission::doesWorkflowHavePermission($workflow_id, $parse_level)){
		$dom = new DOMDocument();
		$parse_elements_template = drupal_get_path('module', 'apiary_project') . "/workflow/assets/xml/parse_elements_template.xml";
		$dom->load($parse_elements_template);
		$xpath = new DOMXPath($dom);
		$groups = $xpath->query("//apiary_ui/ui[@id='$level']/group");
    	foreach($groups as $key=>$group){
			$id = $group->getAttribute('id');
			$element_category = $group->getAttribute('label');
		    $elements = $xpath->query("//apiary_ui/ui[@id='$level']/group[@id='$id']/item");
			foreach($elements as $ekey=>$element){
			  $element_name = $element->getAttribute("name");
			  $element_label = $element->getAttribute("label");
			  if($element_category == "Who and When") {
			    if(strlen($whowhenNames) > 0) {
			      $whowhenNames .= ',';
			      $whowhenLabels .= ',';
			    }
			    $whowhenNames .= $element_name;
			    $whowhenLabels .= $element_label;
			  }
			  else if($element_category == "What") {
			    if(strlen($whatNames) > 0) {
			      $whatNames .= ',';
			      $whatLabels .= ',';
			    }
			    $whatNames .= $element_name;
			    $whatLabels .= $element_label;
			  }
			  else if($element_category == "Where") {
			    if(strlen($whereNames) > 0) {
			      $whereNames .= ',';
			      $whereLabels .= ',';
			    }
			    $whereNames .= $element_name;
			    $whereLabels .= $element_label;
			  }
			}
		}
		return array($whowhenNames, $whowhenLabels, $whatNames, $whatLabels, $whereNames, $whereLabels);
	}
	else{
		return false;
	}
}

function parse_elements($roi_pid, $parse_level, $workflow_id){
	global $user;
	$returnJSON = "";
	if(Workflow_Users::doesWorkflowHaveUserName($workflow_id, $user->name) && Workflow_Permission::doesWorkflowHavePermission($workflow_id, $parse_level)){
		$accordian = get_parse_elements($roi_pid, $parse_level, $workflow_id);
		$returnJSON['accordian'] = $accordian;
	}
	else{
		echo "Sorry! You do not have permission for this operation";
	}
	echo json_encode($returnJSON);
}

function parsing_content($roi_pid, $size, $parse_level){
	global $user;
	$workflow_id = getWorkflowIdFromSessionId($_SESSION['apiary_session_id']);
	$returnJSON = "";
	if(Workflow_Users::doesWorkflowHaveUserName($workflow_id, $user->name) && Workflow_Permission::doesWorkflowHavePermission($workflow_id, $parse_level)){
		list($image_html, $text) = get_parsing_content($roi_pid, $size, $parse_level, $workflow_id);
		$returnJSON['image_html'] = $image_html;
		$returnJSON['text'] = $text;
	}
	else{
		echo "Sorry! You do not have permission for this operation";
	}
	echo json_encode($returnJSON);
}

function get_parsing_content($roi_pid, $size, $parse_level, $workflow_id){
	global $user;
	list($height, $width) = explode(":", $size);
	if(Workflow_Users::doesWorkflowHaveUserName($workflow_id, $user->name) && Workflow_Permission::doesWorkflowHavePermission($workflow_id, $parse_level)){
		$roiMetadata_record = AP_ROI::getroiMetadata_record($roi_pid);
		$roiURL = $roiMetadata_record['roiURL'];
		if($height > (($roiMetadata_record['h']/ $roiMetadata_record['w']) * $width)){
			$roi_image_url = scaleDjatokaURL($roiURL, $width, '0');
			$image_html = "<img class='transcribe_roi_image' src='$roi_image_url' />";
		}
		else{
			$roi_image_url = scaleDjatokaURL($roiURL, '0', $height);
			$image_html = "<img class='transcribe_roi_image' src='$roi_image_url' />";
		}
		$roi_obj = new roiHandler($roi_pid);
		$returnJSON['text'] = "";
		if($roi_obj->ifExist("Text")){
			$text = nl2br($roi_obj->getDatastream("Text"));
		}

	}
	else{
		return false;
	}
	return array($image_html, $text);
}

function save_parsed_text($roi_pid, $nothing, $workflow_id){
	global $user;
	$returnjs = "";
	if(Workflow_Users::doesWorkflowHaveUserName($workflow_id, $user->name) && Workflow_Permission::doesWorkflowHavePermission($workflow_id, "canTranscribe")){
		$query_string = $_SERVER['QUERY_STRING'];
		log_to_db($_SERVER['QUERY_STRING'],'save_parsed_text QUERY_STRING');
		log_to_db($_REQUEST['specimenMetadata'],'save_parsed_text specimenMetadata');
		$specimenMetadata = $_REQUEST['specimenMetadata'];
		$file_path = drupal_get_path('module', 'apiary_project') . "/workflow/assets/xml/metadata_template.xml";
		$doc = new DOMDocument();
		$doc->load($file_path);
		$pairs = explode('&', $specimenMetadata);
		foreach($pairs as $pair){
			if(!empty($pair)){
				list($name, $value) = explode("=", $pair, 2);
				if($name != "q"){
					$element = $doc->getElementsByTagName($name)->item(0);
					$element->nodeValue = urldecode($value);
				}
			}
		}
		$roi_obj = new roiHandler($roi_pid);
		log_to_db($doc->saveXML(),'save_parsed_text doc_saveXML');
		$success = $roi_obj->setDatastream("specimenMetadata", "Label-Information", "text/xml", $doc->saveXML(), FEDORA_DATABASE_USERNAME.":".FEDORA_DATABASE_PASSWORD);
		log_to_db($_REQUEST['text'],'save_parsed_text text');
		$success = $roi_obj->setDatastream("Text", "Parsed", "text/plain", $_POST['text'], FEDORA_DATABASE_USERNAME.":".FEDORA_DATABASE_PASSWORD);
        if($success) {
          $solr_search = new search();
          $solr_search->index($roi_pid);
          $returnjs .= "\$.jGrowl('Parsed text for ROI [$roi_pid] saved successfully.');";
        }
        else {
          $returnjs .= "\$.jGrowl('Parsed text for ROI [$roi_pid] failed to save.');";
        }
	}
	else{
		$returnjs .= "\$.jGrowl('Sorry! You do not have permission for this operation');";
	}
	echo $returnjs;
}

function reload_transcribe_text($roi_pid, $nothing, $workflow_id){
  global $user;
  $returnHTML = "";
  if(Workflow_Users::doesWorkflowHaveUserName($workflow_id, $user->name) && Workflow_Permission::doesWorkflowHavePermission($workflow_id, "canTranscribe")){
    $roi_obj = new roiHandler($roi_pid);
    if($roi_obj->ifExist("Text")){
      $returnHTML = nl2br($roi_obj->getDatastream("Text"));
    }
  }
  echo $returnHTML;
}

function ubio_parse($roi_pid) {
  $returnJSON = "";
  $roi_obj = new roiHandler($roi_pid);
  $ubio_result = $roi_obj->get_ubio();
  //$returnJSON['ubio_result'] = '<div id="ubio_results"><textarea class="ubio"><pre>'.$ubio_result.'</pre></textarea></div>';
  $returnJSON['ubio_result'] = '<div id="ubio_results"><pre>'.$ubio_result.'</pre></div>';
  echo json_encode($returnJSON);
}

function herbis_parse_nonJSON($roi_pid) {
  $apiary_project_herbis_dir = variable_get('apiary_project_herbis_dir', '/var/www/drupal/modules/apiary_project/herbis');
  $apiary_project_herbis_url = variable_get('apiary_project_herbis_url', 'http://txcdk3g.unt.edu:8080/HERBIS');
  $returnJSON = "";
  $roi_obj = new roiHandler($roi_pid);
  $herbis_result = $roi_obj->get_herbis($apiary_project_herbis_dir, $apiary_project_herbis_url);
  echo $herbis_result;
  //$returnJSON['herbis_result'] = $herbis_result;
  //echo json_encode($returnJSON);
}

function herbis_parse($roi_pid) {
  $apiary_project_herbis_dir = variable_get('apiary_project_herbis_dir', '/var/www/drupal/modules/apiary_project/herbis');
  $apiary_project_herbis_url = variable_get('apiary_project_herbis_url', 'http://txcdk3g.unt.edu:8080/HERBIS');
  $returnJSON = "";
  $roi_obj = new roiHandler($roi_pid);
  $herbis_result = $roi_obj->get_herbis($apiary_project_herbis_dir, $apiary_project_herbis_url);
  //echo $herbis_result;
  $returnJSON['herbis_result'] = $herbis_result;
  echo json_encode($returnJSON);
}

function parse_tab_nav($roi_pid, $size, $parse_level = null) {
  $parse_tab_nav = '';
  $current_parse_level = '0';
  $msg = '';
  $returnJSON = "";
  $workflow_id = getWorkflowIdFromSessionId($_SESSION['apiary_session_id']);
  if($roi_pid == 1) {
    $parse_tab_nav = get_empty_parse_tab_nav();
  }
  else {
    $parse_levels = Workflow_Permission::getParseLevelList($workflow_id);
    if(sizeOf($parse_levels) > 0) {
      if($parse_level == null || $parse_level == '0' || $parse_level == '') {
	    $parse_level = (string)$parse_levels[0];
      }
      if(array_search($parse_level, $parse_levels) > -1) {
        $current_parse_level = $parse_level;
        $parse_tab_nav_array = get_parse_tab_nav($roi_pid, $size, $workflow_id, $parse_levels, $parse_level);
        foreach ($parse_tab_nav_array as $thing => $value) {
          $returnJSON[$thing] = $value;
        }
      }
      else {
        $msg = 'This workflow does not have permission to parse at the level requested: '.$parse_level.'.';
      }
    }
    else {
      $msg = 'This workflow does not have permission to parse at any level.';
    }
  }
  $returnJSON['current_parse_level'] = $current_parse_level;
  $returnJSON['msg'] = $msg;
  echo json_encode($returnJSON);
}

function get_parse_tab_nav($roi_pid, $size, $workflow_id, $parse_levels, $parse_level) {
  if($parse_level == "canParseL1") {
    return get_canParseL1_tab_nav($roi_pid, $size, $workflow_id, $parse_levels, $parse_level);//using the tinyMCE as our default now
  }
  else if($parse_level == "canParseL2") {
    return get_canParseL1_tab_nav($roi_pid, $size, $workflow_id, $parse_levels, $parse_level);
  }
  else if($parse_level == "canParseL3") {
    return get_canParseL1_tab_nav($roi_pid, $size, $workflow_id, $parse_levels, $parse_level);
  }
}

function get_canParseL1_tab_nav($roi_pid, $size, $workflow_id, $parse_levels, $parse_level) {
  list($image_html, $text) = get_parsing_content($roi_pid, $size, $parse_level, $workflow_id);
  $accordian = get_parse_elements($roi_pid, $parse_level, $workflow_id);//we only need the accordian here
  $parse_tab_nav = '<div id="parse_tab_nav" class="tab1">
        <div class="tab_nav">
        <ul class="breadcrumbs">
        <li class="tab1 first" onclick="select_tab(\'parse_tab_nav\',\'tab1\',\'parse_tab1_section\');">Text<span></span></li>
        <li class="tab2" onclick="select_tab(\'parse_tab_nav\',\'tab2\',\'parse_tab2_section\');">Image<span></span></li>
  ';
  $parse_tab_nav .= '      <li class="info info2 last" onclick="open_popup(\'help_parse.html\');">?<span></span></li>';
/* Enable this FROM HERE to allow linked switching in the UI from one parse level to another. Change the slash slash to disable.
  for($i = sizeOf($parse_levels); $i > 0; $i--) {
    $parse_levels_level = (string)$parse_levels[($i-1)];
    $level = str_replace('can', '', $parse_levels_level);
    $parse_tab_nav .= '      <li class="parselevel '.strtolower($level).'';
    if($parse_levels_level == $parse_level) {
      $parse_tab_nav .= ' selected';
    }
    $parse_tab_nav .= '" onclick="select_parse_level(\''.$parse_levels_level.'\');">'.$level.'<span></span></li>';
  }
//  Enable this TO HERE to allow linked switching from one parse level to another
*/ //put your asterisk slash here when you enable to comment out the Enable text in the line above 8smileyD ... yes this is a smiley face.
  $parse_tab_nav .= '      </ul>
        <span>&nbsp;</span>
        </div>';
  $parse_tab_nav .= '        <div id="parse_tab1_section" class="tab_section center-layout" style="text-align:left;padding-left:20px;">';
  $parse_tab_nav .= get_canParseL1_tab1_section($text);
  $parse_tab_nav .= '        </div><!-- #parse_tab1_section -->';
  $parse_tab_nav .= '        <div id="parse_tab2_section" class="tab_section">';
  $parse_tab_nav .= $image_html;
  $parse_tab_nav .= '
        </div><!-- #parse_tab2_section -->
        </div> <!-- #parse_tab_nav -->
  ';
  list($whowhenNames, $whowhenLabels, $whatNames, $whatLabels, $whereNames, $whereLabels) = get_parse_level_elements($roi_pid, $workflow_id, $parse_level);
  return array("parse_tab_nav"=>$parse_tab_nav, "accordian"=>$accordian, "whowhenNames"=>$whowhenNames, "whowhenLabels"=>$whowhenLabels, "whatNames"=>$whatNames, "whatLabels"=>$whatLabels, "whereNames"=>$whereNames, "whereLabels"=>$whereLabels);
}

function get_canParseL1_tab1_section($text) {
  $canParseL1_tab1_section = '';
  $canParseL1_tab1_section .= '<!-- Gets replaced with TinyMCE, remember HTML in a textarea should be encoded -->';
  $canParseL1_tab1_section .= '	<textarea id="parse_textarea" name="parse_textarea" rows="15" cols="80" style="width: 80%">';
  $canParseL1_tab1_section .= $text;
  $canParseL1_tab1_section .= '	</textarea>';
  return $canParseL1_tab1_section;
}

function get_empty_parse_tab_nav() {
  $parse_tab_nav = '<div id="parse_tab_nav" class="tab1">
        <div class="tab_nav">
        <ul class="breadcrumbs">
        <li class="tab1 first" onclick="select_tab(\'parse_tab_nav\',\'tab1\',\'parse_tab1_section\');">Text<span></span></li>
        <li class="tab2" onclick="select_tab(\'parse_tab_nav\',\'tab2\',\'parse_tab2_section\');">Image<span></span></li>
        <li class="info info2 last" onclick="open_popup(\'help_parse.html\');">?<span></span></li>
        </ul>
        <span>&nbsp;</span>
        </div>
        <div id="parse_tab1_section" class="tab_section center-layout" style="text-align:left;padding-left:20px;">
            <div class="roundedcornr_ltgray" id="parse_tab_text_container">
            <div class="t"><div class="b"><div class="l"><div class="r"><div class="bl"><div class="br"><div class="tl"><div class="tr" id="parse_tab1_instructions">
                    Highlight the text you wish to parse then right-click and select the desired element from the menu.
            <div class="clearfix"></div>
            </div></div></div></div></div></div></div></div>
            </div><!-- .roundedcornr_ltgray -->

            <div class="roundedcornr_ltgray" id="parse_tab_text_content_container">
            <div class="t"><div class="b"><div class="l"><div class="r"><div class="bl"><div class="br"><div class="tl"><div class="tr" id="parsecontent">
                <div id="parse_tab_text_content" class="parse_tab_text_content" onclick="">

                    <h3>No ROi selected yet</h3>
                </div>
            </div></div></div></div></div></div></div></div>
            </div><!-- .roundedcornr_ltgray -->
        </div>
        <div id="parse_tab2_section" class="tab_section">
            <h3>Image content</h3>
        </div>
        </div> <!-- #parse_tab_nav -->
  ';
  return array("parse_tab_nav"=>$parse_tab_nav);
}