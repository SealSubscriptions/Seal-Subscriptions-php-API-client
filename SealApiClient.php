<?php

class SealApiClient {
	private $token;
	private $secret;
	
	private $lastResponseHeaders 	= [];
	private $lastResponseCode 		= 0;

	public function __construct($token, $secret) {
		$this->token 	= $token;
		$this->secret	= $secret;
	}
	
	private function getEndpoint($path) {
		return "https://app.sealsubscriptions.com/shopify/merchant/api/".$path;
	}
	private function getHeaderAuth() {
		return 'X-Seal-Token: '.$this->token;
	}
	
	public function call($method, $path, $params = [], $debug = false, $verifyHmac = false) {
	
		$url 				= $this->getEndpoint($path);
		$query 				= in_array($method, ['GET','DELETE']) ? $params : [];
		$payload 			= in_array($method, ['POST','PUT']) ? json_encode($params) : [];
		$requestHeaders 	= in_array($method, ['POST','PUT']) ? ["Content-Type: application/json; charset=utf-8", 'Expect:'] : [];

		// Add authorization header
		$requestHeaders[] = $this->getHeaderAuth();

		// Make API request
		$response = $this->curlHttpApiRequest($method, $url, $query, $payload, $requestHeaders);

		// Get response code
		$responseCode = $this->getLastResponseCode();

		// IF response code isn't 200 OK, decode the output and return it
		if ($responseCode !== 200) {
			return json_decode($response, true);
		}

		// You can verify the HMAC of each API response 
		if ($verifyHmac) {
			// Verify HMAC so that we can be sure that the content wasn't tempered with
			$hmacVerified = false;
			
			$responseHeaders = $this->getLastHeaders();
			if (!empty($responseHeaders['x-seal-hmac-sha256'])) {
				$hmacVerified = $this->verifyHmac($responseHeaders['x-seal-hmac-sha256'], $response, $this->secret);
			}
			
			if ($hmacVerified !== true) {
				throw new SealApiException('HMAC does\'t match.');
			}
		}

		// Json decode the response
		$decodedResponse = json_decode($response, true);

		if (!empty($decodedResponse)) {
			// The response was a valid JSON
			return $decodedResponse;
		} else {
			// The response doesn't seem to be a valid JSON. Output it without decoding.
			return $response;
		}
	}

	private function curlHttpApiRequest($method, $url, $query = [], $payload = '', $requestHeaders = []) {
		$url 		= $this->curlAppendQuery($url, $query);
		$ch 		= curl_init($url);
		$this->curlSetopts($ch, $method, $query, $payload, $requestHeaders);
		$response 	= curl_exec($ch);
		$httpcode 	= curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$errno 		= curl_errno($ch);
		$error 		= curl_error($ch);
		curl_close($ch);

		if ($errno) throw new \Exception($error, $errno);

		// Split and use the last part as body and one before that as headers
		$responseParts = preg_split("/\r\n\r\n|\n\n|\r\r/", $response);

		$message_headers 	= $responseParts[count($responseParts)-2];
		$message_body 		= $responseParts[count($responseParts)-1];
		
		$this->lastResponseHeaders 	= $this->curlParseHeaders($message_headers);
		$this->lastResponseCode 	= $httpcode;

		return $message_body;
	}
	
	
	public function getLastHeaders() {
		return $this->lastResponseHeaders;
	}
	
	public function getLastResponseCode() {
		return $this->lastResponseCode;
	}
	
	// Verify if the HMAC signature is valid (used for webhooks, as it reads the $_SERVER variable directly
	public function isWebhookHmacValid($data) {
		if (empty($_SERVER['HTTP_X_SEAL_HMAC_SHA256'])) {
			return false;
		}
		
		$hmac = $_SERVER['HTTP_X_SEAL_HMAC_SHA256'];
		return $this->verifyHmac($hmac, $data, $this->secret);
	}
	
	// Calculate HMAC and compare it with the original value
	public function verifyHmac($originalHmac, $data, $secret) {
		$calculatedHmac = base64_encode(hash_hmac('sha256', $data, $secret, true));
		return hash_equals($originalHmac, $calculatedHmac);
	}
	
	// Append GET query to the URL
	private function curlAppendQuery($url, $query) {
		if (empty($query)) return $url;
		if (is_array($query)) return "$url?".http_build_query($query);
		else return "$url?$query";
	}

	private function curlSetopts($ch, $method, $query, $payload, $requestHeaders) {
		
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 4);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Seal-REST-API-php-client');
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_TIMEOUT, 15);

		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $requestHeaders);

		curl_setopt ($ch, CURLOPT_POSTFIELDS, $payload);
	}

	private function curlParseHeaders($message_headers) {
		$header_lines = preg_split("/\r\n|\n|\r/", $message_headers);
		$headers = array();
		
		$statusMessage = explode(' ', trim(array_shift($header_lines)), 3);
		$headers['http_status_code'] = '';
		$headers['http_status_message'] = '';
		
		if (!empty($statusMessage[1])) {
			$headers['http_status_code'] = $statusMessage[1];
		}
		
		if (!empty($statusMessage[2])) {
			$headers['http_status_message'] = $statusMessage[2];
		}
		
		foreach ($header_lines as $header_line) {
			list($name, $value) = explode(':', $header_line, 2);
			$name = strtolower($name);
			$headers[$name] = trim($value);
		}

		return $headers;
	}
}

class SealApiException extends \Exception {}
?>
