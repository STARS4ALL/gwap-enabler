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
 
angular.module("gwap")
.controller("pictureListCtrl", function($scope, $http, $routeParams, $location, $cookies) {

	$scope.limit			= 12;
	$scope.scrollDisabled	= true;
	
	$scope.$emit('load');
	$scope.data = {};
	$http({
			method  : 'POST',
			url     : 'api/pictureList.php',
			data    : { 'idUser': $cookies.get('gwap_idUser')},		
			headers : { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }
		})
		.success(function (data) {	
			$scope.data.pictureList = data;
			$scope.pictureList = $scope.data.pictureList;
			$scope.scrollDisabled = false;	
			$scope.$emit('unload');
		})
		.error(function (error) {
			$scope.data.error = error;
			$scope.$emit('unload');
		});
		
	$scope.increaseLimit = function () {
		if ($scope.limit < $scope.pictureList.length) {
			$scope.limit += 12;
		}
	};		

});