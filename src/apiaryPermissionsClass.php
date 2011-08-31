<?php

class apiaryPermissionsClass {
  //allowed operations
  public static $ADMINISTER_APIARY = 'Administer Apiary Project';
  public static $VIEW_APIARY = 'View Apiary Project';
  public static $CREATE_APIARY_SPECIMENS = 'Create Apiary Project specimens';
  public static $CREATE_APIARY_IMAGESS = 'Create Apiary Project images';
  public static $CREATE_APIARY_ROIS = 'Create Apiary Project ROIs';
  public static $OCR_APIARY = 'OCR Apiary Project objects';
  public static $PARSE_APIARY = 'Parse Apiary Project objects';

  function apiaryPermissionsClass( ) {
    module_load_include('nc', 'apiaryPermissionsClass', '');
    drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
  }

}

?>
