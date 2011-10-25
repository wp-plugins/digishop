<?php

/*
  Plugin Name: DigiShop
  Plugin URI: http://webweb.ca/site/products/digishop/
  Description: DigiShop plugin allows you to start selling your digital products such as e-books, reports in minutes.
  Version: 1.0.0
  Author: Svetoslav Marinov (Slavi)
  Author URI: http://WebWeb.ca
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
    private $log = 1;
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
    private $plugin_business_email = 'billing@WebWeb.ca'; // used for paypal payments
    private $plugin_business_ipn = 'http://webweb.ca/wp/hosted/payment/ipn.php'; // used for paypal IPN payments
    //private $plugin_business_status_url = 'http://localhost/wp/hosted/payment/status.php'; // used after paypal TXN to to avoid warning of non-ssl return urls
    private $plugin_business_status_url = 'https://secure.webweb.ca/webweb.ca/wp/hosted/payment/status.php'; // used after paypal TXN to to avoid warning of non-ssl return urls
    private $plugin_support_email = 'help@WebWeb.ca'; //
    private $plugin_support_link = 'http://miniads.ca/widgets/contact/profile/digishop?height=200&width=500&description=Please enter your enquiry below.'; //
    private $plugin_admin_url_prefix = null; // filled in later
    private $plugin_home_page = 'http://webweb.ca/site/products/digishop/';
    private $plugin_tinymce_name = 'wwwpdigishop'; // if you change it update the tinymce/editor_plugin.js and reminify the .min.js file.
    private $plugin_cron_hook = __CLASS__;
    private $db_version = '1.0';
    private $plugin_cron_freq = 'daily';
    private $plugin_default_opts = array(
        'status' => 0,
        'test_mode' => 0,
        'business_email' => '',
        'purchase_subject' => 'Download Link',
        'purchase_content' => "Dear %%FIRST_NAME%%,\n\nThank you for your order.\nTransaction: %%TXN_ID%%\nHere is the download link: %%DOWNLOAD_LINK%%\n\nRegards,\n%%SITE%% team",
        'currency' => 'USD',
        'purchase_thanks' => 'Thanks. The payment is being processing now. You should receive an email very soon.',
        'purchase_error' => 'There was a problem with the payment.',
    );

	private $app_title = 'Start selling your digital products (e-books, music, reports) within minutes!';
	private $plugin_description = 'Allows you to start selling your digital products such as e-books, reports in minutes.';

    private $plugin_uploads_path = null; // E.g. /wp-content/uploads/PLUGIN_ID_STR/
    private $plugin_uploads_url = null; // E.g. http://yourdomain/wp-content/uploads/PLUGIN_ID_STR/
    private $plugin_uploads_dir = null; // E.g. DOC_ROOT/wp-content/uploads/PLUGIN_ID_STR/

    // can't be instantiated; just using get_instance
    private function __construct() {
        
    }

    /**
     * handles the singleton
     */
    function get_instance() {
		if (is_null(self::$instance)) {
            global $wpdb;
            
			$cls = __CLASS__;	
			$inst = new $cls;
			
			$site_url = get_settings('siteurl');

			$inst->site_url = $site_url; // e.g. wp-command-center; this can change e.g. a 123 can be appended if such folder exist
			$inst->plugin_dir_name = basename(dirname(__FILE__)); // e.g. wp-command-center; this can change e.g. a 123 can be appended if such folder exist
			$inst->plugin_data_dir = dirname(__FILE__) . '/data';
			$inst->plugin_url = $site_url . '/wp-content/plugins/' . $inst->plugin_dir_name . '/';
			$inst->plugin_settings_key = $inst->plugin_id_str . '_settings';			
            $inst->plugin_support_link .= '&css_file=' . urlencode(get_bloginfo('stylesheet_url'));
            $inst->plugin_admin_url_prefix = $site_url . '/wp-admin/admin.php?page=' . $inst->plugin_dir_name;
		
            $inst->delete_product_url = $inst->plugin_admin_url_prefix . '/menu.products.php&do=delete';
			$inst->add_product_url = $inst->plugin_admin_url_prefix . '/menu.product.add.php';
			$inst->edit_product_url = $inst->plugin_admin_url_prefix . '/menu.product.add.php';		
		
            // where digital products will be saved.
            $inst->plugin_uploads_path = '/wp-content/uploads/' . $inst->plugin_id_str . '/';
            $inst->plugin_uploads_url = $site_url . $inst->plugin_uploads_path;
            $inst->plugin_uploads_dir = ABSPATH . ltrim($inst->plugin_uploads_path, '/');

            // will be retrieved later by ->get method calls
            $inst->plugin_db_prefix = $wpdb->prefix . $inst->plugin_id_str . '_';
            $inst->payment_trigger_key = $inst->plugin_id_str . '_ipn';
            $inst->payment_notify_url = WebWeb_WP_DigiShopUtil::add_url_params($site_url, array($inst->payment_trigger_key => 1));

			define('WEBWEB_WP_DIGISHOP_BASE_DIR', dirname(__FILE__)); // e.g. // htdocs/wordpress/wp-content/plugins/wp-command-center
			define('WEBWEB_WP_DIGISHOP_DIR_NAME', $inst->plugin_dir_name);

			if ($inst->log) {
				ini_set('log_errors', 1);
				ini_set('error_log', $inst->plugin_data_dir . '/error.log');
			}

			add_action('plugins_loaded', array($inst, 'init'), 100);

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
            
            wp_register_style($this->plugin_dir_name, $this->plugin_url . 'css/main.css', false, 0.1);
            wp_enqueue_style($this->plugin_dir_name);


        } else {
            if (!is_feed()) {
                // Runs after WordPress has finished loading but before any headers are sent. Useful for intercepting $_GET or $_POST triggers.
                // http://adambrown.info/p/wp_hooks/hook/init
                add_action('init', array($this, 'handle_non_ui'), 1);
                
                add_action('wp_head', array($this, 'add_plugin_credits'), 1); // be the first in the header
                add_action('wp_footer', array($this, 'add_plugin_credits'), 1000); // be the last in the footer
                wp_enqueue_script('jquery');

                // The short code is has a closing *tag* e.g. [tag]...[/tag] so normal tag partse won't work
                add_shortcode($this->plugin_id_str, array($this, 'parse_short_code'));
                //add_filter('the_content', array($this, 'parse_short_code'), 10000); // run last to check fb container and other stuff are added
            }
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

        $id = empty($attr['id']) ? 0 : $attr['id'];

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
            $paypal_url = str_replace('paypal.com', 'sandbox.paypal.com', $paypal_url);
        }

        $email = $opts['business_email'];
        $notify_url = $this->payment_notify_url;
        $currency = $opts['currency'];		
        $price = $prev_rec['price'];
        
        $return_page = WebWeb_WP_DigiShopUtil::add_url_params($post_url, array($this->plugin_id_str . '_txn_ok' => 1));
        $cancel_return = WebWeb_WP_DigiShopUtil::add_url_params($post_url, array($this->plugin_id_str . '_txn_error' => 1));
        
        $item_name = esc_attr($prev_rec['label']);
        $item_number = $prev_rec['id'];
        $price = sprintf("%01.2f", $price);

        $custom = http_build_query(array('id' => $item_number, 'site' => $this->site_url));

        $buffer = <<<SHORT_CODE_EOF
<!-- $this->plugin_id_str | Plugin URL: {$this->plugin_home_page} | Post URL: $post_url -->
<form action="$paypal_url" method="post" target="_blank">
            <input type='hidden' name="business" value="$email" />
            <input type="hidden" name="cmd" value="_xclick" />
            <input type='hidden' name="item_name" value="$item_name" />
            <input type='hidden' name="item_number" value="$item_number" />
            <input type='hidden' name="amount" value="$price" />
            <input type="hidden" name="no_shipping" value="1" />
            <input type="hidden" name="no_note" value="1" />
            <input type='hidden' name="currency_code" value="$currency" />
            <input type='hidden' name="notify_url" value="$notify_url" />
            <input type='hidden' name="return" value="$return_page" />
            <input type='hidden' name="cancel_return" value="$cancel_return" />
            <input type='hidden' name="custom" value="$custom" />
            <input type="image" src="https://www.paypal.com/en_GB/i/btn/btn_buynow_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online." />
</form>
<!-- /$this->plugin_id_str | Plugin URL: {$this->plugin_home_page} | Post URL: $post_url -->
SHORT_CODE_EOF;

        $extra_msg = '';
        
        if (!empty($_REQUEST[$this->plugin_id_str . '_txn_ok'])) {
            $extra_msg = $this->m("<br/>" . $opts['purchase_thanks'], 1, 1);
        } elseif (!empty($_REQUEST[$this->plugin_id_str . '_txn_error'])) {
            $extra_msg = $this->m("<br/>" . $opts['purchase_error'], 0, 1);
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
        add_options_page(__($this->plugin_name, "WEBWEB_WP_DIGISHOP"), __($this->plugin_name, "WEBWEB_WP_DIGISHOP"), 'manage_options', __FILE__, array($this, 'options'));

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
    public function generate_newsletter_box() {
        $file = WEBWEB_WP_DIGISHOP_BASE_DIR . '/zzz_newsletter_box.html';

        $buffer = WebWeb_WP_DigiShopUtil::read($file);

        wp_get_current_user();
        global $current_user;
        $user_email = $current_user->user_email;

        $replace_vars = array(
            '%%PLUGIN_URL%%' => $this->get('plugin_url'),
            '%%USER_EMAIL%%' => $user_email,
        );
        
        $buffer = str_replace(array_keys($replace_vars), array_values($replace_vars), $buffer);

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
            '%%AMOUNT%%' => '2.99',
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
            $settings_link = '<a href="options-general.php?page='
                    . dirname(plugin_basename(__FILE__)) . '/' . basename(__FILE__) . '">' . (__("Settings", "WEBWEB_WP_DIGISHOP")) . '</a>';
            array_unshift($links, $settings_link);
        }

        return $links;
    }

    /**
     * Downloads served when accessed via yourwpsite.com/?PLUGNI_dl=asflasfjlasjflajslkf124
     * Missing or inactive products are not served.
     */
    function handle_non_ui() {
        $paypal_key = $this->payment_trigger_key;
        $dl_key = $this->plugin_id_str . '_dl';
        $data = $_REQUEST;

        if (!empty($data[$dl_key])) {
            $product_rec = $this->get_product($data[$dl_key]);

            // TODO: limit the downloads by a counter
            if (empty($product_rec) || empty($product_rec['active'])) {
                die($this->m($this->plugin_id_str . ': Invalid download hash.', 0, 1)
                        . $this->add_plugin_credits());
            }

            $file = $this->plugin_uploads_dir . $product_rec['file'];
            
            WebWeb_WP_DigiShopUtil::download_file($file);
        } elseif (!empty($data[$paypal_key])) {
            $custom = $data['custom'];
            $paypal_data = array();
            parse_str($custom, $paypal_data);
            
            if (!empty($paypal_data['id'])) {
                $id = $paypal_data['id'];
            } else {
                //$id = $data['item_number'];
            }

            $product_rec = $this->get_product($id);
            
            // handle PayPal IPN calls
            $data['cmd'] = '_notify-validate';

            $paypal_url = 'https://www.paypal.com/cgi-bin/webscr';

            if (!empty($opts['test_mode'])) {
                $paypal_url = str_replace('paypal.com', 'sandbox.paypal.com', $paypal_url);
            }

            $paypal_url = WebWeb_WP_DigiShopUtil::add_url_params($paypal_url, $data);

            $ua = new WebWeb_WP_DigiShopCrawler();

            if ($ua->fetch($paypal_url)) {
                $buffer = $ua->get_content();

                $subject_prefix = empty($data['test_ipn']) ? '' : 'Test Txn: ';

                $headers = "From: {$_SERVER['HTTP_HOST']} Wordpress <wordpress@{$_SERVER['HTTP_HOST']}>\r\n";
                $opts = $this->get_options();

                $admin_email = get_option('admin_email');
                
                // TODO: insert order ?
                
                $email_buffer = $opts['purchase_content'];

                if (empty($email_buffer)) {
                    $email_buffer = $this->plugin_default_opts['purchase_content'];
                }

                $vars = array(
                    '%%FIRST_NAME%%' => $data['first_name'],
                    '%%LAST_NAME%%' => $data['last_name'],
                    '%%EMAIL%%' => $data['payer_email'],
                    '%%DOWNLOAD_LINK%%' => WebWeb_WP_DigiShopUtil::add_url_params($this->site_url, array($dl_key => $product_rec['hash'])),
                    '%%TXN_ID%%' => $data['txn_id'],
                    '%%SITE%%' => $this->site_url,
                );

                $email_buffer = str_ireplace(array_keys($vars), array_values($vars), $email_buffer);
                    
                if (stripos($buffer, 'VERIFIED') !== false) {
                    $headers .= "BCC: $admin_email\r\n";
                    wp_mail($opts['notification_email'], $subject_prefix . $opts['purchase_subject'], $email_buffer, $headers);
                } else {
                    $admin_email_buffer = "Dear Admin,\n\nThe following transaction didn't validate with PayPal\n\n";
                    $admin_email_buffer .= "When you resolve the issue forward this email to your client.\n";
                    $admin_email_buffer .= "\n=================================================================\n\n";
                    $admin_email_buffer .= $email_buffer;
                    $admin_email_buffer .= "\n\n=================================================================\n";
                    $admin_email_buffer .= "\nReceived Data: \n\n" . var_export($data, 1);

                    
                    wp_mail($admin_email, 'Unsuccessful Transaction', $admin_email_buffer, $headers);
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
        } elseif (!empty($opts['test_mode'])) {
            echo $this->message($this->plugin_name . " is currently in Sandbox mode. To accept real transactions please uncheck Sandbox mode from "
                    . "<a href='{$this->plugin_admin_url_prefix}/menu.settings.php'> {$this->plugin_name} &gt; Settings</a>");
        }
    }

    /**
     * Outputs a message (adds some paragraphs)
     */
    function message($msg, $status = 0) {
        $id = $this->plugin_id_str;
        $cls = empty($status) ? 'error fade' : 'success';

        $str = <<<MSG_EOF
<div id='$id-notice' class='$cls'><p><strong>$msg</strong></p></div>
MSG_EOF;
        return $str;
    }

    /**
     * a simple status message, no formatting except color
     */
    function msg($msg, $status = 0) {
        $id = $this->plugin_id_str;
        $cls = empty($status) ? 'app_error' : 'app_success';

        $str = <<<MSG_EOF
<div id='$id-notice' class='$cls'><strong>$msg</strong></div>
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

        if (empty($data['label'])) {
            $this->add_error("Product name cannot be empty.");
        }
        
        if (empty($data['price'])) {
            $this->add_error("Product price cannot be empty.");
        }

        if (!$this->has_errors()) {
            // add product
            if (!empty($id)) {
                $prev_rec = $this->get_product($id);
            }

            // TODO Sanitize vars
            $product_data['label'] = $data['label'];
            $product_data['price'] = $data['price'];
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

                if (!@copy($_FILES['file']['tmp_name'], $target_file_full)) {
                   $this->add_error("Cannot save the file in [$target_file_full]");
                }
                
                $product_data['hash'] = sha1($target_file);

                // add file name and not the full because people can switch hostings
                $product_data['file'] = $target_file;
            }

            if (empty($id)) {
                $st = $wpdb->insert($this->plugin_db_prefix . 'products', $product_data);
            } else {
                $st = $wpdb->update($this->plugin_db_prefix . 'products', $product_data, array('id' => $id));
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
     *
     * @param string $buffer
     */
    public static function sanitizeFile($str = '') {
        
        return $str;
    }

    /**
     * Serves the file for download. Forces the browser to show Save as and not open the file in the browser
     * @param string $file
     */
    public static function download_file($file) {
        $mm_type = "application/octet-stream";

        // IE 6.0 fix for SSL
        // SRC http://ca3.php.net/header
        // Brandon K [ brandonkirsch uses gmail ] 25-Apr-2007 03:34
        header('Cache-Control: maxage=3600'); //Adjust maxage appropriately
        header('Pragma: public');

        header("Cache-Control: public, must-revalidate");
        header("Pragma: hack");
        header("Content-Type: " . $mm_type);
        header("Content-Length: " . (string) (filesize($file)));
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        header("Content-Transfer-Encoding: binary");

        readfile($file);
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
        $tries = 0;
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
    function is_gziped($buffer) {
        return (strcmp(substr($buffer, 0, 8), "\x1f\x8b\x08\x00\x00\x00\x00\x00") === 0) ? true : false;
    }

    /*
      henryk at ploetzli dot ch
      15-Feb-2002 04:28
      http://php.online.bg/manual/hu/function.gzencode.php
     */

    function gzdecode($string) {
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
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept-Encoding: gzip'));
                curl_setopt($ch, CURLOPT_TIMEOUT, 45);
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
                    if ($this->is_gziped($string)) {
                        $string = $this->gzdecode($string);
                    }

                    $this->buffer = $string;

                    return 1;
                } else {
                    $this->error = $curl_res;
                    return 0;
                }
            }
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