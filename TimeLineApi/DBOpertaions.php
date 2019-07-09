<?php
/*
# Author: Sameeksha Agrawal
# Date Created:  04/07/2019
# This class is the opertaion class performed on DynamoDB
*/

require_once('config.php');
require_once "..\vendor\autoload.php";
use Aws\DynamoDb\Exception\DynamoDbException;
use Aws\DynamoDb\Marshaler;

trait DBOpertaions{
   
	public function __construct()
	{
	
	}
	
	// Convert Object To array--
	public function object_to_array($data)
	{
		if (is_array($data) || is_object($data))
		{
			$result = array();
			foreach ($data as $key => $value)
			{
				$result[$key] = $this->object_to_array($value);
			}
			return $result;
		}
		return $data;
	}
	
	/**
	*	@Connect TO DynamoDB  
	*/
	public function connectDB()
	{
		global $config_cstm;
		
		$sdk = new Aws\Sdk([
			'region'   => $config_cstm['DBregion'],
			'version'  => $config_cstm['DBVersion']
		]);

		$dynamodb = $sdk->createDynamoDb();
		return $dynamodb;
	}


	/**
	* @param $username
	* @return array of tokens
	*/
	public function getTokens($username)
	{
		global $config_cstm;	
		$tableName = $config_cstm['table'];
		$dynamodb = $this->connectDB();
		$marshaler = new Marshaler();
		$key = $marshaler->marshalJson('
			{
				"username": "' . $username . '"
			}
		');

		$params = [
			'TableName' => $tableName,
			'Key' => $key
		];

		try {
			$result = $dynamodb->getItem($params);
			$response1 = $this->object_to_array($result);
			$statusCode =  $response1['@metadata']['statusCode'];
			if ($statusCode == '200') {
				return $result;
			} else {
				$response = array(
					"status" => "Fail",
					"msg" => "Unable to get tokens!"
				);
				return json_encode($response);
			}

		} catch (DynamoDbException $e) {
			$response = array(
				"status" => "Fail",
				"msg" => "Unable to get tokens!"
				);
			//echo $e->getMessage() . "\n";
			return json_encode($response);
		}
	}
	
	/**
	* @param $username, $access_token, $refresh_token, $download_token
	* @return array($status)
	*/

	public function putTokens($username,$access_token,$refresh_token,$download_token,$current_dateTime)
	{
		global $config_cstm;
		$tableName = $config_cstm['table'];
		
		$dynamodb = $this->connectDB();
		$marshaler = new Marshaler();
		$item = $marshaler->marshalJson('
			{
				 "username": "' . $username . '",
				"access_token": "' .$access_token .'",
				"refresh_token": "' . $refresh_token . '",
				"download_token": "' . $download_token . '",
				"access_token_time": "' . $current_dateTime . '",
				"refresh_token_time": "' .  $current_dateTime . '"
			}
		');

		$params = [
			'TableName' => $tableName,
			'Item' => $item
		];

		try {
			$result = $dynamodb->putItem($params);
			$response1 = $this->object_to_array($result);
			if ($response1 == '200') {
				$response = array(
						"status" => "Success",
						);
			} else {
				$response = array(
					"status" => "Fail",
					"msg" => "Connection refused"
				);
			}
			
		} catch (DynamoDbException $e) {
			//echo "Unable to add item:\n";
			//echo $e->getMessage() . "\n";
			$response = array(
					"status" => "Fail",
					"msg" => "Connection refused"
					);
		}
		return json_encode($response);
	}
}


?>
