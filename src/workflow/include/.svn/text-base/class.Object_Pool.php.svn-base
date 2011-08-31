<?php

class Object_Pool {
  public $object_pool_id;
  public $object_pool_name;
  public $object_pool_description;
  public $object_pool_query_type;
  public $object_pool_query;
  public $object_pool_pids;
  private $object_pool_added_by;
  private $object_pool_added_date;
  private $object_pool_updated_by;
  private $object_pool_updated_date;
  public $msg;

  function Object_Pool($object_pool_id = null) {
    module_load_include('nc', 'Object_Pool', '');
    drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
    if(!empty( $object_pool_id)) {
      $this->object_pool_id = $object_pool_id;
      $this->loadDBObjectPool();
      $this->loadFedoraObjectPool();
    }
  }

  function loadDBObjectPool() {
    $obj_pool_db_record = $this->get($this->object_pool_id);
    $this->object_pool_name = $obj_pool_db_record['object_pool_name'];
    $this->object_pool_description = $obj_pool_db_record['object_pool_description'];
    $this->object_pool_query_type = $obj_pool_db_record['object_pool_query_type'];
    $this->object_pool_query = $obj_pool_db_record['object_pool_query'];
  }

  static function get($object_pool_id) {
	$results = db_query("SELECT * FROM {apiary_project_object_pool} WHERE object_pool_id='%s'", $object_pool_id);
	while ($fields = db_fetch_array($results)) {
	  foreach($fields as $key => $value) {
	    $object_pool[$key] = $value;
	  }
    }
    return $object_pool;
  }

  function loadFedoraObjectPool() {
    if($this->object_pool_query_type == "Resource Index Query" && !empty($this->object_pool_query)) {
      $this->object_pool_pids = $this->getObjectPoolByRIQuery($this->object_pool_query);
    }
    else if($this->object_pool_query_type == "SOLR Query") {
      $this->object_pool_pids = $this->getObjectPoolBySolrQuery($this->object_pool_query);
    }
    else if($this->object_pool_query_type == "Specific List") {
    }
    else if($this->object_pool_query_type == "Select From All") {
    }
    else {
    }
  }

  static function getObjectPoolByRIQuery($object_pool_query = null) {
    $object_query_results = FedoraObject::getRIQueryResults('apiary:SpecimenBinders', $object_pool_query);
    $sxml = new SimpleXMLElement($object_query_results);
    $object_pool_pid = array();
    foreach( $sxml->xpath( '//@uri' ) as $uri ) {
      array_push( $object_pool_pid,  substr( strstr( $uri, '/' ), 1 ));
    }
    return $object_pool_pid;
  }

  static function getObjectPoolBySolrQuery($solr_query = null) {
    include_once(drupal_get_path('module', 'apiary_project').'/workflow/include/search.php');
    $solr_search = new search();
	$specimen_list = array();
	if(strpos(strtolower($solr_query), 'q=') > -1) {
    }
	else {
	  $solr_query = 'q='.$solr_query;
	}
	if(strpos(strtolower($solr_query), '&rows=') > -1) {
    }
	else {
	  $solr_query .= '&rows=10000';
	}
	$solr_results = $solr_search->doSearch($solr_query);
	if($solr_results != false) {
	  $solr_sxml = new SimpleXMLElement($solr_results);
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
	}
	return $specimen_list;
  }

  static function getObjectPoolNameList() {
	$sql = "SELECT object_pool_name FROM {apiary_project_object_pool}";
	$object_pool_results = db_query($sql);
	$object_pool_name_list = array();
    while($object_pool_result = db_fetch_object($object_pool_results)) {
      $object_pool_name = $object_pool_result->object_pool_name;
      array_push($object_pool_name_list, trim($object_pool_name));
    }
    return $object_pool_name_list;
  }

  static function getNameFromID($object_pool_id) {
	$results = db_query("SELECT object_pool_name FROM {apiary_project_object_pool} WHERE object_pool_id='%s'", $object_pool_id);
	while ($result = db_fetch_object($results)) {
      $object_pool_name = $result->object_pool_name;
    }
    return $object_pool_name;
  }

  static function getIDFromName($object_pool_name) {
	$results = db_query("SELECT object_pool_id FROM {apiary_project_object_pool} WHERE object_pool_name='%s'", $object_pool_name);
	while ($result = db_fetch_object($results)) {
      $object_pool_id = $result->object_pool_id;
    }
    return $object_pool_id;
  }

  function save($object_pool_id = null, $object_pool_name = null, $object_pool_description = null, $object_pool_query_type = null, $object_pool_query = null) {
  	if($object_pool_id == null && $this->object_pool_id != null && $this->object_pool_id != '') {
  	  $object_pool_id = $this->object_pool_id;
  	}
  	if($object_pool_name == null && $this->object_pool_name != null && $this->object_pool_name != '') {
  	  $object_pool_name = $this->object_pool_name;
  	}
  	else {
  	  $this->msg = 'A Object Pool Name is required.<br>';
  	  return false;
  	}
  	if($object_pool_description == null && $this->object_pool_description != null && $this->object_pool_description != '') {
  	  $object_pool_description = $this->object_pool_description;
  	}
  	if($object_pool_query_type == null && $this->object_pool_query_type != null && $this->object_pool_query_type != '') {
  	  $object_pool_query_type = $this->object_pool_query_type;
  	}
  	if($object_pool_query == null && $this->object_pool_query != null && $this->object_pool_query != '') {
  	  $object_pool_query = $this->object_pool_query;
  	}
  	if($object_pool_id == null || $object_pool_id = '') {
  	  return create($object_pool_description, $object_pool_name, $object_pool_query_type, $object_pool_query);
  	}
  	else {
  	  return update($object_pool_id, $object_pool_description, $object_pool_name, $object_pool_query_type, $object_pool_query);
  	}
  }

  static function create($object_pool_name = null, $object_pool_description = null, $object_pool_query_type = null, $object_pool_query = null) {
  	if($object_pool_name == null || $object_pool_name == '') {
  	  //echo 'A Object Pool Name is required.<br>';
  	  return false;
  	}
    global $user;
    $object_pool_added_by = $user->uid;
  	$object_pool_added_date = date("Y-m-d H:i:s");
	if(!Object_Pool::object_pool_name_exists($object_pool_name)) {
	  $insert_record = db_query("INSERT into {apiary_project_object_pool} (object_pool_name, object_pool_description, object_pool_query_type, object_pool_query, object_pool_added_by, object_pool_added_date)
	                             VALUES ('%s', '%s', '%s', '%s', '%s', '%s')", $object_pool_name, $object_pool_description, $object_pool_query_type, $object_pool_query, $object_pool_added_by, $object_pool_added_date);
      return $insert_record;
    }
    else {
	  //echo "object_pool_name_exists is true <br>";
    }
    return false;
  }

  static function object_pool_id_exists($object_pool_id) {
	$sql = 'SELECT object_pool_id FROM {apiary_project_object_pool}';
	$results = db_query($sql);
	while ($result_object_pool_id = db_result($results)) {
	  if($result_object_pool_id != '' && $result_object_pool_id !=  null)  {
	    return true;
	  }
    }
    return false;
  }

  static function object_pool_name_exists($object_pool_name) {
	$sql = "SELECT object_pool_name FROM {apiary_project_object_pool} WHERE object_pool_name='$object_pool_name'";
	$results = db_query($sql);
	while ($result_object_pool_name = db_result($results)) {
	  if($result_object_pool_name != '' && $result_object_pool_name !=  null)  {
      echo "result_object_pool_name = $result_object_pool_name <br>";
	    return true;
	  }
    }
    return false;
  }

  static function update($object_pool_id, $object_pool_name, $object_pool_description, $object_pool_id = null, $permision_list = null) {
  	global $user;
  	$object_pool_updated_by = $user->id;
  	$object_pool_updated_date = date("Y-m-d H:i:s");
  }
}

?>