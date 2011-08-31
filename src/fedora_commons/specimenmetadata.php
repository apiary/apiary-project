<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
include_once('../workflow/include/search.php');
$specimen_pid = $_GET['id'];
$xml = new DOMDocument();

$url = (!empty($_SERVER['HTTPS'])) ? 'https://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] : 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
$base_url = substr($url, 0, strpos($url, '/fedora_commons/'));
$fedora_url = substr($url, 0, strpos($url, '/', 9)).':8080/fedora';
if(doesDatastreamExist('coreSpecimenMetadata', $specimen_pid, $fedora_url)) {
  $xml_content = getDatastream('coreSpecimenMetadata', $specimen_pid, $fedora_url);
  $xml->loadXML($xml_content);
}
else {
  $xml->load($base_url.'/workflow/assets/xml/ApiaryTemplate.xml');
}

$specimen_metaData_elements = array();
$image_pids = get_children_via_solr($specimen_pid);
foreach($image_pids as $image_pid) {
  $specimen_metaData_elements = process_image_children_specimenMetadata_via_solr($image_pid, $specimen_metaData_elements);
}
foreach($specimen_metaData_elements as $sm_element=>$sm_value) {
  $xml->getElementsByTagName($sm_element)->item(0)->nodeValue = $sm_value;
}
echo $xml->saveXML();

function doesDatastreamExist($dsName, $specimen_pid, $fedora_url){
  $curl_handle=curl_init();
  $url = $fedora_url."/get/".$specimen_pid."/".$dsName;
  curl_setopt($curl_handle, CURLOPT_URL, $url);
  curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
  curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
  $output = curl_exec($curl_handle);
  curl_close($curl_handle);
  if(strpos($output, "404 Not Found")==FALSE) {
    return TRUE;
  }
  else {
    return FALSE;
  }
}

function getDatastream($dsName, $specimen_pid, $fedora_url){
  $curl_handle=curl_init();
  $url = $fedora_url."/get/".$specimen_pid."/".$dsName;
  curl_setopt($curl_handle, CURLOPT_URL, $url);
  curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
  curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
  $output = curl_exec($curl_handle);
  curl_close($curl_handle);
  return $output;
}

function get_parent_pid_via_solr($pid) {
  $solr_q = 'q=id:("'.$pid.'")';
  $solr_fl = 'fl=parent_id';
  $solr_op = '';
  $solr_rows = '';
  $solr_sxml = solr_query_xml($solr_q, $solr_fl, $solr_op, $solr_rows);
  return (string)$solr_sxml->result[0]->doc[0]->str[0];
}

function get_children_via_solr($parent_id) {
  $child_list = array();
  $solr_q = 'q=parent_id:("'.$parent_id.'")';
  $solr_fl = 'fl=id';
  $solr_op = '';
  $solr_rows = 'rows=10000';
  $solr_sxml = solr_query_xml($solr_q, $solr_fl, $solr_op, $solr_rows);
  foreach($solr_sxml->result[0]->doc as $doc) {
    foreach($doc->children() as $sxml_node) {
      if($sxml_node->attributes()->name == 'id') {
        array_push($child_list, (string)$sxml_node);
      }
    }
  }
  return $child_list;
}

function process_image_children_specimenMetadata_via_solr($image_pid, $specimen_metaData_elements) {
  $solr_q = 'q=parent_id:("'.$image_pid.'")';
  $solr_fl = '';
  $solr_op = '';
  $solr_rows = 'rows=10000';
  $solr_sxml = solr_query_xml($solr_q, $solr_fl, $solr_op, $solr_rows);
  foreach($solr_sxml->result[0]->doc as $doc) {
    foreach($doc->children() as $sxml_node) {
      $name = $sxml_node->attributes()->name;
      if(strpos($name, 'roism_') > -1) {
        $sm_name = str_replace('roism_', '', $name);
        $sm_value = trim((string)$sxml_node);
        if($sm_value != null && $sm_value != "") {
          if($specimen_metaData_elements[$sm_name] != null && $specimen_metaData_elements[$sm_name] != "") {
            $specimen_metaData_elements[$sm_name] = $specimen_metaData_elements[$sm_name]." ; ".$sm_value;
          }
          else {
            $specimen_metaData_elements[$sm_name] = $sm_value;
          }
        }
      }
    }
  }
  return $specimen_metaData_elements;
}

function solr_query_xml($q, $fl = '', $op = '', $rows = '') {
  $solr_search = new search();
  if(strpos($q, "q=") === false) {
    $q = 'q='.str_replace('&', '', $q);
  }
  $solr_query = $q;
  if(!empty($fl)) {
    if(strpos($fl, 'fl=') === false) {
      $fl = 'fl='.$fl;
    }
    $solr_query .= '&'.str_replace('&', '', $fl);
  }
  if(!empty($op)) {
    if(strpos($op, 'op=') === false) {
      $op = 'op='.$op;
    }
    $solr_query .= '&'.str_replace('&', '', $op);
  }
  if(!empty($rows)) {
    if(strpos($rows, 'rows=') === false) {
      $rows = 'rows='.$rows;
    }
    $solr_query .= '&'.str_replace('&', '', $rows);
  }
  //echo "solr_query = ".$solr_query."<br>\n";
  $solr_results = $solr_search->doSearch($solr_query);
  if($solr_results != false) {
    $solr_sxml = new SimpleXMLElement($solr_results);
    return $solr_sxml;
  }
  else {
    return false;
  }
}
?>