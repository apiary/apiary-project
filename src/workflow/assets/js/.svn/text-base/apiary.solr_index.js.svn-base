$(document).ready(function () {
	var ajax_url = $("#ajax_url").html();
	$.ajax({
		url:ajax_url+"/solr_index_content/0/0/0",
		success: function(result)
		{
			if(result.length > 0) 
			{
				$("#solr_index_content").replaceWith(result);
			}
			else 
			{
				alert("An error occurred during the retrieval of available solr index all html.");
			}
		}
	});
});

function solr_index_all()
{
	if(confirm("Do not interrupt this process once it has begun!\nAre you sure you wish to re-index now?")){
		$("#solr_index_all_btn").hide();
		var ajax_url = $("#ajax_url").html();
		$.ajax({
			url:ajax_url+"/solr_index_all/0/0/0",
			success: function(result)
			{
				if(result.length > 0) 
				{
					$("#solr_index_content").replaceWith(result);
				}
				else 
				{
					alert("An error occurred during the retrieval of available solr index all html.");
				}
			}
		});
	}
}