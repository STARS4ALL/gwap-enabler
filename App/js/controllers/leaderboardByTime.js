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
.controller("leaderboardByTimeCtrl", function($scope, $http, $routeParams, $location, $cookies, $cookieStore) {

	$scope.limit			= 10;
	$scope.scrollDisabled	= true;
	
	$scope.$emit('load');
	$scope.data = {};	
	
	$scope.startDateUTC = $routeParams.startDateUTC ? ($routeParams.startDateUTC.endsWith("Z") ? new Date($routeParams.startDateUTC.replace("Z", "")) : new Date($routeParams.startDateUTC)) : null;
	$scope.endDateUTC = $routeParams.endDateUTC ? ($routeParams.endDateUTC.endsWith("Z") ? new Date($routeParams.endDateUTC.replace("Z", "")) : new Date($routeParams.endDateUTC)) : null; 
	
	$scope.getLeaderboardByTime = function() {
		$http({
				method  : 'POST',
				url     : 'api/leaderboardByTime.php', 
				data    : {'fromUTC': $routeParams.startDateUTC, 'toUTC': $routeParams.endDateUTC},
				headers : { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }
		})
		.success(function (data) {	
			$scope.data.leaderboardByTime = data;
			$scope.leaderboardByTime = $scope.data.leaderboardByTime;
			$scope.scrollDisabled = false;		
			$scope.$emit('unload');			
		})
		.error(function (error) {
			$scope.data.error = error;
			$scope.$emit('unload');
		});
		
	}
	
	$scope.increaseLimit = function () {
		if ($scope.limit < $scope.leaderboardByTime.userScore.length) {
			$scope.limit += 10;
		}
	};	

	$scope.getLeaderboardByTime();
	
});