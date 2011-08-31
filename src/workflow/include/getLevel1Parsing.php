<?php
global $base_url;
$server_base = variable_get('apiary_research_base_url', 'http://localhost');
$rel_path = drupal_get_path('module','apiary_project');
include_once($rel_path . '/workflow/include/roiHandler.php');
include_once($rel_path . '/workflow/include/functions.php');
include_once($rel_path . '/fedora_commons/class.AP_ROI.php');
function populate_elements($pid){
	$server_base = variable_get('apiary_research_base_url', 'http://localhost');
	$paste_img = $base_url . '/'.$rel_path.'/workflow/assets/img/paste.png';
	$help_img = $base_url . '/'.$rel_path.'/workflow/assets/img/help-faq.png';

	$roi_pid = new roiHandler($pid);
	if($roi_pid->parseXMLExist){
		$xml = new DOMDocument;
		$xml->loadXML($roi_pid->xmlContainer);
	}
	else{
		$xml = false;
	}

	$ap_roi = new AP_ROI();
	$roiMetadata_record = $ap_roi->getroiMetadata_record($pid);
	$roi_type_temp = $roiMetadata_record['roiType'];
	$roi_list = getROItypeList();
	$roi_type = '';
	switch($roi_type_temp){
		case $roi_list[0]: $roi_type = "primary";
						   break;
		case $roi_list[1]: $roi_type = "determination";
						   break;
		case $roi_list[2]: $roi_type = "barcode";
						   break;
		case $roi_list[3]: $roi_type = "type";
						   break;
		case $roi_list[4]: $roi_type = "annotation";
						   break;
	}

	$whenObj = db_query("select * from {apiary_project} where parse_level=1 AND (ui_categories='who' OR ui_categories='when')");
	echo "<div id='accordion'>
			<h3><a href='#'>Who & When</a></h3><div>";
	while($res = db_fetch_object($whenObj)){
		if(stristr($res->roi_association, $roi_type) !== false){
			if($xml)
				$val = $xml->getElementsByTagName($res->term)->item(0)->nodeValue;
			else
				$val = '';
			$help_text_query = db_query("select label,help_text from {apiary_project_help_text} where term='$res->term'");
			$help_text_obj = db_fetch_object($help_text_query);
			$help_text = "<b>$help_text_obj->label</b><br/><hr/>$help_text_obj->help_text";
			print '<div class="wrapper"><label class="style">' . $res->display_label;
			print '  : <a href=javascript:getSelText("' .$res->term . '")><img style="vertical-align:bottom; border-style: none;" src="' . $paste_img . '" /></a></label>';
			print "<div class='field'><input type='text' index=0 value='" . $val . "' name='" . $res->term . "' id='" . $res->term . "' alt='" . $res->display_label ."' />";
			print "<img src='$help_img' class='img_tooltip' title='$help_text'/></div></div>";
		}
	}
	echo "</div>";

	$whatObj = db_query("select * from {apiary_project} where parse_level=1 AND ui_categories='what'");
	echo "<h3><a href='#'>What</a></h3><div>";
	while($res = db_fetch_object($whatObj)){
		if(stristr($res->roi_association, $roi_type) !== false){
			if($xml)
				$val = $xml->getElementsByTagName($res->term)->item(0)->nodeValue;
			else
				$val = '';
			$help_text_query = db_query("select label,help_text from {apiary_project_help_text} where term='$res->term'");
			$help_text_obj = db_fetch_object($help_text_query);
			$help_text = "<b>$help_text_obj->label</b><br/><hr/>$help_text_obj->help_text";
			print '<div class="wrapper"><label class="style">' . $res->display_label;
			print '  : <a href=javascript:getSelText("' .$res->term . '")><img style="vertical-align:bottom; border-style: none;" src="' . $paste_img . '" /></a></label>';
			print "<div class='field'><input type='text' index=1 value='" . $val . "' name='" . $res->term . "' id='" . $res->term . "' alt='" . $res->display_label ."' />";
			print "<img src='$help_img' class='img_tooltip' title='$help_text'/></div></div>";
		}
	}
	echo "</div>";

	$whereObj = db_query("select * from {apiary_project} where parse_level=1 AND ui_categories='where'");
	echo "<h3><a href='#'>Where</a></h3><div>";
	while($res = db_fetch_object($whereObj)){
		if(stristr($res->roi_association, $roi_type) !== false){
			if($xml)
				$val = $xml->getElementsByTagName($res->term)->item(0)->nodeValue;
			else
				$val = '';
			$help_text_query = db_query("select label,help_text from {apiary_project_help_text} where term='$res->term'");
			$help_text_obj = db_fetch_object($help_text_query);
			$help_text = "<b>$help_text_obj->label</b><br/><hr/>$help_text_obj->help_text";
			print '<div class="wrapper"><label class="style">' . $res->display_label;
			print '  : <a href=javascript:getSelText("' .$res->term . '")><img style="vertical-align:bottom; border-style: none;" src="' . $paste_img . '" /></a></label>';
			print "<div class='field'><input type='text' index=2 value='" . $val . "' name='" . $res->term . "' id='" . $res->term . "' alt='" . $res->display_label ."' />";
			print "<img src='$help_img' class='img_tooltip' title='$help_text' /></div></div>";
		}
	}
	echo "</div></div>";
}
function get_right_click_list($pid){
	$ap_roi = new AP_ROI();
	$roiMetadata_record = $ap_roi->getroiMetadata_record($pid);
	$roi_type_temp = $roiMetadata_record['roiType'];
	$roi_list = getROItypeList();
	$roi_type = '';
	switch($roi_type_temp){
		case $roi_list[0]: $roi_type = "primary";
						   break;
		case $roi_list[1]: $roi_type = "determination";
						   break;
		case $roi_list[2]: $roi_type = "barcode";
						   break;
		case $roi_list[3]: $roi_type = "type";
						   break;
		case $roi_list[4]: $roi_type = "annotation";
						   break;
	}
	//Context Menu List
	print '<ul id="apiary_context_menu" class="jeegoocontext cm_blue">';
	//Who & When
	print '<li>Who & When<ul>';
	$whoObj = db_query("select * from {apiary_project} where parse_level=1 AND (ui_categories='who' OR ui_categories='when')");
	while($res = db_fetch_object($whoObj))
	{
		if(stristr($res->roi_association, $roi_type) !== false){
			print '<li><a id="menu_'.$res->term.'" href=javascript:getSelText("' . $res->term . '")>' . $res->display_label . '</a>';
		}
	}
	print "</ul></li>";
	//What
	print '<li>What<ul>';
	$whatObj = db_query("select * from {apiary_project} where parse_level=1 AND ui_categories='what'");
	while($res = db_fetch_object($whatObj))
	{
		if(stristr($res->roi_association, $roi_type) !== false){
			print '<li><a id="menu_'.$res->term.'" href=javascript:getSelText("' . $res->term . '")>' . $res->display_label . '</a>';
		}
	}
	print "</ul></li>";
	//Where
	print '<li>Where</a><ul>';
	$whereObj = db_query("select * from {apiary_project} where parse_level=1 AND ui_categories='where'");
	while($res = db_fetch_object($whereObj))
	{
		if(stristr($res->roi_association, $roi_type) !== false){
			print '<li><a id="menu_'.$res->term.'" href=javascript:getSelText("' . $res->term . '")>' . $res->display_label . '</a>';
		}
	}
	print "</ul></li>";

	print '</ul>';
}

?>



