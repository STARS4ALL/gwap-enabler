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
require __DIR__ . '/vendor/autoload.php';

use Jose\JWTCreator;
use Jose\Signer;
use Jose\Object\JWK;
use Jose\JWTLoader;
use Jose\Verifier;
use Jose\Factory\CheckerManagerFactory;
use Jose\Object\JWKSet;

include_once '../secret/globals.php';

function isAuthorized(array $roles = ['GUEST', 'USER', 'ADMIN']){

	$header = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : (isset(apache_request_headers()['Authorization']) ? apache_request_headers()['Authorization'] : null);

	
	if ($header == null || !(substr($header, 0, 7) === "Bearer ")) {     			
		http_response_code(401);
		return false;			
	}

	$authToken = substr($header, 7);

	$user = null;
	$user = parseToken($authToken);	
	if ($user == null) {
		http_response_code(401);
		return false;        				
	}

	if (!in_array($user['role'], $roles)) {
		http_response_code(403);
		return false;   
	}

	return true;
}

function parseToken($authToken) {
	$user = null;
	
	$claim_checker_list = [
		'exp',
		'iat',
		'nbf'
	];
	$header_checker_list = [
		'crit'
	];

	$checker_manager = CheckerManagerFactory::createClaimCheckerManager($claim_checker_list, $header_checker_list);

	$verifier = Verifier::createVerifier(['HS512']);
	$jwt_loader = new JWTLoader($checker_manager, $verifier);
	
	$jwt = $jwt_loader->load(
		$authToken // The JWS string you want to load
	);
	
	$signature_key = new JWK([
		'kty' => 'oct',
		'k'   => $GLOBALS['jwt_secret']
	]);
	
	$jwkset = new JWKSet();
	$jwkset->addKey($signature_key);
	
	try {
		$index = $jwt_loader->verify(
			$jwt,    // The JWS object
			$jwkset // A Jose\JWKSetInterface object that contains public keys
		);
	} catch (Exception $e) {
		return null;
	}

	//return $index;
	
	$user = array();	
	$user["idUser"] = $jwt->getClaim('sub');
	$user["role"] = $jwt->getClaim('role');
		
	return $user;
}

function generateToken($user, $role) {	
	//$jws = '';		

	// We create a Signer object with the signature algorithms we want to use
	$signer = Signer::createSigner(['HS512']);
	$jwt_creator = new JWTCreator($signer); // The variable $signer must be a valid Jose\SignerInterface object
		
	$payload = [
		'nbf'     => time(),        // Not before
		'iat'     => time(),        // Issued at
		'exp'     => time() + 3600, // Expires at
		// 'iss'     => 'Me',          // Issuer
		// 'aud'     => 'You',         // Audience
		'sub'     => $user["idUser"],   // Subject,
		'role' 	  => $role           // Custom claim
	];	
	
	$headers = [
		'alg'	 => 'HS512'
	];
	
	$signature_key = new JWK([
		'kty' => 'oct',
		'k'   => $GLOBALS['jwt_secret'] //'miosegreto',
	]);

	$jwt = $jwt_creator->sign(
		$payload,       // The payload to sign
		$headers,       // The protected headers (must contain at least the "alg" parameter)
		$signature_key  // The key used to sign (depends on the "alg" parameter)
	);
		
	return $jwt;
}	

?>