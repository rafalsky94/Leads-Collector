<?php
require($_SERVER['DOCUMENT_ROOT']  .'/wp-content/plugins/newsletter-collector/NewsletterCollector.php' );


$inputs = json_decode( file_get_contents( 'php://input' ), true );

$data = array();

foreach ( $inputs['inputs'] as $key => $input ) {
	$data[ $input['name'] ] = $input['value'];
}

$db_data = array(
	'form_id'     => $inputs['form_id'],
	'full_name'   => ( isset( $data['full_name'] ) ) ? $data['full_name'] : '',
	'email'       => ( isset( $data['email'] ) ) ? $data['email'] : '',
	'phone'       => ( isset( $data['phone'] ) ) ? $data['phone'] : '',
	'parent_teen' => ( isset( $data['parent-teen'] ) ) ? $data['parent-teen'] : ''
);

$nc = new NewsletterCollector();
$nc->post($db_data);
