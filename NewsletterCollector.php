<?php

class NewsletterCollector {

	function initialize() {
		$this->registerCollector();
		$this->install();
		$this->adminPage();

	}

	function post( $data ) {
		define( 'SHORTINIT', true );

		require( $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php' );
		global $wpdb;
		$table_name = $wpdb->prefix . "subscribed";
		$duplicate = $this->findDuplicate($table_name, $data['email'] );

		if ( !$duplicate ) {
			$wpdb->insert(
					$table_name,
					$data
			);
		} else {
			if ( strlen( $data['full_name'] ) > 0 ) {
				$wpdb->update(
						$table_name,
						array(
								'id'          => $duplicate,
								'form_id'     => $data['form_id'],
								'full_name'   => $data['full_name'],
								'email'       => $data['email'],
								'phone'       => $data['phone'],
								'parent_teen' => $data['parent_teen'],
						),
						array('id'=>$duplicate)
				);
			}
		}
	}

	private function findDuplicate($table_name, $email ) {
		global $wpdb;

		$id = false;

		$rows = $wpdb->get_results( "SELECT id, email FROM $table_name" );

		foreach ( $rows as $row ) {
			if ( $row->email === $email ) {
				$id = $row->id;
			}
		}

		return $id;
	}

	private function adminPage() {

		if ( isset( $_GET['export'] ) ) {
			$this->exportData();
		}

		function my_admin_menu() {
			add_menu_page(
					__( 'Newsletter Collector', 'newsletter-collector' ),
					__( 'Newsletter Collector', 'newsletter-collector' ),
					'manage_options',
					'newsletter-collector',
					'my_admin_page_contents',
					'dashicons-schedule',
					3
			);
		}

		add_action( 'admin_menu', 'my_admin_menu' );

		function my_admin_page_contents() {
			$rows = NewsletterCollector::getData();

			?>
			<div class="wrap">
				<h1>
					<?php esc_html_e( 'Subscribed people', 'newsletter-collector' ); ?>
				</h1>
				<p class="submit"><a href="admin.php?page=newsletter-collector&export=table&noheader=1"
									 class="button button-primary">Export to CSV</a></p>
				<hr class="wp-header-end">
				<table class="widefat fixed" cellspacing="0">
					<thead>
					<tr>
						<th id="columnname" class="manage-column column-columnname" scope="col">Form ID</th>
						<th id="columnname" class="manage-column column-columnname" scope="col">Form Title</th>
						<th id="columnname" class="manage-column column-columnname" scope="col">Full Name</th>
						<th id="columnname" class="manage-column column-columnname" scope="col">E-mail</th>
						<th id="columnname" class="manage-column column-columnname" scope="col">Phone</th>
						<th id="columnname" class="manage-column column-columnname" scope="col">Parent / Teen</th>

					</tr>
					</thead>

					<tfoot>
					<tr>

						<th id="columnname" class="manage-column column-columnname" scope="col">Form ID</th>
						<th id="columnname" class="manage-column column-columnname" scope="col">Form Title</th>
						<th id="columnname" class="manage-column column-columnname" scope="col">Full Name</th>
						<th id="columnname" class="manage-column column-columnname" scope="col">E-mail</th>
						<th id="columnname" class="manage-column column-columnname" scope="col">Phone</th>
						<th id="columnname" class="manage-column column-columnname" scope="col">Parent / Teen</th>

					</tr>
					</tfoot>

					<tbody>
					<?php
					foreach ( $rows as $key => $row ):
						?>
						<tr class="<?php if ( $key % 2 === 0 ) {
							echo 'alternate';
						} ?>">
							<td class="column-columnname"><?php echo ( ! empty( $row->form_id ) ) ? $row->form_id : ''; ?></td>
							<td class="column-columnname"><?php echo ( ! empty( $row->form_id ) ) ? get_the_title( $row->form_id ) : ''; ?></td>
							<td class="column-columnname"><?php echo ( ! empty( $row->full_name ) ) ? $row->full_name : ''; ?></td>
							<td class="column-columnname"><?php echo ( ! empty( $row->email ) ) ? $row->email : ''; ?></td>
							<td class="column-columnname"><?php echo ( ! empty( $row->phone ) ) ? $row->phone : ''; ?></td>
							<td class="column-columnname"><?php echo ( ! empty( $row->parent_teen ) ) ? $row->parent_teen : ''; ?></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			</div>

			<?php

		}
	}


	private function registerCollector() {
		wp_enqueue_script( 'catch-data', plugin_dir_url( __FILE__ ) . 'catchData.js', null );
	}


	private function install() {
		define( 'SHORTINIT', true );

		require( $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php' );
		global $wpdb;

		$table_name = $wpdb->prefix . "subscribed";

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			  	id mediumint(9) NOT NULL AUTO_INCREMENT,
                form_id mediumint(9) NOT NULL,
     	        full_name text NOT NULL,
  				email text NOT NULL,
  				phone text NOT NULL,
  				parent_teen text NOT NULL,
  				PRIMARY KEY  (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

	function getData() {
		define( 'SHORTINIT', true );

		require( $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php' );
		global $wpdb;

		return $wpdb->get_results( "SELECT * FROM wp_subscribed" );

	}

	private function exportData() {
		$table_head = array(
				'ID',
				'Form ID',
				'Form Title',
				'Full Name',
				'E-mail Address',
				'Phone Number',
				'Parent / Teen'
		);
		$table_body = $this->getData();


		$csv = implode( $table_head, ',' );

		$csv .= "\n";
		foreach ( $table_body as $row ) {
			$data = (array) $row;

			array_splice( $data, 2, 0, array( 'form_title' => get_the_title( $data['form_id'] ) ) );
			$csv .= implode( $data, ',' );
			$csv .= "\n";

		}
//
		$filename = 'leads-' . date( "Y-m-d-H:i:s" ) . '.csv';
		header( 'Content-Type: text/csv' ); // tells browser to download
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Pragma: no-cache' ); // no cache
		header( "Expires: Sat, 26 Jul 1997 05:00:00 GMT" ); // expire date

		echo $csv;
		exit;
	}

}
