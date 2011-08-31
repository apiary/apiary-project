function create_drupal_user(create, stay) {
	if(create == "true"){
		var drupal_user_name = $("#drupal_user_name").val();
		var drupal_user_pass = $("#drupal_user_pass").val();
		var drupal_user_email = $("#drupal_user_email").val();
		var url = "<?php echo $server_base . '/drupal/'; ?>";
		$.post(drupal_url+"/apiary/user/create_drupal_user/0/0/0", { name: drupal_user_name, mail: drupal_user_email, pass: drupal_user_pass }, function(results){
			data = $.parseJSON(results);
			alert(data.msg);
			if($("#drupal_user_names").length) { //this is used in workflow
				if(data.user_successfully_created == "true") {
					$.get(drupal_url+"/apiary/workflow_editor/drupal_user_cbox_option/"+data.user_name+"/ selected/0", function(response){
						cbox_option = $.trim(response);
						$("#drupal_user_names").append(cbox_option);
						$('#drupal_user_namesms2side__dx').append(cbox_option);
						clear_drupal_user_text();
					});
				}
			}
		});
	}
	if(stay == "false") {
		$(".overlay_drupal_user").colorbox.close();
	}
}

function clear_drupal_user_text() {
	if($("#drupal_user_name").length) {
		$("#drupal_user_name").val('');
	}
	if($("#drupal_user_pass").length) {
		$("#drupal_user_pass").val('');
	}
	if($("#drupal_user_email").length) {
		$("#drupal_user_email").val('');
	}
}