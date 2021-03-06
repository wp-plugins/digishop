<?php

/*
  Plugin Name: DigiShop
  Plugin URI: http://orbisius.com/site/products/digishop/
  Description: DigiShop plugin allows you to start selling your digital products such as e-books, reports in minutes.
  Version: 1.1.4
  Author: Svetoslav Marinov (Slavi)
  Author URI: http://orbisius.com
  License: GPL v2
 */

/*
  Copyright 2011-2020 Svetoslav Marinov (slavi@slavi.biz)

  This program ais free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; version 2 of the License.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

// we can be called from the test script
if (empty($_ENV['WEBWEB_WP_DIGISHOP_TEST'])) {
    // Make sure we don't expose any info if called directly
    if (!function_exists('add_action')) {
        echo "Hi there!  I'm just a plugin, not much I can do when called directly.";
        exit;
    }

	$webweb_wp_digishop_obj = WebWeb_WP_DigiShop::get_instance();

    add_action('init', array($webweb_wp_digishop_obj, 'init'));

    register_activation_hook(__FILE__, array($webweb_wp_digishop_obj, 'on_activate'));
    register_deactivation_hook(__FILE__, array($webweb_wp_digishop_obj, 'on_deactivate'));
    register_uninstall_hook(__FILE__, array($webweb_wp_digishop_obj, 'on_uninstall'));
}

class WebWeb_WP_DigiShop {
    private $log_enabled = 0;
    private $log_file = null;
    private $permalinks = 0;
    private static $instance = null; // singleton
    private $site_url = null; // filled in later
    private $plugin_url = null; // filled in later
    private $plugin_settings_key = null; // filled in later
    private $plugin_dir_name = null; // filled in later
    private $plugin_data_dir = null; // plugin data directory. for reports and data storing. filled in later
    private $plugin_name = 'DigiShop'; //
    private $plugin_id_str = 'digishop'; //
    private $plugin_business_sandbox = false; // sandbox or live ???
    private $plugin_business_email_sandbox = 'seller_1264288169_biz@slavi.biz'; // used for paypal payments
    private $plugin_business_email = 'billing@orbisius.com'; // used for paypal payments
    private $plugin_business_ipn = 'https://ssl.orbisius.com/webweb.ca/wp/hosted/payment/ipn.php'; // used for paypal IPN payments
    //private $plugin_business_status_url = 'http://localhost/wp/hosted/payment/status.php'; // used after paypal TXN to to avoid warning of non-ssl return urls
    private $plugin_business_status_url = 'https://ssl.orbisius.com/webweb.ca/wp/hosted/payment/status.php'; // used after paypal TXN to to avoid warning of non-ssl return urls
    private $plugin_support_email = 'help@orbisius.com'; //
    private $plugin_support_link = 'http://miniads.ca/widgets/contact/profile/digishop?height=200&width=500&description=Please enter your enquiry below.'; //
    private $plugin_admin_url_prefix = null; // filled in later
    private $plugin_home_page = 'http://webweb.ca/site/products/digishop/';
    private $plugin_tinymce_name = 'wwwpdigishop'; // if you change it update the tinymce/editor_plugin.js and reminify the .min.js file.
    private $plugin_cron_hook = __CLASS__;
    private $paypal_url = 'https://www.paypal.com/cgi-bin/webscr';
    private $paypal_submit_image_src = 'https://www.paypal.com/en_GB/i/btn/btn_buynow_LG.gif';
    private $db_version = '1.0';
    private $plugin_cron_freq = 'daily';
    private $plugin_default_opts = array(
        'status' => 0,
        'test_mode' => 0,
        'logging_enabled' => 0,
        'secure_hop_url' => '',
        'sandbox_business_email' => '',
        'sandbox_only_ip' => '',
        'notification_email' => '',
        'submit_button_img_src' => 'https://www.paypal.com/en_GB/i/btn/btn_buynow_LG.gif',
        'business_email' => '',
        'purchase_subject' => 'Download Link',
        'purchase_content' => "Dear %%FIRST_NAME%%,\n\nThank you for your order.\nTransaction: %%TXN_ID%%\nHere is the download link: %%DOWNLOAD_LINK%% for %%PRODUCT_NAME%%.\n\nRegards,\n%%SITE%% team",
        'currency' => 'USD',
        'purchase_thanks' => 'Thanks. The payment is being processing now. You should receive an email very soon.',
        'purchase_error' => 'There was a problem with the payment.',
        'callback_url' => '',
    );

	private $app_title = 'Start selling your digital products (e-books, music, reports) within minutes!';
	private $plugin_description = 'Allows you to start selling your digital products such as e-books, reports in minutes.';

    private $plugin_uploads_path = null; // E.g. /wp-content/uploads/PLUGIN_ID_STR/
    private $plugin_uploads_url = null; // E.g. http://yourdomain/wp-content/uploads/PLUGIN_ID_STR/
    private $plugin_uploads_dir = null; // E.g. DOC_ROOT/wp-content/uploads/PLUGIN_ID_STR/

    private $download_key = null; // the param that will hold the download hash
    private $web_trigger_key = null; // the param will trigger something to happen. (e.g. PayPal IPN, test check etc.)

    // can't be instantiated; just using get_instance
    private function __construct() {

    }

    /**
     * handles the singleton
     */
    public static function get_instance() {
		if (is_null(self::$instance)) {
            global $wpdb;

			$cls = __CLASS__;
			$inst = new $cls;

			$site_url = get_settings('siteurl');
			$site_url = rtrim($site_url, '/') . '/'; // e.g. http://domain.com/blog/

			$inst->site_url = $site_url;
			$inst->plugin_dir_name = basename(dirname(__FILE__)); // e.g. wp-command-center; this can change e.g. a 123 can be appended if such folder exist
			$inst->plugin_data_dir = dirname(__FILE__) . '/data';
			$inst->plugin_url = $site_url . 'wp-content/plugins/' . $inst->plugin_dir_name . '/';
			$inst->plugin_settings_key = $inst->plugin_id_str . '_settings';
            $inst->plugin_support_link .= '&css_file=' . urlencode(get_bloginfo('stylesheet_url'));
            $inst->plugin_admin_url_prefix = $site_url . 'wp-admin/admin.php?page=' . $inst->plugin_dir_name;

            $inst->delete_product_url = $inst->plugin_admin_url_prefix . '/menu.products.php&do=delete';
			$inst->add_product_url = $inst->plugin_admin_url_prefix . '/menu.product.add.php';
			$inst->edit_product_url = $inst->plugin_admin_url_prefix . '/menu.product.add.php';

            // where digital products will be saved.
            $inst->plugin_uploads_path = '/wp-content/uploads/' . $inst->plugin_id_str . '/';
            $inst->plugin_uploads_url = $site_url . $inst->plugin_uploads_path;
            $inst->plugin_uploads_dir = ABSPATH . ltrim($inst->plugin_uploads_path, '/');

            // will be retrieved later by ->get method calls
            $inst->plugin_db_prefix = $wpdb->prefix . $inst->plugin_id_str . '_';
            $inst->web_trigger_key = $inst->plugin_id_str . '_cmd';

            // 2012:01:04: let's keep using the old links.
//            $inst->permalinks = get_option('permalink_structure') != '';

            if ($inst->permalinks) { // WP/digishop_cmd/paypal
                $inst->payment_notify_url = $site_url . $inst->web_trigger_key . '=paypal_ipn';
            } else { // old way
                $inst->payment_notify_url = WebWeb_WP_DigiShopUtil::add_url_params($site_url, array($inst->web_trigger_key => 'paypal_ipn'));
            }

            $inst->download_key = $inst->plugin_id_str . '_dl';

            $opts = $inst->get_options();

            if (!$inst->log_enabled && !empty($opts['logging_enabled'])) {
                $inst->log_enabled = $opts['logging_enabled'];
            }

            // the log file be: log.1dd9091e045b9374dfb6b042990d65cc.2012-01-05.log
			if ($inst->log_enabled) {
				$inst->log_file = $inst->plugin_data_dir . '/log.'
                        . md5($site_url . $inst->plugin_dir_name)
                        . '.' . date('Y-m-d') . '.log';
			}

			add_action('plugins_loaded', array($inst, 'init'), 100);

			define('WEBWEB_WP_DIGISHOP_BASE_DIR', dirname(__FILE__)); // e.g. // htdocs/wordpress/wp-content/plugins/wp-command-center
			define('WEBWEB_WP_DIGISHOP_DIR_NAME', $inst->plugin_dir_name);

            self::$instance = $inst;
        }

		return self::$instance;
	}

    public function __clone() {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }

    public function __wakeup() {
        trigger_error('Unserializing is not allowed.', E_USER_ERROR);
    }

    /**
     * Logs whatever is passed IF logs are enabled.
     */
    function log($msg = '') {
        if ($this->log_enabled) {
            $msg = '[' . date('r') . '] ' . '[' . $_SERVER['REMOTE_ADDR'] . '] ' . $msg . "\n";
            error_log($msg, 3, $this->log_file);
        }
    }

    /**
     * handles the init
     */
    function init() {
        global $wpdb;

        if (is_admin()) {
            // Administration menus
            add_action('admin_menu', array($this, 'administration_menu'));
            add_action('admin_init', array($this, 'add_buttons'));
            add_action('admin_init', array($this, 'register_settings'));
            add_action('admin_notices', array($this, 'notices'));

            // http://codex.wordpress.org/Creating_Tables_with_Plugins
            // since 3.1 the register_activation_hook is not called when a plugin is updated, so to run the above
            // code on automatic upgrade you need to check the plugin db version on another hook. like this:
            add_action('plugins_loaded', array($this, 'install_db_tables'));

            wp_register_style($this->plugin_dir_name, $this->plugin_url . 'css/main.css', false, 0.31);
            wp_enqueue_style($this->plugin_dir_name);
        } else {
            if (!is_feed()) {
                add_action('wp_head', array($this, 'add_plugin_credits'), 1); // be the first in the header
                add_action('wp_footer', array($this, 'add_plugin_credits'), 1000); // be the last in the footer
                wp_enqueue_script('jquery');

                // The short code is has a closing *tag* e.g. [tag]...[/tag] so normal tag partse won't work
                add_shortcode($this->plugin_id_str, array($this, 'parse_short_code'));
                //add_filter('the_content', array($this, 'parse_short_code'), 10000); // run last to check fb container and other stuff are added

                add_filter('parse_request', array($this, 'parse_request'));
                add_filter('query_vars', array($this, 'add_query_vars'));

                // If permalinks are enabled
                if ($this->permalinks) {
                    add_filter('rewrite_rules_array', array($this, 'add_rewrite_rules'));
                    add_action('wp_loaded', array($this, 'flush_rewrite_rules'));
                }

                add_action('get_footer', array($this, 'public_notices')); // status after TXN
            }
        }
    }

    private $query_vars;

    /**
     * Parse requests containing "my_plugin=paypal"
     * @param type $wp
     * @see http://codex.wordpress.org/Rewrite_API/add_rewrite_rule
     * @see http://www.james-vandyne.com/process-paypal-ipn-requests-through-wordpress/
     */
    function parse_request($wp) {
        $this->query_vars = $wp->query_vars;

        if (array_key_exists($this->web_trigger_key, $wp->query_vars)
                || array_key_exists($this->download_key, $wp->query_vars)) {
            if ($wp->query_vars[$this->web_trigger_key] == 'paypal_ipn'
                    || $wp->query_vars[$this->web_trigger_key] == 'paypal_checkout') {
                $this->handle_non_ui($wp->query_vars);
            } elseif (!empty($wp->query_vars[$this->download_key])) {
                $this->handle_non_ui($wp->query_vars);
            } elseif ($wp->query_vars[$this->web_trigger_key] == 'smtest') {
                wp_die($this->plugin_name . ': OK :)');
            } else {
                // if it's txdigishop_cmd=txn_okn_ok it'll be handled by the page which renders the form.
                //wp_die($this->plugin_name . ': Invalid value.');
            }
        }
    }

    /**
     *
     * Whitelabels the variable 'digishop'... so WP allows it
     *
     * @param type $vars
     * @return type
     * @see http://codex.wordpress.org/Rewrite_API/add_rewrite_rule
     * @see http://www.james-vandyne.com/process-paypal-ipn-requests-through-wordpress/
     */
    function add_query_vars($vars) {
        // add my_plugin to the valid list of variables
        $new_vars = array($this->web_trigger_key, $this->download_key);
        $vars = $new_vars + $vars;

        add_rewrite_tag('%' . $this->web_trigger_key . '%','([^&]+)');
        add_rewrite_tag('%' . $this->download_key . '%','([^&]+)');

        return $vars;
    }

    /**
     *
     * Adds rewrite rules. If you change them or add new update them in flush_rewrite_rules
     *
     * @global WP_Rewrite $wp_rewrite
     * @param array $wp_rewrite
     * @see http://codex.wordpress.org/Class_Reference/WP_Rewrite
     * @see http://codex.wordpress.org/Rewrite_API/add_rewrite_rule
     * @see http://www.james-vandyne.com/process-paypal-ipn-requests-through-wordpress/
     */
    function add_rewrite_rules($rules) {
        $new_rules = array(
            // handles SITE_URL/digishop_cmd/SOMETHING
            $this->web_trigger_key . '/([\w-]+)/?' => 'index.php?' . $this->web_trigger_key . '=$matches[1]',

            // Handles: http://localhost/wordpress313/digishop_dl/f524efe208d0397d0ac0593ef81a15df79efbf3f
            $this->download_key . '/([\w-]+)/?' => 'index.php?' . $this->download_key . '=$matches[1]',
        );

        return $new_rules + $rules;
    }

    /**
     *
     * @global WP_Rewrite $wp_rewrite
     */
    function flush_rewrite_rules() {
        $rules = get_option('rewrite_rules');

        if (!isset($rules[$this->web_trigger_key . '/([\w-]+)/?'])
                    || !isset($rules[$this->download_key . '/([\w-]+)/?'])) {
            global $wp_rewrite;
            $wp_rewrite->flush_rules();
        }
    }

    /**
     * Searches and replaces the short code [digishop]
     * It will replace the code with errors in case of
     * - invalid ID/missing
     * - no file found
     * - if the product is disabled (active=0)
     */
    function parse_short_code($attr = array()) {
        global $post;
        $post_url = get_permalink($post->ID);

        $opts = $this->get_options();

        $id = empty($attr['id']) ? 0 : WebWeb_WP_DigiShopUtil::stop_bad_input($attr['id'], WebWeb_WP_DigiShopUtil::SANITIZE_NUMERIC);
        $id = empty($attr['id']) ? 0 : WebWeb_WP_DigiShopUtil::stop_bad_input($attr['id'], WebWeb_WP_DigiShopUtil::SANITIZE_NUMERIC);

        if (empty($id)) {
            return $this->m($this->plugin_id_str . ': empty product ID. Possibly incorrect use of the short code.', 0, 1);
        }

        if (empty($opts['status'])) {
            return "<!-- {$this->plugin_id_str} is Disabled | Plugin URL: {$this->plugin_home_page} -->";
        }

        $prev_rec = $this->get_product($id);

        // these errors should be seen by the admin
        if (empty($prev_rec)) {
            return $this->m($this->plugin_id_str . ": Product [$id] was not found.", 0, 1);
        } elseif (empty($prev_rec['file'])) {
            return $this->m($this->plugin_id_str . ": Product [$id] does not have a file associated with it.", 0, 1);
        } elseif (empty($prev_rec['active'])) {
            return "<!-- {$this->plugin_id_str} Product id=$id is inactive | Plugin URL: {$this->plugin_home_page} | Post URL: $post_url -->";
        }

        $paypal_url = 'https://www.paypal.com/cgi-bin/webscr';

        if (!empty($opts['test_mode'])) {
			if (empty($opts['sandbox_only_ip'])
						|| (!empty($opts['sandbox_only_ip']) && $_SERVER['REMOTE_ADDR'] == $opts['sandbox_only_ip'])) {
				//$paypal_url = str_replace('paypal.com', 'sandbox.paypal.com', $paypal_url);
				$paypal_url = str_replace('paypal.com', 'sandbox.paypal.com', $paypal_url);
				$email = empty($opts['sandbox_business_email']) ? $opts['business_email'] : $opts['sandbox_business_email'];
			}
        } else {
            $email = $opts['business_email'];
        }

        $notify_url = $this->payment_notify_url;
        $currency = $opts['currency'];
        $price = $prev_rec['price'];

        $return_page = WebWeb_WP_DigiShopUtil::add_url_params($post_url, array($this->web_trigger_key => 'txn_ok'));
        $cancel_return = WebWeb_WP_DigiShopUtil::add_url_params($post_url, array($this->web_trigger_key => 'txn_error'));

        $item_name = esc_attr($prev_rec['label']);
        $item_number = $prev_rec['id'];
        $price = sprintf("%01.2f", $price);

        $custom = http_build_query(array('id' => $item_number, 'site' => $this->site_url));

        $aaa_cmd_key = $this->web_trigger_key;

        $submit_button_img_src = empty($opts['submit_button_img_src']) ? $this->paypal_submit_image_src : $opts['submit_button_img_src'];
        $form_new_window = empty($opts['form_new_window']) ? '' : ' target="_blank" ';

        /*
         0 – prompt for an address, but do not require one
         1 – do not prompt for an address
         2 – prompt for an address, and require one
         */
        // paypal's logic is inverted but we'll be positive. i.e. when we don't want shipping we'll set no_shipping -> 1
        // https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_html_Appx_websitestandard_htmlvariables

        if (isset($attr['require_shipping'])) { // shipping settings for a specific product
            $no_shipping = empty($attr['require_shipping']) ? 1 : 2;
        } else {
            $no_shipping = empty($opts['require_shipping']) ? 1 : 2;
        }

        $post_url_esc = esc_attr($post_url);

        if (!empty($opts['render_old_paypal_form'])) {
            $buffer = <<<SHORT_CODE_EOF
<!-- $this->plugin_id_str | Plugin URL: {$this->plugin_home_page} | Post URL: $post_url_esc -->
<form action="$paypal_url" method="post" target="_blank" >
            <input type='hidden' name="business" value="$email" />
            <input type="hidden" name="cmd" value="_xclick" />
            <input type='hidden' name="item_name" value="$item_name" />
            <input type='hidden' name="item_number" value="$item_number" />
            <input type='hidden' name="amount" value="$price" />
            <input type="hidden" name="no_shipping" value="$no_shipping" />
            <input type="hidden" name="no_note" value="1" />
            <input type='hidden' name="currency_code" value="$currency" />
            <input type='hidden' name="notify_url" value="$notify_url" />
            <input type='hidden' name="return" value="$return_page" />
            <input type='hidden' name="cancel_return" value="$cancel_return" />
            <input type='hidden' name="custom" value="$custom" />
            <input type='image' src='https://www.paypal.com/en_GB/i/btn/btn_buynow_LG.gif' border="0" name="submit" alt="Buy Now! - The safer, easier way to pay online." />
</form>
<!-- /$this->plugin_id_str | Plugin URL: {$this->plugin_home_page} | Post URL: $post_url_esc -->
SHORT_CODE_EOF;
        } else {

            $buffer = <<<SHORT_CODE_EOF
<!-- $this->plugin_id_str | Plugin URL: {$this->plugin_home_page} | Post URL: $post_url_esc -->
<form id="{$this->plugin_id_str}_form_$id" class="{$this->plugin_id_str}_form" action="$post_url_esc" method="post" $form_new_window onsubmit="jQuery('.{$this->plugin_id_str}_loader').show();">
    <input type='hidden' name="$aaa_cmd_key" value="paypal_checkout" />
    <input type='hidden' name="{$this->plugin_id_str}_product_id" value="$id" />
    <input type='hidden' name="{$this->plugin_id_str}_post_id" value="{$post->ID}" />
    <input type='hidden' name="{$this->plugin_id_str}_no_shipping" value="$no_shipping" />

	<span id="{$this->plugin_id_str}_form_submit_button_container_$id" class="{$this->plugin_id_str}_form_submit_button_container">
		<input id="{$this->plugin_id_str}_form_submit_button_$id" type="image" class="{$this->plugin_id_str}_form_submit_button" src="$submit_button_img_src"
            border="0" name="submit" alt="Buy Now!" />
        <span class="{$this->plugin_id_str}_loader app_hide" style="display:none;">Please wait...</span>
	</span>
</form>
<!-- /$this->plugin_id_str | Plugin URL: {$this->plugin_home_page} | Post URL: $post_url_esc -->
SHORT_CODE_EOF;

        }

        $extra_msg = '';

        if (!empty($_REQUEST[$this->web_trigger_key])) {
            if ($_REQUEST[$this->web_trigger_key] == 'txn_ok') {
                $extra_msg = $this->m("<br/>" . $opts['purchase_thanks'], 1, 1);
            } elseif ($_REQUEST[$this->web_trigger_key] == 'txn_error') {
                $extra_msg = $this->m("<br/>" . $opts['purchase_error'], 0, 1);
            }
        }

        if (!empty($extra_msg)) {
            $extra_msg = "<p>$extra_msg</p>";
        }

        $buffer .= $extra_msg;

		return $buffer;
    }

    /**
     * defines the db tables per version
     * @var array
     */
    private $db_tables = array(
           '1.0' => array(
               'products' => "
                    CREATE TABLE `%%TABLE_NAME%%` (
                    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                    `label` VARCHAR( 255 ) NOT NULL DEFAULT '',
                    `price` DOUBLE NOT NULL DEFAULT '0.0',
                    `file` varchar(255) NOT NULL DEFAULT '' COMMENT 'digital product',
                    `hash` VARCHAR( 100 ) NOT NULL COMMENT 'used for downloads',
                    `added_on` DATETIME NOT NULL ,
                    `status` INT NOT NULL DEFAULT '1' COMMENT '1-Sale, 2-Pre-Order, 3 Subscription',
                    `active` INT NOT NULL DEFAULT '0',
                    INDEX ( `status` , `active` )
                    ) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;
               ",
            /*'orders' => "
                ",*/
           ),
    );

    /**
     * Creates db tables and upgrades them if necessary
     */
    function install_db_tables() {
        $opts = $this->get_options();

        // we don't need to constantly perform checks
        if (!empty($opts['db_checked'])) {
            return 1;
        }

        global $wpdb;
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $version = $this->db_version; // current in the code
        $tables = $this->db_tables[$version];

        $db_ver_set = 0;
        $db_ver_key = $this->plugin_id_str . "_db_version";
        $db_version_site = get_option($db_ver_key); // what version is the db schema of the current site

        // create OR upgrades db tables if necessary
        foreach ($tables as $table_name => $sql) {
            // Goal: WP_PREFX_MY_PLUGIN_PREFIX_TABLE_NAME
            $table_name = $this->plugin_db_prefix . $table_name;
            $sql = str_replace('%%TABLE_NAME%%', $table_name, $sql);

            if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name
                    || (!empty($db_version_site) && $db_version_site != $version)) {
                dbDelta($sql);

                if (empty($db_ver_set)) {
                    update_option($db_ver_key, $version);
                    $db_ver_set = 1;
                    $opts['db_checked'] = 1;
                    $this->set_options($opts);
                }
            }
        }
    }

    /**
     * Handles the plugin activation.
     */
    function uninstall_db_tables() {
        global $wpdb;

        $version = $this->db_version;
        $tables = $this->db_tables[$version];

        foreach ($tables as $table_name => $sql) {
            $table_name = $this->plugin_db_prefix . $table_name;
            $wpdb->query("DROP TABLE IF EXISTS " . $table_name);
        }
    }

    /**
     * Handles the plugin activation. creates db tables and uploads dir with an htaccess file
     */
    function on_activate() {
        $this->install_db_tables();
        $opts['db_checked'] = 1;
        $this->set_options($opts);
    }

    /**
     * Handles the plugin deactivation.
     */
    function on_deactivate() {
        $opts['status'] = 0;
        $opts['test_mode'] = 0;
        $opts['db_checked'] = 0;
        $this->set_options($opts);

        // uncomment only when testing! we don't want the user to loose everything because he/she deactivated the plugin
        //$this->uninstall_db_tables();
    }

    /**
     * Handles the plugin uninstallation.
     */
    function on_uninstall() {
        delete_option($this->plugin_settings_key);
        $this->uninstall_db_tables();
    }

    /**
     * Allows access to some private vars
     * @param str $var
     */
    public function get($var) {
        if (isset($this->$var) /* && (strpos($var, 'plugin') !== false) */) {
            return $this->$var;
        }
    }

    /**
     * gets current options and return the default ones if not exist
     * @param void
     * @return array
     */
    function get_options() {
        $opts = get_option($this->plugin_settings_key);
        $opts = empty($opts) ? array() : (array) $opts;

        // if we've introduced a new default key/value it'll show up.
        $opts = array_merge($this->plugin_default_opts, $opts);

        if (empty($opts['purchase_thanks'])) {
            $opts['purchase_thanks'] = $this->plugin_default_opts['purchase_thanks'];
        }

        if (empty($opts['purchase_error'])) {
            $opts['purchase_error'] = $this->plugin_default_opts['purchase_error'];
        }

        if (empty($opts['purchase_subject'])) {
            $opts['purchase_subject'] = $this->plugin_default_opts['purchase_subject'];
        }

        if (empty($opts['purchase_content'])) {
            $opts['purchase_content'] = $this->plugin_default_opts['purchase_content'];
        }

        if (isset($opts['sandbox_only_ip'])) {
            $opts['sandbox_only_ip'] = trim($opts['sandbox_only_ip']);
        }

        return $opts;
    }

    /**
     * Updates options but it merges them unless $override is set to 1
     * that way we could just update one variable of the settings.
     */
    function set_options($opts = array(), $override = 0) {
        if (!$override) {
            $old_opts = $this->get_options();
            $opts = array_merge($old_opts, $opts);
        }

        update_option($this->plugin_settings_key, $opts);

        return $opts;
    }

    /**
     * This is what the plugin admins will see when they click on the main menu.
     * @var string
     */
    private $plugin_landing_tab = '/menu.dashboard.php';

    /**
     * Adds the settings in the admin menu
     */
    public function administration_menu() {
        // Settings > DigiShop
        add_options_page(__($this->plugin_name, "WEBWEB_WP_DIGISHOP"), __($this->plugin_name, "WEBWEB_WP_DIGISHOP"), 'manage_options', $this->plugin_dir_name . '/menu.settings.php');

        add_menu_page(__($this->plugin_name, $this->plugin_dir_name), __($this->plugin_name, $this->plugin_dir_name), 'manage_options', $this->plugin_dir_name . '/menu.dashboard.php', null, $this->plugin_url . '/images/icon.png');

        add_submenu_page($this->plugin_dir_name . '/' . $this->plugin_landing_tab, __('Dashboard', $this->plugin_dir_name), __('Dashboard', $this->plugin_dir_name), 'manage_options', $this->plugin_dir_name . '/menu.dashboard.php');
        add_submenu_page($this->plugin_dir_name . '/' . $this->plugin_landing_tab, __('Products', $this->plugin_dir_name), __('Products', $this->plugin_dir_name), 'manage_options', $this->plugin_dir_name . '/menu.products.php');
        add_submenu_page($this->plugin_dir_name . '/' . $this->plugin_landing_tab, __('Add Product', $this->plugin_dir_name), __('Add Product', $this->plugin_dir_name), 'manage_options', $this->plugin_dir_name . '/menu.product.add.php');
        add_submenu_page($this->plugin_dir_name . '/' . $this->plugin_landing_tab, __('Settings', $this->plugin_dir_name), __('Settings', $this->plugin_dir_name), 'manage_options', $this->plugin_dir_name . '/menu.settings.php');
        add_submenu_page($this->plugin_dir_name . '/' . $this->plugin_landing_tab, __('FAQ', $this->plugin_dir_name), __('FAQ', $this->plugin_dir_name), 'manage_options', $this->plugin_dir_name . '/menu.faq.php');
        add_submenu_page($this->plugin_dir_name . '/' . $this->plugin_landing_tab, __('Help', $this->plugin_dir_name), __('Help', $this->plugin_dir_name), 'manage_options', $this->plugin_dir_name . '/menu.support.php');

        add_submenu_page($this->plugin_dir_name . '/' . $this->plugin_landing_tab, __('Contact', $this->plugin_dir_name), __('Contact', $this->plugin_dir_name), 'manage_options', $this->plugin_dir_name . '/menu.contact.php');

        add_submenu_page($this->plugin_dir_name . '/' . $this->plugin_landing_tab, __('About', $this->plugin_dir_name), __('About', $this->plugin_dir_name), 'manage_options', $this->plugin_dir_name . '/menu.about.php');

        // when plugins are show add a settings link near my plugin for a quick access to the settings page.
        add_filter('plugin_action_links', array($this, 'add_plugin_settings_link'), 10, 2);
    }

	/**
     * Allows access to some private vars
     * @param str $var
     */
    public function generate_newsletter_box($params = array()) {
        $file = WEBWEB_WP_DIGISHOP_BASE_DIR . '/zzz_newsletter_box.html';

        $buffer = WebWeb_WP_DigiShopUtil::read($file);

        wp_get_current_user();
        global $current_user;
        $user_email = $current_user->user_email;

        $replace_vars = array(
            '%%PLUGIN_URL%%' => $this->get('plugin_url'),
            '%%USER_EMAIL%%' => $user_email,
            '%%PLUGIN_ID_STR%%' => $this->get('plugin_id_str'),
            '%%admin_sidebar%%' => $this->get('plugin_id_str'),
        );

        if (!empty($params['form_only'])) {
            $replace_vars['NEWSLETTER_QR_EXTRA_CLASS'] = "app_hide";
        } else {
            $replace_vars['NEWSLETTER_QR_EXTRA_CLASS'] = "";
        }

        if (!empty($params['src2'])) {
            $replace_vars['SRC2'] = $params['src2'];
        } elseif (!empty($params['SRC2'])) {
            $replace_vars['SRC2'] = $params['SRC2'];
        }

        $buffer = WebWeb_WP_DigiShopUtil::replace_vars($buffer, $replace_vars);

        return $buffer;
    }

    /**
     * Allows access to some private vars
     * @param str $var
     */
    public function generate_donate_box() {
        $msg = '';
        $file = WEBWEB_WP_DIGISHOP_BASE_DIR . '/zzz_donate_box.html';

        if (!empty($_REQUEST['error'])) {
            $msg = $this->message('There was a problem with the payment.');
        }

        if (!empty($_REQUEST['ok'])) {
            $msg = $this->message('Thank you so much!', 1);
        }

        $return_url = WebWeb_WP_DigiShopUtil::add_url_params($this->get('plugin_business_status_url'), array(
            'r' => $this->get('plugin_admin_url_prefix') . '/menu.dashboard.php&ok=1', // paypal de/escapes
            'status' => 1,
        ));

        $cancel_url = WebWeb_WP_DigiShopUtil::add_url_params($this->get('plugin_business_status_url'), array(
            'r' => $this->get('plugin_admin_url_prefix') . '/menu.dashboard.php&error=1', //
            'status' => 0,
        ));

        $replace_vars = array(
            '%%MSG%%' => $msg,
            '%%AMOUNT%%' => '9.99',
            '%%BUSINESS_EMAIL%%' => $this->plugin_business_email,
            '%%ITEM_NAME%%' => $this->plugin_name . ' Donation',
            '%%ITEM_NAME_REGULARLY%%' => $this->plugin_name . ' Donation (regularly)',
            '%%PLUGIN_URL%%' => $this->get('plugin_url'),
            '%%CUSTOM%%' => http_build_query(array('site_url' => $this->site_url, 'product_name' => $this->plugin_id_str)),
            '%%NOTIFY_URL%%' => $this->get('plugin_business_ipn'),
            '%%RETURN_URL%%' => $return_url,
            '%%CANCEL_URL%%' => $cancel_url,
        );

        // Let's switch the Sandbox settings.
        if ($this->plugin_business_sandbox) {
            $replace_vars['paypal.com'] = 'sandbox.paypal.com';
            $replace_vars['%%BUSINESS_EMAIL%%'] = $this->plugin_business_email_sandbox;
        }

        $buffer = WebWeb_WP_DigiShopUtil::read($file);
        $buffer = str_replace(array_keys($replace_vars), array_values($replace_vars), $buffer);

        return $buffer;
    }

    /**
     * Outputs some options info. No save for now.
     */
    function options() {
		$webweb_wp_digishop_obj = WebWeb_WP_DigiShop::get_instance();
        $opts = get_option('settings');

        include_once(WEBWEB_WP_DIGISHOP_BASE_DIR . '/menu.settings.php');
    }

    /**
     * Sets the setting variables
     */
    function register_settings() { // whitelist options
        register_setting($this->plugin_dir_name, $this->plugin_settings_key);
    }

    // Add the ? settings link in Plugins page very good
    function add_plugin_settings_link($links, $file) {
        if ($file == plugin_basename(__FILE__)) {
            //$prefix = 'options-general.php?page=' . dirname(plugin_basename(__FILE__)) . '/';
            $prefix = $this->plugin_admin_url_prefix . '/';

            $dashboard_link = "<a href=\"{$prefix}menu.dashboard.php\">" . __("Dashboard", $this->plugin_dir_name) . '</a>';
            $settings_link = "<a href=\"{$prefix}menu.settings.php\">" . __("Settings", $this->plugin_dir_name) . '</a>';
            $products_link = "<a href=\"{$prefix}menu.products.php\">" . __("Products", $this->plugin_dir_name) . '</a>';

            array_unshift($links, $products_link);
            array_unshift($links, $settings_link);
            array_unshift($links, $dashboard_link);
        }

        return $links;
    }

    /**
     * Downloads served when accessed via yourwpsite.com/?digishop_dl=asflasfjlasjflajslkf124
     * OR yourwpsite.com/digishop_dl/asflasfjlasjflajslkf124
     * Missing or inactive products are not served.
     */
    function handle_non_ui($params = null) {
        $dl_key = $this->download_key;

        if (!is_null($params)) {
            $data = array_merge($_REQUEST, $params);
        } else {
            $data = $_REQUEST;
        }

        $opts = $this->get_options();

        if (!empty($data[$dl_key])) {
            $hash = $data[$dl_key];
            $hash = WebWeb_WP_DigiShopUtil::stop_bad_input($hash, WebWeb_WP_DigiShopUtil::SANITIZE_ALPHA_NUMERIC);

            // shortened hash so read it from a file + check for expiration + check download count
            if (strlen($hash) < 40) {
                $file = $this->plugin_uploads_dir . '___sys_txn_dl_' . $hash . '.txt';
                $dl_cnt_file = $this->plugin_uploads_dir . '___sys_txn_dl_' . $hash . '_cnt.txt';

                if (!file_exists($file)) {
                    wp_die($this->m($this->plugin_id_str . ': Invalid download hash (1).', 0, 1)
                        . $this->add_plugin_credits());
                }

                $hash = WebWeb_WP_DigiShopUtil::read($file); // long sha1 hash
                $dl_cnt = WebWeb_WP_DigiShopUtil::read($dl_cnt_file);
                $dl_cnt = empty($dl_cnt) ? 1 : intval($dl_cnt);

                if (time() - filemtime($file) > 48 * 3600) { // dl expire after 48h
                    wp_die($this->m($this->plugin_id_str . ': The download has expired.', 0, 1)
                            . $this->add_plugin_credits());
                }

                // was it downloaded more than 3 times?
                if ($dl_cnt > 3) {
                    wp_die($this->m($this->plugin_id_str . ': The download limit has been reached.', 0, 1)
                            . $this->add_plugin_credits());
                }

                $dl_cnt++;
                WebWeb_WP_DigiShopUtil::write($dl_cnt_file, $dl_cnt);
            } else { // old sha1 hash
                $hash = $data[$dl_key];
            }

            $product_rec = $this->get_product($hash);

            if (empty($product_rec) || empty($product_rec['active'])) {
                wp_die($this->m($this->plugin_id_str . ': Invalid download hash (2).', 0, 1)
                        . $this->add_plugin_credits());
            }

            // Ext URL
            if (WebWeb_WP_DigiShopUtil::validate_url($product_rec['file'])) {
                $this->log("Going to serve product ID: {$product_rec['id']}, ext URL: " . $product_rec['file']);

                wp_redirect($product_rec['file']);
                exit;
            }

            $file = $this->plugin_uploads_dir . $product_rec['file'];

            $this->log("Going to serve product ID: {$product_rec['id']}, file: $file");
            WebWeb_WP_DigiShopUtil::download_file($file);
        }
        // get product info and prepare PayPal form and redirect.
        elseif (!empty($this->query_vars[$this->web_trigger_key]) && $this->query_vars[$this->web_trigger_key] == 'paypal_checkout') {
            $id = empty($data[$this->plugin_id_str . '_product_id']) ? 0 : $data[$this->plugin_id_str . '_product_id'];
            $post_id = empty($data[$this->plugin_id_str . '_post_id']) ? 0 : $data[$this->plugin_id_str . '_post_id'];
            $no_shipping = isset($data[$this->plugin_id_str . '_no_shipping']) ? $data[$this->plugin_id_str . '_no_shipping'] : 1; // can be 0/1

            $post_url = get_permalink($post_id);

            $id = WebWeb_WP_DigiShopUtil::stop_bad_input($id, WebWeb_WP_DigiShopUtil::SANITIZE_NUMERIC);
            $post_id = WebWeb_WP_DigiShopUtil::stop_bad_input($post_id, WebWeb_WP_DigiShopUtil::SANITIZE_NUMERIC);

            $product_rec = $this->get_product($id);

            if (empty($product_rec) || empty($product_rec['active'])) {
                $this->log('paypal_checkout (1): Invalid Product ID: ' . $id);
                wp_die('Invalid Product ID: ' . $id);
            }

            $paypal_url = $this->paypal_url;

            if (!empty($opts['test_mode'])) {
                $paypal_url = str_replace('paypal.com', 'sandbox.paypal.com', $paypal_url);
                $email = empty($opts['sandbox_business_email']) ? $opts['business_email'] : $opts['sandbox_business_email'];
            } else {
                $email = $opts['business_email'];
            }

            $item_name = esc_attr($product_rec['label']);
            $item_number = $product_rec['id'];

            $price = $product_rec['price'];
            $price = sprintf("%01.2f", $price);

            $cancel_return = WebWeb_WP_DigiShopUtil::add_url_params($post_url, array($this->web_trigger_key => 'txn_error'));
            $return_page = WebWeb_WP_DigiShopUtil::add_url_params($post_url, array($this->web_trigger_key => 'txn_ok'));

            // if we have secure hop url we'll use it.
            if (!empty($opts['secure_hop_url']) && (stripos($opts['secure_hop_url'], 'https://') !== false)) {
                $return_page = WebWeb_WP_DigiShopUtil::add_url_params($opts['secure_hop_url'], array('r' => $return_page));
            }

            $paypal_params = array(
                'cmd' => '_xclick',
                'business' => $email,
                'no_shipping' => $no_shipping,
                'no_note' => 1,
                'amount' => $price,
                'item_name' => $item_name,
                'item_number' => $item_number,
                'currency_code' => $opts['currency'],
                'custom' => http_build_query(array('id' => $item_number, 'site' => $this->site_url)),

                'notify_url' => $this->payment_notify_url,
                'return' => $return_page,
                'cancel_return' => $cancel_return,
            );

            $location = $paypal_url . '?' . http_build_query($paypal_params);

            $this->log('paypal_checkout URL: ' . $location);
            $this->log('paypal_checkout Params: ' . var_export($paypal_params, 1));

            wp_redirect($location);
            exit;
        }
        // IPN called by PayPal: some people reported that they or their clients got lots of emails.
        // we'll create a hash file based on the TXN and not notify if we're called more than once by paypal
        elseif (!empty($this->query_vars[$this->web_trigger_key]) && $this->query_vars[$this->web_trigger_key] == 'paypal_ipn') {
            // checking if this TXN has been processed. Paypal should always provide a unique TXN ID
			$data = $_POST;

            if (!empty($data['txn_id'])) {
                $txn_flag_file = $this->plugin_uploads_dir . '___sys_txn_' . WebWeb_WP_DigiShopUtil::generate_hash($data['txn_id']) . '.txt';
                $do_stop = 0;

                if (file_exists($txn_flag_file)) {
                    $this->log('paypal txn already processed. Will not process the txn. Got data: ' . var_export($data, 1));

                    $do_stop = 1;
                } else {
                    touch($txn_flag_file);
                }

                if (mt_rand(0, 10) % 2 == 0) { // 50% chance to cleanup the txn files after a paypal call.
                    $txn_files = glob($this->plugin_uploads_dir . '___sys_txn_*');

                    foreach ($txn_files as $file) {
                        if (time() - filemtime($txn_flag_file) > 7 * 24 * 3600) { // clean txns older than 7 days
                            $this->log('Deleting old txn flag file: ' . $file);
                            unlink($file);
                        }
                    }
                }

                if ($do_stop) {
                    wp_die($this->m($this->get('plugin_id_str')
                        . ': this transaction seem to have been processed already. Stopping.', 0, 1)
                        . $this->add_plugin_credits());
                }
            }

            $admin_email = get_option('admin_email');

            if (!empty($opts['notification_email'])) {
                $admin_email = $opts['notification_email'];
            }
            // ?? send to business email ???
            else {

            }

            $headers = "From: {$_SERVER['HTTP_HOST']} Wordpress <wordpress@{$_SERVER['HTTP_HOST']}>\r\n";

            if (!empty($data['custom'])) {
                $custom = $data['custom'];
            } elseif (!empty($_REQUEST['custom'])) {
                $custom = $_REQUEST['custom'];
            } else {
                $admin_email_buffer = 'Missing data. Got' . "\n";
                $admin_email_buffer .= "\n\$_REQUEST: \n" . var_export($_REQUEST, 1);
                $admin_email_buffer .= "\nOther data: \n" . var_export($data, 1);
                $admin_email_buffer .= "\nIP: " . $_SERVER['REMOTE_ADDR'] . "";
                $admin_email_buffer .= "\nBrowser: " . $_SERVER['HTTP_USER_AGENT'] . "\n";

                wp_mail($admin_email, 'Invalid Transaction (missing custom field)', $admin_email_buffer, $headers);

                $this->log('paypal_ipn Invalid Transaction (missing custom field). Adm email.' . $admin_email_buffer);

                wp_die($this->plugin_name . ': Invalid call.');
            }

            $paypal_data = array();
            parse_str($custom, $paypal_data);

            if (!empty($paypal_data['id'])) {
                $id = $paypal_data['id'];
            } else {
                //$id = $data['item_number'];
            }

            $id = WebWeb_WP_DigiShopUtil::stop_bad_input($id, WebWeb_WP_DigiShopUtil::SANITIZE_NUMERIC);
            $product_rec = $this->get_product($id);

            if (empty($product_rec) || empty($product_rec['active'])) {
                $this->log('paypal_ipn: Invalid/inactive Product ID: ' . $id);
                wp_die('paypal_ipn: Invalid/inactive Product ID: ' . $id);
            }

            // handle PayPal IPN calls
            //$data['cmd'] = '_notify-validate';
            unset($data['digishop_cmd']); // paypal will not validate the TXN if there are extra params.

            $sandbox = 0;
            $paypal_url = $this->paypal_url;

            if (!empty($opts['test_mode'])) {
                // we are in test mode
                // and if the sandbox IP is supplied we are going to enable sandbox only for that IP address.
                if (empty($opts['sandbox_only_ip'])
							|| !empty($data['test_ipn']) // PayPal submits this
                            || (!empty($opts['sandbox_only_ip']) && $_SERVER['REMOTE_ADDR'] == $opts['sandbox_only_ip'])) {
                    //$paypal_url = str_replace('paypal.com', 'sandbox.paypal.com', $paypal_url);
					$sandbox = 1;
                }
            }

            //$paypal_url = WebWeb_WP_DigiShopUtil::add_url_params($paypal_url, $data);
			$this->log("polgin $opts " . var_export($opts, 1));
			$this->log("{$opts['test_mode']} ?");
			$this->log("{$opts['sandbox_only_ip']} ?");
			$this->log("{$_SERVER['REMOTE_ADDR']} == {$opts['sandbox_only_ip']} ?");

            $ua = new WebWeb_WP_DigiShopCrawler();

            //$check_status = $ua->fetch($paypal_url);
			$paypal_buffer = WebWeb_WP_DigiShopUtil::verify_paypal($data, $sandbox);

            // Let's try again
            if (empty($paypal_buffer)) {
                $this->log('paypal_ipn (2): will try to call paypal again. Got data: ' . var_export($data, 1));
                //$check_status = $ua->fetch($paypal_url);
				$paypal_buffer = WebWeb_WP_DigiShopUtil::verify_paypal($data, $sandbox);
            }

            if (!empty($paypal_buffer)) {
                $buffer = $paypal_buffer;
                $buffer = trim($buffer);

                $subject_prefix = empty($data['test_ipn']) ? '' : 'Test Txn: ';

                // TODO: insert order ?
                $email_subject = $subject_prefix . $opts['purchase_subject'];
                $email_buffer = $opts['purchase_content'];

                if (empty($email_buffer)) {
                    $email_buffer = $this->plugin_default_opts['purchase_content'];
                }

                // Download link will be shortened by using a shortening class
                // we'll generate an ID e.g. time based + the IP of the person
                // this will create a text file with the download hash of the file
                $short = new WebWeb_WP_DigiShop_Shorty();
                $download_hash = $short->encode(time() + mt_rand(100, 100000));

                $file = $this->plugin_uploads_dir . '___sys_txn_dl_' . $download_hash . '.txt';
                WebWeb_WP_DigiShopUtil::write($file, $product_rec['hash']);

                $vars = array(
                    '%%FIRST_NAME%%' => $data['first_name'],
                    '%%LAST_NAME%%' => $data['last_name'],
                    '%%EMAIL%%' => $data['payer_email'],
                    '%%DOWNLOAD_LINK%%' => WebWeb_WP_DigiShopUtil::add_url_params($this->site_url, array($dl_key => $download_hash)),
                    '%%TXN_ID%%' => $data['txn_id'],
                    '%%SITE%%' => $this->site_url,
                    '%%PRODUCT_NAME%%' => $product_rec['label'],
                    '%%PRODUCT_PRICE%%' => $product_rec['price'],
                );

                $email_subject = str_ireplace(array_keys($vars), array_values($vars), $email_subject);
                $email_buffer = str_ireplace(array_keys($vars), array_values($vars), $email_buffer);

                if (strpos($buffer, "VERIFIED") !== false) {
                    $headers .= "BCC: $admin_email\r\n";
                    $mail_status = wp_mail($data['payer_email'], $email_subject, $email_buffer, $headers);

                    $data['digishop_paypal_status'] = 'VERIFIED';
                    $this->log("Email: (status: $mail_status) To: " . $data['payer_email'] . "\n" . $email_buffer);
                } else {
                    $admin_email_buffer = "Dear Admin,\n\nThe following transaction didn't validate with PayPal\n\n";
                    $admin_email_buffer .= "When you resolve the issue forward this email to your client.\n";
                    $admin_email_buffer .= "\n=================================================================\n\n";
                    $admin_email_buffer .= $email_buffer;
                    $admin_email_buffer .= "\n\n=================================================================\n";
                    $admin_email_buffer .= "\nSubmitted Data: \n\n" . var_export($vars, 1);
                    $admin_email_buffer .= "\nReceived Data: \n\n" .  var_export($data, 1);

                    $mail_status = wp_mail($admin_email, 'Unsuccessful Transaction', $admin_email_buffer, $headers);

                    if (strcmp($buffer, "INVALID") == 0) {
                        $data['digishop_paypal_status'] = 'INVALID';
                    } else {
                        $data['digishop_paypal_status'] = 'NOT_AVAILABLE';
                    }

                    $this->log("Email: (status: $mail_status) To: " . $admin_email . "\n" . $admin_email_buffer);
                }

                $this->log('TXN Status: ' . $data['digishop_paypal_status']);

                // Let's execute the callback
                if (!empty($opts['callback_url'])) {
                    $data['digishop_callback_time'] = time();
                    $callback_url = WebWeb_WP_DigiShopUtil::add_url_params($opts['callback_url'], $data);
                    $cb_status = $ua->fetch($callback_url);

                    $this->log("Called Callback URL: " . $callback_url . " status: $cb_status\nContent (from Callback URL): \n"
                            . $ua->get_content() . "\n Data: " . var_export($data, 1));
                }
            }
        }
    }

    /**
     * adds some HTML comments in the page so people would know that this plugin powers their site.
     */
    function add_plugin_credits() {
        //printf("\n" . '<meta name="generator" content="Powered by ' . $this->plugin_name . ' (' . $this->plugin_home_page . ') " />' . PHP_EOL);
        printf(PHP_EOL . '<!-- ' . PHP_EOL . 'Powered by ' . $this->plugin_name
                . ': ' . $this->app_title . PHP_EOL
                . 'URL: ' . $this->plugin_home_page . PHP_EOL
                . '-->' . PHP_EOL . PHP_EOL);
    }

    // kept for future use if necessary

    /**
     * Adds buttons only for RichText mode
     * @return void
     */
    function add_buttons() {
        // Don't bother doing this stuff if the current user lacks permissions
        if (!current_user_can('edit_posts') && !current_user_can('edit_pages')) {
            return;
        }

        // Add only in Rich Editor mode
        if (get_user_option('rich_editing') == 'true') {
            // add the button for wp2.5 in a new way
            add_filter("mce_external_plugins", array($this, "add_tinymce_plugin"), 5);
            add_filter('mce_buttons', array(&$this, 'register_button'), 5);
        }
    }

    // used to insert button in wordpress 2.5x editor
    function register_button($buttons) {
        array_push($buttons, "separator", $this->plugin_tinymce_name);

        return $buttons;
    }

    // Load the TinyMCE plugin : editor_plugin.js (wp2.5)
    function add_tinymce_plugin($plugin_array) {
        $plugin_array[$this->plugin_tinymce_name] = $this->plugin_url . 'tinymce/editor_plugin.min.js';

        return $plugin_array;
    }

    /**
     * Checks if WP simpple shopping cart is installed.
     */
    function notices() {
        $opts = $this->get_options();

        if (empty($opts['status'])) {
            echo $this->message($this->plugin_name . " is currently disabled. Please, enable it from "
                    . "<a href='{$this->plugin_admin_url_prefix}/menu.settings.php'> {$this->plugin_name} &gt; Settings</a>");
        } elseif (!empty($opts['test_mode']) && WebWeb_WP_DigiShopUtil::is_on_plugin_page()) { // show the notice only when checking the settings.
            if (!empty($opts['sandbox_only_ip'])) {
                echo $this->message($this->plugin_name . " is currently in Sandbox mode for <strong>{$opts['sandbox_only_ip']}</strong> address only. "
                    . "Regular users will be using the live PayPal site. To change the settings please go to: "
                    . "<a href='{$this->plugin_admin_url_prefix}/menu.settings.php'> {$this->plugin_name} &gt; Settings</a>");
            } else {
                echo $this->message($this->plugin_name . " is currently in Sandbox mode. To accept real transactions please uncheck Sandbox mode from "
                    . "<a href='{$this->plugin_admin_url_prefix}/menu.settings.php'> {$this->plugin_name} &gt; Settings</a>");
            }
        }
    }

    /**
     * Outputs message after a transaction in addition to the other message.
     */
    function public_notices() {
        $opts = $this->get_options();

        if (!empty($this->query_vars[$this->web_trigger_key])) {
            if ($this->query_vars[$this->web_trigger_key] == 'txn_ok') {
                $extra_msg = $this->msg("<br/>" . $opts['purchase_thanks'], 1, 1);
            } elseif ($this->query_vars[$this->web_trigger_key] == 'txn_error') {
                $extra_msg = $this->msg("<br/>" . $opts['purchase_error'], 0, 1);
            }

            $extra_msg = str_replace('\'', "\"", $extra_msg);

            // 2011-12-19: WP doesn't have a filter/action to insert after the <body> tag yet
            echo <<<CODE_EOF
       <script>
       jQuery(document).ready( function($) {
            $('body').prepend('$extra_msg');
       });
       </script>
CODE_EOF;

        }
    }

    /**
     * Outputs a message (adds some paragraphs)
     */
    function message($msg, $status = 0) {
        $id = $this->plugin_id_str;
        $cls = empty($status) ? 'error fade' : 'success update'; // update is the WP class for success ?!?

        $str = <<<MSG_EOF
<div id='$id-notice' class='$cls'><p><strong>$msg</strong></p></div>
MSG_EOF;
        return $str;
    }

    /**
     * a simple status message, no formatting except color
     */
    function msg($msg, $status = 0, $use_inline_css = 0) {
        $inline_css = '';
        $id = $this->plugin_id_str;
        $cls = empty($status) ? 'app_error' : 'app_success';

        if ($use_inline_css) {
            $inline_css = empty($status) ? 'background-color:red;' : 'background-color:green;';
            $inline_css .= 'text-align:center;margin-left: auto; margin-right:auto; padding-bottom:10px;color:white;';
        }

        $str = <<<MSG_EOF
<div id='$id-notice' class='$cls' style="$inline_css"><strong>$msg</strong></div>
MSG_EOF;
        return $str;
    }

    /**
     * a simple status message, no formatting except color, simpler than its brothers
     */
    function m($msg, $status = 0, $use_inline_css = 0) {
        $cls = empty($status) ? 'app_error' : 'app_success';
        $inline_css = '';

        if ($use_inline_css) {
            $inline_css = empty($status) ? 'color:red;' : 'color:green;';
            $inline_css .= 'text-align:center;margin-left: auto; margin-right: auto;';
        }

        $str = <<<MSG_EOF
<span class='$cls' style="$inline_css">$msg</span>
MSG_EOF;
        return $str;
    }

    /**
     * Loads a product by its ID or by hash
     *
     * @param int/string $id
     * @return array
     */
    function get_product($id = null) {
        global $wpdb;
        $prev_rec = array();

        if (empty($id)) {
            // do nothing
        } elseif (is_numeric($id)) {
            $prev_rec = $wpdb->get_row("SELECT * FROM {$this->plugin_db_prefix}products WHERE id = " . $wpdb->escape($id), ARRAY_A);
        } else {
            $prev_rec = $wpdb->get_row("SELECT * FROM {$this->plugin_db_prefix}products WHERE hash = '" . $wpdb->escape($id) . "'", ARRAY_A);
        }

        return $prev_rec;
    }

    private $errors = array();

    /**
     * accumulates error messages
     * @param array $err
     * @return void
     */
    function add_error($err) {
        return $this->errors[] = $err;
    }

    /**
     * @return array
     */
    function get_errors() {
        return $this->errors;
    }

    function get_errors_str() {
        $str  = join("<br/>", $this->get_errors());
        return $str;
    }

    /**
     *
     * @return bool
     */
    function has_errors() {
        return !empty($this->errors) ? 1 : 0;
    }

    /**
     * Adds or updates a product
     *
     * @param array $data
     * @return bool 1 ok add; 0 error (permissions?)
     */
    function admin_product($data = array(), $id = null) {
        global $wpdb;
        $st = 0;
        $prev_rec = array();

        $id = WebWeb_WP_DigiShopUtil::stop_bad_input($id, WebWeb_WP_DigiShopUtil::SANITIZE_NUMERIC);

        if (empty($data['label'])) {
            $this->add_error("Product name cannot be empty.");
        }

        $data['price'] = preg_replace('#[$\,\s]#', '', $data['price']);

        if (empty($data['price'])) {
            //$this->add_error("Product price cannot be empty.");
        }

        $ext_link = empty($data['ext_link']) ? '' : trim($data['ext_link']);

        if (!$this->has_errors()) {
            // add product
            if (!empty($id)) {
                $prev_rec = $this->get_product($id);
            }

            // TODO Sanitize vars
            $product_data['label'] = $data['label'];
            $product_data['price'] = trim($data['price'], ' $');
            $product_data['active'] = empty($data['active']) ? 0 : 1;
            $product_data['added_on'] = empty($prev_rec['added_on']) ? current_time('mysql') : $prev_rec['added_on'];

            // upload
            if (!empty($_FILES['file']) && $_FILES['file']['error'] == UPLOAD_ERR_OK) {
                $target_file = $_FILES['file']['name'];
                $target_file = basename($target_file);
                $target_file = WebWeb_WP_DigiShopUtil::sanitizeFile($target_file);

                if (!is_dir($this->plugin_uploads_dir) && @mkdir($this->plugin_uploads_dir, 0777, 1)) {
                    $buffer = 'deny from all';
                    WebWeb_WP_DigiShopUtil::write($this->plugin_uploads_dir . '.htaccess', $buffer);
                }

                // if a new file is supplied the old gets deleted.
                if (!empty($prev_rec['file']) && file_exists($this->plugin_uploads_dir . $prev_rec['file'])) {
                    unlink($this->plugin_uploads_dir . $prev_rec['file']);
                }

                $target_file_full = $this->plugin_uploads_dir . $target_file;

                // if the file exists append number (timestamp) before the extension
                while (file_exists($target_file_full)) {
                    $target_file_full = preg_replace('#(\.\w{2,5})$#si', '-sss' . time() . '\\1', $target_file_full);
                    $target_file = basename($target_file_full); // let's update the name.
                }

                // on windows move_uploaded_file could fail for some weird reasons.
                if (!move_uploaded_file($_FILES['file']['tmp_name'], $target_file_full)
                        || !copy($_FILES['file']['tmp_name'], $target_file_full)) {
                   chmod($target_file_full, 0644);
                   $this->add_error("Cannot save the file in [$target_file_full]");
                }

                $product_data['hash'] = WebWeb_WP_DigiShopUtil::generate_hash($target_file);

                // add file name and not the full because people can switch hostings
                $product_data['file'] = $target_file;
            }
            // external SRC
            elseif (!empty($ext_link) && WebWeb_WP_DigiShopUtil::validate_url($ext_link)) {
                $product_data['hash'] = WebWeb_WP_DigiShopUtil::generate_hash($ext_link);
                $product_data['file'] = $ext_link;
            }

            if (empty($id)) {
                $st = $wpdb->insert($this->plugin_db_prefix . 'products', $product_data);
            } else {
                $st = $wpdb->update($this->plugin_db_prefix . 'products', $product_data, array('id' => $id));
                // if it's error the status will be false, otherwise it's affected rows which could be 0 if we are just updating the file name.
                $st = $st === false ? false : true;
            }
        }

        return $st;
    }

    /**
     * deletes a product by in
     *
     * @param int $id
     * @return bool 1 ok; 0 error (when saving)
     */
    function delete_product($id = -1) {
        global $wpdb;

        $id = WebWeb_WP_DigiShopUtil::stop_bad_input($id, WebWeb_WP_DigiShopUtil::SANITIZE_NUMERIC);
        $prev_rec = $this->get_product($id);

        // if a new file is supplied the old gets deleted.
        if (!empty($prev_rec['file']) && file_exists($this->plugin_uploads_dir . $prev_rec['file'])) {
            unlink($this->plugin_uploads_dir . $prev_rec['file']);
        }

        $st = $wpdb->query("DELETE FROM {$this->plugin_db_prefix}products WHERE id = " . $wpdb->escape($id));

        return $st;
    }

    /**
     * deletes a product by in
     *
     * @param int $id
     * @return bool 1 ok; 0 error (when saving)
     */
    function get_products() {
        global $wpdb;
        $data = array();
        $data = $wpdb->get_results("SELECT * FROM {$this->plugin_db_prefix}products", ARRAY_A);

        return $data;
    }
}

class WebWeb_WP_DigiShopUtil {
    // options for read/write methods.
    const FILE_APPEND = 1;
    const UNSERIALIZE_DATA = 2;
    const SERIALIZE_DATA = 3;

    /**
     * Checks if we are on a page that belongs to our plugin.
     * It is really annoying to see a notice in every section of WordPress.
     * That way the notice will be shown only on the plugin's page.
     */
    public static function is_on_plugin_page() {
        $webweb_wp_digishop_obj = WebWeb_WP_DigiShop::get_instance();

        $req_uri = $_SERVER['REQUEST_URI'];
        $id_str = $webweb_wp_digishop_obj->get('plugin_id_str');

        $req_uri = str_replace('_', '-', $req_uri);
        $id_str = str_replace('_', '-', $id_str);

        // because the plugin id str and uri can have dashes or underscore we'll make underscore dashes
        // for both req uri and plugin str id
        $stat = preg_match('#' . preg_quote($id_str) . '#si', $req_uri);

        return $stat;
    }

    /**
     * Replaces the template variables
     * @param string buffer to operate on
     * @param array the keys are uppercased and surrounded by %%KEY_NAME%%
     * @return string modified data
     */
    public static function replace_vars($buffer, $params = array()) {
        foreach ($params as $key => $value) {
            $key = trim($key, '%');
            $key = strtoupper($key);
            $key = '%%' . $key . '%%';

            $buffer = str_ireplace($key, $value, $buffer);
        }
//        var_dump($params);
        // Let's check if there are unreplaced variables
        if (preg_match('#(%%[\w-]+%%)#si', $buffer, $matches)) {
//            trigger_error("Not all template variables were replaced. Please check the missing and add them to the input params." . join(",", $matches[1]), E_USER_WARNING);
            trigger_error("Not all template variables were replaced. Please check the missing and add them to the input params." . var_export($matches, 1), E_USER_WARNING);
        }

        return $buffer;
    }

    /**
     * Checks if the url is valid
     * @param string $url
     */
    public static function validate_url($url = '') {
        $status = preg_match("@^(?:ht|f)tps?://@si", $url);

        return $status;
    }

    /**
     *
     * @param string $buffer
     */
    public static function sanitizeFile($str = '', $lowercase = 0, $sep = '-') {
        $str = urldecode($str);
        $ext = @end(explode('.', $str));

        if (function_exists('iconv')) {
            $src    = "UTF-8";
            // If you append the string //TRANSLIT to out_charset  transliteration is activated.
            $target = "ISO-8859-1//TRANSLIT";
            $str = iconv($src, $target, $str);
        }

        $ext = preg_replace('#[^a-z\d]+#', '', $ext);
        $ext = strtolower($ext);

        $str = preg_replace('#\.\w{2,5}$#si', '', $str); // remove ext
        $str = preg_replace('#[^\w\-]+#', $sep, $str);
        $str = preg_replace('#[\s\-\_]+#', $sep, $str);
        $str = trim($str, ' /\\ -_');

        // If there are non-english characters they will be replaced with entities which we'll use
        // as guideline to find the equivalent in English.
        $str = htmlentities($str);

        // non-enlgish -> english equivalent
        $str = preg_replace('/&([a-z][ez]?)(?:acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig);/si', '\\1', $str);

        // remove any unrecognized entities
        $str = preg_replace('/&([a-z]+);/', '', $str);

        // remove any unrecognized entities
        $str = preg_replace('@\&\#\d+;@s', '', $str);

        if ($lowercase) {
            $str = strtolower($str);
        }

        // There are creazy people that may enter longer link :)
        $str = substr($str, 0, 200);

        if (empty($str)) {
            $str = 'default-name-' . time();
        }

        if (empty($ext)) {
            $ext = 'default-ext';
        }

        $str .= '.' . $ext;

        return $str;
    }

    /**
     * Generates the hash + salt
     *
     * @param type $input_str
     * @return string
     */
    public static function generate_hash($input_str = '') {
        $webweb_wp_digishop_obj = WebWeb_WP_DigiShop::get_instance();

        $res = sha1($input_str . $_SERVER['HTTP_HOST'] . '-' . $webweb_wp_digishop_obj->get('plugin_id_str'));

        return $res;
    }

    const SANITIZE_NUMERIC = 1;
    const SANITIZE_ALPHA_NUMERIC = 2;

    /**
     * Initially this was planned to be a function to clean the IDs. Not it stops when invalid input is found.
     *
     * @param string $value
     * @return string
     */
    public static function stop_bad_input($value = '', $type_id = self::SANITIZE_NUMERIC) {
        if (!empty($value)) {
            $msg = '';
            $webweb_wp_digishop_obj = WebWeb_WP_DigiShop::get_instance();

            if ($type_id == self::SANITIZE_NUMERIC && !is_numeric($value)) {
                $webweb_wp_digishop_obj->log("Invalid value supplied. Received: \n----------------------------------------------------\n"
                            . $value
                            . "\n----------------------------------------------------\n");
                $msg = $webweb_wp_digishop_obj->get('plugin_id_str') . ': Received invalid input. <!-- r: n -->';
            } elseif ($type_id == self::SANITIZE_ALPHA_NUMERIC && !preg_match('#^[\w-]+$#si', $value)) { // alphanum from start to end + dash
                $webweb_wp_digishop_obj->log("Invalid value supplied. Received: \n----------------------------------------------------\n"
                            . $value
                            . "\n----------------------------------------------------\n");
                $msg = $webweb_wp_digishop_obj->get('plugin_id_str') . ': Received invalid input. <!-- r: an -->';
            }

            if (!empty($msg)) {
                $msg = $webweb_wp_digishop_obj->m($msg, 0, 1) . $webweb_wp_digishop_obj->add_plugin_credits();
                wp_die($msg);
            }
        }

        return $value;
    }

    /**
     * Serves the file for download. Forces the browser to show Save as and not open the file in the browser.
     * Makes the script run for 12h just in case and after the file is sent the script stops.
     *
     * Credits:
	 * http://php.net/manual/en/function.readfile.php
     * http://stackoverflow.com/questions/2222955/idiot-proof-cross-browser-force-download-in-php
     *
     * @param string $file
     * @param bool $do_exit - exit after the file has been downloaded.
     */
    public static function download_file($file, $do_exit = 1) {
        set_time_limit(12 * 3600); // 12 hours

        if (ini_get('zlib.output_compression')) {
            @ini_set('zlib.output_compression', 0);

            if (function_exists('apache_setenv')) {
                @apache_setenv('no-gzip', 1);
            }
        }

        if (!empty($_SERVER['HTTPS'])
                && ($_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443)) {
            header("Cache-control: private");
            header('Pragma: private');

            // IE 6.0 fix for SSL
            // SRC http://ca3.php.net/header
            // Brandon K [ brandonkirsch uses gmail ] 25-Apr-2007 03:34
            header('Cache-Control: maxage=3600'); //Adjust maxage appropriately
        } else {
            header('Pragma: public');
        }

        // the actual file that will be downloaded
        $download_file_name = basename($file);

        // if a file with the same name existed we've appended some numbers to the filename but before
        // the extension. Now we'll offer the file without the appended numbers.
        $download_file_name = preg_replace('#-sss\d+(\.\w{2,5})$#si', '\\1', $download_file_name);

        $default_content_type = 'application/octet-stream';

        $ext = end(explode('.', $download_file_name));
        $ext = strtolower($ext);

        // http://en.wikipedia.org/wiki/Internet_media_type
        $content_types_array = array(
            'pdf' => 'application/pdf',
            'zip' => 'application/zip',
            'gz' => 'application/x-gzip',
            'doc' => 'application/msword',
            'xls' => 'application/vnd.xls',
            'ppt' => 'application/vnd.ms-powerpoint',
        );

        $content_type = empty($content_types_array[$ext]) ? $default_content_type : $content_types_array[$ext];

		header('Expires: 0');
 		header('Content-Description: File Transfer');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Type: ' . $content_type);
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . (string) (filesize($file)));
        header('Content-Disposition: attachment; filename="' . $download_file_name . '"');

		ob_clean();
		flush();

        readfile($file);

		if ($do_exit) {
			exit;
		}
    }

    /**
     * Gets the content from the body, removes the comments, scripts
     * Credits: http://php.net/manual/en/function.strip-tags.phpm /  http://networking.ringofsaturn.com/Web/removetags.php
     * @param string $buffer
     * @string string $buffer
     */

    public static function html2text($buffer = '') {
        // we care only about the body so it must be beautiful.
        $buffer = preg_replace('#.*<body[^>]*>(.*?)</body>.*#si', '\\1', $buffer);
        $buffer = preg_replace('#<script[^>]*>.*?</script>#si', '', $buffer);
        $buffer = preg_replace('#<style[^>]*>.*?</style>#siU', '', $buffer);
//        $buffer = preg_replace('@<style[^>]*>.*?</style>@siU', '', $buffer); // Strip style tags properly
        $buffer = preg_replace('#<[a-zA-Z\/][^>]*>#si', ' ', $buffer); // Strip out HTML tags  OR '@<[\/\!]*?[^<>]*\>@si',
        $buffer = preg_replace('@<![\s\S]*?--[ \t\n\r]*>@', '', $buffer); // Strip multi-line comments including CDATA
        $buffer = preg_replace('#[\t\ ]+#si', ' ', $buffer); // replace just one space
        $buffer = preg_replace('#[\n\r]+#si', "\n", $buffer); // replace just one space
        //$buffer = preg_replace('#(\s)+#si', '\\1', $buffer); // replace just one space
        $buffer = preg_replace('#^\s*|\s*$#si', '', $buffer);

        return $buffer;
    }

    /**
     * Gets the content from the body, removes the comments, scripts
     *
     * @param string $buffer
     * @param array $keywords
     * @return array - for now it returns hits; there could be some more complicated results in the future so it's better as an array
     */
    public static function match($buffer = '', $keywords = array()) {
        $status_arr['hits'] = 0;

        foreach ($keywords as $keyword) {
            $cnt = preg_match('#\b' . preg_quote($keyword) . '\b#si', $buffer);

            if ($cnt) {
                $status_arr['hits']++; // total hits
                $status_arr['matches'][$keyword] = array('keyword' => $keyword, 'hits' => $cnt,); // kwd hits
            }
        }

        return $status_arr;
    }

    /**
     * @desc write function using flock
     *
     * @param string $vars
     * @param string $buffer
     * @param int $append
     * @return bool
     */
    public static function write($file, $buffer = '', $option = null) {
        $buff = false;
        $tries = 0;
        $handle = '';

        $write_mod = 'wb';

        if ($option == self::SERIALIZE_DATA) {
            $buffer = serialize($buffer);
        } elseif ($option == self::FILE_APPEND) {
            $write_mod = 'ab';
        }

        if (($handle = @fopen($file, $write_mod))
                && flock($handle, LOCK_EX)) {
            // lock obtained
            if (fwrite($handle, $buffer) !== false) {
                @fclose($handle);
                return true;
            }
        }

        return false;
    }

    /**
     * @desc read function using flock
     *
     * @param string $vars
     * @param string $buffer
     * @param int $option whether to unserialize the data
     * @return mixed : string/data struct
     */
    public static function read($file, $option = null) {
        $buff = false;
        $read_mod = "rb";
        $handle = false;

        if (($handle = @fopen($file, $read_mod))
                && (flock($handle, LOCK_EX))) { //  | LOCK_NB - let's block; we want everything saved
            $buff = @fread($handle, filesize($file));
            @fclose($handle);
        }

        if ($option == self::UNSERIALIZE_DATA) {
            $buff = unserialize($buff);
        }

        return $buff;
    }

    /**
     *
     * Appends a parameter to an url; uses '?' or '&'
     * It's the reverse of parse_str().
     *
     * @param string $url
     * @param array $params
     * @return string
     */
    public static function add_url_params($url, $params = array()) {
        $str = '';

        $params = (array) $params;

        if (empty($params)) {
            return $url;
        }

        $query_start = (strpos($url, '?') === false) ? '?' : '&';

        foreach ($params as $key => $value) {
            $str .= ( strlen($str) < 1) ? $query_start : '&';
            $str .= rawurlencode($key) . '=' . rawurlencode($value);
        }

        $str = $url . $str;

        return $str;
    }

    // generates HTML select
    public static function html_select($name = '', $sel = null, $options = array(), $attr = '') {
        $html = "\n" . '<select name="' . $name . '" id="' . $name . '" ' . $attr . '>' . "\n";

        foreach ($options as $key => $label) {
            $selected = $sel == $key ? ' selected="selected"' : '';
            $html .= "\t<option value='$key' $selected>$label</option>\n";
        }

        $html .= '</select>';
        $html .= "\n";

        return $html;
    }

    // generates status msg
    public static function msg($msg = '', $status = 0) {
        $cls = empty($status) ? 'error' : 'success';
        $cls = $status == 2 ? 'notice' : $cls;

        $msg = "<p class='status_wrapper'><div class=\"status_msg $cls\">$msg</div></p>";

        return $msg;
    }

    /**
     * checks several variables and returns the lowest.
     * @see http://www.kavoir.com/2010/02/php-get-the-file-uploading-limit-max-file-size-allowed-to-upload.html
     * @return int
     */
    public static function get_max_upload_size() {
        $max_upload = (int)(ini_get('upload_max_filesize'));
        $max_post = (int)(ini_get('post_max_size'));
        $memory_limit = (int)(ini_get('memory_limit'));

        $upload_mb = min($max_upload, $max_post, $memory_limit);

        return $upload_mb;
    }

    /**
     * proto str formatFileSize( int $size )
     *
     * @param string
     * @return string 1 KB/ MB
     */
    public static function format_file_size($size) {
    	$size_suff = 'Bytes';

        if ($size > 1024 ) {
            $size /= 1024;
            $size_suff = 'KB';
        }

        if ( $size > 1024 ) {
            $size /= 1024;
            $size_suff = 'MB';
        }

        if ( $size > 1024 ) {
            $size /= 1024;
            $size_suff = 'GB';
        }

        if ( $size > 1024 ) {
            $size /= 1024;
            $size_suff = 'TB';
        }

        $size = number_format($size, 2);

        return $size . " $size_suff";
    }

	function verify_paypal($data = array(), $sandbox = 0) {
		$res = '';
		$obj = WebWeb_WP_DigiShop::get_instance();

		$req = 'cmd=_notify-validate';

		foreach ($data as $key => $value) {
			$value = urlencode(stripslashes($value));
			$req .= "&$key=$value";
		}

		if ($sandbox) {
			$host = 'www.sandbox.paypal.com';
		} else {
			$host = 'www.paypal.com';
		}

		// post back to PayPal system to validate
		$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";

		// If testing on Sandbox use:
		$header .= "Host: $host:443\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";

		$fp = fsockopen ("ssl://$host", 443, $errno, $errstr, 30);

		$obj->log(__METHOD__ . " : TXN (0): host: [$host]; header: [$header], req: [$req]");

		if (!$fp) {
			$obj->log(__METHOD__ . " : Didn't succeed on first attempt: $errno, $errstr");
			$fp = fsockopen ("ssl://$host", 443, $errno, $errstr, 45);
		}

		if (!$fp) {
			$obj->log(__METHOD__ . " : TXN (2): Cannot connect: $errno, $errstr");
			return false;
		} else {
			fputs($fp, $header . $req);

			while (!feof($fp)) {
				$res .= fgets($fp, 2048);

				// next layer will check
				/*if (trim($res) ==  "VERIFIED") {
					$obj->log(__METHOD__ . " : TXN (buff): Success. Content: $res");

				}*/
			}

			fclose($fp);

			$obj->log(__METHOD__ . " : TXN (buff): Content: [$res]");

			return $res;
		}

		$obj->log(__METHOD__ . " : TXN (3): Error");

		return $res;
	}
}

class WebWeb_WP_DigiShopCrawler {

    private $user_agent = "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0) Gecko/20100101 Firefox/6.0";
    private $error = null;
    private $buffer = null;

    function __construct() {
        ini_set('user_agent', $this->user_agent);
    }

    /**
     * Error(s) from the last request
     *
     * @return string
     */
    function getError() {
        return $this->error;
    }

    // checks if buffer is gzip encoded
    function isGziped($buffer) {
        return (strcmp(substr($buffer, 0, 8), "\x1f\x8b\x08\x00\x00\x00\x00\x00") === 0) ? true : false;
    }

    /*
      henryk at ploetzli dot ch
      15-Feb-2002 04:28
      http://php.online.bg/manual/hu/function.gzencode.php
     */

    function gzDecode($string) {
        if (!function_exists('gzinflate')) {
            return false;
        }

        $string = substr($string, 10);
        return gzinflate($string);
    }

    /**
     * Fetches a url and saves the data into an instance variable. The returned status is whether the request was successful.
     *
     * @param string $url
     * @return bool
     */
    function fetch($url) {
        $ok = 0;
        $buffer = '';

        $url = trim($url);

        if (!preg_match("@^(?:ht|f)tps?://@si", $url)) {
            $url = "http://" . $url;
        }

        // try #1 cURL
        // http://fr.php.net/manual/en/function.fopen.php
        if (empty($ok)) {
            if (function_exists("curl_init") && extension_loaded('curl')) {
                $ch = curl_init();

                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HEADER, 0);
//                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept-Encoding: gzip'));
                curl_setopt($ch, CURLOPT_TIMEOUT, 90);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($ch, CURLOPT_MAXREDIRS, 5); /* Max redirection to follow */
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

                /* curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC ) ; // in the future pwd protected dirs
                  curl_setopt($ch, CURLOPT_USERPWD, "username:password"); */ //  http://php.net/manual/en/function.curl-setopt.php

                $string = curl_exec($ch);
                $curl_res = curl_error($ch);

                curl_close($ch);

                if (empty($curl_res) && strlen($string)) {
                    if ($this->isGziped($string)) {
                        $string = $this->gzDecode($string);
                    }

                    $this->buffer = $string;

                    return 1;
                } else {
                    $this->error = $curl_res;
                    return 0;
                }
            } // no curl
        } // empty ok*/

        // try #2 file_get_contents
        if (empty($ok)) {
            $buffer = @file_get_contents($url);

            if (!empty($buffer)) {
                $this->buffer = $buffer;
                return 1;
            }
        }

        // try #3 fopen
        if (empty($ok) && preg_match("@1|on@si", ini_get("allow_url_fopen"))) {
            $fp = @fopen($url, "r");

            if (!empty($fp)) {
                $in = '';

                while (!feof($fp)) {
                    $in .= fgets($fp, 8192);
                }

                @fclose($fp);
                $buffer = $in;

                if (!empty($buffer)) {
                    $this->buffer = $buffer;
                    return 1;
                }
            }
        }

        return 0;
    }

    function get_content() {
        return $this->buffer;
    }
}

/**
 * A nice shorting class based on Ryan Charmley's suggestion see the link on stackoverflow below.
 * @author Svetoslav Marinov (Slavi) | http://WebWeb.ca
 * @see https://github.com/lordspace/
 * @see http://stackoverflow.com/questions/742013/how-to-code-a-url-shortener/10386945#10386945
 */
class WebWeb_WP_DigiShop_Shorty {
    /**
     * Explicitely omitted: i, o, 1, 0 because they are confusing. Also use only lowercase ... as
     * dictating this over the phone might be tough.
     * @var string
     */
    private $dictionary = "abcdfghjklmnpqrstvwxyz23456789";
    private $dictionary_array = array();

    public function __construct() {
        $this->dictionary_array = str_split($this->dictionary);
    }

    /**
     * Gets ID and converts it into a string.
     * @param int $id
     */
    public function encode($id) {
        $str_id = '';
        $base = count($this->dictionary_array);

        while ($id > 0) {
            $rem = $id % $base;
            $id = ($id - $rem) / $base;
            $str_id .= $this->dictionary_array[$rem];
        }

        return $str_id;
    }

    /**
     * Converts /abc into an integer ID
     * @param string
     * @return int $id
     */
    public function decode($str_id) {
        $id = 0;
        $id_ar = str_split($str_id);
        $base = count($this->dictionary_array);

        for ($i = count($id_ar); $i > 0; $i--) {
            $id += array_search($id_ar[$i - 1], $this->dictionary_array) * pow($base, $i - 1);
        }

        return $id;
    }
}
