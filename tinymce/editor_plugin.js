// Docu : http://wiki.moxiecode.com/index.php/TinyMCE:Create_plugin/3.x#Creating_your_own_plugins

(function() {
	// Load plugin specific language pack
	tinymce.PluginManager.requireLangPack('wwwpdigishop');

	tinymce.create('tinymce.plugins.wwwpdigishop', {
		/**
		 * Initializes the plugin, this will be executed after the plugin has been created.
		 * This call is done before the editor instance has finished it's initialization so use the onInit event
		 * of the editor instance to intercept that event.
		 *
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */
		init : function(ed, url) {
			// Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('mceExample');

			ed.addCommand('mcewwwpdigishop', function() {
				ed.windowManager.open({
					file : url + '/window.php',
					width : 600 + ed.getLang('wwwpdigishop.delta_width', 0),
					height : 400 + ed.getLang('wwwpdigishop.delta_height', 0),
					inline : 1
				}, {
					plugin_url : url // Plugin absolute URL
				});
			});

			// Register example button
			ed.addButton('wwwpdigishop', {
				title : 'DigiShop',
				cmd : 'mcewwwpdigishop',
				image : url + '/../images/icon.png'
			});

			// Add a node change handler, selects the button in the UI when a image is selected
			ed.onNodeChange.add(function(ed, cm, n) {
				cm.setActive('wwwpdigishop', n.nodeName == 'IMG');
			});
		},

		/**
		 * Returns information about the plugin as a name/value array.
		 * The current keys are longname, author, authorurl, infourl and version.
		 *
		 * @return {Object} Name/value array containing information about the plugin.
		 */
		getInfo : function() {
			return {
					longname  : 'DigiShop',
					author 	  : 'Svetoslav Marinov (Slavi)',
					authorurl : 'http://www.WebWeb.ca',
					infourl   : 'http://www.WebWeb.ca/site/products/digishop',
					version   : "1.0.0"
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('wwwpdigishop', tinymce.plugins.wwwpdigishop);
})();