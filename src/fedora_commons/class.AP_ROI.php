<?php
  module_load_include('php', 'Apiary_Project', 'fedora_commons/class.FedoraObject');
  include_once(drupal_get_path('module', 'apiary_project') . '/workflow/include/class.Workflow_Sessions.php');
  class AP_ROI extends FedoraObject{
  public $image_pid;
  public $sourceURL;
  public $roiType;
  public $y1;
  public $x1;
  public $height;
  public $width;
  public $roiURL;
  public $parent_image_width;
  public $parent_image_height;
  public $parent_image_display_width;
  public $parent_image_display_height;
  public $pid;
  public $roiMetadata_base_url;
  public $status;

  function AP_ROI() {
    module_load_include('nc', 'AP_ROI', '');
    drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
    session_start();
  }

  function createROIObject($obj_image_pid, $obj_sourceURL, $obj_roiURL, $obj_roiType, $obj_x1, $obj_y1, $obj_width, $obj_height, $obj_parent_image_width, $obj_parent_image_height, $obj_parent_image_display_width, $obj_parent_image_display_height, $obj_label) {
    $this->image_pid = $obj_image_pid;
    $this->sourceURL = $obj_sourceURL;
    $this->roiURL = $obj_roiURL;
    $this->roiType = $obj_roiType;
    $this->x1 = $obj_x1;
    $this->y1 = $obj_y1;
    $this->width = $obj_width;
    $this->height = $obj_height;
    $this->parent_image_width = $obj_parent_image_width;
    $this->parent_image_height = $obj_parent_image_height;
    $this->parent_image_display_width = $obj_parent_image_display_width;
    $this->parent_image_display_height = $obj_parent_image_display_height;
	$this->pid = $this->getNextROIPid();
	$this->status = "";
	if($obj_label != null && $obj_label != '') {
      $this->label = $obj_label;
    }
    else {
      $this->label = $this->pid; //We may use something other than the pid for the label eventually
    }

	  //create relationships
	$pid_base = 'roi';

    if($this->startFOXML()) {
	  if(!$this->addROI_RELS_EXT_datastream()){
	    $this->status .= 'Unable to addROI_RELS_EXT_datastream. ';
	  }
	  if(!$this->addDC_datastream()){
	    $this->status .= 'Unable to addDC_datastream. ';
	  }
	  if(!$this->addROIMetadata_datastream()){
	    $this->status .= 'Unable to addROIMetadata_datastream. ';
	  }
	  if(!$this->addJPEG_datastream()){
	    $this->status .= 'Unable to addJPEG_datastream. ';
	  }

	  try{
	    $foxml_file = str_replace(':', '_', $this->pid).'_'.date("YmdHis");
        $foxml_file = '/var/www/drupal/sites/default/files/apiary_datastreams/'.$foxml_file.'.xml';
        if(file_exists($foxml_file)) {
          unlink($foxml_file);
        }
	    $this->dom->save($foxml_file);

	    if($object = ingest_object_from_FOXML($this->dom)) {
	      $this->msg = "$this->pid successfully created.";
	      $this->status .= $this->msg;
	      return true;
	    }
	    else {
	      $this->msg = "Unable to ingest image FOXML dom document. ";
	      $this->status .= $this->msg;
	      return false;
	    }
	  }
	  catch(exception $e){
	    drupal_set_message(t('Error Ingesting Image Object! ').$e->getMessage(),'error');
	    watchdog(t("Fedora_Repository"), "Error Ingesting Image Object!".$e->getMessage(), WATCHDOG_ERROR);
	    return false;
	  }
	}
    else {
      $this->msg = "Unable to start image FOXML file for create image object. ";
      $this->status .= $this->msg;
      return false;
    }
	return true;
  }

  static function getroiMetadata_record($roi_pid) {
    if(isset($roi_pid)) {
      return FedoraObject::getMetadata_record($roi_pid, 'roiMetadata', 'roiMetadata_record', 'apr');
    }
    else {
      return false;
    }
  }

  function get_image_pid($roi_pid){
  	$rdf = $this->getManagedXMLDom($roi_pid, "RELS-EXT");
  	$ip_string = $rdf->getElementsByTagName("hasModel")->item(0)->getAttributeNode("rdf:resource")->value;
  	$image_pid = str_replace("info:fedora/","",$ip_string);
  	return $image_pid;
  }

  function get_specimen_pid($roi_pid){
    $image_pid = $this->get_image_pid($roi_pid);
    $specimen_pid = AP_Image::get_specimen_pid($image_pid);
  	return $specimen_pid;
  }

  static function getManagedXMLDom($roi_pid, $datastream_name) {
    if(isset($roi_pid)) {
      $params = array('pid' => "$roi_pid", 'dsID' => "$datastream_name", 'asOfDateTime' =>"");
      $object = get_DatastreamDissemination($params);
      $content=$object->dissemination->stream;
      $content=trim($content);
      $doc = new DOMDocument();
      if(!$doc->loadXML($content)){
        return false;
      }
      return $doc;
    }
    else {
      return false;
    }
  }

  function getNextROIPid() {
    $existing_pids = $this->getROIList();
    $largest_pid_number = 0;
    foreach($existing_pids as $existing_pid) {
      $pid_substr = str_replace('ap-roi:ROI-', '', $existing_pid);
      $pid_number = (int)$pid_substr;
      if($pid_number > $largest_pid_number) {
        $largest_pid_number = $pid_number;
      }
    }
    $largest_pid_number++;
    return 'ap-roi:ROI-'.$largest_pid_number;
  }

  static function getROIList() {
    $query = "select pid from doFields where pid like 'ap-roi:%'";
    $rows = get_data_rows($query);
    $list = array();
    foreach($rows as $row) {
      array_push( $list,  $row['pid']);
    }
    return $list;
  }

  static function getROIandParentsList() {
    $query_string = 'select $member $imgMember $roiMember ';
    $query_string .= 'from   <#ri> ';
    $query_string .= 'where  $member <fedora-rels-ext:isMemberOf> <info:fedora/apiary:SpecimenBinders> ';
    $query_string .= 'and    $imgMember <fedora-rels-ext:isMemberOf> $member ';
    $query_string .= 'and    $roiMember <fedora-rels-ext:isMemberOf> $imgMember';
    $ROIandParents_results = get_RIQueryResults('apiary:SpecimenBinders', $query_string);
    $ROIandParents_results = new SimpleXMLElement($ROIandParents_results);

    $ROIfamilyList = array();
    $ROIfamily;
    foreach($ROIandParents_results->xpath( '//@uri' ) as $uri) {
      $result = substr( strstr( $uri, '/' ), 1 );

      if(strpos($result, 'ap-specimen:') > -1) {
        $ROIfamily['specimen_pid'] = $result;
      }

      if(strpos($result, 'ap-image:') > -1) {
        $ROIfamily['image_pid'] = $result;
      }

      if(strpos($result, 'ap-roi:') > -1) {
        $ROIfamily['roi_pid'] = $result;
        array_push( $ROIfamilyList,  $ROIfamily);
        $ROIfamily['specimen_pid'] = '';
        $ROIfamily['image_pid'] = '';
        $ROIfamily['roi_pid'] = '';
      }
    }
    return $ROIfamilyList;
  }

  function addROI_RELS_EXT_datastream($obj_pid = null, $obj_image_pid = null, &$obj_dom = null, &$obj_rootElement = null){
	if($obj_dom == null && $this->dom != null) {
	  $obj_dom = &$this->dom;
	  $obj_rootElement = &$this->rootElement;
	  $obj_pid = $this->pid;
	  $obj_image_pid = $this->image_pid;
	}
	else {
	  echo 'No Dom Document available to addROI_RELS_EXT_datastream.<br>';
	  return false;
	}
    $datastream = $obj_dom->createElement("foxml:datastream");
    $datastream->setAttribute("ID","RELS-EXT");
    $datastream->setAttribute("CONTROL_GROUP","X");

    $datastream_version = $obj_dom->createElement("foxml:datastreamVersion");
    $datastream_version->setAttribute("FORMAT_URI","info:fedora/fedora-system:FedoraRELSExt-1.0");
    $datastream_version->setAttribute("ID","RELS-EXT.0");
    $datastream_version->setAttribute("MIMETYPE","application/rdf+xml");
    $datastream_version->setAttribute("LABEL","RDF Statements about this Object");

    $xmlContent = $obj_dom->createElement("foxml:xmlContent");

    $rdf = $obj_dom->createElement("rdf:RDF");
    $rdf->setAttribute("xmlns:rdf","http://www.w3.org/1999/02/22-rdf-syntax-ns#");
    $rdf->setAttribute("xmlns:fedora-model","info:fedora/fedora-system:def/model#");

    $rdf_description = $obj_dom->createElement("rdf:Description");
    $rdf_description->setAttribute("rdf:about","info:fedora/$obj_pid");

    $rdf_description_member = $obj_dom->createElement("isMemberOf");
    $rdf_description_member->setAttribute("xmlns","info:fedora/fedora-system:def/relations-external#");
    $rdf_description_member->setAttribute("rdf:resource","info:fedora/$obj_image_pid");

    $rdf_hasModel = $obj_dom->createElement("hasModel");
    $rdf_hasModel->setAttribute("xmlns","info:fedora/fedora-system:def/model#");
    $rdf_hasModel->setAttribute("rdf:resource","info:fedora/$obj_image_pid");

    $rdf_fedora_HasModel = $obj_dom->createElement("fedora-model:hasModel");
    $rdf_fedora_HasModel->setAttribute("rdf:resource","info:fedora/ap-sdefcm:ocropus");

    $datastream->appendChild($datastream_version);
    $datastream_version->appendChild($xmlContent);
    $xmlContent->appendChild($rdf);
    $rdf->appendChild($rdf_description);
    $rdf_description->appendChild($rdf_hasModel);
    $rdf_description->appendChild($rdf_fedora_HasModel);
    $rdf_description->appendChild($rdf_description_member);
    $obj_rootElement->appendChild($datastream);
    return true;
  }

  function addJPEG_datastream($obj_roiURL = null, &$obj_dom = null, &$obj_rootElement = null) {
    if($obj_dom == null && $this->dom != null) {
      $obj_dom = &$this->dom;
      $obj_rootElement = &$this->rootElement;
      $obj_roiURL = $this->roiURL;
    }
    else {
      echo 'No Dom Document available to addJPEG_datastream.<br>';
      return false;
    }
    $datastream = $obj_dom->createElement("foxml:datastream");
    $datastream->setAttribute("ID","JPEG");
    $datastream->setAttribute("STATE","A");
    $datastream->setAttribute("CONTROL_GROUP","M");
    $version = $obj_dom->createElement("foxml:datastreamVersion");
    $obj_rootElement->appendChild($datastream);

    $version->setAttribute("ID","JPEG.0");
    $version->setAttribute("MIMETYPE","image/jpeg");
    $version->setAttribute("LABEL","Full size jpeg image for this ROI");

    $roicontent = $obj_dom->createElement('foxml:contentLocation');
    $roicontent->setAttribute("REF","$obj_roiURL");
    $roicontent->setAttribute("TYPE","URL");
    $datastream->appendChild($version);
    $version->appendChild($roicontent);
    return true;
  }

  function addROIMetadata_datastream($obj_pid = null, $obj_x1 = null, $obj_y1 = null, $obj_width = null, $obj_height = null, $obj_roiURL = null, $obj_roiType = null, $obj_sourceURL = null, &$obj_dom = null, &$obj_rootElement = null) {
  	if($obj_dom == null && $this->dom != null) {
  	  $obj_pid = $this->pid;
  	  $obj_dom = &$this->dom;
  	  $obj_rootElement = &$this->rootElement;
  	  $obj_x1 = $this->x1;
  	  $obj_y1 = $this->y1;
  	  $obj_width = $this->width;
  	  $obj_height = $this->height;
  	  $obj_roiURL = $this->roiURL;
  	  $obj_roiType = $this->roiType;
  	  $obj_sourceURL = $this->sourceURL;
  	}
  	else {
  	  echo 'No Dom Document available to addImageMetadata_datastream.<br>';
  	  return false;
  	}
    $datastream = $obj_dom->createElement("foxml:datastream");
    $datastream->setAttribute("ID","roiMetadata");
    $datastream->setAttribute("STATE","A");
    $datastream->setAttribute("CONTROL_GROUP","M");
    $version = $obj_dom->createElement("foxml:datastreamVersion");
    $obj_rootElement->appendChild($datastream);

    $version->setAttribute("ID","roiMetadata.0");
    $version->setAttribute("MIMETYPE","text/xml");
    $version->setAttribute("LABEL","ROI Metadata");
    $obj_dom1 = new DomDocument("1.0", "UTF-8");
    $obj_dom1->formatOutput = true;

    //begin writing Metadata
    $apr = $obj_dom1->createElement("apr:roiMetadata_record");
    $apr->setAttribute('xmlns:apr',"http://www.apiaryproject.org/apr");
    //roiMetadata elements
    $y_element = $obj_dom1->createElement('apr:y',$obj_y1);
    $apr->appendChild($y_element);
    $x_element = $obj_dom1->createElement('apr:x',$obj_x1);
    $apr->appendChild($x_element);
    $h_element = $obj_dom1->createElement('apr:h',$obj_height);
    $apr->appendChild($h_element);
    $w_element = $obj_dom1->createElement('apr:w',$obj_width);
    $apr->appendChild($w_element);
    $roiURL_element = $obj_dom1->createElement('apr:roiURL',htmlentities($obj_roiURL));
    $apr->appendChild($roiURL_element);
    $roiType_element = $obj_dom1->createElement('apr:roiType',$obj_roiType);
    $apr->appendChild($roiType_element);
    $sourceURL_element = $obj_dom1->createElement('apr:sourceURL',$obj_sourceURL);
    $apr->appendChild($sourceURL_element);

    $obj_dom1->appendChild($apr);


    $xml_file_pid = str_replace(':', '_', $obj_pid).'-roiMetadata-content'.'_'.date("YmdHis").'.xml';
    $xml_file = '/var/www/drupal/sites/default/files/apiary_datastreams/'.$xml_file_pid;
    if(file_exists($xml_file)) {
      unlink($xml_file);
    }
    $obj_dom1->save($xml_file);
    if($this->roiMetadata_base_url == '' || $this->roiMetadata_base_url == null) {
      global $base_url;
      $this->roiMetadata_base_url = $base_url;
    }
    $xml_file_Url = $this->roiMetadata_base_url.'/sites/default/files/apiary_datastreams/'.$xml_file_pid;

    $roicontent = $obj_dom->createElement('foxml:contentLocation');
    $roicontent->setAttribute("REF","$xml_file_Url");
    $roicontent->setAttribute("TYPE","URL");

    $datastream->appendChild($version);
    $version->appendChild($roicontent);
    $obj_rootElement->appendChild($datastream);
    return true;
  }

  static function createSpecimenMetadata($pid = null, $dom = null) {
    $xml_file_suffix = str_replace(':', '_', $pid).'_'.date("YmdHis").'.xml';
    $xml_file = '/var/www/drupal/sites/default/files/apiary_datastreams/'.$xml_file_suffix;
    if(file_exists($xml_file)) {
      unlink($xml_file);
    }
    $dom->save($xml_file);
    global $base_url;
    $xml_file_url = $base_url.'/sites/default/files/apiary_datastreams/'.$xml_file_suffix;
    $added = add_Datastream($pid, 'specimenMetadata', 'Metadata', 'text/xml', $xml_file_url);
    if($added) {
      if(file_exists($xml_file)) {
        unlink($xml_file);
      }
    }
    return $added;
  }

  static function getROILock($roi_pid) {
    global $user;
    $now = date("YmdHis");
    $datastream_name = "status";
    $datastream_label = "ROI Status";
    $session_id = $_SESSION['apiary_session_id'];

    $dom = FedoraObject::getManagedXMLDom($roi_pid, $datastream_name);
    if($dom != false && !empty($dom->getElementsByTagName('locked_session')->item(0)->nodeValue)) {
      if($dom->getElementsByTagName('locked')->item(0)->nodeValue == "true") {
        $locked_session = $dom->getElementsByTagName('locked_session')->item(0)->nodeValue;
        if($dom->getElementsByTagName('locked_by')->item(0)->nodeValue == $user->name && $locked_session == $session_id) {
          $dom->getElementsByTagName('locked_time')->item(0)->nodeValue = $now;
        }
        else {
          $last_locked_time = $dom->getElementsByTagName('locked_time')->item(0)->nodeValue;
    	  $apiary_timeout = variable_get('apiary_object_timeout', '1800');
          $last_locked_timeout = $last_locked_time + $apiary_timeout;
          if($now > $last_locked_timeout || !Workflow_Sessions::active_session($locked_session)) {
            $dom->getElementsByTagName('locked_by')->item(0)->nodeValue = $user->name;
            $dom->getElementsByTagName('locked_time')->item(0)->nodeValue = $now;
            $dom->getElementsByTagName('locked_session')->item(0)->nodeValue = $session_id;
          }
          else {
            //can't unlock this record
            return false;
          }
        }
      }
      else {
        $dom->getElementsByTagName('locked')->item(0)->nodeValue = "true";
        $dom->getElementsByTagName('locked_by')->item(0)->nodeValue = $user->name;
        $dom->getElementsByTagName('locked_time')->item(0)->nodeValue = $now;
        $dom->getElementsByTagName('locked_session')->item(0)->nodeValue = $session_id;
      }
    }
    else {
      //create status datastream!
      $dom = new DOMDocument('1.0', 'iso-8859-1');
      $rootElement = $dom->createElement('statuses', '');
      $dom->appendChild($rootElement);

      $newElement = $dom->createElement("locked", "true");
      $rootElement->appendChild($newElement);
      $newElement = $dom->createElement("locked_by", $user->name);
      $rootElement->appendChild($newElement);
      $newElement = $dom->createElement("locked_time", $now);
      $rootElement->appendChild($newElement);
      $newElement = $dom->createElement("locked_session", $session_id);
      $rootElement->appendChild($newElement);
      $newElement = $dom->createElement("transcribedStatus", "");
      $rootElement->appendChild($newElement);
      $newElement = $dom->createElement("transcribedStatusUpdatedBy", "");
      $rootElement->appendChild($newElement);
      $newElement = $dom->createElement("parsedL1Status", "");
      $rootElement->appendChild($newElement);
      $newElement = $dom->createElement("parsedL1StausUpdatedBy", "");
      $rootElement->appendChild($newElement);
      $newElement = $dom->createElement("parsedL2Status", "");
      $rootElement->appendChild($newElement);
      $newElement = $dom->createElement("parsedL2StatusUpdatedBy", "");
      $rootElement->appendChild($newElement);
      $newElement = $dom->createElement("parsedL3Status", "");
      $rootElement->appendChild($newElement);
      $newElement = $dom->createElement("parsedL3StatusUpdatedBy", "");
      $rootElement->appendChild($newElement);
      $newElement = $dom->createElement("qcStatus", "");
      $rootElement->appendChild($newElement);
      $newElement = $dom->createElement("qcStatusUpdatedBy", "");
      $rootElement->appendChild($newElement);
    }

    //We don't get here if we fail
    if(FedoraObject::createManagedXMLDom($roi_pid, $datastream_name, $datastream_label, $dom)) {
      include_once(drupal_get_path('module', 'apiary_project') . '/workflow/include/search.php');
      $search_instance = new search();
      $search_instance->index($roi_pid);
      return FedoraObject::getManagedXMLDom($roi_pid, $datastream_name);
    }
  }

  static function releaseROILock($roi_pid) {
    global $user;
    $now = date("YmdHis");
    $datastream_name = "status";
    $datastream_label = "ROI Status";
    $session_id = $_SESSION['apiary_session_id'];
    if($session_id == '') {
      $session_id = $_SESSION['apiary_cleared_session_id'];
    }

    $dom = FedoraObject::getManagedXMLDom($roi_pid, $datastream_name);
    if($dom != false) {
      if($dom->getElementsByTagName('locked')->item(0)->nodeValue != "false") {
        if(!empty($dom->getElementsByTagName('locked_session')->item(0)->nodeValue)) {
          $locked_session = $dom->getElementsByTagName('locked_session')->item(0)->nodeValue;
          if($dom->getElementsByTagName('locked_by')->item(0)->nodeValue == $user->name && $locked_session == $session_id) {
            $dom->getElementsByTagName('locked')->item(0)->nodeValue = "false";
          }
          else if(false) {
            //could add an override if the user has some admin right
          }
          else {
            $last_locked_time = $dom->getElementsByTagName('locked_time')->item(0)->nodeValue;
    	    $apiary_timeout = variable_get('apiary_object_timeout', '1800');
            $last_locked_timeout = $last_locked_time + $apiary_timeout;
            if($now > $last_locked_timeout || !Workflow_Sessions::active_session($locked_session)) {
              $dom->getElementsByTagName('locked')->item(0)->nodeValue = "false";
            }
            else {
              //can't unlock this record
              return false;
            }
          }
        }
      }
      $dom->getElementsByTagName('locked')->item(0)->nodeValue = "false";
    }
    else {
      //create status datastream!
      $dom = AP_ROI::generateROIStatusDom("false", $user->name, $now, $session_id);
    }

    //We don't get here if we fail
    if(FedoraObject::createManagedXMLDom($roi_pid, $datastream_name, $datastream_label, $dom)) {
      include_once(drupal_get_path('module', 'apiary_project') . '/workflow/include/search.php');
      $search_instance = new search();
      $search_instance->index($roi_pid);
      return FedoraObject::getManagedXMLDom($roi_pid, $datastream_name);
    }
  }

  static function generateROIStatusDom($locked, $locked_by = '', $locked_time = '', $locked_session = '') {
    $dom = new DOMDocument('1.0', 'iso-8859-1');
    $rootElement = $dom->createElement('statuses', '');
    $dom->appendChild($rootElement);

    $newElement = $dom->createElement("locked", $locked);
    $rootElement->appendChild($newElement);
    $newElement = $dom->createElement("locked_by", $username);
    $rootElement->appendChild($newElement);
    $newElement = $dom->createElement("locked_time", $locked_time);
    $rootElement->appendChild($newElement);
    $newElement = $dom->createElement("locked_session", $locked_session);
    $rootElement->appendChild($newElement);
    $newElement = $dom->createElement("transcribedStatus", "");
    $rootElement->appendChild($newElement);
    $newElement = $dom->createElement("transcribedStatusUpdatedBy", "");
    $rootElement->appendChild($newElement);
    $newElement = $dom->createElement("parsedL1Status", "");
    $rootElement->appendChild($newElement);
    $newElement = $dom->createElement("parsedL1StausUpdatedBy", "");
    $rootElement->appendChild($newElement);
    $newElement = $dom->createElement("parsedL2Status", "");
    $rootElement->appendChild($newElement);
    $newElement = $dom->createElement("parsedL2StatusUpdatedBy", "");
    $rootElement->appendChild($newElement);
    $newElement = $dom->createElement("parsedL3Status", "");
    $rootElement->appendChild($newElement);
    $newElement = $dom->createElement("parsedL3StatusUpdatedBy", "");
    $rootElement->appendChild($newElement);
    $newElement = $dom->createElement("qcStatus", "");
    $rootElement->appendChild($newElement);
    $newElement = $dom->createElement("qcStatusUpdatedBy", "");
    $rootElement->appendChild($newElement);
    return $dom;
  }

  static function getROIStatusDom($roi_pid) {
    $datastream_name = "status";
    $datastream_label = "ROI Status";

    if($dom = FedoraObject::getManagedXMLDom($roi_pid, $datastream_name)) {
      return $dom;
    }
    else {
      //create status datastream!
      $dom = new DOMDocument('1.0', 'iso-8859-1');
      $rootElement = $dom->createElement('statuses', '');
      $dom->appendChild($rootElement);

      $newElement = $dom->createElement("locked", "false");
      $rootElement->appendChild($newElement);
      $newElement = $dom->createElement("locked_by", "none");
      $rootElement->appendChild($newElement);
      $newElement = $dom->createElement("locked_time", "20010101000000"); //a long time ago
      $rootElement->appendChild($newElement);
      $newElement = $dom->createElement("locked_session", 'none');
      $rootElement->appendChild($newElement);
      $newElement = $dom->createElement("transcribedStatus", "");
      $rootElement->appendChild($newElement);
      $newElement = $dom->createElement("transcribedStatusUpdatedBy", "");
      $rootElement->appendChild($newElement);
      $newElement = $dom->createElement("parsedL1Status", "");
      $rootElement->appendChild($newElement);
      $newElement = $dom->createElement("parsedL1StausUpdatedBy", "");
      $rootElement->appendChild($newElement);
      $newElement = $dom->createElement("parsedL2Status", "");
      $rootElement->appendChild($newElement);
      $newElement = $dom->createElement("parsedL2StatusUpdatedBy", "");
      $rootElement->appendChild($newElement);
      $newElement = $dom->createElement("parsedL3Status", "");
      $rootElement->appendChild($newElement);
      $newElement = $dom->createElement("parsedL3StatusUpdatedBy", "");
      $rootElement->appendChild($newElement);
      $newElement = $dom->createElement("qcStatus", "");
      $rootElement->appendChild($newElement);
      $newElement = $dom->createElement("qcStatusUpdatedBy", "");
      $rootElement->appendChild($newElement);
    }
    if(FedoraObject::createManagedXMLDom($roi_pid, $datastream_name, $datastream_label, $dom)) {
      include_once(drupal_get_path('module', 'apiary_project') . '/workflow/include/search.php');
      $search_instance = new search();
      $search_instance->index($roi_pid);
      return FedoraObject::getManagedXMLDom($roi_pid, "status");
    }
    else {
      //can't update this datastream!
      return false;
    }
  }

  static function setROIStatus($roi_pid, $status_type, $status) {
    global $user;
    $datastream_name = "status";
    $datastream_label = "ROI Status";

    $dom = FedoraObject::getManagedXMLDom($roi_pid, $datastream_name);
    if($dom != false) {
      $dom->getElementsByTagName($status_type)->item(0)->nodeValue = $status;
      $dom->getElementsByTagName($status_type.'UpdatedBy')->item(0)->nodeValue = $user->name;
    }
    else {
      //Shouldn't be able to get here as a lock is needed before a status can be updated.
      return false;
    }

    //We don't get here if we fail
    $success = FedoraObject::createManagedXMLDom($roi_pid, $datastream_name, $datastream_label, $dom);
    if($success) {
      include_once(drupal_get_path('module', 'apiary_project') . '/workflow/include/search.php');
      $search_instance = new search();
      $search_instance->index($roi_pid);
      return $dom;
    }
    else {
      //can't update this datastream!
      return false;
    }
  }
}

?>
