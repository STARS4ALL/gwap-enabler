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

$complete = array();
$badge = array();
$badges = array();
$position = array();

$postdata = file_get_contents("php://input");
$decodedResults = json_decode($postdata);

$results = $decodedResults->json;
$numberOfErrors = 0;
$numberOfGTErrors = 0;
$levelCounter = 0;
$idUser = $decodedResults->idUser;
$score = 0;
$lifeTime = $decodedResults->lifeTime;
$idRound = $decodedResults->idRound;
$gameTime = $decodedResults->gameTime;
$endRound = date("Y-m-d H:i:s");
$lifeTimeInSeconds = ceil($lifeTime);
$totScore = 0;

$missingPoints = null;

$parameters = parameters($mysqli);

$upperThreshold = $parameters["upperThreshold"];
$positiveParameter = $parameters["positiveK"];
$negativeParameter = $parameters["negativeK"];
$nOfLevels = $parameters["nOfLevels"];
$reputationParam = $parameters["reputationParam"];


$query = "SELECT level, score, nErrors, nGTErrors FROM true_response
		WHERE idRound = '$idRound' AND played = 1
		ORDER BY level DESC
		LIMIT 1";
			
$result = $mysqli->query($query) or die($mysqli->error.__LINE__);

// GOING THROUGH THE DATA
if($result->num_rows > 0) {
	while($row = $result->fetch_assoc()) {
		$score = $row['score'];		
		$numberOfErrors = $row['nErrors'];	
		$numberOfGTErrors = $row['nGTErrors'];					
		$levelCounter = $row['level'];		
	}
}

//UPDATE END ROUND AND INSERT SCORE ROUND
$update_endRound = $mysqli->query("UPDATE round SET endRound = CURRENT_TIMESTAMP, score = '$score' WHERE idRound = $idRound");

if($update_endRound){
	
}else{
	die('Error : ('. $mysqli->errno .') '. $mysqli->error);
}

//UPDATE USER REPUTATION
if($levelCounter != 0){

	$updateUserReputation = exp(-($reputationParam*$numberOfGTErrors));
											 
	$update_userReputation = $mysqli->query("INSERT INTO user_reputation (idUser, idRound, reputation)
											  VALUES ('$idUser', '$idRound', '$updateUserReputation')");												
										
	if($update_userReputation){
		//print $updateUserReputation;
	}else{
		die('Error : ('. $mysqli->errno .') '. $mysqli->error);
	}
}

//SCORE TO ENTER IN BEST LAST PLAYERS

$topPlayers = array();
$topPlayers = topTenRound($mysqli);


$query_userLastTenGame = "SELECT idRound, idUser, startRound, SUM(score) AS score
				 FROM ( SELECT idRound, idUser, startRound, score FROM round
				 WHERE idUser = '$idUser'
				 AND endRound <> '00-00-00 00:00:00'					 
				 ORDER BY startRound DESC
				 LIMIT 10) AS subquery
				";

	$result_query_userLastTenGame = $mysqli->query($query_userLastTenGame) or die($mysqli->error.__LINE__);

		if($result_query_userLastTenGame->num_rows > 0) {

			while($row_query_userLastTenGame = $result_query_userLastTenGame->fetch_assoc()) {

				$userLastTenGameScore = $row_query_userLastTenGame['score'];
									
				if($idUser != $topPlayers[0]['idUser'] && $topPlayers[0]['score'] > $userLastTenGameScore){
					
					$missingPoints = $topPlayers[0]['score'] - $userLastTenGameScore;
					$complete['missingPoints'] = $missingPoints;
				
				}
				else{
					$complete['missingPoints'] = $missingPoints;
				}

			}
		}

//INSERT USER SCORE

$check_user_leaderboard = "SELECT idUser, score FROM leaderboard WHERE idUser = '$idUser'";
$result = $mysqli->query($check_user_leaderboard) or die($mysqli->error.__LINE__);

// GOING THROUGH THE DATA
if($result->num_rows > 0) {
	while($row = $result->fetch_assoc()) {
		$totScore = $score + $row['score'];
		$update_score_leaderboard = $mysqli->query("UPDATE leaderboard
													SET score = '$totScore'
													WHERE idUser = '$idUser'");
	}
}
else
{
	$totScore = $score;		
	$insert_leaderboard = $mysqli->query("INSERT INTO leaderboard (idUser, score) VALUES ('$idUser', '$totScore')");

	if($insert_leaderboard){
		
	}else{
		die('Error : ('. $mysqli->errno .') '. $mysqli->error);
	}
}

//UPDATE USER LIFE PLAY
$update_row = $mysqli->query("UPDATE user SET life_play = life_play + $lifeTimeInSeconds WHERE idUser = $idUser");

if($update_row){
	
}else{
	die('Error : ('. $mysqli->errno .') '. $mysqli->error);
}

//USER BADGE

$query = "SELECT idBadge, name, value, image, goal FROM badge WHERE goal > 0";

$result = $mysqli->query($query) or die($mysqli->error.__LINE__);

// GOING THROUGH THE DATA
	if($result->num_rows > 0) {

		while($row = $result->fetch_assoc()) {
			$idBadge = $row['idBadge'];
			$name = $row['name'];
			$value = $row['value'];
			$image = $row['image'];
			$goal = $row['goal'];

			switch ($goal) {					
				//FIRST GAME
				case 1:
					$count = 0;
					$query1 = "SELECT COUNT(idRound) as count FROM round WHERE endRound <> '00-00-00 00:00:00' AND idUser = '$idUser'";
					$result1 = $mysqli->query($query1) or die($mysqli->error.__LINE__);

					// GOING THROUGH THE DATA
					if($result1->num_rows > 0) {

						while($row = $result1->fetch_assoc()) {
							$count = $row['count'];										
						}
						
						if($count == 1){

							$insert_row = $mysqli->query("INSERT IGNORE INTO user_has_badge (idUser, idBadge) VALUES ('$idUser', '$idBadge')");

							if($insert_row){
								if($mysqli->affected_rows > 0){
									$badge['name'] = $name;
									$badge['value'] = $value;
									$badge['image'] = $image;
									array_push($badges, $badge);
								}
							}
							else{
								die('Error : ('. $mysqli->errno .') '. $mysqli->error);
							}
						}							
					}
					break;
				//10 GAME IN A DAY
				case 2:
					$count = 0;
					$query2 = "SELECT COUNT(idRound) AS count FROM round WHERE endRound <> '00-00-00 00:00:00' AND endRound >= CURRENT_DATE AND endRound <  CURRENT_DATE + INTERVAL 1 DAY AND idUser = '$idUser'";
					$result2 = $mysqli->query($query2) or die($mysqli->error.__LINE__);

					// GOING THROUGH THE DATA
					if($result2->num_rows > 0) {

						while($row = $result2->fetch_assoc()) {
							$count = $row['count'];
							
							if($count >= 10){

								$insert_row = $mysqli->query("INSERT IGNORE INTO user_has_badge (idUser, idBadge) VALUES ('$idUser', '$idBadge')");

								if($insert_row){
									if($mysqli->affected_rows > 0){
										$badge['name'] = $name;
										$badge['value'] = $value;
										$badge['image'] = $image;
										array_push($badges, $badge);
									}
								}
								else{
									die('Error : ('. $mysqli->errno .') '. $mysqli->error);
								}
							}
						}
					}
					break;
				//...
				case 3:
					break;
				//ALL RIGHT ANSWER
				case 4:
					if ($levelCounter > 0 && $numberOfErrors == 0){

						$insert_row = $mysqli->query("INSERT IGNORE INTO user_has_badge (idUser, idBadge) VALUES ('$idUser', '$idBadge')");

						if($insert_row){
							if($mysqli->affected_rows > 0){
								$badge['name'] = $name;
								$badge['value'] = $value;
								$badge['image'] = $image;
								array_push($badges, $badge);
							}
						}
						else{
							die('Error : ('. $mysqli->errno .') '. $mysqli->error);
						}							
					}
					break;				    
				//ALL WRONG ANSWER
				case 5:
					if ($levelCounter > 0 && $numberOfErrors == $levelCounter){

						$insert_row = $mysqli->query("INSERT IGNORE INTO user_has_badge (idUser, idBadge) VALUES ('$idUser', '$idBadge')");

						if($insert_row){
							if($mysqli->affected_rows > 0){
								$badge['name'] = $name;
								$badge['value'] = $value;
								$badge['image'] = $image;
								array_push($badges, $badge);
							}
						}
						else{
							die('Error : ('. $mysqli->errno .') '. $mysqli->error);
						}
					}
					break;
				//WELCOME BACK AFTER A WEEK
				case 6:
						$query6 = "SELECT endRound FROM round WHERE idRound <> '$idRound' AND idUser = '$idUser' AND endRound <> '00-00-00 00:00:00' ORDER BY endRound DESC LIMIT 1";
						$result6 = $mysqli->query($query6) or die($mysqli->error.__LINE__);

						// GOING THROUGH THE DATA
							if($result6->num_rows > 0) {

								while($row = $result6->fetch_assoc()) {
									$endRound_welcomeBack = $row['endRound'];
									//date in milliseconds
									$endRound_milliseconds = strtotime ($endRound_welcomeBack)*1000;
									// date in milliseconds of 1 week before now
									$now_milliseconds = strtotime("now")*1000;
									//one week before
									$oneWeekBeforeNow = $now_milliseconds - (7 * 24 * 3600 * 1000);
									if($endRound_milliseconds < $oneWeekBeforeNow){

										$insert_row = $mysqli->query("INSERT IGNORE INTO user_has_badge (idUser, idBadge) VALUES ('$idUser', '$idBadge')");

										if($insert_row){
											if($mysqli->affected_rows > 0){
												$badge['name'] = $name;
												$badge['value'] = $value;
												$badge['image'] = $image;
												array_push($badges, $badge);
											}
										}
										else{
											die('Error : ('. $mysqli->errno .') '. $mysqli->error);
										}
									}

								}
							}
					break;
				//PLAY MORE THAN 20 MIN IN A DAY
				case 7:
						$timeToPlay = 0;
						$query7 = "SELECT startRound, endRound FROM round WHERE endRound <> '00-00-00 00:00:00' AND endRound >= CURRENT_DATE AND endRound <  CURRENT_DATE + INTERVAL 1 DAY AND idUser = '$idUser'";
						$result7 = $mysqli->query($query7) or die($mysqli->error.__LINE__);

						// GOING THROUGH THE DATA
							if($result7->num_rows > 0) {

								while($row = $result7->fetch_assoc()) {
									//date in seconds
									$timeToPlay = $timeToPlay + (strtotime ($row['endRound']) - strtotime ($row['startRound']));
								}
								
								if($timeToPlay >= 1200){

									$insert_row = $mysqli->query("INSERT IGNORE INTO user_has_badge (idUser, idBadge) VALUES ('$idUser', '$idBadge')");

									if($insert_row){
										if($mysqli->affected_rows > 0){
											$badge['name'] = $name;
											$badge['value'] = $value;
											$badge['image'] = $image;
											array_push($badges, $badge);
										}
									}
									else{
										die('Error : ('. $mysqli->errno .') '. $mysqli->error);
									}
								}
									
							}
					break;
				//MORE THAN 20 ANSWER
				case 8:
					if ($levelCounter >= 20){

						$insert_row = $mysqli->query("INSERT IGNORE INTO user_has_badge (idUser, idBadge) VALUES ('$idUser', '$idBadge')");

						if($insert_row){
							if($mysqli->affected_rows > 0){
								$badge['name'] = $name;
								$badge['value'] = $value;
								$badge['image'] = $image;
								array_push($badges, $badge);
							}
						}
						else{
							die('Error : ('. $mysqli->errno .') '. $mysqli->error);
						}
					}
					break;		
				//TOP OF LEADERBOARD
				case 9:
						$query9 = "SELECT idUser, score FROM leaderboard ORDER BY score DESC LIMIT 1";
						$result9 = $mysqli->query($query9) or die($mysqli->error.__LINE__);

						// GOING THROUGH THE DATA
							if($result9->num_rows > 0) {

								while($row = $result9->fetch_assoc()) {
									$idUser_leader = $row['idUser'];
									
									if($idUser_leader == $idUser){

										$insert_row = $mysqli->query("INSERT IGNORE INTO user_has_badge (idUser, idBadge) VALUES ('$idUser', '$idBadge')");

										if($insert_row){
											if($mysqli->affected_rows > 0){
												$badge['name'] = $name;
												$badge['value'] = $value;
												$badge['image'] = $image;
												array_push($badges, $badge);
											}
										}
										else{
											die('Error : ('. $mysqli->errno .') '. $mysqli->error);
										}
									}
								}
							}
					break;
			}

		}
		$complete['badges'] = $badges;
	}
	else
	{
		'NO BADGE IN THE APPLICATION';	
	}

//RESOURCE SCORE

foreach($results as $resultItem){
	$decodedResult = json_decode($resultItem);
	$gt = -1;
	$idTopicSelected = $decodedResult->idTopic;
	$idResourceSelected = $decodedResult->idResource;
	$level = $decodedResult->level;
	$games = $decodedResult->games;

	$query = "SELECT isGT FROM true_response WHERE idRound = '$idRound' AND level = $level AND played = 1";
	$result = $mysqli->query($query) or die($mysqli->error.__LINE__);
	// GOING THROUGH THE DATA
	if($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$gt = $row['isGT'];
		}
	}
							
	if($gt == 0) { // IF RESOURCE IS NOT GROUND TRUTH THEN UPDATE SCORE FOR TOPICE SELECTED
		foreach($games as $game){

			$idTopicLevel = $game->idTopic;

			//IF TOPIC IS THE ONE SELECTED
			if($idTopicLevel == $idTopicSelected){
				//RETRIVE INFORMATIONS TO UPDATE
				$resource_topic_before = resourceTopic($mysqli, $idResourceSelected, $idTopicLevel);
				
				$score_resource_topic_before = $resource_topic_before['score'];
				
				$updateScore = $score_resource_topic_before;
				//AND SCORE IT'S BETWEEN BOUNDS
				if($score_resource_topic_before <= $upperThreshold){
					//ITS SCORE INCREASES
					$updateScore = $score_resource_topic_before + ($positiveParameter * $updateUserReputation);					
				}	

				//UPDATE INFORMATIONS
				$update_resource_topic = $mysqli->query("UPDATE resource_has_topic SET score = $updateScore WHERE idResource = $idResourceSelected AND idTopic = $idTopicLevel");
							 
				if($update_resource_topic){
					//print $updateUserReputation;
				}else{
					//die('Error : ('. $mysqli->errno .') '. $mysqli->error);
				}
			}
		}			
	}
}

$position = getUserPositions($mysqli, $idUser);
$complete['position'] = $position;

// CLOSE CONNECTION
mysqli_close($mysqli);

echo json_encode($complete);

?>