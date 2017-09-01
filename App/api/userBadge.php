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

$allowedRoles = ['USER','ADMIN'];
if(!isAuthorized($allowedRoles)) {
	return;
}

$badges = array();

$postdata = file_get_contents("php://input");
$request = json_decode($postdata);
$idUser = $request->idUser;

$badges = userBadge($mysqli, $idUser);

$json = $badges;

// CLOSE CONNECTION
mysqli_close($mysqli);

echo json_encode($json);

?>