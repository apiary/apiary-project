
function load_workflow(workflow_id) {
	$.ajax({
		url: drupal_url+"/apiary/workflow_editor/workflow/"+workflow_id+"/"+0+"/0",
		success: function(workflow_html) {
			$("#workflow").replaceWith($.trim(workflow_html));
			load_variables();
			$(".overlay_drupal_user").click(function(){
				load_new_drupal_user_overlay();
			});
			$(".overlay_object_pool").click(function(){
				load_new_object_pool_overlay();
			});
			setup_text_input($('input#workflow_name'));
			setup_text_input($('input#workflow_description'));
			setup_jquery_events();
		},
		error: function() {
			alert("You must be logged in and have permissions to View the Apiary Project to view this page.");
			window.location.href = drupal_url;
		}
	});
}
function load_variables() {
	drupal_user_count = $("#drupal_user_count").val();
	object_pool_count = $("#object_pool_count").val();
}
function setup_text_input(input) {
	contents = input.val();
	charlength = contents.length;
	if(contents.length > 0) {
		newwidth = (charlength*8)+50;
		input.css({width:newwidth});
	}
	input.autoGrowInput({
		comfortZone: 50,
		minWidth: 100,
		maxWidth: 2000
	});
}
function setup_jquery_events() {
	object_pool_overlay();
	drupal_user_overlay();
	drupal_user_selection();
}
function create_workflow() {
	process_workflow("create");
}
function update_workflow() {
	process_workflow("update");
}
function process_workflow(process_type) {
//create or update
	//workflow_id is set when the page loads
	var workflow_name = $("#workflow_name").val();
	var workflow_description = $("#workflow_description").val();
	var object_pool_name = $("#object_pool_names").val();
	var selected_users = get_selected_users();
	if(permissions = get_checked_workflow_permissions()) {
	}
	else {
		alert("At least one permission must be set in order to "+process_type+" the workflow");
		return false;
	}
	$.post(drupal_url+"/apiary/workflow_editor/process_workflow/0/0/0", { workflow_id: workflow_id, workflow_name: workflow_name, workflow_description: workflow_description, object_pool_name: object_pool_name, selected_users: selected_users, permissions: permissions }, function(results){
		data = $.parseJSON(results);
		alert(data.msg);
		if(data.workflow_successfully_created == "true") {
			workflow_id = data.workflow_id;
			$("#create_workflow_btn").hide();
			$("#update_workflow_btn").show();
		}
	});
}
function get_checked_workflow_permissions() {
	var permission_list = Array();
	$("input[name=permissions]:checked").each(function(){
		permission_list.push($(this).val());
	});
	var permissions_csv = permission_list.join(",");
	return permissions_csv;
}
function object_pool_overlay(){
	$(".overlay_object_pool").colorbox({width:"90%", height:"90%", iframe:false, opacity: 0.6});
}
function rebuild_object_pool_cbox(){
	var url = "apiary?ref=workflow";
	if(workflow_id != "" && workflow_id != "0" && workflow_id.length) {
		url = url+"&workflow_id="+workflow_id;
	}
	url = url+"&no_overlay=true";
	$("#workflow_content").load(url,function(responseText){
		var response_object_pool_count = $(responseText).find("#object_pool_count").val();
		if(response_object_pool_count > object_pool_count) {
		  $("#object_pool_cbox").load($(responseText).find("#object_pool_cbox"));
		  object_pool_count = response_object_pool_count;
		}
		else {
		  alert("no change");
		}
		//set_object_pool_cbox_to_last_index();
		//setup_jquery_events();
	});
}
function set_object_pool_cbox_to_last_index() {
	var elem = document.getElementById("object_pool_names");
	var options = elem.options.length;
	elem.selectedIndex = options-1;
}
function load_new_object_pool_overlay() {
	$('.overlay_object_pool').colorbox({inline:true, href:"#object_pool"});
	if(!$("input#object_pool_names").length) {
		$("#object_pool").load(drupal_url+"/apiary/object_pool/new_object_pool_html/0/0/0", function(){
			$('.overlay_object_pool').unbind('click');
			$('.overlay_object_pool').click();
			setup_text_input($('input#object_pool_name'));
			setup_text_input($('input#object_pool_description'));
		});
	}
	else {
		clear_object_pool_text();
	}
}
function load_new_drupal_user_overlay() {
	$('.overlay_drupal_user').colorbox({inline:true, href:"#drupal_user"});
	if(!$("input#drupal_user_name").length) {
		$("#drupal_user").load(drupal_url+"/apiary/user/new_drupal_user_html/0/0/0", function(){
			$('.overlay_drupal_user').unbind('click');
			$('.overlay_drupal_user').click();
			setup_text_input($('input#drupal_user_name'));
			setup_text_input($('input#drupal_user_pass'));
			setup_text_input($('input#drupal_user_email'));
		});
	}
	else {
		clear_drupal_user_text();
	}
}
function drupal_user_overlay(){
	$(".overlay_drupal_user").colorbox({width:"90%", height:"90%", iframe:false, opacity: 0.6});
}
function drupal_user_selection() {
	$("#drupal_user_names").multiselect2side({
		selectedPosition: "right", moveOptions: false, labelsx: "* Available Users *", labeldx: "* Selected Users *"
	});
}
function rebuild_drupal_user_cbox() {
	alert("rebuilding drupal user cbox");
	$("#drupal_user_names").unbind("multiselect2side");
	drupal_user_selection();
	alert("drupal user cbox rebuilt");
}
function get_selected_users() {
	var selected_users = Array();
	$("#drupal_user_names").each(function(index){
		selected_users[index] = $(this).val();
	});
	var selected_user_csv = selected_users.join(",");
	return selected_user_csv;
}
(function($){
	$.fn.autoGrowInput = function(o) {
		o = $.extend({
			maxWidth: 1000,
			minWidth: 0,
			comfortZone: 70
		}, o);
		this.filter('input:text').each(function(){
		var minWidth = o.minWidth || $(this).width(),
		val = '',
		input = $(this),
		testSubject = $('<tester/>').css({
			position: 'absolute',
			top: -9999,
			left: -9999,
			width: 'auto',
			fontSize: input.css('fontSize'),
			fontFamily: input.css('fontFamily'),
			fontWeight: input.css('fontWeight'),
			letterSpacing: input.css('letterSpacing'),
			whiteSpace: 'nowrap'
		}),
		check = function() {

			if (val === (val = input.val())) {return;}

			// Enter new content into testSubject
			var escaped = val.replace(/&/g, '&amp;').replace(/\s/g,'&nbsp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
			testSubject.html(escaped);

			// Calculate new width + whether to change
			var testerWidth = testSubject.width(),
			newWidth = (testerWidth + o.comfortZone) >= minWidth ? testerWidth + o.comfortZone : minWidth,
			currentWidth = input.width(),
			isValidWidthChange = (newWidth < currentWidth && newWidth >= minWidth)
					     || (newWidth > minWidth && newWidth < o.maxWidth);

			// Animate width
			if (isValidWidthChange) {
				input.width(newWidth);
			}
		};
		testSubject.insertAfter(input);
		$(this).bind('keyup keydown blur update', check);

	});
	return this;
    };

})(jQuery);
$('input').autoGrowInput();