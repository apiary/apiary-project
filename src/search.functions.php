<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
global $user;
include_once(drupal_get_path('module', 'apiary_project').'/apiaryPermissionsClass.php');
include_once(drupal_get_path('module', 'apiary_project').'/workflow/include/search.php');
include_once(drupal_get_path('module', 'apiary_project').'/workflow/include/functions.php');

function send_request($function, $param1, $param2, $param3){
  //echo "function = ".$function." param1 = ".$param1." param2 = ".$param2." param3 = ".$param3;
  if($param3 != "0") {
    call_user_func($function, $param1, $param2, $param3);
  }
  else if($param2 != "0") {
    call_user_func($function, $param1, $param2);
  }
  else if($function == "clear_session") {
    call_user_func($function);
  }
  else {
    call_user_func($function, $param1);
  }
}

function get_search() {
  $server_base = variable_get('apiary_research_base_url', 'http://localhost');
  $home_link = '<p><h3><a href="'.$server_base.'/drupal">Home</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="'.$server_base.'/drupal/apiary/admin">Administer Apiary</a></h3></p>';
  $return_html = '';
  $return_html .= $home_link;
  $return_html .= '<div style="font-size:150%; font-weight:bold;"> Apiary Search </div>';
  $return_html .= '<br/>';
  $return_html .= '<table id="search_types_table" cellspacing="0" border="0" width="30%" style="height:22px;">';
  $return_html .= '<tbody>';
  $return_html .= '<tr>';
  $return_html .= '<td><a href="#" onclick="select_search(\'metadata_search\');">ROI specimenMetadata Search</a></td>';
  $return_html .= '<td><a href="#" onclick="select_search(\'status_search\');">Image or ROI Status Search</a></td>';
  $return_html .= '</tr>';
  $return_html .= '</tbody>';
  $return_html .= '</table><!-- search_types_table -->';
  $return_html .= '<br/>';
  $return_html .= '<div id="search_options">';
  $return_html .= metadata_search_options();
  $return_html .= '</div><!-- search_options -->';
  echo $return_html;
}

function get_metadata_search_options() {
  echo metadata_search_options();
}

function metadata_search_options() {
  $return_html = '';
  $return_html .= '<b>Search ROIs by specimenMetadata keyword</b>';
  $return_html .= '<table cellspacing="0" cellpadding="4" border="1" rules="none" frame="box">';
  $return_html .= '<tbody>';
  $return_html .= '<tr valign="top">';
  $return_html .= '<td align="center" nowrap="">';
  $return_html .= 'Field(s):'.metadata_combobox();
  $return_html .= '<input name="metadata_query" id="metadata_query" onkeypress="keyPressEvent(event, \'submit_metadata_search\');" size="30" title="Apiary Search" class="lst" maxlength="2048" style="background: none repeat scroll 0% 0% rgb(255, 255, 255); border-width: 1px; border-style: solid; border-color: rgb(204, 204, 204) rgb(153, 153, 153) rgb(153, 153, 153) rgb(204, 204, 204); color: rgb(0, 0, 0); font: 18px arial, sans-serif bold; height: 25px; margin: 0pt; padding: 5px 8px 0pt 6px; vertical-align: top;">';
  $return_html .= "<button onclick='submit_metadata_search();' style='margin: 5px 0px; border: 1px solid grey;'>Submit</button>"."\n";
  $return_html .= '</td>';
  $return_html .= '</tr>';
  $return_html .= '</tbody>';
  $return_html .= '</table>';
  return $return_html;
}

function metadata_combobox($curr_field = '') {
  $return_html = '<select id="metadata_field">';
  $return_html .= "<option value=''></option>";
  $whenObj = db_query("select term, display_label from {apiary_project}");
  while($res = db_fetch_object($whenObj)){
    if($curr_field != "$res->term") {
      $return_html .= "<option value='$res->term'>$res->display_label</option>";
    }
    else {
      $return_html .= "<option SELECTED value='$res->term'>$res->display_label</option>";
    }
  }
  $return_html .= '</select>';
  return $return_html;
}

function search_metadata() {
  $successfully_searched_text = "false";
  $results_html = '';
  $msg = '';
  $query = trim($_POST['query']);
  $field = $_POST['field'];
  $query = str_replace(" ","+",$query);
  if($field) {
    $solr_q = 'q=roism_'.$field.':("'.$query.'")';
  }
  else {
    $solr_q = 'q="'.$query.'"';
  }
  $solr_fl = 'fl=id';
  $solr_op = '';
  $solr_rows = 'rows=10000';
  $solr_sxml = solr_query_search($solr_q, $solr_fl, $solr_op, $solr_rows);
  $elements= array();
  if($solr_sxml) {
    $successfully_searched_text = "true";
    $dom = new DOMDocument();
    $dom->loadXML($solr_sxml);
    $xpath = new DOMXPath($dom);
    $elements = $xpath->query('//response/result/doc/str[@name="id"]');
    $roi_pids = array();
    foreach($elements as $element){
      if(strpos($element->nodeValue, 'ap-roi') > -1) {
        array_push($roi_pids, $element->nodeValue);
      }
    }
    $results_html .= ROISearchResults($roi_pids);
  }
  else {
    $msg .= "Unable to perform serach!";
  }
  $returnJSON['successfully_searched_text'] = $successfully_searched_text;
  $returnJSON['solr_q'] = $solr_q;
  $returnJSON['query'] = $query;
  $returnJSON['field'] = $field;
  $returnJSON['results_html'] = $results_html;
  $returnJSON['msg'] = $msg;
  echo json_encode($returnJSON);
}

function get_status_search_options() {
  echo status_search_options();
}

function status_search_options() {
  $return_html = '';
  $return_html .= '<b>Search Objects by Status</b>';
  $return_html .= '<table cellspacing="0" cellpadding="4" border="1" rules="none" frame="box">';
  $return_html .= '<tr>';
  $return_html .= '<td>Image Analyze Status:</td>';
  $return_html .= '<td>'.analyzeStatus_combobox().'</td>';
  $return_html .= '</tr>';
  $return_html .= '<tr>';
  $return_html .= '<td>ROI Transcribe Status:</td>';
  $return_html .= '<td>'.transcribeStatus_combobox().'</td>';
  $return_html .= '</tr>';
  $return_html .= '<tr>';
  $return_html .= '<td>ROI Parse Status:</td>';
  $return_html .= '<td>'.parseLevel1Status_combobox().'</td>';
  $return_html .= '</tr>';
  $return_html .= '<tr>';
  $return_html .= '<td>Keyword(s):</td>';
  $return_html .= '<td><input name="status_query" id="status_query" onkeypress="keyPressEvent(event, \'submit_status_search\');" size="30" title="Apiary Search" class="lst" maxlength="2048" style="background: none repeat scroll 0% 0% rgb(255, 255, 255); border-width: 1px; border-style: solid; border-color: rgb(204, 204, 204) rgb(153, 153, 153) rgb(153, 153, 153) rgb(204, 204, 204); color: rgb(0, 0, 0); font: 18px arial, sans-serif bold; height: 25px; margin: 0pt; padding: 5px 8px 0pt 6px; vertical-align: top;"></td>';
  $return_html .= "<td><button onclick='submit_status_search();' style='margin: 5px 0px; border: 1px solid grey;'>Submit</button></td>";
  $return_html .= '</tr>';
  $return_html .= '</table>';
  return $return_html;
}

function get_analyzeStatus_combobox() {
  echo analyzeStatus_combobox();
}

function analyzeStatus_combobox() {
  return generateAnalyzeSpecimenStatusComboBox(); //from include/functions.php
}

function get_transcribeStatus_combobox() {
  echo transcribeStatus_combobox();
}

function transcribeStatus_combobox() {
  return generateTranscribeTextStatusComboBox(); //from include/functions.php
}

function get_parseLevel1Status_combobox() {
  echo parseLevel1Status_combobox();
}

function parseLevel1Status_combobox() {
  return generateParseTextStatusComboBox(); //from include/functions.php
}

function search_status() {
  $successfully_searched_text = "false";
  $results_html = '';
  $msg = '';
  $hasQuery = false;
  $hasAS_status = false;
  $hasTT_status = false;
  $hasPT_status = false;
  $as_status = $_POST['as_status'];
  $query = trim($_POST['query']);
  $tt_status = $_POST['tt_status'];
  $pt_status = $_POST['pt_status'];
  if($as_status != '' && $as_status != null) {
    $hasAS_status = true;
  }
  if($query != '' && $query != null) {
    $hasQuery = true;
  }
  if($tt_status != '' && $tt_status != null) {
    $hasTT_status = true;
  }
  if($pt_status != '' && $pt_status != null) {
    $hasPT_status = true;
  }
  $image_pids= array();
  $roi_pids = array();
  if($hasAS_status) {
    $solr_q = 'q=status_analyzedStatus:("'.$as_status.'")';
    $solr_fl = 'fl=id';
    $solr_op = '';
    $solr_rows = 'rows=10000';
    $solr_xpath = solr_query_xpath($solr_q, $solr_fl, $solr_op, $solr_rows);
    if($solr_xpath) {
      $successfully_searched_text = "true";
      $solr_xpath_image_elements = array();
      $solr_xpath_image_elements = $solr_xpath->query('//response/result/doc/str[@name="id"]');
      foreach($solr_xpath_image_elements as $solr_xpath_image_element) {
        if(strpos($solr_xpath_image_element->nodeValue, 'ap-image') > -1) {
          array_push($image_pids, $solr_xpath_image_element->nodeValue);
        }
      }
      if($hasQuery || $hasTT_status || $hasPT_status) {
        foreach($image_pids as $image_pid) {
          $solr_q = getROIStatusSearchSolrQ($query, $tt_status, $pt_status, $image_pid);
          $solr_fl = 'fl=id';
          $solr_op = '';
          $solr_rows = 'rows=10000';
          $solr_xpath = solr_query_xpath($solr_q, $solr_fl, $solr_op, $solr_rows);
          if($solr_xpath) {
            $solr_xpath_roi_elements = array();
            $solr_xpath_roi_elements = $solr_xpath->query('//response/result/doc/str[@name="id"]');
            foreach($solr_xpath_roi_elements as $solr_xpath_roi_element){
              if(strpos($solr_xpath_roi_element->nodeValue, 'ap-roi') > -1) {
                array_push($roi_pids, $solr_xpath_roi_element->nodeValue);
              }
            }
          }
        }
      }
    }
  }
  else {
    $solr_q = getROIStatusSearchSolrQ($query, $tt_status, $pt_status);
    $solr_fl = 'fl=id';
    $solr_op = '';
    $solr_rows = 'rows=10000';
    $solr_xpath = solr_query_xpath($solr_q, $solr_fl, $solr_op, $solr_rows);
    if($solr_xpath) {
      $successfully_searched_text = "true";
      $solr_xpath_roi_elements = array();
      $solr_xpath_roi_elements = $solr_xpath->query('//response/result/doc/str[@name="id"]');
      foreach($solr_xpath_roi_elements as $solr_xpath_roi_element){
        if(strpos($solr_xpath_roi_element->nodeValue, 'ap-roi') > -1) {
          array_push($roi_pids, $solr_xpath_roi_element->nodeValue);
        }
      }
    }
  }

  if($successfully_searched_text == "true") {
    if(sizeOf($roi_pids) > 0) {
      $results_html .= ROISearchResults($roi_pids);
    }
    else if(sizeOf($image_pids) > 0) {
      //no need to show image results if we already have ROI results
      $results_html .= ImageSearchResults($image_pids);
    }
    else {
      $results_html .= "<br/><br/><b>No Results found!</b>";
    }
  }
  else {
    $msg .= "Unable to perform serach!";
  }
  $returnJSON['successfully_searched_text'] = $successfully_searched_text;
  $returnJSON['solr_q'] = $solr_q;
  $returnJSON['query'] = $query;
  $returnJSON['field'] = $field;
  $returnJSON['results_html'] = $results_html;
  $returnJSON['msg'] = $msg;
  echo json_encode($returnJSON);
}

function solr_query_xpath($solr_q, $solr_fl, $solr_op, $solr_rows) {
  $solr_sxml = solr_query_search(str_replace(' ', '+', $solr_q), $solr_fl, $solr_op, $solr_rows);
  if($solr_sxml) {
    $dom = new DOMDocument();
    $dom->loadXML($solr_sxml);
    $xpath = new DOMXPath($dom);
    return $xpath;
  }
  else {
    return false;
  }
}

function getROIStatusSearchSolrQ($query, $tt_status, $pt_status, $parent_id = null) {
  $solr_q = '';
  if($query != '' && $query != null) {
    $solr_q = 'q="'.$query.'"';
  }
  if($tt_status != '' && $tt_status != null) {
    if(strpos($solr_q, 'q=') > -1) {
      $solr_q .= '+AND+status_transcribedStatus:("'.$tt_status.'")';
    }
    else {
      $solr_q .= 'q=status_transcribedStatus:("'.$tt_status.'")';
    }
  }
  if($pt_status != '' && $pt_status != null) {
    if(strpos($solr_q, 'q=') > -1) {
      $solr_q .= '+AND+status_parsedL1Status:("'.$pt_status.'")';
    }
    else {
      $solr_q .= 'q=status_parsedL1Status:("'.$pt_status.'")';
    }
  }
  if($parent_id != '' && $parent_id != null) {
    if(strpos($solr_q, 'q=') > -1) {
      $solr_q .= '+AND+parent_id:("'.$parent_id.'")';
    }
    else {
      $solr_q .= 'q=parent_id:("'.$parent_id.'")';
    }
  }
  return $solr_q;
}

function ImageSearchResults($image_pids) {
  $found_result = false;
  foreach($image_pids as $image_pid) {
    if(!$found_result) {
      $results_html .= "<div align='left'>";
      $results_html .= "<table id='image_results_table' class='tablesorter' style='width:800px'><thead><tr><th>Image</th><th>Specimen</th></tr><thead>";
      $found_result = true;
    }
    $results_html .= "<tbody><tr>";
    $results_html .= "<td>$image_pid</td>";
    $sp_pid = AP_Image::get_specimen_pid($image_pid);
    $results_html .= "<td>$sp_pid</td>";
    $fedora_base_url = variable_get('fedora_base_url', 'http://localhost:8080/fedora');
    $results_html .= "</tr>";
  }
  if($found_result) {
    $server_base = variable_get('apiary_research_base_url', 'http://localhost');
    $results_html .=  "</tbody></table></div>";
    $results_html .= '<div id="pager" class="pager">';
    $results_html .= '<form>';
    $results_html .= '<img src="'.variable_get('apiary_research_base_url', 'http://localhost').'/drupal/modules/apiary_project/workflow/assets/img/first.png" class="first"/>';
    $results_html .= '<img src="'.variable_get('apiary_research_base_url', 'http://localhost').'/drupal/modules/apiary_project/workflow/assets/img/prev.png" class="prev"/>';
    $results_html .= '<input type="text"  class="pagedisplay"/>';
    $results_html .= '<img src="'.variable_get('apiary_research_base_url', 'http://localhost').'/drupal/modules/apiary_project/workflow/assets/img/next.png"  class="next"/>';
    $results_html .= '<img src="'.variable_get('apiary_research_base_url', 'http://localhost').'/drupal/modules/apiary_project/workflow/assets/img/last.png"  class="last"/>';
    $results_html .= '<select class="pagesize">';
    $results_html .= '<option selected="selected"  value="10">10</option>';
    $results_html .= '<option value="20">20</option>';
    $results_html .= '<option value="30">30</option>';
    $results_html .= '<option value="40">40</option>';
    $results_html .= '</select>';
    $results_html .= '</form>';
    $results_html .= '</div><!--pager-->';
  }
  else {
    $results_html .= "<br/><br/><b>No Results found!</b>";
  }
  return $results_html;
}

function ROISearchResults($roi_pids) {
  $found_result = false;
  foreach($roi_pids as $roi_pid) {
    if(!$found_result) {
      $results_html .= "<div align='left'>";
      $results_html .= "<table id='roi_results_table' class='tablesorter' style='width:800px'><thead><tr><th>ROI</th><th>Image</th><th>Specimen</th><th>Datastream</th></tr><thead>";
      $found_result = true;
    }
    $results_html .= "<tbody><tr>";
    //$results_html .= '<td><a class="preview" href="'.$server_base.'/drupal/apiary?ref=specimenMetadata_details&pid='.$roi_pid.'">'.$roi_pid.'</a></td>';
    $results_html .= '<td><a href="#" class="preview" onclick="display_specimenMetadata_details(\''.$roi_pid.'\');">'.$roi_pid.'</a></td>';
    $roi_ob = new AP_ROI();
    $image_pid = $roi_ob->get_image_pid($roi_pid);
    $results_html .= "<td>$image_pid</td>";
    $sp_pid = AP_Image::get_specimen_pid($image_pid);
    $results_html .= "<td>$sp_pid</td>";
    $fedora_base_url = variable_get('fedora_base_url', 'http://localhost:8080/fedora');
    $text = shell_exec("curl -H - XGET $fedora_base_url/get/$roi_pid/specimenMetadata");
    $check=strpos($text, "404 Not Found");
    if($check === FALSE) {
      $results_html .= "<td><a href='".$fedora_base_url."/get/$roi_pid/specimenMetadata'>specimenMetadata</a></td></tr>";
    }
    else {
      $results_html .= "<td>No specimenMetadata found</td></tr>";
    }
  }
  if($found_result) {
    $server_base = variable_get('apiary_research_base_url', 'http://localhost');
    $results_html .=  "</tbody></table></div>";
    $results_html .= '<div id="pager" class="pager">';
    $results_html .= '<form>';
    $results_html .= '<img src="'.variable_get('apiary_research_base_url', 'http://localhost').'/drupal/modules/apiary_project/workflow/assets/img/first.png" class="first"/>';
    $results_html .= '<img src="'.variable_get('apiary_research_base_url', 'http://localhost').'/drupal/modules/apiary_project/workflow/assets/img/prev.png" class="prev"/>';
    $results_html .= '<input type="text"  class="pagedisplay"/>';
    $results_html .= '<img src="'.variable_get('apiary_research_base_url', 'http://localhost').'/drupal/modules/apiary_project/workflow/assets/img/next.png"  class="next"/>';
    $results_html .= '<img src="'.variable_get('apiary_research_base_url', 'http://localhost').'/drupal/modules/apiary_project/workflow/assets/img/last.png"  class="last"/>';
    $results_html .= '<select class="pagesize">';
    $results_html .= '<option selected="selected"  value="10">10</option>';
    $results_html .= '<option value="20">20</option>';
    $results_html .= '<option value="30">30</option>';
    $results_html .= '<option value="40">40</option>';
    $results_html .= '</select>';
    $results_html .= '</form>';
    $results_html .= '</div><!--pager-->';
  }
  else {
    $results_html .= "<br/><br/><b>No Results found!</b>";
  }
  return $results_html;
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

function solr_query_search($q, $fl = '', $op = '', $rows = '') {
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
  return $solr_search->doSearch($solr_query);
}

function get_specimen_list_via_solr_query($solr_q) {
  $solr_fl = '';
  $solr_op = '';
  $solr_rows = '';
  $specimen_list = array();
  if(strpos(strtolower($solr_query), '&rows=') > -1) {
  }
  else {
    $solr_rows = 'rows=10000';
  }
  $solr_sxml = solr_query_xml($solr_q, $solr_fl, $solr_op, $solr_rows);
  foreach($solr_sxml->result[0]->doc as $doc) {
    foreach($doc->children() as $sxml_node) {
      $node_value = (string)$sxml_node;
      if(strpos($node_value, 'ap-specimen:') > -1) {
        if(!array_search($node_value, $specimen_list)) {
          array_push($specimen_list, $node_value);
        }
      }
    }
  }
  return $specimen_list;
}

?>