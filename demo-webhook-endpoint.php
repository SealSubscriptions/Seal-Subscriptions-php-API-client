<?php

	require_once dirname(__FILE__).'/SealApiClient.php';
	
	$SealApiClient = new SealApiClient('YOUR_SEAL_TOKEN', 'YOUR_SEAL_SECRET');
	
	$webhookContent = '';
	$webhook = fopen('php://input' , 'rb');

	while (!feof($webhook)) {
		$webhookContent .= fread($webhook, 4096);
	}
	fclose($webhook);

	if ($SealApiClient->isWebhookHmacValid($webhookContent) !== true) {
		// Validate the HMAC sent inthe headers, to make sure that the request was sent by Seal Subscriptions and not by somebody else
		die('HMAC is not valid');
	}
	
	// $_SERVER['HTTP_X_SEAL_TOPIC'] contains the topic of the webhook (subscription/created, subscription/updated)
	
	// Decode the webhook payload
	$jsonPayload = json_decode($webhookContent, true);
	
	// TODO: Do something with the JSON payload
?>
