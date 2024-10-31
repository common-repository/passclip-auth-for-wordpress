<?php

require_once ABSPATH . '/wp-admin/includes/class-wp-users-list-table.php';

class PCA_Users_List_Table extends WP_Users_List_Table{


	public function __construct( $args = array() ) {
		parent::__construct( array(
				'singular' => 'user',
				'plural'   => 'users',
				'screen'   => isset( $args['screen'] ) ? $args['screen'] : null,
		) );

		add_filter( 'manage_users_custom_column', array( $this, 'pca_user_info_column' ) ,10 ,3 );
		add_filter( 'users_list_table_query_args', array( $this, 'pca_where_pcainfo' ) );
	}

	/**
	 * Get a list of columns for the pca users list table.
	 *
	 * @return array Array in which the key is the ID of the column,
	 *               and the value is the description.
	 */
	public function get_columns() {
		$c = array(
				'cb'       => '<input type="checkbox" />',
				'username' => __( 'Username' ),
				'email'    => __( 'Email' ),
				'role'     => __( 'Role' ),
				'made_by_pca' => __( 'PassClip info', PCA_TEXTDOMAIN ),
				'pca_sent' => __('Sent PassClip Code', PCA_TEXTDOMAIN),
		);

		return $c;
	}


	/**
	 * Get a list of sortable columns for the pca users list table.
	 *
	 * @access protected
	 * @return array Array of sortable columns.
	 */
	protected function get_sortable_columns() {
		$c = array(
				'username' => 'login',
				'email'    => 'email',
		);

		return $c;
	}


	/**
	 * Return an associative array listing all the views that can be used
	 * with this table.
	 *
	 * Provides a list of info about passclip login.
	 *
	 * @access protected
	 * @return array An array of HTML links, one for each view.
	 */
	protected function get_views() {

		$pca_info = array( 1, 3, 0 );

		$url = 'admin.php?page=pca_manage_users';

		$total_users = $this -> pca_count_users();

		$class = ! isset( $_GET['pcainfo'] ) ? ' class="current"' : '';
		$pca_info_links = array();
		$pca_info_links['all'] = "<a href='$url'$class>" . sprintf( _nx( 'All <span class="count">(%s)</span>', 'All <span class="count">(%s)</span>', $total_users, 'users' ), number_format_i18n( $total_users ) ) . '</a>';

		foreach ( $pca_info as $made_by_pca ) {
			$class = '';

			$pca_users = $this -> pca_count_users( $made_by_pca );

			if ( isset( $_GET['pcainfo'] ) && $made_by_pca == $_GET['pcainfo']) {
				$class = ' class="current"';
			}

			// translators: pcainfo name with count
			$name = $this -> pca_get_text_pcainfo( $made_by_pca );
			$name = sprintf( __('%1$s <span class="count">(%2$s)</span>'), $name, number_format_i18n( $pca_users ) );
			$pca_info_links[$made_by_pca] = "<a href='" . esc_url( add_query_arg( 'pcainfo', $made_by_pca, $url ) ) . "'$class>$name</a>";
		}

		return $pca_info_links;
	}


	/**
	 * Get count of total users for current page.
	 * @param string $made_by_pca
	 * @return number
	 */
	private function pca_count_users( $made_by_pca = '' ){

		if ( $made_by_pca ){
			$args = array( 'meta_key' => 'made_by_pca',
							'meta_value' => 1 == $made_by_pca? array( 1, 2 ) : $made_by_pca,
							'meta_compare' => 'IN',
							'count_total' => true,
							'fields' => 'ID',
						);
		} elseif ( 0 === $made_by_pca ) {
			$args = array( 'meta_key' => 'made_by_pca',
							'meta_compare' => 'NOT EXISTS',
							'count_total' => true,
							'fields' => 'ID',
			);
		} else {
			$args = array( 'count_total' => true,
							'fields' => 'ID',
			);
		}

		$pca_count = new WP_User_Query( $args );
		return $pca_count -> total_users;
	}


	/**
	 * Make the content of "PassClip info" and "Sent PassClip Code"
	 *
	 * @param string $output
	 * @param string $column_name
	 * @param int $user_id
	 * @return Ambigous <string, mixed, boolean, multitype:, unknown, string>
	 */
	public function pca_user_info_column( $output, $column_name, $user_id ){
		if ( 'made_by_pca' == $column_name ){
			$output = get_user_meta( $user_id, 'made_by_pca', true);
			$output =  $this -> pca_get_text_pcainfo( $output );
		} elseif ('pca_sent'){
			$output = get_user_meta( $user_id, 'pca_sent_passclipcode', true);
			if( $output ){
				$output = esc_attr( $output );
			} else {
				$output = '';
			}
		}
		return $output;
	}


	/**
	 * Translate 'made_by_pca' to display message.
	 *
	 * @param int||blank $made_by_pca  1, 2, 3 or ''
	 * @return string message for user_meta 'made_by_pca'
	 */
	static function pca_get_text_pcainfo( $made_by_pca ){
		switch ( $made_by_pca ){
			case 1:
			case 2:
				return __( 'Made by Passclip Auth', PCA_TEXTDOMAIN );
				break;
			case 3:
				return __( 'Already logged in with PassClip(Existing User)', PCA_TEXTDOMAIN );
				break;
			default:
				return __( 'Not yet logged in with PassClip', PCA_TEXTDOMAIN );
				break;
		}
	}


	/**
	 * Make sql query to select users who used passclip or not.
	 * @param unknown $args
	 * @return multitype:number string
	 */
	public function pca_where_pcainfo( $args ){

		if ( isset( $_GET['pcainfo'] ) ){

			switch ( $_GET['pcainfo'] ){
				case 1:
				case 2:
					$args['meta_value'] = array( 1, 2 );
					$args['meta_key'] = 'made_by_pca';
					$args['meta_compare'] = 'IN';
					break;
				case 3:
					$args['meta_value'] = 3;
					$args['meta_key'] = 'made_by_pca';
					$args['meta_compare'] = '=';
					break;
				default:
					$args['meta_key'] = 'made_by_pca';
					$args['meta_compare'] = 'NOT EXISTS';
					break;
			}
		}

		return $args;
	}

	/**
	 * Retrieve an associative array of bulk actions available on this table.
	 *
	 * @access protected
	 * @return array Array of bulk actions.
	 */
	protected function get_bulk_actions() {
		return $actions;
	}

	protected function extra_tablenav( $which ) {
	}

	protected function bulk_actions( $which = '' ) {

		echo '<input hidden=true value="send_mail" name="pca_send_mail">';
		submit_button( __( 'Send PassClip Code', PCA_TEXTDOMAIN ), 'action', '', false, array( 'id' => "doaction" ) );
		echo "\n";
	}

}