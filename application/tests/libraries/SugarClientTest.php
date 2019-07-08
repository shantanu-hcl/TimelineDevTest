<?php
require_once('C:\xampp\htdocs\Timeline\application\libraries\SugarClient.php');
class SugarClientTest extends PHPUnit_Framework_TestCase
{
    protected $client;
	
	public function test_connectToDb()
	{
		$connObj = new SugarClient();
		$connObjResponse = $connObj->connectToDb();
		$this->assertEquals($connObjResponse,$connObjResponse);
	}
	
 	public function testgetBaseUrlFromConfig()
	{
		$connObj = new SugarClient();
		$connObjResponse = $connObj->getBaseUrlFromConfig();
		
		$this->assertEquals('https://sugar-stage.lightspeedone.com/rest/v11',$connObjResponse);
	}
	public function test___load_user_token_from_aws_Pass()
	{
		$connObj = new SugarClient();
		$connObjResponse = $connObj->__load_user_token_from_aws('AgrawalSa');
		$response = $connObjResponse['Item']['username']['S'];
		$this->assertEquals('AgrawalSa', $response);
	}
	public function test__refresh_token()
	{
		$connObj = new SugarClient();
		$loadUserTokenResponse = $connObj->__load_user_token_from_aws('AgrawalSa');
		$refreshToken = $loadUserTokenResponse['Item']['refresh_token']['S'];
		$connObjResponse = $connObj->__refresh_token($refreshToken,'AgrawalSa');
		$json_array = json_decode($connObjResponse);
		$this->assertEquals('Success', $json_array->status ); 
	}
		
	public function test_authenticateFail()
	{
		$connObj = new SugarClient();
		$connObjResponse = $connObj->authenticate('admin123','12345');
		$json_array = json_decode($connObjResponse);
		$response = $json_array->status;
		$this->assertEquals('Fail', $response);  
	}
	public function test_authenticatePass()
	{
		$connObj = new SugarClient();
		$connObjResponse = $connObj->authenticate('AgrawalSa','Test1234');
		$json_array = json_decode($connObjResponse);
		$response = $json_array->status;
		$this->assertEquals('Success', $response);
	}
}