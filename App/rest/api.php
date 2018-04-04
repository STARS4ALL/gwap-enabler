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

include ("Rest.inc.php");
include '../api/jwt.php';
include_once '../secret/globals.php';
include '../api/functions.php';

class API extends REST {

	public $data = "";
	
	private $mysqli = NULL;

	
	public function __construct(){
		parent::__construct();				// Init parent contructor
		$this->dbConnect();					// Initiate Database connection
	}
	
	/*
	 *  Database connection 
	*/
	private function dbConnect(){

		$DB_NAME = $GLOBALS['DB_NAME'];
		$DB_HOST = $GLOBALS['DB_HOST'];
		$DB_USER = $GLOBALS['DB_USER'];
		$DB_PASS = $GLOBALS['DB_PASS'];
			
		$this->mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
		$this->mysqli->set_charset("utf8");
	}
	
	
	/*
	 * Public method for access api.
	 * This method dynmically call the method based on the query string
	 * Author : Arun Kumar Sekar
	 */
	public function processApi(){
		
		$allowedRoles = ['ADMIN'];
		if(!isAuthorized($allowedRoles)) {
			return;
		}
					
		if(isset($_REQUEST['rquest'])) {
			$func = strtolower(trim(str_replace("/","",$_REQUEST['rquest'])));
			if((int)method_exists($this,$func) > 0)
				$this->$func();
			else
				$this->response('',404);				// If the method not exist with in this class, response would be "Page not found".
		} else 
			$this->response('',404);
	}
	
	private function tasks(){					
		$return = array();
	
		// Cross validation if the request method is POST else it will return "Not Acceptable" status
		if($this->get_request_method() != "POST"){
			$return['status'] = 405;
			$return['error'] = 'Method Not Allowed';
			
			$this->response($this->json($return),405);
		}
		
		$resourceId = null;
		if(isset($this->_request->resourceId)) {
			$resourceId = (string)$this->_request->resourceId;
		} else {
			$return['status'] = 500;
			$return['error'] = 'Missing parameter resourceId';
			
			$this->response($this->json($return),500);
		}
		
		$label = null;
		if(isset($this->_request->label)) {
			$label = (string)$this->_request->label;
		}
			
		$lat = null;
		if(isset($this->_request->lat)) {
			$lat = (string)$this->_request->lat;
		}
		
		$long = null;
		if(isset($this->_request->long)) {
			$long = (string)$this->_request->long;
		}
		
		$url = null;
		if(isset($this->_request->url)) {
			$url = (string)$this->_request->url;
		}
		
		$result = createTask($this->mysqli, $resourceId, $label, $lat, $long, $url);

		mysqli_close($this->mysqli);

		
		if($result == "OK") {						
			$return['resourceId'] = $resourceId;
			$return['label'] = $label;			
			$return['lat'] = $lat;
			$return['long'] = $long;			
			$return['url'] = $url;				
			$this->response($this->json($return), 201);
		} else {
			$return['status'] = 500;
			$return['error'] = 'An error occurred: ' .$result;
		}
		$this->response($this->json($return),500);
	}
	
	private function solvedTasks(){
		$return = array();
		
		// Cross validation if the request method is GET else it will return "Not Acceptable" status
		if($this->get_request_method() != "GET"){
			$return['status'] = 405;
			$return['error'] = 'Method Not Allowed';
			
			$this->response($this->json($return),405);
		}
		
		$timestamp = null;
		if(isset($this->_request['timestamp'])) {
			$timestamp = $this->_request['timestamp'];
		}

		$solvedTasks = array();

		$solvedTasks = getSolvedTasks($this->mysqli, $timestamp);
		
		mysqli_close($this->mysqli);
		
		$this->response($this->json($solvedTasks), 200);
	}

	private function solutionLinks() {
		$return = array();
		
		// Cross validation if the request method is GET else it will return "Not Acceptable" status
		if($this->get_request_method() != "GET"){
			$return['status'] = 405;
			$return['error'] = 'Method Not Allowed';
			
			$this->response($this->json($return),405);
		}
		
		$timestamp = null;
		if(isset($this->_request['timestamp'])) {
			$timestamp = $this->_request['timestamp'];
		}

		$solvedTasks = array();

		$solvedTasks = getSolutionLinks($this->mysqli, $timestamp);
		
		mysqli_close($this->mysqli);		
		
		$this->response($this->json($solvedTasks), 200);
	}
	
	private function undoneTasks(){
		$return = array();
		
		// Cross validation if the request method is GET else it will return "Not Acceptable" status
		if($this->get_request_method() != "GET"){
			$return['status'] = 405;
			$return['error'] = 'Method Not Allowed';
			
			$this->response($this->json($return),405);
		}

		$undoneTask = array();

		$undoneTask = getUndoneTasks($this->mysqli);
		
		mysqli_close($this->mysqli);
	
		$this->response($this->json($undoneTask), 200);
	}
	
	private function evaluation(){
		$return = array();
		
		// Cross validation if the request method is GET else it will return "Not Acceptable" status
		if($this->get_request_method() != "GET"){
			$return['status'] = 405;
			$return['error'] = 'Method Not Allowed';
			
			$this->response($this->json($return),405);
		}

		$evaluation = array();

		$evaluation = evaluation($this->mysqli);
		
		mysqli_close($this->mysqli);
		
		$this->response($this->json($evaluation), 200);
	}

}

// Initiiate Library
$api = new API;
$api->processApi();
?>