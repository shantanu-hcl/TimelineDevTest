<?php

/*
# Author: Sameeksha Agrawal
# Date Created:  18/06/2019
# This class is the class for Searching Proposal and update the proposal details in CRM 
*/

require_once('SugarClient.php');
require_once('Curl.php');
require_once('SugarClientException.php');

class ProposalSugarClient extends SugarClient 
{
	use Curl;	
	use Unauthenticate_SugarClientException;
	
	public function __construct()
	{
		parent::__construct();
	}
	
	/*
	* @param $action
	* @return array(refreshToken, httpHeader=>array(Content-Type,oauth-token))
	*/
	public function getHttpHeaders($action)
	{
		$response = '';
		if ($action == 'Fetch' || $action == 'Update') {
			//-- Get Access token---
			global $config_cstm;
			$username = $config_cstm['sugar_userName'];
			$password = $config_cstm['sugar_hash'];
	
			$getAccessTokenExpiry = $this->__load_user_token_from_aws($username);

			if (isset($getAccessTokenExpiry['Item'])) {
				$accessToken = $getAccessTokenExpiry['Item']['access_token']['S'];	
				$refreshToken = $getAccessTokenExpiry['Item']['refresh_token']['S'];	
			} elseif (!isset($getAccessTokenExpiry['Item']) && !isset($getAccessTokenExpiry['status'])) {
				
				$getAccessTokenFromLogin = $this->authenticate($username, $password);
				$authTokensArray = json_decode($getAccessTokenFromLogin);
				if ($authTokensArray->status == 'Fail') {
						$response = $this->authException();
						return json_encode($response);
					
				} else {
					$accessToken = $authTokensArray->access_token;
					$refreshToken = $authTokensArray->refresh_token;	
				}	
			} elseif (isset($getAccessTokenExpiry['status']) && $getAccessTokenExpiry['status']=='Fail') {
				$response = $this->SugarException();
				return json_encode($response);
			}
			
			//-- End Access Token---
			$response = array(
				"refreshToken" => $refreshToken,
				"httpHeader" => array(
				"Content-Type: application/json",
				"oauth-token: {$accessToken}"
				)
			);
			
			return json_encode($response);
		}
	}
	
	/*
	* @param $maconomyNo, $source
	* Source will be webPage or UpdateFun
	* @return array of Proposal Details  
	*/
	public function findProposalByMaconomyNumber($maconomyNo, $source)
	{
		
		global $config_cstm;
		$username = $config_cstm['sugar_userName'];
		//-- Fetch Requested URL ---
		$curl_url = $this->getBaseUrlFromConfig();
		$proposal_url = $curl_url. "/LS010_Proposals/filter";
		$filter_arguments = array(
			"filter" => array(
				array(
						"maconomy_job_c" => array(
							'$equals'=>$maconomyNo,
						),
					),
			),
			"max_num" => 2,
			"offset" => 0,
			"fields" => 'id,name,proposal_id_c,maconomy_job_c,accounts_ls010_proposals_1_name,project_start_date_c,project_close_date_c,estimated_close_date_c,status_c,maconomy_status_c',
			"order_by" => "date_entered",
			"favorites" => false,
			"my_items" => false,
		);
		$proposal_url .= "?" . http_build_query($filter_arguments);
		//-- End Requested URL ---
		
		$method = 'GET';
		
		$httpHeaderResponse = $this->getHttpHeaders('Fetch');
		$httpHeaderResponseArr = json_decode($httpHeaderResponse);
		if(isset($httpHeaderResponseArr->status) && $httpHeaderResponseArr->status == 'Fail'){
				$response = $this->SugarException();	
				return json_encode($response);
		} else {
			$httpHeader = $httpHeaderResponseArr->httpHeader;
			$refreshToken = $httpHeaderResponseArr->refreshToken;
			$proposalResponse = $this->curlCall($proposal_url, $httpHeader, $filter_arguments, $method);
			$proposalJSON = json_decode($proposalResponse);
			
			if (isset($proposalJSON->error) && $proposalJSON->error_message == 'The access token provided is invalid.') {
				$authRefresh = $this->__refresh_token($refreshToken,$username);
				//--- Recursive call---
				return ($this->findProposalByMaconomyNumber($maconomyNo,'webPage'));
				//---END----	
			} elseif(empty($proposalJSON->records)) {
				
				$response = $this->FetchRecordException();	
				return json_encode($response);
			} else {
				$recordArray = $proposalJSON->records[0];
				if ($source == 'UpdateFun') {
					return $recordArray;
				} elseif ($source == 'webPage') {
					$proposalDetailsArray = array(
							"APIStatus" => "APISUCESS",
							"id" => $recordArray->id,
							"name" => $recordArray->name,
							"date_modified" => $recordArray->date_modified,
							"maconomy_job_c" => $recordArray->maconomy_job_c,
							"proposalNO" => $recordArray->proposal_id_c,
							"accountName" => $recordArray->accounts_ls010_proposals_1_name,
							"startDate" => $recordArray->project_start_date_c,
							"closeDate" => $recordArray->project_close_date_c,
							"estimatedCloseDate" => $recordArray->estimated_close_date_c,
							"status" => $recordArray->status_c,
							"maconomyStatus" => $recordArray->maconomy_status_c
						);
					return json_encode($proposalDetailsArray);
				}
			}
		}
		
	}
	
	/*
	* @param $maconomyNo, $proposalId, $lastDateModified, $startDate, $closeDate, $estimatedCloseDate
	* @return status (Success Or Fail)  
	*/
	public function updateProposalByID($maconomyNo, $proposalId, $lastDateModified ,$startDate, $closeDate, $estimatedCloseDate)
	{
		$proposalDetails = $this->findProposalByMaconomyNumber($maconomyNo, 'UpdateFun');
		$lastCopyTime = strtotime($lastDateModified);
		$currentCopyTime = strtotime($proposalDetails->date_modified);
		if ($lastCopyTime != $currentCopyTime) {
			return $this->findProposalByMaconomyNumber($maconomyNo, 'webPage');
		} else	{
			//---Updated Details---
			$proposalDetails->project_start_date_c = $startDate;
			$proposalDetails->project_close_date_c = $closeDate;
			$proposalDetails->estimated_close_date_c = $estimatedCloseDate;
			
			//-- Fetch Requested URL ---
			$curl_url = $this->getBaseUrlFromConfig();
			$proposal_url = $curl_url. "/LS010_Proposals/".$proposalId;
			//-- End Requested URL ---

			$method = 'PUT'; 
			$httpHeaderResponseUpdate = $this->getHttpHeaders('Update');
			
			$httpHeaderResponse = $this->getHttpHeaders('Fetch');
			$httpHeaderResponseUpArr = json_decode($httpHeaderResponseUpdate);
		
			$httpHeader = $httpHeaderResponseUpArr->httpHeader;
			$proposalResponse = $this->curlCall($proposal_url, $httpHeader, $proposalDetails, $method);
			$proposalJSON = json_decode($proposalResponse);
			if (!empty($proposalJSON->id)) {
				$response = array(
					"status" => "Success",
					"msg" => "Proposal updated successfully ."
					);
				return json_encode($response);
			} else {
				$response = $this->UpdateRecordException();
				return json_encode($response);
			}
			 
		}	
	}
}
?>