<?php
/*
# Author: Sameeksha Agrawal
# Date Created:  18/06/2019
* This file is for integration with Codeignitor 
*/

require_once 'ProposalSugarClient.php';
require_once 'SugarClient.php';

$requestJSON = json_decode(file_get_contents('php://input'));

// To log Request & Response
$file = 'Logs\log_'.date('Y-m-d');
$request = '';
$WriteResponse = '';
$request .= "\n \n Request_".date('h:m:s');
file_put_contents($file, $request."-------".json_encode($requestJSON), FILE_APPEND);

if ($requestJSON->request_type == 'Fetch') {
	$maconomyNo = $requestJSON->maconomyNo;
	$ProposalSugarClient = new ProposalSugarClient();
	$response = $ProposalSugarClient->findProposalByMaconomyNumber($maconomyNo,'webPage');
	$WriteResponse .= "\n Response_".date('h:m:s');
	// Write the contents back to the file
	file_put_contents($file, $WriteResponse."-------". $response, FILE_APPEND);
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
	$WriteResponse .= "\n Response_".date('h:m:s');
	// Write the contents back to the file
	file_put_contents($file, $WriteResponse."-------". $updateResponse, FILE_APPEND);
	echo $updateResponse;
}
