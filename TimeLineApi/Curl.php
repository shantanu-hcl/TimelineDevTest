<?php
/*
# Author: Sameeksha Agrawal
# Date Created:  04/07/2019
# This class is the base class to make connection with curl 
*/
require_once('config.php');
require_once('DBOpertaions.php');

trait Curl
{
	/**
	* Make Curl Connect
	**/
	
	public function curlCall($curl_url , $http_header, $payload, $method)
	{ 
		$auth_curl_request = curl_init();
		if ($method == "POST" || $method == "PUT") {
			$json__payload = json_encode($payload);
			if($method == "PUT") {
				curl_setopt($auth_curl_request, CURLOPT_CUSTOMREQUEST, "PUT");
			}
			curl_setopt($auth_curl_request, CURLOPT_POSTFIELDS, $json__payload);
		}
		curl_setopt($auth_curl_request, CURLOPT_URL, $curl_url);
		curl_setopt($auth_curl_request, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
		curl_setopt($auth_curl_request, CURLOPT_HEADER, false);
		curl_setopt($auth_curl_request, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($auth_curl_request, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($auth_curl_request, CURLOPT_FOLLOWLOCATION, 0);
		curl_setopt($auth_curl_request, CURLOPT_HTTPHEADER, $http_header);

		//execute request
		$curl_response = curl_exec($auth_curl_request);
		//print_R($curl_response);
		return $curl_response;
	}

}