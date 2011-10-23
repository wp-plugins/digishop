<?php
// look up for the path
require_once(dirname(dirname(__FILE__)) . '/digishop.bootstrap.php');

global $wpdb;

// check for rights
if (!is_user_logged_in() || !current_user_can('edit_posts')) {
    wp_die(__("You are not allowed to be here"));
}

$webweb_wp_digishop_obj = WebWeb_WP_DigiShop::get_instance();
$products = $webweb_wp_digishop_obj->get_products();

$dropdown_products = array();

foreach ($products as $rec) {
	if (!empty($rec['active'])) {
		$dropdown_products[$rec['id']] = $rec['label'];
	}
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>DigiShop</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/utils/mctabs.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/utils/form_utils.js"></script>

        <script language="javascript" type="text/javascript">
            function init() {
                tinyMCEPopup.resizeToInnerSize();
            }

            function insert_wwwpdigishop_content() {
                var extra = '';
                var content;
                var template = '<p>[digishop id="%%PRODUCT_ID%%"]</p><br />';
		
				var product_id = document.getElementById('product_id').value;
		
                var wwwpdigishop = document.getElementById('wwwpdigishop_panel');
				
                // who is active ?
                if (wwwpdigishop.className.indexOf('current') != -1) {
                    content = template.replace('%%PRODUCT_ID%%', product_id);
                }
            
                if (window.tinyMCE) {
                    window.tinyMCE.execInstanceCommand('content', 'mceInsertContent', false, content);
                    //Peforms a clean up of the current editor HTML.
                    //tinyMCEPopup.editor.execCommand('mceCleanup');
                    //Repaints the editor. Sometimes the browser has graphic glitches.
                    tinyMCEPopup.editor.execCommand('mceRepaint');
                    tinyMCEPopup.close();
                }
		
                return;
            }
        </script>
        <base target="_self" />
    </head>
    <body id="advimage" onload="tinyMCEPopup.executeOnLoad('init();');document.body.style.display='';document.getElementById('wwwpdigishop_product_name').focus();" style="display: none">
        <form name="wwwpdigishop_form" action="#">
            <div class="tabs">
                <ul>
                    <li id="wwwpdigishop_tab" class="current"><span><a href="javascript:mcTabs.displayTab('wwwpdigishop_tab','wwwpdigishop_panel');" onmousedown="return false;">
                        <?php _e("DigiShop", 'WWWPDIGISHOP'); ?></a></span></li>
                </ul>
            </div>

            <div class="panel_wrapper">
                <!-- panel -->
                <div id="wwwpdigishop_panel" class="panel current">
                    <table border="0" cellpadding="4" cellspacing="0">
						<tr>
                            <td nowrap="nowrap" colspan="2">
                                Please choose the digital product to insert in the current post.<br/>
								Showing only active products.
                            </td>
                        </tr>
                        <tr>
                            <td nowrap="nowrap">
                                <label for="wwwpdigishop_product_name"><?php _e("Product Name", 'WWWPDIGISHOP'); ?></label>
                            </td>
                            <td>
                                <?php 
										//echo "<pre>".var_export($products, 1) . "</pre>";										
										echo WebWeb_WP_DigiShopUtil::html_select('product_id', null, $dropdown_products);
									?>
                            </td>                            
                        </tr>
                       
                    </table>
                </div>
                <!-- end panel -->
            </div>

            <div class="mceActionPanel">
                <div style="float: left">
                    <input type="submit" id="insert" name="insert" value="<?php _e("Insert", 'WWWPDIGISHOP'); ?>" onclick="insert_wwwpdigishop_content();return false;" />
                </div>

                <div style="float: right">
                    <input type="button" id="cancel" name="cancel" value="<?php _e("Cancel", 'WWWPDIGISHOP'); ?>" onclick="tinyMCEPopup.close();" />
                </div>
            </div>
        </form>
    </body>
</html>
<?php
?>