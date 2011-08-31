<?php

module_load_include('php', 'Apiary_Project', 'fedora_commons/class.FedoraObject');
class AP_Specimen extends FedoraObject{
  public $pid_base;
  public $msg;

  function AP_Specimen() {
    module_load_include('nc', 'AP_Specimen', '');
    drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
  }
  function createSpecimenObject($collector, $collection_number, $collection_date, $scientific_name, $obj_label) {
    $this->pid = $this->getNextSpecimenPid();
	if($obj_label != null && $label != '') {
      $this->label = $obj_label;
    }
    else {
      $this->label = $this->pid; //We may use something other than the pid for the label eventually
    }

	  //create relationships
	$this->pid_base = 'specimen';

    if($this->startFOXML()) {
	  if(!$this->addSpecimenRELS_EXT_datastream()){
	    echo 'Unable to addSpecimenRELS_EXT_datastream.<br>';
	  }
	  if(!$this->addDC_datastream()){
	    echo 'Unable to addDC_datastream.<br>';
	  }
	  if(!$this->addCoreSpecimenMetadata_datastream($collector, $collection_number, $collection_date, $scientific_name, $pid, $dom, $rootElement)){
	    echo 'Unable to addCoreSpecimenMetadata_datastream.<br>';
	  }
	  if(!$this->addSpecimenMetadata_datastream($pid, $dom, $rootElement)){
	    echo 'Unable to addCoreSpecimenMetadata_datastream.<br>';
	  }

	  $foxml_file = str_replace(':', '_', $this->pid);
      $foxml_file = '/var/www/drupal/sites/default/files/apiary_datastreams/'.$foxml_file.'.xml';
      if(file_exists($foxml_file)) {
        unlink($foxml_file);
      }
	  $this->dom->save($foxml_file);

	  if($object = ingest_object_from_FOXML($this->dom)) {
	    $this->msg = "$this->pid successfully created.";
	    return true;
	  }
	  else {
	    $this->msg = "Unable to ingest specimen FOXML dom document.";
	    return false;
	  }
	}
	else {
	  $this->msg = "Unable to start specimen FOXML file for create specimen object.";
      return false;
	}
  }

  function addSpecimenRELS_EXT_datastream($obj_pid = null, &$obj_dom = null, &$obj_rootElement = null) {
	if($obj_dom == null && $this->dom != null) {
	  $obj_dom = &$this->dom;
	  $obj_rootElement = &$this->rootElement;
	  $obj_pid = $this->pid;
	}
	else {
	  echo 'No Dom Document available to addSpecimenRELS_EXT_datastream.<br>';
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
    $rdf_description_member->setAttribute("rdf:resource","info:fedora/apiary:SpecimenBinders");

    $rdf_hasModel = $obj_dom->createElement("hasModel");
    $rdf_hasModel->setAttribute("xmlns","info:fedora/fedora-system:def/model#");
    $rdf_hasModel->setAttribute("rdf:resource","info:fedora/apiary:SpecimenBinders");

    $rdf_description_member2 = $obj_dom->createElement("isMemberOf");
    $rdf_description_member2->setAttribute("xmlns","info:fedora/fedora-system:def/relations-external#");
    $rdf_description_member2->setAttribute("rdf:resource","info:fedora/apiary:SpecimenBinders1");

    $rdf_hasModel2 = $obj_dom->createElement("hasModel");
    $rdf_hasModel2->setAttribute("xmlns","info:fedora/fedora-system:def/model#");
    $rdf_hasModel2->setAttribute("rdf:resource","info:fedora/demo:ex3CModel");

    $datastream->appendChild($datastream_version);
    $datastream_version->appendChild($xmlContent);
    $xmlContent->appendChild($rdf);
    $rdf->appendChild($rdf_description);
    $rdf_description->appendChild($rdf_hasModel);
    $rdf_description->appendChild($rdf_description_member);
    $rdf_description->appendChild($rdf_hasModel2);
    $rdf_description->appendChild($rdf_description_member2);
    $obj_rootElement->appendChild($datastream);
    return true;
  }

  function addSpecimenMetadata_datastream($obj_pid = null, &$obj_dom = null, &$obj_rootElement = null) {
    global $base_url;
    $rel_path = drupal_get_path('module','apiary_project');
	if($obj_pid == null && $this->pid != null) {
	  $obj_pid = $this->pid;
	}
	else {
	  echo 'No pid available to addSpecimenMetadata_datastream.<br>';
	  return false;
	}
    if($obj_dom == null && $this->dom != null) {
      $obj_dom = &$this->dom;
      $obj_rootElement = &$this->rootElement;
      $obj_URL = $base_url.'/'.$rel_path.'/fedora_commons/specimenmetadata.php?id='.$obj_pid;
    }
    else {
      echo 'No Dom Document available to addSpecimenMetadata_datastream.<br>';
      return false;
  	}
    $datastream = $obj_dom->createElement("foxml:datastream");
    $datastream->setAttribute("ID","specimenMetadata");
    $datastream->setAttribute("STATE","A");
    $datastream->setAttribute("CONTROL_GROUP","E");

    $version = $obj_dom->createElement("foxml:datastreamVersion");
    $version->setAttribute("ID","specimenMetadata.0");
    $version->setAttribute("MIMETYPE","text/xml");
    $version->setAttribute("LABEL","External refernce to combine all the ROI specimenMetadata for this specimen.");

    $location = $obj_dom->createElement("foxml:contentLocation");
    $location->setAttribute("TYPE","URL");
    $location->setAttribute("REF", $obj_URL);

    $version->appendChild($location);
    $datastream->appendChild($version);
    $obj_rootElement->appendChild($datastream);
    return true;
  }

  function addCoreSpecimenMetadata_datastream($collector, $collection_number, $collection_date, $scientific_name, $obj_pid = null, &$obj_dom = null, &$obj_rootElement) {
    global $base_url;
    $rel_path = drupal_get_path('module','apiary_project');
	if($obj_dom == null && $this->dom != null) {
	  $obj_dom = &$this->dom;
	  $obj_rootElement = &$this->rootElement;
	}
	else {
	  echo 'No Dom Document available to addSpecimenMetadata_datastream.<br>';
	  return false;
	}
	if($obj_pid == null && $this->pid != null) {
	  $obj_pid = $this->pid;
	}
	else {
	  echo 'No pid available to addSpecimenMetadata_datastream.<br>';
	  return false;
	}

    $datastream = $obj_dom->createElement("foxml:datastream");
    $datastream->setAttribute("ID","coreSpecimenMetadata");
    $datastream->setAttribute("STATE","A");
    $datastream->setAttribute("CONTROL_GROUP","M");
    $version = $obj_dom->createElement("foxml:datastreamVersion");

    $version->setAttribute("ID","coreSpecimenMetadata.0");
    $version->setAttribute("MIMETYPE","text/xml");
    $version->setAttribute("LABEL","Core Specimen metadata assigned when the specimen is first ingested.");

	$xml = new DOMDocument();
	$xml->load($base_url.'/'.$rel_path.'/workflow/assets/xml/ApiaryTemplate.xml');
	if(isset($collector)) {
	  $xml->getElementsByTagName('recordedBy')->item(0)->nodeValue = $collector;
	}
	if(isset($collection_number)) {
	  $xml->getElementsByTagName('recordNumber')->item(0)->nodeValue = $collection_number;
	}
	if(isset($collection_date)) {
	  $xml->getElementsByTagName('verbatimEventDate')->item(0)->nodeValue = $collection_date;
	}
	if(isset($scientific_name)) {
	  $xml->getElementsByTagName('verbatimScientificName')->item(0)->nodeValue = $scientific_name;
	}
    //assign writing Metadata

	$xml_file_pid = str_replace(':', '_', $obj_pid);
    $xml_file = '/var/www/drupal/sites/default/files/apiary_datastreams/'.$xml_file_pid.'-coreSpecimenMetadata-content.xml';
    if(file_exists($xml_file)) {
      unlink($xml_file);
    }
    $xml->save($xml_file);
    $xml_file_Url = $base_url.'/sites/default/files/apiary_datastreams/'.$xml_file_pid.'-coreSpecimenMetadata-content.xml';

    $sp_content = $obj_dom->createElement('foxml:contentLocation');
    $sp_content->setAttribute("REF","$xml_file_Url");
    $sp_content->setAttribute("TYPE","URL");

    $version->appendChild($sp_content);
    $datastream->appendChild($version);
    $obj_rootElement->appendChild($datastream);
    return true;
  }

  function getNextSpecimenPid() {
    $existing_pids = $this->getAllSpecimenPids();
    $largest_pid_number = 0;
    foreach($existing_pids as $existing_pid) {
      $pid_substr = str_replace('ap-specimen:Specimen-', '', $existing_pid);
      $pid_number = (int)$pid_substr;
      if($pid_number > $largest_pid_number) {
        $largest_pid_number = $pid_number;
      }
    }
    $largest_pid_number++;
    return 'ap-specimen:Specimen-'.$largest_pid_number;
  }

  static function getAllSpecimenPids() {
    $query = "select pid from doFields where pid like 'ap-specimen:%'";
    $rows = get_data_rows($query);
    $list = array();
    foreach($rows as $row) {
      array_push( $list,  $row['pid']);
    }
    return $list;
  }

  static function getSpecimenList($query_string = null) {
    //module_load_include('php', 'Fedora_Repository', 'CollectionClass');
    //$collectionClass= new CollectionClass();
    if($query_string == null || $query == '') {
      $query_string = 'select $sp_pid from <#ri> where $sp_pid<fedora-rels-ext:isMemberOf> <info:fedora/apiary:SpecimenBinders>';
    }
    $pid_objects= get_RIQueryResults('apiary:SpecimenBinders', $query_string);
    $sxml = new SimpleXMLElement( $pid_objects);
    $list = array();
    foreach( $sxml->xpath( '//@uri' ) as $uri ) {
      array_push( $list,  substr( strstr( $uri, '/' ), 1 ));
    }
    return $list;
  }

  static function getImageListForSpecimen($specimen_pid) {
    if($specimen_pid != null && $specimen_pid != '') {
      $query_string = 'select $roiMember
                       from <#ri>
                       where $roiMember <fedora-rels-ext:isMemberOf> <info:fedora/'.$specimen_pid.'>';
      $results = get_RIQueryResults($specimen_pid, $query_string);
      $results_Dom = new SimpleXMLElement($results);
      $list = array();
      foreach($results_Dom->xpath( '//@uri' ) as $uri) {
        $result_pid = substr( strstr( $uri, '/' ), 1 );
        if(strpos($result_pid, 'ap-image:') > -1){
          array_push( $list,  $result_pid);
        }
      }
      return $list;
    }
	else {
	  return false;
    }
  }

  static function getspecimenMetadata_record($specimen_pid = null)
  {
    if($specimen_pid == null) {
        $specimen_pid = $this->pid;
    }
    if($specimen_pid != null && $specimen_pid != '') {
      //returns specimenMetadata_record
      return FedoraObject::getMetadata_record($specimen_pid, 'specimenMetadata', 'SpecimenInstance', 'ns1');
    }
    else {
      return false;
    }
  }

  static function isSpecimenLocked($specimen_pid = null)
  {
    $locked = true;
    if($specimen_pid == null) {
        $specimen_pid = $this->pid;
    }
    if($specimen_pid != null && $specimen_pid != '') {
      //returns specimenMetadata_record
      return FedoraObject::getMetadata_record($specimen_pid, 'specimenMetadata', 'specimenMetadata_record', 'api');
    }
    else {
      return false;
    }
    return $locked;
  }

  static function lockSpecimen($specimen_pid = null)
  {
    global $user;
    $timestamp = date("YmdHis");
    if($specimen_pid == null) {
        $specimen_pid = $this->pid;
    }
    if($specimen_pid != null && $specimen_pid != '') {
      //returns specimenMetadata_record
      return FedoraObject::getMetadata_record($specimen_pid, 'specimenMetadata', 'specimenMetadata_record', 'api');
    }
    else {
      return false;
    }
    return $locked;
  }

  static function unlockSpecimen($specimen_pid = null)
  {
    global $user;
    $timestamp = date("YmdHis");
    if($specimen_pid == null) {
        $specimen_pid = $this->pid;
    }
    if($specimen_pid != null && $specimen_pid != '') {
      //returns specimenMetadata_record
      return FedoraObject::getMetadata_record($specimen_pid, 'specimenMetadata', 'specimenMetadata_record', 'api');
    }
    else {
      return false;
    }
    return $locked;
  }

}
?>