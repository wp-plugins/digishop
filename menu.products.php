<?php

$del_status = 0;

if ($_REQUEST['do'] == 'delete' && is_admin()) {
    $del_status = $webweb_wp_digishop_obj->delete_product($_REQUEST['id']);
}

$options = $webweb_wp_digishop_obj->get_options();
$data = $webweb_wp_digishop_obj->get_products();

$delete_url = $webweb_wp_digishop_obj->get('delete_product_url');
$edit_url   = $webweb_wp_digishop_obj->get('edit_product_url');

$adm_prefix = $webweb_wp_digishop_obj->get('plugin_url');
$plugin_uploads_dir = $webweb_wp_digishop_obj->get('plugin_uploads_dir');

$inactive_product = " <img src='$adm_prefix/images/product_inactive.png' title='' alt='' /> " . $webweb_wp_digishop_obj->m('Inactive');
$active_product = " <img src='$adm_prefix/images/product_active.png' title='' alt='' /> " . $webweb_wp_digishop_obj->m('Active', 1);
?>

<div class="webweb_wp_plugin">
    <div class="wrap">
        <h2>Products</h2>

        <p>The list of products you currently have. Copy the short code into the post where you'd like the buy now button to appear.</p>

        <div class="wrap" id="app-partners-container">
            <table class="widefat fixed app_table_half">
                <thead>
                    <tr>
                        <th scope="col">Short Code</th>
                        <th scope="col">Name</th>
                        <th scope="col">Price (<?php echo $options['currency']; ?>)</th>
                        <th scope="col">Actions</th>
                        <th scope="col">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data)) : ?>
                        <tr>
                            <td colspan="3">No records found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data as $idx => $rec) : ?>
                        <tr>
                            <td><?php echo "[" . $webweb_wp_digishop_obj->get('plugin_id_str') . " id=\"{$rec['id']}\"]"?>

                                <?php if (!empty($rec['file']) && !empty($rec['hash'])) : ?>
                                    <small>
                                        <a href="javascript:void(0);" onclick="jQuery('#download_link_container_<?php echo $rec['id']; ?>').toggle();">show/hide download link</a>
                                        <div id="download_link_container_<?php echo $rec['id']; ?>" class="download_link app_hide">
                                            <input type="text" value="<?php echo
                                                WebWeb_WP_DigiShopUtil::add_url_params($webweb_wp_digishop_obj->get('site_url'),
                                                        array($webweb_wp_digishop_obj->get('download_key') => $rec['hash']));?>" onclick="this.select();" />
                                        </div>
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $rec['label'];

                            if (!empty($rec['file'])) {
                                if (file_exists($plugin_uploads_dir . $rec['file'])) {
                                    echo " <img src='$adm_prefix/images/attach.png' title='The product has a file linked to it.' alt='' />";
                                } else {
                                    echo " <img src='$adm_prefix/images/error.png' title='The product has a file linked to it but the file cannot be found.' alt='' />";
                                }
                            }
                            
                            ?></td>
                            <td><?php echo $rec['price']?></td>
                            <td>
                                <a class="app_edit_button" href="<?php echo WebWeb_WP_DigiShopUtil::add_url_params($edit_url, array('id' => $rec['id']));?>">Edit</a>
        |
                                <a class="app_delete_button" onclick="return confirm('Are you sure?');"
                                   href="<?php echo WebWeb_WP_DigiShopUtil::add_url_params($delete_url, array('id' => $rec['id']));?>">Delete</a>
                            </td>
                            <td><?php echo empty($rec['active']) ? $inactive_product : $active_product;?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>


            <p>Note: If a product is inactive its Buy now button will not be shown and it won't be allowed for download even with the correct download link.</p>
        </div>
    </div>
</div>