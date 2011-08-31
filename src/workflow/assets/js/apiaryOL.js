var map, polygonLayer, pan, controls, modify, draw, remove, tempBounds, tempIndex, panel, panelLoaded= false, inCall=false;
var selected_roi_type, boxes, dialog, tempBounds, metadata, totalROIs=0, doc_x, doc_y, feature_temp;
var OL_loaded_image_pid, OL_current_parse_roi = '', OL_current_transcribe_roi, OL_current_parse_level = '';
var fastconfirm_x, fastconfirm_y;
var roiBoxes = new Array();
var roi_creation_handler = new Array();
var selected_text;
OpenLayers.Feature.Vector.style['default']['strokeWidth'] = '2';
var OUlayer;
var selected_id = null;

function init(metadataUrl, jpegURL, roi_boxes)
{
    feature_temp = undefined;
	roiBoxes = new Array();
	if(panelLoaded)
	{
	    if ( draw != undefined )
    		draw.destroy();
        if ( modify != undefined )
    		modify.destroy();
        if ( pan != undefined )
    		pan.destroy();
        if ( remove != undefined )
    		remove.destroy();
        if ( panel != undefined )
    		panel.destroy();
	}
	panelLoaded = true;
	//djatoka_url set from smarty template
	OUlayer = new OpenLayers.Layer.OpenURL( "Specimen",djatoka_url, {layername: 'basic', format:'image/jpeg', rft_id:jpegURL, metadataUrl: metadataUrl} );
	metadata = OUlayer.getImageMetadata();
	var resolutions = OUlayer.getResolutions();
	var box_extents = new Array();
	for(roi_pid in roi_boxes)
	{
		box_extents.push([roi_boxes[roi_pid].x, metadata.height-roi_boxes[roi_pid].y-roi_boxes[roi_pid].h, roi_boxes[roi_pid].x*1 + roi_boxes[roi_pid].w*1, metadata.height-roi_boxes[roi_pid].y, roi_boxes[roi_pid].pid, roi_boxes[roi_pid].type]);
	}
	/*var box_extents = [
        [apr_x, metadata.height-apr_y-apr_h, apr_x+apr_w, metadata.height-apr_y],
        // [2566, 16, 3766, 608],
        // [6118, 8912, 7030, 9792],
        // [454, 7968, 2726, 9232]
    	];*/
    boxes  = new OpenLayers.Layer.Boxes( "Captured ROI" );
    polygonLayer = new OpenLayers.Layer.Vector("Layer to Modify ROIs", {displayInLayerSwitcher: true, reproject: true});
    for (var i = 0; i < box_extents.length; i++) 
    {
        ext = box_extents[i];
        bounds = new OpenLayers.Bounds(ext[0], ext[1], ext[2], ext[3]);
        selected_roi_type = box_extents[i][5];
        addROIBox(bounds, box_extents[i][4]);
    }       
    var maxExtent = new OpenLayers.Bounds(0, 0, metadata.width, metadata.height);
    var tileSize = OUlayer.getTileSize();
    var options = {resolutions: resolutions, maxExtent: maxExtent, tileSize: tileSize,
                   controls: [
                        new OpenLayers.Control.MouseDefaults(),
                        new OpenLayers.Control.Navigation(),
                        new OpenLayers.Control.ArgParser(),
                        new OpenLayers.Control.Attribution()
                   ]
                   };
    //var options = {resolutions: resolutions, maxExtent: maxExtent, tileSize: tileSize};
    map = new OpenLayers.Map( 'map', options);
    map.div.oncontextmenu = function noContextMenu(e) {
        console.log('click');
        console.log(e);
        console.log(OpenLayers.Event.isRightClick(e));
        if (OpenLayers.Event.isRightClick(e))
        {
            var feature = getFeatureIdFromEvent(e);
            console.log(feature);
            //console.log(e);
            var roi_pid = boxes.markers[feature].id;
            display_popup(e,roi_pid);
            //e.stop();
            // alert("Right button click"); // Add the right click menu here
        }
        return false; //cancel the right click of brower
    }; 

    // End of Image rendering
// Beginning of Box rendering
    polygonLayer.events.on({
		'beforefeaturemodified': function(feature) {
			tempBounds = feature.feature.geometry.getBounds();
        },
        'afterfeaturemodified': function(modifiedfeature) {
			// polygonLayer.removeFeatures([modifiedfeature.feature]);
			updateROIBox(modifiedfeature.feature.geometry.getBounds(), tempBounds);
			$.jGrowl("ROI coordinates Modified");
        }
    });
    polyOptions = {sides: 4, snapAngle: 180, irregular: true};
    pan = new OpenLayers.Control.Navigation({title: "Navigate"});
    pan.events.on({'activate': function(control){
		    polygonLayer.destroyFeatures();
	    }
    });
    draw = new OpenLayers.Control.DrawFeature(polygonLayer, OpenLayers.Handler.RegularPolygon,{ 
		handlerOptions: polyOptions, displayClass: "olControlDrawFeaturePoint", featureAdded: function(feature){draw_feature_added(feature);}
	});
	
	draw.events.on({'activate': function(control){
			polygonLayer.destroyFeatures();
		}
	});
	
	/*
	 * modify = new OpenLayers.Control.ModifyFeature(
	 * polygonLayer, {displayClass:
	 * "olControlModifyFeature", title: "Modify Features"} );
	 * modify.events.on({'activate': function(control){
	 * polygonLayer.destroyFeatures(); drawAllROIFeatures(); }
	 * });
	 */
	modify = new OpenLayers.Control.TransformFeature(
		polygonLayer, {preserveAspectRatio: false, displayClass: "olControlModifyFeature", title: "Modify Features"}
	);
	
	modify.events.on({
	    'activate': function(control){
		    polygonLayer.destroyFeatures();
		    drawAllROIFeatures();
		},
		"beforesetfeature": function(feature){
			tempIndex = getIndex(feature.feature.geometry.getBounds());
		},
		"transformcomplete": function(feature){
		    modify_transform_complete(feature);
		}
	});
	
    remove = new OpenLayers.Control.SelectFeature( polygonLayer, {
        displayClass: "olControlRemoveFeature", title: "Remove Polygon",
	    geometryTypes: ["OpenLayers.Geometry.Polygon"],onSelect: function (feature) {
						ind = getIndex(feature.geometry.bounds);
						roi_id = roiBoxes[ind].id;
						delete_roi(roi_id);
		}
    });
    
    remove.events.on({'activate': function(control){
    	polygonLayer.destroyFeatures();
    	drawAllROIFeatures();
    	}
    });
    var container = document.getElementById("panel");                                                    
    panel = new OpenLayers.Control.Panel({
         displayClass: "olControlEditingToolbar",
         div:container
    });
    panel.addControls([pan, draw]);
    map.addControl(panel);
    var lon = metadata.width / 2;
    var lat = metadata.height / 2;
    /*selectControl = new OpenLayers.Control.SelectFeature([polygonLayer, boxes],{
        clickout: true, toggle: false,
        multiple: false, hover: false,
        toggleKey: "ctrlKey", // ctrl key removes from selection
        multipleKey: "shiftKey" // shift key adds to selection
    });*/
    
    //            map.addControl(selectControl);
//    map.addControls([draw, modify, remove, selectControl]);
    map.addControls([draw, modify, remove]);
    //selectControl.activate();
    //map.addControl(new OpenLayers.Control.LayerSwitcher());
    //map.addLayers([OUlayer, boxes, polygonLayer]);
    map.addLayers([OUlayer, polygonLayer, boxes]);
    
    map.setCenter(new OpenLayers.LonLat(lon, lat), 0);
    map.zoomToMaxExtent();
    pan.activate();
    /*
     * modify.mode &= ~OpenLayers.Control.ModifyFeature.RESHAPE;
     * modify.mode |= OpenLayers.Control.ModifyFeature.RESIZE;
     * modify.mode |= OpenLayers.Control.ModifyFeature.DRAG;
     */
     adjust_openlayers_controls();
     polygonLayer.setVisibility(false);
     pan.activate();
     draw.deactivate();
}
var test_bounds = null;
function getFeatureIdFromEvent(evt) 
{
    OpenLayers.Event.stop(evt,false);
    console.log(evt);
    var pixel
    if ( $.browser.webkit == true )
        pixel = new OpenLayers.Pixel(evt.clientX-25,evt.clientY-25);
    else
        pixel = new OpenLayers.Pixel(evt.layerX,evt.layerY);
        
    console.log(pixel);
    var loc = map.getLonLatFromPixel(pixel);
    console.log(loc);
    var resolution = this.map.getResolution();
    var bounds = new OpenLayers.Bounds(loc.lon - resolution * 5, loc.lat - resolution * 5, loc.lon + resolution * 5, loc.lat + resolution * 5);
    test_bounds = bounds;
    var geom = bounds.toGeometry();
    console.log(geom);
    //console.log(this.features);
    for (var feat in boxes.features) 
    {
        if (!boxes.features.hasOwnProperty(feat)) {
            continue;
        }
        if (boxes.features[feat].geometry.intersects(geom)) {
            return feat;
        }
    }
    for (var marker in boxes.markers) 
    {
        if (boxes.markers[marker].bounds.intersectsBounds(bounds) )
        {
            return marker;
        }
    }
    return null;
}


function modify_transform_complete(feature)
{
	var bounds = feature.object.box.geometry.getBounds();
	if ( (bounds.left < 0 && bounds.right < 0) 
	  || (bounds.left > metadata.width && bounds.right > metadata.width)
	  || (bounds.bottom < 0 && bounds.top < 0) 
	  || (bounds.bottom > metadata.height && bounds.top > metadata.height) 
	  )
    {
		alert("Invalid coordinates");
		polygonLayer.destroyFeatures([modify.feature]);
		var newFeature = drawROIFeature(roiBoxes[tempIndex].bounds);
		modify.setFeature(newFeature);
    }
    else
    {
		if (bounds.left < 0)
		    bounds.left = 0;
		if (bounds.right > metadata.width)
		    bounds.right = metadata.width;
		if (bounds.bottom < 0)
		    bounds.bottom = 0;
		if (bounds.top > metadata.height) 
		    bounds.top = metadata.height;
		$("#map").fastConfirm({
			position: "right",
			questionText: "<u>Choose ROI type</u><br/>"+
			"<input type='radio' name='roi_type_selection' id='roi_type_selection' value='Primary Label'>Primary Label<br/>"+
			"<input type='radio' name='roi_type_selection' id='roi_type_selection' value='Annotation'>Annotation/Other<br/>"+
			"<input type='radio' name='roi_type_selection' id='roi_type_selection' value='Barcode'>Barcode<br/>"+
			"<input type='radio' name='roi_type_selection' id='roi_type_selection' value='Undefined'>Undefined<br/>",
			proceedText: "Modify",
			cancelText: "Cancel",
			onProceed: function(trigger){
				updateROIBox(feature.object.box.geometry.getBounds(), roiBoxes[tempIndex].bounds);
				tempIndex = getIndex(feature.object.box.geometry.getBounds());
				$.jGrowl("ROI of type label "+selected_roi_type+" updated for the Specimen");
			},
			onCancel: function(trigger){
				polygonLayer.destroyFeatures([modify.feature]);
				var newFeature = drawROIFeature(roiBoxes[tempIndex].bounds);
				modify.setFeature(newFeature);
			},
			onClose: function(trigger){
				polygonLayer.destroyFeatures([modify.feature]);
				var newFeature = drawROIFeature(roiBoxes[tempIndex].bounds);
				modify.setFeature(newFeature);
			},
			unique:true
		});
		$(".fast_confirm").css("left", doc_x + 20);
		$(".fast_confirm").css("top", doc_y - 80);
		selected_roi_type = $("input[name='roi_type_selection").val();
		$("input[name='roi_type_selection']").change(function(){
			selected_roi_type = $(this).val();
		});
	}

}

function draw_feature_added(feature)
{
    polygonLayer.setVisibility(true);
    if ( feature_temp != undefined ) 
    {
        //console.log(feature_temp);
        removeROIFeature(feature);
        feature = feature_temp;
    }
    else
    {
        fastconfirm_x = doc_x;
        fastconfirm_y = doc_y;
    }
	feature_temp = feature;
	//console.log(feature);
	if (feature.geometry.bounds.left > feature.geometry.bounds.right - 12
	  || feature.geometry.bounds.top < feature.geometry.bounds.bottom + 12 )
	{
		clear_feature_temp();
        polygonLayer.setVisibility(false);
	}
	else
	{
    	if ( (feature.geometry.bounds.left < 0 && feature.geometry.bounds.right < 0) 
    	  || (feature.geometry.bounds.left > metadata.width && feature.geometry.bounds.right > metadata.width)
    	  || (feature.geometry.bounds.bottom < 0 && feature.geometry.bounds.top < 0) 
    	  || (feature.geometry.bounds.bottom > metadata.height && feature.geometry.bounds.top > metadata.height) 
    	  )
    	{
    		alert("Invalid coordinates");
    		clear_feature_temp();
    	}
    	else 
    	{
    		if (feature.geometry.bounds.left < 0)
    		    feature.geometry.bounds.left = 0;
    		if (feature.geometry.bounds.right > metadata.width)
    		    feature.geometry.bounds.right = metadata.width;
    		if (feature.geometry.bounds.bottom < 0)
    		    feature.geometry.bounds.bottom = 0;
    		if (feature.geometry.bounds.top > metadata.height) 
    		    feature.geometry.bounds.top = metadata.height;
    		$("#map").fastConfirm({
    			position: "right",
    			questionText: "<div class='fastconfirm-select-text' style='text-align:left;'><u>Choose ROI type</u></label><br/>"+
    			"<label for='primaryLabel'><input type='radio' name='roi_type_selection' id='primaryLabel' value='Primary Label' checked>Primary Label</label><br/>"+
    			"<label for='annotation'><input type='radio' name='roi_type_selection' id='annotation' value='Annotation'>Annotation/Other</label><br/>"+
    			"<label for='barcode'><input type='radio' name='roi_type_selection' id='barcode' value='Barcode'>Barcode</label><br/>"+
    			"<label for='non-defined'><input type='radio' name='roi_type_selection' id='non-defined' value='Undefined'>Undefined</label>"+
    			"</div>",
    			proceedText: "Confirm",
    			cancelText: "Cancel",
    			onProceed: function(trigger){
    				confirm_yes();
    			},
    			onCancel: function(trigger){
    				confirm_no();
    				polygonLayer.setVisibility(false);
    			},
    			onClose: function(trigger){
    				confirm_no();
    				polygonLayer.setVisibility(false);
    			},
    			unique:true
    		});
    		$(".fast_confirm").css("left", fastconfirm_x + 20);
    		$(".fast_confirm").css("top", fastconfirm_y - 80);
    		selected_roi_type = $("input[name='roi_type_selection']").val();
    		$("input[name='roi_type_selection']").change(function(){
    			selected_roi_type = $(this).val();
    		});
        }
    }
}

function pan_down()
{
    map.pan(0, 50);
}

function pan_up()
{
    map.pan(0, -50);
}

function pan_left()
{
    map.pan(-50, 0);
}

function pan_right()
{
    map.pan(50, 0);
}

function zoom_in()
{
    map.zoomIn();
}

function zoom_out()
{
    map.zoomOut();
}

function zoom_world()
{
    map.zoomToMaxExtent();
}
            
function removeROIFeature(feature)
{
	polygonLayer.events.triggerEvent("featureunselected", {feature: feature});
    polygonLayer.destroyFeatures([feature]); 
}

function removeROIBox(bounds)
{
    theIndex = getIndex(bounds);
    if(theIndex)
    {
        totalROIs--;
        boxes.removeMarker(roiBoxes[theIndex]);
        roiBoxes.splice(theIndex, 1);
    }
    return theIndex;
}
            
function editROIType(roi_pid)
{
	var roi_pid_replacement = roi_pid.replace(":", "_");
	$("#"+roi_pid_replacement+"-type").fastConfirm({
		position: "left",
		questionText: "<div class='fastconfirm-select-text' style='text-align:left;'><u>Choose ROI type</u></label><br/>"+
		"<label for='primaryLabel'><input type='radio' name='roi_type_selection' id='primaryLabel' value='Primary Label' checked>Primary Label</label><br/>"+
		"<label for='annotation'><input type='radio' name='roi_type_selection' id='annotation' value='Annotation'>Annotation/Other</label><br/>"+
		"<label for='barcode'><input type='radio' name='roi_type_selection' id='barcode' value='Barcode'>Barcode</label><br/>"+
		"<label for='non-defined'><input type='radio' name='roi_type_selection' id='non-defined' value='Undefined'>Undefined</label>"+
		"</div>",
		proceedText: "Save",
		cancelText: "Cancel",
		onProceed: function(trigger){
			requestROITypeChange(roi_pid, roi_pid_replacement);
		    var color = getColorByType(selected_roi_type);
		    changeROIColorByPid(roi_pid,color);
			$("#"+roi_pid_replacement+"-type").fastConfirm('close');
		},
		onCancel: function(trigger){
			$("#"+roi_pid_replacement+"-type").fastConfirm('close');
		},
		onClose: function(trigger){
			$("#"+roi_pid_replacement+"-type").fastConfirm('close');
		},
		unique:true
	});
	var roi_type = $("#"+roi_pid_replacement+"-type").html();
	if(roi_type=="Annotation/Other")
		if(roi_type=="Annotation/Other")
		{
		roi_type = "Annotation";
        		roi_type = "Annotation";
        	}
	selected_roi_type = roi_type;
            	try
            	{
            	    if ( roi_type == "Primary Label" )
                    	$("input[id='primaryLabel']").attr("checked","checked");
                    else
                		$("input[name='roi_type_selection']").filter("[value="+roi_type+"]").attr("checked","checked");
            	}
            	catch(e)
            	{
            		$("input[name='roi_type_selection']").filter("[value=Undefined]").attr("checked","checked");
            	}
	$("input[name='roi_type_selection']").change(function(){
		selected_roi_type = $(this).val();
	});
}
            
function requestROITypeChange(roi_pid, roi_pid_replacement)
{
	var current_roi_type = $("#"+roi_pid_replacement+"-type").html();
	if(selected_roi_type=="Annotation")
		var roi_type = "Annotation/Other";
	else
		var roi_type = selected_roi_type;
	$("#"+roi_pid_replacement+"-type").html(roi_type);
	$.ajax({
    	url: drupal_url+"/apiary/ajaxrequest/change_roi_type/"+roi_pid+"/"+selected_roi_type,
     	success: function(returnText){
			if(returnText.search("Success"))
			{
				$.jGrowl("ROI type for '"+roi_pid+"' is now '"+roi_type+"'");
			}
			else
			{
				$("#"+roi_pid_replacement+"-type").html(current_roi_type);
				$.jGrowl("Request to change ROI failed");
			}
		}
	});
}
            
function removeROIBoxByPid(pid)
{
    totalROIs--;
    for(x in roiBoxes)
    {
        if(roiBoxes[x].id == pid)
        {
            boxes.removeMarker(roiBoxes[x]);
            roiBoxes.splice(x, 1);
            return;
        }
    }
}
            
function changeROIColorByPid(pid,color)
{
    totalROIs--;
    for(x in roiBoxes)
    {
        if(roiBoxes[x].id == pid)
        {
            roiBoxes[x].setBorder(color,2);
            return;
        }
    }
}
            
function create_roi(ROIObjReq)
{
	$.ajax({
    	url: ROIObjReq[0], 
     	success: function(returnJSON){
			roiBoxes[ROIObjReq[1]].id = returnJSON.pid;
			$("#step1_east_section").append(returnJSON.html);
     		$.jGrowl("New ROI: "+returnJSON.pid + " is created!");
     		var image_content_div = "content-"+OL_loaded_image_pid.replace(":", "__");
     		$('#'+image_content_div).append(returnJSON.roi_queue_html);
     		process_ocr(returnJSON.pid, "all"); 
     		increase_image_roi_count(OL_loaded_image_pid);
     		update_info_sections();
     		roi_creation_handler.shift();
     		inCall = false;
     		if(roi_creation_handler.length!=0)
     		{
     			run_queue();
     		}
     	},
     	dataType: 'json'
     });
}
            
function insert_in_queue(newROIReqObj)
{
	roi_creation_handler.push(newROIReqObj);
}
            
function run_queue()
{
    if(inCall || roi_creation_handler.length==0)
    	return;
    else
    {
    	inCall = true;
    	create_roi(roi_creation_handler[0]);
    }
}
            
function confirm_yes()
{
    $("#map").fastConfirm('close');
	apr_x = Math.round(feature_temp.geometry.bounds.left);
	apr_w = Math.round(feature_temp.geometry.bounds.right - apr_x);
	apr_y = Math.round(metadata.height - feature_temp.geometry.bounds.top);
	apr_h = Math.round(feature_temp.geometry.bounds.top - feature_temp.geometry.bounds.bottom);
	var idx = addROIBox(feature_temp.geometry.bounds, 0);
	removeROIFeature(feature_temp);
	url = drupal_url+"/apiary/ajaxrequest/create_roi/"+OL_loaded_image_pid+"/"+ apr_y +":" + apr_x + ":" + apr_h + ":" + apr_w + ":" + selected_roi_type;
	var newROIObj = [url, idx]; 
	insert_in_queue(newROIObj);
	run_queue();
	feature_temp = undefined;
	// dialog.dialog('close');
}
			
function confirm_no()
{
    $("#map").fastConfirm('close');
    clear_feature_temp();
}

function clear_feature_temp()
{
    if ( feature_temp != undefined )
    	removeROIFeature(feature_temp);
	feature_temp = undefined;
}

function addROIBox(bounds, pid)
{
    var markerClick = function (evt) {
        var roi_pid = $(this).attr('id'); 
        display_popup(evt,roi_pid);
    };
    /*var feature_polygon = new OpenLayers.Feature.Vector(
        new OpenLayers.Geometry.Polygon(new OpenLayers.Geometry.LinearRing(
        [
            new OpenLayers.Geometry.Point(bounds.left, bounds.top),
            new OpenLayers.Geometry.Point(bounds.right, bounds.top),
            new OpenLayers.Geometry.Point(bounds.right, bounds.bottom),
            new OpenLayers.Geometry.Point(bounds.left, bounds.bottom)
        ]
        )),
        {}
    );*/
	roiBoxes[totalROIs] = new OpenLayers.Marker.Box(bounds);
	//roiBoxes[totalROIs] = feature_polygon;
    roiBoxes[totalROIs].id = pid;
    var col = getColorByType(selected_roi_type);

    roiBoxes[totalROIs].setBorder(col);
    roiBoxes[totalROIs].events.register("click", roiBoxes[totalROIs], markerClick);
    boxes.addMarker(roiBoxes[totalROIs++]);
    //console.log(feature_polygon);
    //polygonLayer.addFeatures([roiBoxes[totalROIs++]]);
    return (totalROIs-1);
}

function display_popup(evt,roi_pid)
{
    var roi_id = roi_pid.replace(':','_');
    
	$("#map").fastConfirm({
		position: "right",
		questionText: "<div class='fastconfirm-select-text' style='text-align:left;'>"+
		"<span id=\"roi_type_selection\">Choose ROI type</span><br/>"+
		"<select id=\"roi_type_select\"><option>Select a new type</option><option>Primary Label</option><option>Annotation</option><option>Barcode</option><option>Undefined</option></select><br/>"+
		"<a href=\"\" onclick=\"delete_this_roi('"+roi_pid+"');return false;\">Delete</a><br/>"+
		"<a href=\"\" onclick=\"transcribe_this_roi('"+roi_pid+"');return false;\">Transcribe</a><br/>"+
		"<a href=\"\" onclick=\"parse_this_roi('"+roi_pid+"');return false;\">Parse</a><br/>"+
		"<span id=\"popup_text\"></span>"+
		"<span id=\""+roi_id+"_roi_type\">"+selected_roi_type+"</span>"+
		"</div>",
		proceedText: "Confirm",
		cancelText: "Cancel",
		onProceed: function(trigger){
			//confirm_yes();
			original_roi_type = $('#'+roi_id+'-type').html()
			//console.log('original roi='+original_roi_type);
			selected_roi_type = $('#roi_type_select option:selected').val();
			if ( selected_roi_type != original_roi_type )
			{
			    requestROITypeChange(roi_pid, roi_id);
			    var color = getColorByType(selected_roi_type);
			    changeROIColorByPid(roi_pid,color);
			}
			//console.log('selected_roi_type='+selected_roi_type);
			$("#map").fastConfirm('close');
		},
		onCancel: function(trigger){
			//confirm_no();
			$("#map").fastConfirm('close');
		},
		onClose: function(trigger){
			//confirm_no();
			$("#map").fastConfirm('close');
		},
		unique:true
	});
	$(".fast_confirm").css("left", evt.clientX + 10);
	$(".fast_confirm").css("top", evt.clientY - 90);

	$('#popup_text').html($(this).attr('id'));
	selected_id = $(this).attr('id');
    OpenLayers.Event.stop(evt);
}

function getColorByType(selected_roi_type)
{
    var col = "#FFCC00";
	switch(selected_roi_type)
	{
		case "Primary Label": col="red";
						break;
	    case "Annotation/Other": col="blue";
	    					break;
	    case "Annotation": col="blue";
	    					break;
	    case "Barcode": col="green";
	    					break;
	    case "Undefined": col="#FFCC00";
	    					break;
	    default: col="red";break;
	}
	return col;
}

function delete_this_roi(pid)
{
    $("#map").fastConfirm('close');
    //alert(pid+" "+selected_id);
    delete_roi(pid);
}

function transcribe_this_roi(pid)
{
    $("#map").fastConfirm('close');
    //alert(pid+" "+selected_id);
    transcribe_roi(pid);
}

function parse_this_roi(pid)
{
    $("#map").fastConfirm('close');
    //alert(pid+" "+selected_id);
    parse_roi(pid);
}

            
function drawAllROIFeatures()
{
	for(var roiBound in roiBoxes)
	{
		drawROIFeature(roiBoxes[roiBound].bounds);
	}
}

function drawROIFeature(bound)
{
	newROIFeature = new OpenLayers.Feature.Vector(bound.toGeometry());
	polygonLayer.addFeatures([newROIFeature]);
	return newROIFeature;
}

function getIndex(bound)
{
	for(roiBound in roiBoxes)
	{
		if(bound.left == roiBoxes[roiBound].bounds.left && bound.right == roiBoxes[roiBound].bounds.right && 
			bound.top == roiBoxes[roiBound].bounds.top && bound.bottom == roiBoxes[roiBound].bounds.bottom)
	    {
				return roiBound;
		}
	}
	// return -1;
}
            				
function updateROIBox(newBound, oldBound)
{
	var pid = roiBoxes[tempIndex].id;
	index = removeROIBox(oldBound);
	addROIBox(newBound, pid);
}
            
function loadImageOL(image_pid)
{
	$("#map").html(" ");
	OL_loaded_image_pid = image_pid;
	$.ajax({
    	url: drupal_url+"/apiary/ajaxrequest/getImageMetadata/"+image_pid+"/rft_id",
     	success: function(returnJSON){
     		metadataURL = drupal_url+"/apiary/ajaxrequest/getImageMetadata/"+image_pid+"/0";
     		$.ajax({
     			url: drupal_url+"/apiary/ajaxrequest/get_roi_boxes/"+image_pid+"/rft_id",
     			success: function(returnBoxes){
     				init(metadataURL, returnJSON.URL, returnBoxes);
     				$(".olControlDrawFeaturePointItemInactive").click();
     				$(".olControlNavigationItemActive").click();
     			},
     			dataType: 'json'
     		});
     	},
     	dataType: 'json'
    });
}

            
function delete_roi(roi_pid)
{
	if(confirm('Are you sure?')){
    		$.ajax({
    			url:drupal_url+"/apiary/ajaxrequest/remove_roi/"+roi_pid+"/0",
    			success: function(returnJS){
    				eval(returnJS);
    				if(!(returnJS.search("success") == -1))
    				{
    					var ele = document.getElementById(roi_pid+"-section");
    					$(ele).remove();
    					removeROIBoxByPid(roi_pid);
    					delete_roi_from_queue(roi_pid);
    					update_info_sections();
    				}
    			}
    		});
	}
}
            
function load_roi_list(img_pid)
{
	$("#step1_east_section").load(drupal_url+"/apiary/ajaxrequest/getImageROIList/"+img_pid+"/0", function(response, status, xhr) {
          if (status != "error") {
            resize_east_pane();
          }
        });
}
            
function get_selected_text(fieldName)
{
	$("#"+fieldName).val(selected_text);
	var accordion_index = get_accordian_id(fieldName);
	if($('#accordion-content-'+accordion_index+':visible').size() == 0 )
		toggle_accordion('accordion-content-'+accordion_index);
	var fieldElement = $("#"+fieldName).parent().parent();
	$('#accordion-content-'+accordion_index).scrollTop(0);
	var fieldPosition = $(fieldElement).position();
	$('#accordion-content-'+accordion_index).scrollTop(fieldPosition.top-116);
}
            
function get_accordian_id(fieldName)
{
	var listItem = $('#jeegoo-'+fieldName).parent();
	var main_menu_id = $(listItem).attr("id");
	return main_menu_id.replace("jeegoo-", "");
}
            
function copy_selected_text()
{
	if (window.getSelection) 
	{
        selected_text = window.getSelection().toString();
	}
	else if (document.getSelection) 
	{
        selected_text = document.getSelection();
	}
	else if (document.selection) 
	{
        selected_text = document.selection.createRange().text;
	}
}
            
function update_queue_list()
{
	$("#ui-layout-west").load(drupal_url+"/apiary/ajaxrequest/update_queue_list/1/0");
}
            
function isImagePid(pid)
{
	if((pid.search("image")== -1))
		return false;
	else
		return true;
}
            
function make_current_specimen(img_pid)
{
	load_roi_list(img_pid);
	loadImageOL(img_pid);
}

/*-----------------Calls------------------------*/
$(document).ready(function () {
	$(document).mousemove(function(e){
		doc_x = e.pageX;
		doc_y = e.pageY;
	});
});