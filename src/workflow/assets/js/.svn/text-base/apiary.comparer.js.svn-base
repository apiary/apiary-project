function compare_text()
{
	var old_text = $("#old_text").val();
	var new_text = $("#new_text").val();
	$.post(drupal_url+"/apiary/comparer/compare_text/0/0/0", { old_text: old_text, new_text: new_text }, function(results){
		data = $.parseJSON(results);
		if(data.msg.length)
		{
			alert(data.msg);
		}
		$('#daisydiff').html(data.daisydiff_html);
		$('#highlight_diff').html(data.highlight_diff_html);
		$('#percent_results').html(data.percent_results_html);
	});
}

function get_comparer_options()
{
	$.ajax({
		url: drupal_url+"/apiary/comparer/get_comparer/0/0/0",
		success: function(comparer_html) {
			$("#comparer").replaceWith($.trim(comparer_html));
		},
		error: function() {
			alert("You must be logged in and have permissions to View the Apiary Project to view this page.");
			window.location.href = drupal_url;
		}
	});
}

function reset()
{
	$("#old_text").val("");
	$("#new_text").val("");
	$('#daisydiff').html("");
	$('#highlight_diff').html("");
	$('#percent_results').html("");
}