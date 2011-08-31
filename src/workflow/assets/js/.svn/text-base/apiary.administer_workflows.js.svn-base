function delete_workflow(workflow_id, ajax_url){
	if(confirm("Are you completely sure?")){
		$.ajax({
			url:ajax_url+"/"+workflow_id+"/0/0",
			success: function(result){
				if(result.length > 0) {
					if((result.search("success")) > -1){
						var ele = document.getElementById(workflow_id);
                				$(ele).remove();
                			}
                			alert(result);
				}
				else {
					alert("An error occurred during the deletion of this workflow.");
				}
			}
		});
	}
}