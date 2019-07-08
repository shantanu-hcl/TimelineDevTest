<?php
require_once('C:\xampp\htdocs\Timeline_Project_Local\TimeLineApi\ProposalSugarClient.php');
require_once('C:\xampp\htdocs\Timeline_Project_Local\TimeLineApi\SugarClient.php');

class ProposalSugarClientTest extends PHPUnit_Framework_TestCase
{
    protected $client;
	
	public function testgetHttpHeadersWithFetchAction()
	{
		$connObj = new ProposalSugarClient();
		$connObj1 = new SugarClient();
		
		$connObjResponse = $connObj->getHttpHeaders('Fetch');
		/* $connObjResponse1 = $connObj1->__load_user_token_from_aws('AgrawalSa','Test1234');
		$json_array1 = json_decode($connObjResponse1);
		
		$this->assertEquals('Success', $json_array1->status ); */
		
		$json_array = json_decode($connObjResponse);
		print_r($json_array);
		
		$this->assertEquals('Success', $json_array->status );
		//$this->assertTrue("true");
	}
	
	/* public function testgetHttpHeadersWithFetchActionUsingCookie()
	{
		$connObj = new ProposalSugarClient();
		$connObj1 = new SugarClient();
		
		$connObjResponse = $connObj->getHttpHeaders('Fetch');

		$json_array = json_decode($connObjResponse);
		
		$this->assertEquals('Fail', $json_array->status );
		//$this->assertTrue("true");
	} */
	
	public function testfindProposalByMaconomyNumber()
	{
		$connObj = new ProposalSugarClient();
		
		$connObjResponse = $connObj->findProposalByMaconomyNumber('91231524', 'webPage');	
		print_r($connObjResponse);
		
		//$json_array = json_decode($connObjResponse); 
		
		
		
		
		$expected = '{"id":"324cb914-8d86-11e9-b75f-0a86afcfce28","name":"test complicated proposal","date_modified":"2019-06-24T10:30:01+05:30","maconomy_job_c":"91231524","proposalNO":2472,"accountName":"LIGHTSPEED RESEARCH GLOBAL","startDate":"2019-06-22","closeDate":"2019-06-26","estimatedCloseDate":"2019-06-30","status":"Closing Pending Actuals","maconomyStatus":"SUCCESS"}';

		$this->assertEquals($expected, $connObjResponse);
		//$this->assertTrue("true");
	} 
	
	/* public function testupdateProposalByID()
	{
		$connObj = new ProposalSugarClient();
		
		$connObjResponse = $connObj->updateProposalByID('Update');	
		
		$json_array = json_decode($connObjResponse);
		
		$this->assertEquals('Fail', $json_array->status );
		//$this->assertTrue("true");
	}*/
	
}
?>