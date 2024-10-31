<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Define for PCA.
 */
define('PCA_OPTIONS', 'passclip_auth_options');

/**
 * The parameter defined for options
 */
define('PCA_OPTIONS_VERSION', 'PcaOptionsVersion');
define('PCA_AUTH_MEMBER_SITE_URL', 'PcaAuthMembersSiteUrl');
define('PCA_PASSCLIP_SITE_URL', 'PcaPassclipSiteUrl');
define('PCA_PASSCLIP_CODE_GET', 'PcaPassClipCodeGet');
define('PCA_PASSCLIP_HOWTOLOGIN', 'PcaPassclipHowToLogin');
define('PCA_APP_SERVICE_ID', 'PcaAppServiceId');
define('PCA_PASSCLIP_CODE', 'PcaPassClipCode');
define('PCA_OPTION_SHOW_PASSCLIP_CODE', 'PcaOptionShowPassclipCode');
define('PCA_OPTIONS_SERVER_API_URI', 'PcaServerApiUri');
define('PCA_OPTION_DEFAULT_ROLE', 'PcaOptionDefaultRole');
define('PCA_OPTION_ALLOW_WP_LOGIN', 'PcaOptionAllowWpLogin');
define('PCA_OPTION_SEND_NEW_USER_NOTICE_TO', 'PcaOptionSendNewUserNoticeTo');
define('PCA_OPTION_HIDE_LOSTPASSWORD_LINK', 'PcaOptionHideLostpasswordLink');
define('PCA_OPTION_DONT_CREATE_USER', 'PcaOptionDontCreateUser');
//define('PCA_OPTION_PUBLIC_KEY', 'PcaOptionPublicKey');
define('PCA_OPTION_MAILS', 'PcaOptionsMails');
define('PCA_OPTION_WIDGET', 'PcaOptionsWidget');
define('PCA_OPTION_WIDGET_REDIRECT', 'PcaOptionsWidgetRedirect');
define('PCA_OPTION_DONT_SHOW_ADMIN_BAR', 'PcaOptionsDontShowAdminBar' );
define('PCA_OPTION_PRESET', 'PcaOptionsPreset' );

define('PCA_JS_URL', plugin_dir_url(__FILE__) . 'js' );
define('PCA_CSS_URL', plugin_dir_url(__FILE__) . 'css' );


/**
 * When activated, initialize the PCA option. It is stored in WP Option.
 * If it has the current PCA setting(maybe deactivated this plugin.), check the current setting.
 * If needed, upgrade PCA options.
 */
function pca_options_init () {
	$pca_options_default = pca_get_default_setting();

	// Get current options setting.
	$pca_options = pca_options_get();

	//Have no data about this plugin, set default options. else update options if needed.
	if ( !$pca_options ){
		$ret = add_option( PCA_OPTIONS, $pca_options_default );
	} else {
		if ( $pca_options[PCA_OPTIONS_VERSION] != PCA_VERSION ){
			pca_options_upgrade( $pca_options_default, $pca_options );
		}
	}
}


/**
 * Get PCA default setting.
 * @return PCA_options
 */
function pca_get_default_setting(){
	$pca_options_default = array(
			PCA_OPTIONS_VERSION => PCA_VERSION,
			PCA_AUTH_MEMBER_SITE_URL => 'https://member.passclip.com/member/ui/',
			PCA_PASSCLIP_SITE_URL => 'https://www.passclip.com/',
			PCA_PASSCLIP_CODE_GET => 'https://www.passclip.com/',
			PCA_PASSCLIP_HOWTOLOGIN => 'https://www.passclip.com/pca/pca_howtologin/',
			PCA_APP_SERVICE_ID => '',
			PCA_PASSCLIP_CODE => '',
			PCA_OPTION_SHOW_PASSCLIP_CODE => 'no',
			PCA_OPTIONS_SERVER_API_URI => 'https://api.passclip.com/official/auth',
			PCA_OPTION_DEFAULT_ROLE => pca_check_if_woocommerce_activated()? 'customer' : get_option('default_role'),
			PCA_OPTION_ALLOW_WP_LOGIN => '',
			PCA_OPTION_SEND_NEW_USER_NOTICE_TO => 'admin',
			PCA_OPTION_HIDE_LOSTPASSWORD_LINK => 'yes',
			PCA_OPTION_DONT_CREATE_USER => 'no',
			//PCA_OPTION_PUBLIC_KEY => '',
			PCA_OPTION_MAILS => array(
											'subject' => '[blogname] "PassClip Auth" is available.',
											'message' => 'Dear Members,
[blogname] introduced new security system "PassClip".
It protects your account simply but strongly.
Please use and experience this simple login procedure.

>Get PassClip and register your "pattern".
[passclip_link]

>Input PassClip Code "[passclipcode]" in your PassClip.

*Plase do not reply to this email, it’s automatic mail notification.
Best regards,
[blogname]',
									),
			PCA_OPTION_WIDGET => 'no',
			PCA_OPTION_WIDGET_REDIRECT => '',
			PCA_OPTION_DONT_SHOW_ADMIN_BAR => 'yes',
			PCA_OPTION_PRESET => 'blog',
			);
	return $pca_options_default;
}


/**
 * Check the PCA_option's version.
 * If needed, upgrade will run.
 */
function pca_check_version_to_upgrade(){
	$pca_options = pca_options_get();
	if ( PCA_VERSION != $pca_options[PCA_OPTIONS_VERSION] ){
		pca_options_upgrade( pca_get_default_setting(), $pca_options );
	}
}
add_action( 'plugins_loaded', 'pca_check_version_to_upgrade' );


/**
 * PCA Options upgrade.
 * For plugin update. Update current option setting to new setting.
 * Version and Urls will be changed to new default data.
 * Others will not be changed.
 *
 * @param PCA_option $pca_options_default default setting
 * @param PCA_option $pca_options_current currrent setting
 */
function pca_options_upgrade( $pca_options_default , $pca_options_current ){

	$pca_options_for_up = array();

	foreach ( $pca_options_default as $pca_key => $pca_default){
		switch ( $pca_key ){
			case PCA_OPTIONS_VERSION:
			case PCA_AUTH_MEMBER_SITE_URL:
			case PCA_PASSCLIP_SITE_URL:
			case PCA_OPTIONS_SERVER_API_URI:
			case PCA_PASSCLIP_HOWTOLOGIN:{
				$pca_options_for_up[$pca_key] = $pca_default;
				break;
			}
			case PCA_OPTION_PRESET:
				if( isset($pca_options_current[PCA_APP_SERVICE_ID]) && !empty($pca_options_current[PCA_APP_SERVICE_ID]) ){
					$pca_options_for_up[$pca_key] = 'advanced';
				} else {
					$pca_options_for_up[$pca_key] = $pca_default;
				}
				break;
			default:{
				if ( isset( $pca_options_current[$pca_key] ) ){
					$pca_options_for_up[$pca_key] = $pca_options_current[$pca_key];
				} else {
					$pca_options_for_up[$pca_key] = $pca_default;
				}
				break;
			}
		}
	}
	update_option( PCA_OPTIONS, $pca_options_for_up);
}


/**
 * Check plugin's setting.
 * If app_service_id is empty, show message to input the app_service_id.
 */
function pca_check_pca_registration(){
	$pca_app_service_id = pca_options_get( PCA_APP_SERVICE_ID );
	if( empty( $pca_app_service_id ) && current_user_can( 'activate_plugins' ) && ( isset( $_GET['page'] )? 'pca_options_page' != $_GET['page']:true ) ) {
		$pca_setting_url = admin_url( '/admin.php?page=pca_options_page' );
		_e( '<div class="error"><a href="' . $pca_setting_url . '" >');
		_e( 'To complete activation of PassClip Auth, register the PCA app_service_id get from PassClip Auth on the Plugin Setting page.</a></div>', PCA_TEXTDOMAIN );
	}
}
add_action( 'admin_notices', 'pca_check_pca_registration' );


/**
 * Get the PCA options from DB .
 *
 * @param string $option_detail
 * @return PCA options || single value
 *		If param ='' PCA options , else a specific option value.
 */
function pca_options_get( $option_detail = '') {
	if ( !$option_detail ){
		$pca_options = get_option(PCA_OPTIONS);
		return $pca_options;
	} else {
		$pca_options = get_option(PCA_OPTIONS);
		return isset($pca_options[$option_detail])? $pca_options[$option_detail]:'';
	}
}


/**
 * Make the PCA options setting page.
 *
 */
function pca_options_page() {
	$pca_options = get_option(PCA_OPTIONS);

	if ((isset($_POST['pca_action'])) && ( __('Save Changes') == $_POST['pca_action'] )) {
		//To check the connection to PassClip Auth server, post without the information about authentication.
		$response = wp_remote_post( $pca_options[PCA_OPTIONS_SERVER_API_URI],
										array( 'sslverify' => false,
										)
									);

		if ( !is_wp_error($response) || $pca_options[PCA_APP_SERVICE_ID] ){
			pca_options_update( $pca_options );
?>

<div id='setting-error-settings_updated' class='updated'>
<p><strong><?php _e('Settings saved.') ?></strong></p></div>

<?php
		}
		else{
?>
			<div id='setting-error-settings_updated' class='notice notice-error'>
			<p><strong><?php _e('<strong>ERROR</strong>: Could not connect to PassClip Auth server.', PCA_TEXTDOMAIN ) ?></strong></p></div>
<?php
		}
	}
?>

<script type="text/javascript" src="<?php echo( PCA_JS_URL .'/pca-option-form.js');?>">
</script>
<style>th label{padding-left:5px;}</style>

<div class="wrap" id="pca_detail">
<h2><?php _e('PassClip Auth(PCA) for Wordpress Options Setting', PCA_TEXTDOMAIN); ?></h2>

<form method="POST" action="" >

<table class="form-table" style="border:solid 1px;">
	<tr valign="top" >
	<th scope="row"><label for="<?php echo PCA_APP_SERVICE_ID; ?>"><?php _e('PCA app service id', PCA_TEXTDOMAIN); ?><span class="description"><?php _e('(required)'); ?></span></label></th>
	<td>
		<input name="<?php echo PCA_APP_SERVICE_ID; ?>" type="password" id="<?php echo PCA_APP_SERVICE_ID; ?>" value="<?php echo esc_attr($pca_options[PCA_APP_SERVICE_ID]); ?>" class="input" />
		<br><span class="description" id="validate_required" style="color:red;"></span>
	</td>
	<td>
		<a target="_blank" href="<?php echo($pca_options[PCA_AUTH_MEMBER_SITE_URL]); ?>" ><?php _e( 'What is "PassClip Auth app service id(PCA app service id)" ?', PCA_TEXTDOMAIN); ?></a>
		<br><?php _e('If you do not have "PCA app service id", get it from', PCA_TEXTDOMAIN );?>
			<a target="_blank" href="<?php echo($pca_options[PCA_AUTH_MEMBER_SITE_URL]); ?>" ><?php _e('Here.', PCA_TEXTDOMAIN); ?></a>
	</td>
	</tr>

	<tr valign="top" >
	<th scope="row"><label for="<?php echo PCA_PASSCLIP_CODE; ?>"><?php _e('PassClip Code', PCA_TEXTDOMAIN); ?><span class="description"><?php _e('(required)'); ?></span></label></th>
	<td>
		<input name="<?php echo PCA_PASSCLIP_CODE; ?>" type="text" id="<?php echo PCA_PASSCLIP_CODE; ?>" value="<?php echo esc_attr( $pca_options[PCA_PASSCLIP_CODE] ); ?>" class="input" />
		<br><span class="description" id="validate_required2" style="color:red;"></span>
	</td>
	<td>

	</td>
	</tr>

<tr valign="top" >
<th scope="row"><label><?php _e('Simple Setting', PCA_TEXTDOMAIN); ?></label></th>
<td colspan=2>
	<input name="<?php echo PCA_OPTION_PRESET; ?>" class="<?php echo PCA_OPTION_PRESET; ?>"
			type="radio" id="<?php echo PCA_OPTION_PRESET . 1; ?>"
			value="blog" <?php echo ( 'blog' == $pca_options[PCA_OPTION_PRESET] )? 'checked="checked"' : '' ; ?> />
	<label style="padding-right:10px;" for="<?php echo PCA_OPTION_PRESET . 1; ?>"><?php _e( 'for blog', PCA_TEXTDOMAIN); ?></label>
	<input name="<?php echo PCA_OPTION_PRESET; ?>" class="<?php echo PCA_OPTION_PRESET; ?>"
			type="radio" id="<?php echo PCA_OPTION_PRESET . 2; ?>"
			value="community" <?php echo ( 'community' == $pca_options[PCA_OPTION_PRESET] )? 'checked="checked"' : '' ; ?> />
	<label style="padding-right:10px;" for="<?php echo PCA_OPTION_PRESET . 2; ?>"><?php _e( 'for community', PCA_TEXTDOMAIN); ?></label>
	<input name="<?php echo PCA_OPTION_PRESET; ?>" class="<?php echo PCA_OPTION_PRESET; ?>"
			type="radio" id="<?php echo PCA_OPTION_PRESET . 3; ?>"
			value="ECsite" <?php echo ( 'ECsite' == $pca_options[PCA_OPTION_PRESET] )? 'checked="checked"' : '' ; ?> />
	<label style="padding-right:10px;" for="<?php echo PCA_OPTION_PRESET . 3; ?>"><?php _e( 'for ECsite', PCA_TEXTDOMAIN); ?></label>
	<input name="<?php echo PCA_OPTION_PRESET; ?>" class="<?php echo PCA_OPTION_PRESET; ?>"
			type="radio" id="<?php echo PCA_OPTION_PRESET . 4; ?>"
			value="advanced" <?php echo ( 'advanced' == $pca_options[PCA_OPTION_PRESET] )? 'checked="checked"' : '' ; ?> />
	<label style="padding-right:10px;" for="<?php echo PCA_OPTION_PRESET . 4; ?>"><?php _e( 'Advanced Setting', PCA_TEXTDOMAIN); ?></label>
</td>
</tr>
</table>
<p class="submit"><input type="submit" name="pca_action" id="pca_action" class="button button-primary" value="<?php _e('Save Changes'); ?>" /></p>

<h3><?php _e('Advanced Setting', PCA_TEXTDOMAIN); ?></h3>
<table class="form-table" id="pca_advanced" style="border:solid 1px;">
<tr valign="top">
<th scope="row"><label for="<?php echo PCA_OPTION_SHOW_PASSCLIP_CODE; ?>"><?php _e('Show PassClip Code', PCA_TEXTDOMAIN); ?></label></th>
<td>
	<input name="<?php echo PCA_OPTION_SHOW_PASSCLIP_CODE; ?>"
			type="checkbox" id="<?php echo PCA_OPTION_SHOW_PASSCLIP_CODE; ?>"
			value="1" <?php echo ( 'yes' == $pca_options[PCA_OPTION_SHOW_PASSCLIP_CODE] )? 'checked="checked"' : '' ; ?> />
</td>
<td> <?php _e('If checked, your PassClip Code is displayed in the login form.', PCA_TEXTDOMAIN ); ?> </td>
</tr>

<tr valign="top">
<th scope="row"><label for="<?php echo PCA_OPTION_DEFAULT_ROLE; ?>"><?php _e('Default role for users made by PCA', PCA_TEXTDOMAIN); ?></label></th>
<td><select name="<?php echo PCA_OPTION_DEFAULT_ROLE; ?>" id="<?php echo PCA_OPTION_DEFAULT_ROLE; ?>" >
		<?php wp_dropdown_roles( $pca_options[PCA_OPTION_DEFAULT_ROLE] );
		// print the 'no role' option.
		if ( $pca_options[PCA_OPTION_DEFAULT_ROLE] != '' )
			echo '<option value="">' . __('&mdash; No role for this site &mdash;') . '</option>';
		else
			echo '<option value="" selected="selected">' . __('&mdash; No role for this site &mdash;') . '</option>';
		?>
	</select></td>
</tr>

<tr valign="top">
<th scope="row"><label for="<?php echo PCA_OPTION_ALLOW_WP_LOGIN; ?>"><?php _e('Allow login with wordpress password', PCA_TEXTDOMAIN); ?></label></th>
<td id="<?php echo PCA_OPTION_ALLOW_WP_LOGIN; ?>">
<?php
	pca_form_target_roles($pca_options);
?>
</td>
<td>
<?php _e('User with checked role can login using wordpress password.', PCA_TEXTDOMAIN); ?>
</td>
</tr>

<tr valign="top">
<th scope="row"><label for="<?php echo PCA_OPTION_SEND_NEW_USER_NOTICE_TO; ?>"><?php _e('Send new user notice mail to', PCA_TEXTDOMAIN); ?></label></th>
<td id="<?php echo PCA_OPTION_SEND_NEW_USER_NOTICE_TO; ?>">
	<input name="<?php echo PCA_OPTION_SEND_NEW_USER_NOTICE_TO; ?>"
			type="radio" id="<?php echo PCA_OPTION_SEND_NEW_USER_NOTICE_TO."admin"; ?>"
			value="admin" <?php pca_form_send_newuser_notice_to($pca_options, 'admin');?> /> <?php _e('admin'); ?><br>
	<input name="<?php echo PCA_OPTION_SEND_NEW_USER_NOTICE_TO; ?>"
			type="radio" id="<?php echo PCA_OPTION_SEND_NEW_USER_NOTICE_TO."user"; ?>"
			value="user" <?php pca_form_send_newuser_notice_to($pca_options, 'user');?> /> <?php _e('user'); ?><br>
	<input name="<?php echo PCA_OPTION_SEND_NEW_USER_NOTICE_TO; ?>"
			type="radio" id="<?php echo PCA_OPTION_SEND_NEW_USER_NOTICE_TO."both"; ?>"
			value="both" <?php pca_form_send_newuser_notice_to($pca_options, 'both');?> /> <?php _e('both'); ?><br>
	<input name="<?php echo PCA_OPTION_SEND_NEW_USER_NOTICE_TO; ?>"
			type="radio" id="<?php echo PCA_OPTION_SEND_NEW_USER_NOTICE_TO."none"; ?>"
			value="none" <?php pca_form_send_newuser_notice_to($pca_options, 'none');?> /> <?php _e('none'); ?>
</td>
</tr>

<tr valign="top">
<th scope="row"><label for="<?php echo PCA_OPTION_HIDE_LOSTPASSWORD_LINK; ?>"><?php _e('Hide "Lost password" link', PCA_TEXTDOMAIN); ?></label></th>
<td>
	<input name="<?php echo PCA_OPTION_HIDE_LOSTPASSWORD_LINK; ?>"
			type="checkbox" id="<?php echo PCA_OPTION_HIDE_LOSTPASSWORD_LINK; ?>"
			value="1" <?php echo ( 'yes' == $pca_options[PCA_OPTION_HIDE_LOSTPASSWORD_LINK] )? 'checked="checked"' : '' ; ?> />
</td>
<td> <?php _e('If checked, hide "Lost your password?" in the login form.', PCA_TEXTDOMAIN ); ?> </td>
</tr>

<tr valign="top">
<th scope="row"><label for="<?php echo PCA_OPTION_DONT_CREATE_USER; ?>"><?php _e('Prevent PassClip Auth from creating a new user', PCA_TEXTDOMAIN); ?></label></th>
<td>
	<input name="<?php echo PCA_OPTION_DONT_CREATE_USER; ?>"
			type="checkbox" id="<?php echo PCA_OPTION_DONT_CREATE_USER; ?>"
			value="1" <?php echo ( 'yes' == $pca_options[PCA_OPTION_DONT_CREATE_USER] )? 'checked="checked"':''; ?> />
</td>
<td> <?php _e('If checked, only existing members can log in.', PCA_TEXTDOMAIN ); ?> </td>
</tr>

<tr valign="top">
<th scope="row"><label for="<?php echo PCA_OPTION_DONT_SHOW_ADMIN_BAR; ?>"><?php _e('Hide toolbar from new users.', PCA_TEXTDOMAIN); ?></label></th>
<td>
	<input name="<?php echo PCA_OPTION_DONT_SHOW_ADMIN_BAR; ?>"
			type="checkbox" id="<?php echo PCA_OPTION_DONT_SHOW_ADMIN_BAR; ?>"
			value="1" <?php echo ( 'yes' == $pca_options[PCA_OPTION_DONT_SHOW_ADMIN_BAR] )? 'checked="checked"':''; ?> />
</td>
<td> <?php _e('If checked, the toolbar will not appear to new users.', PCA_TEXTDOMAIN ); ?> </td>
</tr>

<tr valign="top">
<th scope="row"><label for="<?php echo PCA_OPTION_WIDGET; ?>"><?php _e('Enable PassClip Login Form Widget', PCA_TEXTDOMAIN); ?></label></th>
<td>
	<input name="<?php echo PCA_OPTION_WIDGET; ?>"
			type="checkbox" id="<?php echo PCA_OPTION_WIDGET; ?>"
			value="1" <?php echo ( 'yes' == $pca_options[PCA_OPTION_WIDGET] )? 'checked="checked"':''; ?> />
</td>
<td> <?php _e('If checked, you can use our loginform widget.', PCA_TEXTDOMAIN ); ?> </td>
</tr>

<tr valign="top">
<th scope="row"><label for="<?php echo PCA_OPTION_WIDGET_REDIRECT; ?>"><?php _e('"Redirect to" for the first time.', PCA_TEXTDOMAIN); ?></label></th>
<td>
	<input name="<?php echo PCA_OPTION_WIDGET_REDIRECT; ?>"
			type="text" id="<?php echo PCA_OPTION_WIDGET_REDIRECT; ?>"
			value="<?php echo esc_attr( $pca_options[PCA_OPTION_WIDGET_REDIRECT] ); ?>" class="input" />
</td>
<td> <?php _e('When a new user logged in from our widget for the first time, he will be redirected to this url. Input the url of the tutorial document, member profile, top page for members, and so on.', PCA_TEXTDOMAIN ); ?> </td>
</tr>

</table>

<p class="submit"><input type="submit" name="pca_action" id="pca_action" class="button button-primary" value="<?php _e('Save Changes'); ?>" /></p>
</form>
</div>

<?php
}


/**
 * When make form, check checkbox by pca_option PCA_OPTION_ALLOW_WP_LOGIN.
 *
 * @param array $pca_options
 */
function pca_form_target_roles( $pca_options ){
	$r = '';
	$p = $pca_options[PCA_OPTION_ALLOW_WP_LOGIN];

	$editable_roles = get_editable_roles();

	foreach ( $editable_roles as $role => $details ) {

		$name = translate_user_role( $details['name'] );
		// preselect specified role
		if ( isset( $p[$role] ) && $p[$role] ) {
			$r .= '<input name="' .
					$role .
					'" type="checkbox" id="' . $role .
					'" value="' . $role . '" ' . 'checked="checked"' . '/>' . $name . '<br>';
		} else {
			$r .= '<input name="' .
					$role .
					'" type="checkbox" id="' . $role .
					'" value="' . $role . '" />' . $name . '<br>';
		}
	}
	echo $r;
}

/**
 * When make form, check radio button by pca_option PCA_OPTION_SEND_NEW_USER_NOTICE_TO.
 *
 * @param array $pca_options
 * @param string $target mail to
 */
function pca_form_send_newuser_notice_to( $pca_options, $target ){
	$checked = '';
	if( $target == $pca_options[PCA_OPTION_SEND_NEW_USER_NOTICE_TO] ){
		$checked = 'checked="checked"';
	}
	echo $checked;
}

/**
 * check PCA_OPTION_ALLOW_WP_LOGIN from $_POST when pca_options set.
 *
 * @param array $pca_options
 * @return array(user_role,boolean)
 */
function pca_target_roles_register_check($pca_options){
	$p = $pca_options[PCA_OPTION_ALLOW_WP_LOGIN];

	$target_roles = array();
	$editable_roles = get_editable_roles();

	foreach ( $editable_roles as $role => $details ) {
		if ( isset( $_POST[$role] ) ){
			$target_roles[$role] = true;
		}
	}
	return $target_roles;
}


/**
 * update options.
 * consider preset check.
 * @param array $pca_options
 */
function pca_options_update( &$pca_options ){

	$pca_options[PCA_APP_SERVICE_ID] = wp_unslash( $_POST[PCA_APP_SERVICE_ID] );
	$pca_options[PCA_PASSCLIP_CODE] = wp_unslash( $_POST[PCA_PASSCLIP_CODE] );

	if( isset( $_POST[PCA_OPTION_PRESET] ) ){
		switch( $_POST[PCA_OPTION_PRESET] ){
			case 'advanced':
				$pca_options[PCA_OPTION_SHOW_PASSCLIP_CODE] = isset($_POST[PCA_OPTION_SHOW_PASSCLIP_CODE])? 'yes' : 'no' ;
				$pca_options[PCA_OPTION_DEFAULT_ROLE] = $_POST[PCA_OPTION_DEFAULT_ROLE];
				$pca_options[PCA_OPTION_ALLOW_WP_LOGIN] = pca_target_roles_register_check( $pca_options );
				$pca_options[PCA_OPTION_SEND_NEW_USER_NOTICE_TO] = $_POST[PCA_OPTION_SEND_NEW_USER_NOTICE_TO];
				$pca_options[PCA_OPTION_HIDE_LOSTPASSWORD_LINK] = isset($_POST[PCA_OPTION_HIDE_LOSTPASSWORD_LINK])? 'yes' : 'no';
				$pca_options[PCA_OPTION_DONT_CREATE_USER] = isset($_POST[PCA_OPTION_DONT_CREATE_USER])? 'yes' : 'no' ;
				//$pca_options[PCA_OPTION_PUBLIC_KEY] = $_POST[PCA_OPTION_PUBLIC_KEY];
				$pca_options[PCA_OPTION_DONT_SHOW_ADMIN_BAR] = isset($_POST[PCA_OPTION_DONT_SHOW_ADMIN_BAR])? 'yes' : 'no' ;
				$pca_options[PCA_OPTION_WIDGET] = isset($_POST[PCA_OPTION_WIDGET])? 'yes' : 'no' ;
				$pca_options[PCA_OPTION_WIDGET_REDIRECT] = isset( $_POST[PCA_OPTION_WIDGET_REDIRECT] )? wp_unslash( $_POST[PCA_OPTION_WIDGET_REDIRECT] ):'';
				$pca_options[PCA_OPTION_PRESET] = $_POST[PCA_OPTION_PRESET];
				break;
			case 'blog':
				$pca_options[PCA_OPTION_SHOW_PASSCLIP_CODE] = 'no' ;
				$pca_options[PCA_OPTION_DEFAULT_ROLE] = '';
				$pca_options[PCA_OPTION_ALLOW_WP_LOGIN] = '' ;
				$pca_options[PCA_OPTION_SEND_NEW_USER_NOTICE_TO] = 'both';
				$pca_options[PCA_OPTION_HIDE_LOSTPASSWORD_LINK] = 'yes';
				$pca_options[PCA_OPTION_DONT_CREATE_USER] = 'yes';
				//$pca_options[PCA_OPTION_PUBLIC_KEY] = $_POST[PCA_OPTION_PUBLIC_KEY];
				$pca_options[PCA_OPTION_DONT_SHOW_ADMIN_BAR] = 'no';
				$pca_options[PCA_OPTION_WIDGET] = 'no';
				$pca_options[PCA_OPTION_WIDGET_REDIRECT] = '';
				$pca_options[PCA_OPTION_PRESET] = $_POST[PCA_OPTION_PRESET];
				break;
			case 'ECsite':
				$pca_options[PCA_OPTION_SHOW_PASSCLIP_CODE] = 'yes' ;
				$pca_options[PCA_OPTION_DEFAULT_ROLE] = __( 'contributor' );
				$pca_options[PCA_OPTION_ALLOW_WP_LOGIN] = '' ;
				$pca_options[PCA_OPTION_SEND_NEW_USER_NOTICE_TO] = 'both';
				$pca_options[PCA_OPTION_HIDE_LOSTPASSWORD_LINK] = 'yes';
				$pca_options[PCA_OPTION_DONT_CREATE_USER] = 'no';
				//$pca_options[PCA_OPTION_PUBLIC_KEY] = $_POST[PCA_OPTION_PUBLIC_KEY];
				$pca_options[PCA_OPTION_DONT_SHOW_ADMIN_BAR] = 'no';
				$pca_options[PCA_OPTION_WIDGET] = 'no';
				$pca_options[PCA_OPTION_WIDGET_REDIRECT] = '';
				$pca_options[PCA_OPTION_PRESET] = $_POST[PCA_OPTION_PRESET];
				break;
			case 'community':
				$pca_options[PCA_OPTION_SHOW_PASSCLIP_CODE] = 'yes' ;
				$pca_options[PCA_OPTION_DEFAULT_ROLE] = '';
				$pca_options[PCA_OPTION_ALLOW_WP_LOGIN] = '';
				$pca_options[PCA_OPTION_SEND_NEW_USER_NOTICE_TO] = 'both';
				$pca_options[PCA_OPTION_HIDE_LOSTPASSWORD_LINK] = 'yes';
				$pca_options[PCA_OPTION_DONT_CREATE_USER] = 'no';
				//$pca_options[PCA_OPTION_PUBLIC_KEY] = $_POST[PCA_OPTION_PUBLIC_KEY];
				$pca_options[PCA_OPTION_DONT_SHOW_ADMIN_BAR] = 'yes';
				$pca_options[PCA_OPTION_WIDGET] = 'yes';
				$pca_options[PCA_OPTION_WIDGET_REDIRECT] = isset( $_POST[PCA_OPTION_WIDGET_REDIRECT] )? wp_unslash($_POST[PCA_OPTION_WIDGET_REDIRECT]):'';
				$pca_options[PCA_OPTION_PRESET] = $_POST[PCA_OPTION_PRESET];
				break;
		}
	}
	update_option(PCA_OPTIONS, $pca_options);
}


/**
 *
 */
function pca_mail_setting_page(){
	$pca_options = get_option(PCA_OPTIONS);

	if ( ( isset( $_POST['pca_action'] ) ) && ( __( 'Save Changes' ) == $_POST['pca_action'] ) ) {
		if ( ! empty($_POST[PCA_OPTION_MAILS . 'subject']) && ! empty($_POST[PCA_OPTION_MAILS . 'message'] ) ){
			$pca_options[PCA_OPTION_MAILS] = array( 'subject' => wp_unslash( $_POST[PCA_OPTION_MAILS . 'subject'] ),
													'message' => wp_unslash( $_POST[PCA_OPTION_MAILS . 'message'] ),
													);
			update_option( PCA_OPTIONS, $pca_options );
?>
<div id='setting-error-settings_updated' class='updated settings-error'>
<p><strong><?php _e( 'Settings saved.' ); ?></strong></p></div>
<?php
		} else {
?>
<div id='setting-error-settings_updated' class='error settings-error'>
<p><?php _e( '<strong>ERROR</strong>: Please enter a subject and a mail body.', PCA_TEXTDOMAIN ); ?></p></div>

<?php  }
	}
?>

<div class="wrap" id="pca_detail">
<h2><?php _e( 'PassClip Auth(PCA) for Wordpress Mail Setting', PCA_TEXTDOMAIN ); ?></h2>
<form method="POST" action="" >

<table class="form-table">
	<tr valign="top" >
	<th scope="row"><label for="<?php echo PCA_OPTION_MAILS . 'subject'; ?>"><?php _e( 'Subject of Notification', PCA_TEXTDOMAIN ); ?></label></th>
	<td>
		<input class="regular-text" type="text" name="<?php echo PCA_OPTION_MAILS . 'subject'; ?>"
							id="<?php echo PCA_OPTION_MAILS . 'subject'; ?>"
							value="<?php echo esc_attr( $pca_options[PCA_OPTION_MAILS]['subject'] ); ?>" />
	</td>
	<td>

	</td>
	</tr>

	<tr valign="top" >
	<th scope="row"><label for="<?php echo PCA_OPTION_MAILS . 'message'; ?>"><?php _e('Notification of PassClip Code', PCA_TEXTDOMAIN); ?></label></th>
	<td>
		<textarea name="<?php echo PCA_OPTION_MAILS . 'message'; ?>" id="<?php echo PCA_OPTION_MAILS . 'message'; ?>" rows="10" cols="65"/>
<?php echo esc_textarea( $pca_options[PCA_OPTION_MAILS]['message'] ) ; ?></textarea>
	</td>
	<td>
		<?php _e( 'You can send this mail from "Manage PCA Users".', PCA_TEXTDOMAIN );?><br>
		<?php _e( 'Tags [passclipcode], [blogname] will be replaced to passclip code and your blogname.', PCA_TEXTDOMAIN );?><br>
		<?php _e( 'Tag [passclip_link] will be replaced to the url of website about passclip.', PCA_TEXTDOMAIN );?>
	</td>
	</tr>
</table>

<p class="submit"><input type="submit" name="pca_action" id="pca_action" class="button button-primary" value="<?php _e('Save Changes'); ?>" /></p>

</form>
</div>
<?php
}
?>