var current_search_type = "metadata_search";
function get_search_options()
{
	$.ajax({
		url: drupal_url+"/apiary/search/get_search/0/0/0",
		success: function(search_html) {
			$("#search").html($.trim(search_html));
		},
		error: function() {
			alert("You must be logged in and have permissions to View the Apiary Project to view this page.");
			window.location.href = drupal_url;
		}
	});
}

function select_search(type)
{
	if(type != current_search_type)
	{
		$("#search_options").load(drupal_url+"/apiary/search/get_"+type+"_options/0/0/0", function() {
			$('#search_results').html("");
			current_search_type = type;
		});
	}
}

function submit_metadata_search()
{
	var query = $("#metadata_query").val();
	var field = $("#metadata_field").val();
	$.post(drupal_url+"/apiary/search/search_metadata/0/0/0", { query: query, field: field }, function(results){
		data = $.parseJSON(results);
		if(data.msg.length)
		{
			alert(data.msg);
		}
		//data.successfully_searched_text
		$('#search_results').html(data.results_html);
		tableSort();
	});
}

function display_specimenMetadata_details(roi_pid)
{
	$.ajax({
		url:drupal_url+"/apiary/workflow_ajax/specimenMetadata_details_content/"+roi_pid+"/0/0",
		success: function(result)
		{
			if(result.length > 0) 
			{
				$.colorbox({width:"90%", height:"90%", inline:true, href:result});
			}
			else 
			{
				alert("An error occurred during the retrieval of specimenMetadata details.");
			}
		}
	});
}

function submit_status_search()
{
	var as_status = $("#as_statuses").val();
	var tt_status = $("#tt_statuses").val();
	var pt_status = $("#pt_statuses").val();
	var query = $("#status_query").val();
	$.post(drupal_url+"/apiary/search/search_status/0/0/0", { query: query, as_status: as_status, tt_status: tt_status, pt_status: pt_status }, function(results){
		data = $.parseJSON(results);
		if(data.msg.length)
		{
			alert(data.msg);
		}
		//data.successfully_searched_text
		$('#search_results').html(data.results_html);
		tableSort();
	});
}

function tableSort()
{
	$("table")
	.tablesorter({ 
		headers: { 
			1: {                
				sorter: false 
			},             
			2: { 
				sorter: false 
			},
			3: { 
				sorter: false 
			}			
		} 
	})
	.tablesorter({widthFixed: true, widgets: ['zebra']})
	.tablesorterPager({container: $("#pager")});
}

function reset()
{
	$('#search_results').html("");
}

function keyPressEvent(event, action)
{
	if(event.keyCode == 13 && action == 'submit_metadata_search')
	{
		submit_metadata_search();
	}
	if(event.keyCode == 13 && action == 'submit_status_search')
	{
		submit_status_search();
	}
}