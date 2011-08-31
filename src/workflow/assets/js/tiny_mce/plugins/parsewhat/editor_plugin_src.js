(function() {
	tinymce.create('tinymce.plugins.ParseWhatPlugin', {
		init : function(ed, url) {
			var t = this, s, v, o;
			t.editor = ed;
		},
		createControl: function(n, cm) {
			switch (n) {
				case 'parsewhatlistbox':
					var t = this, ed = t.editor, each = tinymce.each;
					var pwlb = cm.createListBox('parsewhatlistbox', {
						title : 'What',
						onselect : function(v) {
							ed.execCommand('assignApiaryElement', false, v);
						}
					});
					
					ed.onInit.add(function() {
						var items = ed.getParam('parsewhat_items');

						if (items) {
							each(items, function(item) {
								var keys = 0;

								each(item, function() {keys++;});

								if (keys > 1) 
								{
									pwlb.add(item.title, item.classes);
								}
							});
						}
					});

					// Add values to the listbox when tinymce init is called by using parsewhat_items

					// Return the new listbox instance
					return pwlb;
			}
			return null;
		},

		getInfo : function() {
			return {
				longname : 'Parse What Apiary Project specimenMetadata elements.',
				author : 'Apiary Research Project',
				authorurl : 'http://demo.apiaryproject.org',
				infourl : 'http://demo.apiaryproject.org',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('parsewhat', tinymce.plugins.ParseWhatPlugin);
})();