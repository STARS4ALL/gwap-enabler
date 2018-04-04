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
.controller("gwapCtrl",function($scope, $rootScope, $http, $routeParams, $location, $cookies, $timeout, gwapService, $window) {
$scope.gameIsStart = 0;
$scope.userFromStart = 0;
$scope.data = {};
$scope.selectedIndex = 0;
$scope.value = [];
$scope.endGameResults = [];
$scope.score = 0;
$scope.useTimer = true;
$scope.timerSeconds = 60;
$scope.newBadge = [];
$scope.languageKey = 'it';
$scope.rankLeaderboard = 0;
$scope.rankTopten = 0;
$scope.leaderboard = 0;
$scope.topten = 0;	

$scope.home = function () {
	$scope.stop();
	$scope.gameIsStart = 0;
	$location.path("/home");
};

$scope.setUser = function(socialNetwork) {

	$scope.SocialNetwork = socialNetwork;
	if (socialNetwork != 'anonymous') {		
		hello.login($scope.SocialNetwork);

		hello.on('auth.login', function(auth){
			var apiUrl = '/me';
			if(auth.network ===	'facebook') {
				apiUrl = '/me?fields=first_name,last_name,name,cover';
			}
			hello(auth.network).api(apiUrl).then(function(r){			
				gwapService.socialLogin(r,auth).then(function(data){
					$scope.dataUser = data;
									
					$scope.idUser = angular.copy($scope.dataUser.idUser);
					$scope.user = angular.copy($scope.dataUser.firstName);
					$scope.userName = angular.copy($scope.dataUser.name);
					$scope.userCover = angular.copy($scope.dataUser.cover);
					
					$cookies.put('gwap_idUser', data.idUser);
					$cookies.put('gwap_user', data.firstName);
					$cookies.put('gwap_userName', data.name);
					$cookies.put('gwap_userCover', data.cover);
					$cookies.put('gwap_userSocial', $scope.SocialNetwork);
					
					$window.localStorage.setItem('token', data.token);					
				})
				.then (function() {	
					var toUrl = $rootScope.redirectTo;
					$rootScope.redirectTo = null;
					$rootScope.allowAnonymous = null;
					if(toUrl) {
						$location.path(toUrl);	
					} else {
						$location.path('/');
					}				
				});	
			});
		});
	} else {		
		gwapService.anonymousLogin().then(function(data){
			$scope.dataUser = data;
							
			$scope.idUser = angular.copy($scope.dataUser.idUser);
			$scope.user = angular.copy($scope.dataUser.firstName);
			$scope.userName = angular.copy($scope.dataUser.name);
			$scope.userCover = angular.copy($scope.dataUser.cover);
			
			$cookies.put('gwap_idUser', data.idUser);
			$cookies.put('gwap_user', data.firstName);
			$cookies.put('gwap_userName', data.name);
			$cookies.put('gwap_userCover', data.cover);
			$cookies.put('gwap_userSocial', $scope.SocialNetwork);
			
			$window.localStorage.setItem('token', data.token);
		})
		.then (function() {	
			var toUrl = $rootScope.redirectTo;
			var allowAnonymous = $rootScope.allowAnonymous;
			$rootScope.redirectTo = null;
			$rootScope.allowAnonymous = null;
			if(toUrl && allowAnonymous) {
				$location.path(toUrl);	
			} else {
				$location.path('/');
			}				
		});	
	}
};

$scope.changeUser = function(user) {
		$scope.userBadges = null; 

		$scope.rankLeaderboard = 0;
		$scope.rankTopten = 0;
		
		$scope.idUser = null;
		$scope.user = null;
		$scope.userName = null;	
		$scope.userCover = null;	
		$scope.SocialNetwork = null;
		
		$scope.stop();
		$cookies.remove('gwap_idUser');
		$cookies.remove('gwap_user');		
		$cookies.remove('gwap_userName');
		$cookies.remove('gwap_userCover');
		$cookies.remove('gwap_userSocial');
		$scope.userFromStart = 0;
		
		$window.localStorage.removeItem('token');

		$scope.stop();
		$scope.gameIsStart = 0;

		$location.path("/home");

};

$scope.countdown = function() {
    $scope.countdownStop = $timeout(function() {
		if($scope.counter == 0 || $scope.stopTimer == true){
			$scope.stop();
			$scope.completeRound();
		}
		else{
			$scope.counter--;   
			$scope.countdown();
		} 
    }, 1000);
 };

$scope.stop = function(){
   $timeout.cancel($scope.countdownStop);
};

$scope.$on('load', function(){
	$scope.loading = true;
	$scope.gotPartner = false;
});

$scope.$on('unload', function(){
	$scope.loading = false;
});

$scope.getLevels = function() {

	$scope.data = {};

	return $http({
		method  : 'POST',
		url     : 'api/levels.php', 
		data    : {'idUser': $cookies.get('gwap_idUser')}, //'nOfLevels': $scope.nOfLevels},
		headers : { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }
	})
	.success(function (data) {
		$scope.data.resource = data.levels;			

		$scope.idRound = data.round.idRound;
		
		$scope.$emit('unload');
	})
	.error(function (error) {
		$scope.data.error = error;
	});		

};

$scope.getPartner = function () {
	// il codice commentato serve per rendere casuale se mostrare la pagina di connection (waiting for a partner...) per un pò prima del countdown
	// var rnd = Math.random();
	var rndTime = 0;
	// if(rnd > 0.5) {
		// rndTime = Math.floor(Math.random() * 10000) + 5000;
	// }
	rndTime = Math.floor(Math.random() * 10000) + 5000;
	$timeout(function() {		
		$scope.gotPartner = true;
		$scope.liftOff();
		$location.path("/countdown").replace();
	}, rndTime);	
};

$scope.liftOff = function () {
	$timeout(function() {		
		$location.path("/game/1").replace();
	}, 4000);	
};

$scope.startgame = function () {
	
	if (!$cookies.get('gwap_idUser')){
		$location.path("/login");
	}
	else {		
		$scope.$emit('load');
		$scope.endGameResults = [];
		$scope.newBadge = [];
		$scope.counter = $scope.timerSeconds;
		$scope.gameTime = null;
		$scope.stopTimer = false;
		$scope.currentDateStart = new Date();
		$scope.startGameTime = $scope.currentDateStart.getTime();
		$scope.selectedIndex = 0;
		$scope.userFromStart = 1;
		$scope.gameIsStart = 1;
		$scope.timerCanStart = false;
		
		$scope.getLevels().then( function() {

			$scope.getPartner();
			
			$location.path("connection").replace();			
			
			$scope.score = 0;
			$scope.currentPage = null;
		
			$http({
				method  : 'POST',
				url     : 'api/userPosition.php', 
				data    : {'idUser': $scope.idUser },
				headers : { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }
			})
			.success(function(data, status) {			
				$scope.prevLeaderboard = data.leaderboard;
				$scope.prevTopten = data.topten;			
			})
			.error(function(data, status) {			
				 console.log("post errore");   		 
			});		
		
		});		
	}
};

$scope.itemClicked = function (game) {
	
	if($scope.value.length==1) {
		return false;
	}

	$scope.value.push(game.idTopic);
	
	var showAnswerResult = -1;
	var answerLabel = '';
	var score = $scope.score;
	
	var idTopicTrue = 0;
	var isGT = -1;
	
	$http({
			method  : 'POST',
			url     : 'api/logging.php', 
			data    : {'idUser': $scope.idUser, 'idTopic': game.idTopic, 'idResource': $scope.currentPage.idResource, 'idRound': $scope.idRound, 'level': $scope.currentPage.level, 'json': $scope.currentPage.games},
			headers : { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }
	})
	.success(function(data, status) {
		idTopicTrue = data.idTopicTrue;
		answerLabel = data.topicLabel;
		score = data.score;
		$scope.consecutiveAnswer = data.consecutiveAnswer;

		if(idTopicTrue != game.idTopic)
		{			
			showAnswerResult = 0;
			$scope.imageToJson(game.idTopic, $scope.currentPage.idResource, $scope.currentPage.level, $scope.currentPage.games);			
			$scope.nextLevel = $scope.currentPage.nextlevel;
			$scope.selectedIndex = game.idTopic;
		}
		else{
			showAnswerResult = 2;			
			$scope.imageToJson(game.idTopic, $scope.currentPage.idResource, $scope.currentPage.level, $scope.currentPage.games);
			$scope.nextLevel = $scope.currentPage.nextlevel;
			$scope.selectedIndex = game.idTopic;
		}
			
		var rnd = Math.random();
		if(rnd > 0.5) {
			var rndTime = Math.floor(Math.random() * 3000) + 500;
			$timeout(function () {
				$scope.showAnswerResult = showAnswerResult;
				$scope.answerLabel = answerLabel;
				$scope.score = score;
			}, rndTime);
		} else {
			$scope.showAnswerResult = showAnswerResult;
			$scope.answerLabel = answerLabel;
			$scope.score = score;
		}
		
		$scope.insertLevel();
	})
	.error(function(data, status) {			
		 
	});
	
};

$scope.nextLevelButton = function () {
	
	if($scope.nextLevel > $scope.data.resource.length || $scope.counter <= 0){
		
		$scope.selectedIndex == 0		
		
		if($scope.useTimer) {		
			$scope.stopTimer = true;
			$scope.stop();
			$scope.completeRound();
		} else {
			$scope.completeRound();
		}
	}
	else{
		$location.path("/game/"+$scope.nextLevel).replace();
	}
};

$scope.imageToJson = function (idTopic, idResource, level, games) {
	$scope.jsonImage = angular.toJson({"idTopic": idTopic, "idResource": idResource, "level": level, "games": games });
	$scope.endGameResults.push($scope.jsonImage);
};

$scope.completeRound = function () {	
	$scope.currentDateEnd = new Date();
	$scope.endGameTime = $scope.currentDateEnd.getTime();
	$scope.lifeTime = $scope.endGameTime - $scope.startGameTime;
	$scope.gameTime = $scope.timerSeconds - $scope.counter;
	$scope.stop();
	
	$http({
		method  : 'POST',
        url     : 'api/complete.php', 
        data    : {'idUser': $scope.idUser, 'lifeTime': $scope.lifeTime, 'idRound': $scope.idRound, 'gameTime': $scope.gameTime , 'json': $scope.endGameResults},
        headers : { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }
	})
	.success(function(data, status) {
		$scope.newBadge = data.badges;
		$scope.missingPoints = data.missingPoints;					
		$scope.leaderboard = data.position['leaderboard'];
		$scope.topten = data.position['topten'];
		
		$scope.gameIsStart = 0;	
		$location.path("/gameover");
		
	})
	.error(function(data, status) {
	    console.log("post errore");  
		$scope.gameIsStart = 0;	
		$location.path("/gameover");
	});

};

$scope.abortRound = function () {	
	$scope.home();
}

$scope.$watch(function () { return $routeParams.level }, function (newVal) {

	if($location.path() === '' || $location.path() === '/' || $location.path() === '/home') {
		$scope.home();
	}

    if (typeof newVal === 'undefined') return;

	$scope.value = [];
	$scope.selectedIndex = 0;
	$scope.showAnswerResult = null;
	$scope.answerLabel = null;
	
	if ($scope.data.resource.length == 0) return;
		
    if (newVal == 1){

		$scope.timerCanStart = true;
		$scope.currentPage = $scope.data.resource.filter(function (p) { 
			return p.level === newVal;
		})[0];

		$scope.getUserBadgesIdRound();				
			
		if($scope.useTimer && $scope.timerCanStart) {
			$scope.countdown();			
		}
		
    }
    else{
		if(newVal == $scope.currentPage.nextlevel) {
			$scope.currentPage = $scope.data.resource.filter(function (p) { 
				return p.level === newVal;
			})[0];
			$scope.insertLevel();
		} else {
			$scope.resetGameInfo();
		}		
	}
});

$scope.getUserBadges = function () {
	if($scope.idUser){
		$http({
			method  : 'POST',
			url     : 'api/userBadge.php', 
			data    : {'idUser': $scope.idUser},
			headers : { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }
		})
		.success(function(data, status) {				
			$scope.userBadges = data;
		
		})
		.error(function(data, status) {
			console.log("post errore");  
		});
	}	
};

$scope.getUserBadgesIdRound = function () {
	if($scope.idUser){
		$http({
			method  : 'POST',
			url     : 'api/userBadgeIdRound.php', 
			data    : {'idUser': $scope.idUser},
			headers : { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }
		})
		.success(function(data, status) {				
			$scope.userBadges = data.badges;
			$scope.insertLevel();
		})
		.error(function(data, status) {
			console.log("post errore");  
		});
	}	
};

$scope.getUserPosition = function () {
	if($scope.idUser){
		$http({
			method  : 'POST',
	        url     : 'api/userPosition.php', 
	        data    : {'idUser': $scope.idUser },
	        headers : { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }
		})
		.success(function(data, status) {			
			$scope.rankLeaderboard = data.leaderboard;
			$scope.rankTopten = data.topten;
	    })
	    .error(function(data, status) {			
	         console.log("post errore"); 			 
	    });
	}	
};

$scope.insertLevel = function () {
	if($scope.idUser){
		$http({
			method  : 'POST',
			url     : 'api/insertLevel.php', 
			data    : {'level': $scope.currentPage.level, 'idRound': $scope.idRound},
			headers : { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }
		})
		.success(function(data, status) {							
		})
		.error(function(data, status) {
			
		});
	}	
};

$scope.resetGameInfo = function () {
	$scope.currentPage = null;
	$scope.counter = $scope.timerSeconds;
	$scope.score = 0;
};

$scope.toInt = function (arg) {
	var i = parseInt(arg);
	return i;
};


if($cookies.get('gwap_idUser')) {
	$scope.idUser = $cookies.get('gwap_idUser');
	$scope.user = $cookies.get('gwap_user');
	$scope.userName = $cookies.get('gwap_userName');
	$scope.userCover = $cookies.get('gwap_userCover');
	$scope.SocialNetwork = $cookies.get('gwap_userSocial');
}	
	
});