<?php
$drupal_path = "/var/www/drupal";
$cdir = getcwd();
chdir($drupal_path);
require_once './includes/bootstrap.inc';
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
module_load_include('php', 'Apiary_Project', 'fedora_commons/class.AP_ROI');
module_load_include('php', 'Apiary_Project', 'fedora_commons/class.AP_Image');
include_once 'roiHandler.php';
$attempts = 0;
$http_code = '';
class search{
	public $query_string, $result_doc, $result_html;

	public function __construct(){

	}

	public function index($pid){
  		if(strpos($pid, 'ap-image:') > -1) {
  		  $this->index_image($pid);
  		}
  		else if(strpos($pid, 'ap-roi:') > -1) {
  		  $this->index_roi($pid);
  		}
	}

	public function index_roi($pid){
		$dom = new DOMDocument('1.0', 'iso-8859-1');
		$addElement = $dom->createElement('add', '');
		$dom->appendChild($addElement);
		$docElement = $dom->createElement('doc', '');
		$addElement->appendChild($docElement);
		$fieldElement = $dom->createElement('field', $pid);
		$fieldElement->setAttribute("name","id");
		$docElement->appendChild($fieldElement);

		$curr_pid = new roiHandler($pid);
		if($curr_pid->ifExist("RELS-EXT")) {
			$xml = new DOMDocument('1.0', 'iso-8859-1');
			$xml->loadXML($curr_pid->getDatastream("RELS-EXT"));
  			$ip_string = $xml->getElementsByTagName("hasModel")->item(0)->getAttributeNode("rdf:resource")->value;
  			$image_pid = str_replace("info:fedora/","",$ip_string);
			$fieldElement = $dom->createElement('field', $image_pid);
			$fieldElement->setAttribute("name","parent_id");
			$docElement->appendChild($fieldElement);
  		}
		if($curr_pid->parseXMLExist){
			$xml = new DOMDocument('1.0', 'iso-8859-1');
			$xml->loadXML($curr_pid->xmlContainer);
			$ApiaryElement = $xml->getElementsByTagName("ApiaryElements")->item(0);
			foreach($ApiaryElement->childNodes as $ApiaryTerm){
			  //I really cannot figure out why this is reading some nodeNames as #text but this is causing severe errors in the indexing
			  //The xml from the getDatastream loooks great both in the roiHandler and the xml here
			  //this is indended as a workaround but has been apparently working
			  if($ApiaryTerm->nodeName != '#text') {
				$searchValue = $ApiaryTerm->nodeValue;
				$searchField = "roism_".$ApiaryTerm->nodeName;
				$fieldElement = $dom->createElement('field', $searchValue);
				$fieldElement->setAttribute("name",$searchField);
				$docElement->appendChild($fieldElement);
              }
			}
		}
		if($curr_pid->ifExist("status")){
			$xml = new DOMDocument('1.0', 'iso-8859-1');
			$xml->loadXML($curr_pid->getDatastream("status"));
			$StatusesElement = $xml->getElementsByTagName("statuses")->item(0);
			foreach($StatusesElement->childNodes as $theTerm){
				$searchValue = $theTerm->nodeValue;
				if($theTerm->nodeName!="locked_time")
				$searchField = "status_".$theTerm->nodeName;
				else
				$searchField = "locked_time";
				$fieldElement = $dom->createElement('field', $searchValue);
				$fieldElement->setAttribute("name",$searchField);
				$docElement->appendChild($fieldElement);
			}
		}
		$this->result_doc = $dom;
		if(!$this->update_index()){
			echo "Error Occurred while update index of ROI: $pid";
		}
	}

	public function index_image($pid){
		$dom = new DOMDocument('1.0', 'iso-8859-1');
		$addElement = $dom->createElement('add', '');
		$dom->appendChild($addElement);
		$docElement = $dom->createElement('doc', '');
		$addElement->appendChild($docElement);
		$fieldElement = $dom->createElement('field', $pid);
		$fieldElement->setAttribute("name","id");
		$docElement->appendChild($fieldElement);

		$curr_pid = new roiHandler($pid); //we may need to create an imageHandler but should decide on moving ahead with the curl route or using islandora and stick with one route
		if($curr_pid->ifExist("RELS-EXT")) {
			$xml = new DOMDocument('1.0', 'iso-8859-1');
			$xml->loadXML($curr_pid->getDatastream("RELS-EXT"));
  			$ip_string = $xml->getElementsByTagName("hasModel")->item(0)->getAttributeNode("rdf:resource")->value;
  			$specimen_pid = str_replace("info:fedora/","",$ip_string);
			$fieldElement = $dom->createElement('field', $specimen_pid);
			$fieldElement->setAttribute("name","parent_id");
			$docElement->appendChild($fieldElement);
  		}
		if($curr_pid->ifExist("status")){
			$xml = new DOMDocument('1.0', 'iso-8859-1');
			$xml->loadXML($curr_pid->getDatastream("status"));
			$StatusesElement = $xml->getElementsByTagName("statuses")->item(0);
			foreach($StatusesElement->childNodes as $theTerm){
				$searchValue = $theTerm->nodeValue;
				if($theTerm->nodeName!="locked_time")
				$searchField = "status_".$theTerm->nodeName;
				else
				$searchField = "locked_time";
				$fieldElement = $dom->createElement('field', $searchValue);
				$fieldElement->setAttribute("name",$searchField);
				$docElement->appendChild($fieldElement);
			}
		}
		if($curr_pid->ifExist("imageMetadata")){
			$xml = new DOMDocument('1.0', 'iso-8859-1');
			$xml->loadXML($curr_pid->getDatastream("imageMetadata"));
			$imageMetadataElement = $xml->getElementsByTagName("imageMetadata_record")->item(0);
			foreach($imageMetadataElement->childNodes as $theTerm){
			  if($theTerm->nodeName != '#text') {
				$searchValue = $theTerm->nodeValue;
				$searchField = "imageMetadata_".str_replace("api:", "", $theTerm->nodeName);
				$fieldElement = $dom->createElement('field', $searchValue);
				$fieldElement->setAttribute("name",$searchField);
				$docElement->appendChild($fieldElement);
              }
			}
		}

		$this->result_doc = $dom;
		if(!$this->update_index()){
			echo "Error Occurred while update index of Image: $pid";
		}
	}

	public function index_all_rois(){
		$roi_list = AP_ROI::getROIList();
		foreach($roi_list as $roi) {
		  $this->index_roi($roi);
		}
	}

	public function index_all(){
		$image_list = AP_Image::getImageList();
		foreach($image_list as $image) {
		  $this->index($image);
		}

		$roi_list = AP_ROI::getROIList();
		foreach($roi_list as $roi) {
		  $this->index($roi);
		}
	}

	public function delete_all_index() {
		$delete_dom = new DOMDocument('1.0', 'iso-8859-1');
		$deleteElement = $delete_dom->createElement('delete', '');
		$delete_dom->appendChild($deleteElement);
		$docElement = $delete_dom->createElement('query', "*:*");
		$deleteElement->appendChild($docElement);
		$header = array("Content-type:text/xml; charset=utf-8");
		$send = curl_init();
		curl_setopt($send, CURLOPT_URL, "http://localhost:8983/solr/update?commit=true");
		curl_setopt($send, CURLOPT_POST, 1);
		curl_setopt($send, CURLOPT_HTTPHEADER, $header);
		curl_setopt($send, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($send, CURLOPT_POSTFIELDS, $delete_dom->saveXML());
		curl_setopt($send, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($send, CURLINFO_HEADER_OUT, 1);
		curl_exec($send);
		if(curl_errno($send)) {
		  return false;
		}
		else{
			$info = curl_getinfo($send);
			if($info['http_code']==200){
				return true;
			}
			else{
				return false;
			}
		}
	}

	public function doSearch($query){
		$this->query_string = $query;
		$send = curl_init();
		curl_setopt($send, CURLOPT_URL, "http://localhost:8983/solr/select/?$query");
		curl_setopt($send, CURLOPT_RETURNTRANSFER, 1);
		$xml = curl_exec($send);
		if(curl_errno($send)){
			return false;
		}
		else{
			$info = curl_getinfo($send);
			if($info['http_code']==200){
				return $xml;
			}

			else{
				return false;
			}
		}
	}

	public function update_index(){
		//$header = array('Content-Type:text/xml; charset=utf-8');
		if($this->attempts < 3) {
		  $dom = $this->result_doc;
		  $dom_xml = $dom->saveXML();
		  $header = array("Content-Type: text/xml", "Content-length: ".strlen($dom_xml));
		  //echo '<br>Dom Save XML<br>'."\n".$dom->saveXML().'<br>End Dom Save XML'."\n";
		  $send = curl_init();
		  curl_setopt($send, CURLOPT_URL, "http://localhost:8983/solr/update?commit=true");
		  curl_setopt($send, CURLOPT_POST, 1);
		  curl_setopt($send, CURLOPT_HTTPHEADER, $header);
		  curl_setopt($send, CURLOPT_RETURNTRANSFER, 1);
		  curl_setopt($send, CURLOPT_POSTFIELDS, $dom_xml);
		  curl_setopt($send, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		  curl_setopt($send, CURLINFO_HEADER_OUT, 1);
		  curl_exec($send);
		  if(curl_errno($send)) {
		    echo 'error here';
		    return false;
		  }
		  else{
		    $info = curl_getinfo($send);
		    if($info['http_code']==200){
		      $this->attempts = 0;
		      return true;
		    }
			else {
		      $this->attempts++;
			  $this->http_code = 'http_code = '.$info['http_code'].'<br>'."\n";
			  $this->update_index();
			}
		  }
		}
		else{
		  $this->attempts = 0;
		  echo 'http_code = '.$this->http_code.'<br>'."\n";
		  $dom = $this->result_doc;
		  echo 'saveXML: '.$dom->saveXML().'<br>'."\n";
		  echo 'End saveXML';
          return false;
		}
	}

	public function delete_index($pid){
		$delete_dom = new DOMDocument('1.0', 'iso-8859-1');
		$deleteElement = $delete_dom->createElement('delete', '');
		$delete_dom->appendChild($deleteElement);
		$docElement = $delete_dom->createElement('id', $pid);
		$deleteElement->appendChild($docElement);
		$header = array("Content-type:text/xml; charset=utf-8");
		$send = curl_init();
		curl_setopt($send, CURLOPT_URL, "http://localhost:8983/solr/update?commit=true");
		curl_setopt($send, CURLOPT_POST, 1);
		curl_setopt($send, CURLOPT_HTTPHEADER, $header);
		curl_setopt($send, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($send, CURLOPT_POSTFIELDS, $delete_dom->saveXML());
		curl_setopt($send, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($send, CURLINFO_HEADER_OUT, 1);
		curl_exec($send);
		if(curl_errno($send)) {
		  return false;
		}
		else{
			$info = curl_getinfo($send);
			if($info['http_code']==200){
				return true;
			}
			else{
				return false;
			}
		}
	}
}