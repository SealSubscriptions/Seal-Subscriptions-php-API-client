<?php

	/*
		This is a sample script which you can use to retrieve subscriptions from Seal Subscriptions app via API. 
		To retrieve subscriptions and use the API, you need the Seal API token and Seal API secret, which can be found 
		in Seal Subscriptions app > Settings > General Settings > API.
	*/

	require_once dirname(__FILE__).'/SealApiClient.php';
	
	// Set the content type if you are testing this in the browser, so that the output will be nicely formatted.
	header('Content-Type: application/json; charset=utf-8');

	
	// Initialize the SealApiClient class with your Seal API token and Seal API secret.
	$SealApiClient = new SealApiClient('YOUR_SEAL_TOKEN', 'YOUR_SEAL_SECRET');

	try {
		// Here are a few different sample API calls. Each call is wrapped in comments. To try them out, just uncomment the one you want to use and run the script.
		
		// Get first page of subscriptions in the shop
		$response = $SealApiClient->call('GET', 'subscriptions');
		
		/*
		// Get second page of subscriptions in a shop
		$response = $SealApiClient->call('GET', 'subscriptions', [
			'page' => 1
		]);
		*/
		
		/*
		// Get first page of subscriptions that mach your search parameter. You can search by email, name and last name.
		$response = $SealApiClient->call('GET', 'subscriptions', [
			'page' => 1,
			'query' => 'john'
		]);
		*/
	
		/*
		// Get a specific subscription by it's ID
		$response = $SealApiClient->call('GET', 'subscription', [
			'id' => 123456789 // TODO: Replace this ID with the ID of the subscription you want to retrieve
		], true);
		*/		
		
		/*
		// Create a webhook to listen for new subscriptions
		$response = $SealApiClient->call('POST', 'webhooks', [
			'topic' 	=> 'subscription/created',
			'address' 	=> 'YOUR_HTTPS_ENDPOINT',
		], true);
		*/
		
		/*
		// Create a webhook to listen for changes in existing subscriptions
		$response = $SealApiClient->call('POST', 'webhooks', [
			'topic' 	=> 'subscription/updated',
			'address' 	=> 'YOUR_HTTPS_ENDPOINT',
		], true);
		*/
		
		
		/*
		// Get all webhooks
		$response = $SealApiClient->call('GET', 'webhooks', [], true);
		*/
		
		/*
		// Delete a webhook
		$response = $SealApiClient->call('DELETE', 'webhooks', [
			'id' => 123456789
		], true);
		*/
		
		echo json_encode($response, true);
		
		
	} catch(\SealApiException $e) {
		echo $e->getMessage();
	} catch(\Exception $e) {
		echo $e->getMessage();
	}

	
	
	
?>
