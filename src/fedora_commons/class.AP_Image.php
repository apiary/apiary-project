<?php

module_load_include('php', 'Apiary_Project', 'fedora_commons/class.FedoraObject');
class AP_Image extends FedoraObject{
  public $specimen_pid;
  public $jp2URL;
  public $rft_id;
  public $sourceURL;
  public $width;
  public $height;
  public $jpeg_datastream_url;
  public $msg;

  function AP_Image() {
    module_load_include('nc', 'AP_Image', '');
    drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
    session_start();
  }

  function createImageObject($obj_specimen_pid, $obj_jp2URL, $obj_rft_id, $obj_sourceURL, $obj_width, $obj_height, $obj_jpeg_datastream_url, $obj_label) {
	$this->pid = $this->getNextImagePid();
    $this->specimen_pid = $obj_specimen_pid;
    $this->jp2URL = $obj_jp2URL;
    $this->rft_id = $obj_rft_id;
    $this->sourceURL = $obj_sourceURL;
    $this->width = $obj_width;
    $this->height = $obj_height;
	$this->jpeg_datastream_url = $obj_jpeg_datastream_url;
	if($obj_label != null && $obj_label != '') {
      $this->label = $obj_label;
    }
    else {
      $this->label = $this->pid; //We may use something other than the pid for the label eventually
    }

	  //create relationships
	$pid_base = 'image';

    if($this->startFOXML()) {
	  if(!$this->addImage_RELS_EXT_datastream()){
	    echo 'Unable to addImage_RELS_EXT_datastream.<br>';
	  }
	  if(!$this->addDC_datastream()){
	    echo 'Unable to addDC_datastream.<br>';
	  }
	  list($this->width, $this->height) = getimagesize($this->jp2URL);
	  if(!$this->addImageMetadata_datastream()){
	    echo 'Unable to addImageMetadata_datastream.<br>';
	  }
	  if(!$this->addJPEG_datastream()){
	    echo 'Unable to addJPEG_datastream.<br>';
	  }

	  try{
	    $foxml_file = str_replace(':', '_', $this->pid);
        $foxml_file = '/var/www/drupal/sites/default/files/apiary_datastreams/'.$foxml_file.'.xml';
        if(file_exists($foxml_file)) {
          unlink($foxml_file);
        }
	    $this->dom->save($foxml_file);

	    if($object = ingest_object_from_FOXML($this->dom)) {
	      $this->msg = "$this->pid successfully created.";
          include_once(drupal_get_path('module', 'apiary_project') . '/workflow/include/search.php');
          $search_instance = new search();
          $search_instance->index($this->pid);
	      return true;
	    }
	    else {
	      $this->msg = "Unable to ingest image FOXML dom document.";
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
      $this->msg = "Unable to start image FOXML file for create image object.";
      return false;
    }
	return true;
  }

  function getNextImagePid() {
    $existing_pids = $this->getImageList();
    $largest_pid_number = 0;
    foreach($existing_pids as $existing_pid) {
      $pid_substr = str_replace('ap-image:Image-', '', $existing_pid);
      $pid_number = (int)$pid_substr;
      if($pid_number > $largest_pid_number) {
        $largest_pid_number = $pid_number;
      }
    }
    $largest_pid_number++;
    return 'ap-image:Image-'.$largest_pid_number;
  }


  static function getImageList() {
    $query = "select pid from doFields where pid like 'ap-image:%'";
    $rows = get_data_rows($query);
    $list = array();
    foreach($rows as $row) {
      array_push( $list,  $row['pid']);
    }
    return $list;
  }

  static function getAllImagePids($query_string = null) {
    if($query_string == null || $query == '') {
      $query_string = 'select $img_pid from <#ri> where $sp_pid<fedora-rels-ext:isMemberOf> <info:fedora/apiary:SpecimenBinders> and $img_pid <fedora-rels-ext:isMemberOf> $sp_pid';
    }
    $pid_objects= get_RIQueryResults('apiary:SpecimenBinders', $query_string);
    $sxml = new SimpleXMLElement( $pid_objects);
    $list = array();
    foreach( $sxml->xpath( '//@uri' ) as $uri ) {
      array_push( $list,  substr( strstr( $uri, '/' ), 1 ));
    }
    return $list;
  }

  function addImage_RELS_EXT_datastream($obj_pid = null, $obj_specimen_pid = null, &$obj_dom = null, &$obj_rootElement = null) {
	if($obj_dom == null && $this->dom != null) {
	  $obj_dom = &$this->dom;
	  $obj_rootElement = &$this->rootElement;
	  $obj_pid = $this->pid;
	  $obj_specimen_pid = $this->specimen_pid;
	}
	else {
	  echo 'No Dom Document available to addImage_RELS_EXT_datastream.<br>';
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
    $rdf_description_member->setAttribute("rdf:resource","info:fedora/$obj_specimen_pid");

    $rdf_hasModel = $obj_dom->createElement("hasModel");
    $rdf_hasModel->setAttribute("xmlns","info:fedora/fedora-system:def/model#");
    $rdf_hasModel->setAttribute("rdf:resource","info:fedora/$obj_specimen_pid");

    $datastream->appendChild($datastream_version);
    $datastream_version->appendChild($xmlContent);
    $xmlContent->appendChild($rdf);
    $rdf->appendChild($rdf_description);
    $rdf_description->appendChild($rdf_hasModel);
    $rdf_description->appendChild($rdf_description_member);
    $obj_rootElement->appendChild($datastream);
    return true;
  }

  function addImageMetadata_datastream($obj_pid = null, $obj_height = null, $obj_width = null, $obj_jp2URL = null, $obj_rft_id = null, $obj_sourceURL = null, &$obj_dom = null, &$obj_rootElement = null) {
  	if($obj_dom == null && $this->dom != null) {
  	  $obj_pid = $this->pid;
  	  $obj_dom = &$this->dom;
  	  $obj_rootElement = &$this->rootElement;
  	  $obj_height = $this->height;
  	  $obj_width = $this->width;
  	  $obj_jp2URL = $this->jp2URL;
  	  $obj_rft_id = $this->rft_id;
  	  $obj_sourceURL = $this->sourceURL;
  	}
  	else {
  	  echo 'No Dom Document available to addImageMetadata_datastream.<br>';
  	  return false;
  	}
    $datastream = $obj_dom->createElement("foxml:datastream");
    $datastream->setAttribute("ID","imageMetadata");
    $datastream->setAttribute("STATE","A");
    $datastream->setAttribute("CONTROL_GROUP","M");
    $version = $obj_dom->createElement("foxml:datastreamVersion");
    $obj_rootElement->appendChild($datastream);

    $version->setAttribute("ID","imageMetadata.0");
    $version->setAttribute("MIMETYPE","text/xml");
    $version->setAttribute("LABEL","Image Metadata");
    $obj_dom1 = new DomDocument("1.0", "UTF-8");
    $obj_dom1->formatOutput = true;

    //begin writing Metadata
    $api = $obj_dom1->createElement("api:imageMetadata_record");
    $api->setAttribute('xmlns:api',"http://www.apiaryproject.org/api");
    //imageMetadata elements
    $h_element = $obj_dom1->createElement('api:h',$obj_height);
    $api->appendChild($h_element);
    $w_element = $obj_dom1->createElement('api:w',$obj_width);
    $api->appendChild($w_element);
    $URL_element = $obj_dom1->createElement('api:URL',htmlentities($obj_jp2URL));
    $api->appendChild($URL_element);
    if($obj_rft_id != null && $obj_rft_id != '') {
      $rft_id_element = $obj_dom1->createElement('api:rft_id',$obj_rft_id);
      $api->appendChild($rft_id_element);
    }
    $sourceURL_element = $obj_dom1->createElement('api:sourceURL',htmlentities($obj_sourceURL));
    $api->appendChild($sourceURL_element);

    $obj_dom1->appendChild($api);

	$xml_file_pid = str_replace(':', '_', $obj_pid);
    $xml_file = '/var/www/drupal/sites/default/files/apiary_datastreams/'.$xml_file_pid.'-imageMetadata-content.xml';
    if(file_exists($xml_file)) {
      unlink($xml_file);
    }
    $obj_dom1->save($xml_file);
    global $base_url;
    $xml_file_Url = $base_url.'/sites/default/files/apiary_datastreams/'.$xml_file_pid.'-imageMetadata-content.xml';

    $imagecontent = $obj_dom->createElement('foxml:contentLocation');
    $imagecontent->setAttribute("REF","$xml_file_Url");
    $imagecontent->setAttribute("TYPE","URL");

    $datastream->appendChild($version);
    $version->appendChild($imagecontent);
    $obj_rootElement->appendChild($datastream);
    return true;
  }

  static function getROIListForImage($image_pid) {
    if($image_pid != null && $image_pid != '') {
      $query_string = 'select $roiMember
                       from <#ri>
                       where $roiMember <fedora-rels-ext:isMemberOf> <info:fedora/'.$image_pid.'>';
      $ROI_results_Dom = get_RIQueryResults($image_pid, $query_string);
      $ROI_results_Dom = new SimpleXMLElement($ROI_results_Dom);
      $list = array();
      foreach($ROI_results_Dom->xpath( '//@uri' ) as $uri) {
        $temp_pid = substr( strstr( $uri, '/' ), 1 );
        if(strpos($temp_pid, 'ap-roi:') > -1){
          array_push( $list,  $temp_pid);
        }
      }
      return $list;
    }
	else {
	  return false;
    }
  }

  function addJPEG_datastream($obj_URL = null, &$obj_dom = null, &$obj_rootElement = null) {
    if($obj_dom == null && $this->dom != null) {
      $obj_dom = &$this->dom;
      $obj_rootElement = &$this->rootElement;
      $obj_URL = $this->jpeg_datastream_url;
    }
    else {
      echo 'No Dom Document available to addJPEG_datastream.<br>';
      return false;
  	}
    $datastream = $obj_dom->createElement("foxml:datastream");
    $datastream->setAttribute("ID","JPEG");
    $datastream->setAttribute("STATE","A");
    $datastream->setAttribute("CONTROL_GROUP","E");

    $version = $obj_dom->createElement("foxml:datastreamVersion");
    $version->setAttribute("ID","JPEG.0");
    $version->setAttribute("MIMETYPE","image/jpeg");
    $version->setAttribute("LABEL","External reference to high-res jpeg");

    $location = $obj_dom->createElement("foxml:contentLocation");
    $location->setAttribute("TYPE","URL");
    $location->setAttribute("REF", $obj_URL);

    $version->appendChild($location);
    $datastream->appendChild($version);
    $obj_rootElement->appendChild($datastream);
    return true;
  }

  static function getimageMetadata_record($image_pid = null)
  {
    if($image_pid != null && $image_pid != '') {
      //returns imageMetadata_record
      return FedoraObject::getMetadata_record($image_pid, 'imageMetadata', 'imageMetadata_record', 'api');
    }
    else {
      return false;
    }
  }

  static function getImageLock($image_pid) {
    global $user;
    $now = date("YmdHis");
    $datastream_name = "status";
    $datastream_label = "Image Status";
    $session_id = $_SESSION['apiary_session_id'];

    $dom = FedoraObject::getManagedXMLDom($image_pid, $datastream_name);
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
            $dom->getElementsByTagName('locked_time')->item(0)->nodeValue =$now;
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
      $dom = AP_Image::generateImageStatusDom("true", $user->name, $now, $session_id);
    }

    //We don't get here if we fail
    if(FedoraObject::createManagedXMLDom($image_pid, $datastream_name, $datastream_label, $dom)) {
      include_once(drupal_get_path('module', 'apiary_project') . '/workflow/include/search.php');
      $search_instance = new search();
      $search_instance->index($image_pid);
      return FedoraObject::getManagedXMLDom($image_pid, $datastream_name);
    }
  }

  static function releaseImageLock($image_pid) {
    global $user;
    $now = date("YmdHis");
    $datastream_name = "status";
    $datastream_label = "Image Status";
    $session_id = $_SESSION['apiary_session_id'];
    if($session_id == '') {
      $session_id = $_SESSION['apiary_cleared_session_id'];
    }

    $dom = FedoraObject::getManagedXMLDom($image_pid, $datastream_name);
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
              //cannot unlock this record
              return false;
            }
          }
        }
      }
      $dom->getElementsByTagName('locked')->item(0)->nodeValue = "false";
    }
    else {
      //create status datastream!
      $dom = AP_Image::generateImageStatusDom("false", $user->name, $now, $session_id);
    }

    //We don't get here if we fail
    if(FedoraObject::createManagedXMLDom($image_pid, $datastream_name, $datastream_label, $dom)) {
      include_once(drupal_get_path('module', 'apiary_project') . '/workflow/include/search.php');
      $search_instance = new search();
      $search_instance->index($image_pid);
      return FedoraObject::getManagedXMLDom($image_pid, $datastream_name);
    }
  }

  static function generateImageStatusDom($locked, $locked_by = '', $locked_time = '', $locked_session = '') {
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
    $newElement = $dom->createElement("analyzedStatus", "");
    $rootElement->appendChild($newElement);
    $newElement = $dom->createElement("analyzedStatusUpdatedBy", "");
    $rootElement->appendChild($newElement);
    $newElement = $dom->createElement("qcStatus", "");
    $rootElement->appendChild($newElement);
    $newElement = $dom->createElement("qcStatusUpdatedBy", "");
    $rootElement->appendChild($newElement);
    return $dom;
  }

  static function get_specimen_pid($image_pid){
  	$rdf = FedoraObject::getManagedXMLDom($image_pid, "RELS-EXT");
  	$ip_string = $rdf->getElementsByTagName("hasModel")->item(0)->getAttributeNode("rdf:resource")->value;
  	$sp_pid = str_replace("info:fedora/","",$ip_string);
  	return $sp_pid;
  }
   static function getImageStatusDom($image_pid) {
    $datastream_name = "status";
    $datastream_label = "Image Status";

    if($dom = FedoraObject::getManagedXMLDom($image_pid, $datastream_name)) {
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
      $newElement = $dom->createElement("analyzedStatus", "");
      $rootElement->appendChild($newElement);
      $newElement = $dom->createElement("analyzedStatusUpdatedBy", "");
      $rootElement->appendChild($newElement);
      $newElement = $dom->createElement("qcStatus", "");
      $rootElement->appendChild($newElement);
      $newElement = $dom->createElement("qcStatusUpdatedBy", "");
      $rootElement->appendChild($newElement);
    }
    if(FedoraObject::createManagedXMLDom($image_pid, $datastream_name, $datastream_label, $dom)) {
      include_once(drupal_get_path('module', 'apiary_project') . '/workflow/include/search.php');
      $search_instance = new search();
      $search_instance->index($image_pid);
      return FedoraObject::getManagedXMLDom($image_pid, "status");
    }
    else {
      //can't update this datastream!
      return false;
    }
  }

  static function setImageStatus($image_pid, $status_type, $status) {
    global $user;
    $datastream_name = "status";
    $datastream_label = "Image Status";

    $dom = FedoraObject::getManagedXMLDom($image_pid, $datastream_name);
    if($dom != false) {
      $dom->getElementsByTagName($status_type)->item(0)->nodeValue = $status;
      $dom->getElementsByTagName($status_type.'UpdatedBy')->item(0)->nodeValue = $user->name;
    }
    else {
      //Shouldn't be able to get here as a lock is needed before a status can be updated.
      return false;
    }

    //We don't get here if we fail
    $success = FedoraObject::createManagedXMLDom($image_pid, $datastream_name, $datastream_label, $dom);
    if($success) {
      include_once(drupal_get_path('module', 'apiary_project') . '/workflow/include/search.php');
      $search_instance = new search();
      $search_instance->index($image_pid);
      return $dom;
    }
    else {
      //can't update this datastream!
      return false;
    }
  }
}

?>