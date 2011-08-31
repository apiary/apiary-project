(function() {
	tinymce.create('tinymce.plugins.ParseApiaryPlugin', {
		init : function(ed, url) {
			var t = this;
			t.editor = ed;
			t.parseapiary_element = '';
			t.parseapiary_list = '';
			ed.addCommand('hasApiaryElement', function(ui, v) {
				t.parseapiary_element = v;
				return t.hasApiaryElement();
			});
			ed.addCommand('assignApiaryElement', function(ui, v) {
				t.parseapiary_element = v;
				t.assignApiaryElement();
			});
			ed.addCommand('removeApiaryElement', function(ui, v) {
				t.parseapiary_element = v;
				t.removeApiaryElement();
			});
			ed.addCommand('populateParseTreeview', function(ui, v) {
				alert("Here");
				each = tinymce.each;
				var content = tinymce.get('parse_textarea').getContent();
				var contentTrim = content.trim();
				var content_length = contentTrim.length; //prevents assigning an element to a space
				alert("Here 2 contentTrim = "+contentTrim);
				if(content_length > 0)
				{
					var spanElems = ed.dom.select('span');
					each(spanElems, function(spanElem) {
						var spanNode = spanElem.getNode();
						content = spanNode.innerHTML;
						contentTrim = content.trim();
						var apiary_element = ed.dom.getAttrib(spanElem, 'class');
						if(typeof addSpecimenMetadataToParseTreeView == "function")
						{
							addSpecimenMetadataToParseTreeView(apiary_element, contentTrim);
						}
					});
				}
			});

			ed.onKeyDown.add(function(ed, e) {
				if (e.keyCode === 67 || e.keyCode === 99)
				{
					//C=67,c=99
					t.parseapiary_list = 'parsewhowhen_items';
					t.parseapiary_element = 'recordedBy';
					if(t.hasApiaryElementInList())
					{
						t.assignApiaryElement();
					}
				}
				else if(e.keyCode === 68 || e.keyCode === 100)
				{
					//D=68,d=100
					t.parseapiary_list = 'parsewhowhen_items';
					t.parseapiary_element = 'verbatimEventDate';
					if(t.hasApiaryElementInList())
					{
						t.assignApiaryElement();
					}
				}
				else if (e.keyCode === 72 || e.keyCode === 104)
				{
					//H=72,h=104
					t.parseapiary_list = 'parsewhat_items';
					t.parseapiary_element = 'collectionCode';
					if(t.hasApiaryElementInList())
					{
						t.assignApiaryElement();
					}
				}
				return tinyMCE.dom.Event.cancel(e);
			});
		},
		
		hasApiaryElementInList : function() {
			var t = this, ed = t.editor, each = tinymce.each, apiary_element = t.parseapiary_element, apiary_list = t.parseapiary_list;
			var hasElement = false;
			var parse_items = ed.getParam(apiary_list);
			if (parse_items)
			{
				each(parse_items, function(item)
				{
					var keys = 0;
					each(item, function() {
						keys++;
					});
					if (keys > 1)
					{
						if(item.classes == apiary_element)
						{
							hasElement = true;
						}
					}
				});
			}
			return hasElement;
		},

		hasApiaryElement : function() {
			var t = this, ed = t.editor, each = tinymce.each, apiary_element = t.parseapiary_element, se = ed.selection, el = se.getNode();
			if(apiary_element.length > 0)
			{
				var content = tinyMCE.activeEditor.selection.getContent();
				if(ed.dom.hasClass(el, apiary_element))
				{
					t.parseapiary_element = '';
					return true;
				}
				else
				{
					var parents = ed.dom.getParents(el, 'span');
					each(parents, function(parent) {
						if(ed.dom.hasClass(parent, apiary_element))
						{
							t.parseapiary_element = '';//this has to be set to blank before returning
							return true;//go ahead and break it here with the return
						}
					});
				}
				
			}
			t.parseapiary_element = '';
			return false;
		},
		
		assignApiaryElement : function() {
			var t = this, apiary_element = t.parseapiary_element;
			//var content = tinyMCE.activeEditor.selection.getContent();
			//var contentTrim = content.trim();
			//var contentTrim = contentTrim.replace(/<\/[\S]+>/, "");//removes </span>
			//var contentTrim = contentTrim.replace(/<\s*\w.*?>/g, "");//removes <span class=...>
			var content = tinyMCE.activeEditor.selection.getContent();
			var content_text = tinyMCE.activeEditor.selection.getContent({format : 'text'});//no html tags
			var contentTrim = content_text.trim();
			var content_length = contentTrim.length; //prevents assigning an element to a space
			if(content_length > 0 && apiary_element.length > 0)
			{
				//var start_index = content.indexOf(contentTrim);
				//var contentTrim_length = contentTrim.length;
				//var stop_index = start_index + contentTrim_length;
				//var newContent = content.substring(0, start_index) + '<span class="'+apiary_element+'">' + content.substring(start_index, stop_index) + '</span>' + content.substring(stop_index, content_length);
				if(!t.hasApiaryElement())
				{
					var newContent = '<span class="'+apiary_element+'">' + content + '</span>';
					tinyMCE.activeEditor.selection.setContent(newContent);
					tinyMCE.triggerSave();
					if(typeof addSpecimenMetadataToParseTreeView == "function")
					{
						addSpecimenMetadataToParseTreeView(apiary_element, contentTrim);
					}
				}
			}
			t.parseapiary_element = '';
		},

		removeApiaryElement : function() {
			var t = this, ed = t.editor, each = tinymce.each, apiary_element = t.parseapiary_element, se = ed.selection, el = se.getNode();
			if(apiary_element.length > 0)
			{
				var content = tinyMCE.activeEditor.selection.getContent({format : 'text'});
				var contentTrim = content.trim();
				var content_length = contentTrim.length;
				if(content_length == 0)
				{
					content = el.innerHTML;
					contentTrim = content.trim();
				}
				if(ed.dom.hasClass(el, apiary_element))
				{
					tinyMCE.DOM.remove(el, true);
					if(typeof removeSpecimenMetadataToParseTreeView == "function")
					{
						removeSpecimenMetadataToParseTreeView(apiary_element, contentTrim);
					}
				}
				else
				{
					var parents = ed.dom.getParents(el, 'span');
					each(parents, function(parent) {
						if(ed.dom.hasClass(parent, apiary_element))
						{
							tinyMCE.DOM.remove(parent, true);
							if(typeof removeSpecimenMetadataToParseTreeView == "function")
							{
								removeSpecimenMetadataToParseTreeView(apiary_element, contentTrim);
							}
						}
					});
				}
				
			}
			t.parseapiary_element = '';
		},

		getInfo : function() {
			return {
				longname : 'Parse functions for Apiary Project specimenMetadata elements.',
				author : 'Apiary Research Project',
				authorurl : 'http://demo.apiaryproject.org',
				infourl : 'http://demo.apiaryproject.org',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('parseapiary', tinymce.plugins.ParseApiaryPlugin);
})();