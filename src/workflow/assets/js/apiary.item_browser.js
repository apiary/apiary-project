function add_items_to_queue_original()
{
	parent.$.fn.colorbox.close();
	//alert("selected items: "+selected_items);
	$("#add_items_list .selected").each(function(){
		var item__id = $(this).attr("id");
		var specimen__pid = $(this).attr("specimen_pid");
		var queue_id = $(this).attr("id")+"_queue";
		if($('#'+queue_id).length==0){
			//alert("element does not exist");
			var image_pid = item__id.replace("__", ":");
			var specimen_pid = specimen__pid.replace("__", ":");
			add_image_with_rois_to_queue(specimen_pid, image_pid);
		}
		else {
			//it is already in the queue so show it/activate it/etc
		}
	});
}

function add_items_to_queue()
{
	parent.$.fn.colorbox.close();
	var new_selected_items = new Array();
	$("#add_items_list .selected").each(function(){
		var item__id = $(this).attr("id");
		var queue_id = $(this).attr("id")+"_queue";
		if($('#'+queue_id).length==0){
			//alert("element does not exist");
			var image_pid = item__id.replace("__", ":");
			new_selected_items.push(image_pid);
		}
		else {
			//it is already in the queue so show it/activate it/etc
		}
	});
	add_images_with_rois_to_queue(new_selected_items);
}

var item_browser_items_order_array = new Array();
function load_item_browser()
{
	myLayout.close('west');
	$('#item_browser').html('Please wait for the item browser to load...');
	$('.add_items_link').colorbox({inline:true, href:"#item_browser"});
	$.get(drupal_url+"/apiary/workflow_ajax/get_workflow_items/"+workflow_id+"/"+epp+"/0", function(results){
		data = $.parseJSON(results);
		if(data.workflow_items_successfully_created == "true") {
			var item_count = data.workflow_item_count;
			$("#item_browser").replaceWith(data.item_browser);
			$("#item_browser_items").append(data.workflow_items);
			item_browser_items_order_array = (data.item_browser_items_order_array).split(",");
			var items_length = $("#item_browser_items .pool_item").length;
			var array_length = item_browser_items_order_array.length;
			$('.add_items_link').unbind('click');
			fill_item_browser();
			$('.add_items_link').click();
			$('.add_items_link').unbind('click');
			$('.add_items_link').click(function(){
			  load_item_browser();
			});
		}
	});
}

function fill_item_browser()
{
	var total_items = item_browser_items_order_array.length;
	
	if($("#item_browser_items .pool_item").length != total_items)
	{
		//put all items back in item_browser_items
		restore_item_browser_items();
	}
	
	var available_items_added = 0;
	$.each(item_browser_items_order_array, function(index, item) {
		var items_per_page = epp;
		if(epp == "All")
		{
			items_per_page = total_items;
		}
		var page = Math.ceil((available_items_added+1)/items_per_page); //the plus 1 avoids 0 and puts numbers like 10 for an items_per_page of 10 onto page 2
		var first_page_item = (page-1)*items_per_page;
		var last_leftpanel_item = first_page_item+Math.round(items_per_page/2);
		var max_page_item = Math.min(page * items_per_page, total_items);
		//alert("page = "+page+", first_page_item ="+first_page_item+", last_leftpanel_item ="+last_leftpanel_item+", max_page_item ="+max_page_item+", available_items_added ="+available_items_added);
		if($("#"+item).hasClass('available'))
		{
			if(available_items_added >= first_page_item && available_items_added < last_leftpanel_item)
			{
				$("#add_items_page_"+page).children("div .leftpanel48").append($("#"+item));
			}
			else if(available_items_added >= first_page_item && available_items_added < max_page_item)
			{
				$("#add_items_page_"+page).children("div .rightpanel48").append($("#"+item));
			}
			last_items_page = "#add_items_page_"+page;
			available_items_added++;
		}
	});
	select_page("#add_items_page_1");
	load_delayed_images();
}

function restore_item_browser_items()
{
	$.each(item_browser_items_order_array, function(index, item) {
		$("#item_browser_items").append($("#"+item));
	});
}

function refresh_workflow_items()
{
}

function reload_item_browser()
{
	new_epp = $('#epp').val();
	if($('#epp').val() != epp) {
		restore_item_browser_items();//don't wipe the pool_items already in the item_browser
		epp = $('#epp').val();
		//load_item_browser();
		var total_items = item_browser_items_order_array.length;
		$.get(drupal_url+"/apiary/workflow_ajax/create_bare_item_browser/"+epp+"/"+total_items+"/0", function(results){
			data = $.parseJSON(results);
			if(data.bare_item_browser_successfully_created == "true") {
				$("#item_browser").replaceWith(data.item_browser);
				$('.add_items_link').click();
			}
		});
	}
}

function add_items_cancel()
{
	parent.$.fn.colorbox.close();
}

function select_page(page)
{
    console.log(page);
	var index = page.replace("#add_items_page_", "");
//	alert("index = "+index);
	$(current_page).hide();
	$(current_page+"_control").removeClass('current');
	$(page).show();
	$(page+"_control").addClass('current');
	current_page=page;
	load_delayed_images(page);
	if ( current_page == "#add_items_page_1" )
	{
	    $('#add_items_first_control').addClass('inactive');
	    $('#add_items_prev_control').addClass('inactive');
	}
	else
	{
	    $('#add_items_first_control').removeClass('inactive');
	    $('#add_items_prev_control').removeClass('inactive');
	}
	if ( current_page == last_items_page )
	{
	    $('#add_items_last_control').addClass('inactive');
	    $('#add_items_next_control').addClass('inactive');
	}
	else
	{
	    $('#add_items_last_control').removeClass('inactive');
	    $('#add_items_next_control').removeClass('inactive');
	}
}

function select_first_page()
{
    select_page("#add_items_page_1");
}

function select_next_page()
{
	var index = current_page.replace("#add_items_page_", "");
	index = parseInt(index)+1;
	
	if ( $("#add_items_page_"+index).html() != null )
        select_page("#add_items_page_"+index);
}

function select_prev_page()
{
	var index = current_page.replace("#add_items_page_", "");
	index = parseInt(index)-1;
	if ( $("#add_items_page_"+index).html() != null )
        select_page("#add_items_page_"+index);
}

function select_last_page()
{
    select_page(last_items_page);
}

function load_delayed_images(page)
{
	$(page+" img.delayLoad").each(function() {
		$(this).mouseover();// Simulate mouseover to switch the img src
		$(this).removeAttr("onmouseover");// Remove the mouseover attribute to prevet updating the img src every mouseover
		$(this).mouseout();// Not sure if this is needed but just to be safe
	}); 
}
