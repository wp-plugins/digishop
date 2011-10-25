<?php

$del_status = 0;

if ($_REQUEST['do'] == 'delete' && is_admin()) {
    $del_status = $webweb_wp_digishop_obj->delete_product($_REQUEST['id']);
}

$options = $webweb_wp_digishop_obj->get_options();
$data = $webweb_wp_digishop_obj->get_products();

$delete_url = $webweb_wp_digishop_obj->get('delete_product_url');
$edit_url   = $webweb_wp_digishop_obj->get('edit_product_url');

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
                            <td><?php echo "[" . $webweb_wp_digishop_obj->get('plugin_id_str') . " id=\"{$rec['id']}\"]"?></td>
                            <td><?php echo $rec['label']?></td>
                            <td><?php echo $rec['price']?></td>
                            <td>
                                <a class="app_edit_button" href="<?php echo WebWeb_WP_DigiShopUtil::add_url_params($edit_url, array('id' => $rec['id']));?>">Edit</a>
        |
                                <a class="app_delete_button" onclick="return confirm('Are you sure?');"
                                   href="<?php echo WebWeb_WP_DigiShopUtil::add_url_params($delete_url, array('id' => $rec['id']));?>">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>