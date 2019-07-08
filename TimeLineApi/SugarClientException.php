<?php
/**
*	All Sugar Client Exception should be writtien in here
*/
trait Unauthenticate_SugarClientException //extends Exceptions
{
	/*
	* @return authentication exceptions
	*/
	public function authException(){
		$response = array(
					"status" => "Fail",
					"msg" => "You must specify a valid username and password"
					);
		return json_encode($response);
	}
	
	/*
	* @return DB exceptions
	*/
	public function DBException(){
		$response = array(
					"status" => "Fail",
					"msg" => "Connection refused"
					);
		return json_encode($response);
	}
	
	/*
	* @return SugarException exceptions
	*/
	public function SugarException(){
		$response = array(
					"status" => "Fail",
					"msg" => "Something went wrong. Please try again !"
					);
		return json_encode($response);
	}
	
	/*
	* @return Sugar Fetch Proposal exceptions
	*/
	public function FetchRecordException(){
		$response = array(
					"status" => "Fail",
					"msg" => "No Proposal Found"
					);
		return json_encode($response);
	}
	
	/*
	* @return Sugar Update Proposal exceptions
	*/
	public function UpdateRecordException(){
		$response = array(
					"status" => "Fail",
					"msg" => "Can't update the proposal,Please contact to Administrator"
					);
		return json_encode($response);
	}
}

?>