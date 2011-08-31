<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
global $user;
include_once(drupal_get_path('module', 'apiary_project') . '/workflow/include/functions.php');

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

function new_object_pool_html() {
  $object_pool_query_type_combobox = generateObjectPoolQueryTypeComboBox();
  $html = '
  <div id="object_pool">
<h3>Create Object Pool</h3>
 <br/>
  <label for="object_pool_name">Object Pool Name:</label>
 <br/>
  <input type="text" name="object_pool_name" id="object_pool_name" />
 <p/>
  <label for="object_pool_description">Object Pool Description:</label>
 <br/>
  <input type="text" name="object_pool_description" id="object_pool_description" />
 <p/>
  <label>Object Pool Query Type:</label>
 <br/>'.$object_pool_query_type_combobox.'
 <div id="object_pool_query_space">
  <label for="object_pool_query">Object Pool Query:</label>
 <br/>
  <textarea type="text" name="object_pool_query" id="object_pool_query"></textarea>
 </div><!-- object_pool_query_space -->
 <p/>';
  //$html .= '<a href="javascript:view_object_pool_results();">[view results]</a><p/>';
 $html .= '<span>
  <input type="button" name="create_object_pool_btn" onClick="create_object_pool(\'true\');" value="Create Object Pool" />
  <input type="button" name="create_object_pool_btn" onClick="create_object_pool(\'false\');" value="Cancel" />
</span>
 <br/>
</div><!-- object_pool -->';
echo $html;
}

function create_object_pool() {
  $object_pool_successfully_created = "false";
  $server_base = variable_get('apiary_research_base_url', 'http://localhost');
  if(isset($_POST['object_pool_name'])){
    $object_pool_name = $_POST['object_pool_name'];
    $object_pool_description = $_POST['object_pool_description'];
    $object_pool_query_type = $_POST['object_pool_query_type'];
    $object_pool_query = $_POST['object_pool_query'];
    module_load_include('php', 'apiary_project', 'workflow/include/class.Object_Pool');
    if(Object_Pool::create($object_pool_name, $object_pool_description, $object_pool_query_type, $object_pool_query)) {
      $object_pool_successfully_created = "true";
      $msg = "Object Pool ".$object_pool_name." successfully created.";
    }
    else {
      $msg = "Object Pool ".$object_pool_name." failed to created.";
    }
  }
  $returnJSON['object_pool_successfully_created'] = $object_pool_successfully_created;
  $returnJSON['msg'] = $msg;
  echo json_encode($returnJSON);
}

function view_object_pool_results($type, $query) {
  if($type == "Resource Index Query") {
  }
  else if($type == "SOLR Query") {
  }
  else if($type == "Specific List") {
  }
}

function getObjectPoolQueryTypeList() {
  return array("Resource Index Query", "SOLR Query", "Specific List");
}

function generateObjectPoolQueryTypeComboBox() {
  $object_pool_type_list = getObjectPoolQueryTypeList();
  $combobox_html = '';
  $combobox_html .= '<select name="object_pool_query_types" id="object_pool_query_types">'."\n";
  $combobox_html .= '<option value="" selected> </option>'."\n";
  for($i = 0; $i < sizeof($object_pool_type_list); $i++) {
    $combobox_html .= '<option onClick="update_object_pool_query_space(\''.$object_pool_type_list[$i].'\');" value="'.$object_pool_type_list[$i].'">'.$object_pool_type_list[$i].'</option>'."\n";
  }
  $combobox_html .= '</select>'."\n";
  return $combobox_html;
}

?>