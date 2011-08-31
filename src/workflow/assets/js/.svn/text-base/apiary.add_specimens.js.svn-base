$(document).ready(function () {
	var ajax_url = $("#ajax_url").html();
	$.ajax({
		url:ajax_url+"/research_server_image_list/0/0/0",
		success: function(result)
		{
			if(result.length > 0) 
			{
				$("#specimen_list").replaceWith(result);
			}
			else 
			{
				alert("An error occurred during the retrieval of available specimen images.");
			}
		}
	});
});

function toggle_selected(item)
{
	if ( $(item).hasClass('unselected') )
	{
		$(item).removeClass('roundedcornr_ltgray');
		$(item).addClass('roundedcornr_dgray');
		$(item).removeClass('unselected');
		$(item).addClass('selected');
		$(item+" .no_specimen_image_metadata").hide();
		$(item+" .specimen_image_metadata").show();
	}
	else if ( $(item).hasClass('selected') && !$(item).hasClass('ingested'))
	{
		$(item).removeClass('roundedcornr_dgray');
		$(item).addClass('roundedcornr_ltgray');
		$(item).removeClass('selected');
		$(item).addClass('unselected');
		$(item+" .specimen_image_metadata").hide();
		$(item+" .no_specimen_image_metadata").show();
	}
}

var selected_noningested_count = 0;
var ingested_count = 0;
var selected_noningested = new Array();
function ingest_specimen_images()
{
	selected_noningested_count = 0;
	ingested_count = 0;
	selected_noningested = new Array();
	$('.selected').each(function() {
		if(!$(this).hasClass('ingested'))
		{
			var id = $(this).attr('id');
			selected_noningested.push(id);
			selected_noningested_count = selected_noningested_count + 1;
		}
	});
	if(selected_noningested_count > 0)
	{
		if(confirm("WARNING:Do not interrupt this process!\nIngestion can take up to a minute per specimen.\nYou are attempting to ingest "+selected_noningested_count+" images.\nDo you wish to proceed?")){
			$("#ingest_specimen_images_btn").hide();
			ingest_specimen_image(ingested_count);
		}
	}
	else
	{
		alert("No images have been selected to be added!");
	}
}

function ingest_specimen_image(index)
{
	var ajax_url = $("#ajax_url").html();
	var id = selected_noningested[index];
	var rft_id = id.replace('_d_','.').replace('__',':').replace('_s_','/');
	var original_url = $("#"+id).attr('original_url');
	var jp2_url = $("#"+id).attr('jp2_url');
	var collector = $("#"+id+"_collector").val();
	var collection_number = $("#"+id+"_collection_number").val();
	var collection_date = $("#"+id+"_collection_date").val();
	var scientific_name = $("#"+id+"_scientific_name").val();
	var metadata_html = $("#"+id+"_metadata").html();
	$.post(ajax_url+"/ingest_specimen_image/0/0/0", {rft_id: rft_id, original_url: original_url, jp2_url: jp2_url, collector: collector, collection_number: collection_number, collection_date: collection_date, scientific_name: scientific_name }, function(results){
		data = $.parseJSON(results);
		if(data.specimen_image_successfully_ingested == "true") 
		{
			$("#"+id).addClass('ingested');
			$("#"+id+"_metadata").html("<strong>"+rft_id+" Ingestion Completed!</strong><br/>"+data.msg);
		}
		else
		{
			$("#"+id+"_metadata").html("<strong>"+rft_id+" Ingestion Not Completed!</strong><br/>"+data.msg);
		}
		ingested_count = ingested_count + 1;
		if(ingested_count == selected_noningested_count)
		{
			alert("All specimens have finished being created");
			$("#ingest_specimen_images_btn").show();
		}
		else
		{
			ingest_specimen_image(ingested_count);
		}
	});
}