<?php
class roiHandler{
	public $pid, $ocrText, $roiMetadata, $xmlContainer, $ocrTextExist, $parseXMLExist;
	private $server_base;
	private $fedora_base;
	public function __construct($current_pid){
		$this->server_base = variable_get('apiary_research_base_url', 'http://localhost');
		$this->fedora_base = variable_get('fedora_base_url', 'http://localhost:8080/fedora');
		$this->pid = $current_pid;
		if(strpos($this->pid, 'ap-roi:') > -1) {
		  $this->ocrTextExist = $this->ifExist("Text");
		  if($this->ocrTextExist==TRUE){
            $this->ocrText = $this->getDatastream("Text");
		  }
		  $this->parseXMLExist = $this->ifExist("specimenMetadata");
		  if($this->parseXMLExist==TRUE){
			$this->xmlContainer = $this->getDatastream("specimenMetadata");
		  }
		}
	}

	public function ifExist($dsType){
		$curl_handle=curl_init();
		$url = $this->fedora_base."/get/".$this->pid."/".$dsType;
		curl_setopt($curl_handle, CURLOPT_URL, $url);
		curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($curl_handle);
		curl_close($curl_handle);
		if(strpos($output, "404 Not Found")==FALSE)
		return TRUE;
		else
		return FALSE;
	}

	public function getDatastream($dsType){
		$curl_handle=curl_init();
		$url = $this->fedora_base."/get/".$this->pid."/".$dsType;
		curl_setopt($curl_handle, CURLOPT_URL, $url);
		curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($curl_handle);
		curl_close($curl_handle);
		return $output;
	}

	public function setDatastream($dsType, $dsLabel, $mimeType, $content, $credential){
		$url = $this->fedora_base."/objects/$this->pid/datastreams/$dsType?controlGroup=M&dsLabel=$dsLabel&mimeType=$mimeType";
		$send = curl_init();
		//$userAgent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)';
		curl_setopt($send, CURLOPT_URL, $url);
		curl_setopt($send, CURLOPT_POST, 1);
		curl_setopt($send, CURLOPT_USERAGENT, $userAgent);
		curl_setopt($send, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($send, CURLOPT_USERPWD, $credential);
		curl_setopt($send, CURLOPT_HEADER, true);
		curl_setopt($send, CURLOPT_POSTFIELDS, $content);
		$exec = curl_exec($send);
		if(!curl_errno($send)){
			$info = curl_getinfo($send);
			curl_close($send);
			if($info['http_code'] == 200 || $info['http_code'] == 201 || $info['http_code'] == 202){
				return true;
			}
			else{
				return false;
			}
		}
		else{
			curl_close($send);
			return false;
		}
	}

	public function removeROI($pid, $credential){
		$curl_handle=curl_init();
		$url = $this->fedora_base."/objects/".$this->pid;
		curl_setopt($curl_handle, CURLOPT_URL, $url);
		curl_setopt($curl_handle, CURLOPT_USERPWD, $credential);
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl_handle, CURLOPT_HEADER, true);
		curl_setopt($curl_handle, CURLOPT_CUSTOMREQUEST, "DELETE");
		$output = curl_exec($curl_handle);
		if(!curl_errno($curl_handle)){
			$info = curl_getinfo($curl_handle);
			curl_close($curl_handle);
			if($info['http_code']==204 || $info['http_code']==200){
				return true;
			}
			else{
				return false;
			}
		}
		else{
			curl_close($curl_handle);
			return false;
		}
	}

	public function get_ubio(){
		$curl_handle = curl_init();
		//$text = iconv("UTF-8","Windows-1252",$this->ocrText);
		$text = urlencode($this->ocrText);
		$url = "http://www.ubio.org/webservices/service.php?function=taxonFinder&freeText=".$text."&includeLinks=1";
		curl_setopt($curl_handle, CURLOPT_URL, $url);
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($curl_handle);
		return $output;
	}

	public function get_herbis($herbis_dir, $herbis_url){
		$text = $this->ocrText;
		$text = $this->replace_newline($text);
		$herbis_cmd = "java -classpath $herbis_dir/lib/jahmm-0.3.1.jar:$herbis_dir/lib/xmlrpc-2.0.jar:$herbis_dir/lib/commons-codec-1.3.jar:. org.apache.xmlrpc.XmlRpcClientLite $herbis_url/tagger HMMProcess.run \"$text\"";
		$return_xml = shell_exec($herbis_cmd);
		$labeldata_index = strpos($return_xml, '<labeldata>');
		$return_xml = substr($return_xml, $labeldata_index);
		$return_xml = str_replace("\n", "", $return_xml);
		$return_xml = str_replace("\r", "", $return_xml);
		$return_xml = trim($return_xml);
		return $return_xml;
	}

	function replace_newline($string) {
	  $string = (string)str_replace(array("\r", "\r\n", "\n"), ' ', $string);//replace linebreaks with space
	  $string = (string)str_replace(array('  ', '   ', '    '), ' ', $string);//replace multiple spaces with a sigle space
	  $string = (string)str_replace(array('  ', '   ', '    '), ' ', $string);//a second time for good measure because this does not add much overhead
	  return $string;
	}
}
?>