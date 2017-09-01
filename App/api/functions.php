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
 
// Function to get the client IP address
function get_client_ip() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP'])) {
		//echo "SERVER['HTTP_CLIENT_IP']" . $_SERVER['HTTP_CLIENT_IP'];
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
	} else if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		//echo "SERVER['HTTP_X_FORWARDED_FOR']" . $_SERVER['HTTP_X_FORWARDED_FOR'];
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else if(isset($_SERVER['HTTP_X_FORWARDED'])) {
		//echo "SERVER['HTTP_X_FORWARDED']" . $_SERVER['HTTP_X_FORWARDED'];
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    } else if(isset($_SERVER['HTTP_FORWARDED_FOR'])) {
		//echo "SERVER['HTTP_FORWARDED_FOR']" . $_SERVER['HTTP_FORWARDED_FOR'];
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    } else if(isset($_SERVER['HTTP_FORWARDED'])) {
		//echo "SERVER['HTTP_FORWARDED']" . $_SERVER['HTTP_FORWARDED'];
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    } else if(isset($_SERVER['REMOTE_ADDR'])) {
		//echo "SERVER['REMOTE_ADDR']" . $_SERVER['REMOTE_ADDR'];
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    } else {
		//echo "ipaddress = 'UNKNOWN'";
        $ipaddress = 'UNKNOWN';
	}
    return $ipaddress;
}

function levels($mysqli, $idUser, $idRound){
	$levels = array();
	$level = array();
	$parameters = parameters($mysqli);
	
	$upperThreshold = $parameters["upperThreshold"];
	$nOfLevels = $parameters["nOfLevels"];

	$noRows = false;
		
	$ngt = floor($nOfLevels/3);
	$nres = ($nOfLevels - $ngt) ;
	
	//GET nres RESOURCES (nOfLevels LEVELS OF THE ROUND)
	//EXCEPT FOR THE ONES WITH WITCH THE USER HAS ALREADY PLAYED;
	//ngt ARE FROM GROUND TRUTH FOR USER REPUTATION (SCORE > UPPER)
	$query = 	"(
				SELECT idResource, url, 0 as gt
				FROM resource
				WHERE idResource NOT IN (SELECT DISTINCT idResource FROM logging WHERE idUser = $idUser)
				AND idResource NOT IN (SELECT DISTINCT idResource FROM resource_has_topic WHERE score > $upperThreshold)				
				ORDER BY orderBy
				LIMIT $nres				
				)		
				UNION
				(
				SELECT idResource, url, 1 as gt
				FROM resource
				WHERE idResource NOT IN (SELECT DISTINCT idResource FROM logging WHERE idUser = $idUser)
				AND idResource IN (SELECT DISTINCT idResource FROM resource_has_topic WHERE score > $upperThreshold)
				ORDER BY orderBy
				LIMIT $ngt
				)						
				ORDER BY rand()";
				
	$result = $mysqli->query($query) or die($mysqli->error.__LINE__);

	// GOING THROUGH THE DATA
	if($result->num_rows == $nOfLevels) { //if($result->num_rows > 0) {
		$i=1;
		while($row = $result->fetch_assoc()) {
			$level["name"] = "Livello ".$i."";
			$level["level"] = "".$i++."";
			$level["nextlevel"] = "".$i."";
			$level["idResource"] = $row['idResource'];
			$level["url"] = $row['url'];			
			$level["games"] = games($mysqli, $row['idResource'], $idUser, $upperThreshold, $idRound, $level["level"], $row['gt']);
			array_push($levels, $level);
		}
	} else {
		//echo 'no rows';
		$noRows = true;
	}
	
	return $levels;

} //CLOSE FUNCTION LEVELS

function games($mysqli, $idResource, $idUser, $upperThreshold, $idRound, $level, $isGT){
	$games = array();
	$game = array();
	$choosenTopics = array();
	$idTrueTopic = 0;
	
	//GET THE TRUE TOPIC RELATED TO THE RESOURCE:
	//score > upperLimit (GROUND TRUTH)	(IT SHOULD BE ONE)
	//ELSE THE MOST SELECTED TOPIC FROM THE OTHER USERS
	//ELSE A RANDOM TOPIC
	$query = 	"SELECT Q.* FROM
				(
				(
				SELECT RT.idResource, RT.idTopic, T.value, RT.score, true as result
				FROM  resource_has_topic AS RT
				JOIN topic AS T ON T.idTopic = RT.idTopic
				JOIN resource AS R ON R.idResource = RT.idResource
				WHERE RT.idResource = $idResource AND RT.score > $upperThreshold
				ORDER BY RT.score DESC
				LIMIT 1
				)
				UNION
				(
				SELECT L.idResource, L.idTopic, T.value, RT.score, true as result
				FROM logging L
				JOIN topic AS T ON T.idTopic = L.idTopic
				JOIN resource_has_topic AS RT ON RT.idResource = L.idResource AND RT.idTopic = L.idTopic
				WHERE L.idResource = $idResource AND L.idUser <> $idUser AND L.choosen = 1
				GROUP BY L.idResource, L.idTopic, T.value, RT.score
				ORDER BY COUNT(L.idUser) DESC
				LIMIT 1
				)
				UNION
				(
				SELECT RT.idResource, RT.idTopic, T.value, RT.score, true as result
				FROM  resource_has_topic AS RT
				JOIN topic AS T ON T.idTopic = RT.idTopic
				JOIN resource AS R ON R.idResource = RT.idResource
				WHERE RT.idResource = $idResource AND RT.score < $upperThreshold AND (SELECT COUNT(*) FROM logging L WHERE L.idResource = $idResource AND L.idUser <> $idUser) = 0
				ORDER BY RAND()
				LIMIT 1
				)
				) AS Q
				ORDER BY Q.score DESC
				LIMIT 1
				";
				
	$result = $mysqli->query($query) or die($mysqli->error.__LINE__);

	// GOING THROUGH THE DATA
	if($result->num_rows > 0) {
		$i=1;
		while($row = $result->fetch_assoc()) {
			$game["idTopic"] = $row['idTopic'];
			$idTrueTopic = $row['idTopic'];
			$choosenTopics[] = $row['idTopic'];
			$game["value"] = utf8_encode($row['value']);
			
			true_response($mysqli, $idRound, $level, $idTrueTopic, $isGT);
			
			array_push($games, $game);
		}
	}

	//GET ALL THE OTHER TOPICS RELATED TO THE RESOURCES THAT HAVE BEEN EVENTUALLY CHOOSEN BY A USER
	$query = 	"SELECT RT.idResource, RT.idTopic, T.value, RT.score
				 FROM topic AS T
				 JOIN resource_has_topic AS RT ON RT.idTopic = T.idTopic
				 JOIN resource AS R ON R.idResource = RT.idResource
				 WHERE RT.idResource = $idResource
				 AND RT.idTopic <> $idTrueTopic";

	$result = $mysqli->query($query) or die($mysqli->error.__LINE__);

	// GOING THROUGH THE DATA
	if($result->num_rows > 0) {
		$i=1;
		while($row = $result->fetch_assoc()) {
			$game["idTopic"] = $row['idTopic'];
			$choosenTopics[] = $row['idTopic'];
			$game["value"] = utf8_encode($row['value']);
			array_push($games, $game);
		}
	}

	//GET ALL THE OTHER TOPIC TO FILL ALL THE POSSIBLE CLASSIFICATIONS
	$query = "SELECT idTopic, value FROM topic WHERE idTopic NOT IN ( '" . implode($choosenTopics, "', '") . "' )
				AND idTopic NOT IN (7, 8)"; //EXCLUDE 404 AND UNKNOWN
	$result = $mysqli->query($query) or die($mysqli->error.__LINE__);

	// GOING THROUGH THE DATA
	if($result->num_rows > 0) {
		$i=1;
		while($row = $result->fetch_assoc()) {
			$game["idTopic"] = $row['idTopic'];
			$game["value"] = utf8_encode($row['value']);
			array_push($games, $game);			
		}
	}
	
	//shuffle ($games);
	
	//THEN ORDER BY CLASSIFICATION
	usort($games, function($a, $b) {
		return $a['idTopic'] - $b['idTopic'];
	});
	
	return $games;

} //CLOSE FUNCTION GAMES

function true_response($mysqli, $idRound, $level, $idTopicTrue, $isGT) {
	
	$insert_row = $mysqli->query("INSERT INTO true_response (idRound, level, idTopicTrue, isGT) VALUES ('$idRound', '$level', '$idTopicTrue', $isGT)");

	if($insert_row){
		//print $insert_row;
	}else{
		die('Error : ('. $mysqli->errno .') '. $mysqli->error);
	}	
}

function parameters($mysqli){

	$parameters = array();

	$query = "SELECT upperThreshold, lowerThreshold, positiveK, negativeK, nOfLevels, maxScore FROM configuration";

	$result = $mysqli->query($query) or die($mysqli->error.__LINE__);

	// GOING THROUGH THE DATA
	if($result->num_rows > 0) {

		while($row = $result->fetch_assoc()) {
			$parameters["upperThreshold"] = $row['upperThreshold'];
			$parameters["lowerThreshold"] = $row['lowerThreshold'];
			$parameters["positiveK"] = $row['positiveK'];
			$parameters["negativeK"] = $row['negativeK'];
			$parameters["nOfLevels"] = $row['nOfLevels'];
			$parameters["maxScore"] = $row['maxScore'];
		}
	}

	else
	{
		echo 'NO RESULTS';	
	}

	return $parameters;

} //CLOSE FUNCTION GET POSITIVEK AND NEGATIVEK

function resourceTopic($mysqli, $idResource, $idTopic){

	$resourceTopic = array();

	$query = "SELECT score FROM resource_has_topic WHERE idResource = '$idResource' AND idTopic = '$idTopic'";
	$result = $mysqli->query($query) or die($mysqli->error.__LINE__);

	// GOING THROUGH THE DATA
	if($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$resourceTopic["score"] = $row['score'];
		}
	}

	else
	{
		//echo 'NO RESULTS';	
		$resourceTopic["score"] = 0;
	}

	return $resourceTopic;

} //CLOSE FUNCTION RESOURCE TOPIC

function leaderboard($mysqli, $idUser){

	$leaderboard = array();
	$userScore = array();
	$topRankPlayers = array();
	$beforeUserPlayers = array();
	$afterUserPlayers = array();
	$user = array();

	$leaderboard['topRank'] = $topRankPlayers;
	$leaderboard['userScore'] = 0;
		
	//RETRIEVE DATA FROM TABLE LEADERBOARD
	$query_leaderboard = "SELECT userPosition, idUser, name, social, thumbnail, score FROM
									(SELECT case L.score
												when @score then @rank
												when @score := L.score then @rank:= @rank + 1
											end as userPosition
									, L.idUser, U.name, U.social, U.thumbnail, L.score
									FROM leaderboard AS L
									JOIN user AS U ON L.iduser = U.idUser,
									(SELECT @row:= 0, @rank:= 0, @score:= 0.0) R
									WHERE U.social <> 'anonymous'
									ORDER BY L.score DESC
									) Q
								WHERE idUser = '$idUser'
							 ";					 
							 
	$result_query_leaderboard = $mysqli->query($query_leaderboard) or die($mysqli->error.__LINE__);
	
	if($result_query_leaderboard->num_rows > 0) {	
		while($row_query_leaderboard = $result_query_leaderboard->fetch_assoc()) {

			$userPosition = $row_query_leaderboard['userPosition'];
			$userPositionBeforeStart = $userPosition-3;
			$userPositionBeforeEnd = $userPosition-1;
			$userPositionAfterStart = $userPosition+1;
			$userPositionAfterEnd = $userPosition+3;

			$user['userPosition'] = $userPosition;
			$user['idUser'] = $row_query_leaderboard['idUser'];
			$user['userName'] = $row_query_leaderboard['name'];
			$user['social'] = $row_query_leaderboard['social'];
			$user['thumbnail'] = $row_query_leaderboard['thumbnail'];
			$user['score'] = $row_query_leaderboard['score'];
		}
		
		$leaderboard['topRank'] = topRankPlayers($mysqli, $user, $topRankPlayers);
		$leaderboard['userScore'] = $user;		
	}
	else
	{
		$leaderboard['topRank'] = topRankPlayers($mysqli, $user, $topRankPlayers);	
	}

	return $leaderboard;

} //CLOSE FUNCTION LEADERBOARD

function leaderboardByTime($mysqli, $fromUTC = null, $toUTC = null) {
	
	$fromUTC = isset($fromUTC) ? $fromUTC : '0000-00-00T00:00:00Z';
	$toUTC = isset($toUTC) ? $toUTC : '9999-99-99T99:99:99Z';

	$leaderboard = array();
	$userScore = array();
	$user = array();

	//RETRIEVE DATA FROM TABLE LEADERBOARD
	$query_leaderboard = "SELECT case L.score
									when @score then @rank
									when @score := L.score then @rank:= @rank + 1
								end as userPosition
						, L.idUser, U.name, U.social, U.thumbnail, L.score, L.minStartRoundUTC, L.maxEndRoundUTC
						FROM (SELECT idUser, SUM(score) AS score, CONVERT_TZ(MIN(startRound), @@session.time_zone, '+00:00') AS minStartRoundUTC, CONVERT_TZ(MAX(endRound), @@session.time_zone, '+00:00') AS maxEndRoundUTC
								FROM round
								WHERE endRound <> '0000-00-00 00:00:00'
								AND CONVERT_TZ(startRound, @@session.time_zone, '+00:00') >= '$fromUTC' AND CONVERT_TZ(endRound, @@session.time_zone, '+00:00') <= '$toUTC'
								GROUP BY idUser) AS L
						JOIN user AS U ON L.iduser = U.idUser,
						(SELECT @row:= 0, @rank:= 0, @score:= 0.0) R
						WHERE U.social <> 'anonymous'
						ORDER BY L.score DESC
						";
						
	$result_query_leaderboard = $mysqli->query($query_leaderboard) or die($mysqli->error.__LINE__);
	
	$position = 0;
	$prevscore = 0;
	if($result_query_leaderboard->num_rows > 0) {	
		while($row_query_leaderboard = $result_query_leaderboard->fetch_assoc()) {
			
			if($prevscore != $row_query_leaderboard['score']) {
				$position = $position+1;	
			}
			$prevscore = $row_query_leaderboard['score'];
			
			$user['userPosition'] = $position;
			$user['idUser'] = $row_query_leaderboard['idUser'];
			$user['userName'] = $row_query_leaderboard['name'];
			$user['social'] = $row_query_leaderboard['social'];
			$user['thumbnail'] = $row_query_leaderboard['thumbnail'];
			$user['score'] = $row_query_leaderboard['score'];
			$user['minStartRoundUTC'] = $row_query_leaderboard['minStartRoundUTC'];
			$user['maxEndRoundUTC'] = $row_query_leaderboard['maxEndRoundUTC'];
			array_push($userScore, $user);
			
		}
		
		$leaderboard['userScore'] = $userScore;		
	}	

	return $leaderboard;

} //CLOSE FUNCTION LEADERBOARDBYTIME

function topRankPlayers($mysqli, $user, $topRankPlayers) {

	//SELECT ALL PLAYERS
	$query_top_players = "SELECT userPosition, idUser, name, social, thumbnail, score FROM 
							(SELECT case L.score
										when @score then @rank
										when @score := L.score then @rank:= @rank + 1
									end as userPosition
							, L.idUser, U.name, U.social, U.thumbnail, L.score
							FROM leaderboard AS L
							JOIN user AS U ON L.iduser = U.idUser,
							(SELECT @row:= 0, @rank:= 0, @score:= 0.0) R
							WHERE U.social <> 'anonymous'
							ORDER BY L.score DESC
							) Q
						  ORDER BY userPosition
						   ";						   
						   
	$result_query_top_players = $mysqli->query($query_top_players) or die($mysqli->error.__LINE__);

	// GOING THROUGH THE DATA
	if($result_query_top_players->num_rows > 0) {

		while($row_query_top_players = $result_query_top_players->fetch_assoc()) {

			$user['userPosition'] = $row_query_top_players['userPosition'];
			$user['idUser'] = $row_query_top_players['idUser'];
			$user['userName'] = $row_query_top_players['name'];
			$user['social'] = $row_query_top_players['social'];
			$user['thumbnail'] = $row_query_top_players['thumbnail'];
			$user['score'] = $row_query_top_players['score'];
			array_push($topRankPlayers, $user);
		}
	}

	return $topRankPlayers;

}

function userBeforePlayers($mysqli, $beforeUserPlayers, $user, $userPositionBeforeStart, $userPositionBeforeEnd, $increment){

	//SELECT THREE PLAYERS BEFORE USER LEADERBOARD POSITION				
	$query_before_players = "SELECT userPosition, idUser, name, social, thumbnail, score FROM 
								(SELECT case L.score
											when @score then @rank
											when @score := L.score then @rank:= @rank + 1
										end as userPosition
								, L.idUser, U.name, U.social, U.thumbnail, L.score
								FROM leaderboard AS L
								JOIN user AS U ON L.iduser = U.idUser,	
								(SELECT @row:= 0, @rank:= 0, @score:= 0.0) R
								WHERE U.social <> 'anonymous'
								ORDER BY L.score DESC
								) Q			
							 WHERE userPosition BETWEEN '$userPositionBeforeStart' AND '$userPositionBeforeEnd'
							 ORDER BY userPosition
							";										

	$result_query_before_players = $mysqli->query($query_before_players) or die($mysqli->error.__LINE__);

	// GOING THROUGH THE DATA
	if($result_query_before_players->num_rows > 0) {

		while($row_query_before_players = $result_query_before_players->fetch_assoc()) {

			$user['userPosition'] = $row_query_before_players['userPosition'];
			$user['idUser'] = $row_query_before_players['idUser'];
			$user['userName'] = $row_query_before_players['name'];
			$user['social'] = $row_query_before_players['social'];
			$user['thumbnail'] = $row_query_before_players['thumbnail'];
			$user['score'] = $row_query_before_players['score'];
			array_push($beforeUserPlayers, $user);
		}
	}

	return $beforeUserPlayers;

}

function userAfterPlayers($mysqli, $afterUserPlayers, $user, $userPositionAfterStart, $userPositionAfterEnd, $increment) {


	//SELECT THREE PLAYERS AFTER USER LEADERBOARD POSITION
	$query_after_players = "SELECT userPosition, idUser, name, social, thumbnail, score FROM
								(SELECT case L.score
											when @score then @rank
											when @score := L.score then @rank:= @rank + 1
										end as userPosition
								, L.idUser, U.name, U.social, U.thumbnail, L.score
								FROM leaderboard AS L
								JOIN user AS U ON L.iduser = U.idUser,
								(SELECT @row:= 0, @rank:= 0, @score:= 0.0) R
								WHERE U.social <> 'anonymous'
								ORDER BY L.score DESC
								) Q				
							WHERE userPosition BETWEEN '$userPositionAfterStart' AND '$userPositionAfterEnd'
							ORDER BY userPosition
						   ";
						   
	$result_query_after_players = $mysqli->query($query_after_players) or die($mysqli->error.__LINE__);

	// GOING THROUGH THE DATA
	if($result_query_after_players->num_rows > 0) {

		while($row_query_after_players = $result_query_after_players->fetch_assoc()) {

			$user['userPosition'] = $row_query_after_players['userPosition'];
			$user['idUser'] = $row_query_after_players['idUser'];
			$user['userName'] = $row_query_after_players['name'];
			$user['social'] = $row_query_after_players['social'];
			$user['thumbnail'] = $row_query_after_players['thumbnail'];
			$user['score'] = $row_query_after_players['score'];						
			array_push($afterUserPlayers, $user);
		}
	}

	return $afterUserPlayers;

}

//FUNCTION TOP TEN ROUND
function topTenRound($mysqli){

	$topTenRound = array();
	$user = array();
	$score = array();

	$query_users = "SELECT idUser, name, social, thumbnail
					FROM user
					WHERE social <> 'anonymous'
					";
	$result_query_users = $mysqli->query($query_users) or die($mysqli->error.__LINE__);

	if($result_query_users->num_rows > 0) {

		while($row_query_users = $result_query_users->fetch_assoc()) {

			$idUser = $row_query_users['idUser'];
			$user['userName'] = $row_query_users['name'];
			$user['social'] = $row_query_users['social'];
			$user['thumbnail'] = $row_query_users['thumbnail'];
			
			$query_topTen = "SELECT idRound, idUser, startRound, SUM(score) AS score
								FROM (SELECT idRound, idUser, startRound, score FROM round
								WHERE idUser = '$idUser'
								AND endRound <> '00-00-00 00:00:00'
								ORDER BY startRound DESC
								LIMIT 10) AS subquery
								";
				$result_query_topTen = $mysqli->query($query_topTen) or die($mysqli->error.__LINE__);

				if($result_query_topTen->num_rows > 0) {

					while($row_query_topTen = $result_query_topTen->fetch_assoc()) {
						//if($row_query_topTen['score'] > 0) {
							$user['idRound'] = $row_query_topTen['idRound'];
							$user['idUser'] = $row_query_topTen['idUser'];
							$user['startRound'] = $row_query_topTen['startRound'];
							$user['score'] = $row_query_topTen['score'];
							array_push($topTenRound, $user);
						//}											
					}
				}
				else
				{
					$user['idRound'] = 0;
					$user['idUser'] = $idUser;
					$user['startRound'] = null;
					$user['score'] = 0;
					array_push($topTenRound, $user);	
				}
		}
		
		foreach ($topTenRound as $key => $row) {
			$idRound[$key]  = $row['idRound'];
			$idUser[$key] = $row['idUser'];
			$startRound[$key] = $row['startRound'];
			$score[$key] = $row['score'];
		}

		array_multisort($score, SORT_DESC, $topTenRound);
		
		$rank = 1;
		$prev = 0;
		for($i = 0; $i < count($topTenRound); ++$i) {			
			if($prev > $topTenRound[$i]['score']) {
				$rank = $rank+1;
			}
			$topTenRound[$i]['position'] = $rank;
			$prev = $topTenRound[$i]['score'];
		}
		
	}

	else
	{
		$user['idRound'] = 0;
		$user['idUser'] = 0;
		$user['startRound'] = null;
		$user['score'] = 0;
		array_push($topTenRound, $user);
	}
	

	return $topTenRound;

} //CLOSE FUNCTION TOP TEN ROUND

function badgeList($mysqli) {

	$badges = array();
	$badge = array();

		
	$query_badgeList = "SELECT name, value, image FROM badge WHERE goal > 0";

	$result_badgeList = $mysqli->query($query_badgeList) or die($mysqli->error.__LINE__);

		// GOING THROUGH THE DATA
		if($result_badgeList->num_rows > 0) {

			while($row_badgeList = $result_badgeList->fetch_assoc()) {

				$badge['name'] = $row_badgeList['name'];
				$badge['value'] = $row_badgeList['value'];
				$badge['image'] = $row_badgeList['image'];
				array_push($badges, $badge);
			}
		}

	return $badges;

}

function user($mysqli, $user, $idUser){

	
	$query = "SELECT idUser, firstName FROM user WHERE idUser = $idUser";
	$result = $mysqli->query($query) or die($mysqli->error.__LINE__);

	// GOING THROUGH THE DATA
	if($result->num_rows > 0) {

		while($row = $result->fetch_assoc()) {
			$user['username'] = $row['firstName'];
			$user['idUser'] = $row['idUser'];
		}
	}

	else
	{
		echo 'NO RESULTS';	
	}

	return $user;

} //CLOSE FUNCTION USER

function gameRound($mysqli, $idUser){
	
	$user = array();

	//INSERT ROUND
	$insert_row = $mysqli->query("INSERT INTO round (startRound, idUser, score) VALUES (CURRENT_TIMESTAMP,'$idUser',0)");

	if($insert_row){
		$user["idRound"] = $mysqli->insert_id; //"".$mysqli->insert_id."";
	}else{
	    die('Error : ('. $mysqli->errno .') '. $mysqli->error);
	}

	return $user;

} //CLOSE FUNCTION ROUND

function userBadge($mysqli, $idUser){

	$badges = array();
	$badge = array();
	
	$query = "SELECT B.name, B.value, B.image, CASE WHEN U.idUser IS NOT null THEN UB.timestamp ELSE null END AS timestamp
			FROM badge AS B
			LEFT JOIN user_has_badge AS UB ON B.idBadge = UB.idBadge AND $idUser = UB.idUser
			LEFT JOIN user AS U ON UB.idUser = U.idUser AND 'anonymous' <> U.social
			WHERE B.goal > 0
			ORDER BY UB.timestamp DESC";
			  
	$result = $mysqli->query($query) or die($mysqli->error.__LINE__);

	// GOING THROUGH THE DATA
	if($result->num_rows > 0) {

		while($row = $result->fetch_assoc()) {
			$badge['name'] = $row['name'];
			$badge['value'] = $row['value'];
			$badge['image'] = $row['image'];
			$badge['timestamp'] = $row['timestamp'];
			array_push($badges, $badge);
		}
	}

	else
	{

	}

	return $badges;

} //CLOSE FUNCTION USERBADGE

function evaluation($mysqli){

	$evaluation = array();
	
	//RETRIEVE DATA
	$query_evaluation = "select 
						SRD2.NofPlayers
						, SRD.TotalLifePlay AS TotalLifePlayInSeconds
						, CONCAT(FLOOR(HOUR(SEC_TO_TIME(SRD.TotalLifePlay)) / 24), ' ', LPAD(MOD(HOUR(SEC_TO_TIME(SRD.TotalLifePlay)), 24),2,0), ':', LPAD(MINUTE(SEC_TO_TIME(SRD.TotalLifePlay)),2,0), ':', LPAD(SECOND(SEC_TO_TIME(SRD.TotalLifePlay)),2,0)) AS TotalLifePlayddHHmmss						
						, SRT3.NCompleted AS CompletedTasks
						, SRT2.NStarted AS StartedTasks
						, SRT4.Resources AS TotalTasks						
						, FORMAT(SRT3.NCompleted/SRT4.Resources*100, 2) AS '%Completion'
						, FORMAT(SRT3.NCompleted/SRD.TotalLifePlay*3600, 2) AS 'Throughput [solved tasks/hour]'
						, FORMAT(SRD.TotalLifePlay/SRD2.NofPlayers/60, 2) AS 'ALP(Average Life Play) [minutes/player]'
						, FORMAT(SRT3.NCompleted/SRD2.NofPlayers, 2) AS 'Expected contribution [solved tasks/player]'
						from
						(SELECT COUNT(DISTINCT idUser) AS NofPlayers FROM round R join level L on L.idRound = R.idRound WHERE endRound <> '0000-00-00 00:00:00' AND TIME_TO_SEC(TIMEDIFF(endRound, startRound)) < 15*60) SRD2,
						#(SELECT SUM(TIME_TO_SEC(TIMEDIFF(endRound, startRound))) AS TotalLifePlay FROM round WHERE endRound <> '0000-00-00 00:00:00') SRD,
						(SELECT SUM(TIME_TO_SEC(TIMEDIFF(endLevel, startLevel))) AS TotalLifePlay FROM level L join round R on R.idRound = L.idRound WHERE endLevel <> '0000-00-00 00:00:00' AND endRound <> '0000-00-00 00:00:00' AND TIME_TO_SEC(TIMEDIFF(endRound, startRound)) < 15*60) SRD,
						(SELECT COUNT(DISTINCT RT.idResource) AS NStarted FROM resource_has_topic RT WHERE score >  0 AND score <> 2) SRT2,
						(SELECT COUNT(DISTINCT RT.idResource) AS NCompleted FROM resource_has_topic RT WHERE score >=  (SELECT upperThreshold FROM darkskiesiss.configuration) AND score <> 2) SRT3,
						(SELECT COUNT(DISTINCT RT.idResource) AS Resources FROM resource_has_topic RT WHERE score <> 2) SRT4
						  ";
	$result_query_evaluation = $mysqli->query($query_evaluation) or die($mysqli->error.__LINE__);

	if($result_query_evaluation->num_rows > 0) {

		while($row_query_evaluation = $result_query_evaluation->fetch_assoc()) {

			$evaluation['nOfPlayer'] = $row_query_evaluation['NofPlayers'];
			$evaluation['totalLifePlayddHHmmss'] = $row_query_evaluation['TotalLifePlayddHHmmss'];
			$evaluation['completedTasks'] = $row_query_evaluation['CompletedTasks'];
			$evaluation['startedTasks'] = $row_query_evaluation['StartedTasks'];
			$evaluation['totalTasks'] = $row_query_evaluation['TotalTasks'];
			$evaluation['completion'] = $row_query_evaluation['%Completion'];		
			$evaluation['throughput'] = $row_query_evaluation['Throughput [solved tasks/hour]'];
			$evaluation['alp'] = $row_query_evaluation['ALP(Average Life Play) [minutes/player]'];
			$evaluation['contribution'] = $row_query_evaluation['Expected contribution [solved tasks/player]'];		
			
		}
	}

	else
	{
		echo 'NO RESULTS';	
	}

	return $evaluation;

} //CLOSE FUNCTION EVALUATION

function getResources($mysqli, $idResource = null, $likeTxt = null) {
	
	$idResource = isset($idResource) ? $idResource : 0;
	$likeTxt = isset($likeTxt) ? $likeTxt : '';
	
	$resources = array();
	$resource = array();	
	
	$query = 	"SELECT idResource, refId, lat, lng, url
				FROM resource
				WHERE ($idResource = 0 OR idResource = $idResource)
				AND ('$likeTxt' = '' OR nome like '%$likeTxt%')
				;";		
	
	$result = $mysqli->query($query) or die($mysqli->error.__LINE__);
	
	// GOING THROUGH THE DATA
	if($result->num_rows > 0) {

		while($row = $result->fetch_assoc()) {				
			$resource['idResource'] = $row['idResource'];
			$resource['refId'] = $row['refId'];				
			$resource['lat'] = $row['lat'];
			$resource['lng'] = $row['lng'];
			$resource['url'] = $row['url'];
			array_push($resources, $resource);
		}
	}

	return $resources;	
}

function getPlayedResources($mysqli, $idUser = null) {
	
	$idUser = isset($idUser) ? $idUser : 0;
	
	$resources = array();
	$resource = array();	
	
	$query = 	"SELECT DISTINCT R.idResource, refId, lat, lng, url, refUrl
				FROM resource R join logging L on R.idResource = L.idResource
				WHERE ($idUser = 0 OR L.idUser = $idUser)
				ORDER BY L.timestamp DESC
				;";		
	
	$result = $mysqli->query($query) or die($mysqli->error.__LINE__);
	
	// GOING THROUGH THE DATA
	if($result->num_rows > 0) {

		while($row = $result->fetch_assoc()) {				
			$resource['idResource'] = $row['idResource'];
			$resource['refId'] = $row['refId'];				
			$resource['lat'] = $row['lat'];
			$resource['lng'] = $row['lng'];
			$resource['url'] = $row['url'];
			$resource['refUrl'] = $row['refUrl'];
			array_push($resources, $resource);
		}
	}

	return $resources;	
}

function getUserPositions($mysqli, $idUser) {
	
	$position = array();	
	
	$topTenRound = array();
	
	$topTenRound = topTenRound($mysqli);

	$position['topten'] = 0;
	$position['leaderboard'] = 0;
	
	for($i = 0; $i < count($topTenRound); ++$i) {
		if($topTenRound[$i]['idUser'] == $idUser) {
			$position['topten'] = $topTenRound[$i]['position'];
			break;
		}
	}
	
	$query = "select Q.position from (
			SELECT L.*,  case L.score
							when @score then @rank
							when @score := L.score then @rank:= @rank + 1
						end AS position
			FROM leaderboard L
			JOIN user AS U ON L.iduser = U.idUser,			
			(SELECT @row:= 0, @rank:= 0, @score:= 0.0) R
			WHERE U.social <> 'anonymous'
			ORDER BY score DESC
			) Q
			where Q.idUser = $idUser
			  ";
			  
	$result = $mysqli->query($query) or die($mysqli->error.__LINE__);

	if($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$position['leaderboard'] = $row['position'];
		}
	}

	return $position;
}

function getSolvedTasks($mysqli, $timestamp = null) {
	
	$timestamp = isset($timestamp) ? $timestamp : '0000-00-00T00:00:00Z';

	$solvedTasks = array();
	$solvedTask = array();	
	
	$query = 	"SELECT 
				RT.id AS resultId
				, R.refId AS taskId
				, R.url AS smallPhotoUrl
				, T.refId AS class
				, COUNT(DISTINCT L.idUser) AS numPlayers
				, DATE_FORMAT(CONVERT_TZ(RT.timestamp, @@session.time_zone, '+00:00'), '%Y-%m-%dT%TZ') AS solutionDate
				FROM resource_has_topic RT
				JOIN resource R ON RT.idResource = R.idResource
				JOIN topic T ON RT.idTopic = T.idTopic
				JOIN logging L ON RT.idResource = L.idResource AND RT.idTopic = L.idTopic
				WHERE score > (SELECT upperThreshold FROM configuration) AND score <> 2
				AND L.timestamp <= RT.timestamp
				GROUP BY 
				RT.id
				, R.refId
				, R.url
				, T.refId
				, RT.timestamp
				HAVING CONVERT_TZ(RT.timestamp, @@session.time_zone, '+00:00') >= '$timestamp'
				;";		

	$result = $mysqli->query($query) or die($mysqli->error.__LINE__);

	// GOING THROUGH THE DATA
	if($result->num_rows > 0) {

		while($row = $result->fetch_assoc()) {				
			$solvedTask['resultId'] = $row['resultId'];
			$solvedTask['taskId'] = $row['taskId'];				
			$solvedTask['smallPhotoUrl'] = $row['smallPhotoUrl'];
			$solvedTask['class'] = $row['class'];
			$solvedTask['numPlayers'] = $row['numPlayers'];
			$solvedTask['solutionDate'] = $row['solutionDate'];
			array_push($solvedTasks, $solvedTask);
		}
	}

	return $solvedTasks;	
}

function getSolvedTasksCount($mysqli, $timestamp = null) {
	
	$timestamp = isset($timestamp) ? $timestamp : '0000-00-00T00:00:00Z';
	
	$solvedTasksCount = array();	
	
	$query = 	"SELECT COUNT(*) AS solvedTasksCount FROM
				(	
				SELECT 
				RT.id AS resultId
				, R.refId AS taskId
				, R.url AS smallPhotoUrl
				, T.refId AS class
				, COUNT(DISTINCT L.idUser) AS numPlayers
				, DATE_FORMAT(CONVERT_TZ(RT.timestamp, @@session.time_zone, '+00:00'), '%Y-%m-%dT%TZ') AS solutionDate
				FROM resource_has_topic RT
				JOIN resource R ON RT.idResource = R.idResource
				JOIN topic T ON RT.idTopic = T.idTopic
				JOIN logging L ON RT.idResource = L.idResource AND RT.idTopic = L.idTopic
				WHERE score > (SELECT upperThreshold FROM configuration) AND score <> 2
				AND L.timestamp <= RT.timestamp
				GROUP BY 
				RT.id
				, R.refId
				, R.url
				, T.refId
				, RT.timestamp
				HAVING CONVERT_TZ(RT.timestamp, @@session.time_zone, '+00:00') >= '$timestamp'
				) Q;";		

	$result = $mysqli->query($query) or die($mysqli->error.__LINE__);

	// GOING THROUGH THE DATA
	if($result->num_rows > 0) {

		while($row = $result->fetch_assoc()) {				
			$solvedTasksCount['solvedTasksCount'] = $row['solvedTasksCount'];		
		}
	}

	return $solvedTasksCount;	
}

function createTask($mysqli, $taskId, $smallPhotoUrl, $nasaPageUrl) {	
	$ret = "OK";	
	
	$count = 0;
	
	$result = $mysqli->query("SELECT COUNT(idResource) AS count FROM resource WHERE refId = '$taskId';");

	// GOING THROUGH THE DATA
	if($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$count = $row['count'];										
		}
	}
	
	if($count > 0) {
		$ret = 'Error: taskId duplicated.';
		return $ret;	
	}
	
	$insert_row = $mysqli->query("INSERT INTO resource (refId, url, refUrl, orderBy) VALUES ('$taskId', '$smallPhotoUrl', '$nasaPageUrl', rand());");

	if($insert_row){
		$idResource = "".$mysqli->insert_id."";		
		$insert_row = $mysqli->query("INSERT INTO resource_has_topic (idResource, idTopic, score)
									SELECT $idResource AS idResource, idTopic, 0 AS score FROM topic WHERE weight IS NOT NULL;");
									
		if(!$insert_row) {
			$ret = 'Error : ('. $mysqli->errno .') '. $mysqli->error;			
		}
	}else{
		$ret = 'Error : ('. $mysqli->errno .') '. $mysqli->error;
	}

	return $ret;	
}

function getUndoneTasks($mysqli) {	

	$undoneTask = array();	

	$parameters = parameters($mysqli);
	
	$upperThreshold = $parameters["upperThreshold"];
	$nOfLevels = $parameters["nOfLevels"];

	$ngt = floor($nOfLevels/3);
	$nres = ($nOfLevels - $ngt) ;
	
	//GET nres RESOURCES (nOfLevels LEVELS OF THE ROUND)
	//EXCEPT FOR THE ONES WITH WITCH THE USER HAS ALREADY PLAYED;
	//ngt ARE FROM GROUND TRUTH FOR USER REPUTATION (SCORE > UPPER)
	$query = 	"
				SELECT COUNT(idResource) as count
				FROM resource
				WHERE idResource NOT IN (SELECT DISTINCT idResource FROM logging)
				AND idResource NOT IN (SELECT DISTINCT idResource FROM resource_has_topic WHERE score > $upperThreshold)				
				";
				
	$result = $mysqli->query($query) or die($mysqli->error.__LINE__);

	// GOING THROUGH THE DATA
	if($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {				
			$undoneTask['numUndoneTasks'] = $row['count'];
			$undoneTask['minNumRequired'] = $nOfLevels;							
		}
	}
	
	return $undoneTask;		
}

function getChosenTasks($mysqli, $timestamp = null) {
	
	$timestamp = isset($timestamp) ? $timestamp : '0000-00-00T00:00:00Z';
	
	$chosenTasks = array();
	$chosenTask = array();	

	$query = 	"SELECT 
				L.idUser 
				, R.refId AS taskId
				, R.url AS smallPhotoUrl
				, DATE_FORMAT(CONVERT_TZ(L.timestamp, @@session.time_zone, '+00:00'), '%Y-%m-%dT%TZ') AS timestampUTC
				, T.refId AS chosenCategory				
				, CASE WHEN RT.score > (SELECT upperThreshold FROM configuration) and RT.timestamp <= RO.startRound THEN true ELSE false END AS groundTruth				
				, CASE WHEN U.social = 'anonymous' THEN true ELSE false END AS guest
				, CASE WHEN U.social = 'anonymous' THEN U.idSocial END AS ipAddress
				FROM logging L
				JOIN resource R ON L.idResource = R.idResource
				JOIN topic T ON L.idTopic = T.idTopic
				JOIN user U ON L.idUser = U.idUser
				JOIN resource_has_topic RT ON L.idResource = RT.idResource AND L.idTopic = RT.idTopic
				JOIN round RO ON L.idRound = RO.idRound				
				WHERE L.choosen = 1
				AND CONVERT_TZ(L.timestamp, @@session.time_zone, '+00:00') >= '$timestamp'
				;";		
	
	$result = $mysqli->query($query) or die($mysqli->error.__LINE__);
	
	// GOING THROUGH THE DATA
	if($result->num_rows > 0) {

		while($row = $result->fetch_assoc()) {				
			$chosenTask['idUser'] = $row['idUser'];
			$chosenTask['taskId'] = $row['taskId'];				
			$chosenTask['smallPhotoUrl'] = $row['smallPhotoUrl'];
			$chosenTask['timestampUTC'] = $row['timestampUTC'];
			$chosenTask['chosenCategory'] = $row['chosenCategory'];
			$chosenTask['groundTruth'] = $row['groundTruth'];
			$chosenTask['guest'] = $row['guest'];
			$chosenTask['ipAddress'] = $row['ipAddress'];
			array_push($chosenTasks, $chosenTask);
		}
	}

	return $chosenTasks;	
}

?>