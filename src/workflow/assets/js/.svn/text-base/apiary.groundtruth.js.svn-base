var last_specimen_pid = "";
$(document).ready(function () {
	last_specimen_pid = $("#specimen_pid").val();
	load_groundtruth();
});

function load_groundtruth()
{
	var ajax_url = $("#ajax_url").html();
	var specimen_pid = 0;
	if($("#specimen_pid").val() != "")
	{
		specimen_pid = $("#specimen_pid").val();
	}
	$.ajax({
		url:ajax_url+"/groundtruth_content/"+specimen_pid+"/0/0",
		success: function(result)
		{
			if(result.length > 0) 
			{
				$("#groundtruth_content").replaceWith(result);
				last_specimen_pid = $("#specimen_pid").val();
			}
			else 
			{
				alert("An error occurred during the retrieval of groundtruth html.");
			}
		}
	});
}

function display_groundtruth() {
	load_groundtruth();
}

function reset_groundtruth() {
	$("#specimen_pid").val(last_specimen_pid);
	load_groundtruth();
}

function cancel_groundtruth() {
	$("#specimen_pid").val(last_specimen_pid);
	load_groundtruth();
}

function save_groundtruth() {
	var ajax_url = $("#ajax_url").html();
	if($("#specimen_pid").val() != last_specimen_pid)
	{
		if(confirm("The specimen pid had been changed to "+$("#specimen_pid").val()+" but this will still be saving the groundtruth for "+last_specimen_pid+". Proceed anyway?"))
		{
			//no need to do anything
		}
		else
		{
			return false;
		}
	}
	var groundtruth_xml = $("#groundtruth_data").val();
	$.post(ajax_url+"/save_groundtruth/0/0/0", {specimen_pid: last_specimen_pid, groundtruth_xml: groundtruth_xml}, function(results){
		data = $.parseJSON(results);
		if(data.successfully_saved_groundtruth == "true") 
		{
			alert("Successfully save groundtruth xml for specimen "+last_specimen_pid);
		}
		else
		{
			alert(data.msg);
		}
	});
}

function display_groundtruth_keyPressEvent(event)
{
	if(event.keyCode == 13)
	{
		display_groundtruth();
	}
}