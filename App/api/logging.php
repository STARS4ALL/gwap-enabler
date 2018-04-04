<?php
/* 
 * (C) Copyright 2017 CEFRIEL (http://www.cefriel.com/).
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * Contributors:
 *     Andrea Fiano, Gloria Re Calegari, Irene Celino.
 */
include 'db_connect.php';
include 'functions.php';
include 'jwt.php';

if(!isAuthorized()) {
	return;
}
	
$postdata = file_get_contents("php://input");
$request = json_decode($postdata);

$idUser = $request->idUser;
$idTopicSelected = $request->idTopic;
$idResource = $request->idResource;
$idRound = $request->idRound;
$level = $request->level;	
$games = $request->json;

$trueResponse = array();
$isGT = 0;
$consecutiveAnswer = -1;
$score = 0;
$nErrors = 0;
$nGTErrors = 0;
$played = 0;

$parameters = parameters($mysqli);

$levelPoints = $parameters["levelPoints"];
$consecutiveLevelPoints = $parameters["consecutiveLevelPoints"];

$query = "SELECT idTopicTrue, T.label AS topicLabel, isGT, score, consecutiveAnswer, nErrors, nGTErrors, played
		 FROM true_response TR
		 JOIN topic T on TR.idTopicTrue = T.idTopic
		 WHERE idRound = '$idRound' AND level = $level
		 ";
			
$result = $mysqli->query($query) or die($mysqli->error.__LINE__);

// GOING THROUGH THE DATA
if($result->num_rows > 0) {
	while($row = $result->fetch_assoc()) {
		$trueResponse["idTopicTrue"] = $row['idTopicTrue'];
		$trueResponse["topicLabel"] = $row['topicLabel'];
		$isGT = $row['isGT'];
		$score = $row['score'];
		$consecutiveAnswer = $row['consecutiveAnswer'];	
		$nErrors = $row['nErrors'];	
		$nGTErrors = $row['nGTErrors'];	
		$played = $row['played'];	
	}
} else {
	$noRows = true;
}

if($played == 1) {
	echo 'Livello già giocato';
	return;
}

foreach($games as $gameItem){
	$idTopic = $gameItem->idTopic;
	$result = ($trueResponse["idTopicTrue"] == $idTopic);  //$gameItem->result;
	
	$partnerChosen = (int)$result;
	$chosen = (int)($idTopicSelected == $idTopic);
			
	//INSERT LOGGING		
	$insert_row = $mysqli->query("INSERT INTO logging (idUser, idTopic, idResource, idRound, idLevel, partnerChosen, chosen)
		  VALUES ('$idUser', '$idTopic', '$idResource', '$idRound', (SELECT idLevel FROM level WHERE level = $level and idRound = '$idRound'), $partnerChosen, $chosen)");

	if($insert_row){
		//print 'Success!'; 
	}else{
		die('Error : ('. $mysqli->errno .') '. $mysqli->error);
	}	
}		
	
if($idTopicSelected != $trueResponse["idTopicTrue"]) {
	
	$nErrors++;
	if($isGT == 1) 
	{
		$nGTErrors++;
	}			
	$consecutiveAnswer = -1;
	
} else {
	
	$consecutiveAnswer++;
	$score = $score + $levelPoints + ($consecutiveLevelPoints * $consecutiveAnswer);	
}
	
$trueResponse["score"] = $score;
$trueResponse["consecutiveAnswer"] = $consecutiveAnswer;

//update		
$update_row = $mysqli->query("UPDATE true_response SET score = $score, consecutiveAnswer = $consecutiveAnswer, nErrors = $nErrors, nGTErrors = $nGTErrors
							  WHERE idRound = $idRound AND level >= $level;");
if($update_row){
	//print 'Success!'; 
}else{
	die('Error : ('. $mysqli->errno .') '. $mysqli->error);
}	

//update		
$update_row = $mysqli->query("UPDATE true_response SET played = 1
							  WHERE idRound = $idRound AND level = $level;");
if($update_row){
	//print 'Success!'; 
}else{
	die('Error : ('. $mysqli->errno .') '. $mysqli->error);
}	
	
/* close connection */
$mysqli->close();

echo json_encode($trueResponse);

?>
