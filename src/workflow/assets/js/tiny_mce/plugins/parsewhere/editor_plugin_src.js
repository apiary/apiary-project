(function() {
	tinymce.create('tinymce.plugins.ParseWherePlugin', {
		init : function(ed, url) {
			var t = this, s, v, o;
			t.editor = ed;
		},
		createControl: function(n, cm) {
			switch (n) {
				case 'parsewherelistbox':
					var t = this, ed = t.editor, each = tinymce.each;
					var pwrlb = cm.createListBox('parsewherelistbox', {
						title : 'Where',
						onselect : function(v) {
							ed.execCommand('assignApiaryElement', false, v);
						}
					});
					
					ed.onInit.add(function() {
						var items = ed.getParam('parsewhere_items');

						if (items) {
							each(items, function(item) {
								var keys = 0;

								each(item, function() {keys++;});

								if (keys > 1)
								{
									pwrlb.add(item.title, item.classes);
								}
							});
						}
					});

					// Add values to the listbox when tinymce init is called by using parsewhere_items

					// Return the new listbox instance
					return pwrlb;
			}
			return null;
		},

		getInfo : function() {
			return {
				longname : 'Parse Where Apiary Project specimenMetadata elements.',
				author : 'Apiary Research Project',
				authorurl : 'http://demo.apiaryproject.org',
				infourl : 'http://demo.apiaryproject.org',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('parsewhere', tinymce.plugins.ParseWherePlugin);
})();