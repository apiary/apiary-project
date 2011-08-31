<?php
function apiary_project_help_text_form_submit($form, &$form_state){
	$records = db_query("select * from {apiary_project_help_text} ");
	$new_msg = '';
	while($record = db_fetch_object($records))
	{
		$help_text = $form_state['values'][$record->term];
		$exe_query = db_query("UPDATE {apiary_project_help_text} SET help_text='%s' where term='%s'", $help_text,$record->term);
	}
	if($form_state['values']['new_term'] != "" || $form_state['values']['new_label'] != ""){
		$term = $form_state['values']['new_term'];
		$label = $form_state['values']['new_label'];
		$category = $form_state['values']['new_category'];
		$help_text = $form_state['values']['new_help_text'];
		if($term != "" && $label != "" && $category != ""){
			$exe_query = db_query("INSERT INTO {apiary_project_help_text} (term, label, category, help_text) VALUES ('%s','%s','%s','%s')",
			$term, $label, $category, $help_text);
		}
		else{
			$new_msg = "New text couldn't be saved. Please make sure no field are empty.";
		}
	}
	if ($exe_query !== false) {
		$msg = t('Help Text Saved!');
		watchdog('apiary_project', $msg, WATCHDOG_INFO);
		drupal_set_message(t('Saved!!') . t($new_msg));
	} else {
		$msg = t('Could not Save');
		$vars = array();
		watchdog('apiary_project', $msg, WATCHDOG_ERROR);
		drupal_set_message(t("Could not Save."));
	}
	$form_state['redirect'] = "admin/settings/apiary_project/help_text_settings";
}


function apiary_project_help_text_form(){
	$categories = db_query("select DISTINCT category from {apiary_project_help_text} ");
	$records = db_query("select * from {apiary_project_help_text} ");
	$form = '';$k = 0;$flag = true;
	while($category = db_fetch_object($categories)){
		$form[$category->category] = array(
			'#type' => 'fieldset',
			'#title' => t($category->category),
			'#weight' => $k,
			'#collapsible' => TRUE,
			'#collapsed' => TRUE,
		);
		$k++;
		if($category->category=='General')
		$flag = false;
	}
	if($flag){
		$form['General'] = array(
				'#type' => 'fieldset',
				'#title' => t('General'),
				'#weight' => $k,
				'#collapsible' => TRUE,
				'#collapsed' => TRUE,
		);
	}
	while($record = db_fetch_object($records)){
		$form[$record->category][$record->term] = array(
				'#type' => 'textarea',
				'#cols' => '30',
				'#rows' => '2',
				'#title' => t($record->label),
				'#default_value' => t($record->help_text),
		);
	}
	$form['General']['new_term'] = array(
			'#type' => 'textfield',
			'#title' => t('Term Name'),
	);
	$form['General']['new_label'] = array(
			'#type' => 'textfield',
			'#title' => t('Term Label'),
	);
	$form['General']['new_category'] = array(
			'#type' => 'select',
			'#title' => t('Category'),
			'#options' => array('General' => t('General')),
	);
	$form['General']['new_help_text'] = array(
			'#type' => 'textarea',
			'#cols' => '30',
			'#rows' => '2',
			'#title' => t('Help text'),
	);
	$form['submit'] = array(
			'#type' => 'submit',
			'#value' => t('Save'),
			'#weight' => 200,
	);

	return $form;
}

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

function apiary_project_message_form_submit($form, &$form_state) {
	$m = new metadata_elements();
	for($i=0; $i<$m->current_row-2; $i++)
	{
		if($m->data_array[$i]["DwC/AP Term Name"]!==""){
			$term_name = $m->data_array[$i]["DwC/AP Term Name"];
			$test_message = $form_state['values'][$term_name];
			$exe_query = db_query("UPDATE {apiary_project} SET frequent=%d where term='%s'", $test_message,$term_name);
		}
	}
	if ($exe_query !== false) {
		$msg = 'Settings Saved!';
		watchdog('apiary_project', $msg, WATCHDOG_INFO);
		drupal_set_message(t('Settings Saved'));
	} else {
		$msg = 'Could not save settings ';
		$vars = array();
		watchdog('apiary_project', $msg, WATCHDOG_ERROR);
		drupal_set_message(t('Could not save settings.'));
	}

	$form_state['redirect'] = 'admin/settings/apiary_project/settings';
}
