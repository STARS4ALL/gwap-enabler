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
.controller("leaderboardCtrl", function($scope, $http, $routeParams, $location, $cookies, $cookieStore) {

	$scope.limit1			= 10;
	$scope.scrollDisabled1	= true;
	$scope.limit2			= 10;
	$scope.scrollDisabled2	= true;
	
	$scope.$emit('load');
	$scope.data = {};
	
	$scope.endCall1 = false;
	$scope.endCall2 = false;
	
	$scope.$on('load', function(){
		$scope.loading = true;
		$scope.gotPartner = false;
	});

	$scope.$on('endCall', function(){		
		if($scope.endCall1 && $scope.endCall2) {
			$scope.$emit('unload');	
		}	
	});
	
	$http({
			method  : 'POST',
			url     : 'api/leaderboard.php', 
			data    : {'idUser': $cookieStore.get('gwap_idUser')},
			headers : { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }
	})
	.success(function (data) {	
		$scope.data.leaderboard = data;
		$scope.leaderboard = $scope.data.leaderboard;
		$scope.endCall1 = true;
		$scope.scrollDisabled1 = false;		
		$scope.$emit('endCall');				
	})
	.error(function (error) {
		$scope.data.error = error;
		$scope.endCall1 = true;
		$scope.$emit('endCall');
	});
	
	$scope.increaseLimit1 = function () {
		if ($scope.limit1 < $scope.leaderboard.topRank.length) {
			$scope.limit1 += 10;
		}
	};
	
	$http({
		method  : 'GET',
        url     : 'api/topTenRound.php', 
        headers : { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }
	})
	.success(function (data) {	
		$scope.data.topTenRound = data;
		$scope.topTenRound = $scope.data.topTenRound;	
		$scope.endCall2 = true;
		$scope.scrollDisabled2 = false;
		$scope.$emit('endCall');
	})
	.error(function (error) {
		$scope.data.error = error;
		$scope.endCall2 = true;
		$scope.$emit('endCall');
	});

	$scope.increaseLimit2 = function () {
		if ($scope.limit2 < $scope.topTenRound.length) {
			$scope.limit2 += 10;
		}
	};
	
});