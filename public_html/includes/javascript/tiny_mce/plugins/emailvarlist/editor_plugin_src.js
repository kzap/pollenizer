/**
 * editor_plugin_src.js
 *
 * Copyright 2009, Moxiecode Systems AB
 * Released under LGPL License.
 *
 * License: http://tinymce.moxiecode.com/license
 * Contributing: http://tinymce.moxiecode.com/contributing
 */

(function() {
	tinymce.create('tinymce.plugins.EmailVarList', {
		/**
		 * Initializes the plugin, this will be executed after the plugin has been created.
		 * This call is done before the editor instance has finished it's initialization so use the onInit event
		 * of the editor instance to intercept that event.
		 *
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */
		init : function(ed, url) {
		},

		/**
		 * Creates control instances based in the incomming name. This method is normally not
		 * needed since the addButton method of the tinymce.Editor class is a more easy way of adding buttons
		 * but you sometimes need to create more complex controls like listboxes, split buttons etc then this
		 * method can be used to create those.
		 *
		 * @param {String} n Name of the control to create.
		 * @param {tinymce.ControlManager} cm Control manager to use inorder to create new control.
		 * @return {tinymce.ui.Control} New control instance or null if no control was created.
		 */
		createControl : function(n, cm) {
			switch (n) {
				case "emailvarlist":
					var mlb = cm.createListBox("mylistbox", {
						title : "Email Vars List",
						onselect : function(v) {
							if (v) {
								tinyMCE.activeEditor.execCommand("mceInsertContent", false, v);
							}
                    	}
               		});
					mlb.add("--Member Variables--", "");
       		        mlb.add("First Name", "{members_firstname}");
               		mlb.add("Last Name", "{members_lastname}");
	                mlb.add("Email", "{members_email}");
					mlb.add("Address 1", "{members_address01}");
					mlb.add("Address 2", "{members_address02}");
					mlb.add("City", "{members_city}");
					mlb.add("State", "{members_state}");
					mlb.add("Country", "{members_country}");
					mlb.add("ZIP/Post Code", "{members_zip_code}");
					mlb.add("Phone #", "{members_phone}");
	                return mlb;
	        }
			return null;
		},

		/**
		 * Returns information about the plugin as a name/value array.
		 * The current keys are longname, author, authorurl, infourl and version.
		 *
		 * @return {Object} Name/value array containing information about the plugin.
		 */
		getInfo : function() {
			return {
				longname : 'Example plugin',
				author : 'Some author',
				authorurl : 'http://tinymce.moxiecode.com',
				infourl : 'http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/example',
				version : "1.0"
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('emailvarlist', tinymce.plugins.EmailVarList);
})();