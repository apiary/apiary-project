<?php

class apiaryAdminClass {

  function apiaryAdminClass() {
    module_load_include('nc', 'apiaryAdminClass', '');
    drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);

  }
  /*
   * create the paths for urls and map them to php functions
   */
  function createMenu(){
    module_load_include('php', 'Apiary_Project', 'apiaryPermissionsClass');
    $items = array ();

    $items['apiary_workflow'] = array (
	  'title' => t('Workspace'),
      'menu_name' => t('primary-links'),
	  'description' => t('Explore the Apiary Project workflow here'),
	  'page callback' => 'apiary_project_workspace',
	  'page arguments' => array('apiary_project'),
	  'access arguments' => array(apiaryPermissionsClass::$VIEW_APIARY),
	  'type' => MENU_NORMAL_ITEM
    );
	$items['admin/settings/apiary_project'] = array (
	  'title' => t('Apiary Project Settings'),
	  'description' => t('Configure Apiary Project metadata and variables here'),
	  'page callback' => 'drupal_get_form',
	  'page arguments' => array('apiary_project_admin'),
	  'access arguments' => array(apiaryPermissionsClass::$ADMINISTER_APIARY),
	  'type' => MENU_NORMAL_ITEM,
    );
    $items['admin/settings/apiary_project/variables'] = array (
      'title' => t('Variables List'),
      'description' => t('Enter the Apiary Research information here.'),
      'access arguments' => array(apiaryPermissionsClass::$ADMINISTER_APIARY),
      'type' => MENU_DEFAULT_LOCAL_TASK,
      'weight' => 0,
    );
	$items['admin/settings/apiary_project/settings'] = array(
        'title' => 'Metadata Settings',
        'description' => 'Select metadata elements to be used',
        'page callback' => 'apiary_project_message',
        'access arguments' => array(apiaryPermissionsClass::$ADMINISTER_APIARY),
		'file' => 'admin_functions.php',
        'type' => MENU_LOCAL_TASK,
    );

    $items['admin/settings/apiary_project/help_text_settings'] = array(
        'title' => 'Help Text Settings',
        'description' => t('Add/Remove/Edit Help text options'),
        'page callback' => 'apiary_project_help_text',
        'access arguments' => array(apiaryPermissionsClass::$ADMINISTER_APIARY),
    	'file' => 'admin_functions.php',
        'type' => MENU_LOCAL_TASK,
    );
    return $items;
  }

  function createAdminForm() {
    if(!user_access('administer site configuration')){
      drupal_set_message(t('Unauthorized access to administer site configuration'),'error');
      return;
    }
    $form = array ();
    $form['apiary_version'] = array (
        '#type' => 'textfield',
        '#title' => t('Apiary Version'),
        '#default_value' => variable_get('apiary_version',
        'apiary-0.1.1'),
        '#description' => t('The version of Apiary to be ran'), '#required' => true,
        '#weight' => -3
    );
    $form['apiary_object_timeout'] = array (
        '#type' => 'textfield',
        '#title' => t('Apiary Object Timeout (in seconds)'),
        '#default_value' => variable_get('apiary_object_timeout',
        '1800'),
        '#description' => t('Timeout used for Apiary workflow objects'), '#required' => true,
        '#weight' => -2
    );
    $form['apiary_research_name'] = array (
        '#type' => 'textfield',
        '#title' => t('Apiary Project Research Site name' ),
      '#default_value' => variable_get('apiary_research_name', 'Apiary Research Project'),
      '#description' => t('The Name of this particular Apiary Research Site'),
      '#required' => true,
      '#weight' => -1
    );
    $form['apiary_research_base_url'] = array (
        '#type' => 'textfield',
        '#title' => t('Apiary Project site Url'),
        '#default_value' => variable_get('apiary_research_base_url', 'http://localhost'),
        '#description' => t('The domain of the server this Apiary Research site is located'),
        '#required' => true,
        '#weight' => 0
    );
    $form['apiary_research_djatoka_url'] = array (
        '#type' => 'textfield',
        '#title' => t('Apiary Research Djatoka server URL'),
        '#default_value' => variable_get('apiary_research_djatoka_url',
        'http://localhost:8080/adore-djatoka'),
        '#description' => t('The url of the Apiary Research JPEG2000 image server'), '#required' => true,
        '#weight' => 0
    );
    $form['apiary_project_herbis_dir'] = array (
        '#type' => 'textfield',
        '#title' => t('Apiary Project Herbis Directory'),
        '#default_value' => variable_get('apiary_project_herbis_dir',
        '/var/www/drupal/modules/apiary_project/herbis'),
        '#description' => t('The path to the herbis folder on the machine hosting the drupal module.'), '#required' => true,
        '#weight' => 0
    );
    $form['apiary_project_herbis_url'] = array (
        '#type' => 'textfield',
        '#title' => t('Apiary Project HERBIS server URL'),
        '#default_value' => variable_get('apiary_project_herbis_url',
        'http://txcdk3g.unt.edu:8080/HERBIS'),
        '#description' => t('The url of the Apiary Research natual language processing server'), '#required' => true,
        '#weight' => 0
    );

    return system_settings_form($form);
  }

   /**
	* Apiary Module Messages
	* @return array An array of form data.
	*/
	function apiary_project_message() {
		$page_content = '';
		$page_content .= drupal_get_form('apiary_project_message_form');
		return $page_content;
	}

	function apiary_project_help_text() {
		$page_content = '';
		$page_content .= drupal_get_form('apiary_project_help_text_form');
		return $page_content;
	}

	/**
	* The callback function (form constructor) that creates the HTML form for apiary_project_message().
	* @return form an array of form data.
	*/
	function apiary_project_message_form() {
		$meta = new metadata_elements();
		$form =   $meta->get_metadata();
		//Submit button:
		$form['submit'] = array(
			'#type' => 'submit',
			'#value' => t('Save'),
			'#weight' => 200,
		);

		return $form;
	}

}
?>
