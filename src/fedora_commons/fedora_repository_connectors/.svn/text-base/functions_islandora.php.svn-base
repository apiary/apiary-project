<?php

function delete_digitalobject($pid) {
  module_load_include('php', 'Fedora_Repository', 'ConnectionHelper');

  $params = array (
        "pid" => $pid,
        "logMessage" => "Purged",
        "force" => ""
        );
  try {
    $soapHelper = new ConnectionHelper();
    $client = $soapHelper->getSoapClient(variable_get('fedora_soap_manage_url', 'http://localhost:8080/fedora/services/management?wsdl'));
    $object = $client->__soapCall('purgeObject', array (
    $params
    ));
    return true;
  }
  catch (exception $e) {
    drupal_set_message(t($e->getMessage()), 'error');
    return false;
  }
}

function get_DatastreamDissemination($params) {
  try{
    module_load_include('php', 'Fedora_Repository', 'ConnectionHelper');
    $soapHelper = new ConnectionHelper();
    $fedora_soap_url = variable_get('fedora_soap_url', 'http://localhost:8080/fedora/services/access?wsdl');
    $client = $soapHelper->getSoapClient($fedora_soap_url);
    $object = $client->__soapCall('getDatastreamDissemination', array ('parameters' => $params));
    return $object;
  }
  catch(Exception $e){
    return 'Error Getting DataStream imageMetadata';
  }
}

function get_RIQueryResults($pid, $query) {
  module_load_include('php', 'Fedora_Repository', 'CollectionClass');
  $collectionClass= new CollectionClass();
  $results= $collectionClass->getRelatedItems($pid, $query);
  return $results;
}

function ingest_object_from_FOXML($dom) {
  module_load_include('php', 'Fedora_Repository', 'ConnectionHelper');
  module_load_include('php', 'Fedora_Repository', 'api/fedora_item');
  try{
	$object = Fedora_Item::ingest_from_FOXML($dom);
	return $object;
  }
  catch(exception $e){
	drupal_set_message(t('Error Ingesting Object! ').$e->getMessage(),'error');
	$msg = "Error Ingesting Object!".$e->getMessage();
	watchdog(t("Fedora_Repository"), $msg, WATCHDOG_ERROR);
	return false;
  }
}

function add_Datastream($pid, $datastream, $dsLabel, $mimetype, $xml_url) {
  module_load_include('php', 'Fedora_Repository', 'ObjectHelper');
  module_load_include('php', 'Fedora_Repository', 'ConnectionHelper');
  global $user;

  if(!fedora_repository_access(OBJECTHELPER :: $ADD_FEDORA_STREAMS, $pid,$user)){
    drupal_set_message('You do not have permission to add datastreams to this object!');
    return false;
  }
  global $base_url;
  module_load_include('php', 'Fedora_Repository', 'ConnectionHelper');
  $controlGroup = "M";
  $params = array (
    'pid' => $pid,
    'dsID' => $datastream,
    'altIDs' => "",
    'dsLabel' => $dsLabel,
    'versionable' => "true",
    'MIMEType' => $mimetype,
    'formatURI' => "URL",
    'dsLocation' => $xml_url,
    'controlGroup' => "$controlGroup",
    'dsState' => "A",
    'checksumType' => "DISABLED",
    'checksum' => "none",
    'logMessage' => "datastream added"
  );
  try {
    $soapHelper = new ConnectionHelper();
    $client = $soapHelper->getSoapClient(variable_get('fedora_soap_manage_url', 'http://localhost:8080/fedora/services/management?wsdl'));

    if ($client == null) {
      drupal_set_message(t('Error Getting Soap Client.'), 'error');
      return false;
    }
    $object = $client->__soapCall('addDatastream', array (
    'parameters' => $params
    ));
    return true;
  }
  catch (exception $e) {
    try {
      $params['force'] = 'true';
      $object = $client->__soapCall('ModifyDatastreamByReference', array (
      $params
      ));
      return true;
    }
    catch(exception $e1) {
      drupal_set_message(t($e1->getMessage()), 'error');
      return false;
    }
  }
}
?>