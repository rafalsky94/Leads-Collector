document.addEventListener( 'wpcf7submit', function( event ) {
	const data = {
		form_id: event.detail.contactFormId,
		inputs: event.detail.inputs,
	}
	fetch('/wp-content/plugins/newsletter-collector/collector.php',{
		method: "POST",
		body: JSON.stringify(data)
	});
}, false );
