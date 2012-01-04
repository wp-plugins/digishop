<?php 

if (!defined('WP_UNINSTALL_PLUGIN')) {
	exit();
}

$_ENV['WEBWEB_WP_DIGISHOP_TEST'] = 1;

require_once(dirname(__FILE__) . '/digishop.bootstrap.php');
require_once(dirname(__FILE__) . '/digishop.php');

$webweb_wp_digishop_obj = WebWeb_WP_DigiShop::get_instance();
$webweb_wp_digishop_obj->on_uninstall();
