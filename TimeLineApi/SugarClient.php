<?php
/*
# Author: Sameeksha Agrawal
# Date Created:  18/06/2019
# This class is the base class for client integrated with sugar 
*/
require_once('config.php');
require_once('DBOpertaions.php');
require_once('Curl.php');
require_once('SugarClientException.php');

class SugarClient
{
	use Curl;
	use DBOpertaions;
	use Unauthenticate_SugarClientException;
	
	public function __construct()
	{
		//parent::__construct();	
		$this->base_url = self::getBaseUrlFromConfig();
	}

	/*
	*	@return base url for API 
	*/
	public function getBaseUrlFromConfig()
	{
		// load the config
		global $config_cstm;
		$url = $config_cstm['sugar_base_url'];
		return $url;
	}
	
	/**
	* @param $username
	* @return array($itemList)
	*/
	public function __load_user_token_from_aws($username)
	{
		$tokens = $this->getTokens($username);
		return $tokens;
	}
	
	/*
	* @param $username
	* @return array($status, $access_token, $refresh_token, $download_token)
	*/
	public function authenticate($username, $password)
	{
		global $config_cstm;
		$curl_url = $this->getBaseUrlFromConfig();
		$auth_url = $curl_url . "/oauth2/token";
		$clientID = $config_cstm['sugar_clientID'];
		$clientSecret = $config_cstm['sugar_clientSecret'];
		$grantType = $config_cstm['sugar_grant'];
		$platform = $config_cstm['platform'];
		$httpHeader = array(
				"Content-Type: application/json"
			);
		$oauth2_payload = array(
			"grant_type" => $grantType,
			"client_id" => $clientID, 
			"client_secret" => $clientSecret,
			"username" => $username,
			"password" => $password,
			"platform" => $platform 
		);
		$method = "POST";
		$curl_response = $this->curlCall($auth_url, $httpHeader, $oauth2_payload, $method);
		$curl_response_array = json_decode($curl_response);
		if (isset($curl_response_array->access_token)) {
			$access_token = $curl_response_array->access_token;
			$refresh_token = $curl_response_array->refresh_token;
			$download_token =$curl_response_array->download_token;
			$responseArray = array(
				"status" => "Success",
				"access_token" => $access_token,
				"refresh_token" => $refresh_token,
				"download_token" => $download_token );
				
			//---put the All Token to dynamodb
			$current_dateTime = date('Y-m-d h:m:s');
			$dbRespone = $this->putTokens($username,$access_token,$refresh_token,$download_token,$current_dateTime);
			$dbResponeArr = json_decode($dbRespone);
			if($dbResponeArr->status == 'Success') {
				return json_encode($responseArray);
			} elseif ($dbResponeArr->status == 'Fail') {
				$response = $this->DBException();	
				return json_encode($response);
			}
			//------End------			
			
		} else {
			$response = $this->authException();	
			return json_encode($response);
		}
	}
		
	/*
	* @param $refresh_token
	* @return array($status, $access_token, $refresh_token, $download_token)
	*/
	public function __refresh_token($refresh_token,$username)
	{
		
		global $config_cstm;
		
		if (isset($refresh_token)) {
			$httpHeader = array(
					"Content-Type: application/json"
				);
			$oauth2_payload = array(
				"grant_type" => "refresh_token",
				"client_id" => 'sugar', 
				"client_secret" => '',
				"refresh_token" => $refresh_token,
			);
			
			$method = "POST";
			$curl_url = $this->getBaseUrlFromConfig();
			$auth_url = $curl_url . "/oauth2/token";
			$curl_response = $this->curlCall($auth_url, $httpHeader, $oauth2_payload, $method);
			$curl_response_array = json_decode($curl_response);
		
			if (isset($curl_response_array->error) && $curl_response_array->error_message == 'Invalid refresh token') {
				
				$password = $config_cstm['sugar_hash'];
				$getAccessTokenFromLogin = $this->authenticate($username, $password);
				return json_encode($getAccessTokenFromLogin);	
			}
			elseif (isset($curl_response_array->access_token)) {
				$access_token = $curl_response_array->access_token;
				$refresh_token = $curl_response_array->refresh_token;
				$download_token =$curl_response_array->download_token;
				$responseArray = array(
					"status" => "Success",
					"access_token" => $access_token,
					"refresh_token" => $refresh_token,
					"download_token" => $download_token );

				//---put the All Token to dynamodb
				$current_dateTime = date('Y-m-d h:m:s');
				$dbRespone = $this->putTokens($username,$access_token,$refresh_token,$download_token,$current_dateTime);
				$dbResponeArr = json_decode($dbRespone);
			
				if($dbResponeArr->status == 'Success') {
					return json_encode($responseArray);
				} elseif ($dbResponeArr->status == 'Fail') {
					$response = $this->DBException();	
					return json_encode($response);
				}
			//------End------	
			} else {
				$response = $this->SugarException();
				return json_encode($response);
			}	
		} 
	}	
}
?>
