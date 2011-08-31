function save_transcribe_text()
{
    var transcribe_text = tinyMCE.get('transcribe_control').getContent();
    transcribe_text = transcribe_text.replace('<p>&nbsp;</p>','<br />');
	$.ajax({
		url: drupal_url+"/apiary/ajaxrequest/save_transcribe_text/"+OL_current_transcribe_roi+"/0",
		data: {"text": transcribe_text },
		success: function(returnJS){
			eval(returnJS);
			reload_transcribe_text();
			if ( permission_map['canParseL1']
				|| permission_map['canParseL2']
				|| permission_map['canParseL3'] )
			{
				var parse_level = OL_current_parse_level || "0";
				load_parse_tab(OL_current_transcribe_roi, parse_level);
			}
		},
		type: "POST"
	});
}

function reload_transcribe_text()
{
	if(OL_current_transcribe_roi.length > 0){
		roi_pid = OL_current_parse_roi;
		$.ajax({
			url: drupal_url+"/apiary/ajaxrequest/reload_transcribe_text/"+roi_pid+"/0",
			success: function(returnHTML){
				tinyMCE.get('transcribe_control').setContent(returnHTML);
			}
		});
	}
	else
	{
		alert("The is no ROI to reload the transcibe text tab for.");
	}
}

function load_transcribe_content(roi_pid)
{
	OL_current_transcribe_roi = roi_pid;
	var ht = $("div#step1_section").height();
	var wd = $(window).width()-540;
	$.ajax({
		url: drupal_url+"/apiary/ajaxrequest/get_transcribe_content/"+roi_pid+"/"+ht+":"+wd,
		success: function(returnJSON){
			$("#step2_section").html(returnJSON.image_html);
			//$("textarea#transcribe_control").val(returnJSON.text);
			tinyMCE.get('transcribe_control').setContent(returnJSON.text);
			$("textarea#ocrad").val(returnJSON.ocrad);
			$("textarea#ocropus").val(returnJSON.ocropus);
			$("textarea#gocr").val(returnJSON.gocr);
		},
		dataType: 'json'
	});
	if(OL_current_parse_roi != OL_current_transcribe_roi){
		load_parse_tab(OL_current_transcribe_roi);
	}
}

function copy_ocr(type)
{
	//$('#transcribe_control').val($('#transcribe_control').val()+"\n"+$('#'+type).val());
	tinyMCE.get('transcribe_control').setContent(tinyMCE.get('transcribe_control').getContent()+"<p>"+$('#'+type).val().replace("\n",'<br />')+"</p>");
}

function reprocess_ocr(type)
{
	var roi_pid = OL_current_transcribe_roi;
	if(type == "all") {
		$("#reprocess_ocrad_button").hide();
		$("#reprocess_ocropus_button").hide();
		$("#reprocess_gocr_button").hide();
		$("#reprocessing_ocrad").show();
		$("#reprocessing_ocropus").show();
		$("#reprocessing_gocr").show();
	}
	else {
		$("#reprocess_"+type+"_button").hide();
		$("#reprocessing_"+type).show();
	}
	$.get(drupal_url+"/apiary/workflow_ajax/process_ocr/"+workflow_id+"/"+roi_pid+"/"+type, function(results){
		data = $.parseJSON(results);
		if(type == "all" || type == "ocrad")
		{
			$("#ocrad").val(data.ocrad);
			$("#reprocessing_ocrad").hide();
			$("#reprocess_ocrad_button").show();
		}
		if(type == "all" || type == "ocropus")
		{
			$("#ocropus").val(data.ocropus);
			$("#reprocessing_ocropus").hide();
			$("#reprocess_ocropus_button").show();
		}
		if(type == "all" || type == "gocr")
		{
			$("#gocr").val(data.gocr);
			$("#reprocessing_gocr").hide();
			$("#reprocess_gocr_button").show();
		}
	        $.jGrowl(data.msg);
	});
}

function process_ocr(roi_pid, type)
{
	$.get(drupal_url+"/apiary/workflow_ajax/process_ocr/"+workflow_id+"/"+roi_pid+"/"+type, function(results){
		data = $.parseJSON(results);
	        $.jGrowl(data.msg);
	});
}