(function() {
	tinymce.create('tinymce.plugins.TranscribeApiaryPlugin', {
		init : function(ed, url) {
			var t = this;
			t.editor = ed;

			ed.onKeyDown.add(function(ed, e) {
				if (e.keyCode === 13)
				{
					//enter=13
					var newContent = '<br />';
					tinyMCE.activeEditor.selection.setContent(newContent);
					tinyMCE.triggerSave();
					return tinyMCE.dom.Event.cancel(e);
				}
			});
		},

		getInfo : function() {
			return {
				longname : 'Transcribe functions for Apiary Project specimenMetadata elements.',
				author : 'Apiary Research Project',
				authorurl : 'http://demo.apiaryproject.org',
				infourl : 'http://demo.apiaryproject.org',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('transcribeapiary', tinymce.plugins.TranscribeApiaryPlugin);
})();