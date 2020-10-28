<?php
/*
Plugin Name: Newsletter Collector
Description: Plugin made for collecting leads.
Author:      Rafał Batorowicz @ Fireart Studio
*/
require($_SERVER['DOCUMENT_ROOT']  .'/wp-content/plugins/newsletter-collector/NewsletterCollector.php' );

function init() {
	global $nc;

	if ( ! isset( $nc ) ):
		$nc = new NewsletterCollector();
		$nc->initialize();
	endif;
}

init();
