<?php
/**
 * passclip auth login widget.
 */
class WP_Widget_passclip_auth_login extends WP_Widget {

	function __construct() {
		$widget_name = __( 'PassClip Login Form', PCA_TEXTDOMAIN );
		add_action( 'wp_loaded', array( $this, 'pca_authenticate_from_widget' ), 9999 );

		parent::__construct(
				'pca_passclip_login_form',
				$widget_name,
				array( 'description' => __( 'Login form for PassClip Auth', PCA_TEXTDOMAIN ) )
		);
	}

	function widget( $args, $instance ) {

		echo $args['before_widget'];

		if( ( is_user_logged_in() && !is_multisite() ) || ( is_multisite() && is_user_member_of_blog() ) ){
			$form = '<div><a href="' . $this->pca_make_link( $instance['link'] ) . '">' . get_avatar( get_current_user_id(), 50 ) . '<h4>' . get_userdata( get_current_user_id() )->display_name . '</h4></a></div>
			<form name="pca_login_widget" id="pca_login_widget" action="' . esc_url( wp_logout_url( $_SERVER['REQUEST_URI'] ) ) . '" method="post">'
			.'<p class="logout-submit">
				<input type="submit" name="wp-submit" id="pca_logout_submit" class="button button-primary" value="' . __( 'Log Out', PCA_TEXTDOMAIN ) . '" />
			</p>
			</form>';
		} else {
			$form = '<form name="pca_login_widget" id="pca_login_widget" action="" method="post">' .
					wp_nonce_field( 'pca_widget_login', 'pca_widget_login', true, false ) .
				'<p class="login-username">
					<label for="pca_login_name">' . __( 'Username or Email Address', PCA_TEXTDOMAIN ) . '</label>
					<input type="text" name="log" id="pca_login_name" class="input" value="" size="20" />
				</p>
				<p class="login-password">
					<label for="pca_login_password">' . __( 'Password', PCA_TEXTDOMAIN ) . '</label>
					<input type="password" name="pwd" id="pca_login_password" class="input" value="" size="20" placeholder="" />
				</p>' .
			    '<p class="forgetmenot"><label for="rememberme">' . __( 'Remember Me', PCA_TEXTDOMAIN ) . '</label><input name="rememberme" type="checkbox" id="rememberme" value="forever"  />'.
				'<p class="login-submit">
					<input type="submit" name="wp-submit" id="pca_login_submit" class="button button-primary" value="' . __( 'Log In', PCA_TEXTDOMAIN ) . '" />
					<input type="hidden" name="redirect_to" value="' . $_SERVER['REQUEST_URI'].  '" />
				</p>
			</form>';
		}

		if ( !empty( $GLOBALS['pca_widget_login_error'] ) ){
			echo $GLOBALS['pca_widget_login_error'] ;
			unset( $GLOBALS['pca_widget_login_error'] );
		}

		echo apply_filters( 'pca_passclip_login_form', $form );

		if ( ! is_user_logged_in() || ( is_multisite() && ! is_user_member_of_blog() ) ){
			pca_make_login_form();
		}
		echo $args['after_widget'];
	}


	/**
	 * make url using the current user information.
	 * @param string $url
	 * @return string modified url
	 */
	function pca_make_link( $url ){
		if ( ! empty( $url ) ){
			$url = str_replace( '[user_nicename]', get_userdata( get_current_user_id() )->user_nicename, $url );
			$url = str_replace( '[user_login]', get_userdata( get_current_user_id() )->user_login, $url );
			$url = str_replace( '[display_name]', get_userdata( get_current_user_id() )->display_name, $url );
		}
		return apply_filters( 'pca_make_link', $url );
	}


	function form( $instance ) {
		$defaults = array( 'link' => '' );

		$instance = wp_parse_args( (array) $instance, $defaults );
?>
		<p>
			<label for="<?php echo $this->get_field_id( 'link' ); ?>"><?php _e( 'Link from username:', PCA_TEXTDOMAIN ); ?>
			<input class="widefat" id="<?php echo $this->get_field_id( 'link' ); ?>" name="<?php echo $this->get_field_name( 'link' ); ?>" type="text" value="<?php echo esc_attr( $instance['link'] ); ?>" /></label>
		</p>
<?php
		_e( 'When logged in, the displayed user name has link to this url. [user_nicename],[user_login],[display_name] will be replaced by the data of the current user.', PCA_TEXTDOMAIN );
		echo '<br/>';
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['link'] = esc_attr( $new_instance['link'] );
		return $instance;
	}

	/**
	 * Call wp_signin after wp_loaded.
	 * enable authenticate without wp-login.php.
	 */
	function pca_authenticate_from_widget(){
		if (  isset( $_POST['pca_widget_login'] ) && wp_verify_nonce( $_POST['pca_widget_login'], 'pca_widget_login' ) ){
			$pca_option_redirect = pca_options_get( PCA_OPTION_WIDGET_REDIRECT );

			if ( is_email( $_POST['log'] ) ){
				$pca_user_exists = false;
				if ( get_user_by( 'email', $_POST['log'] ) ){
					$pca_user_exists = true;
				}
			}
			$user = wp_signon();

			if ( ! is_wp_error( $user ) ){
				wp_set_current_user( $user->ID, $user->display_name );
				if ( ! empty( $pca_option_redirect ) && ! $pca_user_exists ){
					wp_redirect( $this -> pca_make_link( $pca_option_redirect ) );
					exit;
				}
			} else {
				//login failed.
				$errors = $user->get_error_messages();
				$GLOBALS['pca_widget_login_error'] = '';
				foreach( $errors as $error ){
					$GLOBALS['pca_widget_login_error'] .= $error . '<br>';
				}
			}
		}
	}
}
add_action( 'widgets_init', function(){ return register_widget( "WP_Widget_passclip_auth_login" );} );