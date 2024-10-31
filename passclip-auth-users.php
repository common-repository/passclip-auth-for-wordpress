<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 *
 * @param bool $show_screen
 * @param screen_id $screen
 * @return boolean if our objective page, returns true, else returns itself.
 */
function pca_add_screen_option_in_manage_users( $show_screen, $screen ){
	if ( 'passclip-auth_page_pca_manage_users' == $screen -> id ){
		$screen -> add_option( 'per_page' );
		return true;
	}

	return $show_screen;
}
add_filter( 'screen_options_show_screen', 'pca_add_screen_option_in_manage_users', 10, 2 );


/**
 *
 * @param int $per_page
 * @return int per_page  if there is posted value, returns it. Else, return users_per_page or itself.
 */
function pca_set_perpage( $per_page ){
	$_per_page = (int) get_user_option( 'users_per_page' );
	if ( isset( $_POST['wp_screen_options'] ) && $_POST['wp_screen_options']['value'] > 0 && $_per_page != $_POST['wp_screen_options']['value']){
		if ( is_numeric( $_POST['wp_screen_options']['value'] ) ){
			update_user_option( get_current_user_id(), 'users_per_page', $_POST['wp_screen_options']['value'] );
			return $_POST['wp_screen_options']['value'];
		}
	}
	if ( empty( $_per_page ) || $_per_page < 1 )
		return $per_page;

	return $_per_page;
}
add_filter( 'passclip_auth_page_pca_manage_users_per_page', 'pca_set_perpage' );

/**
 * PCA manage users page.
 */
function pca_manage_users(){

	if ( ! pca_options_get( PCA_APP_SERVICE_ID ) ){
		_e( 'You can manage your members here after you set your PCA app service id.', PCA_TEXTDOMAIN );
		exit;
	}

	require_once 'class-pca-users-list-table.php';

	if ( isset( $_POST['pca_send_mail'] ) && 'send_mail' == $_POST['pca_send_mail'] ){
		if( isset( $_POST['users'] ) && ! empty($_POST['users']) ){
?>
			<form method="post" >
			<div class="wrap">
			<h1><?php _e( 'Send PassClip Code to these users.', PCA_TEXTDOMAIN ); ?></h1>
			<ul>
<?php
			foreach ( $_POST['users'] as $user_id ){
				$user = get_userdata( $user_id );
				$user_pca = PCA_Users_List_Table::pca_get_text_pcainfo( get_user_meta( $user_id, 'made_by_pca', true ) );

				echo '<li><input type="hidden" name="users[]" value="' . esc_attr($user_id) . '" />' . sprintf(__('ID #%1$s: %2$s: %3$s'), $user_id, $user->user_login, $user_pca) . '</li>';
			}
?>
			</ul>
			<input type="hidden" name="pca_do_send_mail" value="do_send_mail" >
			<?php submit_button( __( 'Send Mail', PCA_TEXTDOMAIN ) ); ?>
			</div>
			</form>
<?php
			exit;
		}

	} elseif( isset($_POST['pca_do_send_mail']) && 'do_send_mail' == $_POST['pca_do_send_mail'] ){

		$email_results = pca_send_passclip_code( $_POST['users'] );
?>
		<div id="message" class="updated notice is-dismissible fade">
		<ul>
<?php
		foreach( $email_results as $ID => $email_result ){
			if ( $email_result ){
?>
				<li>#<?php echo esc_attr( $ID ); ?>:<?php _e( 'Sent PassClip Code successfully.', PCA_TEXTDOMAIN ); ?></li>
<?php
			} else {
?>
				<li>#<?php echo esc_attr( $ID ); ?>:<?php _e( 'Failed in sending emails.', PCA_TEXTDOMAIN );?></li>
<?php
			}
		}
?>
		</ul></div>
<?php
	}

		$pca_list_table = new PCA_Users_List_Table( array( 'screen' => 'pca_user_manage' ) );
		$pagenum = $pca_list_table->get_pagenum();

		$pca_list_table->prepare_items();
		$total_pages = $pca_list_table->get_pagination_arg( 'total_pages' );
		if ( $pagenum > $total_pages && $total_pages > 0 ) {
			wp_redirect( add_query_arg( 'paged', $total_pages ) );
			exit;
		}

?>
		<div class="wrap">
		<h1 class="wp-heading-inline"> <?php _e('PassClip Auth(PCA) for Wordpress Manage Users', PCA_TEXTDOMAIN); ?> </h1>

		<hr class="wp-header-end">
<?php
		$pca_list_table->views();
?>
		<form method="post" action="">
<?php
		if ( isset($_GET['pcainfo']) ){
?>
		<input type="hidden" name="pcainfo" value="<?php echo esc_attr( $_GET['pcainfo'] ); ?>" />
<?php
		}
		$pca_list_table->display();
?>
		</form>
		<br class="clear" />
		</div>
<?php
}


/**
 *
 *
 * @param array $user_ids
 */
function pca_send_passclip_code( $user_ids ){
	if ( empty( $user_ids ) ){
		return;
	}

	$_users = get_users( array( 'include' => $user_ids,
								'fields' => array( 'ID', 'user_email'), ) );

	$pca_mails = pca_options_get( PCA_OPTION_MAILS );

	$subject = pca_make_mail( $pca_mails['subject'] );
	$message = pca_make_mail( $pca_mails['message'] );

	$result = array();

	foreach( $_users as $_user ){
		$result[$_user -> ID] =  wp_mail( $_user -> user_email, $subject, $message );
		if( $result[$_user -> ID] ){
			update_user_meta( $_user -> ID, 'pca_sent_passclipcode', date_i18n( get_option('date_format').get_option('time_format'), false, false ) );
		}
	}

	return $result;
}


/**
 *
 * @param string $text  mail subject or mail body.
 * @return string $text  tagged replaced
 */
function pca_make_mail( $text ){
	$text = str_replace( '[passclipcode]', pca_options_get(PCA_PASSCLIP_CODE), $text );
	$text = str_replace( '[blogname]', wp_specialchars_decode( get_option('blogname'), ENT_QUOTES ), $text );
	$text = str_replace( '[passclip_link]', pca_options_get(PCA_PASSCLIP_SITE_URL), $text );
	return $text;
}
?>
