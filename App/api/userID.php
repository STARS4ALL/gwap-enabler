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
					
$user = array();
$postdata = file_get_contents("php://input");
$request = json_decode($postdata);
$firstName = isset($request->firstName) ? $request->firstName : '';
$lastName = isset($request->lastName) ? $request->lastName : '';
$idSocial = isset($request->idSocial) ? $request->idSocial : get_client_ip();
$social = $request->social;
$name = isset($request->name) ? $request->name : '';
$thumbnail = isset($request->thumbnail) ? $request->thumbnail : '';
$cover = isset($request->cover) ? $request->cover : '';
//$access_token = isset($request->access_token) ? $request->access_token : ''

$select_user = "SELECT idUser FROM user WHERE idSocial = '$idSocial' AND social = '$social'";
$result = $mysqli->query($select_user) or die($mysqli->error.__LINE__);

// GOING THROUGH THE DATA
if($result->num_rows > 0) {
	
	while($row = $result->fetch_assoc()) {
		$user["idUser"] = "".$row['idUser']."";
		$user["firstName"] = "".$firstName."";
		$user["name"] = "".$name."";
		$user["cover"] = "".$cover."";
	}

	$update_user = $mysqli->query("UPDATE user SET firstName = '$firstName', lastName = '$lastName', name = '$name', cover = '$cover', thumbnail = '$thumbnail'
						 WHERE idSocial = '$idSocial' AND social = '$social'");
						 
	if($update_user){
		//print $update_user;
	}else{
		die('Error : ('. $mysqli->errno .') '. $mysqli->error);
	}												 

}
else
{
	$insert_user = $mysqli->query("INSERT INTO user (firstName, lastName, idSocial, social, name, cover, thumbnail) 
									VALUES ('$firstName', '$lastName', '$idSocial', '$social', '$name', '$cover', '$thumbnail')");

	if($insert_user){
		if($mysqli->affected_rows > 0){
			$user["idUser"] = "".$mysqli->insert_id."";
			$user["firstName"] = "".$firstName."";
			$user["name"] = "".$name."";
			$user["cover"] = "".$cover."";			
		}
	}else{
		die('Error : ('. $mysqli->errno .') '. $mysqli->error);
	}
}

$role = 'GUEST'; //GUEST, USER, ADMIN
if(count($user) > 0) {	
	//if($access_token != '') {
	if($social != 'anonymous') {
		$role = 'USER';
	}	
	$jwt = generateToken($user, $role);	
	$user["token"] = "".$jwt."";
}
		
/* close connection */
$mysqli->close();

echo json_encode($user);

?>