(function() {
	tinymce.create('tinymce.plugins.ParseApiaryPlugin', {
		init : function(ed, url) {
			var t = this;
			t.editor = ed;
			t.parseapiary_element = '';
			t.parseapiary_list = '';
			ed.addCommand('assignApiaryElement', function(ui, v) {
				t.parseapiary_element = v;
				t.assignApiaryElement();
			});

			ed.onKeyDown.add(function(ed, e) {
				if (e.keyCode === 67 || e.keyCode === 99)
				{
					//C=67,c=99
					var apiary_element = 'recordedBy';
					var hasElement = false;
					var parse_items = ed.getParam('parsewhowhen_items');
					if (parse_items) {
						tinymce.each(parse_items, function(item) {
							var keys = 0;

							tinymce.each(item, function() {
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
					if(hasElement)
					{
						t.parseapiary_element = apiary_element;
						t.assignApiaryElement();
					}
				}
				else if((e.keyCode === 68) || (e.keyCode === 100))
				{
					//D=68,d=100
					var parse_items = ed.getParam('parsewhowhen_items');
					if(parse_items)
					{
						t.parseapiary_element = 'verbatimEventDate';
						t.assignApiaryElement();
					}
				}
				else if (e.keyCode === 72 || e.keyCode === 104)
				{
					//H=72,h=104
					t.parseapiary_element = 'collectionCode';
					t.assignApiaryElement();
				}
				return tinyMCE.dom.Event.cancel(e);
			});
		},
		
		hasApiaryElement : function() {
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
		
		assignApiaryElement : function() {
			var t = this, apiary_element = t.parseapiary_element;
			var content = tinyMCE.activeEditor.selection.getContent();
			var content_length = content.length;
			if(content_length > 0 && apiary_element.length > 0)
			{
				var contentTrim = content.trim();
				var start_index = content.indexOf(contentTrim);
				var contentTrim_length = contentTrim.length;
				var stop_index = start_index + contentTrim_length;
				var newContent = content.substring(0, start_index) + '<span class="'+apiary_element+'">' + content.substring(start_index, stop_index) + '</span>' + content.substring(stop_index, content_length);
				tinyMCE.activeEditor.selection.setContent(newContent);
				tinyMCE.triggerSave();
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