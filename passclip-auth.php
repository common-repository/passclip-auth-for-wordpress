<?php
/*
Plugin Name: PassClip Auth for WordPress
Description: PassClip Auth will give you an easy and powerful authentication.
Version: 1.0.5
Author: Passlogy Co.,Ltd.
Author URI: https://www.passlogy.com/en/
Plugin URI: https://www.passclip.com/ja/pca/pca_for_wp/
Text Domain: passclip-auth-for-wordpress
Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


/**
 * PassClip Auth will give you an easy and powerful authentication.
 *
 * @package passclip-auth
 * @author naka(), oka(2017/5-)
 * @copyright Passlogy Co.,Ltd. 2017
 */
if (!defined('WP_CONTENT_URL'))
	define('WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
if (!defined('WP_CONTENT_DIR'))
	define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
if (!defined('WP_PLUGIN_URL') )
	define('WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins');
if (!defined('WP_PLUGIN_DIR') )
	define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins');


/**
 * Define for PCA.
 */
define('PCA_VERSION', '1.0.5');
define('PCA_TEXTDOMAIN', 'passclip-auth-for-wordpress');
define('PCA_OPTIONS_SETTING', 'pca_options_setting');
define('PCA_LANG_DIR', 'languages');

require_once('passclip-auth-login-form.php');
require_once('passclip-auth-user-edit.php');
require_once('passclip-auth-users.php');

require_once('passclip-auth-options.php');

require_once('passclip-auth-login.php');

if ( 'yes' == pca_options_get( PCA_OPTION_WIDGET ) ){
	require_once('class-passclip-auth-login-widget.php');
}

if ( pca_check_if_woocommerce_activated() ) {
	require_once('passclip-auth-for-woo.php');
}

/**
 * @package PassClip_Auth
 */
class PassClip_Auth {

	public static function passclip_auth_action_init() {
		add_filter('authenticate', 'pca_login_authenticate', 100, 3);
	}

} // class end
add_action('init', array('PassClip_Auth', 'passclip_auth_action_init'));


/**
 * This is the method that load the languages file
 */
function pca_filter_init(){
	//if our ja.mo file exists, use it. else search languages folder.
	$locale = is_admin() ? get_user_locale() : get_locale();
	if( ! load_textdomain( PCA_TEXTDOMAIN, WP_PLUGIN_DIR . '/' . dirname( plugin_basename(__FILE__) ) . '/' .PCA_LANG_DIR . '/' . PCA_TEXTDOMAIN  . '-' . $locale . '.mo' ) ){
		load_plugin_textdomain( PCA_TEXTDOMAIN, false, dirname( plugin_basename(__FILE__) ).'/'.PCA_LANG_DIR );
	}
}
add_filter( 'init', 'pca_filter_init' );


/**
 * This is the method that add the PCA options setting menu link to admin setting menu
 */
function pca_action_admin_menu() {

	add_menu_page(__('PassClip Auth', PCA_TEXTDOMAIN),
					 __('PassClip Auth', PCA_TEXTDOMAIN),
					 'manage_options',
					 basename(__FILE__),
					 null,
					 'dashicons-paperclip');

	add_submenu_page( basename(__FILE__),
					__('PassClip Auth Options', PCA_TEXTDOMAIN),
					__('PassClip Auth Options', PCA_TEXTDOMAIN),
					'manage_options',
					'pca_options_page',
					'pca_options_page');

	add_submenu_page( basename(__FILE__),
					__('Manage PCA Users', PCA_TEXTDOMAIN),
					__('Manage PCA Users', PCA_TEXTDOMAIN),
					'manage_options',
					'pca_manage_users',
					'pca_manage_users');

	add_submenu_page( basename(__FILE__),
					__('PCA Mail Settings', PCA_TEXTDOMAIN),
					__('PCA Mail Settings', PCA_TEXTDOMAIN),
					'manage_options',
					'pca_mail_setting_page',
					'pca_mail_setting_page');

}
add_action('admin_menu', 'pca_action_admin_menu');


/**
 * unset Title from submenu.
 */
function pca_admin_menu(){
	global $submenu;
	unset($submenu[basename(__FILE__)][0]);
}
add_action( 'admin_head', 'pca_admin_menu' );


/**
 * This is the method that callback form filter of plugin_action_links
 *
 * @param links
 *				the tag form filter
 * @param file
 *				the value for the tag
 * @return a link to PCA options setting
 */
function pca_filter_plugin_action_links( $links, $file ) {
	static $thisPlugin;

	if (!$thisPlugin) {
		$thisPlugin = plugin_basename( __FILE__ );
	}
	if ( $file == $thisPlugin ){
		$settings_link = '<a href="admin.php?page=pca_options_page">'.__('Settings').'</a>';
		array_unshift( $links, $settings_link );
	}
	return $links;
}
add_filter('plugin_action_links', 'pca_filter_plugin_action_links', 10, 2);


/**
 * This is the method is called when activate the plugin
 */
function pca_activation(){
	// Initialize the PCA ootions
	pca_options_init();
}
register_activation_hook( __FILE__, 'pca_activation' );


/**
 * called when uninstall.
 * delete options made by PCA
 */
function pca_uninstall(){
	//Delete PCA option.
	if ( ! delete_option( PCA_OPTIONS ) ){
	}
	//Delete user_meta added by plugin.
	if ( ! delete_metadata( 'user', '', 'made_by_pca', '', true ) ){

	}
	//Delete user_meta added by plugin.
	if ( ! delete_metadata( 'user', '', 'pca_login_code', '', true ) ){

	}
	if ( ! delete_metadata( 'user', '', 'pca_sent_passclipcode', '', true ) ){

	}
}
register_uninstall_hook (__FILE__, 'pca_uninstall');


/**
 * when a user is created, add a setting "show_admin_bar_front = no".
 * @param WP_User $user
 * @return string
 */
function pca_dont_show_adminbar( $user ){
	$user['show_admin_bar_front'] = 'no';
	return $user;
}
if ( 'yes' == pca_options_get( PCA_OPTION_DONT_SHOW_ADMIN_BAR ) ){
	add_filter( 'pca_create_user', 'pca_dont_show_adminbar' );
}


/**
 * Check if WooCommerce is active.
 */
function pca_check_if_woocommerce_activated(){
	if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		return true;
	}
	return false;
}

