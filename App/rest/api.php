<?php

/* 
	This is an example class script proceeding secured API
	To use this class you should keep same as query string and function name
	Ex: If the query string value rquest=delete_user Access modifiers doesn't matter but function should be
		 function delete_user(){
			 You code goes here
		 }
	Class will execute the function dynamically;
	
	usage :
	
		$object->response(output_data, status_code);
		$object->_request	- to get santinized input 	
		
		output_data : JSON (I am using)
		status_code : Send status message for headers
*/

//include 'db_connect.php';

include ("Rest.inc.php");
include '../api/jwt.php';
include_once '../secret/globals.php';

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
			
		//$this->mysqli = new mysqli($this->DB_HOST, $this->DB_USER, $this->DB_PASS, $this->DB_NAME);
		$this->mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
		$this->mysqli->set_charset("utf8");
	}
	
	
	/*
	 * Public method for access api.
	 * This method dynmically call the method based on the query string
	 *
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
		
		include '../api/functions.php';

		$solvedTasks = array();

		$solvedTasks = getSolvedTasks($this->mysqli, $timestamp);
		
		mysqli_close($this->mysqli);
		
		$this->response($this->json($solvedTasks), 200);
	}

	private function solvedTasksCount(){
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
		
		include '../api/functions.php';

		$solvedTasksCount = array();

		$solvedTasksCount = getSolvedTasksCount($this->mysqli, $timestamp);
		
		mysqli_close($this->mysqli);
	
		$this->response($this->json($solvedTasksCount), 200);
	}	
	
	private function tasks(){					
		$return = array();
	
		// Cross validation if the request method is POST else it will return "Not Acceptable" status
		if($this->get_request_method() != "POST"){
			$return['status'] = 405;
			$return['error'] = 'Method Not Allowed';
			
			$this->response($this->json($return),405);
		}
		
		$taskId = null;
		if(isset($this->_request->taskId)) {
			$taskId = (string)$this->_request->taskId;
		} else {
			$return['status'] = 500;
			$return['error'] = 'Missing parameter taskId';
			
			$this->response($this->json($return),500);
		}
		
		$smallPhotoUrl = null;
		if(isset($this->_request->smallPhotoUrl)) {
			$smallPhotoUrl = (string)$this->_request->smallPhotoUrl;
		} else {
			$return['status'] = 500;
			$return['error'] = 'Missing parameter smallPhotoUrl';
			
			$this->response($this->json($return),500);
		}
					
		$nasaPageUrl = null;
		if(isset($this->_request->nasaPageUrl)) {
			$nasaPageUrl = (string)$this->_request->nasaPageUrl;
		} else {
							$return['status'] = 500;
			$return['error'] = 'Missing parameter nasaPageUrl';
			
			$this->response($this->json($return),500);
		}
		
		include '../api/functions.php';

		$result = createTask($this->mysqli, $taskId, $smallPhotoUrl, $nasaPageUrl);

		mysqli_close($this->mysqli);

		
		if($result == "OK") {				
			// If success everythig is good send header as "OK" and return list of users in JSON format				
			$return['taskId'] = $taskId;
			$return['smallPhotoUrl'] = $smallPhotoUrl;
			$return['nasaPageUrl'] = $nasaPageUrl;				
			$this->response($this->json($return), 201);
		} else {
			$return['status'] = 500;
			$return['error'] = 'An error occurred: ' .$result;
		}
		$this->response($this->json($return),500);
	}

	private function undoneTasks(){
		$return = array();
		
		// Cross validation if the request method is GET else it will return "Not Acceptable" status
		if($this->get_request_method() != "GET"){
			$return['status'] = 405;
			$return['error'] = 'Method Not Allowed';
			
			$this->response($this->json($return),405);
		}
		
		include '../api/functions.php';

		$undoneTask = array();

		$undoneTask = getUndoneTasks($this->mysqli);
		
		mysqli_close($this->mysqli);
	
		$this->response($this->json($undoneTask), 200);
	}		
	
	private function timeleaderboard(){					
		$return = array();
	
		// Cross validation if the request method is POST else it will return "Not Acceptable" status
		if($this->get_request_method() != "POST"){
			$return['status'] = 405;
			$return['error'] = 'Method Not Allowed';
			
			$this->response($this->json($return),405);
		}
		
		$fromUTC = isset($this->_request->startDateUTC) ? $this->_request->startDateUTC : null;
		$toUTC = isset($this->_request->endDateUTC) ? $this->_request->endDateUTC : null;		

		include '../api/functions.php';
		
		$leaderboardByTime = array();
		
		$leaderboardByTime = leaderboardByTime($this->mysqli, $fromUTC, $toUTC);

		mysqli_close($this->mysqli);
		
		$this->response($this->json($leaderboardByTime), 200);		
	}
	
	private function doneTasks(){
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
		
		include '../api/functions.php';

		$chosenTasks = array();

		$chosenTasks = getChosenTasks($this->mysqli, $timestamp);
		
		mysqli_close($this->mysqli);
		
		$this->response($this->json($chosenTasks), 200);
	}	
	
	private function evaluation(){
		$return = array();
		
		// Cross validation if the request method is GET else it will return "Not Acceptable" status
		if($this->get_request_method() != "GET"){
			$return['status'] = 405;
			$return['error'] = 'Method Not Allowed';
			
			$this->response($this->json($return),405);
		}
				
		include '../api/functions.php';

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