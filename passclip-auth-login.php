<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


/**
 * Authenticate by PassClip Auth
 *
 * @param $user WP_User || WP_Error
 * @param $username username input by user,so email address or user_login
 * @param $password password input by user
 * @return WP_User || WP_Error  if failed authentication, return WP_Error. else WP_User.
 */
function pca_login_authenticate( $user, $username, $password ) {
	$pca_options = pca_options_get();
	if ( empty( $pca_options[PCA_APP_SERVICE_ID] ) ){
		return $user;
	}

	// login error occurred.
	if (is_wp_error($user) ) {
		//when log in with temporary code, do not try PCA authentication.
		if (isset($_POST['pca_login_code'])){
			return $user;
		}
		if ( '' == $password || '' == $username ){
			return $user;
		}

		if (!get_user_by('login', $username) && !get_user_by( 'email', $username)){
			if ( is_email($username) ){
				// Authenticate by PCA server API
				$result = pca_login_authenticate_server_api( $username, $password );
				if ( is_wp_error($result) ){
					// PCA server error
					$error = new WP_Error('incorrect_password', __('<strong>ERROR</strong>: Could not connect to PassClip Auth server.', PCA_TEXTDOMAIN));
				} elseif ($result) {
					// PCA Authenticate success
					//check if PCA may create new user.
					if ( 'yes' == $pca_options[PCA_OPTION_DONT_CREATE_USER] ){
						$error = new WP_Error('incorrect_password', __('<strong>ERROR</strong>: This site is only for members.', PCA_TEXTDOMAIN));
						return $error;
					}

					// user create and login
					$created_user = array('user_login' => $username,
													'user_pass'   =>  '',
													'user_email'  => $username,
													'role' => $pca_options[PCA_OPTION_DEFAULT_ROLE],
													);
					$user_id = wp_insert_user( apply_filters( 'pca_create_user', $created_user ) );

					if (!$user_id || is_wp_error($user_id)) {
						// failed in create user
						$error = new WP_Error('incorrect_password', __( '<strong>ERROR</strong>: Something is wrong in this site.', PCA_TEXTDOMAIN) );

					} else {
						// register success.
						// add user_meta that shows 'this user was made by pca'.
						add_user_meta( $user_id, 'made_by_pca', 1 );

						if ( 'none' != $pca_options[PCA_OPTION_SEND_NEW_USER_NOTICE_TO] ){
							pca_new_user_notification( $user_id, $pca_options[PCA_OPTION_SEND_NEW_USER_NOTICE_TO]);
						}
						$error = new WP_User($user_id);
					}
				} else {
					//PCA Authenticate failed
					$error = new WP_Error(
									'incorrect_password',
									__('<strong>ERROR</strong>: Login failed. Considerable reasons are below.', PCA_TEXTDOMAIN )
									);
					$error -> add('incorrect_password', __('･Wrong user.', PCA_TEXTDOMAIN) );
					$error -> add('incorrect_password', __('･Invalid password.', PCA_TEXTDOMAIN ) );
					$error -> add('incorrect_password', __('･Blocked by the login protection.', PCA_TEXTDOMAIN) );
				}
			} else {
				// user is not yet registered to wordpress and input is not email, so can't try authenticate in PCA
				$error = new WP_Error(
									'incorrect_password',
									__('<strong>ERROR</strong>: Invalid Password or Username. Input correctly.', PCA_TEXTDOMAIN)
									);
			}
		} else {
			// user is already registered to wordpress but failed to login. so try authentification by PCA

			//get user by the input data(email or user_login).
			$registered_wp_user = get_user_by('email', $username)? get_user_by('email', $username) : get_user_by('login', $username) ;

			//try authentication by PCA
			$result = pca_login_authenticate_server_api($registered_wp_user -> user_email, $password );

			if ( is_wp_error($result) ) {
				//failed in Connect to PCA server
				$error = new WP_Error('incorrect_password', __('<strong>ERROR</strong>: Could not connect to PassClip Auth server.', PCA_TEXTDOMAIN));

				if ( isset( $registered_wp_user->caps['administrator'] ) && true == $registered_wp_user->caps['administrator'] ) {
					// Display the temporarily login link if the administrator's login.
					$message = sprintf(
							__('<br><a href="%s" title="Log in without PassClip Auth temporarily">Log in without PassClip Auth temporarily</a> ?', PCA_TEXTDOMAIN),
							get_login_url('templogin', $username)
					);
				} else {
					$message = '';
				}
				$error->add('incorrect_password', $message);

			} elseif ( $result ) {
				//PCA Auth Success
				if ( ! get_user_meta( $registered_wp_user -> ID, 'made_by_pca', true ) ){
					//Existing user logged in with PassClip, save that information as user_meta 3.
					add_user_meta( $registered_wp_user -> ID, 'made_by_pca', 3);
				}
				$error = $registered_wp_user;
			} else {
				//PCA Auth failed
				$error = new WP_Error(
									'incorrect_password',
									__('<strong>ERROR</strong>: Login failed. Considerable reasons are below.', PCA_TEXTDOMAIN )
									);
				$error -> add('incorrect_password', __('･Wrong user.', PCA_TEXTDOMAIN) );
				$error -> add('incorrect_password', __('･Invalid password.', PCA_TEXTDOMAIN ) );
				$error -> add('incorrect_password', __('･Blocked by the login protection.', PCA_TEXTDOMAIN) );
			}
		}
	} else {
		//wordpress login success
		if ( isset($_POST['pca_login_code']) ) {
			//have one time login code
			$result = pca_compare_logincode($user->ID, $_POST['pca_login_code']);
			if ( !$result ){
				$message = __('<strong>ERROR</strong>: Input the temporary login code correctly.', PCA_TEXTDOMAIN);
			}
		} else {
			//have no additional code. Check if allowed login with wordpress password.
			if ( ! get_user_meta( $user->ID, 'made_by_pca', true ) || 3 == get_user_meta( $user->ID, 'made_by_pca', true ) ){
				$result = isset( $pca_options[PCA_OPTION_ALLOW_WP_LOGIN][$user->roles[0]])? true : false ;
				if ( !$result ){
					$message = __( '<strong>ERROR</strong>: You are not allowed login with wordpress password.', PCA_TEXTDOMAIN );

					if ( isset( $user->caps['administrator'] ) && true == $user->caps['administrator'] ){
						$message .= sprintf(
								__( '<br><a href="%s" title="Log in without PassClip Auth temporarily">Log in without PassClip Auth temporarily</a> ?', PCA_TEXTDOMAIN ),
								get_login_url( 'templogin', $username )
								);
					}
				}
			} else {
				//Usually, users who were made by PCA can not log in successfully with wordpress password.
				//And we do not allow such kinds of login.
				$result = false;
				$message = __( '<strong>ERROR</strong>: You are not allowed login with wordpress password.', PCA_TEXTDOMAIN );
			}
		}

		if ( $result ) {
			// Authenticate success in ether way
			$error = $user;
		} else {
			// Authenticate failed.
			$error = new WP_Error(
							'incorrect_password',
							$message
						);
		}
	}
	if ( !is_wp_error( $error ) && isset( $_POST['pca_memberCard'] ) ){
		//if you want to use user_data for member card, use this function.
		pca_show_member_card( $error );
		exit;
	}

	if ( !is_wp_error( $error ) && 1 == get_user_meta( $error->ID, 'made_by_pca', true ) && !empty( $pca_options[PCA_OPTION_DEFAULT_ROLE] ) ){
		//If user is made by PCA and not yet registered username, redirect to "profile" page in order to change "user_login".
		$_REQUEST['redirect_to'] = admin_url('profile.php');
		add_filter( 'login_redirect', 'pca_redirect_to', 10, 2 );
	} elseif ( !is_wp_error( $error ) && isset( $_POST['pca_redirect_to'] ) ){
		//If you want to guide users who used PassClip slot login to an objective page, please set it in PassClip Auth members site.
		$_REQUEST['redirect_to'] = $_POST['pca_redirect_to'];
		add_filter( 'login_redirect', 'pca_redirect_to', 10, 2 );
	}

	if ( is_multisite() ){
		//user is exists and authenticate successfully and new to the blog.
		if ( ! is_wp_error( $error ) ){
			$id = get_current_blog_id();
			if ( ! is_user_member_of_blog( $error->ID, $id ) ){
				//check if PCA may create new user.
				if ( 'yes' == $pca_options[PCA_OPTION_DONT_CREATE_USER] ){
					$error = new WP_Error( 'incorrect_password', __('<strong>ERROR</strong>: This site is only for members.', PCA_TEXTDOMAIN));
					return $error;
				}

				add_user_to_blog( $id, $error->ID, $pca_options[PCA_OPTION_DEFAULT_ROLE] );

				if ( 'none' != $pca_options[PCA_OPTION_SEND_NEW_USER_NOTICE_TO] ){
					pca_new_user_notification( $error->ID, $pca_options[PCA_OPTION_SEND_NEW_USER_NOTICE_TO]);
				}
			}
		}
	}

	return $error;
}


/**
 * If "redirect_to" was posted, set redirect_to.
 * @param string $redirect_to
 * @param string $requested_redirect_to
 */
function pca_redirect_to( $redirect_to, $requested_redirect_to ){
	if( ! empty( $requested_redirect_to ) ){
		return $requested_redirect_to;
	}
	return $redirect_to;
}
add_filter( 'login_redirect', 'pca_redirect_to', 10, 2 );


/**
 * This is the method that send an email with the Login Code to user
 *
 * @param $username the user name, it was input by user
 */
function pca_send_login_code( $username ) {
	$user = get_user_by( 'login', $username )? get_user_by( 'login', $username ):get_user_by( 'email', $username );

	if ( isset( $user->caps['administrator'] ) && true == $user->caps['administrator'] ){
		// Create a login code for login temporarily, and save it to user meta
		pca_create_logincode( $user->ID );
	} else {
		//Usually not called. This method is for the administrator. So if not admin, redirect to usual login form.
		wp_redirect( wp_login_url() );
		exit;
	}

	$args = is_array( get_user_meta( $user->ID, "pca_login_code", true ) )? get_user_meta( $user->ID, "pca_login_code", true ): array();

	if ( isset($args['email_sent']) && false == $args['email_sent'] || !isset( $args['email_sent'] ) ) {
		$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

		// Make the message
		$message  = __('A temporary login code was sent to your email address for login without PassClip Auth.', PCA_TEXTDOMAIN)."\r\n\r\n";
		$message .= sprintf(__('Your temporarily login code is "%s".', PCA_TEXTDOMAIN), $args['login_code'])."\r\n";

		// Update the email_sent flag of user meta by email send result
		$args['email_sent'] = wp_mail(
									$user->data->user_email,
									sprintf(
										__('[%s] Your temporarily login code', PCA_TEXTDOMAIN),
										$blogname
									),
									$message
								);
		update_user_meta( $user->ID, "pca_login_code", $args );
	}
}


/**
 * This is the method that authenticate by PCA server API
 *
 * @param $username
 *				the user name, it was input by user
 * @param $register
 *				is it register or not
 * @return authentication result
 *				WP_Error: server error
 *					true: success
 *				   false: failed
 */
function pca_login_authenticate_server_api($username, $password ) {
	$pca_options = pca_options_get();

	$result = pca_login_authenticate_post(
					$pca_options[PCA_OPTIONS_SERVER_API_URI],
					$username,
					$password,
					$pca_options[PCA_APP_SERVICE_ID] );
	return $result;
}


/**
 * This is the method that post the authenitication info to PCA server API
 *
 * @param $url
 *				URL to PCA server API
 * @param $username
 *				user name
 * @param $password
 *				password input by user
 * @return authentication result
 *				WP_Error: server error
 *					true: success
 *				   false: failed
 */
function pca_login_authenticate_post($url, $username, $password, $id) {

	$response = wp_remote_post(
						$url,
						array(
							'sslverify' => false,
							'body' => array( 'app_service_id' => $id,
											'email' => $username,
											'password' => $password,
											'response_format'=>'json',
											),
						)
					);
	if ( is_wp_error( $response ) ) {
		// failed in wp_remote_post
		return $response;
	} else {
		// wp_remote_post success
		return pca_login_response_purse( $response['body'] );
	}
}


/**
 * if you want to use user_data for member card, use this function.
 * @param WP_User $user
 * @return information about user.as a member card.
 */
function pca_show_member_card( $user ){
	if ( apply_filters( 'pca_show_member_card', true, $user ) ) {
		$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
		_e('You are a member of <strong>' . $blogname . '</strong><br>' );
		echo ( $user->display_name . '<br>' );
		echo ( 'Your role is ' . $user->roles[0] . '<br>' );
		echo ( 'You registered on ' . $user->user_registered . '<br>' );
	}
}

/**
 * Define for PCA server API response "code"
 */
define('PCA_SVRAPI_RESPCODE_SUCCESS', '000');

/**
 * This is the method that purse the response from PCA server API
 *
 * @param body
 *				response body data (usually json)
 * @return authentication result
 *				 true: success
 *				false: failed
 */
function pca_login_response_purse($body) {

	$elements = json_decode( $body );

	if ( isset( $elements->code ) ){
		switch ( $elements->code ) {
			case PCA_SVRAPI_RESPCODE_SUCCESS:
				$result = true;
				break;
			default:
				$result = false;
				break;
		}
	} else {
		$result = false;
	}

	return $result;
}

/**
 * This is the method that generate a password by PCA server API
 *
 * @return a password, it was generated by PCA
 */
function pca_login_generate_password() {

	$password = wp_generate_password( 8, false );

	return $password;
}

/**
 * Get the login URL with PCA mode
 *
 * @param $pca_mode
 *				PCA mode
 * @param $username
 *				user name
 * @return the login URL with PCA mode
 */
function get_login_url($pca_mode = '', $username = '') {
	$args = array(
				'pcamode' => $pca_mode,
				'log' => $username,
			);
	$login_url = add_query_arg($args, wp_login_url());

	return $login_url;
}

/**
 * Create a logincode, save it to user meta, and sent it to user by email
 *
 * @param $user_id
 *				user ID
 */
function pca_create_logincode( $user_id ) {

	// Generate a temp password
	$code = pca_login_generate_password();

	$args = is_array( get_user_meta( $user_id, 'pca_login_code', true) )? get_user_meta( $user_id, 'pca_login_code', true) : array();

	if ( !isset( $args['email_sent'] ) || false == $args['email_sent'] ){
		$args['login_code'] = $code;

		// Save the logincode to user meta
		if ( update_user_meta( $user_id, "pca_login_code", $args ) != true) {
			add_user_meta( $user_id, "pca_login_code", $args );
		}
	}
}

/**
 * This is the method that compare the logincode
 *
 * @param $user_id
 *				user ID
 * @param $logincode
 *				temporal login code
 * @return compare result(if equal, return true)
 */
function pca_compare_logincode($user_id, $logincode) {
	if ($logincode == ''){
		return false;
	}

	$args = get_user_meta( $user_id, "pca_login_code", true );
	delete_user_meta( $user_id, "pca_login_code" );

	return ( strcmp($args['login_code'], $logincode ) == 0 );
}



/**
 * Email to a newly-registered user.
 * A new user registration notification is also sent to admin email.
 *
 * @param int    $user_id    User ID.
 * @param string $notify     Optional. Type of notification that should happen. Accepts 'admin' or an empty
 *                           string (admin only), 'user', or 'both' (admin and user). Default empty.
 */
function pca_new_user_notification( $user_id, $notify = '' ) {

	$user = get_userdata( $user_id );

	// The blogname option is escaped with esc_html on the way into the database in sanitize_option
	// we want to reverse this for the plain text arena of emails.
	$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

	if ( 'user' !== $notify ) {
		$switched_locale = switch_to_locale( get_locale() );
		$message  = sprintf( __( 'New user registration on your site %s:' ), $blogname ) . "\r\n\r\n";
		$message .= sprintf( __( 'Username: %s' ), $user->user_login ) . "\r\n\r\n";
		$message .= sprintf( __( 'Email: %s' ), $user->user_email ) . "\r\n\r\n";
		$message .= __( 'This user was created by PassClip Auth', PCA_TEXTDOMAIN ) . "\r\n";

		@wp_mail( get_option( 'admin_email' ), sprintf( __( '[%s] New User Registration' ), $blogname ), $message );

		if ( $switched_locale ) {
			restore_previous_locale();
		}
	}

	if ( 'admin' === $notify ) {
		return;
	}

	$message = sprintf( __( 'You are successfully registered to the site %s.', PCA_TEXTDOMAIN ), $blogname ) . "\r\n\r\n";
	$message .= wp_login_url() . "\r\n\r\n";

	wp_mail( $user->user_email, sprintf( __( '[%s] New User Registration' ), $blogname ), apply_filters( 'pca_new_user_noticemail', $message ) );
}


/**
 * Check if a user is made by PassClip Auth when the user want to make new password.
 * This plugin do not let to remake password if the user is made by this plugin.
 * As the password can be a cause of password hacking.
 *
 * @param WP_Error $errors
 * @return WP_Error || dont return  If error occured, return WP_Error. Else do nothing.
 */
function pca_dont_let_lostpassword( $errors ){

	if ( $errors->get_error_code() ){
		return $errors;
	}

	$user = get_user_by( 'email', trim( wp_unslash( $_POST['user_login'] ) ) );
	if ( ! $user ){
		$user = get_user_by('login', trim( $_POST['user_login'] ) );
	}

	$made_by_pca = get_user_meta( $user->ID, 'made_by_pca', true );
	if ( empty($made_by_pca) || 3 === $made_by_pca ){

	} else {
		return $errors->add( 'invalidcombo', __( '<strong>ERROR</strong>: You are not allowed login with wordpress password.', PCA_TEXTDOMAIN ));
	}
}
add_action( 'lostpassword_post', 'pca_dont_let_lostpassword' );
?>
