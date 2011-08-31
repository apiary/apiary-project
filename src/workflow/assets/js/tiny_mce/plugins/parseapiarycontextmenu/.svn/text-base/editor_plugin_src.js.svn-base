(function() {
	var Event = tinymce.dom.Event, each = tinymce.each, DOM = tinymce.DOM;

	/**
	 * This plugin is a context menu to TinyMCE editor based on the tinymce.plugins.ContextMenu plugin 
	 * The ContextMenu plugin GetInfo looks like
	 * longname : 'Contextmenu',
	 * author : 'Moxiecode Systems AB',
	 * authorurl : 'http://tinymce.moxiecode.com',
	 * infourl : 'http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/contextmenu',
	 * version : tinymce.majorVersion + "." + tinymce.minorVersion
	 * @class tinymce.plugins.ContextMenu
	 */
	tinymce.create('tinymce.plugins.ParseApiaryContextMenu', {
		init : function(ed) {
			var t = this, showMenu, contextmenuNeverUseNative, realCtrlKey;

			t.editor = ed;

			contextmenuNeverUseNative = ed.settings.contextmenu_never_use_native;

			/**
			 * This event gets fired when the context menu is shown.
			 *
			 * @event onApiaryContextMenu
			 * @param {tinymce.plugins.ApiaryContextMenu} sender Plugin instance sending the event.
			 * @param {tinymce.ui.DropMenu} menu Drop down menu to fill with more items if needed.
			 */
			t.onApiaryContextMenu = new tinymce.util.Dispatcher(this);

			showMenu = ed.onContextMenu.add(function(ed, e) {
				// Block TinyMCE menu on ctrlKey and work around Safari issue
				if ((realCtrlKey !== 0 ? realCtrlKey : e.ctrlKey) && !contextmenuNeverUseNative)
					return;

				Event.cancel(e);

				// Select the image if it's clicked. WebKit would other wise expand the selection
				if (e.target.nodeName == 'IMG')
					ed.selection.select(e.target);

				t._getMenu(ed).showMenu(e.clientX || e.pageX, e.clientY || e.pageX);
				Event.add(ed.getDoc(), 'click', function(e) {
					hide(ed, e);
				});

				ed.nodeChanged();
			});

			ed.onRemove.add(function() {
				if (t._menu)
					t._menu.removeAll();
			});

			function hide(ed, e) {
				realCtrlKey = 0;

				// Since the contextmenu event moves
				// the selection we need to store it away
				if (e && e.button == 2) {
					realCtrlKey = e.ctrlKey;
					return;
				}

				if (t._menu) {
					t._menu.removeAll();
					t._menu.destroy();
					Event.remove(ed.getDoc(), 'click', hide);
				}
			};

			ed.onMouseDown.add(hide);
			ed.onKeyDown.add(hide);
			ed.onKeyDown.add(function(ed, e) {
				if (e.shiftKey && !e.ctrlKey && !e.altKey && e.keyCode === 121) {
					Event.cancel(e);
					showMenu(ed, e);
				}
			});
		},

		getInfo : function() {
			return {
				longname : 'Apiary Project Parse ContextMenu.',
				author : 'Apiary Research Project',
				authorurl : 'http://demo.apiaryproject.org',
				infourl : 'http://demo.apiaryproject.org',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		},

		_getMenu : function(ed) {
			var t = this, m = t._menu, se = ed.selection, col = se.isCollapsed(), el = se.getNode() || ed.getBody(), each = tinymce.each, pww, pwa, pwe, p1, p2;
			var found_first_parse_list = false;
			var content = tinyMCE.activeEditor.selection.getContent({format : 'text'});
			var contentTrim = content.trim();
			var content_length = contentTrim.length;

			if (m) {
				m.removeAll();
				m.destroy();
			}

			p1 = DOM.getPos(ed.getContentAreaContainer());
			p2 = DOM.getPos(ed.getContainer());

			m = ed.controlManager.createDropMenu('parseapiarycontextmenu', {
				offset_x : p1.x + ed.getParam('contextmenu_offset_x', 0),
				offset_y : p1.y + ed.getParam('contextmenu_offset_y', 0),
				constrain : 1,
				keyboard_focus: true
			});

			t._menu = m;
			
			var parents = ed.dom.getParents(el, 'span');
			var remove_items = [];
			var parsewhowhen_items = ed.getParam('parsewhowhen_items');
			if (parsewhowhen_items)
			{
				if(content_length > 0)
				{
					if(found_first_parse_list)
					{
						m.addSeparator();
					}
					else
					{
						found_first_parse_list = true;
					}
					pww = m.addMenu({title : 'Who/When'});
				}

				each(parsewhowhen_items, function(item) {
					var hasMatch = false;
					if(ed.dom.hasClass(el, item.classes))
					{
						remove_items.push({title:item.title, classes:item.classes});
						hasMatch = true;
					}
					if(!hasMatch)
					{
						each(parents, function(parent) {
							if(ed.dom.hasClass(parent, item.classes))
							{
								remove_items.push({title:item.title, classes:item.classes});
								hasMatch = true;
							}
						});
					}
					if(content_length > 0)
					{
						if(hasMatch)
						{
							pww.add({title : item.title+" -Remove", icon : '', onclick : function(){ed.execCommand('removeApiaryElement', false, item.classes);}});
						}
						else
						{
							pww.add({title : item.title, icon : '', onclick : function(){ed.execCommand('assignApiaryElement', false, item.classes);}});
						}
					}
				});
			}
			
			var parsewhat_items = ed.getParam('parsewhat_items');
			if (parsewhat_items)
			{
				if(content_length > 0)
				{
					if(found_first_parse_list)
					{
						m.addSeparator();
					}
					else
					{
						found_first_parse_list = true;
					}
					pwa = m.addMenu({title : 'What'});
				}

				each(parsewhat_items, function(item) {
					var hasMatch = false;
					if(ed.dom.hasClass(el, item.classes))
					{
						remove_items.push({title:item.title, classes:item.classes});
						hasMatch = true;
					}
					if(!hasMatch)
					{
						each(parents, function(parent) {
							if(ed.dom.hasClass(parent, item.classes))
							{
								remove_items.push({title:item.title, classes:item.classes});
								hasMatch = true;
							}
						});
					}
					if(content_length > 0)
					{
						if(hasMatch)
						{
							pwa.add({title : item.title+" -Remove", icon : '', onclick : function(){ed.execCommand('removeApiaryElement', false, item.classes);}});
						}
						else
						{
							pwa.add({title : item.title, icon : '', onclick : function(){ed.execCommand('assignApiaryElement', false, item.classes);}});
						}
					}
				});
			}
			
			var parsewhere_items = ed.getParam('parsewhere_items');
			if (parsewhere_items)
			{
				if(content_length > 0)
				{
					if(found_first_parse_list)
					{
						m.addSeparator();
					}
					else
					{
						found_first_parse_list = true;
					}
					pwe = m.addMenu({title : 'Where'});
				}

				each(parsewhere_items, function(item) {
					var hasMatch = false;
					if(ed.dom.hasClass(el, item.classes))
					{
						remove_items.push({title:item.title, classes:item.classes});
						hasMatch = true;
					}
					if(!hasMatch)
					{
						each(parents, function(parent) {
							if(ed.dom.hasClass(parent, item.classes))
							{
								remove_items.push({title:item.title, classes:item.classes});
								hasMatch = true;
							}
						});
					}
					if(content_length > 0)
					{
						if(hasMatch)
						{
							pwe.add({title : item.title+" -Remove", icon : '', onclick : function(){ed.execCommand('removeApiaryElement', false, item.classes);}});
						}
						else
						{
							pwe.add({title : item.title, icon : '', onclick : function(){ed.execCommand('assignApiaryElement', false, item.classes);}});
						}
					}
				});
			}

			if (remove_items.length > 0)
			{
				if(found_first_parse_list)
				{
					m.addSeparator();
				}
				else
				{
					found_first_parse_list = true;
				}
				pwe = m.addMenu({title : 'Remove'});

				each(remove_items, function(item) {
					pwe.add({title : item.title+" -Remove", icon : '', onclick : function(){ed.execCommand('removeApiaryElement', false, item.classes);}});
					
				});
			}
			
			t.onApiaryContextMenu.dispatch(t, m, el, col);

			return m;
		}
	});

	// Register plugin
	tinymce.PluginManager.add('parseapiarycontextmenu', tinymce.plugins.ParseApiaryContextMenu);
})();
