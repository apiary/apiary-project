function parse_roi(pid)
{
    	if(pid != OL_current_parse_roi)
    	{
		select_roi(pid);
                load_parse_tab(pid);
	}
	select_nav('step3');
}

var update_parse_controls;
function update_controls()
{
	$('#parse-treeview').treeview();
}

function get_parse_elements(roi_pid, parse_level)
{
	OL_current_parse_roi = roi_pid;
	$.ajax({
		url: drupal_url+"/apiary/workflow_ajax/parse_elements/"+roi_pid+"/"+parse_level+"/"+workflow_id,
		success: function(returnJSON){
			$("div#step3_east_section").html(returnJSON.accordian);
            		update_parse_controls = setInterval(update_controls,100);
		},
		dataType: 'json'
	});
}

function get_parse_contents(roi_pid, parse_level)
{
	var ht = $("div#step1_section").height()*.8;
	var wd = $(".ui-layout-center").width()*.85 ;
	$.ajax({
		url: drupal_url+"/apiary/workflow_ajax/parsing_content/"+roi_pid+"/"+Math.round(ht)+":"+Math.round(wd)+"/"+parse_level,
		success: function(returnJSON){
			$("div#parse_tab2_section").html(returnJSON.image_html);
			$("div#parse_tab_text_content").html(returnJSON.text);
		},
		dataType: 'json'
	});
}

function load_parse_tab(roi_pid, parse_level)
{
	parse_level = parse_level || "0"; //we do not pass a parse_level for all calls to this function
	if(parse_level == "0" && OL_current_parse_level.length)
	{
		parse_level = OL_current_parse_level;
	}
	if(!isGetParseTabLoading)
	{
		isGetParseTabLoading = true; //global var initiated in the head if index.tpl.html which prevents the double ajax calling of this function
		var ht = $("div#step1_section").height()*.8;
		var wd = $(".ui-layout-center").width()*.85;
		var size = Math.round(ht)+":"+Math.round(wd);
		try
		{
			tinyMCE.execCommand('mceRemoveControl',true,'parse_textarea');
		}
		catch(e)
		{
			//alert("Unable to remove TinyMCE controls for parse_textarea");
		}
		$.ajax({
			url: drupal_url+"/apiary/workflow_ajax/parse_tab_nav/"+roi_pid+"/"+size+"/"+parse_level,
			success: function(results)
			{
				if(results.parse_tab_nav.length > 0)
				{
					OL_current_parse_roi = roi_pid;
					OL_current_parse_level = results.current_parse_level;
					$("#parse_tab_nav").unbind();
					$("#parse_tab_nav").replaceWith(results.parse_tab_nav);
					$("div#step3_east_section").html(results.accordian);
					update_parse_controls = setInterval(update_controls,100);
					if(OL_current_parse_level == "canParseL1")
					{
						var whowhenElements = [];
						var whatElements = [];
						var whereElements = [];
						
						var whowhenLabels = $.trim(results.whowhenLabels);
						var whowhenNames = $.trim(results.whowhenNames);
						var whowhenLabelsList = new Array();
						var whowhenNamesList = new Array();
						if(whowhenLabels.length > 0)
						{
							whowhenLabelsList = whowhenLabels.split(",");
							whowhenNamesList = whowhenNames.split(",");
							whowhenElements = tinymce_map_arrays(whowhenNamesList, whowhenLabelsList);
						}
						
						var whatLabels = $.trim(results.whatLabels);
						var whatNames = $.trim(results.whatNames);
						var whatLabelsList = new Array();
						var whatNamesList = new Array();
						if(whatLabels.length > 0)
						{
							whatLabelsList = whatLabels.split(",");
							whatNamesList = whatNames.split(",");
							whatElements = tinymce_map_arrays(whatNamesList, whatLabelsList);
							//$.each(whatElements, function(i, whatElements_array) {
							//	alert("reinit_tinymce_What: whatElements_array[title] = "+whatElements_array['title']+", whatElements_array[classes] = "+whatElements_array['classes']);
							//	alert("whatElements using i: whatElements[i][title] = "+whatElements[i]['title']+", whatElements[i][classes] = "+whatElements[i]['classes']);
							//});
						}
						
						var whereLabels = $.trim(results.whereLabels);
						var whereNames = $.trim(results.whereNames);
						var whereLabelsList = new Array();
						var whereNamesList = new Array();
						if(whereLabels.length > 0)
						{
							whereLabelsList = whereLabels.split(",");
							whereNamesList = whereNames.split(",");
							whereElements = tinymce_map_arrays(whereNamesList, whereLabelsList);
						}
						
						reinit_tinymce(whowhenElements, whatElements, whereElements);
						tinyMCE.execCommand('mceAddControl',true,'parse_textarea');
						//tinyMCE.execCommand('populateParseTreeview', false, null);
					}
					$(".tab1 .first").click();
					if(OL_current_parse_roi != OL_current_transcribe_roi)
					{
						load_transcribe_content(OL_current_parse_roi);
					}
					resize_east_pane();
				}
				else
				{
					$.jGrowl('Error retreiving the parse tab navigation (parsetab_nav).');
					//alert("An error occurred during the retrieval of the parse tab navigation (parsetab_nav).");
				}
				isGetParseTabLoading = false;
			},
			error: function() {
				$.jGrowl('Error in retrieval of the parse tab navigation (parsetab_nav).');
				isGetParseTabLoading = false;
			},
			dataType: 'json'
		});
	}
}
    
function find_taxa_via_ubio()
{
	roi_pid = OL_current_parse_roi;
	$.get(drupal_url+"/apiary/workflow_ajax/ubio_parse/"+roi_pid+"/0/0", function(results){
		data = $.parseJSON(results);
	        $('#ubio_results').replaceWith(data.ubio_result);
	        $.colorbox({width:"80%", height:"80%", inline:true, href:"#ubio_results"});
	});
}

function herbis_nlp()
{
	roi_pid = OL_current_parse_roi;
	$.get(drupal_url+"/apiary/workflow_ajax/herbis_parse/"+roi_pid+"/0/0", function(results){
		data = $.parseJSON(results);
		var herbis_result = $.trim(data.herbis_result);
		//alert("herbis_result = "+herbis_result);
		process_herbis_result(herbis_result);
        update_parse_spans();
	});
}

function process_herbis_result(herbis_result)
{
	var herbis_xml = $.parseXML(herbis_result);
	$(herbis_xml).find("labeldata").children().each(function(){
		var elem_name = (this).nodeName;
		var elem_value = $(this).text();
		apiary_elem_name = matchHERBISfield(elem_name);
		if(apiary_elem_name.length)
		{
			set_from_herbis_result(apiary_elem_name, elem_value);
		}
	});
}

function set_from_herbis_result(apiary_elem_name, elem_value)
{
	$("#"+apiary_elem_name).html(elem_value);
}

function matchHERBISfield(herbis_field)
{
	var apiary_elem;
	switch (herbis_field)
	{
		case "bt": apiary_elem = "";//Barcode Text
		break;
		case "bc": apiary_elem = "catalogNumber";//Barcode
		break;
		case "cn": apiary_elem = "recordNumber";//Collection Number
		break;
		case "cnl": apiary_elem = "";//Collection Number Label
		break;
		case "ct": apiary_elem = "occurrenceRemarks";//Citation
		break;
		case "dt": apiary_elem = "annotatedBy";//Determiner
		break;
		case "dtl": apiary_elem = "";//Determiner Label
		break;
		case "hd": apiary_elem = "locationRemarks";//Header
		break;
		case "hdlc": apiary_elem = "";//Header location indicating the area of a flora (e.g. Flora of Oz)
		break;
		case "in": apiary_elem = "institutionCode";//Institution
		break;
		case "fm": apiary_elem = "";//Family Name
		break;
		case "fml": apiary_elem = "";//Family Name label
		break;
		case "snl": apiary_elem = "";//Species Name Label
		break;
		case "gn": apiary_elem = "";//Genus
		break;
		case "sp": apiary_elem = "";//Species
		break;
		case "val": apiary_elem = "";//variety label
		break;
		case "va": apiary_elem = "";//variety
		break;
		case "pd": apiary_elem = "";//Plant Description
		break;
		case "pdl": apiary_elem = "";//Plant Description Label
		break;
		case "sa": apiary_elem = "";//Species Author
		break;
		case "cm": apiary_elem = "";//Common Name
		break;
		case "cml": apiary_elem = "";//Common Name Label
		break;
		case "hb": apiary_elem = "habitat";//Habitat
		break;
		case "hbl": apiary_elem = "";//Habitat Label
		break;
		case "alt": apiary_elem = "verbatimElevation";//Altitude
		break;
		case "altl": apiary_elem = "elevationLabel";//Altitude Label
		break;
		case "pb": apiary_elem = "";//Prepared by (Who prepared the specimen)
		break;
		case "pbl": apiary_elem = "";//Prepared by Label
		break;
		case "db": apiary_elem = "";//Distributed by
		break;
		case "dbl": apiary_elem = "";//Distributed by Label
		break;
		case "lc": apiary_elem = "locality";//Location
		break;
		case "lcl": apiary_elem = "localityLabel";//Location Label
		break;
		case "cd": apiary_elem = "verbatimEventDate";//Collection Date
		break;
		case "cdl": apiary_elem = "";//Collection Date Label
		break;
		case "co": apiary_elem = "recordedBy";//Collector
		break;
		case "col": apiary_elem = "";//Collector Label
		break;
		case "ft": apiary_elem = "";//Footer
		break;
		case "ftl": apiary_elem = "";//Footer Label
		break;
		case "ns": apiary_elem = "";//Noise
		break;
		case "ot": apiary_elem = "annotationRemarks";//Other: only cryptic information that had some meaning but no semantic code in this dtd
		break;
		case "tcl": apiary_elem = "";//town code label
		break;
		case "tc": apiary_elem = "";//town code
		break;
		case "scl": apiary_elem = "";//species code label
		break;
		case "sc": apiary_elem = "";//species code
		break;
		case "rgn": apiary_elem = "";//redetermination genus
		break;
		case "rsp": apiary_elem = "";//redetermination species
		break;
		case "rsa": apiary_elem = "";//redetermination species author
		break;
		case "rva": apiary_elem = "";//redetermination variety
		break;
		case "rva1": apiary_elem = "";//redetermination label variety label
		break;
		case "vaa": apiary_elem = "";//variety author
		break;
		case "rvaa": apiary_elem = "";//redetermination varity author
		break;
		case "rdscl": apiary_elem = "";//species label indide of redetermination label
		break;
		case "rddt": apiary_elem = "";//redetermination date
		break;
		case "rddtl": apiary_elem = "";//redetermination date label
		break;
		case "rin": apiary_elem = "";//redetermination institution/place
		break;
		case "rdp": apiary_elem = "";//redeterminator's name
		break;
		case "rdpl": apiary_elem = "";//redeterminator name's label
		break;
		case "tgn": apiary_elem = "";//type genus
		break;
		case "tsp": apiary_elem = "";//type species
		break;
		case "tsa": apiary_elem = "";//type species author
		break;
		case "pin": apiary_elem = "";//Possessing institution in a possession transfer label
		break;
		case "ptverb": apiary_elem = "";//possession transfer verb
		break;
		case "pprep": apiary_elem = "";//possession transfer preposition
		break;
		case "pperson": apiary_elem = "";//person doing possession transfer
		break;;
		case "pdt": apiary_elem = "";//possession transfer date on a possession transfer label
		break;
		case "oin": apiary_elem = "";//original owning institution stamp
		break;
		case "thd": apiary_elem = "";//type label header
		break;
		case "ddtl": apiary_elem = "";//determination date label
		break;
		case "dtd": apiary_elem = "";//determination date
		break;
		case "inlc": apiary_elem = "";//institution location
		break;
		case "mcl": apiary_elem = "";//microcitation label
		break;
		case "mc": apiary_elem = "";//microcitation
		break;
		case "ty": apiary_elem = "";//type specimen
		break;
		case "latlon": apiary_elem = "";//latitude and longtitude
		break;
		default: apiary_elem = "";
		break;
	}
	return apiary_elem;
}
            
function save_parse_text()
{
	var specimenMetadata = getSpecimenMetadataFromParseTreeView();
	//var text = tinyMCE.get(0).getContent();
	var text = tinyMCE.get('parse_textarea').getContent();
	//alert("text = "+text);
	text = text.trim();
		//data: {"text": $("#parse_tab_text_content").html().trim(), "specimenMetadata": specimenMetadata},
		//data: {"text": $("#parse_textarea").html().trim(), "specimenMetadata": specimenMetadata},
	$.ajax({
		url: drupal_url+"/apiary/ajaxrequest/save_parsed_text/"+OL_current_parse_roi+"/0",
		data: {"text": text, "specimenMetadata": specimenMetadata},
		success: function(returnJS){
			eval(returnJS);
			reload_transcribe_text();
		},
		type: "POST"
	});
}
            
function getSpecimenMetadataFromParseTreeView()
{
	var specimenMetadata = "";
	$("#parse-treeview").find("span").each(function() {
		var element = $(this).attr('id');
		var value = $(this).html().trim();
		if(element.length > 0 && value.length > 0)
		{
			if(specimenMetadata.length > 0)
			{
				specimenMetadata = specimenMetadata+"&";
			}
			specimenMetadata = specimenMetadata+element+"="+value;
		}
	});
	return specimenMetadata;
}

function select_parse_level(parse_level)
{
    	if(parse_level != OL_current_parse_level)
    	{
		load_parse_tab(OL_current_parse_roi, parse_level);
	}
}
            
function addSpecimenMetadataToParseTreeView(element, specimenMetadata)
{
	//alert("element = " +element+", specimenMetadata = "+specimenMetadata);
	var current_specimenMetadata = $("#parse-treeview #"+element).html().trim();
	if(current_specimenMetadata.length > 0 && specimenMetadata.length > 0)
	{
		//alert("passed in specimenMetadata = "+specimenMetadata);
		//alert("current_specimenMetadata = "+current_specimenMetadata);
		$("#parse-treeview #"+element).html(current_specimenMetadata+" ; "+specimenMetadata);
	}
	else if(specimenMetadata.length > 0)
	{
		$("#parse-treeview #"+element).html(specimenMetadata);
	}
}
            
function removeSpecimenMetadataToParseTreeView(element, specimenMetadata)
{
	var current_specimenMetadata = $("#parse-treeview #"+element).html().trim();
	specimenMetadata = specimenMetadata.trim();
	if(current_specimenMetadata.indexOf(" ; "+specimenMetadata) > -1)
	{
		var new_specimenMetadata = current_specimenMetadata.replace(" ; "+specimenMetadata, "");
		$("#parse-treeview #"+element).html(new_specimenMetadata);
	}
	else if(current_specimenMetadata.indexOf(specimenMetadata+" ; ") > -1)
	{
		var new_specimenMetadata = current_specimenMetadata.replace(specimenMetadata+" ; ", "");
		$("#parse-treeview #"+element).html(new_specimenMetadata);
	}
	else if(current_specimenMetadata.indexOf(specimenMetadata) > -1)
	{
		var new_specimenMetadata = current_specimenMetadata.replace(specimenMetadata, "");
		$("#parse-treeview #"+element).html(new_specimenMetadata);
	}
}
