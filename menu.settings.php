<?php
$settings_key = $webweb_wp_digishop_obj->get('plugin_settings_key');
$opts = $webweb_wp_digishop_obj->get_options();

?>
<div class="webweb_wp_plugin">
    <div class="wrap">
        <h2>Settings</h2>

        <form method="post" action="options.php">
            <?php settings_fields($webweb_wp_digishop_obj->get('plugin_dir_name')); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Status</th>
                    <td>
                        <label for="radio1"> 
                            <input type="radio" id="radio1" name="<?php echo $settings_key; ?>[status]"
                                value="1" <?php echo empty($opts['status']) ? '' : 'checked="checked"'; ?> /> Enabled
                        </label>
                        <br/>
                        <label for="radio2">
                            <input type="radio" name="<?php echo $settings_key; ?>[status]"  id="radio2"
                                value="0" <?php echo!empty($opts['status']) ? '' : 'checked="checked"'; ?> /> Disabled
                        </label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">PayPal Email</th>
                    <td><input type="text" name="<?php echo $settings_key; ?>[business_email]" value="<?php echo $opts['business_email']; ?>" class="input_field" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Subject (download email)</th>
                    <td><input type="text" name="<?php echo $settings_key; ?>[purchase_subject]" value="<?php echo $opts['purchase_subject']; ?>" class="input_field"/></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Content (download email)</th>
                    <td><textarea name="<?php echo $settings_key; ?>[purchase_content]"><?php echo $opts['purchase_content']; ?></textarea>

                    <br/>
                    <h4>Supported Variables <a href="javascript:void(0);" onclick="jQuery('.suppored_vars').toggle('slow');return false;">(show/hide)</a></h4>
                        <ul class="suppored_vars app_hide">
                            <li>%%SITE%%</li>
                            <li>%%FIRST_NAME%% - Payer's first name</li>
                            <li>%%LAST_NAME%% - Payer's last name</li>
                            <li>%%EMAIL%% - Payer's email</li>
                            <li>%%TXN_ID%% - Transaction ID (PayPal)</li>
                            <li>%%PRODUCT_NAME%% - Product name</li>
                            <li>%%PRODUCT_PRICE%% - Product price</li>
                            <li>%%DOWNLOAD_LINK%% - Download link</li>
                        </ul>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Thank You message (after a successful payment)</th>
                    <!--<td><textarea name="<?php echo $settings_key; ?>[purchase_thanks]"><?php echo $opts['purchase_thanks']; ?></textarea></td>-->
                    <td><input type="text" name="<?php echo $settings_key; ?>[purchase_thanks]" value="<?php echo $opts['purchase_thanks']; ?>" class="input_field"/></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Error message (after a failed payment)</th>
                    <!--<td><textarea name="<?php echo $settings_key; ?>[purchase_error]"><?php echo $opts['purchase_error']; ?></textarea></td>-->
                    <td><input type="text" name="<?php echo $settings_key; ?>[purchase_error]" value="<?php echo $opts['purchase_error']; ?>" class="input_field"/></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Currency</th>
                    <td><input type="text" name="<?php echo $settings_key; ?>[currency]" value="<?php echo $opts['currency']; ?>" /> (Example: USD, CAD, EUR)</td>
                </tr>
                <tr valign="top">
                    <th scope="row">Sandbox (no real transactions)</th>
                    <td><input type="checkbox" name="<?php echo $settings_key; ?>[test_mode]" value="1"
                                <?php echo empty($opts['test_mode']) ? '' : 'checked="checked"'; ?> />								
					</td>
                </tr>
            </table>
					
					<p>Note: Keep in mind when the sandbox is enabled do make sure you are using the paypal email generated from 
							<a href="http://developer.paypal.com" target="_blank">developer.paypal.com</a> otherwise transactions will fail.
					</p>
					
            <p class="submit">
                <input type="submit" class="button-primary" value="<?php _e('Save') ?>" />
            </p>
        </form>
    </div>
</div>