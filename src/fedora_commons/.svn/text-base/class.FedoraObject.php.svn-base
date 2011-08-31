<?php
include_once("fedora_repository_connectors/functions_islandora.php");
include_once("fedoradb.php");

class FedoraObject {
  public $dom;
  public $rootElement;
  public $pid;
  public $label;
  public $state;
  public $ownerId;
  public $cDate;
  public $mDate;
  public $dcmDate;
  public $dcTitle;
  public $dcDescription;
  public $dcIdentifier;
  public $dcCreator;
  public $dcSubject;
  public $dcPublisher;
  public $dcContributor;
  public $dcDate;
  public $dcType;
  public $dcFormat;
  public $dcSource;
  public $dcLanguage;
  public $dcRelation;
  public $dcCoverage;
  public $dcRights;
  public $datastreams;
  public $isNewObject;

  function FedoraObject() {
    module_load_include('nc', 'FedoraObject', '');
    drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
  }
  function startFOXML($obj_pid = null, $obj_label = null, &$obj_dom = null, &$obj_rootElement = null) {
    if($obj_pid == null) {
	  if($this->pid != null) {
	    $obj_pid = $this->pid;
	    $obj_label = $this->label;
	  }
	  else {
	    echo 'No pid available to startFOXML.<br>';
	    return false;
	  }
	}
	if($obj_dom == null) {
      $this->dom = new DomDocument("1.0","UTF-8");
      $this->dom->formatOutput = true;
      $this->rootElement = $this->dom->createElement("foxml:digitalObject");
      $obj_dom = &$this->dom;
      $obj_rootElement = &$this->rootElement;
	}
    $obj_rootElement->setAttribute('VERSION','1.1');
    $obj_rootElement->setAttribute('PID',"$obj_pid");
    $obj_rootElement->setAttribute('xmlns:foxml',"info:fedora/fedora-system:def/foxml#");
    $obj_rootElement->setAttribute('xmlns:xsi',"http://www.w3.org/2001/XMLSchema-instance");
    $obj_rootElement->setAttribute('xsi:schemaLocation',"info:fedora/fedora-system:def/foxml# http://www.fedora.info/definitions/1/0/foxml1-1.xsd");
	$obj_dom->appendChild($obj_rootElement);
	if($this->addStandardFedoraFOXML()){
	  return true;
	}
	else {
	  echo 'Could not addStandardFedoraFOXML.<br>';
	  return false;
	}
  }

  static function get($pid) {
    $query = "select * from doFields where pid='".$pid."'";
    return get_data_row($query);
  }
  static function delete($pid) {
    return delete_digitalobject($pid);
  }

  static function getMetadata_record($obj_pid, $datastream_name, $Metadata_record_name = null, $obj_namespace = null) {

    if($datastream_name != null && $datastream_name != '') {
      if($obj_pid != null && $obj_pid != '') {
        //returns roiMetadata_record
        $params = array('pid' => "$obj_pid", 'dsID' => "$datastream_name", 'asOfDateTime' =>"");
        $object = get_DatastreamDissemination($params);
        $content=$object->dissemination->stream;
        $content=trim($content);
        $doc = new DOMDocument();
        if(!$doc->loadXML($content)){
          echo 'cannot load XMLDoc for '.$datastream_name.'.<br>';
          return false;
        }
        $elems=$doc->getElementsByTagName($Metadata_record_name);

        $Items=$elems->item(0)->getElementsByTagName('*');
        $form=array();
        for ($i = 0; $i < $Items->length; $i++)
        {
          $name=$Items->item($i)->nodeName;
          if($obj_namespace != null && $obj_namespace != '') {
            if(strpos($name, $obj_namespace.':') > -1){
              $record = str_replace($obj_namespace.':', '', $name);
              $roiMetadata_record[$record] = $Items->item($i)->nodeValue;
            }
          }
          else {
            $roiMetadata_record[$name] = $Items->item($i)->nodeValue;
          }
        }
        return $roiMetadata_record;
      }
      else {
        echo 'A pid must be sent to getMetadata_record.<br>';
        return false;
      }
    }
    else {
      echo 'A datastream must be sent to getMetadata_record.<br>';
      return false;
    }
  }

  static function getRIQueryResults($pid, $query) {
    return get_RIQueryResults($pid, $query);
  }

  function addStandardFedoraFOXML($obj_label = null, &$obj_dom = null, &$obj_rootElement = null) {
	if($obj_dom == null && $this->dom != null) {
	  $obj_dom = &$this->dom;
	  $obj_rootElement = &$this->rootElement;
	  $obj_label = $this->label;
	}
	else {
	  echo 'No Dom Document available to addStandardFedoraFOXML.<br>';
	  return false;
	}
    //this was adapted from ISLANDORA FormBuilder::createStandardFedoraStuff
    global $user;
    /*foxml object properties section */
    $objproperties = $obj_dom->createElement("foxml:objectProperties");
    $prop2 = $obj_dom->createElement("foxml:property");
    $prop2->setAttribute("NAME","info:fedora/fedora-system:def/model#state");
    $prop2->setAttribute("VALUE","A");
    $prop3 = $obj_dom->createElement("foxml:property");
    $prop3->setAttribute("NAME","info:fedora/fedora-system:def/model#label");
    $prop3->setAttribute("VALUE",$obj_label);
    $prop5 = $obj_dom->createElement("foxml:property");
    $prop5->setAttribute("NAME","info:fedora/fedora-system:def/model#ownerId");
    $prop5->setAttribute("VALUE",$user->name);
    //$objproperties->appendChild($prop1);
    $objproperties->appendChild($prop2);
    $objproperties->appendChild($prop3);
    $objproperties->appendChild($prop5);
    $obj_rootElement->appendChild($objproperties);
    return true;
  }

  function addDC_datastream($obj_pid = null, &$obj_dom = null, &$obj_rootElement = null, $obj_pid_base = null) {
    if($obj_dom == null) {
      if($this->pid != null && $this->dom != null && $this->rootElement != null) {
        $obj_pid = $this->pid;
        $obj_dom = &$this->dom;
        $obj_rootElement = &$this->rootElement;
      }
      else {
	    echo 'No pid or dom document available to addDC_datastream.<br>';
        return false;
      }
    }
    $datastream = $obj_dom->createElement("foxml:datastream");
    $datastream->setAttribute("ID","DC");
    $datastream->setAttribute("STATE","A");
    $datastream->setAttribute("CONTROL_GROUP","X");
    $version = $obj_dom->createElement("foxml:datastreamVersion");
    $version->setAttribute("ID","DC.0");
    $version->setAttribute("MIMETYPE","text/xml");
    $version->setAttribute("LABEL","Dublin Core Record");
    $datastream->appendChild($version);
    $content = $obj_dom->createElement("foxml:xmlContent");
    $version->appendChild($content);
    ///begin writing qdc
    $oai = $obj_dom->createElement("oai_dc:dc");
    $oai->setAttribute('xmlns:oai_dc',"http://www.openarchives.org/OAI/2.0/oai_dc/");
    $oai->setAttribute('xmlns:dc',"http://purl.org/dc/elements/1.1/");
    $oai->setAttribute('xmlns:dcterms',"http://purl.org/dc/terms/");
    $oai->setAttribute('xmlns:xsi',"http://www.w3.org/2001/XMLSchema-instance");
    $content->appendChild($oai);
    //dc elements
    $dc_title = $obj_dom->createElement('dc:title','Apiary '.$obj_pid_base.' object');
    $oai->appendChild($dc_title);
    $dc_identifier = $obj_dom->createElement('dc:identifier', $obj_pid);
    $oai->appendChild($dc_identifier);
    $dc_description = $obj_dom->createElement('dc:description','Apiary '.$obj_pid_base.' object used for herbarium sheet analysis.');
    $oai->appendChild($dc_description);
    $obj_rootElement->appendChild($datastream);
    return true;
  }

  static function createManagedXMLDom($pid, $datastream_name, $datastream_label, $dom) {
    $fedora_base_url = variable_get('fedora_base_url', 'http://localhost:8080/fedora');
    $curl_userpd = FEDORA_DATABASE_USERNAME.":".FEDORA_DATABASE_PASSWORD;
    $send = curl_init();
    $datastream_label = str_replace(' ', '%20', $datastream_label);
    $url = "$fedora_base_url/objects/$pid/datastreams/$datastream_name?controlGroup=M&dsLabel=$datastream_label&mimeType=text/xml";
	curl_setopt($send, CURLOPT_URL, $url);
    curl_setopt($send, CURLOPT_POST, 1);
    curl_setopt($send, CURLOPT_USERPWD, $curl_userpd);
    curl_setopt($send, CURLOPT_HEADER, false);
    curl_setopt($send, CURLOPT_POSTFIELDS, $dom->saveXML());
    if(curl_exec($send)) {
      $success = true;
    }
    else {
      $success = false;
    }
	curl_close($send);
	return $success;
  }

  static function getManagedXMLDom($pid, $datastream_name) {
    if(!isset($pid)) {
        return false;
    }
    $params = array('pid' => "$pid", 'dsID' => "$datastream_name", 'asOfDateTime' =>"");
    $object = get_DatastreamDissemination($params);
    $content=$object->dissemination->stream;
    $content=trim($content);
    $doc = new DOMDocument();
    if(!$doc->loadXML($content)){
      return false;
    }
    return $doc;
  }

  static function getTextFromDatastream($pid, $datastream_name) {
	return $text;
  }
}

?>