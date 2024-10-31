<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


/**
 * Add javascript and its params in login form.
 */
function pca_login_form_scripts(){

	if ( pca_options_get( PCA_APP_SERVICE_ID ) ){

		$pca_options_lostpassword = pca_options_get( PCA_OPTION_HIDE_LOSTPASSWORD_LINK );

		wp_enqueue_style( 'pca-login-form', PCA_CSS_URL . '/pca-login-form.css' );
		wp_enqueue_script('pca-login-form', PCA_JS_URL . '/pca-login-form.js', array('jquery') );
		wp_localize_script('pca-login-form', 'pca_password_form', array( 'pca_password_id' => 'user_pass',
							'pca_lostpassword_url' => ('yes' == $pca_options_lostpassword )? wp_lostpassword_url() : '',
							'pca_password_placeholder' => (isset( $_GET['pcamode'] ) && 'templogin' == $_GET['pcamode']) || isset($_POST['pca_login_code']) ?
									'' : __( 'Enter the password showed by the slot for this site in passclip', PCA_TEXTDOMAIN) )
							);
	}
}
add_action( 'login_enqueue_scripts', 'pca_login_form_scripts');


/**
 * Creating login form
 * if admin set "dont show lostpassword link", that link will not appear.
 *
*/
function pca_make_login_form() {
	$pca_options = pca_options_get();
	if ( empty($pca_options[PCA_APP_SERVICE_ID]) ){
		return;
	}

	if ( (isset( $_GET['pcamode'] ) && 'templogin' == $_GET['pcamode'] ) || isset($_POST['pca_login_code']) ) {
		$username = esc_attr(wp_unslash( isset( $_GET['log'] )? $_GET['log']:$_POST['log'])) ;
		?>
	<p>
		<label for="pca_login_code"><?php _e('Login code', PCA_TEXTDOMAIN) ?>
		<input type="password" name="pca_login_code" id="pca_login_code" class="input" value="" size="20" /></label>
	</p>
	<script type="text/javascript">document.getElementById("user_login").value = "<?php echo $username; ?>";</script>
<?php
		// Sent the logincode to user by email
		pca_send_login_code( $username );
	}

	pca_show_passclip_logo_and_mark( $pca_options, 100 );

}
add_action( 'login_form', 'pca_make_login_form');


/**
 *
 * @param array $pca_options
 * @param int $width
 */
function pca_show_passclip_logo_and_mark( $pca_options, $width ){

	if ( ! is_numeric($width) ){
		return;
	}

	_e( '<p>You need <strong><font color="#fa0">PassClip</font></strong> to log in.</p>', PCA_TEXTDOMAIN);

	if ( 'yes' == $pca_options[PCA_OPTION_SHOW_PASSCLIP_CODE] ){
		echo __( '<p>If you do not have PassClip, please get from ', PCA_TEXTDOMAIN )
		. '<a target="blank" href="' . esc_url( __( $pca_options[PCA_PASSCLIP_SITE_URL], PCA_TEXTDOMAIN ) )
		. __( '"><strong>here</strong></a>.</p>', PCA_TEXTDOMAIN );
		printf( __( '<p>You can know how to log in with PassClip from <a target="_blank" href="%s">this article.</a></p>', PCA_TEXTDOMAIN ),
				__( $pca_options[PCA_PASSCLIP_HOWTOLOGIN], PCA_TEXTDOMAIN ) );
?>
		<div id="passclip_mark" style="position:relative; max-width:600px; " >
		<a target="blank" href="<?php echo esc_url( __( $pca_options[PCA_PASSCLIP_HOWTOLOGIN], PCA_TEXTDOMAIN ) ); ?>">
		<img width="<?php echo $width; ?>%" class="pca_code_mark" src="<?php echo $pca_options[PCA_AUTH_MEMBER_SITE_URL] . "mark.php"; ?>" />
		</a>
		<div style="position:absolute; top:10%; left:<?php echo ($width / 4);?>%; "  > <?php echo esc_html( $pca_options[PCA_PASSCLIP_CODE] ); ?> </div>
		</div><br>
<?php
	}
}


/**
 * This is the method that is errors filter on WordPress login page
 *
 * @param errors
 *				WP Error object
 * @param redirect_to
 *				redirect destination URL
 * @return WP Error object
 */
function pca_filter_login_errors( $errors, $redirect_to ) {

	if ( isset( $_GET['pcamode'] ) && 'templogin' == $_GET['pcamode'] ) {
		$errors->add( 'pcamode_templogin', __('Input the temporary login code that was sent to your email to log in without PassClip Auth.', PCA_TEXTDOMAIN), 'message');
	}
	return $errors;
}
add_filter( 'wp_login_errors', 'pca_filter_login_errors', 10, 2);