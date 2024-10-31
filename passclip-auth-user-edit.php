<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Hide the password field in user_edit page.
 *
 * @param bool $show Whether to show the password fields. If not allowed to log in with wp password, make this false.
 * @param WP_User $user WP_User object for the user to edit.
 */
function pca_hide_password_field( $show, $user ){
	$pca_options = pca_options_get();

	if ( empty( $pca_options[PCA_APP_SERVICE_ID] ) ){
		return $show;
	}

	if ( empty( $user->roles ) || ( ! isset( $pca_options[PCA_OPTION_ALLOW_WP_LOGIN][$user->roles[0]] ) )
	|| 2 == get_user_meta( $user->ID, 'made_by_pca', true ) || 1 == get_user_meta( $user->ID, 'made_by_pca', true )  ){
		?>
	</table>
	<h2><?php _e( 'Account Management' ); ?></h2>
	<table class="form-table">
	<tr id="password" class="user-pass1-wrap">
		<th><label ><?php _e( 'Password' ); ?></label></th>
		<td>
			<?php _e( 'This user is protected by <strong>PassClip Auth</strong>.', PCA_TEXTDOMAIN); ?>
		</td>
	</tr>

<?php
		$show = false;
	} else {
		$show = true;
	}

	return $show;
}
add_filter( 'show_password_fields', 'pca_hide_password_field', 10, 2);


/**
 *
 * @param WP_User $user
 */
function pca_show_user_meta_passclip( $user ){
	if ( ! pca_options_get(PCA_APP_SERVICE_ID) ){
		return;
	}
?>
	<h2><?php _e( 'PassClip Auth Information', PCA_TEXTDOMAIN ); ?></h2>
	<table class="form-table">

<?php
	if ( get_user_meta( $user->ID, 'made_by_pca', true )  ){
?>
	<tr id="password" class="user-pass1-wrap">
		<th><label ><?php _e( 'PassClip' ); ?></label></th>
		<td>
			<?php _e( 'This user has logged in with <strong>PassClip</strong>.', PCA_TEXTDOMAIN); ?>
		</td>
	</tr></table>
<?php
	} else {
?>
	<tr id="password" class="user-pass1-wrap">
	<th><label ><?php _e( 'PassClip' ); ?></label></th>
		<td>
			<?php _e( 'This user has not logged in with <strong>PassClip</strong> yet.', PCA_TEXTDOMAIN); ?>
		</td>
	</tr></table>
<?php
	}
}
add_action( 'edit_user_profile', 'pca_show_user_meta_passclip' );


/**
 * Make form for register user_login.
 * Also show errors in registering user_login.
 *
 * make submit button here, then include admin-footer and die.
 */
function pca_make_form_register_user_login(){
	$made_by_pca = get_user_meta( get_current_user_id(), 'made_by_pca', true);

	if ( 1 == $made_by_pca ){
		$user = wp_get_current_user();
		global $wp_http_referer, $user_id;
		?>
		>
		<h3><?php _e( 'Register Username', PCA_TEXTDOMAIN ); ?></h3>

		<?php if ( isset ( $_GET['pca_register_failed'] ) ) : ?>
			<div class="notice notice-error">
			<p>
				<?php switch ($_GET['pca_register_failed'] ){
						case 'empty' :
							_e( '<strong>ERROR</strong>: Cannot register with an empty username.', PCA_TEXTDOMAIN);
							break;
						case 'too_long' :
							_e( '<strong>ERROR</strong>: Username may not be longer than 60 characters.' );
							break;
						case 'exist' :
							_e( '<strong>ERROR</strong>: This username is already registered. Please choose another one.' );
							break;
						case 'cannot_email' :
							_e( '<strong>ERROR</strong>: Cannot register with an email as a username.', PCA_TEXTDOMAIN );
							break;
						case 'invalid_characters' :
							_e( '<strong>ERROR</strong>: This username is invalid because it uses illegal characters. Please enter a valid username.' );
							break;
						case 'ex' :
						default:
							_e( '<strong>ERROR</strong>: Something is wrong in this site.', PCA_TEXTDOMAIN );
						}
						?>
			</p>
			</div>
		<?php endif; ?>

		<input type="hidden" name="pca_user_register" value="pca" />
		<table class="form-table">
			<tr valign="bottom">
				<th></th>
  				<td colspan="2"><?php _e( 'Enter your username for this site.', PCA_TEXTDOMAIN ); ?><br>
				<?php _e( 'Username can be used when you log in to this site and used as default nickname.', PCA_TEXTDOMAIN ); ?>
				</td>
			</tr>

			<tr class="user-login-pca">
				<th><label for="user_login"><?php _e('Username');?></label></th>
				<td><input type="text" name="user_login" id="user_login" value="" class="regular-text" maxlength="60" /></td>
				<td><span class="description"><?php _e( 'Cannot be changed after registration.', PCA_TEXTDOMAIN ); ?></span></td>
			</tr>

			<tr valign="bottom">
				<th></th>
  				<td colspan="2"><?php _e( 'As you registered this email to "PassClip Auth", you need this email to log in with "Passclip".', PCA_TEXTDOMAIN ); ?><br>
					<?php _e( 'After registration of Username, you can use either Username or Email to log in.', PCA_TEXTDOMAIN ); ?>
				</td>
			</tr>

			<tr class="user-email-pca">
				<th><label for="email"><?php _e('Email'); ?></label></th>
				<td><input type="email" name="email" id="email" disabled="disabled"
						value="<?php echo esc_attr( $user->user_email ) ?>" class="regular-text ltr" />
				</td>
				<td><span class="description"><?php _e('Cannot be changed', PCA_TEXTDOMAIN); ?></span></td>
			</tr>
		</table>
		<?php wp_nonce_field('update-user_' . $user_id ); ?>
		<?php if ( $wp_http_referer ) : ?>
			<input type="hidden" name="wp_http_referer" value="<?php echo esc_url($wp_http_referer); ?>" />
		<?php endif; ?>
		<p>
			<input type="hidden" name="from" value="profile" />
			<input type="hidden" name="checkuser_id" value="<?php echo get_current_user_id(); ?>" />
		</p>
		<input type="hidden" name="action" value="update" />
		<input type="hidden" name="user_id" id="user_id" value="<?php echo esc_attr( $user_id ); ?>" />
		<?php submit_button( __('Register') ); ?>
		</form>
	</div>
<?php
	include( ABSPATH . 'wp-admin/admin-footer.php');
	exit;
	}
}
add_action('user_edit_form_tag', 'pca_make_form_register_user_login');


/**
 * Hide Title on the user_login registration page.
 */
function pca_register_user_login_css(){
	if ( 1 == get_user_meta( get_current_user_id(), 'made_by_pca', true )){
		wp_enqueue_style( 'pca-register-user-login-form', PCA_CSS_URL . '/pca-register-user-login-form.css' );
	}
}
add_action('admin_print_styles-profile.php', 'pca_register_user_login_css');


/**
 * Disable to edit user_email.
 * Enqueue js when user(who was made by PCA) edit profile.
 */
function pca_user_edit_js(){
	if ( get_user_meta( get_current_user_id(), 'made_by_pca', true )){
		wp_enqueue_script('pca-register-user-login-form', PCA_JS_URL.'/pca-register-user-login-form.js' );
	}
}
add_action('admin_print_scripts-profile.php', 'pca_user_edit_js');


/**
 * When a user whom made by PCA edits its profile, the user need to register its "user_login".
 * As wordpress do not let change "user_login" , change its email first, then make new user and delete old user.
 *
 * @param int $user_id editing user's ID.
 */
function pca_update_user_login($user_id){

	if ( isset($_POST['pca_user_register']) &&
			$_POST['pca_user_register'] == 'pca' &&
			1 == get_user_meta( $user_id, 'made_by_pca',true ) ){

		$_user_bk = get_user_to_edit($user_id);

		$_user = get_user_to_edit($user_id);
		$_user -> user_email = $_user -> user_email.'_pca_before_register';

		$user_login_name =sanitize_user($_POST['user_login'], true);

		// user_login validation.
		if ( mb_strlen( $user_login_name )!= mb_strlen( $_POST['user_login'] )){
			wp_redirect( admin_url('profile.php').'?pca_register_failed=invalid_characters');
			exit;
		} elseif ( empty( $user_login_name ) ) {
			wp_redirect( admin_url('profile.php').'?pca_register_failed=empty');
			exit;
		} elseif ( mb_strlen( $user_login_name ) > 60 ) {
			wp_redirect( admin_url('profile.php').'?pca_register_failed=too_long');
			exit;
		} elseif( is_email( $user_login_name ) ){
			//When log in with email, if the email exists in other users' "user_login" , that can cause the other user's login.
			//So, we do not let register user_login using email.
			wp_redirect( admin_url('profile.php').'?pca_register_failed=cannot_email');
			exit;
		} elseif ( username_exists( $user_login_name ) ) {
			wp_redirect( admin_url('profile.php').'?pca_register_failed=exist');
			exit;
		}

		$new_user = get_user_to_edit($user_id);
		$new_user -> user_login = $user_login_name;
		$new_user -> ID = '';
		$new_user -> data -> ID = '';
		$new_user -> display_name = $user_login_name;

		$new_user -> role = pca_options_get( PCA_OPTION_DEFAULT_ROLE );

		//update user's email to create new user with the same email.
		$_user_id = wp_insert_user( $_user );
		if ( !is_wp_error( $_user_id) ){
			clean_user_cache( $_user );
			$new_user_id = wp_insert_user( $new_user );
			if ( !is_wp_error($new_user_id) ){
				if ( is_multisite() ){
					pca_delete_user_multi( $_user_id, $new_user_id );
				} else {
					wp_delete_user( $_user_id, $new_user_id );
				}

					//Over write the login_info as it is "register" for the user.
					global $user_id ;
					$user_id = $new_user_id;
					wp_set_current_user( $new_user_id, $user_login_name );
					wp_set_auth_cookie( $new_user_id );

					//save the information that the user registered its user_login.
					if ( ! update_user_meta( $new_user_id, 'made_by_pca', 2) ){
						//TODO
					}

					//back to the usual profile page.
					wp_redirect( admin_url('profile.php?updated=1'));
					exit;

			} else {
				//failed in create new user.
				//roleback the email change.
				if ( !is_wp_error( wp_insert_user( $_user_bk ) ) ){
					wp_redirect( admin_url('profile.php').'?pca_register_failed=ex');
					exit;
				} else {
					//Maybe the old user with the email added "pcabeforeregister" remains.But rarely.
				}
			}
		} else {
			//failed in updating the old user data. Maybe failed in create a new user before.
			wp_redirect( admin_url('profile.php').'?pca_register_failed=ex');
			exit;
		}
	}
}
add_action('personal_options_update', 'pca_update_user_login');


/**
 * To avoid the error cause of disabled email form, add POST['email'].
 * @param int $user_id
 */
function pca_edit_user_add_email( $user_id ){
	$_user = get_user_to_edit( $user_id );
	if( ! isset( $_POST['email'] ) && ! empty( $_user -> user_email ) ){
		$_POST['email'] = $_user -> user_email;
	}
}
add_action( 'personal_options_update', 'pca_edit_user_add_email' );


/**
 * when a user registered username in multisite,
 * if user already have posts, the posts will move to the new registered user.
 * And the sites that already logged in will also take over.
 * @param int $user_id
 * @param int $new_id
 */
function pca_delete_user_multi( $user_id, $new_id ){
	$sites = get_sites( array( 'fields' => 'ids', ) );
	foreach ( $sites as $site ){
		if ( is_user_member_of_blog( $user_id, $site ) ){
			switch_to_blog( $site );
			$role = get_user_by( 'ID', $user_id )->roles[0];
			restore_current_blog();
			add_user_to_blog( $site, $new_id, empty($role)? '':$role );

			switch_to_blog( $site );
			wp_delete_user( $user_id, $new_id );
			restore_current_blog();
		}
	}
	//when multi_site, delete the dummy user.
	wpmu_delete_user( $user_id );
}
