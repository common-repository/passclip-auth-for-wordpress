<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * In login_form, show passclip code, the logo and enqueue css and scripts.
 */
function pca_make_login_form_for_woo(){
	$pca_options = pca_options_get();

	if ( ! empty( $pca_options[PCA_APP_SERVICE_ID] ) ){
		$pca_options_lostpassword = $pca_options[PCA_OPTION_HIDE_LOSTPASSWORD_LINK];

		pca_show_passclip_logo_and_mark( $pca_options, 50 );

		wp_enqueue_script('pca-login-form', PCA_JS_URL . '/pca-login-form.js');
		wp_localize_script('pca-login-form', 'pca_password_form', array( 'pca_password_id' => 'password',
																	'pca_lostpassword_url' => ( 'yes' == $pca_options_lostpassword )? wp_lostpassword_url() : '',
																	'pca_password_placeholder' => __( 'Enter the password showed by the slot for this site in passclip', PCA_TEXTDOMAIN) ));
		wp_enqueue_style('pca-login-form', PCA_CSS_URL . '/pca-login-form.css');
	}

}
add_action( 'woocommerce_login_form_end', 'pca_make_login_form_for_woo' );


/**
 * If woocommerce let user make username, this plugin let user to edit display_name.
 */
function pca_edit_display_name_form_for_woo(){

	if (  pca_options_get( PCA_APP_SERVICE_ID ) ){
		if( 'no' == get_option('woocommerce_registration_generate_username') ){
			$user = wp_get_current_user();
?>
			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="display_name"><?php _e( 'Display name publicly as', PCA_TEXTDOMAIN ) ?></label>
			<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="display_name" id="display_name" value="<?php echo esc_attr( $user-> display_name );?>" />
			</p>
<?php
		}
	}
}
add_action( 'woocommerce_edit_account_form_start', 'pca_edit_display_name_form_for_woo' );

/**
 * In the edit-account form, if current_user is made by PCA or loged in with Passclip, show this message.
 */
function pca_edit_account_form_for_woo(){
	if ( pca_options_get( PCA_APP_SERVICE_ID ) ){
		if ( get_user_meta( get_current_user_id(), 'made_by_pca', true ) ){
			_e('<p>This account is protected by <strong><font color="#fa0">PassClip Auth</font></strong>.</p><br>', PCA_TEXTDOMAIN);
		}
	}
}
add_action( 'woocommerce_edit_account_form', 'pca_edit_account_form_for_woo' );


/**
 * enqueue script and styles for edit_account form.
 * Hide password form and, disable email change.
 */
function pca_before_edit_account_form_for_woo(){

	if ( pca_options_get( PCA_APP_SERVICE_ID ) ){

		wp_enqueue_script('pca-edit-account-form-for-woo', PCA_JS_URL . '/pca-edit-account-form-for-woo.js');
		wp_localize_script('pca-edit-account-form-for-woo',
							'pca_made_by_pca',
							array( 'made_by_pca' => get_user_meta( get_current_user_id(), 'made_by_pca', true) ) );

		wp_enqueue_style( 'pca-edit-account-form-for-woo', PCA_CSS_URL . '/pca-edit-account-form-for-woo.css' );
		//print before the password form.
		wp_print_styles( 'pca-edit-account-form-for-woo' );
	}
}
add_action( 'woocommerce_before_edit_account_form', 'pca_before_edit_account_form_for_woo' );


/**
 * when login to woocommerce, it checks the email is exists.
 * However, our plugin want to authenticate the email though it do not exist in wp_db.
 *
 * @return boolean false
 */
function pca_dont_get_username_from_email(){
	if ( pca_options_get( PCA_APP_SERVICE_ID ) ){
		return false;
	}
}
add_filter( 'woocommerce_get_username_from_email', 'pca_dont_get_username_from_email' );


/**
 * As disabled email, unset account_email from required fields.
 *
 * @param array $args required_fields
 * @return array $args required_fields
 */
function pca_email_is_not_required( $args ){

	unset( $args['account_email'] );
	return $args;
}
add_filter( 'woocommerce_save_account_details_required_fields', 'pca_email_is_not_required' );


/**
 * When account edit, let user change display_name.
 * As disabled editting email, fill it with the existing email.
 *
 * @param WP_Error $error
 * @param WP_User $user
 */
function pca_edit_user_detail_for_woo( $error, $user ){
	if ( isset($_POST['display_name']) ){
		$user -> display_name = wc_clean( $_POST['display_name'] );
	}
	$_user = wp_get_current_user();
	$user -> user_email = $_user -> user_email;
}
add_action( 'woocommerce_save_account_details_errors', 'pca_edit_user_detail_for_woo', 10, 2 );


/**
 * @param string $bool 'yes' or 'no' (woocommerce_enable_myaccount_registration)
 * @return string if PCA activated and request is not for admin_page, return 'no' else return woocommerce_enable_myaccount_registration value.
 */
function pca_override_woo_settings_registration( $bool ){
	if ( ! is_admin() && pca_options_get( PCA_APP_SERVICE_ID ) ){
		return 'no';
	}
	return $bool;
}
add_filter( 'option_woocommerce_enable_myaccount_registration', 'pca_override_woo_settings_registration');


/**
 *
 * @param string $bool 'yes' or 'no' (woocommerce_registration_generate_password)
 * @return string  if PCA activated and request is not for admin_page, return 'no' else return woocommerce_registration_generate_password value.
 */
function pca_override_woo_settings_password( $bool ){
	if ( ! is_admin() && pca_options_get( PCA_APP_SERVICE_ID ) ){
		return 'no';
	}
	return $bool;
}
add_filter( 'option_woocommerce_registration_generate_password', 'pca_override_woo_settings_password');


/**
 *
 * @param string $bool 'yes' or 'no' (woocommerce_enable_signup_and_login_from_checkout)
 * @return string  if PCA activated and request is not for admin_page, return 'no' else return woocommerce_enable_signup_and_login_from_checkout value.
 */
function pca_override_woo_settings_checkout_signup( $bool ){
	if ( ! is_admin() && pca_options_get( PCA_APP_SERVICE_ID ) ){
		return 'no';
	}
	return $bool;
}
add_filter( 'option_woocommerce_enable_signup_and_login_from_checkout', 'pca_override_woo_settings_checkout_signup' );


