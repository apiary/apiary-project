function create_object_pool(create)
{
	if(create == "true")
	{
		var object_pool_name = $("#object_pool_name").val();
		var object_pool_description = $("#object_pool_description").val();
		var object_pool_query_type = $("#object_pool_query_types").val();
		var object_pool_query = $("#object_pool_query").val();
		$.post(drupal_url+"/apiary/object_pool/create_object_pool/0/0/0", { object_pool_name: object_pool_name, object_pool_description: object_pool_description, object_pool_query_type: object_pool_query_type, object_pool_query: object_pool_query }, function(results){
			data = $.parseJSON(results);
			alert(data.msg);
			if($("#object_pool_names").length) 
			{ //this is used in workflow
				if(data.object_pool_successfully_created == "true") 
				{
					$.get(drupal_url+"/apiary/workflow_editor/object_pool_cbox_option/"+object_pool_name+"/ selected/0", function(response)
					{
						cbox_option = $.trim(response);
						$("#object_pool_names").append(cbox_option);
						clear_object_pool_text();
						$(".overlay_object_pool").colorbox.close();
					});
				}
			}
		});
	}
	else
	{
		$(".overlay_object_pool").colorbox.close();
	}
}

function clear_object_pool_text()
{
	if($("#object_pool_name").length)
	{
		$("#object_pool_name").val('');
	}
	if($("#object_pool_description").length)
	{
		$("#object_pool_description").val('');
	}
	if($("#object_pool_query").length)
	{
		$("#object_pool_query").val('');
	}
}

function view_object_pool_results()
{
	alert("view_object_pool_results!");
}

function update_object_pool_query_space(query_type)
{
	if (query_type == "Resource Index Query")
	{
		$("#object_pool_name").hide();
	}
	else if(query_type == "SOLR Query")
	{
	}
	else if (query_type == "Specific List")
	{
	}
}