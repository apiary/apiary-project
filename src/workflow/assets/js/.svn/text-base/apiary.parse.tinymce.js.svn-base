function tinymce_map_arrays(arrayKeys, arrayValues)
{
	var tinymce_arrayMap = new Array();
        if (arrayKeys.length)
        {
		for( var i=0; i<arrayKeys.length; i++)
		{
			var tinymce_arrayMap_array = {title : String(arrayValues[i]), classes : String(arrayKeys[i])};
			//tinymce_arrayMap['title'] = String(arrayValues[i]);
			//tinymce_arrayMap['classes'] = String(arrayKeys[i]);
			tinymce_arrayMap.push(tinymce_arrayMap_array);
		}
        }
        return tinymce_arrayMap;
}

function reinit_tinymce(whowhenElements, whatElements, whereElements)
{
	var hasWhowhenElements = false;
	var hasWhatElements = false;
	var hasWhereElements = false;
	if (!$.isEmptyObject(whowhenElements))
	{
		hasWhowhenElements = true;
	}
	if (!$.isEmptyObject(whatElements))
	{
		hasWhatElements = true;
	}
	if (!$.isEmptyObject(whereElements))
	{
		hasWhereElements = true;
	}
	
	if(hasWhowhenElements && hasWhatElements && hasWhereElements)
	{
		//alert("reinit_tinymce_all3");
		reinit_tinymce_all3(whowhenElements, whatElements, whereElements);
	}
	else if(hasWhowhenElements && hasWhatElements && !hasWhereElements)
	{
		//alert("reinit_tinymce_WhowhenWhat");
		reinit_tinymce_WhowhenWhat(whowhenElements, whatElements);
	}
	else if(hasWhowhenElements && !hasWhatElements && hasWhereElements)
	{
		//alert("reinit_tinymce_WhowhenWhere");
		reinit_tinymce_WhowhenWhere(whowhenElements, whereElements);
	}
	else if(hasWhowhenElements && !hasWhatElements && !hasWhereElements)
	{
		//alert("reinit_tinymce_Whowhen");
		reinit_tinymce_Whowhen(whowhenElements);
	}
	else if(!hasWhowhenElements && hasWhatElements && hasWhereElements)
	{
		//alert("reinit_tinymce_WhatWhere");
		reinit_tinymce_WhatWhere(whatElements, whereElements);
	}
	else if(!hasWhowhenElements && hasWhatElements && !hasWhereElements)
	{
		//alert("reinit_tinymce_What");
		reinit_tinymce_What(whatElements);
	}
	else if(!hasWhowhenElements && !hasWhatElements && hasWhereElements)
	{
		//alert("reinit_tinymce_Where");
		reinit_tinymce_Where(whereElements);
	}
	else
	{
		alert("All Whowhen, What and Where elements are empty!");
	}
}

function reinit_tinymce_Whowhen(whowhenElements)
{
	try
	{
		tinyMCE.execCommand('mceRemoveControl',true,'parse_textarea');
	}
	catch(e)
	{
		//alert("Unable to remove Who and When TinyMCE controls");
	}
	try
	{
		tinyMCE.init({
			// General options
			//mode : "textareas",            
			mode : "specific_textareas",
            		editor_selector : "parse_textarea",
			theme : "advanced",
			plugins : "parseapiary,parsewhowhen,parseapiarycontextmenu,fullscreen",
			theme_advanced_buttons3_add : "styleprops",


			// Theme options
			theme_advanced_buttons1 : "parsewhowhenlistbox,code",
			theme_advanced_buttons2 : "",
			theme_advanced_buttons3 : "",
			theme_advanced_buttons4 : "",
			theme_advanced_toolbar_location : "top",
			theme_advanced_toolbar_align : "left",
			theme_advanced_statusbar_location : "bottom",
			theme_advanced_resizing : true,

			parsewhowhen_items : whowhenElements,
			
			force_br_newlines : false,
			force_p_newlines : false,

			content_css : "assets/css/apiary.tinymce.css"
		});
	}
	catch(e)
	{
		alert("Unable to load Who and When TinyMCE");
	}
}

function reinit_tinymce_What(whatElements)
{
	try
	{
		tinyMCE.execCommand('mceRemoveControl',true,'parse_textarea');
	}
	catch(e)
	{
		//alert("Unable to remove What TinyMCE controls");
	}
	try
	{
		tinyMCE.init({
			// General options
			//mode : "textareas",            
			mode : "specific_textareas",
            		editor_selector : "parse_textarea",
			theme : "advanced",
			plugins : "parseapiary,parsewhat,parseapiarycontextmenu,fullscreen",
			theme_advanced_buttons3_add : "styleprops",


			// Theme options
			theme_advanced_buttons1 : "parsewhatlistbox,code",
			theme_advanced_buttons2 : "",
			theme_advanced_buttons3 : "",
			theme_advanced_buttons4 : "",
			theme_advanced_toolbar_location : "top",
			theme_advanced_toolbar_align : "left",
			theme_advanced_statusbar_location : "bottom",
			theme_advanced_resizing : true,

			parsewhat_items : whatElements,
			
			force_br_newlines : false,
			force_p_newlines : false,

			content_css : "assets/css/apiary.tinymce.css"
		});
	}
	catch(e)
	{
		alert("Unable to load What TinyMCE");
	}
}

function reinit_tinymce_all3(whowhenElements, whatElements, whereElements)
{
	try
	{
		tinyMCE.init({
			// General options
			//mode : "textareas",            
			mode : "specific_textareas",
            		editor_selector : "parse_textarea",
			theme : "advanced",
			plugins : "parseapiary,parsewhowhen,parsewhat,parsewhere,parseapiarycontextmenu,fullscreen",
			theme_advanced_buttons3_add : "styleprops",


			// Theme options
			theme_advanced_buttons1 : "parsewhowhenlistbox,parsewhatlistbox,parsewherelistbox,code",
			theme_advanced_buttons2 : "",
			theme_advanced_buttons3 : "",
			theme_advanced_buttons4 : "",
			theme_advanced_toolbar_location : "top",
			theme_advanced_toolbar_align : "left",
			theme_advanced_statusbar_location : "bottom",
			theme_advanced_resizing : true,

			parsewhowhen_items : whowhenElements,

			parsewhat_items : whatElements,

			parsewhere_items : whereElements,
			
			force_br_newlines : false,
			force_p_newlines : false,

			content_css : "assets/css/apiary.tinymce.css"
		});
	}
	catch(e)
	{
		alert("Unable to load Who and When, What and Where TinyMCE");
	}
}