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
 
'use strict';
angular.module("gwap")
.factory('loginService', function($http){

	return{
		sociallogin: function(data, scope, location, rootScope, cookies){
			var $promise = $http({
								method  : 'POST',
            					url     : 'api/admin.php', 
            					data    : userLogin,
            					headers : { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }
    						});
			$promise.then(function(msg){
				if(msg.data=='success') {
					scope.msgtxt = 'Success';
					rootScope.loggedInUser = scope.msgtxt;
					cookies.gwapAdmin = scope.msgtxt;
    				location.path("/upload");
				} 
				else {
					scope.msgtxt = 'Error';
				}
			})
		}
	}
})