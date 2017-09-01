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

$idRound = $request->idRound;	
$level = $request->level;	

$select = "SELECT idLevel FROM level WHERE level = '$level' AND idRound = '$idRound';";
$result = $mysqli->query($select) or die($mysqli->error.__LINE__);

// GOING THROUGH THE DATA
if($result->num_rows > 0) {
	
	//UPDATE LEVEL		
	$update_row = $mysqli->query("UPDATE level SET endLevel = CURRENT_TIMESTAMP WHERE level = '$level' AND idRound = '$idRound';");
						 
	if($update_row){
		//print $update_row;
	}else{
		die('Error : ('. $mysqli->errno .') '. $mysqli->error);
	}												 

}
else
{
	//INSERT LEVEL		
	$insert_row = $mysqli->query("INSERT INTO level (level, idRound) VALUES ('$level', '$idRound')");

	if($insert_row){
		//print $insert_row;
	}else{
		die('Error : ('. $mysqli->errno .') '. $mysqli->error);
	}
}
	
/* close connection */
$mysqli->close();
?>
