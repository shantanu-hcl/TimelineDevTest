<?php
/*
# Author: Sameeksha Agrawal
# Date Created:  18/06/2019
* This file is for integration with Codeignitor 
*/

require_once('ProposalSugarClient.php');
require_once('SugarClient.php');

$requestJSON = json_decode(file_get_contents('php://input'));

if ($requestJSON->request_type == 'Fetch') {
	
	/*$sugarClient = new SugarClient();
	$response = $sugarClient->authenticate('AgrawalSa','Test1234');
	print_R($response);
	die;*/
	$maconomyNo = $requestJSON->maconomyNo;
	$ProposalSugarClient = new ProposalSugarClient();
	$response = $ProposalSugarClient->findProposalByMaconomyNumber($maconomyNo,'webPage');
	echo $response;
	
} elseif ($requestJSON->request_type == 'Update') {
	$maconomyNo = $requestJSON->maconomyNo;
	$proposalId = $requestJSON->maconomyId;
	$lastDateModified = $requestJSON->lastDateModified;
	$startDate = $requestJSON->startDate;
	$closeDate = $requestJSON->closeDate;
	$estimatedCloseDate = $requestJSON->estimatedCloseDate;
	$ProposalSugarClient = new ProposalSugarClient();
	$updateResponse = $ProposalSugarClient->updateProposalByID($maconomyNo, $proposalId, $lastDateModified ,$startDate, $closeDate, $estimatedCloseDate);
	echo $updateResponse;
}