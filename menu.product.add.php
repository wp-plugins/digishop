<?php

$id = !isset($_REQUEST['id']) ? null : $_REQUEST['id'];
$settings_key = $webweb_wp_digishop_obj->get('plugin_settings_key');
$db_prefix = $webweb_wp_digishop_obj->get('plugin_db_prefix');
$msg = '';

if (!empty($_POST)) {
    $data = $_REQUEST[$settings_key];
    
    if (!$webweb_wp_digishop_obj->admin_product($data, $id)) {
        $msg = $webweb_wp_digishop_obj->message('Cannot add/update record. <br/>Errors: <br/>' . $webweb_wp_digishop_obj->get_errors_str());
        $err = 1;
    } else {
        $msg = $webweb_wp_digishop_obj->message('Successfully added/updated record.', 1);
    }

    // preserve data only when updating or error adding
    if (!empty($err) || !empty($id)) {
        $opts = $_REQUEST[$settings_key];
    }
}

if (!empty($id)) {
    $opts = $webweb_wp_digishop_obj->get_product($id);
}

?>

<div class="webweb_wp_plugin">
    <div class="wrap">
        <h2>Add/Edit Product</h2>
        <?php echo $msg; ?>
        
        <form method="post" enctype="multipart/form-data">
            <?php settings_fields($webweb_wp_digishop_obj->get('plugin_dir_name')); ?>

            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Name</th>
                    <td><input type="text" name="<?php echo $settings_key; ?>[label]" value="<?php echo $opts['label']; ?>" class="input_field" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Price (e.g. 29.95, 10)</th>
                    <td><input type="text" name="<?php echo $settings_key; ?>[price]" value="<?php echo $opts['price']; ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">File</th>
                    <td>
                        <input type="file" name="file" value="" />

                        <?php if (!empty($opts['file'])) : ?>
                        <br/><?php echo $opts['file'];?>
                        <?php elseif (!empty($id)) : ?>
                        <span class="app_error">You haven't uploaded a file yet.</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Active</th>
                    <td><input type="checkbox" name="<?php echo $settings_key; ?>[active]" value="1"
                                <?php echo empty($opts['active']) ? '' : 'checked="checked"'; ?> /></td>
                </tr>
            </table>
                        <p>
                            <br/>Notes:<br/> One file per product. If you need more please add them to a ZIP file.
                            <br/>Uploading a new file will override the previous one.
                        </p>
            <p class="submit">
                <input type="submit" class="button-primary" value="<?php _e('Save') ?>" />
            </p>
        </form>
    </div>
</div>