<?php
$settings_key = $webweb_wp_digishop_obj->get('plugin_settings_key');
$opts = $webweb_wp_digishop_obj->get_options();

?>
<div class="webweb_wp_digishop">
    <div class="wrap">
        <div class="main_content">
            <h2>Settings</h2>

            <form method="post" action="options.php">
                <?php settings_fields($webweb_wp_digishop_obj->get('plugin_dir_name')); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row" colspan="2"><h2>General</h2></th>
                    </tr>
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
                        <th scope="row">Order Notification Email</th>
                        <td><input type="text" name="<?php echo $settings_key; ?>[notification_email]" value="<?php echo $opts['notification_email']; ?>" class="input_field" />
                            This email will CC or BCC'ed with the email sent to the customer.
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Subject (download email)</th>
                        <td><input type="text" name="<?php echo $settings_key; ?>[purchase_subject]" value="<?php echo $opts['purchase_subject']; ?>" class="input_field"/></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Content (download email)</th>
                        <td><textarea name="<?php echo $settings_key; ?>[purchase_content]"><?php echo $opts['purchase_content']; ?></textarea>

                            <div style="float:right">
                                <strong>Supported Variables <a href="javascript:void(0);" onclick="jQuery('.suppored_vars').toggle('slow');return false;">(show/hide)</a></strong>
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
                            </div>
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
                        <td><input type="text" name="<?php echo $settings_key; ?>[currency]" value="<?php echo $opts['currency']; ?>" /> Example: USD, CAD, EUR
                            <a href="https://cms.paypal.com/ca/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_api_nvp_currency_codes" target="_blank">See full list</a>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Submit the form in a new window</th>
                        <td>
                            <label for="digishop_form_new_window">
                                    <input type="checkbox" id="digishop_form_new_window" name="<?php echo $settings_key; ?>[form_new_window]" value="1"
                                        <?php echo empty($opts['form_new_window']) ? '' : 'checked="checked"'; ?> /> Enable form submission in a new window</label>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Require buyer's shipping address (on PayPal's site)</th>
                        <td>
                            <label for="digishop_require_shipping">
                                    <input type="checkbox" id="digishop_require_shipping" name="<?php echo $settings_key; ?>[require_shipping]" value="1"
                                        <?php echo empty($opts['require_shipping']) ? '' : 'checked="checked"'; ?> /> Enable</label>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" colspan="2"><h2>Advanced (<a href="javascript:void(0);" onclick="jQuery('.digishop_advanced_options').toggle('slow');return false;">show/hide</a>)
                        </h2>
                        </th>
                    </tr>
                    </table>
                    <table class="digishop_advanced_options app_hide">
                    <tr valign="top">
                        <th scope="row">Sandbox (no real transactions)</th>
                        <td>
                            <label for="digishop_sandbox_mode">
                                    <input type="checkbox" id="digishop_sandbox_mode" name="<?php echo $settings_key; ?>[test_mode]" value="1"
                                        <?php echo empty($opts['test_mode']) ? '' : 'checked="checked"'; ?> /> Enable Sandbox</label>

                            <p>If the sandbox mode is enabled please use the test accounts generated from
                                    <a href="http://developer.paypal.com" target="_blank">developer.paypal.com</a> otherwise transactions will fail.
                            </p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Sandbox IP Address</th>
                        <td>
                            <input type="text" id="sandbox_only_ip" name="<?php echo $settings_key; ?>[sandbox_only_ip]"
                                    value="<?php echo $opts['sandbox_only_ip']; ?>" class="input_field" />
                            Your IP: <?php echo $_SERVER['REMOTE_ADDR']; ?> (<a href="javascript:void(0);" onclick="jQuery('#sandbox_only_ip').val('<?php echo $_SERVER['REMOTE_ADDR']; ?>');"
                                                                                    title="This will use your current IP address as sandbox IP address.">Use</a>)
                            <p>If the sandbox is enabled and you have entered IP in the box the sandbox will be enabled only for that specific IP address. <br/>
                                Is it made for testing live installation of DigiShop.
                            </p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Sandbox PayPal Email</th>
                        <td><input type="text" name="<?php echo $settings_key; ?>[sandbox_business_email]" value="<?php echo $opts['sandbox_business_email']; ?>" class="input_field" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Submit Button Image Source
                                <br/>(optional)
                        </th>
                        <td><input type="text" name="<?php echo $settings_key; ?>[submit_button_img_src]" value="<?php echo $opts['submit_button_img_src']; ?>" class="input_field" />
                            Example: http://domain.com/image.jpg ,
                            <?php
                            if (!empty($opts['submit_button_img_src'])) {
                                echo <<<EOF
    <br/> <span style="vertical-align:middle;">Preview: <img src="{$opts['submit_button_img_src']}" alt="" /></span>
EOF;
                            }
                            ?>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Logging (for debugging purposes only!)</th>
                        <td>
                                <label for="digishop_logging">
                                    <input type="checkbox" id="digishop_logging" name="<?php echo $settings_key; ?>[logging_enabled]" value="1"
                                        <?php echo empty($opts['logging_enabled']) ? '' : 'checked="checked"'; ?> /> Enable Logging</label>

                                <br/> Log Directory: <?php echo $webweb_wp_digishop_obj->get('plugin_data_dir'); ?>
                                <?php echo is_writable($webweb_wp_digishop_obj->get('plugin_data_dir'))
                                            ? '<br/>'
                                            : $webweb_wp_digishop_obj->msg('Folder not writable!'); ?>
                                For security reasons files are not available for direct download. Please use an FTP client for that purpose.
                                <br/>All the transaction info will be recorded including customer info.
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Secure HOP URL</th>
                        <td><input type="text" name="<?php echo $settings_key; ?>[secure_hop_url]" value="<?php echo $opts['secure_hop_url']; ?>" class="input_field" />
                            <br/>Example: https://secure.yoursite.com/proxy.php
                            <br/>
                            The main idea of the Secure HOP URL is to redirect to another URL. It must redirect to an address passed by the "r" parameter.
                            Having this kind of redirect is very useful because when your visitors are about to return to your site PayPal checks and if
                            the returning URL is a non-ssl link then it puts a prompt. <br/>
                            
                            <a href="<?php echo $webweb_wp_digishop_obj->get('plugin_url');?>/images/example_paypal_non_ssl_site_warning.png" target="_blank"><img
                                    style="border:2px dashed red;width: 50%;" src="<?php echo $webweb_wp_digishop_obj->get('plugin_url');?>/images/example_paypal_non_ssl_site_warning.png" alt="example_paypal_non_ssl_site_warning" /></a>

                            <div><strong>Sample redirect script</strong>. Right click and copy it and install it on your secure area.</div>
                            <textarea class="input_field" rows="6" readonly="readonly" onclick="this.select();">&lt;?php
// WordPress DigiShop
if (empty($_REQUEST['r'])) {
    die('It Works!');
}

$loc = empty($_REQUEST['r']) ? '/' : $_REQUEST['r'];
header('Location: ' . $loc);
die;
?&gt;</textarea>

                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Post Transaction Callback URL</th>
                        <td><input type="text" name="<?php echo $settings_key; ?>[callback_url]" value="<?php echo $opts['callback_url']; ?>" class="input_field" />
                            <br/>Example: http://yourdomain.com/another_ipn.php
                            <br/>
                            This is useful if you want to do execute operations after a transaction. <br/>
                            This could be creating user accounts, calling external APIs e.g. mailchimp to subscribe the person to a mailing list.<br/>
                            Your script will receive all the info sent from PayPal plus a variable called <strong>digishop_paypal_status</strong>
                                which can be: VERIFIED, INVALID, or NOT_AVAILABLE which will reflect the status of the transaction.
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <input type="submit" class="button-primary" value="<?php _e('Save') ?>" />
                </p>
            </form>
        </div> <!-- /main_content -->

        <?php include_once(dirname(__FILE__) . '/zzz_admin_sidebar.php'); ?>
    </div> <!-- /wrap -->
</div>